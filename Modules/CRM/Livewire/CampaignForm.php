<?php

declare(strict_types=1);

namespace Modules\CRM\Livewire;

use Livewire\Component;
use Modules\CRM\Models\Campaign;
use Modules\CRM\Models\ClientType;
use Modules\CRM\Models\ClientCategory;
use Modules\CRM\Services\CampaignService;
use Illuminate\Support\Facades\Auth;

class CampaignForm extends Component
{
    public ?Campaign $campaign = null;
    public bool $isEdit = false;

    // بيانات الحملة
    public string $title = '';
    public string $subject = '';
    public string $message = '';

    // فلاتر الاستهداف
    public array $targetFilters = [];
    public ?string $address = null;
    public ?int $clientTypeId = null;
    public ?int $clientCategoryId = null;
    public ?int $lastPurchaseDays = null;
    public ?float $totalPurchasesMin = null;
    public ?bool $isActive = null;

    // معاينة العملاء
    public bool $showPreview = false;
    public int $previewTotal = 0;
    public array $previewClients = [];

    public function mount(?Campaign $campaign = null): void
    {
        if ($campaign && $campaign->exists) {
            $this->campaign = $campaign;
            $this->isEdit = true;
            $this->title = $campaign->title;
            $this->subject = $campaign->subject;
            $this->message = $campaign->message;
            $this->targetFilters = $campaign->target_filters ?? [];

            // تحميل الفلاتر
            $this->address = $this->targetFilters['address'] ?? null;
            $this->clientTypeId = $this->targetFilters['client_type_id'] ?? null;
            $this->clientCategoryId = $this->targetFilters['client_category_id'] ?? null;
            $this->lastPurchaseDays = $this->targetFilters['last_purchase_days'] ?? null;
            $this->totalPurchasesMin = $this->targetFilters['total_purchases_min'] ?? null;
            $this->isActive = $this->targetFilters['is_active'] ?? null;
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'address' => ['nullable', 'string'],
            'clientTypeId' => ['nullable', 'integer', 'exists:client_types,id'],
            'clientCategoryId' => ['nullable', 'integer', 'exists:client_categories,id'],
            'lastPurchaseDays' => ['nullable', 'integer', 'min:1'],
            'totalPurchasesMin' => ['nullable', 'numeric', 'min:0'],
            'isActive' => ['nullable', 'boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => __('Campaign title is required'),
            'subject.required' => __('Email subject is required'),
            'message.required' => __('Campaign message is required'),
        ];
    }

    public function previewCustomers(): void
    {
        $this->prepareFilters();

        $service = app(CampaignService::class);
        $preview = $service->previewCampaign($this->targetFilters);

        $this->previewTotal = $preview['total'];
        $this->previewClients = $preview['preview'];
        $this->showPreview = true;
    }

    public function closePreview(): void
    {
        $this->showPreview = false;
    }

    private function prepareFilters(): void
    {
        $this->targetFilters = array_filter([
            'address' => $this->address,
            'client_type_id' => $this->clientTypeId,
            'client_category_id' => $this->clientCategoryId,
            'last_purchase_days' => $this->lastPurchaseDays,
            'total_purchases_min' => $this->totalPurchasesMin,
            'is_active' => $this->isActive,
        ], fn($value) => $value !== null && $value !== '');
    }

    public function save(): void
    {
        $this->validate();

        $this->prepareFilters();

        $data = [
            'title' => $this->title,
            'subject' => $this->subject,
            'message' => $this->message,
            'target_filters' => $this->targetFilters,
        ];

        if ($this->isEdit) {
            $this->campaign->update($data);
            session()->flash('message', __('Campaign updated successfully'));
            $this->redirect(route('campaigns.show', $this->campaign));
        } else {
            $data['created_by'] = Auth::id();
            $data['branch_id'] = Auth::user()->branches()->where('branches.is_active', 1)->value('branches.id');
            $data['status'] = 'draft';

            $campaign = Campaign::create($data);
            session()->flash('message', __('Campaign created successfully'));
            $this->redirect(route('campaigns.show', $campaign));
        }
    }

    public function render()
    {
        $clientTypes = ClientType::all();
        $clientCategories = ClientCategory::all();
        return view('crm::livewire.campaign-form', [
            'clientTypes' => $clientTypes,
            'clientCategories' => $clientCategories,
        ]);
    }
}
