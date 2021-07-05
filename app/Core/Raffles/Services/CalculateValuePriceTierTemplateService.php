<?php

namespace App\Core\Raffles\Services;

use App\Core\Raffles\Models\Raffle;
use App\Core\Raffles\Models\RaffleDraw;
use App\Core\Raffles\Models\RaffleTierTemplate;
use Illuminate\Support\Collection;

/**
 * Class CalculateValuePriceTierTemplateService
 * @package App\Services
 */
class CalculateValuePriceTierTemplateService
{

    public function execute(Collection $raffles)
    {
        $raffles = $raffles->map($this->mapGetRelationsTransform());

        return $raffles;
    }

    private function mapGetRelationsTransform(): callable
    {
        return function (Raffle $item, $key) {
            /** @var \App\Core\Raffles\Models\RaffleDraw $dates */
            $dates      = $item->getRelation('datesResultRaffles');
            $raffleTier = null;
            if (is_null($dates)) {
                return $item;
            }

            $rffId = $dates->rff_id;
            $raffleTier = $item->getRelation('datesResultRaffles')
                ->getRelation('raffleTier');

            if (is_null($raffleTier)) {
                return $item;
            }
            $raffleTierTemplates = $item->getRelation('datesResultRaffles')
                ->getRelation('raffleTier')
                ->getRelation('raffleTierTemplates');

            $raffleTierTemplates = $raffleTierTemplates->map(
                $this->calculateValueRaffleTierTemplateTransform($rffId)
            );

            $item->getRelation('datesResultRaffles')
                ->getRelation('raffleTier')
                ->setRelation('raffleTierTemplates', $raffleTierTemplates);

            return $item;
        };
    }

    /**
     * @param $rffId
     * @return callable
     */
    private function calculateValueRaffleTierTemplateTransform($rffId): callable
    {
        return static function (RaffleTierTemplate $item, $key) use ($rffId) {
            $item->value = $item->evaluate($rffId);
            return $item;
        };
    }

}
