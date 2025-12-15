<?php

namespace Modules\Checks\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Checks\Models\Check;

class CheckCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Check $check
    ) {}
}
