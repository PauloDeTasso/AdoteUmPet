<?php
require '../db/conexao_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $cpf = $_POST['cpf'];
    $nome = $_POST['nome'];
    $dataNascimento = $_POST['data_nascimento'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $senha = crypt($_POST['senha'], gen_salt('bf'));
    $permissaoId = $_POST['permissao'];

    $stmt = $pdo->prepare("INSERT INTO Usuario (cpf, nome, data_nascimento, email, telefone, senha, fk_Permissao_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$cpf, $nome, $dataNascimento, $email, $telefone, $senha, $permissaoId]);

    header('Location: ../usuario/usuarios.php');
}