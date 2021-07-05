<?php

namespace App\Console\Commands;

use App\Core\Reports\Models\ReportV2;
use App\Core\Base\Traits\LogCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class ReporterV2 extends Command
{
    use LogCache;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report_v2:process';

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
        //$this->record_log('access', 'GENERATING REPORT V2');
        /**
         * Board
         */
        $now = now()->format('Y-m-d-H');
        $static_name = "reports/board_v2.json";
        $name = $static_name . $now;
        $board_report = new ReportV2();
        $board_report->board_info_json_data(1, $name, 'en');

        /**
         * End Board
         */
    }
}
