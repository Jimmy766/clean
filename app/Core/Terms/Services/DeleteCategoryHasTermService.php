<?php

namespace App\Core\Terms\Services;

use App\Core\Terms\Models\CategoryHasTerm;
use App\Core\Terms\Models\Term;

class DeleteCategoryHasTermService
{

    public function execute(Term $term)
    {
    	CategoryHasTerm::where('id_term',$term->id_term)
		    ->delete();
    }
}
