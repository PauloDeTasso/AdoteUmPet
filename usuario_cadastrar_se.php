<?php

include 'conexao_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $conn = conectar();

    $cpf = $_POST['cpf'];
    $nome = $_POST['nome'];
    $dataNascimento = $_POST['data_nascimento'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $senha = $_POST['senha'];
    $rua = $_POST['rua'];
    $numero = $_POST['numero'];
    $bairro = $_POST['bairro'];
    $cep = $_POST['cep'];
    $referencia = $_POST['referencia'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];

    // Verifica se o arquivo de imagem foi enviado
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK)
    {
        // Caminho completo para salvar a imagem
        $imagemNome = uniqid() . '-' . basename($_FILES['imagem']['name']);
        $imagemPath = 'imagens/usuarios/' . $imagemNome;

        // Move o arquivo para o diretório correto
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $imagemPath))
        {
            $imagemUrl = $imagemPath;
        }
        else
        {
            echo "Erro ao mover o arquivo para o diretório.";
        }
    }
    else
    {
        echo "Erro no envio do arquivo de imagem.";
        $imagemUrl = null;
    }


    // Insere o usuário
    $sql = "INSERT INTO Usuario (cpf, nome, data_nascimento, email, telefone, status, senha) VALUES (:cpf, :nome, :data_nascimento, :email, :telefone, 'ATIVO', :senha)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':cpf' => $cpf,
        ':nome' => $nome,
        ':data_nascimento' => $dataNascimento,
        ':email' => $email,
        ':telefone' => $telefone,
        ':senha' => $senha
    ]);

    // Insere o endereço se fornecido
    if ($rua && $bairro && $cep && $cidade && $estado)
    {
        $sqlEndereco = "INSERT INTO Endereco (rua, numero, bairro, cep, referencia, cidade, estado) VALUES (:rua, :numero, :bairro, :cep, :referencia, :cidade, :estado)";
        $stmtEndereco = $conn->prepare($sqlEndereco);
        $stmtEndereco->execute([
            ':rua' => $rua,
            ':numero' => $numero,
            ':bairro' => $bairro,
            ':cep' => $cep,
            ':referencia' => $referencia,
            ':cidade' => $cidade,
            ':estado' => $estado
        ]);

        $enderecoId = $conn->lastInsertId();

        $sqlEnderecosUsuarios = "INSERT INTO Enderecos_Usuarios (fk_Usuario_cpf, fk_Endereco_id) VALUES (:cpf, :endereco_id)";
        $stmtEnderecosUsuarios = $conn->prepare($sqlEnderecosUsuarios);
        $stmtEnderecosUsuarios->execute([
            ':cpf' => $cpf,
            ':endereco_id' => $enderecoId
        ]);
    }

    // Insere a imagem do usuário, se houver
    if ($imagemUrl)
    {
        $sqlImagem = "INSERT INTO Imagem_Usuario (url_imagem, fk_Usuario_cpf) VALUES (:imagem_url, :cpf)";
        $stmtImagem = $conn->prepare($sqlImagem);
        $stmtImagem->execute([
            ':imagem_url' => $imagemUrl,
            ':cpf' => $cpf
        ]);
    }

    // Fecha a conexão
    $conn = null;

    // Redireciona após o cadastro
    echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href = window.location.href;</script>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cadastro de Novo Usuário</title>
        <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        header {
            background-color: #3498db;
            padding: 20px;
            color: white;
            text-align: center;
            /* Centraliza o texto do cabeçalho */
        }

        main {
            width: 80%;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            margin-top: 0;
        }

        label {
            display: block;
            margin-top: 10px;
            position: relative;
            padding-right: 20px;
        }

        label.required::after {
            content: "*";
            color: red;
            position: absolute;
            right: 0;
            top: 0;
        }

        input[type="text"],
        input[type="date"],
        input[type="email"],
        input[type="tel"],
        input[type="password"],
        input[type="file"],
        button {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background-color: #3498db;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        button:hover {
            background-color: #407091;
            transform: scale(1.05);
        }

        /* Estiliza todos os botões do tipo reset */
        button[type='reset'] {
            background-color: #8a8a8a;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        /* Botão ao passar o mouse */
        .btn:hover,
        button[type='reset']:hover {
            background-color: rgb(179, 179, 179);
            /* Altera a cor de fundo ao passar o mouse */
            transform: scale(1.05);
            /* Aumenta ligeiramente o tamanho do botão */
        }

        /* Botão ao receber foco */
        .btn:focus,
        button[type='reset']:focus {
            outline: none;
            /* Remove o contorno padrão de foco */
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
            /* Adiciona uma sombra ao redor do botão */
        }

        .endereco {
            margin-top: 20px;
        }

        .endereco.hidden {
            display: none;
        }

        .optional {
            color: #666;
            font-style: italic;
        }

        .required {
            color: red;
            font-weight: bold;
        }
        </style>
    </head>

    <body>
        <header>
            <h1>Adote um Pet</h1>
            <p>Cuidados e adoção de pets pela Vigilância Sanitária</p>
            <p>PREFEITURA MUNICIPAL DE IMACULADA - PB</p>
            <hr><br>
            <h1>Cadastro de Novo Usuário</h1>
        </header>

        <main>
            <form method="post" enctype="multipart/form-data" onsubmit="return validarFormulario()">
                <label for="cpf" class="required">CPF:</label>
                <input type="text" id="cpf" name="cpf" required pattern="\d{11}" maxlength="11"
                    placeholder="Digite apenas números">

                <label for="nome" class="required">Nome:</label>
                <input type="text" id="nome" name="nome" required maxlength="255">

                <label for="data_nascimento" class="required">Data de Nascimento:</label>
                <input type="date" id="data_nascimento" name="data_nascimento" required>

                <label for="email" class="optional">Email:</label>
                <input type="email" id="email" name="email" maxlength="255">

                <label for="telefone" class="required">Telefone:</label>
                <input type="tel" id="telefone" name="telefone" required pattern="\d{11}" maxlength="11"
                    placeholder="Digite apenas números">

                <label for="senha" class="required">Senha:</label>
                <input type="password" id="senha" name="senha" required maxlength="255">

                <label for="imagem">Foto do Perfil:</label>
                <input type="file" id="imagem" name="imagem" accept="image/*">

                <button type="button" onclick="toggleEndereco()">Adicionar Endereço</button>

                <div class="endereco hidden" id="endereco">
                    <h2>Endereço</h2>

                    <label for="rua" class="required">Rua:</label>
                    <input type="text" id="rua" name="rua" maxlength="255">

                    <label for="numero">Número:</label>
                    <input type="text" id="numero" name="numero" maxlength="10">

                    <label for="bairro" class="required">Bairro:</label>
                    <input type="text" id="bairro" name="bairro" maxlength="255">

                    <label for="cep" class="required">CEP:</label>
                    <input type="text" id="cep" name="cep" maxlength="10">

                    <label for="referencia">Referência:</label>
                    <input type="text" id="referencia" name="referencia" maxlength="255">

                    <label for="cidade" class="required">Cidade:</label>
                    <input type="text" id="cidade" name="cidade" maxlength="255">

                    <label for="estado" class="required">Estado:</label>
                    <input type="text" id="estado" name="estado" maxlength="2">
                </div>

                <hr>
                <button type="reset">Limpar Formulário</button>
                <button type="submit">Cadastrar</button>
            </form>
        </main>

        <script>
        // Função para validar o formulário
        function validarFormulario() {
            const cpf = document.getElementById('cpf').value;
            const telefone = document.getElementById('telefone').value;
            const senha = document.getElementById('senha').value;
            const rua = document.getElementById('rua');
            const bairro = document.getElementById('bairro');
            const cep = document.getElementById('cep');
            const cidade = document.getElementById('cidade');
            const estado = document.getElementById('estado');

            // Valida CPF (11 dígitos)
            if (cpf.length !== 11) {
                alert('O CPF deve ter 11 dígitos.');
                return false;
            }

            // Valida telefone (11 dígitos)
            if (telefone.length !== 11) {
                alert('O telefone deve ter 11 dígitos.');
                return false;
            }

            // Valida senha
            if (senha.length < 6) {
                alert('A senha deve ter pelo menos 6 caracteres.');
                return false;
            }

            // Valida endereço se algum campo estiver preenchido
            if (rua.value || bairro.value || cep.value || cidade.value || estado.value) {
                if (!rua.value || !bairro.value || !cep.value || !cidade.value || !estado.value) {
                    alert('Todos os campos de endereço são obrigatórios.');
                    return false;
                }
            }

            return true;
        }

        // Função para alternar a visibilidade dos campos de endereço
        function toggleEndereco() {
            const enderecoDiv = document.getElementById('endereco');
            enderecoDiv.classList.toggle('hidden');
        }
        </script>
    </body>

</html>