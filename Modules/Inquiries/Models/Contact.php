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
        return $this->belongsToMany(
            InquirieRole::class,
            'contact_role',
            'contact_id',
            'role_id'
        );
    }

    public function parent()
    {
        return $this->belongsTo(Contact::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Contact::class, 'parent_id');
    }

    // ========== العلاقات الجديدة Many-to-Many ==========

    public function companies()
    {
        return $this->belongsToMany(
            Contact::class,
            'contact_relationships',
            'contact_id',
            'related_contact_id'
        )
            ->where('contacts.type', 'company')
            ->withPivot('relationship_type')
            ->withTimestamps();
    }

    public function persons()
    {
        return $this->belongsToMany(
            Contact::class,
            'contact_relationships',
            'related_contact_id',
            'contact_id'
        )
            ->where('contacts.type', 'person')
            ->withPivot('relationship_type')
            ->withTimestamps();
    }

    public function relatedContacts()
    {
        return $this->belongsToMany(
            Contact::class,
            'contact_relationships',
            'contact_id',
            'related_contact_id'
        )
            ->withPivot('relationship_type')
            ->withTimestamps();
    }

    public function inquiries()
    {
        return $this->belongsToMany(Inquiry::class, 'inquiry_contact')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function scopePersons($query)
    {
        return $query->where('type', 'person');
    }

    public function scopeCompanies($query)
    {
        return $query->where('type', 'company');
    }

    public function scopeWithRole($query, $roleName)
    {
        return $query->whereHas('roles', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }
}
