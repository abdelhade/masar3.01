<?php

namespace Modules\POS\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Accounts\Models\AccHead;
use Modules\Branches\Models\Branch;
use App\Models\User;

class CashierTransaction extends Model
{
    protected $table = 'cashier_transactions';

    protected $guarded = ['id'];

    protected $casts = [
        'items' => 'array',
        'pro_date' => 'date',
        'accural_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'additional' => 'decimal:2',
        'additional_percentage' => 'decimal:2',
        'total' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'card_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'synced_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    /**
     * العميل
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'customer_id');
    }

    /**
     * المخزن
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'store_id');
    }

    /**
     * حساب الصندوق
     */
    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'cash_account_id');
    }

    /**
     * الموظف
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'employee_id');
    }

    /**
     * المستخدم
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * الفرع
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * نوع الفاتورة
     */
    public function proType(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ProType::class, 'pro_type_id');
    }

    /**
     * Scope للمعاملات المعلقة
     */
    public function scopePending($query)
    {
        return $query->where('sync_status', 'pending');
    }

    /**
     * Scope للمعاملات المزامنة
     */
    public function scopeSynced($query)
    {
        return $query->where('sync_status', 'synced');
    }

    /**
     * Scope للمعاملات الفاشلة
     */
    public function scopeFailed($query)
    {
        return $query->where('sync_status', 'failed');
    }
}
