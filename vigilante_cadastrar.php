<?php
include 'conexao_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    // Validações específicas de entrada
    $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_STRING);
    if (strlen($cpf) !== 11 || !ctype_digit($cpf))
    {
        echo "<script>alert('O CPF deve ter exatamente 11 dígitos.');</script>";
        exit;
    }

    $cep = filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_STRING);
    if (!preg_match('/^\d{8}$/', $cep))
    {
        echo "<script>alert('O CEP deve estar no formato correto.');</script>";
        exit;
    }

    // Conexão com PDO
    try
    {
        $conn = conectar();

        // Sanitização de entradas
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
        $dataNascimento = filter_input(INPUT_POST, 'data_nascimento', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
        $senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);
        $rua = filter_input(INPUT_POST, 'rua', FILTER_SANITIZE_STRING);
        $numero = filter_input(INPUT_POST, 'numero', FILTER_SANITIZE_STRING);
        $bairro = filter_input(INPUT_POST, 'bairro', FILTER_SANITIZE_STRING);
        $referencia = filter_input(INPUT_POST, 'referencia', FILTER_SANITIZE_STRING);
        $cidade = filter_input(INPUT_POST, 'cidade', FILTER_SANITIZE_STRING);
        $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);

        // Verifica se o arquivo de imagem foi enviado
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK)
        {
            $imagemNome = basename($_FILES['imagem']['name']);
            $imagemPath = 'imagens/vigilantes/' . $imagemNome;

            // Move o arquivo para o diretório correto
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $imagemPath))
            {
                $imagemUrl = $imagemPath;
            }
            else
            {
                throw new Exception("Erro ao mover o arquivo para o diretório.");
            }
        }
        else
        {
            $imagemUrl = null;
        }

        // Insere o usuário
        $conn->beginTransaction();
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
        if ($rua && $bairro && $cep && $cidade && $estado)
        {
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
        if ($imagemUrl)
        {
            $sqlImagem = "INSERT INTO Imagem_Usuario (url_imagem, fk_Usuario_cpf) VALUES (:imagem_url, :cpf)";
            $stmtImagem = $conn->prepare($sqlImagem);
            $stmtImagem->execute([
                ':imagem_url' => $imagemUrl,
                ':cpf' => $cpf
            ]);
        }

        $conn->commit();
        echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href = 'home.php';</script>";
    }
    catch (PDOException $e)
    {
        $conn->rollBack();
        echo "Erro no banco de dados: " . $e->getMessage();
    }
    catch (Exception $e)
    {
        echo "Erro: " . $e->getMessage();
    }
    finally
    {
        $conn = null;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Novo Usuário</title>
    <link rel="stylesheet" href="css/vigilante/vigilante_cadastrar.css">

    <script>
        function validarFormulario() {
            let cpf = document.getElementById('cpf').value;
            let telefone = document.getElementById('telefone').value;
            let senha = document.getElementById('senha').value;
            let confirmacaoSenha = document.getElementById('confirmacao_senha').value;
            let email = document.getElementById('email').value;
            let cep = document.getElementById('cep').value;

            // Validação de CPF
            if (cpf.length !== 11 || isNaN(cpf)) {
                alert('CPF deve ter exatamente 11 dígitos.');
                return false;
            }

            // Validação de Telefone
            if (telefone.length !== 11 || isNaN(telefone)) {
                alert('Telefone deve ter exatamente 11 dígitos.');
                return false;
            }

            // Validação de Senha
            if (senha.length < 6) {
                alert('A senha deve ter pelo menos 6 caracteres.');
                return false;
            }

            // Validação de Confirmação de Senha
            if (senha !== confirmacaoSenha) {
                alert('As senhas não coincidem.');
                return false;
            }

            // Validação de Email
            let regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!regexEmail.test(email)) {
                alert('Insira um email válido.');
                return false;
            }

            // Validação de CEP
            let regexCep = /^\d{8}$/;
            if (!regexCep.test(cep)) {
                alert('Insira um CEP válido no formato 12345678.');
                return false;
            }

            return true;
        }

        function toggleEndereco() {
            let enderecoDiv = document.getElementById('endereco');
            enderecoDiv.classList.toggle('hidden');
        }
    </script>
</head>

<body>

    <?php include 'cabecalho.php'; ?>

    <section class="cabecalho">
        <h3>Cadastro de Vigilante Sanitário</h3>
    </section>

    <main>
        <form method="post" enctype="multipart/form-data" onsubmit="return validarFormulario()">
            <fieldset>
                <legend>Informações Pessoais</legend>

                <label for="cpf">CPF</label>
                <input type="text" id="cpf" name="cpf" placeholder="Digite o CPF (somente números)" maxlength="11"
                    required>

                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" placeholder="Digite o nome completo" required>

                <label for="data_nascimento">Data de Nascimento</label>
                <input type="date" id="data_nascimento" name="data_nascimento" required>

                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="Digite o e-mail" required>

                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" placeholder="Digite o telefone (somente números)"
                    maxlength="11" required>

                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Digite a senha" required>

                <label for="confirmacao_senha">Confirme a Senha</label>
                <input type="password" id="confirmacao_senha" name="confirmacao_senha"
                    placeholder="Confirme a senha" required>
            </fieldset>

            <button type="button" class="toggle-endereco" onclick="toggleEndereco()">Adicionar Endereço</button>

            <fieldset id="endereco" class="hidden">
                <legend>Endereço</legend>

                <label for="rua">Rua</label>
                <input type="text" id="rua" name="rua" placeholder="Digite o nome da rua">

                <label for="numero">Número</label>
                <input type="text" id="numero" name="numero" placeholder="Digite o número da casa ou apartamento">

                <label for="bairro">Bairro</label>
                <input type="text" id="bairro" name="bairro" placeholder="Digite o bairro">

                <label for="cep">CEP</label>
                <input type="text" id="cep" name="cep" placeholder="Digite o CEP (00000-000)" maxlength="9">

                <label for="referencia">Referência</label>
                <input type="text" id="referencia" name="referencia" placeholder="Ponto de referência">

                <label for="cidade">Cidade</label>
                <input type="text" id="cidade" name="cidade" placeholder="Digite a cidade">

                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="">Selecione o estado</option>
                    <option value="AC">Acre</option>
                    <option value="AL">Alagoas</option>
                    <option value="AP">Amapá</option>
                    <option value="AM">Amazonas</option>
                    <option value="BA">Bahia</option>
                    <option value="CE">Ceará</option>
                    <option value="DF">Distrito Federal</option>
                    <option value="ES">Espírito Santo</option>
                    <option value="GO">Goiás</option>
                    <option value="MA">Maranhão</option>
                    <option value="MT">Mato Grosso</option>
                    <option value="MS">Mato Grosso do Sul</option>
                    <option value="MG">Minas Gerais</option>
                    <option value="PA">Pará</option>
                    <option value="PB">Paraíba</option>
                    <option value="PR">Paraná</option>
                    <option value="PE">Pernambuco</option>
                    <option value="PI">Piauí</option>
                    <option value="RJ">Rio de Janeiro</option>
                    <option value="RN">Rio Grande do Norte</option>
                    <option value="RS">Rio Grande do Sul</option>
                    <option value="RO">Rondônia</option>
                    <option value="RR">Roraima</option>
                    <option value="SC">Santa Catarina</option>
                    <option value="SP">São Paulo</option>
                    <option value="SE">Sergipe</option>
                    <option value="TO">Tocantins</option>
                </select>
            </fieldset>

            <fieldset>
                <legend>Imagem de Perfil</legend>
                <label for="imagem">Carregar Imagem</label>
                <input type="file" id="imagem" name="imagem" accept="image/*">
            </fieldset>

            <button type="submit">Cadastrar</button>
        </form>
    </main>
</body>

</html>