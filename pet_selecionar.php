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

// Obtém o brinco do pet selecionado
$brinco = $_GET['brinco'] ?? null;

if ($brinco)
{
    // Função para obter os dados do pet selecionado
    function obterPetPorBrinco($pdo, $brinco)
    {
        $sql = "SELECT p.brinco, p.nome, p.sexo, p.idade, p.raca, p.pelagem, p.local_resgate, 
                       p.data_resgate, p.data_cadastro, p.status, p.informacoes, i.url_imagem
                FROM Pet p
                LEFT JOIN Imagem_Pet i ON p.brinco = i.fk_Pet_brinco
                WHERE p.brinco = :brinco";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':brinco', $brinco, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $pet = obterPetPorBrinco($pdo, $brinco);
}

if (!$pet)
{
    echo "Pet não encontrado.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pet['nome']) ?></title>
    <link rel="stylesheet" href="css/pet/pet_selecionar.css">
    <script>
        function habilitarEdicao() {
            // Habilita os campos para edição
            document.querySelectorAll('.editavel').forEach(campo => campo.disabled = false);

            // Troca o botão de "Editar" para "Atualizar"
            document.getElementById('editarBtn').style.display = 'none';
            document.getElementById('atualizarBtn').style.display = 'inline-block';
        }
    </script>
</head>

<body>
    <?php include 'cabecalho.php'; ?>

    <div class="container">
        <div class="pet-detalhes">
            <form action="pet_atualizar.php" method="POST">
                <div class="pet-imagem">
                    <img src="<?= htmlspecialchars($pet['url_imagem']) ?>"
                        alt="Imagem de <?= htmlspecialchars($pet['nome']) ?>">
                </div>

                <div class="pet-info">
                    <h1><input type="text" name="nome" value="<?= htmlspecialchars($pet['nome']) ?>"
                            class="editavel" disabled></h1>
                    <p><strong>Raça:</strong> <input type="text" name="raca"
                            value="<?= htmlspecialchars($pet['raca']) ?>" class="editavel" disabled></p>
                    <p><strong>Idade:</strong> <input type="number" name="idade"
                            value="<?= htmlspecialchars($pet['idade']) ?>" class="editavel" disabled></p>
                    <p><strong>Pelagem:</strong> <input type="text" name="pelagem"
                            value="<?= htmlspecialchars($pet['pelagem']) ?>" class="editavel" disabled></p>

                    <!-- Sexo com cor diferenciada -->
                    <p><strong>Sexo:</strong>
                        <select name="sexo" class="editavel" disabled>
                            <option value="M" <?= htmlspecialchars($pet['sexo']) === 'M' ? 'selected' : '' ?>>Macho
                            </option>
                            <option value="F" <?= htmlspecialchars($pet['sexo']) === 'F' ? 'selected' : '' ?>>Fêmea
                            </option>
                        </select>
                    </p>

                    <p><strong>Local de Resgate:</strong> <input type="text" name="local_resgate"
                            value="<?= htmlspecialchars($pet['local_resgate']) ?>" class="editavel" disabled></p>
                    <p><strong>Data de Resgate:</strong> <input type="date" name="data_resgate"
                            value="<?= htmlspecialchars($pet['data_resgate']) ?>" class="editavel" disabled></p>
                    <p><strong>Informações Adicionais:</strong> <textarea name="informacoes" class="editavel"
                            disabled><?= htmlspecialchars($pet['informacoes']) ?></textarea></p>

                    <input type="hidden" name="brinco" value="<?= htmlspecialchars($pet['brinco']) ?>">

                    <?php if ($_SESSION['tipo'] === 'Administrador'): ?>
                        <button type="button" id="editarBtn" class="btn" onclick="habilitarEdicao()">Editar</button>
                        <button type="submit" id="atualizarBtn" class="btn" style="display: none;">Atualizar</button>
                        <a href="pet_remover.php?brinco=<?= $pet['brinco'] ?>" class="btn"
                            onclick="return confirm('Tem certeza que deseja remover este pet?');">Remover</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</body>

</html>