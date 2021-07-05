<?php

namespace App\Core\Rapi\Models;

use App\Core\Rapi\Services\Util;
use App\Core\Rapi\Transforms\TransactionTransformer;
use DB;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded=[];
    public $transformer = TransactionTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'name', 'order'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id', 'type', 'name', 'order', 'attributes_list'
    ];

    public function getAttributesListAttribute() {
        $connection = 'mysql_external';

        $user = request()->user()->usr_id;
        switch ($this->type_id) {
            case 0:
                $subscriptions = DB::connection($connection)
                    ->table('carts')
                    ->join('cart_suscriptions', 'carts.crt_id', '=', 'cart_suscriptions.crt_id')
                    ->join('subscriptions', 'subscriptions.cts_id', '=', 'cart_suscriptions.cts_id')
                    ->join('payways', 'carts.pay_id', '=', 'payways.pay_id')
                    ->where('carts.usr_id', '=', $user)
                    ->orderBy('carts.crt_date', 'desc')
                    ->select('carts.crt_id', 'carts.crt_price', 'carts.crt_total', 'carts.crt_discount',
                        'carts.crt_from_account', 'carts.crt_buyDate as crt_date', 'carts.crt_currency',
                        'carts.crt_pay_method', 'carts.pay_id', 'payways.pay_show_name', 'carts.cart_type')
                    ->distinct()
                    ->get();
                $syndicates = DB::connection($connection)
                    ->table('syndicate_subscriptions')
                    ->join('syndicate_cart_subscriptions', 'syndicate_subscriptions.syndicate_cts_id', '=', 'syndicate_cart_subscriptions.cts_id')
                    ->join('carts', 'syndicate_cart_subscriptions.crt_id', '=', 'carts.crt_id')
                    ->join('payways', 'carts.pay_id', '=', 'payways.pay_id')
                    ->where('carts.usr_id', '=', $user)
                    ->where('carts.crt_status', '!=', 3)
                    ->orderBy('carts.crt_date', 'desc')
                    ->select('carts.crt_id', 'carts.crt_price', 'carts.crt_total', 'carts.crt_discount',
                        'carts.crt_from_account', 'carts.crt_date' , 'carts.crt_currency' ,
                        'carts.crt_pay_method' , 'carts.pay_id', 'payways.pay_show_name', 'carts.cart_type')
                    ->distinct()
                    ->get();
                $memberships = DB::connection($connection)
                    ->table('memberships_subscriptions')
                    ->join('memberships_cart_subscriptions', 'memberships_subscriptions.cts_id', '=', 'memberships_cart_subscriptions.cts_id')
                    ->join('carts', 'memberships_cart_subscriptions.crt_id', '=', 'carts.crt_id')
                    ->join('payways', 'carts.pay_id', '=', 'payways.pay_id')
                    ->where('carts.usr_id', '=', $user)
                    ->orderBy('carts.crt_date', 'desc')
                    ->select('carts.crt_id', 'carts.crt_price', 'carts.crt_total', 'carts.crt_discount',
                        'carts.crt_from_account', 'carts.crt_date' , 'carts.crt_currency' ,
                        'carts.crt_pay_method' , 'carts.pay_id', 'payways.pay_show_name', 'carts.cart_type')
                    ->distinct()
                    ->get();
                $raffles = DB::connection($connection)
                    ->table('raffles_tickets')
                    ->join('cart_raffles', 'raffles_tickets.crf_id', '=', 'cart_raffles.crf_id')
                    ->join('carts', 'cart_raffles.crt_id', '=', 'carts.crt_id')
                    ->join('payways', 'carts.pay_id', '=', 'payways.pay_id')
                    ->where('carts.usr_id', '=', $user)
                    ->orderBy('carts.crt_date', 'desc')
                    ->select('carts.crt_id', 'carts.crt_price', 'carts.crt_total', 'carts.crt_discount',
                        'carts.crt_from_account', 'carts.crt_date' , 'carts.crt_currency' ,
                        'carts.crt_pay_method' , 'carts.pay_id', 'payways.pay_show_name', 'carts.cart_type')
                    ->distinct()
                    ->get();
                $raffles_syndicate = DB::connection($connection)
                    ->table('syndicate_raffle_subscriptions')
                    ->join('syndicate_cart_raffles', 'syndicate_raffle_subscriptions.rsyndicate_cts_id', '=', 'syndicate_cart_raffles.cts_id')
                    ->join('carts', 'syndicate_cart_raffles.crt_id', '=', 'carts.crt_id')
                    ->join('payways', 'carts.pay_id', '=', 'payways.pay_id')
                    ->where('carts.usr_id', '=', $user)
                    ->orderBy('carts.crt_date', 'desc')
                    ->select('carts.crt_id', 'carts.crt_price', 'carts.crt_total', 'carts.crt_discount',
                        'carts.crt_from_account', 'carts.crt_date' , 'carts.crt_currency' ,
                        'carts.crt_pay_method' , 'carts.pay_id', 'payways.pay_show_name', 'carts.cart_type')
                    ->distinct()
                    ->get();
                $scratches = DB::connection($connection)
                    ->table('scratches_subscriptions')
                    ->join('scratches_cart_subscriptions', 'scratches_subscriptions.scratches_cts_id', '=', 'scratches_cart_subscriptions.cts_id')
                    ->join('carts', 'scratches_cart_subscriptions.crt_id', '=', 'carts.crt_id')
                    ->join('payways', 'carts.pay_id', '=', 'payways.pay_id')
                    ->where('carts.usr_id', '=', $user)
                    ->orderBy('carts.crt_date', 'desc')
                    ->select('carts.crt_id', 'carts.crt_price', 'carts.crt_total', 'carts.crt_discount',
                        'carts.crt_from_account', 'carts.crt_date' , 'carts.crt_currency' ,
                        'carts.crt_pay_method' , 'carts.pay_id', 'payways.pay_show_name', 'carts.cart_type')
                    ->distinct()
                    ->get();
                $subscriptions =  $subscriptions->merge($syndicates)->merge($memberships)->merge($raffles)->merge($raffles_syndicate)->merge($scratches);
                $collection = collect([]);
                $subscriptions->each(function($item, $key) use ($collection) {
                    if ($item->pay_id == 41) {
                        $pay_method = '#PAY_METHOD_BONUS#';
                    } elseif ($item->pay_id == 246) {
                        $pay_method = '#PAY_METHOD_PRESALE#';
                    } elseif ($item->crt_pay_method == 0 || $item->pay_id == 25) {
                        $pay_method = '#PAY_METHOD_ACCOUNT#';
                    } elseif ($item->crt_pay_method == 2) {
                        $pay_method = '#PAY_METHOD_MIX#';
                    } elseif ($item->pay_id == 30) {
                        $pay_method = '#OTHER#';
                    } else {
                        $pay_method = $item->pay_show_name;
                    }
                    /*$collection->push([
                        'order' => $item->crt_id,
                        'date' => $item->crt_date,
                        'pay_method' => $pay_method,
                        'currency' => $item->crt_currency,
                        'amount' => $item->cart_type == 2 ? $item->crt_price : $item->crt_total - $item->crt_discount
                    ]);*/

                    $it['order'] = $item->crt_id;
                    $it['date'] = $item->crt_date;
                    $it['pay_method'] = $pay_method;
                    $it['currency'] = $item->crt_currency;
                    $it['amount'] = (double)Util::round($item->cart_type == 2 ? $item->crt_price : $item->crt_total - $item->crt_discount);
                    $collection->push($it);
                });
                $collection =$collection->sortByDesc('date');
                return $collection->values();
           /* case 1:
                $winnings = DB::connection($connection)
                    ->table('tickets')
                    ->join('subscriptions', 'tickets.sub_id', '=', 'subscriptions.sub_id')
                    ->where('subscriptions.usr_id', '=', $user)
                    ->where('tickets.tck_prize_usr', '>', 0)
                    ->select('tickets.tck_id', 'tickets.tck_updated', 'tickets.tck_status', 'tickets.tck_prize_usr', 'tickets.curr_code')
                    ->get();
                $collection = collect([]);
                $winnings->each(function($item, $key) use ($collection) {
                    $it['order'] = $item->tck_id;
                    $it['date'] = $item->tck_updated;
                    $it['pay_method'] = $item->tck_status;
                    $it['currency'] = $item->curr_code;
                    $it['amount'] = $item->tck_prize_usr;
                    $collection->push($it);
                });
                return $collection;*/
            case 2:
                $payments = DB::connection($connection)
                    ->table('payments')
                    ->join('users', 'users.usr_id', '=', 'payments.usr_id')
                    ->leftJoin('payways', 'payways.pay_id', '=', 'payments.pay_id')
                    ->leftjoin('payout_methods as payout_requested', 'payout_requested.payout_id', '=', 'payments.payout_requested_id')
                    ->leftjoin('payout_methods as payout_del_payway', 'payout_del_payway.payout_id', '=', 'payways.payout_id')
                    ->where('payments.usr_id', '=', $user)
                    ->where('payments.status_process', '=', 1)
                    ->where('status', '!=', "canceled")
                    ->select('payments.date_pay', 'payments.date_request', 'payments.amount', 'payments.amount_currency', 'payments.amount_usr',
                        'payments.amount_usr_curr', 'payments.id', 'payments.status', 'payments.pay_id', 'payout_requested.name as payout_requested_name',
                        'payways.pay_name', 'payout_del_payway.name as payout_del_payway_name')
                    ->get();
                $collection = collect([]);
                $payments->each(function($item, $key) use ($collection) {
                    $it['order'] = $item->id;
                    $it['date'] = $item->date_request;
                    $it['pay_method'] = !empty($item->payout_del_payway_name) ? 'Payout_name_'.$item->payout_del_payway_name :
                        !empty($item->payout_requested_name) ? 'Payout_name_'.$item->payout_requested_name : 'name_Other';
                    $it['currency'] = $item->amount_usr_curr;
                    $it['amount'] = (double)Util::round($item->amount_usr);
                    $collection->push($it);
                });
                $collection =$collection->sortByDesc('date');
                return $collection->values();
            case 5:
                $deposits = DB::connection($connection)
                    ->table('carts')
                    ->join('payways', 'payways.pay_id', '=', 'carts.pay_id')
                    ->where('carts.usr_id', '=', $user)
                    ->where('carts.cart_type', '=', 3)
                    ->whereIn('carts.crt_status', [2,5])
                    ->orderBy('carts.crt_date', 'desc')
                    ->select('carts.crt_id', 'carts.crt_price', 'carts.crt_total', 'carts.crt_discount',
                        'carts.crt_from_account', 'carts.crt_buyDate as crt_date' , 'carts.crt_currency' ,
                        'carts.crt_pay_method' , 'carts.pay_id', 'carts.crt_pay_method', 'payways.pay_name', 'carts.cart_type')
                    ->distinct()
                    ->get();
                $collection = collect([]);
                $deposits->each(function($item, $key) use ($collection) {
                    $it['order'] = $item->crt_id;
                    $it['date'] = $item->crt_date;
                    $it['pay_method'] = $item->pay_name;
                    $it['currency'] = $item->crt_currency;
                    $it['amount'] = (double)Util::round($item->crt_price);
                    $collection->push($it);
                });
                $collection =$collection->sortByDesc('date');
                return $collection->values();
            default:
                return collect([]);
        }
    }
}
