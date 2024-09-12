<?php
$host = 'localhost';
$dbname = 'adote_um_pet';
$user = 'postgres';
$password = '12345678';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'UTF8'");
} catch (PDOException $e) {
    echo 'Erro de conexão: ' . $e->getMessage();
}