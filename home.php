<?php
session_start();
if (!isset($_SESSION['cpf']))
{
    header("Location: login.php");
    exit;
}
?>

<h1>Bem-vindo ao sistema Adote um Pet</h1>
<a href="logout.php">Sair</a>