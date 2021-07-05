<?php


namespace App\Core\Telem\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use DB;

class TelemCartService
{

    private static $instance = null;
    private $error;
    private $trillonario_db;
    private function __construct()
    {}

    public static function getInstance(){
        if(!self::$instance){
            self::$instance =  new TelemCartService();
            self::$instance->trillonario_db = Config::get('database.connections.mysql_external.database');
        }
        return self::$instance;
    }

    public function telemCart(Request $request){
        try{
            // revisar como solicitar estos param en la request
            $usr_id = $request->user_id;
            $crt_id = $request->crt_id;

            $ip = $request->user_ip;
            $sys_id = $request->client_sys_id;

            $camp_id = $request->has("campaign_id") ? $request->campaign_id : 0;
            $telem_user_id = $request->agent_id;

            //log_action_admin($crt_id, $usr_id, 0, $_SERVER["REMOTE_ADDR"], 2, "");

            $actiontype = 2;

            $sql = 'SELECT act_msg FROM actiontypes_admin WHERE act_id = '.$actiontype . " limit 1";
            $rs = DB::connection("mysql_external")->select($sql);
            $msg = 'Wrong action type sent to log function';
            if ($rs)
            {
                $msg = $rs[0]->act_msg;
            }

            $sql = "INSERT INTO log_admin " .
                "SET crt_id = " . $crt_id . ", " .
                "sus_id = " . $telem_user_id . ", " .
                "usr_id = " . $usr_id . ", " .
                "sub_id = 0, " .
                "ip = '" . $ip . "', " .
                "log_date = NOW(), " .
                "log_actiontype = " . $actiontype . ", " .
                "log_msg = '" . $this->quote_smart($msg) . "'";

            DB::connection('mysql_external')->insert($sql);

            // add cart to tracking table
            DB::connection('mysql_external')->insert(
                "INSERT INTO cart_tracking (crt_id,telem_sus_id,telem_camp_id,reg_date,status)
values ($crt_id,'" . $telem_user_id . "',$camp_id,now(),0)");


            // add to mailing requests table
           /* DB::connection('mysql_external')->insert("INSERT INTO mailing_requests
    (type_id,origin,date_added,sent,usr_id,crt_id,params,is_test)
    values (2,'order_add.php',now(),0,$usr_id,$crt_id,'crt_id => $crt_id, usr_id => $usr_id',0)");*/


            //$this->saveCallOutcome($usr_id, $camp_id, $telem_user_id, 27, $crt_id, $sys_id);

            // si esta en master list and ( tiene menos prospect que el maximo posible )
            /*if ( $this->isDiaryStatusMasterList($usr_id) == TRUE &&
                ($this->getProspectsCount($telem_user_id) < $this->getMaxProspectCount()) ){
                $this->setDiaryStatusProspect($usr_id, $telem_user_id);
            }*/

            return true;
        }catch (\Exception $ex){

            $this->error = $ex->getMessage();

            return false;
        }
    }

    public function getError(){
        return $this->error;
    }


    function quote_smart($value){

        // Stripslashes
        if (get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }

        return  strip_tags($value);
    }

    /**	toma un contacto y lo deja en estado prospect asignado al agente recibido
     *	@param int $usr_id
     *		identificador del contacto
     *	@param int $agent_id
     *		identificador del agente
     *	@return
     */
    private function setDiaryStatusProspect($usr_id, $agent_id)
    {
        $sql = 'UPDATE telem_campaigns_users SET diary_status = 1, date_diary_status = NOW(),
                                 sus_id = '.$agent_id.' WHERE usr_id = '.$usr_id;
        DB::connection('mysql_external')->update($sql);
    }

    /**
     *
     * devuelve la cantidad maxima de prospects que puede tener un agente
     * @return mixed cantidad max de prospects que puede tener un agente
     * @throws \Exception
     */
    private function getMaxProspectCount()
    {
        $sql = "select ug.max_prospects_count from {$this->trillonario_db}.users_group ug
                inner join {$this->trillonario_db}.users_system us on ug.group_id = sus_groupId
                where us.sus_id =".request()->agent_id."
                and sus_groupId > 0
                and ug.group_active;";

        $resp = DB::connection("mysql_external")->select($sql);

        if (!isset($resp[0]) || !isset($resp[0]->max_prospects_count))
        {
            throw new \Exception("users_group.max_prospects_count not set");
        }

        $max_prospects_count = $resp[0]->max_prospects_count ;

        return $max_prospects_count;
    }

    /**
     * 	devuelve la cantidad de contactos en estado prospect de un agente
     *	puede ser el total en todas las campaigns o en una en particular
     *	@param int $agent_id
     *		identificador del agente
     *	@param int $campaign_id
     *		identificador de la campaign
     *		si vale 0: todas las campaigns
     *		si es > 0: solamente en esa campaign
     *	@return int
     */
    private function getProspectsCount($agent_id, $campaign_id=0)
    {
        $str_campaign	= ($campaign_id == 0) ? '' : ' AND camp_id = '.$campaign_id;
        $sql = 'SELECT COUNT(DISTINCT usr_id) as cnt FROM telem_campaigns_users WHERE
                                                                      sus_id = '.$agent_id.'
                                                                      AND diary_status = 1'.$str_campaign;
        $cant = DB::connection('mysql_external')->select($sql);
        return $cant[0]->cnt;
    }

    /**	verifica si un contacto esta en master list
     *	@param int $usr_id
     *		identificador del contacto
     *	@return boolean
     */
    private function isDiaryStatusMasterList($usr_id)
    {
        $sql = 'SELECT COUNT(*) as cnt FROM telem_campaigns_users WHERE diary_status <> 0 AND usr_id = '.$usr_id;
        $cant = DB::connection('mysql_external')->select($sql);

        if ($cant[0]->cnt == 0)
        {
            return TRUE;
        }

        return FALSE;
    }


    /**	verifica si un contacto esta en estado split commission
     *	@param int $usr_id
     *		identificador del contacto
     *	@return boolean
     */
    private function isDiaryStatusSplit($usr_id)
    {
        $sql = 'SELECT COUNT(*) as cnt FROM telem_campaigns_users WHERE diary_status = 4 AND usr_id = '.$usr_id;
        $cant = DB::connection("mysql_external")->select($sql);

        if ($cant[0]->cnt > 0)
        {
            return TRUE;
        }

        return FALSE;
    }

    /**	devuelve los datos de split de un contacto
     *	@param int $usr_id
     *		identificador del contacto
     *	@return
     *		array con informacion del split
     */
    private function getSplitInfoByUser($usr_id)
    {
        $sql = 'SELECT * FROM telem_split_contacts WHERE status = 1 AND usr_id = '.$usr_id . ' limit 1';
        $info = DB::connection("mysql_external")->select($sql);

        if (isset($info[0]) && isset($info[0]->usr_id))
        {
            return $info;
        }

        return array();
    }

    /**
     * @param $usr_id
     * @param $campaign_id
     * @param $agent_id
     * @param $call_status
     * @param int $crt_id
     * @param int $sys_id
     * @param string $call_date
     * @param string $language
     * @param string $upsell
     */
    private function saveCallOutcome($usr_id, $campaign_id, $agent_id,
                                     $call_status, $crt_id=0, $sys_id=0,
                                     $call_date='', $language='', $upsell=''){
        $split_info = '';
        if ($call_date == ''){
            $call_date = date('Y-m-d H:i:s', time());
        }
        if ($this->isDiaryStatusSplit($usr_id) === TRUE){
            $split_info = $this->getSplitInfoByUser($usr_id);
        }

        $str_outcome_split = (isset($split_info->usr_id)) ?
            ', is_split = 1, split_owner = '.$split_info->return_to_agent : '';

        // guardo la llamada
        $sql = 'INSERT INTO telem_calls SET usr_id = '.$usr_id.', camp_id = '.$campaign_id.', crt_id = '.$crt_id.
            ', sus_id = '.$agent_id.', typ_id = '.$call_status.', call_date = \''.$call_date.'\', sys_id = '.$sys_id.
            $str_outcome_split;
        DB::connection('mysql_external')->insert($sql);

        // actualizo el status del contacto y ya no queda marcado el campo to_call
        if($upsell == ''){
            $sql = 'UPDATE telem_campaigns_users SET to_call = 0,
                                 call_status = '.$call_status.', date_call_status = \''.$call_date.'\''.
                ' WHERE usr_id = '.$usr_id.' AND camp_id = '.$campaign_id;
        }else{

            $sql = 'UPDATE telem_upsell_diary SET call_status = '.$call_status.
                ', date_call_outcome = "'.$call_date.'" WHERE usr_id = '.$usr_id.' AND camp_id = '.$campaign_id.
                ' AND upsell_status = 0';
        }
        //echo("<br>".$sql);
        DB::connection('mysql_external')->update($sql);

    }


}
