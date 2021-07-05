<?php

    namespace App\Core\ScratchCards\Models;

    use App\Core\ScratchCards\Models\ScratchCardPrice;
    use App\Core\ScratchCards\Transforms\ScratchCardPriceLineTransformer;
    use Illuminate\Database\Eloquent\Model;


    class ScratchCardPriceLine extends Model
    {
        public $timestamps = false;
        public $transformer = ScratchCardPriceLineTransformer::class;
        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $primaryKey = 'prcln_id';
        protected $table = 'scratches_prices_line';


        public function price() {
            return $this->belongsTo(ScratchCardPrice::class, 'prc_id', 'prc_id');
        }

        /**
         * @return array
         */
        public function getCountryListEnabledAttribute() {
            return $this->prcln_country_list_enabled === '0' ? [] : explode(',', $this->prcln_country_list_enabled);
        }

        /**
         * @return array
         */
        public function getCountryListDisabledAttribute() {
            return $this->prcln_country_list_disabled === '0' ? [] : explode(',', $this->prcln_country_list_disabled);
        }
    }
