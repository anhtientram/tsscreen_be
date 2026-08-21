<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

final class NotDeleted
{
    public static function apply(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNull('deleted')->orWhere('deleted', '!=', 'y');
        });
    }
}
