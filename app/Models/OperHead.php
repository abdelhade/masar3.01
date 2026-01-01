<?php

namespace App\Models;

use App\Enums\OperationTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Accounts\Models\AccHead;
use Modules\Branches\Models\Branch;

class OperHead extends Model
{
    use HasFactory;

    protected $table = 'operhead';

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'currency_rate' => 'decimal:6',
    ];

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
     * Get the view route for this operation
     */
    public function getViewRoute(): string
    {
        $operationType = $this->getOperationTypeEnum();

        return $operationType?->getViewRoute() ?? $this->getEditRoute();
    }

    /**
     * Get the view URL for this operation
     */
    public function getViewUrl(): string
    {
        return route($this->getViewRoute(), $this->id);
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

    /**
     * Get the translated text for the operation type
     */
    public function getOperationTypeText(): string
    {
        $operationType = $this->getOperationTypeEnum();

        if ($operationType === null) {
            return __('reports.unspecified');
        }

        return match ($operationType) {
            OperationTypeEnum::SALES_INVOICE => __('reports.sales_invoice'),
            OperationTypeEnum::PURCHASE_INVOICE => __('reports.purchase_invoice'),
            OperationTypeEnum::SALES_RETURN => __('reports.sales_return'),
            OperationTypeEnum::PURCHASE_RETURN => __('reports.purchase_return'),
            OperationTypeEnum::DAILY_ENTRY => __('reports.journal_entry'),
            OperationTypeEnum::MULTI_ENTRY => __('reports.account_journal_entry'),
            default => $operationType->getArabicName(),
        };
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function manufacturingOrder(): BelongsTo
    {
        return $this->belongsTo(\Modules\Manufacturing\Models\ManufacturingOrder::class, 'manufacturing_order_id');
    }

    public function manufacturingStage(): BelongsTo
    {
        return $this->belongsTo(\Modules\Manufacturing\Models\ManufacturingStage::class, 'manufacturing_stage_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function invoiceTemplate()
    {
        return $this->belongsTo(\Modules\Invoices\Models\InvoiceTemplate::class, 'template_id');
    }

    /**
     * Currency relationship
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(\Modules\Settings\Models\Currency::class);
    }

    /**
     * Get amount in original currency (before conversion to base)
     * 
     * @return float
     */
    public function getAmountInOriginalCurrency(): float
    {
        // If no currency or exchange rate, return as is
        if (!$this->currency_id || !$this->currency_rate) {
            return (float) $this->pro_value;
        }
        
        // Convert from base currency back to original
        $converter = app(\App\Services\CurrencyConverterService::class);
        return $converter->convertFromBase(
            (float) $this->pro_value,
            $this->currency_id,
            (float) $this->currency_rate
        );
    }

    /**
     * Get formatted amount with currency symbol
     * 
     * @return string
     */
    public function getFormattedAmount(): string
    {
        $amount = $this->getAmountInOriginalCurrency();
        $symbol = $this->currency?->symbol ?? '';
        
        return number_format($amount, 2) . ' ' . $symbol;
    }
}
