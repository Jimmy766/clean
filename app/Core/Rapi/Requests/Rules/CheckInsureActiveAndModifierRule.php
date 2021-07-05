<?php

namespace App\Core\Rapi\Requests\Rules;

use App\Core\Base\Classes\ModelConst;
use App\Core\Countries\Services\GetCountryByCodeCountryService;
use App\Core\Lotteries\Services\Boosted\CalculateMountsBoostedJackpotService;
use App\Core\Lotteries\Services\Boosted\FilterBoostedJackpotExceedLimitService;
use App\Core\Lotteries\Services\Boosted\FilterBoostedJackpotModifierService;
use App\Core\Lotteries\Services\CheckLotteriesNotExceedLimitJackpotService;
use App\Core\Lotteries\Services\GetIsLotteryPriceModifierExistService;
use App\Core\Lotteries\Services\GetLotteriesAndCheckInsureBlackListService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class CheckInsureActiveAndModifierRule
 * @package App\Http\Requests\Rules
 */
class CheckInsureActiveAndModifierRule implements Rule
{
    /**
     * @var array|Application|Request|string
     */
    private $request;

    /**
     * @var string
     */
    private $attribute;
    /**
     * @var string
     */
    private $message;

    public function __construct()
    {
        $this->request = request();
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;

        $lottery = $this->request->input('lot_id');

        if ($lottery === null) {
            $cartSubscription = $this->request->route('cart_subscription');
            if ($cartSubscription === null) {
                $this->message = __('please send cart_subscription');
                return false;
            }
            $lottery = $cartSubscription->lottery;
            $lottery = $lottery->lot_id;
        }

        $insureModifier = $this->request->input('type_insure_modifier');
        if ($insureModifier === null && $attribute !== null) {
            $this->message = __('please select type jackpot');
            return false;
        }

        $idsLotteries = [ $lottery ];
        $relations    = [ 'lotteriesBoostedJackpot.lotteriesModifier', 'prices.price_lines' ];
        $idUser       = Auth::id();


        [ $enableBoostedJackpot, $lotteries ] = $this->validateIfBoostedJackpotIsEnable(
            $idsLotteries, $relations, $idUser, $insureModifier
        );


        $request = $this->request;
        $existModifier = true;
        if($request->boosted_modifier != 0){
            $existModifier = $this->validateExistModifier($lotteries, $value);
        }

        $lottery = $lotteries->first();
        if($lottery === null){
            $this->message = __('not exist lottery');
            return false;
        }
        $price = $lottery->prices->where('prc_id', $request->prc_id)->first();
        if($price === null){
            $this->message = __('not exist price');
            return false;
        }
        if ($request->boosted_modifier !== null && $request->boosted_modifier != 0) {
            $pricesLines = $price->prices_lines_attributes;
            $pricesLines = collect($pricesLines);
            $pricesLines = $pricesLines->where('modifier_id', $request->boosted_modifier);
            if ($pricesLines->count() === 0) {
                $this->message = __('price has not modifier');
                return false;
            }
        }

        if ($enableBoostedJackpot === false) {
            return false;
        }
        if ($existModifier === false) {
            return false;
        }

        return true;
    }

    public function validateIfBoostedJackpotIsEnable($idsLotteries, $relations, $idUser, $insureModifier)
    {
        $getCountryCode = new GetCountryByCodeCountryService();

        $lotteryInsureBlackListService = new GetLotteriesAndCheckInsureBlackListService($getCountryCode);

        $lotteries = $lotteryInsureBlackListService->execute(
            $idsLotteries, null, $relations, $idUser
        );

        $lottery = $lotteries->first();

        if ($lottery !== null) {
            if ($lottery->insure_boosted_jackpot === false || $insureModifier != ModelConst::LOTTERY_INSURE_MODIFIER) {
                $this->message = __('jackpot_is_not_enable');
                return [ false, $lotteries ];
            }
        }

        return [ true, $lotteries ];
    }

    public function validateExistModifier($lotteries, $modifierBoosted)
    {
        $calculateMountBoostedService = new CalculateMountsBoostedJackpotService();
        $filterBoostedJackpotModifierService    = new FilterBoostedJackpotModifierService();
        $filterBoostedJackpotExceedLimitService = new FilterBoostedJackpotExceedLimitService($calculateMountBoostedService);

        $checkLimitBoostedJackpot = new CheckLotteriesNotExceedLimitJackpotService(
            $filterBoostedJackpotModifierService, $filterBoostedJackpotExceedLimitService
        );

        $lotteries = $checkLimitBoostedJackpot->execute($lotteries);
        $lottery   = $lotteries->first();

        $getIsLotteryPriceModifierExist = new GetIsLotteryPriceModifierExistService();

        $modifierEvaluate = $modifierBoosted;

        $existModifier = $getIsLotteryPriceModifierExist->execute($lottery, $modifierEvaluate);
        if ($existModifier === false) {
            $this->message = __('modifier_not_valid');
            return false;
        }

        return true;
    }

    public function message()
    {
        return $this->message;
    }
}
