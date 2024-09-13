<?php
session_start();
require 'conexao_db.php';

$id = $_GET['id'] ?? '';

$pdo = conectar();

$sql = 'SELECT * FROM Pet WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$pet = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = 'SELECT * FROM Imagem_Pet WHERE fk_Pet_id = :id';
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$imagens_pet = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Pet</title>
    <link rel="stylesheet" href="css/pet/pets.css">
</head>

<body>
    <h2>Visualizar Pet</h2>
    <p><strong>Nome:</strong> <?= htmlspecialchars($pet['nome']) ?></p>
    <p><strong>Brinco:</strong> <?= htmlspecialchars($pet['brinco']) ?></p>
    <p><strong>Sexo:</strong> <?= htmlspecialchars($pet['sexo']) ?></p>
    <p><strong>Idade:</strong> <?= htmlspecialchars($pet['idade']) ?></p>
    <p><strong>Raça:</strong> <?= htmlspecialchars($pet['raca']) ?></p>
    <p><strong>Pelagem:</strong> <?= htmlspecialchars($pet['pelagem']) ?></p>
    <p><strong>Local do Resgate:</strong> <?= htmlspecialchars($pet['local_resgate']) ?></p>
    <p><strong>Data do Resgate:</strong> <?= htmlspecialchars($pet['data_resgate']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($pet['status']) ?></p>
    <p><strong>Informações:</strong> <?= htmlspecialchars($pet['informacoes']) ?></p>

    <h3>Imagens</h3>
    <?php foreach ($imagens_pet as $imagem): ?>
    <img src="<?= htmlspecialchars($imagem['url_imagem']) ?>" alt="Imagem do Pet" style="width: 150px;">
    <?php endforeach; ?>

    <p><a href="pets.php">Voltar</a></p>
</body>

</html>