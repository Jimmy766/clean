<?php

namespace App\Core\Rapi\Services;

use App\Core\Banners\Resources\BannerResource;
use App\Core\Banners\Services\GetBannerAvailableService;
use App\Core\Skins\Collections\SkinCollection;
use App\Core\Skins\Services\AllSkinsAvailableService;
use App\Core\Slides\Resources\SlideResource;
use App\Core\Slides\Services\AllSlidesAvailableService;
use App\Core\Clients\Services\IP2LocTrillonario;

/**
 * Class GlobalHomeInfoService
 * logic to return inf from various endpoint's, to increase performance and reduce number request to home user
 * @package App\Services
 */
class GlobalAvailableService
{

    /**
     * @var AllSlidesAvailableService
     */
    private $allSlidesAvailableService;
    /**
     * @var GetBannerAvailableService
     */
    private $getBannerAvailableService;
    /**
     * @var AllSkinsAvailableService
     */
    private $allSkinsAvailableService;

    public function __construct(
        AllSlidesAvailableService $allSlidesAvailableService,
        GetBannerAvailableService $getBannerAvailableService,
        AllSkinsAvailableService $allSkinsAvailableService
    ) {
        $this->allSlidesAvailableService = $allSlidesAvailableService;
        $this->getBannerAvailableService = $getBannerAvailableService;
        $this->allSkinsAvailableService    = $allSkinsAvailableService;
    }

    /**
     * @param $request
     * @return array
     */
    public function execute($request): array
    {
        /*===========SLIDES===========*/
        $dataSlideService             = $this->allSlidesAvailableService->execute();
        $data[ 'ip' ]                 = $request->user_ip;
        $arrayReturn[ 'codeCountry' ] = $dataSlideService[ 'codeCountry' ];
        $arrayReturn[ 'date' ]        = $dataSlideService[ 'date' ];
        $arrayReturn[ 'day' ]         = $dataSlideService[ 'day' ];
        $arrayReturn[ 'slides' ]      = SlideResource::collection($dataSlideService[ 'slides' ]);

        /*===========BANNERS===========*/
        [ $codeCountry ] = IP2LocTrillonario::get_iso('');
        $banner          = $this->getBannerAvailableService->execute($codeCountry, $request);
        $bannersResource = null;
        if ($banner !== null) {
            $bannersResource = new BannerResource($banner);
        }
        $arrayReturn[ 'banner' ] = $bannersResource;

        /*===========SKINS===========*/
        $skins                  = $this->allSkinsAvailableService->execute();
        $arrayReturn[ 'skins' ] = SkinCollection::collection($skins);

        return $arrayReturn;
    }

}
