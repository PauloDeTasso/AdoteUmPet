<?php
// Inclua o arquivo de conexão
include 'conexao_db.php';

// Inicie a sessão para verificar o usuário logado
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['cpf']))
{
    header('Location: login.php');
    exit();
}

// Obtenha a instância do PDO
$pdo = conectar();

// Função para obter um adotante específico pelo CPF
function getAdotante($pdo, $cpf)
{
    $sql = 'SELECT u.cpf, u.nome, COALESCE(i.url_imagem, \'imagens/usuarios/default.jpg\') AS imagem_url
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
    $sql = 'SELECT p.brinco, p.nome, COALESCE(i.url_imagem, \'imagens/pets/default.jpg\') AS imagem_url
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

                    <div class="image-preview">
                        <img src="<?php echo htmlspecialchars($adotante['imagem_url']); ?>" alt="Foto do Adotante">
                    </div>
                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($adotante['nome']); ?></p>
                    <p><strong>CPF:</strong> <?php echo htmlspecialchars($adotante['cpf']); ?></p>
                </div>

                <!-- Informações do Pet -->
                <div class="pet-info">
                    <h2>Pet</h2>
                    <div class="image-preview">
                        <img src="<?php echo htmlspecialchars($pet['imagem_url']); ?>" alt="Foto do Pet">
                    </div>
                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($pet['nome']); ?></p>
                    <p><strong>Brinco:</strong> <?php echo htmlspecialchars($pet['brinco']); ?></p>
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