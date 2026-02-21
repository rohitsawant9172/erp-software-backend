<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\MultitenantTrait;

class Payment extends Model
{
    use HasFactory, MultitenantTrait;

    protected $fillable = [
        'tenant_id',
        'farmer_id',
        'invoice_id',
        'amount',
        'payment_method',
        'transaction_id',
        'payment_date',
        'notes'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'tenant_id');
    }

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
