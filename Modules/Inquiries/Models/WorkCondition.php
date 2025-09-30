<?php

namespace Modules\Inquiries\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WorkCondition extends Model
{
    protected $fillable = ['name', 'score', 'options'];

    protected $casts = [
        'options' => 'array',
    ];

    public function inquiries(): BelongsToMany
    {
        return $this->belongsToMany(Inquiry::class, 'inquiry_work_condition');
    }
}
