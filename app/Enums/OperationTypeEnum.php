<?php

namespace App\Enums;

enum OperationTypeEnum: int
{
    // 🧾 السندات - Vouchers
    case RECEIPT = 1;
    case PAYMENT = 2;
    case MULTI_RECEIPT = 32;
    case MULTI_PAYMENT = 33;
    case ALLOWED_DISCOUNT = 30;
    case EARNED_DISCOUNT = 31;
    case PETTY_CASH_SETTLEMENT = 34;
    case STOCK_DAMAGE = 35;
    case PROVISION_ENTRY = 36;
    case PERSONAL_LOAN = 37;

    // 🔄 التحويلات - Transfers  
    case CASH_TO_CASH = 3;
    case CASH_TO_BANK = 4;
    case BANK_TO_CASH = 5;
    case BANK_TO_BANK = 6;
    case INVENTORY_TRANSFER = 21;
    case BRANCH_TRANSFER = 23;
    case CURRENCY_CONVERSION = 38;

    // 📋 القيود - Journals
    case DAILY_ENTRY = 7;
    case MULTI_ENTRY = 8;

    // 🧾 الفواتير - Invoices
    case SALES_INVOICE = 10;
    case PURCHASE_INVOICE = 11;
    case SALES_RETURN = 12;
    case PURCHASE_RETURN = 13;
    case DAMAGE_INVOICE = 18;
    case CASHIER_INVOICE = 102; // فاتورة كاشير
    case CASHIER_RETURN = 112; // مرتجع كاشير

    // 📋 الأوامر - Orders
    case SALE_ORDER = 14;
    case PURCHASE_ORDER = 15;
    case WITHDRAW_ORDER = 19;
    case ADD_ORDER = 20;
    case RESERVATION_ORDER = 22;

    // 💰 عروض الأسعار - Quotations
    case QUOTATION_CUSTOMER = 16;
    case QUOTATION_SUPPLIER = 17;

    // 👥 الموارد البشرية - HR
    case SALARY_CALCULATION = 40;
    case EXTRA_CALC = 41;
    case DISCOUNT_CALC = 42;
    case INSURANCE_CALC = 43;
    case TAX_CALC = 44;

    // 📋 العقود - Contracts
    case CONTRACT = 45;
    case SALES_CONTRACT = 49;

    // 💰 المستحقات - Accruals
    case ACCURED_EXPENSE = 46;
    case ACCURED_INCOME = 47;
    case BANK_COMMISSION = 48;

    // 🏭 الأصول - Assets
    case DEPRECIATION = 50;
    case SELL_ASSET = 51;
    case BUY_ASSET = 52;
    case INCREASE_ASSET_VALUE = 53;
    case DECREASE_ASSET_VALUE = 54;

    // 💰 المالية - Finance
    case PARTNER_PROFIT_SHARING = 55;

    // 🏭 التصنيع - Manufacturing
    case PRODUCTION_MODEL = 56;
    case JOB_ORDER = 57;
    case STANDARD_MANUFACTURING = 58;
    case FREE_MANUFACTURING = 59;
    case MANUFACTURING_EXAMPLE = 63;

    // 📊 الأرصدة الافتتاحية - Opening Balances
    case INVENTORY_START_BALANCE = 60;
    case ACCOUNTS_START_BALANCE = 61;

    // 🏠 التأجير - Rental
    case RENTAL = 62;

    /**
     * Get the Arabic name for the operation type
     */
    public function getArabicName(): string
    {
        return match($this) {
            self::RECEIPT => 'سند قبض',
            self::PAYMENT => 'سند دفع',
            self::CASH_TO_CASH => 'تحويل نقدية من صندوق لصندوق',
            self::CASH_TO_BANK => 'تحويل نقدية من صندوق لبنك',
            self::BANK_TO_CASH => 'تحويل نقدية من بنك لصندوق',
            self::BANK_TO_BANK => 'تحويل نقدية من بنك لبنك',
            self::DAILY_ENTRY => 'قيد يومية',
            self::MULTI_ENTRY => 'قيد متعدد',
            self::SALES_INVOICE => 'فاتورة مبيعات',
            self::PURCHASE_INVOICE => 'فاتورة مشتريات',
            self::SALES_RETURN => 'مردود مبيعات',
            self::PURCHASE_RETURN => 'مردود مشتريات',
            self::CASHIER_INVOICE => 'فاتورة كاشير',
            self::CASHIER_RETURN => 'مرتجع كاشير',
            self::SALE_ORDER => 'امر بيع',
            self::PURCHASE_ORDER => 'امر شراء',
            self::QUOTATION_CUSTOMER => 'عرض سعر لعميل',
            self::QUOTATION_SUPPLIER => 'عرض سعر من مورد',
            self::DAMAGE_INVOICE => 'فاتورة توالف',
            self::WITHDRAW_ORDER => 'امر صرف',
            self::ADD_ORDER => 'امر اضافة',
            self::INVENTORY_TRANSFER => 'تحويل من مخزن لمخزن',
            self::RESERVATION_ORDER => 'امر حجز',
            self::BRANCH_TRANSFER => 'تحويل بين فروع',
            self::ALLOWED_DISCOUNT => 'خصم مسموح به',
            self::EARNED_DISCOUNT => 'خصم مكتسب',
            self::MULTI_RECEIPT => 'سند قبض متعدد',
            self::MULTI_PAYMENT => 'سند دفع متعدد',
            self::PETTY_CASH_SETTLEMENT => 'تسوية عهدة',
            self::STOCK_DAMAGE => 'سند إتلاف مخزون',
            self::PROVISION_ENTRY => 'مخصصات',
            self::PERSONAL_LOAN => 'سلفة شخصية',
            self::CURRENCY_CONVERSION => 'تحويل بين عملات',
            self::SALARY_CALCULATION => 'احتساب رواتب الموظفين',
            self::EXTRA_CALC => 'احتساب اضافي للموظفين',
            self::DISCOUNT_CALC => 'احتساب خصم للموظفين',
            self::INSURANCE_CALC => 'احتساب تأمينات اجتماعية',
            self::TAX_CALC => 'احتساب ضريبه دخل',
            self::CONTRACT => 'اتفاقية خدمة',
            self::ACCURED_EXPENSE => 'مصروفات مستحقة',
            self::ACCURED_INCOME => 'ايرادات مستحقة',
            self::BANK_COMMISSION => 'احتساب عمولة بنكية',
            self::SALES_CONTRACT => 'عقد بيع',
            self::DEPRECIATION => 'اهلاك الاصل',
            self::SELL_ASSET => 'بيع اصل',
            self::BUY_ASSET => 'شراء اصل',
            self::INCREASE_ASSET_VALUE => 'زيادة ف قيمة الاصل',
            self::DECREASE_ASSET_VALUE => 'نقص في قيمة الاصل',
            self::PARTNER_PROFIT_SHARING => 'توزيع الارباح علي الشركاء',
            self::PRODUCTION_MODEL => 'نموزج تصنيع',
            self::JOB_ORDER => 'امر تشغيل',
            self::STANDARD_MANUFACTURING => 'تصنيع معياري',
            self::FREE_MANUFACTURING => 'تصنيع حر',
            self::INVENTORY_START_BALANCE => 'تسجيل الارصده الافتتاحيه للمخازن',
            self::ACCOUNTS_START_BALANCE => 'تسجيل الارصده الافتتاحيه للحسابات',
            self::RENTAL => 'مستند تأجير معدة',
            self::MANUFACTURING_EXAMPLE => 'نموذج تصنيع',
        };
    }

    /**
     * Get the appropriate route name for editing this operation type
     */
    public function getEditRoute(): string
    {
        return match($this) {
            // Invoices
            self::SALES_INVOICE,
            self::PURCHASE_INVOICE,
            self::SALES_RETURN,
            self::PURCHASE_RETURN,
            self::DAMAGE_INVOICE,
            self::SALE_ORDER,
            self::PURCHASE_ORDER,
            self::QUOTATION_CUSTOMER,
            self::QUOTATION_SUPPLIER,
            self::WITHDRAW_ORDER,
            self::ADD_ORDER,
            self::INVENTORY_TRANSFER,
            self::RESERVATION_ORDER => 'invoices.edit',

            // Vouchers
            self::RECEIPT,
            self::PAYMENT,
            self::MULTI_RECEIPT,
            self::MULTI_PAYMENT => 'vouchers.edit',

            // Multi Vouchers
            self::ALLOWED_DISCOUNT,
            self::EARNED_DISCOUNT,
            self::PETTY_CASH_SETTLEMENT,
            self::STOCK_DAMAGE,
            self::PROVISION_ENTRY,
            self::PERSONAL_LOAN => 'multi-vouchers.edit',

            // Journals
            self::DAILY_ENTRY => 'journals.edit',
            self::MULTI_ENTRY => 'multi-journals.edit',

            // Transfers
            self::CASH_TO_CASH,
            self::CASH_TO_BANK,
            self::BANK_TO_CASH,
            self::BANK_TO_BANK,
            self::BRANCH_TRANSFER,
            self::CURRENCY_CONVERSION => 'transfers.edit',

            // Manufacturing
            self::PRODUCTION_MODEL,
            self::JOB_ORDER,
            self::STANDARD_MANUFACTURING,
            self::FREE_MANUFACTURING,
            self::MANUFACTURING_EXAMPLE => 'manufacturing.edit',

            // Rental
            self::RENTAL => 'rentals.edit',

            // Inventory Start Balance
            self::INVENTORY_START_BALANCE => 'inventory-balance.edit',

            // Default for other types
            default => 'journals.edit',
        };
    }

    /**
     * Get the appropriate route name for viewing this operation type
     */
    public function getViewRoute(): string
    {
        return match($this) {
            // Invoices - use specific view route
            self::RECEIPT,
            self::PAYMENT ,
            self::SALES_INVOICE,
            self::PURCHASE_INVOICE,
            self::SALES_RETURN,
            self::PURCHASE_RETURN,
            self::DAMAGE_INVOICE,
            self::SALE_ORDER,
            self::PURCHASE_ORDER,
            self::QUOTATION_CUSTOMER,
            self::QUOTATION_SUPPLIER,
            self::WITHDRAW_ORDER,
            self::ADD_ORDER,
            self::INVENTORY_TRANSFER,
            self::RESERVATION_ORDER => 'invoice.view',

            // For other types, fallback to edit route (which typically shows the form in view mode)
            default => $this->getEditRoute(),
        };
    }

    /**
     * Check if this operation type is an invoice
     */
    public function isInvoice(): bool
    {
        return in_array($this, [
            self::SALES_INVOICE,
            self::PURCHASE_INVOICE,
            self::SALES_RETURN,
            self::PURCHASE_RETURN,
            self::DAMAGE_INVOICE,
            self::SALE_ORDER,
            self::PURCHASE_ORDER,
            self::QUOTATION_CUSTOMER,
            self::QUOTATION_SUPPLIER,
            self::WITHDRAW_ORDER,
            self::ADD_ORDER,
            self::INVENTORY_TRANSFER,
            self::RESERVATION_ORDER,
        ]);
    }

    /**
     * Check if this operation type is a voucher
     */
    public function isVoucher(): bool
    {
        return in_array($this, [
            self::RECEIPT,
            self::PAYMENT,
            self::MULTI_RECEIPT,
            self::MULTI_PAYMENT,
            self::ALLOWED_DISCOUNT,
            self::EARNED_DISCOUNT,
            self::PETTY_CASH_SETTLEMENT,
            self::STOCK_DAMAGE,
            self::PROVISION_ENTRY,
            self::PERSONAL_LOAN,
        ]);
    }

    /**
     * Check if this operation type is a journal entry
     */
    public function isJournal(): bool
    {
        return in_array($this, [
            self::DAILY_ENTRY,
            self::MULTI_ENTRY,
        ]);
    }

    /**
     * Check if this operation type is a transfer
     */
    public function isTransfer(): bool
    {
        return in_array($this, [
            self::CASH_TO_CASH,
            self::CASH_TO_BANK,
            self::BANK_TO_CASH,
            self::BANK_TO_BANK,
            self::BRANCH_TRANSFER,
            self::CURRENCY_CONVERSION,
        ]);
    }

    /**
     * Get operation type from integer value
     */
    public static function fromValue(int $value): ?self
    {
        return self::tryFrom($value);
    }
}