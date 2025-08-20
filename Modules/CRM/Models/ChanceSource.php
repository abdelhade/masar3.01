<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;

class ChanceSource extends Model
{
    protected $fillable = ['title'];

    public function leads()
    {
        return $this->hasMany(Lead::class, 'source_id'); // هنا نحدد العمود الصحيح
    }
}
