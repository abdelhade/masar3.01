<?php

namespace App\Observers;

use App\Models\Item;
use Illuminate\Support\Facades\Cache;

class ItemObserver
{
    public function created(Item $item)
    {
        Cache::forget('partners'); // مسح الكاش إذا كان مرتبطًا بالأصناف
        Cache::forget('item_list'); // اسم مفتاح الكاش الخاص بالأصناف
    }

    public function updated(Item $item)
    {
        Cache::forget('partners');
        Cache::forget('item_list');
    }

    public function deleted(Item $item)
    {
        Cache::forget('partners');
        Cache::forget('item_list');
    }
}
