<?php
session_start();
require 'conexao_db.php'; // Inclua o arquivo de conexão ao banco de dados

// Verifica se o usuário está logado
if (!isset($_SESSION['cpf']))
{
    header('Location: login.php');
    exit;
}

$usuarioLogadoCpf = $_SESSION['cpf'];
$tipoUsuario = $_SESSION['tipo'];

// Conecta ao banco de dados
$pdo = conectar();

// Busca os dados do usuário logado
$sql = 'SELECT * FROM Usuario WHERE cpf = ?';
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuarioLogadoCpf]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica se o usuário foi encontrado
if (!$usuario)
{
    die('Usuário não encontrado.');
}

// Busca dados do pet (exemplo estático, ajuste conforme necessário)
$petId = filter_input(INPUT_GET, 'pet_id', FILTER_SANITIZE_NUMBER_INT);
$sqlPet = 'SELECT * FROM Pet WHERE id = ?';
$stmtPet = $pdo->prepare($sqlPet);
$stmtPet->execute([$petId]);
$pet = $stmtPet->fetch(PDO::FETCH_ASSOC);

// Se o usuário logado for um administrador, busca os adotantes
if ($tipoUsuario === 'ADMINISTRADOR')
{
    $sqlAdotantes = 'SELECT * FROM Usuario WHERE fk_Permissao_id != 1'; // Selecione adotantes
    $stmtAdotantes = $pdo->prepare($sqlAdotantes);
    $stmtAdotantes->execute();
    $adotantes = $stmtAdotantes->fetchAll(PDO::FETCH_ASSOC);
}

// Processa o formulário de adoção
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_STRING);
    $adotanteCpf = filter_input(INPUT_POST, 'usuario_cpf', FILTER_SANITIZE_STRING);
    $petId = filter_input(INPUT_POST, 'pet_id', FILTER_SANITIZE_NUMBER_INT);

    // Valida se o adotante está correto
    if ($tipoUsuario === 'ADOTANTE')
    {
        $adotanteCpf = $usuarioLogadoCpf;
    }

    // Insere a adoção no banco de dados
    $sqlAdocao = 'INSERT INTO Adocao (observacoes, fk_Usuario_cpf, fk_Pet_id) VALUES (?, ?, ?)';
    $stmtAdocao = $pdo->prepare($sqlAdocao);
    $stmtAdocao->execute([$observacoes, $adotanteCpf, $petId]);

    // Redireciona após o sucesso
    header('Location: home.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cadastrar Adoção</title>
        <link rel="stylesheet" href="css/adocao/adocao_cadastrar.css">
    </head>

    <body>
        <h2>Cadastrar Nova Adoção</h2>

        <form method="POST">
            <input type="hidden" name="pet_id" value="<?= htmlspecialchars($petId) ?>">

            <p><strong>Usuário:</strong> <?= htmlspecialchars($usuario['nome']) ?></p>
            <p><strong>Telefone:</strong> <?= htmlspecialchars($usuario['telefone']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
            <p><strong>Cidade:</strong> <?= htmlspecialchars($usuario['cidade']) ?></p>
            <p><strong>Estado:</strong> <?= htmlspecialchars($usuario['estado']) ?></p>

            <!-- Exibe imagem do usuário se houver -->
            <?php
        $sqlImagemUsuario = 'SELECT url_imagem FROM Imagem_Usuario WHERE fk_Usuario_cpf = ?';
        $stmtImagemUsuario = $pdo->prepare($sqlImagemUsuario);
        $stmtImagemUsuario->execute([$usuarioLogadoCpf]);
        $imagemUsuario = $stmtImagemUsuario->fetch(PDO::FETCH_ASSOC);
        ?>
            <p><strong>Imagem do Usuário:</strong>
                <?= $imagemUsuario ? '<img src="' . htmlspecialchars($imagemUsuario['url_imagem']) . '" alt="Imagem do Usuário">' : 'Este usuário não tem foto.' ?>
            </p>

            <label for="pet_id">Pet:</label>
            <input type="text" id="pet_id" name="pet_id" value="<?= htmlspecialchars($pet['id']) ?>" readonly><br>

            <p><strong>Nome:</strong> <?= htmlspecialchars($pet['nome']) ?></p>
            <p><strong>Brinco:</strong> <?= htmlspecialchars($pet['brinco']) ?></p>
            <p><strong>Sexo:</strong> <?= htmlspecialchars($pet['sexo']) ?></p>
            <p><strong>Idade:</strong> <?= htmlspecialchars($pet['idade']) ?></p>
            <p><strong>Raça:</strong> <?= htmlspecialchars($pet['raca']) ?></p>
            <p><strong>Pelagem:</strong> <?= htmlspecialchars($pet['pelagem']) ?></p>
            <p><strong>Local do Resgate:</strong> <?= htmlspecialchars($pet['local_resgate']) ?></p>
            <p><strong>Data do Resgate:</strong> <?= htmlspecialchars($pet['data_resgate']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($pet['status']) ?></p>
            <p><strong>Informações:</strong> <?= htmlspecialchars($pet['informacoes']) ?></p>

            <!-- Exibe imagem do pet se houver -->
            <?php
        $sqlImagemPet = 'SELECT url_imagem FROM Imagem_Pet WHERE fk_Pet_id = ?';
        $stmtImagemPet = $pdo->prepare($sqlImagemPet);
        $stmtImagemPet->execute([$petId]);
        $imagemPet = $stmtImagemPet->fetch(PDO::FETCH_ASSOC);
        ?>
            <p><strong>Imagem do Pet:</strong>
                <?= $imagemPet ? '<img src="' . htmlspecialchars($imagemPet['url_imagem']) . '" alt="Imagem do Pet">' : 'Este pet está sem foto.' ?>
            </p>

            <label for="observacoes">Observações:</label>
            <textarea id="observacoes" name="observacoes"></textarea><br>

            <!-- Se for administrador, exibe lista de adotantes -->
            <?php if ($tipoUsuario === 'ADMINISTRADOR'): ?>
            <label for="usuario_cpf">Adotante:</label>
            <select id="usuario_cpf" name="usuario_cpf" required>
                <?php foreach ($adotantes as $adotante): ?>
                <option value="<?= htmlspecialchars($adotante['cpf']) ?>"><?= htmlspecialchars($adotante['nome']) ?>
                </option>
                <?php endforeach; ?>
            </select><br>
            <?php else: ?>
            <input type="hidden" name="usuario_cpf" value="<?= htmlspecialchars($usuarioLogadoCpf) ?>">
            <?php endif; ?>

            <button type="submit">Cadastrar Adoção</button>
        </form>

        <p><a href="home.php">Voltar para Home</a></p>
    </body>

</html>