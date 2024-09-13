<?php
session_start();
require 'conexao_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = $_POST['cpf'];
    $senha = $_POST['senha'];

    $pdo = conectar();
    $sql = 'SELECT * FROM Usuario WHERE cpf = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cpf]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['cpf'] = $usuario['cpf'];
        $_SESSION['tipo'] = $usuario['fk_Permissao_id'] === 1 ? 'ADMINISTRADOR' : 'ADOTANTE';
        header('Location: home.php');
        exit;
    } else {
        $erro = "CPF ou senha inválidos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/login/login.css">
</head>

<body>
    <h2>Login</h2>
    <?php if (isset($erro)): ?>
    <p><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label for="cpf">CPF:</label>
        <input type="text" name="cpf" id="cpf" required><br>
        <label for="senha">Senha:</label>
        <input type="password" name="senha" id="senha" required><br>
        <button type="submit">Entrar</button>
    </form>
    <p><a href="usuario_cadastrar.php">Cadastrar-se</a></p>
</body>

</html>