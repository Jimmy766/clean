<?php

namespace App\Core\Base\Traits;

use App\Core\Base\Services\LogType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Trait UtilsFormatText
 * @package App\Traits
 */
trait UtilsFormatText
{
    /**
     * function convert latin to utf8 fix
     * @param $text
     * @return array|false|string
     */
    public function convertTextCharset($text)
    {
        try {
            $text         = $this->convertFromLatin1ToUtf8Recursively($text);
            $test         = \GuzzleHttp\json_encode($text);
            $test         = response()->json($text);
            $jsonResponse = new JsonResponse();
            $jsonResponse->setData($text);
            if (mb_check_encoding($text, 'UTF-8') === false) {
                return '';
            }
            return $text;
        }
        catch(Exception $exception) {
            $errorMessage = $exception->getMessage();
            LogType::error(__FILE__, __LINE__, $errorMessage, [
                'exception' => $exception,
                'usersId'   => Auth::id(),
            ]);
            return '';
        }
    }

    /**
     * @param $text
     * @return array|false|string
     */
    public function convertFromLatin1ToUtf8Recursively($text)
    {
        if (is_string($text)) {
            return utf8_encode($text);
        } elseif (is_array($text)) {
            $ret = [];
            foreach ($text as $incremental => $data) {
                $ret[ $incremental ] = self::convertFromLatin1ToUtf8Recursively($data);
            }

            return $ret;
        } elseif (is_object($text)) {
            foreach ($text as $incremental => $data) {
                $text->$incremental = self::convertFromLatin1ToUtf8Recursively($data);
            }

            return $text;
        } else {
            return $text;
        }
    }
}
