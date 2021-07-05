<?php

namespace App\Core\Casino\Services;

use App\Core\Casino\Models\CasinoGame;
use App\Core\Casino\Models\Favorite;
use Exception;
use Illuminate\Http\Response;

class SetModelFavoritableService
{

    public function execute(Favorite $favorite): Favorite
    {
        if($favorite->type_favorite === Favorite::CASINO){
            $favorite->type_favoritable = CasinoGame::class;
        }

        if($favorite->type_favoritable === null){
            throw new Exception(__('error set type favoritable'),
                Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $favorite;
    }
}
