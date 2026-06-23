<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once "autorizacao.php";
require_once "conexao.php";

function normalizeColorValue($value) {
    $value = trim($value);
    if (strpos($value, '#') === 0) return $value;
    $value = strtolower(ltrim($value, '#'));
    $nameMap = ['azul' => '#0000ff', 'preto' => '#000000', 'branco' => '#ffffff', 'vermelho' => '#ff0000', 'verde' => '#008000', 'amarelo' => '#ffff00', 'cinza' => '#808080'];
    if (isset($nameMap[$value])) return $nameMap[$value];
    if (strlen($value) == 3 && ctype_xdigit($value)) return '#' . $value[0].$value[0].$value[1].$value[1].$value[2].$value[2];
    if (strlen($value) == 6 && ctype_xdigit($value)) return '#' . $value;
    return '';
}

$userId = $_SESSION['idusuario'];
$mensagemUrl = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';

$sql = "SELECT * FROM looks WHERE idusuario = ? ORDER BY id DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $userId); 
$stmt->execute();
$looks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Looks</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <style>
        .btn-floating {background:#000!important;color:#fff!important;border-radius:50px!important;padding:10px 25px!important;font-weight:500!important;border:none!important;display:inline-block!important;text-decoration:none!important}
        .look-card { border: 1px solid #eee; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: 0.3s; background:#fff;}
        .look-card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.08); }
        .look-preview-container { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 300px; background-color: #f8f9fa; padding: 10px; position: relative; }
        .look-item-img { max-width: 90%; max-height: 100px; object-fit: contain; margin: 2px 0; }
        .extra-item-img { position: absolute; right: 15px; top: 55%; max-width: 70px; max-height: 70px; border: none; background: transparent; z-index: 5;}
        .color-swatch-display { width: 18px; height: 18px; border-radius: 50%; border: 1px solid #ddd; display: inline-block; }
        .color-swatch-wrapper { display: flex; gap: 5px; align-items: center; margin-top: 8px; flex-wrap: wrap; }
        .alert-modal-exclusao { background-color: #fff5f5; border: 1px solid #ffe3e3; color: #e53e3e; padding: 12px; border-radius: 12px; margin-top: 15px; text-align: left; }
        .btn-confirmar-exclusao.disabled { opacity: 0.4; cursor: not-allowed; pointer-events: none; }
    </style>
</head>
<body class="index-page">
<?php include 'nav.php'; ?>
<main class="main">
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center py-4">
            <h1 class="mb-3 mb-lg-0">Meus Looks</h1>
            <a href="cadastrarlook.php" class="btn btn-floating">Novo Look</a>
        </div>
    </div>
    <div class="container my-5">
        <?php if (empty($looks)): ?>
            <div class="text-center py-5"><i class="bi bi-heart text-muted" style="font-size: 3rem;"></i><h5 class="text-muted mt-3">Nenhum look cadastrado ainda.</h5></div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach ($looks as $look): 
                    $tem_pecas = array_filter([$look['idroupa1'], $look['idroupa2'], $look['idroupa3'], $look['idroupa4'], $look['idroupa5']]);
                    if (empty($tem_pecas)) continue; 
                ?>
                    <div class="col">
                        <div class="card look-card h-100">
                            <div class="look-preview-container">
                                <?php
                                $todas_cores = [];
                                $ids_verticais = array_filter([$look['idroupa1'], $look['idroupa2'], $look['idroupa5'], $look['idroupa3']]);
                                if (!empty($ids_verticais)) {
                                    $ids_str = implode(',', $ids_verticais);
                                    $res_p = $con->query("SELECT foto, cor1, cor2 FROM roupas WHERE id IN ($ids_str) ORDER BY FIELD(id, $ids_str)");
                                    while($p = $res_p->fetch_assoc()){ 
                                        echo '<img src="'.$p['foto'].'" class="look-item-img">'; 
                                        if($p['cor1']) $todas_cores[] = $p['cor1'];
                                        if($p['cor2']) $todas_cores[] = $p['cor2'];
                                    }
                                }
                                if (!empty($look['idroupa4'])) {
                                    $res_extra = $con->query("SELECT foto, cor1, cor2 FROM roupas WHERE id = " . $look['idroupa4']);
                                    if($extra = $res_extra->fetch_assoc()) {
                                        echo '<img src="'.$extra['foto'].'" class="extra-item-img">';
                                        if($extra['cor1']) $todas_cores[] = $extra['cor1'];
                                        if($extra['cor2']) $todas_cores[] = $extra['cor2'];
                                    }
                                }
                                ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title text-truncate fw-bold mb-1" style="font-size:1.1rem;"><?= htmlspecialchars($look['nome']) ?></h5>
                                <div class="color-swatch-wrapper">
                                    <small class="text-muted fw-bold small">Cores:</small>
                                    <?php 
                                    $exibidas = array_unique(array_filter($todas_cores));
                                    foreach ($exibidas as $cor_bruta) {
                                        $hex = normalizeColorValue($cor_bruta);
                                        if($hex) echo '<div class="color-swatch-display" style="background-color: '.$hex.';" title="'.$cor_bruta.'"></div>';
                                    }
                                    ?>
                                </div>
                                <p class="small text-muted mb-3 mt-2 text-truncate">Tags: <?= htmlspecialchars($look['tags']) ?: 'Nenhuma tag' ?></p>
                                <div class="d-flex justify-content-between gap-2">
                                    <a href="cadastrarlook.php?id=<?= $look['id'] ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3 w-100">Editar</a>
                                    <button class="btn btn-sm btn-danger rounded-pill px-3 w-100" data-bs-toggle="modal" data-bs-target="#modalL<?= $look['id'] ?>">Excluir</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Confirmação de Exclusão -->
                    <div class="modal fade" id="modalL<?= $look['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-sm">
                            <div class="modal-content text-center" style="border-radius: 20px;">
                                <div class="modal-body p-4">
                                    <h5 class="fw-bold text-dark">Excluir Look?</h5>
                                    <p class="text-muted small mt-2">Deseja realmente deletar permanentemente <strong>"<?= htmlspecialchars($look['nome']) ?>"</strong>?</p>
                                    
                                    <div class="alert-modal-exclusao py-2 px-3">
                                        <p class="mb-0 small text-center fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Ação Irreversível</p>
                                    </div>
                                    
                                    <div class="d-flex gap-2 mt-4">
                                        <button type="button" class="btn btn-light w-100 rounded-pill small" data-bs-dismiss="modal">Cancelar</button>
                                        <a href="deletarlook.php?id=<?= $look['id'] ?>" class="btn btn-danger w-100 rounded-pill small btn-confirmar-exclusao disabled">
                                            Excluir (<span class="timer-seconds">5</span>s)
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Modal Geral para Avisos e Retornos do Sistema (Sucesso) -->
<div class="modal fade" id="modalFeedbackGeral" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center" style="border-radius: 20px;">
            <div class="modal-header d-flex justify-content-center border-0 pt-4">
                <i class="bi bi-info-circle text-dark" style="font-size: 3rem;"></i>
            </div>
            <div class="modal-body pt-0">
                <h5 class="fw-bold">Aviso do Sistema</h5>
                <p class="text-muted small px-2"><?= $mensagemUrl ?></p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-dark rounded-pill px-5 fw-bold btn-sm" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Exibe o feedback elegante caso haja mensagens via URL (?msg=...)
    <?php if ($mensagemUrl): ?>
        const feedbackModal = new bootstrap.Modal(document.getElementById('modalFeedbackGeral'));
        feedbackModal.show();
    <?php endif; ?>

    if (window.history.replaceState) {
        const url = new URL(window.location.href);
        url.searchParams.delete('msg');
        window.history.replaceState({path:url.href}, '', url.href);
    }

    // Cronômetro do botão de exclusão
    const modais = document.querySelectorAll('.modal');
    modais.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function () {
            const btnExcluir = modal.querySelector('.btn-confirmar-exclusao');
            const spanSegundos = modal.querySelector('.timer-seconds');
            if (!btnExcluir) return;

            let tempoRestante = 5;
            btnExcluir.classList.add('disabled');
            if(spanSegundos) spanSegundos.textContent = tempoRestante;

            const intervalo = setInterval(() => {
                tempoRestante--;
                if (spanSegundos) spanSegundos.textContent = tempoRestante;

                if (tempoRestante <= 0) {
                    clearInterval(intervalo);
                    btnExcluir.classList.remove('disabled');
                    btnExcluir.innerHTML = 'Sim, excluir';
                }
            }, 1000);

            modal.addEventListener('hidden.bs.modal', function () {
                clearInterval(intervalo);
                btnExcluir.classList.add('disabled');
                btnExcluir.innerHTML = 'Excluir (<span class="timer-seconds">5</span>s)';
            }, { once: true });
        });
    });
});
</script>
</body>
</html>