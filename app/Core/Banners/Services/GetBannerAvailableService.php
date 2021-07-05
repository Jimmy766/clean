<?php

namespace App\Core\Banners\Services;

use App\Core\Banners\Models\Banner;
use App\Core\Countries\Services\GetCountryByCodeCountryService;

/**
 * Class GetSlideByDateOrDayService
 * @package App\Services
 */
class GetBannerAvailableService
{

    /**
     * @var \App\Core\Countries\Services\GetCountryByCodeCountryService
     */
    private $getCountryByCodeCountryService;

    /**
     * GetSlideByDateOrDayService constructor.
     * @param \App\Core\Countries\Services\GetCountryByCodeCountryService $getCountryByCodeCountryService
     */
    public function __construct(\App\Core\Countries\Services\GetCountryByCodeCountryService $getCountryByCodeCountryService)
    {
        $this->getCountryByCodeCountryService = $getCountryByCodeCountryService;
    }

    /**
     * @param $countryCode
     * @param $request
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function execute($countryCode, $request)
    {
        $country = $this->getCountryByCodeCountryService->execute($countryCode);

        $bannerByCountry     = $this->getBannerByCountry($request, $country);
        $bannerWithOutRegion = $this->getBannerWitOutRegion($request);
        return $this->getBanner($bannerByCountry, $bannerWithOutRegion);
    }

    /**
     * @param $request
     * @param $country
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private function getBannerByCountry($request, $country)
    {
        $tags        = [ Banner::TAG_CACHE_MODEL, ];
        $bannerQuery = Banner::query();
        if ($request->type_product !== null) {
            $typeProduct = $request->type_product;
            $typeProduct = (int) $typeProduct;
            $bannerQuery = $bannerQuery->where('type_product', '!=', $typeProduct);
        }
        if ($request->type !== null) {
            $typeProduct = $request->type;
            $typeProduct = (int) $typeProduct;
            $bannerQuery = $bannerQuery->where('type', $typeProduct);
        }
        $bannerQuery = $bannerQuery->join('banner_region as br', 'banners.id_banner', '=', 'br.id_banner')
            ->join('country_region as cr', 'br.id_region', '=', 'cr.id_region')
            ->whereIn('cr.id_country', $country)
            ->groupBy([ "banners.id_banner" ])
            ->where('banners.active',1)
            ->where('banners.status',1)
            ->whereNull('cr.deleted_at')
            ->whereNull('br.deleted_at');
        $banners = $bannerQuery->getFromCache([ "banners.id_banner" ], $tags);
        if($banners->count() <= 0){
            return null;
        }
        return $banners->random();
    }

    private function getBannerWitOutRegion($request)
    {
        $tags        = [ Banner::TAG_CACHE_MODEL, ];
        $bannerQuery = Banner::query();
        if ($request->type_product !== null) {
            $typeProduct = $request->type_product;
            $typeProduct = (int) $typeProduct;
            $bannerQuery = $bannerQuery->where('type_product', '!=', $typeProduct);
        }
        if ($request->type !== null) {
            $typeProduct = $request->type;
            $typeProduct = (int) $typeProduct;
            $bannerQuery = $bannerQuery->where('type', $typeProduct);
        }
        $bannerQuery = $bannerQuery->leftJoin('banner_region as br', 'banners.id_banner', '=', 'br.id_banner')
            ->groupBy([ "banners.id_banner" ])
            ->where('banners.active',1)
            ->where('banners.status',1)
            ->whereNull('br.id_region')
            ->whereNull('br.deleted_at');

        $banners = $bannerQuery->getFromCache([ "banners.id_banner" ], $tags);
        if($banners->count() <= 0){
            return null;
        }
        return $banners->random();
    }

    private function getBanner($bannerByCountry, $bannerWithOutRegion)
    {
        $tags     = [ Banner::TAG_CACHE_MODEL, ];
        $relation = [ 'configBanner.languages' ];
        $banners  = [];
        if ($bannerByCountry !== null) {
            $banners[] = [ 'id_banner' => $bannerByCountry->id_banner ];
        }
        if ($bannerWithOutRegion !== null) {
            $banners[] = [ 'id_banner' => $bannerWithOutRegion->id_banner ];
        }

        $bannerQuery = Banner::query()
            ->with($relation);
        $bannerQuery = $bannerQuery->whereIn('id_banner', $banners);
        $banners     = $bannerQuery->getFromCache([ "banners.*" ], $tags);
        if($banners->count() <= 0){
            return null;
        }
        return $banners->random();
    }

}
