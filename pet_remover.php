<?php
session_start();
require 'conexao_db.php';

if (!isset($_SESSION['cpf']) || $_SESSION['tipo'] !== 'Administrador')
{
    header('Location: login.php');
    exit;
}

$brinco = $_GET['brinco'] ?? '';

$pdo = conectar();

try
{
    $sql = 'DELETE FROM Pet WHERE brinco = :brinco';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':brinco' => $brinco]);

    $_SESSION['message'] = 'Pet removido com sucesso!';
}
catch (Exception $e)
{
    $_SESSION['message'] = 'Erro ao remover o pet: ' . $e->getMessage();
}

header('Location: pets.php');
exit;
