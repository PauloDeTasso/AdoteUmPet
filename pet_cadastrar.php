<?php
include 'conexao_db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $sexo = filter_input(INPUT_POST, 'sexo', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    $stmt = $pdo->prepare("INSERT INTO Pet (nome, sexo, status) VALUES (:nome, :sexo, :status)");
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':sexo', $sexo);
    $stmt->bindParam(':status', $status);
    $stmt->execute();

    header("Location: pets.php");
    exit;
}
?>

<form method="POST" action="pet_cadastrar.php">
    <input type="text" name="nome" placeholder="Nome" required>
    <input type="text" name="sexo" placeholder="Sexo" required>
    <input type="text" name="status" placeholder="Status" required>
    <button type="submit">Cadastrar</button>
</form>