<?php

namespace App\Core\Casino\Services;

use App\Core\Casino\Models\Favorite;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Class AllFavoriteAvailableService
 * @package App\Services
 */
class AllFavoritesAvailableService
{

    /**
     * @param $request
     * @return Collection
     */
    public function execute($request): Collection
    {

        $idUser    = Auth::id();

        $tag = Favorite::KEY_CACHE_MODEL."".$idUser;
        $favorites = Favorite::query()
            ->where('id_user', $idUser)
            ->with([ 'favoriteable' ]);

        if($request->type_favorite !== null){
            $favorites = $favorites->where('type_favorite', $request->type_favorite);
        }

        return $favorites->getFromCache(['*'],$tag);

    }

}
