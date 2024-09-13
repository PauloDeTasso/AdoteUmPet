<?php
$host = 'localhost';
$dbname = 'adote_um_pet';
$user = 'postgres';
$password = '12345678';

try
{
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e)
{
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}