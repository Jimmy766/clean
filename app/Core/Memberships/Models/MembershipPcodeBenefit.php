<?php

namespace App\Core\Memberships\Models;

use App\Core\Rapi\Models\Promotion;
use App\Core\Memberships\Transforms\MembershipPcodeBenefitTransformer;
use App\Core\Memberships\Transforms\PromotionMembershipBenefitTransformer;
use Illuminate\Database\Eloquent\Model;

class MembershipPcodeBenefit extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    public $timestamps = false;
    public $transformer = MembershipPcodeBenefitTransformer::class;

    private const TYPE_TAGS = [
        1 => [
            'title_tag'=> '#MGOLD_MY_BENEF_TEXT_1#',
            'description_tag'=> '#MGOLD_MY_BENEF_TEXT_1_DESC#',
        ],
        2 => [
            'title_tag'=> '#MGOLD_MY_BENEF_TEXT_2#',
            'description_tag'=> '#MGOLD_MY_BENEF_TEXT_2_ON_PURCHASE#',
        ],
        3 => [
            'title_tag'=> '#MPLAT_MY_BENEF_TEXT_1#',
            'description_tag'=> '#MGOLD_MY_BENEF_TEXT_2_ON_PURCHASE#',
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [
    ];

    /**
     * @return mixed
     */
    public function getTitleTagAttribute(){
        return self::TYPE_TAGS[$this->benefit_type]['title_tag'];
    }

    /**
     * @return mixed
     */
    public function getDescriptionTagAttribute() {
        return self::TYPE_TAGS[$this->benefit_type]['description_tag'];
    }

    /**
     * @return mixed
     */
    public function getPromoCodesAttribute() {
        $promotions = Promotion::whereIn('promotion_id',explode(',',$this->promotion_id))->get();
        $promo_codes = collect([]);
        $promotions->each(function ($item, $key) use ($promo_codes) {
            $item->transformer = PromotionMembershipBenefitTransformer::class;
            $promo_codes->push($item->transformer ? $item->transformer::transform($item) : $item);
        });
        return $promo_codes;
    }
}
