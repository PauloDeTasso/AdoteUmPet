<?php
session_start();
require 'conexao_db.php';

// Captura o ID do pet da URL
$id = $_GET['id'] ?? '';

// Validação do ID
if (!is_numeric($id) || $id <= 0)
{
    $pet = null;
}
else
{
    $pdo = conectar();

    // Consulta os detalhes do pet apenas se o status for 'ADOTÁVEL'
    $sql = 'SELECT * FROM Pet WHERE id = :id AND status = \'ADOTÁVEL\'';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $pet = $stmt->fetch(PDO::FETCH_ASSOC);

    // Consulta as imagens do pet, se o pet existir
    if ($pet)
    {
        $sql = 'SELECT * FROM Imagem_Pet WHERE fk_Pet_id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $imagens_pet = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Consulta para obter informações do usuário
        $usuario_cpf = $_GET['usuario_cpf'] ?? ''; // ou outro método para obter o CPF
        $sql_usuario = 'SELECT * FROM Usuario WHERE cpf = :cpf';
        $stmt_usuario = $pdo->prepare($sql_usuario);
        $stmt_usuario->execute([':cpf' => $usuario_cpf]);
        $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

        // Consulta as imagens do usuário, se o usuário existir
        if ($usuario)
        {
            $sql = 'SELECT * FROM Imagem_Usuario WHERE fk_Usuario_cpf = :cpf';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':cpf' => $usuario_cpf]);
            $imagens_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Detalhes do Pet</title>
        <link rel="stylesheet" href="css/pet_selecionar.css">
    </head>

    <body>
        <h2>Detalhes do Pet</h2>

        <?php if ($pet): ?>
        <p><strong>Nome:</strong> <?= htmlspecialchars($pet['nome']); ?></p>
        <p><strong>Brinco:</strong> <?= htmlspecialchars($pet['brinco']); ?></p>
        <p><strong>Sexo:</strong> <?= htmlspecialchars($pet['sexo']); ?></p>
        <p><strong>Idade:</strong> <?= htmlspecialchars($pet['idade']); ?></p>
        <p><strong>Raça:</strong> <?= htmlspecialchars($pet['raca']); ?></p>
        <p><strong>Pelagem:</strong> <?= htmlspecialchars($pet['pelagem']); ?></p>
        <p><strong>Local do Resgate:</strong> <?= htmlspecialchars($pet['local_resgate']); ?></p>
        <p><strong>Data do Resgate:</strong> <?= htmlspecialchars($pet['data_resgate']); ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($pet['status']); ?></p>
        <p><strong>Informações:</strong> <?= htmlspecialchars($pet['informacoes']); ?></p>

        <h3>Imagens</h3>
        <?php if (!empty($imagens_pet)): ?>
        <?php foreach ($imagens_pet as $imagem): ?>
        <img src="<?= htmlspecialchars($imagem['url_imagem']); ?>" alt="Imagem do Pet" style="width: 150px;">
        <?php endforeach; ?>
        <?php else: ?>
        <p>Este pet não possui imagens cadastradas.</p>
        <?php endif; ?>

        <p>
            <!-- Exibe o botão de adoção apenas se o pet estiver disponível (ADOTÁVEL) -->
            <a href="adocao_cadastrar.php?pet_id=<?= htmlspecialchars($pet['id']); ?>">Adotar</a> |
            <a href="curtir.php?id=<?= htmlspecialchars($pet['id']); ?>">Curtir</a> |
            <a href="comentar.php?id=<?= htmlspecialchars($pet['id']); ?>">Comentar</a> |
            <a href="compartilhar.php?id=<?= htmlspecialchars($pet['id']); ?>">Compartilhar</a>
        </p>

        <?php if ($usuario): ?>
        <h3>Detalhes do Usuário</h3>
        <p><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome']); ?></p>
        <p><strong>Telefone:</strong> <?= htmlspecialchars($usuario['telefone']); ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']); ?></p>
        <p><strong>Cidade:</strong> <?= htmlspecialchars($usuario['cidade']); ?></p>
        <p><strong>Estado:</strong> <?= htmlspecialchars($usuario['estado']); ?></p>

        <h4>Imagens do Usuário</h4>
        <?php if (!empty($imagens_usuario)): ?>
        <?php foreach ($imagens_usuario as $imagem): ?>
        <img src="<?= htmlspecialchars($imagem['url_imagem']); ?>" alt="Imagem do Usuário" style="width: 150px;">
        <?php endforeach; ?>
        <?php else: ?>
        <p>Este usuário não possui imagens cadastradas.</p>
        <?php endif; ?>

        <?php else: ?>
        <p>Usuário não encontrado.</p>
        <?php endif; ?>

        <?php else: ?>
        <p>Pet não encontrado ou não disponível para adoção.</p>
        <?php endif; ?>

        <p><a href="pets.php">Voltar</a></p>
    </body>

</html>