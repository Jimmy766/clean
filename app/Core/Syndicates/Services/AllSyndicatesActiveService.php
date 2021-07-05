<?php

namespace App\Core\Syndicates\Services;

use App\Core\Syndicates\Models\Syndicate;
use App\Core\Base\Traits\ApiResponser;
use Illuminate\Support\Collection;

/**
 * Class AllSyndicatesActiveService
 * @package App\Services
 */
class AllSyndicatesActiveService
{

    use ApiResponser;

    /**
     * @return Collection
     */
    public function execute(): Collection
    {

        $relations = [
            'syndicate_prices.syndicate_price_lines',
            'syndicate_prices.lottery_time_draws',
            'syndicate_lotteries.lottery.draws',
            'syndicate_lotteries.draws.lottery',
            'syndicate_lottery.draws.lottery',
            'syndicate_prices.syndicate.syndicate_lotteries.lottery',
            'routingFriendly',
        ];
        $idProducts = self::client_syndicates(1)
            ->pluck('product_id');
        return Syndicate::query()
            ->with($relations)
            ->where('active', '=', 1)
            ->whereIn( 'id', $idProducts )
            ->getFromCache();

    }

}
