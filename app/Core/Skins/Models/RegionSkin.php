<?php

namespace App\Core\Skins\Models;

use App\Core\Base\Models\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegionSkin extends CoreModel
{
    use SoftDeletes;

    /**
     * Database table name
     */
    protected $table = 'region_skins';

    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'id_region',
        'id_skin',
    ];

    /**
     * Date time columns.
     */
    protected $dates = [];

}
