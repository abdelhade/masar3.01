<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignLog extends Model
{
    protected $fillable = [
        'campaign_id',
        'client_id',
        'status',
        'email',
        'error_message',
        'sent_at',
        'opened_at',
        'clicked_at',
        'tracking_code',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsOpened(): void
    {
        if ($this->status !== 'opened' && $this->status !== 'clicked') {
            $this->update([
                'status' => 'opened',
                'opened_at' => now(),
            ]);
        }
    }

    public function markAsClicked(): void
    {
        $this->update([
            'status' => 'clicked',
            'clicked_at' => now(),
        ]);

        if (!$this->opened_at) {
            $this->update(['opened_at' => now()]);
        }
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }
}
