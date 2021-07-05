<?php

namespace App\Core\Base\Models;

use App\Core\Base\Repositories\JoinBuilder\CoreBuilder;
use Illuminate\Database\Eloquent\Model;

class CoreModel extends Model
{

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new CoreBuilder($query);
    }

}
