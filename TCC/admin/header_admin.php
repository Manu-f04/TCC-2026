<?php 
// header_admin.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("../conexao.php"); // A conexão fica centralizada aqui
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Administrador</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/estilo_admin.css">
</head>
<body>

<aside class="sidebar">
    <div class="d-flex align-items-center mb-5">
        <h2 class="fw-bold m-0" style="font-size: 1.2rem; color: #fff;">Fashion Admin</h2>
    </div>
    
    <nav class="nav-admin flex-grow-1">
        <a href="dashboard.php" class="nav-link <?= ($pagina_ativa == 'dashboard') ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>
        <a href="usuarios_lista.php" class="nav-link <?= ($pagina_ativa == 'usuarios') ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Usuários
        </a>
        <a href="roupas_lista.php" class="nav-link <?= ($pagina_ativa == 'roupas') ? 'active' : '' ?>">
            <i class="bi bi-bag-check"></i> Roupas
        </a>
        <a href="looks_lista.php" class="nav-link <?= ($pagina_ativa == 'looks') ? 'active' : '' ?>">
            <i class="bi bi-magic"></i> Looks
        </a>
        <a href="../index.php" class="nav-link"><i class="bi bi-eye"></i> Ver Site</a>
    </nav>
    <a href="../logout.php" class="nav-link btn-logout mt-auto"><i class="bi bi-box-arrow-right"></i> Sair</a>
</aside>

<main class="main-content">