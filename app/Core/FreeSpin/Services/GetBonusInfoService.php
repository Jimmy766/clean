<?php

namespace App\Core\FreeSpin\Services;

use App\Core\FreeSpin\Models\CasinoFreeSpinsPromotions;
use App\Core\Base\Models\CoreModel;
use App\Core\Base\Repositories\JoinBuilder\CoreBuilder;
use App\Core\Base\Services\TranslateTextService;
use App\Core\Base\Services\SendLogConsoleService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class GetBonusInfoService
 * @package App\Services
 */
class GetBonusInfoService
{

    /**
     * @param $promoCode
     * @param $freeSpinType
     * @return CoreModel|CoreBuilder|Model|Builder|object|null
     */
    public function execute($user, $promoCode, $freeSpinType)
    {
        $columns = [
            'cf.*',
            'casino_freespins_promotions.total_uses',
            'casino_freespins_promotions.type as freespins_type',
            'casino_freespins_promotions.user_reg_date_before',
            'casino_freespins_promotions.freespins_promotion',
        ];

        $casinoFreeSpinsPromotions = CasinoFreeSpinsPromotions::query()
            ->join('casino_freespins as cf', 'casino_freespins_promotions.casino_freespins_id', '=', 'cf.id')
            ->where('casino_freespins_promotions.freespins_promotion', $promoCode)
            ->where('casino_freespins_promotions.type', $freeSpinType)
            ->where('cf.start_date', '<=', Carbon::now())
            ->where('cf.end_date', '>=', Carbon::now())
            ->first($columns);

        if ($casinoFreeSpinsPromotions === null) {
            $message = TranslateTextService::execute('not_exist_promotion_code');

            $array[ 'message' ]        = $message;
            $array[ 'promo_code' ]     = $promoCode;
            $array[ 'free_spin_type' ] = $freeSpinType;
            $array[ 'id_user' ]        = $user->usr_id;
            $array[ 'id_promo_code' ]  = null;

            $sendLogConsoleService = new \App\Core\Base\Services\SendLogConsoleService();
            $sendLogConsoleService->execute(request(), 'promo-code', 'access', 'terminate', $array);

            throw new UnprocessableEntityHttpException(
                $message, null, Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return $casinoFreeSpinsPromotions;
    }

}
