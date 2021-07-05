<?php

namespace App\Core\Rapi\Models;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Services\SetTransformerService;
use App\Core\Base\Services\TranslateTextService;
use App\Core\Lotteries\Models\Lottery;
use App\Core\Rapi\Transforms\PriceLineBoostedTransformer;
use App\Core\Rapi\Transforms\PriceTransformer;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'prc_id';
    public $timestamps = false;
    public $transformer = PriceTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lot_id',
        'sys_id',
        'prc_draws',
        'prc_time',
        'prc_time_type',
        'prc_min_tickets',
        'prc_min_jackpot',
        'prc_days_by_tickets',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'prc_id',
        'lot_id',
        'sys_id',
        'prc_draws',
        'prc_time',
        'prc_time_type',
        'prc_min_tickets',
        'prc_min_jackpot',
        'price_line',
        'prc_model_type',
        'prc_days_by_tickets',
    ];

    public function price_lines()
    {
        $currency = request('country_currency');
        return $this->hasMany(PriceLine::class, 'prc_id', 'prc_id')
            ->leftJoin(
                'lotteries_boosted_jackpot_prices_line as lbjpl',
                'prices_line.prcln_id',
                '=',
                'lbjpl.prcln_id'
            )
            ->leftJoin(
                'lotteries_modifiers as lm',
                function ($join) {
                    $join->on('lm.modifier_id', '=', 'lbjpl.modifier_id')
                        ->where('modifier_visible', ModelConst::ENABLED)
                        ->where('active', ModelConst::ENABLED);
                },
                '',
                ''
            )
            ->where('curr_code', '=', $currency);
    }

    public function lottery() {
        return $this->belongsTo(Lottery::class, 'lot_id', 'lot_id');
    }


    public function getPricesLinesAttributesAttribute(): array
    {
        $pricesLines = $this->price_lines->where('lot_id',$this->lot_id);
        $setTransformerObjet = new SetTransformerService();
        return $setTransformerObjet->execute($pricesLines->values(),
                                             PriceLineBoostedTransformer::class);
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

    public function getTimeTypeAttribute() {
        if ($this->prc_time == 0) {
            return null;
        }

        if ($this->prc_time_type == 1) {
            if ($this->prc_time == 1) {
                return TranslateTextService::execute('week');
            }
            return TranslateTextService::execute('weeks');
        }

        if ($this->prc_time_type == 0) {
            if ($this->prc_time == 1) {
                return TranslateTextService::execute('month');
            }
            return TranslateTextService::execute('months');
        }

        return null;
    }
}
