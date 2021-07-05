<?php


namespace App\Core\Base\Services;

use Illuminate\Support\Str;

class DBRapiService
{

    /**
     * Combines SQL and its bindings
     *
     * @param \Eloquent $query
     * @return string
     */
    public static function getEloquentSqlWithBindings($query)
    {
        return vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
            return is_numeric($binding) ? $binding : "'{$binding}'";
        })->toArray());
    }

    public static function stringify($query){
        return  Str::slug(self::getEloquentSqlWithBindings($query));
    }
}
