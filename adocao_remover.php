<?php
include_once "start.php";

$id = $_GET['id'] ?? '';

if ($id)
{
    $pdo = conectar();
    $sql = 'DELETE FROM Adocao WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
}

header('Location: adocoes.php');
exit;
