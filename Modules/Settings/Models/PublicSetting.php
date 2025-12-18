<?php

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class PublicSetting extends Model
{
    protected $fillable = ['label', 'key', 'input_type', 'category_id', 'value'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
