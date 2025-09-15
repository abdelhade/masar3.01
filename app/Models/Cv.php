<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Cv extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = ['id'];

    protected $table = 'cvs';

    // registerMediaConversions
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('HR_Cvs')
            ->useDisk('public')
            ->singleFile();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
