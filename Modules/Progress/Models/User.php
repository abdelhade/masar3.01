<?php

namespace Modules\Progress\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
// في موديل User
public function employee()
{
    return $this->hasOne(Employee::class);
}
    // في ملف app/Models/User.php
// في User model
// في موديل User
public function projects()
{
    return $this->belongsToMany(Project::class);
}

/**
 * Get issues reported by this user
 */
public function reportedIssues()
{
    return $this->hasMany(Issue::class, 'reporter_id');
}

/**
 * Get issues assigned to this user
 */
public function assignedIssues()
{
    return $this->hasMany(Issue::class, 'assigned_to');
}

/**
 * Get comments made by this user
 */
public function issueComments()
{
    return $this->hasMany(IssueComment::class);
}

/**
 * Get attachments uploaded by this user
 */
public function issueAttachments()
{
    return $this->hasMany(IssueAttachment::class);
}

// في ملف app/Models/Project.php
// public function employees()
// {
//     return $this->belongsToMany(User::class, 'employee_projects');
// }
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        ];
    }
}
