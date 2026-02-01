<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CRM\Models\Campaign;
use Modules\CRM\Services\CampaignService;
use Modules\CRM\Jobs\SendCampaignEmailJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    public function __construct(
        private CampaignService $campaignService
    ) {}

    public function index()
    {
        $campaigns = Campaign::with('creator')
            ->latest()
            ->paginate(15);

        return view('crm::campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('crm::campaigns.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'target_filters' => ['nullable', 'array'],
        ]);

        $validated['created_by'] = Auth::id();
        $validated['branch_id'] = Auth::user()->branches()->where('branches.is_active', 1)->value('branches.id');
        $validated['status'] = 'draft';

        $campaign = Campaign::create($validated);

        session()->flash('message', __('Campaign created successfully'));

        return redirect()->route('campaigns.show', $campaign);
    }

    public function show(Campaign $campaign)
    {
        $campaign->load(['creator', 'logs.client']);

        $topClients = $campaign->logs()
            ->with('client')
            ->whereIn('status', ['opened', 'clicked'])
            ->latest('opened_at')
            ->take(10)
            ->get();

        return view('crm::campaigns.show', compact('campaign', 'topClients'));
    }

    public function edit(Campaign $campaign)
    {
        if (!$campaign->isDraft()) {
            return redirect()->route('campaigns.show', $campaign)
                ->with('error', __('Cannot edit sent campaign'));
        }

        return view('crm::campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        if (!$campaign->isDraft()) {
            return redirect()->route('campaigns.show', $campaign)
                ->with('error', __('Cannot edit sent campaign'));
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'target_filters' => ['nullable', 'array'],
        ]);

        $campaign->update($validated);

        session()->flash('message', __('Campaign updated successfully'));

        return redirect()->route('campaigns.show', $campaign);
    }

    public function destroy(Campaign $campaign)
    {
        if ($campaign->isSent()) {
            return redirect()->route('campaigns.index')
                ->with('error', __('Cannot delete sent campaign'));
        }

        $campaign->delete();

        session()->flash('message', __('Campaign deleted successfully'));

        return redirect()->route('campaigns.index');
    }

    public function preview(Request $request)
    {
        $filters = $request->input('filters', []);
        $preview = $this->campaignService->previewCampaign($filters);

        return response()->json($preview);
    }

    public function send(Campaign $campaign)
    {
        if (!$campaign->isDraft()) {
            return redirect()->route('campaigns.show', $campaign)
                ->with('error', __('Campaign already sent'));
        }

        // إنشاء سجلات الإرسال
        $totalRecipients = $this->campaignService->createCampaignLogs($campaign);

        if ($totalRecipients === 0) {
            return redirect()->route('campaigns.show', $campaign)
                ->with('error', __('No recipients found'));
        }

        // تحديث حالة الحملة
        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        // إرسال الإيميلات عبر Queue
        foreach ($campaign->logs as $log) {
            SendCampaignEmailJob::dispatch($log, $campaign)->onQueue('emails');
        }

        session()->flash('message', __('Campaign is being sent to') . ' ' . $totalRecipients . ' ' . __('clients'));

        return redirect()->route('campaigns.show', $campaign);
    }
}
