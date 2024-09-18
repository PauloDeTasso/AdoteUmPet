<?php
// Inclua o arquivo de conexão
include 'conexao_db.php';

// Obtenha a instância do PDO
$pdo = conectar();

// Função para obter usuários adotantes
function getUsuariosAdotantes($pdo)
{
    $sql = '
        SELECT u.cpf, u.nome, i.url_imagem AS imagem_url
        FROM Usuario u
        LEFT JOIN Imagem_Usuario i ON u.cpf = i.fk_Usuario_cpf
        WHERE u.fk_Permissao_id IN (SELECT id FROM Permissao WHERE tipo IN (:tipo_adotante, :tipo_administrador))
        AND u.status = :status
    ';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':tipo_adotante' => 'Adotante',
        ':tipo_administrador' => 'Administrador',
        ':status' => 'ATIVO'
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter pets disponíveis
function getPetsDisponiveis($pdo)
{
    $sql = '
        SELECT p.brinco, p.nome, i.url_imagem AS imagem_url
        FROM Pet p
        LEFT JOIN Imagem_Pet i ON p.brinco = i.fk_Pet_brinco
        WHERE p.status = :status
    ';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':status' => 'ADOTÁVEL'
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpfUsuario = filter_var(trim($_POST['usuario']), FILTER_SANITIZE_STRING);
    $brincoPet = filter_var(trim($_POST['pet']), FILTER_SANITIZE_NUMBER_INT);
    $observacoes = filter_var(trim($_POST['observacoes']), FILTER_SANITIZE_STRING);

    // Validação básica dos dados
    if (!empty($cpfUsuario) && !empty($brincoPet)) {
        if (strlen($observacoes) > 255) {
            $mensagem = 'Observações não podem exceder 255 caracteres.';
        } else {
            try {
                // Inicia a transação
                $pdo->beginTransaction();

                // Insere a adoção
                $sql = 'INSERT INTO Adocao (data_adocao, observacoes, fk_Usuario_cpf, fk_Pet_brinco) VALUES (CURRENT_DATE, :observacoes, :cpfUsuario, :brincoPet)';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':observacoes' => $observacoes,
                    ':cpfUsuario' => $cpfUsuario,
                    ':brincoPet' => $brincoPet
                ]);

                // Atualiza o status do pet para "ADOTADO"
                $sqlUpdatePet = 'UPDATE Pet SET status = :status WHERE brinco = :brincoPet';
                $stmtUpdatePet = $pdo->prepare($sqlUpdatePet);
                $stmtUpdatePet->execute([
                    ':status' => 'ADOTADO',
                    ':brincoPet' => $brincoPet
                ]);

                // Commit da transação
                $pdo->commit();
                $mensagem = 'Adoção registrada com sucesso!';
            } catch (PDOException $e) {
                // Rollback em caso de erro
                $pdo->rollBack();
                // Verifica se o erro é relacionado à violação de unicidade
                if ($e->getCode() === '23505') {
                    $mensagem = 'Esse pet já foi adotado por esse adotante. Por favor, selecione outro pet ou adotante.';
                } else {
                    $mensagem = 'Erro ao registrar adoção: ' . $e->getMessage();
                }
            }
        }
    } else {
        $mensagem = 'Por favor, preencha todos os campos obrigatórios.';
    }
}

// Obtém os dados para exibição
$usuariosAdotantes = getUsuariosAdotantes($pdo);
$petsDisponiveis = getPetsDisponiveis($pdo);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Adoção</title>
    <link rel="stylesheet" href="css/adocao/adocao_cadastrar.css">
    <script>
    function atualizarImagem() {
        var usuario = document.getElementById('usuario').value;
        var pet = document.getElementById('pet').value;
        var imagemUsuario = document.getElementById('imagemUsuario');
        var imagemPet = document.getElementById('imagemPet');

        // Atualiza a imagem do adotante
        if (usuario) {
            var imagemUrlUsuario = document.querySelector('option[value="' + usuario + '"]').getAttribute(
            'data-imagem');
            imagemUsuario.src = imagemUrlUsuario ? imagemUrlUsuario : 'imagens/usuarios/default.jpg';
            imagemUsuario.style.display = 'block';
        } else {
            imagemUsuario.style.display = 'none';
        }

        // Atualiza a imagem do pet
        if (pet) {
            var imagemUrlPet = document.querySelector('option[value="' + pet + '"]').getAttribute('data-imagem');
            imagemPet.src = imagemUrlPet ? imagemUrlPet : 'imagens/pets/default.jpeg';
            imagemPet.style.display = 'block';
        } else {
            imagemPet.style.display = 'none';
        }
    }

    function validarFormulario() {
        var usuario = document.getElementById('usuario').value;
        var pet = document.getElementById('pet').value;
        var observacoes = document.getElementById('observacoes').value;
        var mensagem = '';

        if (!usuario) {
            mensagem += 'Por favor, selecione um adotante.\n';
        }
        if (!pet) {
            mensagem += 'Por favor, selecione um pet.\n';
        }
        if (observacoes.length > 255) {
            mensagem += 'Observações não podem exceder 255 caracteres.\n';
        }

        if (mensagem) {
            alert(mensagem);
            return false;
        }
        return true;
    }
    </script>
</head>

<body>

    <?php include "cabecalho.php"; ?>

    <div class="container">
        <h1>Cadastro de Adoção</h1>
        <form action="" method="post" onsubmit="return validarFormulario()">
            <div class="form-group">
                <label for="usuario">Adotante:</label>
                <div class="form-row">
                    <select name="usuario" id="usuario" onchange="atualizarImagem()" required>
                        <option value="">Selecione um adotante</option>
                        <?php foreach ($usuariosAdotantes as $usuario): ?>
                        <option value="<?= htmlspecialchars($usuario['cpf']) ?>"
                            <?= isset($_POST['usuario']) && $_POST['usuario'] === $usuario['cpf'] ? 'selected' : '' ?>
                            data-imagem="<?= htmlspecialchars($usuario['imagem_url']) ?>">
                            <?= htmlspecialchars($usuario['nome']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="image-preview">
                        <img id="imagemUsuario" src="" alt="Imagem do Adotante" style="display:none;">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="pet">Pet:</label>
                <div class="form-row">
                    <select name="pet" id="pet" onchange="atualizarImagem()" required>
                        <option value="">Selecione um pet</option>
                        <?php foreach ($petsDisponiveis as $pet): ?>
                        <option value="<?= htmlspecialchars($pet['brinco']) ?>"
                            <?= isset($_POST['pet']) && $_POST['pet'] === $pet['brinco'] ? 'selected' : '' ?>
                            data-imagem="<?= htmlspecialchars($pet['imagem_url']) ?>">
                            <?= htmlspecialchars($pet['nome']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="image-preview">
                        <img id="imagemPet" src="" alt="Imagem do Pet" style="display:none;">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="observacoes">Observações:</label>
                <textarea name="observacoes" id="observacoes" rows="4" maxlength="255"
                    placeholder="Digite observações adicionais sobre a adoção (máx. 255 caracteres)"></textarea>
            </div>
            <button type="submit">Registrar Adoção</button>
        </form>
        <?php if (isset($mensagem)): ?>
        <p class="message"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>
    </div>
</body>

</html>