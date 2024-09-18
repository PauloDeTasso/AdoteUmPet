<?php
include 'conexao_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = conectar();

    $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_STRING);
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $dataNascimento = filter_input(INPUT_POST, 'data_nascimento', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
    $senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);
    $rua = filter_input(INPUT_POST, 'rua', FILTER_SANITIZE_STRING);
    $numero = filter_input(INPUT_POST, 'numero', FILTER_SANITIZE_STRING);
    $bairro = filter_input(INPUT_POST, 'bairro', FILTER_SANITIZE_STRING);
    $cep = filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_STRING);
    $referencia = filter_input(INPUT_POST, 'referencia', FILTER_SANITIZE_STRING);
    $cidade = filter_input(INPUT_POST, 'cidade', FILTER_SANITIZE_STRING);
    $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);

    // Verifica se o arquivo de imagem foi enviado
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $imagemNome = basename($_FILES['imagem']['name']);
        $imagemPath = 'imagens/vigilantes/' . $imagemNome;

        // Move o arquivo para o diretório correto
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $imagemPath)) {
            $imagemUrl = $imagemPath;
        } else {
            echo "Erro ao mover o arquivo para o diretório.";
            $imagemUrl = null;
        }
    } else {
        $imagemUrl = null;
    }

    // Insere o usuário
    $sql = "INSERT INTO Usuario (cpf, nome, data_nascimento, email, telefone, status, senha, fk_Permissao_id) 
            VALUES (:cpf, :nome, :data_nascimento, :email, :telefone, 'ATIVO', :senha, (SELECT id FROM Permissao WHERE tipo = 'Administrador'))";
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
    if ($rua && $bairro && $cep && $cidade && $estado) {
        $sqlEndereco = "INSERT INTO Endereco (rua, numero, bairro, cep, referencia, cidade, estado) 
                        VALUES (:rua, :numero, :bairro, :cep, :referencia, :cidade, :estado)";
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
    if ($imagemUrl) {
        $sqlImagem = "INSERT INTO Imagem_Usuario (url_imagem, fk_Usuario_cpf) VALUES (:imagem_url, :cpf)";
        $stmtImagem = $conn->prepare($sqlImagem);
        $stmtImagem->execute([
            ':imagem_url' => $imagemUrl,
            ':cpf' => $cpf
        ]);
    }

    $conn = null;

    echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href = window.location.href;</script>";
}
?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Novo Usuário</title>
    <link rel="stylesheet" href="css/usuario/usuario_cadastrar_se.css">
    <style>
    .hidden {
        display: none;
    }
    </style>
</head>

<body>
    <?php include 'cabecalho.php'; ?>

    <main>
        <form method="post" enctype="multipart/form-data" onsubmit="return validarFormulario()">
            <label for="cpf" class="required">CPF:</label>
            <input type="text" id="cpf" name="cpf" required pattern="\d{11}" maxlength="11"
                placeholder="Digite apenas números">

            <label for="nome" class="required">Nome:</label>
            <input type="text" id="nome" name="nome" required maxlength="255" placeholder="Digite seu nome completo">

            <label for="data_nascimento" class="required">Data de Nascimento:</label>
            <input type="date" id="data_nascimento" name="data_nascimento" required>

            <label for="email" class="optional">Email:</label>
            <input type="email" id="email" name="email" maxlength="255" placeholder="exemplo@dominio.com">

            <label for="telefone" class="required">Telefone:</label>
            <input type="tel" id="telefone" name="telefone" required pattern="\d{11}" maxlength="11"
                placeholder="Digite apenas números">

            <label for="senha" class="required">Senha:</label>
            <input type="password" id="senha" name="senha" required maxlength="255" placeholder="Digite a senha">

            <label for="imagem">Foto do Perfil:</label>
            <input type="file" id="imagem" name="imagem" accept="image/*">

            <button type="button" onclick="toggleEndereco()">Adicionar Endereço</button>

            <div class="endereco hidden" id="endereco">
                <h2>Endereço</h2>

                <label for="rua">Rua:</label>
                <input type="text" id="rua" name="rua" maxlength="255" placeholder="Rua da Residência">

                <label for="numero">Número:</label>
                <input type="text" id="numero" name="numero" maxlength="10" placeholder="Número do Imóvel">

                <label for="bairro" class="required">Bairro:</label>
                <input type="text" id="bairro" name="bairro" maxlength="255" placeholder="Bairro">

                <label for="cep">CEP:</label>
                <input type="text" id="cep" name="cep" maxlength="10" placeholder="CEP">

                <label for="referencia">Referência:</label>
                <input type="text" id="referencia" name="referencia" maxlength="255"
                    placeholder="Referência (opcional)">

                <label for="cidade" class="required">Cidade:</label>
                <input type="text" id="cidade" name="cidade" maxlength="255" placeholder="Cidade">

                <label for="estado" class="required">Estado:</label>
                <input type="text" id="estado" name="estado" maxlength="2" placeholder="UF">
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
                alert('Todos os campos do endereço devem ser preenchidos se um deles for fornecido.');
                return false;
            }
        }
        return true;
    }

    // Função para alternar a visibilidade do formulário de endereço
    function toggleEndereco() {
        const enderecoDiv = document.getElementById('endereco');
        enderecoDiv.classList.toggle('hidden');
    }
    </script>