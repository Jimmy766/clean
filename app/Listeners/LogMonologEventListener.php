<?php
namespace App\Listeners;
use App\Events\Logs\LogMonologEvent;
use App\AuxModels\ElasticLog;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Foundation\Bus\Dispatchable;
class LogMonologEventListener implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $queue = 'logs';
    protected $log;
    public function __construct(ElasticLog $log) {
        $this->log = $log;
}
    /**
     * @param $event
     */
    public function onLog($event)
    {
        $message = $event->records['message'];
        $formated = $event->records['formatted'];
        $level = $formated['level'];
        $token =  $formated['token'];
        $this->sendToElastic($level,$message,$token);

    }
    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            LogMonologEvent::class,
            'App\Listeners\LogMonologEventListener@onLog'
        );
    }
}