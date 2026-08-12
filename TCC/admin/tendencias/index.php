<?php
$pagina_titulo = "Gerenciar Tendências";
$pagina_ativa = "tendencias";

// Garante o carregamento correto das dependências
chdir(__DIR__ . '/..');
require_once("verificar_admin.php");
require_once("../conexao.php");

$erro = null;
$res = null;

try {
    if (!isset($con)) {
        throw new Exception("Conexão com o banco de dados não encontrada.");
    }
    
    $sql = "SELECT * FROM tendencias ORDER BY id DESC";
    $res = $con->query($sql);
} catch (Exception $e) {
    $erro = "Erro ao carregar tendências: " . $e->getMessage();
}

require_once("header_admin.php");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold h3">Tendências Cadastradas</h1>
        <p class="text-muted">Gerencie os cards e destaques exibidos na página inicial.</p>
    </div>
    <div class="text-end">
        <a href="<?= $base_admin ?>tendencias/cadastrar.php" class="btn btn-dark rounded-3 shadow-sm me-2">
            <i class="bi bi-plus-lg me-1"></i> Nova Tendência
        </a>
        <span class="badge bg-dark px-3 py-2 rounded-pill">Total: <?= ($res && isset($res->num_rows)) ? $res->num_rows : 0 ?></span>
    </div>
</div>

<?php if ($erro): ?>
    <div class="alert alert-danger shadow-sm rounded-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($erro) ?>
    </div>
<?php endif; ?>

<div class="f-table-container shadow-sm border rounded-4 bg-white p-3">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th style="width: 80px;">Imagem</th>
                <th>Título</th>
                <th>Legenda / Descrição</th>
                <th>Posição/Card</th>
                <th style="width: 120px;">Data</th>
                <th class="text-center" style="width: 130px;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($res && $res->num_rows > 0): ?>
                <?php while ($tendencia = $res->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php 
                        $img = $tendencia['imagem'];
                        if (strpos($img, 'http') === 0) {
                            $src = $img;
                        } else {
                            $src = '../../uploads/' . $img;
                        }
                        ?>
                        <img src="<?= htmlspecialchars($src) ?>" class="rounded-3 border" style="width: 55px; height: 55px; object-fit: cover;" onerror="this.src='../assets/img/default.jpg'">
                    </td>
                    <td class="fw-bold text-dark"><?= htmlspecialchars($tendencia['titulo']) ?></td>
                    
                    <!-- Coluna de Legenda / Descrição -->
                    <td class="text-muted small" style="max-width: 250px;">
                        <?= !empty($tendencia['descricao']) ? htmlspecialchars(mb_strimwidth($tendencia['descricao'], 0, 70, '...')) : '<em class="text-secondary">Sem legenda</em>' ?>
                    </td>

                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($tendencia['categoria']) ?></span></td>
                    
                    <td>
                        <span class="badge bg-light text-dark border font-monospace">
                            <?php 
                            if (!empty($tendencia['data_publicacao'])) {
                                echo date('d/m/Y', strtotime($tendencia['data_publicacao']));
                            } elseif (!empty($tendencia['created_at'])) {
                                echo date('d/m/Y', strtotime($tendencia['created_at']));
                            } elseif (!empty($tendencia['data'])) {
                                echo date('d/m/Y', strtotime($tendencia['data']));
                            } else {
                                echo date('d/m/Y');
                            }
                            ?>
                        </span>
                    </td>

                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="<?= $base_admin ?>tendencias/editar.php?id=<?= $tendencia['id'] ?>" class="btn btn-sm btn-dark" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalExcluirTendencia" data-id="<?= $tendencia['id'] ?>" data-nome="<?= htmlspecialchars($tendencia['titulo']) ?>" title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">Nenhuma tendência cadastrada até o momento.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal de Confirmação de Exclusão Padrão -->
<div class="modal fade" id="modalExcluirTendencia" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">Confirmar Exclusão</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4 text-center">
        <p>Deseja excluir permanentemente a tendência:</p>
        <h5 class="fw-bold" id="nomeTendenciaExcluir"></h5>
      </div>
      <div class="modal-footer bg-light justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <a href="#" id="btnConfirmarExcluirTendencia" class="btn btn-dark">Excluir Agora</a>
      </div>
    </div>
  </div>
</div>

<script>
const modalExcluirTendencia = document.getElementById('modalExcluirTendencia');
if (modalExcluirTendencia) {
    modalExcluirTendencia.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const nome = button.getAttribute('data-nome');
        modalExcluirTendencia.querySelector('#nomeTendenciaExcluir').textContent = nome;
        modalExcluirTendencia.querySelector('#btnConfirmarExcluirTendencia').href = '<?= $base_admin ?>tendencias/excluir.php?id=' + id;
    });
}
</script>

<?php require_once("footer_admin.php"); ?>