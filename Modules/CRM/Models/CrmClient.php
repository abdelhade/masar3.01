<?php

namespace Modules\CRM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CrmClient extends Model
{
    protected $table = 'crm_clients';

    protected $guarded = ['id'];


    public function contacts()
    {
        return $this->hasMany(ClientContact::class, 'client_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'client_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
