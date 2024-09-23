<?php
session_start();
require_once 'conexao_db.php';
require_once 'auth.php';

// Verifica se o usuário está logado
verificarSessao();

$pdo = conectar();

// Obtém o CPF do usuário logado a partir da sessão
$cpfUsuario = $_SESSION['cpf'];

// Consulta para obter os dados do usuário logado
$queryUsuario = $pdo->prepare('SELECT nome, fk_Permissao_id FROM Usuario WHERE cpf = :cpf');
$queryUsuario->execute([':cpf' => $cpfUsuario]);
$usuarioLogado = $queryUsuario->fetch(PDO::FETCH_ASSOC);

// Verifica se o usuário foi encontrado
if (!$usuarioLogado)
{
    echo 'Usuário não encontrado!';
    exit();
}

// Obtém a imagem do usuário a ser removido
$queryImagem = $pdo->prepare('SELECT url_imagem FROM Imagem_Usuario WHERE fk_Usuario_cpf = :cpf');
$queryImagem->execute([':cpf' => $cpfUsuario]);
$imagemUsuario = $queryImagem->fetch(PDO::FETCH_ASSOC);

// Excluir o usuário após a confirmação
if (isset($_POST['confirmarRemocao']) && $_POST['confirmarRemocao'] === 'sim')
{
    try
    {
        $pdo->beginTransaction();

        // Remover a imagem do usuário do banco de dados
        if ($imagemUsuario && file_exists($imagemUsuario['url_imagem']))
        {
            unlink($imagemUsuario['url_imagem']); // Exclui a imagem do servidor
        }

        // Excluir as relações do usuário no banco de dados (tabela Imagem_Usuario)
        $deleteImagem = $pdo->prepare('DELETE FROM Imagem_Usuario WHERE fk_Usuario_cpf = :cpf');
        $deleteImagem->execute([':cpf' => $cpfUsuario]);

        // Excluir o endereço do usuário (tabela Enderecos_Usuarios)
        $deleteEndereco = $pdo->prepare('DELETE FROM Enderecos_Usuarios WHERE fk_Usuario_cpf = :cpf');
        $deleteEndereco->execute([':cpf' => $cpfUsuario]);

        // Excluir o próprio usuário (tabela Usuario)
        $deleteUsuario = $pdo->prepare('DELETE FROM Usuario WHERE cpf = :cpf');
        $deleteUsuario->execute([':cpf' => $cpfUsuario]);

        $pdo->commit();

        // Redireciona após a exclusão com mensagem de sucesso
        $_SESSION['mensagem'] = 'Usuário removido com sucesso!';

        // Verifica se o usuário é administrador ou adotante
        if ($usuarioLogado['fk_Permissao_id'] == 1)
        {
            header('Location: home.php'); // Administrador
        }
        else
        {
            header('Location: logout.php'); // Adotante
        }

        exit();
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
            <form id="formRemocao" action="usuario_remover.php" method="POST">
                <p>Olá <?= htmlspecialchars($usuarioLogado['nome'], ENT_QUOTES, 'UTF-8'); ?>, se você continuar, sua
                    conta será excluída permanentemente.</p>
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