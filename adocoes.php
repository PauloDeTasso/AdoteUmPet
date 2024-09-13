<?php
session_start();
include('conexao_db.php');

// Verifica se o usuário está autenticado e tem permissão de administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['fk_Permissao_id'] != 1) {
    header("Location: login.php");
    exit();
}

// Consulta para obter todas as adoções
$sql = "SELECT a.id, a.data_adocao, a.observacoes, p.nome AS pet_nome, u.nome AS usuario_nome
        FROM Adocao a
        JOIN Pet p ON a.fk_Pet_id = p.id
        JOIN Usuario u ON a.fk_Usuario_cpf = u.cpf
        ORDER BY a.data_adocao DESC";
$stmt = $pdo->query($sql);
$adoções = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Adoções</title>
    <link rel="stylesheet" href="css/adocao/adocoes.css">
</head>

<body>
    <h1>Adoções</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Data de Adoção</th>
                <th>Observações</th>
                <th>Pet</th>
                <th>Usuário</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($adoções as $adoção): ?>
            <tr>
                <td><?php echo htmlspecialchars($adoção['id']); ?></td>
                <td><?php echo htmlspecialchars($adoção['data_adocao']); ?></td>
                <td><?php echo htmlspecialchars($adoção['observacoes']); ?></td>
                <td>
                    <?php echo htmlspecialchars($adoção['pet_nome']); ?>
                    <?php
                        $imagemPet = "imagens/pet/pet" . str_pad($adoção['id'], 5, "0", STR_PAD_LEFT) . ".jpg";
                        if (file_exists($imagemPet)): ?>
                    <img src="<?php echo $imagemPet; ?>" alt="Imagem do Pet" width="100">
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($adoção['usuario_nome']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="home.php">Voltar para a Home</a>
</body>

</html>