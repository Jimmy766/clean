<?php

namespace App\Console\Commands;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Traits\CacheUtilsTraits;
use App\Core\Base\Services\GetInfoFromExceptionService;
use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Base\Traits\ErrorNotificationTrait;
use App\Core\Base\Traits\LogCache;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendGroupNotificationCommand extends Command
{
    use LogCache;
    use ErrorNotificationTrait;
    use CacheUtilsTraits;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exception-notification:queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process to send group notification queue';

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
        try {
            foreach (ModelConst::LISTS_EXCEPTION_ERROR as $nameException) {
                $directNotification = true;
                $tag                = ModelConst::CACHE_NAME_EXCEPTION_NOTIFICATION;
                $nameCache          = 'rapi_errors_' . $nameException;
                $dataFromCache      = Cache::tags($tag)
                    ->get($nameCache);
                if ($dataFromCache !== null) {
                    $this->forgetCacheByTag($tag);
                    $countErrors = $dataFromCache[ 'count_errors' ];
                    if($countErrors === 1){
                        return false;
                    }
                    $type = $dataFromCache[ 'type_notification' ];
                    $this->sendSlackNotification($dataFromCache, $type, $directNotification);
                    $this->sendMailNotification($dataFromCache, $nameException, $type, $directNotification);
                }
            }
        }
        catch(Exception $exception) {
            $request = request();
            $infoEndpoint = GetInfoFromExceptionService::execute($request, $exception);
            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute(
                $request,
                'errors',
                'errors',
                'error:' . $exception->getMessage(),
                $infoEndpoint
            );
        }

    }

}
