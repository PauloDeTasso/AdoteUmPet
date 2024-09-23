<?php
// Incluir arquivo de conexão com o banco de dados
require_once 'conexao_db.php';

// Função para sanitizar e validar entradas (proteção contra XSS)
function sanitizarEntrada($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Inicializar variáveis para armazenar os valores do formulário e erros
$nome = $sexo = $idade = $raca = $pelagem = $local_resgate = $data_resgate = $informacoes = "";
$fotoNome = "";
$erroMsg = [];
$sucessoMsg = "";

// Verificar se o formulário foi enviado via POST
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    // Sanitizar entradas
    $nome = sanitizarEntrada($_POST['nome']);
    $sexo = sanitizarEntrada($_POST['sexo']);
    $idade = sanitizarEntrada($_POST['idade']);
    $raca = sanitizarEntrada($_POST['raca']);
    $pelagem = sanitizarEntrada($_POST['pelagem']);
    $local_resgate = sanitizarEntrada($_POST['local_resgate']);
    $data_resgate = sanitizarEntrada($_POST['data_resgate']);
    $informacoes = sanitizarEntrada($_POST['informacoes']);

    // Validações do formulário
    if (empty($nome) || strlen($nome) > 255)
    {
        $erroMsg[] = "O campo Nome é obrigatório e deve ter no máximo 255 caracteres.";
    }

    // Validações adicionais para cada campo
    if ($sexo !== "M" && $sexo !== "F")
    {
        $erroMsg[] = "O campo Sexo deve ser 'M' (Macho) ou 'F' (Fêmea).";
    }

    if (!empty($idade) && !is_numeric($idade))
    {
        $erroMsg[] = "O campo Idade deve ser um número inteiro.";
    }

    if (strlen($raca) > 100)
    {
        $erroMsg[] = "O campo Raça deve ter no máximo 100 caracteres.";
    }

    if (strlen($pelagem) > 100)
    {
        $erroMsg[] = "O campo Pelagem deve ter no máximo 100 caracteres.";
    }

    if (strlen($local_resgate) > 255)
    {
        $erroMsg[] = "O campo Local de Resgate deve ter no máximo 255 caracteres.";
    }

    if (strlen($informacoes) > 500)
    {
        $erroMsg[] = "O campo Informações Adicionais deve ter no máximo 500 caracteres.";
    }

    // Tratamento da imagem (opcional)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0)
    {
        $diretorioImagens = 'imagens/pets/';
        if (!is_dir($diretorioImagens))
        {
            mkdir($diretorioImagens, 0755, true);
        }

        $extensaoFoto = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $baseNomeFoto = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nome); // Substituir caracteres inválidos
        $fotoNome = $diretorioImagens . $baseNomeFoto;

        // Gerar nome único
        $contador = 1;
        while (file_exists($fotoNome . '.' . $extensaoFoto))
        {
            $fotoNome = $diretorioImagens . $baseNomeFoto . '_' . $contador;
            $contador++;
        }
        $fotoNome .= '.' . $extensaoFoto;

        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $fotoNome))
        {
            $erroMsg[] = "Erro ao enviar a foto.";
        }
    }

    // Se não houver erros, prosseguir com o cadastro no banco de dados
    if (empty($erroMsg))
    {
        try
        {
            // Conectar ao banco de dados
            $pdo = conectar();
            $pdo->beginTransaction();

            $status = 'ADOTÁVEL';

            // Prepare a consulta, permitindo NULL para campos opcionais
            $sqlPet = "INSERT INTO Pet (nome, sexo, idade, raca, pelagem, local_resgate, data_resgate, data_cadastro, status, informacoes)
                       VALUES (:nome, :sexo, :idade, :raca, :pelagem, :local_resgate, :data_resgate, CURRENT_DATE, :status, :informacoes)";

            $stmtPet = $pdo->prepare($sqlPet);
            $stmtPet->bindParam(':nome', $nome);
            $stmtPet->bindParam(':sexo', $sexo);
            $stmtPet->bindValue(':idade', !empty($idade) ? (int)$idade : null, PDO::PARAM_INT); // Permitir NULL
            $stmtPet->bindParam(':raca', $raca);
            $stmtPet->bindParam(':pelagem', $pelagem);
            $stmtPet->bindParam(':local_resgate', $local_resgate);
            $stmtPet->bindValue(':data_resgate', !empty($data_resgate) ? $data_resgate : null, PDO::PARAM_NULL); // Permitir NULL
            $stmtPet->bindParam(':status', $status);
            $stmtPet->bindParam(':informacoes', $informacoes);

            // Executar inserção na tabela Pet
            if ($stmtPet->execute())
            {
                $brincoPet = $pdo->lastInsertId();

                // Inserir a imagem do pet na tabela Imagem_Pet se foi enviada
                if (!empty($fotoNome))
                {
                    $sqlImagem = "INSERT INTO Imagem_Pet (url_imagem, fk_Pet_brinco)
                                  VALUES (:url_imagem, :fk_Pet_brinco)";

                    $stmtImagem = $pdo->prepare($sqlImagem);
                    $stmtImagem->bindParam(':url_imagem', $fotoNome);
                    $stmtImagem->bindParam(':fk_Pet_brinco', $brincoPet);

                    // Executar inserção na tabela Imagem_Pet
                    if (!$stmtImagem->execute())
                    {
                        $pdo->rollBack();
                        $erroMsg[] = "Erro ao cadastrar a imagem do pet.";
                    }
                }

                $pdo->commit();
                $sucessoMsg = "Pet cadastrado com sucesso!";
            }
            else
            {
                $erroMsg[] = "Erro ao cadastrar o pet.";
            }
        }
        catch (PDOException $e)
        {
            $pdo->rollBack();
            $erroMsg[] = "Erro no banco de dados: " . $e->getMessage();
        }
    }
}
?>




<!DOCTYPE html>
<html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cadastrar Pet</title>
        <link rel="stylesheet" href="css/pet/pet_cadastrar.css">

        <style>
        /* Estilo para o toast */
        #toast {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 2px;
            padding: 16px;
            position: fixed;
            z-index: 1;
            left: 50%;
            bottom: 30px;
            font-size: 17px;
        }

        #toast.show {
            visibility: visible;
            -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
            animation: fadein 0.5s, fadeout 0.5s 2.5s;
        }

        @-webkit-keyframes fadein {
            from {
                bottom: 0;
                opacity: 0;
            }

            to {
                bottom: 30px;
                opacity: 1;
            }
        }

        @keyframes fadein {
            from {
                bottom: 0;
                opacity: 0;
            }

            to {
                bottom: 30px;
                opacity: 1;
            }
        }

        @-webkit-keyframes fadeout {
            from {
                bottom: 30px;
                opacity: 1;
            }

            to {
                bottom: 0;
                opacity: 0;
            }
        }

        @keyframes fadeout {
            from {
                bottom: 30px;
                opacity: 1;
            }

            to {
                bottom: 0;
                opacity: 0;
            }
        }
        </style>

        <script>
        // Função para mostrar o toast
        function mostrarToast(mensagem, tipo) {
            let toast = document.getElementById("toast");
            toast.innerHTML = mensagem;
            if (tipo === 'erro') {
                toast.style.backgroundColor = '#e74c3c'; // Cor para erro
            } else {
                toast.style.backgroundColor = '#2ecc71'; // Cor para sucesso
            }
            toast.className = "show";
            setTimeout(function() {
                toast.className = toast.className.replace("show", "");
            }, 3000);
        }

        // Mostrar erros ou sucesso ao carregar a página
        window.onload = function() {
            <?php if (!empty($erroMsg)) : ?>
            mostrarToast("<?php echo implode('<br>', $erroMsg); ?>", 'erro');
            <?php elseif (!empty($sucessoMsg)) : ?>
            mostrarToast("<?php echo $sucessoMsg; ?>", 'sucesso');
            <?php endif; ?>
        };
        </script>
    </head>

    <body>

        <?php include 'cabecalho.php'; ?>

        <section class="cabecalho">
            <h3>Cadastrar Pet</h3>
        </section>

        <div id="toast"></div>

        <section class="sessaoFormulario">

            <form method="POST" enctype="multipart/form-data">
                <label for="nome">Nome*:</label>
                <input type="text" id="nome" name="nome" placeholder="Digite o nome do pet (máx. 255 caracteres)"
                    maxlength="255" required>

                <label for="sexo">Sexo*:</label>
                <select id="sexo" name="sexo" required>
                    <option value="">Selecione o sexo</option>
                    <option value="M">Macho</option>
                    <option value="F">Fêmea</option>
                </select>

                <label for="idade">Idade:</label>
                <input type="number" id="idade" name="idade" placeholder="Digite a idade do pet (em anos)" min="0">

                <label for="raca">Raça:</label>
                <input type="text" id="raca" name="raca" placeholder="Digite a raça do pet (máx. 100 caracteres)"
                    maxlength="100">

                <label for="pelagem">Pelagem:</label>
                <input type="text" id="pelagem" name="pelagem"
                    placeholder="Digite a pelagem do pet (máx. 100 caracteres)" maxlength="100">

                <label for="local_resgate">Local de Resgate:</label>
                <input type="text" id="local_resgate" name="local_resgate"
                    placeholder="Digite o local de resgate (máx. 255 caracteres)" maxlength="255">

                <label for="data_resgate">Data de Resgate:</label>
                <input type="date" id="data_resgate" name="data_resgate">

                <label for="informacoes">Informações Adicionais:</label>
                <textarea id="informacoes" name="informacoes" placeholder="Digite informações adicionais sobre o pet"
                    rows="4" maxlength="500"></textarea>

                <label for="foto">Foto:</label>
                <input type="file" id="foto" name="foto" accept="image/*">

                <button type="submit">Cadastrar Pet</button>

                <div class="feedback erro">
                    <?php foreach ($erroMsg as $msg): ?>
                    <p><?php echo $msg; ?></p>
                    <?php endforeach; ?>
                </div>

                <div class="feedback sucesso">
                    <?php if ($sucessoMsg): ?>
                    <p><?php echo $sucessoMsg; ?></p>
                    <?php endif; ?>
                </div>
            </form>

        </section>

        <?php include 'rodape.php'; ?>

    </body>

</html>