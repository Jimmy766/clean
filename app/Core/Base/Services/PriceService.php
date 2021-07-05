<?php


namespace App\Core\Base\Services;


class PriceService
{

    public static function translateMesure($prc_time, $prc_time_type){
        $measure = null;

        if ($prc_time_type == 1) {
            if ($prc_time > 1) {
                $measure = trans('lang.weeks');
            } else {
                $measure = trans('lang.week');
            }
        } elseif($prc_time_type == 0 && $prc_time > 0) {
            if ($prc_time > 1) {
                $measure = trans('lang.months');
            } else {
                $measure = trans('lang.month');
            }
        }

        return $measure;
    }

    public static function translateMesureSyndicate($prc_time, $prc_time_type){
        $measure = null;
        if($prc_time == 1){
            switch ($prc_time_type) {
                case 0:
                    $measure = trans('lang.month');
                    break;
                case 1:
                    $measure = trans('lang.week');
                    break;
                case 2:
                    $measure = 'draw';
                    break;
                default:
                    break;
            }

            return $measure;
        }

        switch ($prc_time_type) {
            case 0:
                $measure = trans('lang.months');
                break;
            case 1:
                $measure = trans('lang.weeks');
                break;
            case 2:
                $measure = 'draws';
                break;
            default:
                break;
        }

        return $measure;
    }
}
