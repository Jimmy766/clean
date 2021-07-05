<?php

    namespace App\Core\Lotteries\Models;

    use App\Core\Lotteries\Models\LiveLotterySubscription;
    use App\Core\Lotteries\Transforms\LiveLotterySubscriptionPickTransformer;
    use Illuminate\Database\Eloquent\Model;



    class LiveLotterySubscriptionPick extends Model {
        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $primaryKey = 'pck_id';
        protected $table = 'subscriptions_picks';
        public $timestamps = false;
        public $transformer = LiveLotterySubscriptionPickTransformer::class;


        /**
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function subscription() {
            return $this->belongsTo(LiveLotterySubscription::class, 'sub_id', 'sub_id');
        }

        /**
         * @return \Illuminate\Support\Collection
         */
        public function getPicksAttribute() {
            $picks = collect([]);
            $lot_balls = $this->subscription->lottery->lot_balls;
            for ($i = 1; $i <= $lot_balls; $i++) {
                $ball = 'pck_'.$i;
                $picks->push($this->$ball);
            }
            return $picks;
        }
    }
