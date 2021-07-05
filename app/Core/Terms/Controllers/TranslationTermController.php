<?php

	namespace App\Core\Terms\Controllers;

	use App\Core\Base\Classes\ModelConst;
	use App\Core\Base\Services\TranslateArrayService;
	use App\Core\Base\Traits\CacheUtilsTraits;
	use App\Core\Terms\Collections\TermCollection;
	use App\Core\Terms\Collections\TranslationTermCollection;
	use App\Core\Terms\Requests\StoreTranslationTermRequest;
    use App\Http\Controllers\Controller;
    use App\Http\Requests\UpdateTranslationTermRequest;
	use App\Core\Languages\Resources\LanguageResource;
	use App\Core\Terms\Resources\TranslationTermResource;
	use App\Core\Terms\Models\Language;
    use App\Core\Base\Services\LogType;
    use App\Core\Terms\Models\Term;
	use App\Core\Base\Traits\ApiResponser;
	use App\Core\Terms\Models\TranslationTerm;
	use Exception;
	use Illuminate\Http\Request;
	use Illuminate\Http\Response;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;

	class TranslationTermController extends Controller
	{
		use ApiResponser, CacheUtilsTraits;


		public function __construct()
		{
			$this->middleware('client.credentials');
			$this->middleware('check.external_access');
		}

		/**
		 * @SWG\Get(
		 *   path="/term_translations",
		 *   summary="Show Term Translation list ",
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
		 *   security={
		 *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
		 *     {"password": {}, "user_ip":{},  "Content-Language":{}}
		 *   },
		 *   @SWG\Response(
		 *     response=200,
		 *     description="Successful operation",
		 *     @SWG\Schema(
		 *         @SWG\Property(property="data", type="array",
		 *                                        @SWG\Items(ref="#/definitions/TranslationTerm")),
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
		public function index()
		{
			$relations              = ['term'];
			$translations           = TranslationTerm::with($relations)
				->paginateFromCacheByRequest(['*'],TranslationTerm::TAG_CACHE_MODEL);
			$translationsCollection = new TranslationTermCollection($translations);
			$data['translations']   = $translationsCollection;
			return $this->successResponseWithMessage($data);
		}

		/**
		 * @SWG\Get(
		 *   path="/term_translations/create",
		 *   summary="Get info necessary to store Term Translations",
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
		 *     name="term_name",
		 *     in="query",
		 *     description="Term Name",
		 *     type="string"
		 *   ),
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
		public function create(Request $request)
		{
			$languages = Language::getFromCache(['*'],Language::TAG_CACHE_MODEL);
			$languages = LanguageResource::collection($languages);
			$term_name=$request->term_name;
			$termsQuery=Term::query();
			if($term_name !== null && $term_name !==''){
				$termsQuery     = $termsQuery->where('name','like','%'.$term_name.'%');
			}
			$terms=$termsQuery->paginateFromCacheByRequest(['*'],Term::TAG_CACHE_MODEL);

			$terms     = new TermCollection($terms);

			$data = [
				'languages' => $languages,
				'terms'     => $terms,
				'status'    => TranslateArrayService::execute(ModelConst::TRANSLATION_STATUS_RANGE)
			];
			return $this->successResponseWithMessage($data);
		}

		/**
		 * @SWG\Post(
		 *   path="/term_translations",
		 *   summary="Store Term Translation ",
		 *   tags={"Traduction"},
		 *   consumes={"application/json"},
		 *
		 *   @SWG\Parameter(
		 *     name="request",
		 *     in="body",
		 *     description="request body json",
		 *     type="object",
		 *     @SWG\Schema(
		 *         ref="#/definitions/StoreTranslationTerm",
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
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/TranslationTerm"),
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
		 * @param StoreTranslationTermRequest $request
		 * @return \Illuminate\Http\JsonResponse
		 */
		public function store(StoreTranslationTermRequest $request)
		{
			$successMessage = __('Successful');
			$errorMessage   = __('An error has ocurred');

			try {
				DB::beginTransaction();

				$translation = new TranslationTerm($request->validated());
				$translation->save();


				DB::commit();

				$tag = [TranslationTerm::TAG_CACHE_MODEL];
				$this->forgetCacheByTag($tag);

				$translation->load([
					'term'
				]);

				$data['translation'] = new TranslationTermResource($translation);


				return $this->successResponseWithMessage(
					$data,
					$successMessage,
					Response::HTTP_CREATED
				);

			} catch (Exception $exception) {
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
		 *   path="/term_translations/{id_term_has_language}",
		 *   summary="Show Translation by ID ",
		 *   tags={"Traduction"},
		 *   @SWG\Parameter(
		 *     name="id_term_has_language",
		 *     in="path",
		 *     description="Translation ID",
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
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/TranslationTerm"),
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
		public function show(TranslationTerm $term_translation)
		{
			$term_translation->load([
				'term'
			]);
			$data['translation'] = new TranslationTermResource($term_translation);
			return $this->successResponseWithMessage($data['translation']);
		}

		/**
		 * @SWG\Put(
		 *   path="/term_translations/{id_term_has_language}",
		 *   summary="Update Translation ",
		 *   tags={"Traduction"},
		 *   consumes={"application/json"},
		 *   @SWG\Parameter(
		 *     name="id_term_has_language",
		 *     in="path",
		 *     description="Translation ID",
		 *     type="integer",
		 *     required=true,
		 *   ),
		 *   @SWG\Parameter(
		 *     name="request",
		 *     in="body",
		 *     description="request body json",
		 *     type="object",
		 *     @SWG\Schema(
		 *         ref="#/definitions/StoreTranslationTerm",
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
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/TranslationTerm"),
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
		public function update(TranslationTerm $term_translation, StoreTranslationTermRequest $request)
		{
			$successMessage = __('Successful');
			$errorMessage   = __('An error has ocurred');

			try {
				DB::beginTransaction();

				$term_translation->fill($request->validated());
				$term_translation->save();

				DB::commit();

                $tag = [TranslationTerm::TAG_CACHE_MODEL];
				$this->forgetCacheByTag($tag);

				$term_translation->load([
					'term'
				]);

				$data['translation'] = new TranslationTermResource($term_translation);


				return $this->successResponseWithMessage(
					$data,
					$successMessage,
					Response::HTTP_CREATED
				);

			} catch (Exception $exception) {
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
		 *   path="/term_translations/{id_term_has_language}",
		 *   summary="Delete Translation ",
		 *   tags={"Traduction"},
		 *   @SWG\Parameter(
		 *     name="id_term_has_language",
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
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/TranslationTerm"),
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
		public function destroy(TranslationTerm $term_translation)
		{
			$successMessage = __('Successful');
			$errorMessage   = __('An error has ocurred');

			try {
				DB::beginTransaction();

				$term_translation->delete();

				DB::commit();

                $tag = [TranslationTerm::TAG_CACHE_MODEL];
				$this->forgetCacheByTag($tag);

				$data['translation'] = new TranslationTermResource($term_translation);

				return $this->successResponseWithMessage(
					$data,
					$successMessage,
					Response::HTTP_CREATED
				);

			} catch (Exception $exception) {
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
