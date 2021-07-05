<?php

    namespace App\Core\Carts\Models;

    use App\Core\Carts\Transforms\CartLiveLotterySubscriptionPickTransformer;
    use Illuminate\Database\Eloquent\Model;

    class CartLiveLotterySubscriptionPick extends Model {
        public $timestamps = false;
        public $transformer = CartLiveLotterySubscriptionPickTransformer::class;
        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $primaryKey = 'ctpck_id';
        protected $table = 'cart_subscriptions_picks';
        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'cts_id',
            'ctpck_1',
            'ctpck_2',
            'ctpck_3',
            'ctpck_4',
            'ctpck_5',
            'ctpck_6',
            'ctpck_7',
            'ctpck_8',
            'ctpck_9',
            'ctpck_10',
            'ctpck_11',
            'ctpck_12',
            'cts_wheel_picked_balls',
            'cts_wheel_picked_extras',
        ];

        /**
         * The attributes that should be hidden for arrays.
         *
         * @var array
         */
        protected $visible = [
            'ctpck_id',
            'cts_id',
            'ctpck_1',
            'ctpck_2',
            'ctpck_3',
            'ctpck_4',
            'ctpck_5',
            'ctpck_6',
            'ctpck_7',
            'ctpck_8',
            'ctpck_9',
            'ctpck_10',
            'ctpck_11',
            'ctpck_12',
            'cts_wheel_picked_balls',
            'cts_wheel_picked_extras',
        ];

        public function cart_subscription() {
            return $this->belongsTo(CartLiveLotterySubscription::class, 'cts_id', 'cts_id');
        }


        /**
         * @return \Illuminate\Support\Collection
         */
        public function getPicksAttribute() {
            $picks = collect([]);
            $lot_balls = $this->cart_subscription->lottery->lot_balls;
            for ($i = 1; $i <= $lot_balls; $i++) {
                $ball = 'ctpck_'.$i;
                $picks->push($this->$ball);
            }
            return $picks;
        }

        public function setPicks($picks) {
            for ($i = 1; $i <= 12; $i++) {
                $this->{"ctpck_" . $i} = 0;
            }
            foreach ($picks as $k => $pick) {
                $this->{"ctpck_" . ($k+1)} = $pick;
            }
        }
    }
