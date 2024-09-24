<?php
include_once "start.php";

$cpf = $_GET['cpf'] ?? $_SESSION['cpf']; // Obter CPF do parâmetro GET ou da sessão

$cpfUsuarioLogado = $_SESSION['cpf'];

$pdo = conectar();

// Consulta para obter os dados do usuário a ser removido
$queryUsuarioGet = $pdo->prepare('SELECT nome, fk_Permissao_id FROM Usuario WHERE cpf = :cpf');
$queryUsuarioGet->execute([':cpf' => $cpf]);
$usuarioGet = $queryUsuarioGet->fetch(PDO::FETCH_ASSOC);

// Consulta para obter os dados do usuário logado
$queryUsuarioLogado = $pdo->prepare('SELECT nome, fk_Permissao_id FROM Usuario WHERE cpf = :cpf');
$queryUsuarioLogado->execute([':cpf' => $cpfUsuarioLogado]);
$usuarioLogado = $queryUsuarioLogado->fetch(PDO::FETCH_ASSOC);

// Verifica se o usuário a ser removido foi encontrado
if (!$usuarioGet)
{
    echo 'Usuário não encontrado!';
    exit();
}

// Obtém a imagem do usuário a ser removido
$queryImagem = $pdo->prepare('SELECT url_imagem FROM Imagem_Usuario WHERE fk_Usuario_cpf = :cpf');
$queryImagem->execute([':cpf' => $cpf]);
$imagemUsuario = $queryImagem->fetch(PDO::FETCH_ASSOC);

// Excluir o usuário após a confirmação
if (isset($_POST['confirmarRemocao']) && $_POST['confirmarRemocao'] === 'sim')
{
    try
    {
        $pdo->beginTransaction();

        // Remover a imagem do usuário do servidor
        if ($imagemUsuario && file_exists($imagemUsuario['url_imagem']))
        {
            unlink($imagemUsuario['url_imagem']); // Exclui a imagem do servidor
        }

        // Excluir as relações do usuário no banco de dados (tabela Imagem_Usuario)
        $deleteImagem = $pdo->prepare('DELETE FROM Imagem_Usuario WHERE fk_Usuario_cpf = :cpf');
        $deleteImagem->execute([':cpf' => $cpf]);

        // Excluir o endereço do usuário (tabela Enderecos_Usuarios)
        $deleteEndereco = $pdo->prepare('DELETE FROM Enderecos_Usuarios WHERE fk_Usuario_cpf = :cpf');
        $deleteEndereco->execute([':cpf' => $cpf]);

        // Excluir o próprio usuário (tabela Usuario)
        $deleteUsuario = $pdo->prepare('DELETE FROM Usuario WHERE cpf = :cpf');
        $deleteUsuario->execute([':cpf' => $cpf]);

        $pdo->commit();

        // Redireciona após a exclusão com mensagem de sucesso
        $_SESSION['mensagem'] = 'Usuário removido com sucesso!';

        // Verifica se o campo fk_permissao_id está definido e redireciona
        if (isset($usuarioLogado['fk_permissao_id']))
        {
            // Verifica se o usuário logado é administrador e o nome é o mesmo que o do usuário alvo
            if ($usuarioLogado['fk_permissao_id'] == "1" && $usuarioLogado['nome'] == $usuarioGet['nome'])
            {
                // Redireciona o administrador para a página inicial
                header('Location: logout.php');
                exit(); // Importante: garante que o script pare após o redirecionamento
            }
            else
            {
                // Redireciona o administrador para a página de usuários
                header('Location: usuarios.php');
                exit();
            }
        }
        else
        {
            // Se a permissão não for reconhecida (por exemplo, se for adotante), redireciona para logout
            header('Location: logout.php');
            exit();
        }
    }
    catch (Exception $e)
    {
        $pdo->rollBack();
        echo 'Erro ao remover o usuário: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remover Usuário</title>
    <link rel="stylesheet" href="css/usuario/usuario_remover.css">
</head>

<body>
    <?php include_once 'cabecalho.php'; ?>

    <section class="cabecalho">
        <h3>Remover Conta</h3>
    </section>

    <section class="secaoPrincipal">
        <form id="formRemocao" action="usuario_remover.php?cpf=<?= htmlspecialchars($cpf, ENT_QUOTES, 'UTF-8'); ?>"
            method="POST">

            <?php
            // Exibir a mensagem apropriada com base nas permissões e nomes dos usuários

            if ($usuarioLogado['fk_permissao_id'] == '1' && $usuarioGet['fk_permissao_id'] == "2" && $usuarioLogado['nome'] != $usuarioGet['nome'])
            {
                // O Vigilante Sanitário está prestes a excluir a conta de um Adotante
            ?>
                <p>Olá <?= htmlspecialchars($usuarioLogado['nome'], ENT_QUOTES, 'UTF-8'); ?>, você está prestes a
                    excluir a conta do Adotante
                    <strong><?= htmlspecialchars($usuarioGet['nome'], ENT_QUOTES, 'UTF-8'); ?></strong>. Se continuar, a
                    conta será excluída permanentemente.
                </p>
            <?php
            }
            elseif ($usuarioLogado['fk_permissao_id'] == '1' && $usuarioGet['fk_permissao_id'] == "1" && $usuarioLogado['nome'] != $usuarioGet['nome'])
            {
                // O Vigilante Sanitário está prestes a excluir a conta de outro Vigilante Sanitário
            ?>
                <p>Olá <?= htmlspecialchars($usuarioLogado['nome'], ENT_QUOTES, 'UTF-8'); ?>, você está prestes a
                    excluir a conta de Vigilante Sanitário de
                    <strong><?= htmlspecialchars($usuarioGet['nome'], ENT_QUOTES, 'UTF-8'); ?></strong>. Se continuar, a
                    conta será excluída permanentemente.
                </p>
            <?php
            }
            elseif ($usuarioLogado['fk_permissao_id'] == '1' && $usuarioGet['fk_permissao_id'] == "1" && $usuarioLogado['nome'] == $usuarioGet['nome'])
            {
                // O Vigilante Sanitário está prestes a excluir a própria conta
            ?>
                <p>Olá <?= htmlspecialchars($usuarioLogado['nome'], ENT_QUOTES, 'UTF-8'); ?>, se você continuar, sua
                    conta de Vigilante Sanitário será excluída permanentemente.</p>
            <?php
            }
            elseif ($usuarioLogado['fk_permissao_id'] == '2' && $usuarioGet['fk_permissao_id'] == "2" && $usuarioLogado['nome'] == $usuarioGet['nome'])
            {
                // O Adotante está prestes a excluir a própria conta
            ?>
                <p>Olá <?= htmlspecialchars($usuarioLogado['nome'], ENT_QUOTES, 'UTF-8'); ?>, se você continuar, sua
                    conta de Adotante será excluída permanentemente.</p>
            <?php
            }
            else
            {
            ?>
                <p>A permissão do usuário não é reconhecida.</p>
            <?php
            }
            ?>


            <input type="hidden" name="confirmarRemocao" value="sim">
            <button type="button" id="botaoRemover">Remover Conta</button>
        </form>
    </section>

    <script>
        // Função para exibir a confirmação e enviar o formulário
        function confirmarRemocao() {
            const confirmacao = confirm("Tem certeza de que deseja remover sua conta? Esta ação é irreversível.");
            if (confirmacao) {
                document.getElementById('formRemocao').submit();
            }
        }

        // Adiciona o evento ao botão
        document.getElementById('botaoRemover').addEventListener('click', confirmarRemocao);
    </script>
</body>

</html>