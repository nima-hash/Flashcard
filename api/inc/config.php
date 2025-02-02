<?php
// define("PROJECT_ROOT_PATH", __DIR__ . '/../');
$dotenv = file(__DIR__ . "/databankconfig.env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($dotenv as $line) {
    putenv(trim($line));
}

$DB_HOST = getenv("DB_HOST");
$DB_USERNAME = getenv("DB_USERNAME");
$DB_PASSWORD = getenv("DB_PASSWORD");
$DB_DATABASE_NAME = getenv("DB_DATABASE_NAME");
$DB_PORT = getenv("DB_PORT");
// define("DB_HOST", "127.0.0.1");
// define("DB_USERNAME", "root");
// define("DB_PASSWORD", "root");
// define("DB_DATABASE_NAME", "Flashcards");
// define("DB_PORT", "8889");