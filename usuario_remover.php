<?php
require '../db/conexao_db.php';

if (isset($_POST['cpf']))
{
    $cpf = $_POST['cpf'];

    $stmt = $pdo->prepare("DELETE FROM Usuario WHERE cpf = ?");
    $stmt->execute([$cpf]);

    header('Location: ../usuario/usuarios.php');
}