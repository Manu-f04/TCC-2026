<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../conexao.php'; 

// Detecta se a página atual está dentro de uma subpasta
$in_subfolder = (basename(dirname($_SERVER['PHP_SELF'])) !== 'admin');
$base_admin   = $in_subfolder ? '../' : '';
$base_root    = $in_subfolder ? '../../' : '../';

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pagina_titulo ?? 'Administrador' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $base_admin ?>css/estilo_admin.css">
    
</head>
<body>

<aside class="sidebar">
    <div class="d-flex align-items-center mb-5">
        <h2 class="fw-bold m-0" style="font-size: 1.2rem; color: #fff;">Fashion Admin</h2>
    </div>
    
    <nav class="nav-admin flex-grow-1">
        <a href="<?= $base_admin ?>dashboard.php" class="nav-link <?= ($pagina_ativa == 'dashboard') ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>
        <a href="<?= $base_admin ?>usuarios_lista.php" class="nav-link <?= ($pagina_ativa == 'usuarios') ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Usuários
        </a>
        <a href="<?= $base_admin ?>roupas_lista.php" class="nav-link <?= ($pagina_ativa == 'roupas') ? 'active' : '' ?>">
            <i class="bi bi-bag-check"></i> Roupas
        </a>
        <a href="<?= $base_admin ?>looks_lista.php" class="nav-link <?= ($pagina_ativa == 'looks') ? 'active' : '' ?>">
            <i class="bi bi-magic"></i> Looks
        </a>
        <a href="<?= $base_admin ?>tendencias/index.php" class="nav-link <?= ($pagina_ativa == 'tendencias') ? 'active' : '' ?>">
            <i class="bi bi-stars"></i> Tendências
        </a>
        <a href="<?= $base_root ?>index.php" class="nav-link"><i class="bi bi-eye"></i> Ver Site</a>
    </nav>
    <a href="#" onclick="confirmarSaida(event)" class="nav-link text-danger">
        <i class="bi bi-box-arrow-right me-2"></i> Sair
    </a>
</aside>

<!-- Script de Confirmação de Saída -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmarSaida(event) {
    event.preventDefault();
    const urlLogout = '<?= $base_root ?>logout.php';

    Swal.fire({
        title: 'Sair da Conta?',
        text: 'Você está prestes a encerrar sua sessão de administrador.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, sair',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = urlLogout;
        }
    });
}
</script>

<main class="main-content">