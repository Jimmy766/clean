<?php


namespace App\Core\Base\Services;


use App\Core\Raffles\Models\Raffle;
use App\Core\Syndicates\Models\SyndicateRaffle;
use App\Core\Rapi\Services\Log;
use App\Core\Telem\Models\TelemUserSystem;
use Illuminate\Support\Collection;

class OrcaService
{


    public static function getSyndicateRaffles()
    {
        $agent_id = request()->agent_id;
        $agent = TelemUserSystem::findOrFail($agent_id);

        Log::record_log("access", "PROD_AVAILABLES_SRAFFLE: ( id $agent_id) ". $agent->raffles());


        if($agent->hasSyndicateRafflesAvailable()){
            return SyndicateRaffle::with(['syndicate_raffle_raffles', 'syndicate_raffle_raffles.raffle', 'syndicate_raffle_raffles.raffle.draw_active'])
                ->whereIn('id', explode(",", $agent->syndicateRaffles()))
                ->get();
        }

        return new Collection();
    }

    public static function getRaffles()
    {
        $agent_id = request()->agent_id;
        $agent = TelemUserSystem::findOrFail($agent_id);

        Log::record_log("access", "PROD_AVAILABLES_RAFFLE: ( id $agent_id) ". $agent->raffles());


        if($agent->hasRafflesAvailable()){


            return Raffle::with(['raffle_prices.price_lines', 'draw_active'])
                ->whereIn('inf_id', explode(",", $agent->raffles()))
                ->get()
                ->filter(function (Raffle $item) {
                    return $item->draw_active;
                })
                ->sortBy('date');

        }

        return new Collection();
    }
}
