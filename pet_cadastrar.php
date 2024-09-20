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

// Verificar se o formulário foi enviado via POST
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    // Validações do formulário (conforme já estavam no código original)
    // (...)

    // Tratamento da imagem (obrigatório)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0)
    {
        // Verificar se o diretório de imagens existe, se não, criar
        $diretorioImagens = 'imagens/pets/';
        if (!is_dir($diretorioImagens))
        {
            mkdir($diretorioImagens, 0755, true);
        }

        // Nome da imagem com base no nome do pet
        $extensaoFoto = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $fotoNome = $diretorioImagens . $nome . '.' . $extensaoFoto;

        // Mover a foto para o diretório de imagens
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $fotoNome))
        {
            $erroMsg[] = "Erro ao enviar a foto.";
        }
    }
    else
    {
        $erroMsg[] = "O campo Foto é obrigatório.";
    }

    // Se não houver erros, prosseguir com o cadastro no banco de dados
    if (empty($erroMsg))
    {
        try
        {
            // Conectar ao banco de dados
            $pdo = conectar();

            // Iniciar transação para cadastrar o pet e a imagem
            $pdo->beginTransaction();

            // Definir status automaticamente como 'ADOTÁVEL'
            $status = 'ADOTÁVEL';

            // Inserir dados na tabela Pet
            $sqlPet = "INSERT INTO Pet (nome, sexo, idade, raca, pelagem, local_resgate, data_resgate, status, informacoes)
                       VALUES (:nome, :sexo, :idade, :raca, :pelagem, :local_resgate, :data_resgate, :status, :informacoes)";

            $stmtPet = $pdo->prepare($sqlPet);
            $stmtPet->bindParam(':nome', $nome);
            $stmtPet->bindParam(':sexo', $sexo);
            $stmtPet->bindParam(':idade', $idade);
            $stmtPet->bindParam(':raca', $raca);
            $stmtPet->bindParam(':pelagem', $pelagem);
            $stmtPet->bindParam(':local_resgate', $local_resgate);
            $stmtPet->bindParam(':data_resgate', $data_resgate);
            $stmtPet->bindParam(':status', $status); // Valor padrão 'ADOTÁVEL'
            $stmtPet->bindParam(':informacoes', $informacoes);

            // Executar inserção na tabela Pet
            if ($stmtPet->execute())
            {
                // Recuperar o ID do pet recém-cadastrado (brinco)
                $brincoPet = $pdo->lastInsertId();

                // Inserir a imagem do pet na tabela Imagem_Pet
                $sqlImagem = "INSERT INTO Imagem_Pet (url_imagem, fk_Pet_brinco)
                              VALUES (:url_imagem, :fk_Pet_brinco)";

                $stmtImagem = $pdo->prepare($sqlImagem);
                $stmtImagem->bindParam(':url_imagem', $fotoNome);
                $stmtImagem->bindParam(':fk_Pet_brinco', $brincoPet);

                // Executar inserção na tabela Imagem_Pet
                if ($stmtImagem->execute())
                {
                    // Commit da transação
                    $pdo->commit();
                    echo "<p>Pet cadastrado com sucesso!</p>";
                }
                else
                {
                    // Rollback se falhar a inserção da imagem
                    $pdo->rollBack();
                    echo "<p>Erro ao cadastrar a imagem do pet.</p>";
                }
            }
            else
            {
                echo "<p>Erro ao cadastrar o pet.</p>";
            }
        }
        catch (PDOException $e)
        {
            // Em caso de erro, exibir a mensagem e rollback
            $pdo->rollBack();
            echo "Erro no banco de dados: " . $e->getMessage();
        }
    }
    else
    {
        // Exibir erros de validação
        foreach ($erroMsg as $erro)
        {
            echo "<p style='color:red;'>$erro</p>";
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

    <script>
        // Função para validar o formulário no lado do cliente
        function validarFormulario() {
            let erroMsg = [];

            // Validar campo nome (obrigatório e até 255 caracteres)
            let nome = document.getElementById('nome').value;
            if (nome === "" || nome.length > 255) {
                erroMsg.push("O campo Nome é obrigatório e deve ter no máximo 255 caracteres.");
            }

            // Validar sexo (obrigatório)
            let sexo = document.getElementById('sexo').value;
            if (sexo !== "M" && sexo !== "F") {
                erroMsg.push("O campo Sexo deve ser 'M' (Macho) ou 'F' (Fêmea).");
            }

            // Validar idade (opcional, mas deve ser número inteiro)
            let idade = document.getElementById('idade').value;
            if (idade !== "" && isNaN(idade)) {
                erroMsg.push("O campo Idade deve ser um número inteiro.");
            }

            // Validar raca (até 100 caracteres)
            let raca = document.getElementById('raca').value;
            if (raca.length > 100) {
                erroMsg.push("O campo Raça deve ter no máximo 100 caracteres.");
            }

            // Validar pelagem (até 100 caracteres)
            let pelagem = document.getElementById('pelagem').value;
            if (pelagem.length > 100) {
                erroMsg.push("O campo Pelagem deve ter no máximo 100 caracteres.");
            }

            // Validar local de resgate (até 255 caracteres)
            let local_resgate = document.getElementById('local_resgate').value;
            if (local_resgate.length > 255) {
                erroMsg.push("O campo Local de Resgate deve ter no máximo 255 caracteres.");
            }

            // Validar status (obrigatório, até 20 caracteres)
            let status = document.getElementById('status').value;
            if (status === "" || status.length > 20) {
                erroMsg.push("O campo Status é obrigatório e deve ter no máximo 20 caracteres.");
            }

            // Validar foto (obrigatório)
            let foto = document.getElementById('foto').files.length;
            if (foto === 0) {
                erroMsg.push("A foto do pet é obrigatória.");
            }

            // Mostrar mensagens de erro, se houver
            if (erroMsg.length > 0) {
                document.getElementById('erroMsg').innerHTML = erroMsg.join("<br>");
                return false; // Impedir o envio do formulário
            }
            return true; // Prosseguir com o envio se não houver erros
        }

        // Função para limitar campos de texto e apenas números
        function apenasNumeros(e) {
            let tecla = (window.event) ? event.keyCode : e.which;
            if ((tecla > 47 && tecla < 58)) return true;
            else {
                if (tecla == 8 || tecla == 0) return true;
                else return false;
            }
        }
    </script>
</head>

<body>

    <?php include_once 'cabecalho.php'; ?>

    <section class="secaoPrincipal">
        <p id="erroMsg" class="error"></p>

        <form action="pet_cadastrar.php" method="POST" enctype="multipart/form-data"
            onsubmit="return validarFormulario();">
            <label for="nome">Nome do Pet (obrigatório):</label>
            <input type="text" name="nome" id="nome" maxlength="255" placeholder="Ex: Rex" required>

            <label for="sexo">Sexo do Pet (obrigatório):</label>
            <select name="sexo" id="sexo" required>
                <option value="">Selecione</option>
                <option value="M">Macho</option>
                <option value="F">Fêmea</option>
            </select>

            <label for="idade">Idade do Pet (opcional):</label>
            <input type="text" name="idade" id="idade" placeholder="Ex: 3"
                onkeypress="return apenasNumeros(event);">

            <label for="raca">Raça do Pet (até 100 caracteres):</label>
            <input type="text" name="raca" id="raca" maxlength="100" placeholder="Ex: Labrador">

            <label for="pelagem">Pelagem do Pet (até 100 caracteres):</label>
            <input type="text" name="pelagem" id="pelagem" maxlength="100" placeholder="Ex: Curta, Branca">

            <label for="local_resgate">Local de Resgate (até 255 caracteres):</label>
            <input type="text" name="local_resgate" id="local_resgate" maxlength="255"
                placeholder="Ex: Rua Principal, Centro">

            <label for="data_resgate">Data de Resgate (opcional):</label>
            <input type="date" name="data_resgate" id="data_resgate">

            <label for="informacoes">Informações Adicionais (opcional):</label>
            <textarea name="informacoes" id="informacoes"
                placeholder="Descreva detalhes adicionais sobre o pet."></textarea>

            <label for="foto">Foto do Pet (obrigatório):</label>
            <input type="file" name="foto" id="foto" accept="image/*" required>

            <button type="submit">Cadastrar Pet</button>
            <button type="reset">Limpar Formulário</button>
        </form>
    </section>

    <?php include 'rodape.php'; ?>

</body>

</html>