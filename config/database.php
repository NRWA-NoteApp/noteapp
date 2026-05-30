<?php

$host = getenv('DB_HOST') ?: '127.0.0.1';
$database = getenv('DB_DATABASE') ?: 'noteapp';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

$dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";

return new PDO($dsn, $username, $password, [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
