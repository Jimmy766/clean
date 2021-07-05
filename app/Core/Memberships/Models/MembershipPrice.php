<?php

    namespace App\Core\Memberships\Models;

    use App\Core\Memberships\Models\Membership;
    use App\Core\Memberships\Models\MembershipPriceLine;
    use App\Core\Memberships\Transforms\MembershipPriceTransform;
    use Illuminate\Database\Eloquent\Model;


    class MembershipPrice extends Model {

        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $primaryKey = 'prc_id';
        protected $table = 'memberships_prices';
        public $timestamps = false;
        public $transformer = MembershipPriceTransform::class;
        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'lot_id', 'sys_id', 'prc_draws', 'prc_time', 'prc_time_type', 'prc_min_tickets', 'prc_min_jackpot',
        ];

        /**
         * The attributes that should be hidden for arrays.
         *
         * @var array
         */
        protected $visible = [
            'prc_id', 'lot_id', 'sys_id', 'prc_draws', 'prc_time', 'prc_time_type', 'prc_min_tickets', 'prc_min_jackpot', 'price_line',
        ];

        public function price_lines() {
            $currency = request('country_currency');
            return $this->hasMany(MembershipPriceLine::class, 'prc_id', 'prc_id')
                ->where('curr_code', '=', $currency);
        }

        public function membership() {
            return $this->belongsTo(Membership::class, 'lot_id', 'lot_id');
        }

        public function price_line_country_check(){
            $country_id = request('client_country_id');
            return $price_line = $this->price_lines->filter(function ($item, $key) use ($country_id) {
                return (!empty($item->prcln_country_list_disabled) && !in_array($country_id, $item->prcln_country_list_disabled)) //no esta en los disabled
                    || (!empty($item->prcln_country_list_enabled) && in_array($country_id, $item->prcln_country_list_enabled)) // esta en los enabled
                    || (empty($item->prcln_country_list_enabled) && empty($item->prcln_country_list_disabled)); //esta para todos
            })->first();
        }

        public function getPriceLineAttribute() {
            $price_line = $this->price_line_country_check();
            return $price_line ? $price_line->transformer::transform($price_line) : null;
        }
    }
