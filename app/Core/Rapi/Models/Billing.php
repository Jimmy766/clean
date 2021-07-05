<?php

namespace App\Core\Rapi\Models;

use App\Core\Carts\Models\Cart;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'bil_id';
    protected $table = 'billings';

    public function credit_card() {
        return $this->hasOne(CreditCard::class, 'tpps_ccard_id', 'tpps_ccard_id')
            ->where('cc_visible', '=', 1)
            ->whereHas('credit_card_stat');
    }

    public function cart() {
        return $this->belongsTo(Cart::class, 'crt_id', 'crt_id')
            ->whereIn('crt_status', [2, 4, 5]);
    }
}
