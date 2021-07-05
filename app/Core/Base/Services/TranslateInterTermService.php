<?php

namespace App\Core\Base\Services;


use App\Core\Terms\Models\CategoryTerm;
use App\Core\Terms\Models\SectionTerm;
use App\Core\Terms\Models\Term;
use App\Core\Terms\Models\TranslationTerm;

class TranslateInterTermService
{

    public static function execute($text = '')
    {
        $locale = \app()->getLocale();
        $tag = [
            Term::TAG_CACHE_MODEL,
            CategoryTerm::TAG_CACHE_MODEL,
            SectionTerm::TAG_CACHE_MODEL,
            TranslationTerm::TAG_CACHE_MODEL
        ];
		$translated=Term::query()->where('terms.name',$text)
			->join('translations_terms as tt','tt.id_term','terms.id_term')
			->join('languages as l','l.id_language','tt.id_language')
			->where('l.code',$locale)
			->whereNull('tt.deleted_at')
			->whereNull('l.deleted_at')
			->whereNull('terms.deleted_at')
			->firstFromCache(['tt.text'],$tag);

		if($translated!==null){
            return $translated['text'];
        }
        return null;
    }
}
