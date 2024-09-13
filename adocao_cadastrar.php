<?php
session_start();
require 'conexao_db.php';

if (!isset($_SESSION['cpf']) || $_SESSION['tipo'] !== 'ADMINISTRADOR') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_cpf = $_POST['usuario_cpf'];
    $pet_id = $_POST['pet_id'];
    $observacoes = $_POST['observacoes'];

    $pdo = conectar();
    $sql = 'INSERT INTO Adocao (fk_Usuario_cpf, fk_Pet_id, observacoes) VALUES (?, ?, ?)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_cpf, $pet_id, $observacoes]);

    header('Location: adocoes.php');
    exit;
}

$pdo = conectar();
$sql = 'SELECT * FROM Pet WHERE status = \'ADOTAVEL\'';
$stmt = $pdo->query($sql);
$pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = 'SELECT * FROM Usuario';
$stmt = $pdo->query($sql);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Adoção</title>
    <link rel="stylesheet" href="css/adocao/adocao_cadastrar.css">
</head>

<body>
    <h2>Cadastrar Nova Adoção</h2>
    <form method="POST">
        <label for="usuario_cpf">Usuário:</label>
        <select name="usuario_cpf" id="usuario_cpf" required>
            <?php foreach ($usuarios as $usuario): ?>
            <option value="<?= htmlspecialchars($usuario['cpf']) ?>"><?= htmlspecialchars($usuario['nome']) ?></option>
            <?php endforeach; ?>
        </select><br>
        <label for="pet_id">Pet:</label>
        <select name="pet_id" id="pet_id" required>
            <?php foreach ($pets as $pet): ?>
            <option value="<?= htmlspecialchars($pet['id']) ?>"><?= htmlspecialchars($pet['nome']) ?></option>
            <?php endforeach; ?>
        </select><br>
        <label for="observacoes">Observações:</label>
        <textarea name="observacoes" id="observacoes"></textarea><br>
        <button type="submit">Cadastrar</button>
    </form>
    <p><a href="home.php">Voltar</a></p>
</body>

</html>