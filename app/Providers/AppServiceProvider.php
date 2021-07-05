<?php

namespace App\Providers;

use Illuminate\Support\Facades\Queue;
use App\Core\Base\Traits\LogCache;
use App\Core\Rapi\Services\DBLog;
use DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    use LogCache;
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        DB::listen(function($query) {

            if (!strpos($query->sql, 'log_config')) {

                //$log = 'Time: '.$query->time.' --- Query: '.$query->sql.' --- Parameters: '.var_export($query->bindings, true);
                //$string = str_replace(array("\n", "\r"), ' ', var_export($log, true));
                DBLog::getInstance()->queryTime($query);
                //$this->record_log('query', '[DBLOG_'.$query->connectionName.']'.$string);
            }
        });


        Queue::failing(function (JobFailed $event) {


            DB::table('failed_jobs')->insert([
                'connection' => $event->connectionName,
                'queue'      => $event->job,
                'payload'    => $event->exception,
            ]);
        });

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //HashedPassport::enableEncrytion();
    }
}
