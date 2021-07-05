<?php

	namespace App\Core\Terms\Controllers;

	use App\Core\Base\Traits\CacheUtilsTraits;
	use App\Core\Terms\Collections\SectionTermCollection;
	use App\Core\Terms\Requests\StoreSectionTermRequest;
	use App\Core\Terms\Resources\SectionTermResource;
	use App\Core\Terms\Models\SectionTerm;
    use App\Core\Base\Services\LogType;
    use App\Core\Base\Traits\ApiResponser;
    use App\Http\Controllers\Controller;
    use App\Http\Controllers\Exception;
    use Illuminate\Http\Request;
	use Illuminate\Http\Response;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;

	class SectionTermController extends Controller
	{
		use ApiResponser,CacheUtilsTraits;


		public function __construct()
		{
			$this->middleware('client.credentials');
			$this->middleware('check.external_access');
		}

		/**
		 * @SWG\Get(
		 *   path="/term_sections",
		 *   summary="Show Term Section by Name ",
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
		 *   security={
		 *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
		 *     {"password": {}, "user_ip":{},  "Content-Language":{}}
		 *   },
		 *   @SWG\Response(
		 *     response=200,
		 *     description="Successful operation",
		 *     @SWG\Schema(
		 *         @SWG\Property(property="data", type="array",
		 *                                        @SWG\Items(ref="#/definitions/SectionTerm")),
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
			$sectionsQuery=SectionTerm::query();
			if($name!==null && $name !==''){
				$sectionsQuery=$sectionsQuery->where('name','like','%'.$name.'%');
			}
			$sections=$sectionsQuery->paginateFromCacheByRequest(['*'],SectionTerm::TAG_CACHE_MODEL);
			$data['sections']=new SectionTermCollection($sections);
			return $this->successResponseWithMessage($data);
		}

		/**
		 * @SWG\Post(
		 *   path="/term_sections",
		 *   summary="Store Term Section  ",
		 *   tags={"Traduction"},
		 *   @SWG\Parameter(
		 *     name="name",
		 *     in="formData",
		 *     description="Section Name",
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
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/SectionTerm"),
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
		public function store(StoreSectionTermRequest $request)
		{
			$successMessage = __('Successful');
			$errorMessage   = __('An error has ocurred');

			try {
				DB::beginTransaction();

				$sectionTerm=new SectionTerm($request->validated());
				$sectionTerm->save();

				DB::commit();

				$tag = [ SectionTerm::TAG_CACHE_MODEL, ];
				$this->forgetCacheByTag($tag);

				$data[ 'section' ] = new SectionTermResource($sectionTerm);

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
		 *   path="/term_sections/{id_section}",
		 *   summary="Show Term Section by ID ",
		 *   tags={"Traduction"},
		 *   @SWG\Parameter(
		 *     name="id_section",
		 *     in="path",
		 *     description="Section ID",
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
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/SectionTerm"),
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
		public function show(SectionTerm $term_section)
		{

			$data['section']=new SectionTermResource($term_section);
			return $this->successResponseWithMessage($data);
		}

		/**
		 * @SWG\Put(
		 *   path="/term_sections/{id_section}",
		 *   summary="Update Term Section ",
		 *   tags={"Traduction"},
		 *   consumes={"application/x-www-form-urlencoded"},
		 *   @SWG\Parameter(
		 *     name="id_section",
		 *     in="path",
		 *     description="Section ID",
		 *     type="string",
		 *     required=true,
		 *   ),
		 *   @SWG\Parameter(
		 *     name="name",
		 *     in="formData",
		 *     description="Section Name",
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
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/SectionTerm"),
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
		public function update(SectionTerm $term_section, StoreSectionTermRequest $request)
		{
			$successMessage = __('Successful');
			$errorMessage   = __('An error has ocurred');

			try {
				DB::beginTransaction();

				$term_section->update($request->validated());
				$term_section->save();

				DB::commit();

				$tag = [ SectionTerm::TAG_CACHE_MODEL, ];
				$this->forgetCacheByTag($tag);

				$data[ 'section' ] = new SectionTermResource($term_section);

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
		 *   path="/term_sections/{id_section}",
		 *   summary="Delete Term Section ",
		 *   tags={"Traduction"},
		 *   @SWG\Parameter(
		 *     name="id_section",
		 *     in="path",
		 *     description="Section ID",
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
		 *         @SWG\Property(property="data", type="object",ref="#/definitions/SectionTerm"),
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
		public function destroy(SectionTerm $term_section)
		{
			$successMessage = __('Successful');
			$errorMessage   = __('An error has ocurred');

			try {
				DB::beginTransaction();

				$term_section->delete();

				DB::commit();

				$tag = [ SectionTerm::TAG_CACHE_MODEL, ];
				$this->forgetCacheByTag($tag);

				$data[ 'section' ] = new SectionTermResource($term_section);

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
