<?php

namespace Modules\Checks\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\Checks\Models\Check;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\OperHead;
use App\Models\JournalHead;
use App\Models\JournalDetail;

class ChecksController extends Controller
{
    /**
     * Display the main checks page
     */
    public function index(Request $request)
    {
        return redirect()->route('checks.incoming');
    }

    /**
     * Display incoming checks (Ø£ÙˆØ±Ø§Ù‚ Ø§Ù„Ù‚Ø¨Ø¶)
     */
    public function incoming(Request $request)
    {
        $query = Check::query()->with(['creator', 'approver'])->where('type', 'incoming');

        // Search filter
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('check_number', 'like', '%' . $request->search . '%')
                  ->orWhere('bank_name', 'like', '%' . $request->search . '%')
                  ->orWhere('account_holder_name', 'like', '%' . $request->search . '%')
                  ->orWhere('payee_name', 'like', '%' . $request->search . '%');
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('due_date', [$request->start_date, $request->end_date]);
        }

        $checks = $query->orderBy('created_at', 'desc')->paginate(15);
        $pageType = 'incoming';
        $pageTitle = 'Ø£ÙˆØ±Ø§Ù‚ Ø§Ù„Ù‚Ø¨Ø¶';

        return view('checks::index', compact('checks', 'pageType', 'pageTitle'));
    }

    /**
     * Show create form for incoming check
     */
    public function createIncoming()
    {
        $pageType = 'incoming';
        $pageTitle = 'Ø¥Ø¶Ø§ÙØ© ÙˆØ±Ù‚Ø© Ù‚Ø¨Ø¶';
        
        // Get clients accounts
        $accounts = \Modules\\Accounts\\Models\\AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '1103%')
            ->select('id', 'aname', 'code')
            ->get();

        return view('checks::create', compact('pageType', 'pageTitle', 'accounts'));
    }

    /**
     * Display outgoing checks (Ø£ÙˆØ±Ø§Ù‚ Ø§Ù„Ø¯ÙØ¹)
     */
    public function outgoing(Request $request)
    {
        $query = Check::query()->with(['creator', 'approver'])->where('type', 'outgoing');

        // Search filter
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('check_number', 'like', '%' . $request->search . '%')
                  ->orWhere('bank_name', 'like', '%' . $request->search . '%')
                  ->orWhere('account_holder_name', 'like', '%' . $request->search . '%')
                  ->orWhere('payer_name', 'like', '%' . $request->search . '%');
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('due_date', [$request->start_date, $request->end_date]);
        }

        $checks = $query->orderBy('created_at', 'desc')->paginate(15);
        $pageType = 'outgoing';
        $pageTitle = 'Ø£ÙˆØ±Ø§Ù‚ Ø§Ù„Ø¯ÙØ¹';

        return view('checks::index', compact('checks', 'pageType', 'pageTitle'));
    }

    /**
     * Show create form for outgoing check
     */
    public function createOutgoing()
    {
        $pageType = 'outgoing';
        $pageTitle = 'Ø¥Ø¶Ø§ÙØ© ÙˆØ±Ù‚Ø© Ø¯ÙØ¹';
        
        // Get suppliers accounts
        $accounts = \Modules\\Accounts\\Models\\AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '2101%')
            ->select('id', 'aname', 'code')
            ->get();

        return view('checks::create', compact('pageType', 'pageTitle', 'accounts'));
    }

    /**
     * Show the dashboard
     */
    public function dashboard()
    {
        return view('checks::index');
    }

    /**
     * Show the management page
     */
    public function management()
    {
        return view('checks::index', ['defaultTab' => 'management']);
    }

    /**
     * Download attachment
     */
    public function downloadAttachment(Check $check, $attachmentIndex)
    {
        if (!$check->attachments || !isset($check->attachments[$attachmentIndex])) {
            abort(404, 'Attachment not found');
        }

        $attachment = $check->attachments[$attachmentIndex];
        $filePath = $attachment['path'];

        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found');
        }

        $fullPath = Storage::disk('public')->path($filePath);
        return response()->download($fullPath, $attachment['name']);
    }

    /**
     * Export checks to Excel/CSV
     */
    public function export(Request $request)
    {
        $query = Check::query()->with(['creator', 'approver']);

        // Apply filters from request
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('due_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('bank_name')) {
            $query->where('bank_name', 'like', '%' . $request->bank_name . '%');
        }

        $checks = $query->orderBy('created_at', 'desc')->get();

        $filename = 'checks_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($checks) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for proper Arabic encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV headers
            fputcsv($file, [
                'Ø±Ù‚Ù… Ø§Ù„Ø´ÙŠÙƒ',
                'Ø§Ù„Ø¨Ù†Ùƒ',
                'Ø±Ù‚Ù… Ø§Ù„Ø­Ø³Ø§Ø¨',
                'ØµØ§Ø­Ø¨ Ø§Ù„Ø­Ø³Ø§Ø¨',
                'Ø§Ù„Ù…Ø¨Ù„Øº',
                'ØªØ§Ø±ÙŠØ® Ø§Ù„Ø¥ØµØ¯Ø§Ø±',
                'ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ø³ØªØ­Ù‚Ø§Ù‚',
                'ØªØ§Ø±ÙŠØ® Ø§Ù„Ø¯ÙØ¹',
                'Ø§Ù„Ø­Ø§Ù„Ø©',
                'Ø§Ù„Ù†ÙˆØ¹',
                'Ø§Ù„Ù…Ø³ØªÙÙŠØ¯',
                'Ø§Ù„Ø¯Ø§ÙØ¹',
                'Ø±Ù‚Ù… Ø§Ù„Ù…Ø±Ø¬Ø¹',
                'Ù…Ù„Ø§Ø­Ø¸Ø§Øª',
                'Ø£Ù†Ø´Ø¦ Ø¨ÙˆØ§Ø³Ø·Ø©',
                'ØªØ§Ø±ÙŠØ® Ø§Ù„Ø¥Ù†Ø´Ø§Ø¡'
            ]);

            // Data rows
            foreach ($checks as $check) {
                fputcsv($file, [
                    $check->check_number,
                    $check->bank_name,
                    $check->account_number,
                    $check->account_holder_name,
                    $check->amount,
                    $check->issue_date->format('Y-m-d'),
                    $check->due_date->format('Y-m-d'),
                    $check->payment_date ? $check->payment_date->format('Y-m-d') : '',
                    $check->status,
                    $check->type,
                    $check->payee_name,
                    $check->payer_name,
                    $check->reference_number,
                    $check->notes,
                    $check->creator->name ?? '',
                    $check->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show single check
     */
    public function show(Check $check)
    {
        $check->load(['creator', 'approver']);
        return response()->json($check);
    }

    /**
     * Show edit form data
     */
    public function edit(Check $check)
    {
        return response()->json($check);
    }

    /**
     * Store a newly created check
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pro_date' => 'required|date', // Ø§Ù„ØªØ§Ø±ÙŠØ®
            'check_number' => 'required|string|max:50|unique:checks',
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder_name' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'status' => 'required|in:pending,cleared,bounced,cancelled',
            'type' => 'required|in:incoming,outgoing',
            'payee_name' => 'nullable|string|max:100',
            'payer_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'reference_number' => 'nullable|string|max:50',
            'acc1_id' => 'required|integer|exists:acc_head,id', // Ø§Ù„Ø­Ø³Ø§Ø¨ Ø§Ù„Ù…Ø³ØªÙ„Ù… Ù…Ù†Ù‡
            'portfolio_id' => 'required|integer|exists:acc_head,id', // Ø§Ù„Ø­Ø§ÙØ¸Ø© (required now)
            'branch_id' => 'required|exists:branches,id',
        ]);

        try {
            DB::beginTransaction();

            // ØªØ­Ø¯ÙŠØ¯ Ù†ÙˆØ¹ Ø§Ù„Ø¹Ù…Ù„ÙŠØ©
            $proType = $validated['type'] === 'incoming' ? 65 : 66; // 65: ÙˆØ±Ù‚Ø© Ù‚Ø¨Ø¶ØŒ 66: ÙˆØ±Ù‚Ø© Ø¯ÙØ¹
            $portfolioAccount = $validated['portfolio_id']; // Ø§Ù„Ø­Ø§ÙØ¸Ø© Ø§Ù„Ù…Ø®ØªØ§Ø±Ø© Ù…Ù† Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…
            
            // Ø¥Ù†Ø´Ø§Ø¡ Ø§Ù„Ø³Ø¬Ù„ ÙÙŠ operhead
            $lastProId = OperHead::where('pro_type', $proType)->max('pro_id') ?? 0;
            $newProId = $lastProId + 1;

            $oper = OperHead::create([
                'pro_id' => $newProId,
                'pro_type' => $proType,
                'pro_date' => $validated['pro_date'], // Ø§Ù„ØªØ§Ø±ÙŠØ® Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠ
                'pro_num' => $validated['check_number'],
                'pro_serial' => $validated['reference_number'] ?? null,
                'acc1' => $portfolioAccount, // Ø§Ù„Ø­Ø§ÙØ¸Ø© Ø§Ù„Ù…Ø®ØªØ§Ø±Ø©
                'acc2' => $validated['acc1_id'], // Ø§Ù„Ø­Ø³Ø§Ø¨ Ø§Ù„Ù…Ø³ØªÙ„Ù… Ù…Ù†Ù‡/Ø§Ù„Ù…Ø¯ÙÙˆØ¹ Ù„Ù‡
                'acc1_before' => 0,
                'acc1_after' => 0,
                'acc2_before' => 0,
                'acc2_after' => 0,
                'pro_value' => $validated['amount'],
                'fat_net' => $validated['amount'],
                'details' => "Ø´ÙŠÙƒ Ø±Ù‚Ù… {$validated['check_number']} - {$validated['bank_name']} - Ø§Ø³ØªØ­Ù‚Ø§Ù‚: {$validated['due_date']}",
                'info' => $validated['payee_name'] ?? $validated['payer_name'] ?? $validated['account_holder_name'],
                'info2' => $validated['notes'],
                'info3' => json_encode([
                    'bank_name' => $validated['bank_name'],
                    'account_number' => $validated['account_number'],
                    'account_holder' => $validated['account_holder_name'],
                    'due_date' => $validated['due_date'],
                ]),
                'is_finance' => 1,
                'is_journal' => 1,
                'journal_type' => 2,
                'isdeleted' => 0,
                'tenant' => 0,
                'branch' => 1,
                'user' => Auth::id(),
                'branch_id' => $validated['branch_id'],
            ]);

            // Ø¥Ù†Ø´Ø§Ø¡ Ø§Ù„Ù‚ÙŠØ¯ Ø§Ù„Ù…Ø­Ø§Ø³Ø¨ÙŠ
            $this->createJournalEntry($oper, $validated, $portfolioAccount, $proType);

            // Ø¥Ù†Ø´Ø§Ø¡ Ø§Ù„Ø´ÙŠÙƒ
            $validated['created_by'] = Auth::id();
            $validated['oper_id'] = $oper->id; // Ø±Ø¨Ø· Ø§Ù„Ø´ÙŠÙƒ Ø¨Ø§Ù„Ø¹Ù…Ù„ÙŠØ©
            $check = Check::create($validated);

            DB::commit();

            return redirect()->route('checks.index')->with('success', 'ØªÙ… Ø¥Ø¶Ø§ÙØ© Ø§Ù„Ø´ÙŠÙƒ ÙˆØ¥Ù†Ø´Ø§Ø¡ Ø§Ù„Ù‚ÙŠØ¯ Ø§Ù„Ù…Ø­Ø§Ø³Ø¨ÙŠ Ø¨Ù†Ø¬Ø§Ø­');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Ø­Ø¯Ø« Ø®Ø·Ø£: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Create journal entry for check
     */
    private function createJournalEntry($oper, $data, $portfolioAccount, $proType)
    {
        // Ø¥Ù†Ø´Ø§Ø¡ journal_head
        $lastJournalId = JournalHead::max('journal_id') ?? 0;
        $newJournalId = $lastJournalId + 1;

        $journalHead = JournalHead::create([
            'journal_id' => $newJournalId,
            'total' => $data['amount'],
            'date' => $data['pro_date'], // Ø§Ù„ØªØ§Ø±ÙŠØ® Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠ Ù„Ù„Ø¹Ù…Ù„ÙŠØ©
            'op_id' => $oper->id,
            'pro_type' => $proType,
            'details' => "Ø´ÙŠÙƒ Ø±Ù‚Ù… {$data['check_number']} - {$data['bank_name']} - Ø§Ø³ØªØ­Ù‚Ø§Ù‚: {$data['due_date']}",
            'user' => Auth::id(),
            'branch_id' => $data['branch_id'],
        ]);

        $checkInfo = "Ø´ÙŠÙƒ {$data['check_number']} - {$data['bank_name']} - Ø§Ø³ØªØ­Ù‚Ø§Ù‚ {$data['due_date']}";

        // Ø§Ù„Ù‚ÙŠØ¯ Ø§Ù„Ù…Ø­Ø§Ø³Ø¨ÙŠ
        if ($data['type'] === 'incoming') {
            // ÙˆØ±Ù‚Ø© Ù‚Ø¨Ø¶: Ù…Ù† Ø­/ Ø­Ø§ÙØ¸Ø© Ø£ÙˆØ±Ø§Ù‚ Ø§Ù„Ù‚Ø¨Ø¶ (Ù…Ø¯ÙŠÙ†)
            //              Ø¥Ù„Ù‰ Ø­/ Ø§Ù„Ø¹Ù…ÙŠÙ„ (Ø¯Ø§Ø¦Ù†)
            JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $portfolioAccount, // Ø­Ø§ÙØ¸Ø© Ø£ÙˆØ±Ø§Ù‚ Ø§Ù„Ù‚Ø¨Ø¶
                'debit' => $data['amount'],
                'credit' => 0,
                'type' => 0, // Ù…Ø¯ÙŠÙ†
                'info' => $checkInfo,
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'tenant' => 0,
                'branch' => 1,
                'branch_id' => $data['branch_id'],
            ]);

            if (isset($data['acc1_id']) && $data['acc1_id']) {
                JournalDetail::create([
                    'journal_id' => $newJournalId,
                    'account_id' => $data['acc1_id'], // Ø§Ù„Ø¹Ù…ÙŠÙ„
                    'debit' => 0,
                    'credit' => $data['amount'],
                    'type' => 1, // Ø¯Ø§Ø¦Ù†
                    'info' => $checkInfo,
                    'op_id' => $oper->id,
                    'isdeleted' => 0,
                    'tenant' => 0,
                    'branch' => 1,
                    'branch_id' => $data['branch_id'],
                ]);
            }
        } else {
            // ÙˆØ±Ù‚Ø© Ø¯ÙØ¹: Ù…Ù† Ø­/ Ø§Ù„Ù…ÙˆØ±Ø¯ (Ù…Ø¯ÙŠÙ†)
            //            Ø¥Ù„Ù‰ Ø­/ Ø­Ø§ÙØ¸Ø© Ø£ÙˆØ±Ø§Ù‚ Ø§Ù„Ø¯ÙØ¹ (Ø¯Ø§Ø¦Ù†)
            if (isset($data['acc1_id']) && $data['acc1_id']) {
                JournalDetail::create([
                    'journal_id' => $newJournalId,
                    'account_id' => $data['acc1_id'], // Ø§Ù„Ù…ÙˆØ±Ø¯
                    'debit' => $data['amount'],
                    'credit' => 0,
                    'type' => 0, // Ù…Ø¯ÙŠÙ†
                    'info' => $checkInfo,
                    'op_id' => $oper->id,
                    'isdeleted' => 0,
                    'tenant' => 0,
                    'branch' => 1,
                    'branch_id' => $data['branch_id'],
                ]);
            }

            JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $portfolioAccount, // Ø­Ø§ÙØ¸Ø© Ø£ÙˆØ±Ø§Ù‚ Ø§Ù„Ø¯ÙØ¹
                'debit' => 0,
                'credit' => $data['amount'],
                'type' => 1, // Ø¯Ø§Ø¦Ù†
                'info' => $checkInfo,
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'tenant' => 0,
                'branch' => 1,
                'branch_id' => $data['branch_id'],
            ]);
        }
    }

    /**
     * Update the specified check
     */
    public function update(Request $request, Check $check)
    {
        $validated = $request->validate([
            'check_number' => 'required|string|max:50|unique:checks,check_number,' . $check->id,
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder_name' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'status' => 'required|in:pending,cleared,bounced,cancelled',
            'type' => 'required|in:incoming,outgoing',
            'payee_name' => 'nullable|string|max:100',
            'payer_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'reference_number' => 'nullable|string|max:50',
        ]);

        $check->update($validated);

        return redirect()->route('checks.index')->with('success', 'ØªÙ… ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø´ÙŠÙƒ Ø¨Ù†Ø¬Ø§Ø­');
    }

    /**
     * Delete the specified check
     */
    public function destroy(Check $check)
    {
        // Delete attached files
        if (!empty($check->attachments)) {
            foreach ($check->attachments as $attachment) {
                if (isset($attachment['path'])) {
                    Storage::disk('public')->delete($attachment['path']);
                }
            }
        }

        $check->delete();

        return response()->json(['success' => true, 'message' => 'ØªÙ… Ø­Ø°Ù Ø§Ù„Ø´ÙŠÙƒ Ø¨Ù†Ø¬Ø§Ø­']);
    }

    /**
     * Clear a check (ØªØ­ØµÙŠÙ„ Ø§Ù„Ø´ÙŠÙƒ - ØªØ­ÙˆÙŠÙ„ Ù„Ù„Ø¨Ù†Ùƒ)
     */
    public function clear(Request $request, Check $check)
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|integer|exists:acc_head,id', // Ø­Ø³Ø§Ø¨ Ø§Ù„Ø¨Ù†Ùƒ
            'collection_date' => 'required|date',
            'branch_id' => 'required|exists:branches,id',
        ]);

        try {
            DB::beginTransaction();

            // Ù†ÙˆØ¹ Ø§Ù„Ø¹Ù…Ù„ÙŠØ©: ØªØ­ØµÙŠÙ„ Ø´ÙŠÙƒ (67)
            $proType = 67;
            $lastProId = OperHead::where('pro_type', $proType)->max('pro_id') ?? 0;
            $newProId = $lastProId + 1;

            // Ø­Ø§ÙØ¸Ø© Ø£ÙˆØ±Ø§Ù‚ Ø§Ù„Ù‚Ø¨Ø¶ Ø£Ùˆ Ø§Ù„Ø¯ÙØ¹
            $portfolioAccount = $check->type === 'incoming' ? 63 : 66;

            // Ø¥Ù†Ø´Ø§Ø¡ Ø¹Ù…Ù„ÙŠØ© Ø§Ù„ØªØ­ØµÙŠÙ„ ÙÙŠ operhead
            $oper = OperHead::create([
                'pro_id' => $newProId,
                'pro_type' => $proType,
                'pro_date' => $validated['collection_date'],
                'pro_num' => $check->check_number,
                'acc1' => $validated['bank_account_id'], // Ø§Ù„Ø¨Ù†Ùƒ (Ù…Ø¯ÙŠÙ†)
                'acc2' => $portfolioAccount, // Ø­Ø§ÙØ¸Ø© Ø§Ù„Ø£ÙˆØ±Ø§Ù‚ (Ø¯Ø§Ø¦Ù†)
                'acc1_before' => 0,
                'acc1_after' => 0,
                'acc2_before' => 0,
                'acc2_after' => 0,
                'pro_value' => $check->amount,
                'fat_net' => $check->amount,
                'details' => "ØªØ­ØµÙŠÙ„ Ø´ÙŠÙƒ Ø±Ù‚Ù… {$check->check_number} Ù…Ù† {$check->bank_name}",
                'info' => $check->account_holder_name,
                'info2' => "ØªØ­ÙˆÙŠÙ„ Ù„Ù„Ø¨Ù†Ùƒ Ø¨ØªØ§Ø±ÙŠØ® {$validated['collection_date']}",
                'is_finance' => 1,
                'is_journal' => 1,
                'journal_type' => 2,
                'isdeleted' => 0,
                'tenant' => 0,
                'branch' => 1,
                'user' => Auth::id(),
                'branch_id' => $validated['branch_id'],
            ]);

            // Ø¥Ù†Ø´Ø§Ø¡ Ø§Ù„Ù‚ÙŠØ¯ Ø§Ù„Ù…Ø­Ø§Ø³Ø¨ÙŠ Ù„Ù„ØªØ­ØµÙŠÙ„
            $lastJournalId = JournalHead::max('journal_id') ?? 0;
            $newJournalId = $lastJournalId + 1;

            $journalHead = JournalHead::create([
                'journal_id' => $newJournalId,
                'total' => $check->amount,
                'date' => $validated['collection_date'],
                'op_id' => $oper->id,
                'pro_type' => $proType,
                'details' => "ØªØ­ØµÙŠÙ„ Ø´ÙŠÙƒ Ø±Ù‚Ù… {$check->check_number}",
                'user' => Auth::id(),
                'branch_id' => $validated['branch_id'],
            ]);

            $checkInfo = "ØªØ­ØµÙŠÙ„ Ø´ÙŠÙƒ {$check->check_number} - {$check->bank_name}";

            // Ù…Ù† Ø­/ Ø§Ù„Ø¨Ù†Ùƒ (Ù…Ø¯ÙŠÙ†)
            JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $validated['bank_account_id'],
                'debit' => $check->amount,
                'credit' => 0,
                'type' => 0,
                'info' => $checkInfo,
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'tenant' => 0,
                'branch' => 1,
                'branch_id' => $validated['branch_id'],
            ]);

            // Ø¥Ù„Ù‰ Ø­/ Ø­Ø§ÙØ¸Ø© Ø§Ù„Ø£ÙˆØ±Ø§Ù‚ Ø§Ù„Ù…Ø§Ù„ÙŠØ© (Ø¯Ø§Ø¦Ù†)
            JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $portfolioAccount,
                'debit' => 0,
                'credit' => $check->amount,
                'type' => 1,
                'info' => $checkInfo,
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'tenant' => 0,
                'branch' => 1,
                'branch_id' => $validated['branch_id'],
            ]);

            // ØªØ­Ø¯ÙŠØ« Ø­Ø§Ù„Ø© Ø§Ù„Ø´ÙŠÙƒ
            $check->markAsCleared($validated['collection_date']);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'ØªÙ… ØªØµÙÙŠØ© Ø§Ù„Ø´ÙŠÙƒ Ø¨Ù†Ø¬Ø§Ø­']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Ø­Ø¯Ø« Ø®Ø·Ø£: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Batch collect checks
     */
    public function batchCollect(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'bank_account_id' => 'required|integer|exists:acc_head,id',
            'collection_date' => 'required|date',
            'branch_id' => 'required|exists:branches,id',
        ]);

        try {
            DB::beginTransaction();

            $checks = Check::whereIn('id', $validated['ids'])
                ->where('status', Check::STATUS_PENDING)
                ->get();

            foreach ($checks as $check) {
                // ØªØ­ØµÙŠÙ„ ÙƒÙ„ Ø´ÙŠÙƒ Ø¹Ù„Ù‰ Ø­Ø¯Ø©
                $this->collectSingleCheck($check, $validated['bank_account_id'], $validated['collection_date'], $validated['branch_id']);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => "ØªÙ… ØªØ­ØµÙŠÙ„ {$checks->count()} Ø´ÙŠÙƒ Ø¨Ù†Ø¬Ø§Ø­"]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Ø­Ø¯Ø« Ø®Ø·Ø£: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Collect single check (helper method)
     */
    private function collectSingleCheck($check, $bankAccountId, $collectionDate, $branchId)
    {
        // Ù†ÙˆØ¹ Ø§Ù„Ø¹Ù…Ù„ÙŠØ©: ØªØ­ØµÙŠÙ„ Ø´ÙŠÙƒ (67)
        $proType = 67;
        $lastProId = OperHead::where('pro_type', $proType)->max('pro_id') ?? 0;
        $newProId = $lastProId + 1;

        $portfolioAccount = $check->type === 'incoming' ? 63 : 66;

        // Ø¥Ù†Ø´Ø§Ø¡ Ø¹Ù…Ù„ÙŠØ© Ø§Ù„ØªØ­ØµÙŠÙ„
        $oper = OperHead::create([
            'pro_id' => $newProId,
            'pro_type' => $proType,
            'pro_date' => $collectionDate,
            'pro_num' => $check->check_number,
            'acc1' => $bankAccountId,
            'acc2' => $portfolioAccount,
            'acc1_before' => 0,
            'acc1_after' => 0,
            'acc2_before' => 0,
            'acc2_after' => 0,
            'pro_value' => $check->amount,
            'fat_net' => $check->amount,
            'details' => "ØªØ­ØµÙŠÙ„ Ø´ÙŠÙƒ Ø±Ù‚Ù… {$check->check_number} Ù…Ù† {$check->bank_name}",
            'info' => $check->account_holder_name,
            'is_finance' => 1,
            'is_journal' => 1,
            'journal_type' => 2,
            'isdeleted' => 0,
            'tenant' => 0,
            'branch' => 1,
            'user' => Auth::id(),
            'branch_id' => $branchId,
        ]);

        // Ø¥Ù†Ø´Ø§Ø¡ Ø§Ù„Ù‚ÙŠØ¯
        $lastJournalId = JournalHead::max('journal_id') ?? 0;
        $newJournalId = $lastJournalId + 1;

        JournalHead::create([
            'journal_id' => $newJournalId,
            'total' => $check->amount,
            'date' => $collectionDate,
            'op_id' => $oper->id,
            'pro_type' => $proType,
            'details' => "ØªØ­ØµÙŠÙ„ Ø´ÙŠÙƒ Ø±Ù‚Ù… {$check->check_number}",
            'user' => Auth::id(),
            'branch_id' => $branchId,
        ]);

        $checkInfo = "ØªØ­ØµÙŠÙ„ Ø´ÙŠÙƒ {$check->check_number}";

        // Ù…Ù† Ø­/ Ø§Ù„Ø¨Ù†Ùƒ
        JournalDetail::create([
            'journal_id' => $newJournalId,
            'account_id' => $bankAccountId,
            'debit' => $check->amount,
            'credit' => 0,
            'type' => 0,
            'info' => $checkInfo,
            'op_id' => $oper->id,
            'isdeleted' => 0,
            'tenant' => 0,
            'branch' => 1,
            'branch_id' => $branchId,
        ]);

        // Ø¥Ù„Ù‰ Ø­/ Ø­Ø§ÙØ¸Ø© Ø§Ù„Ø£ÙˆØ±Ø§Ù‚
        JournalDetail::create([
            'journal_id' => $newJournalId,
            'account_id' => $portfolioAccount,
            'debit' => 0,
            'credit' => $check->amount,
            'type' => 1,
            'info' => $checkInfo,
            'op_id' => $oper->id,
            'isdeleted' => 0,
            'tenant' => 0,
            'branch' => 1,
            'branch_id' => $branchId,
        ]);

        // ØªØ­Ø¯ÙŠØ« Ø­Ø§Ù„Ø© Ø§Ù„Ø´ÙŠÙƒ
        $check->markAsCleared($collectionDate);
    }

    /**
     * Batch cancel with reversal entry
     */
    public function batchCancelReversal(Request $request)
    {
        $ids = $request->input('ids', []);
        Check::whereIn('id', $ids)->update(['status' => Check::STATUS_CANCELLED]);
        
        // TODO: Add reversal journal entry logic here

        return response()->json(['success' => true, 'message' => 'ØªÙ… Ø¥Ù„ØºØ§Ø¡ Ø§Ù„Ø´ÙŠÙƒØ§Øª Ø¨Ù‚ÙŠØ¯ Ø¹ÙƒØ³ÙŠ']);
    }

    /**
     * Get checks statistics API endpoint
     */
    public function statistics(Request $request)
    {
        $dateRange = $this->getDateRange($request->get('period', 'month'));
        
        $stats = [
            'total' => Check::whereBetween('created_at', $dateRange)->count(),
            'pending' => Check::whereBetween('created_at', $dateRange)
                ->where('status', Check::STATUS_PENDING)->count(),
            'cleared' => Check::whereBetween('created_at', $dateRange)
                ->where('status', Check::STATUS_CLEARED)->count(),
            'bounced' => Check::whereBetween('created_at', $dateRange)
                ->where('status', Check::STATUS_BOUNCED)->count(),
            'total_amount' => Check::whereBetween('created_at', $dateRange)->sum('amount'),
            'pending_amount' => Check::whereBetween('created_at', $dateRange)
                ->where('status', Check::STATUS_PENDING)->sum('amount'),
            'cleared_amount' => Check::whereBetween('created_at', $dateRange)
                ->where('status', Check::STATUS_CLEARED)->sum('amount'),
        ];

        return response()->json($stats);
    }

    private function getDateRange($period)
    {
        return match($period) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }
}
