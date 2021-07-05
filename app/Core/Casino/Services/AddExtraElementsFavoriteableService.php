<?php

namespace App\Core\Casino\Services;

use App\Core\Casino\Services\GenerateUrlDemoCasinoGameService;
use App\Core\Casino\Services\GenerateUrlRealPlayCasinoGameService;
use App\Core\Casino\Models\Favorite;
use Illuminate\Support\Collection;

class AddExtraElementsFavoriteableService
{

    public function execute(Collection $collection)
    {
        $collection =
            $collection->map($this->mapChangeToObjectItemsTransform());
        $collection = $collection->map($this->mapSetGameUrlTransform());

        return $collection;
    }

    private function mapSetGameUrlTransform(): callable
    {
        return function ($item, $key) {
            if ($item->type_favorite === Favorite::CASINO) {
                $live = $item->favoriteable['live'];
                $generateDemoUrl     = new GenerateUrlDemoCasinoGameService();
                $generateRealPlayUrl =
                    new GenerateUrlRealPlayCasinoGameService();

                $idGame = $item->favoriteable[ 'id' ];

                $item->favoriteable[ 'demo_url' ] =
                    $generateDemoUrl->execute($idGame, $live);

                $item->favoriteable[ 'real_play_url' ] =
                    $generateRealPlayUrl->execute($idGame);
            }
            return $item;
        };
    }

    private function mapChangeToObjectItemsTransform(): callable
    {
        return function (Favorite $favorite, $key) {
            return (object) $favorite->toArray();
        };
    }

}
