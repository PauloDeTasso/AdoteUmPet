<?php
session_start();
include 'conexao_db.php';

if (isset($_SESSION['usuario_logado'])) {
    header('Location: home.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $cpf = $_POST['cpf'];
    $nome = $_POST['nome'];
    $data_nascimento = $_POST['data_nascimento'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $senha = $_POST['senha'];

    if (empty($cpf) || empty($nome) || empty($data_nascimento) || empty($email) || empty($senha)) {
        $erro = "Todos os campos são obrigatórios.";
    } else {

        $stmt = $pdo->prepare("SELECT cpf FROM Usuario WHERE cpf = :cpf");
        $stmt->bindParam(':cpf', $cpf);
        $stmt->execute();
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $erro = "CPF já cadastrado.";
        } else {

            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO Usuario (cpf, nome, data_nascimento, email, telefone, senha) VALUES (:cpf, :nome, :data_nascimento, :email, :telefone, :senha)");
            $stmt->bindParam(':cpf', $cpf);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':data_nascimento', $data_nascimento);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':senha', $senha_hash);
            if ($stmt->execute()) {
                header('Location: login.php');
                exit();
            } else {
                $erro = "Erro ao cadastrar o usuário. Tente novamente.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário</title>
    <link rel="stylesheet" href="../css/cadastro_usuario.css">
</head>

<body>
    <div class="container">
        <h2>Cadastro de Usuário</h2>
        <form id="cadastroForm" method="POST" action="cadastro_usuario.php">
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" required>
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>
            <label for="data_nascimento">Data de Nascimento:</label>
            <input type="date" id="data_nascimento" name="data_nascimento" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="telefone">Telefone:</label>
            <input type="text" id="telefone" name="telefone">
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
            <button type="submit">Cadastrar</button>
        </form>

        <?php if (isset($erro)): ?>
        <p style="color: red;"><?php echo $erro; ?></p>
        <?php endif; ?>
    </div>
</body>

</html>