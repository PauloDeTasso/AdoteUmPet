<?php
session_start();
require_once 'conexao_db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['cpf']))
{
    header('Location: login.php');
    exit();
}

// Conecta ao banco de dados
$pdo = conectar();

// Obtém as informações do usuário logado usando o CPF armazenado na sessão
$cpfUsuario = $_SESSION['cpf'];
$queryUsuario = $pdo->prepare('SELECT cpf, nome, fk_permissao_id FROM Usuario WHERE cpf = :cpf');
$queryUsuario->execute([':cpf' => $cpfUsuario]);
$usuarioLogado = $queryUsuario->fetch(PDO::FETCH_ASSOC);

if (!$usuarioLogado)
{
    session_destroy();
    header('Location: login.php');
    exit();
}

// Verifica se o campo fk_permissao_id está presente e se o valor é válido
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
    $queryPermissao = $pdo->prepare('SELECT tipo FROM Permissao WHERE id = :id');
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

// Funções para obter contagens específicas
function contarUsuarios($pdo)
{
    $query = $pdo->query('SELECT COUNT(*) FROM Usuario');
    return $query->fetchColumn();
}

function contarVigilantes($pdo)
{
    $query = $pdo->query("SELECT COUNT(*) FROM Usuario WHERE fk_permissao_id = (SELECT id FROM Permissao WHERE tipo = 'Administrador')");
    return $query->fetchColumn();
}

function contarPetsCadastrados($pdo)
{
    $query = $pdo->query('SELECT COUNT(*) FROM Pet');
    return $query->fetchColumn();
}

function contarPetsAdotados($pdo)
{
    $query = $pdo->query("SELECT COUNT(*) FROM Pet WHERE status = 'ADOTADO'");
    return $query->fetchColumn();
}

function contarPetsDisponiveis($pdo)
{
    $query = $pdo->query("SELECT COUNT(*) FROM Pet WHERE status = 'ADOTÁVEL'");
    return $query->fetchColumn();
}

// Obter estatísticas do sistema
$totalUsuarios = contarUsuarios($pdo);
$totalVigilantes = contarVigilantes($pdo);
$totalPetsCadastrados = contarPetsCadastrados($pdo);
$totalPetsAdotados = contarPetsAdotados($pdo);
$totalPetsDisponiveis = contarPetsDisponiveis($pdo);

?>

<!DOCTYPE html>
<html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home - Adote um Pet</title>
        <link rel="stylesheet" href="css/home.css">
    </head>

    <body>
        <header>
            <!-- Exibe o nome do usuário logado -->
            <h1>Bem-vindo, <?= htmlspecialchars($usuarioLogado['nome'], ENT_QUOTES, 'UTF-8'); ?>!</h1>
            <nav>
                <ul>
                    <li><a href="logout.php">Sair</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <!-- Seção de Ações e Menus -->
            <section class="acoes">
                <h2>Menu</h2>
                <ul>
                    <!-- Categoria de Pets -->
                    <li><strong>Pets</strong>
                        <ul>
                            <li><a href="pets.php">Pesquisar Pets Disponíveis</a></li>
                            <?php if ($tipoPermissao == 'Administrador') : ?>
                            <li><a href="pet_cadastrar.php">Cadastrar Novo Pet</a></li>
                            <li><a href="pet_editar.php">Editar Pet</a></li>
                            <li><a href="pet_remover.php">Remover Pet</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <!-- Categoria de Usuários -->
                    <li><strong>Usuários</strong>
                        <ul>
                            <?php if ($tipoPermissao == 'Administrador') : ?>
                            <li><a href="usuarios.php">Pesquisar Usuários</a></li>
                            <li><a href="usuario_cadastrar.php">Cadastrar Novo Usuário</a></li>
                            <?php endif; ?>
                            <?php if ($tipoPermissao == 'Adotante' || $tipoPermissao == 'Administrador') : ?>
                            <li><a
                                    href="usuario_editar.php?cpf=<?= htmlspecialchars($usuarioLogado['cpf'], ENT_QUOTES, 'UTF-8'); ?>">Editar
                                    Perfil</a></li>
                            <li><a
                                    href="usuario_remover.php?cpf=<?= htmlspecialchars($usuarioLogado['cpf'], ENT_QUOTES, 'UTF-8'); ?>">Remover
                                    Conta</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <!-- Categoria de Vigilantes Sanitários -->
                    <li><strong>Vigilantes Sanitários</strong>
                        <ul>
                            <li><a href="vigilantes.php">Pesquisar Vigilantes Sanitários</a></li>
                            <?php if ($tipoPermissao == 'Administrador') : ?>
                            <li><a href="vigilante_cadastrar.php">Cadastrar Novo Vigilante</a></li>
                            <li><a href="vigilante_editar.php">Editar Vigilante</a></li>
                            <li><a href="vigilante_remover.php">Remover Vigilante</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <!-- Categoria de Adoções -->
                    <li><strong>Adoções</strong>
                        <ul>
                            <li><a href="adocoes.php">Pesquisar Adoções</a></li>
                            <?php if ($tipoPermissao == 'Administrador') : ?>
                            <li><a href="adocao_cadastrar.php">Cadastrar Nova Adoção</a></li>
                            <li><a href="adocao_editar.php">Editar Adoção</a></li>
                            <li><a href="adocao_remover.php">Remover Adoção</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <!-- Categoria de Sistema -->
                    <li><strong>Sistema</strong>
                        <ul>
                            <li><a href="configuracoes.php">Configurações do Sistema</a></li>
                        </ul>
                    </li>
                </ul>
            </section>

            <!-- Seção de Estatísticas do sistema -->
            <section class="estatisticas">
                <h2>Estatísticas</h2>
                <p>Usuários cadastrados: <strong><?= htmlspecialchars($totalUsuarios, ENT_QUOTES, 'UTF-8'); ?></strong>
                </p>
                <hr>
                <p>Vigilantes sanitários:
                    <strong><?= htmlspecialchars($totalVigilantes, ENT_QUOTES, 'UTF-8'); ?></strong>
                </p>
                <hr>
                <p>Pets cadastrados:
                    <strong><?= htmlspecialchars($totalPetsCadastrados, ENT_QUOTES, 'UTF-8'); ?></strong>
                </p>
                <p>Pets adotados: <strong><?= htmlspecialchars($totalPetsAdotados, ENT_QUOTES, 'UTF-8'); ?></strong></p>
                <p>Pets disponíveis:
                    <strong><?= htmlspecialchars($totalPetsDisponiveis, ENT_QUOTES, 'UTF-8'); ?></strong>
                </p>
            </section>

        </main>

        <footer>
            <!-- Imagem de logotipo da Vigilância Sanitária -->
            <img src="imagens/sistema/img_sistema001.jpg" alt="Logo Vigilância Sanitária">
            <p>Vigilância Sanitária - Recolhendo cães abandonados e promovendo adoção responsável.</p>
        </footer>

        <script>
        // Função para abrir/fechar submenus ao clicar
        document.querySelectorAll('.acoes ul li strong').forEach(function(menuItem) {
            menuItem.addEventListener('click', function() {
                const submenu = this.nextElementSibling;
                submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
            });
        });
        </script>
    </body>

</html>