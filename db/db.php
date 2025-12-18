<?php

$DB_HOST = "db";
$DB_NAME = "eclipse";
$DB_USER = "eclipse_user";
$DB_PASS = "charlie_kirk";

$dsn = "mysql:host={$DB_HOST};port=3306;dbname={$DB_NAME};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}