<?php

namespace App\Models;

use App\Enums\ItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Branches\Models\Branch;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Item extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'cost', 'price', 'is_active', 'isdeleted'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "تم {$eventName} الصنف");
    }

    protected $table = 'items';

    protected $guarded = ['id'];

    protected $casts = [
        'type' => ItemType::class,
        'is_weight_scale' => 'boolean',
    ];

    // protected static function booted()
    // {
    //     static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    // }

    public function barcodes(): HasMany
    {
        return $this->hasMany(Barcode::class);
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'item_units', 'item_id', 'unit_id')
            ->withPivot('u_val', 'cost')
            ->withTimestamps();
    }

    public function prices(): BelongsToMany
    {
        return $this->belongsToMany(Price::class, 'item_prices', 'item_id', 'price_id')
            ->withPivot('unit_id', 'price', 'discount', 'tax_rate')
            ->withTimestamps();
    }

    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'item_notes', 'item_id', 'note_id')
            ->withPivot('note_detail_name')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', 0);
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('isdeleted', 0);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Register media collections for item images
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('item-images')
            ->useFallbackUrl(asset('images/no-image.png'))
            ->useFallbackPath(public_path('images/no-image.png'));

        $this->addMediaCollection('item-thumbnail')
            ->singleFile()
            ->useFallbackUrl(asset('images/no-image.png'))
            ->useFallbackPath(public_path('images/no-image.png'));
    }

    /**
     * Register media conversions for image optimization
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('preview')
            ->width(400)
            ->height(400)
            ->sharpen(5)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->width(800)
            ->height(800)
            ->sharpen(3)
            ->nonQueued();
    }

    // public function getCurrentQuantityAttribute()
    // {
    //     // حساب إجمالي الكميات الداخلة (qty_in) والخارجة (qty_out)
    //     $totalIn = OperationItems::where('item_id', $this->id)
    //         ->where('isdeleted', 0)
    //         ->sum('qty_in');

    //     $totalOut = OperationItems::where('item_id', $this->id)
    //         ->where('isdeleted', 0)
    //         ->sum('qty_out');

    //     return $totalIn - $totalOut;
    // }
}
