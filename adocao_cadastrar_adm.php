<?php
// Inclua o arquivo de conexão e utilidades
include 'conexao_db.php';
include 'utilidades.php';

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

// Função para calcular a idade com base na data de nascimento
function calcularIdade($dataNascimento)
{
    $dataAtual = new DateTime();
    $nascimento = new DateTime($dataNascimento);
    $idade = $dataAtual->diff($nascimento)->y;
    return $idade;
}

// Função para obter todos os usuários ativos maiores de 18 anos
function getUsuariosAtivos($pdo)
{
    $sql = '
        SELECT u.cpf, u.nome, u.data_nascimento, i.url_imagem
        FROM Usuario u
        LEFT JOIN Imagem_Usuario i ON u.cpf = i.fk_Usuario_cpf
        WHERE u.status = :status
    ';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':status' => 'ATIVO']);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filtra apenas os usuários maiores de 18 anos
    $usuariosValidos = [];
    foreach ($usuarios as $usuario)
    {
        $idade = calcularIdade($usuario['data_nascimento']);
        if ($idade >= 18)
        {
            $usuariosValidos[] = $usuario;
        }
    }
    return $usuariosValidos;
}

// Função para obter pets disponíveis
function getPetsDisponiveis($pdo)
{
    $sql = '
        SELECT p.brinco, p.nome, i.url_imagem
        FROM Pet p
        LEFT JOIN Imagem_Pet i ON p.brinco = i.fk_Pet_brinco
        WHERE p.status = :status
    ';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':status' => 'ADOTÁVEL']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtém o CPF do adotante da URL, se estiver disponível
$cpfAdotante = filter_input(INPUT_GET, 'adotante', FILTER_SANITIZE_STRING);

// Obtém os dados para exibição
$usuariosAtivos = getUsuariosAtivos($pdo);
$petsDisponiveis = getPetsDisponiveis($pdo);

// Define o pet pré-selecionado, se existir
$petSelecionado = filter_input(INPUT_GET, 'pet', FILTER_SANITIZE_NUMBER_INT);

// Inicializa a variável mensagem para evitar avisos
$mensagem = '';
$tipoMensagem = 'info'; // Default message type

// Processa o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    // Verifica se os índices estão definidos
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
    $pet = filter_input(INPUT_POST, 'pet', FILTER_SANITIZE_NUMBER_INT);
    $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_STRING);

    // Valida os dados
    if ($usuario && $pet)
    {
        // Verifica a idade do adotante
        $sql = 'SELECT data_nascimento FROM Usuario WHERE cpf = :cpf LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':cpf' => $usuario]);
        $dataNascimento = $stmt->fetchColumn();

        if ($dataNascimento)
        {
            $idade = calcularIdade($dataNascimento);

            if ($idade >= 18)
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
            }
            else
            {
                // Mensagem de erro para idade insuficiente
                $mensagem = 'O adotante deve ter 18 anos ou mais para adotar um pet.';
                $tipoMensagem = 'erro';
            }
        }
    }
    else
    {
        // Mensagem de erro
        $mensagem = 'Por favor, selecione um adotante e um pet.';
        $tipoMensagem = 'erro';
    }
}

// Lógica para requisição AJAX
if (isset($_GET['action']))
{
    if ($_GET['action'] === 'getImagemPet' && isset($_GET['brinco']))
    {
        $brinco = filter_input(INPUT_GET, 'brinco', FILTER_SANITIZE_NUMBER_INT);
        $sql = 'SELECT url_imagem FROM Imagem_Pet WHERE fk_Pet_brinco = :brinco LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':brinco' => $brinco]);
        $imagemUrl = $stmt->fetchColumn();
        echo $imagemUrl ? $imagemUrl : 'imagens/pets/default.jpg';
        exit();
    }
    elseif ($_GET['action'] === 'getPetData' && isset($_GET['brinco']))
    {
        $brinco = filter_input(INPUT_GET, 'brinco', FILTER_SANITIZE_NUMBER_INT);
        $sql = 'SELECT nome, sexo FROM Pet WHERE brinco = :brinco LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':brinco' => $brinco]);
        $petData = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($petData ? $petData : ['nome' => '-', 'sexo' => '-']);
        exit();
    }
    elseif ($_GET['action'] === 'getImagemUsuario' && isset($_GET['cpf']))
    {
        $cpf = filter_input(INPUT_GET, 'cpf', FILTER_SANITIZE_STRING);
        $sql = 'SELECT url_imagem FROM Imagem_Usuario WHERE fk_Usuario_cpf = :cpf LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':cpf' => $cpf]);
        $imagemUrl = $stmt->fetchColumn();
        echo $imagemUrl ? $imagemUrl : 'imagens/usuarios/default.jpg';
        exit();
    }
    elseif ($_GET['action'] === 'getUsuarioData' && isset($_GET['cpf']))
    {
        $cpf = filter_input(INPUT_GET, 'cpf', FILTER_SANITIZE_STRING);
        $sql = 'SELECT nome, telefone FROM Usuario WHERE cpf = :cpf LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':cpf' => $cpf]);
        $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($usuarioData ? $usuarioData : ['nome' => '-', 'telefone' => '-']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cadastro de Adoção</title>
        <link rel="stylesheet" href="css/adocao/adocao_cadastrar_adm.css">
        <script>
        // Função para atualizar a imagem do pet com AJAX
        function atualizarImagemPet() {
            const petSelecionado = document.getElementById('pet');
            const imagemPet = document.getElementById('imagemPet');
            const brincoPet = petSelecionado.value;

            if (brincoPet) {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', 'adocao_cadastrar_adm.php?action=getImagemPet&brinco=' + brincoPet, true);
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

        function atualizarDadosPet() {
            const petSelecionado = document.getElementById('pet');
            const brincoPet = petSelecionado.value;

            if (brincoPet) {
                const petNome = document.getElementById('pet_nome');
                const petSexo = document.getElementById('pet_sexo');

                const xhr = new XMLHttpRequest();
                xhr.open('GET', 'adocao_cadastrar_adm.php?action=getPetData&brinco=' + brincoPet, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const petData = JSON.parse(xhr.responseText);
                        petNome.textContent = petData.nome ? petData.nome : '-';
                        petSexo.textContent = petData.sexo ? (petData.sexo === 'M' ? 'Macho' : 'Fêmea') : '-';
                    } else {
                        petNome.textContent = '-';
                        petSexo.textContent = '-';
                    }
                };
                xhr.send();
            } else {
                document.getElementById('pet_nome').textContent = '-';
                document.getElementById('pet_sexo').textContent = '-';
            }
        }


        function atualizarImagemUsuario() {
            const usuarioSelecionado = document.getElementById('usuario');
            const cpfUsuario = usuarioSelecionado.value;
            const imagemUsuario = document.getElementById('imagemUsuario');

            if (cpfUsuario) {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', 'adocao_cadastrar_adm.php?action=getImagemUsuario&cpf=' + cpfUsuario, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const imagemUrl = xhr.responseText;
                        imagemUsuario.src = imagemUrl ? imagemUrl : 'imagens/usuarios/default.jpg';
                    } else {
                        imagemUsuario.src = 'imagens/usuarios/default.jpg';
                    }
                };
                xhr.send();
            } else {
                imagemUsuario.src =
                    'imagens/usuarios/default.jpg'; // Imagem padrão se nenhum usuário estiver selecionado
            }
        }

        function atualizarDadosUsuario() {
            const usuarioSelecionado = document.getElementById('usuario');
            const cpfUsuario = usuarioSelecionado.value;

            if (cpfUsuario) {
                const usuarioNome = document.getElementById('usuario_nome');
                const usuarioTelefone = document.getElementById('usuario_telefone');

                const xhr = new XMLHttpRequest();
                xhr.open('GET', 'adocao_cadastrar_adm.php?action=getUsuarioData&cpf=' + cpfUsuario, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const usuarioData = JSON.parse(xhr.responseText);
                        usuarioNome.textContent = usuarioData.nome ? usuarioData.nome : '-';
                        usuarioTelefone.textContent = usuarioData.telefone ? usuarioData.telefone : '-';
                    } else {
                        usuarioNome.textContent = '-';
                        usuarioTelefone.textContent = '-';
                    }
                };
                xhr.send();
            } else {
                document.getElementById('usuario_nome').textContent = '-';
                document.getElementById('usuario_telefone').textContent = '-';
            }
        }
        </script>
    </head>

    <body>

        <?php include 'cabecalho.php'; ?>

        <section class="cabecalho">
            <h3>Cadastro de Adoção</h3>
        </section>

        <section class="sessaoPrincipal">

            <div class="container">
                <?php if ($mensagem): ?>
                <div class="mensagem <?= $tipoMensagem ?>">
                    <?= $mensagem ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Seleção de Usuário -->
                    <label for="usuario">Adotante:</label>
                    <select id="usuario" name="usuario" onchange="atualizarImagemUsuario(); atualizarDadosUsuario();">
                        <option value="">Selecione um adotante</option>
                        <?php foreach ($usuariosAtivos as $usuario): ?>
                        <option value="<?= $usuario['cpf']; ?>"><?= $usuario['nome']; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Exibição da Imagem e Dados do Adotante -->
                    <img id="imagemUsuario" src="imagens/usuarios/default.jpg" alt="Imagem do Adotante" width="100">
                    <p id="usuario_nome">-</p>
                    <p id="usuario_telefone">-</p>

                    <!-- Seleção de Pet -->
                    <label for="pet">Pet:</label>
                    <select id="pet" name="pet" onchange="atualizarImagemPet(); atualizarDadosPet();">
                        <option value="">Selecione um pet</option>
                        <?php foreach ($petsDisponiveis as $pet): ?>
                        <option value="<?= $pet['brinco']; ?>"><?= $pet['nome']; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Exibição da Imagem e Dados do Pet -->
                    <img id="imagemPet" src="imagens/pets/default.jpg" alt="Imagem do Pet" width="100">
                    <p id="pet_nome">-</p>
                    <p id="pet_sexo">-</p>


                    <label for="observacoes">Observações:</label>
                    <textarea name="observacoes" id="observacoes" rows="3"
                        placeholder="Observações adicionais"></textarea>

                    <button type="submit">Cadastrar Adoção</button>
                </form>
            </div>

        </section>

        <?php include 'rodape.php'; ?>

    </body>

</html>