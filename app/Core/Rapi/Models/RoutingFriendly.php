<?php

namespace App\Core\Rapi\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Rapi\Transforms\RoutingFriendlyTransformer;

class RoutingFriendly extends CoreModel
{
    public    $timestamps  = false;
    protected $guarded     = [];
    public $connection  = 'mysql_external';
    protected $primaryKey  = 'id';
    protected $table       = 'routing_friendly_lang';
    public    $transformer = RoutingFriendlyTransformer::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'element_id',
        'element_name',
        'element_type',
        'lang',
        'sys_id',
    ];

    public const ELEMENT_LOTTERY = 1;
    public const ELEMENT_SYNDICATE = 2;
    public const ELEMENT_RAFFLE = 4;
    public const ELEMENT_SYNDICATE_RAFFLE = 3;
}
