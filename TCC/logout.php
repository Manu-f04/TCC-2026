<?php
session_start();
session_unset();    // Limpa as variáveis de sessão
session_destroy();  // Destrói a sessão

// Descobre a URL base do servidor dinamicamente (ex: http://localhost/seu-projeto/)
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$project_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

// Redireciona de forma absoluta diretamente para o index.php na raiz do projeto
header("Location: " . $base_url . $project_path . "/index.php");
exit();
?>