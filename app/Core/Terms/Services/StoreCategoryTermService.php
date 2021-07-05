<?php

namespace App\Core\Terms\Services;

use App\Core\Terms\Models\CategoryHasTerm;
use App\Core\Terms\Models\Term;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StoreCategoryTermService
{

    public function execute(Term $term,Request $request)
    {

        $categories = $request->categories;
        $categories = collect($categories);

        $categories=$categories->map($this->mapSetTermToCategory($term));
        CategoryHasTerm::insert($categories->toArray());
    }

    private function mapSetTermToCategory(Term $term): callable
    {
        return static function ($item, $key) use ($term) {

        	$newItem=[];
	        $newItem[ 'id_term' ] = $term->id_term;
	        $newItem[ 'id_category' ] = $item['id_category'];
	        $newItem[ 'created_at' ] = Carbon::now();
	        return $newItem;
        };
    }


}
