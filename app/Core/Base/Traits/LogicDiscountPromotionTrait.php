<?php

namespace App\Core\Base\Traits;

use App\Core\Users\Models\UsersReferringCode;

/**
 * Trait LogicDiscountPromotionTrait
 * @package App\Core\Base\Traits
 */
trait LogicDiscountPromotionTrait
{

    /**
     * Si promo_product != 0,1 no aplica
     * Si promo_product == 0,1 y tiene membresia no aplica
     * @param $user_id
     * @param $promotion
     * @return bool
     */
    public function applyLoginBonusPromoCode($user_id, $promotion): bool
    {
        if ( !$user_id) {
            $discountLevel = $promotion->promotion_discount_levels
                ? $promotion->promotion_discount_levels->sortBy('high_value')
                    ->first() : null;
            $discountValue = $discountLevel ? $discountLevel->discount_value : null;

            $this->promotion_discount_value = $discountValue;
        } else {
            return false;
        }
        return true;
    }

    /**
     * Si promo_product != 0,1 -- calcula amount
     * Si promo_product == 0,1 y tiene membresia -- calcula amount sin membresia
     * @param $user_id
     * @param $promotion
     * @return bool
     */
    public function applyMatchBonusPromoCode($user_id, $promotion): bool
    {
        $amount = $this->getAmmount($promotion, $user_id);
        if ($amount > 0) {
            // Si amount > 0 busco high_value y discount_value mayor que amount
            $discountLevels = $promotion->promotion_discount_levels
                ? $promotion->promotion_discount_levels->where('high_value', '>', $amount)
                    ->sortBy('high_value') : null;
            // Si no selecciono el mayor disponible
            $discountLevel = $discountLevels
                ? $discountLevels->first()
                : $promotion->promotion_discount_levels->sortByDesc('high_value')
                    ->first();
            if ($discountLevel) {
                $this->promotion_discount_value = $discountLevel->discount_value;
                $this->promotion_high_value     = $discountLevel->high_value;
            } else {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * No tienen en cuenta promo_product
     * @param $user_id
     * @param $promotion
     * @return bool
     */
    public function applyVipPromoCodeNoDepositBonus($user_id, $promotion): bool
    {
        // Si es nuevo usuario y tiene compras
        if ($promotion->user_type == 2 && $user_id && $this->user_purchases()) {
            return false;
        }
        $discountLevel = $promotion->promotion_discount_levels
            ? $promotion->promotion_discount_levels->sortBy('high_value')
                ->first() : null;
        if ($discountLevel) {
            $this->promotion_discount_value = $discountLevel->discount_value;
        } else {
            return false;
        }

        return false;
    }

    /**
     * Si promo_product != 0,1 -- calcula amount con membresia
     * Si promo_product == 0,1 y tiene membresia -- calcula amount sin membresia
     * Si promo_product == 0,1 y no tiene membresia
     * @param $user_id
     * @param $promotion
     * @return bool
     */
    public function applyVipPromoCodeWithIsPercentageBased($user_id, $promotion): bool
    {
        $amount = $this->getAmmount($promotion, $user_id);
        if ($amount > 0) {
            $discountLevel = $promotion->promotion_discount_levels
                ? $promotion->promotion_discount_levels->where('high_value', '>', $amount)
                    ->sortBy('high_value')
                    ->first() : null;
            if ($discountLevel) {
                $this->promotion_discount_value = $discountLevel->discount_value;
            } else {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /*$user_id*
     * Si promo_product != 0,1 -- calcula amount con membresia
     * Si promo_product == 0,1 y tiene membresia -- calcula amount con membresia
     * Si promo_product == 0,1 y no tiene membresia -- no calcula nada
     * @param $user_id
     * @param $promotion
     * @param $code
     * @return bool
     */
    /**
     * @param $user_id
     * @param $promotion
     * @param $code
     * @return bool
     */
    public function applyAmountOrTicketsPromoCode($user_id, $promotion, $code): bool
    {
        if ($promotion->user_type == 2 && $user_id && $this->user_purchases()) {
            return false;
        }
        if ($user_id) {
            $userReferringCode = UsersReferringCode::find($user_id);
            if ($userReferringCode && $userReferringCode->usr_referring_code === $code) {
                return false;
            }
        }

        if (( $promotion->promo_product == '1' || $promotion->promo_product == '0' ) && $this->membership_cart_subscriptions->isEmpty(
            )) {
            $discountLevel = $promotion->promotion_discount_levels
                ? $promotion->promotion_discount_levels->sortBy('high_value')
                    ->first() : null;
        }
        // Si no aplica a todos calculo con membresias || aplica a todos y tiene membresias calculo amount sin membresias
        if (( $promotion->promo_product != '1' && $promotion->promo_product != '0' ) || ( ( $promotion->promo_product == '1' || $promotion->promo_product == '0' ) && $this->membership_cart_subscriptions->isNotEmpty(
                ) )) {
            $amount        = $this->amount_promo_cart($promotion);
            $discountLevel = $promotion->promotion_discount_levels
                ? $promotion->promotion_discount_levels->where('high_value', '>', $amount)
                    ->sortBy('high_value')
                    ->first() : null;
        }
        if ($discountLevel) {
            $crtDiscount = $discountLevel->discount_value;
            if ($crtDiscount > 0) {
                $this->crt_price                = round($this->crt_total - $crtDiscount, 2);
                $this->crt_discount             = $crtDiscount;
                $this->promotion_discount_value = $crtDiscount;
            } else {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Si promo_product != 0,1 -- calcula amount con membresias
     * Si promo_product == 0,1 -- no calcula nada
     * @param $user_id
     * @param $promotion
     * @return bool
     */
    public function percentageBased($user_id, $promotion): bool
    {
        // Si es nuevo usuario y tiene compras
        if ($promotion->user_type == 2 && $user_id && $this->user_purchases()) {
            return false;
        }
        if ($promotion->promo_product == '1' || $promotion->promo_product == '0') {
            $discountLevels = $promotion->promotion_discount_levels->where(
                'high_value',
                '>',
                $this->crt_total
            )
                ->sortBy('high_value');
            if ($discountLevels->isEmpty()) {
                return false;
            }
            $discountLevel = $discountLevels->first();
            $crtDiscount   = round(( $discountLevel->discount_value * $this->crt_total ) / 100, 2);
            $crtPrice      = round($this->crt_total - $crtDiscount, 2);
            if ($crtDiscount > 0) {
                $this->crt_discount             = $crtDiscount;
                $this->crt_price                = $crtPrice;
                $this->promotion_discount_value = $discountLevel->discount_value;
            } else {
                return false;
            }
        } else {
            $crtDiscount   = 0;
            $discountValue = 0;
            // calculo amount con membresias
            $amount = $this->amount_promo_cart($promotion);
            if ($amount > 0) {
                $discountLevel = $promotion->promotion_discount_levels
                    ? $promotion->promotion_discount_levels->where('high_value', '>', $amount)
                        ->sortBy('high_value')
                        ->first() : null;
                if ($discountLevel) {
                    $discountValue = $discountLevel->discount_value;
                    $promoDiscount = round(( $discountValue * $amount ) / 100, 2);
                    $crtDiscount   = round($promoDiscount, 2);
                }
            }
            if ($crtDiscount > 0) {
                $crtPrice                       = round($this->crt_total - $crtDiscount, 2);
                $this->crt_price                = $crtPrice;
                $this->crt_discount             = $crtDiscount;
                $this->promotion_discount_value = $discountValue;
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Calcula tickets extra por loterias
     * @param $user_id
     * @param $promotion
     * @param $code
     * @return bool
     */
    public function calculatePromoCodeCaseLottery($user_id, $promotion, $code): bool
    {
        if ($promotion->user_type == 2 && $user_id && $this->user_purchases()) {
            return false;
        }
        if ($user_id) {
            $usersReferringCode = UsersReferringCode::find($user_id);
            if ($usersReferringCode->usr_referring_code === $code) {
                return false;
            }
        }
        $ticketsExtra = 0;
        // si aplica a todos los productos o aplica a todas las loterias obtengo suscripciones
        if ($promotion->promo_product_lot_id == '0' || ( ( $promotion->promo_product == '1' || $promotion->promo_product == '0' ) && $this->membership_cart_subscriptions->isEmpty(
                ) )) {
            $cartSubscriptions = $this->cart_subscriptions ? $this->cart_subscriptions->where('lot_live', 0)
                : null;
        } else {
            // obtengo suscripciones del cart a las que aplica
            $cartSubscriptions = $this->cart_subscriptions ? $this->cart_subscriptions->whereIn(
                'lot_id',
                $promotion->lotteries()
            ) : null;
        }
        if ($cartSubscriptions && $cartSubscriptions->isNotEmpty()) {
            foreach ($cartSubscriptions as $cartSubscription) {
                // obtengo tickets por loterias segun precio de cada subscripcion
                $discountLevels = $promotion->promotion_discount_levels
                    ? $promotion->promotion_discount_levels->where(
                        'high_value',
                        '>',
                        $cartSubscription->cts_price
                    )
                        ->sortBy('high_value') : null;
                $discountLevel  = $discountLevels ? $discountLevels->first() : null;
                if ($discountLevel) {
                    $cartSubscription->cts_ticket_extra = $discountLevels->discount_value;
                    $cartSubscription->save();
                    $ticketsExtra += $discountLevels->discount_value;
                }
            }
        }
        if ($ticketsExtra > 0) {
            $this->promotion_discount_value = $ticketsExtra;
        } else {
            return false;
        }
        return true;
    }
}
