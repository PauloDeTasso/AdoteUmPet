<?php
// Inclua o arquivo de conexão e utilidades
include 'conexao_db.php';
include 'utilidades.php';

// Inicie a sessão para verificar o usuário logado
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['cpf'])) {
    header('Location: login.php');
    exit();
}

// Obtenha a instância do PDO
$pdo = conectar();

// Função para obter todos os usuários ativos
function getUsuariosAtivos($pdo)
{
    $sql = '
        SELECT u.cpf, u.nome, COALESCE(i.url_imagem, \'imagens/usuarios/default.jpg\') AS imagem_url
        FROM Usuario u
        LEFT JOIN Imagem_Usuario i ON u.cpf = i.fk_Usuario_cpf
        WHERE u.status = :status
    ';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':status' => 'ATIVO']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter pets disponíveis
function getPetsDisponiveis($pdo)
{
    $sql = '
        SELECT p.brinco, p.nome, COALESCE(i.url_imagem, \'imagens/pets/default.jpg\') AS imagem_url
        FROM Pet p
        LEFT JOIN Imagem_Pet i ON p.brinco = i.fk_Pet_brinco
        WHERE p.status = :status
    ';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':status' => 'ADOTÁVEL']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtém o CPF do adotante da URL, se estiver disponível
$cpfAdotante = isset($_GET['adotante']) ? filter_var(trim($_GET['adotante']), FILTER_SANITIZE_STRING) : '';

// Obtém os dados para exibição
$usuariosAtivos = getUsuariosAtivos($pdo);
$petsDisponiveis = getPetsDisponiveis($pdo);

// Define o pet pré-selecionado, se existir
$petSelecionado = isset($_GET['pet']) ? filter_var(trim($_GET['pet']), FILTER_SANITIZE_NUMBER_INT) : '';

// Geração do token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Inicializa a variável mensagem para evitar avisos
$mensagem = '';
$tipoMensagem = 'info'; // Default message type

// Processa o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica o token CSRF
    if (isset($_POST['csrf_token']) && hash_equals($csrf_token, $_POST['csrf_token'])) {
        // Verifica se os índices estão definidos
        $usuario = isset($_POST['usuario']) ? filter_var(trim($_POST['usuario']), FILTER_SANITIZE_STRING) : '';
        $pet = isset($_POST['pet']) ? filter_var(trim($_POST['pet']), FILTER_SANITIZE_NUMBER_INT) : '';
        $observacoes = isset($_POST['observacoes']) ? filter_var(trim($_POST['observacoes']), FILTER_SANITIZE_STRING) : '';

        // Valida os dados
        if ($usuario && $pet) {
            // Insere a adoção no banco de dados
            $sql = '
                INSERT INTO Adocao (fk_Usuario_cpf, fk_Pet_brinco, observacoes)
                VALUES (:usuario, :pet, :observacoes)
            ';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':usuario' => $usuario,
                ':pet' => $pet,
                ':observacoes' => $observacoes
            ]);

            // Atualiza o status do pet para 'ADOTADO'
            $sql = 'UPDATE Pet SET status = :status WHERE brinco = :pet';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':status' => 'ADOTADO',
                ':pet' => $pet
            ]);

            // Mensagem de sucesso
            $mensagem = 'Adoção cadastrada com sucesso!';
            $tipoMensagem = 'sucesso';
        } else {
            // Mensagem de erro
            $mensagem = 'Por favor, selecione um adotante e um pet.';
            $tipoMensagem = 'erro';
        }
    } else {
        // Mensagem de erro
        $mensagem = 'Token CSRF inválido.';
        $tipoMensagem = 'erro';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Adoção</title>
    <link rel="stylesheet" href="css/adocao/adocao_cadastrar.css">
    <script src="utilidades.php"></script>
    <script>
    function atualizarImagem() {
        var usuario = document.getElementById('usuario').value;
        var pet = document.getElementById('pet').value;
        var imagemUsuario = document.getElementById('imagemUsuario');
        var imagemPet = document.getElementById('imagemPet');

        // Atualiza a imagem do adotante
        if (usuario) {
            var imagemUrlUsuario = doabel.querySelector('option[value="' + usuario + '"]').getAttribute(
                'data-imagem');
            imagemUsuario.src = imagemUrlUsuario ? imagemUrlUsuario : 'imagens/usuarios/default.jpg';
            imagemUsuario.style.display = 'block';
        } else {
            imagemUsuario.src = 'imagens/usuarios/default.jpg';
            imagemUsuario.style.display = 'block';
        }

        // Atualiza a imagem do pet
        if (pet) {
            var imagemUrlPet = document.querySelector('option[value="' + pet + '"]').getAttribute('data-imagem');
            imagemPet.src = imagemUrlPet ? imagemUrlPet : 'imagens/pets/default.jpg';
            imagemPet.style.display = 'block';
        } else {
            imagemPet.src = 'imagens/pets/default.jpg';
            imagemPet.style.display = 'block';
        }
    }

    function validarFormulario() {
        var usuario = document.getElementById('usuario').value;
        var pet = document.getElementById('pet').value;
        var observacoes = document.getElementById('observacoes').value;
        var mensagem = '';

        if (!usuario) {
            mensagem += 'Por favor, selecione um adotante.\n';
        }
        if (!pet) {
            mensagem += 'Por favor, selecione um pet.\n';
        }
        if (observacoes.length > 255) {
            mensagem += 'Observações não podem exceder 255 caracteres.\n';
        }

        if (mensagem) {
            exibirMensagem('erro', mensagem, '');
            return false;
        }
        return true;
    }

    window.onload = function() {
        atualizarImagem();

        <?php if ($mensagem): ?>
        exibirMensagem('<?= $tipoMensagem ?>', '<?= htmlspecialchars($mensagem) ?>', '');
        <?php endif; ?>
    };
    </script>
</head>

<body>

    <?php include 'cabecalho.php'; ?>

    <section class="cabecalho">
        <h3>Cadastrar uma Adoção</h3>
    </section>

    <div class="container">
        <form action="" method="post" onsubmit="return validarFormulario()">
            <div class="form-group">
                <label for="usuario">Adotante:</label>
                <div class="form-row">
                    <select name="usuario" id="usuario" onchange="atualizarImagem()"
                        <?= $cpfAdotante ? 'disabled' : '' ?> required>
                        <option value="">Selecione um adotante</option>
                        <?php foreach ($usuariosAtivos as $usuario): ?>
                        <option value="<?= htmlspecialchars($usuario['cpf']) ?>"
                            <?= $cpfAdotante === $usuario['cpf'] ? 'selected' : (isset($_POST['usuario']) && $_POST['usuario'] === $usuario['cpf'] ? 'selected' : '') ?>
                            data-imagem="<?= htmlspecialchars($usuario['imagem_url']) ?>">
                            <?= htmlspecialchars($usuario['nome']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="image-preview">
                        <img id="imagemUsuario" src="imagens/usuarios/default.jpg" alt="Imagem do Adotante"
                            style="display:none;">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="pet">Pet:</label>
                <div class="form-row">
                    <select name="pet" id="pet" onchange="atualizarImagem()" required>
                        <option value="">Selecione um pet</option>
                        <?php foreach ($petsDisponiveis as $pet): ?>
                        <option value="<?= htmlspecialchars($pet['brinco']) ?>"
                            <?= $petSelecionado === $pet['brinco'] ? 'selected' : (isset($_POST['pet']) && $_POST['pet'] === $pet['brinco'] ? 'selected' : '') ?>
                            data-imagem="<?= htmlspecialchars($pet['imagem_url']) ?>">
                            <?= htmlspecialchars($pet['nome']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="image-preview">
                        <img id="imagemPet" src="imagens/pets/default.jpg" alt="Imagem do Pet" style="display:none;">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="observacoes">Observações:</label>
                <textarea name="observacoes" id="observacoes" maxlength="255"></textarea>
            </div>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <button type="submit">Cadastrar Adoção</button>
        </form>
    </div>

    <?php include 'rodape.php'; ?>

</body>

</html>