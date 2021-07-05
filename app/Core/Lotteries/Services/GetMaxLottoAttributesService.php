<?php

namespace App\Core\Lotteries\Services;

use App\Core\Base\Classes\ModelConst;
use App\Core\Lotteries\Models\Lottery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetMaxLottoAttributesService
{

    public static function execute($lottery): ?Model
    {
        if($lottery->lot_id !== ModelConst::MAX_LOTTO_ID_LOTTERY ){
            return null;
        }


        $raw = DB::raw('lotteries.lot_id, MIN(draw_date) AS draw_date, lotteries.curr_code,
    IF(ce.exch_factor IS NOT NULL, ce.exch_factor * draw_jackpot, draw_jackpot) as usd_jackpot, draw_jackpot');
        $columns = [$raw];
        $arrayIdLotteries = [ 6, 25, 26, 1000 ];
        return Lottery::query()
            ->join('draws as d', 'lotteries.lot_id', '=', 'd.lot_id')
            ->join('currencies as c', 'lotteries.curr_code', '=', 'c.curr_code')
            ->leftJoin('currency_exchange as ce', function($join){
                $join->on('lotteries.curr_code', '=', 'ce.curr_code_from')
                    ->where('ce.curr_code_to', ModelConst::CURR_CODE_USD)
                    ->where('ce.active', ModelConst::NUMBER_ONE)
                ;
            })
            ->where('draw_status', ModelConst::NUMBER_ZERO)
            ->where('lotteries.lot_active', ModelConst::NUMBER_ONE)
            ->whereNotIn('lotteries.lot_id', $arrayIdLotteries)
            ->whereRaw('TIME_TO_SEC(TIMEDIFF(ADDTIME(d.draw_date, d.draw_time), NOW())) > TIME_TO_SEC(d.draw_time_process)')
            ->groupBy(['d.lot_id'])
            ->orderBy('usd_jackpot', 'desc')
            ->firstFromCache($columns)
        ;
    }
}
