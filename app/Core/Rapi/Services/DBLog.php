<?php


namespace App\Core\Rapi\Services;


class DBLog
{
    private static $time = 0;
    private static $queries = [];
    private static $total_queries = 0;

    private static $query_details = [];

    private static $long_queries = [];

    private static $instance =  null;

    private function __construct(){}

    public static function getInstance(){
        if(!self::$instance){
            self::$instance = new DBLog();
        }

        return self::$instance;
    }

    public function queryTime($query){
        $sql = $query->sql;
        $time = $query->time;
        $con = $query->connectionName;
        $params = $query->bindings;

        self::$time += $time;
        $q = $con.":".self::convertQuery($sql);
        self::$total_queries++;

        if(isset(self::$queries[$q])){
            self::$queries[$q]++;
        }else{
            self::$queries[$q] = 1;
        }

        if($time >= 1000){
            self::$long_queries[$q] = $time;
        }

        self::$query_details[$q]["times"] = self::$queries[$q];
        self::$query_details[$q]["queries"][] = [
            "query" => $sql,
            "time" => $time,
            "con" => $con,
            "params" => var_export($params, true)
        ];

    }

    public function getTime(){
        return self::$time;
    }

    private function convertQuery($query){
        $query = preg_replace('~[\\\\/:?<>|\s]~', '_', $query);
        return preg_replace('~["\'´`]~', '', $query);
    }

    public function getQueries(){
        return self::$queries;
    }

    public function getLongQueries(){
        return self::$long_queries;
    }

    public function getDuplicateQueries($times = 1){
        return array_filter($this->getQueries(), function($query) use ($times) {
            return $query > $times;
        });
    }

    public function getSimpleQueries($times = 1){
        return array_filter($this->getQueries(), function($query) use ($times) {
            return $query <= $times;
        });
    }

    public function getQueryDetail($query){
        return str_replace(array("\n", "\r"), '', var_export(self::$query_details[$query], true));
    }

    public function getTotalQueries(){
        return self::$total_queries;
    }
}
