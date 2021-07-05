<?php

namespace App\Core\Blocks\Models;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Models\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExceptionBlock extends CoreModel
{
    use SoftDeletes;

    /**
     * Database table name
     */
    protected $table = 'exceptions';

    protected $primaryKey = 'id_exception';

    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'name',
        'active',
        'type',
        'value',
    ];

    public const TAG_CACHE_MODEL = 'EXCEPTION_CACHE_';

    public const TIME_CACHE_MODEL = ModelConst::CACHE_TIME_DAY;

    protected $casts = [
        'type'          => 'integer',
        'active'        => 'integer',
    ];

    /**
     * Date time columns.
     */
    protected $dates = [];

}
