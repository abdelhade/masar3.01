<?php

declare(strict_types=1);

namespace Modules\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\POS\Models\CashierTransaction;

class TransactionSaved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public CashierTransaction $transaction
    ) {}
}
