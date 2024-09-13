<?php
include 'conexao_db.php';

$stmt = $pdo->query("SELECT * FROM Usuario");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Usuários Cadastrados</h1>
<a href="usuario_cadastrar.php">Cadastrar Usuário</a>
<ul>
    <?php foreach ($usuarios as $usuario): ?>
    <li><?php echo htmlspecialchars($usuario['nome']); ?> - <a
            href="usuario_editar.php?cpf=<?php echo $usuario['cpf']; ?>">Editar</a> | <a
            href="usuario_remover.php?cpf=<?php echo $usuario['cpf']; ?>">Remover</a></li>
    <?php endforeach; ?>
</ul>