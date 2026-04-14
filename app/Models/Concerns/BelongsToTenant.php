<?php

namespace App\Models\Concerns;

use App\Support\CurrentTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $id = CurrentTenant::id();
            if ($id !== null) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', $id);
            }
        });

        static::creating(function (Model $model): void {
            if (empty($model->getAttribute('tenant_id'))) {
                $id = CurrentTenant::id();
                if ($id !== null) {
                    $model->setAttribute('tenant_id', $id);
                }
            }
        });
    }
}
