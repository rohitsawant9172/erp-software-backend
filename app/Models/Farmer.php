<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\MultitenantTrait;

class Farmer extends Model
{
    use HasFactory, MultitenantTrait;

    protected $fillable = [
        'tenant_id',
        'name',
        'phone',
        'address',
        'land_size',
        'crops_grown',
        'credit_limit',
        'current_usage'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'tenant_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
