<?php

namespace Modules\Accounts\Models;

use App\Models\City;
use App\Models\Country;
use App\Models\JournalDetail;
use App\Models\OperHead;
use App\Models\State;
use App\Models\Town;
use App\Models\Transfer;
use Modules\Branches\Models\Branch;
use Modules\Depreciation\Models\AccountAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AccHead extends Model
{
    protected $table = 'acc_head';

    protected $guarded = ['id'];

    public $timestamps = false;

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

