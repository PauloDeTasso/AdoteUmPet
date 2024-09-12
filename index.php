<?php
session_start();

if (!isset($_SESSION['usuario_logado'])) {
    header('Location: php/login.php');
    exit();
}

header('Location: php/home.php');
exit();