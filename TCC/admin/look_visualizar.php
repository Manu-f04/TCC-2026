<?php 
require_once("verificar_admin.php"); 
require_once("../conexao.php");

$pagina_titulo = "Visualizar Look";
$pagina_ativa = "looks";

if (!isset($_GET['id'])) {
    header("Location: looks.php");
    exit();
}

$id = intval($_GET['id']);

// 1. Busca os detalhes do look e do usuário criador
$sql_look = "SELECT l.*, u.nome as criador, u.nome_usuario, u.email, u.foto as foto_usuario 
             FROM looks l 
             LEFT JOIN usuarios u ON l.idusuario = u.id 
             WHERE l.id = ?";
$stmt = $con->prepare($sql_look);
$stmt->bind_param("i", $id);
$stmt->execute();
$look = $stmt->get_result()->fetch_assoc();

if (!$look) { 
    die("Look não encontrado."); 
}

// 2. Mapeamento e busca detalhada das peças que compõem o look
$pecas = [];
$ids_roupas = array_filter([
    'Peça 1' => $look['idroupa1'],
    'Peça 2' => $look['idroupa2'],
    'Peça 3' => $look['idroupa3'],
    'Peça 4' => $look['idroupa4'] ?? null,
    'Peça 5' => $look['idroupa5'] ?? null
]);

if (!empty($ids_roupas)) {
    $ids_str = implode(',', $ids_roupas);
    // Busca a roupa trazendo também o nome da categoria correspondente
    $sql_roupas = "SELECT r.*, c.nome as categoria_nome 
                   FROM roupas r 
                   LEFT JOIN categorias c ON r.idCategoria = c.id 
                   WHERE r.id IN ($ids_str)";
    $res_roupas = $con->query($sql_roupas);
    
    while ($roupa = $res_roupas->fetch_assoc()) {
        $pecas[$roupa['id']] = $roupa;
    }
}

$foto_user = (!empty($look['foto_usuario']) && file_exists("../assets/img/usuarios/" . $look['foto_usuario']))
    ? "../assets/img/usuarios/" . $look['foto_usuario']
    : "../assets/img/usuarios/default.jpg";

require_once("header_admin.php"); 
?>

<style>
    .look-preview-box { 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
        justify-content: center; 
        height: 320px; 
        width: 240px;
        background-color: #f8f9fa; 
        padding: 15px; 
        position: relative; 
        border-radius: 16px;
        border: 1px solid #dee2e6;
        margin: 0 auto;
    }
    .look-preview-box .look-item-img { 
        max-width: 90%; 
        max-height: 80px; 
        object-fit: contain; 
        margin: 3px 0; 
    }
    .look-preview-box .extra-item-img { 
        position: absolute; 
        right: 15px; 
        top: 45%; 
        max-width: 75px; 
        max-height: 75px; 
        border: none; 
        background: transparent; 
        z-index: 5;
        object-fit: contain;
    }
    .color-circle {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-block;
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }
</style>

<div class="mb-4">
    <a href="http://localhost/manu.Info31/TCC/admin/looks_lista.php" class="btn btn-light btn-sm mb-3"><i class="bi bi-arrow-left"></i> Voltar para a Lista</a>
    <h1 class="fw-bold h3">Informações do Look</h1>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
            <div class="card-body p-4 text-center">
                <h5 class="fw-bold mb-3 text-start">Montagem Visual</h5>
                
                <div class="look-preview-box shadow-sm">
                    <?php
                    // Ordem Vertical Padrão (Roupa 1, 2, 5 e 3)
                    $ordem_verticais = array_filter([$look['idroupa1'], $look['idroupa2'], $look['idroupa5'], $look['idroupa3']]);
                    foreach ($ordem_verticais as $id_p) {
                        if (isset($pecas[$id_p])) {
                            echo '<img src="../' . $pecas[$id_p]['foto'] . '" class="look-item-img" onerror="this.src=\'../assets/img/roupas/default.jpg\'">';
                        }
                    }

                    // Peça Extra Sobreposta (Roupa 4)
                    if (!empty($look['idroupa4']) && isset($pecas[$look['idroupa4']])) {
                        echo '<img src="../' . $pecas[$look['idroupa4']]['foto'] . '" class="extra-item-img" onerror="this.src=\'../assets/img/roupas/default.jpg\'">';
                    }

                    if (empty($ordem_verticais) && empty($look['idroupa4'])) {
                        echo '<span class="text-muted">Nenhuma peça configurada</span>';
                    }
                    ?>
                </div>
                
                <div class="mt-3">
                    
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-4 text-center">
                <h5 class="fw-bold mb-3 text-start">Criado por</h5>
                <img src="<?= $foto_user ?>" class="rounded-circle border shadow-sm mb-3" style="width: 80px; height: 80px; object-fit: cover;" onerror="this.src='../assets/img/usuarios/default.jpg'">
                <h6 class="fw-bold mb-1"><?= htmlspecialchars($look['criador'] ?? 'Visitante') ?></h6>
                <p class="text-muted small mb-0">@<?= htmlspecialchars($look['nome_usuario'] ?? 'anonimo') ?></p>
                <p class="text-muted small border-top pt-2 mt-2"><i class="bi bi-envelope"></i> <?= htmlspecialchars($look['email'] ?? 'N/A') ?></p>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
            <div class="card-body p-4">
                <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($look['nome'] ?? 'Look sem nome') ?></h4>
                <p class="text-muted small mb-3">ID do Look: #<?= $look['id'] ?></p>
                
                <div class="row g-3 border-top pt-3">
                    <div class="col-12">
                        <label class="fw-bold text-muted small d-block">Legenda da publicação</label>
                        <p class="bg-light p-3 rounded-3 text-dark mb-0 italic-text">
                            <?= !empty($look['legenda']) ? htmlspecialchars($look['legenda']) : '<em>Nenhuma legenda informada pelo usuário.</em>' ?>
                        </p>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="fw-bold text-muted small d-block">Tags do Look</label>
                        <?php if(!empty($look['tags'])): ?>
                            <?php foreach(explode(',', $look['tags']) as $tag): ?>
                                <span class="badge bg-dark rounded-3 me-1"><?= htmlspecialchars(trim($tag)) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted small">Nenhuma tag</span>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label class="fw-bold text-muted small d-block">Data de Publicação</label>
                        <span class="text-dark small">
                            <i class="bi bi-calendar3"></i> 
                            <?= !empty($look['data_publicacao']) ? date('d/m/Y H:i', strtotime($look['data_publicacao'])) : 'Não publicado' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Peças Utilizadas nesta Combinação</h5>
                
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 150px;">Posição</th>
                                <th style="width: 90px;">Foto</th>
                                <th>Categoria</th>
                                <th>Cores da Peça</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Configuração dinâmica da Peça 1 (Parte de Cima ou Corpo Inteiro)
                            $label_peca1 = 'Parte de cima';
                            if (!empty($look['idroupa1']) && isset($pecas[$look['idroupa1']])) {
                                $id_cat = intval($pecas[$look['idroupa1']]['idCategoria']);
                                // De acordo com seu .sql, IDs 18 (Vestido) e 19 (Macacão) são peças únicas/corpo inteiro
                                if ($id_cat === 18 || $id_cat === 19) {
                                    $label_peca1 = 'Corpo Inteiro';
                                }
                            }

                            $posicoes = [
                                'idroupa1' => $label_peca1,
                                'idroupa2' => 'Parte de baixo',
                                'idroupa3' => 'Calçado',
                                'idroupa4' => 'Sobreposição',
                                'idroupa5' => 'Acessório'
                            ];

                            foreach ($posicoes as $campo => $label): 
                                $id_roupa = $look[$campo];
                                if (!empty($id_roupa) && isset($pecas[$id_roupa])):
                                    $item = $pecas[$id_roupa];
                            ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= $label === 'Corpo Inteiro' ? 'bg-dark text-white' : 'bg-light text-dark' ?> border">
                                            <?= $label ?>
                                        </span>
                                    </td>
                                    <td>
                                        <img src="../<?= $item['foto'] ?>" class="border rounded-2 bg-light" style="width: 60px; height: 60px; object-fit: contain;" onerror="this.src='../assets/img/roupas/default.jpg'">
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($item['categoria_nome'] ?? 'Sem Categoria') ?></span>
                                        <small class="text-muted d-block">ID Peça: #<?= $item['id'] ?></small>
                                    </td>
                                    <td>
                                        <?php if(!empty($item['cor1'])): ?>
                                            <span class="color-circle shadow-sm" style="background-color: <?= $item['cor1'] ?>;" title="<?= $item['cor1'] ?>"></span>
                                        <?php endif; ?>
                                        <?php if(!empty($item['cor2'])): ?>
                                            <span class="color-circle shadow-sm ms-1" style="background-color: <?= $item['cor2'] ?>;" title="<?= $item['cor2'] ?>"></span>
                                        <?php endif; ?>
                                        <?php if(empty($item['cor1']) && empty($item['cor2'])): ?>
                                            <span class="text-muted small">Não especificadas</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td><span class="badge bg-light text-muted border"><?= $label ?></span></td>
                                    <td colspan="3" class="text-muted small italic-text text-center py-3">
                                        <?php 
                                        if ($campo === 'idroupa2' && $label_peca1 === 'Corpo Inteiro') {
                                            echo 'Não necessária (Utilizando uma peça de corpo inteiro).';
                                        } else {
                                            echo 'Nenhuma peça selecionada para esta posição.';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once("footer_admin.php"); ?>