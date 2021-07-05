<?php

namespace App\Core\Terms\Services;

use App\Core\Terms\Models\Term;
use App\Core\Terms\Models\TranslationTerm;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StoreTranslationsTermService
{

    public function execute(Term $term,Request $request)
    {

        $translations = $request->translations;
        $translations = collect($translations);

	    $translations=$translations->map($this->mapSetTranslations($term));
	    TranslationTerm::insert($translations->toArray());
    }

    private function mapSetTranslations(Term $term): callable
    {
        return static function ($item, $key) use ($term) {

	        $newItem                = [];
	        $newItem['id_term']     = $term->id_term;
	        $newItem['id_language'] = $item["id_language"];
	        $newItem['status']      = $item["status"] ?? 0;
	        $newItem['active']      = $item["active"] ?? 0;
	        $newItem['text']        = $item["text"];
	        $newItem['created_at']  = Carbon::now();
	        return $newItem;
        };
    }


}
