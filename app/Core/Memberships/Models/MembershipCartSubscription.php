<?php

namespace App\Core\Memberships\Models;

use App\Core\Carts\Models\Cart;
use App\Core\Memberships\Models\Membership;
use App\Core\Rapi\Models\Bonus;
use App\Core\Memberships\Transforms\MembershipCartSubscriptionTransformer;
use Illuminate\Database\Eloquent\Model;

class MembershipCartSubscription extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'cts_id';
    protected $table = 'memberships_cart_subscriptions';
    public $transformer = MembershipCartSubscriptionTransformer::class;
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'crt_id',
        'memberships_id',
        'cts_price',
        'sub_id',
        'cts_renew',
        'cts_prc_id',
        'bonus_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'cts_id',
        'crt_id',
        'memberships_id',
        'cts_price',
        'cts_renew',
        'cts_prc_id',
        'bonus_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cart() {
        return $this->belongsTo(Cart::class, 'crt_id', 'crt_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function membership() {
        return $this->belongsTo(Membership::class, 'memberships_id', 'id');
    }

    public function bonus() {
        return $this->belongsTo(Bonus::class, 'bonus_id', 'id');
    }

    public function getBonusProductsAttribute() {

        $bonus = null;
        if ($this->bonus) {
            $bonus = $this->bonus->bonus_products_detail;
        }

        return $bonus ? $bonus : [];
    }
}
