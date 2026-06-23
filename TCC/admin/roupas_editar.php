<?php 
require_once("verificar_admin.php"); 
require_once("../conexao.php"); 

$pagina_titulo = "Editar Peça";
$pagina_ativa = "roupas"; 

try {
    $resultCat = $con->query("SELECT id, nome FROM categorias ORDER BY nome"); 
    $categorias = $resultCat->fetch_all(MYSQLI_ASSOC); 
    $resultEst = $con->query("SELECT id, nome FROM estacoes ORDER BY id"); 
    $listaEstacoes = $resultEst->fetch_all(MYSQLI_ASSOC); 
} catch (Exception $e) { die("Erro: " . $e->getMessage()); }

$roupa = null; 
$isEdit = false; 
$showSuccess = false;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $con->prepare("SELECT * FROM roupas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $roupa = $stmt->get_result()->fetch_assoc();
    if ($roupa) { $isEdit = true; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isEdit) {
    $idRoupa = $_POST['id'];
    $categoriaForm = $_POST['categoria'];
    $cor1 = $_POST['cor1'];
    $cor2 = $_POST['cor2'];
    $tags = trim($_POST['tags']);
    $estacoesStr = implode(',', $_POST['estacoes'] ?? []);

    $sql = "UPDATE roupas SET cor1=?, cor2=?, idCategoria=?, estacoes=?, tags=? WHERE id=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssissi", $cor1, $cor2, $categoriaForm, $estacoesStr, $tags, $idRoupa);
    
    if($stmt->execute()) {
        $showSuccess = true;
    }
}

require_once("header_admin.php"); 
?>

<div class="mb-4">
    <a href="roupas_lista.php" class="btn btn-light btn-sm mb-2"><i class="bi bi-arrow-left"></i> Voltar para a Lista</a>
    <h1 class="fw-bold h3">Ajustar Informações Técnicas</h1>
</div>

<?php if ($isEdit): ?>
<div class="card border-0 shadow-sm rounded-4 bg-white">
    <div class="card-body p-4">
        <form id="formEditar" method="POST">
            <input type="hidden" name="id" value="<?= $roupa['id'] ?>">
            <div class="row g-4">
                <div class="col-md-4 text-center border-end">
                    <label class="form-label fw-bold d-block text-start">Peça Cadastrada</label>
                    <img src="../<?= htmlspecialchars($roupa['foto']) ?>" class="img-fluid rounded-4 border shadow-sm" style="max-height: 400px; width: 100%; object-fit: cover;">
                    <div class="badge bg-dark mt-3 px-3 py-2">Imagem Original</div>
                </div>

                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Categoria</label>
                            <select name="categoria" class="form-select" required>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $roupa['idCategoria'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tag / Estilo</label>
                            <input type="text" class="form-control" name="tags" value="<?= htmlspecialchars($roupa['tags']) ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Estações</label>
                            <div class="p-3 border rounded-3 bg-light d-flex flex-wrap gap-3">
                                <?php 
                                $marcados = explode(',', $roupa['estacoes'] ?? '');
                                foreach ($listaEstacoes as $est): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="estacoes[]" value="<?= $est['id'] ?>" id="est<?= $est['id'] ?>" <?= in_array($est['id'], $marcados) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="est<?= $est['id'] ?>"><?= $est['nome'] ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold">Cores Principais</label>
                            <div class="d-flex gap-2 p-2 border rounded bg-white shadow-sm">
                                <input type="color" class="form-control form-control-color border-0" name="cor1" value="<?= $roupa['cor1'] ?>">
                                <input type="color" class="form-control form-control-color border-0" name="cor2" value="<?= $roupa['cor2'] ?>">
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 pt-3 border-top text-end">
                        <button type="submit" class="btn btn-dark px-5 py-2 rounded-3 shadow-sm">
                            <i class="bi bi-check-lg"></i> Salvar Alterações
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
<?php if ($showSuccess): ?>
    Swal.fire({
        title: 'Atualizado!',
        text: 'As informações da roupa foram salvas com sucesso.',
        icon: 'success',
        confirmButtonColor: '#212529'
    }).then(() => {
        window.location.href = 'roupas_lista.php';
    });
<?php endif; ?>

document.getElementById('formEditar').onsubmit = function() {
    if (document.querySelectorAll('input[name="estacoes[]"]:checked').length === 0) {
        Swal.fire('Ops!', 'Selecione pelo menos uma estação.', 'warning');
        return false;
    }
    return true;
};
</script>

<?php require_once("footer_admin.php"); ?>