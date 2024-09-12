<?php
session_start();
include 'conexao_db.php';

if (!isset($_SESSION['usuario_logado'])) {
    header('Location: login.php');
    exit();
}

$cpf_usuario = $_SESSION['usuario_logado'];
$stmt = $pdo->prepare("SELECT * FROM Usuario WHERE cpf = :cpf");
$stmt->bindParam(':cpf', $cpf_usuario);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "Usuário não encontrado.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Inicial</title>
    <link rel="stylesheet" href="../css/home.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Bem-vindo, <?php echo htmlspecialchars($usuario['nome']); ?>!</h1>
            <nav>
                <ul>
                    <li><a href="adocoes.php">Adoções</a></li>
                    <li><a href="pets.php">Pets</a></li>
                    <li><a href="usuarios.php">Usuários</a></li>
                    <li><a href="logout.php">Sair</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <h2>Informações do Usuário</h2>
            <p><strong>Nome:</strong> <?php echo htmlspecialchars($usuario['nome']); ?></p>
            <p><strong>CPF:</strong> <?php echo htmlspecialchars($usuario['cpf']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
            <p><strong>Telefone:</strong> <?php echo htmlspecialchars($usuario['telefone']); ?></p>
            <p><strong>Status:</strong> <?php echo $usuario['status'] ? 'Ativo' : 'Inativo'; ?></p>
            <p><strong>Data de Cadastro:</strong> <?php echo htmlspecialchars($usuario['data_cadastro']); ?></p>
        </main>
    </div>
</body>

</html>