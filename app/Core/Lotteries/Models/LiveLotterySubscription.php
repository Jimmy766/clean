<?php

    namespace App\Core\Lotteries\Models;

    use App\Core\Carts\Models\CartLiveLotterySubscription;
    use App\Core\Lotteries\Models\LiveLottery;
    use App\Core\Lotteries\Models\LiveDraw;
    use App\Core\Lotteries\Models\LiveLotterySubscriptionPick;
    use App\Core\Lotteries\Models\LiveLotteryTicket;
    use App\Core\Lotteries\Models\LotteryModifier;
    use App\Core\Lotteries\Transforms\LiveLotterySubscriptionTransformer;
    use Illuminate\Database\Eloquent\Model;


    class LiveLotterySubscription extends Model {
        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $primaryKey = 'sub_id';
        protected $table = 'subscriptions';
        public $timestamps = false;
        public $transformer = LiveLotterySubscriptionTransformer::class;

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'sub_id',
            'lot_id',
            'sub_buydate',
            'pck_type',
            'modifier_1',
        ];

        /**
         * The attributes that should be visible for arrays.
         *
         * @var array
         */
        protected $visible = [
            'sub_id',
            'lot_id',
            'sub_buydate',
            'pck_type',
            'modifier_1',
        ];

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasOne
         */
        public function modifier() {
            return $this->hasOne(LotteryModifier::class, 'modifier_id', 'modifier_1');
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasOne
         */
        public function ticket() {
            return $this->hasOne(LiveLotteryTicket::class, 'sub_id', 'sub_id');
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasOne
         */
        public function draw() {
            return $this->hasOne(LiveDraw::class, 'draw_id', 'sub_lastdraw_id');
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasOne
         */
        public function subscription_picks() {
            return $this->hasOne(LiveLotterySubscriptionPick::class, 'sub_id', 'sub_id');
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasOne
         */
        public function cart_subscription() {
            return $this->hasOne(CartLiveLotterySubscription::class, 'cts_id', 'cts_id');
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function lottery() {
            return $this->belongsTo(LiveLottery::class, 'lot_id', 'lot_id');
        }

        /**
         * @return string
         */
        public function getStatusAttribute() {
            if ($this->sub_status != 2 && ($this->sub_tickets > $this->sub_emitted) && ($this->draw && $this->draw->isValid())) {
                return trans('lang.active_subscription');
            }
            if ((($this->sub_tickets - $this->sub_emitted) == 0 || $this->sub_status == 2) && ($this->draw && !$this->draw->isValid())) {
                return trans('lang.expired_subscription');
            }
            return '';
        }

        /**
         * @return string
         */
        public function getStatusTagAttribute() {
            if ($this->sub_status != 2 && ($this->sub_tickets > $this->sub_emitted && (!$this->draw || $this->draw->isValid()))) {
                return trans('lang.active_subscription_tag');
            }
            if ((($this->sub_tickets - $this->sub_emitted) == 0 || $this->sub_status == 2) && ($this->draw && !$this->draw->isValid())) {
                return trans('lang.expired_subscription_tag');
            }
            return '';
        }

        public function getPickTypeTextAttribute() {
            if ($this->pck_type != 3) {
                return trans('lang.quick_pick');
            } else {
                return trans('lang.user_pick');
            }
        }

        /**
         * @return array
         */
        public function getPicksAttribute() {
            $pick_type = [];
            if ($this->pck_type == 3) {
                $pick_type = $this->subscription_picks ? $this->subscription_picks->transformer ? $this->subscription_picks->transformer::transform($this->subscription_picks) : $this->subscription_picks : null;
            }
            return $pick_type;
        }

        /**
         * @return mixed
         */
        public function getModifierAttributesAttribute() {
            return $this->modifier ? $this->modifier->transformer ? $this->modifier->transformer::transform($this->modifier) : $this->modifier : null;
        }

        /**
         * @return mixed
         */
        public function getDrawAttributesAttribute() {
            return $this->draw ? $this->draw->transformer ? $this->draw->transformer::transform($this->draw) : $this->draw : null;
        }

        /**
         * @return mixed
         */
        public function getTicketAttributesAttribute() {
            return $this->ticket ? $this->ticket->transformer ? $this->ticket->transformer::transform($this->ticket) : $this->ticket : null;
        }
    }
