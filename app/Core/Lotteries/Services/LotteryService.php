<?php


namespace App\Core\Lotteries\Services;


use App\Core\Rapi\Services\Util;
use App\Core\Telem\Services\TelemService;

class LotteryService
{
    private static $instance = null;

    /**
     * LotteryService constructor.
     */
    private function __construct()
    {
    }

    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new LotteryService();
        }
        return self::$instance;
    }

    public function generateUserPicks($lottery, $ticket_byDraw)
    {
        $cant_picks = $ticket_byDraw*$lottery->slip_min_lines;

        $result=array();

        for($i=1; $i <= $cant_picks; $i++){
            $nums = $this->generateNums($lottery->lot_pick_balls,$lottery->lot_maxNum,1);
            sort($nums);
            if($lottery->lot_pick_extra > 0){
                $extra = $this->generateNums($lottery->lot_pick_extra, $lottery->lot_extra_maxNum,$lottery->lot_extra_startNum);
                sort($extra);
                $aux=[
                    implode(",", $nums),
                    implode(",", $extra)
                ];
            }else{
                $aux=[
                    implode(",", $nums),
                     []
                ];
            }
            //array_unshift($aux,'');
            $result["balls"][$i] = $aux[0];
            $result["extra"][$i] = $aux[1];
        }

        $balls = array_key_exists('balls', $result) === true ? $result[ "balls" ] : null;
        $extra = array_key_exists('extra', $result) === true ? $result[ "extra" ] : null;
        $result["balls"] = is_array($balls) ? array_values($balls) : $balls;
        $result["extra"] = is_array($extra) ? array_values($extra) : $extra;

        return $result;
    }

    function generateNums($balls,$maxNum,$start){
        $arr_result=array();
        for($i=1; $i<=$balls; $i++){
            $num='';
            $seguir	=	1;
            while ($seguir != 0){
                $num = rand($start,$maxNum);
                if(!$this->exists_num($num,$arr_result)){
                    $seguir=0;
                }
            }
            $arr_result[$i] = $num;
        }
        return $arr_result;
    }

    function exists_num($num,$arr_result){
        $exists=false;
        if(count($arr_result) >0 ){
            foreach($arr_result as $key => $value){
                if($value == $num){
                    $exists=true;
                }
            }
        }
        return $exists;
    }

    /**
     * Picks for lottery wheels
     * @param $pick_type
     * @param $pick_balls
     * @param $pick_extra_balls
     * @param $lottery
     * @param $wheel_info
     * @param $cts_ticket_byDraw
     * @return array|\Illuminate\Contracts\Translation\Translator|string|null
     */

    public static function wheelPicks($pick_type, $pick_balls, $pick_extra_balls, $lottery,
                                      $wheel_info, $cts_ticket_byDraw){

        if($pick_type == 3) {
            $lot_pick_balls = $lottery->lot_pick_balls;
            $lot_pick_extra = $lottery->lot_auto_pick_extra == 0 ? $lottery->lot_pick_extra : 0;
            $lot_extra_startNum = $lottery->lot_extra_startNum;


            $lot_maxNum = $lottery->lot_maxNum;
            $lot_extra_maxNum = $lottery->lot_extra_maxNum;
            $lot_name_en = $lottery->lot_name_en;

            /**
             * $wheel_info->wheel_type {1: full, 2: abreviado}
             */
            $pick_extra_to_check = ($wheel_info->wheel_type && $lot_pick_balls == $wheel_info->wheel_balls) ? 0 : $lot_pick_extra;


            if (count($pick_balls) != ($cts_ticket_byDraw * $lottery->slip_min_lines)) {
                return trans('lang.pick_ball_line_qty_count',
                    ["expected" => ($cts_ticket_byDraw * $lottery->slip_min_lines),
                        "got"=> count($pick_balls) . ". Ticket By Draw: $cts_ticket_byDraw - Min lines: $lottery->slip_min_lines " ]);
            }

            if ($pick_extra_to_check > 0 && (!isset($pick_extra_balls) ||
                    count($pick_extra_balls) <= 0 ||
                    (count($pick_extra_balls) != $cts_ticket_byDraw * $lottery->slip_min_lines))) {
                return trans('lang.extra_pick_ball_line_qty');
            }

            $balls = [];

            foreach ($pick_balls as $klines => $lines) {
                $picks = explode(",", $lines);

                /**
                 * No pueden haber duplicados
                 */
                if (Util::array_has_dupes($picks)) {
                    return trans('lang.distinct_pick_balls');
                }

                if (count($picks) != $wheel_info->wheel_balls) {
                    return trans('lang.pick_ball_line_qty_count', [
                        "expected" => $wheel_info->wheel_balls, "got" => count($picks)
                    ]);
                }


                foreach ($picks as $k => $pick) {
                    if ($pick == "")
                        $pick = 0;

                    if (!is_numeric($pick))
                        return trans('lang.pick_ball_only_integer');

                    if (($k + 1) <= $wheel_info->wheel_balls && $pick == 0) {
                        return trans('lang.pick_ball_zero');
                    } elseif (($k + 1) > $wheel_info->wheel_balls && $pick < $lot_extra_startNum) {
                        return trans('extra balls for ' . $lot_name_en . ' must be equal or bigger than ' . $lot_extra_startNum);
                    }

                    if ($pick > $lot_maxNum) {
                        return trans('lang.pick_ball_max');
                    }
                }

                $picks_extra = [];

                if(!empty($pick_extra_balls)) {

                    $picks_extra = explode(",", $pick_extra_balls[$klines]);

                    if (count($picks_extra) != $pick_extra_to_check) {
                        return trans('Wrong line qty. Got:' . count($picks_extra) . " expected: " . $pick_extra_to_check);

                    }

                    if (Util::array_has_dupes($picks_extra)) {
                        return trans('lang.distinct_pick_balls');
                    }

                    if (count($picks_extra) != $pick_extra_to_check) {
                        return trans('lang.extra_pick_ball_qty');
                    }

                    foreach ($picks_extra as $pick_extra) {
                        //CHEQUEO DE MAXIMOS
                        if ($pick_extra > $lot_extra_maxNum) {
                            return trans('lang.pick_ball_max');
                        }
                    }
                }

                /**
                 * Ordenamos
                 */
                sort($picks);
                sort($picks_extra);


                $balls[] = [
                    "balls" => $picks,
                    "extras" => $picks_extra
                ];
            }

            return $balls;
        }

        /*
         * Si no es user pick generamos automáticamnete
         */
        $created_picks = TelemService::generatePicksWheels($lottery, $wheel_info, $cts_ticket_byDraw);

        return $created_picks;
    }
}
