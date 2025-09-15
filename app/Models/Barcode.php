<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barcode extends Model
{
    use HasFactory;

    // تحديد اسم الجدول في قاعدة البيانات (اختياري إذا كان الجدول اسمه بنفس اسم الـ Model)
    protected $table = 'barcodes';

    // تحديد الأعمدة القابلة للتعبئة (mass assignable)
    protected $fillable = [
        'item_id',
        'unit_id',
        'barcode',
        'isdeleted',
        'tenant',
        'branch'
    ];

    /**
     * Get the unit that owns the Barcode.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
