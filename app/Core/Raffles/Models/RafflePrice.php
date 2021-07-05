<?php

namespace App\Core\Raffles\Models;

use App\Core\Raffles\Models\Raffle;
use App\Core\Raffles\Models\RafflePriceLine;
use App\Core\Raffles\Transforms\RafflePriceTransformer;
use Illuminate\Database\Eloquent\Model;

class RafflePrice extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'prc_rff_id';
    public $timestamps = false;
    protected $table = 'prices_raffles';
    public $transformer = RafflePriceTransformer::class;

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

    public function price_lines() {
        $currency = request('country_currency');
        return $this->hasMany(RafflePriceLine::class, 'prc_rff_id', 'prc_rff_id')
            ->where('curr_code','=', $currency);
    }

    public function raffle() {
        return $this->belongsTo(Raffle::class, 'inf_id', 'inf_id');
    }

    public function getPriceLineAttribute() {
        $country_id = request('client_country_id');
        $price_line = $this->price_lines->filter(function ($item, $key) use ($country_id) {
            return (!empty($item->country_list_disabled) && !in_array($country_id, $item->country_list_disabled)) //no esta en los disabled
                || (!empty($item->country_list_enabled) && in_array($country_id, $item->country_list_enabled)) // esta en los enabled
                || (empty($item->country_list_enabled) && empty($item->country_list_disabled)); //esta para todos
        })->first();
        return $price_line ? $price_line->transformer::transform($price_line): null;
    }

}
