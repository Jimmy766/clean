<?php

namespace App\Core\Lotteries\Services;

use App\Core\Base\Classes\ModelConst;
use App\Core\Clients\Services\IP2LocTrillonario;
use App\Core\Lotteries\Models\Lottery;
use App\Core\Countries\Services\GetCountryByCodeCountryService;
use App\Core\Users\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Class CheckInsureBlackListService
 * @package App\Services
 */
class GetLotteriesAndCheckInsureBlackListService
{

    /**
     * @var \App\Core\Countries\Services\GetCountryByCodeCountryService
     */
    private $getCountryByCodeCountryService;

    public function __construct(\App\Core\Countries\Services\GetCountryByCodeCountryService $getCountryByCodeCountryService)
    {
        $this->getCountryByCodeCountryService = $getCountryByCodeCountryService;
    }

    /**
     * @param array $idsLotteries
     * @param null  $idInsureBoostedJackpot
     * @param null  $relations
     * @param null  $idUser
     * @return Collection
     */
    public function execute(
        array $idsLotteries,
        $idInsureBoostedJackpot = null,
        $relations = null,
        $idUser = null
    ): Collection {
        $listInsureBlackList = $this->getLotteriesWithListInsureBlackList(
            $idsLotteries, $idInsureBoostedJackpot, $relations
        );

        $idCountry = $this->getIdCountryByIpOrUser($idUser);

        return $this->checkCountryIsBlackList($listInsureBlackList, $idCountry);
    }

    /**
     * @param array $idsLotteries
     * @param null  $idInsureBoostedJackpot
     * @param       $relations
     * @return Collection
     */
    private function getLotteriesWithListInsureBlackList(
        array $idsLotteries,
        $idInsureBoostedJackpot = null,
        $relations = null
    ): Collection {
        $idInsureBoostedJackpot = $idInsureBoostedJackpot ?? ModelConst::CHECK_INFORMATION_PROVIDE_ID_INSURE_BOOSTED_JACKPOT;

        $lotteryQuery = Lottery::query()
            ->leftJoin('tickets_providers_information as tpi', function($join) use ($idInsureBoostedJackpot) {
                $join->on('lotteries.lot_id', '=', 'tpi.lot_id')
                    ->where('tpi.scg_id', $idInsureBoostedJackpot);
            })
            ->whereIn('lotteries.lot_id', $idsLotteries)
            ->where('lot_active', '=', ModelConst::ENABLED);

        if ($relations !== null) {
            $lotteryQuery = $lotteryQuery->with($relations);
        }

        $columns = [
            'lotteries.*',
            'tpi.countries_blacklist_ids',
            'tpi.limit_max_jackpot',
        ];
        return $lotteryQuery->getFromCache($columns);

    }

    /**
     * @param null $idUser
     * @return array|int|null
     */
    private function getIdCountryByIpOrUser($idUser = null)
    {
        $idCountry = null;
        if ($idUser !== null) {
            $user = $this->getUserWithCountryIdByCache($idUser);
            $idCountry = $user->country_id;
        }

        if ($idUser === null) {
            [$codeCountry] = IP2LocTrillonario::get_iso('');
            $idCountry   = $this->getCountryByCodeCountryService->execute($codeCountry);
        }

        return $idCountry;

    }

    /**
     * @param $listInsureBlackList
     * @param $idCountry
     * @return mixed
     */
    private function checkCountryIsBlackList($listInsureBlackList, $idCountry): Collection
    {
        return $listInsureBlackList->map($this->mapInsureDontHasCountryBlackListTransform($idCountry));
    }

    /**
     * @param $idCountry
     * @return callable
     */
    private function mapInsureDontHasCountryBlackListTransform($idCountry): callable
    {
        return function ($item, $key) use ($idCountry) {
            $arrayCountriesBlackList = explode(',', $item->countries_blacklist_ids);

            $collectionCountriesBlackList = collect($arrayCountriesBlackList);

            //search in collection blacklist if exist idCountry and return boolean
           $hasBlockCountry = $this->checkExistArrayIdInCollection($collectionCountriesBlackList, $idCountry);

            $item->insure_boosted_jackpot = $hasBlockCountry;

            return $item;
        };
    }

    /**
     * @param $collectionCountriesBlackList
     * @param $idCountry
     * @return bool
     */
    public function checkExistArrayIdInCollection($collectionCountriesBlackList, $idCountry): bool
    {
        if (!is_array($idCountry)) {
            $hasCode = $collectionCountriesBlackList->search($idCountry);

            //if is true return false and reverse
            return $hasCode === false;
        }

        if(is_array($idCountry)){
           $idCountryCollection = collect($idCountry);
            $idCountryCollection = $idCountryCollection->filter(
                static function ($item, $key) use ($collectionCountriesBlackList) {
                    return $collectionCountriesBlackList->search($item['country_id']);
                }
            );

            $countBlock = $idCountryCollection->count() > 0;

            //if is true return false and reverse
            return !$countBlock;
        }


        return false;
    }


    /**
     * @param $idUser
     * @return mixed|null
     */
    private function getUserWithCountryIdByCache($idUser)
    {
        if($idUser === null){
            return  null;
        }
        $nameCache = "lottery_search_user_{$idUser}";
        $nameCache = md5($nameCache);
        $time = config('constants.cache_5');

        return Cache::remember(
            $nameCache, $time, function () use ($idUser) {
            return User::where('usr_id', $idUser)
                ->first([ 'country_id' ]);
        }
        );

    }


}
