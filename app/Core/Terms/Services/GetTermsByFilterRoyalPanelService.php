<?php

namespace App\Core\Terms\Services;

use App\Core\Terms\Models\CategoryTerm;
use App\Core\Terms\Models\SectionTerm;
use App\Core\Terms\Models\Term;
use App\Core\Terms\Models\TranslationTerm;
use Illuminate\Http\Request;

class GetTermsByFilterRoyalPanelService
{

    public function execute(Request $request)
    {

	    $name     = $request->name;
	    $category = $request->id_category;
	    $section  = $request->id_section;
	    $site  = $request->id_site;
	    $text  = $request->text;

        $tag = [
            Term::TAG_CACHE_MODEL,
            CategoryTerm::TAG_CACHE_MODEL,
            SectionTerm::TAG_CACHE_MODEL,
            TranslationTerm::TAG_CACHE_MODEL
        ];
	    $relations = ['categories', 'sections', 'sites','translations'];
	    $termsQuery     = Term::where('name', 'like', '%' . $name . '%')
		    ->with($relations);
	    $this->queryCategories($termsQuery,$category);
	    $this->querySections($termsQuery,$section);
	    $this->querySites($termsQuery,$site);
	    $this->queryText($termsQuery,$text);

	    return $termsQuery->paginateFromCacheByRequest(['*'],$tag);
    }

	private function queryCategories($termsQuery, $category)
	{
		if($category!==null){
			$termsQuery->whereHas('categories', function ($query) use ($category){
				$query->where('categories_has_terms.id_category',$category);
			});
		}
    }
	private function querySections($termsQuery, $section)
	{
		if($section!==null){
			$termsQuery->whereHas('sections', function ($query) use ($section){
				$query->where('sections_has_terms.id_section',$section);
			});
		}
    }
	private function querySites($termsQuery, $site)
	{
		if($site!==null){
			$termsQuery->whereHas('sites', function ($query) use ($site){
				$query->where('site_id',$site);
			});
		}
	}
	private function queryText($termsQuery, $text)
	{
		if($text!==null){
			$termsQuery->whereHas('translations', function ($query) use ($text){
				$query->where('text','like','%'.$text.'%');
			});
		}
	}




}
