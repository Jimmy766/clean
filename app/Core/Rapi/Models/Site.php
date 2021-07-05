<?php

namespace App\Core\Rapi\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Base\Traits\LogCache;
use App\Core\Rapi\Transforms\SiteTransformer;
use Illuminate\Support\Facades\Config;

class Site extends CoreModel
{
    use LogCache;
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'site_id';
    public $timestamps = false;
    public $transformer = SiteTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'site_name', 'site_url', 'site_url_https', 'site_lang', 'site_info_mail', 'description',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'site_id',
        'site_name',
        'site_url',
        'site_url_https',
        'site_lang',
        'site_info_mail',
        'description',
        'system_attributes',
    ];

    public const TAG_CACHE_MODEL = 'TAG_CACHE_SITE_';

    public function system() {
        return $this->belongsTo(System::class, 'sys_id', 'sys_id');
    }

    function getSystemAttributesAttribute() {
        $system = $this->rememberCache('site_system_'.$this->usr_id, Config::get('constants.cache_daily'), function() {
            $system = $this->system;
            return $system ? $system->transformer::transform($system) : null;
        });
        return $system;
    }


}
