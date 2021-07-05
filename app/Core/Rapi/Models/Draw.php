<?php

namespace App\Core\Rapi\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Base\Traits\Utils;
use App\Core\Lotteries\Models\LiveLottery;
use App\Core\Lotteries\Models\Lottery;
use App\Core\Raffles\Models\DrawResultRaffle;
use App\Core\Rapi\Transforms\DrawTransformer;
use App\Core\Lotteries\Transforms\LotteryResultTransformer;
use DateTime;
use DateTimeZone;
use function foo\func;

class Draw extends CoreModel
{
    use Utils;


    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'draw_id';
    const CREATED_AT = 'draw_regdate';
    const UPDATED_AT = 'draw_lastupdate';
    public $transformer = DrawTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'draw_date',
        'draw_time',
        'draw_jackpot',
        'draw_jackpot_cash',
        'draw_ball1',
        'draw_ball2',
        'draw_ball2',
        'draw_ball3',
        'draw_ball4',
        'draw_ball5',
        'draw_ball6',
        'draw_ball7',
        'draw_ball8',
        'draw_ball9',
        'draw_ball10',
        'draw_ball11',
        'draw_ball12',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'draw_id',
        'draw_date',
        'draw_time',
        'draw_jackpot',
        'draw_jackpot_cash',
        'draw_ball1',
        'draw_ball2',
        'draw_ball2',
        'draw_ball3',
        'draw_ball4',
        'draw_ball5',
        'draw_ball6',
        'draw_ball7',
        'draw_ball8',
        'draw_ball9',
        'draw_ball10',
        'draw_ball11',
        'draw_ball12',

    ];

    public function getDrawDatesAttribute() {
        $date_now = new \DateTime();
        $date_next = new \DateTime();
        $time = explode(':', $this->draw_time);
        $date_next->setTime($time[0], $time[1], $time[2]);
        $date_next->add(new \DateInterval("P{$this->next_draw_date()}D"));
        $time_process = explode(':', $this->draw_time_process);
        $existKeys = array_key_exists(0, $time_process) && array_key_exists(1, $time_process) &&
            array_key_exists(2, $time_process);
        if($existKeys === false){
            return null;
        }
        $process_interval = new \DateInterval("PT{$time_process[0]}H{$time_process[1]}M{$time_process[2]}S");
        $date_next->sub($process_interval);

        $date_actual = new \DateTime($this->draw_date.' '.$this->draw_time);
        $date_actual->sub($process_interval);

        return $date_actual >= $date_now ? $date_actual->format('Y-m-d H:i:s') : $date_next->format('Y-m-d H:i:s');
    }

    /**
     * @return string
     */
    public function getLiveLotteryDrawDateDisplayAttribute() {
        $tz_display = new DateTimeZone($this->live_lottery->getTzDisplay());
        $date = new DateTime($this->draw_date . ' ' . $this->draw_time, new DateTimeZone(date_default_timezone_get()));
        $date->setTimezone($tz_display);
        return $date->format('Y-m-d');
    }

    public function lottery() {
        return $this->belongsTo(Lottery::class, 'lot_id', 'lot_id');
    }

    public function live_lottery() {
        return $this->belongsTo(LiveLottery::class, 'lot_id', 'lot_id');
    }

    public function next_draw_date() {
        $lottery = $this->lottery;
        $days = [
            0 => $lottery->lot_sun,
            1 => $lottery->lot_mon,
            2 => $lottery->lot_tue,
            3 => $lottery->lot_wed,
            4 => $lottery->lot_thu,
            5 => $lottery->lot_fri,
            6 => $lottery->lot_sat,
        ];
        $cont = 0;
        for ($i = date('w')+1; $i<7; $i++) {
            $cont++;
            if ($days[$i] == 1) {
                return $cont;
            }
        }
        for ($i = 0; $i<=date('w'); $i++) {
            $cont++;
            if ($days[$i] == 1) {
                return $cont;
            }
        }
        return $cont;
    }

    public function getLotBallsAttribute() {
        $lottery = $this->lottery;
        $lot_ballss = collect([]);
        $lot_balls = $lottery->lot_balls;
        if ($lottery->lot_id === 14 && $this->draw_id <= 9764) {
            $lot_balls = 5;
        }
        if ($lottery->lot_id === 15 && $this->draw_id <= 10166) {
            $lot_balls = 6;
        }
        for ($i = 1; $i <= $lot_balls; $i++) {
            $ball = 'draw_ball'.$i;
            $lot_ballss->push($this->$ball);
        }
        return $lot_ballss;
    }

    public function getExtraBallsAttribute() {
        $lottery = $this->lottery;
        $extra_balls = collect([]);
        $lot_balls = $lottery->lot_balls;
        $lot_extra = $lottery->lot_extra;
        if ($lottery->lot_id === 14 && $this->draw_id <= 9764) {
            $lot_balls = 5;
            $lot_extra = 1;
        }
        if ($lottery->lot_id === 15 && $this->draw_id <= 10166) {
            $lot_balls = 6;
            $lot_extra = 2;
        }
        for ($i = $lot_balls + 1; $i <= $lot_extra + $lot_balls; $i++) {
            $ball = 'draw_ball'.$i;
            $extra_balls->push($this->$ball);
        }
        return $extra_balls;
    }

    public function getRefundBallsAttribute() {
        $lottery = $this->lottery;
        $refund_balls = collect([]);
        $lot_balls = $lottery->lot_balls;
        $lot_extra = $lottery->lot_extra;
        if ($lottery->lot_id === 14 && $this->draw_id <= 9764) {
            $lot_balls = 5;
            $lot_extra = 1;
        }
        if ($lottery->lot_id === 15 && $this->draw_id <= 10166) {
            $lot_balls = 6;
            $lot_extra = 2;
        }
        for ($i = $lot_balls + $lot_extra + 1; $i <= $lot_extra + $lot_balls + $lottery->lot_reintegro; $i++) {
            $ball = 'draw_ball'.$i;
            $refund_balls->push($this->$ball);
        }
        return $refund_balls;
    }

    public function raffles() {
        return $this->hasMany(DrawResultRaffle::class, 'draw_id', 'draw_id');
    }

    public function getRafflesAttributesAttribute() {
        $lottery = $this->lottery;
        $raffles = collect([]);
        if ($lottery->lot_raffle_number == 1 && ($this->lot_id != 8 || ($this->draw_id < 39014 && $this->lot_id == 8))) {
            $this->raffles->each(function ($item) use ($raffles) {
                $raffles->push($item->transformer::transform($item));
            });
        }
        return $raffles;
    }

    public function has_results() {
        $lot_balls = $this->lottery->lot_balls;
        for ($i = 1; $i <= $lot_balls; $i++) {
            $ball = 'draw_ball'.$i;
            if ($this->$ball != 0) {
                return true;
            }
        }
        return false;
    }

    public function getJackpotChangeAttribute() {
        $lottery = $this->lottery ? $this->lottery : null;
        return $lottery ? $lottery->jackpot_change : null;
    }

    public function getDrawLotteryAttribute() {
        $lottery = $this->lottery ? $this->lottery : null;
        $lottery->transformer = LotteryResultTransformer::class;
        return $lottery ? $lottery->transformer::transform($lottery) : null;
    }

    public function getLotteryNameAttribute() {
        $lottery = $this->lottery ? $this->lottery : null;
        return $lottery ? $lottery->name : null;
    }
}
