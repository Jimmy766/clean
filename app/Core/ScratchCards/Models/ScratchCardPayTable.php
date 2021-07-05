<?php

    namespace App\Core\ScratchCards\Models;

    use App\Core\ScratchCards\Transforms\ScratchPayTableTransformer;
    use Illuminate\Database\Eloquent\Model;


    class ScratchCardPayTable extends Model
    {
        public $timestamps = false;
        public $transformer = ScratchPayTableTransformer::class;
        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $primaryKey = 'paytable_id';
        protected $table = 'scratches_paytable';

        public function getOrderAttribute() {
            return $this->paytable_tier;
        }
    }
