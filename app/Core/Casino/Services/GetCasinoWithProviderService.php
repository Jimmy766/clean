<?php

namespace App\Core\Casino\Services;


use App\Core\Casino\Models\CasinoCategory;
use App\Core\Base\Classes\ModelConst;
use App\Core\Countries\Services\CheckCountryAndStateBlocksService;
use Illuminate\Support\Facades\Cache;

class GetCasinoWithProviderService
{

    public function execute($live=0,$provider=null)
    {

        $clientId = request()->oauth_client_id;
        $isoState = request()->client_country_region_iso;
        $isoCountry = request()->client_country_iso;
        $key          = 'get_casino_with_provider_' . $provider.'_live_'.$live.'_client_id_'.$clientId.'_state_'.$isoState.'_country_'.$isoCountry;
        $minutes      = config('constants.cache_5');
        $casino=Cache::remember($key, $minutes, function () use($provider,$live) {
            $casinoQuery=$this->getCasinoQuery(intval($live));
            $casinoQuery= $this->getCasinoWithProviderQuery($casinoQuery,$provider);
            $casino=$casinoQuery->get();
            $casino = $casino->filter(function ($item, $key) {
                return $item->casino_games_category->isNotEmpty();
            });

            $listExcept = ModelConst::EXCEPT_COUNTRY_REGION_CASINO_SPORT_SCRATCH;
            $exceptCountryState = collect($listExcept);

            $casino = CheckCountryAndStateBlocksService::execute($exceptCountryState, $casino);
            return $casino;
        });

        return $casino;
    }

    private function getCasinoQuery($live=0)
    {
        $relations = [
            "casino_games_category.casino_game.provider",
            "casino_games_category.casino_game.description",
            "casino_games_category.casino_game.casino_games_bet_config",
        ];
        $casinoCategoryQuery = CasinoCategory::query()->with($relations)
            ->with(["casino_games_category.casino_game"=>function($query) use ($live) {
                $query->where("live", $live)->where('is_lobby', $live);
            }])
            ->where('live',$live)
            ->where('active',1);
        return $casinoCategoryQuery;
    }
    private function getCasinoWithProviderQuery($casinoQuery, $provider)
    {
        if ($provider)
            $casinoQuery = $casinoQuery->with(array_merge([ "casino_games_category.casino_game.provider" => function($query) use ($provider){
                $query->where("id", $provider);
            } ],$casinoQuery->getEagerLoads()));
        return $casinoQuery;
    }

}
