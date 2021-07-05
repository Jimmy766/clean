<?php

namespace App\Core\Countries\Models;

use App\Core\Banners\Models\Banner;
use App\Core\Base\Models\CoreModel;
use App\Core\Countries\Models\CountryRegionPivot;
use App\Core\Slides\Models\Slide;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegionRapi extends CoreModel
{

    use SoftDeletes;

    protected $table      = 'regions';
    protected $primaryKey = 'id_region';

    public const TAG_CACHE_MODEL = 'TAG_CACHE_REGION_RAPI_';

    protected $fillable = [
        'name',
    ];
    protected $guarded  = [];

    public function countriesRegions()
    {
        return $this->hasMany(CountryRegionPivot::class, 'id_region', 'id_region')
            ->whereNull('country_region.deleted_at');
    }

    public function slides()
    {
        return $this->belongsToMany(Slide::class, 'region_slide', 'id_region', 'id_slide');
    }
    public function banners()
    {
        return $this->belongsToMany(Banner::class,'banner_region','id_region','id_banner')
            ->withPivot('type_region');
    }
}
