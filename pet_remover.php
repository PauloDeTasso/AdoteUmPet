<?php
require '../db/conexao_db.php';

if (isset($_POST['id']))
{
    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM Pet WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: ../pet/pets.php');
}