<?php

namespace App\Core\Casino\Controllers;


use App\Core\Casino\Models\CasinoGame;
use App\Core\Casino\Models\CasinoGamesTransaction;
use App\Core\Casino\Models\CasinoProvider;
use App\Core\Base\Traits\ApiResponser;
use App\Http\Controllers\ApiController;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CasinoGamesTransactionController extends ApiController
{
    use ApiResponser;

    public function __construct() {
        parent::__construct();
        $this->middleware('auth:api');
    }


    /**
     * @SWG\Post(
     *   path="/games/user/transactions",
     *   tags={"Games"},
     *   summary="Show user games transactions",
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="from_date",
     *     in="formData",
     *     description="From date (YYYY-MM-DD)",
     *     type="string",
     *     format="date-time",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="to_date",
     *     in="formData",
     *     description="To date (YYYY-MM-DD)",
     *     type="string",
     *     format="date-time",
     *     required=false,
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/CasinoTransaction")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     */
    /**
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function transactions(Request $request) {
        $rules = [
            'from_date' => 'date_format:"Y-m-d"',
            'to_date' => 'date_format:"Y-m-d"',
        ];
        $this->validate($request, $rules);
        if ($request['from_date'] && $request['to_date']) {
            $rules = [
                'to_date' => 'after_or_equal:from_date'
            ];
            $this->validate($request, $rules);
        }

        $gamesTransaction = CasinoGamesTransaction::where('usr_id', '=', $request['user_id']);

        if ($request['from_date']) {
            $date = $request['from_date'].' 00:00:00';
            $gamesTransaction = $gamesTransaction->where('reg_date', '>=', $date);
        }
        if ($request['to_date']) {
            $date = $request['to_date'].' 23:59:59';
            $gamesTransaction = $gamesTransaction->where('reg_date', '<=', $date);
        }

        /* Por defecto me traigo los últimos 7 días */
        if(!$request['to_date'] && !$request['from_date']){
            $gamesTransaction->whereBetween('reg_date', [
                Carbon::now()->subDays(7)->startOfDay(),
                Carbon::now()->endOfDay()]);
        }

        $gamesTransaction = $gamesTransaction
            ->orderByDesc("reg_date")
            ->get()
            ->groupBy('rounds_id');

        $trans = collect([]);
        $multislot_ids = $orxy_ids = $redtiger_ids = [];

        //print_r($gamesTransaction);

        foreach ($gamesTransaction as $gamesT) {
            $item = $gamesT->sortBy('id');

            if($item->first()->casino_provider_id == CasinoProvider::MULTISLOT_CASINO_PROVIDER){
                if( !in_array($item->first()->gameId, $multislot_ids))
                    array_push($multislot_ids, $item->first()->gameId);
            }

            if($item->first()->casino_provider_id == CasinoProvider::ORYX_CASINO_PROVIDER){
                if( !in_array($item->first()->gameId, $orxy_ids))
                    array_push($orxy_ids, $item->first()->gameId);
            }

            if($item->first()->casino_provider_id == CasinoProvider::REDTIGER_CASINO_PROVIDER){
                if( !in_array($item->first()->gameId, $redtiger_ids))
                    array_push($redtiger_ids, $item->first()->gameId);
            }
        };

        $result_multislot = CasinoGame::with("first_description")
            ->whereIn('external_casino_id',  $multislot_ids)
            ->orWhereIn('game_code_mobile', $multislot_ids)->get();

        $result_orxy = CasinoGame::with("first_description")->whereIn('game_code',  $orxy_ids)
            ->orWhereIn('game_code_mobile', $orxy_ids)->get();

        $result_redtiger = CasinoGame::with("first_description")
            ->whereIn('game_code',  $redtiger_ids)
            ->get();


        $gamesTransaction->each(function ($item) use ($trans, $result_multislot, $result_orxy, $result_redtiger) {
            $transAux = collect([]);
            $item = $item->sortBy('id');

            $item->each(function ($item, $key) use ($transAux) {

                $itemAux = [
                    'type' => $item->type,
                    'amount' => 0,
                ];

                switch ($item->transactionType) {
                    case (CasinoGamesTransaction::BET_TRANSACTION_TYPE):
                        if ($item->balAdj > 0) {
                            $itemAux['curr_code'] = $item->curr_code;
                            $itemAux['amount'] = $item->balAdj;
                        } else {
                            if ($item->totalWagered != $item->balAdj && $item->balAdj == 0 && $item->totalWagered > 0) {
                                unset($itemAux);// se elimina este BET, ya que son los BETs resumen de los juegos multiapuesta
                            } else {
                                $itemAux['curr_code'] = $item->curr_code;
                                $itemAux['amount'] = $item->balAdj;
                            }
                        }
                        break;
                    case(CasinoGamesTransaction::RESULT_TRANSACTION_TYPE || CasinoGamesTransaction::WIN_TRANSACTION_TYPE):
                        if ($item['balAdj'] > 0) {
                            $itemAux['curr_code'] = $item->curr_code;
                            $itemAux['amount'] = $item->balAdj;
                        } else {
                            $itemAux['curr_code'] = $item->curr_code;
                            $itemAux['amount'] = $item->balAdj;
                        }
                        break;
                    default:
                        $amount = $item->curr_code . ' ' . $item->balAdj;
                        break;
                }
                if (isset($itemAux)) {
                    $transAux->push($itemAux);
                }
            });
            $fitem = $item->first();
            $gameName = "";

            /* $query = CasinoGame::with("first_description")->where(function ($query) use ($fitem) {
                if ($fitem->casino_provider_id == CasinoProvider::MULTISLOT_CASINO_PROVIDER) {
                    $query->where('external_casino_id', '=', $fitem->gameId);
                }
                if ($fitem->casino_provider_id == CasinoProvider::ORYX_CASINO_PROVIDER) {
                    $query->where('game_code', '=', $fitem->gameId);
                }
                $query->orWhere('game_code_mobile', '=', $fitem->gameId);
            });

            $query_str = Str::slug(DBUtils::getEloquentSqlWithBindings($query));



            $game = Cache::remember("trans-".$query_str,2, function () use ($query) {
                $qq = $query->first();
                return is_null($qq) ? false : $qq;
            });

            */
            $game = null;
            //echo "GAMEID ". $fitem->gameId . PHP_EOL;
            if($fitem->casino_provider_id == CasinoProvider::MULTISLOT_CASINO_PROVIDER){
                foreach($result_multislot as $result_mult){
                    // echo "ex casino " .$result_mult->getRealExternalCasinoProviderId() . PHP_EOL;
                    //echo "game code " . $result_mult->getRealGameCodeMobile() . PHP_EOL;
                    if($result_mult->getRealExternalCasinoProviderId() == $fitem->gameId
                        || $result_mult->getRealGameCodeMobile() == $fitem->gameId){

                        $game = $result_mult;
                        break;
                    }
                }
            }

            if($fitem->casino_provider_id == CasinoProvider::ORYX_CASINO_PROVIDER){
                foreach($result_orxy as $result_o){
                    if($result_o->getRealGameCode() == $fitem->gameId
                        || $result_o->getRealGameCodeMobile() == $fitem->gameId){
                        $game = $result_o;
                        break;
                    }
                }
            }

            if($fitem->casino_provider_id == CasinoProvider::REDTIGER_CASINO_PROVIDER){

                foreach($result_redtiger as $result_rd){
                    if($result_rd->getRealGameCode() == $fitem->gameId){
                        $game = $result_rd;
                        break;
                    }
                }
            }

            if ($game) {
                $desc = $game->first_description;
                $gameName = isset($desc->name) ? $desc->name : $game->name;
            }

            $data = [
                'id' => $game === null ? 0 : $game->id,
                'game' => $gameName,
                'date' => $fitem->reg_date,
                'transactions' => $transAux
            ];
            $trans->push($data);
        });

        return $this->showAllNoPaginated($trans);
    }
}

