<?php

namespace App\Core\ScratchCards\Services;

use App\Core\ScratchCards\Models\ScratchCard;
use App\Core\Base\Traits\ApiResponser;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Collection;

/**
 * Class AllScratchCardsActiveService
 * @package App\Services
 */
class AllScratchCardsActiveService
{

    use ApiResponser;
    use ValidatesRequests;

    public function execute(): Collection
    {
        $relations = [ 'ticket_price', 'prices.prices_lines', 'paytables' ];

        $idsProducts = self::client_scratch_cards(1)
            ->pluck('product_id');

        return ScratchCard::query()
            ->with($relations)
            ->where('active', '=', 1)
            ->whereIn('id', $idsProducts)
            ->join('scratches_ticket_price as stp', 'stp.scratches_id', '=', 'scratches.id')
            ->where('stp.curr_code', request()->country_currency)
            ->orderBy('id')
            ->getFromCache();
    }

}
