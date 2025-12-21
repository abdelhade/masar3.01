<?php

namespace App\Observers;

use App\Models\NoteDetails;
use Illuminate\Support\Facades\Cache;

class NoteDetailsObserver
{
    public function created(NoteDetails $noteDetails): void
    {
        $this->clearCache($noteDetails);
    }

    public function updated(NoteDetails $noteDetails): void
    {
        $this->clearCache($noteDetails);
    }

    public function deleted(NoteDetails $noteDetails): void
    {
        $this->clearCache($noteDetails);
    }

    protected function clearCache(NoteDetails $noteDetails): void
    {
        // Clear both caches to ensure filters are always up to date
        Cache::forget('note_groups');
        Cache::forget('note_categories');
    }
}
