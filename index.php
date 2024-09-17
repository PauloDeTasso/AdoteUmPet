<?php
include('conexao_db.php');

// Número de itens por página
$itensPorPagina = 10;

// Página atual (recebida via GET, padrão é 1 se não fornecida)
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$paginaAtual = max($paginaAtual, 1); // Garante que a página atual não seja menor que 1

// Função para contar o total de pets disponíveis
function contarPetsDisponiveis()
{
    $pdo = conectar();
    $sql = "SELECT COUNT(*) AS total FROM Pet WHERE status = 'ADOTÁVEL'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

// Função para contar adoções realizadas
function contarAdocoesRealizadas()
{
    $pdo = conectar();
    $sql = "SELECT COUNT(*) AS total FROM Adocao";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

// Função para obter as últimas adoções (com nome e imagem do pet e adotante)
function obterUltimasAdocoes()
{
    $pdo = conectar();
    $sql = "SELECT p.nome AS pet_nome, p.brinco, p.status, u.nome AS usuario_nome, u.cpf, a.data_adocao,
                   ip.url_imagem AS imagem_pet, iu.url_imagem AS imagem_adotante
            FROM Adocao a
            JOIN Pet p ON a.fk_Pet_brinco = p.brinco
            JOIN Usuario u ON a.fk_Usuario_cpf = u.cpf
            LEFT JOIN Imagem_Pet ip ON ip.fk_Pet_brinco = p.brinco
            LEFT JOIN Imagem_Usuario iu ON iu.fk_Usuario_cpf = u.cpf
            ORDER BY a.data_adocao DESC
            LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter pets disponíveis para adoção com paginação
function obterPetsDisponiveis($paginaAtual, $itensPorPagina)
{
    $pdo = conectar();
    $offset = ($paginaAtual - 1) * $itensPorPagina;
    $sql = "SELECT p.nome AS pet_nome, p.brinco, ip.url_imagem AS imagem_pet
            FROM Pet p
            LEFT JOIN Imagem_Pet ip ON ip.fk_Pet_brinco = p.brinco
            WHERE p.status = 'ADOTÁVEL'
            LIMIT :itensPorPagina OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':itensPorPagina', $itensPorPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter pets já adotados
function obterPetsAdotados()
{
    $pdo = conectar();
    $sql = "SELECT p.nome AS pet_nome, p.brinco, ip.url_imagem AS imagem_pet
            FROM Pet p
            LEFT JOIN Imagem_Pet ip ON ip.fk_Pet_brinco = p.brinco
            WHERE p.status = 'ADOTADO'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Armazena os resultados das consultas em variáveis
$quantidadePetsDisponiveis = contarPetsDisponiveis();
$quantidadeAdocoesRealizadas = contarAdocoesRealizadas();
$ultimasAdocoes = obterUltimasAdocoes();
$petsDisponiveis = obterPetsDisponiveis($paginaAtual, $itensPorPagina);
$petsAdotados = obterPetsAdotados();

// Calcular o número total de páginas
$totalPaginas = ceil($quantidadePetsDisponiveis / $itensPorPagina);
?>

<!DOCTYPE html>
<html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Adote um Pet</title>
        <link rel="stylesheet" href="css/index.css">
    </head>

    <body>

        <header>
            <h1>Adote um Pet</h1>
            <p>Cuidados e adoção de pets pela Vigilância Sanitária</p>
            <p>PREFEITURA MUNICIPAL DE IMACULADA - PB</p>
        </header>

        <section class="login-cadastro">
            <p><a href="login.php" class="btn">Login</a> ou <a href="usuario_cadastrar_se.php"
                    class="btn">Cadastre-se</a></p>
        </section>

        <h3>Seja bem-vindo(a)!</h3>

        <section class="estatisticas">

            <ul>
                <li>Pets disponíveis: <strong><?php echo $quantidadePetsDisponiveis; ?></strong></li>
                <li>Adoções: <strong><?php echo $quantidadeAdocoesRealizadas; ?></strong></li>
            </ul>
        </section>

        <section class="pets-disponiveis">
            <h2>Pets Disponíveis para Adoção</h2>
            <section>
                <?php if (count($petsDisponiveis) > 0): ?>
                <?php foreach ($petsDisponiveis as $pet): ?>
                <div>
                    <img src="<?= htmlspecialchars($pet['imagem_pet']); ?>"
                        alt="Imagem de <?php echo htmlspecialchars($pet['pet_nome']); ?>">
                    <strong><?php echo htmlspecialchars($pet['pet_nome']); ?></strong>
                </div>
                <?php endforeach; ?>
                <!-- Mensagem para mais itens -->
                <?php if ($totalPaginas > 1): ?>
                <p>Mostrando
                    <?php echo min($itensPorPagina, $quantidadePetsDisponiveis - (($paginaAtual - 1) * $itensPorPagina)); ?>
                    pets de <?php echo $quantidadePetsDisponiveis; ?> disponíveis.</p>
                <!-- Paginação -->
                <div class="paginacao">
                    <?php if ($paginaAtual > 1): ?>
                    <a href="?pagina=<?php echo $paginaAtual - 1; ?>" class="btn">Anterior</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <a href="?pagina=<?php echo $i; ?>" class="btn"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($paginaAtual < $totalPaginas): ?>
                    <a href="?pagina=<?php echo $paginaAtual + 1; ?>" class="btn">Próximo</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <p>Não há pets disponíveis para adoção no momento.</p>
                <?php endif; ?>
            </section>
        </section>

        <section class="pets-adotados">
            <h2>Pets Adotados</h2>
            <ul>
                <?php foreach ($petsAdotados as $pet): ?>
                <li>
                    <img src="<?php echo htmlspecialchars($pet['imagem_pet']); ?>"
                        alt="Imagem de <?php echo htmlspecialchars($pet['pet_nome']); ?>"
                        style="width:100px;height:100px;">
                    <strong><?php echo htmlspecialchars($pet['pet_nome']); ?></strong>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="ultimas-adocoes">
            <h2>Últimas Adoções</h2>
            <ul>
                <?php foreach ($ultimasAdocoes as $adocao): ?>
                <li>
                    <img src="<?php echo htmlspecialchars($adocao['imagem_pet']); ?>"
                        alt="Imagem de <?php echo htmlspecialchars($adocao['pet_nome']); ?>"
                        style="width:100px;height:100px;">
                    Pet <strong><?php echo htmlspecialchars($adocao['pet_nome']); ?></strong> adotado por
                    <img src="<?php echo htmlspecialchars($adocao['imagem_adotante']); ?>"
                        alt="Imagem de <?php echo htmlspecialchars($adocao['usuario_nome']); ?>"
                        style="width:50px;height:50px;">
                    <strong><?php echo htmlspecialchars($adocao['usuario_nome']); ?></strong> em
                    <strong><?php echo htmlspecialchars(date('d/m/Y', strtotime($adocao['data_adocao']))); ?></strong>.
                </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="login-cadastro">
            <p><a href="login.php" class="btn">Login</a> ou <a href="usuario_cadastrar_se.php"
                    class="btn">Cadastre-se</a></p>
        </section>

        <footer>
            <div class="logo">
                <img src="imagens/sistema/icones/icone001.jpg" alt="Logo Vigilância Sanitária" />
                <p>Vigilância Sanitária - Prefeitura de Imaculada-PB</p>
            </div>
        </footer>
    </body>

</html>