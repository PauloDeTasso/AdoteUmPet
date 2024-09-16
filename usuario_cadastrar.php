<?php
session_start();
require 'conexao_db.php'; 

// Função para validar o CPF
function validarCPF($cpf)
{
    $cpf = preg_replace('/\D/', '', $cpf);

    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf))
    {
        return false;
    }

    for ($t = 9; $t < 11; $t++)
    {
        for ($d = 0, $c = 0; $c < $t; $c++)
        {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d)
        {
            return false;
        }
    }
    return true;
}

// Função para validar telefone
function validarTelefone($telefone)
{
    return preg_match('/^\d{11}$/', $telefone);
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    // Captura os dados do formulário
    $cpf = $_POST['cpf'];
    $nome = $_POST['nome'];
    $dataNascimento = $_POST['data_nascimento'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $status = $_POST['status'];
    $senha = $_POST['senha'];
    $fkPermissaoId = $_POST['fk_Permissao_id'];

    // Captura dados de endereço (opcional)
    $rua = !empty($_POST['rua']) ? $_POST['rua'] : null;
    $numero = !empty($_POST['numero']) ? $_POST['numero'] : null;
    $bairro = !empty($_POST['bairro']) ? $_POST['bairro'] : null;
    $cep = !empty($_POST['cep']) ? $_POST['cep'] : null;
    $referencia = !empty($_POST['referencia']) ? $_POST['referencia'] : null;
    $cidade = !empty($_POST['cidade']) ? $_POST['cidade'] : null;
    $estado = !empty($_POST['estado']) ? $_POST['estado'] : null;

    // Validação do CPF e Telefone
    if (!validarCPF($cpf))
    {
        $_SESSION['erro'] = "CPF inválido!";
        header('Location: usuario_cadastrar.php');
        exit();
    }

    if (!validarTelefone($telefone))
    {
        $_SESSION['erro'] = "Telefone inválido! Insira um número com 11 dígitos.";
        header('Location: usuario_cadastrar.php');
        exit();
    }

    // Prepara e executa a inserção do usuário
    try
    {
        $conn->beginTransaction();

        // Verifica se o usuário está autenticado e é um administrador
        $usuarioAutenticado = $_SESSION['usuario_cpf'] ?? null;
        $sqlPermissao = "SELECT fk_Permissao_id FROM Usuario WHERE cpf = :cpf";
        $stmtPermissao = $conn->prepare($sqlPermissao);
        $stmtPermissao->execute([':cpf' => $usuarioAutenticado]);
        $permAutenticado = $stmtPermissao->fetchColumn();

        if ($usuarioAutenticado && $permAutenticado == 1) // Considerando que o id 1 é para Administrador
        {
            // Inserir o usuário com permissão escolhida
            $sqlUsuario = "INSERT INTO Usuario (cpf, nome, data_nascimento, email, telefone, status, senha, fk_Permissao_id)
                           VALUES (:cpf, :nome, :data_nascimento, :email, :telefone, :status, :senha, :fk_Permissao_id)";
            $stmtUsuario = $conn->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':cpf' => $cpf,
                ':nome' => $nome,
                ':data_nascimento' => $dataNascimento,
                ':email' => $email,
                ':telefone' => $telefone,
                ':status' => $status,
                ':senha' => $senha,
                ':fk_Permissao_id' => $fkPermissaoId
            ]);
        }
        else
        {
            // Novo usuário: não pode escolher permissão
            $sqlUsuario = "INSERT INTO Usuario (cpf, nome, data_nascimento, email, telefone, status, senha)
                           VALUES (:cpf, :nome, :data_nascimento, :email, :telefone, :status, :senha)";
            $stmtUsuario = $conn->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':cpf' => $cpf,
                ':nome' => $nome,
                ':data_nascimento' => $dataNascimento,
                ':email' => $email,
                ':telefone' => $telefone,
                ':status' => $status,
                ':senha' => $senha
            ]);
        }

        // Inserir endereço, se foi fornecido
        if ($rua && $bairro && $cidade && $estado)
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

            // Pegar o ID do endereço recém-criado
            $enderecoId = $conn->lastInsertId();

            // Vincular o usuário ao endereço
            $sqlEndUsuario = "INSERT INTO Enderecos_Usuarios (fk_Usuario_cpf, fk_Endereco_id)
                              VALUES (:cpf, :endereco_id)";
            $stmtEndUsuario = $conn->prepare($sqlEndUsuario);
            $stmtEndUsuario->execute([
                ':cpf' => $cpf,
                ':endereco_id' => $enderecoId
            ]);
        }

        $conn->commit();
        $_SESSION['sucesso'] = "Usuário cadastrado com sucesso!";
        header('Location: usuario_cadastrar.php');
    }
    catch (Exception $e)
    {
        $conn->rollBack();
        $_SESSION['erro'] = "Erro ao cadastrar usuário: " . $e->getMessage();
        header('Location: usuario_cadastrar.php');
    }
}
?>

<!-- HTML Formulário de Cadastro -->
<!DOCTYPE html>
<html lang="pt-br">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cadastro de Usuário</title>
        <script>
        function verificarStatus() {
            var status = document.getElementById('status').value;
            var permDiv = document.getElementById('permissao_div');
            if (status === 'administrador') {
                permDiv.style.display = 'block';
            } else {
                permDiv.style.display = 'none';
                document.getElementById('fk_Permissao_id').value = '';
            }
        }
        </script>
    </head>

    <body>

        <h2>Cadastro de Usuário</h2>

        <?php
    if (isset($_SESSION['erro']))
    {
        echo '<p style="color:red;">' . $_SESSION['erro'] . '</p>';
        unset($_SESSION['erro']);
    }

    if (isset($_SESSION['sucesso']))
    {
        echo '<p style="color:green;">' . $_SESSION['sucesso'] . '</p>';
        unset($_SESSION['sucesso']);
    }
    ?>

        <form action="usuario_cadastrar.php" method="POST">
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" required maxlength="11"><br>

            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required><br>

            <label for="data_nascimento">Data de Nascimento:</label>
            <input type="date" id="data_nascimento" name="data_nascimento" required><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email"><br>

            <label for="telefone">Telefone (com DDD):</label>
            <input type="text" id="telefone" name="telefone" required maxlength="11"><br>

            <label for="status">Status:</label>
            <select id="status" name="status" onchange="verificarStatus()">
                <option value="adotante" selected>Adotante</option>
                <option value="administrador">Administrador</option>
            </select><br>

            <div id="permissao_div" style="display:none;">
                <label for="fk_Permissao_id">Permissão:</label>
                <select id="fk_Permissao_id" name="fk_Permissao_id">
                    <!-- Opções de permissões devem ser carregadas do banco de dados --> <?php $sqlPermissoes = "SELECT id, descricao FROM Permissao";
                                                                                        $stmtPermissoes = $conn->prepare($sqlPermissoes);
                                                                                        $stmtPermissoes->execute();
                                                                                        while ($row = $stmtPermissoes->fetch(PDO::FETCH_ASSOC))
                                                                                        {
                                                                                            echo "<option value='{$row['id']}'>{$row['descricao']}</option>";
                                                                                        } ?>
                </select>
            </div> <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required><br>

            <!-- Endereço (opcional) -->
            <h3>Endereço (opcional)</h3>
            <label for="rua">Rua:</label>
            <input type="text" id="rua" name="rua"><br>

            <label for="numero">Número:</label>
            <input type="text" id="numero" name="numero"><br>

            <label for="bairro">Bairro:</label>
            <input type="text" id="bairro" name="bairro"><br>

            <label for="cep">CEP:</label>
            <input type="text" id="cep" name="cep"><br>

            <label for="referencia">Referência:</label>
            <input type="text" id="referencia" name="referencia"><br>

            <label for="cidade">Cidade:</label>
            <input type="text" id="cidade" name="cidade"><br>

            <label for="estado">Estado:</label>
            <input type="text" id="estado" name="estado"><br>

            <input type="submit" value="Cadastrar">
        </form>
    </body>

</html>