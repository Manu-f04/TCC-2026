<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexão e autorização buscando corretamente da raiz do projeto
require_once '../conexao.php';
require_once '../autorizacao.php';

/** * Normalização de Cores CSS - Trazido exatamente do seu looks.php
 */
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
$erro = "";
$sucesso = false;

// Verificar se o ID da publicação foi fornecido via parâmetro GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: comunidade.php");
    exit;
}

$id_look = intval($_GET['id']);

// Processa a atualização da legenda enviada pelo formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $legenda = trim($_POST['legenda'] ?? '');

    // Atualiza apenas a legenda da publicação pertencente ao usuário logado
    $stmt = $con->prepare("UPDATE looks SET legenda = ? WHERE id = ? AND idusuario = ? AND publicado = 1");
    $stmt->bind_param("sii", $legenda, $id_look, $userId);
    
    if ($stmt->execute()) {
        $sucesso = true;
    } else {
        $erro = "Erro ao atualizar a publicação. Tente novamente.";
    }
    $stmt->close();
}

// BUSCA OS DADOS DO LOOK ESPECÍFICO (Garantindo segurança que pertence ao usuário)
$sql = "SELECT * FROM looks WHERE id = ? AND idusuario = ? AND publicado = 1 LIMIT 1";
$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $id_look, $userId); 
$stmt->execute();
$look = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Se a publicação não for encontrada ou não pertencer ao usuário, redireciona de volta
if (!$look) {
    header("Location: comunidade.php");
    exit;
}

// =========================================================================
// CAPTURA E CORREÇÃO DINÂMICA DA NAVBAR E FOOTER (SAINDO DA PASTA COMUNIDADE)
// =========================================================================
ob_start();
include '../nav.php';
$navbar_conteudo = ob_get_clean();

$navbar_conteudo = str_replace('href="index.php"', 'href="../index.php"', $navbar_conteudo);
$navbar_conteudo = str_replace('href="sobre.php"', 'href="../sobre.php"', $navbar_conteudo);
$navbar_conteudo = str_replace('href="guardaroupa.php"', 'href="../guardaroupa.php"', $navbar_conteudo);
$navbar_conteudo = str_replace('href="looks.php"', 'href="../looks.php"', $navbar_conteudo);
$navbar_conteudo = str_replace('href="comunidade/comunidade.php"', 'href="comunidade.php"', $navbar_conteudo);
$navbar_conteudo = str_replace('href="configuracoes_perfil.php"', 'href="../configuracoes_perfil.php"', $navbar_conteudo);
$navbar_conteudo = str_replace('href="cadastro.php"', 'href="../cadastro.php"', $navbar_conteudo);
$navbar_conteudo = str_replace('href="login.php"', 'href="../login.php"', $navbar_conteudo);
$navbar_conteudo = str_replace('href="logout.php"', 'href="../logout.php"', $navbar_conteudo);
$navbar_conteudo = str_replace('href="admin/dashboard.php"', 'href="../admin/dashboard.php"', $navbar_conteudo);
$navbar_conteudo = str_replace('src="assets/', 'src="../assets/', $navbar_conteudo);
$navbar_conteudo = str_replace('src="logo.jpg"', 'src="logo.jpg"', $navbar_conteudo);

ob_start();
include '../footer.php';
$footer_conteudo = ob_get_clean();
$footer_conteudo = str_replace('href="index.php"', 'href="../index.php"', $footer_conteudo);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Publicação </title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <style>
        html, body { background-color: #fafafa !important; font-family: 'Poppins', sans-serif !important; height: auto !important; }
        
        /* Estilos de Card idênticos ao do looks.php / publicar_look.php */
        .look-card { border: 3px solid #212529 !important; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.15); background:#f8f9fa; position: relative; max-width: 340px; margin: 0 auto; }
        .look-preview-container { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 300px; background-color: #f8f9fa; padding: 10px; position: relative; }
        .look-item-img { max-width: 90%; max-height: 100px; object-fit: contain; margin: 2px 0; }
        .extra-item-img { position: absolute; right: 15px; top: 55%; max-width: 70px; max-height: 70px; border: none; background: transparent; z-index: 5;}
        .color-swatch-display { width: 18px; height: 18px; border-radius: 50%; border: 1px solid #ddd; display: inline-block; }
        .color-swatch-wrapper { display: flex; gap: 5px; align-items: center; margin-top: 8px; flex-wrap: wrap; }

        .btn-publicar {
            background-color: #212529 !important;
            color: #fff !important;
            border: none;
            transition: background-color 0.2s ease;
        }
        .btn-publicar:hover {
            background-color: #000000 !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="index-page">

    <?= $navbar_conteudo ?>

    <main class="main" style="margin-top: 130px;">
        <div class="container my-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    
                    <a href="comunidade.php" class="text-decoration-none text-secondary d-inline-flex align-items-center mb-4 small fw-bold">
                        <i class="bi bi-arrow-left me-1"></i> Voltar para a Comunidade
                    </a>

                    <div class="card p-4 shadow-sm border-0 rounded-4 bg-white">
                        <div class="mb-4">
                            <h2 class="fw-bold h4 text-dark mb-1">Editar Publicação</h2>
                            <p class="text-muted small mb-0">Modifique a legenda abaixo e salve as alterações do seu post.</p>
                        </div>

                        <?php if (!empty($erro)): ?>
                            <div class="alert alert-danger rounded-3 small"><?= $erro ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-3">Visualização do Look selecionado:</label>
                                
                                <div class="card look-card">
                                    <div class="look-preview-container">
                                        <?php
                                        $todas_cores = [];
                                        $ids_verticais = array_filter([$look['idroupa1'], $look['idroupa2'], $look['idroupa5'], $look['idroupa3']]);
                                        if (!empty($ids_verticais)) {
                                            $ids_str = implode(',', $ids_verticais);
                                            $res_p = $con->query("SELECT foto, cor1, cor2 FROM roupas WHERE id IN ($ids_str) ORDER BY FIELD(id, $ids_str)");
                                            while($p = $res_p->fetch_assoc()){ 
                                                echo '<img src="../'.$p['foto'].'" class="look-item-img">'; 
                                                if($p['cor1']) $todas_cores[] = $p['cor1'];
                                                if($p['cor2']) $todas_cores[] = $p['cor2'];
                                            }
                                        }
                                        if (!empty($look['idroupa4'])) {
                                            $res_extra = $con->query("SELECT foto, cor1, cor2 FROM roupas WHERE id = " . $look['idroupa4']);
                                            if($extra = $res_extra->fetch_assoc()) {
                                                echo '<img src="../'.$extra['foto'].'" class="extra-item-img">';
                                                if($extra['cor1']) $todas_cores[] = $extra['cor1'];
                                                if($extra['cor2']) $todas_cores[] = $extra['cor2'];
                                            }
                                        }
                                        ?>
                                    </div>
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-truncate fw-bold mb-1"><?= htmlspecialchars($look['nome']) ?></h6>
                                        <div class="color-swatch-wrapper">
                                            <?php 
                                            $exibidas = array_unique(array_filter($todas_cores));
                                            foreach ($exibidas as $cor_bruta) {
                                                $hex = normalizeColorValue($cor_bruta);
                                                if($hex) echo '<div class="color-swatch-display" style="background-color: '.$hex.';" title="'.$cor_bruta.'"></div>';
                                            }
                                            ?>
                                        </div>
                                        <p class="small text-muted mb-0 mt-2 text-truncate" style="font-size:0.75rem;">Tags vinculadas: <?= htmlspecialchars($look['tags']) ?: 'Sem tags' ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-2">Altere a legenda do feed:</label>
                                <textarea name="legenda" id="legenda" class="form-control rounded-3" rows="3" placeholder="Comente algo sobre o seu look para a comunidade..." maxlength="255" style="border: 1px solid #ced4da; resize: none;"><?= htmlspecialchars($look['legenda']) ?></textarea>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="comunidade.php" class="btn btn-outline-secondary rounded-pill px-4 py-2">Cancelar</a>
                                <button type="submit" class="btn btn-publicar rounded-pill px-5 py-2 fw-bold">
                                    <i class="bi bi-check-lg me-1"></i> Salvar Alterações
                                </button>
                            </div>

                        </form>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <?= $footer_conteudo ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
    <?php if ($sucesso): ?>
        Swal.fire({
            title: 'Atualizado!',
            text: 'As alterações na legenda foram salvas com sucesso.',
            icon: 'success',
            confirmButtonColor: '#212529'
        }).then(() => {
            window.location.href = 'comunidade.php';
        });
    <?php endif; ?>
    </script>
</body>
</html>