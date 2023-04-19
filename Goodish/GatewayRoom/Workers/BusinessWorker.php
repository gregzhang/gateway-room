<?php


namespace Goodish\GatewayRoom\Workers;

use Goodish\GatewayRoom\Events\BusinessEvents;
use GatewayWorker\BusinessWorker as WorkerManBusinessWorker;
use Workerman\Worker;

class BusinessWorker
{
    static function start(array $options = null): Worker
    {
        $options['name'] ??= 'RoomBusinessWorker';
        $options['count'] ??= 4;
        $options['handler'] ??= BusinessEvents::class;
        $options['register_address'] ??= '127.0.0.1:1236';

        $worker = new WorkerManBusinessWorker();
        $worker->name = $options['name'];
        $worker->count = $options['count'];
        $worker->eventHandler = $options['handler'];
        $worker->registerAddress = $options['register_address'];
        return $worker;
    }

}
