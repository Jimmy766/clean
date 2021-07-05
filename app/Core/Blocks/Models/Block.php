<?php

namespace App\Core\Blocks\Models;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Models\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Block extends CoreModel
{
    use SoftDeletes;

    public const TAG_CACHE_MODEL = 'BLOCKS_CACHE_';

    public const TIME_CACHE_MODEL = ModelConst::CACHE_TIME_DAY;

    protected $primaryKey = 'id_block';

    /**
     * Database table name
     */
    protected $table = 'blocks';
    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'name',
        'type',
        'active',
        'value',
        'id_entityable',
        'type_entityable',
        'id_blockable',
        'type_blockable',
    ];
    /**
     * Date time columns.
     */
    protected $dates = [];
    protected $casts = [
        'id_entityable' => 'integer',
        'id_blockable'  => 'integer',
        'type'          => 'integer',
        'active'        => 'integer',
    ];

    public function entityable()
    {
        return $this->morphTo('entityable', 'type_entityable', 'id_entityable');
    }

    public function blockable()
    {
        return $this->morphTo('blockable', 'type_blockable', 'id_blockable');
    }
}
