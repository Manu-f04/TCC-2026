<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); } 
require_once "autorizacao.php"; 
require_once "conexao.php";
$userId = $_SESSION['idusuario']; 

try {
    $resultCat = $con->query("SELECT id, nome FROM categorias ORDER BY id"); 
    $categorias = $resultCat->fetch_all(MYSQLI_ASSOC); 
    $resultEst = $con->query("SELECT id, nome FROM estacoes ORDER BY id"); 
    $listaEstacoes = $resultEst->fetch_all(MYSQLI_ASSOC); 
} catch (Exception $e) { die("Erro: " . $e->getMessage()); }

// --- MAPEAMENTO DINÂMICO POR NOMES ---
$mapeamentoGrupos = [
    "Peça de Cima" => ['Top', 'Camisa/Camiseta', 'Regata', 'Cropped', 'Casaco'],
    "Peça de Baixo" => ['Calça', 'Short', 'Saia', 'Bermuda'],
    "Corpo Inteiro" => ['Vestido', 'Macacão', 'Body'],
    "Moda Praia" => ['Biquíni (Parte de Cima)', 'Biquíni (Parte de Baixo)', 'Maiô', 'Canga', 'Saída de Praia'],
    "Calçados" => ['Tênis', 'Sandália', 'Salto', 'Bota'],
    "Acessórios e Sobreposições" => ['Casaco', 'Bolsa', 'Óculos', 'Chapéu']
];

$grupos = [];
foreach ($mapeamentoGrupos as $titulo => $nomesCategorias) {
    $grupos[$titulo] = [];
    foreach ($categorias as $cat) {
        if (in_array($cat['nome'], $nomesCategorias)) {
            $grupos[$titulo][] = $cat['id'];
        }
    }
}

$roupa = null; $isEdit = false; 
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $con->prepare("SELECT * FROM roupas WHERE id = ? AND idusuario = ?");
    $stmt->bind_param("ii", $id, $userId);
    $stmt->execute();
    $roupa = $stmt->get_result()->fetch_assoc();
    if ($roupa) { $isEdit = true; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idRoupa = $_POST['id'] ?? null;
    $categoriaForm = $_POST['categoria'] ?? '';
    $estacoesPost = $_POST['estacoes'] ?? []; 
    $cor1 = $_POST['cor1'] ?? '#000000';
    $cor2 = $_POST['cor2'] ?? '#ffffff';
    $tags = trim($_POST['tags'] ?? '');
    $fotoNome = $isEdit ? $roupa['foto'] : '';

    if (!empty($_FILES['foto']['name'])) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $fotoNome = 'uploads/roupas/' . uniqid('roupa_') . '.' . $ext;
        if(!is_dir('uploads/roupas')) mkdir('uploads/roupas', 0755, true);
        move_uploaded_file($_FILES['foto']['tmp_name'], $fotoNome);
    }

    $estacoesStr = implode(',', $estacoesPost);

    if ($isEdit && $idRoupa) {
        $sql = "UPDATE roupas SET foto=?, cor1=?, cor2=?, idCategoria=?, estacoes=?, tags=? WHERE id=? AND idusuario=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sssisiii", $fotoNome, $cor1, $cor2, $categoriaForm, $estacoesStr, $tags, $idRoupa, $userId);
    } else {
        $sql = "INSERT INTO roupas (idusuario, foto, cor1, cor2, idCategoria, estacoes, tags) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("isssiss", $userId, $fotoNome, $cor1, $cor2, $categoriaForm, $estacoesStr, $tags);
    }
    $stmt->execute();
    header("Location: guardaroupa.php?msg=" . urlencode($isEdit ? "Peça alterada com sucesso!" : "Peça cadastrada com sucesso!"));
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $isEdit ? 'Editar' : 'Cadastrar' ?> Roupa</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <style>
        .btn-floating {background:#000!important;color:#fff!important;border-radius:50px!important;padding:10px 25px!important;font-weight:500!important;border:none!important;display:inline-block!important;text-decoration:none!important}
        .img-preview {max-height:300px; border-radius:15px; border: 2px dashed #ccc; margin-top:10px; background: #fff; padding: 5px;}
        .color-container { display: flex; gap: 20px; justify-content: flex-start; align-items: flex-start; margin-top: 5px; }
        .color-wrapper { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; }
        .color-circle { -webkit-appearance: none; -moz-appearance: none; appearance: none; width: 45px !important; height: 45px !important; background-color: transparent; border: 2px solid #ddd; border-radius: 50% !important; cursor: pointer; overflow: hidden; padding: 0; }
        .color-circle::-webkit-color-swatch { border: none; border-radius: 50%; padding: 0; }
        .color-circle::-webkit-color-swatch-wrapper { padding: 0; border-radius: 50%; }
        .caixa-selecao { border: 1px solid #ddd; border-radius: 10px; padding: 10px; background: #fff; max-height: 400px; overflow-y: auto; }
        .grupo-header { background: #f8f9fa; padding: 12px; border-radius: 8px; cursor: pointer; margin-bottom: 5px; font-weight: bold; display: flex; justify-content: space-between; align-items: center; border: 1px solid #eee; }
        .subcategorias { display: none; padding: 10px 20px; border-left: 2px solid #000; margin-bottom: 10px; }
        .required-label::after { content: " *"; color: red; }
    </style>
</head>
<body class="index-page">
    <?php include 'nav.php'; ?>
    <main class="main">
        <div class="page-title light-background">
            <div class="container d-lg-flex justify-content-between align-items-center py-4">
                <h1 class="m-0"><?= $isEdit ? 'Editar Roupa' : 'Cadastrar Roupa' ?></h1>
                <a href="guardaroupa.php" class="btn btn-floating">Voltar</a>
            </div>
        </div>

        <div class="container my-5">
            <div class="card p-4 shadow-sm border-0" style="border-radius:15px;">
                <form id="formRoupa" method="POST" enctype="multipart/form-data" onsubmit="return validarForm(event)">
                    <input type="hidden" name="id" value="<?= $roupa['id'] ?? '' ?>">

                    <div class="row g-4">
                        <div class="col-md-5 text-center border-end">
                            <label class="form-label fw-bold d-block text-start required-label">Foto da Peça</label>
                            <input type="file" class="form-control mb-3" name="foto" id="inputFoto" onchange="previewImg(this)" <?= $isEdit ? '' : 'required' ?>>

                            <div id="previewContainer">
                                <?php if ($isEdit && $roupa['foto']): ?>
                                    <img src="<?= $roupa['foto'] ?>" class="img-preview" id="currentImg">
                                <?php endif; ?>
                                <img id="preview" src="#" class="img-preview d-none mx-auto">
                            </div>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label fw-bold required-label">Categoria</label>
                            <div class="caixa-selecao mb-4">
                                <?php
                                foreach ($grupos as $titulo => $ids): 
                                    if(empty($ids)) continue;
                                    $aberto = ($isEdit && in_array($roupa['idCategoria'], $ids)) ? 'display:block' : 'display:none';
                                ?>
                                    <div class="grupo-item">
                                        <div class="grupo-header" onclick="toggleGrupo(this)">
                                            <span><?= $titulo ?></span> <i class="bi bi-chevron-down"></i>
                                        </div>
                                        <div class="subcategorias" style="<?= $aberto ?>">
                                            <?php foreach ($categorias as $cat): 
                                                if (in_array($cat['id'], $ids)): 
                                                    $inputID = "cat_" . $cat['id'] . "_" . preg_replace('/[^a-zA-Z0-9]/', '', $titulo);
                                                ?>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="radio" name="categoria" value="<?= $cat['id'] ?>" id="<?= $inputID ?>" <?= ($isEdit && $roupa['idCategoria'] == $cat['id']) ? 'checked' : '' ?> required>
                                                        <label class="form-check-label" for="<?= $inputID ?>"><?= htmlspecialchars($cat['nome']) ?></label>
                                                    </div>
                                                <?php endif; endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <label class="form-label fw-bold required-label">Estações</label>
                                    <div class="d-flex flex-column gap-1" id="grupoEstacoes">
                                        <?php 
                                        $marcados = $isEdit ? explode(',', $roupa['estacoes'] ?? '') : [];
                                        foreach ($listaEstacoes as $est): ?>
                                            <div class="form-check">
                                                <input class="form-check-input check-estacao" type="checkbox" name="estacoes[]" value="<?= $est['id'] ?>" id="est<?= $est['id'] ?>" <?= in_array($est['id'], $marcados) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="est<?= $est['id'] ?>"><?= $est['nome'] ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-md-7 mb-3">
                                    <label class="form-label fw-bold required-label">Cores Principais</label>
                                    <div class="color-container">
                                        <div class="color-wrapper">
                                            <input type="color" class="color-circle" name="cor1" value="<?= $roupa['cor1'] ?? '#000000' ?>">
                                            <small class="text-muted text-nowrap">(Cor Principal)</small>
                                        </div>
                                        <div class="color-wrapper">
                                            <input type="color" class="color-circle" name="cor2" value="<?= $roupa['cor2'] ?? '#ffffff' ?>">
                                            <small class="text-muted text-nowrap">(Cor Secundária)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Tags (Opcional)</label>
                                <input type="text" class="form-control" name="tags" value="<?= htmlspecialchars($roupa['tags'] ?? '') ?>" placeholder="Ex: seda, listrado, vintage">
                            </div>

                            <button type="submit" class="btn btn-dark btn-lg w-100 py-3 shadow">
                                <i class="bi bi-check-lg"></i> <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar Roupa' ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Modal de Alerta Padronizado -->
    <div class="modal fade" id="modalErro" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center" style="border-radius: 20px;">
                <div class="modal-header d-flex justify-content-center border-0 pt-4"><i class="bi bi-exclamation-circle text-warning" style="font-size: 3rem;"></i></div>
                <div class="modal-body pt-0"><h5 class="fw-bold">Atenção!</h5><p class="text-muted small px-2" id="msgErroTexto"></p></div>
                <div class="modal-footer border-0 justify-content-center pb-4"><button type="button" class="btn btn-dark rounded-pill px-5 fw-bold btn-sm" data-bs-dismiss="modal">Entendi</button></div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalErro = new bootstrap.Modal(document.getElementById('modalErro'));

        function validarForm(event) {
            const checks = document.querySelectorAll('.check-estacao:checked');
            if (checks.length === 0) {
                event.preventDefault();
                document.getElementById('msgErroTexto').innerText = "Por favor, selecione pelo menos uma estação antes de salvar.";
                modalErro.show();
                return false;
            }
            return true;
        }

        function toggleGrupo(header) {
            const sub = header.nextElementSibling;
            const icon = header.querySelector('i');
            const visible = sub.style.display === "block";
            sub.style.display = visible ? "none" : "block";
            icon.classList.replace(visible ? 'bi-chevron-up' : 'bi-chevron-down', visible ? 'bi-chevron-down' : 'bi-chevron-up');
        }

        function previewImg(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const prev = document.getElementById('preview');
                    prev.src = e.target.result;
                    prev.classList.remove('d-none');
                    if(document.getElementById('currentImg')) document.getElementById('currentImg').style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>