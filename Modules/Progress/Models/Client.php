<?php
namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{

    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address'
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
