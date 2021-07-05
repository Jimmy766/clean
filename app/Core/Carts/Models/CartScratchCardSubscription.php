<?php

    namespace App\Core\Carts\Models;

    use App\Core\ScratchCards\Models\ScratchCard;
    use App\Core\ScratchCards\Models\ScratchCardPrice;
    use App\Core\ScratchCards\Models\ScratchCardSubscription;
    use App\Core\Carts\Transforms\CartScratchCardSubscriptionTransformer;
    use Illuminate\Database\Eloquent\Model;

    class CartScratchCardSubscription extends Model
    {
        public $timestamps = false;
        public $transformer = CartScratchCardSubscriptionTransformer::class;
        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $primaryKey = 'cts_id';
        protected $table = 'scratches_cart_subscriptions';

        /**
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function cart() {
            return $this->belongsTo(Cart::class, 'crt_id', 'crt_id');
        }

        public function scratch_card() {
            return $this->belongsTo(ScratchCard::class, 'scratches_id', 'id');
        }

        public function price() {
            return $this->hasOne(ScratchCardPrice::class, 'prc_id', 'cts_prc_id');
        }

        public function subscription() {
            return $this->hasOne(ScratchCardSubscription::class, 'scratches_cts_id', 'cts_id');
        }

        public function getPriceAttributesAttribute() {
            return $this->price->transformer ? $this->price->transformer::transform($this->price) : $this->price;
        }
    }
