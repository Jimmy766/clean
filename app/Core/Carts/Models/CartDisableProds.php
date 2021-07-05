<?php

namespace App\Core\Carts\Models;

use App\Core\Base\Traits\ApiResponser;

class CartDisableProds extends Cart
{
    use ApiResponser;

    private $blocked_products = [];
    private $has_blocked_products = false;

    public function getBlockedProducts(){
        return $this->blocked_products;
    }

    /**
     * @return bool
     */
    public function hasBlockedProducts(){

        if (request('user_id') != 0) {
            $country_id = [request('client_country_id'), request('user_country')];
        } else {
            $country_id = [request('client_country_id')];
        }

        if($this->has_blocked_products)
            $this->record_log2('access',
                str_replace(
                    array("\n", "\r"), '',
                    "CART_HAS_BLOCKED_PRODUCTS " .
                    var_export([
                        "country" => $country_id,
                        "client" => request('user_id'),
                        "products" => $this->blocked_products
                    ], true))
                );

        return $this->has_blocked_products;
    }

    private function checkBlockedProducts(){
        if($this->hasBlockedSubscriptions())
            return true;
        if($this->hasBlockedSyndicateSubscriptions())
            return true;
        if($this->hasBlockedRaffles())
            return true;
        if($this->hasBlockedRafflesSyndicate())
            return true;
        if($this->hasBlockedLiveSubscriptions())
            return true;
        if($this->hasBlockedScratches())
            return true;
        if($this->hasBlockedMemberships())
            return true;

        return false;
    }


    /**
     * @return bool
     */
    public function hasBlockedSubscriptions() {
        $can_play_lotteries = self::client_lotteries(1,0)->pluck('product_id');

        //Blocked lotteries
        $blocked_lottery = $this->cart_subscriptions()
            ->whereNotIn('lot_id', $can_play_lotteries)
            ->first();

        array_push($this->blocked_products, ["subscriptions" => $blocked_lottery]);

        if($blocked_lottery){
            return true;
        }

        return false;
    }

    public function hasBlockedSyndicateSubscriptions() {
        $can_play_syndicate_subscriptions = self::client_syndicates(1,0)->pluck('product_id');

        $blocked_syndicate_subscriptions = $this->syndicate_cart_subscriptions()
                ->whereNotIn('syndicate_id', $can_play_syndicate_subscriptions)
                ->first();

        array_push($this->blocked_products, ["syndicate_subscriptions" =>
            $blocked_syndicate_subscriptions]);

        if($blocked_syndicate_subscriptions){
            return true;
        }

        return false;
    }

    public function hasBlockedRaffles() {

        $can_play_raffles = self::client_raffles(1,0)->pluck('product_id');

        $blocked_raffles = $this->cart_raffles()
            ->whereNotIn('inf_id', $can_play_raffles)
            ->first();

        array_push($this->blocked_products, ["raffles" => $blocked_raffles]);

        if($blocked_raffles){
            return true;
        }

        return false;
    }

    public function hasBlockedRafflesSyndicate() {

        $can_play_raffles_syndicate = self::client_raffles_syndicates(1,0)->pluck('product_id');

        $blocked_raffles = $this->syndicate_cart_raffles()
            ->whereNotIn('rsyndicate_id', $can_play_raffles_syndicate)
            ->first();

        array_push($this->blocked_products, ["syndicate_raffles" => $blocked_raffles]);

        if($blocked_raffles){
            return true;
        }

        return false;
    }

    public function hasBlockedLiveSubscriptions() {
        $can_play_live_subscriptions = self::client_live_lotteries(1,0)->pluck('product_id');

        $blocked_live_subscriptions = $this->cart_live_subscriptions()
            ->whereNotIn('lot_id', $can_play_live_subscriptions)
            ->first();

        array_push($this->blocked_products, ["live_subscriptions" =>
            $blocked_live_subscriptions]);

        if($blocked_live_subscriptions){
            return true;
        }

        return false;
    }

    public function hasBlockedScratches() {

        $can_play_scratches_subscriptions = self::client_scratch_cards(1,0)->pluck('product_id');

        $blocked_scratches_subscriptions = $this->scratches_cart_subscriptions()
            ->whereNotIn('scratches_id', $can_play_scratches_subscriptions)
            ->first();

        array_push($this->blocked_products, ["scratches_subscriptions" =>
            $blocked_scratches_subscriptions]);

        if($blocked_scratches_subscriptions){
            return true;
        }

        return false;

    }

    public function hasBlockedMemberships() {

        $can_play_memberships = self::client_memberships(1,0)->pluck('product_id');

        $blocked_memberships = $this->membership_cart_subscriptions()
            ->whereNotIn('memberships_id', $can_play_memberships)
            ->first();

        array_push($this->blocked_products, ["memberships" => $blocked_memberships]);

        if($blocked_memberships){
            return true;
        }

        return false;

    }

    public static function from(Cart $cart)
    {
        $newCart = new self();

        foreach ($cart->getAttributes() as $key => $attribute) {
            $newCart->{$key} = $attribute;
        }

        $newCart->has_blocked_products = $newCart->checkBlockedProducts();

        return $newCart;
    }

}
