<?php 
$pagina_titulo = "Dashboard";
$pagina_ativa = "dashboard";

require_once("verificar_admin.php"); 
require_once("header_admin.php"); 

try {
    $total_usuarios = $con->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'] ?? 0;
    $total_roupas = $con->query("SELECT COUNT(*) as total FROM roupas")->fetch_assoc()['total'] ?? 0;
    $total_looks = $con->query("SELECT COUNT(*) as total FROM looks")->fetch_assoc()['total'] ?? 0;
    $total_tendencias = $con->query("SELECT COUNT(*) as total FROM tendencias")->fetch_assoc()['total'] ?? 0;
} catch (Exception $e) {
    $total_usuarios = $total_roupas = $total_looks = $total_tendencias = "Erro";
}

$nome_admin = $_SESSION['nomeusuario'] ?? 'Admin';
?>

<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h1 class="fw-bold h2">Olá, <?= htmlspecialchars($nome_admin) ?></h1>
        <p class="text-muted m-0">Bem-vinda ao Sistema da Fashion Style.</p>
    </div>
    <span class="badge bg-dark px-3 py-2 rounded-pill">Administrador</span>
</div>

<!-- Container Centralizado e Limitado para um Grid 2x2 Elegante -->
<div class="mx-auto" style="max-width: 950px;">
    <div class="row g-4 justify-content-center">
        <!-- Card 1: Usuários -->
        <div class="col-md-6">
            <div class="f-card text-center p-5 shadow-sm border rounded-4 bg-white h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="mb-3 text-primary display-3"><i class="bi bi-people"></i></div>
                    <h2 class="display-5 fw-bold m-0"><?= $total_usuarios ?></h2>
                    <p class="text-muted small text-uppercase fw-bold mt-2 mb-0">Usuários</p>
                </div>
                <div>
                    <hr class="my-4">
                    <a href="usuarios_lista.php" class="text-dark text-decoration-none fw-bold">Ver todos <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Card 2: Roupas -->
        <div class="col-md-6">
            <div class="f-card text-center p-5 shadow-sm border rounded-4 bg-white h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="mb-3 text-success display-3"><i class="bi bi-bag-check"></i></div>
                    <h2 class="display-5 fw-bold m-0"><?= $total_roupas ?></h2>
                    <p class="text-muted small text-uppercase fw-bold mt-2 mb-0">Roupas</p>
                </div>
                <div>
                    <hr class="my-4">
                    <a href="roupas_lista.php" class="text-dark text-decoration-none fw-bold">Ver todas <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Card 3: Looks -->
        <div class="col-md-6">
            <div class="f-card text-center p-5 shadow-sm border rounded-4 bg-white h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="mb-3 text-warning display-3"><i class="bi bi-magic"></i></div>
                    <h2 class="display-5 fw-bold m-0"><?= $total_looks ?></h2>
                    <p class="text-muted small text-uppercase fw-bold mt-2 mb-0">Looks</p>
                </div>
                <div>
                    <hr class="my-4">
                    <a href="looks_lista.php" class="text-dark text-decoration-none fw-bold">Ver todos <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Card 4: Tendências -->
        <div class="col-md-6">
            <div class="f-card text-center p-5 shadow-sm border rounded-4 bg-white h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="mb-3 text-dark display-3"><i class="bi bi-stars"></i></div>
                    <h2 class="display-5 fw-bold m-0"><?= $total_tendencias ?></h2>
                    <p class="text-muted small text-uppercase fw-bold mt-2 mb-0">Tendências</p>
                </div>
                <div>
                    <hr class="my-4">
                    <a href="tendencias/index.php" class="text-dark text-decoration-none fw-bold">Gerenciar <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

</main> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>