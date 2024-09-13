<?php
session_start();
require 'conexao_db.php';

if (!isset($_SESSION['cpf']) || $_SESSION['tipo'] !== 'ADMINISTRADOR') {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? '';

$pdo = conectar();

$sql = 'DELETE FROM Pet WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);

header('Location: pets.php');
exit;