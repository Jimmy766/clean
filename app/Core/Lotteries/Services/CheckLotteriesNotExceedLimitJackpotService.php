<?php

namespace App\Core\Lotteries\Services;

use App\Core\Lotteries\Services\Boosted\FilterBoostedJackpotExceedLimitService;
use App\Core\Lotteries\Services\Boosted\FilterBoostedJackpotModifierService;
use Illuminate\Support\Collection;

/**
 * Class CheckLotteriesNotExceedLimitJackpotService
 * @package App\Services
 */
class CheckLotteriesNotExceedLimitJackpotService
{
    /**
     * @var \App\Core\Lotteries\Services\Boosted\FilterBoostedJackpotModifierService
     */
    private $filterBoostedJackpotModifierService;
    /**
     * @var FilterBoostedJackpotExceedLimitService
     */
    private $filterBoostedJackpotExceedLimitService;

    public function __construct(
        FilterBoostedJackpotModifierService $filterBoostedJackpotModifierService,
        FilterBoostedJackpotExceedLimitService $filterBoostedJackpotExceedLimitService
    ) {
        $this->filterBoostedJackpotModifierService = $filterBoostedJackpotModifierService;
        $this->filterBoostedJackpotExceedLimitService = $filterBoostedJackpotExceedLimitService;
    }

    /**
     * @param Collection $lotteries
     * @return Collection
     */
    public function execute(Collection $lotteries): Collection
    {
        $lotteries = $lotteries->map($this->mapLotteryGetRelationLotteriesBoostedJackpotTransform());

        return $lotteries;
    }

    private function mapLotteryGetRelationLotteriesBoostedJackpotTransform(): callable
    {
        return function ($item) {
            $lotteriesBoostedJackpot = $item->lotteriesBoostedJackpot;

            $lotteriesBoostedJackpot = $item->insure_boosted_jackpot === true ? $lotteriesBoostedJackpot
                : collect([]);


            $lotteriesBoostedJackpot = $this->filterBoostedJackpotModifierService->execute(
                $lotteriesBoostedJackpot);

            [$lotteriesBoostedJackpot, $jackpot, $countValuesActive] =
                $this->filterBoostedJackpotExceedLimitService->execute(
                $lotteriesBoostedJackpot, $item);

            $item->lotteriesBoostedJackpot = $lotteriesBoostedJackpot;
            if ($item->insure_boosted_jackpot) {
                $item->insure_boosted_jackpot = $lotteriesBoostedJackpot->count() !== 0;
            }

            if($item->limit_max_jackpot < $jackpot){
                $item->insure_boosted_jackpot = false;
            }

            if($countValuesActive === 0){
                $item->insure_boosted_jackpot = false;
            }

            return $item;
        };
    }
}
