<?php

// CONFIG
define("MM_DB_HOST", "conduit-mariadb");
define("MM_DB_DATABASE", "conduit");
define("MM_DB_USER", "conduit");
define("MM_DB_PASSWORD", "password");


// ERROR REPORTING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DATABASE CONNECTION
$mm_pdo = new PDO('mysql:host=' . MM_DB_HOST . ';dbname=' . MM_DB_DATABASE . '', MM_DB_USER, MM_DB_PASSWORD, [
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
function db(): PDO
{
    global $mm_pdo;
    return $mm_pdo;
}

// RENDER INTO LAYOUT HELPER
ob_start();
function render_into_layout($path)
{
    $body = ob_get_contents();
    ob_end_clean();
    include($path);
}

