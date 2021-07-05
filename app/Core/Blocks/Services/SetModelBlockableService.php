<?php

namespace App\Core\Blocks\Services;

use App\Core\Blocks\Models\Block;
use App\Core\Casino\Models\CasinoGame;
use App\Core\Base\Classes\ModelConst;
use App\Core\Casino\Models\Favorite;
use App\Core\Terms\Models\Language;
use App\Core\Countries\Models\Region;
use App\Core\Countries\Models\RegionRapi;
use Exception;
use Illuminate\Http\Response;

class SetModelBlockableService
{

    public function execute(Block $block, $request): Block
    {
        if($request->type_block === ModelConst::BLOCKEABLE_REGION){
            $block->type_blockable = RegionRapi::class;
        }

        if($request->type_block === ModelConst::BLOCKEABLE_LANGUAGE){
            $block->type_blockable = Language::class;
        }

        return $block;
    }
}
