<?php

namespace Modules\Inquiries\Models;

use Illuminate\Database\Eloquent\Model;

class InquirySource extends Model
{
    protected $fillable = ['name', 'parent_id', 'is_active'];
    
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function ancestors()
    {
        return $this->belongsTo(self::class, 'parent_id')->with('ancestors');
    }


    public function inquiries()
    {
        return $this->hasMany(Inquiry::class, 'inquiry_source_id');
    }
}
