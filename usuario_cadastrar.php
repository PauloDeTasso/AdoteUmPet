<?php
include_once "start.php";

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

    // Lista de estados válidos
    $estadosValidos = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];

    // Valida se o estado está na lista de estados válidos
    if (!in_array($estado, $estadosValidos))
    {
        echo "<script>alert('Estado inválido!'); window.location.href = 'usuario_cadastrar.php';</script>";
        exit; // Impede a continuação da execução
    }

    // Verifica se o arquivo de imagem foi enviado
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK)
    {
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));

        if (in_array($extensao, $extensoesPermitidas))
        {
            $imagemNome = uniqid() . '-' . basename($_FILES['imagem']['name']);
            $imagemPath = 'imagens/usuarios/' . $imagemNome;

            if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $imagemPath))
            {
                echo "Erro ao mover o arquivo para o diretório.";
                $imagemUrl = null;
            }
            else
            {
                $imagemUrl = $imagemPath;
            }
        }
        else
        {
            echo "A extensão da imagem não é permitida.";
            $imagemUrl = null;
        }
    }
    else
    {
        echo "Erro no envio do arquivo de imagem.";
        $imagemUrl = null;
    }

    // Tenta inserir os dados do usuário
    try
    {
        $conn->beginTransaction();

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

        // Verifica se os campos de endereço foram preenchidos antes de tentar inserir
        if (!empty($rua) && !empty($bairro) && !empty($cep) && !empty($cidade) && !empty($estado))
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

        $conn->commit();
        echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href = 'usuarios.php';</script>";
    }
    catch (PDOException $e)
    {
        $conn->rollBack();

        if ($e->getCode() === '23505')
        {
            echo "<script>alert('CPF já cadastrado!'); window.location.href = 'usuario_cadastrar.php';</script>";
        }
        else
        {
            echo "<script>alert('Erro no cadastro: " . $e->getMessage() . "'); window.location.href = 'usuario_cadastrar.php';</script>";
        }
    }

    $conn = null;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Novo Usuário</title>
    <link rel="stylesheet" href="css/usuario/usuario_cadastrar.css">
</head>

<body>
    <?php include 'cabecalho.php'; ?>

    <section class="cabecalho">
        <h3>Cadastrar Adotante</h3>
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

            <button type="button" id="adicionarEndereco" onclick="toggleEndereco()">Adicionar Endereço</button>

            <div id="enderecoSection" style="display: none;">
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

        function validarFormulario() {
            const cpf = document.getElementById('cpf').value;
            const nome = document.getElementById('nome').value;
            const dataNascimento = document.getElementById('data_nascimento').value;
            const telefone = document.getElementById('telefone').value;
            const senha = document.getElementById('senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha').value;

            // Validação de campos obrigatórios básicos
            if (!cpf || !nome || !dataNascimento || !telefone || !senha || !confirmarSenha) {
                alert('Por favor, preencha todos os campos obrigatórios.');
                return false;
            }

            // Verifica se as senhas coincidem
            if (senha !== confirmarSenha) {
                alert('As senhas não coincidem.');
                return false;
            }

            // Validação dos campos de endereço se o endereço estiver visível
            if (enderecoVisivel) {
                const rua = document.getElementById('rua').value;
                const bairro = document.getElementById('bairro').value;
                const cep = document.getElementById('cep').value;
                const cidade = document.getElementById('cidade').value;
                const estado = document.getElementById('estado').value;

                if (!rua || !bairro || !cep || !cidade || !estado) {
                    alert('Por favor, preencha todos os campos de endereço.');
                    return false;
                }
            }

            return true; // Se todas as validações forem bem-sucedidas
        }

        // Variável de controle para verificar se o endereço será adicionado
        let enderecoVisivel = false;

        function toggleEndereco() {
            const enderecoSection = document.getElementById('enderecoSection');

            // Exibe ou oculta os campos de endereço
            if (!enderecoVisivel) {
                enderecoSection.style.display = 'block';
                enderecoVisivel = true;
            } else {
                enderecoSection.style.display = 'none';
                enderecoVisivel = false;

                // Limpa os valores dos campos de endereço
                document.getElementById('rua').value = '';
                document.getElementById('numero').value = '';
                document.getElementById('bairro').value = '';
                document.getElementById('cep').value = '';
                document.getElementById('referencia').value = '';
                document.getElementById('cidade').value = '';
                document.getElementById('estado').value = '';
            }
        }
    </script>

    <?php include 'rodape.php'; ?>

</body>

</html>