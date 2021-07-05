<?php

namespace App\Core\Skins\Services;

use App\Core\Skins\Models\DateProgramsSkin;
use App\Core\Skins\Models\ProgramSkin;
use App\Core\Skins\Models\Skin;

class DeleteProgramSkinService
{

    public function execute(Skin $skin)
    {
        $programSkin = ProgramSkin::where('id_skin', $skin->id_skin)->get([ 'id_program' ]);

        DateProgramsSkin::whereIn('id_program', $programSkin->toArray())->delete();

        ProgramSkin::where('id_skin', $skin->id_skin)->delete();

    }
}
