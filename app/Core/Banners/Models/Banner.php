<?php

namespace App\Core\Banners\Models;

use App\Core\Banners\Models\ConfigBanner;
use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Models\CoreModel;
use App\Core\Countries\Models\RegionRapi;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends CoreModel
{
    use SoftDeletes;

    public const BANNER_TYPE_LOTTERIES      = 0;
    public const BANNER_TYPE_RAFFLES        = 1;
    public const BANNER_TYPE_SYNDICATES     = 2;
    public const BANNER_TYPE_VIRTUAL_CASINO = 3;
    public const BANNER_TYPE_LIVE_CASINO    = 4;
    public const BANNER_TYPE_SPORT_BOOKS    = 5;
    public const BANNER_TYPE_PRODUCT        = [
        [
            'id'   => self::BANNER_TYPE_LOTTERIES,
            'name' => 'Lotteries',
        ],
        [
            'id'   => self::BANNER_TYPE_RAFFLES,
            'name' => 'Raffles',
        ],
        [
            'id'   => self::BANNER_TYPE_SYNDICATES,
            'name' => 'Syndicate',
        ],
        [
            'id'   => self::BANNER_TYPE_VIRTUAL_CASINO,
            'name' => 'Virtual Casino',
        ],
        [
            'id'   => self::BANNER_TYPE_LIVE_CASINO,
            'name' => 'Live Casino',
        ],
        [
            'id'   => self::BANNER_TYPE_SPORT_BOOKS,
            'name' => 'SportBooks',
        ],
    ];

    public const BANNER_SIMPLE_SIMPLE = 0;
    public const BANNER_TYPE_COMPLETE = 1;
    public const BANNER_TYPE          = [
        [
            'id'   => self::BANNER_SIMPLE_SIMPLE,
            'name' => 'Simple',
        ],
        [
            'id'   => self::BANNER_TYPE_COMPLETE,
            'name' => 'Complete',
        ],
    ];
    protected $table      = 'banners';
    protected $primaryKey = 'id_banner';
    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'name',
        'status',
        'active',
        'type',
        'type_product',
        'image',
        'link',
    ];

    protected $casts = [
        'status'       => 'integer',
        'active'       => 'integer',
        'type'         => 'integer',
        'type_product' => 'integer',
    ];

    public const TAG_CACHE_MODEL = 'TAG_CACHE_BANNER_';

    public const TIME_CACHE_MODEL = ModelConst::CACHE_TIME_DAY;

    public function regions()
    {
        return $this->belongsToMany(
            RegionRapi::class, 'banner_region', 'id_banner', 'id_region'
        )->whereNull('banner_region.deleted_at');
    }

    public function configBanner()
    {
        return $this->hasMany(ConfigBanner::class, 'id_banner');
    }
}
