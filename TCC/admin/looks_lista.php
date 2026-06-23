<?php 
$pagina_titulo = "Gerenciar Looks";
$pagina_ativa = "looks";

require_once("verificar_admin.php"); 
require_once("../conexao.php"); 
require_once("header_admin.php"); 

$res = null;
$erro = null;

try {
    if (!isset($con)) {
        throw new Exception("Conexão com o banco de dados não encontrada.");
    }
    
    // Busca os looks cadastrados no sistema e quem os criou
    $sql = "SELECT l.*, u.nome as criador 
            FROM looks l 
            LEFT JOIN usuarios u ON l.idusuario = u.id 
            ORDER BY l.id DESC";
            
    $res = $con->query($sql);
} catch (Exception $e) {
    $erro = "Erro ao carregar looks: " . $e->getMessage();
}
?>

<style>
    .look-preview-container { 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
        justify-content: center; 
        height: 160px; 
        width: 130px;
        background-color: #f8f9fa; 
        padding: 5px; 
        position: relative; 
        border-radius: 8px;
        border: 1px solid #dee2e6;
        margin: 0 auto;
    }
    .look-item-img { 
        max-width: 90%; 
        max-height: 45px; 
        object-fit: contain; 
        margin: 1px 0; 
    }
    .extra-item-img { 
        position: absolute; 
        right: 8px; 
        top: 50%; 
        max-width: 40px; 
        max-height: 40px; 
        border: none; 
        background: transparent; 
        z-index: 5;
        object-fit: contain;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold h3">Looks da Comunidade</h1>
        <p class="text-muted">Visualize e gerencie as combinações criadas.</p>
    </div>
    <div class="text-end">
        <span class="badge bg-dark px-3 py-2 rounded-pill">Total: <?= ($res && isset($res->num_rows)) ? $res->num_rows : 0 ?></span>
    </div>
</div>

<?php if ($erro): ?>
    <div class="alert alert-danger shadow-sm rounded-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($erro) ?>
    </div>
<?php endif; ?>

<div class="f-table-container shadow-sm border rounded-4 bg-white p-3">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Nome do Look</th>
                <th class="text-center" style="width: 160px;">Look Montado</th>
                <th>Criado por</th>
                <th class="text-center">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if($res && $res->num_rows > 0): ?>
                <?php while($look = $res->fetch_assoc()): ?>
                <tr>
                    <td class="fw-bold text-dark"><?= htmlspecialchars($look['nome'] ?? 'Look sem nome') ?></td>
                    
                    <td class="text-center">
                        <div class="look-preview-container">
                            <?php
                            // 1. MONTAGEM VISUAL DO LOOK (ORDEM VERTICAL: Roupa 1, 2, 5 e 3)
                            $ids_verticais = array_filter([$look['idroupa1'], $look['idroupa2'], $look['idroupa5'], $look['idroupa3']]);
                            if (!empty($ids_verticais)) {
                                $ids_str = implode(',', $ids_verticais);
                                $res_p = $con->query("SELECT foto FROM roupas WHERE id IN ($ids_str) ORDER BY FIELD(id, $ids_str)");
                                while($p = $res_p->fetch_assoc()){ 
                                    echo '<img src="../'.$p['foto'].'" class="look-item-img" onerror="this.src=\'../assets/img/roupas/default.jpg\'">'; 
                                }
                            }

                            // 2. RENDERIZAÇÃO DA PEÇA EXTRA (SOBREPOSTA - Roupa 4)
                            if (!empty($look['idroupa4'])) {
                                $res_extra = $con->query("SELECT foto FROM roupas WHERE id = " . intval($look['idroupa4']));
                                if($extra = $res_extra->fetch_assoc()) {
                                    echo '<img src="../'.$extra['foto'].'" class="extra-item-img" onerror="this.src=\'../assets/img/roupas/default.jpg\'">';
                                }
                            }
                            
                            if (empty($ids_verticais) && empty($look['idroupa4'])) {
                                echo '<span class="text-muted small">Sem peças</span>';
                            }
                            ?>
                        </div>
                    </td>
                    
                    <td>
                        <div class="small fw-bold"><?= htmlspecialchars($look['criador'] ?? 'Visitante') ?></div>
                    </td>
                    
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="../looks.php?id=<?= $look['id'] ?>" class="btn btn-sm btn-dark" title="Visualizar Look">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalExcluirLook" data-id="<?= $look['id'] ?>" data-nome="<?= htmlspecialchars($look['nome'] ?? 'Look sem nome') ?>" title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">Nenhum look encontrado no banco de dados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalExcluirLook" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">Confirmar Exclusão</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4 text-center">
        <p>Deseja excluir permanentemente o look:</p>
        <h5 class="fw-bold" id="nomeLookExcluir"></h5>
      </div>
      <div class="modal-footer bg-light justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <a href="#" id="btnConfirmarExcluir" class="btn btn-dark">Excluir Agora</a>
      </div>
    </div>
  </div>
</div>

<script>
const modalExcluirLook = document.getElementById('modalExcluirLook');
if (modalExcluirLook) {
    modalExcluirLook.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const nome = button.getAttribute('data-nome');
        modalExcluirLook.querySelector('#nomeLookExcluir').textContent = nome;
        modalExcluirLook.querySelector('#btnConfirmarExcluir').href = 'looks_excluir.php?id=' + id;
    });
}
</script>

<?php require_once("footer_admin.php"); ?>