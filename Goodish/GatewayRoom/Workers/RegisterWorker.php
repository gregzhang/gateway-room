<?php


namespace Goodish\GatewayRoom\Workers;

use GatewayWorker\Register;

class RegisterWorker
{
    static function start(array $options = null)
    {
        $options['socket_name'] ??= 'text://0.0.0.0:1236';
        $options['name'] ??= 'RoomRegister';
        $worker =  new Register($options['socket_name']);
        $worker->name = $options['name'];
    }
}
