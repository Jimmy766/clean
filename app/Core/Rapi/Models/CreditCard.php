<?php

namespace App\Core\Rapi\Models;

use Illuminate\Database\Eloquent\Model;

class CreditCard extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'cc_id';
    protected $table = 'ccards';

    public function credit_card_stat() {
        return $this->hasOne(CreditCardStat::class, 'cc_id', 'cc_id')
            ->where('total_chargebacks', '=', 0);
    }
}
