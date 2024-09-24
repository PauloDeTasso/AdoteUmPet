<?php
include_once "start.php";

if (isset($_GET['cpf']))
{
    $cpf = $_GET['cpf'] ?? '';
}
else
{
    $cpf = $_SESSION['cpf'];
}

// Conecta ao banco de dados
$conexao = conectar();

// Obtém as informações do usuário logado usando o CPF armazenado na sessão
$cpfUsuario = $_SESSION['cpf'];
$queryUsuario = $conexao->prepare('SELECT cpf, nome, fk_Permissao_id FROM Usuario WHERE cpf = :cpf');
$queryUsuario->execute([':cpf' => $cpfUsuario]);
$usuarioLogado = $queryUsuario->fetch(PDO::FETCH_ASSOC);

if (isset($usuarioLogado['fk_permissao_id']) && !empty($usuarioLogado['fk_permissao_id']))
{
    $permissaoUsuario = $usuarioLogado['fk_permissao_id'];
}
else
{
    echo "Erro: fk_permissao_id não está definido ou está vazio para o usuário.";
    $permissaoUsuario = null;
}

// Consulta o tipo de permissão do usuário (Administrador ou Adotante)
if ($permissaoUsuario)
{
    $queryPermissao = $conexao->prepare('SELECT tipo FROM Permissao WHERE id = :id');
    $queryPermissao->execute([':id' => $permissaoUsuario]);
    $tipoPermissao = $queryPermissao->fetchColumn();

    if ($tipoPermissao === false)
    {
        echo "Erro: Tipo de permissão não encontrado para id = " . htmlspecialchars($permissaoUsuario);
        $tipoPermissao = 'Indefinido';
    }
}
else
{
    echo "Erro: O usuário não possui uma permissão válida.";
    $tipoPermissao = 'Indefinido';
}

// Verifica se o usuário é administrador
$isAdmin = $_SESSION['tipo'] === 'Administrador';

try
{
    // Consulta para obter os dados do usuário com base no CPF
    $query = "
        SELECT u.*, i.url_imagem, e.rua, e.numero, e.bairro, e.cidade, e.estado, e.cep, e.referencia, p.tipo
        FROM Usuario u
        LEFT JOIN Imagem_Usuario i ON u.cpf = i.fk_Usuario_cpf
        LEFT JOIN Enderecos_Usuarios eu ON u.cpf = eu.fk_Usuario_cpf
        LEFT JOIN Endereco e ON eu.fk_Endereco_id = e.id
        LEFT JOIN Permissao p ON u.fk_Permissao_id = p.id
        WHERE u.cpf = :cpf
    ";
    $stmt = $conexao->prepare($query);
    $stmt->bindParam(':cpf', $cpf, PDO::PARAM_STR);
    $stmt->execute();

    // Verifica se o usuário existe
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario)
    {
        echo "Usuário não encontrado!";
        exit;
    }
}
catch (PDOException $e)
{
    echo "Erro ao buscar usuário: " . $e->getMessage();
    exit;
}
?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Usuário</title>
    <link rel="stylesheet" href="css/usuario/usuario_selecionar.css">
    <script>
        function toggleEndereco() {
            var enderecoFields = document.getElementById('endereco-fields');
            var toggleInput = document.getElementsByName('enderecoToggle')[0];
            if (enderecoFields.style.display === 'none') {
                enderecoFields.style.display = 'block';
                toggleInput.value = 'true';
            } else {
                enderecoFields.style.display = 'none';
                toggleInput.value = 'false';
            }
        }
    </script>

</head>

<body>
    <?php include 'cabecalho.php'; ?>

    <section class="cabecalho">
        <h3>Informações detalhadas</h3>
    </section>

    <section class="sessaoPrincipal">

        <div class="usuario-detalhes">
            <?php if (!empty($usuario['url_imagem'])): ?>
                <img src="<?= htmlspecialchars($usuario['url_imagem']); ?>"
                    alt="Foto de <?= htmlspecialchars($usuario['nome']); ?>" class="usuario-foto-grande">
            <?php else: ?>
                <img src="imagens/usuarios/default.jpg" alt="Foto padrão" class="usuario-foto-grande">
            <?php endif; ?>


            <h4>Nome: <?= htmlspecialchars($usuario['nome']); ?></h4>
            <p>Email: <?= htmlspecialchars($usuario['email']); ?></p>
            <p>Telefone: <?= htmlspecialchars($usuario['telefone']); ?></p>
            <p>Data de Nascimento: <?= htmlspecialchars($usuario['data_nascimento']); ?></p>
            <p>Endereço:
                <?= htmlspecialchars($usuario['rua']) . ', ' . htmlspecialchars($usuario['numero']) . ', ' . htmlspecialchars($usuario['bairro']) . ', ' . htmlspecialchars($usuario['cidade']) . ' - ' . htmlspecialchars($usuario['estado']) . ', ' . htmlspecialchars($usuario['cep']); ?>
            </p>

            <?php if ($isAdmin) : ?>

                <div id="linkBtn">
                    <button class="btnAtualizar"> <a
                            href="usuario_editar_dados.php?cpf=<?= htmlspecialchars($cpf); ?>">Editar</a></button>
                </div>

                <form class="formRemover" method="get" action="usuario_remover.php"
                    onsubmit="return confirm('Tem certeza que deseja remover esta conta?');">
                    <fieldset>
                        <legend>Perigo:</legend>
                        <input type="hidden" name="cpf" value="<?= htmlspecialchars($cpf); ?>">
                        <button id="btnRemover" type="submit">Remover Conta</button>
                    </fieldset>
                </form>

            <?php endif; ?>

        </div>

        <a href="usuarios.php?tipo=vigilante">
            <button class="btnVoltar">Lista de Vigilantes Sanitários</button></a>

        <?php if ($isAdmin): ?>
            <a href="usuarios.php">
                <button class="btnVoltar">Lista de Adotantes</button></a>
        <?php endif; ?>

    </section>

    <?php include 'rodape.php'; ?>
</body>

</html>