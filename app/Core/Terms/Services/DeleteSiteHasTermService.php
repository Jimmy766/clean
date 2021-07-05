<?php

namespace App\Core\Terms\Services;

use App\Core\Terms\Models\SiteHasTerm;
use App\Core\Terms\Models\Term;

class DeleteSiteHasTermService
{

    public function execute(Term $term)
    {
	    SiteHasTerm::where('id_term',$term->id_term)
		    ->delete();
    }
}
