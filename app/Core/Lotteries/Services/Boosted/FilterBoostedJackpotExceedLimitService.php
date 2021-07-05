<?php

namespace App\Core\Lotteries\Services\Boosted;

use App\Core\Base\Classes\ModelConst;
use App\Core\Rapi\Models\Draw;
use App\Core\Lotteries\Models\Lottery;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class FilterBoostedJackpotExceedLimitService
 * @package App\Services
 */
class FilterBoostedJackpotExceedLimitService
{

    /**
     * @var CalculateMountsBoostedJackpotService
     */
    private $calculateMountsBoostedJackpotService;

    public function __construct(CalculateMountsBoostedJackpotService  $calculateMountsBoostedJackpotService)
    {
        $this->calculateMountsBoostedJackpotService = $calculateMountsBoostedJackpotService;
    }

    /**
     * @param Collection $lotteriesBoostedJackpot
     * @return array|Collection
     */
    public function execute(Collection $lotteriesBoostedJackpot, Lottery $lottery)
    {
        $countValuesActive = 0;
        $jackpotLottery = $this->getJackpotNowOrPrevious($lottery);

        [
            $lotteriesBoostedJackpot,
            $countValuesActive,
        ] = $this->calculateMountsBoostedJackpotService->execute(
            $lotteriesBoostedJackpot,
            $lottery,
            $jackpotLottery,
            $countValuesActive
        );

        return [$lotteriesBoostedJackpot, $jackpotLottery, $countValuesActive];
    }

    /**
     * @param \App\Core\Lotteries\Models\Lottery $lottery
     * @return int|mixed
     */
    public function getJackpotNowOrPrevious(Lottery $lottery)
    {
        $lotteriesBoostedJackpot = $lottery->lotteriesBoostedJackpot;
        $jackpotLottery          = $lottery->jackpot_usd;
        $insureBoostedJackpot    = $lottery->insure_boosted_jackpot;

        if ($lotteriesBoostedJackpot->count() > 0 && $jackpotLottery < 0 && $insureBoostedJackpot === true) {
            $date         = Carbon::now()
                ->toDateString();
            $lotteryDraws = Draw::query()
                ->where('lot_id', $lottery->lot_id)
                ->orderByDesc('draw_id')
                ->where('draw_jackpot', '>', 0)
                ->whereRaw("DATE_FORMAT(draw_date, '%m-%d') <= DATE_FORMAT('{$date}', '%m-%d')")
                ->limit(2)
                ->getFromCache([ 'draw_jackpot' ]);

            $draw = null;

            if($lotteryDraws->count() === 1){
                $draw = $lotteryDraws->first();
            }
            if($lotteryDraws->count() > 1){
                $draw = $lotteryDraws->pop();
            }

            if ( !empty($draw)) {
                $drawJackpot = $draw->draw_jackpot;
                $drawJackpot += ModelConst::LOTTERY_SUM_PREVIOUS_MILLION_JACKPOT;
                return $drawJackpot;
            }

            return 0;
        }

        return $jackpotLottery;
    }
}
