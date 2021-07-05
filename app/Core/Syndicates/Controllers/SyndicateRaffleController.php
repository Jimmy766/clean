<?php

namespace App\Core\Syndicates\Controllers;

use App\Core\Users\Notifications\PasswordToken;
use App\Core\Base\Services\ClientService;
use App\Core\Base\Services\OrcaService;
use App\Core\Syndicates\Models\SyndicateRaffle;
use App\Core\Syndicates\Models\SyndicateRaffleRaffle;
use App\Core\Base\Traits\Pixels;
use App\Core\Syndicates\Transforms\SyndicateRaffleTransformer;
use App\Core\Users\Models\User;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SyndicateRaffleController extends ApiController
{
    use Pixels;

    public function __construct() {
        parent::__construct();
        $this->middleware('auth:api')->except('show', 'index');
        $this->middleware('client.credentials')->only('index', 'show');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Get(
     *   path="/syndicate_raffles",
     *   summary="Show raffles syndicates list ",
     *   tags={"Raffles Syndicates"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/RaffleSyndicate")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function index(Request $request) {

        if(ClientService::isOrca()){
            $rules = [
                'agent_id' => 'required'
            ];
            $this->validate($request, $rules);
            $syndicate_raffles =  OrcaService::getSyndicateRaffles();
        }else{
            $idsProducts = self::client_raffles_syndicates(1)->pluck('product_id');
            $relations = [
                'syndicate_raffle_raffles',
                'syndicate_raffle_raffles.raffle',
                'syndicate_raffle_raffles.raffle.draw_active',
                'routingFriendly',
            ];
            $syndicate_raffles = SyndicateRaffle::query()->with($relations)
                ->whereIn('id', $idsProducts)
                ->where('active', '=', 1)
                ->getFromCache()
                ->filter(function (SyndicateRaffle $item) {
                    return $item->isActive() ? true : false;
                });
        }



        return $this->showAllNoPaginated($syndicate_raffles);
    }


    /**
     * @SWG\Get(
     *   path="/syndicate_raffles/{syndicate_raffle}",
     *   summary="Show raffle syndicate details ",
     *   tags={"Raffles Syndicates"},
     *   @SWG\Parameter(
     *     name="syndicate_raffle",
     *     in="path",
     *     description="Raffle Syndicate Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(
     *         property="data",
     *         allOf={
     *          @SWG\Schema(ref="#/definitions/RaffleSyndicate"),
     *         }
     *       ),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function show(SyndicateRaffle $syndicate_raffle) {
        $prices = $syndicate_raffle->syndicate_raffle_prices;
        $price = $prices->first();
        $price_id = $price->prc_id;
        $price_line_id = $price->price_line['identifier'];
        $product = [
            'id' => $syndicate_raffle->inf_id,
        ];
        $request = request();
        $request->merge(['pixel' => $this->retargeting(3, $product, $price_id, $price_line_id)]);

        $syndicate_raffle->transformer = SyndicateRaffleTransformer::class;
        return $this->showOne($syndicate_raffle);
    }

}
