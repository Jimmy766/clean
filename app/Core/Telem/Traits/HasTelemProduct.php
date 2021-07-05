<?php


namespace App\Core\Telem\Traits;


use App\Core\Telem\Services\TelemService;

trait HasTelemProduct
{
    private $available_prods = [];
    private $has_available_syndicate_wheels = false;
    private $has_available_syndicate_raffles = false;
    private $has_available_raffles = false;
    private $has_available_lottery_wheels = false;
    private $has_available_lottery_wheels_full = false;
    private $has_asked_db = false;


    public function getAvailableProducts(){
        if(!$this->has_asked_db){
            $group = $this->group;
            if($group){
                $this->available_prods = TelemService::availableProducts($group->group_id,
                    request()->client_sys_id);
                $this->has_asked_db = true;
            }
        }
        $av_prods = $this->available_prods;

        if(isset($av_prods["syndicates_wheels"]) && $av_prods["syndicates_wheels"] != -1 ){
            $this->has_available_syndicate_wheels = true;
        }

        if(isset($av_prods["raffles"]) && $av_prods["raffles"] != -1){
            $this->has_available_raffles = true;
        }

        if(isset($av_prods["syndicates_raffles"]) && $av_prods["syndicates_raffles"] != -1 ){
            $this->has_available_syndicate_raffles = true;
        }

        if(isset($av_prods["wheels"]) && $av_prods["wheels"] != -1 ){
            $this->has_available_lottery_wheels = true;
        }

        if(isset($av_prods["wheels_full"]) && $av_prods["wheels_full"] != -1 ){
            $this->has_available_lottery_wheels_full = true;
        }

        return $this->available_prods;
    }

    public function hasSyndicateWheelsAvailable(){
        if(empty($this->available_prods)){
            $this->getAvailableProducts();
        }
        return $this->has_available_syndicate_wheels;
    }

    public function hasSyndicateRafflesAvailable(){
        if(empty($this->available_prods)){
            $this->getAvailableProducts();
        }
        return $this->has_available_syndicate_raffles;
    }

    public function hasRafflesAvailable(){
        if(empty($this->available_prods)){
            $this->getAvailableProducts();
        }
        return $this->has_available_raffles;
    }

    public function hasLotteryWheelsAvailable(){
        if(empty($this->available_prods)){
            $this->getAvailableProducts();
        }
        return $this->has_available_lottery_wheels;
    }

    public function hasLotteryWheelsFullAvailable(){
        if(empty($this->available_prods)){
            $this->getAvailableProducts();
        }
        return $this->has_available_lottery_wheels_full;
    }

    public function syndicateWheels(){

        if(empty($this->available_prods)){
            $this->getAvailableProducts();
        }

        if(isset($this->available_prods["syndicates_wheels"])){
           return $this->available_prods["syndicates_wheels"];
        }

        return -1;
    }

    public function syndicateRaffles(){

        if(empty($this->available_prods)){
            $this->getAvailableProducts();
        }

        if(isset($this->available_prods["syndicates_raffles"])){
            return $this->available_prods["syndicates_raffles"];
        }

        return -1;
    }

    public function raffles(){

        if(empty($this->available_prods)){
            $this->getAvailableProducts();
        }

        if(isset($this->available_prods["raffles"])){
            return $this->available_prods["raffles"];
        }

        return -1;
    }

    public function lotteryWheels(){

        if(empty($this->available_prods)){
            $this->getAvailableProducts();
        }

        if(isset($this->available_prods["wheels"])){
            return $this->available_prods["wheels"];
        }

        return -1;
    }

    public function lotteryWheelsFull(){

        if(empty($this->available_prods)){
            $this->getAvailableProducts();
        }

        if(isset($this->available_prods["wheels_full"])){
            return $this->available_prods["wheels_full"];
        }

        return -1;
    }

}
