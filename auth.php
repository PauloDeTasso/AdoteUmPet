<?php

if (session_status() === PHP_SESSION_NONE)
{
    session_start();
}

function verificarSessao()
{
    if (!isset($_SESSION['cpf']))
    {
        header('Location: index.php');
        exit();
    }
}
