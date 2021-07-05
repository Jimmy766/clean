<?php

namespace App\Core\Assets\Models;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Models\CoreModel;
use App\timestamp;
use App\varchar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property varchar   $name       name
 * @property varchar   $image      image
 * @property timestamp $created_at created at
 * @property timestamp $updated_at updated at
 * @property timestamp $deleted_at deleted at
 */
class Asset extends CoreModel
{

    use SoftDeletes;

    /**
     * Database table name
     */
    protected $table = 'assets';
    protected $primaryKey = 'id_asset';

    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'name',
        'image',
    ];

    /**
     * Date time columns.
     */
    protected $dates = [];

}
