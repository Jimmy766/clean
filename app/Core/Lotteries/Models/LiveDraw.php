<?php

    namespace App\Core\Lotteries\Models;

    use App\Core\Lotteries\Models\LiveLottery;
    use App\Core\Rapi\Models\LiveSoldByDraw;
    use App\Core\Rapi\Transforms\LiveDrawTransformer;
    use DateInterval;
    use DateTime;
    use DateTimeZone;
    use Exception;
    use Illuminate\Database\Eloquent\Model;


    class LiveDraw extends Model
    {
        const CREATED_AT = 'draw_regdate';
        const UPDATED_AT = 'draw_lastupdate';
        const ACTIVE_STATUS_ID = 0;
        const FUTURE_STATUS_ID = 2;
        const PAST_STATUS_ID = 1;
        const VALID_TIME_LIMIT = 3;
        const DEFAULT_VIDEO_TIME = 50;
        public $transformer = LiveDrawTransformer::class;
        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $primaryKey = 'draw_id';
        protected $table = 'draws';
        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'draw_date',
            'draw_time',
            'draw_external_id',
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
            'draw_external_id',
            'draw_time',
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
        private $video_src_config = [
            43 => 'https://us.dvr1.amberbox.eu/bs-gen-q3/ntsc/',
            44 => 'https://us.dvr1.amberbox.eu/bs-gen-q4/ntsc/',
            45 => 'https://us.dvr1.amberbox.eu/bs-gen-q3/ntsc/',
        ];

        /**
         * @return string
         */
        public function getDrawDateDisplayAttribute() {
            $tz_display = new DateTimeZone($this->lottery->getTzDisplay());
            $date = new DateTime($this->draw_date . ' ' . $this->draw_time, new DateTimeZone(date_default_timezone_get()));
            $date->setTimezone($tz_display);
            return $date->format('Y-m-d');
        }

        /**
         * @return string
         */
        public function getDrawTimeDisplayAttribute() {
            $tz_display = new DateTimeZone($this->lottery->getTzDisplay());
            $date = new DateTime($this->draw_date . ' ' . $this->draw_time, new DateTimeZone(date_default_timezone_get()));
            $date->setTimezone($tz_display);
            return $date->format('H:i:00');
        }

        /**
         * @return string
         */
        public function getDrawTimeZoneDisplayAttribute() {
            $tz_display = new DateTimeZone($this->lottery->getTzDisplay());
            $date = new DateTime($this->draw_date . ' ' . $this->draw_time, new DateTimeZone(date_default_timezone_get()));
            $date->setTimezone($tz_display);
            return $date->format('T');
        }

        /**
         * @return string
         * @throws Exception
         */
        public function getDrawFullDateDisplayAttribute() {
            $tz_display = new DateTimeZone($this->lottery->getTzDisplay());
            $date = new DateTime($this->draw_date . ' ' . $this->draw_time, new DateTimeZone(date_default_timezone_get()));
            $date->setTimezone($tz_display);
            $gap = $this->lottery->draw_gap;
            //set seconds to 00
            $date->sub(new DateInterval('PT' . $gap . 'S'));
            return $date->format('Y-m-d H:i:s');
        }

        /**
         * @return int
         * @throws Exception
         */
        public function getTimestampAttribute() {
            $date = new DateTime($this->draw_date . ' ' . $this->draw_time, new DateTimeZone(date_default_timezone_get()));
            $gap_video = $this->lottery->video_gap;
            $gap = $this->lottery->draw_gap;
            //set seconds to 00
            $date->sub(new DateInterval('PT' . $gap . 'S'));
            //add interval to show video
            $date->add(new DateInterval('PT' . $gap_video . 'S'));
            return $date->getTimestamp();
        }

        /**
         * @return \Illuminate\Support\Collection
         */
        public function getVideoUrlAttribute() {
            $timestamp = $this->timestamp; // poner bien las url desde el tipo user agent
            $video_url = [
                'defaults' => $this->lot_id >=43 && $this->lot_id <= 44 ? $this->video_src_config[ $this->lot_id ] . "Manifest-" . $timestamp . "-" . self::DEFAULT_VIDEO_TIME . ".mpd" : '',
                'ios' => $this->lot_id >=43 && $this->lot_id <= 44 ? $this->video_src_config[ $this->lot_id ] . "index-" . $timestamp . "-" . self::DEFAULT_VIDEO_TIME . ".m3u8" : '',
            ];
            return collect($video_url);
        }

        public function lottery() {
            return $this->belongsTo(LiveLottery::class, 'lot_id', 'lot_id');
        }

        public function getLotBallsAttribute() {
            $lot_ballss = collect([]);
            $lot_balls = $this->lottery->lot_balls;
            for ($i = 1; $i <= $lot_balls; $i++) {
                $ball = 'draw_ball' . $i;
                $lot_ballss->push($this->$ball);
            }
            return $lot_ballss;
        }

        public function getExtraBallsAttribute() {
            $extra_balls = collect([]);
            $lot_balls = $this->lottery->lot_balls;
            $lot_extra = $this->lottery->lot_extra;
            for ($i = $lot_balls + 1; $i <= $lot_extra + $lot_balls; $i++) {
                $ball = 'draw_ball' . $i;
                $extra_balls->push($this->$ball);
            }
            return $extra_balls;
        }

        /**
         * @return bool
         */
        public function isValid() {
            return in_array($this->draw_status, self::validStatus());
        }

        /**
         * @return array
         */
        public static function validStatus() {
            return [self::ACTIVE_STATUS_ID, self::FUTURE_STATUS_ID];
        }

        public function getResultsAttribute() {
            return $this->has_results() ? [
                'pick_balls' => $this->lot_balls,
                'extra_balls' => $this->extra_balls,
            ] : -1;
        }

        public function has_results() {
            return $this->draw_status == self::PAST_STATUS_ID;
        }

        public function exposures() {
            return $this->hasMany(LiveSoldByDraw::class, 'draw_id', 'draw_id');
        }
    }
