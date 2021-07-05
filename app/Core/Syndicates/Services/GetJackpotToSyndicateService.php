<?php

namespace App\Core\Syndicates\Services;

use App\Core\Base\Traits\CartUtils;

class GetJackpotToSyndicateService
{

    use CartUtils;

    /**
     * @param $syndicate
     * @param $typeForceJackpot
     * @return array
     */
    public function execute( $syndicate, $typeForceJackpot = '' ): ?array
    {
        $date = null;
        $currCode = request()['country_currency'];
        $jackpotCalculate = 0;
        if ($syndicate->multi_lotto == 1) {
            $currCodes = collect([]);
            $dates = collect([]);
            $syndicateLotteries = $syndicate->syndicate_lotteries->unique('lot_id');
            $syndicateLotteries->each(function ($item, $key) use ( $typeForceJackpot, &$jackpotCalculate, $currCodes, $dates) {
                $currCodeLottery = $item->lottery ? $item->lottery->curr_code : '';
                $currCodes->push($currCodeLottery);
                $activeDraw = $item->active_draw ? $item->active_draw : null;
                $dates->push($activeDraw->draw_dates);
                $currCodeRequest = $typeForceJackpot === 'USD' ? 'USD' : request()[ 'country_currency'];
                $resultDraw = 0;
                if ($currCodeRequest != $currCodeLottery) {
                    $factor = $this->convertCurrency($currCodeLottery, $currCodeRequest);
                    $resultDraw = $factor * $activeDraw->draw_jackpot;
                } else {
                    $resultDraw = $activeDraw->draw_jackpot;
                }
                $jackpotCalculate += $activeDraw ? $resultDraw : 0;
            });
            $date = $dates->min();
           return [$jackpotCalculate, $date, $currCode];
        } else {
            $syndicate_lottery = $syndicate->syndicate_lottery ? $syndicate->syndicate_lottery : null;
            $currCode = $syndicate_lottery ? $syndicate_lottery->lottery ? $syndicate_lottery->lottery->curr_code : null : null;
            $activeDraw = $syndicate_lottery->active_draw;
            $date = $activeDraw->draw_dates;
            $jackpotCalculate = $syndicate_lottery ? $activeDraw ? $activeDraw->draw_jackpot : null : null;
            return [$jackpotCalculate, $date, $currCode];
        }

    }

}
