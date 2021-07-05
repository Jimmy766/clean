<?php

namespace App\Core\Syndicates\Models;

use App\Core\Lotteries\Models\LotteryTimeDraw;
use App\Core\Syndicates\Models\Syndicate;
use App\Core\Syndicates\Models\SyndicatePriceLine;
use App\Core\Syndicates\Transforms\SyndicatePriceTransformer;
use Illuminate\Database\Eloquent\Model;


class SyndicatePrice extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'prc_id';
    public $timestamps = false;
    public $transformer = SyndicatePriceTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'syndicate_id',
        'sys_id',
        'prc_time',
        'prc_time_type',
        'active',
        'prc_share_cost'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'prc_id',
        'syndicate_id',
        'sys_id',
        'prc_time',
        'prc_time_type',
        'active',
        'prc_share_cost'
    ];

    public function syndicate_price_lines() {
        $currency = request('country_currency');
        return $this->hasMany(SyndicatePriceLine::class, 'prc_id', 'prc_id')
            ->where('curr_code','=', $currency);
    }

    public function getSyndicatePriceLineAttribute() {
        $country_id = request('client_country_id');
        $syndicate_price_line = $this->syndicate_price_lines->filter(function ($item, $key) use ($country_id) {
            return (!empty($item->country_list_disabled) && !in_array($country_id, $item->country_list_disabled))
                || (!empty($item->country_list_enabled) && in_array($country_id, $item->country_list_enabled))
                || (empty($item->country_list_enabled) && empty($item->country_list_disabled));
        })->first();
        return $syndicate_price_line ? $syndicate_price_line->transformer::transform($syndicate_price_line): null;
    }

    public function syndicate() {
        return $this->belongsTo(Syndicate::class, 'syndicate_id', 'id');
    }

    public function getTimeTypeAttribute() {
        return $this->prc_time_type == 0 ? ($this->prc_time > 1 ? trans('lang.months') : trans('lang.month')) :
            ($this->prc_time > 1 ? trans('lang.weeks') : trans('lang.week'));
    }

    public function lottery_time_draws() {
        return $this->hasMany(LotteryTimeDraw::class, 'price_id', 'prc_id');
    }

    public function getDrawsAttribute() {
        $syndicate = $this->syndicate ? $this->syndicate : null;
        if ($syndicate->multi_lotto == 0) {
            $syndicate_lottery = $syndicate ? $syndicate->syndicate_lotteries->isNotEmpty() ? $syndicate->syndicate_lotteries->first() : null: null;
            $lottery_time_draw = $syndicate_lottery ? $this->lottery_time_draws->where('lot_id', '=', $syndicate_lottery->lot_id)->first() : null;
            return $lottery_time_draw ? $lottery_time_draw->prc_draws : null;
        }
        return 0;
    }
}
