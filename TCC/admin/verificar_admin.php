<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado E se o nível dele é 'admin'
if (!isset($_SESSION['idusuario']) || $_SESSION['nivel'] !== 'admin') {
    // Se não for admin, define uma mensagem de erro e chuta para o login
    $_SESSION['erro'] = "Acesso negado! Área restrita a administradores.";
    header("Location: ../login.php");
    exit();
}
?>