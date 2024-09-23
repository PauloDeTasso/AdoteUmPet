<?php
// Incluir a conexão com o banco de dados
include 'conexao_db.php';

// Iniciar a sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['cpf']))
{
    header("Location: login.php");
    exit();
}

// Conectar ao banco de dados
$conexao = conectar();

// Obter CPF do usuário logado
$cpf_usuario_logado = $_SESSION['cpf'];

// Obtém a URL da imagem do usuário
$queryImagem = $conexao->prepare('SELECT url_imagem FROM Imagem_Usuario WHERE fk_Usuario_cpf = :cpf');
$queryImagem->execute([':cpf' => $cpf_usuario_logado]);
$imagemUsuario = $queryImagem->fetch(PDO::FETCH_ASSOC);

$imagemUrl = $imagemUsuario ? $imagemUsuario['url_imagem'] : 'imagens/usuarios/default.jpg';

// Consultar os dados do usuário logado e seu endereço
$query = "
    SELECT u.*, e.rua, e.numero, e.bairro, e.cep, e.cidade, e.estado
    FROM Usuario u
    LEFT JOIN Enderecos_Usuarios eu ON u.cpf = eu.fk_Usuario_cpf
    LEFT JOIN Endereco e ON eu.fk_Endereco_id = e.id
    WHERE u.cpf = :cpf";
$stmt = $conexao->prepare($query);
$stmt->bindParam(':cpf', $cpf_usuario_logado);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se o usuário existe
if (!$usuario)
{
    echo "Usuário não encontrado.";
    exit();
}

// Processar o envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (isset($_POST['atualizar']))
    {
        // Validações
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $telefone = trim($_POST['telefone']);
        $data_nascimento = $_POST['data_nascimento'];
        $rua = trim($_POST['rua']);
        $numero = trim($_POST['numero']);
        $bairro = trim($_POST['bairro']);
        $cep = trim($_POST['cep']);
        $cidade = trim($_POST['cidade']);
        $estado = trim($_POST['estado']);

        // Validações simples
        if (
            empty($nome) || empty($email) || empty($telefone) || empty($data_nascimento) ||
            empty($rua) || empty($numero) || empty($bairro) || empty($cep) || empty($cidade) || empty($estado)
        )
        {
            echo "<script>showToast('Por favor, preencha todos os campos obrigatórios.');</script>";
        }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            echo "<script>showToast('Email inválido.');</script>";
        }
        else
        {
            // Atualizar imagem se um novo arquivo for enviado
            $url_imagem_nova = $usuario['url_imagem']; // Manter a imagem atual

            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK)
            {
                $imagem_temp = $_FILES['imagem']['tmp_name'];
                $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
                $nome_imagem = "usuario_" . $cpf_usuario_logado . "." . $extensao;
                $url_imagem_nova = "imagens/usuario/" . $nome_imagem;

                // Remover a imagem antiga do servidor
                if (!empty($usuario['url_imagem']) && file_exists($usuario['url_imagem']))
                {
                    unlink($usuario['url_imagem']);
                }

                // Mover o novo arquivo para a pasta
                move_uploaded_file($imagem_temp, $url_imagem_nova);
            }

            // Atualizar no banco de dados
            $update_query = "
                UPDATE Usuario 
                SET nome = :nome, email = :email, telefone = :telefone, 
                    data_nascimento = :data_nascimento 
                WHERE cpf = :cpf";
            $update_stmt = $conexao->prepare($update_query);
            $update_stmt->bindParam(':nome', $nome);
            $update_stmt->bindParam(':email', $email);
            $update_stmt->bindParam(':telefone', $telefone);
            $update_stmt->bindParam(':data_nascimento', $data_nascimento);
            $update_stmt->bindParam(':cpf', $cpf_usuario_logado);

            if ($update_stmt->execute())
            {
                // Atualizar a tabela de imagens
                $imagem_update_query = "
                    INSERT INTO Imagem_Usuario (url_imagem, fk_Usuario_cpf) 
                    VALUES (:url_imagem, :cpf) 
                    ON CONFLICT (fk_Usuario_cpf) DO UPDATE 
                    SET url_imagem = :url_imagem";
                $imagem_update_stmt = $conexao->prepare($imagem_update_query);
                $imagem_update_stmt->bindParam(':url_imagem', $url_imagem_nova);
                $imagem_update_stmt->bindParam(':cpf', $cpf_usuario_logado);
                $imagem_update_stmt->execute();

                // Atualizar dados de endereço
                $endereco_update_query = "
                    INSERT INTO Endereco (rua, numero, bairro, cep, cidade, estado) 
                    VALUES (:rua, :numero, :bairro, :cep, :cidade, :estado) 
                    ON CONFLICT (id) DO UPDATE 
                    SET rua = :rua, numero = :numero, bairro = :bairro, cep = :cep, cidade = :cidade, estado = :estado";
                $endereco_update_stmt = $conexao->prepare($endereco_update_query);
                $endereco_update_stmt->bindParam(':rua', $rua);
                $endereco_update_stmt->bindParam(':numero', $numero);
                $endereco_update_stmt->bindParam(':bairro', $bairro);
                $endereco_update_stmt->bindParam(':cep', $cep);
                $endereco_update_stmt->bindParam(':cidade', $cidade);
                $endereco_update_stmt->bindParam(':estado', $estado);
                $endereco_update_stmt->execute();

                echo "<script>showToast('Dados atualizados com sucesso!');</script>";
            }
            else
            {
                echo "<script>showToast('Erro ao atualizar os dados.');</script>";
            }
        }
    }
    elseif (isset($_POST['remover']))
    {
        // Remover o usuário
        $delete_query = "DELETE FROM Usuario WHERE cpf = :cpf";
        $delete_stmt = $conexao->prepare($delete_query);
        $delete_stmt->bindParam(':cpf', $cpf_usuario_logado);

        if ($delete_stmt->execute())
        {
            // Remover a imagem do servidor
            if (!empty($usuario['url_imagem']) && file_exists($usuario['url_imagem']))
            {
                unlink($usuario['url_imagem']);
            }

            echo "<script>showToast('Usuário removido com sucesso!');</script>";
            // Fazer logout e redirecionar
            session_destroy();
            header("Location: index.php");
            exit();
        }
        else
        {
            echo "<script>showToast('Erro ao remover o usuário.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Editar Dados do Usuário</title>
        <link rel="stylesheet" href="css/usuario/usuario_editar_dados.css">
        <script>
        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerText = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        function validateForm() {
            // Aqui você pode adicionar validações adicionais se necessário
            return true;
        }
        </script>

    </head>

    <body>
        <?php include_once 'cabecalho.php'; ?>

        <section class="cabecalho">

            <h3>Atualizar meus Dados</h3>
            <section class="usuario-imagem">
                <img src="<?= htmlspecialchars($imagemUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto do usuário">
            </section>
        </section>
        <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required
                placeholder="Digite seu nome">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>"
                required placeholder="exemplo@dominio.com">

            <label for="telefone">Telefone:</label>
            <input type="text" id="telefone" name="telefone"
                value="<?php echo htmlspecialchars($usuario['telefone']); ?>" required
                placeholder="Digite seu telefone">

            <label for="data_nascimento">Data de Nascimento:</label>
            <input type="date" id="data_nascimento" name="data_nascimento"
                value="<?php echo htmlspecialchars($usuario['data_nascimento']); ?>" required>

            <label for="imagem">Imagem:</label>
            <input type="file" id="imagem" name="imagem" accept="image/*">

            <h2>Endereço</h2>
            <label for="rua">Rua:</label>
            <input type="text" id="rua" name="rua" value="<?php echo htmlspecialchars($usuario['rua']); ?>" required>

            <label for="numero">Número:</label>
            <input type="text" id="numero" name="numero" value="<?php echo htmlspecialchars($usuario['numero']); ?>"
                required>

            <label for="bairro">Bairro:</label>
            <input type="text" id="bairro" name="bairro" value="<?php echo htmlspecialchars($usuario['bairro']); ?>"
                required>

            <label for="cep">CEP:</label>
            <input type="text" id="cep" name="cep" value="<?php echo htmlspecialchars($usuario['cep']); ?>" required>

            <label for="cidade">Cidade:</label>
            <input type="text" id="cidade" name="cidade" value="<?php echo htmlspecialchars($usuario['cidade']); ?>"
                required>

            <label for="estado">Estado:</label>
            <input type="text" id="estado" name="estado" value="<?php echo htmlspecialchars($usuario['estado']); ?>"
                required>

            <section class="secaoBotoes">
                <button type="submit" name="atualizar">Atualizar Dados</button>
            </section>

        </form>
    </body>

</html>