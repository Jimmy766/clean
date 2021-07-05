<?php

namespace App\Core\Rapi\Controllers;

use App\Core\Rapi\Models\AlertMails;
use App\Core\Rapi\Models\AlertMailsData;
use App\Core\Clients\Models\Client;
use App\Core\Lotteries\Models\Lottery;
use App\Core\Rapi\Transforms\AlertMailsDataSaveTransformer;
use App\Core\Lotteries\Transforms\LotteryAlertListTransformer;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AlertsMailsController extends ApiController
{

    public function __construct() {
        parent::__construct();
        $this->middleware('client.credentials')->except('save_alerts');
        $this->middleware('auth:api')->only('save_alerts');
        $this->middleware('transform.input:' . AlertMailsDataSaveTransformer::class)->only('save_alerts');
    }

    /**
     * @SWG\Post(
     *   path="/users/alerts",
     *   summary="Create user lottery mail alert for results/jackpot",
     *   tags={"Users"},
     *   consumes={"application/json"},
     *   @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Alerts",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/AlertMailsDataSave")
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/LotteryAlertList")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save_alerts(Request $request) {

        $rules = [
            'lotteries' => 'required|array|' . Rule::exists('mysql_external.lotteries', 'lot_id')->where('lot_active','1')->whereIn('lot_id', self::client_lotteries(1)->pluck('product_id')->toArray()),
            'send_results'  => 'required_without:send_jackpot|boolean',
            'send_jackpot' => 'required_without:send_results|boolean',
        ];

        $this->validate($request, $rules);

        //Stores user mail and system to send alerts
        $alert_mails = AlertMails::firstOrCreate(
            [
                'mail'=> Auth::user()->usr_email,
                'sys_id'    => Client::where('id', request()['oauth_client_id'])->first()->site->system->sys_id
            ],
            [
                'usr_language'=> substr(Auth::user()->usr_language,0,2),
                'confirmed' => 1
            ]);

        //Stores lotteries and results/jackpot settings
        foreach ($request->lotteries as $lot_id) {
            $alert_mails_data = AlertMailsData::updateOrCreate(
                [
                    'alertMail_id'=> $alert_mails->alertMail_id,
                    'lot_id'    => $lot_id
                ],
                [
                    'send_results'=> $request->send_results,
                    'send_jackpot'=> $request->send_jackpot,
                ]);
        };

        //Show all lotteries
        $lotteries = Lottery::where('lot_active', '=',1)
            ->whereIn('lot_id', self::client_lotteries(1)->pluck('product_id'))
            ->get();
        if ($lotteries->isNotEmpty()) {
            $lotteries->first()->transformer = LotteryAlertListTransformer::class;
        }
        return $this->showAllNoPaginated($lotteries);
    }
}
