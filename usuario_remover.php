<?php
session_start();
require 'conexao_db.php';

if (!isset($_SESSION['cpf']) || $_SESSION['tipo'] !== 'ADMINISTRADOR')
{
    header('Location: login.php');
    exit;
}

$cpf = $_GET['cpf'] ?? '';

$pdo = conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $sql = 'DELETE FROM Usuario WHERE cpf = :cpf';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':cpf' => $cpf]);

    header('Location: usuarios.php');
    exit;
}

$sql = 'SELECT * FROM Usuario WHERE cpf = :cpf';
$stmt = $pdo->prepare($sql);
$stmt->execute([':cpf' => $cpf]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Remover Usuário</title>
        <link rel="stylesheet" href="css/usuario/usuarios.css">
    </head>

    <body>
        <h2>Remover Usuário</h2>
        <p>Tem certeza que deseja remover o usuário <?= htmlspecialchars($usuario['nome']) ?>?</p>
        <form action="usuario_remover.php?cpf=<?= htmlspecialchars($usuario['cpf']) ?>" method="POST">
            <button type="submit">Remover</button>
            <a href="usuarios.php">Cancelar</a>
        </form>

        <?php include 'rodape.php'; ?>

    </body>

</html>