<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../conexao.php'; 

$logado = isset($_SESSION['idusuario']) && !empty($_SESSION['idusuario']);
$id_usuario_logado = $logado ? (int)$_SESSION['idusuario'] : null;

// Descobre a URL base do seu projeto dinamicamente (ex: http://localhost/seu-projeto/)
$root_url = "http://localhost/manu.Info31/TCC/";

// Lógica para o usuário publicar um look dele
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['look_escolhido']) && $logado) {
    $id_look = intval($_POST['look_escolhido']);
    $stmt = $con->prepare("UPDATE looks SET publicado = 1, data_publicacao = NOW() WHERE id = ? AND idusuario = ?");
    $stmt->bind_param("ii", $id_look, $id_usuario_logado);
    $stmt->execute();
    header("Location: comunidade.php");
    exit;
}

// CAPTURAR O FILTRO DA URL
$filtro = isset($_GET['filtro']) && $_GET['filtro'] === 'populares' ? 'populares' : 'recentes';

// DEFINIR A ORDEM, FILTRO DE CURTIDAS DA SEMANA E LIMITE COM BASE NO FILTRO
if ($filtro === 'populares') {
    $ordenacao = "total_curtidas DESC, l.data_publicacao DESC";
    $condicao_popular = "HAVING total_curtidas > 0"; 
    $limite_posts = "LIMIT 4"; 
    $sql_contagem_curtidas = "(SELECT COUNT(*) FROM comunidade_curtidas c WHERE c.idlook = l.id AND c.data_curtida >= NOW() - INTERVAL 7 DAY)";
} else {
    $ordenacao = "l.data_publicacao DESC";
    $condicao_popular = ""; 
    $limite_posts = ""; 
    $sql_contagem_curtidas = "(SELECT COUNT(*) FROM comunidade_curtidas c WHERE c.idlook = l.id)";
}

// BUSCAR POSTS DO FEED
$sql_feed = "SELECT l.*, l.legenda, l.tags, u.nome AS nome_criador, u.foto AS foto_perfil,
            $sql_contagem_curtidas AS total_curtidas,
            (SELECT COUNT(*) FROM comunidade_curtidas c WHERE c.idlook = l.id AND c.idusuario = ?) AS usuario_curtiu
            FROM looks l 
            JOIN usuarios u ON l.idusuario = u.id 
            WHERE l.publicado = 1 
            $condicao_popular
            ORDER BY $ordenacao
            $limite_posts";

$stmt_feed = $con->prepare($sql_feed);
$stmt_feed->bind_param("i", $id_usuario_logado);
$stmt_feed->execute();
$result_feed = $stmt_feed->get_result();
$posts = $result_feed->fetch_all(MYSQLI_ASSOC);

// Buscar looks do usuário não publicados
$meus_looks = [];
if ($logado) {
    $stmt_meus = $con->prepare("SELECT id, nome FROM looks WHERE idusuario = ? AND (publicado = 0 OR publicado IS NULL) ORDER BY id DESC");
    $stmt_meus->bind_param("i", $id_usuario_logado);
    $stmt_meus->execute();
    $meus_looks = $stmt_meus->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunidade</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fafafa; color: #262626; }
        .look-card-feed { border: 1px solid #ddd; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background:#fff; display: flex; flex-direction: column; height: 100%; position: relative; }
        .look-preview-container { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 260px; background-color: #f8f9fa; padding: 10px; position: relative; }
        .look-item-img { max-width: 90%; max-height: 80px; object-fit: contain; margin: 2px 0; }
        .extra-item-img { position: absolute; right: 15px; top: 50%; max-width: 60px; max-height: 60px; border: none; background: transparent; z-index: 5;}
        
        .avatar-feed-container { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 1px solid #ddd; overflow: hidden; background: #f0f0f0; }
        .avatar-feed { width: 100%; height: 100%; object-fit: cover; }
        .avatar-icon-default { font-size: 1.2rem; color: #888; }

        .interaction-btn { background: none; border: none; font-size: 1.3rem; padding: 0 8px 0 0; color: #262626; transition: transform 0.1s ease; text-decoration: none; display: inline-block; }
        .interaction-btn:hover { transform: scale(1.1); color: #262626; }
        .bi-heart-fill { color: #ff3040; }
        .comment-section { max-height: 110px; overflow-y: auto; font-size: 0.82rem; background-color: #f9f9f9; padding: 6px 10px; border-radius: 6px; }
        .btn-delete-comment { background: none; border: none; color: #bbb; padding: 0; font-size: 0.85rem; transition: color 0.2s; cursor: pointer; }
        .btn-delete-comment:hover { color: #dc3545; }

        .btn-options-post { background: none; border: none; color: #8e8e8e; transition: color 0.2s; padding: 4px 8px; font-size: 1.1rem; }
        .btn-options-post:hover { color: #262626; }
        .btn-options-post::after { display: none !important; }
        .dropdown-menu-post { border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: 1px solid #efefef; min-width: 120px; }
        .dropdown-item-post { font-size: 0.85rem; padding: 8px 16px; }
        .dropdown-item-post.text-danger:active { background-color: #dc3545; }

        .nav-tabs-feed { border-bottom: 2px solid #efefef; }
        .nav-tabs-feed .nav-link { border: none; color: #8e8e8e; font-weight: 500; font-size: 0.95rem; padding: 10px 20px; position: relative; transition: color 0.2s; }
        .nav-tabs-feed .nav-link:hover { color: #262626; }
        .nav-tabs-feed .nav-link.active { color: #262626; font-weight: 600; background: none; }
        .nav-tabs-feed .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 2px; background-color: #262626; }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const rootUrl = "<?= $root_url ?>";

            // LISTA DE PALAVRAS BLOQUEADAS (Adicione os palavrões/ofensas que quiser aqui em minúsculo)
            const palavrasBloqueadas = ["idiota", "imbecil", "burro", "retardado", "otario", "otário", "babaca", "trouxa", "inutil", "inútil", "nojento", "ridiculo", "ridículo", "patetico", "patético", "escroto",
             "lixo", "feio", "horroroso", "verme", "fracassado", "vagabundo", "vagabunda", "covarde", "canalha", "cretino", "palhaco", "palhaço", "animal", "doente", "maluco", "louco", "babão", "tapado", "anta", "otaria", 
             "otária", "corno", "corna", "arrombado", "arrombada", "fdp", "filho da puta", "filha da puta", "puta", "puto", "merda", "bosta", "caralho", "cacete", "porra", "foda", "foder", "fudido", "fudida", "vai tomar no cu", 
             "tomar no cu", "cuzao", "cuzão", "pau no cu", "desgracado", "desgraçado", "desgracada", "desgraçada", "infeliz", "miseravel", "miserável", "safado", "safada", "pilantra", "sem nocao", "sem noção", "troglodita", "asno", "jumento",
             "energumeno", "energúmeno", "abestado", "mongol", "debil", "débil", "imundo", "ridicula", "ridícula"];

            // Instancia o modal de ofensa para o JS usar
            const modalOfensa = new bootstrap.Modal(document.getElementById('modalOfensaComentario'));

            // CORREÇÃO DOS LINKS DO NAV VIA URL ABSOLUTA
            document.querySelectorAll("header a, .navmenu a").forEach(link => {
                let href = link.getAttribute("href");
                if (href) {
                    if (href.includes("logout.php")) { 
                        link.setAttribute("href", rootUrl + "logout.php");
                        return;
                    }
                    if (!href.startsWith("http") && !href.startsWith("#") && !href.startsWith("../")) {
                        if (href === "comunidade.php" || href === "comunidade/comunidade.php") {
                            link.setAttribute("href", "comunidade.php");
                        } else if (href.startsWith("admin/")) {
                            link.setAttribute("href", rootUrl + href);
                        } else {
                            link.setAttribute("href", rootUrl + href);
                        }
                    }
                }
            });

            document.querySelectorAll("header img, .navmenu img").forEach(img => {
                let src = img.getAttribute("src");
                if (
    src &&
    !src.startsWith("http") &&
    !src.startsWith("../") &&
    !src.startsWith("/")
) {
                    img.setAttribute("src", rootUrl + src);
                }
            });

            // LÓGICA DE CURTIDA EM SEGUNDO PLANO (FETCH)
            document.querySelectorAll('.like-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    <?php if (!$logado): ?>
                        window.location.href = rootUrl + "login.php";
                        return;
                    <?php endif; ?>

                    const lookId = this.getAttribute('data-id');
                    const icon = this.querySelector('i');
                    const countSpan = document.getElementById('like-count-' + lookId);

                    fetch('interagir.php?acao=curtir&look=' + lookId)
                        .then(() => {
                            if (icon.classList.contains('bi-heart')) {
                                icon.classList.remove('bi-heart');
                                icon.classList.add('bi-heart-fill');
                                countSpan.textContent = parseInt(countSpan.textContent) + 1 + ' curtidas';
                            } else {
                                icon.classList.remove('bi-heart-fill');
                                icon.classList.add('bi-heart');
                                countSpan.textContent = parseInt(countSpan.textContent) - 1 + ' curtidas';
                            }
                        })
                        .catch(err => console.log('Erro ao curtir:', err));
                });
            });

            // POSTAR COMENTÁRIO COM VALIDAÇÃO ANTI-OFENSA
            document.querySelectorAll('.comment-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const input = this.querySelector('input[name="comentario"]');
                    const textoComentario = input.value.trim().toLowerCase();

                    if (!textoComentario) return;

                    // VALIDAR SE CONTÉM ALGUM XINGAMENTO
                    const contemOfensa = palavrasBloqueadas.some(palavra => textoComentario.includes(palavra));

                    if (contemOfensa) {
                        // Limpa o input ofensivo por educação/limpeza
                        input.value = '';
                        // Abre o modal de alerta e barra a execução
                        modalOfensa.show();
                        return;
                    }

                    const formData = new FormData(this);
                    formData.append('comentar', '1');
                    const lookId = formData.get('idlook');
                    const sectionComentarios = document.getElementById('comment-section-' + lookId);

                    fetch('interagir.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(() => {
                        input.value = '';
                        return fetch(window.location.href);
                    })
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const novaSection = doc.getElementById('comment-section-' + lookId);
                        
                        if (novaSection) {
                            sectionComentarios.innerHTML = novaSection.innerHTML;
                            sectionComentarios.scrollTop = sectionComentarios.scrollHeight;
                            configurarGatilhosExclusao();
                        }
                    })
                    .catch(err => console.log('Erro ao postar comentário:', err));
                });
            });

            // GERENCIADOR DE MODAL (COMENTÁRIO E POST)
            let urlExclusaoPendente = '';
            let tipoExclusao = ''; 
            
            const modalExcluir = new bootstrap.Modal(document.getElementById('modalConfirmarExcluir'));
            const btnConfirmarExcluir = document.getElementById('btnConfirmarExclusaoUrl');
            const modalTitulo = document.getElementById('modalExcluirTitulo');
            const modalTexto = document.getElementById('modalExcluirTexto');

            function configurarGatilhosExclusao() {
                document.querySelectorAll('.btn-delete-comment').forEach(btn => {
                    btn.replaceWith(btn.cloneNode(true));
                });

                document.querySelectorAll('.btn-delete-comment').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        urlExclusaoPendente = this.getAttribute('href');
                        tipoExclusao = 'comentario';
                        
                        modalTitulo.textContent = "Excluir comentário?";
                        modalTexto.textContent = "Tem certeza que deseja remover permanentemente este comentário?";
                        modalExcluir.show();
                    });
                });
            }

            document.querySelectorAll('.btn-trigger-delete-post').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    urlExclusaoPendente = this.getAttribute('data-href');
                    tipoExclusao = 'post';

                    modalTitulo.textContent = "Excluir publicação?";
                    modalTexto.textContent = "Tem certeza que deseja remover este post do seu feed? Essa ação não pode ser desfeita.";
                    modalExcluir.show();
                });
            });

            btnConfirmarExcluir.addEventListener('click', function() {
                if(urlExclusaoPendente) {
                    modalExcluir.hide();
                    
                    fetch(urlExclusaoPendente)
                        .then(() => {
                            if (tipoExclusao === 'comentario') {
                                const gatilho = document.querySelector(`.btn-delete-comment[href="${urlExclusaoPendente}"]`);
                                if(gatilho) {
                                    const linhaComentario = gatilho.closest('.d-flex');
                                    const containerPai = linhaComentario.parentNode;
                                    linhaComentario.remove();

                                    if (containerPai.children.length === 0) {
                                        containerPai.innerHTML = '<span class="text-muted d-block no-comments-msg" style="font-size: 0.78rem;">Nenhum comentário.</span>';
                                    }
                                }
                            } else if (tipoExclusao === 'post') {
                                const gatilho = document.querySelector(`.btn-trigger-delete-post[data-href="${urlExclusaoPendente}"]`);
                                if(gatilho) {
                                    const colunaCard = gatilho.closest('.col');
                                    colunaCard.style.transition = 'all 0.3s ease';
                                    colunaCard.style.opacity = '0';
                                    colunaCard.style.transform = 'scale(0.8)';
                                    setTimeout(() => {
                                        colunaCard.remove();
                                        if(document.querySelectorAll('.look-card-feed').length === 0) {
                                            window.location.reload();
                                        }
                                    }, 300);
                                }
                            }
                        })
                        .catch(err => console.log('Erro ao processar exclusão:', err));
                }
            });

            configurarGatilhosExclusao();
        });
    </script>
</head>
<body class="index-page">

    <?php include '../nav.php'; ?>

    <main class="main" style="margin-top: 90px;">
        <div class="container my-5">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 fw-bold m-0">Comunidade</h1>
                <?php if ($logado): ?>
                    <a href="publicar_look.php" class="btn btn-dark rounded-pill px-4">
                        <i class="bi bi-plus-lg"></i> Compartilhar Look
                    </a>
                <?php endif; ?>
            </div>

            <ul class="nav nav-tabs-feed mb-4">
                <li class="nav-item">
                    <a class="nav-link <?= $filtro === 'recentes' ? 'active' : '' ?>" href="comunidade.php?filtro=recentes">
                        <i class="bi bi-clock me-1"></i> Publicações mais recentes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $filtro === 'populares' ? 'active' : '' ?>" href="comunidade.php?filtro=populares">
                        <i class="bi bi-fire me-1" style="color: #ff8c00;"></i> Top 4 Looks da Semana
                    </a>
                </li>
            </ul>

            <?php if (empty($posts)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-heart-break h1 text-muted"></i>
                    <p class="text-muted mt-2">Nenhum look com curtidas nesta semana.</p>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                    <?php foreach ($posts as $post): ?>
                        <div class="col" id="look-<?= $post['id'] ?>">
                            <div class="card look-card-feed">
                                
                                <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                                    <div class="d-flex align-items-center overflow-hidden">
                                        <div class="avatar-feed-container me-2 flex-shrink-0">
                                            <?php if (!empty($post['foto_perfil']) && file_exists('../' . $post['foto_perfil'])): ?>
                                                <img src="../<?= htmlspecialchars($post['foto_perfil']) ?>" class="avatar-feed" alt="Perfil">
                                            <?php else: ?>
                                                <i class="bi bi-person-fill avatar-icon-default"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="overflow-hidden">
                                            <span class="fw-bold d-block text-truncate" style="font-size: 0.85rem;">@<?= htmlspecialchars($post['nome_criador']) ?></span>
                                            <span class="text-muted d-block" style="font-size: 0.7rem;"><?= !empty($post['data_publicacao']) ? date('d/m/Y H:i', strtotime($post['data_publicacao'])) : 'Recentemente'; ?></span>
                                        </div>
                                    </div>
                                    
                                    <?php if ($logado && (int)$post['idusuario'] === $id_usuario_logado): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-options-post dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-post">
                                                <li>
                                                    <a class="dropdown-item dropdown-item-post" href="editar_publicacao.php?id=<?= $post['id'] ?>">
                                                        <i class="bi bi-pencil me-2"></i> Editar
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item dropdown-item-post text-danger btn-trigger-delete-post" data-href="excluir.php?acao=deletar_post&id=<?= $post['id'] ?>">
                                                        <i class="bi bi-trash3 me-2"></i> Excluir
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="look-preview-container">
                                    <?php
                                    $ids_verticais = array_filter([$post['idroupa1'], $post['idroupa2'], $post['idroupa5'], $post['idroupa3']]);
                                    if (!empty($ids_verticais)) {
                                        $ids_str = implode(',', $ids_verticais);
                                        $res_p = $con->query("SELECT foto FROM roupas WHERE id IN ($ids_str) ORDER BY FIELD(id, $ids_str)");
                                        while ($p = $res_p->fetch_assoc()) { 
                                            echo '<img src="../'.$p['foto'].'" class="look-item-img">'; 
                                        }
                                    }
                                    
                                    if (!empty($post['idroupa4'])) {
                                        $res_extra = $con->query("SELECT foto FROM roupas WHERE id = " . intval($post['idroupa4']));
                                        if ($extra = $res_extra->fetch_assoc()) {
                                            echo '<img src="../'.$extra['foto'].'" class="extra-item-img">';
                                        }
                                    }
                                    ?>
                                </div>

                                <div class="card-body d-flex flex-column justify-content-between p-3">
                                    <div>
                                        <div class="d-flex align-items-center mb-2">
                                            <button class="interaction-btn like-btn" data-id="<?= $post['id'] ?>">
                                                <i class="bi <?= $post['usuario_curtiu'] ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                                            </button>
                                            <label for="comment-input-<?= $post['id'] ?>" class="interaction-btn" style="cursor:pointer; margin: 0;">
                                                <i class="bi bi-chat"></i>
                                            </label>
                                        </div>
                                        
                                        <span class="fw-bold d-block small mb-1" id="like-count-<?= $post['id'] ?>"><?= $post['total_curtidas'] ?> curtidas</span>
                                        
                                        <span class="fw-bold d-block small text-secondary mb-1">
                                            <?php 
                                            if (!empty($post['tags'])) {
                                                $array_tags = explode(',', $post['tags']);
                                                $tags_formatadas = array_map(function($tag) {
                                                    return '#' . trim($tag);
                                                }, $array_tags);
                                                echo htmlspecialchars(implode(' ', $tags_formatadas));
                                            } else {
                                                echo 'Sem tags';
                                            }
                                            ?>
                                        </span>

                                        <?php if(!empty($post['legenda'])): ?>
                                            <p class="small mb-1" style="line-height: 1.3;">
                                                <span class="fw-bold me-1"><?= htmlspecialchars($post['nome_criador']) ?></span><?= htmlspecialchars($post['legenda']) ?>
                                            </p>
                                        <?php endif; ?>

                                        <div class="comment-section mb-2" id="comment-section-<?= $post['id'] ?>">
                                            <?php
                                            $stmt_c = $con->prepare("SELECT c.*, u.nome AS nome_comentador FROM comunidade_comentarios c JOIN usuarios u ON c.idusuario = u.id WHERE c.idlook = ? ORDER BY c.data_comentario ASC");
                                            $stmt_c->bind_param("i", $post['id']);
                                            $stmt_c->execute();
                                            $comentarios = $stmt_c->get_result()->fetch_all(MYSQLI_ASSOC);
                                            
                                            if (empty($comentarios)):
                                            ?>
                                                <span class="text-muted d-block no-comments-msg" style="font-size: 0.78rem;">Nenhum comentário.</span>
                                            <?php else: ?>
                                                <?php foreach ($comentarios as $com): ?>
                                                    <div class="d-flex justify-content-between align-items-start mb-1 gap-1" style="line-height: 1.2;">
                                                        <div class="text-truncate">
                                                            <span class="fw-bold me-1"><?= htmlspecialchars($com['nome_comentador']) ?></span>
                                                            <span style="white-space: pre-wrap;"><?= htmlspecialchars($com['comentario']) ?></span>
                                                        </div>
                                                        <?php if ($logado && ((int)$com['idusuario'] === $id_usuario_logado || (int)$post['idusuario'] === $id_usuario_logado)): ?>
                                                            <a href="excluir.php?acao=deletar_comentario&id=<?= $com['id'] ?>" class="btn-delete-comment">
                                                                <i class="bi bi-trash3"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if ($logado): ?>
                                        <div class="border-top pt-2 mt-2">
                                            <form action="interagir.php" method="POST" class="d-flex align-items-center comment-form">
                                                <input type="hidden" name="idlook" value="<?= $post['id'] ?>">
                                                <input type="text" id="comment-input-<?= $post['id'] ?>" name="comentario" class="form-control form-control-sm border-0 ps-1 bg-transparent" placeholder="Comente..." required autocomplete="off" style="font-size: 0.8rem;">
                                                <button type="submit" class="btn btn-sm fw-bold text-primary border-0 bg-transparent p-0 ms-1" style="font-size: 0.8rem;">Postar</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO (Já existente) -->
    <div class="modal fade" id="modalConfirmarExcluir" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow" style="border-radius: 14px;">
                <div class="modal-body text-center pt-4 pb-3">
                    <i class="bi bi-exclamation-circle text-danger display-5 d-block mb-3"></i>
                    <h6 class="fw-bold mb-1" id="modalExcluirTitulo">Excluir?</h6>
                    <p class="text-muted small px-2" id="modalExcluirTexto">Tem certeza que deseja remover?</p>
                </div>
                <div class="d-flex border-top">
                    <button type="button" class="btn btn-link w-100 text-muted text-decoration-none border-end py-3 rounded-0 m-0 small" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnConfirmarExclusaoUrl" class="btn btn-link w-100 text-danger fw-bold text-decoration-none py-3 rounded-0 m-0 small">Excluir</button>
                </div>
            </div>
        </div>
    </div>

    <!-- NOVO MODAL: ALERTA DE COMENTÁRIO OFENSIVO (FILTRO ANTI-TOXICIDADE) -->
    <div class="modal fade" id="modalOfensaComentario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow" style="border-radius: 14px;">
                <div class="modal-body text-center pt-4 pb-3">
                    <i class="bi bi-shield-x text-danger display-4 d-block mb-3"></i>
                    <h6 class="fw-bold text-dark mb-2">Comentário Bloqueado</h6>
                    <p class="text-muted small px-2 mb-0">Seu comentário contém ofensas ou termos não permitidos. Vamos manter a comunidade respeitosa! 😊</p>
                </div>
                <div class="border-top">
                    <button type="button" class="btn btn-link w-100 text-primary fw-bold text-decoration-none py-3 rounded-0 m-0 small" data-bs-dismiss="modal">Entendi</button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>