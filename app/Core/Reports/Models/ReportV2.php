<?php

namespace App\Core\Reports\Models;

use App\Core\Base\Traits\LogCache;
use App\Core\Reports\Models\ReportType;
use App\Core\Reports\Transforms\ReportTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use DB;
use Illuminate\Support\Facades\Storage;

class ReportV2 extends Model {
    use LogCache;

    protected $guarded = [];
    public $transformer = ReportTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'start', 'end', 'status', 'url','token', 'tag', 'sys_id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = ['id', 'start', 'end', 'status', 'url','token', 'tag', 'sys_id'];

    public function report_type() {
        return $this->belongsTo(ReportType::class);
    }

    public function getTypeAttributesAttribute() {
        return $this->report_type->transformer::transform($this->report_type);
    }



    public function board_info_json_data($sys_id, $name, $lang) {

        $json_file = App::getFacadeApplication()->basePath().'/storage/app/public/'.$name;

        // LOTTOS
        //////////
        $lottery_columns = [
            'id',
            'name',
            'country',
            'jackpot',
            'currency',
            'date',
        ];

        $sql = "SELECT  l.lot_id as 'id',
                        l.lot_name_$lang AS 'name',
                        r.reg_name_$lang AS 'country',
                        draw_jackpot as 'jackpot',
                        l.curr_code as 'currency',
                        concat(min(draw_date),' ', draw_time) as 'date'
                  FROM draws d INNER JOIN lotteries l ON d.lot_id = l.lot_id
                  INNER JOIN currencies c ON l.curr_code = c.curr_code
                  INNER JOIN regions r ON l.lot_region_country = r.reg_id
                  INNER JOIN lotteries_extra_info le ON l.lot_id = le.lot_id
                  WHERE draw_status=0 AND lot_active
                  GROUP BY d.lot_id";

        $lottery_result= collect(DB::connection('mysql_reports')->select($sql));


        $sql = "SELECT d.draw_id,d.lot_id FROM draws d INNER JOIN lotteries l ON d.lot_id = l.lot_id WHERE d.draw_status= 0 AND lot_active;";

        $result= collect(DB::connection('mysql_reports')->select($sql));

        $active_draws = array();
        $lottery_draws = array();

        foreach($result as $active_draw){
            $active_draws[] = $active_draw->draw_id;
            $lottery_draws[$active_draw->lot_id]["draw_id"] = $active_draw->draw_id;
        }


        $sql = "SELECT s.sys_id,s.lot_id,t.draw_id,us.sus_name,sum(1) as cant
        FROM tickets t, subscriptions s, users_system us
        WHERE t.sub_id = s.sub_id AND t.draw_id IN (".implode(",", $active_draws).")
                AND us.sus_id = t.sus_id
                AND s.sys_id = $sys_id
        GROUP BY sat_id,t.draw_id
        ORDER BY s.lot_id;";

        echo "Active draws total " . PHP_EOL;

        echo $sql . PHP_EOL;

        $active_draws_info_total = collect(DB::connection('mysql_reports')->select($sql));

        print_r($active_draws_info_total);

        echo PHP_EOL;

        foreach($active_draws_info_total as $active_draw_info_total){
            if(!isset($lottery_draws[$active_draw_info_total->lot_id]["info_total"])){
                $lottery_draws[$active_draw_info_total->lot_id]["info_total"] = 0;
            }
            $lottery_draws[$active_draw_info_total->lot_id]["info_total"] += $active_draw_info_total->cant;
        }

        $sql = "SELECT s.sys_id,s.lot_id,t.draw_id,us.sus_name,sum(1) as cantEmailed
        FROM tickets t, subscriptions s, users_system us
        WHERE t.sub_id = s.sub_id AND t.draw_id IN (".implode(",", $active_draws).")
        AND us.sus_id = t.sus_id
        AND s.sys_id IN ($sys_id)
        AND t.tck_send = 1
        GROUP BY sat_id";

        echo "Active draws sent ";
        echo $sql . PHP_EOL;

        $active_draws_info_sent= collect(DB::connection('mysql_reports')->select($sql));

        print_r($active_draws_info_sent);

        echo PHP_EOL;

        foreach($active_draws_info_sent as $active_draw_info_sent){
            $lottery_draws[$active_draw_info_sent->lot_id]["offices"][$active_draw_info_sent->sus_name]["email"]  = $active_draw_info_sent->cantEmailed;
        }

        echo "Active draws processed" . PHP_EOL;

        $sql = "SELECT us.sus_name,s.lot_id, sum(1) as cantProcessed
        FROM tickets t, subscriptions s, users_system us
        WHERE t.sub_id = s.sub_id AND t.draw_id IN (".implode(",", $active_draws).")
                AND us.sus_id = t.sus_id
                AND s.sys_id IN ($sys_id)
                AND t.tck_send != 1
        GROUP BY sat_id";

        echo $sql . PHP_EOL;

        $active_draws_info_proceesed = collect(DB::connection('mysql_reports')->select($sql));

        print_r($active_draws_info_proceesed);

        echo PHP_EOL;

        foreach($active_draws_info_proceesed as $active_draw_info_proceesed){
            $lottery_draws[$active_draw_info_proceesed->lot_id]["offices"][$active_draw_info_proceesed->sus_name]["proccesed"]  = $active_draw_info_proceesed->cantProcessed;
        }

        $sql = "SELECT us.sus_name,s.lot_id, sum(s.sub_ticket_nextDraw) as cantReserved
        FROM subscriptions s
        INNER JOIN lotteries l ON l.lot_id = s.lot_id
        INNER JOIN users_system us ON s.sus_id = us.sus_id
        WHERE s.sys_id IN ($sys_id)
        AND (s.lot_id = 2 OR (s.lot_id = 1000 AND s.lot_id_big = 2))
        AND s.sub_next_draw_id = 0
        AND (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(s.sub_buydate)) >= l.lot_wait_reserve
        AND s.sub_lastdraw_id NOT IN (".implode(",", $active_draws).")
        AND ((s.sub_tickets+s.sub_ticket_extra)-s.sub_emitted>0)
        AND s.sub_status <> 2
        GROUP BY sat_id;";

        echo "Active draws reserved " . PHP_EOL;

        echo $sql . PHP_EOL;

        $active_draws_info_reserved = collect(DB::connection('mysql_reports')->select($sql));

        print_r($active_draws_info_reserved);

        echo PHP_EOL;

        foreach($active_draws_info_reserved as $active_draw_info_reserved){
            $lottery_draws[$active_draw_info_reserved->lot_id]["offices"][$active_draw_info_reserved->sus_name]["reserved"]  = $active_draw_info_reserved->cantReserved;

            if(!isset( $lottery_draws[$active_draw_info_reserved->lot_id]["info_total"])){
                $lottery_draws[$active_draw_info_reserved->lot_id]["info_total"] = 0;
            }
            $lottery_draws[$active_draw_info_reserved->lot_id]["info_total"] += $active_draw_info_reserved->cantReserved;
        }

        $json = [];
        $json['lottos'] = [];

        $lottery_result->each(function($item) use (&$json, $lottery_columns, $lottery_draws) {
            $item = get_object_vars($item);

            foreach ($lottery_columns as $c){
                $item[$c] = html_entity_decode(iconv('ISO-8859-1','UTF-8',$item[$c]));
                if(isset($lottery_draws[$item["id"]])){
                    $item["generated"] =  isset($lottery_draws[$item["id"]]) && isset($lottery_draws[$item["id"]]["info_total"]) ? $lottery_draws[$item["id"]]["info_total"] : 0;

                    $item["offices"] = [];

                    if(isset( $lottery_draws[$item["id"]]["offices"])){
                        $arr = [];
                        foreach($lottery_draws[$item["id"]]["offices"] as $key => $data){
                            $arr[] = [
                                "email" => isset($data["email"]) ? $data["email"] : 0,
                                "reserved" => isset($data["reserved"]) ? $data["reserved"] : 0,
                                "procesed" => isset($data["proccesed"]) ? $data["proccesed"] : 0,
                                "office" => $key
                            ];
                        }
                        $item["offices"] = $arr;
                    }

                }

            }
            $json['lottos'][] = $item;
        });

        // SYNDICATES
        //////////////
        $syndicate_columns = [
            'id',
            'name',
            'jackpot',
            'currency',
            'date',
            'cat',
        ];

        // SYNDICATE LOTTO
        $sql = "SELECT  s.id, s.printable_name as 'name',d.draw_jackpot as 'jackpot', curr_code as 'currency', addtime(d.draw_date,d.draw_time) as 'date', 'lotto' as 'cat'
		            FROM syndicate s, syndicate_lotto sl , lotteries l, draws d, regions r, continents c, lotteries_extra_info le
			    WHERE s.id = sl.syndicate_id
				AND s.active=1
				AND sl.lot_id=l.lot_id
				AND l.lot_id  = d.lot_id
				AND l.lot_id = le.lot_id
				AND d.draw_status = 0
				AND l.lot_region_country = r.reg_id
				AND r.cont_id = c.cont_id
				AND s.sys_id = $sys_id
				ORDER BY s.id, date DESC";

        $result= collect(DB::connection('mysql_reports')->select($sql));

        $json['syndicates'] = [];
        $count = 0;
        $result->each(function($item) use (&$json, $syndicate_columns) {
            $item = get_object_vars($item);
            foreach ($syndicate_columns as $c){
                $item[$c] = html_entity_decode(iconv('ISO-8859-1','UTF-8',$item[$c]));
            }
            $json['syndicates'][] = $item;
        });

        // SYNDICATE RAFFLE
        $sql = "select sl.rsyndicate_id as 'id', s.printable_name as 'name', rff_jackpot as 'jackpot', d.curr_code as 'currency',  d.rff_playdate as 'date' , 'raffle' as 'cat'
			FROM syndicate_raffle s
				INNER JOIN  syndicate_raffle_raffles sl ON s.id = sl.rsyndicate_id
				INNER JOIN  raffle_info l ON sl.inf_id=l.inf_id
				INNER JOIN  raffles d ON  l.inf_id  = d.inf_id
				INNER JOIN  regions r ON d.rff_region = r.reg_id
				INNER JOIN  continents c ON r.cont_id = c.cont_id
			WHERE  s.active=1
				AND d.rff_view=1
				AND d.rff_status = 1
				AND s.sys_id = $sys_id
				ORDER BY s.id, 'date' DESC";

        $result= collect(DB::connection('mysql_reports')->select($sql));

        // we continue count on previous iteration value
        $result->each(function($item) use (&$json, $syndicate_columns) {
            $item = get_object_vars($item);
            foreach ($syndicate_columns as $c){
                $item[$c] = html_entity_decode(iconv('ISO-8859-1','UTF-8',$item[$c]));
            }
            $json['syndicates'][] = $item;
        });

        // RAFFLES
        $raffle_columns = [
            'id',
            'name',
            'country',
            'jackpot',
            'currency',
            'date',
            'xinfo'
        ];

        $sql = "SELECT  raffles.inf_id as 'id',
                        rff_name as 'name',
                        reg_name_$lang as 'country',
                        rff_jackpot as 'jackpot',
                        raffles.curr_code as 'currency' ,
                        rff_playdate as 'date',
                        inf_name as 'xinfo'
				FROM raffles
				INNER JOIN raffle_info ON raffle_info.inf_id = raffles.inf_id
				INNER JOIN  regions r ON raffles.rff_region = r.reg_id
				WHERE rff_status = 1
				AND rff_view = 1
				ORDER BY rff_playdate";

        $result= collect(DB::connection('mysql_reports')->select($sql));

        $result->each(function($item) use (&$json, $raffle_columns) {
            $item = get_object_vars($item);
            foreach ($raffle_columns as $c){
                $item[$c] = html_entity_decode(iconv('ISO-8859-1','UTF-8',$item[$c]));
            }
            $json['raffles'][] = $item;
        });


        print_r(json_encode($json));


        $static_name = "reports/board_v2.json";

        Storage::disk('s3-public')->put($static_name,  json_encode($json));


        //file_put_contents($json_file, json_encode($json));

    }

}
