<?php
require '../db/conexao_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $cpf = $_POST['cpf'];
    $nome = $_POST['nome'];
    $dataNascimento = $_POST['data_nascimento'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE Usuario SET nome = ?, data_nascimento = ?, email = ?, telefone = ?, status = ? WHERE cpf = ?");
    $stmt->execute([$nome, $dataNascimento, $email, $telefone, $status, $cpf]);

    header('Location: ../usuario/usuarios.php');
}