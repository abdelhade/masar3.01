<?php

namespace App\Models;

use Modules\Accounts\Models\AccHead;
use App\Models\ProType;
use App\Models\OperationItems;
use App\Enums\OperationTypeEnum;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperHead extends Model
{
    use HasFactory;

    protected $table = 'operhead';

    protected $guarded = ['id'];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function type()
    {
        return $this->belongsTo(ProType::class, 'pro_type');
    }

    public function acc1Head()
    {
        return $this->belongsTo(AccHead::class, 'acc1');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function acc2Head()
    {
        return $this->belongsTo(AccHead::class, 'acc2');
    }

    public function acc3Head()
    {
        return $this->belongsTo(AccHead::class, 'acc3');
    }

    public function employee()
    {
        return $this->belongsTo(AccHead::class, 'emp_id');
    }

    public function store()
    {
        return $this->belongsTo(AccHead::class, 'store_id');
    }

    public function acc1Headuser()
    {
        return $this->belongsTo(AccHead::class, 'user');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center');
    }
    public function operationItems()
    {
        return $this->hasMany(OperationItems::class, 'pro_id', 'id');
    }

    public function journalHead()
    {
        return $this->hasOne(JournalHead::class, 'op_id');
    }

    public function journalDetails()
    {
        return $this->hasManyThrough(
            JournalDetail::class,
            JournalHead::class,
            'op_id', // Foreign key on JournalHead table
            'journal_id', // Foreign key on JournalDetail table
            'id', // Local key on OperHead table
            'id' // Local key on JournalHead table
        );
    }

    /**
     * Get the operation type enum
     */
    public function getOperationTypeEnum(): ?OperationTypeEnum
    {
        return OperationTypeEnum::fromValue($this->pro_type);
    }

    /**
     * Get the edit route for this operation
     */
    public function getEditRoute(): string
    {
        $operationType = $this->getOperationTypeEnum();
        return $operationType?->getEditRoute() ?? 'journals.edit';
    }

    /**
     * Get the edit URL for this operation
     */
    public function getEditUrl(): string
    {
        return route($this->getEditRoute(), $this->id);
    }

    /**
     * Check if this operation is an invoice
     */
    public function isInvoice(): bool
    {
        $operationType = $this->getOperationTypeEnum();
        return $operationType?->isInvoice() ?? false;
    }

    /**
     * Check if this operation is a voucher
     */
    public function isVoucher(): bool
    {
        $operationType = $this->getOperationTypeEnum();
        return $operationType?->isVoucher() ?? false;
    }

    /**
     * Check if this operation is a journal entry
     */
    public function isJournal(): bool
    {
        $operationType = $this->getOperationTypeEnum();
        return $operationType?->isJournal() ?? false;
    }

    /**
     * Check if this operation is a transfer
     */
    public function isTransfer(): bool
    {
        $operationType = $this->getOperationTypeEnum();
        return $operationType?->isTransfer() ?? false;
    }
    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
