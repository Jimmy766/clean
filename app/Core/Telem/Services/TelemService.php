<?php


namespace App\Core\Telem\Services;

use App\Core\Telem\Models\TelemProduct;
use DB;

class TelemService
{

    /**
     * @param $group_id
     * @param $sys_id
     * @return array
     */
    public static function availableProducts($group_id, $sys_id){
        $product = TelemProduct::where("group_id", "=", $group_id)
            ->where("sys_id", "=", $sys_id)->first();

        $available = array();
        $available['lottos']		= '';
        $available['syndicates']	= '';
        $available['raffles']		= '';
        $available['syndicates_raffles'] = '';
        $available['wheels'] = '';
        $available['wheels_full']	= '';
        $available['syndicates_wheels']	= '';

        if($product){
            $available['lottos']		= $product->tp_lotteries;
            $available['syndicates']	= $product->tp_syndicates;
            $available['raffles']		= $product->tp_raffles;
            $available['syndicates_raffles'] = $product->tp_syndicates_raffles;
            $available['wheels'] = $product->tp_lotteries_wheels;
            $available['wheels_full']	= $product->tp_lotteries_wheels_full;
            $available['syndicates_wheels']	= $product->tp_syndicates_wheels;
        }

        return $available;
    }



    public static function getAvailablePicks($all_picks_data, $country_id, $insurance_black_list, $pre_sale_is_active=FALSE)
    {
        $available_picks = array();
        $has_insurance_groups = array();
        // first mark syndicates that have insurable groups available
        foreach ($all_picks_data as $temp_pickdata)	{
            // init setting syndicate as if it has no insurance groups
            if (!isset($has_insurance_groups[$temp_pickdata->syndicate_id])){
                $has_insurance_groups[$temp_pickdata->syndicate_id] = FALSE;
            }
            if ($temp_pickdata->insurance_play == 1) {
                $has_insurance_groups[$temp_pickdata->syndicate_id] = TRUE;
            }
        }
        // array with picks active picks
        $picks_data = array();
        // check all picks
        // if syndicate has insurable groups then
        //      if group is insurable and client is insurable then save that group
        // else save the group
        foreach ($all_picks_data as $temp_pickdata) {
            // if it has insurable groups and client is insurable those are the picks to offer
            if ($has_insurance_groups[$temp_pickdata->syndicate_id] == TRUE) {
                if ( (isset($insurance_black_list[$temp_pickdata->lot_id]) &&
                    !in_array($country_id, $insurance_black_list[$temp_pickdata->lot_id]) ) ) {
                    if ($temp_pickdata->insurance_play == 1) {
                        $picks_data[] = $temp_pickdata;
                    }
                }
                else {
                    if ($temp_pickdata->insurance_play == 0) {
                        $picks_data[] = $temp_pickdata;
                    }
                }
            }
            else {
                $picks_data[] =$temp_pickdata;
            }
        }
        // check which picks have available participations
        foreach ($picks_data as $temp_pickdata) {
            if (FALSE /*$temp_pickdata['product_always_available'] == 1*/) {
                $available_picks[] = $temp_pickdata;
            }
            else {
                // if not assigning participations then must count processed carts
                if ($temp_pickdata->cron_assign == 0){
                    $sql= 'select sum(participations_sold) as sum from syndicate_participation_sold
where syndicate_picks_id=\''.$temp_pickdata->syndicate_picks_id. '\' and active=1 and syndicate_type = 0
GROUP BY syndicate_picks_id limit 1';
                }
                else {
                    // if assigning participations only check last hour for not processed orders
                    $str_crt_status = ' crt_status IN (4, 5) AND crt_date > DATE_SUB(NOW(), INTERVAL 1 HOUR) ';
                    $sql = 'SELECT SUM(cts_ticket_byDraw) as sum FROM carts c INNER JOIN syndicate_cart_subscriptions cs
    ON (c.crt_id = cs.crt_id)
					    WHERE '.$str_crt_status.' AND syndicate_picks_id = '.$temp_pickdata->syndicate_picks_id.'
					    GROUP BY syndicate_picks_id limit 1';
                }

                $tmp_preauth = DB::connection("mysql_external")->select($sql);

                if ($pre_sale_is_active == TRUE){
                    $sql = 'SELECT SUM(cts_ticket_byDraw) as sum FROM pre_sale_users psu INNER JOIN pre_sale_carts psc
    ON psu.id = psc.pre_sale_users_id AND psu.enabled INNER JOIN carts c ON psc.crt_id = c.crt_id
    INNER JOIN syndicate_cart_subscriptions cs ON (c.crt_id = cs.crt_id)
				WHERE c.crt_status = 1 AND syndicate_picks_id = '.$temp_pickdata['syndicate_picks_id'].'
				GROUP BY syndicate_picks_id limit 1';
                    $presale_participations = DB::connection("mysql_external")->select($sql);
                }
                else {
                    $presale_participations = 0;
                }

                if(empty($tmp_preauth)){
                    $tmp_preauth = 0;
                }else{
                    $tmp_preauth = $tmp_preauth[0]->sum;
                }

                if ($temp_pickdata->available_participations - ($tmp_preauth + $presale_participations) >= 2)
                {
                    $temp_pickdata->available_participations = $temp_pickdata->available_participations -
                        ($tmp_preauth + $presale_participations);
                    $available_picks[] = $temp_pickdata;
                }

            }
        }
        return $available_picks;
    }

    public static function insurance_black_list(){
        $sql = 'SELECT p.lot_id, p.countries_blacklist_ids FROM tickets_providers_information p
                INNER JOIN sat_cash_groups g ON p.scg_id = g.scg_id AND g.scg_type = 2
                WHERE p.active ORDER BY p.lot_id';
        $tmp_tpi = DB::connection("mysql_external")->select($sql);
        $insurance_black_list = [];

        foreach ($tmp_tpi as $value) {
            $insurance_black_list[$value->lot_id] = explode(',', $value->countries_blacklist_ids);
        }

        return $insurance_black_list;
    }

    public static function generatePicksWheels($lotto_info, $wheel_info, $cantRequested)
    {
        //array to return picks
        $picks = array();
        //for each combination requested
        for ($j=1; $j <= $cantRequested; $j++)
        {
            //array temporal
            $pick_balls = array();
            if ($wheel_info->wheel_balls == $lotto_info->lot_maxNum){
                for ($i = 1; $i <= $wheel_info->wheel_balls; $i++)
                {
                    $pick_balls[] = $i;
                }
            }
            else {
                //temp pick balls
                for ($i = 1; $i <= $wheel_info->wheel_balls; $i++)
                {
                    $new_number = false;
                    while (!$new_number)
                    {
                        $number = rand(1, $lotto_info->lot_maxNum);
                        if (!in_array($number, $pick_balls))
                        {
                            $pick_balls[] = $number;
                            $new_number = true;
                        }
                    }
                }
                //order pick balls
                sort($pick_balls);
            }
            //temp extra balls
            $pick_extras = array();
            //extra balls
            if ( !($wheel_info->wheel_type == 1 && $wheel_info->wheel_balls == $lotto_info->lot_pick_balls) )
            {
                for ($k = 1; $k <= $lotto_info->lot_pick_extra; $k++)
                {
                    $new_number = false;
                    while (!$new_number)
                    {
                        $number = rand($lotto_info->lot_extra_startNum, $lotto_info->lot_extra_maxNum);
                        if (!in_array($number, $pick_extras))
                        {
                            $pick_extras[] = $number;
                            $new_number = true;
                        }
                    }
                }
                //order extra balls
                sort($pick_extras);
            }
            $picks[] = array('balls' => $pick_balls, 'extras' => $pick_extras);
        }
        return $picks;
    }
}
