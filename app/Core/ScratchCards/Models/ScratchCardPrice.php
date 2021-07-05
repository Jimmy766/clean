<?php

    namespace App\Core\ScratchCards\Models;

    use App\Core\ScratchCards\Models\ScratchCardPriceLine;
    use App\Core\ScratchCards\Transforms\ScratchCardPriceTransformer;
    use Illuminate\Database\Eloquent\Model;


    class ScratchCardPrice extends Model
    {
        public $timestamps = false;
        public $transformer = ScratchCardPriceTransformer::class;
        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $primaryKey = 'prc_id';
        protected $table = 'scratches_prices';


        /**
         * @return \Illuminate\Database\Eloquent\Relations\hasMany
         */
        public function prices_lines() {
            $currency = request('country_currency');
            return $this->hasMany(ScratchCardPriceLine::class,'prc_id','prc_id')
                ->where('curr_code','=', $currency);
        }

        public function getPriceLineAttribute() {
            $country_id = request('client_country_id');
            $price_line = $this->prices_lines->filter(function ($item) use ($country_id) {
                return (!empty($item->country_list_disabled) && !in_array($country_id, $item->country_list_disabled)) //no esta en los disabled
                    || (!empty($item->country_list_enabled) && in_array($country_id, $item->country_list_enabled)) // esta en los enabled
                    || (empty($item->country_list_enabled) && empty($item->country_list_disabled)); //esta para todos
            })->first();
            return $price_line ? $price_line->transformer::transform($price_line): null;
        }

        public function getPriceAttribute() {
            return $this->price_line ? $this->price_line['price']: null;
        }
        public function getCurrencyAttribute() {
            return $this->price_line ? $this->price_line['currency']: null;
        }
    }
