<?php

namespace App\Core\Rapi\Models;

use App\Core\Base\Traits\Utils;
use App\Core\Clients\Models\Client;
use App\Core\Messages\Models\PromotionExtraMessage;
use App\Core\Rapi\Transforms\PromotionTransformer;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use Utils;

    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $primaryKey = 'promotion_id';
    public $timestamps = false;
    public $transformer = PromotionTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'promo_description',
        'promo_currency',
        'promo_product',
        'promo_product_lot_id',
        'promo_product_syndicate_id',
        'promo_product_inf_id',
        'promo_product_rsyndicate_id',
        'promo_product_membership_id',
        'promo_product_scratches_id',
        'promo_usr_id',
        'code',
        'discount_type',
        'max_uses',
        'bonus_id',
        'promo_max_uses',
        'user_type',
        'start_date',
        'expiration_date',
        'usr_id',
        'aff_cookies',
        'lot_id',
        'syndicate_id',
        'applies_to_renewals',
        'all_tickets_nextDraw',
        'sys_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'promotion_id',
        'name',
        'promo_description',
        'promo_currency',
        'promo_product',
        'promo_product_lot_id',
        'promo_product_syndicate_id',
        'promo_product_inf_id',
        'promo_product_rsyndicate_id',
        'promo_product_membership_id',
        'promo_product_scratches_id',
        'promo_usr_id',
        'code',
        'discount_type',
        'max_uses',
        'bonus_id',
        'promo_max_uses',
        'user_type',
        'start_date',
        'expiration_date',
        'usr_id',
        'aff_cookies',
        'lot_id',
        'syndicate_id',
        'applies_to_renewals',
        'all_tickets_nextDraw',
        'sys_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function system() {
        return $this->belongsTo(System::class, 'sys_id', 'sys_id');
    }

    public function promotion_extra_messages() {
        return $this->hasMany(PromotionExtraMessage::class, 'promotion_id', 'promotion_id');
    }

    public function getExtraMessagesAttribute() {
        $messages = collect([]);
        $this->promotion_extra_messages->each(function ($item, $key) use ($messages){
            $message = $item->transformer ? $item->transformer::transform($item) : $item;
            $messages->push($message);
        });
        return $messages;
    }

    public function getExtraMessageByLangAttribute(){

        $language = $this->getClientLanguage();
        $one_extra_message = "";
        $messages = $this->promotion_extra_messages;
        $messages->each(function ($item) use (&$one_extra_message, $language){
            if($item->lang == $language)
            {
                $one_extra_message = $item->text;
            }
        });
        return $one_extra_message;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function promotion_usages() {
        return $this->hasMany(PromotionCodesUsage::class, 'promotion_id', 'promotion_id');
    }

    /**
     * @return bool
     */
    public function getPromotionCodeUsageAttribute(){
        return $this->promotion_usages()->where('usr_id','=',request()->user()->usr_id)->where('status','!=',3)->first() ? 1 : 0;
    }

    public function promotion_discount_levels($show_zero = false) {
        $promotion_discount_levels = null;
        switch ($this->promo_currency) {
            case 1:
                $promotion_discount_levels = $this->hasMany(PromotionDiscountLevel::class, 'promotion_id', 'promotion_id');
                if(!$show_zero) $promotion_discount_levels->where('discount_value', '>', 0);

                break;
            case 2:
                $promotion_discount_levels = $this->hasMany(PromotionDiscountLevel::class, 'promotion_id', 'promotion_id')
                    ->where('curr_code', '=', request()['country_currency']);
                if(!$show_zero) $promotion_discount_levels->where('discount_value', '>', 0);
                break;
        }
        return $promotion_discount_levels;
    }

    public function getPromotionDiscountLevelsAttributesAttribute() {
        $discount_levels = collect([]);
        $promotion_discount_levels = $this->promotion_discount_levels(true)->get();
        $promotion_discount_levels->each(function ($item, $key) use ($discount_levels) {
            $curr_code = ($item->curr_code != '') ? $item->curr_code : request()['country_currency'];
            $discount_level  = collect(["up_to"          => $item->high_value,
                                        "discount_value" => round((float)$item->discount_value,2),
                                        "curr_code"      => $curr_code]);
            $discount_levels->push($discount_level);
        });
        return $discount_levels;
    }

    public function getLotteriesAttribute() {
        return explode(",",$this->promo_product_lot_id);
    }

    public function promo_products() {
        return $this->promo_product == 1 ? collect([$this->promo_product]) : collect(explode(',', $this->promo_product));
    }

    public function lotteries() {
        return $this->promo_products()->contains(2) ?
            ($this->promo_product_lot_id != 0 ? collect(explode(',', $this->promo_product_lot_id)) : collect([0]) )
            : collect([]);
    }

    public function syndicates() {
        return $this->promo_products()->contains(3) ?
            $this->promo_product_syndicate_id != 0 ? collect(explode(',', $this->promo_product_syndicate_id)) : collect([0])
            : collect([]);
    }

    public function raffles() {
        return $this->promo_products()->contains(4) ?
            $this->promo_product_inf_id != 0 ? collect(explode(',', $this->promo_product_inf_id)) : collect([0])
            : collect([]);
    }

    public function raffle_syndicates() {
        return $this->promo_products()->contains(5) ?
            $this->promo_product_rsyndicate_id != 0 ? collect(explode(',', $this->promo_product_rsyndicate_id)) : collect([0])
            : collect([]);
    }

    public function memberships() {
        return $this->promo_products()->contains(6) ?
            $this->promo_product_membership_id != 0 ? collect(explode(',', $this->promo_product_membership_id)) : collect([0])
            : collect([]);
    }

    public function scratches() {
        return $this->promo_products()->contains(7) ?
            $this->promo_product_scratches_id != 0 ? collect(explode(',', $this->promo_product_scratches_id)) : collect([0])
            : collect([]);
    }

    public function bonus() {
        $site_id = Client::where('id', request()['oauth_client_id'])->first()->site_id;
        return $this->belongsTo(Bonus::class, 'bonus_id', 'id')
            ->where('active', '=', 1)
            ->where('sys_id', '=', $this->sys_id)
            ->whereIn('site_id', [0, $site_id]);
    }

    public function getBonusAttributesAttribute() {
        return $this->bonus ? $this->bonus->transformer::transform($this->bonus) : $this->bonus;
    }

    public function deal() {
        return $this->hasOne(Deal::class, 'promotion_id', 'promotion_id');
    }

    public function hasProducts()
    {
        return ($this->promo_product_lot_id != 0
            ||  $this->promo_product_syndicate_id != 0
            ||  $this->promo_product_inf_id != 0
            ||  $this->promo_product_rsyndicate_id != 0
            ||  $this->promo_product_membership_id != 0
            ||  $this->promo_product_scratches_id != 0);
    }

    public function getTagAttribute(){

        switch ($this->discount_type) {
            case 1:
                return $this->hasProducts() ? "#CART_STEP1_PROMO_HOME_APPLY_LOTTO#" : "#CART_STEP1_PROMO_HOME_APPLY#" ;
                break;
            case 2:
                $promo_product = $this->promo_products()->first();
                if(!$this->hasProducts())
                {
                    switch ($promo_product)
                    {
                        case 1:
                            return "#CART_STEP1_PROMO_HOME_APPLY_DISCOUNT#";
                            break;
                        case 2:
                            return "#CART_STEP1_PROMO_HOME_APPLY_DISCOUNT#";
                            break;
                        case 3:
                            return "#CART_STEP1_PROMO_SYNDICATE_DISCOUNT#";
                            break;
                        case 4:
                            return "#CART_STEP1_PROMO_RAFFLE_DISCOUNT#";
                            break;
                        case 5:
                            return "#CART_STEP1_PROMO_SYNDICATE_RAFFLE_DISCOUNT#";
                            break;
                        case 6:
                            return "#CART_STEP1_PROMO_MEMERSHIP_DISCOUNT#";
                            break;
                    }
                }
                else
                {
                    return ($promo_product == 6) ? "#CART_STEP1_PROMO_MEMERSHIP_DISCOUNT#" : "#CART_STEP1_PROMO_HOME_APPLY_DISCOUNT_LOTTO#";

                }

                break;
            case 3:
                $promo_product = $this->promo_products()->first();
                if(!$this->hasProducts())
                {
                    switch ($promo_product)
                    {
                        case 1:
                            return "#CART_STEP1_PROMO_HOME_APPLY_DISCOUNT#";
                            break;
                        case 2:
                            return "#CART_STEP1_PROMO_HOME_APPLY_DISCOUNT#";
                            break;
                        case 3:
                            return "#CART_STEP1_PROMO_SYNDICATE_DISCOUNT#";
                            break;
                        case 4:
                            return "#CART_STEP1_PROMO_RAFFLE_DISCOUNT#";
                            break;
                        case 5:
                            return "#CART_STEP1_PROMO_SYNDICATE_RAFFLE_DISCOUNT#";
                            break;
                        Default:
                            return "#CART_STEP1_PROMO_HOME_APPLY_DISCOUNT#";
                            break;
                    }
                }
                else
                {
                    return "#CART_STEP1_PROMO_HOME_APPLY_DISCOUNT_LOTTO#";
                }
                break;
            case 4:
                return "#CART_STEP1_PROMO_DEPOSIT_BONUS#";
                break;
            case 5:
                return "#CART_STEP1_PROMO_VIP_BONUS_GIFT#";
                break;
            case 6:
                return "#CART_STEP1_PROMO_MATCH_BONUS#";
                break;
            case 7:
                return "#CART_STEP1_PROMO_LOGIN_BONUS#";
                break;
            case 9:
                return '#CART_STEP1_PROMO_CASINO_BONUS#';
                break;
        }
        return null;
    }
}
