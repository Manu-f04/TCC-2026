<?php 
require_once("verificar_admin.php"); 
require_once("../conexao.php"); 

$pagina_titulo = "Detalhes da Peça";
$pagina_ativa = "roupas"; 

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: roupas_lista.php");
    exit();
}

$id = (int)$_GET['id'];

// Busca a roupa trazendo o nome real da categoria e não apenas o ID
$sql = "SELECT r.*, c.nome as categoria_nome 
        FROM roupas r 
        LEFT JOIN categorias c ON r.idCategoria = c.id 
        WHERE r.id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$roupa = $stmt->get_result()->fetch_assoc();

$isFound = $roupa ? true : false;

// Busca todas as estações para mapear os nomes amigáveis
$listaEstacoes = [];
if ($isFound) {
    $resultEst = $con->query("SELECT id, nome FROM estacoes");
    while ($row = $resultEst->fetch_assoc()) {
        $listaEstacoes[$row['id']] = $row['nome'];
    }
}

require_once("header_admin.php"); 
?>

<style>
    .color-preview-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-block;
        border: 2px solid #dee2e6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        vertical-align: middle;
    }
    .info-label {
        font-weight: 700;
        color: #6c757d;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 4px;
    }
    .info-value {
        color: #212529;
        font-size: 1.1rem;
        font-weight: 500;
    }
</style>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <a href="roupas_lista.php" class="btn btn-light btn-sm mb-2"><i class="bi bi-arrow-left"></i> Voltar para a Lista</a>
        <h1 class="fw-bold h3">Ficha Técnica da Peça</h1>
    </div>
</div>

<?php if ($isFound): ?>
<div class="card border-0 shadow-sm rounded-4 bg-white">
    <div class="card-body p-4">
        <div class="row g-4">
            
            <div class="col-md-4 text-center border-end">
                <span class="info-label text-start d-block mb-2">Imagem Cadastrada</span>
                <?php 
                $foto_peca = (!empty($roupa['foto']) && file_exists("../" . $roupa['foto'])) 
                    ? "../" . htmlspecialchars($roupa['foto']) 
                    : "../assets/img/roupas/default.jpg";
                ?>
                <img src="<?= $foto_peca ?>" class="img-fluid rounded-4 border shadow-sm mb-3" style="max-height: 350px; width: 100%; object-fit: contain; background: #f8f9fa;">
                <div class="badge bg-dark px-3 py-2 fs-6">ID da Peça: #<?= $roupa['id'] ?></div>
            </div>

            <div class="col-md-8">
                <div class="row g-4">
                    
                    <div class="col-md-6">
                        <span class="info-label">Categoria</span>
                        <div class="info-value">
                            <i class="bi bi-tag-fill me-2 text-secondary"></i>
                            <?= htmlspecialchars($roupa['categoria_nome'] ?? 'Sem Categoria') ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <span class="info-label">Tags / Estilo</span>
                        <div class="info-value">
                            <?php if(!empty($roupa['tags'])): ?>
                                <?php foreach(explode(',', $roupa['tags']) as $tag): ?>
                                    <span class="badge bg-light text-dark border rounded-3 me-1"><?= htmlspecialchars(trim($tag)) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted small italic-text">Nenhuma tag informada</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <span class="info-label">Estações Recomendadas</span>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            <?php 
                            $marcados = array_filter(explode(',', $roupa['estacoes'] ?? ''));
                            if (!empty($marcados)):
                                foreach ($marcados as $idEst): 
                                    if(isset($listaEstacoes[$idEst])): ?>
                                        <span class="badge bg-dark rounded-pill px-3 py-2">
                                            <i class="bi bi-cloud-sun me-1"></i> <?= htmlspecialchars($listaEstacoes[$idEst]) ?>
                                        </span>
                                    <?php endif;
                                endforeach;
                            else: ?>
                                <span class="text-muted small italic-text">Nenhuma estação vinculada</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <span class="info-label">Cores Extraídas da Peça</span>
                        <div class="d-flex gap-3 mt-2 p-2 border rounded bg-light align-items-center justify-content-start" style="max-width: max-content;">
                            <?php if(!empty($roupa['cor1'])): ?>
                                <div class="text-center px-2">
                                    <span class="color-preview-circle" style="background-color: <?= $roupa['cor1'] ?>;"></span>
                                    <small class="d-block text-muted mt-1 small" style="font-family: monospace;"><?= $roupa['cor1'] ?></small>
                                </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($roupa['cor2'])): ?>
                                <div class="text-center px-2 border-start">
                                    <span class="color-preview-circle" style="background-color: <?= $roupa['cor2'] ?>;"></span>
                                    <small class="d-block text-muted mt-1 small" style="font-family: monospace;"><?= $roupa['cor2'] ?></small>
                                </div>
                            <?php endif; ?>

                            <?php if(empty($roupa['cor1']) && empty($roupa['cor2'])): ?>
                                <span class="text-muted p-2 small">Nenhuma cor detectada</span>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <div class="mt-5 pt-4 border-top d-flex justify-content-between">
                    <span class="text-muted small my-auto">Esta peça pertence ao closet do usuário ID #<?= $roupa['idusuario'] ?></span>
                    <div>
                        <a href="roupas_lista.php" class="btn btn-dark px-4 py-2 rounded-3">
                            Fechar Visualização
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-warning rounded-4 shadow-sm p-4 text-center" role="alert">
    <i class="bi bi-exclamation-triangle display-4 d-block mb-3"></i>
    <h4 class="fw-bold">Peça de roupa não encontrada!</h4>
    <p class="mb-3">O código identificador informado na URL não corresponde a nenhuma peça em nosso banco de dados.</p>
    <a href="roupas_lista.php" class="btn btn-dark btn-sm px-4">Voltar para a Listagem</a>
</div>
<?php endif; ?>

<?php require_once("footer_admin.php"); ?>