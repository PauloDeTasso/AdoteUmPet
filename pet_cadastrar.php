<?php
session_start();
require 'conexao_db.php';

if (!isset($_SESSION['cpf']) || $_SESSION['tipo'] !== 'ADMINISTRADOR') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brinco = $_POST['brinco'];
    $nome = $_POST['nome'];
    $sexo = $_POST['sexo'];
    $idade = $_POST['idade'];
    $raca = $_POST['raca'];
    $pelagem = $_POST['pelagem'];
    $local_resgate = $_POST['local_resgate'];
    $data_resgate = $_POST['data_resgate'];
    $status = $_POST['status'];
    $informacoes = $_POST['informacoes'];

    $pdo = conectar();
    $sql = 'INSERT INTO Pet (brinco, nome, sexo, idade, raca, pelagem, local_resgate, data_resgate, status, informacoes) VALUES (:brinco, :nome, :sexo, :idade, :raca, :pelagem, :local_resgate, :data_resgate, :status, :informacoes)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':brinco' => $brinco,
        ':nome' => $nome,
        ':sexo' => $sexo,
        ':idade' => $idade,
        ':raca' => $raca,
        ':pelagem' => $pelagem,
        ':local_resgate' => $local_resgate,
        ':data_resgate' => $data_resgate,
        ':status' => $status,
        ':informacoes' => $informacoes
    ]);

    header('Location: pets.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Pet</title>
    <link rel="stylesheet" href="css/pet/pets.css">
</head>

<body>
    <h2>Adicionar Pet</h2>
    <form action="pet_adicionar.php" method="POST">
        <label for="brinco">Brinco:</label>
        <input type="text" id="brinco" name="brinco" required>
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>
        <label for="sexo">Sexo:</label>
        <input type="text" id="sexo" name="sexo" required>
        <label for="idade">Idade:</label>
        <input type="number" id="idade" name="idade" required>
        <label for="raca">Raça:</label>
        <input type="text" id="raca" name="raca">
        <label for="pelagem">Pelagem:</label>
        <input type="text" id="pelagem" name="pelagem">
        <label for="local_resgate">Local do Resgate:</label>
        <input type="text" id="local_resgate" name="local_resgate">
        <label for="data_resgate">Data do Resgate:</label>
        <input type="date" id="data_resgate" name="data_resgate">
        <label for="status">Status:</label>
        <input type="text" id="status" name="status" required>
        <label for="informacoes">Informações:</label>
        <textarea id="informacoes" name="informacoes"></textarea>
        <button type="submit">Adicionar</button>
    </form>
    <p><a href="pets.php">Voltar</a></p>
</body>

</html>