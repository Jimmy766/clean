<?php

namespace App\Core\Raffles\Services;

use App\Core\Base\Services\OrcaService;
use App\Core\Raffles\Models\Raffle;
use App\Core\Base\Services\ClientService;
use App\Core\Base\Traits\ApiResponser;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Class AllRafflesActiveService
 * @package App\Services
 */
class AllRafflesActiveService
{

    use ApiResponser;
    use ValidatesRequests;

    /**
     * @param $request
     * @return Collection
     * @throws ValidationException
     */
    public function execute($request): Collection
    {

        if(\App\Core\Base\Services\ClientService::isOrca()) {
            $rules = [
                'agent_id' => 'required'
            ];
            $this->validate($request, $rules);
            return OrcaService::getRaffles();
        }
        $idProducts = self::client_raffles(1)
            ->pluck('product_id');
        $relations = [ 'raffle_prices.price_lines', 'draw_active', 'routingFriendly' ];
        $raffles = Raffle::query()
            ->with($relations)
            ->where('inf_raffle_mx', '=', 0)
            ->whereIn('inf_id', $idProducts)
            ->getFromCache();
        $raffles = $raffles->filter(function (Raffle $item) {
            return $item->draw_active;
        });
        return $raffles->sortBy('date');

    }

}
