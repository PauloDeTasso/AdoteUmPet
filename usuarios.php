<?php
include 'conexao_db.php';

$conn = conectar();

// Obtém o tipo de usuário a partir dos parâmetros da URL
$tipoUsuario = isset($_GET['tipo']) ? $_GET['tipo'] : 'adotante';

try
{
    // Define a consulta SQL de acordo com o tipo de usuário (adotante ou administrador)
    if ($tipoUsuario === 'vigilante')
    {
        // Pesquisa de vigilantes sanitários (administradores)
        $query = "
        SELECT u.cpf, u.nome, MAX(i.url_imagem) AS url_imagem 
        FROM Usuario u
        JOIN Permissao p ON u.fk_Permissao_id = p.id 
        LEFT JOIN Imagem_Usuario i ON u.cpf = i.fk_Usuario_cpf
        WHERE p.tipo = 'Administrador'
        GROUP BY u.cpf, u.nome
        ORDER BY u.nome
        ";
        $titulo = 'Vigilantes Sanitários';  // Título da página para os administradores
    }
    else
    {
        // Pesquisa padrão de adotantes
        $query = "
        SELECT u.cpf, u.nome, MAX(i.url_imagem) AS url_imagem 
        FROM Usuario u
        JOIN Permissao p ON u.fk_Permissao_id = p.id 
        LEFT JOIN Imagem_Usuario i ON u.cpf = i.fk_Usuario_cpf
        WHERE p.tipo = 'Adotante'
        GROUP BY u.cpf, u.nome
        ORDER BY u.nome
        ";
        $titulo = 'Adotantes';  // Título da página para os adotantes
    }

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e)
{
    echo "Erro ao buscar usuários: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($titulo); ?> Cadastrados</title>
        <link rel="stylesheet" href="css/usuario/usuarios.css">
    </head>

    <body>

        <?php include_once 'cabecalho.php'; ?>

        <section class="cabecalho">
            <h3><?= htmlspecialchars($titulo); ?></h3>
        </section>

        <div class="usuarios-container">
            <?php if (count($usuarios) > 0): ?>
            <?php foreach ($usuarios as $usuario): ?>
            <div class="usuario-card">
                <!-- Verifica se há imagem associada ao usuário -->
                <?php if (!empty($usuario['url_imagem'])): ?>
                <!-- Usa a URL da imagem direto do banco de dados -->
                <img src="<?= htmlspecialchars($usuario['url_imagem']); ?>"
                    alt="Foto de <?= htmlspecialchars($usuario['nome']); ?>" class="usuario-foto">
                <?php else: ?>
                <!-- Exibe uma imagem padrão se não houver URL de imagem -->
                <img src="imagens/usuarios/default.jpg" alt="Foto padrão" class="usuario-foto">
                <?php endif; ?>

                <p><?= htmlspecialchars($usuario['nome']); ?></p>
                <a href="usuario_selecionar.php?cpf=<?= htmlspecialchars($usuario['cpf']); ?>"
                    class="btn-ver-usuario">Ver Detalhes</a>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p>Nenhum usuário cadastrado no momento.</p>
            <?php endif; ?>
        </div>

        <?php include 'rodape.php'; ?>

    </body>

</html>