<?php
include '../db/conexao_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $dataAdocao = filter_input(INPUT_POST, 'data_adocao', FILTER_SANITIZE_STRING);
    $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_STRING);
    $cpfUsuario = filter_input(INPUT_POST, 'cpf_usuario', FILTER_SANITIZE_STRING);
    $idPet = filter_input(INPUT_POST, 'id_pet', FILTER_SANITIZE_NUMBER_INT);

    $stmt = $pdo->prepare("INSERT INTO Adocao (data_adocao, observacoes, fk_Usuario_cpf, fk_Pet_id) 
                           VALUES (:data_adocao, :observacoes, :fk_Usuario_cpf, :fk_Pet_id)");
    $stmt->bindParam(':data_adocao', $dataAdocao);
    $stmt->bindParam(':observacoes', $observacoes);
    $stmt->bindParam(':fk_Usuario_cpf', $cpfUsuario);
    $stmt->bindParam(':fk_Pet_id', $idPet);
    $stmt->execute();

    header("Location: adocoes.php");
    exit;
}
?>

<form method="POST" action="cadastrar_adocao.php">
    <input type="date" name="data_adocao" required>
    <textarea name="observacoes" placeholder="Observações" required></textarea>
    <input type="text" name="cpf_usuario" placeholder="CPF do Usuário" required>
    <input type="number" name="id_pet" placeholder="ID do Pet" required>
    <button type="submit">Cadastrar Adoção</button>
</form>