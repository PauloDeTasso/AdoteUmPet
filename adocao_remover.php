<?php
include '../db/conexao_db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$stmt = $pdo->prepare("DELETE FROM Adocao WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();

header("Location: adocoes.php");
exit;