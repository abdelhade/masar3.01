<?php

namespace Modules\CRM\Models;

use App\Models\User;
use App\Models\Client;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $table = 'leads';

    // تحديد الحقول القابلة للتعبئة
    protected $fillable = [
        'title',
        'client_id',
        'status_id',
        'amount',
        'source_id',
        'assigned_to',
        'description',
        'branch_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function status()
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function changeStatus($newStatusId)
    {
        $this->update(['status_id' => $newStatusId]);
    }

    public function source()
    {
        return $this->belongsTo(ChanceSource::class, 'source_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
