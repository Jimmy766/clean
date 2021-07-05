<?php

namespace App\Core\Terms\Services;

use App\Core\Terms\Models\Term;
use App\Core\Terms\Models\TranslationTerm;

class DeleteTranslationsTermService
{

    public function execute(Term $term )
    {
        TranslationTerm::where('id_term', $term->id_term)
            ->delete();
    }
}
