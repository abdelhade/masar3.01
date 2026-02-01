<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignLink extends Model
{
    protected $fillable = [
        'campaign_id',
        'original_url',
        'tracking_code',
        'clicks_count',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function incrementClicks(): void
    {
        $this->increment('clicks_count');
    }
}
