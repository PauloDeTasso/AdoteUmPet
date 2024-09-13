<?php
session_start();
require 'conexao_db.php';

if (!isset($_SESSION['cpf']) || $_SESSION['tipo'] !== 'ADMINISTRADOR') {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_cpf = $_POST['usuario_cpf'];
    $pet_id = $_POST['pet_id'];
    $observacoes = $_POST['observacoes'];

    $pdo = conectar();
    $sql = 'UPDATE Adocao SET fk_Usuario_cpf = ?, fk_Pet_id = ?, observacoes = ? WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_cpf, $pet_id, $observacoes, $id]);

    header('Location: adocoes.php');
    exit;
}

$pdo = conectar();
$sql = 'SELECT * FROM Adocao WHERE id = ?';
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$adocao = $stmt->fetch(PDO::FETCH_ASSOC);

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
    <title>Editar Adoção</title>
    <link rel="stylesheet" href="css/adocao/adocao_editar.css">
</head>

<body>
    <h2>Editar Adoção</h2>
    <form method="POST">
        <label for="usuario_cpf">Usuário:</label>
        <select name="usuario_cpf" id="usuario_cpf" required>
            <?php foreach ($usuarios as $usuario): ?>
            <option value="<?= htmlspecialchars($usuario['cpf']) ?>"
                <?= $adocao['fk_Usuario_cpf'] === $usuario['cpf'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($usuario['nome']) ?></option>
            <?php endforeach; ?>
        </select><br>
        <label for="pet_id">Pet:</label>
        <select name="pet_id" id="pet_id" required>
            <?php foreach ($pets as $pet): ?>
            <option value="<?= htmlspecialchars($pet['id']) ?>"
                <?= $adocao['fk_Pet_id'] === $pet['id'] ? 'selected' : '' ?>><?= htmlspecialchars($pet['nome']) ?>
            </option>
            <?php endforeach; ?>
        </select><br>
        <label for="observacoes">Observações:</label>
        <textarea name="observacoes" id="observacoes"><?= htmlspecialchars($adocao['observacoes']) ?></textarea><br>
        <button type="submit">Salvar</button>
    </form>
    <p><a href="adocoes.php">Voltar</a></p>
</body>

</html>