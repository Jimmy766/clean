<?php

namespace App\Core\Blocks\Services;

use App\Core\Blocks\Models\Block;
use App\Core\Base\Classes\ModelConst;
use App\Core\Countries\Models\Country;
use App\Core\Terms\Models\Language;
use App\Core\Clients\Services\IP2LocTrillonario;
use App\Core\Countries\Models\RegionRapi;
use App\Core\Countries\Services\GetCountryByCodeCountryService;
use Illuminate\Support\Collection;

/**
 * Class LocationService
 * @package App\Core\Base\Services
 */
class CheckBlockService
{

    /**
     * @var GetCountryByCodeCountryService
     */
    private $getCountryByCodeCountryService;

    public function __construct(GetCountryByCodeCountryService $getCountryByCodeCountryService)
    {
        $this->getCountryByCodeCountryService = $getCountryByCodeCountryService;
    }

    /**
     * @param      $request
     * @param null $exceptions
     * @return Collection
     */
    public function execute($request, $exceptions = null): Collection
    {
        $validationsCollect = collect([]);
        if ($exceptions === 'yes') {
            return $validationsCollect;
        }

        $validation = $this->validateBlockByIp($request);
        $validationsCollect->push($validation);
        $validation = $this->validateBlockByRegion($request);
        $validationsCollect->push($validation);
        $validation = $this->validateBlockByLanguage($request);
        $validationsCollect->push($validation);
        $validation = $this->validateBlockByAffiliate($request);
        $validationsCollect->push($validation);

        return $validationsCollect;
    }

    private function validateBlockByIp($request): array
    {
        $ipUser  = $request->user_ip;
        $value   = Block::query()
            ->where('active', ModelConst::ENABLED)
            ->where('type', ModelConst::TYPE_BLOCK_IP)
            ->where('value', $ipUser)
            ->firstFromCache([ 'id_block' ], Block::TAG_CACHE_MODEL);
        $message = __('IP BLOCK') . " - " . $ipUser;

        return [ 'type' => 'ip', 'value' => $value, 'message' => $message ];
    }

    private function validateBlockByRegion($request): array
    {
        [$codeCountry] = IP2LocTrillonario::get_iso('');
        $country     = $this->getCountryByCodeCountryService->execute($codeCountry);
        $ipUser      = $request->user_ip;

        $value = Block::query()
            ->join('country_region', 'country_region.id_region', '=', 'blocks.id_blockable')
            ->where('active', ModelConst::ENABLED)
            ->where('type', ModelConst::TYPE_BLOCK_REGION)
            ->whereNull('id_entityable')
            ->whereNotNull('id_blockable')
            ->where('type_blockable', RegionRapi::class)
            ->whereNull('country_region.deleted_at')
            ->whereIn('country_region.id_country', $country)
            ->firstFromCache([ 'id_block' ], Block::TAG_CACHE_MODEL);

        $message = __('COUNTRY BLOCK');
        $message = "{$message} - {$ipUser} - {$codeCountry}";

        return [ 'type' => 'region', 'value' => $value, 'message' => $message ];
    }

    private function validateBlockByLanguage($request): array
    {
        $codeLanguage = $request->code_language;

        $value = Block::query()
            ->join('languages', 'languages.id_language', '=', 'blocks.id_blockable')
            ->where('active', ModelConst::ENABLED)
            ->where('type', ModelConst::TYPE_BLOCK_LANGUAGE)
            ->whereNull('id_entityable')
            ->whereNotNull('id_blockable')
            ->where('type_blockable', Language::class)
            ->where('languages.code', $codeLanguage)
            ->whereNull('languages.deleted_at')
            ->firstFromCache([ 'id_block' ], Block::TAG_CACHE_MODEL);

        $message = __('LANGUAGE BLOCK');
        $message = "{$message} - {$codeLanguage} ";
        $value = $codeLanguage === null ? null : $value;

        return [ 'type' => 'language', 'value' => $value, 'message' => $message ];
    }

    private function validateBlockByAffiliate($request): array
    {
        $idAffiliate = $request->affiliate;

        $value = Block::query()
            ->join('languages', 'languages.id_language', '=', 'blocks.id_blockable')
            ->where('active', ModelConst::ENABLED)
            ->where('type', ModelConst::TYPE_BLOCK_AFFILIATE)
            ->where('value', $idAffiliate)
            ->firstFromCache([ 'id_block' ], Block::TAG_CACHE_MODEL);

        $message = __('AFFILIATE BLOCK');
        $message = "{$message} - {$idAffiliate} ";
        $value = $idAffiliate === null ? null : $value;

        return [ 'type' => 'affiliate', 'value' => $value, 'message' => $message ];
    }

}
