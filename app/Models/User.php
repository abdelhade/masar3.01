<?php

namespace App\Models;

use Illuminate\Support\Str;
use Modules\Branches\Models\Branch;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Modules\Inquiries\Models\UserInquiryPreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, HasPermissions, HasRoles, Notifiable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn(string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_user')->withTimestamps();
    }

    public function assignedInquiries()
    {
        return $this->belongsToMany(
            \Modules\Inquiries\Models\Inquiry::class,
            'inquiry_assigned_engineers',
            'user_id',
            'inquiry_id'
        )
            ->withPivot('assigned_at', 'notes')
            ->withTimestamps();
    }


    public function getEmployeeIdAttribute()
    {
        return $this->employee?->id;
    }

    public function getFingerPrintIdAttribute()
    {
        return $this->employee?->finger_print_id ?: $this->id;
    }

    public function getFingerPrintNameAttribute()
    {
        return $this->employee?->finger_print_name ?: $this->name;
    }

    public function receivesBroadcastNotificationsOn()
    {
        return 'App.Models.User.' . $this->id;
    }

    public function inquiryPreferences()
    {
        return $this->hasOne(UserInquiryPreference::class);
    }

    public function loginSessions()
    {
        return $this->hasMany(LoginSession::class);
    }

    public function activeSessions()
    {
        return $this->hasMany(LoginSession::class)->whereNull('logout_at');
    }
}
