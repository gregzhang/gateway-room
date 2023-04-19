<?php


namespace Goodish\GatewayRoom\Workers;

use GatewayWorker\Gateway;
use Workerman\Worker;

class GatewayWorker
{
    static function start(array $options = null): Worker
    {
        $options['socket_name'] ??= "Websocket://0.0.0.0:7272";
        $options['name'] ??= 'RoomGateway';
        $options['count'] ??= 4;
        $options['lan_ip'] ??= '127.0.0.1';
        $options['port']['start'] ??= 2300;
        $options['ping']['interval'] ??= 10;
        $options['ping']['data'] ??= '{"type":"ping"}';
        $options['register']['address'] ??= '127.0.0.1:1236';

        $gateway = new Gateway($options['socket_name']);
        $gateway->name = $options['name'];
        $gateway->count = $options['count'];
        $gateway->lanIp = $options['lan_ip'];
        $gateway->startPort = $options['port']['start'];
        $gateway->pingInterval = $options['ping']['interval'];
        $gateway->pingData = $options['ping']['data'];
        $gateway->registerAddress = $options['register']['address'];

        return $gateway;
    }
}
