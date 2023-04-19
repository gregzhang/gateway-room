<?php
if (!function_exists('storage_path')) {
    function storage_path($path): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . $path;
    }
}
