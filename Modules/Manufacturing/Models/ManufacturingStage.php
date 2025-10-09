<?php

namespace Modules\Manufacturing\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class ManufacturingStage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'order',
        'estimated_duration',
        'cost',
        'is_active',
        'branch_id',
        // 'invoice_id', // هنضيفه لما نربطه بالفاتورة
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'estimated_duration' => 'decimal:2',
        'cost' => 'decimal:2',
        'order' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    /**
     * Scope للمراحل النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope للترتيب حسب order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * علاقة مع الفاتورة (هنفعلها لاحقاً)
     */
    // public function invoice()
    // {
    //     return $this->belongsTo(Invoice::class);
    // }
}
