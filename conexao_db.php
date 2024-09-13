<?php
function conectar()
{
    $host = 'localhost';
    $dbname = 'adote_um_pet';
    $user = 'postgres';
    $password = '12345678';

    try {
        $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Erro na conexão: " . $e->getMessage());
    }
}