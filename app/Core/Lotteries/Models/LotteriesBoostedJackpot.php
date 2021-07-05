<?php

namespace App\Core\Lotteries\Models;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Models\CoreModel;
use App\Core\Base\Services\SetTransformToModelOrCollectionService;
use App\Core\Lotteries\Models\LotteryModifier;
use App\Core\Lotteries\Transforms\LotteryBoostedJackPotTransformer;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class LotteriesBoostedJackpot
 * @package App
 */
class LotteriesBoostedJackpot extends CoreModel
{
    protected $table      = 'lotteries_boosted_jackpot';
    public    $connection = 'mysql_external';

    public $transformer = LotteryBoostedJackPotTransformer::class;

    public function lotteriesModifierO(): HasMany
    {
        return $this->hasMany(LotteryModifier::class, 'lot_id', 'lot_id')
            ->where('modifier_visible', ModelConst::ENABLED)
            ->where('active', ModelConst::ENABLED);
    }

        public function getLotteriesModifierAttributesAttribute()
        {
            $collection = $this->lotteriesModifier;
            $model = $collection->first();

            return SetTransformToModelOrCollectionService::execute($model);
        }


    public function lotteriesModifier(): HasMany
    {
        return $this->hasMany(LotteryModifier::class, 'lot_id', 'lot_id')
            ->join(
                'lotteries_boosted_jackpot_prices_line as lbjpl',
                'lotteries_modifiers.modifier_id',
                '=',
                'lbjpl.modifier_id'
            )
            ->join(
                'prices_line as pl',
                'lbjpl.prcln_id',
                '=',
                'pl.prcln_id'
            )
            ->where('pl.curr_code', '=', request()->country_currency)
            ->where('modifier_visible', ModelConst::ENABLED)
            ->where('active', ModelConst::ENABLED);
    }

}


