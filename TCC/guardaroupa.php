<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "autorizacao.php"; 
require_once "conexao.php"; 

function normalizeHex($hex) {
    if (empty($hex)) return '';
    $hex = strtolower(ltrim($hex, '#'));
    if (strlen($hex) == 3) {
        return $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    return $hex;
}

$userId = $_SESSION['idusuario'];
$roupas = [];
$categoriasComContagem = [];
$mensagem = '';
$filtroCategoriaId = $_GET['categoria'] ?? null;

if (isset($_GET['msg'])) {
    $mensagem = htmlspecialchars(urldecode($_GET['msg']));
}

try {
    if (!isset($con) || !($con instanceof mysqli) || $con->connect_error) {
        throw new Exception("Conexão falhou.");
    }
    
    $sqlCat = "SELECT c.id, c.nome, COUNT(r.id) as contagem
                FROM categorias c
                LEFT JOIN roupas r ON c.id = r.idCategoria AND r.idusuario = ?
                GROUP BY c.id, c.nome
                ORDER BY c.nome";
    $stmtCat = $con->prepare($sqlCat);
    $stmtCat->bind_param("i", $userId);
    $stmtCat->execute();
    $categoriasComContagem = $stmtCat->get_result()->fetch_all(MYSQLI_ASSOC);

    $sqlRoupas = "SELECT r.*, c.nome AS nome_categoria FROM roupas r 
                  JOIN categorias c ON r.idCategoria = c.id WHERE r.idusuario = ?";
    if ($filtroCategoriaId && is_numeric($filtroCategoriaId)) {
        $sqlRoupas .= " AND r.idCategoria = " . (int)$filtroCategoriaId;
    }
    $sqlRoupas .= " ORDER BY r.id DESC";
    $stmtR = $con->prepare($sqlRoupas);
    $stmtR->bind_param("i", $userId);
    $stmtR->execute();
    $roupas = $stmtR->get_result()->fetch_all(MYSQLI_ASSOC);

    $coresDaListagem = [];
    foreach ($roupas as $r) {
        if (!empty($r['cor1'])) $coresDaListagem[] = normalizeHex($r['cor1']);
        if (!empty($r['cor2'])) $coresDaListagem[] = normalizeHex($r['cor2']);
    }
    $todasCoresHex = array_unique($coresDaListagem);

} catch (Exception $e) {
    $mensagem = 'Erro: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guarda-roupa</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <style>
        .btn-floating{background:#000!important;color:#fff!important;border-radius:50px!important;padding:10px 25px!important;font-weight:500!important;border:none!important;transition:all .3s!important;display:inline-block!important;text-decoration:none!important}
        .btn-floating:hover{background:#fff!important;color:#000!important;border:1px solid #000!important}
        
        @media (min-width: 992px) {
            .col-sidebar { width: 30% !important; } 
            .col-content { width: 70% !important; }
        }

        .grid-categorias { display: grid; grid-template-columns: 1fr 1fr; position: relative; border: 1px solid #eee; border-radius: 10px; background: #fff; overflow: hidden; width: 100%; }
        .grid-categorias::after { content: ""; position: absolute; left: 50%; top: 0; bottom: 0; width: 1px; background-color: #eee; }
        .categoria-item { border-bottom: 1px solid #eee; display: flex; }
        .categoria-link { text-decoration: none; display: flex; justify-content: space-between; align-items: center; width: 100%; padding: 10px 12px; color: #555; font-size: 0.88rem; }
        .categoria-link:hover { background: #f9f9f9; color: #000; }
        .categoria-link.active { background: #f3f3f3; color: #000; font-weight: bold; }
        .contagem-num { font-size: 0.75rem; color: #bbb; margin-left: 5px; }

        .product-image img{width:100%;height:260px;object-fit:contain;border-radius:12px}
        .product-card{border:1px solid #eee;border-radius:16px;background:#fff;transition:all .3s;height:100%}
        .product-card:hover{transform:translateY(-8px);box-shadow:0 12px 25px rgba(0,0,0,0.08)}
        .product-info{padding:15px;text-align:center}
        
        .color-swatch{width:32px;height:32px;border-radius:50%;cursor:pointer;border:2px solid #eee;display:inline-flex;justify-content:center;align-items:center;font-size:8px;font-weight:bold;color:#555}
        .color-swatch.active{border:2px solid #000;transform:scale(1.1)}
        .products-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px}
        
        .txt-vinculo { font-size: 0.72rem; color: #999; margin-bottom: 10px; display: block; }
    </style>
</head>
<body class="index-page">
<?php include 'nav.php'; ?>
<main class="main">
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center py-4">
            <h1 class="m-0">Guarda-roupa</h1>
            <a href="editar_roupa.php" class="btn btn-floating">Cadastrar Roupa</a>
        </div>
    </div>

    <div class="container-fluid px-lg-5 my-4">
        <div class="row g-4">
            <div class="col-lg-3 col-sidebar">
                <div class="card p-3 shadow-sm border-0" style="border-radius: 15px; background: #fcfcfc;">
                    <h6 class="fw-bold mb-3">Categorias</h6>
                    <a href="guardaroupa.php" class="d-block mb-3 <?= $filtroCategoriaId === null ? 'fw-bold text-dark' : 'text-muted' ?>" style="text-decoration:none; font-size: 0.9rem;">
                        Todas as categorias
                    </a>

                    <div class="grid-categorias">
                        <?php foreach ($categoriasComContagem as $cat): 
                            $nomeExibicao = str_replace(['(Parte de Cima)', '(Parte de Baixo)'], ['(Top)', '(Baixo)'], $cat['nome']);
                        ?>
                            <div class="categoria-item">
                                <a href="?categoria=<?= $cat['id'] ?>" class="categoria-link <?= $filtroCategoriaId == $cat['id'] ? 'active' : '' ?>">
                                    <span class="nome-cat"><?= htmlspecialchars($nomeExibicao) ?></span>
                                    <span class="contagem-num"><?= $cat['contagem'] ?></span>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <h6 class="fw-bold mt-4 mb-3">Cores</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="color-swatch active" data-color="todos">ALL</span>
                        <?php foreach ($todasCoresHex as $hex): ?>
                            <span class="color-swatch" data-color="<?= $hex ?>" style="background:#<?= $hex ?>;"></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-9 col-content">
                <?php if (empty($roupas)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-archive text-muted" style="font-size: 3rem;"></i>
                        <h4 class="text-muted mt-3">Nenhuma peça nesta categoria.</h4>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($roupas as $r): 
                            $cor1 = normalizeHex($r['cor1']); $cor2 = normalizeHex($r['cor2']);
                            $coresFiltro = implode(',', array_filter([$cor1, $cor2]));

                            $sql_l = "SELECT nome FROM looks WHERE idusuario = ? AND (idroupa1 = ? OR idroupa2 = ? OR idroupa3 = ? OR idroupa4 = ? OR idroupa5 = ?)";
                            $stmt_l = $con->prepare($sql_l);
                            $stmt_l->bind_param("iiiiii", $userId, $r['id'], $r['id'], $r['id'], $r['id'], $r['id']);
                            $stmt_l->execute();
                            $res_l = $stmt_l->get_result();
                            $looks_vinculados = $res_l->fetch_all(MYSQLI_ASSOC);
                            $total_looks = count($looks_vinculados);
                            
                            $frase_vinculo = ($total_looks == 0) ? "Essa peça não está vinculada a nenhum look" : ($total_looks == 1 ? "Vinculada a 1 look" : "Vinculada a $total_looks looks");
                        ?>
                            <div class="product-item" data-colors="<?= $coresFiltro ?>">
                                <div class="product-card">
                                    <div class="product-image"><img src="<?= htmlspecialchars($r['foto']) ?>"></div>
                                    <div class="product-info">
                                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($r['nome_categoria']) ?></h6>
                                        <span class="txt-vinculo"><?= $frase_vinculo ?></span>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="editar_roupa.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3">Editar</a>
                                            <button class="btn btn-sm btn-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalR<?= $r['id'] ?>">Excluir</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal de Confirmação de Exclusão (Atualizado) -->
                            <div class="modal fade" id="modalR<?= $r['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered modal-sm">
                                    <div class="modal-content text-center" style="border-radius: 20px;">
                                        <div class="modal-body p-4">
                                            <h5 class="fw-bold text-dark">Excluir Peça?</h5>
                                            <?php if ($total_looks > 0): ?>
                                                <p class="text-danger small mt-2"><strong>Atenção:</strong> Esta peça está nos looks:<br>
                                                <?php foreach($looks_vinculados as $lk): ?> "<?= htmlspecialchars($lk['nome']) ?>"<br> <?php endforeach; ?>
                                                Se você excluir, ela sumirá deles.</p>
                                            <?php else: ?>
                                                <p class="text-muted small mt-2">Deseja realmente deletar esta peça?</p>
                                            <?php endif; ?>
                                            <div class="d-flex gap-2 mt-4">
                                                <button class="btn btn-light w-100 rounded-pill small" data-bs-dismiss="modal">Não</button>
                                                <a href="deletar_roupa.php?id=<?= $r['id'] ?>" class="btn btn-danger w-100 rounded-pill small">Sim, excluir</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Modal Pop-up Geral para Retornos e Sucessos (Substitui o Alert Simples do Topo) -->
<div class="modal fade" id="modalFeedbackGeral" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center" style="border-radius: 20px;">
            <div class="modal-header d-flex justify-content-center border-0 pt-4">
                <i class="bi bi-info-circle text-dark" style="font-size: 3rem;"></i>
            </div>
            <div class="modal-body pt-0">
                <h5 class="fw-bold">Aviso do Sistema</h5>
                <p class="text-muted small px-2"><?= $mensagem ?></p>
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
    document.addEventListener('DOMContentLoaded', () => {
        // Dispara o novo modal se houver alguma mensagem retornada na URL
        <?php if ($mensagem): ?>
            const feedbackModal = new bootstrap.Modal(document.getElementById('modalFeedbackGeral'));
            feedbackModal.show();
        <?php endif; ?>

        // LIMPA URL
        if (window.history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.delete('msg');
            window.history.replaceState({path:url.href}, '', url.href);
        }

        // FILTRO DE CORES
        const swatches = document.querySelectorAll('.color-swatch');
        const items = document.querySelectorAll('.product-item');
        swatches.forEach(swatch => {
            swatch.addEventListener('click', () => {
                swatches.forEach(s => s.classList.remove('active'));
                swatch.classList.add('active');
                const selectedColor = swatch.dataset.color;
                items.forEach(item => {
                    const itemColors = item.dataset.colors || '';
                    item.style.display = (selectedColor === 'todos' || itemColors.split(',').includes(selectedColor)) ? 'block' : 'none';
                });
            });
        });
    });
</script>
</body>
</html>