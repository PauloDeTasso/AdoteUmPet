<?php
include_once "start.php";

$pdo = conectar();

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $brinco = $_POST['brinco'];
    $nome = $_POST['nome'];
    $sexo = $_POST['sexo'];
    $idade = $_POST['idade'] ?: null;
    $raca = $_POST['raca'] ?: null;
    $pelagem = $_POST['pelagem'] ?: null;
    $local_resgate = $_POST['local_resgate'] ?: null;
    $data_resgate = $_POST['data_resgate'] ?: null;
    $informacoes = $_POST['informacoes'] ?: null;

    // Atualiza o pet no banco de dados
    $sql = "UPDATE Pet
            SET nome = :nome, sexo = :sexo, idade = :idade, raca = :raca, pelagem = :pelagem,
                local_resgate = :local_resgate, data_resgate = :data_resgate, informacoes = :informacoes
            WHERE brinco = :brinco";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':sexo', $sexo);
    $stmt->bindParam(':idade', $idade);
    $stmt->bindParam(':raca', $raca);
    $stmt->bindParam(':pelagem', $pelagem);
    $stmt->bindParam(':local_resgate', $local_resgate);
    $stmt->bindParam(':data_resgate', $data_resgate);
    $stmt->bindParam(':informacoes', $informacoes);
    $stmt->bindParam(':brinco', $brinco, PDO::PARAM_INT);

    if ($stmt->execute())
    {
        header('Location: pet_selecionar.php?brinco=' . $brinco);
        exit();
    }
    else
    {
        echo "Erro ao atualizar o pet.";
    }
}
