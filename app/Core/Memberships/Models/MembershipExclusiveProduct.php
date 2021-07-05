<?php

    namespace App\Core\Memberships\Models;

    use App\Core\Rapi\Models\Bonus;
    use App\Core\Syndicates\Models\Syndicate;
    use App\Core\Memberships\Transforms\MembershipExclusiveProductSyndicateTransformer;
    use App\Core\Memberships\Transforms\MembershipExclusiveProductTransformer;
    use Illuminate\Database\Eloquent\Model;


    class MembershipExclusiveProduct extends Model {
        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $table = 'memberships_exclusive_products';
        public $timestamps = false;
        public $transformer = MembershipExclusiveProductTransformer::class;

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'product_id',
            'bonus_id',
            'prc_id',
            'product_type',
            'active',
        ];

        /**
         * The attributes that should be visible for arrays.
         *
         * @var array
         */
        protected $visible = [
            'id',
            'product_id',
            'bonus_id',
            'prc_id',
            'product_type',
            'active',
        ];

        /**
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function syndicate() {
            return $this->belongsTo(Syndicate::class, 'product_id', 'id')
                ->with('syndicate_lotteries.lottery')
                ->whereHas('syndicate_prices.syndicate_price_lines',function ($query) {
                    return $query->where('prc_id','=',$this->prc_id);
                });
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function bonus() {
            return $this->belongsTo(Bonus::class, 'bonus_id', 'id');
        }

        /**
         * @return mixed
         */
        public function getBonusProductsAttribute() {
            $bonus_products_detail = $this->bonus->bonus_products_detail;
            $this->bonus_price_total = $this->bonus->bonus_price_total;
            return $bonus_products_detail;
        }

        public function getProductAttribute(){
            $product = [];
            switch ($this->product_type){
                case 1: //Lotteries
                    break;
                case 4: //Raffles
                    break;
                case 2: //Syndicates
                    $syndicate = $this->syndicate()->first();
                    $syndicate->exclusive_product_prc_id = $this->prc_id;
                    $syndicate->transformer = MembershipExclusiveProductSyndicateTransformer::class;
                    $product = $syndicate->transformer::transform($syndicate);
                    break;
                case 3: //Syndicates Raffle
                    break;
                case 7: //Scratches
                    break;
            }
            return $product;
        }
    }
