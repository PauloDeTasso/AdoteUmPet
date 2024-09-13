<?php
require '../db/conexao_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $brinco = $_POST['brinco'];
    $sexo = $_POST['sexo'];
    $idade = $_POST['idade'];
    $raca = $_POST['raca'];
    $pelagem = $_POST['pelagem'];
    $localResgate = $_POST['local_resgate'];
    $dataResgate = $_POST['data_resgate'];
    $status = $_POST['status'];
    $informacoes = $_POST['informacoes'];

    $stmt = $pdo->prepare("UPDATE Pet SET nome = ?, brinco = ?, sexo = ?, idade = ?, raca = ?, pelagem = ?, local_resgate = ?, data_resgate = ?, status = ?, informacoes = ? WHERE id = ?");
    $stmt->execute([$nome, $brinco, $sexo, $idade, $raca, $pelagem, $localResgate, $dataResgate, $status, $informacoes, $id]);

    header('Location: ../pet/pets.php');
}