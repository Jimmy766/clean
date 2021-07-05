<?php

    namespace App\Core\Memberships\Models;

    use App\Core\Memberships\Models\MembershipPrice;
    use App\Core\Memberships\Transforms\MembershipPriceLineTransform;
    use Illuminate\Database\Eloquent\Model;


    class MembershipPriceLine extends Model {
        protected $guarded=[];
        public $connection = 'mysql_external';
        protected $primaryKey = 'prcln_id';
        protected $table = 'memberships_prices_line';
        public $timestamps = false;
        public $transformer = MembershipPriceLineTransform::class;

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'prcln_id',
            'prcln_price',
            'prc_free_credit',
            'prcln_country_list_enabled',
            'prcln_country_list_disabled',
        ];

        /**
         * The attributes that should be visible for arrays.
         *
         * @var array
         */
        protected $visible = [
            'prcln_id',
            'prcln_price',
            'prc_free_credit',
            'prcln_country_list_enabled',
            'prcln_country_list_disabled',
        ];

        public function price() {
            return $this->belongsTo(MembershipPrice::class, 'prc_id', 'prc_id');
        }

        public function getCountryListEnabledAttribute() {
            return $this->prcln_country_list_enabled === '0' ? [] : explode(',', $this->prcln_country_list_enabled);
        }

        public function getCountryListDisabledAttribute() {
            return $this->prcln_country_list_disabled === '0' ? [] : explode(',', $this->prcln_country_list_disabled);
        }
    }
