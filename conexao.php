<?php
$host = 'localhost';
$dbname = 'AppM';
$user = 'postgres';
$password = 'minduim';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("DB connect error: " . $e->getMessage());
}
?>
