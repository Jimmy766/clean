<?php

namespace App\Core\Rapi\Controllers;

use App\Core\Rapi\Models\Deal;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;

class DealController extends ApiController
{

    /**
     * DealController constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->middleware('auth:api')->except('index','show');
        $this->middleware('client.credentials')->only( 'index','show');
    }


    /**
     *   @SWG\Definition(
     *     definition="Deal",
     *     @SWG\Property(
     *       property="identifier",
     *       type="integer",
     *       description="Deal Id",
     *       example="1"
     *     ),
     *     @SWG\Property(
     *       property="deal_promo_type",
     *       type="integer",
     *       description="Promo type Id",
     *       example="2"
     *     ),
     *     @SWG\Property(
     *       property="deal_promo_value",
     *       type="integer",
     *       description="Promo value",
     *       example="10"
     *     ),
     *     @SWG\Property(
     *       property="deal_uses",
     *       type="integer",
     *       description="Deal Uses Count",
     *       example="7"
     *     ),
     *     @SWG\Property(
     *       property="deal_max_uses",
     *       type="integer",
     *       description="Deal Max Uses Count",
     *       example="20"
     *     ),
     *     @SWG\Property(
     *       property="deal_tag",
     *       type="string",
     *       description="Deal Tag",
     *       example="#DEALS_DISCOUNT_PERCENT_BUY#"
     *     ),
     *     @SWG\Property(
     *       property="promotion",
     *       description="Promotion",
     *       type="array",
     *       @SWG\Items(ref="#/definitions/Promotion"),
     *     ),
     *   ),
     */
    /**
     * @SWG\Get(
     *   path="/deals",
     *   summary="Show deals list ",
     *   tags={"Deals"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *          @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Deal")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       if(self::client_deals(1)->isNotEmpty())
       {
           //$_day_deals = Deal::with([ 'promotion'])
             //  ->where('deal_active', 1)
           $_day_deals = Deal::with("promotion")
               ->where('deal_active', 1)
               ->get()->filter(function ($item) {
                   return ($item->promotion);
               });



           /*$arr_lot_aux=collect([]);
          $day_deals = collect([]);
          $_day_deals->each(function ($item) use ($day_deals,$arr_lot_aux) {

               if($item->promotion->promo_product_lot_id != 0) {
                   $lottos_by_promo = $item->lottosByPromo();
                   if(count($lottos_by_promo) == 1){
                       $day_deals->push($item);
                   }
                   else
                   {
                       foreach($lottos_by_promo as $lbp){
                           $day_deals->push($item);
                       }
                   }
               }
               else
               {
                   $day_deals->push($item);
               }

           }); */

           return $this->showAllNoPaginated($_day_deals);
       }


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Rapi\Models\Deal $deal
     * @return \Illuminate\Http\Response
     */
    public function show(Deal $deal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Core\Rapi\Models\Deal $deal
     * @return \Illuminate\Http\Response
     */
    public function edit(Deal $deal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request   $request
     * @param  \App\Core\Rapi\Models\Deal $deal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Deal $deal)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Core\Rapi\Models\Deal $deal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Deal $deal)
    {
        //
    }
}
