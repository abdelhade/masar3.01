<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Covenant extends Model implements HasMedia
{
    // عهده عمل
    use InteractsWithMedia;

    protected $guarded = ['id'];
    protected $table = 'covenants';

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }


    // registerMediaConversions
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('HR_Covenants')
            ->useDisk('public')
            ->singleFile();
    }

    /**
     * Get the covenant's image URL or null if no image exists
     * Works correctly in both local (Laragon) and production environments
     * Uses asset() helper to ensure correct URL generation
     */
    public function getImageUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('HR_Covenants');

        // If no media exists, return null (no fallback for covenants)
        if (!$media) {
            return null;
        }

        // Build URL using asset() helper with the correct path
        // Path format: storage/{id}/{filename}
        // This works correctly with the symlink created by php artisan storage:link
        return asset('storage/'.$media->id.'/'.$media->file_name);
    }
}
