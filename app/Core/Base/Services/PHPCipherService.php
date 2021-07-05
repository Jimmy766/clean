<?php

namespace App\Core\Base\Services;

/**
 * Class PHPCipher
 * @package Cloud\Services
 */
class PHPCipherService
{

    public function generateToken($agentCode, $agentKey, $secretKey)
    {
        $timestamp    = time() * 1000;
        $hashToken    = md5($agentCode . $timestamp . $agentKey);
        $tokenPayLoad = $agentCode . '|' . $timestamp . '|' . $hashToken;
        return $this->encryptAES($secretKey, $tokenPayLoad);
    }

    public function encryptAES($secretKey, $tokenPayLoad)
    {
        $iv      = "RandomInitVector";
        $encrypt = openssl_encrypt($tokenPayLoad, "AES-128-CBC", $secretKey, OPENSSL_RAW_DATA, $iv);
        return base64_encode($encrypt);
    }

    function decryptAES($secretKey, $tokenPayLoad)
    {
        $iv = "RandomInitVector";
        return openssl_decrypt(base64_decode($tokenPayLoad), "AES-128-CBC", $secretKey, OPENSSL_RAW_DATA, $iv);
    }

}
