<?php
session_start();
require 'conexao_db.php';

// Conecta ao banco de dados
$pdo = conectar();

// Consulta todos os pets disponíveis para adoção
$sql = 'SELECT * FROM Pet WHERE status = :status';
$stmt = $pdo->prepare($sql);
$stmt->execute([':status' => 'ADOTÁVEL']); // Verifique se o status é exatamente 'ADOTÁVEL'
$pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pets Disponíveis</title>
        <link rel="stylesheet" href="css/pets.css">
    </head>

    <body>
        <h2>Pets Disponíveis para Adoção</h2>

        <?php if (count($pets) > 0): ?>
        <ul>
            <?php foreach ($pets as $pet): ?>
            <li>
                <h3><?= htmlspecialchars($pet['nome']); ?></h3>
                <p><strong>Brinco:</strong> <?= htmlspecialchars($pet['brinco']); ?></p>
                <p><a href="pet_selecionar.php?id=<?= htmlspecialchars($pet['id']); ?>">Ver Detalhes</a></p>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p>Atualmente não há pets disponíveis para adoção. Por favor, volte mais tarde.</p>
        <?php endif; ?>

        <p><a href="home.php">Voltar</a></p>
    </body>

</html>