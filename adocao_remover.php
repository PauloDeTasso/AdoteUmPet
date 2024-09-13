<?php
session_start();
require 'conexao_db.php';

if (!isset($_SESSION['cpf']) || $_SESSION['tipo'] !== 'ADMINISTRADOR') {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? '';

if ($id) {
    $pdo = conectar();
    $sql = 'DELETE FROM Adocao WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
}

header('Location: adocoes.php');
exit;