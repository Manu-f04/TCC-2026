<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['idusuario']) || ($_SESSION['nivel'] ?? '') !== 'admin') {
    $_SESSION['erro'] = "Acesso negado! Área restrita a administradores.";
    header("Location: ../login.php");
    exit();
}
?>