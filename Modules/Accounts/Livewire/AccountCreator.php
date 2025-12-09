<?php

namespace Modules\Accounts\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Modules\Accounts\Models\AccHead;

class AccountCreator extends Component
{
    public $accountType; // نوع الحساب (client, supplier, etc.)

    public $accountCode; // الكود الأساسي (1103, 2101, etc.)

    public $buttonText = 'إضافة حساب'; // نص الزر

    public $buttonClass = 'btn btn-sm btn-outline-primary'; // كلاس الزر

    // بيانات الحساب الجديد
    public $code = '';

    public $aname = '';

    public $phone = '';

    public $address = '';

    public $zatca_name = '';

    public $vat_number = '';

    public $national_id = '';

    public $zatca_address = '';

    public $company_type = '';

    public $nationality = '';

    public $parent_id = '';

    // قوائم البيانات
    public $parentAccounts;

    public $branches;

    public $branch_id = '';

    // حالة المودال
    public $showModal = false;

    public function mount($type = 'client', $buttonText = null, $buttonClass = null)
    {
        $this->accountType = $type;
        $this->buttonText = $buttonText ?? $this->getDefaultButtonText($type);
        $this->buttonClass = $buttonClass ?? 'btn btn-sm btn-outline-primary';

        // تحديد الكود بناءً على النوع
        $this->accountCode = $this->getAccountCode($type);

        // جلب الفروع
        $this->branches = userBranches();
        if ($this->branches->isNotEmpty()) {
            $this->branch_id = $this->branches->first()->id;
        }
    }

    private function getAccountCode($type)
    {
        $map = [
            'client' => '1103',
            'supplier' => '2101',
            'fund' => '1101',
            'bank' => '1102',
            'expense' => '57',
            'revenue' => '42',
            'creditor' => '2104',
            'debtor' => '1106',
            'partner' => '31',
            'asset' => '1202',
            'employee' => '2102',
            'store' => '1104',
            'current-partner' => '32',
        ];

        return $map[$type] ?? '1103';
    }

    private function getDefaultButtonText($type)
    {
        $map = [
            'client' => 'إضافة عميل',
            'supplier' => 'إضافة مورد',
            'fund' => 'إضافة صندوق',
            'bank' => 'إضافة بنك',
            'expense' => 'إضافة مصروف',
            'revenue' => 'إضافة إيراد',
            'creditor' => 'إضافة دائن',
            'debtor' => 'إضافة مدين',
            'partner' => 'إضافة شريك',
            'asset' => 'إضافة أصل',
            'employee' => 'إضافة موظف',
            'store' => 'إضافة مخزن',
            'current-partner' => 'إضافة جارى شريك',
        ];

        return $map[$type] ?? 'إضافة حساب';
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->loadParentAccounts();
        $this->generateCode();
        $this->resetForm();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function loadParentAccounts()
    {
        $this->parentAccounts = DB::table('acc_head')
            ->where('is_basic', '1')
            ->where('code', 'like', $this->accountCode.'%')
            ->orderBy('code')
            ->get();

        // تعيين أول حساب أب كقيمة افتراضية
        if ($this->parentAccounts->isNotEmpty()) {
            $this->parent_id = $this->parentAccounts->first()->id;
        }
    }

    private function generateCode()
    {
        $parent = $this->accountCode;

        $lastAccount = DB::table('acc_head')
            ->where('code', 'like', $parent.'%')
            ->orderByDesc('id')
            ->first();

        if ($lastAccount) {
            $suffix = str_replace($parent, '', $lastAccount->code);
            $next = str_pad(((int) $suffix + 1), 3, '0', STR_PAD_LEFT);
            $this->code = $parent.$next;
        } else {
            $this->code = $parent.'001';
        }
    }

    public function saveAccount()
    {
        $this->validate([
            'code' => 'required|string|max:9|unique:acc_head,code',
            'aname' => 'required|string|max:100|unique:acc_head,aname',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:250',
            'parent_id' => 'required|integer|exists:acc_head,id',
            'branch_id' => 'required|exists:branches,id',
        ], [
            'code.required' => 'الكود مطلوب.',
            'code.unique' => 'هذا الكود مستخدم بالفعل.',
            'aname.required' => 'اسم الحساب مطلوب.',
            'aname.unique' => 'هذا الاسم مستخدم بالفعل.',
            'parent_id.required' => 'يجب اختيار الحساب الأب.',
            'branch_id.required' => 'الفرع مطلوب.',
        ]);

        try {
            $newAccount = AccHead::create([
                'code' => $this->code,
                'aname' => $this->aname,
                'phone' => $this->phone,
                'address' => $this->address,
                'parent_id' => $this->parent_id,
                'branch_id' => $this->branch_id,
                'zatca_name' => $this->zatca_name,
                'vat_number' => $this->vat_number,
                'national_id' => $this->national_id,
                'zatca_address' => $this->zatca_address,
                'company_type' => $this->company_type,
                'nationality' => $this->nationality,
                'is_basic' => 0,
                'deletable' => 1,
                'editable' => 1,
                'start_balance' => 0,
                'credit' => 0,
                'debit' => 0,
                'balance' => 0,
                'isdeleted' => 0,
                'crtime' => now(),
                'mdtime' => now(),
            ]);

            // إذا كان الحساب شريك، ننشئ جاري شريك تلقائياً
            if ($this->accountType === 'partner') {
                $this->createCurrentPartnerAccount($newAccount);
            }

            $this->closeModal();

            // إرسال الحساب الجديد للكومبونت الأب
            $this->dispatch('account-created', [
                'account' => [
                    'id' => $newAccount->id,
                    'aname' => $newAccount->aname,
                    'code' => $newAccount->code,
                ],
                'type' => $this->accountType,
            ]);

            session()->flash('success', 'تم إنشاء الحساب بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء إنشاء الحساب: '.$e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->aname = '';
        $this->phone = '';
        $this->address = '';
        $this->zatca_name = '';
        $this->vat_number = '';
        $this->national_id = '';
        $this->zatca_address = '';
        $this->company_type = '';
        $this->nationality = '';
    }

    /**
     * إنشاء حساب جاري الشريك تلقائياً عند إنشاء حساب شريك
     */
    private function createCurrentPartnerAccount(AccHead $partnerAccount): void
    {
        // جلب الحساب الأساسي (كود 21081 - جاري الشركاء)
        $parentAccount = DB::table('acc_head')
            ->where('code', '21081')
            ->where('is_basic', 1)
            ->first();

        if (! $parentAccount) {
            return;
        }

        // توليد كود جديد لجاري الشريك (21081xx)
        $lastAccount = DB::table('acc_head')
            ->where('code', 'like', '21081%')
            ->where('code', '!=', '21081')
            ->orderByDesc('id')
            ->first();

        if ($lastAccount) {
            $suffix = str_replace('21081', '', $lastAccount->code);
            $next = str_pad(((int) $suffix + 1), 2, '0', STR_PAD_LEFT);
            $newCode = '21081'.$next;
        } else {
            $newCode = '2108101';
        }

        // إنشاء حساب جاري الشريك
        AccHead::create([
            'code' => $newCode,
            'aname' => 'جاري الشريك - '.$partnerAccount->aname,
            'phone' => $partnerAccount->phone,
            'address' => $partnerAccount->address,
            'parent_id' => $parentAccount->id,
            'branch_id' => $partnerAccount->branch_id,
            'acc_type' => '12',
            'zatca_name' => $partnerAccount->zatca_name,
            'vat_number' => $partnerAccount->vat_number,
            'national_id' => $partnerAccount->national_id,
            'zatca_address' => $partnerAccount->zatca_address,
            'company_type' => $partnerAccount->company_type,
            'nationality' => $partnerAccount->nationality,
            'is_basic' => 0,
            'deletable' => 1,
            'editable' => 1,
            'start_balance' => 0,
            'credit' => 0,
            'debit' => 0,
            'balance' => 0,
            'isdeleted' => 0,
            'crtime' => now(),
            'mdtime' => now(),
        ]);
    }

    public function render()
    {
        return view('accounts::livewire.account-creator');
    }
}
