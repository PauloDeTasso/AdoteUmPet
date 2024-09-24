<?php
require_once 'conexao_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    try
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

        // Lista de estados válidos
        $estadosValidos = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];

        // Valida se o estado está na lista de estados válidos
        if (!in_array($estado, $estadosValidos))
        {
            echo "<script>alert('Estado inválido!'); window.location.href = 'usuario_cadastre_se.php';</script>";
            exit;
        }

        // Verifica se o arquivo de imagem foi enviado
        $imagemUrl = null;
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK)
        {
            $diretorioImagens = 'imagens/usuarios/';
            $nomeImagemOriginal = basename($_FILES['imagem']['name']);
            $extensaoImagem = strtolower(pathinfo($nomeImagemOriginal, PATHINFO_EXTENSION));

            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($extensaoImagem, $extensoesPermitidas))
            {
                echo "Tipo de imagem não permitido.";
                exit;
            }

            $imagemNome = uniqid() . '-' . $nomeImagemOriginal;
            $imagemPath = $diretorioImagens . $imagemNome;

            while (file_exists($imagemPath))
            {
                $imagemNome = uniqid() . '-' . $nomeImagemOriginal;
                $imagemPath = $diretorioImagens . $imagemNome;
            }

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
        }

        // Tenta inserir o usuário
        $sql = "INSERT INTO Usuario (cpf, nome, data_nascimento, email, telefone, status, senha, fk_Permissao_id) 
                VALUES (:cpf, :nome, :data_nascimento, :email, :telefone, 'ATIVO', :senha, 2)";
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

            $sqlEnderecosUsuarios = "INSERT INTO Enderecos_Usuarios (fk_Usuario_cpf, fk_Endereco_id) 
                                     VALUES (:cpf, :endereco_id)";
            $stmtEnderecosUsuarios = $conn->prepare($sqlEnderecosUsuarios);
            $stmtEnderecosUsuarios->execute([
                ':cpf' => $cpf,
                ':endereco_id' => $enderecoId
            ]);
        }

        // Insere a imagem do usuário, se houver
        if ($imagemUrl)
        {
            $sqlImagem = "INSERT INTO Imagem_Usuario (url_imagem, fk_Usuario_cpf) 
                          VALUES (:imagem_url, :cpf)";
            $stmtImagem = $conn->prepare($sqlImagem);
            $stmtImagem->execute([
                ':imagem_url' => $imagemUrl,
                ':cpf' => $cpf
            ]);
        }

        echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href = 'login.php';</script>";
    }
    catch (PDOException $e)
    {
        // Verifica se o erro é de chave duplicada
        if ($e->getCode() == 23505)
        {
            echo "<script>alert('CPF já cadastrado!'); window.location.href = 'usuario_cadastre_se.php';</script>";
        }
        else
        {
            echo "Erro ao cadastrar usuário: " . $e->getMessage();
        }
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
    <link rel="stylesheet" href="css/usuario/usuario_cadastre_se.css">
</head>

<body>
    <?php include 'cabecalho3.php'; ?>

    <section class="cabecalho">
        <h3>Cadastre-se</h3>
    </section>

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

            <!-- Campo de Senha -->
            <label for="senha" class="required">Senha:</label>
            <div class="password-wrapper">
                <input type="password" id="senha" name="senha" required maxlength="255">
                <span class="toggle-visibility" onclick="toggleSenhaVisibilidade('senha', 'olhoSenha')">
                    <img id="olhoSenha" src="imagens/sistema/icones/olho_aberto_senha.webp" alt="Mostrar senha"
                        width="20px">
                </span>
            </div>

            <!-- Campo de Confirmar Senha -->
            <label for="confirmar_senha" class="required">Confirmar Senha:</label>
            <div class="password-wrapper">
                <input type="password" id="confirmar_senha" name="confirmar_senha" required maxlength="255">
                <span class="toggle-visibility"
                    onclick="toggleSenhaVisibilidade('confirmar_senha', 'olhoConfirmarSenha')">
                    <img id="olhoConfirmarSenha" src="imagens/sistema/icones/olho_aberto_senha.webp"
                        alt="Mostrar senha" width="20px">
                </span>
            </div>

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
                <label for="cep">CEP:</label>
                <input type="text" id="cep" name="cep" maxlength="10">
                <label for="referencia">Referência:</label>
                <input type="text" id="referencia" name="referencia" maxlength="255">
                <label for="cidade" class="required">Cidade:</label>
                <input type="text" id="cidade" name="cidade" maxlength="255">
                <label for="estado" class="required">Estado:</label>
                <select id="estado" name="estado" required>
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
            </div>

            <hr>
            <button type="reset">Limpar Formulário</button>
            <button type="submit">Cadastrar</button>
        </form>
    </main>

    <script>
        // Função para alternar a visibilidade da senha
        function toggleSenhaVisibilidade(campoId, iconeId) {
            const campoSenha = document.getElementById(campoId);
            const olhoSenha = document.getElementById(iconeId);

            if (campoSenha.type === 'password') {
                campoSenha.type = 'text';
                olhoSenha.src = 'imagens/sistema/icones/olho_aberto_senha.webp';
            } else {
                campoSenha.type = 'password';
                olhoSenha.src = 'imagens/sistema/icones/olho_fechado_senha.png';
            }
        }

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

            // Valida endereço se o campo for visível
            if (!document.getElementById('endereco').classList.contains('hidden')) {
                if (!rua.value || !bairro.value || !cep.value || !cidade.value || !estado.value) {
                    alert('Todos os campos do endereço devem ser preenchidos.');
                    return false;
                }
            }

            if (senha !== confirmarSenha) {
                alert("As senhas não coincidem!");
                return false;
            }

            return true;
        }

        // Função para mostrar/ocultar o campo de endereço
        function toggleEndereco() {
            const endereco = document.getElementById('endereco');
            endereco.classList.toggle('hidden');
        }
    </script>

    <?php include 'rodape.php'; ?>

</body>

</html>