<?php

namespace Modules\MyResources\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\MyResources\Enums\DocumentType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceDocument extends Model
{
    protected $fillable = [
        'resource_id',
        'document_type',
        'file_path',
        'title',
        'description',
        'uploaded_by',
    ];

    protected $casts = [
        'document_type' => DocumentType::class,
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}

