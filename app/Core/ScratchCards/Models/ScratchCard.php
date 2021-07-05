<?php

    namespace App\Core\ScratchCards\Models;

    use App\Core\Base\Models\CoreModel;
    use App\Core\Casino\Services\PariplayIntegration;
    use App\Core\Cloud\Services\GetCloudUrlService;
    use App\Core\Base\Services\GetOriginRequestService;
    use App\Core\Cloud\Services\SetOriginCloudUrlService;
    use App\Core\Base\Traits\LogCache;
    use App\Core\ScratchCards\Models\ScratchCardPayTable;
    use App\Core\ScratchCards\Models\ScratchCardPrice;
    use App\Core\ScratchCards\Models\ScratchCardTicketPrice;
    use App\Core\ScratchCards\Transforms\ScratchCardTransformer;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;

    class ScratchCard extends CoreModel
    {
        use LogCache;
        public $timestamps = false;
        public $transformer = ScratchCardTransformer::class;
        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $primaryKey = 'id';
        protected $table = 'scratches';
        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'id', 'name', 'tag_name', 'gamecode_desktop', 'gamecode_mobile', 'odds', 'cards_quantity', 'branding_type', 'game_languages', 'order_ltk', 'order_tri',
        ];

        /**
         * The attributes that should be hidden for arrays.
         *
         * @var array
         */
        protected $visible = [
            'id', 'name', 'tag_name', 'gamecode_desktop', 'gamecode_mobile', 'odds', 'cards_quantity', 'branding_type', 'game_languages', 'order_ltk', 'order_tri',
        ];

        /**
         * @return \Illuminate\Support\Collection
         */
        public static function getActivesScratches() {
            $currency = request('country_currency');
            return self::where('active', '=', 1)->get()->filter(function ($item) use ($currency) {
                if ($item->ticket_price->curr_code == $currency)
                    return $item;
            });
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\hasOne
         */
        public function ticket_price() {
            return $this->hasOne(ScratchCardTicketPrice::class, 'scratches_id')->where('curr_code', '=', request()->country_currency);
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\hasMany
         */
        public function prices() {
            $sys_id = request('client_sys_id');
            return $this->hasMany(ScratchCardPrice::class, 'scratches_id')
                ->where('active', '=', 1)
                ->where('sys_id', '=', $sys_id);
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\hasMany
         */
        public function paytables() {
            return $this->hasMany(ScratchCardPayTable::class, 'scratches_id')
                ->where('curr_code', '=', request()->country_currency)
                ->orderBy('paytable_tier');
        }

        public function getPaytableAttributeAttribute() {
            $paytables = collect([]);
            $this->paytables->each(function($item) use ($paytables) {
                $paytables->push($item->transformer::transform($item));
            });
            return $paytables;
        }

        public function getMaxWinAttribute() {
            return $this->paytables->max('paytable_prize');
        }

        /**
         * @return array
         */
        public function getLanguagesAttribute() {
            return explode(',', $this->game_languages);
        }

        /**
         * @return string
         */
        public function getNameTagAttribute() {
            return "#{$this->tag_name}_NAME#";
        }

        /**
         * @return string
         */
        public function getInfoTagAttribute() {
            return "#{$this->tag_name}_INFO#";
        }
        /**
         * @return string
         */
        public function getGameCode($is_mobile) {
            return $is_mobile ? $this->gamecode_mobile : $this->gamecode_desktop;
        }

        /**
         * @return string
         */
        public function getDemoUrlAttribute() {
            $token    = request()->header('authorization');
            $token    = explode(" ", $token);
            $token    = $token[ 1 ];
            $token    = base64_encode($token);
            $ip       = request()->user_ip;
            $url = $this->getUrl() . "/scratch_cards/?id={$this->id}&game_mode=demo&user_ip={$ip}&t={$token}";
            return SetOriginCloudUrlService::execute($url);
        }

        public function realPlayUrl($id) {
            $token    = request()->header('authorization');
            $token    = explode(" ", $token);
            $token    = $token[ 1 ];
            $token    = base64_encode($token);
            $ip       = request()->user_ip;
            $url = $this->getUrl() . "/scratch_cards/?id={$id}&game_mode=real_play&user_ip={$ip}&t={$token}";
            return SetOriginCloudUrlService::execute($url);
        }

        public function getUrl() {
            return GetCloudUrlService::execute();
        }

        /**
         * @return integer
         */
        public function getOrderAttribute() {
            $sys_id = request('client_sys_id');
            switch ($sys_id) {
                case 1:
                    $order = 'order_tri';
                    break;
                case 5:
                    $order = 'order_ltk';
                    break;
                default:
                    $order = 'order_tri';
            }
            return $this->{$order};
        }

        /**
         * @return \Illuminate\Support\Collection
         */
        public function getPricesListAttribute() {
            $prices = collect([]);
            $this->prices->each(function (ScratchCardPrice $item) use ($prices) {
                $prices->push($item->transformer ? $item->transformer::transform($item) : $item);
            });
            return $prices;
        }

        public function srcDemo($is_mobile, $language) {
            $this->record_log('pariplay', "Call demo game");
            return PariplayIntegration::prepareResponse(PariplayIntegration::getResponsePlayUrl($this, $is_mobile, $language));
        }
    }
