<?php

namespace App\Core\Lotteries\Services\Boosted;

use App\Core\Lotteries\Models\LotteriesBoostedJackpot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class CheckLotteriesNotExceedLimitJackpotService
 * @package App\Services
 */
class FilterBoostedJackpotModifierService
{

    /**
     * @param $lotteriesBoostedJackpot
     * @return mixed
     */
    public function execute($lotteriesBoostedJackpot)
    {

        $lotteriesBoostedJackpot = $lotteriesBoostedJackpot->filter(
            $this->filterLotteriesModifierNullTransform()
        );

        return $lotteriesBoostedJackpot->map(
            $this->mapLotteriesBoostedJackpotGetRelationLotteriesModifierTransform()
        );

    }

    private function mapLotteriesBoostedJackpotGetRelationLotteriesModifierTransform(): callable
    {
        return function (LotteriesBoostedJackpot $item) {
            $lotteriesModifier = $item->lotteriesModifier;
            if (is_a($lotteriesModifier, Model::class)) {
                return $item;
            }

            if ($lotteriesModifier === null) {
                return $item;
            }

            $lotteriesModifier = $lotteriesModifier->filter(
                $this->filterOnlyItemMatchByModifierTransform($item)
            );

            $lotteriesModifier = $lotteriesModifier->unique('modifier_id');
            /** @var Collection $item */
            $item->lotteriesModifier = $lotteriesModifier->values();
            return $item;
        };
    }

    /**
     * @param $lotteryBoostedJackpot
     * @return callable
     */
    private function filterOnlyItemMatchByModifierTransform($lotteryBoostedJackpot): callable
    {
        return static function ($item) use ($lotteryBoostedJackpot) {
            return $item->modifier_id === $lotteryBoostedJackpot->modifier_id;
        };
    }

    private function filterLotteriesModifierNullTransform(): callable
    {
        return static function ($item) {
            $lotteriesModifier = $item->lotteriesModifier;
            if(is_null($lotteriesModifier)){
                return false;
            }

            return $lotteriesModifier->count() > 0;
        };
    }

}
