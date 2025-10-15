<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $guarded = ['id'];
    protected $table = 'attendances';
    protected $casts = [
        'date' => 'date',
        'time' => 'string', // Cast as string since it's a time field
        'location' => 'array', // تغيير من string إلى array للـ JSON
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendanceProcessingDetails()
    {
        return $this->hasMany(AttendanceProcessingDetail::class);
    }

    // Accessor to format time for display
    public function getFormattedTimeAttribute()
    {
        return $this->time ? $this->time : '';
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
