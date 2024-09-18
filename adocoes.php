<?php
require 'conexao_db.php';

// Definindo o número de registros por página
$registrosPorPagina = 10;

// Capturando o número da página atual
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$paginaAtual = $paginaAtual > 0 ? $paginaAtual : 1;

// Calculando o offset
$offset = ($paginaAtual - 1) * $registrosPorPagina;

try {
    // Conecta ao banco de dados
    $pdo = conectar();

    // Consulta para obter as adoções com dados dos adotantes e pets
    $sql = 'SELECT a.id, a.data_adocao, a.observacoes, u.nome AS adotante_nome, p.nome AS pet_nome, 
                   (SELECT url_imagem FROM Imagem_Usuario WHERE fk_Usuario_cpf = u.cpf LIMIT 1) AS adotante_imagem,
                   (SELECT url_imagem FROM Imagem_Pet WHERE fk_Pet_brinco = p.brinco LIMIT 1) AS pet_imagem
            FROM Adocao a
            JOIN Usuario u ON a.fk_Usuario_cpf = u.cpf
            JOIN Pet p ON a.fk_Pet_brinco = p.brinco
            ORDER BY a.data_adocao DESC
            LIMIT :limit OFFSET :offset';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $registrosPorPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $adoções = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para contar o total de adoções
    $sqlCount = 'SELECT COUNT(*) FROM Adocao';
    $stmtCount = $pdo->query($sqlCount);
    $totalAdoções = $stmtCount->fetchColumn();

    // Calculando o número total de páginas
    $totalPaginas = ceil($totalAdoções / $registrosPorPagina);
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adoções</title>
    <link rel="stylesheet" href="css/adocao/adocoes.css">
</head>

<body>
    <?php include 'cabecalho.php'; ?>

    <div class="container">
        <a href="adocao_cadastrar.php" class="btn">Realizar uma Adoção</a>

        <table>
            <thead>
                <tr>
                    <th>Data da Adoção</th>
                    <th>Adotante</th>
                    <th>Pet</th>
                    <th>Observações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($adoções)): ?>
                <?php foreach ($adoções as $adoção): ?>
                <tr>
                    <td><?= htmlspecialchars($adoção['data_adocao']) ?></td>
                    <td>
                        <img src="<?= htmlspecialchars($adoção['adotante_imagem']) ?>" alt="Imagem do Adotante"
                            class="imagem-pet">
                        <?= htmlspecialchars($adoção['adotante_nome']) ?>
                    </td>
                    <td>
                        <img src="<?= htmlspecialchars($adoção['pet_imagem']) ?>" alt="Imagem do Pet"
                            class="imagem-pet">
                        <?= htmlspecialchars($adoção['pet_nome']) ?>
                    </td>
                    <td><?= htmlspecialchars($adoção['observacoes']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="4">Nenhuma adoção encontrada.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="paginas">
            <?php if ($paginaAtual > 1): ?>
            <a href="adocoes.php?pagina=<?= $paginaAtual - 1 ?>" class="btn">Anterior</a>
            <?php endif; ?>

            <?php if ($paginaAtual < $totalPaginas): ?>
            <a href="adocoes.php?pagina=<?= $paginaAtual + 1 ?>" class="btn">Próxima</a>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>