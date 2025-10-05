<?php

namespace App\Models;

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
    public function parent()
    {
        return $this->belongsTo(AccHead::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(AccHead::class, 'parent_id')->with('children');
    }
    // add the country and city and state and town
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

    // polymorphic relationship with Employee and anyother model
    public function accountable()
    {
        return $this->morphTo();
    }

    /**
     * One-to-one relationship with AccountAsset.
     */
    public function accountAsset(): HasOne
    {
        return $this->hasOne(AccountAsset::class, 'acc_head_id');
    }

    /**
     * Check if this account is an asset account.
     */
    public function isAssetAccount(): bool
    {
        return $this->acc_type == 11 && $this->is_basic == 0;
    }

    /**
     * Check if this account has depreciation settings.
     */
    public function hasDepreciationSettings(): bool
    {
        return $this->accountAsset()->exists();
    }

}
