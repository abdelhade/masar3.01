<?php

namespace Modules\Inquiries\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone_1',
        'phone_2',
        'type',
        'address_1',
        'address_2',
        'tax_number',
        'role_id',
        'parent_id',
        'notes'
    ];

    protected $casts = [
        'type' => 'string',
    ];

    /**
     * Accessor for 'cname' - returns 'name' field
     * This makes $contact->cname work
     */
    public function getCnameAttribute()
    {
        return $this->name;
    }

    public function role()
    {
        return $this->belongsTo(InquirieRole::class, 'role_id');
    }

    public function roles()
    {
        return $this->belongsToMany(InquirieRole::class, 'contact_role', 'contact_id', 'role_id');
    }

    public function parent()
    {
        return $this->belongsTo(Contact::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Contact::class, 'parent_id');
    }

    public function inquiries()
    {
        return $this->belongsToMany(Inquiry::class, 'inquiry_contact')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    /**
     * Scope للحصول على الأشخاص فقط
     */
    public function scopePersons($query)
    {
        return $query->where('type', 'person');
    }

    /**
     * Scope للحصول على الشركات فقط
     */
    public function scopeCompanies($query)
    {
        return $query->where('type', 'company');
    }

    /**
     * Scope للحصول على Contacts بدور معين
     */
    public function scopeWithRole($query, $roleName)
    {
        return $query->whereHas('roles', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }
}
