<?php

namespace Modules\Inquiries\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InquiryComment extends Model
{
    protected $fillable = [
        'inquiry_id',
        'user_id',
        'comment'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
