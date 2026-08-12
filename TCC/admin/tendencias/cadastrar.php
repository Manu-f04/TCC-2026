<?php
$pagina_titulo = "Cadastrar Tendência";
$pagina_ativa = "tendencias";

// Garante o carregamento dos arquivos de configuracao da pasta pai (admin)
chdir(__DIR__ . '/..');
require_once("verificar_admin.php");
require_once("../conexao.php");

$mensagem = "";
$hoje = date('Y-m-d'); // Data atual no formato YYYY-MM-DD

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo          = trim($_POST['titulo']);
    $categoria       = $_POST['categoria'];
    $data_publicacao = $_POST['data_publicacao'];
    $descricao       = trim($_POST['descricao']);
    $nomeImagem      = '';

    if ($data_publicacao < $hoje) {
        $mensagem = "data_invalida";
    } else {
        // Verifica se já existe uma tendência cadastrada com o mesmo título (ignorando maiúsculas/minúsculas)
        $stmtCheck = $con->prepare("SELECT id FROM tendencias WHERE LOWER(titulo) = LOWER(?)");
        $stmtCheck->bind_param("s", $titulo);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();

        if ($resCheck->num_rows > 0) {
            $mensagem = "duplicado";
        } else {
            // Extrai o número do mês (1 a 12) da data selecionada
            $mes = (int)date('n', strtotime($data_publicacao));

            if (!empty($_FILES['imagem']['name'])) {
                $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
                if (in_array($extensao, ['jpg', 'jpeg', 'jfif', 'png', 'webp'])) {
                    $nomeImagem = uniqid('tend_') . '.' . $extensao;
                    $destino = '../uploads/' . $nomeImagem;

                    if (!is_dir('../uploads')) {
                        mkdir('../uploads', 0777, true);
                    }
                    move_uploaded_file($_FILES['imagem']['tmp_name'], $destino);
                }
            }

            $stmt = $con->prepare("INSERT INTO tendencias (titulo, categoria, mes, descricao, imagem) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiss", $titulo, $categoria, $mes, $descricao, $nomeImagem);
            
            if ($stmt->execute()) {
                $mensagem = "sucesso";
            } else {
                $mensagem = "erro";
            }
            $stmt->close();
        }
        $stmtCheck->close();
    }
}

require_once("header_admin.php");
?>

<div class="mb-4">
    <a href="index.php" class="btn btn-light btn-sm mb-3"><i class="bi bi-arrow-left"></i> Voltar para a Lista</a>
    <h1 class="fw-bold h3">Cadastrar Nova Tendência</h1>
    <p class="text-muted">Adicione destaques para serem exibidos na página principal do site.</p>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white">
    <div class="card-body p-4">
        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-md-12">
                    <label class="form-label fw-bold">Título da Tendência</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ex: Tendências Verão 2026" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Posição do Card</label>
                    <select name="categoria" class="form-select" required>
                        <option value="destaque">Card Grande (Destaque Principal)</option>
                        <option value="card1">Card 1</option>
                        <option value="card2">Card 2</option>
                        <option value="card3">Card 3</option>
                        <option value="card4">Card 4</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Data de Publicação</label>
                    <input type="date" name="data_publicacao" class="form-control" min="<?= $hoje ?>" value="<?= $hoje ?>" required>
                    <small class="text-muted">Selecione hoje ou uma data futura.</small>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold">Descrição</label>
                    <textarea name="descricao" class="form-control" rows="4" placeholder="Escreva o texto descritivo sobre o que está em alta..." required></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold">Imagem da Tendência</label>
                    <input type="file" name="imagem" class="form-control" accept="image/*" required>
                    
                    <!-- Dica de Formato e Imagem -->
                    <div class="alert alert-light border mt-2 mb-0 p-3 rounded-3 small text-secondary">
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
                    <i class="bi bi-check-lg"></i> Salvar Tendência
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
            title: 'Cadastrado!',
            text: 'A nova tendência foi salva com sucesso.',
            icon: 'success',
            confirmButtonColor: '#212529'
        }).then(() => { window.location.href = "index.php"; });
    <?php elseif ($mensagem == "duplicado"): ?>
        Swal.fire({
            title: 'Tendência já existente!',
            text: 'Já existe uma tendência cadastrada com esse mesmo título. Por favor, utilize outro título.',
            icon: 'warning',
            confirmButtonColor: '#212529'
        });
    <?php elseif ($mensagem == "data_invalida"): ?>
        Swal.fire({
            title: 'Data Inválida!',
            text: 'Você não pode cadastrar uma tendência para datas anteriores a hoje.',
            icon: 'warning',
            confirmButtonColor: '#212529'
        });
    <?php elseif ($mensagem == "erro"): ?>
        Swal.fire({
            title: 'Erro!',
            text: 'Não foi possível salvar a tendência. Verifique os campos.',
            icon: 'error',
            confirmButtonColor: '#212529'
        });
    <?php endif; ?>
});
</script>

<?php require_once("footer_admin.php"); ?>