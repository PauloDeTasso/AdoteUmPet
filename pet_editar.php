<?php
session_start();
require 'conexao_db.php';

if (!isset($_SESSION['cpf']) || $_SESSION['tipo'] !== 'ADMINISTRADOR') {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? '';

$pdo = conectar();

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

    $sql = 'UPDATE Pet SET brinco = :brinco, nome = :nome, sexo = :sexo, idade = :idade, raca = :raca, pelagem = :pelagem, local_resgate = :local_resgate, data_resgate = :data_resgate, status = :status, informacoes = :informacoes WHERE id = :id';
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
        ':informacoes' => $informacoes,
        ':id' => $id
    ]);

    header('Location: pets.php');
    exit;
}

$sql = 'SELECT * FROM Pet WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$pet = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pet</title>
    <link rel="stylesheet" href="css/pet/pets.css">
</head>

<body>
    <h2>Editar Pet</h2>
    <form action="pet_editar.php?id=<?= htmlspecialchars($pet['id']) ?>" method="POST">
        <label for="brinco">Brinco:</label>
        <input type="text" id="brinco" name="brinco" value="<?= htmlspecialchars($pet['brinco']) ?>" required>
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($pet['nome']) ?>" required>
        <label for="sexo">Sexo:</label>
        <input type="text" id="sexo" name="sexo" value="<?= htmlspecialchars($pet['sexo']) ?>" required>
        <label for="idade">Idade:</label>
        <input type="number" id="idade" name="idade" value="<?= htmlspecialchars($pet['idade']) ?>" required>
        <label for="raca">Raça:</label>
        <input type="text" id="raca" name="raca" value="<?= htmlspecialchars($pet['raca']) ?>">
        <label for="pelagem">Pelagem:</label>
        <input type="text" id="pelagem" name="pelagem" value="<?= htmlspecialchars($pet['pelagem']) ?>">
        <label for="local_resgate">Local do Resgate:</label>
        <input type="text" id="local_resgate" name="local_resgate"
            value="<?= htmlspecialchars($pet['local_resgate']) ?>">
        <label for="data_resgate">Data do Resgate:</label>
        <input type="date" id="data_resgate" name="data_resgate" value="<?= htmlspecialchars($pet['data_resgate']) ?>">
        <label for="status">Status:</label>
        <input type="text" id="status" name="status" value="<?= htmlspecialchars($pet['status']) ?>" required>
        <label for="informacoes">Informações:</label>
        <textarea id="informacoes" name="informacoes"><?= htmlspecialchars($pet['informacoes']) ?></textarea>
        <button type="submit">Salvar</button>
    </form>
    <p><a href="pets.php">Voltar</a></p>
</body>

</html>