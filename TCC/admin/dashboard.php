<?php 
// 1. Definições da página
$pagina_titulo = "Dashboard";
$pagina_ativa = "dashboard";

// 2. Segurança e Cabeçalho (O header já chama o conexao.php)
require_once("verificar_admin.php"); 
require_once("header_admin.php"); 

// 3. Consultas (O $con vem do header_admin.php)
try {
    $total_usuarios = $con->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'] ?? 0;
    $total_roupas = $con->query("SELECT COUNT(*) as total FROM roupas")->fetch_assoc()['total'] ?? 0;
    $total_looks = $con->query("SELECT COUNT(*) as total FROM looks")->fetch_assoc()['total'] ?? 0;
} catch (Exception $e) {
    $total_usuarios = $total_roupas = $total_looks = "Erro";
}

$nome_admin = $_SESSION['nomeusuario'] ?? 'Admin';
?>

<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h1 class="fw-bold h2">Olá, <?= htmlspecialchars($nome_admin) ?></h1>
        <p class="text-muted">Bem vinda ao Sistema da Fashion Style.</p>
    </div>
    <span class="badge bg-dark px-3 py-2 rounded-pill">Administrador</span>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="f-card text-center p-4 shadow-sm border rounded-4 bg-white">
            <div class="mb-2 text-primary h1"><i class="bi bi-people"></i></div>
            <h2 class="fw-bold m-0"><?= $total_usuarios ?></h2>
            <p class="text-muted small text-uppercase fw-bold">Usuários</p>
            <hr>
            <a href="usuarios_lista.php" class="text-dark text-decoration-none small fw-bold">Ver todos <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="f-card text-center p-4 shadow-sm border rounded-4 bg-white">
            <div class="mb-2 text-success h1"><i class="bi bi-bag-check"></i></div>
            <h2 class="fw-bold m-0"><?= $total_roupas ?></h2>
            <p class="text-muted small text-uppercase fw-bold">Roupas</p>
            <hr>
            <a href="roupas_lista.php" class="text-dark text-decoration-none small fw-bold">Ver todas<i class="bi bi-arrow-right"></i></a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="f-card text-center p-4 shadow-sm border rounded-4 bg-white">
            <div class="mb-2 text-warning h1"><i class="bi bi-magic"></i></div>
            <h2 class="fw-bold m-0"><?= $total_looks ?></h2>
            <p class="text-muted small text-uppercase fw-bold">Looks</p>
            <hr>
            <a href="looks_lista.php" class="text-dark text-decoration-none small fw-bold">Ver todos <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</div>

</main> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>