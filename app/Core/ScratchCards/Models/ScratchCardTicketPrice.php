<?php

    namespace App\Core\ScratchCards\Models;

    use App\Core\ScratchCards\Transforms\ScratchCardTicketPriceTransformer;
    use Illuminate\Database\Eloquent\Model;

    class ScratchCardTicketPrice extends Model
    {
        public $timestamps = false;
        public $transformer = ScratchCardTicketPriceTransformer::class;
        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $primaryKey = 'scratches_id';
        protected $table = 'scratches_ticket_price';
    }
