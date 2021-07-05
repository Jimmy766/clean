<?php


namespace App\Core\Telem\Controllers;


use App\Http\Controllers\ApiController;
use App\Core\Telem\Requests\TelemPricesRequest;
use App\Core\Rapi\Services\Log;
use App\Core\Lotteries\Models\Lottery;
use App\Core\Base\Services\PriceService;
use App\Core\Telem\Services\TelemService;
use Illuminate\Http\Request;
use DB;

class PricesController extends ApiController
{

    public function __construct() {
        parent::__construct();
        $this->middleware('client.credentials');
    }

    public function index(TelemPricesRequest $request){
        switch ($request->product){
            case "lottery":
            case 1:
                return $this->lottery_prices($request);
            case "syndicate":
            case 2:
                return $this->syndicates_prices($request);
            case "syndicate_raffle":
            case 3:
                return $this->syndicate_raffle_prices($request);
            case "raffle":
            case 4:
                return $this->raffles_prices($request);
            case "wheels":
            case 5:
                return $this->wheels_prices($request);
            case "syndicate_wheel":
            case 6:
                return $this->syndicates_wheels_prices($request);
            default:
                return $this->errorResponse("You need to specify a valid product", 422);
        }
    }

    private function wheels_prices(TelemPricesRequest $request){
        $sys_id = $request->client_sys_id == 7 || $request->client_sys_id == 2 ? 1 :
            $request->client_sys_id;
        $country_id = $request->user_country;
        $curr_code = $request->country_currency;
        $user_group = $request->user_group;

        $is_full = $request->has("type") && ($request->get("type") == "full" || $request->get("type") == 1 ) ? true : false;

        $available = TelemService::availableProducts($user_group, $sys_id);

        if($is_full && $available['wheels_full'] == '-1'){
           return $this->successResponse([
                "prices" => []
            ], 200);
        }

        if(!$is_full && $available['wheels'] == '-1'){
            return $this->successResponse([
                "prices" => []
            ], 200);
        }



        $type = ($is_full ? 1 : 2);
        $str_lottos = $is_full ?
         ($available['wheels_full'] == '') ? '' : ' AND p.lot_id IN ('.$available['wheels_full'].')'
            : ($available['wheels'] == '') ? '' : ' AND p.lot_id IN ('.$available['wheels'].')';


        $sql = "SELECT p.prc_id, p.lot_id, p.prc_draws, p.prc_time, pl.prcln_price, curr_code,
				p.prc_min_jackpot, p.prc_time_type, p.prc_days_by_tickets, w.wheel_balls, w.wheel_lines, w.wheel_warranty
				FROM prices p INNER JOIN prices_line pl ON p.prc_id = pl.prc_id
				INNER JOIN wheels w ON p.wheel_id = w.wheel_id
				WHERE p.sys_id = ". $sys_id ." AND p.prc_draws <> 0 AND prc_time <> 0 and p.active_admin_telem
				AND pl.curr_code = '". $curr_code ."' ".$str_lottos."
				AND (prcln_country_list_enabled = 0 OR prcln_country_list_enabled LIKE '%".$country_id."%')
				AND (prcln_country_list_disabled NOT LIKE '%".$country_id."%') AND w.wheel_type = {$type}
				ORDER BY curr_code ASC, p.lot_id ASC, pl.prcln_date ASC, p.prc_min_jackpot ASC, p.prc_draws ASC";


        $lottery_wheels = DB::connection("mysql_external")->select($sql);

        $lottos = [];
        $lottery_prices = [];

        foreach($lottery_wheels as $lottery_wheel){
            $lottos[$lottery_wheel->lot_id][] = $lottery_wheel;
        }

        foreach($lottos as $lottery){
            foreach($lottery as $lotto){
                $lottery_prices[$lotto->lot_id][] = [
                    "identifier" => $lotto->prc_id,
                    "time" => $lotto->prc_time,
                    "time_type" => PriceService::translateMesure($lotto->prc_time, $lotto->prc_time_type) ,
                    "warranty" => "$lotto->wheel_warranty - $lotto->wheel_balls Numbers ({$lotto->wheel_lines} Games)",
                    "min_jackpot" => $lotto->prc_min_jackpot,
                    "wheel_balls"  => $lotto->wheel_balls,
                    "wheel_lines" => $lotto->wheel_lines,
                    "currency" => $lotto->curr_code,
                    "price" => number_format($lotto->prcln_price,2,'.','')
                ];
            }
        }

        return $this->successResponse([
            "prices"=> $lottery_prices
        ], 200);
    }

    private function syndicate_raffle_prices(TelemPricesRequest $request)
    {
        $sys_id = $request->client_sys_id == 7 || $request->client_sys_id == 2 ? 1 :
            $request->client_sys_id;
        $country_id = $request->user_country;
        $curr_code = $request->country_currency;
        $user_group = $request->user_group;

        $sql = "SELECT sp.prc_id, sp.prc_time, sp.prc_time_type, spl.prc_id, spl.prcln_price,
			spl.curr_code , rsyndicate_id
			FROM syndicate_raffle_prices sp, syndicate_raffle_prices_line spl, syndicate_raffle s
			WHERE sp.prc_id = spl.prc_id AND sp.sys_id = " . $sys_id . " AND s.active_admin_telem = 1
			AND s.id = sp.rsyndicate_id AND sp.active = 1 AND spl.curr_code = '$curr_code'
			AND (prcln_country_list_enabled = 0 OR prcln_country_list_enabled LIKE '%" . $country_id . "%')
			AND (prcln_country_list_disabled NOT LIKE '%" . $country_id . "%')
			ORDER BY rsyndicate_id ASC, prc_time asc";

        $rs_temp = DB::connection("mysql_external")->select($sql);

        $syndicate_raffles_prices = [];
        foreach ($rs_temp as $data) {
            $price = [
                "identifier" => $data->prc_id,
                "time"=> $data->prc_time,
                "time_type"=> PriceService::translateMesureSyndicate($data->prc_time, $data->prc_time_type),
                "price"=> $data->prcln_price,
                "currency"=> $data->curr_code
            ];
            $syndicate_raffles_prices[$data->rsyndicate_id][] = $price;
        }
        return $this->successResponse([
            "prices" => $syndicate_raffles_prices
        ], 200);

    }

    private function raffles_prices(TelemPricesRequest $request){
        $sys_id = $request->client_sys_id == 7 || $request->client_sys_id == 2 ? 1 :
            $request->client_sys_id;
        $country_id = $request->user_country;
        $curr_code = $request->country_currency;
        $user_group = $request->user_group;

        $available = TelemService::availableProducts($user_group, $sys_id);

        if ($available['raffles'] == '-1') return $this->successResponse([
            "prices" => []
        ], 200);;


        $str_raffles = ($available['raffles'] == '') ? '' : ' AND r.inf_id IN ('.$available['raffles'].') ';

        $sql = "SELECT r.rff_ticket_type, r.rff_id, p.prc_rff_time,
        p.rtck_blocks, p.prc_rff_min_tickets, p.prc_rff_id, p.prc_rff_renew,
        prcl.prcln_rff_price, prcl.curr_code, prcl.prcln_rff_date, i.inf_raffle_mx, i.inf_id
        FROM raffles r INNER JOIN prices_raffles p
        ON r.inf_id = p.inf_id INNER JOIN prices_line_raffles prcl
        ON p.prc_rff_id = prcl.prc_rff_id INNER JOIN raffle_info i
        ON r.inf_id = i.inf_id WHERE r.rff_view = 1
        AND p.sys_id = ". $sys_id ."
        AND prcl.curr_code = '". $curr_code ."'
        AND i.inf_raffle_mx = 0 ".$str_raffles."
        AND (prcln_rff_country_list_enabled = 0
        OR prcln_rff_country_list_enabled LIKE '%".$country_id."%')
        AND (prcln_rff_country_list_disabled NOT LIKE '%".$country_id."%')
        ORDER BY r.rff_id, rtck_blocks, prc_rff_min_tickets, prcln_rff_date
        DESC";

        $raffles = DB::connection("mysql_external")->select($sql);

        $raffles_prices = [];

        foreach($raffles as $raffle){
            $raffle = [
                "identifier"=> $raffle->rff_id,
                "time" => $raffle->prc_rff_time,
                "min_tickets"=> $raffle->prc_rff_min_tickets,
                "price"=> $raffle->prcln_rff_price,
                "currency" => $raffle->curr_code
            ];
            $raffles_prices[$raffle["identifier"]][] = $raffle;
        }

        return $this->successResponse([
            "prices" => $raffles_prices
        ], 200);
    }

    private function lottery_prices(TelemPricesRequest $request){

        $sys_id = $request->client_sys_id == 7 || $request->client_sys_id == 2 ? 1 :
            $request->client_sys_id;
        $country_id = $request->user_country;
        $curr_code = $request->country_currency;
        $user_group = $request->user_group;

        $sql = $this->getLotteryQuery($user_group, $curr_code, $country_id, $sys_id);

        $rsExt = DB::connection("mysql_external")->select($sql);

        $ext_curr = "";
        $prices = [];
        $lotteries = [];
        $all = [];

        $sql = "(SELECT MAX(draw_id) AS maxdid, draw_jackpot, l.lot_id, lot_name_en, lot_balls, lot_extra, lot_pick_balls, lot_max_draws_playing, lot_pick_extra, lot_auto_pick_extra, lot_auto_pick_reintegro, lot_pick_reintegro, lot_maxNum, lot_extra_startNum, lot_extra_maxNum, slip_min_lines
		FROM lotteries l INNER JOIN draws d ON l.lot_id = d.lot_id
		WHERE draw_status = 0 AND l.lot_active
		GROUP BY l.lot_id)
	UNION (select 0 as maxdid, 0 as draw_jackpot, l.lot_id, lot_name_en, lot_balls, lot_extra, lot_pick_balls, lot_max_draws_playing, lot_pick_extra, lot_auto_pick_extra, lot_auto_pick_reintegro, lot_pick_reintegro, lot_maxNum, lot_extra_startNum, lot_extra_maxNum, slip_min_lines
		FROM lotteries l where lot_id = 1000)
	 ORDER BY lot_name_en";

        $lotto = DB::connection("mysql_external")->select($sql);
        foreach($lotto as $lot){
            $lotteries[$lot->lot_id] = $lot;
        }

        $lottery_prices = [];
        foreach($rsExt as $key => $value){
            $lottery_prices[$value->lot_id][] = $value;
        }

        foreach($lottery_prices as $lkey => $lottery_price){
            $big_jackpot = 0;
            $lot_jackpot = isset($lotteries[$lkey]) ? $lotteries[$lkey]->draw_jackpot : 0;

            foreach($lottery_price as $key => $value){

                if(!isset($prices[$value->lot_id])) $prices[$value->lot_id] = [];

                if( $ext_curr != $value->curr_code ){
                    $ext_curr = $value->curr_code;
                }

                $prcln_price = ($sys_id == 5) ? ($value->prcln_price * $value->slip_min_lines) : $value->prcln_price;

                $price = [
                    "identifier" => $value->prc_id,
                    "time" => $value->prc_time,
                    "time_type" => PriceService::translateMesure($value->prc_time, $value->prc_time_type) ,
                    "min_jackpot" => $value->prc_min_jackpot,
                    "currency" => $value->curr_code,
                    "price" => number_format($prcln_price,2,'.','')
                ];

                $price["draws"] = $value->prc_draws;
                $price["ticket_play"] = "";
                $price["is_renewable"] = $value->prc_draws > 1 ? true : false;

                /** Ticket play 1 draw */
                if($ext_curr == $curr_code && $value->lot_id == 19){
                    $price["ticket_play"] = $value->prc_days_by_tickets;
                }

                $price["lot_jackpot"] = $lot_jackpot;


                if($value->prc_min_jackpot > $price["lot_jackpot"]) {
                    $big_jackpot = $value->prc_min_jackpot;
                    $prices[$value->lot_id]["bigjackpot"][] = $price;
                }else {
                    $prices[$value->lot_id]["jackpot"][] = $price;
                }
                $all[$value->lot_id][] = $price;

            }

            /**
             * Dependiendo del jackpot de la lotería
             * se usa un conjunto de precios u otro
             */
            if($big_jackpot > $lot_jackpot){
                $prices[$lkey] = $prices[$lkey]["bigjackpot"];
            }else{
                $prices[$lkey] = $prices[$lkey]["jackpot"];
            }
        }
        return $this->successResponse([
            "prices"=> $prices
        ], 200);
    }

    private function getLotteryQuery($user_group, $curr_code, $country_id, $sys_id){
        if($user_group == 33 || $user_group == 2){
            $prc_id_in = '3,4,8,166,462,475,476,478,545,546,547,666,669,751';
            $prc_id_not_in = '895,896,897,898,899,900,907,908,909,910,911,912';
        }else{

            $prc_id_not_in = "9,10,11,12,13,14,15,16,112,160,168,169,255,256,257,258,259,260,261,262,263,273,274,275,276,277,278,279,280,281,329,330,436,437,438,439,440,442,443,658,659,662,663";
            $prc_id_in = "951,950,949,948,947,946,945,944,943,942,941,940,939,938,937,936,935,934,933,932,931,930,929,928,927,926,925,924,923,922,921,920,919,918,917,916,915";
        }

        $sql = "SELECT p.prc_id, p.lot_id, p.prc_draws, p.prc_time, pl.prcln_price, pl.curr_code, p.prc_min_jackpot, p.prc_time_type, p.prc_days_by_tickets, l.slip_min_lines
				FROM prices p INNER JOIN lotteries l ON p.lot_id = l.lot_id INNER JOIN prices_line pl ON p.prc_id = pl.prc_id
				WHERE p.sys_id = ". $sys_id ." AND pl.curr_code = '".$curr_code."' and p.prc_draws <> 0
				AND (p.active_admin_telem or p.prc_id IN(".$prc_id_in.") )
				AND p.prc_id not in (".$prc_id_not_in.")
				AND p.wheel_id = 0
				AND (prcln_country_list_enabled = 0 OR prcln_country_list_enabled LIKE '%".$country_id."%')
				AND (prcln_country_list_disabled NOT LIKE '%".$country_id."%')
				ORDER BY p.lot_id ASC, p.prc_min_jackpot ASC, p.prc_draws ASC";

        return $sql;
    }

    private function syndicates_prices(TelemPricesRequest $request)
    {
        $user_id = $request->user_id;

        $sys_id = $request->client_sys_id == 7 || $request->client_sys_id == 2 ? 1 :
            $request->client_sys_id;
        $country_id = $request->user_country;
        $curr_code = $request->country_currency;
        $user_group = $request->user_group;

        $sql = "SELECT sp.prc_id, sp.prc_time, sp.prc_time_type, spl.prc_id, spl.prcln_price, spl.curr_code,
                syndicate_id, s.synd_root
				FROM syndicate_prices sp, syndicate_prices_line spl, syndicate s
				WHERE sp.prc_id = spl.prc_id AND sp.sys_id = ".$sys_id."
				AND s.active_admin_telem = 1 AND s.id = sp.syndicate_id AND sp.prc_time > 0 AND sp.active = 1
				AND s.has_wheel = 0 AND spl.curr_code = '". $curr_code ."'
				AND (prcln_country_list_enabled = 0 OR prcln_country_list_enabled LIKE '%".$country_id."%')
				AND (prcln_country_list_disabled NOT LIKE '%".$country_id."%')
				order by syndicate_id ASC, prc_time asc";


        $rs_temp = DB::connection("mysql_external")->select($sql);

        $available = TelemService::availableProducts($user_group, $sys_id);

        if ($available['syndicates'] == '-1') return $this->successResponse([
            "prices" => []
        ], 200);


        $str_synds = ($available['syndicates'] == '') ? '' : ' AND s.id IN ('.$available['syndicates'].') ';

// lista de picks

        $sql = 'SELECT sp.syndicate_picks_id, sp.label, sp.insurance_play, ss.lot_id, s.id AS syndicate_id,'.
            's.cron_assign, s.synd_root, ss.available_participations, ss.participations,'.
            's.syndicate_pck_type, s.product_always_available FROM syndicate_picks sp'.
            ' INNER JOIN syndicate_status ss ON sp.syndicate_picks_id = ss.syndicate_picks_id'.
            ' INNER JOIN syndicate s'.
            ' ON sp.syndicate_id = s.synd_root'.
            ' WHERE (s.product_always_available = 1 OR (s.product_always_available = 0 AND ss.draw_id > 0)) AND sp.active'.
            ' AND s.has_wheel = 0 AND ss.syndicate_id = sp.syndicate_id AND s.sys_id = '.$sys_id.$str_synds.
            ' GROUP BY ss.syndicate_picks_id'.
            ' ORDER BY syndicate_id, syndicate_picks_id';

        $Temp_rsSyndPicks = DB::connection("mysql_external")->select($sql);

        //$insurance_black_list = $this->getInsuranceBlacklist();

        if (is_array($Temp_rsSyndPicks))
        {
            //$rsSyndPicks = $this->getAvailablePicks($Temp_rsSyndPicks, $country_id, $insurance_black_list);

            if (is_array($rs_temp)){
                // check if the user is playing one pick already
                $sql = 'SELECT DISTINCT ss.syndicate_picks_id
					FROM syndicate_participation sp, syndicate_status ss, syndicate s
					WHERE s.synd_root = ss.syndicate_id AND ss.draw_id = sp.draw_id
					AND ss.sub_id = sp.sub_id AND sp.usr_id = '.$user_id;

                $tmp_picks_played = DB::connection("mysql_external")->select($sql);

                if (is_array($tmp_picks_played) && count($tmp_picks_played) > 0){
                    foreach ($tmp_picks_played as $data_tmp){
                        $sindAssignedParticipation[] = $data_tmp['syndicate_picks_id'];
                    }
                }

            }
        }

        $syndicates_prices = [];

        $tmp_price = array();
        foreach($rs_temp as $data => $value){
            if(!isset($tmp_price[$value->curr_code])){
                $tmp_price[$value->curr_code] = 1;
                $sindExtPrices[$value->curr_code] = [];
            }
            if(!isset($tmp_price2[$value->curr_code][$value->syndicate_id])){
                $tmp_price2[$value->curr_code][$value->syndicate_id] = 1;
                $sindExtPrices[$value->curr_code][$value->syndicate_id] = [];
            }
            $sindExtPrices[$value->curr_code][$value->syndicate_id][$value->prc_id] =
                number_format($value->prcln_price,2,'.','');
            if(!isset($tmp_sind[$value->syndicate_id])){
                $tmp_sind[$value->syndicate_id] = 1;
                $sindExtensions[$value->syndicate_id] = [];
            }
            $tmptxt = $value->prc_time;

            if($value->curr_code == $curr_code){
                $sindExtensions[$value->syndicate_id][$value->prc_id] = $tmptxt;
            }

            $syndicates_prices[$value->syndicate_id][] = [
                "identifier" => $value->prc_id,
                "time" => $value->prc_time,
                "time_type" => PriceService::translateMesureSyndicate($value->prc_time, $value->prc_time_type),
                //"draws" => 2,
                "price" => number_format($value->prcln_price,2,'.',''),
                "currency"=> $value->curr_code
            ];
        }

        return $this->successResponse([
            "prices" => $syndicates_prices
        ], 200);
    }

    public function syndicates_wheels_prices(TelemPricesRequest $request){
        $user_id = $request->user_id;

        $sys_id = $request->client_sys_id == 7 || $request->client_sys_id == 2 ? 1 :
            $request->client_sys_id;
        $country_id = $request->user_country;
        $curr_code = $request->country_currency;
        $user_group = $request->user_group;

        $available = TelemService::availableProducts($user_group, $sys_id);

        Log::record_log("access", Log::stringify($available));

        if ($available['syndicates_wheels'] == '-1') return $this->successResponse([
            "prices" => []
        ], 200);


        $str_synds = ($available['syndicates_wheels'] == '') ? '' :
            ' AND p.syndicate_id IN ('.$available['syndicates_wheels'].')';

        $sql = "SELECT p.prc_id, p.syndicate_id, p.prc_time, pl.prcln_price, curr_code,
				p.prc_time_type, s.tickets_to_show
				FROM syndicate_prices p INNER JOIN syndicate_prices_line pl ON p.prc_id = pl.prc_id
				INNER JOIN syndicate s ON p.syndicate_id = s.id
				WHERE p.sys_id = ". $sys_id ." AND p.active
				AND s.has_wheel = 1
				AND pl.curr_code = '". $curr_code ."' ".$str_synds."
				AND (prcln_country_list_enabled = 0 OR prcln_country_list_enabled LIKE '%".$country_id."%')
				AND (prcln_country_list_disabled NOT LIKE '%".$country_id."%')
				ORDER BY curr_code ASC, p.syndicate_id ASC, pl.prcln_date ASC";

        $syndicate_prices = DB::connection("mysql_external")->select($sql);

        $response = [];

        foreach($syndicate_prices as $syndicate_price){
            $response[$syndicate_price->syndicate_id]["prices"][] = [
                "identifier" => $syndicate_price->prc_id,
                "time" => $syndicate_price->prc_time,
                "time_type" => $syndicate_price->prc_time_type,
                "tickets_to_show" => $syndicate_price->tickets_to_show,
                "price" => $syndicate_price->prcln_price,
                "currency" => $syndicate_price->curr_code
            ];
        }

        $str_synds = ($available['syndicates_wheels'] == '') ? '' : ' AND s.id IN ('.$available['syndicates_wheels'].')';
        // lista de picks
        $sql = 'SELECT sp.syndicate_picks_id, sp.label, sp.insurance_play, ss.lot_id, s.id AS syndicate_id,
       s.cron_assign, s.synd_root,
       ss.available_participations,
       ss.participations,
       s.syndicate_pck_type,
       s.product_always_available FROM syndicate_picks sp
           INNER JOIN syndicate_status ss ON sp.syndicate_picks_id = ss.syndicate_picks_id
				  INNER JOIN syndicate s
					ON sp.syndicate_id = s.synd_root
				WHERE
				(s.product_always_available = 1 OR (s.product_always_available = 0 AND ss.draw_id > 0)) AND sp.active
				AND s.has_wheel = 1 AND ss.syndicate_id = sp.syndicate_id AND s.sys_id = '.
            $sys_id.$str_synds.
            ' GROUP BY ss.syndicate_picks_id
				  ORDER BY syndicate_id, syndicate_picks_id';

        $Temp_rsSyndWheelsPicks = DB::connection("mysql_external")->select($sql);

        $insurance_black_list = TelemService::insurance_black_list();

        //print_r($Temp_rsSyndWheelsPicks);
        if (is_array($Temp_rsSyndWheelsPicks))
        {
            $rsSyndWheelsPicks = TelemService::getAvailablePicks(
                $Temp_rsSyndWheelsPicks,
                $country_id,
                $insurance_black_list,
                FALSE); // where is presale
        }

        foreach($rsSyndWheelsPicks as $rsSyndWheelsPick){
            $response[$rsSyndWheelsPick->syndicate_id]["picks"][] = [
                "syndicate_picks_id" => $rsSyndWheelsPick->syndicate_picks_id,
                "label" => $rsSyndWheelsPick->label,
                "insurance_play" => $rsSyndWheelsPick->insurance_play,
                "available_participations" => $rsSyndWheelsPick->available_participations,
                "participations" => $rsSyndWheelsPick->participations
            ];
        }

        $str_synds = ($available['syndicates_wheels'] == '') ? '' :
            ' WHERE syndicate_id IN ('.$available['syndicates_wheels'].')';


        $sql = 'SELECT l.syndicate_id, wheel_balls, wheel_lines FROM syndicate_lotto l
    INNER JOIN wheels w ON l.wheel_id = w.wheel_id ' . $str_synds;


        // save wheel data
        $rs_synd_wheel_data = DB::connection("mysql_external")->select($sql);

        foreach ($rs_synd_wheel_data as $tmp_data)
        {
            $response[$tmp_data->syndicate_id]["wheels"] =
                array(
                    'balls'=> $tmp_data->wheel_balls,
                    'lines'=>$tmp_data->wheel_lines
                );
        }

        /*
         * field: Extension / Wheel
         * ($tickets_to_show / $wheel_lines ).' x ' $wheel_balls Numbers ($wheel_lines Games)
         *
         * field: Group 1 (155/200 shares available)
         *  $label ($available_participations / $participations) shares available
         *
         */

        foreach($response as $kres => $res){
            if(!isset($res["picks"])){
                unset($response[$kres]);
            }
        }

        return $this->successResponse([
            "prices" => $response
        ], 200);
    }
}
