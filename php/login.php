<?php
session_start();
include 'conexao_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cpf = $_POST['cpf'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT cpf, senha FROM Usuario WHERE cpf = :cpf");
    $stmt->bindParam(':cpf', $cpf);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_logado'] = $cpf;
        header('Location: home.php');
        exit();
    } else {
        $erro = "CPF ou senha incorretos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tela de Login</title>
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>
    <div class="container">
        <h2>Login</h2>
        <form id="loginForm" method="POST" action="login.php">
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" required>
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
            <button type="submit">Entrar</button>
        </form>

        <?php if (isset($erro)): ?>
        <p class="error"><?php echo $erro; ?></p>
        <?php endif; ?>

        <p class="register-link">Não tem uma conta? <a href="cadastro_usuario.php">Cadastre-se aqui</a></p>
    </div>
</body>

</html>