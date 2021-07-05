<?php


namespace App\Core\Rapi\Services;


class Curl
{

    /**
     * @param $url
     * @param string $type
     * @param string $postData
     * @param string $headers
     * @param string $proxy
     * @return bool|string
     */
    public static function sendRequest($url, $type = "GET", $postData = [], $headers = "", $proxy = "")
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($proxy != "") {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }

        if ($type == "POST") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }

        if ($headers != "") {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $result = curl_exec($ch);

        $Error = curl_error($ch);

        if ($Error != null && $Error != "") {
            return null;
        }

        if (!curl_errno($ch)) {
            curl_close($ch);
        } else {
            return null;
        }


        return json_decode($result);
    }


    /**
     * @param $url
     * @param string $postData
     * @param string $headers
     * @param string $proxy
     * @return bool|string
     */
    public static function post($url, $postData = "", $headers = "", $proxy = ""){
        return self::sendRequest($url, "POST", $postData, $headers, $proxy);
    }


    public static function get($url, $postData = "", $headers = "", $proxy = ""){
        if($postData != "")
            $url .= "?" . http_build_query($postData) ;
        return self::sendRequest($url, "GET", "", $headers, $proxy);
    }



}
