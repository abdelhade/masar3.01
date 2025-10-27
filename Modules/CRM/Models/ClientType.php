<?php

namespace Modules\CRM\Models;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Modules\Branches\Models\Branch;

class ClientType extends Model
{
    protected $fillable = [
        'title',
        'branch_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'client_type_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
