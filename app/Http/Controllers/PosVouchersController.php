<?php

namespace App\Http\Controllers;

use App\Models\{AccHead, OperHead, CostCenter, Voucher, JournalDetail, JournalHead, Project, Item, Note, NoteDetails};
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class PosVouchersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $posVouchers = OperHead::where('pro_type', 10) // Assuming 10 is POS sales type
            ->where('isdeleted', 0)
            ->orderByDesc('pro_date')
            ->get();
        return view('pos_vouchers.index', compact('posVouchers'));
    }

    public function create()
    {
        // Get next operation ID
        $lastProId = OperHead::where('pro_type', 10)->max('pro_id') ?? 0;
        $newProId = $lastProId + 1;

        // Get cash accounts
        $cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '121%')
            ->select('id', 'aname')
            ->get();

        // Get employee accounts
        $employeeAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '213%')
            ->select('id', 'aname')
            ->get();

        // Get customer accounts
        $customerAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '120%')
            ->select('id', 'aname')
            ->get();

        // Get notes (categories)
        $notes = Note::with('noteDetails')
            ->where('id', )
            ->get();

        return view('pos_vouchers.create', compact(
            'newProId',
            'cashAccounts',
            'employeeAccounts',
            'customerAccounts',
            'notes'
        ));
    }

    public function store(Request $request)
    {
        // Add debugging
        \Log::info('Store method called', [
            'request' => $request->all(),
            'session_data' => session('pos_voucher_data'),
        ]);

        // Get data from session (set by Livewire component)
        $posData = session('pos_voucher_data');

        if (!$posData) {
            \Log::error('No session data found');
            return redirect()->back()->withErrors(['error' => 'لم يتم العثور على بيانات العملية']);
        }

        $validated = [
            'pro_id' => $posData['pro_id'],
            'pro_date' => $posData['pro_date'],
            'acc1' => $posData['acc1'],
            'acc2' => $posData['acc2'],
            'emp_id' => $posData['emp_id'],
            'pro_value' => $posData['pro_value'],
            'items' => $posData['items'],
            'details' => $posData['details'] ?? 'مبيعات نقاط البيع',
            'pro_serial' => $posData['pro_serial'] ?? null,
            'pro_num' => $posData['pro_num'] ?? null,
        ];

        try {
            DB::beginTransaction();

            \Log::info('Creating operation head', $validated);

            // Create operation head
            $operHead = OperHead::create([
                'pro_id' => $validated['pro_id'],
                'pro_date' => $validated['pro_date'],
                'pro_type' => 10, // POS sales
                'acc1' => $validated['acc1'], // Customer
                'acc2' => $validated['acc2'], // Cash
                'pro_value' => $validated['pro_value'],
                'details' => $request->details ?? 'مبيعات نقاط البيع',
                'pro_serial' => $request->pro_serial ?? null,
                'pro_num' => $request->pro_num ?? null,
                'isdeleted' => 0,
                'tenant' => 0,
                'branch' => 1,
                'is_finance' => 1,
                'is_journal' => 1,
                'journal_type' => 2,
                'emp_id' => $validated['emp_id'],
                'acc1_before' => 0,
                'acc1_after' => 0,
                'acc2_before' => 0,
                'acc2_after' => 0,
                'user' => Auth::user()->id,
            ]);

            \Log::info('Operation head created', ['operHead_id' => $operHead->id]);

            // Create operation items
            \Log::info('Creating operation items', ['items_count' => count($validated['items'])]);

            foreach ($validated['items'] as $index => $item) {
                try {
                    DB::table('operation_items')->insert([
                        'pro_id' => $operHead->id,
                        'item_id' => $item['item_id'],
                        'unit_id' => $item['unit_id'],
                        'qty_in' => $item['quantity'],
                        'qty_out' => 0,
                        'item_price' => $item['price'],
                        'cost_price' => 0, // Will be calculated
                        'current_stock_value' => 0,
                        'item_discount' => 0,
                        'additional' => 0,
                        'detail_value' => $item['total'],
                        'profit' => 0,
                        'notes' => null,
                        'is_stock' => 1,
                        'isdeleted' => 0,
                        'tenant' => 0,
                        'branch' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    \Log::info("Operation item {$index} created successfully", $item);
                } catch (\Exception $e) {
                    \Log::error("Error creating operation item {$index}: " . $e->getMessage(), $item);
                    throw $e;
                }
            }

            // Create journal entry
            \Log::info('Creating journal entry');

            $journalId = JournalHead::max('journal_id') + 1;
            $journalHead = JournalHead::create([
                'journal_id' => $journalId,
                'total' => $validated['pro_value'],
                'op_id' => $operHead->id,
                'pro_type' => 10,
                'date' => $validated['pro_date'],
                'details' => 'قيد مبيعات نقاط البيع',
                'user' => Auth::user()->id,
            ]);

            \Log::info('Journal head created', ['journal_id' => $journalId]);

            // Create journal details
            \Log::info('Creating journal details');

            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $validated['acc1'], // Customer (Debit)
                'debit' => $validated['pro_value'],
                'credit' => 0,
                'type' => 0,
                'info' => 'مدين - عميل',
                'op_id' => $operHead->id,
                'isdeleted' => 0,
            ]);

            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $validated['acc2'], // Cash (Credit)
                'debit' => 0,
                'credit' => $validated['pro_value'],
                'type' => 1,
                'info' => 'دائن - صندوق',
                'op_id' => $operHead->id,
                'isdeleted' => 0,
            ]);

            \Log::info('Journal details created successfully');

            DB::commit();
            \Log::info('Transaction committed successfully');

            return redirect()->route('pos-vouchers.index')->with('success', 'تم حفظ عملية نقاط البيع بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in store method: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'posData' => $posData,
                'validated' => $validated ?? null,
            ]);

            $errorMessage = 'حدث خطأ أثناء حفظ العملية: ' . $e->getMessage();
            return redirect()->back()->withErrors(['error' => $errorMessage])->withInput();
        }
    }

    public function show($id)
    {
        $posVoucher = OperHead::with(['operationItems.item', 'operationItems.unit', 'account1', 'account2', 'emp1'])
            ->where('pro_type', 10)
            ->findOrFail($id);

        return view('pos_vouchers.show', compact('posVoucher'));
    }

    public function edit($id)
    {
        $posVoucher = OperHead::with(['operationItems.item', 'operationItems.unit'])
            ->where('pro_type', 10)
            ->findOrFail($id);

        // Get accounts
        $cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '121%')
            ->select('id', 'aname')
            ->get();

        $employeeAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '213%')
            ->select('id', 'aname')
            ->get();

        $customerAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '120%')
            ->select('id', 'aname')
            ->get();

        // Get notes (categories)
        $notes = Note::with('noteDetails')->get();

        return view('pos_vouchers.edit', compact(
            'posVoucher',
            'cashAccounts',
            'employeeAccounts',
            'customerAccounts',
            'notes'
        ));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'pro_date' => 'required|date',
            'acc1' => 'required|integer',
            'acc2' => 'required|integer',
            'emp_id' => 'required|integer',
            'pro_value' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $operHead = OperHead::where('pro_type', 10)->findOrFail($id);

            // Update operation head
            $operHead->update([
                'pro_date' => $validated['pro_date'],
                'acc1' => $validated['acc1'],
                'acc2' => $validated['acc2'],
                'pro_value' => $validated['pro_value'],
                'details' => $request->details ?? 'مبيعات نقاط البيع',
                'pro_serial' => $request->pro_serial ?? null,
                'pro_num' => $request->pro_num ?? null,
                'emp_id' => $validated['emp_id'],
                'user' => Auth::user()->id,
            ]);

            // Delete old operation items
            DB::table('operation_items')->where('pro_id', $operHead->id)->delete();

            // Create new operation items
            foreach ($validated['items'] as $item) {
                DB::table('operation_items')->insert([
                    'pro_id' => $operHead->id,
                    'item_id' => $item['item_id'],
                    'unit_id' => $item['unit_id'],
                    'qty_in' => $item['quantity'],
                    'qty_out' => 0,
                    'item_price' => $item['price'],
                    'cost_price' => 0,
                    'current_stock_value' => 0,
                    'item_discount' => 0,
                    'additional' => 0,
                    'detail_value' => $item['total'],
                    'profit' => 0,
                    'notes' => null,
                    'is_stock' => 1,
                    'isdeleted' => 0,
                    'tenant' => 0,
                    'branch' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Update journal
            $journalHead = JournalHead::where('op_id', $operHead->id)->first();
            if ($journalHead) {
                $journalHead->update([
                    'total' => $validated['pro_value'],
                    'date' => $validated['pro_date'],
                    'details' => 'قيد مبيعات نقاط البيع',
                    'user' => Auth::user()->id,
                ]);

                // Update journal details
                JournalDetail::where('journal_id', $journalHead->journal_id)->delete();

                JournalDetail::create([
                    'journal_id' => $journalHead->journal_id,
                    'account_id' => $validated['acc1'],
                    'debit' => $validated['pro_value'],
                    'credit' => 0,
                    'type' => 0,
                    'info' => 'مدين - عميل',
                    'op_id' => $operHead->id,
                    'isdeleted' => 0,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalHead->journal_id,
                    'account_id' => $validated['acc2'],
                    'debit' => 0,
                    'credit' => $validated['pro_value'],
                    'type' => 1,
                    'info' => 'دائن - صندوق',
                    'op_id' => $operHead->id,
                    'isdeleted' => 0,
                ]);
            }

            DB::commit();
            return redirect()->route('pos-vouchers.index')->with('success', 'تم تحديث عملية نقاط البيع بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $operHead = OperHead::where('pro_type', 10)->findOrFail($id);

            // Delete operation items
            DB::table('operation_items')->where('pro_id', $operHead->id)->delete();

            // Delete journal
            $journalHead = JournalHead::where('op_id', $operHead->id)->first();
            if ($journalHead) {
                JournalDetail::where('journal_id', $journalHead->journal_id)->delete();
                $journalHead->delete();
            }

            // Delete operation head
            $operHead->delete();

            DB::commit();
            return redirect()->route('pos-vouchers.index')->with('success', 'تم حذف عملية نقاط البيع بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()]);
        }
    }

    // API method to get items by note detail
    public function getItemsByNoteDetail(Request $request)
    {
        $noteDetailId = $request->note_detail_id;

        $items = Item::whereHas('notes', function ($query) use ($noteDetailId) {
            $query->where('note_details.id', $noteDetailId);
        })->with(['units', 'prices'])->get();

        return response()->json($items);
    }
}
