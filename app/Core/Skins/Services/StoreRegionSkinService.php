<?php

namespace App\Core\Skins\Services;

use App\Core\Skins\Models\RegionSkin;
use App\Core\Skins\Models\Skin;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StoreRegionSkinService
{

    public function execute(Skin $skin, Request $request)
    {
        $regions = $request->input('regions');
        $regions = collect($regions);

        $regions = $regions->map($this->mapSetSkinToRegionTransform($skin));

        RegionSkin::insert($regions->toArray());
    }

    private function mapSetSkinToRegionTransform(Skin $skin): callable
    {
        return static function ($item, $key) use ($skin) {
            $item[ 'id_skin' ]    = $skin->id_skin;
            $item[ 'created_at' ] = Carbon::now();
            $item[ 'updated_at' ] = Carbon::now();

            return $item;
        };
    }

}
