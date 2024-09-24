<?php
function conectar()
{
    // Verificar se estamos em localhost ou produção
    if ($_SERVER['SERVER_NAME'] == 'localhost')
    {
        // Configurações para o ambiente local
        $host = 'localhost';
        $dbname = 'adote_um_pet';
        $user = 'postgres';
        $password = '12345678';
    }
    else
    {
        // Configurações para o ambiente de produção
        $host = 'db.adoteumpet.com.br'; // Endereço do servidor de banco de dados em produção (domínio ou IP real)
        $dbname = 'adote_um_pet_prod';   // Nome do banco de dados usado em produção
        $user = 'user_adote_um_pet_prod';          // Usuário do banco de dados em produção
        $password = 'senhaSegura';  // Senha do banco de dados em produção
    }

    try
    {
        // Criando a conexão com o banco de dados
        $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
    catch (PDOException $e)
    {
        // Em caso de erro na conexão, exibir uma mensagem
        die("Erro na conexão: " . $e->getMessage());
    }
}
