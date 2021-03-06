<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;


    public function users()
    {
        return $this->belongsToMany(User::class, 'user_businesses')->withPivot(['role', 'salary', 'is_active', 'is_favorite']);;
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}
