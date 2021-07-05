<?php

namespace App\Core\Carts\Services;

use App\Core\Base\Classes\DirtyQuery;
use Illuminate\Support\Facades\DB;

class GetCrtTotalCartServices
{

    public static function execute($cart)
    {
        $sql  = DirtyQuery::getQueryCartLotterySubscription($cart);
        $totalLotterySubscription = DB::connection('mysql_external')
            ->select($sql);
        $totalLotterySubscription = $totalLotterySubscription[ 0 ];
        $totalLotterySubscription = $totalLotterySubscription->total;

        $sql  = DirtyQuery::getQueryCartSyndicateSubscription($cart);
        $totalSyndicateSubscription = DB::connection('mysql_external')
            ->select($sql);
        $totalSyndicateSubscription = $totalSyndicateSubscription[ 0 ];
        $totalSyndicateSubscription = $totalSyndicateSubscription->total;

        $sql  = DirtyQuery::getQueryCartMemberShipsSubscription($cart);
        $totalMemberShipsSubscription = DB::connection('mysql_external')
            ->select($sql);
        $totalMemberShipsSubscription = $totalMemberShipsSubscription[ 0 ];
        $totalMemberShipsSubscription = $totalMemberShipsSubscription->total;

        $sql  = DirtyQuery::getQueryCartRafflesSubscription($cart);
        $totalRaffleSubscription = DB::connection('mysql_external')
            ->select($sql);
        $totalRaffleSubscription = $totalRaffleSubscription[ 0 ];
        $totalRaffleSubscription = $totalRaffleSubscription->total;

        $sql  = DirtyQuery::getQueryCartSyndicateRafflesSubscription($cart);
        $totalSyndicateRaffleSubscription = DB::connection('mysql_external')
            ->select($sql);
        $totalSyndicateRaffleSubscription = $totalSyndicateRaffleSubscription[ 0 ];
        $totalSyndicateRaffleSubscription = $totalSyndicateRaffleSubscription->total;

        $sql  = DirtyQuery::getQueryCartScratchesSubscription($cart);
        $totalScratchesSubscription = DB::connection('mysql_external')
            ->select($sql);
        $totalScratchesSubscription = $totalScratchesSubscription[ 0 ];
        $totalScratchesSubscription = $totalScratchesSubscription->total;

        return $totalLotterySubscription + $totalSyndicateSubscription + $totalMemberShipsSubscription + $totalRaffleSubscription + $totalSyndicateRaffleSubscription + $totalScratchesSubscription;
    }

}
