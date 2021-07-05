<?php

namespace App\Core\Slides\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegionSlidePivot extends Model
{


    use SoftDeletes;

    /**
     * Database table name
     */
    protected $table = 'region_slide';

    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'id_region',
        'id_slide',
    ];

    /**
     * Date time columns.
     */
    protected $dates = [];

}
