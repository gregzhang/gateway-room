<?php
namespace Goodish\GatewayRoom;

use Goodish\GatewayRoom\Events\BusinessEvents;
use Goodish\GatewayRoom\Workers\BusinessWorker;
use Goodish\GatewayRoom\Workers\GatewayWorker;
use Goodish\GatewayRoom\Workers\HttpStaticalWorker;
use Goodish\GatewayRoom\Workers\RegisterWorker;
use Workerman\Worker;

class RoomServer
{

    function start(array $options = null): array
    {

        $options['business']['name'] ??= 'RoomBusinessWorker';
        $options['business']['count'] ??= 4;
        $options['business']['handler'] ??= null;
        $options['business']['register_address'] ??= '127.0.0.1:1236';

        $options['gateway']['socket_name'] ??= "Websocket://0.0.0.0:7272";
        $options['gateway']['name'] ??= 'RoomGateway';
        $options['gateway']['count'] ??= 4;
        $options['gateway']['lan_ip'] ??= '127.0.0.1';
        $options['gateway']['port']['start'] ??= 2300;
        $options['gateway']['ping']['interval'] ??= 10;
        $options['gateway']['ping']['data'] ??= '{"type":"ping"}';
        $options['gateway']['register']['address'] ??= '127.0.0.1:1236';

        $options['register']['socket_name'] ??= 'text://0.0.0.0:1236';
        $options['register']['name'] ??= 'RoomRegister';

        $options['http']['socket_name'] ??= "http://0.0.0.0:55151";
        $options['http']['count'] ??= 2;
        $options['http']['name'] ??= 'RoomHttpStatic';
        $options['http']['web_root'] ??= self::getWebRoot();
        $options['http']['message_handler'] ??= null;

        ini_set('display_errors', 'on');
        if (!$this->checkEnvironment($message)) {
            return [false, $message];
        }

        BusinessWorker::start($options['business'] ?? null );
        GatewayWorker::start( $options['gateway'] ?? null );
        RegisterWorker::start($options['register'] ?? null );
        HttpStaticalWorker::start($options['http'] ?? null);

        global $argv;
        $argv[0] = 'start.php';
        $argv[1] = 'start';
        Worker::runAll();
        return [true, '启动成功'];
    }

    function checkEnvironment(&$message = ''): bool
    {
        if (str_starts_with(strtolower(PHP_OS), 'win')) {
            $message = 'error os';
            return false;
        }

        // 检查扩展
        if (!extension_loaded('pcntl')) {
            $message = "Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html";
            return false;
        }

        if (!extension_loaded('posix')) {
            $message = "Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html";
            return false;
        }

        return true;
    }

    static function getWebRoot(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'room';
    }
}
