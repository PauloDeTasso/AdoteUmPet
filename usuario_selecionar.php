<?php
session_start();
require_once 'conexao_db.php';

// Verifica usuário na sessão
require_once 'auth.php';
verificarSessao();

$cpf = $_GET['cpf'] ?? '';

$conn = conectar();

try
{
    // Consulta para obter os dados do usuário com base no CPF
    $query = "
        SELECT u.*, i.url_imagem, e.rua, e.numero, e.bairro, e.cidade, e.estado, e.cep, e.referencia, p.tipo
        FROM Usuario u
        LEFT JOIN Imagem_Usuario i ON u.cpf = i.fk_Usuario_cpf
        LEFT JOIN Enderecos_Usuarios eu ON u.cpf = eu.fk_Usuario_cpf
        LEFT JOIN Endereco e ON eu.fk_Endereco_id = e.id
        LEFT JOIN Permissao p ON u.fk_Permissao_id = p.id
        WHERE u.cpf = :cpf
    ";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':cpf', $cpf, PDO::PARAM_STR);
    $stmt->execute();

    // Verifica se o usuário existe
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario)
    {
        echo "Usuário não encontrado!";
        exit;
    }

    // Processa a atualização dos dados
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        // Recebe os dados do formulário
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        $data_nascimento = $_POST['data_nascimento'] ?? '';
        $rua = $_POST['rua'] ?? null;
        $numero = $_POST['numero'] ?? null;
        $bairro = $_POST['bairro'] ?? null;
        $cidade = $_POST['cidade'] ?? null;
        $estado = $_POST['estado'] ?? null;
        $cep = $_POST['cep'] ?? null;
        $referencia = $_POST['referencia'] ?? null;

        // Validação dos campos obrigatórios do usuário
        if (
            empty($nome) || empty($email) || empty($telefone) || empty($data_nascimento) ||
            empty($rua) || empty($bairro) || empty($cidade) || empty($estado)
        )
        {
            echo "Os campos nome, email, telefone, data de nascimento, rua, bairro, cidade e estado são obrigatórios!";
            exit;
        }

        // Verifica se uma nova imagem foi enviada
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0)
        {
            // Caminho temporário e nome do arquivo
            $fotoTmp = $_FILES['foto']['tmp_name'];
            $fotoNome = $_FILES['foto']['name'];
            $destino = "imagens/usuarios/" . $fotoNome;

            // Verifica se o usuário já tem uma imagem associada
            $verificaImagemQuery = "
                SELECT url_imagem FROM Imagem_Usuario
                WHERE fk_Usuario_cpf = :cpf
            ";
            $verificaImagemStmt = $conn->prepare($verificaImagemQuery);
            $verificaImagemStmt->bindParam(':cpf', $cpf);
            $verificaImagemStmt->execute();
            $imagemExistente = $verificaImagemStmt->fetch(PDO::FETCH_ASSOC);

            // Remove imagem antiga, se existir
            if ($imagemExistente)
            {
                $imagemAntiga = $imagemExistente['url_imagem'];
                if (file_exists($imagemAntiga))
                {
                    unlink($imagemAntiga);
                }
                // Atualiza a imagem no banco de dados
                $updateImagemQuery = "
                    UPDATE Imagem_Usuario
                    SET url_imagem = :url_imagem
                    WHERE fk_Usuario_cpf = :cpf
                ";
                $updateImagemStmt = $conn->prepare($updateImagemQuery);
                $updateImagemStmt->bindParam(':cpf', $cpf);
                $updateImagemStmt->bindParam(':url_imagem', $destino);
                $updateImagemStmt->execute();
            }
            else
            {
                // Insere uma nova imagem
                $inserirImagemQuery = "
                    INSERT INTO Imagem_Usuario (fk_Usuario_cpf, url_imagem)
                    VALUES (:cpf, :url_imagem)
                ";
                $inserirImagemStmt = $conn->prepare($inserirImagemQuery);
                $inserirImagemStmt->bindParam(':cpf', $cpf);
                $inserirImagemStmt->bindParam(':url_imagem', $destino);
                $inserirImagemStmt->execute();
            }

            // Move o novo arquivo para o diretório de imagens
            move_uploaded_file($fotoTmp, $destino);
            $usuario['url_imagem'] = $destino;
        }

        // Atualiza os dados do usuário no banco de dados
        $updateQuery = "
            UPDATE Usuario
            SET nome = :nome, email = :email, telefone = :telefone, data_nascimento = :data_nascimento
            WHERE cpf = :cpf
        ";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':nome', $nome);
        $updateStmt->bindParam(':email', $email);
        $updateStmt->bindParam(':telefone', $telefone);
        $updateStmt->bindParam(':data_nascimento', $data_nascimento);
        $updateStmt->bindParam(':cpf', $cpf);
        $updateStmt->execute();

        // Atualiza o endereço do usuário no banco de dados
        $updateEnderecoQuery = "
            UPDATE Endereco
            SET rua = :rua, 
                numero = COALESCE(:numero, numero), 
                bairro = :bairro, 
                cidade = :cidade, 
                estado = :estado, 
                cep = COALESCE(:cep, cep),
                referencia = COALESCE(:referencia, referencia)
            FROM Enderecos_Usuarios eu
            WHERE eu.fk_Usuario_cpf = :cpf AND Endereco.id = eu.fk_Endereco_id
        ";
        $updateEnderecoStmt = $conn->prepare($updateEnderecoQuery);
        $updateEnderecoStmt->bindParam(':rua', $rua);
        $updateEnderecoStmt->bindParam(':numero', $numero);
        $updateEnderecoStmt->bindParam(':bairro', $bairro);
        $updateEnderecoStmt->bindParam(':cidade', $cidade);
        $updateEnderecoStmt->bindParam(':estado', $estado);
        $updateEnderecoStmt->bindParam(':cep', $cep);
        $updateEnderecoStmt->bindParam(':referencia', $referencia);
        $updateEnderecoStmt->bindParam(':cpf', $cpf);
        $updateEnderecoStmt->execute();

        // Atualiza a variável de dados do usuário para refletir as mudanças
        $usuario = array_merge($usuario, [
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'data_nascimento' => $data_nascimento,
            'rua' => $rua,
            'numero' => $numero,
            'bairro' => $bairro,
            'cidade' => $cidade,
            'estado' => $estado,
            'cep' => $cep,
            'referencia' => $referencia
        ]);

        echo "<p>Dados atualizados com sucesso!</p>";
    }

    // Verifica se o usuário clicou em "Editar"
    $editMode = isset($_GET['edit']) && $_GET['edit'] === 'true';

    // Verifica se o usuário é administrador
    $isAdmin = $_SESSION['tipo'] === 'Administrador';
}
catch (PDOException $e)
{
    echo "Erro ao buscar usuário: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Detalhes do Usuário</title>
        <link rel="stylesheet" href="css/usuario/usuario_selecionar.css">
        <script>
        function toggleEndereco() {
            var enderecoFields = document.getElementById('endereco-fields');
            enderecoFields.style.display = enderecoFields.style.display === 'none' ? 'block' : 'none';
        }
        </script>
    </head>

    <body>
        <?php include 'cabecalho.php'; ?>

        <section class="cabecalho">
            <h3>Informações do Usuário</h3>
        </section>

        <div class="usuario-detalhes">
            <?php if (!empty($usuario['url_imagem'])): ?>
            <img src="<?= htmlspecialchars($usuario['url_imagem']); ?>"
                alt="Foto de <?= htmlspecialchars($usuario['nome']); ?>" class="usuario-foto-grande">
            <?php else: ?>
            <img src="imagens/usuarios/default.png" alt="Foto padrão" class="usuario-foto-grande">
            <?php endif; ?>

            <?php if ($editMode): ?>
            <form method="POST" enctype="multipart/form-data">
                <fieldset>
                    <legend>Editar Dados do Usuário</legend>

                    <label for="nome">Nome: <span style="color:red;">*</span></label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']); ?>" required>

                    <label for="email">Email: <span style="color:red;">*</span></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']); ?>" required>

                    <label for="telefone">Telefone: <span style="color:red;">*</span></label>
                    <input type="text" name="telefone" value="<?= htmlspecialchars($usuario['telefone']); ?>" required>

                    <label for="data_nascimento">Data de Nascimento: <span style="color:red;">*</span></label>
                    <input type="date" name="data_nascimento"
                        value="<?= htmlspecialchars($usuario['data_nascimento']); ?>" required>

                    <label for="foto">Foto:</label>
                    <input type="file" name="foto" accept="image/*">

                    <button type="button" onclick="toggleEndereco()">Editar Endereço</button>

                    <div id="endereco-fields" style="display: none;">
                        <h4>Endereço</h4>
                        <label for="rua">Rua: <span style="color:red;">*</span></label>
                        <input type="text" name="rua" value="<?= htmlspecialchars($usuario['rua']); ?>" required>

                        <label for="numero">Número:</label>
                        <input type="text" name="numero" value="<?= htmlspecialchars($usuario['numero']); ?>">

                        <label for="bairro">Bairro: <span style="color:red;">*</span></label>
                        <input type="text" name="bairro" value="<?= htmlspecialchars($usuario['bairro']); ?>" required>

                        <label for="cidade">Cidade: <span style="color:red;">*</span></label>
                        <input type="text" name="cidade" value="<?= htmlspecialchars($usuario['cidade']); ?>" required>

                        <label for="estado">Estado: <span style="color:red;">*</span></label>
                        <input type="text" name="estado" value="<?= htmlspecialchars($usuario['estado']); ?>" required>

                        <label for="cep">CEP:</label>
                        <input type="text" name="cep" value="<?= htmlspecialchars($usuario['cep']); ?>">

                        <label for="referencia">Referência:</label>
                        <input type="text" name="referencia" value="<?= htmlspecialchars($usuario['referencia']); ?>">
                    </div>

                    <button type="submit">Salvar</button>
                    <a href="usuarios.php">Cancelar</a>
                </fieldset>
            </form>
            <?php else: ?>
            <p><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']); ?></p>
            <p><strong>Telefone:</strong> <?= htmlspecialchars($usuario['telefone']); ?></p>
            <p><strong>Data de Nascimento:</strong> <?= htmlspecialchars($usuario['data_nascimento']); ?></p>
            <p><strong>Tipo de Usuário:</strong> <?= htmlspecialchars($usuario['tipo']); ?></p>

            <h4>Endereço</h4>
            <p><strong>Rua:</strong> <?= htmlspecialchars($usuario['rua']); ?></p>
            <p><strong>Número:</strong> <?= htmlspecialchars($usuario['numero']); ?></p>
            <p><strong>Bairro:</strong> <?= htmlspecialchars($usuario['bairro']); ?></p>
            <p><strong>Cidade:</strong> <?= htmlspecialchars($usuario['cidade']); ?></p>
            <p><strong>Estado:</strong> <?= htmlspecialchars($usuario['estado']); ?></p>
            <p><strong>CEP:</strong> <?= htmlspecialchars($usuario['cep']); ?></p>
            <p><strong>Referência:</strong> <?= htmlspecialchars($usuario['referencia']); ?></p>

            <?php if ($isAdmin): ?>

            <form method="POST" action="usuario_remover.php"
                onsubmit="return confirm('Tem certeza que deseja remover esta conta?');">
                <input type="hidden" name="cpf" value="<?= htmlspecialchars($usuario['cpf']); ?>">
                <button type="submit">Remover essa Conta</button>
            </form>
            <?php endif; ?>
            <a href="?cpf=<?= htmlspecialchars($usuario['cpf']); ?>&edit=true">Editar Dados</a>
            <?php endif; ?>
        </div>

        <?php include 'rodape.php'; ?>
    </body>

</html>