<?php
session_start();
include 'conexao_db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT cpf, senha FROM Usuario WHERE email = :email AND status = 'ATIVA'");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha']))
    {
        $_SESSION['cpf'] = $usuario['cpf'];
        header("Location: home.php");
        exit;
    }
    else
    {
        $erro = "Credenciais inválidas";
    }
}
?>

<form method="POST" action="login.php">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="senha" placeholder="Senha" required>
    <button type="submit">Login</button>
    <?php if (isset($erro)) echo $erro; ?>
</form>