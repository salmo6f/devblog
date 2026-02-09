<?php

function app_base_url()
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? (string) $_SERVER['SCRIPT_NAME'] : '';
    $dir = str_replace('\\', '/', rtrim(dirname($scriptName), '/'));

    foreach (array('/public', '/admin', '/student') as $suffix) {
        if ($dir === $suffix) {
            $dir = '';
            break;
        }
        if ($suffix !== '' && substr($dir, -strlen($suffix)) === $suffix) {
            $dir = substr($dir, 0, -strlen($suffix));
            break;
        }
    }

    $base = $dir;
    return $base;
}

function url_for($path = '')
{
    $base = app_base_url();
    $path = ltrim((string) $path, '/');

    if ($path === '') {
        return $base === '' ? '/' : ($base . '/');
    }

    return $base === '' ? ('/' . $path) : ($base . '/' . $path);
}

