<?php
session_start();
require_once 'conexao_db.php';
require_once 'auth.php';
verificarSessao();

// Conecta ao banco de dados
$pdo = conectar();

// Acessível para Administrador e Adotante
if ($_SESSION['tipo'] !== 'Administrador' && $_SESSION['tipo'] !== 'Adotante') {
    echo "Acesso negado.";
    exit();
}

// Obter nome e imagem do usuário com base no CPF
$cpfUsuario = $_SESSION['cpf'];
$sqlUsuario = "SELECT nome, (SELECT url_imagem FROM Imagem_Usuario WHERE fk_Usuario_cpf = :cpf LIMIT 1) AS imagem FROM Usuario WHERE cpf = :cpf";
$stmt = $pdo->prepare($sqlUsuario);
$stmt->execute([':cpf' => $cpfUsuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "Usuário não encontrado.";
    exit();
}

// Inicializa variáveis de mensagem
$mensagemSucesso = '';
$mensagemErro = '';

// Atualização de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['senha_atual'], $_POST['nova_senha'], $_POST['confirmar_senha']) && !empty($_POST['nova_senha'])) {
        $senhaAtual = filter_input(INPUT_POST, 'senha_atual', FILTER_SANITIZE_STRING);
        $novaSenha = filter_input(INPUT_POST, 'nova_senha', FILTER_SANITIZE_STRING);
        $confirmarSenha = filter_input(INPUT_POST, 'confirmar_senha', FILTER_SANITIZE_STRING);

        // Verificar se a nova senha e a confirmação coincidem
        if ($novaSenha !== $confirmarSenha) {
            $_SESSION['mensagemErro'] = "A nova senha e a confirmação não coincidem.";
        } else {
            // Verificar se a senha atual está correta
            $sql = "SELECT senha FROM Usuario WHERE cpf = :cpf";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':cpf' => $cpfUsuario]);
            $senhaAtualBD = $stmt->fetchColumn();

            if ($senhaAtual === $senhaAtualBD) {
                // Atualizar senha no banco de dados
                $sql = "UPDATE Usuario SET senha = :nova_senha WHERE cpf = :cpf";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':nova_senha' => $novaSenha, ':cpf' => $cpfUsuario]);

                $_SESSION['mensagemSucesso'] = "Senha atualizada com sucesso!";
            } else {
                $_SESSION['mensagemErro'] = "A senha atual está incorreta.";
            }
        }
    } else {
        $_SESSION['mensagemErro'] = "Por favor, preencha todos os campos.";
    }
    header("Location: configuracoes.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações</title>
    <link rel="stylesheet" href="css/configuracoes.css">
</head>

<body>

    <?php include 'cabecalho.php'; ?>

    <section class="cabecalho">
        <h3>Configurações</h3>
    </section>

    <div class="container">
        <!-- Exibe nome e imagem do usuário -->
        <div class="usuario-info">
            <h3><?= htmlspecialchars($usuario['nome']) ?></h3>
            <img src="<?= htmlspecialchars($usuario['imagem']) ?>" alt="Foto de perfil" class="usuario-imagem">
        </div>

        <!-- Menu de Configurações -->
        <div class="menu-configuracoes">
            <ul>
                <li><a href="#" id="alterar-senha-link">Configuração de Senha</a></li>
                <!-- Adicione mais opções de configuração aqui -->
            </ul>
        </div>

        <!-- Submenu - Alterar Senha -->
        <div id="alterar-senha-form" style="display: none;">
            <h3>Criar Nova Senha</h3>

            <form method="POST" id="form-senha" class="form-configuracoes">
                <label for="senha_atual">Senha atual:</label>
                <input type="password" name="senha_atual" id="senha_atual" placeholder="Digite sua senha atual"
                    required>

                <label for="nova_senha">Nova senha:</label>
                <input type="password" name="nova_senha" id="nova_senha" placeholder="Digite a nova senha" required>

                <label for="confirmar_senha">Confirmar nova senha:</label>
                <input type="password" name="confirmar_senha" id="confirmar_senha" placeholder="Confirme a nova senha"
                    required>

                <button type="submit">Atualizar Senha</button>
            </form>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <script>
    // Exibe mensagens de feedback (toast)
    function exibirToast(mensagem, tipo) {
        const toastContainer = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.classList.add('toast', tipo);
        toast.textContent = mensagem;
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Validar o formulário antes de enviar
    document.getElementById('form-senha').addEventListener('submit', function(event) {
        const novaSenha = document.getElementById('nova_senha').value;
        const confirmarSenha = document.getElementById('confirmar_senha').value;

        if (novaSenha !== confirmarSenha) {
            event.preventDefault(); // Evita o envio
            exibirToast('As senhas não coincidem.', 'erro');
        }
    });

    // Exibir mensagens de feedback do servidor
    <?php if (isset($_SESSION['mensagemSucesso'])): ?>
    exibirToast('<?= $_SESSION['mensagemSucesso'] ?>', 'sucesso');
    <?php unset($_SESSION['mensagemSucesso']); ?>
    <?php elseif (isset($_SESSION['mensagemErro'])): ?>
    exibirToast('<?= $_SESSION['mensagemErro'] ?>', 'erro');
    <?php unset($_SESSION['mensagemErro']); ?>
    <?php endif; ?>

    // Alterna a visibilidade do formulário de alterar senha
    document.getElementById('alterar-senha-link').addEventListener('click', function(event) {
        event.preventDefault();

        const alterarSenhaForm = document.getElementById('alterar-senha-form');
        const isFormVisible = alterarSenhaForm.style.display === 'block';

        alterarSenhaForm.style.display = isFormVisible ? 'none' : 'block';

        // Atualizar os itens do menu, removendo a classe active dos outros itens
        const links = document.querySelectorAll('.menu-configuracoes a');
        links.forEach(link => link.classList.remove('active'));

        if (!isFormVisible) {
            this.classList.add('active');
        }
    });
    </script>

    <?php include 'rodape.php'; ?>

</body>

</html>