<?php

namespace App\Core\Base\Traits;

use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Carts\Models\Cart;
use App\Core\Carts\Models\CurrencyExchange;
use App\Core\Carts\Services\GetCrtTotalCartServices;
use Illuminate\Support\Facades\Cache;

trait CartUtils
{
    use ApiResponser;

    public function validateCart($crt_id) {
        $currency = request()['country_currency'];
        $array['request_currency'] = $currency;
        $sendLogConsoleService = new SendLogConsoleService();
        $cart = Cart::where('crt_id', $crt_id)->first();
        $user_id = request('user_id') ? request('user_id') : 0;
        $array['id_cart'] = $crt_id;
        $array['id_user'] = $user_id;
        if (!$cart) {
            $message = trans('lang.cart_valid');
            $array['message'] = $message;
            $sendLogConsoleService->execute(request(), 'error-cart', 'access', 'access',$array);
            return $this->errorResponse($message, 422);
        }
        $array['cart_currency'] = $cart->crt_currency;
        $cart_sys_id = $cart->site ? $cart->site->sys_id : null;
        $array['sys_id'] = $cart_sys_id;
        if (!$cart_sys_id) {
            $message = trans('lang.cart_valid');
            $array['message'] = $message;
            $sendLogConsoleService->execute(request(), 'error-cart', 'access', 'access',$array);
            return $this->errorResponse($message, 422);
        }
        if ((request('client_sys_id') != $cart_sys_id) || ($user_id != 0 && $user_id != $cart->usr_id)
            || $cart->crt_status != 0 || $cart->crt_lastStep > 2 || $cart->cart_type != 1) {
            $message = trans('lang.cart_forbidden');
            $array['message'] = $message;
            $sendLogConsoleService->execute(request(), 'error-cart', 'access', 'access',$array);
            return $this->errorResponse($message, 422);
        }
        if ($cart->crt_currency != request()['country_currency']) {
            $message = trans('lang.cart_different_currency');
            $array['message'] = $message;
            $sendLogConsoleService->execute(request(), 'error-cart', 'access', 'access',$array);
            return $this->errorResponse($message, 422);
        }
        return false;
    }


    public function cartAmounts($crt_id) {
        $cart = (!$crt_id instanceof Cart) ? Cart::where('crt_id', $crt_id)->first() : $crt_id;
        $cart->crt_from_account = 0;
        $total = GetCrtTotalCartServices::execute($cart);
        $cart->crt_total = $total;
        $cart->crt_price = $total;
        if ($cart->crt_promotion_code != '') {
            $promotion_code = $cart->crt_promotion_code;
            $cart->reset_promocode();
            $cart->apply_promo($promotion_code);
        }
        $cart->save();
        $this->unlock_cart($cart->crt_id);
    }

    public function convertCurrency( $from, $to) {
        $currencyExchange = CurrencyExchange::query()
            ->where( 'active', '=', 1 )
            ->where( 'curr_code_from', '=', $from )
            ->where( 'curr_code_to', '=', $to )
            ->firstFromCache(['exch_factor']);

        if($currencyExchange === null){
            return 1;
        }

        return $currencyExchange->exch_factor;
    }

    public function validate_by_country($crt_id) {
        $deleted = 0;
        $total = 0;
        $cart = Cart::where('crt_id', $crt_id)->first();
        if ($cart) {
            $lotteries = self::client_lotteries(1)->pluck('product_id');
            foreach ($cart->cart_subscriptions as $cart_subscription) {
                $total ++;
                if (!$lotteries->contains($cart_subscription->lot_id)) {
                    // TODO fix remove next Wednesday when paulimar fix picker
                    if($cart_subscription->lot_id !== 1000 && $cart_subscription->lot_id !==19){
                        $deleted ++;
                        $cart_subscription->delete();
                    }
                }
            }
            $cart->load('cart_subscriptions');

            //TODO remove comment not available now live lottery
//            $live_lotteries = self::client_live_lotteries(1)->pluck('product_id');
//            foreach ($cart->cart_live_subscriptions as $cart_live_subscription) {
//                $total ++;
//                if (!$live_lotteries->contains($cart_live_subscription->lot_id)) {
//                    $deleted ++;
//                    $cart_live_subscription->delete();
//                }
//            }
            $cart->load('cart_live_subscriptions');

            $syndicates = self::client_syndicates(1)->pluck('product_id');
            foreach ($cart->syndicate_cart_subscriptions as $syndicate_cart_subscription) {
                $total ++;
                if (!$syndicates->contains($syndicate_cart_subscription->syndicate_id)) {
                    $deleted ++;
                    $syndicate_cart_subscription->delete();
                }
            }
            $cart->load('syndicate_cart_subscriptions');

            $raffle_syndicates = self::client_raffles_syndicates(1)->pluck('product_id');
            foreach ($cart->syndicate_cart_raffles as $syndicate_cart_raffle) {
                $total ++;
                if (!$raffle_syndicates->contains($syndicate_cart_raffle->rsyndicate_id)) {
                    $deleted ++;
                    $syndicate_cart_raffle->delete();
                }
            }
            $cart->load('syndicate_cart_raffles');

            $raffles = self::client_raffles(1)->pluck('product_id');
            foreach ($cart->cart_raffles as $cart_raffle) {
                $total ++;
                if (!$raffles->contains($cart_raffle->inf_id)) {
                    $deleted ++;
                    $cart_raffle->delete();
                }
            }
            $cart->load('cart_raffles');

            $scratches = self::client_scratch_cards(1)->pluck('product_id');
            foreach ($cart->scratches_cart_subscriptions as $cart_scratch_card) {
                $total ++;
                if (!$scratches->contains($cart_scratch_card->scratches_id)) {
                    $deleted ++;
                    $cart_scratch_card->delete();
                }
            }
            $cart->load('scratches_cart_subscriptions');

            $casino = self::client_casino_games(1)->pluck('product_id');
            foreach ($cart->scratches_cart_subscriptions as $cart_scratch_card) {
                $total ++;
                if (!$scratches->contains($cart_scratch_card->scratches_id)) {
                    $deleted ++;
                    $cart_scratch_card->delete();
                }
            }
            $cart->load('scratches_cart_subscriptions');
        }
        return ['total_products'=> $total, 'deleted_products' => $deleted];
    }

    public function unlock_cart($crt_id) {
        if (Cache::has('lock_cart_' . $crt_id)) {
            Cache::forget('lock_cart_' . $crt_id);
            $this->record_log2('raffle_errors', 'UnLocking Cart: ' . $crt_id);
        } else {
            $this->record_log2('raffle_errors', 'No need to UnLock Cart: ' . $crt_id);
        }
    }

    /**
     * @param $crt_id
     * @return bool|\Illuminate\Http\JsonResponse
     */
    public function check_for_cart_lock($crt_id) {
        if ($this->is_lock($crt_id)) {
            for ($i = 0; $i < 10; $i++) {
                $this->record_log2('raffle_errors', 'Locked Cart: ' . $crt_id . ' Round ' . $i);
                sleep(1);
                if (!$this->is_lock($crt_id)) {
                    Cache::rememberForever('lock_cart_' . $crt_id, function () use ($crt_id) {
                        return $crt_id;
                    });
                    $this->record_log2('raffle_errors', 'UnLocked Cart: '.$crt_id.' Round'.$i);
                    break;
                }
            }
            if ($i >= 10) {
                return $this->errorResponse(trans('lang.cart_valid'), 422);
            }
        } else {
            Cache::rememberForever('lock_cart_' . $crt_id, function () use ($crt_id) {
                return $crt_id;
            });
            $this->record_log2('raffle_errors', 'Locking Cart: '.$crt_id);
        }
        return false;
    }

    /**
     * @param $crt_id
     * @return bool
     */
    public function is_lock($crt_id) {
        return Cache::has('lock_cart_' . $crt_id) ? true : false;
    }
}
