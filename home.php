<?php
session_start();
require 'conexao_db.php';

if (!isset($_SESSION['cpf'])) {
    header('Location: login.php');
    exit;
}

$tipo = $_SESSION['tipo'];
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="css/home.css">
</head>

<body>
    <h1>Bem-vindo, <?= htmlspecialchars($_SESSION['cpf']) ?></h1>
    <ul>
        <li><a href="pets.php">Ver Pets</a></li>
        <li><a href="usuarios.php">Ver Vigilantes</a></li>
        <?php if ($tipo === 'ADMINISTRADOR'): ?>
        <li><a href="adocoes.php">Ver Adoções</a></li>
        <li><a href="usuarios.php">Ver Usuários</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Sair</a></li>
    </ul>
</body>

</html>