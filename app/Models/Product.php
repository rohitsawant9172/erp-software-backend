<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\MultitenantTrait;

class Product extends Model
{
    use HasFactory, MultitenantTrait;

    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'category',
        'gst_rate',
        'price',
        'stock_level',
        'low_stock_threshold'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'tenant_id');
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }
}
