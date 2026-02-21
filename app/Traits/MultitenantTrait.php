<?php

namespace App\Traits;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;

trait MultitenantTrait
{
    public static function bootMultitenantTrait()
    {
        if (auth()->check()) {
            static::creating(function ($model) {
                // Automatically set tenant_id if not set and user is logged in
                if (!$model->tenant_id && auth()->user()->tenant_id) {
                    $model->tenant_id = auth()->user()->tenant_id;
                }
            });

            static::addGlobalScope('tenant', function (Builder $builder) {
                if (auth()->check() && auth()->user()->tenant_id) {
                    $builder->where('tenant_id', auth()->user()->tenant_id);
                }
            });
        }
    }
}
