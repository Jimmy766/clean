<?php

namespace App\Core\Terms\Services;

use App\Core\Terms\Models\SiteHasTerm;
use App\Core\Terms\Models\Term;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StoreSiteTermService
{

    public function execute(Term $term,Request $request)
    {

        $sites = $request->sites;
        $sites = collect($sites);

	    $sites=$sites->map($this->mapSetTermToSite($term));
	    SiteHasTerm::insert($sites->toArray());
    }

    private function mapSetTermToSite(Term $term): callable
    {
        return static function ($item, $key) use ($term) {

	        $newItem=[];
	        $newItem[ 'id_term' ] = $term->id_term;
	        $newItem[ 'id_site' ] = $item['id_site'];
	        $newItem[ 'created_at' ] = Carbon::now();
	        return $newItem;
        };
    }


}
