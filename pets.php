<?php
include 'conexao_db.php';

$stmt = $pdo->query("SELECT * FROM Pet");
$pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Pets Disponíveis</h1>
<a href="pet_cadastrar.php">Cadastrar Pet</a>
<ul>
    <?php foreach ($pets as $pet): ?>
    <li><?php echo htmlspecialchars($pet['nome']); ?> - <a href="pet_editar.php?id=<?php echo $pet['id']; ?>">Editar</a>
        | <a href="pet_remover.php?id=<?php echo $pet['id']; ?>">Remover</a></li>
    <?php endforeach; ?>
</ul>