<?php

namespace App\Core\Base\Traits;

trait Encoding
{
    public function encode(&$request) {
        foreach ($request->request as $item => $value) {
            $request->$item = $this->charsToUnicode($value);
        }
//        $request->headers->add(['Content-Type' => 'application/x-www-form-urlencoded; charset=iso-8859-1']);
    }

    public function charsToUnicode($str) {
        preg_match_all('/./u', $str, $matches);
        $c = "";
        foreach ($matches[0] as $m) {
            if (preg_match_all('/[\x{4e00}-\x{9fa5}]+/u', $m, $chinese)) {
                $c .= "&#" . base_convert(bin2hex(iconv('UTF-8', "UCS-4", $chinese[0][0])), 16, 10) . ';';
            } elseif (preg_match_all('/[\x{0400}-\x{04FF}]+/u', $m, $russian)) {
                $c .= "&#" . base_convert(bin2hex(iconv('UTF-8', "UCS-4", $russian[0][0])), 16, 10) . ';';
            } else {
                $c .= utf8_decode($m);
            }
        }
        // Unicode Blocks
        // https://www.regular-expressions.info/unicode.html
        return $c;
    }
}
