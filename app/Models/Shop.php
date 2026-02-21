<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_name',
        'email',
        'phone',
        'gstin',
        'address',
        'logo_path',
        'is_active',
        'trial_ends_at'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }
}
