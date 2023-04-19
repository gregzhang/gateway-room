<?php


namespace Goodish\GatewayRoom\Workers;

use GatewayWorker\Register;
use Workerman\Worker;

class RegisterWorker
{
    static function start()
    {
        $worker =  new Register('text://0.0.0.0:1236');
        $worker->name = 'RoomRegister';
    }
}
