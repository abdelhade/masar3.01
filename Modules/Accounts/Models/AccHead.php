<?php

namespace Modules\Accounts\Models;

use App\Models\City;
use App\Models\Country;
use App\Models\JournalDetail;
use App\Models\OperHead;
use App\Models\State;
use App\Models\Town;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Branches\Models\Branch;
use Modules\Depreciation\Models\AccountAsset;

class AccHead extends Model
{
    use HasFactory;

    protected $table = 'acc_head';

    protected $guarded = ['id'];

    public $timestamps = false;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Database\Factories\AccHeadFactory
    {
        return \Database\Factories\AccHeadFactory::new();
    }

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function transfersAsAcc1()
    {
        return $this->hasMany(Transfer::class);
    }

    public function transfersAsAcc2()
    {
        return $this->hasMany(Transfer::class, 'acc2');
    }

    public function operheadsAsAcc1()
    {
        return $this->hasMany(OperHead::class, 'acc1');
    }

    public function operheadsAsAcc2()
    {
        return $this->hasMany(OperHead::class, 'acc2');
    }

    public function employees()
    {
        return $this->hasMany(OperHead::class, 'emp_id');
    }

    public function stores()
    {
        return $this->hasMany(OperHead::class, 'store_id');
    }

    public function users()
    {
        return $this->hasMany(OperHead::class, 'user');
    }

    public function haveParent()
    {
        return $this->belongsTo(AccHead::class, 'parent_id');
    }

    public function haveChildrens()
    {
        return $this->hasMany(AccHead::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(AccHead::class, 'parent_id');
    }

    // إضافة علاقة متداخلة للأبناء
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function town()
    {
        return $this->belongsTo(Town::class, 'town_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function accountType()
    {
        return $this->belongsTo(AccountsType::class, 'acc_type');
    }

    public function accountable()
    {
        return $this->morphTo();
    }

    public function accountAsset(): HasOne
    {
        return $this->hasOne(AccountAsset::class, 'acc_head_id');
    }

    public function isAssetAccount(): bool
    {
        return $this->acc_type == 11 && $this->is_basic == 0;
    }

    public function hasDepreciationSettings(): bool
    {
        return $this->accountAsset()->exists();
    }

    // علاقة مع JournalDetails
    public function journalDetails()
    {
        return $this->hasMany(JournalDetail::class, 'account_id', 'id');
    }

    /**
     * حساب الرصيد للفترة المحددة
     */
    public function calculateBalance($fromDate, $toDate, $type = 'revenue')
    {
        $query = $this->journalDetails()
            ->whereHas('journalHead.operHead', function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('pro_date', [$fromDate, $toDate]);
            });

        return $type === 'revenue' ? $query->sum('credit') : $query->sum('debit');
    }

    /**
     * التحقق من وجود حسابات فرعية نشطة
     */
    public function hasActiveChildren()
    {
        return $this->children()->exists();
    }

    // ============================================
    // Query Scopes
    // ============================================

    /**
     * Scope للبحث في الحسابات (الكود، الاسم، ID)
     */
    public function scopeSearch($query, ?string $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
                ->orWhere('aname', 'like', "%{$search}%")
                ->orWhere('id', $search);
        });
    }

    /**
     * Scope للفلترة حسب نوع الحساب
     */
    public function scopeByType($query, ?string $type)
    {
        if (empty($type)) {
            return $query;
        }

        // حالة خاصة لـ current-partners: البحث مباشرة عن acc_type = 12
        if ($type === 'current-partners') {
            return $query->where('acc_type', 12);
        }

        return $query->whereHas('accountType', function ($q) use ($type) {
            $q->where('name', $type);
        });
    }

    /**
     * Scope للحسابات غير الأساسية فقط
     */
    public function scopeNonBasic(Builder $query): Builder
    {
        return $query->where('is_basic', 0);
    }

    /**
     * Scope لإخفاء الحسابات السرية عن المستخدمين غير المصرح لهم
     */
    public function scopeVisibleForUser(Builder $query, ?User $user): Builder
    {
        // لو نظام الحسابات السرية غير مفعل، نعرض كل الحسابات
        $secretEnabled = (bool) setting('enable_secret_accounts', 1);

        if (! $secretEnabled) {
            return $query;
        }

        // لو المستخدم عنده صلاحية رؤية الحسابات السرية، نعرض كل الحسابات
        if ($user && $user->can('allow_secret_accounts')) {
            return $query;
        }

        // إخفاء الحسابات السرية عن باقي المستخدمين
        return $query->where('secret', false);
    }

    /**
     * Scope للحصول على الحسابات مع العلاقات الأساسية
     */
    public function scopeWithBasicRelations($query)
    {
        return $query->with('accountType')
            ->select([
                'id',
                'code',
                'acc_type',
                'balance',
                'address',
                'phone',
                'aname',
                'is_basic',
                'is_stock',
                'is_fund',
                'employees_expensses',
                'deletable',
                'editable',
                'rentable',
                'secret',
            ]);
    }

    /**
     * الحصول على مستوى الحساب في الشجرة
     */
    public function getLevel()
    {
        $level = 0;
        $parent = $this->haveParent;

        while ($parent) {
            $level++;
            $parent = $parent->haveParent;
        }

        return $level;
    }
}

