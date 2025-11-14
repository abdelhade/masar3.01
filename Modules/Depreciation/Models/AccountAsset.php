<?php

namespace Modules\Depreciation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Accounts\Models\AccHead;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AccountAsset extends Model
{
    use HasFactory;

    protected $table = 'accounts_assets';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'acc_head_id',
        'asset_name',
        'purchase_date',
        'purchase_cost',
        'salvage_value',
        'useful_life_years',
        'depreciation_method',
        'annual_depreciation',
        'accumulated_depreciation',
        'depreciation_start_date',
        'last_depreciation_date',
        'depreciation_account_id',
        'expense_account_id',
        'is_active',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'purchase_date' => 'date',
        'depreciation_start_date' => 'date',
        'last_depreciation_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'annual_depreciation' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the main asset account
     */
    public function accHead(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'acc_head_id');
    }

    /**
     * Get the accumulated depreciation account
     */
    public function depreciationAccount(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'depreciation_account_id');
    }

    /**
     * Get the depreciation expense account
     */
    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'expense_account_id');
    }

    /**
     * Calculate net book value (cost - accumulated depreciation)
     */
    public function getNetBookValue(): float
    {
        return ($this->purchase_cost ?? 0) - ($this->accumulated_depreciation ?? 0);
    }

    /**
     * Calculate depreciation percentage
     */
    public function getDepreciationPercentage(): float
    {
        if (($this->purchase_cost ?? 0) == 0) return 0;
        return (($this->accumulated_depreciation ?? 0) / $this->purchase_cost) * 100;
    }

    /**
     * Calculate remaining useful life in years
     */
    public function getRemainingLife(): int
    {
        if (!$this->purchase_date || !$this->useful_life_years) return 0;
        
        $yearsUsed = now()->diffInYears($this->purchase_date);
        return max(0, $this->useful_life_years - $yearsUsed);
    }

    /**
     * Check if asset is fully depreciated
     */
    public function isFullyDepreciated(): bool
    {
        $depreciableAmount = ($this->purchase_cost ?? 0) - ($this->salvage_value ?? 0);
        return ($this->accumulated_depreciation ?? 0) >= $depreciableAmount;
    }

    /**
     * Calculate annual depreciation based on method
     */
    public function calculateAnnualDepreciation(): float
    {
        if (!$this->purchase_cost || !$this->useful_life_years) return 0;
        
        $depreciableAmount = $this->purchase_cost - ($this->salvage_value ?? 0);
        
        switch ($this->depreciation_method) {
            case 'straight_line':
                return $depreciableAmount / $this->useful_life_years;
            
            case 'double_declining':
                $rate = (2 / $this->useful_life_years);
                $currentBookValue = $this->getNetBookValue();
                return min($currentBookValue * $rate, $depreciableAmount - ($this->accumulated_depreciation ?? 0));
            
            case 'sum_of_years':
                $sumOfYears = ($this->useful_life_years * ($this->useful_life_years + 1)) / 2;
                $remainingYears = $this->getRemainingLife();
                if ($remainingYears <= 0) return 0;
                return ($depreciableAmount * $remainingYears) / $sumOfYears;
            
            default:
                return $depreciableAmount / $this->useful_life_years;
        }
    }



    /**
     * Calculate remaining book value.
     */
    public function getRemainingValue(): float
    {
        return max(0, $this->purchase_cost - $this->salvage_value - $this->accumulated_depreciation);
    }

    /**
     * Calculate years since purchase.
     */
    public function getYearsSincePurchase(): int
    {
        if (!$this->purchase_date) {
            return 0;
        }
        
        return Carbon::parse($this->purchase_date)->diffInYears(now());
    }

    /**
     * Scope for active assets only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for assets ready for depreciation.
     */
    public function scopeReadyForDepreciation($query)
    {
        return $query->where('is_active', true)
                    ->whereNotNull('depreciation_start_date')
                    ->where('depreciation_start_date', '<=', now())
                    ->whereColumn('accumulated_depreciation', '<', 'purchase_cost');
    }
}
