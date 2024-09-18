<?php
session_start();
require_once 'conexao_db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['cpf']))
{
    header('Location: login.php');
    exit();
}

$pdo = conectar();

// Função para obter a lista de pets
function obterPets($pdo)
{
    $sql = "SELECT p.brinco, p.nome, p.sexo, p.idade, p.raca, p.pelagem, p.local_resgate, 
                   p.data_resgate, p.data_cadastro, p.status, p.informacoes, i.url_imagem
            FROM Pet p
            LEFT JOIN Imagem_Pet i ON p.brinco = i.fk_Pet_brinco
            WHERE p.status = 'ADOTÁVEL'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Verifica o tipo de usuário (Administrador ou adotante)
$tipoUsuario = $_SESSION['tipo'];

// Obtém a lista de pets
$pets = obterPets($pdo);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pets Disponíveis</title>
    <link rel="stylesheet" href="css/pet/pets.css">
    <style>
        .pet-item {
            cursor: pointer;
        }
    </style>
    <script>
        // Função para redirecionar para a página pet_selecionar.php com o brinco do pet
        function selecionarPet(brinco) {
            window.location.href = "pet_selecionar.php?brinco=" + brinco;
        }
    </script>
</head>

<body>
    <?php include 'cabecalho.php'; ?>

    <div class="container">
        <h2>Pets Disponíveis para Adoção</h2>

        <?php if ($tipoUsuario === 'Administrador'): ?>
            <a href="pet_cadastrar.php" class="btn">Cadastrar Novo Pet</a>
        <?php endif; ?>

        <div class="pets-list">
            <?php if (count($pets) > 0): ?>
                <?php foreach ($pets as $pet): ?>
                    <!-- Adiciona a classe com base no sexo do pet (Macho ou Fêmea) -->
                    <div class="pet-item <?= strtolower($pet['sexo']) === 'm' ? 'macho' : 'femea' ?>"
                        onclick="selecionarPet(<?= $pet['brinco'] ?>)">
                        <img src="<?= htmlspecialchars($pet['url_imagem']) ?>"
                            alt="Imagem de <?= htmlspecialchars($pet['nome']) ?>" class="pet-img">
                        <div class="pet-info">
                            <h3><?= htmlspecialchars($pet['nome']) ?></h3>
                            <p><strong>Raça:</strong> <?= htmlspecialchars($pet['raca']) ?: 'Não informado' ?></p>
                            <p><strong>Idade:</strong> <?= htmlspecialchars($pet['idade']) ?: 'Não informado' ?></p>
                            <p><strong>Pelagem:</strong> <?= htmlspecialchars($pet['pelagem']) ?: 'Não informado' ?></p>
                            <p><strong>Status:</strong> <?= htmlspecialchars($pet['status']) ?></p>
                            <p><strong>Local de Resgate:</strong>
                                <?= htmlspecialchars($pet['local_resgate']) ?: 'Não informado' ?></p>
                            <p><strong>Data de Resgate:</strong>
                                <?= htmlspecialchars($pet['data_resgate']) ?: 'Não informada' ?></p>
                            <p><strong>Data de Cadastro:</strong>
                                <?= htmlspecialchars($pet['data_cadastro']) ?: 'Não informada' ?></p>
                            <p><strong>Informações Adicionais:</strong>
                                <?= htmlspecialchars($pet['informacoes']) ?: 'Não informado' ?></p>

                            <?php if ($tipoUsuario === 'Administrador'): ?>
                                <a href="pet_editar.php?brinco=<?= $pet['brinco'] ?>" class="btn">Editar</a>
                                <a href="pet_remover.php?brinco=<?= $pet['brinco'] ?>" class="btn"
                                    onclick="return confirm('Tem certeza que deseja remover este pet?');">Remover</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Não há pets cadastrados no momento.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>