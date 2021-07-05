<?php

namespace App\Core\Lotteries\Models;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Models\CoreModel;
use App\Core\Lotteries\Models\LotteryModifier;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LotteriesBoostedJackpotPricesLine extends CoreModel
{
    public    $connection = 'mysql_external';
    protected $table      = 'lotteries_boosted_jackpot_prices_line';


    public function lotteriesModifier(): HasMany
    {
        return $this->hasMany(
            LotteryModifier::class,
            'modifier_id',
            'modifier_id'
        )
            ->join(
                'prices_line as pl',
                'lotteries_boosted_jackpot_prices_line.prcln_id',
                '=',
                'pl.prcln_id'
            )
            ->where('pl.curr_code', '=', request()->country_currency)
            ->where('modifier_visible', ModelConst::ENABLED)
            ->where('active', ModelConst::ENABLED);
    }
}


