<?php

namespace App\Core\Terms\Services;

use App\Core\Terms\Models\SectionHasTerm;
use App\Core\Terms\Models\Term;

class DeleteSectionTermService
{

    public function execute(Term $term)
    {
        SectionHasTerm::where('id_term', $term->id_term)
            ->delete();
    }
}
