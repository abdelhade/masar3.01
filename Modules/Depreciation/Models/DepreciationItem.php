<?php

namespace Modules\Depreciation\Models;

use Modules\Accounts\Models\AccHead;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepreciationItem extends Model
{
    protected $table = 'depreciation_items';
    protected $guarded = [];
    
    protected $casts = [
        'purchase_date' => 'date',
        'cost' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'annual_depreciation' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'asset_account_id');
    }

    public function depreciationAccount(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'depreciation_account_id');
    }

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'expense_account_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // Methods
    public function calculateAnnualDepreciation(): float
    {
        if ($this->depreciation_method === 'straight_line') {
            return ($this->cost - $this->salvage_value) / $this->useful_life;
        }
        
        return 0; // Add other methods as needed
    }

    public function getNetBookValue(): float
    {
        return $this->cost - $this->accumulated_depreciation;
    }

    public function getRemainingLife(): int
    {
        $yearsUsed = now()->diffInYears($this->purchase_date);
        return max(0, $this->useful_life - $yearsUsed);
    }

    public function getDepreciationPercentage(): float
    {
        if ($this->cost == 0) return 0;
        return ($this->accumulated_depreciation / $this->cost) * 100;
    }
}
