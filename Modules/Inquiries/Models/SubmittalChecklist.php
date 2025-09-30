<?php

namespace Modules\Inquiries\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubmittalChecklist extends Model
{
    protected $fillable = ['name', 'score'];

    public function inquiries(): BelongsToMany
    {
        return $this->belongsToMany(Inquiry::class, 'inquiry_submittal_checklist');
    }
}
