<?php

namespace Modules\CRM\Models;

use App\Models\User;
use App\Models\Client;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'crm_tickets';

    protected $fillable = [
        'client_id',
        'assigned_to',
        'created_by',
        'subject',
        'description',
        'status',
        'priority',
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

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class, 'ticket_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function updateStatus($newStatus)
    {
        $this->update(['status' => $newStatus]);
    }
}
