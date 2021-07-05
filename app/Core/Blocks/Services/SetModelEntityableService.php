<?php

namespace App\Core\Blocks\Services;

use App\Core\Blocks\Models\Block;
use App\Core\Casino\Models\CasinoGame;
use App\Core\Base\Classes\ModelConst;
use App\Core\Lotteries\Models\Lottery;

class SetModelEntityableService
{

    public function execute(Block $block, $request): Block
    {
        if($request->type_entity === ModelConst::ENTITYABLE_LOTTERY){
            $block->type_entityable = Lottery::class;
        }

        if($request->type_entity === ModelConst::ENTITYABLE_GAME){
            $block->type_entityable = CasinoGame::class;
        }

        return $block;
    }
}
