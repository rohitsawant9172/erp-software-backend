<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\MultitenantTrait;

class Invoice extends Model
{
    use HasFactory, MultitenantTrait;

    protected $fillable = [
        'tenant_id',
        'farmer_id',
        'invoice_no',
        'sub_total',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_status',
        'payment_method',
        'due_date',
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

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
