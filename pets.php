<?php
session_start();
require_once 'conexao_db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['cpf'])) {
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
    <script>
    // Função para redirecionar para a página pet_selecionar.php com o brinco do pet
    function verDetalhesPet(brinco) {
        window.location.href = "pet_selecionar.php?brinco=" + brinco;
    }

    // Função para redirecionar para a página adocao_cadastrar.php com o brinco do pet
    function adotarPet(brinco) {
        let tipoUsuario = "<?php echo $tipoUsuario; ?>"; // Passa o tipo de usuário para o JavaScript
        if (tipoUsuario === 'Administrador') {
            window.location.href = "adocao_cadastrar_adm.php?pet=" + brinco + "&adotante=" +
                "<?php echo $_SESSION['cpf']; ?>";
        } else if (tipoUsuario === 'Adotante') {
            window.location.href = "adocao_cadastrar.php?pet=" + brinco + "&adotante=" +
                "<?php echo $_SESSION['cpf']; ?>";
        }
    }
    </script>
</head>

<body>

    <?php include 'cabecalho.php'; ?>

    <section class="cabecalho">
        <h3>Pets Disponíveis para Adoção</h3>
    </section>

    <div class="container">

        <?php if ($tipoUsuario === 'Administrador'): ?>
        <div class="btn-container">
            <a href="pet_cadastrar.php" class="btn">Cadastrar Novo Pet</a>
        </div>
        <?php endif; ?>

        <div class="pets-list">
            <?php if (count($pets) > 0): ?>
            <?php foreach ($pets as $pet): ?>
            <!-- Adiciona a classe com base no sexo do pet (Macho ou Fêmea) -->
            <div class="pet-item <?= strtolower($pet['sexo']) === 'm' ? 'macho' : 'femea' ?>">
                <a href="pet_selecionar.php?brinco=<?= $pet['brinco'] ?>" class="pet-link">
                    <img src="<?= htmlspecialchars($pet['url_imagem']) ?>"
                        alt="Imagem de <?= htmlspecialchars($pet['nome']) ?>" class="pet-img">
                </a>
                <div class="pet-info">
                    <h3><?= htmlspecialchars($pet['nome']) ?></h3>
                    <p><strong>Idade:</strong> <?= htmlspecialchars($pet['idade']) ?: 'Não informado' ?></p>
                    <p><strong>Sexo:</strong> <?= strtolower($pet['sexo']) === 'm' ? 'Macho' : 'Fêmea' ?></p>
                    <p><strong>Raça:</strong> <?= htmlspecialchars($pet['raca']) ?: 'Não informado' ?></p>
                    <p><strong>Informações:</strong>
                        <?= htmlspecialchars($pet['informacoes']) ?: 'Não informado' ?></p>
                </div>
                <!-- Adiciona o botão para adoção -->
                <button onclick="adotarPet(<?= $pet['brinco'] ?>)" class="btn-adotar">Adotar Pet</button>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p>Não há pets cadastrados no momento.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'rodape.php'; ?>

</body>

</html>