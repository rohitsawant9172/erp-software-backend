<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\MultitenantTrait;

class ProductBatch extends Model
{
    use HasFactory, MultitenantTrait;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'batch_number',
        'mfg_date',
        'expiry_date',
        'cost_price',
        'mrp',
        'sale_price',
        'stock_level',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'tenant_id');
    }
}
