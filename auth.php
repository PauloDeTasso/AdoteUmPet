<?php
function verificarSessao()
{
    if (!isset($_SESSION['cpf']))
    {
        header('Location: login.php');
        exit();
    }
}
