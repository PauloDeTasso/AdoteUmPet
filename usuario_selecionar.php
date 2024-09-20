<?php
include 'conexao_db.php';

$cpf = $_GET['cpf'] ?? '';

$conn = conectar();

try {
    // Consulta para obter os dados do usuário com base no CPF, incluindo a imagem e endereço
    $query = "
        SELECT u.*, i.url_imagem, e.rua, e.numero, e.bairro, e.cidade, e.estado
        FROM Usuario u
        LEFT JOIN Imagem_Usuario i ON u.cpf = i.fk_Usuario_cpf
        LEFT JOIN Enderecos_Usuarios eu ON u.cpf = eu.fk_Usuario_cpf
        LEFT JOIN Endereco e ON eu.fk_Endereco_id = e.id
        WHERE u.cpf = :cpf
    ";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':cpf', $cpf, PDO::PARAM_STR);
    $stmt->execute();

    // Verifica se o usuário existe
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo "Usuário não encontrado!";
        exit;
    }

    // Processa a atualização dos dados
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recebe os dados do formulário
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $telefone = $_POST['telefone'];
        $rua = $_POST['rua'];
        $numero = $_POST['numero'];
        $bairro = $_POST['bairro'];
        $cidade = $_POST['cidade'];
        $estado = $_POST['estado'];

        // Verifica se uma nova imagem foi enviada
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            // Caminho temporário e nome do arquivo
            $fotoTmp = $_FILES['foto']['tmp_name'];
            $fotoNome = $_FILES['foto']['name'];

            // Define o destino para onde a imagem será movida
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

            // Se já existir uma imagem, remove a imagem antiga do servidor
            if ($imagemExistente) {
                $imagemAntiga = $imagemExistente['url_imagem'];
                if (file_exists($imagemAntiga)) {
                    unlink($imagemAntiga); // Remove a imagem antiga do sistema de arquivos
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
            } else {
                // Se não existir, insere uma nova imagem
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

            // Atualiza a URL da imagem no array do usuário
            $usuario['url_imagem'] = $destino;
        }

        // Atualiza os dados do usuário no banco de dados
        $updateQuery = "
            UPDATE Usuario
            SET nome = :nome, email = :email, telefone = :telefone
            WHERE cpf = :cpf
        ";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':nome', $nome);
        $updateStmt->bindParam(':email', $email);
        $updateStmt->bindParam(':telefone', $telefone);
        $updateStmt->bindParam(':cpf', $cpf);
        $updateStmt->execute();

        // Atualiza o endereço do usuário no banco de dados
        $updateEnderecoQuery = "
            UPDATE Endereco
            SET rua = :rua, 
                numero = :numero, 
                bairro = :bairro, 
                cidade = :cidade, 
                estado = :estado
            FROM Enderecos_Usuarios eu
            WHERE eu.fk_Usuario_cpf = :cpf AND Endereco.id = eu.fk_Endereco_id
        ";
        $updateEnderecoStmt = $conn->prepare($updateEnderecoQuery);
        $updateEnderecoStmt->bindParam(':rua', $rua);
        $updateEnderecoStmt->bindParam(':numero', $numero);
        $updateEnderecoStmt->bindParam(':bairro', $bairro);
        $updateEnderecoStmt->bindParam(':cidade', $cidade);
        $updateEnderecoStmt->bindParam(':estado', $estado);
        $updateEnderecoStmt->bindParam(':cpf', $cpf);
        $updateEnderecoStmt->execute();

        // Atualiza a variável de dados do usuário para refletir as mudanças
        $usuario = array_merge($usuario, [
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'rua' => $rua,
            'numero' => $numero,
            'bairro' => $bairro,
            'cidade' => $cidade,
            'estado' => $estado
        ]);

        echo "<p>Dados atualizados com sucesso!</p>";
    }

    // Verifica se o usuário clicou em "Editar"
    $editMode = isset($_GET['edit']) && $_GET['edit'] === 'true';
} catch (PDOException $e) {
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
                <legend>Editar Dados</legend>

                <label for="foto">Alterar Foto:</label>
                <input type="file" id="foto" name="foto" accept="image/*">

                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']); ?>" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']); ?>"
                    required>

                <label for="telefone">Telefone:</label>
                <input type="tel" id="telefone" name="telefone" value="<?= htmlspecialchars($usuario['telefone']); ?>"
                    required>

                <label for="rua">Rua:</label>
                <input type="text" id="rua" name="rua" value="<?= htmlspecialchars($usuario['rua']); ?>" required>

                <label for="numero">Número:</label>
                <input type="text" id="numero" name="numero" value="<?= htmlspecialchars($usuario['numero']); ?>"
                    required>

                <label for="bairro">Bairro:</label>
                <input type="text" id="bairro" name="bairro" value="<?= htmlspecialchars($usuario['bairro']); ?>"
                    required>

                <label for="cidade">Cidade:</label>
                <input type="text" id="cidade" name="cidade" value="<?= htmlspecialchars($usuario['cidade']); ?>"
                    required>

                <label for="estado">Estado:</label>
                <input type="text" id="estado" name="estado" value="<?= htmlspecialchars($usuario['estado']); ?>"
                    required>

                <button type="submit">Atualizar</button>
            </fieldset>
        </form>
        <?php else: ?>
        <p><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome']); ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']); ?></p>
        <p><strong>Telefone:</strong> <?= htmlspecialchars($usuario['telefone']); ?></p>
        <p><strong>Rua:</strong> <?= htmlspecialchars($usuario['rua']); ?></p>
        <p><strong>Número:</strong> <?= htmlspecialchars($usuario['numero']); ?></p>
        <p><strong>Bairro:</strong> <?= htmlspecialchars($usuario['bairro']); ?></p>
        <p><strong>Cidade:</strong> <?= htmlspecialchars($usuario['cidade']); ?></p>
        <p><strong>Estado:</strong> <?= htmlspecialchars($usuario['estado']); ?></p>
        <p><a href="?cpf=<?= urlencode($cpf); ?>&edit=true">Editar</a>
            <a href="usuario_remover.php?cpf=<?= htmlspecialchars($usuario['cpf']); ?>" class="btn-remover">Remover</a>
        </p>

        <?php endif; ?>
    </div>

    <?php include 'rodape.php'; ?>

</body>

</html>