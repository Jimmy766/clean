<?php

    namespace App\Core\Lotteries\Models;

    use App\Core\Base\Traits\Utils;
    use App\Core\Lotteries\Models\Bet;
    use App\Core\Lotteries\Models\LiveDraw;
    use App\Core\Lotteries\Models\LotteryExtraInfo;
    use App\Core\Lotteries\Models\LotteryLevel;
    use App\Core\Lotteries\Models\LotteryModifier;
    use App\Core\Lotteries\Transforms\LiveLotteryTransformer;
    use DateTime;
    use DateTimeZone;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use DB;


    class LiveLottery extends Model
    {
        use Utils;

        public $timestamps = false;
        public $transformer = LiveLotteryTransformer::class;
        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $primaryKey = 'lot_id';
        protected $table = 'lotteries';

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'lot_id',
            'lot_name_en',
            'curr_code',
            'lot_balls',
            'lot_extra',
            'lot_pick_balls',
            'lot_pick_extra',
            'lot_maxNum',
            'lot_extra_maxNum',
            'lot_extra_startNum',
        ];

        /**
         * The attributes that should be visible for arrays.
         *
         * @var array
         */
        protected $visible = [
            'lot_id',
            'lot_name_en',
            'curr_code',
            'lot_balls',
            'lot_extra',
            'lot_pick_balls',
            'lot_pick_extra',
            'lot_maxNum',
            'lot_extra_maxNum',
            'lot_extra_startNum',
        ];
        private $tz_display = 'America/Nassau';

        /**
         * Espacio de tiempo para los videos
         *
         * @var array
         */
        private $video_gap_config = [
            43 => '0',
            44 => '37',
            45 => '0',
        ];
        /**
         * Espacio de tiempo para los draw
         *
         * @var array
         */
        private $draw_gap_config = [
            43 => '15',
            44 => '52',
            45 => '0',
        ];

        /**
         * Configuracion de
         *
         * @var array
         */
        private $modifiers_boxed_config = [
            43 => [
                8 => [],  // Three different numbers
                21 => [2], // boxed 3 Two numbers are the same
            ],
            44 => [
                10 => [], //Four different numbers
                22 => [2], // boxed 12  Two numbers are the same
                23 => [2, 2], // boxed 6 Two pairs of equal numbers
                24 => [3], // boxed 4  Three equal numbers
            ],
            45 => [
                12 => [],
                13 => [2], // boxed 60 Two numbers are the same
                14 => [2, 2], // boxed 30 Two pairs of equal numbers
                15 => [3], // boxed 20 Three equal numbers
                16 => [2, 3], // boxed 10 Two numbers are the same and three equal numbers
                17 => [4], // boxed 5 Four equal numbers
                18 => [],
                19 => [],
                20 => [],
            ],
        ];
        private $streaming_config = [
            'defaults' => [
                43 => 'https://us.dvr1.amberbox.eu/bs-gen-q3/ntsc/Manifest.mpd',
                44 => 'https://us.dvr1.amberbox.eu/bs-gen-q4/ntsc/Manifest.mpd',
                45 => 'https://us.dvr1.amberbox.eu/bs-gen-q5/ntsc/Manifest.mpd',
            ],
            'ios' => [
                43 => 'https://us.dvr1.amberbox.eu/bs-gen-q3/ntsc/index.m3u8',
                44 => 'https://us.dvr1.amberbox.eu/bs-gen-q4/ntsc/index.m3u8',
                45 => 'https://us.dvr1.amberbox.eu/bs-gen-q3/ntsc/index.m3u8',
            ],
        ];

        const DRAWS_BY_DAY = 48;

        /**
         * @return array
         */
        public function getModifiersBoxed(): array {
            return isset($this->modifiers_boxed_config[ $this->lot_id ]) ? array_keys($this->modifiers_boxed_config[ $this->lot_id ]) : [];
        }

        /**
         * @return integer
         */
        public function getVideoGapAttribute(): int {
            return $this->video_gap_config[ $this->lot_id ] ?? 0;
        }
        /**
         * @return integer
         */
        public function getDrawGapAttribute(): int {
            return $this->draw_gap_config[ $this->lot_id ] ?? 0;
        }

        /**
         * All live lotteries started in 1
         *
         * @return integer
         */
        public function startNumAttribute(): int {
            return 1;
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasMany
         */
        public function levels() {
            return $this->hasMany(LotteryLevel::class, 'lot_id', 'lot_id')->where('lol_order', '=', 1);
        }

        public function bets() {
            return $this->hasMany(Bet::class, 'lot_id', 'lot_id');
        }

        public function extra_info() {
            return $this->belongsTo(LotteryExtraInfo::class, 'lot_id', 'lot_id');
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasMany
         */
        public function modifiers() {
            return $this->hasMany(LotteryModifier::class, 'lot_id', 'lot_id');
        }

        /**
         * @return \Illuminate\Support\Collection
         */
        public function getModifiersListAttribute() {
            $modifiers = collect([]);
            $this->modifiers->where('modifier_visible', '=', 1)->each(function ($item) use ($modifiers) {
                $modifiers->push($item->transformer ? $item->transformer::transform($item) : $item);
            });
            return $modifiers;
        }

        /**
         * @return \Illuminate\Support\Collection
         */
        public function getStreamingUrlAttribute() {
            $streaming = [
                'defaults' => $this->streaming_config[ 'defaults' ][ $this->lot_id ],
                'ios' => $this->streaming_config[ 'ios' ][ $this->lot_id ],
            ];
            return collect($streaming);
        }

        /**
         * @return \Illuminate\Support\Collection
         */
        public function getHiddenModifiersListAttribute() {
            $modifiers = collect([]);
            $this->modifiers->where('modifier_visible', '=', 0)->each(function ($item) use ($modifiers) {
                $modifiers->push($item->transformer ? $item->transformer::transform($item) : $item);
            });
            return $modifiers;
        }

        /**
         * @return \Illuminate\Support\Collection
         */
        public function getModifierBalls() {
            return $this->modifiers->where('mod_balls', '!=', $this->lot_pick_balls);
        }

        public function getBetAttribute() {
            $bet = $this->active_bet->first();
            return $bet ? $bet->transformer ? $bet->transformer::transform($bet) : $bet : null;
        }

        /**
         * @return mixed
         */
        public function active_bet() {
            return $this->bets()
                ->where('active', '=', 1)
                ->where('curr_code', '=', request()->country_currency);
        }

        /**
         * @return mixed
         */
        public function getLotExtraInfoAttribute() {
            return $this->extra_info;
        }

        /**
         * @return string
         */
        public function getLotNameAttribute() {
            $name = 'lot_name_'.$this->getLanguage();
            return $this->$name ? $this->$name : $this->lot_name_en;
        }

        public function valid_draws_play() {
            return $this->draws()
                ->where(function (Builder $query) {
                    $query->whereRaw(DB::raw("DATE_SUB(concat(draw_date,' ',draw_time), INTERVAL " . LiveDraw::VALID_TIME_LIMIT . " MINUTE) > now()"));
                })
                ->whereIn('draw_status', LiveDraw::validStatus())
                ->orderBy('draw_date')
                ->orderBy('draw_time')
                ->orderBy('draw_external_id')
                ->limit(self::DRAWS_BY_DAY);
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasMany
         */
        public function draws() {
            return $this->hasMany(LiveDraw::class, 'lot_id', 'lot_id');
        }

        /**
         * @return mixed
         */
        public function getValidDrawsPlay() {
            return $this->valid_draws_play;
        }

        /**
         * @param $result_date
         *
         * @return \Illuminate\Support\Collection
         * @throws \Exception
         */
        public function result($result_date) {
            if ($result_date) {   //save, verify if insert or edit
                $day = new DateTime($result_date . ' 00:00:00', $this->getTimeZoneDisplay());
            } else {
                $day = new DateTime(date('Y-m-d') . ' 00:00:00', $this->getTimeZoneDisplay());
            }
            $hours = 'PT23H31M';
//             tener en cuenta los dias de cambios de horario
//            if ($result_date == '2017-11-05' || $result_date == '2018-03-11'){
//                $hours = 'PT24H31M';
//            }
            $day->setTimezone(new DateTimeZone(date_default_timezone_get()));
            $start_day = $day->format('Y-m-d H:i:s');
            $end_day = $day->add(new \DateInterval($hours))->format('Y-m-d H:i:s');

            return $this->draws->where('draw_status', 1)->filter(function ($value) use ($start_day, $end_day) {
                $date = $value->draw_date . ' ' . $value->draw_time;
                return $date > $start_day && $date < $end_day;
            })->sortbyDesc('draw_full_date_display');
        }

        public function getTimeZoneDisplay() {
            return new DateTimeZone($this->getTzDisplay());
        }

        /**
         * @return string
         */
        public function getTzDisplay(): string {
            return $this->tz_display;
        }

        /**
         * @param $pikcs
         * @return int
         */
        public function findRealModifierId($picks) {
            $mod_id = null;
            if (isset($this->modifiers_boxed_config[ $this->lot_id ])) {
                $boxedConfig = $this->modifiers_boxed_config[ $this->lot_id ];
                $scores = $this->countRepeatNumbers($picks);
                foreach ($boxedConfig as $m => $config) {
                    $indexs = [];
                    $count = count($config);
                    if (count($config) != count($scores)) {
                        continue;
                    }
                    foreach ($scores as $score) {
                        if (false !== ($index = array_search($score, $config))) {
                            array_splice($config, $index, 1);
                            array_push($indexs, $index);
                        }
                    }
                    if ($count == count($indexs)) {
                        $mod_id = $m;
                        break;
                    }
                }
            }
            return $mod_id;
        }

        /**
         * @param $pikcs
         *
         * @return \Illuminate\Support\Collection
         */
        private function countRepeatNumbers($pikcs) {
            $_repeats = [];
            foreach ($pikcs as $p) {
                if (!isset($_repeats[ $p ])) {
                    $_repeats[ $p ] = 0;
                }
                $_repeats[ $p ] += 1;
            }
            $repeats = collect($_repeats);
//            if is only one number and repeat in all balls
            if ($repeats->count() === 1 && $repeats->first() === $this->lot_balls) {
                $repeats->pop();
            }
//            filter by more than one
            return $repeats->filter(function ($value) { return ($value != 1); });
        }

        public function getPermutations($elements, $first = true) {
            $all = array();
            if (count($elements) == 0) {
                return array('');
            }
            foreach ($elements as $index => $element) {
                $_elems = $elements;
                array_splice($_elems, $index, 1);
                $_permutations = $this->getPermutations($_elems, !$first);
                foreach ($_permutations as $permutation) {
                    if ($first) {
                        if (!in_array($element . $permutation, $all)) {
                            array_push($all, $element . $permutation);
                        }
                    }
                    else {
                        array_push($all, $element . $permutation);
                    }
                }
            }

            return $all;
        }
    }
