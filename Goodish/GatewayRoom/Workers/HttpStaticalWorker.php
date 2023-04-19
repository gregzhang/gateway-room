<?php

namespace Goodish\GatewayRoom\Workers;

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Worker;
use Multiavatar;
use Closure;

use Exception;

class HttpStaticalWorker
{
    protected string $web_root;

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

    static function start(array $options = null): Worker
    {
        return (new self)->work($options);
    }

    protected function getWebRoot(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'room';
    }

    protected function work(array $options = null): Worker
    {
        // WebServer
        $options['socket_name'] ??= "http://0.0.0.0:55151";
        $options['count'] ??= 2;
        $options['name'] ??= 'RoomHttpStatic';
        $options['web_root'] ??= null;
        $options['message_handler'] ??= null;

        $web = new Worker($options['socket_name']);
        $web->count = $options['count'];
        $web->name = $options['name'];
        $this->web_root = $options['web_root'] ?? $this->getWebRoot();
        $web->onMessage = $options['message_handler'] ?? $this->getDefaultHandler();
        return $web;
    }

    function getDefaultHandler(): Closure
    {
        return function (TcpConnection $connection, Request $request) {
            $_GET = $request->get();
            $path = $request->path();
            if ($path === '/') {
                $file = $this->web_root . '/index.html';
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

            $file = realpath($this->web_root . $path);
            if (false === $file) {
                $connection->send(new Response(404, [], '<h3>404 Not Found</h3>'));
                return;
            }
            // Security check! Very important!!!
            if (strpos($file, $this->web_root) !== 0) {
                $connection->send(new Response(400));
                return;
            }
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $connection->send($this->display($file));
                return;
            }
            $if_modified_since = $request->header('if-modified-since');
            if (!empty($if_modified_since)) {
                $info = stat($file);
                $modified_time = $info ? date('D, d M Y H:i:s', $info['mtime']) . ' ' . date_default_timezone_get() : '';
                if ($modified_time === $if_modified_since) {
                    $connection->send(new Response(304));
                    return;
                }
            }

            $connection->send((new Response())->withFile($file));
        };
    }
}
