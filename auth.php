<?php
function verificarSessao()
{
    if (!isset($_SESSION['cpf']))
    {
        header('Location: index.php');
        exit();
    }
}