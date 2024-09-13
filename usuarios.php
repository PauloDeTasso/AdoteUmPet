<?php
session_start();
require 'conexao_db.php';

if (!isset($_SESSION['cpf'])) {
    header('Location: login.php');
    exit;
}

$tipoUsuario = $_SESSION['tipo']; // Pode ser 'ADMINISTRADOR' ou 'ADOTANTE'
$cpf = $_GET['cpf'] ?? '';

$pdo = conectar();

// Verifica se o usuário logado é um administrador ou um adotante
if ($tipoUsuario === 'ADMINISTRADOR') {
    // Administradores podem ver qualquer usuário
    $sql = 'SELECT * FROM Usuario WHERE cpf = :cpf';
} else {
    // Adotantes só podem ver seu próprio cadastro
    if ($cpf !== $_SESSION['cpf']) {
        header('Location: usuarios.php');
        exit;
    }
    $sql = 'SELECT * FROM Usuario WHERE cpf = :cpf';
}

$stmt = $pdo->prepare($sql);
$stmt->execute([':cpf' => $cpf]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = 'SELECT * FROM Imagem_Usuario WHERE fk_Usuario_cpf = :cpf';
$stmt = $pdo->prepare($sql);
$stmt->execute([':cpf' => $cpf]);
$imagens_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Usuário</title>
    <link rel="stylesheet" href="css/usuario/usuarios.css">
</head>

<body>
    <h2>Visualizar Usuário</h2>
    <?php if ($usuario): ?>
    <p><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome']) ?></p>
    <p><strong>Data de Nascimento:</strong> <?= htmlspecialchars($usuario['data_nascimento']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
    <p><strong>Telefone:</strong> <?= htmlspecialchars($usuario['telefone']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($usuario['status']) ?></p>
    <p><strong>Permissão:</strong>
        <?= htmlspecialchars($usuario['fk_Permissao_id'] == 1 ? 'ADMINISTRADOR' : 'ADOTANTE') ?></p>

    <h3>Imagens</h3>
    <?php if ($imagens_usuario): ?>
    <?php foreach ($imagens_usuario as $imagem): ?>
    <img src="<?= htmlspecialchars($imagem['url_imagem']) ?>" alt="Imagem do Usuário" style="width: 150px;">
    <?php endforeach; ?>
    <?php else: ?>
    <p>Nenhuma imagem disponível.</p>
    <?php endif; ?>

    <?php if ($tipoUsuario === 'ADMINISTRADOR'): ?>
    <p><a href="usuario_editar.php?cpf=<?= htmlspecialchars($usuario['cpf']) ?>">Editar</a></p>
    <p><a href="usuario_remover.php?cpf=<?= htmlspecialchars($usuario['cpf']) ?>">Remover</a></p>
    <?php endif; ?>
    <?php else: ?>
    <p>Usuário não encontrado.</p>
    <?php endif; ?>

    <p><a href="usuarios.php">Voltar</a></p>
</body>

</html>