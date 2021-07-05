<?php

    namespace App\Core\Terms\Services;

    use App\Core\Rapi\Models\Site;
    use App\Core\Terms\Models\Term;
    use Illuminate\Http\Request;

    class AddSitesTermService
    {

        /**
         * @var StoreSiteTermService
         */
        private $storeSiteTermService;
        /**
         * @var DeleteSiteHasTermService
         */
        private $deleteSiteHasTermService;

        public function __construct(StoreSiteTermService $storeSiteTermService,
            DeleteSiteHasTermService $deleteSiteHasTermService
        )
        {
            $this->storeSiteTermService = $storeSiteTermService;
            $this->deleteSiteHasTermService = $deleteSiteHasTermService;
        }

        public function execute(Request $request)
        {


            $termsWintrillions=Term::query()
                ->whereHas('translations',function ($query){
                    $wintrillions='Wintrillions';
                    $query->where('text','like','%'.$wintrillions.'%');
                })->get();
            $termsTrillonario=Term::query()
                ->whereHas('translations',function ($query){
                    $trillonario='Trillonario';
                    $query->where('text','like','%'.$trillonario.'%');
                })->get();
            $termsWintrillionsIds=$termsWintrillions->pluck('id_term');

            $termsTrillonarioIds=$termsTrillonario->pluck('id_term');
            $termIds=$termsWintrillionsIds->merge($termsTrillonarioIds)->toArray();

            $termsGeneral=Term::query()->whereNotIn('id_term',$termIds)->get();

            $sitesWintrillons=Site::where('sys_id',1)
                ->where('wlabel',0)
                ->where('site_url','not like','%trillonario%')
                ->get();

            $sitesTrillonario=Site::where('sys_id',1)
                ->where('wlabel',0)
                ->where('site_url','like','%trillonario%')
                ->get();
            $sitesAll=Site::where('sys_id',1)
                ->where('wlabel',0)
                ->get();

            $sitesWintrillonsMap=$sitesWintrillons->map($this->sitesMap());
            $sitesTrillonarioMap=$sitesTrillonario->map($this->sitesMap());
            $sitesAllMap=$sitesAll->map($this->sitesMap());
            $request->sites=$sitesWintrillonsMap;
            foreach ($termsWintrillions as $term) {
                $this->deleteSiteHasTermService->execute($term);
                $this->storeSiteTermService->execute($term,$request);
            }
            $request->sites=$sitesTrillonarioMap;
            foreach ($termsTrillonario as $term) {
                $this->deleteSiteHasTermService->execute($term);
                $this->storeSiteTermService->execute($term,$request);
            }
            $request->sites=$sitesAllMap;
            foreach ($termsGeneral as $term) {
                $this->deleteSiteHasTermService->execute($term);
                $this->storeSiteTermService->execute($term,$request);
            }

        }

        private function sitesMap(){
            return function ($item,$key){
                $newItem=[];
                $newItem[ 'id_site' ] = $item['site_id'];
                return $newItem;
            };
        }

    }
