<?php
namespace Goodish\GatewayRoom;

use Goodish\GatewayRoom\Workers\BusinessWorker;
use Goodish\GatewayRoom\Workers\GatewayWorker;
use Goodish\GatewayRoom\Workers\HttpStaticalWorker;
use Goodish\GatewayRoom\Workers\RegisterWorker;
use Workerman\Worker;

class RoomServer
{

    function start(): array
    {
        ini_set('display_errors', 'on');
        if (!$this->checkEnvironment($message)) {
            return [false, $message];
        }
        BusinessWorker::start();
        GatewayWorker::start();
        RegisterWorker::start();
        HttpStaticalWorker::start();
        global $argv;
        $argv[0] = 'start.php';
        $argv[1] = 'start';
        Worker::runAll();
        return [true, '启动成功'];
    }

    function checkEnvironment(&$message = ''): bool
    {
        if (str_starts_with(strtolower(PHP_OS), 'win')) {
            $message = 'start.php not support windows, please use start_for_win.bat';
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
}
