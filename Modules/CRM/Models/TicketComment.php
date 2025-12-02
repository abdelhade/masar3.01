<?php

namespace Modules\CRM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TicketComment extends Model
{
    protected $table = 'crm_ticket_comments';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'comment',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
