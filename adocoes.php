<?php
include '../db/conexao_db.php';

$stmt = $pdo->prepare("SELECT a.id, a.data_adocao, a.observacoes, u.nome AS usuario_nome, p.nome AS pet_nome 
                       FROM Adocao a 
                       JOIN Usuario u ON a.fk_Usuario_cpf = u.cpf 
                       JOIN Pet p ON a.fk_Pet_id = p.id");
$stmt->execute();
$adocoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Lista de Adoções</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Data Adoção</th>
            <th>Observações</th>
            <th>Usuário</th>
            <th>Pet</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($adocoes as $adocao): ?>
        <tr>
            <td><?php echo htmlspecialchars($adocao['id']); ?></td>
            <td><?php echo htmlspecialchars($adocao['data_adocao']); ?></td>
            <td><?php echo htmlspecialchars($adocao['observacoes']); ?></td>
            <td><?php echo htmlspecialchars($adocao['usuario_nome']); ?></td>
            <td><?php echo htmlspecialchars($adocao['pet_nome']); ?></td>
            <td>
                <a href="editar_adocao.php?id=<?php echo $adocao['id']; ?>">Editar</a>
                <a href="remover_adocao.php?id=<?php echo $adocao['id']; ?>"
                    onclick="return confirm('Deseja remover esta adoção?');">Remover</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<a href="adocao_cadastrar.php">Cadastrar Nova Adoção</a>