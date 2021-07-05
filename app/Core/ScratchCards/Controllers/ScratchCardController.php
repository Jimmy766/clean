<?php

    namespace App\Core\ScratchCards\Controllers;

    use App\Core\ScratchCards\Models\ScratchCard;
    use App\Core\ScratchCards\Services\AllScratchCardsActiveService;
    use App\Core\Base\Traits\Pixels;
    use App\Http\Controllers\ApiController;
    use Dotenv\Exception\ValidationException;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

    /**
     * Class ScratchCardController
     * @package App\Http\Controllers
     */
    class ScratchCardController extends ApiController
    {
        use Pixels;

        /**
         * @var \App\Core\ScratchCards\Services\AllScratchCardsActiveService
         */
        private $allScratchCardsActiveService;

        public function __construct(AllScratchCardsActiveService $allScratchCardsActiveService)
        {
            parent::__construct();
            $this->middleware('client.credentials')->only('index', 'show', 'paytables', 'src');
            $this->allScratchCardsActiveService = $allScratchCardsActiveService;
        }

        /**
         * @SWG\Get(
         *   path="/scratch_cards",
         *   summary="Show scratch cards list ",
         *   tags={"Scratch Cards"},
         *   security={
         *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
         *     {"password": {}, "user_ip":{},  "Content-Language":{}},
         *   },
         *   @SWG\Response(
         *     response=200,
         *     description="Successful operation",
         *     @SWG\Schema(
         *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/ScratchCard")),
         *     ),
         *   ),
         *   @SWG\Response(response=401, ref="#/responses/401"),
         *   @SWG\Response(response=403, ref="#/responses/403"),
         *   @SWG\Response(response=500, ref="#/responses/500"),
         * )
         *
         */
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function index()
        {
            $scratches = $this->allScratchCardsActiveService->execute();
            return $this->showAllNoPaginated($scratches);
        }


        /**
         * @SWG\Get(
         *   path="/scratch_cards/{scratch_card}",
         *   summary="Show scratch card details ",
         *   tags={"Scratch Cards"},
         *   @SWG\Parameter(
         *     name="scratch_card",
         *     in="path",
         *     description="Scratch card Id.",
         *     required=true,
         *     type="integer"
         *   ),
         *   security={
         *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
         *     {"password": {}, "user_ip":{},  "Content-Language":{}},
         *   },
         *   @SWG\Response(
         *     response=200,
         *     description="Successful operation",
         *     @SWG\Schema(
         *         @SWG\Property(
         *         property="data",
         *         allOf={
         *          @SWG\Schema(ref="#/definitions/ScratchCard"),
         *         }
         *       ),
         *     ),
         *   ),
         *   @SWG\Response(response=401, ref="#/responses/401"),
         *   @SWG\Response(response=403, ref="#/responses/403"),
         *   @SWG\Response(response=422, ref="#/responses/422"),
         *   @SWG\Response(response=500, ref="#/responses/500"),
         * )
         *
         */
        /**
         * Display the specified resource.
         *
         * @param  \App\Core\ScratchCards\Models\ScratchCard $scratch_card
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function show($scratch_card) {
            $relations = ['prices.prices_lines'];
            $scratch_card = ScratchCard::query()
                ->where('id', $scratch_card)
                ->with($relations)
                ->firstFromCache([ '*' ]);
            if($scratch_card === null){
                throw new UnprocessableEntityHttpException(__('scratch cart dont exist'), null, Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $prices = $scratch_card->prices;
            $price = $prices->first();
            $price_id = $price->prc_id;
            $price_line_id = $price->price_line['identifier'];
            $product = [
                'id' => $scratch_card->id,
                'name' => $scratch_card->name,
            ];
            $request = request();
            $request->merge(['pixel' => $this->retargeting(7, $product, $price_id, $price_line_id)]);

            return self::client_scratch_cards(1)->pluck('product_id')->contains($scratch_card->id) ? $this->showOne($scratch_card) : $this->errorResponse(trans('lang.scratch_forbidden'), 403);
        }

        /**
         * @SWG\Get(
         *   path="/scratch_cards/paytables/{scratch_card}",
         *   summary="Show scratch cards pay tables",
         *   tags={"Scratch Cards"},
         *   @SWG\Parameter(
         *     name="scratch_card",
         *     in="path",
         *     description="Scratch card Id.",
         *     required=true,
         *     type="integer"
         *   ),
         *   security={
         *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
         *     {"password": {}, "user_ip":{},  "Content-Language":{}},
         *   },
         *   @SWG\Response(
         *     response=200,
         *     description="Successful operation",
         *     @SWG\Schema(
         *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/ScratchCardPayTable")),
         *     ),
         *   ),
         *   @SWG\Response(response=401, ref="#/responses/401"),
         *   @SWG\Response(response=422, ref="#/responses/422"),
         *   @SWG\Response(response=403, ref="#/responses/403"),
         *   @SWG\Response(response=500, ref="#/responses/500"),
         * )
         *
         */
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function paytables(ScratchCard $scratch_card) {
            return self::client_scratch_cards(1)->pluck('product_id')->contains($scratch_card->id) ? $this->showAllNoPaginated($scratch_card->paytables) : $this->errorResponse(trans('lang.scratch_forbidden'), 403);
        }

        /**
         * @param Request                                   $request
         * @param \App\Core\ScratchCards\Models\ScratchCard $scratch_card
         * @return \Illuminate\Http\JsonResponse
         */
        public function src(Request $request, ScratchCard $scratch_card) {
            $rules = [
//                validate
                'is_mobile' => 'required|boolean',
            ];
            $this->validate($request, $rules);
            if (!self::client_scratch_cards(1)->pluck('product_id')->contains($scratch_card->id) )
                return $this->errorResponse(trans('lang.scratch_forbidden'), 403);
            if ($scratch_card->active) {
                $language = $this->getLanguage();
                $data = $scratch_card->srcDemo($request->is_mobile, $language);
                if (isset($data->Url))
                    return $this->successResponse(['data' => (array)$data]);
                else
                    return $this->errorResponse($data->error, 422);
            } else {
                return $this->errorResponse(trans('lang.scratch_forbidden'), 422);
            }
        }
    }
