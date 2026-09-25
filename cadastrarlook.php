<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "autorizacao.php";   
require_once "conexao.php";       

function buscarRoupasPorCategorias($con, $idUsuario, $categoriasArray) {
    if (empty($categoriasArray)) return [];
    $placeholders = str_repeat('?,', count($categoriasArray) - 1) . '?';
    $sql = "SELECT id FROM categorias WHERE nome IN ($placeholders)";
    $stmt = $con->prepare($sql); 
    $types = str_repeat('s', count($categoriasArray)); 
    $stmt->bind_param($types, ...$categoriasArray); 
    $stmt->execute();
    $result = $stmt->get_result();
    
    $catIds = [];
    while ($row = $result->fetch_row()) { $catIds[] = $row[0]; }
    if (empty($catIds)) return [];

    $placeholdersR = str_repeat('?,', count($catIds) - 1) . '?';
    $sqlR = "SELECT id, foto FROM roupas WHERE idusuario = ? AND idCategoria IN ($placeholdersR) ORDER BY id DESC";
    $stmtR = $con->prepare($sqlR);
    $typesR = 'i' . str_repeat('i', count($catIds)); 
    $stmtR->bind_param($typesR, $idUsuario, ...$catIds);
    $stmtR->execute();
    return $stmtR->get_result()->fetch_all(MYSQLI_ASSOC);
}

$userId = $_SESSION['idusuario']; 
$mensagem = '';
$showErrorModal = false; 
$errorMsg = '';
$look_data = null; 
$isEdit = false; 
$preview_fotos = []; 
$acessorios_selecionados_ids = []; 

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $lookId = (int)$_GET['id'];
    $sql = "SELECT * FROM looks WHERE id = ? AND idusuario = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $lookId, $userId);
    $stmt->execute();
    $look_data = $stmt->get_result()->fetch_assoc();
    
    if ($look_data) {
        $isEdit = true;
        if (!empty($look_data['idroupa4'])) {
            $acessorios_selecionados_ids = explode(',', $look_data['idroupa4']);
        }
        $pecas_ids = [$look_data['idroupa1'], $look_data['idroupa2'], $look_data['idroupa3'], $look_data['idroupa5']];
        foreach($acessorios_selecionados_ids as $ac_id) { $pecas_ids[] = $ac_id; }
        $pecas_ids = array_filter($pecas_ids);
        
        if(!empty($pecas_ids)){
            $ids_string = implode(',', array_map('intval', $pecas_ids));
            $res_fotos = $con->query("SELECT id, foto FROM roupas WHERE id IN ($ids_string)");
            while($f = $res_fotos->fetch_assoc()){ $preview_fotos[$f['id']] = $f['foto']; }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_look = trim($_POST['nomeLook'] ?? '');
    $tags      = trim($_POST['tagsLook'] ?? '');
    $lookId    = !empty($_POST['lookId']) ? (int)$_POST['lookId'] : null;

    $r1 = (!empty($_POST['idroupa1'])) ? (int)$_POST['idroupa1'] : NULL;
    $r2 = (!empty($_POST['idroupa2'])) ? (int)$_POST['idroupa2'] : NULL;
    $r3 = (!empty($_POST['idroupa3'])) ? (int)$_POST['idroupa3'] : NULL;
    $r4 = (!empty($_POST['idroupa4'])) ? trim($_POST['idroupa4']) : NULL; 
    $r5 = (!empty($_POST['idroupa5'])) ? (int)$_POST['idroupa5'] : NULL;

    $temCorpo = (!empty($r5) || (!empty($r1) && !empty($r2)));

    if (empty($nome_look)) {
        $errorMsg = "Dê um nome ao seu look!";
        $showErrorModal = true;
    } elseif (!$temCorpo || empty($r3)) {
        $errorMsg = "Selecione as peças principais (Corpo e Calçado) antes de salvar!";
        $showErrorModal = true;
    } else {
        $sqlCheck = "SELECT id FROM looks WHERE idusuario = ? AND 
                     COALESCE(idroupa1,0)=COALESCE(?,0) AND COALESCE(idroupa2,0)=COALESCE(?,0) AND 
                     COALESCE(idroupa3,0)=COALESCE(?,0) AND COALESCE(idroupa4,'')=COALESCE(?,'') AND 
                     COALESCE(idroupa5,0)=COALESCE(?,0) AND id != ?";
        $stmtCheck = $con->prepare($sqlCheck);
        $cid = $isEdit ? $lookId : 0;
        $stmtCheck->bind_param("iiiisii", $userId, $r1, $r2, $r3, $r4, $r5, $cid);
        $stmtCheck->execute();
        
        if ($stmtCheck->get_result()->num_rows > 0) {
            $errorMsg = "Você já tem este look exatamente igual em sua galeria!";
            $showErrorModal = true;
        } else {
            if ($isEdit) {
                $sql = "UPDATE looks SET nome=?, tags=?, idroupa1=?, idroupa2=?, idroupa3=?, idroupa4=?, idroupa5=? WHERE id=? AND idusuario=?";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("ssiiissii", $nome_look, $tags, $r1, $r2, $r3, $r4, $r5, $lookId, $userId);
                $msg_sucesso = "Look alterado com sucesso!";
            } else {
                $sql = "INSERT INTO looks (idusuario, nome, tags, idroupa1, idroupa2, idroupa3, idroupa4, idroupa5) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("isssiiss", $userId, $nome_look, $tags, $r1, $r2, $r3, $r4, $r5);
                $msg_sucesso = "Look salvo com sucesso!";
            }

            if ($stmt->execute()) {
                echo "<script>window.location.href='looks.php?msg=" . urlencode($msg_sucesso) . "';</script>";
                exit;
            } else {
                $errorMsg = "Erro ao salvar no banco de dados.";
                $showErrorModal = true;
            }
        }
    }
}

$galeria_pecas = [
    'idroupa1' => [
        'label' => 'Cima',
        'items' => buscarRoupasPorCategorias($con, $userId, ['Top', 'Camisa/Camiseta', 'Regata', 'Cropped', 'Casaco', 'Body', 'Maiô', 'Biquíni (Parte de Cima)'])
    ],
    'idroupa2' => [
        'label' => 'Baixo',
        'items' => buscarRoupasPorCategorias($con, $userId, ['Calça', 'Short', 'Saia', 'Bermuda', 'Biquíni (Parte de Baixo)'])
    ],
    'idroupa5' => [
        'label' => 'Corpo Inteiro',
        'items' => buscarRoupasPorCategorias($con, $userId, ['Vestido', 'Macacão', 'Maiô'])
    ],
    'idroupa3' => [
        'label' => 'Calçado',
        'items' => buscarRoupasPorCategorias($con, $userId, ['Tênis', 'Sandália', 'Salto', 'Bota'])
    ],
    'idroupa4' => [
        'label' => 'Acessório', 
        'items' => buscarRoupasPorCategorias($con, $userId, ['Casaco', 'Bolsa', 'Óculos', 'Chapéu', 'Canga', 'Saída de Praia'])
    ],
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Editar' : 'Cadastrar' ?> Look</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <style>
        .btn-floating {background:#000!important;color:#fff!important;border-radius:50px!important;padding:10px 25px!important;font-weight:500!important;border:none!important;display:inline-block!important;text-decoration:none!important}
        .look-layout-container { display: flex; gap: 20px; max-width: 550px; margin: 0 auto; }
        .look-preview-area { display: flex; flex-direction: column; gap: 12px; width: 300px; }
        .look-accessories-area { display: flex; flex-direction: column; gap: 12px; width: 110px; }
        .look-preview-item { background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 12px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; height: 160px; }
        .box-calcado { height: 100px; }
        .preview-img { max-width: 90%; max-height: 90%; object-fit: contain; display: none; }
        .preview-label { position: absolute; color: #adb5bd; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; text-align: center; pointer-events: none;}
        
        .grade-acessorio-vazia { height: 100px; border: 2px dotted #adb5bd; background: #fff; cursor: pointer; border-radius: 12px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; transition: 0.2s; }
        .grade-acessorio-vazia.active-target { border-color: #000; background: #f0f7ff; box-shadow: 0 0 5px rgba(0,0,0,0.15); }
        .btn-remove-peca { position: absolute; top: 5px; right: 5px; background: rgba(220, 53, 69, 0.9); color: white; border: none; border-radius: 50%; width: 22px; height: 22px; font-size: 12px; display: none; align-items: center; justify-content: center; z-index: 10; cursor: pointer; }
        .look-preview-item.has-content:hover .btn-remove-peca, .grade-acessorio-vazia.has-content:hover .btn-remove-peca { display: flex; }

        .roupa-item-gallery img { width: 100%; height: 110px; object-fit: contain; cursor: pointer; border: 2px solid #f1f1f1; border-radius: 10px; background: #fff; }
        .roupa-item-gallery img.selected { border-color: #000; box-shadow: 0 0 8px rgba(0,0,0,0.2); }
        .finalizar-look-box { background: #fff; border: 1px solid #eee; border-radius: 15px; padding: 20px; margin: 40px auto; max-width: 420px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .btn-salvar { background: #000; color: #fff; padding: 10px 20px; border-radius: 10px; font-weight: 600; border: none; width: auto; min-width: 200px; font-size: 0.95rem; }
        .label-normal { font-weight: 600; margin-bottom: 4px; font-size: 0.9rem; color: #333; }
        .d-none-view { display: none !important; }
    </style>
</head>
<body class="index-page">
    <?php include 'nav.php'; ?>
    <main class="main">
        <div class="page-title light-background">
            <div class="container d-lg-flex justify-content-between align-items-center py-3">
                <h2 class="mb-0 fw-bold" style="font-size: 1.3rem;"><?= $isEdit ? 'Editar Look' : 'Montar Look' ?></h2>
                <a href="looks.php" class="btn btn-floating">Voltar</a>
            </div>
        </div>

        <div class="container mt-4">
            <form method="POST" id="formLook">
                <input type="hidden" name="lookId" value="<?= $look_data['id'] ?? '' ?>">
                <input type="hidden" name="idroupa1" value="<?= $look_data['idroupa1'] ?? '' ?>">
                <input type="hidden" name="idroupa2" value="<?= $look_data['idroupa2'] ?? '' ?>">
                <input type="hidden" name="idroupa3" value="<?= $look_data['idroupa3'] ?? '' ?>">
                <input type="hidden" name="idroupa4" id="idroupa4" value="<?= $look_data['idroupa4'] ?? '' ?>">
                <input type="hidden" name="idroupa5" value="<?= $look_data['idroupa5'] ?? '' ?>">

                <div class="row">
                    <div class="col-lg-5 mb-4">
                        <div class="look-layout-container">
                            <div class="look-preview-area">
                                <?php 
                                $campos = ['idroupa1' => 'Cima', 'idroupa2' => 'Baixo', 'idroupa5' => 'Corpo Inteiro', 'idroupa3' => 'Calçado'];
                                foreach($campos as $idField => $label): 
                                    $foto = isset($look_data[$idField]) ? ($preview_fotos[$look_data[$idField]] ?? '') : '';
                                    $classeCorpo = ($idField == 'idroupa5' && empty($foto)) ? 'd-none-view' : '';
                                    $classeCimaBaixo = (($idField == 'idroupa1' || $idField == 'idroupa2') && !empty($look_data['idroupa5'])) ? 'd-none-view' : '';
                                ?>
                                <div class="look-preview-item <?= ($idField == 'idroupa3')?'box-calcado':'' ?> <?= $foto ? 'has-content' : '' ?> <?= $classeCorpo ?> <?= $classeCimaBaixo ?>" id="box-<?= $idField ?>">
                                    <button type="button" class="btn-remove-peca" onclick="confirmarRemocao('<?= $idField ?>')"><i class="bi bi-x-lg"></i></button>
                                    <img id="img-<?= $idField ?>" src="<?= $foto ?>" class="preview-img" style="<?= $foto ? 'display:block;' : '' ?>">
                                    <span class="preview-label" style="<?= $foto ? 'display:none;' : '' ?>"><?= $label ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="look-accessories-area" id="coluna-acessorios-grades">
                                <!-- Gerado via JS -->
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <ul class="nav nav-tabs border-0" id="lookTabs">
                            <?php $active = true; foreach ($galeria_pecas as $key => $data): ?>
                            <li class="nav-item">
                                <button class="nav-link <?= $active?'active':'' ?> fw-bold text-dark border-0 py-2" id="tab-btn-<?= $key ?>" data-bs-toggle="tab" data-bs-target="#tab-<?= $key ?>" type="button"><?= $data['label'] ?></button>
                            </li>
                            <?php $active = false; endforeach; ?>
                        </ul>
                        <div class="tab-content p-3 border rounded-3 bg-white shadow-sm" style="min-height: 400px;">
                            <?php $active = true; foreach ($galeria_pecas as $key => $data): ?>
                            <div class="tab-pane fade <?= $active?'show active':'' ?>" id="tab-<?= $key ?>">
                                
                                <?php if($key === 'idroupa4'): ?>
                                <div class="bg-light p-2 rounded-3 mb-3 d-flex align-items-center justify-content-between">
                                    <span class="fw-bold text-secondary small">Quantas grades de acessórios você quer?</span>
                                    <select class="form-select form-select-sm w-auto fw-bold text-center" id="qtdGradesAcessorio" onchange="alterarQuantidadeGrades(this.value)">
                                        <option value="0">Nenhum</option>
                                        <option value="1">1 Grade</option>
                                        <option value="2">2 Grades</option>
                                        <option value="3">3 Grades</option>
                                        <option value="4">4 Grades</option>
                                        <option value="5">5 Grades</option>
                                    </select>
                                </div>
                                <?php endif; ?>

                                <div class="row row-cols-3 g-2">
                                    <?php foreach ($data['items'] as $roupa): ?>
                                    <div class="col text-center">
                                        <div class="roupa-item-gallery">
                                            <img src="<?= $roupa['foto'] ?>" 
                                                 class="img-peca-galeria"
                                                 id="galeria-item-<?= $roupa['id'] ?>"
                                                 data-id="<?= $roupa['id'] ?>" data-target="<?= $key ?>" onclick="selecionarPeca(this)">
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php $active = false; endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="finalizar-look-box text-center">
                    <h5 class="fw-bold mb-3">Finalizar Look</h5>
                    <div class="text-start">
                        <label class="label-normal">Nome do look</label>
                        <input type="text" name="nomeLook" class="form-control mb-3" value="<?= $look_data['nome'] ?? '' ?>" required>
                        <label class="label-normal">Tags</label>
                        <input type="text" name="tagsLook" class="form-control mb-3" value="<?= $look_data['tags'] ?? '' ?>" placeholder="Ex: casual, inverno, noite">
                    </div>
                    <button type="submit" class="btn btn-dark w-100 rounded-3 py-2 fw-bold">Salvar meu look</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Modal de Alerta Padronizado -->
    <div class="modal fade" id="modalErro" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center" style="border-radius: 20px;">
                <div class="modal-header d-flex justify-content-center border-0 pt-4"><i class="bi bi-exclamation-circle text-warning" style="font-size: 3rem;"></i></div>
                <div class="modal-body pt-0"><h5 class="fw-bold">Atenção!</h5><p class="text-muted small px-2" id="msgErroTexto"><?= $errorMsg ?></p></div>
                <div class="modal-footer border-0 justify-content-center pb-4"><button type="button" class="btn btn-dark rounded-pill px-5 fw-bold btn-sm" data-bs-dismiss="modal">Entendi</button></div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Remoção de Peça -->
    <div class="modal fade" id="modalConfirmarRemover" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center" style="border-radius: 20px;">
                <div class="modal-body p-4">
                    <h5 class="fw-bold">Remover peça do look?</h5>
                    <p class="text-muted small">A peça sairá da pré-visualização da montagem.</p>
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-light w-100 rounded-pill small" data-bs-dismiss="modal">Não</button>
                        <button type="button" id="btnConfirmarExclusaoPeca" class="btn btn-danger w-100 rounded-pill small">Sim</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <script>
    let campoParaRemover = "";
    let gradeAcessorioAlvoIndex = null; 
    const modalRemover = new bootstrap.Modal(document.getElementById('modalConfirmarRemover'));
    const modalErroInstance = new bootstrap.Modal(document.getElementById('modalErro'));
    let slotsAcessorios = []; 

    window.addEventListener('DOMContentLoaded', () => {
        <?php if ($showErrorModal): ?>
            modalErroInstance.show();
        <?php endif; ?>

        const input4 = document.getElementById('idroupa4');
        if(input4 && input4.value.trim() !== "") {
            slotsAcessorios = input4.value.split(',');
            document.getElementById('qtdGradesAcessorio').value = slotsAcessorios.length;
            renderizarGradesAcessorios(slotsAcessorios.length);
            
            slotsAcessorios.forEach((id, index) => {
                const imgOriginal = document.getElementById('galeria-item-' + id);
                if(imgOriginal) {
                    definirFotoNaGrade(index, id, imgOriginal.src);
                }
            });
        } else {
            alterarQuantidadeGrades(0);
        }
    });

    function alterarQuantidadeGrades(total) {
        total = parseInt(total);
        if (slotsAcessorios.length > total) {
            slotsAcessorios = slotsAcessorios.slice(0, total);
        } else {
            while(slotsAcessorios.length < total) { slotsAcessorios.push(""); }
        }
        renderizarGradesAcessorios(total);
        atualizarInputHiddenAcessorios();
        atualizarDestaquesGaleriaAcessorios();
    }

    function renderizarGradesAcessorios(total) {
        const container = document.getElementById('coluna-acessorios-grades');
        container.innerHTML = ""; 

        for(let i = 0; i < total; i++) {
            const div = document.createElement('div');
            div.className = "grade-acessorio-vazia" + (slotsAcessorios[i] ? " has-content" : "");
            div.id = `grade-ac-${i}`;
            div.setAttribute('onclick', `definirGradeAtiva(${i})`);
            
            let imgHtml = `<img id="img-grade-ac-${i}" class="preview-img" style="display:none;">`;
            let labelHtml = `<span class="preview-label" id="lbl-grade-ac-${i}">Ac. ${i+1}</span>`;
            let btnHtml = `<button type="button" class="btn-remove-peca" onclick="event.stopPropagation(); removerAcessorioSlot(${i})"><i class="bi bi-x-lg"></i></button>`;
            
            div.innerHTML = btnHtml + imgHtml + labelHtml;
            container.appendChild(div);
        }

        if(total > 0) {
            let primeiroVazio = slotsAcessorios.findIndex(val => val === "");
            definirGradeAtiva(primeiroVazio !== -1 ? primeiroVazio : 0);
        } else {
            gradeAcessorioAlvoIndex = null;
        }
    }

    function definirGradeAtiva(index) {
        gradeAcessorioAlvoIndex = index;
        document.querySelectorAll('.grade-acessorio-vazia').forEach(g => g.classList.remove('active-target'));
        const gradeAtiva = document.getElementById(`grade-ac-${index}`);
        if(gradeAtiva) gradeAtiva.classList.add('active-target');
    }

    function definirFotoNaGrade(index, id, src) {
        slotsAcessorios[index] = id;
        const box = document.getElementById(`grade-ac-${index}`);
        const img = document.getElementById(`img-grade-ac-${index}`);
        const lbl = document.getElementById(`lbl-grade-ac-${index}`);
        
        if(box && img && lbl) {
            img.src = src;
            img.style.display = "block";
            lbl.style.display = "none";
            box.classList.add('has-content');
        }
        atualizarInputHiddenAcessorios();
        atualizarDestaquesGaleriaAcessorios();
    }

    function removerAcessorioSlot(index) {
        slotsAcessorios[index] = "";
        const box = document.getElementById(`grade-ac-${index}`);
        const img = document.getElementById(`img-grade-ac-${index}`);
        const lbl = document.getElementById(`lbl-grade-ac-${index}`);
        
        if(box && img && lbl) {
            img.src = "";
            img.style.display = "none";
            lbl.style.display = "block";
            box.classList.remove('has-content');
        }
        atualizarInputHiddenAcessorios();
        atualizarDestaquesGaleriaAcessorios();
        definirGradeAtiva(index);
    }

    function atualizarInputHiddenAcessorios() {
        const filtrados = slotsAcessorios.filter(id => id !== "");
        document.getElementById('idroupa4').value = filtrados.join(',');
    }

    function atualizarDestaquesGaleriaAcessorios() {
        document.querySelectorAll(`img[data-target="idroupa4"]`).forEach(i => i.classList.remove('selected'));
        slotsAcessorios.forEach(id => {
            if(id) {
                const imgGaleria = document.getElementById('galeria-item-' + id);
                if(imgGaleria) imgGaleria.classList.add('selected');
            }
        });
    }

    function selecionarPeca(el) {
        const roupaId = el.dataset.id;
        const target = el.dataset.target;

        if (target === 'idroupa4') {
            if (gradeAcessorioAlvoIndex === null) {
                document.getElementById('msgErroTexto').innerText = "Por favor, selecione quantas grades de acessórios deseja colocar primeiro!";
                modalErroInstance.show();
                return;
            }
            definirFotoNaGrade(gradeAcessorioAlvoIndex, roupaId, el.src);
            let proximoVazio = slotsAcessorios.findIndex(val => val === "");
            if(proximoVazio !== -1) definirGradeAtiva(proximoVazio);
            return;
        }

        const inputHidden = document.querySelector(`input[name="${target}"]`);
        if (inputHidden) inputHidden.value = roupaId;

        if (target === 'idroupa5') { 
            limparDadosSemModal('idroupa1');
            limparDadosSemModal('idroupa2');
            document.getElementById('box-idroupa1').classList.add('d-none-view');
            document.getElementById('box-idroupa2').classList.add('d-none-view');
            document.getElementById('box-idroupa5').classList.remove('d-none-view');
        } else if (target === 'idroupa1' || target === 'idroupa2') { 
            limparDadosSemModal('idroupa5');
            document.getElementById('box-idroupa5').classList.add('d-none-view');
            document.getElementById('box-idroupa1').classList.remove('d-none-view');
            document.getElementById('box-idroupa2').classList.remove('d-none-view');
        }

        document.querySelectorAll(`img[data-target="${target}"]`).forEach(i => i.classList.remove('selected'));
        el.classList.add('selected');
        
        const imgPreview = document.getElementById('img-' + target);
        if (imgPreview) {
            imgPreview.src = el.src; 
            imgPreview.style.display = 'block';
        }
        const box = document.getElementById('box-' + target);
        if (box) box.classList.add('has-content');

        const label = box.querySelector('.preview-label');
        if (label) label.style.display = 'none';
    }

    function confirmarRemocao(idField) {
        campoParaRemover = idField;
        modalRemover.show();
    }

    document.getElementById('btnConfirmarExclusaoPeca').addEventListener('click', function() {
        limparDadosSemModal(campoParaRemover);
        modalRemover.hide();
    });

    function limparDadosSemModal(idField) {
        const input = document.querySelector(`input[name="${idField}"]`);
        if (input) input.value = "";

        const box = document.getElementById('box-' + idField);
        const img = document.getElementById('img-' + idField);
        if (img) {
            img.src = ""; 
            img.style.display = 'none';
        }
        if (box) box.classList.remove('has-content');

        const lbl = document.querySelector(`#box-${idField} .preview-label`);
        if(lbl) lbl.style.display = 'block';

        document.querySelectorAll(`img[data-target="${idField}"]`).forEach(i => i.classList.remove('selected'));
        
        if(idField === 'idroupa5'){
            document.getElementById('box-idroupa1').classList.remove('d-none-view');
            document.getElementById('box-idroupa2').classList.remove('d-none-view');
            document.getElementById('box-idroupa5').classList.add('d-none-view');
        }
    }
    </script>
</body>
</html>