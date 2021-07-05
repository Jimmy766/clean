<?php

    namespace App\Core\Memberships\Models;

    use App\Core\Memberships\Models\MembershipPrice;
    use App\Core\Memberships\Transforms\MembershipTransformer;
    use Illuminate\Database\Eloquent\Model;


    class Membership extends Model {
        protected $guarded = [];
        public $connection = 'mysql_external';
        public $timestamps = false;
        public $transformer = MembershipTransformer::class;

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'id',
            'name',
            'description',
            'level',
            'sys_id',
            'active',
        ];

        /**
         * The attributes that should be visible for arrays.
         *
         * @var array
         */
        protected $visible = [
            'id',
            'name',
            'description',
            'level',
            'sys_id',
            'active',
        ];

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasMany
         */
        public function prices() {
            return $this->hasMany(MembershipPrice::class, 'memberships_id')
                ->where('active', '=', 1)
                ->where('sys_id', '=', request('client_sys_id'));
        }

        public function getPriceAttribute() {
            $price_line = $this->prices->first();
            return $price_line ? $price_line->transformer::transform($price_line) : null;
        }

        public function getTagNameAttribute(){
            return '#'.$this->description.'#';
        }

    }
