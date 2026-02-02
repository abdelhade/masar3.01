<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Models\User;
use App\Models\Client;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'title',
        'subject',
        'message',
        'status',
        'target_filters',
        'created_by',
        'branch_id',
        'sent_at',
        'total_recipients',
        'total_sent',
        'total_opened',
        'total_clicked',
        'total_failed',
    ];

    protected $casts = [
        'target_filters' => 'array',
        'sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CampaignLog::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(CampaignLink::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function getOpenRateAttribute(): float
    {
        if ($this->total_sent === 0) {
            return 0;
        }

        return round(($this->total_opened / $this->total_sent) * 100, 2);
    }

    public function getClickRateAttribute(): float
    {
        if ($this->total_sent === 0) {
            return 0;
        }

        return round(($this->total_clicked / $this->total_sent) * 100, 2);
    }

    public function getSuccessRateAttribute(): float
    {
        if ($this->total_recipients === 0) {
            return 0;
        }

        return round(($this->total_sent / $this->total_recipients) * 100, 2);
    }
}
