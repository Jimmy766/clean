<?php

namespace App\Core\Casino\Services;


use App\Core\Casino\Models\CasinoGame;
use App\Core\Base\Classes\ModelConst;
use Illuminate\Http\Request;

class GetCasinoGamesPaginateService
{

    public function execute(Request $request)
    {

        $provider = $request->provider;
        $live= $request->live;
        $category= $request->category;
        $popular= $request->popular;
        $name= $request->name;

        $tag = [ CasinoGame::TAG_CACHE_MODEL, ];
        $relations = [
            "description",
            "casino_games_bet_config",
            "provider",
            'casino_games_category_clients.casino_category'
        ];

        $gamesQuery=CasinoGame::with($relations)->where('game_enabled','=','1');
        $gamesQuery=$this->queryPopular($gamesQuery,$popular);
        $gamesQuery=$this->queryName($gamesQuery,$name);
        $gamesQuery=$this->queryLive($gamesQuery,$live);
        $gamesQuery=$this->queryProvider($gamesQuery,$provider);
        $gamesQuery=$this->queryCategory( $gamesQuery,$category,$live);
        $gamesQuery=$gamesQuery->latest('reg_date');

        return $gamesQuery->paginateFromCacheByRequest(['*'],$tag);
    }

    private function queryLive($gamesQuery,$live)
    {
        if(isset($live)){
            $gamesQuery=$gamesQuery->where("live", $live)->where('is_lobby', $live);
        }
        return $gamesQuery;
    }
    private function queryPopular($gamesQuery,$popular)
    {
        if(isset($popular)){
            $gamesQuery=$gamesQuery->whereHas('casino_games_category_clients', function ($query) use($popular){
                $query->where('popular_game',$popular);
            });
        }
        return $gamesQuery;
    }
    private function queryName($gamesQuery,$name)
    {
        if(isset($name)){
            $gamesQuery=$gamesQuery->whereHas('description', function ($query) use($name){
                $query->where('name','like','%'.$name.'%');
            });
        }
        return $gamesQuery;
    }
    private function queryProvider($gamesQuery,$provider)
    {
        $gamesQuery=$gamesQuery->whereHas('provider', function ($query) {
            $query->where('active', ModelConst::ENABLED);
        });
        if(isset($provider)){
            $gamesQuery=$gamesQuery->whereHas('provider', function ($query) use ($provider) {
                $query->where('id', $provider);
            });
        }
        return $gamesQuery;
    }
    private function queryCategory( $gamesQuery,$category,$live){

        $gamesQuery = $gamesQuery->whereHas('casino_games_category_clients',
            function ($query){
                $query->whereNotNull('casino_games_id');
            });
        $gamesQuery = $gamesQuery->whereHas('casino_games_category_clients.casino_category',
            function ($query){
                $query->where('active', ModelConst::ENABLED);
            });

        if (isset($category)) {
            $gamesQuery = $gamesQuery->whereHas('casino_games_category_clients.casino_category',
                function ($query) use ($category, $live) {
                    $query->where('id', $category);
                    if (isset($live)) {
                        $query->where('live', $live)
                            ->where('active', ModelConst::ENABLED);
                    }
                });
        }
        return $gamesQuery;
    }

}
