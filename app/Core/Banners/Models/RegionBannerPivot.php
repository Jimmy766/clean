<?php

namespace App\Core\Banners\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegionBannerPivot extends Model
{

    use SoftDeletes;

    /**
     * Database table name
     */
    protected $table = 'banner_region';
    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'id_region',
        'id_banner',
    ];
    /**
     * Date time columns.
     */
    protected $dates = [];
}
