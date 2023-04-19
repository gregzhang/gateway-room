<?php

namespace Goodish\GatewayRoom\Workers;

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Worker;
use Multiavatar;

use Exception;

class HttpStaticalWorker
{

    protected function display($file)
    {
        ob_start();
        // Try to include php file.
        try {
            include $file;
        } catch (Exception $e) {
            echo $e;
        }
        return ob_get_clean();
    }

    static function start(): Worker
    {
        return (new self)->work();
    }

    protected function work(): Worker
    {
        // WebServer
        $web = new Worker("http://0.0.0.0:55151");
        // WebServer进程数量
        $web->count = 2;

        $web->name = 'RoomHttpStatic';

        if (function_exists('storage_path')) {
            define('WEBROOT', storage_path('room'));
        } else {
            define('WEBROOT', __DIR__ . DIRECTORY_SEPARATOR . '..'
                . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'room');
        }


        $web->onMessage = function (TcpConnection $connection, Request $request) {

            $_GET = $request->get();
            $path = $request->path();

            if ($path === '/') {
//                $connection->send($this->display(WEBROOT . '/index.php'));
                $file = WEBROOT . '/index.html';
                $connection->send((new Response())->withFile($file));
                return;
            }

            if ($path === '/avatar') {
                $avatar = new Multiavatar();
                $avatarId = $_GET['name'];
                $svg = $avatar($avatarId, true, null);
                $connection->send(new Response(200, ['Content-Type' => 'image/svg+xml'], $svg));
                return;
            }

            $file = realpath(WEBROOT . $path);
            if (false === $file) {
                $connection->send(new Response(404, [], '<h3>404 Not Found</h3>'));
                return;
            }
            // Security check! Very important!!!
            if (strpos($file, WEBROOT) !== 0) {
                $connection->send(new Response(400));
                return;
            }
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $connection->send($this->display($file));
                return;
            }

            $if_modified_since = $request->header('if-modified-since');
            if (!empty($if_modified_since)) {
                // Check 304.
                $info = stat($file);
                $modified_time = $info ? date('D, d M Y H:i:s', $info['mtime']) . ' ' . date_default_timezone_get() : '';
                if ($modified_time === $if_modified_since) {
                    $connection->send(new Response(304));
                    return;
                }
            }
            $connection->send((new Response())->withFile($file));
        };

        return $web;
    }

}
