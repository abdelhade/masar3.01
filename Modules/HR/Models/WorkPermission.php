<?php

declare(strict_types=1);

namespace Modules\HR\Models;

use Modules\HR\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WorkPermission extends Model
{
    // أذن انصراف
    protected $guarded = ['id'];
    protected $table = 'work_permissions';
    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updated_by()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approved_by()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
