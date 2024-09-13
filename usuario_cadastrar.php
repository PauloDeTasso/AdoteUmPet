<?php
session_start();
require 'conexao_db.php';

if (!isset($_SESSION['cpf']) || $_SESSION['tipo'] !== 'ADMINISTRADOR') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = $_POST['cpf'];
    $nome = $_POST['nome'];
    $data_nascimento = $_POST['data_nascimento'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $status = $_POST['status'];
    $senha = $_POST['senha'];
    $fk_Permissao_id = $_POST['fk_Permissao_id'];

    $pdo = conectar();
    $sql = 'INSERT INTO Usuario (cpf, nome, data_nascimento, email, telefone, status, senha, fk_Permissao_id) VALUES (:cpf, :nome, :data_nascimento, :email, :telefone, :status, crypt(:senha, gen_salt(\'bf\')), :fk_Permissao_id)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':cpf' => $cpf,
        ':nome' => $nome,
        ':data_nascimento' => $data_nascimento,
        ':email' => $email,
        ':telefone' => $telefone,
        ':status' => $status,
        ':senha' => $senha,
        ':fk_Permissao_id' => $fk_Permissao_id
    ]);

    header('Location: usuarios.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Usuário</title>
    <link rel="stylesheet" href="css/usuario/usuarios.css">
</head>

<body>
    <h2>Adicionar Usuário</h2>
    <form action="usuario_adicionar.php" method="POST">
        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf" required>
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>
        <label for="data_nascimento">Data de Nascimento:</label>
        <input type="date" id="data_nascimento" name="data_nascimento" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone" required>
        <label for="status">Status:</label>
        <input type="text" id="status" name="status" required>
        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required>
        <label for="fk_Permissao_id">Permissão:</label>
        <select id="fk_Permissao_id" name="fk_Permissao_id" required>
            <option value="1">Administrador</option>
            <option value="2">Adotante</option>
        </select>
        <button type="submit">Adicionar</button>
    </form>
    <p><a href="usuarios.php">Voltar</a></p>
</body>

</html>