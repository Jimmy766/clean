<?php

namespace App\Core\Bonus\Models;

use App\Core\Syndicates\Models\Syndicate;
use App\Core\Rapi\Transforms\BonusProductTransformer;
use Illuminate\Database\Eloquent\Model;

class BonusProduct extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    public $timestamps = false;
    public $transformer = BonusProductTransformer::class;
    protected $table = 'bonuses_products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bonus_id',
        'bonus_tag',
        'product_type',
        'product_id',
        'product_quantity',
        'prc_id',
        'active',
    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'bonus_id',
        'bonus_tag',
        'product_type',
        'product_id',
        'product_quantity',
        'prc_id',
        'active',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function syndicate() {
        return $this->belongsTo(Syndicate::class, 'product_id', 'id');
    }

    /**
     * Gets the product detail by the product_type
     */
    public function getProductDetailAttribute() {
        switch ($this->product_type) {
            case 1: //Lotteries
                break;
            case 4: //Raffles
                break;
            case 2: //Syndicates
                $syndicate = $this->syndicate()
                    ->with('syndicate_prices')
                    ->first();
                $this->product_name = '#PLAY_GROUP_NAME_'.$syndicate->name.'#';
                $syndicate_price = $syndicate->syndicate_prices->where('prc_id','=',$this->prc_id)->first();
                if ($syndicate_price) {
                    $this->product_price =  $syndicate_price->transformer ? $syndicate_price->transformer::transform($syndicate_price) : $syndicate_price;
                }
                break;
            case 3: //Syndicates Raffle
                break;
            case 7: //Scratches
                break;
        }
    }


}
