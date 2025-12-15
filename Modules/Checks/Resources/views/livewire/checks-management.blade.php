<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Modules\Checks\Models\Check;
use Modules\Checks\Services\CheckService;
use Modules\Checks\Services\CheckAccountingService;
use Modules\Checks\Services\CheckPortfolioService;
use Modules\Accounts\Models\AccHead;
use Modules\Branches\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithPagination, WithFileUploads;

    private function checkService(): CheckService
    {
        return app(CheckService::class);
    }

    private function accountingService(): CheckAccountingService
    {
        return app(CheckAccountingService::class);
    }

    private function portfolioService(): CheckPortfolioService
    {
        return app(CheckPortfolioService::class);
    }

    // Form fields
    public $check_number = '';
    public $bank_name = '';
    public $account_number = '';
    public $account_holder_name = '';
    public $amount = '';
    public $issue_date = '';
    public $due_date = '';
    public $payment_date = '';
    public $status = 'pending';
    public $type = 'outgoing';
    public $payee_name = '';
    public $payer_name = '';
    public $notes = '';
    public $reference_number = '';
    public $attachments = [];
    public $tempAttachments = [];
    public $acc1_id = '';
    public $portfolio_id = '';
    public $branch_id = '';

    // Filters
    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';
    public $bankFilter = '';
    public $startDate = '';
    public $endDate = '';

    // Selection
    public $selectedChecks = [];
    public $selectAll = false;

    // Modals
    public $showModal = false;
    public $editingCheckId = null;
    public $showConfirmation = false;
    public $confirmationAction = '';
    public $confirmationMessage = '';
    public $confirmationCheckId = null;
    public $showViewModal = false;
    public $viewingCheck = null;

    public function openModal()
    {
        $this->showModal = true;
        $this->resetForm();
        $this->editingCheckId = null;
    }

    public function editCheck($checkId)
    {
        $check = Check::findOrFail($checkId);
        
        $this->editingCheckId = $checkId;
        $this->check_number = $check->check_number;
        $this->bank_name = $check->bank_name;
        $this->account_number = $check->account_number;
        $this->account_holder_name = $check->account_holder_name;
        $this->amount = $check->amount;
        $this->issue_date = $check->issue_date->format('Y-m-d');
        $this->due_date = $check->due_date->format('Y-m-d');
        $this->payment_date = $check->payment_date?->format('Y-m-d') ?? '';
        $this->status = $check->status;
        $this->type = $check->type;
        $this->payee_name = $check->payee_name ?? '';
        $this->payer_name = $check->payer_name ?? '';
        $this->notes = $check->notes ?? '';
        $this->reference_number = $check->reference_number ?? '';
        $this->attachments = $check->attachments ?? [];
        $this->acc1_id = $check->customer_id ?? $check->supplier_id ?? '';
        $this->portfolio_id = '';
        $this->branch_id = $check->operation?->branch_id ?? '';

        $this->showModal = true;
    }

    public function saveCheck()
    {
        $validated = $this->validate([
            'check_number' => ['required', 'string', 'max:50', $this->editingCheckId ? 'unique:checks,check_number,' . $this->editingCheckId : 'unique:checks'],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder_name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', 'in:pending,cleared,bounced,cancelled'],
            'type' => ['required', 'in:incoming,outgoing'],
            'payee_name' => ['nullable', 'string', 'max:100'],
            'payer_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'reference_number' => ['nullable', 'string', 'max:50'],
            'acc1_id' => ['required', 'integer', 'exists:acc_head,id'],
            'portfolio_id' => ['required', 'integer', 'exists:acc_head,id'],
            'branch_id' => ['required', 'exists:branches,id'],
        ], [
            'check_number.required' => 'رقم الشيك مطلوب',
            'check_number.unique' => 'رقم الشيك مستخدم بالفعل',
            'bank_name.required' => 'اسم البنك مطلوب',
            'amount.required' => 'المبلغ مطلوب',
            'due_date.after_or_equal' => 'تاريخ الاستحقاق يجب أن يكون بعد أو يساوي تاريخ الإصدار',
        ]);

        try {
            // Handle file uploads
            if (!empty($this->tempAttachments)) {
                $uploadedFiles = [];
                foreach ($this->tempAttachments as $file) {
                    $filename = $file->store('checks/attachments', 'public');
                    $uploadedFiles[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $filename,
                        'size' => $file->getSize(),
                    ];
                }
                $validated['attachments'] = array_merge($this->attachments, $uploadedFiles);
            } else {
                $validated['attachments'] = $this->attachments;
            }

            $validated['pro_date'] = $validated['issue_date'];
            $validated['customer_id'] = $this->type === 'incoming' ? $this->acc1_id : null;
            $validated['supplier_id'] = $this->type === 'outgoing' ? $this->acc1_id : null;

            if ($this->editingCheckId) {
                $check = Check::findOrFail($this->editingCheckId);
                $this->checkService()->updateCheck($check, $validated);
                $this->dispatch('notify', type: 'success', message: 'تم تحديث الشيك بنجاح');
            } else {
                $this->checkService()->createCheck($validated);
                $this->dispatch('notify', type: 'success', message: 'تم إضافة الشيك بنجاح');
            }

            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function confirmAction($action, $checkId, $message)
    {
        $this->confirmationAction = $action;
        $this->confirmationCheckId = $checkId;
        $this->confirmationMessage = $message;
        $this->showConfirmation = true;
    }

    public function executeConfirmedAction()
    {
        try {
            $check = Check::findOrFail($this->confirmationCheckId);
            $branchId = $check->operation?->branch_id ?? auth()->user()->branch_id ?? 1;

            match ($this->confirmationAction) {
                'clear' => $this->accountingService()->clearCheck($check, request()->input('bank_account_id', 1), now()->toDateString(), $branchId),
                'bounce' => $this->accountingService()->bounceCheck($check, $branchId),
                'cancel' => $this->accountingService()->cancelCheckWithReversal($check, $branchId),
                'approve' => $check->approve(Auth::id()),
                'delete' => $this->checkService()->deleteCheck($check),
                default => throw new \Exception('عملية غير معروفة'),
            };

            $this->dispatch('notify', type: 'success', message: 'تم تنفيذ العملية بنجاح');
            $this->showConfirmation = false;
            $this->resetPage();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function viewCheck($checkId)
    {
        $this->viewingCheck = Check::with(['creator', 'approver'])->findOrFail($checkId);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingCheck = null;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedChecks = Check::query()
                ->when($this->search, fn($q) => $q->where(function($query) {
                    $query->where('check_number', 'like', '%' . $this->search . '%')
                          ->orWhere('bank_name', 'like', '%' . $this->search . '%')
                          ->orWhere('account_holder_name', 'like', '%' . $this->search . '%');
                }))
                ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
                ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
                ->pluck('id')->toArray();
        } else {
            $this->selectedChecks = [];
        }
    }

    public function removeAttachment($index)
    {
        if (isset($this->attachments[$index])) {
            if (isset($this->attachments[$index]['path'])) {
                Storage::disk('public')->delete($this->attachments[$index]['path']);
            }
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments);
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->check_number = '';
        $this->bank_name = '';
        $this->account_number = '';
        $this->account_holder_name = '';
        $this->amount = '';
        $this->issue_date = '';
        $this->due_date = '';
        $this->payment_date = '';
        $this->status = 'pending';
        $this->type = 'outgoing';
        $this->payee_name = '';
        $this->payer_name = '';
        $this->notes = '';
        $this->reference_number = '';
        $this->attachments = [];
        $this->tempAttachments = [];
        $this->editingCheckId = null;
        $this->acc1_id = '';
        $this->portfolio_id = '';
        $this->branch_id = '';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Check::query()
            ->with(['creator', 'approver'])
            ->orderBy('created_at', 'desc');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('check_number', 'like', '%' . $this->search . '%')
                  ->orWhere('bank_name', 'like', '%' . $this->search . '%')
                  ->orWhere('account_holder_name', 'like', '%' . $this->search . '%')
                  ->orWhere('payee_name', 'like', '%' . $this->search . '%')
                  ->orWhere('payer_name', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->typeFilter)) {
            $query->where('type', $this->typeFilter);
        }

        if (!empty($this->bankFilter)) {
            $query->where('bank_name', 'like', '%' . $this->bankFilter . '%');
        }

        if (!empty($this->startDate) && !empty($this->endDate)) {
            $query->whereBetween('due_date', [$this->startDate, $this->endDate]);
        }

        $checks = $query->paginate(15);

        $accounts = $this->type === 'incoming'
            ? AccHead::where('is_basic', 0)->where('isdeleted', 0)->where('code', 'like', '1103%')->select('id', 'aname', 'code')->get()
            : AccHead::where('is_basic', 0)->where('isdeleted', 0)->where('code', 'like', '2101%')->select('id', 'aname', 'code')->get();

        $portfolios = $this->type === 'incoming'
            ? [$this->portfolioService()->getIncomingPortfolio()]
            : [$this->portfolioService()->getOutgoingPortfolio()];

        $branches = Branch::orderBy('name')->get();

        return [
            'checks' => $checks,
            'statuses' => Check::getStatuses(),
            'types' => Check::getTypes(),
            'accounts' => $accounts,
            'portfolios' => array_filter($portfolios),
            'branches' => $branches,
        ];
    }
};

?>

<div>
    <div class="flex justify-between items-center mb-4">
        <flux:heading>إدارة الشيكات</flux:heading>
        <div class="flex gap-2">
            <flux:button href="{{ route('checks.export.pdf', request()->query()) }}" variant="ghost" size="sm" target="_blank">
                <i class="fas fa-file-pdf me-2"></i> PDF
            </flux:button>
            <flux:button href="{{ route('checks.export.excel', request()->query()) }}" variant="ghost" size="sm" target="_blank">
                <i class="fas fa-file-excel me-2"></i> Excel
            </flux:button>
        </div>
    </div>

    <!-- Filters -->
    <flux:card class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <flux:field>
                <flux:label>البحث</flux:label>
                <flux:input wire:model.live.debounce.300ms="search" placeholder="رقم الشيك، البنك، أو اسم صاحب الحساب" />
            </flux:field>

            <flux:field>
                <flux:label>الحالة</flux:label>
                <flux:select wire:model.live="statusFilter">
                    <option value="">جميع الحالات</option>
                    @foreach($statuses as $key => $status)
                        <option value="{{ $key }}">{{ $status }}</option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>النوع</flux:label>
                <flux:select wire:model.live="typeFilter">
                    <option value="">جميع الأنواع</option>
                    @foreach($types as $key => $type)
                        <option value="{{ $key }}">{{ $type }}</option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>من تاريخ</flux:label>
                <flux:input type="date" wire:model.live="startDate" />
            </flux:field>

            <flux:field>
                <flux:label>إلى تاريخ</flux:label>
                <flux:input type="date" wire:model.live="endDate" />
            </flux:field>
        </div>
    </flux:card>

    <!-- Actions -->
    <div class="mb-4 flex justify-between items-center">
        <flux:button wire:click="openModal" variant="primary">
            إضافة شيك جديد
        </flux:button>

        @if(!empty($selectedChecks))
            <div class="flex gap-2">
                <flux:button wire:click="clearSelectedChecks" variant="success" size="sm">
                    تصفية المحدد
                </flux:button>
                <flux:button wire:click="$set('selectedChecks', [])" variant="ghost" size="sm">
                    إلغاء التحديد
                </flux:button>
            </div>
        @endif
    </div>

    <!-- Table -->
    <flux:card>
        <flux:table :headers="['رقم الشيك', 'البنك', 'المبلغ', 'تاريخ الاستحقاق', 'الحالة', 'النوع', 'صاحب الحساب', 'الإجراءات']">
            @foreach($checks as $check)
                <flux:row wire:key="check-{{ $check->id }}" class="{{ $check->isOverdue() ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                    <flux:cell>
                        <div>
                            <strong>{{ $check->check_number }}</strong>
                            @if($check->reference_number)
                                <br><small class="text-muted">مرجع: {{ $check->reference_number }}</small>
                            @endif
                        </div>
                    </flux:cell>
                    <flux:cell>
                        <div>
                            {{ $check->bank_name }}
                            <br><small class="text-muted">{{ $check->account_number }}</small>
                        </div>
                    </flux:cell>
                    <flux:cell>
                        <strong>{{ number_format($check->amount, 2) }} ر.س</strong>
                    </flux:cell>
                    <flux:cell>
                        <div>
                            {{ $check->due_date->format('Y-m-d') }}
                            @if($check->isOverdue())
                                <br><flux:badge color="danger" size="sm">متأخر</flux:badge>
                            @endif
                        </div>
                    </flux:cell>
                    <flux:cell>
                        <flux:badge color="{{ $check->status_color }}">
                            {{ $statuses[$check->status] }}
                        </flux:badge>
                    </flux:cell>
                    <flux:cell>
                        <flux:badge color="{{ $check->type === 'incoming' ? 'success' : 'info' }}">
                            {{ $types[$check->type] }}
                        </flux:badge>
                    </flux:cell>
                    <flux:cell>{{ $check->account_holder_name }}</flux:cell>
                    <flux:cell>
                        <div class="flex gap-2">
                            <flux:button wire:click="editCheck({{ $check->id }})" variant="ghost" size="sm">
                                تعديل
                            </flux:button>
                            
                            @if($check->status === 'pending')
                                <flux:button wire:click="confirmAction('clear', {{ $check->id }}, 'هل أنت متأكد من تصفية هذا الشيك؟')" variant="ghost" size="sm" color="success">
                                    تصفية
                                </flux:button>
                                <flux:button wire:click="confirmAction('bounce', {{ $check->id }}, 'هل أنت متأكد من تمييز هذا الشيك كمرتد؟')" variant="ghost" size="sm" color="warning">
                                    مرتد
                                </flux:button>
                            @endif
                            
                            <flux:button wire:click="confirmAction('delete', {{ $check->id }}, 'هل أنت متأكد من حذف هذا الشيك؟')" variant="ghost" size="sm" color="danger">
                                حذف
                            </flux:button>
                        </div>
                    </flux:cell>
                </flux:row>
            @endforeach

            <flux:empty-state>
                لا توجد شيكات مطابقة للبحث
            </flux:empty-state>
        </flux:table>

        <div class="mt-4">
            {{ $checks->links() }}
        </div>
    </flux:card>

    <!-- Add/Edit Modal -->
    <flux:modal name="check-form" wire:model="showModal">
        <flux:heading>{{ $editingCheckId ? 'تعديل الشيك' : 'إضافة شيك جديد' }}</flux:heading>

        <flux:errors />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:field>
                <flux:label>رقم الشيك <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="check_number" />
                <flux:error name="check_number" />
            </flux:field>

            <flux:field>
                <flux:label>اسم البنك <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="bank_name" />
                <flux:error name="bank_name" />
            </flux:field>

            <flux:field>
                <flux:label>رقم الحساب <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="account_number" />
                <flux:error name="account_number" />
            </flux:field>

            <flux:field>
                <flux:label>صاحب الحساب <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="account_holder_name" />
                <flux:error name="account_holder_name" />
            </flux:field>

            <flux:field>
                <flux:label>المبلغ <span class="text-red-500">*</span></flux:label>
                <flux:input type="number" step="0.01" wire:model="amount" />
                <flux:error name="amount" />
            </flux:field>

            <flux:field>
                <flux:label>تاريخ الإصدار <span class="text-red-500">*</span></flux:label>
                <flux:input type="date" wire:model="issue_date" />
                <flux:error name="issue_date" />
            </flux:field>

            <flux:field>
                <flux:label>تاريخ الاستحقاق <span class="text-red-500">*</span></flux:label>
                <flux:input type="date" wire:model="due_date" />
                <flux:error name="due_date" />
            </flux:field>

            <flux:field>
                <flux:label>الحالة</flux:label>
                <flux:select wire:model="status">
                    @foreach($statuses as $key => $status)
                        <option value="{{ $key }}">{{ $status }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="status" />
            </flux:field>

            <flux:field>
                <flux:label>النوع</flux:label>
                <flux:select wire:model="type">
                    @foreach($types as $key => $type)
                        <option value="{{ $key }}">{{ $type }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="type" />
            </flux:field>

            <flux:field>
                <flux:label>الحساب</flux:label>
                <flux:select wire:model="acc1_id">
                    <option value="">اختر الحساب</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->aname }} ({{ $account->code }})</option>
                    @endforeach
                </flux:select>
                <flux:error name="acc1_id" />
            </flux:field>

            <flux:field>
                <flux:label>حافظة الأوراق المالية</flux:label>
                <flux:select wire:model="portfolio_id">
                    <option value="">اختر الحافظة</option>
                    @foreach($portfolios as $portfolio)
                        <option value="{{ $portfolio->id }}">{{ $portfolio->aname }} ({{ $portfolio->code }})</option>
                    @endforeach
                </flux:select>
                <flux:error name="portfolio_id" />
            </flux:field>

            <flux:field>
                <flux:label>الفرع</flux:label>
                <flux:select wire:model="branch_id">
                    <option value="">اختر الفرع</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="branch_id" />
            </flux:field>

            <flux:field>
                <flux:label>اسم المستفيد</flux:label>
                <flux:input wire:model="payee_name" />
                <flux:error name="payee_name" />
            </flux:field>

            <flux:field>
                <flux:label>اسم الدافع</flux:label>
                <flux:input wire:model="payer_name" />
                <flux:error name="payer_name" />
            </flux:field>

            <flux:field>
                <flux:label>رقم المرجع</flux:label>
                <flux:input wire:model="reference_number" />
                <flux:error name="reference_number" />
            </flux:field>

            <flux:field>
                <flux:label>ملاحظات</flux:label>
                <flux:textarea wire:model="notes" rows="3" />
                <flux:error name="notes" />
            </flux:field>

            <flux:field>
                <flux:label>المرفقات</flux:label>
                <flux:input type="file" wire:model="tempAttachments" multiple />
                
                @if(!empty($attachments))
                    <div class="mt-2">
                        @foreach($attachments as $index => $attachment)
                            <div class="flex justify-between items-center p-2 bg-gray-100 dark:bg-gray-800 rounded">
                                <span>{{ $attachment['name'] ?? 'ملف مرفق' }}</span>
                                <flux:button wire:click="removeAttachment({{ $index }})" variant="ghost" size="sm" color="danger">
                                    حذف
                                </flux:button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:field>
        </div>

        <flux:actions>
            <flux:button wire:click="closeModal" variant="ghost">إلغاء</flux:button>
            <flux:button wire:click="saveCheck">
                <flux:loading wire:target="saveCheck" />
                حفظ
            </flux:button>
        </flux:actions>
    </flux:modal>

    <!-- Confirmation Modal -->
    <flux:modal name="confirmation" wire:model="showConfirmation">
        <flux:heading>تأكيد العملية</flux:heading>
        <p>{{ $confirmationMessage }}</p>
        <flux:actions>
            <flux:button wire:click="$set('showConfirmation', false)" variant="ghost">إلغاء</flux:button>
            <flux:button wire:click="executeConfirmedAction" variant="primary">
                تأكيد
            </flux:button>
        </flux:actions>
    </flux:modal>
</div>
