<?php

    namespace App\Core\Lotteries\Models;

    use App\Core\Lotteries\Models\LiveLotterySubscription;
    use App\Core\Lotteries\Models\LiveDraw;
    use App\Core\Lotteries\Transforms\LiveLotteryTicketTransformer;
    use Illuminate\Database\Eloquent\Model;


    class LiveLotteryTicket extends Model {

        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $primaryKey = 'tck_id';
        protected $table = 'tickets';
        public $timestamps = false;
        public $transformer = LiveLotteryTicketTransformer::class;

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasOne
         */
        public function draw() {
            return $this->hasOne(LiveDraw::class, 'draw_id', 'draw_id');
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function subscription() {
            return $this->belongsTo(LiveLotterySubscription::class, 'sub_id', 'sub_id');
        }

        /**
         * @return \Illuminate\Support\Collection
         */
        public function getBallsAttribute() {
            $balls = collect([]);
            $lot_balls = $this->subscription->lottery->lot_balls;
            for ($i = 1; $i <= $lot_balls; $i++) {
                $ball = 'tck_n' . $i;
                $balls->push($this->$ball);
            }
            return $balls;
        }

        /**
         * @return \Illuminate\Support\Collection
         */
        public function getExtraBallsAttribute() {
            $extra_balls = collect([]);
            $lot_balls = $this->subscription->lottery->lot_balls;
            $lot_extra = $this->subscription->lottery->lot_extra;
            for ($i = $lot_balls + 1; $i <= $lot_extra + $lot_balls; $i++) {
                $ball = 'tck_n' . $i;
                $extra_balls->push($this->$ball);
            }
            return $extra_balls;
        }

        /**
         * @return array
         */
        public function getModifierAttribute() {
            return $this->subscription ? $this->subscription->modifier_attributes : null;
        }

        /**
         * @return string
         */
        public function getStatusTagAttribute(){
            switch ($this->tck_status){
                case 1:
                case 5:
                    $status_tag = '#WINNINGS_DETAIL_PENDING_CLAIM#';
                    break;
                case 2:
                    $status_tag = '#WINNINGS_DETAIL_PENDING_APPROVAL#';
                    break;
                case 3:
                    $status_tag = '#WINNINGS_DETAIL_CHANGED#';
                    break;
                case 4:
                case 6:
                    $status_tag = '#WINNINGS_DETAIL_CREDIT#';
                    break;
            }
            return $status_tag;
        }

    }
