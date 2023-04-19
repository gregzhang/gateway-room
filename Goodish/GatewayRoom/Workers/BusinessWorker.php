<?php


namespace Goodish\GatewayRoom\Workers;

use Goodish\GatewayRoom\Events\BusinessEvents;
use GatewayWorker\BusinessWorker as WorkerManBusinessWorker;
use Workerman\Worker;

class BusinessWorker
{
    static function start(): Worker
    {
        // businessWorker 进程
        $worker = new WorkerManBusinessWorker();
        // worker名称
        $worker->name = 'RoomBusinessWorker';
        // businessWorker进程数量
        $worker->count = 4;

        $worker->eventHandler = BusinessEvents::class;
        // 服务注册地址
        $worker->registerAddress = '127.0.0.1:1236';

        return $worker;
    }

}
