<?php

function getDbConnection(): PDO
{
    $host = getenv('DB_HOST');
    $dbName = getenv('DB_NAME');
    $user = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');

    $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";

    return new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
}
