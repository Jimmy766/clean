<?php

namespace App\Core\FreeSpin\Services;

use App\Core\FreeSpin\Models\CasinoFreeSpinsUser;
use Illuminate\Support\Facades\DB;

/**
 * Class CountBonusFreeSpinByUserService
 * @package App\Services
 */
class CountBonusFreeSpinUseService
{

    /**
     * @param $idCasinoFreeSpins
     * @return int
     */
    public function execute( $idCasinoFreeSpins): int
    {
        $casinoFreeSpinsUser = CasinoFreeSpinsUser::query()
            ->where('casino_freespins_id', $idCasinoFreeSpins)
            ->get([ DB::raw('count(*) as count_free_spins'), ]);

        $casinoFreeSpinUser = $casinoFreeSpinsUser->first();

        return $casinoFreeSpinUser === null ? 0 : $casinoFreeSpinUser->count_free_spins;
    }

}
