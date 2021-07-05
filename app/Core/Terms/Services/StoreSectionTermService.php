<?php

namespace App\Core\Terms\Services;

use App\Core\Terms\Models\SectionHasTerm;
use App\Core\Terms\Models\Term;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StoreSectionTermService
{

    public function execute(Term $term,Request $request)
    {

        $sections = $request->sections;
        $sections = collect($sections);

	    $sections=$sections->map($this->mapSetTermToSection($term));
	    SectionHasTerm::insert($sections->toArray());
    }

    private function mapSetTermToSection(Term $term): callable
    {
        return static function ($item, $key) use ($term) {

	        $newItem=[];
	        $newItem[ 'id_term' ] = $term->id_term;
	        $newItem[ 'id_section' ] = $item['id_section'];
	        $newItem[ 'created_at' ] = Carbon::now();
	        return $newItem;
        };
    }


}
