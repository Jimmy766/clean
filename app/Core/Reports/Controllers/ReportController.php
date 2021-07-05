<?php

namespace App\Core\Reports\Controllers;

use App\Core\Clients\Models\Client;
use App\Console\Commands\Reporter;
use App\Core\Reports\Models\Report;
use App\Http\Controllers\ApiController;
use App\ReportsType;
use App\Core\Reports\Models\ReportType;
use App\Core\Rapi\Models\Site;
use App\Core\Reports\Transforms\ReportTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Auth;

class ReportController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('client.credentials');
        //$this->middleware('client.credentials:reports');
        $this->middleware('transform.input:' . ReportTransformer::class);

    }

    /**
     * @SWG\Get(
     *   path="/reports",
     *   summary="Show reports list ",
     *   tags={"Reports"},
     *   security={{"client_credentials": {}, "user_ip":{}}},
     *   @SWG\Parameter(
     *     name="sort_by_asc",
     *     in="query",
     *     description="Attribute to Sort in Ascending Order",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="sort_by_desc",
     *     in="query",
     *     description="Attribute to Sort in Descending Order",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="status",
     *     in="query",
     *     description="onhold, processing, error, ready",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="tag",
     *     in="query",
     *     description="customer_information, customer_optin, customer_activity, lottery_activity",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Report")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $date_to = Carbon::now()->subHours(48)->format('Y-m-d H:i:s');
        $client = Client::where('id', $request['oauth_client_id'])->first();
        $site = Site::where('site_id', $client->site_id)->first();

        $reports = Report::where('created_at', '>=', $date_to)->where('sys_id', '=', $site->sys_id)->get();
        return $this->showAllNoPaginated($reports);
    }

    /**
     * @SWG\Post(
     *   path="/reports",
     *   summary="Register new report",
     *   tags={"Reports"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="start",
     *     in="formData",
     *     description="Report start date (YYYY-MM-DD)",
     *     type="string",
     *     format="date-time",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="end",
     *     in="formData",
     *     description="Report end date (YYYY-MM-DD)",
     *     type="string",
     *     format="date-time",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="tag",
     *     in="formData",
     *     description="Report type ( customer_information, customer_optin, customer_activity, lottery_activity)",
     *     type="string",
     *     required=true,
     *   ),
     *   security={{"client_credentials": {}, "user_ip":{}}},
     *   @SWG\Response(response=201, ref="#/responses/201"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $rules = [
            'start' => 'required|date',
            'end' => 'required|date_format:"Y-m-d"|after:start',
            'tag' => 'required|exists:report_types,tag',
        ];


       // if ($request->filled('oauth_client_id')){
    $this->validate($request, $rules);
    $client = Client::where('id', $request['oauth_client_id'])->first();
    $site = Site::where('site_id', $client->site_id)->first();
    $token = md5(now());
    $request->request->add(['status' => 'onhold', 'url' => '', 'token' => $token, 'sys_id' => $site->sys_id]);

    $report = Report::create($request->all());

    $report->id = 0;
    if ($report->id == null || $report->id == 0) {
        //$report->id = Report::where(1, '=', '1')->get()->last();;
        $report->id = Report::pluck('id')->last();
        //$report->id = Report::select('SELECT LAST_INSERT_ID()');
    }

    return $this->showOne($report, 201);
    /*
}else{
    abort(401, 'Unauthorized');

}
*/



    }


    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @SWG\Get(
     *   path="/reports/{report}",
     *   summary="Show report details ",
     *   tags={"Reports"},
     *   @SWG\Parameter(
     *     name="report",
     *     in="path",
     *     description="Report Id.",
     *     required=true,
     *     type="string"
     *   ),
     *   security={{"client_credentials": {}, "user_ip":{}}},
     *   @SWG\Response(response=200, ref="#/responses/200"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function show(Request $request, Report $report)
    {
        $client = Client::where('id', $request['oauth_client_id'])->first();
        $site = Site::where('site_id', $client->site_id)->first();
        if ($site->sys_id != $report->sys_id)
            return $this->errorResponse(trans('lang.invalid_client'), 422);
        return $this->showOne($report);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @SWG\Delete(
     *   path="/reports/{report}",
     *   summary="Delete Report",
     *   tags={"Reports"},
     *   @SWG\Parameter(
     *     name="report",
     *     in="path",
     *     description="Report Id.",
     *     required=true,
     *     type="string"
     *   ),
     *   security={{"client_credentials": {}, "user_ip":{}}},
     *   @SWG\Response(response=200, ref="#/responses/200"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function destroy(Request $request, Report $report)
    {
        $client = Client::where('id', $request['oauth_client_id'])->first();
        $site = Site::where('site_id', $client->site_id)->first();
        if ($site->sys_id != $report->sys_id)
            return $this->errorResponse(trans('lang.invalid_client'), 422);
        return $this->successResponse(['data' => ['delete' => $report->delete()]], 200);
    }

    public function reportTest(Request $request)
    {

        $rules = [
            'start' => 'required|date',
            'end' => 'required|date_format:"Y-m-d"|after:start',
            'tag' => 'required|exists:report_types,tag',
        ];
        $this->validate($request, $rules);
        $client = Client::where('id', $request['oauth_client_id'])->first();
        $site = Site::where('site_id', $client->site_id)->first();
        $token = md5(now());
        $request->request->add(['status' => 'onhold', 'url' => '', 'token' => $token, 'sys_id' => $site->sys_id]);

        $report = Report::create($request->all());

        print_r($report);
        dd("aca termina");
        //return $report;
        //return $this->showOne($report, 201);

    }

    public function test(Request $request)
    {

        $queue = Report::where('status', '=', 'onhold')
            ->get()->sortBy('created_at');

        $item = $queue->isNotEmpty() ? $queue->first() : null;
        if ($item) {
            $item->status = 'processing';
            $item->save();
            $name = 'reports/' . $item->token . '.csv';
            $report_type = ReportType::where('tag', '=', $item->tag)->get();
            $report_type = $report_type->isNotEmpty() ? $report_type->first()->id : 0;
            try {
                switch ($report_type) {
                    case 1:
                        $item->customer_information($request, $item->start, $item->end,
                        $item->sys_id, $name);
                        break;
                    case 2:
                        $item->customer_optin($request, $item->start, $item->end, $item->sys_id, $name);
                        break;
                    case 3:
                        $item->customer_activity($request, $item->start, $item->end, $item->sys_id, $name);
                        break;
                    case 4:
                        $item->lottery_activity($request, $item->start, $item->end, $item->sys_id, $name);
                        break;
                    default:
                        $item->status = 'error';
                        $item->save();
                }
            } catch (\Exception $exception) {
                $item->status = 'error';
                $item->url = substr($exception->getMessage(), 0, 250);
                $item->save();
            }

            if ($item->status !== 'error') {
                $item->status = 'ready';
                $item->url = config('app.url') . '/api/download/' . $name . '?user_ip=182.10.12.10';
                $item->save();
            }
        }


        $reporter = new Report();
        $date1 = Carbon::now()->startOfMonth();
        $date2 = Carbon::now();
        switch ($request->id) {
            case 1:
                return $reporter->customer_information($request, $date1, $date2, 1, 'file_1');
            case 2:
                return $reporter->customer_optin($request, $date1, $date2, 1, 'file_2');
            case 3:
                return $reporter->customer_activity($request, $date1, $date2, 1, 'file_3');
            case 4:
                return $reporter->lottery_activity($request, $date1, $date2, 1, 'file_4');
        }
    }
    public function download($fileName)
    {

        try {
            //get content from S3
            $file = Storage::disk('s3')->get('reports/' . $fileName);
            //save to local
            Storage::disk('local')->put('reports/' . $fileName, $file);
            //delete from S3
            Storage::disk('s3')->delete('reports/' . $fileName);
            //Return local file
            return Storage::disk('local')->download('reports/' . $fileName);;
        } catch (\Exception $e) {

            return "Error";
        }
    }

    public function reportsV2(){
        try{
            Artisan::call("report_v2:process");
            echo "Process queue";
        }catch (\Exception $ex){
            print_r($ex->getMessage());
        }
    }

}
