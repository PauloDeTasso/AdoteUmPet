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
    $soma = 0;
    for ($i = 0; $i < 9; $i++)
    {
        $soma += $cpf[$i] * (10 - $i);
    }
    $resto = $soma % 11;
    $digito1 = ($resto < 2) ? 0 : 11 - $resto;

    $soma = 0;
    for ($i = 0; $i < 10; $i++)
    {
        $soma += $cpf[$i] * (11 - $i);
    }
    $resto = $soma % 11;
    $digito2 = ($resto < 2) ? 0 : 11 - $resto;

    return $cpf[9] == $digito1 && $cpf[10] == $digito2;
}

// Função para validar o telefone
function validarTelefone($telefone)
{
    $telefone = preg_replace('/\D/', '', $telefone);
    return strlen($telefone) >= 10 && strlen($telefone) <= 11;
}

// Verifica se o usuário está logado e se é administrador
$isAdmin = isset($_SESSION['cpf']) && $_SESSION['tipo'] === 'ADMINISTRADOR';

// Caso o usuário não esteja logado, redireciona para login
if (!$isAdmin && !isset($_SESSION['cpf']))
{
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $cpf = $_POST['cpf'];
    $nome = $_POST['nome'];
    $data_nascimento = $_POST['data_nascimento'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $status = $isAdmin ? $_POST['status'] : 'Adotante'; // Se for admin, usa o valor do POST, senão define como 'Adotante'
    $senha = $_POST['senha'];
    $fk_Permissao_id = $isAdmin ? $_POST['fk_Permissao_id'] : 2; // 2 é a permissão padrão para 'Adotante'

    // Valida os campos
    if (!validarCPF($cpf))
    {
        echo "<p>CPF inválido. Por favor, insira um CPF válido.</p>";
    }
    elseif (!validarTelefone($telefone))
    {
        echo "<p>Telefone inválido. O telefone deve conter 10 ou 11 dígitos.</p>";
    }
    else
    {
        try
        {
            $pdo = conectar();
            $sql = 'INSERT INTO Usuario (cpf, nome, data_nascimento, email, telefone, status, senha, fk_Permissao_id, data_cadastro) 
                    VALUES (:cpf, :nome, :data_nascimento, :email, :telefone, :status, :senha, :fk_Permissao_id, CURRENT_DATE)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':cpf' => $cpf,
                ':nome' => $nome,
                ':data_nascimento' => $data_nascimento,
                ':email' => $email,
                ':telefone' => $telefone,
                ':status' => $status,
                ':senha' => $senha, // Sem hash para a senha
                ':fk_Permissao_id' => $fk_Permissao_id
            ]);

            // Redireciona para a lista de usuários após o cadastro
            header('Location: usuarios.php');
            exit;
        }
        catch (PDOException $e)
        {
            die("Erro ao cadastrar o usuário: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Adicionar Usuário</title>
        <link rel="stylesheet" href="css/usuario/usuario_cadastrar.css">
        <script>
        // Função para validar CPF
        function validarCPF(cpf) {
            cpf = cpf.replace(/\D+/g, '');
            if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
                return false;
            }
            let soma = 0;
            for (let i = 0; i < 9; i++) {
                soma += cpf[i] * (10 - i);
            }
            let resto = (soma * 10) % 11;
            if (resto === 10 || resto === 11) resto = 0;
            if (resto !== parseInt(cpf[9])) return false;
            soma = 0;
            for (let i = 0; i < 10; i++) {
                soma += cpf[i] * (11 - i);
            }
            resto = (soma * 10) % 11;
            if (resto === 10 || resto === 11) resto = 0;
            return resto === parseInt(cpf[10]);
        }

        // Função para validar telefone
        function validarTelefone(telefone) {
            telefone = telefone.replace(/\D+/g, '');
            return telefone.length === 10 || telefone.length === 11;
        }

        // Função para validar o formulário
        function validarFormulario() {
            const cpf = document.getElementById('cpf').value;
            const telefone = document.getElementById('telefone').value;

            if (!validarCPF(cpf)) {
                alert('CPF inválido. Por favor, insira um CPF válido.');
                return false;
            }

            if (!validarTelefone(telefone)) {
                alert('Telefone inválido. O telefone deve conter 10 ou 11 dígitos.');
                return false;
            }

            return true;
        }

        // Função para formatar o telefone
        function formatarTelefone(event) {
            const input = event.target;
            let valor = input.value.replace(/\D+/g, ''); // Remove caracteres não numéricos
            if (valor.length > 11) {
                valor = valor.slice(0, 11); // Limita a 11 dígitos
            }
            if (valor.length > 6) {
                valor = valor.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
            } else if (valor.length > 2) {
                valor = valor.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            input.value = valor;
        }
        </script>
    </head>

    <body>
        <h2><?= $isAdmin ? 'Adicionar Usuário' : 'Cadastrar-se' ?></h2>
        <form method="POST" onsubmit="return validarFormulario()">
            <label for="cpf">CPF:</label>
            <input type="text" name="cpf" id="cpf" required maxlength="14" pattern="\(\d{2}\) \d{5}-\d{4}"
                placeholder="(00) 00000-0000" oninput="formatarTelefone(event)"><br>
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" required maxlength="255"><br>
            <label for="data_nascimento">Data de Nascimento:</label>
            <input type="date" name="data_nascimento" id="data_nascimento" required><br>
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" required maxlength="255"><br>
            <label for="telefone">Telefone:</label>
            <input type="text" name="telefone" id="telefone" required maxlength="15" pattern="\(\d{2}\) \d{5}-\d{4}"
                placeholder="(00) 00000-0000" oninput="formatarTelefone(event)"><br>
            <label for="senha">Senha:</label>
            <input type="password" name="senha" id="senha" required maxlength="255"><br>
            <?php if ($isAdmin): ?>
            <label for="status">Status:</label>
            <select name="status" id="status">
                <option value="Ativo">Ativo</option>
                <option value="Inativo">Inativo</option>
            </select><br>
            <label for="fk_Permissao_id">Permissão:</label>
            <select name="fk_Permissao_id" id="fk_Permissao_id">
                <option value="1">Administrador</option>
                <option value="2">Adotante</option>
            </select><br>
            <?php endif; ?>
            <button type="submit"><?= $isAdmin ? 'Adicionar' : 'Cadastrar' ?></button>
        </form>
        <p><a href="usuarios.php">Voltar</a></p>
    </body>

</html>