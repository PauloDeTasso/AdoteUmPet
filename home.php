<?php
session_start();
require_once 'conexao_db.php';

// Verifica usuário na sessão
require_once 'auth.php';
verificarSessao();

$pdo = conectar();

// Obtém as informações do usuário logado usando o CPF armazenado na sessão
$cpfUsuario = $_SESSION['cpf'];
$queryUsuario = $pdo->prepare('SELECT cpf, nome, fk_Permissao_id FROM Usuario WHERE cpf = :cpf');
$queryUsuario->execute([':cpf' => $cpfUsuario]);
$usuarioLogado = $queryUsuario->fetch(PDO::FETCH_ASSOC);

// Obtém a URL da imagem do usuário
$queryImagem = $pdo->prepare('SELECT url_imagem FROM Imagem_Usuario WHERE fk_Usuario_cpf = :cpf');
$queryImagem->execute([':cpf' => $cpfUsuario]);
$imagemUsuario = $queryImagem->fetch(PDO::FETCH_ASSOC);

$imagemUrl = $imagemUsuario ? $imagemUsuario['url_imagem'] : 'imagens/usuarios/default.jpg';


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
    $query = $pdo->query("SELECT COUNT(*) FROM Usuario WHERE fk_permissao_id = (SELECT id FROM Permissao WHERE tipo = 'Adotante')");
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

// Função para contar adoções realizadas
function contarAdocoesRealizadas($pdo)
{
    $sql = "SELECT COUNT(*) AS total FROM Adocao";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

// Número de itens por página
$itensPorPagina = 10;

// Página atual (recebida via GET, padrão é 1 se não fornecida)
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$paginaAtual = max($paginaAtual, 1); // Garante que a página atual não seja menor que 1

// Função para obter as últimas adoções (com nome e imagem do pet e adotante)
function obterUltimasAdocoes($pdo)
{
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
function obterPetsDisponiveis($pdo, $paginaAtual, $itensPorPagina)
{
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
function obterPetsAdotados($pdo)
{
    $sql = "SELECT p.nome AS pet_nome, p.brinco, ip.url_imagem AS imagem_pet
FROM Pet p
LEFT JOIN Imagem_Pet ip ON ip.fk_Pet_brinco = p.brinco
WHERE p.status = 'ADOTADO'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obter estatísticas do sistema
$totalUsuarios = contarUsuarios($pdo);
$totalVigilantes = contarVigilantes($pdo);
$totalPetsCadastrados = contarPetsCadastrados($pdo);
$totalPetsAdotados = contarPetsAdotados($pdo);
$totalPetsDisponiveis = contarPetsDisponiveis($pdo);
$quantidadeAdocoesRealizadas = contarAdocoesRealizadas($pdo);

// Obter últimos pets disponíveis e últimas adoções
$petsDisponiveis = obterPetsDisponiveis($pdo, $paginaAtual, $itensPorPagina);
$ultimasAdocoes = obterUltimasAdocoes($pdo);
$petsAdotados = obterPetsAdotados($pdo);

// Calcular o número total de páginas
$totalPaginas = ceil($totalPetsDisponiveis / $itensPorPagina);

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

        <?php include_once 'cabecalho.php'; ?>

        <section class="cabecalho">
            <h1>Bem-vindo, <?= htmlspecialchars($usuarioLogado['nome'], ENT_QUOTES, 'UTF-8'); ?>!</h1>
            <!-- Exibe a imagem do usuário -->
            <section class="usuario-imagem">
                <img src="<?= htmlspecialchars($imagemUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto do usuário">
            </section>
            <nav>
                <a href="logout.php">Sair</a>
            </nav>
        </section>

        <main>

            <section class="secaoPrincipal">

                <!-- Seção de Ações e Menus -->
                <section class="acoes">
                    <ul>
                        <!-- Categoria de Pets -->
                        <li><strong>Pets</strong>
                            <ul>
                                <li><a href="pets.php">Pesquisar Pets Disponíveis</a></li>
                                <?php if ($tipoPermissao == 'Administrador') : ?>
                                <li><a href="pet_cadastrar.php">Cadastrar Novo Pet</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <!-- Categoria de Usuários -->
                        <li><strong>Adotantes</strong>
                            <ul>
                                <?php if ($tipoPermissao == 'Administrador') : ?>
                                <li> <a href="usuarios.php?tipo=adotante">Pesquisar Adotantes</a></li>
                                <li><a href="usuario_cadastrar.php">Cadastrar Novo Adotante</a></li>
                                <?php endif; ?>
                                <?php if ($tipoPermissao == 'Adotante') : ?>
                                <li><a
                                        href="usuario_editar.php?cpf=<?= htmlspecialchars($usuarioLogado['cpf'], ENT_QUOTES, 'UTF-8'); ?>">Editar
                                        meu perfil</a></li>
                                <li><a
                                        href="usuario_remover.php?cpf=<?= htmlspecialchars($usuarioLogado['cpf'], ENT_QUOTES, 'UTF-8'); ?>">Excluir
                                        minha conta</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <!-- Categoria de Vigilantes Sanitários -->
                        <li><strong>Vigilantes Sanitários</strong>
                            <ul>
                                <li><a href="usuarios.php?tipo=vigilante">Pesquisar Vigilantes Sanitários</a></li>
                                <?php if ($tipoPermissao == 'Administrador') : ?>
                                <li><a href="vigilante_cadastrar.php">Cadastrar Novo Vigilante</a></li>
                                <li><a href="vigilante_editar.php">Editar meus dados</a></li>
                                <li><a href="vigilante_remover.php">Remover minha conta</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <!-- Categoria de Adoções -->
                        <li><strong>Adoções</strong>
                            <ul>
                                <li><a href="adocoes.php">Pesquisar Adoções</a></li>
                                <?php if ($tipoPermissao == 'Administrador') : ?>
                                <li><a href="adocao_cadastrar_adm.php">Cadastrar Nova Adoção</a></li>
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
                    <h2>Cadastros</h2>
                    <p>Adotantes: <strong><?= htmlspecialchars($totalUsuarios, ENT_QUOTES, 'UTF-8'); ?></strong></p>
                    <p>Vigilantes sanitários:
                        <strong><?= htmlspecialchars($totalVigilantes, ENT_QUOTES, 'UTF-8'); ?></strong>
                    </p>
                    <p>Pets resgatados:
                        <strong><?= htmlspecialchars($totalPetsCadastrados, ENT_QUOTES, 'UTF-8'); ?></strong>
                    </p>
                    <p>Pets adotados: <strong><?= htmlspecialchars($totalPetsAdotados, ENT_QUOTES, 'UTF-8'); ?></strong>
                    </p>
                    <p>Pets disponíveis:
                        <strong><?= htmlspecialchars($totalPetsDisponiveis, ENT_QUOTES, 'UTF-8'); ?></strong>
                    </p>

                </section>

                <!-- Seção de Pets Disponíveis -->
                <section class="pets-disponiveis">
                    <h2>Eles estão esperando por você:</h2>
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
                            <?php echo min($itensPorPagina, $totalPetsDisponiveis - (($paginaAtual - 1) * $itensPorPagina)); ?>
                            pets de <?php echo $totalPetsDisponiveis; ?> disponíveis.</p>
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

                    <br>
                    <h2>Adote um amigo(a)!</h2>
                </section>

                <!-- Seção de Últimas Adoções -->
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

            </section>

        </main>

        <script>
        // Função para abrir/fechar submenus ao clicar
        document.querySelectorAll('.acoes ul li strong').forEach(function(menuItem) {
            menuItem.addEventListener('click', function() {
                const submenu = this.nextElementSibling;

                // Verifica se o submenu existe
                if (submenu && submenu.tagName === 'UL') {
                    // Alterna a exibição do submenu
                    if (submenu.style.display === 'block') {
                        submenu.style.display = 'none';
                    } else {
                        submenu.style.display = 'block';
                    }
                }
            });
        });
        </script>

        <?php include 'rodape.php'; ?>

    </body>

</html>