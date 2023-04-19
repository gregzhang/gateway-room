<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Goodish\GatewayRoom\RoomServer;

(new RoomServer())->start();
