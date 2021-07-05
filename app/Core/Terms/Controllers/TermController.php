<?php

	namespace App\Core\Terms\Controllers;

	use App\Core\Terms\Models\CategoryTerm;
    use App\Core\Base\Traits\CacheUtilsTraits;
    use App\Core\Terms\Collections\TermCollection;
    use App\Core\Terms\Requests\ListTranslationsRequest;
    use App\Core\Terms\Requests\StoreTermRequest;
    use App\Core\Terms\Requests\StoreTranslationsTermRequest;
    use App\Core\Terms\Resources\CategoryTermResource;
    use App\Core\Terms\Resources\SectionTermResource;
    use App\Core\Terms\Resources\TermResource;
    use App\Core\Terms\Models\SectionTerm;
    use App\Core\Terms\Services\AddSitesTermService;
    use App\Core\Terms\Services\DeleteCategoryHasTermService;
    use App\Core\Terms\Services\DeleteSectionTermService;
    use App\Core\Terms\Services\DeleteSiteHasTermService;
    use App\Core\Terms\Services\DeleteTranslationsTermService;
    use App\Core\Terms\Services\GetTermsByFilterRoyalPanelService;
    use App\Core\Terms\Services\GetTranslationsService;
    use App\Core\Terms\Services\StoreCategoryTermService;
    use App\Core\Terms\Services\StoreSectionTermService;
    use App\Core\Terms\Services\StoreSiteTermService;
    use App\Core\Terms\Services\StoreTranslationsTermService;
    use App\Core\Base\Services\LogType;
    use App\Http\Controllers\Controller;
    use App\Core\Rapi\Models\Site;
    use App\Core\Terms\Models\Term;
    use App\Core\Base\Traits\ApiResponser;
    use App\Core\Rapi\Transforms\SiteTransformer;
    use Exception;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;

    class TermController extends Controller
	{
		use ApiResponser,CacheUtilsTraits;


		/**
		 * @var \App\Core\Terms\Services\StoreSectionTermService
		 */
		private $storeSectionTermService;
		/**
		 * @var \App\Core\Terms\Services\StoreCategoryTermService
		 */
		private $storeCategoryTermService;
		/**
		 * @var \App\Core\Terms\Services\StoreSiteTermService
		 */
		private $storeSiteTermService;
		/**
		 * @var DeleteSectionTermService
		 */
		private $deleteSectionTermService;
		/**
		 * @var DeleteCategoryHasTermService
		 */
		private $deleteCategoryHasTermService;
		/**
		 * @var \App\Core\Terms\Services\DeleteSiteHasTermService
		 */
		private $deleteSiteHasTermService;
		/**
		 * @var \App\Core\Terms\Services\StoreTranslationsTermService
		 */
		private $storeTranslationsTermService;
		/**
		 * @var GetTermsByFilterRoyalPanelService
		 */
		private $getTermByFilterService;
		/**
		 * @var GetTranslationsService
		 */
		private $getTranslationsService;
		/**
		 * @var DeleteTranslationsTermService
		 */
		private $deleteTranslationsTermService;
        /**
         * @var AddSitesTermService
         */
        private $addSitesTermService;

        public function __construct(
            StoreSectionTermService $storeSectionTermService,
            StoreCategoryTermService $storeCategoryTermService,
            StoreSiteTermService $storeSiteTermService,
            DeleteSectionTermService $deleteSectionTermService,
            DeleteCategoryHasTermService $deleteCategoryHasTermService,
            DeleteSiteHasTermService $deleteSiteHasTermService,
            StoreTranslationsTermService $storeTranslationsTermService,
            GetTermsByFilterRoyalPanelService $getTermsByFilterService,
            GetTranslationsService $getTranslationsService,
            DeleteTranslationsTermService $deleteTranslationsTermService,
            AddSitesTermService $addSitesTermService
		)
		{
			$this->middleware('client.credentials');
			$this->middleware('check.external_access')->except('translate');
			$this->storeSectionTermService=$storeSectionTermService;
			$this->storeCategoryTermService = $storeCategoryTermService;
			$this->storeSiteTermService = $storeSiteTermService;
			$this->deleteSectionTermService = $deleteSectionTermService;
			$this->deleteCategoryHasTermService = $deleteCategoryHasTermService;
			$this->deleteSiteHasTermService = $deleteSiteHasTermService;
			$this->storeTranslationsTermService = $storeTranslationsTermService;
			$this->getTermByFilterService = $getTermsByFilterService;
			$this->getTranslationsService = $getTranslationsService;
			$this->deleteTranslationsTermService = $deleteTranslationsTermService;
            $this->addSitesTermService = $addSitesTermService;
        }

		/**
		 * @SWG\Get(
		 *   path="/terms",
		 *   summary="Show Terms by Name, Section, Site and Category ID",
		 *   tags={"Traduction"},
		 *   @SWG\Parameter(
		 *     name="page_pagination",
		 *     in="query",
		 *     description="page pagination",
		 *     type="integer",
		 *     default=1
		 *   ),
		 *   @SWG\Parameter(
		 *     name="size_pagination",
		 *     in="query",
		 *     description="size pagination",
		 *     type="integer",
		 *     default=15
		 *   ),
		 *   @SWG\Parameter(
		 *     name="name",
		 *     in="query",
		 *     description="Section Name",
		 *     type="string",
		 *   ),
		 *   @SWG\Parameter(
		 *     name="id_section",
		 *     in="query",
		 *     description="Section ID",
		 *     type="integer",
		 *   ),
		 *   @SWG\Parameter(
		 *     name="id_category",
		 *     in="query",
		 *     description="Category ID",
		 *     type="integer",
		 *   ),
		 *   @SWG\Parameter(
		 *     name="text",
		 *     in="query",
		 *     description="Translation Text",
		 *     type="string",
		 *   ),
		 *   @SWG\Parameter(
		 *     name="id_site",
		 *     in="query",
		 *     description="Site ID",
		 *     type="integer",
		 *   ),
		 *   security={
		 *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
		 *     {"password": {}, "user_ip":{},  "Content-Language":{}}
		 *   },
		 *   @SWG\Response(
		 *     response=200,
		 *     description="Successful operation",
		 *     @SWG\Schema(
		 *         @SWG\Property(property="data", type="array",
		 *                                        @SWG\Items(ref="#/definitions/Term")),
		 *     ),
		 *   ),
		 *   @SWG\Response(response=401, ref="#/responses/401"),
		 *   @SWG\Response(
		 *     response="403",
		 *     description="Forbidden Access",
		 *     @SWG\Schema(
		 *       @SWG\Property(property="error", type="string",
		 *                                       description="Message error",
		 *                                       example="This data is not allowed for you"),
		 *       @SWG\Property(property="code", type="integer",
		 *                                      description="Response code",
		 *                                      example="403"),
		 *     ),
		 *   ),
		 *   @SWG\Response(response=500, ref="#/responses/500"),
		 * )
		 *
		 */
		public function index(Request $request)
		{
			$terms=$this->getTermByFilterService->execute($request);
			$data['terms']=new TermCollection($terms);
			return $this->successResponseWithMessage($data);
		}

		/**
		 * @SWG\Get(
		 *   path="/terms/create",
		 *   summary="Get info necessary to store Terms",
		 *   tags={"Traduction"},
		 *   security={
		 *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
		 *     {"password": {}, "user_ip":{},  "Content-Language":{}}
		 *   },
		 *   @SWG\Response(response=200, ref="#/responses/200"),
		 *   @SWG\Response(response=422, ref="#/responses/422"),
		 *   @SWG\Response(response=401, ref="#/responses/401"),
		 *   @SWG\Response(response=403, ref="#/responses/403"),
		 *   @SWG\Response(response=500, ref="#/responses/500"),
		 * )
		 *
		 */
		public function create()
		{
			$categories = CategoryTerm::getFromCache(['*'],CategoryTerm::TAG_CACHE_MODEL);
			$categories = CategoryTermResource::collection($categories);
			$sections   = SectionTerm::getFromCache(['*'],SectionTerm::TAG_CACHE_MODEL);
			$sections   = SectionTermResource::collection($sections);
			$sites      = Site::where('sys_id',1)
                ->where('wlabel',0)
                ->getFromCache(['*'],Site::TAG_CACHE_MODEL);
			$sites      = \fractal($sites, new SiteTransformer)->toArray();

			$data       = [
				'categories' => $categories,
				'sections'   => $sections,
				'sites'      => $sites['data'],
			];
			return $this->successResponseWithMessage($data) ;
		}

		/**
		 * @SWG\Post(
		 *   path="/terms",
		 *   summary="Store Term  ",
		 *   tags={"Traduction"},
		 *   consumes={"application/json"},
		 *
		 *   @SWG\Parameter(
		 *     name="request",
		 *     in="body",
		 *     description="request body json",
		 *     type="object",
		 *     @SWG\Schema(
		 *         ref="#/definitions/StoreTerm",
		 *     )
		 *   ),
		 *   security={
		 *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
		 *     {"password": {}, "user_ip":{},  "Content-Language":{}}
		 *   },
		 *   @SWG\Response(
		 *     response=200,
		 *     description="Successful operation",
		 *     @SWG\Schema(
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/Term"),
		 *     ),
		 *   ),
		 *   @SWG\Response(response=401, ref="#/responses/401"),
		 *   @SWG\Response(
		 *     response="403",
		 *     description="Forbidden Access",
		 *     @SWG\Schema(
		 *       @SWG\Property(property="error", type="string",
		 *                                       description="Message error",
		 *                                       example="This data is not allowed
		 *                                       for you"),
		 *       @SWG\Property(property="code", type="integer",
		 *                                      description="Response code",
		 *                                      example="403"),
		 *     ),
		 *   ),
		 *   @SWG\Response(response=500, ref="#/responses/500"),
		 * )
		 * @param \App\Core\Terms\Requests\StoreTermRequest $request
		 * @return JsonResponse
		 */
		public function store(StoreTermRequest $request)
		{
			$successMessage = __('Successful');
			$errorMessage   = __('An error has ocurred');

			try {
				DB::beginTransaction();

				$term=new Term($request->validated());
				$term->save();

				$this->storeSectionTermService->execute($term,$request);
				$this->storeCategoryTermService->execute($term,$request);
				$this->storeSiteTermService->execute($term,$request);

				DB::commit();

				$tag = [ Term::TAG_CACHE_MODEL, ];
				$this->forgetCacheByTag($tag);

				$term->load([
					'sections',
					'categories',
					'sites'
				]);

				$data[ 'term' ] = new TermResource($term);


				return $this->successResponseWithMessage(
					$data,
					$successMessage,
					Response::HTTP_CREATED
				);

			}
			catch(Exception $exception) {
				DB::rollBack();

                LogType::error(
                    __FILE__,
                    __LINE__,
                    $errorMessage,
                    [
                        'exception' => $exception,
                        'usersId'   => Auth::id(),
                    ]
                );

				return $this->errorCatchResponse(
					$exception,
					$errorMessage,
					Response::HTTP_INTERNAL_SERVER_ERROR
				);
			}
		}

		/**
		 * @SWG\Post(
		 *   path="/terms/{id_term}/translations",
		 *   summary="Store Translations by Term",
		 *   tags={"Traduction"},
		 *   consumes={"application/json"},
		 *   @SWG\Parameter(
		 *     name="id_term",
		 *     in="path",
		 *     type="integer",
		 *     description="Term ID",
		 *     ),
		 *   @SWG\Parameter(
		 *     name="request",
		 *     in="body",
		 *     description="request body json",
		 *     type="object",
		 *     @SWG\Schema(
		 *         ref="#/definitions/StoreTranslationsTerm",
		 *     )
		 *   ),
		 *   security={
		 *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
		 *     {"password": {}, "user_ip":{},  "Content-Language":{}}
		 *   },
		 *   @SWG\Response(
		 *     response=200,
		 *     description="Successful operation",
		 *     @SWG\Schema(
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/Term"),
		 *     ),
		 *   ),
		 *   @SWG\Response(response=401, ref="#/responses/401"),
		 *   @SWG\Response(
		 *     response="403",
		 *     description="Forbidden Access",
		 *     @SWG\Schema(
		 *       @SWG\Property(property="error", type="string",
		 *                                       description="Message error",
		 *                                       example="This data is not allowed
		 *                                       for you"),
		 *       @SWG\Property(property="code", type="integer",
		 *                                      description="Response code",
		 *                                      example="403"),
		 *     ),
		 *   ),
		 *   @SWG\Response(response=500, ref="#/responses/500"),
		 * )
		 * @param Term                                                  $term
		 * @param \App\Core\Terms\Requests\StoreTranslationsTermRequest $request
		 * @return \Illuminate\Http\JsonResponse
		 */
		public function storeTranslations(Term $term,StoreTranslationsTermRequest $request)
		{
			$successMessage = __('Successful');
			$errorMessage   = __('An error has ocurred');

			try {
				DB::beginTransaction();

				$this->storeTranslationsTermService->execute($term,$request);


				DB::commit();

				$tag = [ Term::TAG_CACHE_MODEL, ];
				$this->forgetCacheByTag($tag);

				$term->load([
					'sections',
					'categories',
					'sites',
					'translations'
				]);

				$data[ 'term' ] = new TermResource($term);


				return $this->successResponseWithMessage(
					$data,
					$successMessage,
					Response::HTTP_CREATED
				);

			}
			catch(Exception $exception) {
				DB::rollBack();

                LogType::error(
                    __FILE__,
                    __LINE__,
                    $errorMessage,
                    [
                        'exception' => $exception,
                        'usersId'   => Auth::id(),
                    ]
                );

				return $this->errorCatchResponse(
					$exception,
					$errorMessage,
					Response::HTTP_INTERNAL_SERVER_ERROR
				);
			}
		}
		/**
		 * @SWG\Put(
		 *   path="/terms/{id_term}/translations",
		 *   summary="Update Translations by Term",
		 *   tags={"Traduction"},
		 *   consumes={"application/json"},
		 *   @SWG\Parameter(
		 *     name="id_term",
		 *     in="path",
		 *     type="integer",
		 *     description="Term ID",
		 *     ),
		 *   @SWG\Parameter(
		 *     name="request",
		 *     in="body",
		 *     description="request body json",
		 *     type="object",
		 *     @SWG\Schema(
		 *         ref="#/definitions/StoreTranslationsTerm",
		 *     )
		 *   ),
		 *   security={
		 *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
		 *     {"password": {}, "user_ip":{},  "Content-Language":{}}
		 *   },
		 *   @SWG\Response(
		 *     response=200,
		 *     description="Successful operation",
		 *     @SWG\Schema(
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/Term"),
		 *     ),
		 *   ),
		 *   @SWG\Response(response=401, ref="#/responses/401"),
		 *   @SWG\Response(
		 *     response="403",
		 *     description="Forbidden Access",
		 *     @SWG\Schema(
		 *       @SWG\Property(property="error", type="string",
		 *                                       description="Message error",
		 *                                       example="This data is not allowed
		 *                                       for you"),
		 *       @SWG\Property(property="code", type="integer",
		 *                                      description="Response code",
		 *                                      example="403"),
		 *     ),
		 *   ),
		 *   @SWG\Response(response=500, ref="#/responses/500"),
		 * )
		 * @param \App\Core\Terms\Models\Term  $term
		 * @param StoreTranslationsTermRequest $request
		 * @return \Illuminate\Http\JsonResponse
		 */
		public function updateTranslations(Term $term,StoreTranslationsTermRequest $request)
		{
			$successMessage = __('Successful');
			$errorMessage   = __('An error has ocurred');

			try {
				DB::beginTransaction();

				$this->deleteTranslationsTermService->execute($term);
				$this->storeTranslationsTermService->execute($term,$request);


				DB::commit();

				$tag = [ Term::TAG_CACHE_MODEL, ];
				$this->forgetCacheByTag($tag);

				$term->load([
					'sections',
					'categories',
					'sites',
					'translations'
				]);

				$data[ 'term' ] = new TermResource($term);


				return $this->successResponseWithMessage(
					$data,
					$successMessage,
					Response::HTTP_CREATED
				);

			}
			catch(Exception $exception) {
				DB::rollBack();

                LogType::error(
                    __FILE__,
                    __LINE__,
                    $errorMessage,
                    [
                        'exception' => $exception,
                        'usersId'   => Auth::id(),
                    ]
                );

				return $this->errorCatchResponse(
					$exception,
					$errorMessage,
					Response::HTTP_INTERNAL_SERVER_ERROR
				);
			}
		}

		/**
		 * @SWG\Get(
		 *   path="/terms/{id_term}",
		 *   summary="Show Term by ID ",
		 *   tags={"Traduction"},
		 *   @SWG\Parameter(
		 *     name="id_term",
		 *     in="path",
		 *     description="Term ID",
		 *     type="integer",
		 *     required=true,
		 *   ),
		 *   security={
		 *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
		 *     {"password": {}, "user_ip":{},  "Content-Language":{}}
		 *   },
		 *   @SWG\Response(
		 *     response=200,
		 *     description="Successful operation",
		 *     @SWG\Schema(
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/Term"),
		 *     ),
		 *   ),
		 *   @SWG\Response(response=401, ref="#/responses/401"),
		 *   @SWG\Response(
		 *     response="403",
		 *     description="Forbidden Access",
		 *     @SWG\Schema(
		 *       @SWG\Property(property="error", type="string",
		 *                                       description="Message error",
		 *                                       example="This data is not allowed for you"),
		 *       @SWG\Property(property="code", type="integer",
		 *                                      description="Response code",
		 *                                      example="403"),
		 *     ),
		 *   ),
		 *   @SWG\Response(response=500, ref="#/responses/500"),
		 * )
		 *
		 */
		public function show(Term $term)
		{
			$term->load([
				'sections',
				'categories',
				'sites',
				'translations'
			]);
			$data['term']=new TermResource($term);
			return $this->successResponseWithMessage($data);
		}


		/**
		 * @SWG\Post (
		 *   path="/terms/translate",
		 *   summary="Show translations by Term, Section, Site and Category ID",
		 *   tags={"Traduction"},
		 *   consumes={"application/json"},
		 *   @SWG\Parameter(
		 *     name="request",
		 *     in="body",
		 *     description="request body json",
		 *     type="object",
		 *     @SWG\Schema(
		 *         ref="#/definitions/ListTranslationsRequest",
		 *     )
		 *   ),
		 *   security={
		 *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
		 *     {"password": {}, "user_ip":{},  "Content-Language":{}}
		 *   },
		 *   security={
		 *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
		 *     {"password": {}, "user_ip":{},  "Content-Language":{}}
		 *   },
		 *   @SWG\Response(
		 *     response=200,
		 *     description="Successful operation",
		 *     @SWG\Schema(
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/Term"),
		 *     ),
		 *   ),
		 *   @SWG\Response(response=401, ref="#/responses/401"),
		 *   @SWG\Response(
		 *     response="403",
		 *     description="Forbidden Access",
		 *     @SWG\Schema(
		 *       @SWG\Property(property="error", type="string",
		 *                                       description="Message error",
		 *                                       example="This data is not allowed for you"),
		 *       @SWG\Property(property="code", type="integer",
		 *                                      description="Response code",
		 *                                      example="403"),
		 *     ),
		 *   ),
		 *   @SWG\Response(response=500, ref="#/responses/500"),
		 * )
		 * @param Request $request
		 * @return \Illuminate\Http\JsonResponse
		 */
		public function translate(ListTranslationsRequest $request)
		{

			$terms=$this->getTranslationsService->execute($request);
			$data['terms']=TermResource::collection($terms);
			return $this->successResponseWithMessage($data);
		}

		/**
		 * @SWG\Put(
		 *   path="/terms/{id_term}",
		 *   summary="Update Term ",
		 *   tags={"Traduction"},
		 *   consumes={"application/json"},
		 *   @SWG\Parameter(
		 *     name="id_term",
		 *     in="path",
		 *     description="Term ID",
		 *     type="integer",
		 *     required=true,
		 *   ),
		 *   @SWG\Parameter(
		 *     name="request",
		 *     in="body",
		 *     description="request body json",
		 *     type="object",
		 *     @SWG\Schema(
		 *         ref="#/definitions/StoreTerm",
		 *     )
		 *   ),
		 *   security={
		 *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
		 *     {"password": {}, "user_ip":{},  "Content-Language":{}}
		 *   },
		 *   @SWG\Response(
		 *     response=200,
		 *     description="Successful operation",
		 *     @SWG\Schema(
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/Term"),
		 *     ),
		 *   ),
		 *   @SWG\Response(response=401, ref="#/responses/401"),
		 *   @SWG\Response(
		 *     response="403",
		 *     description="Forbidden Access",
		 *     @SWG\Schema(
		 *       @SWG\Property(property="error", type="string",
		 *                                       description="Message error",
		 *                                       example="This data is not allowed
		 *                                       for you"),
		 *       @SWG\Property(property="code", type="integer",
		 *                                      description="Response code",
		 *                                      example="403"),
		 *     ),
		 *   ),
		 *   @SWG\Response(response=500, ref="#/responses/500"),
		 * )
		 *
		 */
		public function update(Term $term, StoreTermRequest $request)
		{
			$successMessage = __('Successful');
			$errorMessage   = __('An error has ocurred');

			try {
				DB::beginTransaction();

				$term->fill($request->validated());
				$term->save();
				$this->deleteCategoryHasTermService->execute($term);
				$this->deleteSectionTermService->execute($term);
				$this->deleteSiteHasTermService->execute($term);

				$this->storeSectionTermService->execute($term,$request);
				$this->storeCategoryTermService->execute($term,$request);
				$this->storeSiteTermService->execute($term,$request);

				DB::commit();

				$tag = [ Term::TAG_CACHE_MODEL, ];
				$this->forgetCacheByTag($tag);

				$term->load([
					'sections',
					'categories',
					'sites'
				]);

				$data[ 'term' ] = new TermResource($term);


				return $this->successResponseWithMessage(
					$data,
					$successMessage,
					Response::HTTP_CREATED
				);

			}
			catch(Exception $exception) {
				DB::rollBack();

                LogType::error(
                    __FILE__,
                    __LINE__,
                    $errorMessage,
                    [
                        'exception' => $exception,
                        'usersId'   => Auth::id(),
                    ]
                );

				return $this->errorCatchResponse(
					$exception,
					$errorMessage,
					Response::HTTP_INTERNAL_SERVER_ERROR
				);
			}
		}

		/**
		 * @SWG\Delete(
		 *   path="/terms/{id_term}",
		 *   summary="Delete Term ",
		 *   tags={"Traduction"},
		 *   @SWG\Parameter(
		 *     name="id_term",
		 *     in="path",
		 *     description="Term ID",
		 *     type="string",
		 *     required=true,
		 *   ),
		 *   security={
		 *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
		 *     {"password": {}, "user_ip":{},  "Content-Language":{}}
		 *   },
		 *   @SWG\Response(
		 *     response=200,
		 *     description="Successful operation",
		 *     @SWG\Schema(
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/Term"),
		 *     ),
		 *   ),
		 *   @SWG\Response(response=401, ref="#/responses/401"),
		 *   @SWG\Response(
		 *     response="403",
		 *     description="Forbidden Access",
		 *     @SWG\Schema(
		 *       @SWG\Property(property="error", type="string",
		 *                                       description="Message error",
		 *                                       example="This data is not allowed
		 *                                       for you"),
		 *       @SWG\Property(property="code", type="integer",
		 *                                      description="Response code",
		 *                                      example="403"),
		 *     ),
		 *   ),
		 *   @SWG\Response(response=500, ref="#/responses/500"),
		 * )
		 *
		 */
		public function destroy(Term $term)
		{
			$successMessage = __('Successful');
			$errorMessage   = __('An error has ocurred');

			try {
				DB::beginTransaction();

				$this->deleteCategoryHasTermService->execute($term);
				$this->deleteSectionTermService->execute($term);
				$this->deleteSiteHasTermService->execute($term);
				$this->deleteTranslationsTermService->execute($term);

				$term->delete();

				DB::commit();

				$tag = [ Term::TAG_CACHE_MODEL, ];
				$this->forgetCacheByTag($tag);

				$data[ 'term' ] = new TermResource($term);

				return $this->successResponseWithMessage(
					$data,
					$successMessage,
					Response::HTTP_CREATED
				);

			}
			catch(Exception $exception) {
				DB::rollBack();

                LogType::error(
                    __FILE__,
                    __LINE__,
                    $errorMessage,
                    [
                        'exception' => $exception,
                        'usersId'   => Auth::id(),
                    ]
                );

				return $this->errorCatchResponse(
					$exception,
					$errorMessage,
					Response::HTTP_INTERNAL_SERVER_ERROR
				);
			}
		}
		/**
		 * @SWG\Put(
		 *   path="/terms/update_sites",
		 *   summary="Update Term Sites",
		 *   tags={"Traduction"},
		 *   security={
		 *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
		 *     {"password": {}, "user_ip":{},  "Content-Language":{}}
		 *   },
         *   @SWG\Response(response=200, ref="#/responses/200"),
         *   @SWG\Response(response=422, ref="#/responses/422"),
         *   @SWG\Response(response=401, ref="#/responses/401"),
         *   @SWG\Response(response=403, ref="#/responses/403"),
         *   @SWG\Response(response=500, ref="#/responses/500"),
		 * )
		 *
		 */
		public function config(Request $request)
		{
			$successMessage = __('Successful');
			$errorMessage   = __('An error has ocurred');

			try {
				DB::beginTransaction();

				$this->addSitesTermService->execute($request);

				DB::commit();

				return $this->successResponseWithMessage(
					[],
					$successMessage,
					Response::HTTP_CREATED
				);

			}
			catch(Exception $exception) {
				DB::rollBack();

                LogType::error(
                    __FILE__,
                    __LINE__,
                    $errorMessage,
                    [
                        'exception' => $exception,
                        'usersId'   => Auth::id(),
                    ]
                );

				return $this->errorCatchResponse(
					$exception,
					$errorMessage,
					Response::HTTP_INTERNAL_SERVER_ERROR
				);
			}
		}
	}
