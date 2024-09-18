<?php
session_start();
require 'conexao_db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    // Pega os valores do formulário
    $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_STRING);
    $senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);

    try
    {
        // Conecta ao banco de dados
        $pdo = conectar();

        // Consulta para obter o usuário e sua permissão
        $sql = 'SELECT u.*, p.tipo AS permissao_tipo FROM Usuario u 
                LEFT JOIN Permissao p ON u.fk_Permissao_id = p.id
                WHERE u.cpf = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cpf]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica se o usuário foi encontrado
        if ($usuario)
        {
            // Comparação direta da senha (sem hashing ou criptografia)
            if ($senha === $usuario['senha'])
            {
                // Inicia a sessão do usuário
                $_SESSION['cpf'] = $usuario['cpf'];
                $_SESSION['nome'] = $usuario['nome'];
                $_SESSION['telefone'] = $usuario['telefone'];
                $_SESSION['email'] = $usuario['email'];

                // Verifica se o usuário tem endereço
                $sqlEndereco = 'SELECT e.cidade, e.estado FROM Endereco e
                                JOIN Enderecos_Usuarios eu ON e.id = eu.fk_Endereco_id
                                WHERE eu.fk_Usuario_cpf = ?';
                $stmtEndereco = $pdo->prepare($sqlEndereco);
                $stmtEndereco->execute([$cpf]);
                $endereco = $stmtEndereco->fetch(PDO::FETCH_ASSOC);

                // Caso o endereço não seja encontrado
                if ($endereco)
                {
                    $_SESSION['cidade'] = $endereco['cidade'];
                    $_SESSION['estado'] = $endereco['estado'];
                }
                else
                {
                    $_SESSION['cidade'] = null;
                    $_SESSION['estado'] = null;
                }

                // Define o tipo de usuário
                $_SESSION['tipo'] = $usuario['permissao_tipo'] === 'Administrador' ? 'Administrador' : 'Adotante';

                // Redireciona para a página home.php
                header('Location: home.php');
                exit;
            }
            else
            {
                // Senha incorreta
                $erro = "Senha incorreta.";
            }
        }
        else
        {
            // CPF não encontrado
            $erro = "Usuário não encontrado com esse CPF.";
        }
    }
    catch (PDOException $e)
    {
        // Tratamento de erro ao conectar ao banco de dados
        $erro = "Erro ao conectar com o banco de dados: " . $e->getMessage();
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

    <?php include 'cabecalho.php'; ?>

    <div class="container">
        <div class="container2">
            <h2>Entrar</h2>

            <!-- Exibe mensagem de erro se houver -->
            <?php if (isset($erro)): ?>
                <p class="error"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>

            <form method="POST">
                <label for="cpf">CPF:</label>
                <input type="text" name="cpf" id="cpf" required maxlength="11" pattern="\d{11}"
                    title="Digite apenas números, 11 dígitos no total">

                <label for="senha">Senha:</label>
                <input type="password" name="senha" id="senha" required maxlength="255">

                <button type="submit">Entrar</button>
            </form>

            <p><a href="usuario_cadastrar_se.php">Cadastrar-se</a></p>
        </div>
    </div>
</body>

</html>