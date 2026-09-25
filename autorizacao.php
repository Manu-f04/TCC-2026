<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado, olhando se existe a variável 'idusuario' na sessão
// Essa variável é criada quando o usuário faz login com sucesso
if (!isset($_SESSION['idusuario'])) {

    // Se o usuário não estiver logado, redireciona para a página de login
    header("Location: login.php");
    // Encerra a execução do script para garantir que o redirecionamento aconteça imediatamente
    exit;
}

// Se o usuário estiver logado, o código continua normalmente (pode acessar a página protegida)