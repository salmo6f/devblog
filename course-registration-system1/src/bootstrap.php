<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/util.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/url.php';
require_once __DIR__ . '/flash.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/schema.php';
require_once __DIR__ . '/view.php';

if (isset($conn) && $conn instanceof mysqli) {
    $conn->set_charset('utf8mb4');
}
