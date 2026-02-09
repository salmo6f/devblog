<?php
// Database connection (use env vars if set)
$DB_HOST = getenv('CRS_DB_HOST') ?: 'localhost';
$DB_USER = getenv('CRS_DB_USER') ?: 'root';
$DB_PASS = getenv('CRS_DB_PASS') ?: '';
$DB_NAME = getenv('CRS_DB_NAME') ?: 'course_registration';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
} catch (mysqli_sql_exception $e) {
    // If the database doesn't exist yet, try to create it (common in local XAMPP setups).
    if ((int) $e->getCode() === 1049) {
        $server = new mysqli($DB_HOST, $DB_USER, $DB_PASS);
        $dbEsc = str_replace('`', '``', $DB_NAME);
        $server->query("CREATE DATABASE IF NOT EXISTS `{$dbEsc}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $server->close();
        $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    } else {
        throw $e;
    }
}
?>
