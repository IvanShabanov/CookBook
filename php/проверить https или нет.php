<?php

list($host, $port) = explode(':', $_SERVER['HTTP_HOST']);
if ((strtolower($_SERVER['HTTPS']) !== 'off') ||
    ($port == 465) ||
    ($_SERVER['SERVER_PORT'] == 465) ||
    (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') ||
    (strtolower($_SERVER["REQUEST_SCHEME"]) == 'https')) {
    $protokol = 'https://';
} else {
    $protokol = 'http://';
};