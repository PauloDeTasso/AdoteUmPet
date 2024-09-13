<?php
include '../db/conexao_db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$stmt = $pdo->prepare("SELECT * FROM Adocao WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$adocao = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $dataAdocao = filter_input(INPUT_POST, 'data_adocao', FILTER_SANITIZE_STRING);
    $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_STRING);

    $stmt = $pdo->prepare("UPDATE Adocao SET data_adocao = :data_adocao, observacoes = :observacoes WHERE id = :id");
    $stmt->bindParam(':data_adocao', $dataAdocao);
    $stmt->bindParam(':observacoes', $observacoes);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header("Location: adocoes.php");
    exit;
}
?>

<form method="POST" action="editar_adocao.php?id=<?php echo $id; ?>">
    <input type="date" name="data_adocao" value="<?php echo htmlspecialchars($adocao['data_adocao']); ?>" required>
    <textarea name="observacoes" required><?php echo htmlspecialchars($adocao['observacoes']); ?></textarea>
    <button type="submit">Salvar</button>
</form>