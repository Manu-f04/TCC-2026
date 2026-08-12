<?php
$pagina_titulo = "Editar Tendência";
$pagina_ativa = "tendencias";

// Garante o carregamento dos arquivos de configuracao da pasta pai (admin)
chdir(__DIR__ . '/..');
require_once("verificar_admin.php");
require_once("../conexao.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mensagem = "";
$hoje = date('Y-m-d');

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $con->prepare("SELECT * FROM tendencias WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$tendencia = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tendencia) {
    header("Location: index.php");
    exit;
}


$mesCadastrado = isset($tendencia['mes']) ? (int)$tendencia['mes'] : (int)date('n');
$dataExibicao = date('Y') . '-' . str_pad($mesCadastrado, 2, '0', STR_PAD_LEFT) . '-01';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo          = trim($_POST['titulo']);
    $categoria       = $_POST['categoria'];
    $data_publicacao = $_POST['data_publicacao'];
    $descricao       = trim($_POST['descricao']);
    $nomeImagem      = $tendencia['imagem']; // Mantém a imagem atual caso não suba nova

    // Bloqueia a atualização se a data informada for anterior a hoje
    if ($data_publicacao < $hoje) {
        $mensagem = "data_invalida";
    } else {
        // Extrai o número do mês (1 a 12) da data selecionada
        $mes = (int)date('n', strtotime($data_publicacao));

        if (!empty($_FILES['imagem']['name'])) {
            $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            if (in_array($extensao, ['jpg', 'jpeg', 'png', 'webp', 'jfif'])) {
                $novoNome = uniqid('tend_') . '.' . $extensao;
                $destino = '../uploads/' . $novoNome;

                if (!is_dir('../uploads')) {
                    mkdir('../uploads', 0777, true);
                }

                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
                    $nomeImagem = $novoNome;
                }
            }
        }

        $stmt = $con->prepare("UPDATE tendencias SET titulo = ?, categoria = ?, mes = ?, descricao = ?, imagem = ? WHERE id = ?");
        $stmt->bind_param("ssissi", $titulo, $categoria, $mes, $descricao, $nomeImagem, $id);
        
        if ($stmt->execute()) {
            $mensagem = "sucesso";
        } else {
            $mensagem = "erro";
        }
        $stmt->close();
    }
}

require_once("header_admin.php");
?>

<div class="mb-4">
    <a href="index.php" class="btn btn-light btn-sm mb-3"><i class="bi bi-arrow-left"></i> Voltar para a Lista</a>
    <h1 class="fw-bold h3">Editar Tendência</h1>
    <p class="text-muted">Atualize as informações do destaque exibido na página principal do site.</p>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white">
    <div class="card-body p-4">
        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-md-12">
                    <label class="form-label fw-bold">Título da Tendência</label>
                    <input type="text" name="titulo" class="form-control" value="<?= htmlspecialchars($tendencia['titulo']) ?>" placeholder="Ex: Tendências Verão 2026" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Posição do Card</label>
                    <select name="categoria" class="form-select" required>
                        <option value="destaque" <?= $tendencia['categoria'] === 'destaque' ? 'selected' : '' ?>>Card Grande (Destaque Principal)</option>
                        <option value="card1" <?= $tendencia['categoria'] === 'card1' ? 'selected' : '' ?>>Card 1</option>
                        <option value="card2" <?= $tendencia['categoria'] === 'card2' ? 'selected' : '' ?>>Card 2</option>
                        <option value="card3" <?= $tendencia['categoria'] === 'card3' ? 'selected' : '' ?>>Card 3</option>
                        <option value="card4" <?= $tendencia['categoria'] === 'card4' ? 'selected' : '' ?>>Card 4</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Data de Publicação</label>
                    <input type="date" name="data_publicacao" class="form-control" value="<?= $dataExibicao ?>" min="<?= $hoje ?>" required>
                    <small class="text-muted">Altere a data para atualizar o mês de exibição (somente datas de hoje em diante).</small>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold">Descrição</label>
                    <textarea name="descricao" class="form-control" rows="4" placeholder="Escreva o texto descritivo sobre o que está em alta..." required><?= htmlspecialchars($tendencia['descricao']) ?></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold">Imagem da Tendência (Opcional para alterar)</label>
                    <input type="file" name="imagem" class="form-control" accept="image/*">
                    
                    <?php if (!empty($tendencia['imagem'])): ?>
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <span class="small text-muted">Imagem atual:</span>
                            <img src="../uploads/<?= htmlspecialchars($tendencia['imagem']) ?>" alt="Imagem Atual" class="rounded border" style="height: 45px; object-fit: cover;">
                        </div>
                    <?php endif; ?>

                    <!-- Dica de Formato e Imagem -->
                    <div class="alert alert-light border mt-3 mb-0 p-3 rounded-3 small text-secondary">
                        <div class="d-flex align-items-center mb-1 text-dark fw-bold">
                            <i class="bi bi-aspect-ratio me-2 fs-6"></i> Dica de Formato e Imagem
                        </div>
                        Para evitar que a imagem fique muito cortada ou descentralizada no card:
                        <ul class="mb-0 mt-1 ps-3">
                            <li>Formatos aceitos: <strong>JPG, JPEG, JFIF, PNG e WEBP</strong>.</li>
                            <li>Prefira fotos na <strong>horizontal (retangulares)</strong>.</li>
                            <li>O sistema centraliza a imagem automaticamente no espaço disponível.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top text-end">
                <a href="index.php" class="btn btn-light me-2">Cancelar</a>
                <button type="submit" class="btn btn-dark px-4 shadow-sm">
                    <i class="bi bi-check-lg"></i> Atualizar Tendência
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($mensagem == "sucesso"): ?>
        Swal.fire({
            title: 'Atualizado!',
            text: 'A tendência foi alterada com sucesso.',
            icon: 'success',
            confirmButtonColor: '#212529'
        }).then(() => { window.location.href = "index.php"; });
    <?php elseif ($mensagem == "data_invalida"): ?>
        Swal.fire({
            title: 'Data Inválida!',
            text: 'Não é possível selecionar uma data anterior ao dia de hoje.',
            icon: 'warning',
            confirmButtonColor: '#212529'
        });
    <?php elseif ($mensagem == "erro"): ?>
        Swal.fire({
            title: 'Erro!',
            text: 'Não foi possível atualizar a tendência. Verifique os campos.',
            icon: 'error',
            confirmButtonColor: '#212529'
        });
    <?php endif; ?>
});
</script>

<?php require_once("footer_admin.php"); ?>