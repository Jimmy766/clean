<?php

    namespace App\Core\Terms\Services;

    use App\Core\Terms\Models\CategoryTerm;
    use App\Core\Terms\Models\SectionTerm;
    use App\Core\Terms\Models\Term;
    use App\Core\Terms\Models\TranslationTerm;
    use Closure;
    use Illuminate\Http\Request;

    class GetTranslationsService
    {

        public function execute(Request $request)
        {
            $categories = $request->categories;
            $sections   = $request->sections;
            $site       = $request->client_site_id;

            $tag = [
                Term::TAG_CACHE_MODEL,
                CategoryTerm::TAG_CACHE_MODEL,
                SectionTerm::TAG_CACHE_MODEL,
                TranslationTerm::TAG_CACHE_MODEL
            ];

            $relations  = [
                'categories' => $this->queryWithCategories($categories),
                'sections' => $this->queryWithSections($sections),
                'sites' =>$this->queryWithSites($site),
                'translationsByLanguage'
            ];

            $termsQuery = Term::query();
            $termsQuery=$this->queryCategories($termsQuery,$categories);
            $termsQuery=$this->querySections($termsQuery, $sections);
            $termsQuery=$this->querySites($termsQuery, $site);

            $termsQuery=$termsQuery->with($relations);

            return $termsQuery->getFromCache(['terms.*'], $tag);
        }

        private function queryWithCategories($categories): Closure
        {
            return static function ($query) use ($categories) {
                if ($categories !== null) {
                    $query->whereIn('categories_term.name', $categories);
                }
            };
        }

        private function queryWithSections($sections): Closure
        {
            return static function ($query) use ($sections) {
                if ($sections !== null) {
                    $query->whereIn('sections_term.name', $sections);
                }
            };
        }

        private function queryWithSites($site): Closure
        {

            return static function ($query) use ($site) {
                if ($site !== null) {
                    $query->where('id_site', $site);
                }
            };
        }

        private function queryCategories($termsQuery, $categories)
        {
            if ($categories !== null) {
                $termsQuery=$termsQuery->join('categories_has_terms as cht',function($query){
                    $query->on('cht.id_term','=','terms.id_term')
                    ->whereNull('cht.deleted_at');
                })
                ->join('categories_term as ct',function($query)use ($categories){
                    $query->on('ct.id_category','=','cht.id_category')
                        ->whereIn('ct.name',$categories);
                });
            }
            return $termsQuery;
        }

        private function querySections($termsQuery, $sections)
        {
            if ($sections !== null) {
                $termsQuery=$termsQuery->join('sections_has_terms as sht',function($query){
                    $query->on('sht.id_term','=','terms.id_term')
                        ->whereNull('sht.deleted_at');;
                })
                ->join('sections_term as st',function($query)use ($sections){
                    $query->on('st.id_section','=','sht.id_section')
                        ->whereIn('st.name',$sections);
                });
            }
            return $termsQuery;
        }

        private function querySites($termsQuery, $site)
        {
            if ($site !== null) {
                $termsQuery=$termsQuery->join('sites_has_terms as siht',function($query)use ($site){
                    $query->on('siht.id_term','=','terms.id_term')
                        ->where('siht.id_site',$site)
                        ->whereNull('siht.deleted_at');;
                });
            }
            return $termsQuery;
        }


    }
