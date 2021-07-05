<?php

namespace App\Console\Commands;

use App\Core\Reports\Models\Report;
use App\Core\Reports\Models\ReportType;
use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Base\Traits\LogCache;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class Reporter extends Command
{
    use LogCache;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reporter:queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process reporter queue';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(env('APP_ENV') !== 'prod'){
            $this->info('command only to prod');
            return false;
        }
        $request       = Request::create( '', 'GET', [] );
        $sendLogConsoleService = new SendLogConsoleService();

        $sendLogConsoleService->execute($request, 'reports','reports', 'Running cron ...');

        /**
         * FASTTRACK
         */
        $processing = Report::where('status', '=', 'processing')->get();

        if ($processing->isEmpty()) {
            $queue = Report::where('status', '=', 'onhold')->get()
                ->sortBy('created_at');
            $item = $queue->isNotEmpty() ? $queue->first() : null;
            if ($item) {
                $sendLogConsoleService->execute($request, 'reports','reports', 'Begin reporter');

                $this->info('Starting :' . $item->tag);
                $item->status = 'processing';
                $item->save();
                $this->info('Processing');
                $sendLogConsoleService->execute($request, 'reports','reports', 'ID: ' . $item->id . ' Processing');
                $name = 'reports/' . $item->token . '.csv';
                $report_type = ReportType::where('tag', '=', $item->tag)->get();
                $report_type = $report_type->isNotEmpty() ? $report_type->first()->id : 0;
                try {
                    switch ($report_type) {
                        case 1:
                            $item->customer_information($request, $item->start, $item->end, $item->sys_id, $name);
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
                    $item->save();
                    $this->info($exception->getMessage());

                    $tags='REPORTS ' . strtoupper(config('app.env')) . ' Id: ' . $item->id . ' - Error: ' . $exception->getMessage();
                    $sendLogConsoleService->execute($request, 'reports','reports', $tags);
                }

                if ($item->status !== 'error') {
                    $item->status = 'ready';
                    $item->url = config('app.url') . '/api/download/' . $name . '?user_ip=182.10.12.10';
                    $item->save();
                    Storage::disk('s3')->put($name, file_get_contents(App::getFacadeApplication()->basePath() . '/storage/app/public/' . $name));
                }

                $tags='ID: ' . $item->id . ' End report';
                $sendLogConsoleService->execute($request, 'reports','reports', $tags);

            }
        }
        /**
         * End Fasttrack
         */


        /**
         * Board
         */
        $now = now()->format('Y-m-d-H');
        $static_name = "reports/board.json";
        $name = $static_name . $now;
        if (!Storage::disk('s3')->exists($name)) {
            // hour doesn't exist. we create it and copy to static json
            $board_report = new Report();
            $board_report->board_info_json_data($request, 1, $name, 'en');

            $path = App::getFacadeApplication()->basePath() . '/storage/app/public/';
            //save to public bucket
            Storage::disk('s3-public')->put($static_name, file_get_contents($path . $name));
            file_put_contents($path . $static_name, file_get_contents($path . $name));

            $tags = 'ID: ' . $name . ' Copied board report json';

            $sendLogConsoleService->execute($request, 'reports','reports', $tags);

            // delete file from previous hour
            $last_hour = \Carbon\Carbon::now()->subHour(1);
            $last_hour_name = $static_name . $last_hour->format('Y-m-d-H');
            if (Storage::disk('s3')->exists($last_hour_name)) {
                Storage::disk('s3')->delete($last_hour_name);
            }
        }
        /**
         * End Board
         */
    }
}
