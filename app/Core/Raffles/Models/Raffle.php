<?php

namespace App\Core\Raffles\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Base\Services\ClientService;
use App\Core\Base\Services\SetTransformToModelOrCollectionService;
use App\Core\Base\Traits\CartUtils;
use App\Core\Raffles\Models\RaffleDraw;
use App\Core\Raffles\Models\RafflePrice;
use App\Core\Raffles\Models\RaffleTier;
use App\Core\Rapi\Models\RoutingFriendly;
use App\Core\Raffles\Transforms\RaffleTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Raffle extends CoreModel
{
    use CartUtils;
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'inf_id';
    public $timestamps = false;
    protected $table = 'raffle_info';
    public $transformer = RaffleTransformer::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [

    ];

    public function routingFriendly() {
        return $this->hasOne(RoutingFriendly::class, 'element_id', 'inf_id')
            ->where('element_type', RoutingFriendly::ELEMENT_RAFFLE)
            ->where('lang', app()->getLocale())
            ->where('sys_id', request()->client_sys_id);
    }

    public function getRoutingFriendlyAttributesAttribute() {
        return SetTransformToModelOrCollectionService::execute($this->routingFriendly);
    }

    public function raffle_draws() {
        return $this->hasMany(RaffleDraw::class, 'inf_id', 'inf_id');
    }

    // refactor this
    public function active_draw() {
        return $this->hasOne(RaffleDraw::class, 'inf_id', 'inf_id')
            ->where('rff_status', '=', 1)
            ->where('rff_view', '=', 1);
    }

    public function draw_active() {
        return $this->hasOne(RaffleDraw::class, 'inf_id', 'inf_id')->where('rff_status', '=', 1)->where('rff_view', '=', 1);
    }

    public function getNameAttribute() {
        return $this->inf_tag;
    }

    public function getDrawExtraIdAttribute() {
        $active_draw = $this->draw_active;
        return $active_draw ? $active_draw->rff_extra_id : null;
    }

    public function getDrawCurrCodeAttribute() {
        $active_draw = $this->draw_active;
        return $active_draw ? $active_draw->curr_code : null;
    }

    public function getJackpotAttribute() {
        $active_draw = $this->draw_active;
        return $active_draw ? $active_draw->rff_jackpot : null;
    }

    public function getDateAttribute() {
        $active_draw = $this->draw_active;
        return $active_draw ? $active_draw->rff_playdate : null;
    }

    public function raffle_prices() {
        $sys_id = ClientService::getSystem(request()->oauth_client_id);
        return $this->hasMany(RafflePrice::class, 'inf_id', 'inf_id')
            ->where('active', '=', 1)
            ->where('sys_id', '=', $sys_id);
    }

    public function getPricesAttribute() {
        $prices = collect([]);
        $this->raffle_prices->each(function ($item) use ($prices) {
            $price = $item->transformer ? $item->transformer::transform($item) : $item;
            $prices->push($price);
        });
        return $prices;
    }

    public function getDrawIdAttribute() {
        return $this->draw_active ? $this->draw_active->rff_id : null;
    }

    public function getTypeTagAttribute() {
        return $this->inf_type_tag != '' ? '#'.$this->inf_type_tag.'#' : null;
    }

    public function getJackpotUsdAttribute() {
        $active_draw = $this->draw_active;
        if ($active_draw) {
            if($this->curr_code != 'USD') {
                $factor = $this->convertCurrency($this->curr_code, 'USD');
                return (integer)($active_draw->rff_jackpot * $factor);
            }
            return $active_draw->rff_jackpot;
        } else {
            return -1;
        }
    }

    public function dates() {
        return $this->raffle_draws()
            ->where('rff_status', '!=', 0)
            ->whereHas('raffle_tier_results')
            ->get()
            ->sortByDesc('rff_playdate')
            ->pluck('rff_playdate', 'rff_id')
            ->values();
    }

    /**
     * @return Builder|HasOne
     */
    public function datesResultRaffles() {
        return $this->hasOne(RaffleDraw::class, 'inf_id', 'inf_id')
            ->whereHas('raffle_tier_results')
            ->where('rff_view', 0)
            ->where('rff_status', 2)
            ->whereHas('raffleTier')
            ->orderByDesc('rff_playdate')
            ;
    }

    public function raffle_tiers() {
        return $this->hasMany(RaffleTier::class, 'inf_id', 'inf_id');
    }

}
