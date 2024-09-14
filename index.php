<?php
// Inclui o arquivo de conexão com o banco de dados
include('conexao_db.php');

// Função para contar pets disponíveis para adoção
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

// Função para obter as últimas adoções
function obterUltimasAdocoes()
{
    $pdo = conectar();
    $sql = "SELECT p.nome AS pet_nome, u.nome AS usuario_nome, a.data_adocao
            FROM Adocao a
            JOIN Pet p ON a.fk_Pet_id = p.id
            JOIN Usuario u ON a.fk_Usuario_cpf = u.cpf
            ORDER BY a.data_adocao DESC
            LIMIT 5"; // Ajuste o LIMIT conforme necessário
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Armazena os resultados das consultas em variáveis
$quantidadePetsDisponiveis = contarPetsDisponiveis();
$quantidadeAdocoesRealizadas = contarAdocoesRealizadas();
$ultimasAdocoes = obterUltimasAdocoes();
?>

<!DOCTYPE html>
<html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bem-vindo ao Adote um Pet</title>
        <link rel="stylesheet" href="css/index.css">
    </head>

    <body>
        <header>
            <h1>Bem-vindo ao Adote um Pet</h1>
            <p>Cuidados e adoção de pets pela Vigilância Sanitária</p>
        </header>

        <section class="estatisticas">
            <h2>Informações:</h2>
            <ul>
                <li>Pets disponíveis pra adoção: <strong><?php echo $quantidadePetsDisponiveis; ?></strong></li>
                <li>Adoções realizadas: <strong><?php echo $quantidadeAdocoesRealizadas; ?></strong></li>
            </ul>
        </section>

        <section class="noticias">
            <h2>Últimas Adoções</h2>
            <ul>
                <?php foreach ($ultimasAdocoes as $adocao): ?>
                <li>
                    Pet <strong><?php echo htmlspecialchars($adocao['pet_nome']); ?></strong> adotado por
                    <strong><?php echo htmlspecialchars($adocao['usuario_nome']); ?></strong> em
                    <strong><?php echo htmlspecialchars(date('d/m/Y', strtotime($adocao['data_adocao']))); ?></strong>.
                </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="login-cadastro">
            <p><a href="login.php" class="btn">Login</a> ou <a href="usuario_cadastrar.php" class="btn">Cadastre-se</a>
            </p>
        </section>

        <footer>
            <div class="logo">
                <img src="imagens/sistema/icones/icone001.jpg" alt="Logo Vigilância Sanitária" />
                <p>Vigilância Sanitária - Prefeitura de Imaculada-PB</p>
            </div>
        </footer>
    </body>

</html>