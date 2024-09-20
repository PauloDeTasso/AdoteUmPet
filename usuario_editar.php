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
    $nome = $_POST['nome'];
    $data_nascimento = $_POST['data_nascimento'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $status = $_POST['status'];
    $senha = $_POST['senha'];
    $fk_Permissao_id = $_POST['fk_Permissao_id'];

    $sql = 'UPDATE Usuario SET nome = :nome, data_nascimento = :data_nascimento, email = :email, telefone = :telefone, status = :status, senha = crypt(:senha, gen_salt(\'bf\')), fk_Permissao_id = :fk_Permissao_id WHERE cpf = :cpf';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome' => $nome,
        ':data_nascimento' => $data_nascimento,
        ':email' => $email,
        ':telefone' => $telefone,
        ':status' => $status,
        ':senha' => $senha,
        ':fk_Permissao_id' => $fk_Permissao_id,
        ':cpf' => $cpf
    ]);

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
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="css/usuario/usuarios.css">
</head>

<body>
    <h2>Editar Usuário</h2>
    <form action="usuario_editar.php?cpf=<?= htmlspecialchars($usuario['cpf']) ?>" method="POST">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
        <label for="data_nascimento">Data de Nascimento:</label>
        <input type="date" id="data_nascimento" name="data_nascimento"
            value="<?= htmlspecialchars($usuario['data_nascimento']) ?>" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($usuario['telefone']) ?>" required>
        <label for="status">Status:</label>
        <input type="text" id="status" name="status" value="<?= htmlspecialchars($usuario['status']) ?>" required>
        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required>
        <label for="fk_Permissao_id">Permissão:</label>
        <select id="fk_Permissao_id" name="fk_Permissao_id" required>
            <option value="1" <?= $usuario['fk_Permissao_id'] == 1 ? 'selected' : '' ?>>Administrador</option>
            <option value="2" <?= $usuario['fk_Permissao_id'] == 2 ? 'selected' : '' ?>>Adotante</option>
        </select>
        <button type="submit">Salvar</button>
    </form>
    <p><a href="usuarios.php">Voltar</a></p>
    
    <?php include 'rodape.php'; ?>

</body>

</html>