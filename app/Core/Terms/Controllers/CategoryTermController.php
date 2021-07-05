<?php

	namespace App\Core\Terms\Controllers;

	use App\Core\Terms\Models\CategoryTerm;
	use App\Core\Base\Traits\CacheUtilsTraits;
	use App\Core\Terms\Collections\CategoryTermCollection;
	use App\Core\Terms\Requests\StoreCategoryTermRequest;
	use App\Core\Terms\Resources\CategoryTermResource;
    use App\Core\Base\Services\LogType;
    use App\Core\Base\Traits\ApiResponser;
    use App\Http\Controllers\Controller;
    use App\Http\Controllers\Exception;
    use Illuminate\Http\Request;
	use Illuminate\Http\Response;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;

	class CategoryTermController extends Controller
	{
		use ApiResponser,CacheUtilsTraits;

		/**
		 * CategoryTermController constructor.
		 */
		public function __construct()
		{
			$this->middleware('client.credentials');
			$this->middleware('check.external_access');
		}

		/**
		 * @SWG\Get(
		 *   path="/term_categories",
		 *   summary="Show Term Category by Name ",
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
		 *     @SWG\Parameter(
		 *     name="name",
		 *     in="query",
		 *     description="Category Name",
		 *     type="string",
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
		 *                                        @SWG\Items(ref="#/definitions/CategoryTerm")),
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
			$name=$request->name;
			$categoriesQuery=CategoryTerm::query();
			if($name!==null && $name !==''){
				$categoriesQuery=$categoriesQuery->where('name','like','%'.$name.'%');
			}
			$categories=$categoriesQuery->paginateFromCacheByRequest(['*'],CategoryTerm::TAG_CACHE_MODEL);
			$data['categories']=new CategoryTermCollection($categories);
			return $this->successResponseWithMessage($data);
		}

		/**
		 * @SWG\Post(
		 *   path="/term_categories",
		 *   summary="Store Term Category  ",
		 *   tags={"Traduction"},
		 *   @SWG\Parameter(
		 *     name="name",
		 *     in="formData",
		 *     description="Category Name",
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
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/CategoryTerm"),
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
		public function store(StoreCategoryTermRequest $request)
		{
			$successMessage = __('Successful');
			$errorMessage   = __('An error has ocurred');

			try {
				DB::beginTransaction();

				$categoryTerm=new CategoryTerm($request->validated());
				$categoryTerm->save();

				DB::commit();

				$tag = [ CategoryTerm::TAG_CACHE_MODEL, ];
				$this->forgetCacheByTag($tag);

				$data[ 'category' ] = new CategoryTermResource($categoryTerm);

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
		 *   path="/term_categories/{id_category}",
		 *   summary="Show Term Category by ID ",
		 *   tags={"Traduction"},
		 *   @SWG\Parameter(
		 *     name="id_category",
		 *     in="path",
		 *     description="Category ID",
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
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/CategoryTerm"),
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
		public function show(CategoryTerm $term_category)
		{

			$data['category']=new CategoryTermResource($term_category);
			return $this->successResponseWithMessage($data);
		}

		/**
		 * @SWG\Put(
		 *   path="/term_categories/{id_category}",
		 *   summary="Update Term Category ",
		 *   tags={"Traduction"},
		 *   consumes={"application/x-www-form-urlencoded"},
		 *   @SWG\Parameter(
		 *     name="id_category",
		 *     in="path",
		 *     description="Category ID",
		 *     type="string",
		 *     required=true,
		 *   ),
		 *   @SWG\Parameter(
		 *     name="name",
		 *     in="formData",
		 *     description="Category Name",
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
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/CategoryTerm"),
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
		public function update(CategoryTerm $term_category, StoreCategoryTermRequest $request)
		{
			$successMessage = __('Successful');
			$errorMessage   = __('An error has ocurred');

			try {
				DB::beginTransaction();

				$term_category->update($request->validated());
				$term_category->save();

				DB::commit();

				$tag = [ CategoryTerm::TAG_CACHE_MODEL, ];
				$this->forgetCacheByTag($tag);

				$data[ 'category' ] = new CategoryTermResource($term_category);

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
		 *   path="/term_categories/{id_category}",
		 *   summary="Delete Term Category ",
		 *   tags={"Traduction"},
		 *   @SWG\Parameter(
		 *     name="id_category",
		 *     in="path",
		 *     description="Category ID",
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
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/CategoryTerm"),
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
		public function destroy(CategoryTerm $term_category)
		{
			$successMessage = __('Successful');
			$errorMessage   = __('An error has ocurred');

			try {
				DB::beginTransaction();

				$term_category->delete();

				DB::commit();

				$tag = [ CategoryTerm::TAG_CACHE_MODEL, ];
				$this->forgetCacheByTag($tag);

				$data[ 'category' ] = new CategoryTermResource($term_category);

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
	}
