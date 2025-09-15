<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_start',
        'period_end',
        'status',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function payrollEntries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class);
    }

    // Helper methods
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function canBeModified(): bool
    {
        return $this->isDraft();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
