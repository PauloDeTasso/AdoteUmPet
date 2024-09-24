<?php
include_once "start.php";

// Obtenha a instância do PDO
$pdo = conectar();

// Função para verificar a permissão do usuário logado
function verificaPermissao($pdo, $cpf)
{
    $sql = 'SELECT p.tipo FROM Usuario u
            INNER JOIN Permissao p ON u.fk_Permissao_id = p.id
            WHERE u.cpf = :cpf';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':cpf' => $cpf]);
    return $stmt->fetchColumn();
}

// Função para obter um adotante específico pelo CPF, incluindo a data de nascimento
function getAdotante($pdo, $cpf)
{
    $sql = 'SELECT u.cpf, u.nome, u.data_nascimento, i.url_imagem
            FROM Usuario u
            LEFT JOIN Imagem_Usuario i ON u.cpf = i.fk_Usuario_cpf
            WHERE u.cpf = :cpf AND u.status = :status';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':cpf' => $cpf, ':status' => 'ATIVO']);

    $getAdotanteDb = $stmt->fetch(PDO::FETCH_ASSOC);


    if (empty($getAdotanteDb['url_imagem']) || !file_exists($getAdotanteDb['url_imagem']))
    {
        $getAdotanteDb['url_imagem'] = 'imagens/usuarios/default.jpg';
    }
    return $getAdotanteDb;
}


// Função para obter um pet específico pelo brinco
function getPet($pdo, $brinco)
{
    $sql = 'SELECT p.brinco, p.nome, p.sexo
            FROM Pet p
            WHERE p.brinco = :brinco AND p.status = :status';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':brinco' => $brinco, ':status' => 'ADOTÁVEL']);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para obter a URL da imagem de um pet
function getImagemPet($pdo, $brinco)
{
    $sql = 'SELECT url_imagem FROM Imagem_Pet WHERE fk_Pet_brinco = :brinco LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':brinco' => $brinco]);
    return $stmt->fetchColumn();
}

// Função para listar todos os pets adotáveis
function listarPetsAdotaveis($pdo)
{
    $sql = 'SELECT p.brinco, p.nome FROM Pet p WHERE p.status = :status';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':status' => 'ADOTÁVEL']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtém os parâmetros da URI (pet e adotante), se existirem
$cpfUsuarioLogado = $_SESSION['cpf'];
$cpfUsuario = filter_input(INPUT_GET, 'adotante', FILTER_SANITIZE_STRING);
if (!$cpfUsuario)
{
    $cpfUsuario = $cpfUsuarioLogado;
}

$brincoPet = filter_input(INPUT_GET, 'pet', FILTER_SANITIZE_NUMBER_INT);

// Obtém os dados do adotante
$adotante = getAdotante($pdo, $cpfUsuario);

// Se o brinco do pet não for fornecido, a página carrega sem pet selecionado inicialmente
$pet = null;
$imagemPetUrl = 'imagens/pets/default.jpg'; // URL da imagem padrão
if ($brincoPet)
{
    $pet = getPet($pdo, $brincoPet);
    if ($pet)
    {
        $imagemPetUrl = getImagemPet($pdo, $brincoPet);
        if (!$imagemPetUrl)
        {
            $imagemPetUrl = 'imagens/pets/default.jpg'; // Fallback para a imagem padrão se não houver URL
        }
    }
}

// Verifica se o adotante existe no banco de dados
if (!$adotante)
{
    echo "Erro: Adotante não encontrado.";
    exit();
}

// Obtém a lista de pets adotáveis para exibir na caixa de seleção
$petsAdotaveis = listarPetsAdotaveis($pdo);

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $observacoes = filter_var(trim($_POST['observacoes']), FILTER_SANITIZE_STRING);
    $brincoPetPost = filter_input(INPUT_POST, 'pet_brinco', FILTER_SANITIZE_NUMBER_INT);

    // Validação básica dos dados
    if (strlen($observacoes) > 255)
    {
        $mensagem = 'Observações não podem exceder 255 caracteres.';
    }
    elseif (!$brincoPetPost)
    {
        $mensagem = 'Erro: Você deve selecionar um pet válido.';
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
                ':brincoPet' => $brincoPetPost
            ]);

            // Atualiza o status do pet para 'ADOTADO'
            $sql = 'UPDATE Pet SET status = :status WHERE brinco = :brinco';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':status' => 'ADOTADO',
                ':brinco' => $brincoPetPost
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

// Se a requisição for AJAX para obter a imagem do pet
if (isset($_GET['action']) && $_GET['action'] === 'getImagemPet')
{
    $brinco = filter_input(INPUT_GET, 'brinco', FILTER_SANITIZE_NUMBER_INT);
    if ($brinco)
    {
        $imagemUrl = getImagemPet($pdo, $brinco);
        echo $imagemUrl ? $imagemUrl : 'imagens/pets/default.jpg';
    }
    else
    {
        echo 'imagens/pets/default.jpg'; // Imagem padrão se não houver brinco
    }
    exit();
}

// Função para obter os dados do pet
if (isset($_GET['action']) && $_GET['action'] === 'getPetData')
{
    $brinco = filter_input(INPUT_GET, 'brinco', FILTER_SANITIZE_NUMBER_INT);
    if ($brinco)
    {
        $petData = getPet($pdo, $brinco);
        echo json_encode($petData);
    }
    exit();
}

// Verifica se o adotante tem idade suficiente (18 anos ou mais)
$dataNascimento = $adotante['data_nascimento'];

try
{
    $dataNascimentoObj = new DateTime($dataNascimento);
    $dataAtual = new DateTime();
    $idade = $dataAtual->diff($dataNascimentoObj)->y;
}
catch (Exception $e)
{
    echo "<script>alert('Erro ao calcular idade: " . $e->getMessage() . "');</script>";
    exit();
}

if ($idade < 18)
{
    echo "<script>
            alert('Erro: Você deve ter 18 anos ou mais para adotar um pet.');
            window.location.href = 'pets.php';
          </script>";
    exit();
}

?>

<!DOCTYPE html>
<html lang="pt-br">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Adoção de Pet</title>
        <link rel="stylesheet" href="css/adocao/adocao_cadastrar.css">
        <script>
        // Função para atualizar a imagem do pet com AJAX
        function atualizarImagemPet() {
            const petSelecionado = document.getElementById('pet_brinco');
            const imagemPet = document.getElementById('imagem_pet');
            const brincoPet = petSelecionado.value;

            if (brincoPet) {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', `adocao_cadastrar.php?action=getImagemPet&brinco=${brincoPet}`, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const imagemUrl = xhr.responseText;
                        imagemPet.src = imagemUrl ? imagemUrl : 'imagens/pets/default.jpg';
                    } else {
                        imagemPet.src = 'imagens/pets/default.jpg'; // Fallback
                    }
                };
                xhr.send();
            } else {
                imagemPet.src = 'imagens/pets/default.jpg'; // Imagem padrão se nenhum pet estiver selecionado
            }
        }

        // Função para atualizar os dados do pet
        function atualizarDadosPet() {
            const petSelecionado = document.getElementById('pet_brinco');
            const brincoPet = petSelecionado.value;

            // Atualiza os dados do pet, como nome e sexo, se um pet for selecionado
            if (brincoPet) {
                const petNome = document.getElementById('pet_nome');
                const petSexo = document.getElementById('pet_sexo');

                // Faz a requisição AJAX para obter os dados do pet
                const xhr = new XMLHttpRequest();
                xhr.open('GET', `adocao_cadastrar.php?action=getPetData&brinco=${brincoPet}`, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const petData = JSON.parse(xhr.responseText);
                        petNome.textContent = `Nome: ${petData.nome}`;
                        petSexo.textContent = `Sexo: ${petData.sexo === 'M' ? 'Macho' : 'Fêmea'}`;
                    } else {
                        petNome.textContent = 'Nome: -';
                        petSexo.textContent = 'Sexo: -';
                    }
                };
                xhr.send();
            } else {
                // Limpa os dados se nenhum pet estiver selecionado
                petNome.textContent = 'Nome: -';
                petSexo.textContent = 'Sexo: -';
            }
        }
        </script>
    </head>

    <body>

        <?php include_once 'cabecalho.php'; ?>

        <section class="cabecalho">
            <h2>Adoção de Pet</h2>
        </section>

        <div class="container">

            <!-- Formulário de Adoção -->
            <form method="POST">

                <!-- Verifica se o adotante existe -->
                <?php if ($adotante): ?>
                <div class="form-row">
                    <!-- Informações do Adotante -->
                    <img id="imagem_adotante" src="<?php echo htmlspecialchars($adotante['url_imagem']); ?>"
                        alt="Imagem do Adotante" />
                    <section class="info">
                        <h3>Adotante:</h3>
                        <p>Nome: <?php echo htmlspecialchars($adotante['nome']); ?></p>
                        <p>CPF: <?php echo htmlspecialchars($adotante['cpf']); ?></p>
                    </section>
                </div>
                <?php else: ?>
                <p>Adotante não encontrado.</p>
                <?php endif; ?>

                <div class="form-row">
                    <img id="imagem_pet" src="<?php echo htmlspecialchars($imagemPetUrl); ?>" alt="Imagem do Pet" />
                    <section class="info">
                        <h4>Pet:</h4>
                        <p id="pet_nome">Nome:
                            <?php
                        // Exibe o nome do pet se o array $pet existir e contiver um nome
                        echo isset($pet['nome']) ? htmlspecialchars($pet['nome']) : 'Nenhum pet selecionado';
                        ?>
                        </p>
                        <p id="pet_sexo">Sexo:
                            <?php
                        // Exibe o sexo do pet se o array $pet existir e contiver o campo sexo
                        if (isset($pet['sexo']))
                        {
                            echo htmlspecialchars($pet['sexo'] === 'M' ? 'Macho' : 'Fêmea');
                        }
                        else
                        {
                            echo 'Nenhum pet selecionado';
                        }
                        ?>
                        </p>
                    </section>
                </div>


                <div class="form-row">
                    <select id="pet_brinco" name="pet_brinco" onchange="atualizarImagemPet(); atualizarDadosPet()">
                        <option value="">Selecione um Pet</option>
                        <?php foreach ($petsAdotaveis as $pet): ?>
                        <option value="<?php echo htmlspecialchars($pet['brinco']); ?>"
                            <?php if ($pet && $pet['brinco'] === (int)$brincoPet) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($pet['nome']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>


                <div class="form-row">
                    <label for="observacoes">Observações:</label>
                    <textarea id="observacoes" name="observacoes" rows="4" cols="50"
                        placeholder="Digite aqui suas observações..."><?php echo htmlspecialchars($observacoes ?? ''); ?></textarea>
                </div>

                <button type="submit">Confirmar Adoção</button>
            </form>

            <!-- Exibe mensagens de erro ou sucesso -->
            <?php if (isset($mensagem)): ?>
            <p class="mensagem"><?php echo htmlspecialchars($mensagem); ?></p>
            <?php endif; ?>

        </div>

        <?php include 'rodape.php'; ?>

    </body>

</html>