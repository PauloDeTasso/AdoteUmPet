<?php
// Inclua o arquivo de conexão e utilidades
include 'conexao_db.php';
include 'utilidades.php';

// Obtenha a instância do PDO
$pdo = conectar();

// Função para obter todos os usuários ativos
function getUsuariosAtivos($pdo)
{
    $sql = '
        SELECT u.cpf, u.nome, i.url_imagem AS imagem_url
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
        SELECT p.brinco, p.nome, i.url_imagem AS imagem_url
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
session_start();
if (empty($_SESSION['csrf_token']))
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Inicializa a variável mensagem para evitar avisos
$mensagem = '';
$tipoMensagem = 'info'; // Default message type

// Processa o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    // Verifica o token CSRF
    if (isset($_POST['csrf_token']) && hash_equals($csrf_token, $_POST['csrf_token']))
    {
        // Verifica se os índices estão definidos
        $usuario = isset($_POST['usuario']) ? filter_var(trim($_POST['usuario']), FILTER_SANITIZE_STRING) : '';
        $pet = isset($_POST['pet']) ? filter_var(trim($_POST['pet']), FILTER_SANITIZE_NUMBER_INT) : '';
        $observacoes = isset($_POST['observacoes']) ? filter_var(trim($_POST['observacoes']), FILTER_SANITIZE_STRING) : '';

        // Valida os dados
        if ($usuario && $pet)
        {
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

            // Mensagem de sucesso
            $mensagem = 'Adoção cadastrada com sucesso!';
            $tipoMensagem = 'sucesso';
        }
        else
        {
            // Mensagem de erro
            $mensagem = 'Por favor, selecione um adotante e um pet.';
            $tipoMensagem = 'erro';
        }
    }
    else
    {
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
                var imagemUrlUsuario = document.querySelector('option[value="' + usuario + '"]').getAttribute(
                    'data-imagem');
                imagemUsuario.src = imagemUrlUsuario ? imagemUrlUsuario : 'imagens/usuarios/default.jpg';
                imagemUsuario.style.display = 'block';
            } else {
                imagemUsuario.style.display = 'none';
            }

            // Atualiza a imagem do pet
            if (pet) {
                var imagemUrlPet = document.querySelector('option[value="' + pet + '"]').getAttribute('data-imagem');
                imagemPet.src = imagemUrlPet ? imagemUrlPet : 'imagens/pets/default.jpeg';
                imagemPet.style.display = 'block';
            } else {
                imagemPet.style.display = 'none';
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

    <?php include "cabecalho.php"; ?>

    <div class="container">
        <h1>Cadastro de Adoção</h1>
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
                        <img id="imagemUsuario" src="" alt="Imagem do Adotante" style="display:none;">
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
                                <?= ($petSelecionado && $petSelecionado == $pet['brinco']) ? 'selected' : (isset($_POST['pet']) && $_POST['pet'] === $pet['brinco'] ? 'selected' : '') ?>
                                data-imagem="<?= htmlspecialchars($pet['imagem_url']) ?>">
                                <?= htmlspecialchars($pet['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="image-preview">
                        <img id="imagemPet" src="" alt="Imagem do Pet" style="display:none;">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="observacoes">Observações:</label>
                <textarea name="observacoes" id="observacoes"
                    rows="4"><?= isset($_POST['observacoes']) ? htmlspecialchars($_POST['observacoes']) : '' ?></textarea>
            </div>
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <button type="submit">Cadastrar Adoção</button>
        </form>
    </div>

    <?php include "rodape.php"; ?>
</body>

</html>


------------------------------------

backup

<?php
// Inclua o arquivo de conexão
include 'conexao_db.php';

// Inicie a sessão para verificar o usuário logado
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['cpf']))
{
    header('Location: ../login/login.php');
    exit();
}

// Obtenha a instância do PDO
$pdo = conectar();

// Função para obter um adotante específico pelo CPF
function getAdotante($pdo, $cpf)
{
    $sql = 'SELECT u.cpf, u.nome, i.url_imagem AS imagem_url
            FROM Usuario u
            LEFT JOIN Imagem_Usuario i ON u.cpf = i.fk_Usuario_cpf
            WHERE u.cpf = :cpf AND u.status = :status';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':cpf' => $cpf,
        ':status' => 'ATIVO'
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para obter um pet específico pelo brinco
function getPet($pdo, $brinco)
{
    $sql = 'SELECT p.brinco, p.nome, i.url_imagem AS imagem_url
            FROM Pet p
            LEFT JOIN Imagem_Pet i ON p.brinco = i.fk_Pet_brinco
            WHERE p.brinco = :brinco AND p.status = :status';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':brinco' => $brinco,
        ':status' => 'ADOTÁVEL'
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Obtém os parâmetros da URI (pet e adotante)
$cpfUsuario = filter_input(INPUT_GET, 'adotante', FILTER_SANITIZE_STRING);
$brincoPet = filter_input(INPUT_GET, 'pet', FILTER_SANITIZE_NUMBER_INT);

// Verifica se ambos os parâmetros estão presentes
if (!$cpfUsuario || !$brincoPet)
{
    echo "Erro: Parâmetros inválidos.";
    exit();
}

// Obtém os dados do adotante e do pet
$adotante = getAdotante($pdo, $cpfUsuario);
$pet = getPet($pdo, $brincoPet);

// Verifica se o adotante e o pet existem no banco de dados
if (!$adotante || !$pet)
{
    echo "Erro: Adotante ou Pet não encontrado.";
    exit();
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $observacoes = filter_var(trim($_POST['observacoes']), FILTER_SANITIZE_STRING);

    // Validação básica dos dados
    if (strlen($observacoes) > 255)
    {
        $mensagem = 'Observações não podem exceder 255 caracteres.';
    }
    else
    {
        try
        {
            // Inicia a transação
            $pdo->beginTransaction();

            // Insere a adoção
            $sql = 'INSERT INTO Adocao (data_adocao, observacoes, fk_Usuario_cpf, fk_Pet_brinco) 
                    VALUES (CURRENT_DATE, :observacoes, :cpfUsuario, :brincoPet)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':observacoes' => $observacoes,
                ':cpfUsuario' => $cpfUsuario,
                ':brincoPet' => $brincoPet
            ]);

            // Atualiza o status do pet para 'ADOTADO'
            $sql = 'UPDATE Pet SET status = :status WHERE brinco = :brinco';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':status' => 'ADOTADO',
                ':brinco' => $brincoPet
            ]);

            // Confirma a transação
            $pdo->commit();

            // Redireciona para a página inicial
            header('Location: home.php');
            exit();
        }
        catch (Exception $e)
        {
            // Reverte a transação em caso de erro
            $pdo->rollBack();
            $mensagem = 'Erro ao processar a adoção: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adoção de Pet</title>
    <link rel="stylesheet" href="css/adocao/adocao_cadastrar.css"> <!-- Caminho do CSS -->
</head>

<body>

    <?php include_once 'cabecalho.php'; ?>

    <section class="cabecalho">
        <h2>Confirmar Adoção de Pet</h2>
    </section>

    <div class="container">

        <!-- Verifica se o adotante e o pet existem -->
        <?php if ($adotante && $pet): ?>
            <div class="form-row">
                <!-- Informações do Adotante -->
                <div class="adotante-info">
                    <h2>Adotante</h2>

                    <?php if ($adotante['imagem_url']): ?>
                        <div class="image-preview">
                            <img src="<?php echo htmlspecialchars($adotante['imagem_url']); ?>" alt="Foto do Adotante">
                        </div>
                        <p><strong>Nome:</strong> <?php echo htmlspecialchars($adotante['nome']); ?></p>
                        <p><strong>CPF:</strong> <?php echo htmlspecialchars($adotante['cpf']); ?></p>
                    <?php else: ?>
                        <p>Adotante sem foto cadastrada.</p>
                    <?php endif; ?>
                </div>

                <!-- Informações do Pet -->
                <div class="pet-info">
                    <h2>Pet</h2>
                    <?php if ($pet['imagem_url']): ?>
                        <div class="image-preview">
                            <img src="<?php echo htmlspecialchars($pet['imagem_url']); ?>" alt="Foto do Pet">
                        </div>
                        <p><strong>Nome:</strong> <?php echo htmlspecialchars($pet['nome']); ?></p>
                        <p><strong>Brinco:</strong> <?php echo htmlspecialchars($pet['brinco']); ?></p>
                    <?php else: ?>
                        <p>Pet sem foto cadastrada.</p>
                    <?php endif; ?>
                </div>
            </div>
            <hr>
            <!-- Formulário de Observações e Confirmação -->
            <form action="" method="POST">
                <div class="form-group">
                    <label for="observacoes">Observações sobre a adoção:</label>
                    <textarea id="observacoes" name="observacoes" maxlength="255"
                        placeholder="Digite suas observações sobre a adoção aqui..."></textarea>
                </div>

                <button type="submit">Confirmar Adoção</button>
            </form>

            <!-- Mensagem de erro ou sucesso -->
            <?php if (isset($mensagem)): ?>
                <div class="message">
                    <?php echo htmlspecialchars($mensagem); ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>Erro: Adotante ou Pet não encontrado.</p>
        <?php endif; ?>
    </div>
</body>

</html>