<?php 
require_once("verificar_admin.php"); 
require_once("../conexao.php"); // Certifique-se que o caminho da conexão está correto

$pagina_titulo = "Lista de Roupas";
$pagina_ativa = "roupas";
require_once("header_admin.php"); 

// 1. Buscamos todas as estações para criar um "dicionário" na memória
$resEstacoes = $con->query("SELECT id, nome FROM estacoes");
$estacoesMap = [];
while($est = $resEstacoes->fetch_assoc()){
    $estacoesMap[$est['id']] = $est['nome'];
}

// 2. Consulta principal com JOIN para Categorias e Usuários
$sql = "SELECT r.*, c.nome AS nome_categoria, u.nome AS nome_proprietario 
        FROM roupas r
        LEFT JOIN categorias c ON r.idCategoria = c.id
        LEFT JOIN usuarios u ON r.idusuario = u.id
        ORDER BY r.id DESC";
$res = $con->query($sql);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold h3">Gerenciar roupas</h1>
        <p class="text-muted">Gerencie o inventário de peças e visualizações.</p>
    </div>
    <div class="text-end">
        <span class="badge bg-dark px-3 py-2 rounded-pill">Total: <?= $res->num_rows ?></span>
    </div>
</div>

<div class="f-table-container shadow-sm border rounded-4 bg-white p-3">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Imagem</th>
                <th>Categoria</th>
                <th>Estilo e Estações</th>
                <th>Proprietário</th>
                <th class="text-center">Status de Uso</th>
                <th class="text-center">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $res->fetch_assoc()): 
                // Lógica para converter IDs de estações (ex: "1,3") em nomes (ex: "Verão, Primavera")
                $nomesEstacoes = [];
                if (!empty($row['estacoes'])) {
                    $ids = explode(',', $row['estacoes']);
                    foreach ($ids as $id) {
                        if (isset($estacoesMap[$id])) {
                            $nomesEstacoes[] = $estacoesMap[$id];
                        }
                    }
                }
                $txtEstacoes = !empty($nomesEstacoes) ? implode(', ', $nomesEstacoes) : "Não definida";

                // Lógica de vínculo com looks (idroupa1 até idroupa5)
                $idR = $row['id'];
                $checkLooks = $con->query("SELECT COUNT(*) as total FROM looks WHERE idroupa1=$idR OR idroupa2=$idR OR idroupa3=$idR OR idroupa5=$idR");
                $totalLooks = $checkLooks->fetch_assoc()['total'];
            ?>
            <tr>
                <td>
                    <img src="../<?= htmlspecialchars($row['foto']) ?>" 
                         class="rounded-3 border" 
                         style="width: 60px; height: 60px; object-fit: cover;"
                         onerror="this.src='../assets/img/roupas/default.jpg'">
                </td>
                <td>
                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['nome_categoria']) ?></div>
                    <div class="d-flex gap-1 mt-1">
                        <div style="width:15px; height:15px; background:<?= $row['cor1'] ?>; border:1px solid #ccc; border-radius:50%"></div>
                        <div style="width:15px; height:15px; background:<?= $row['cor2'] ?>; border:1px solid #ccc; border-radius:50%"></div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-dark">#<?= htmlspecialchars($row['tags'] ?: 'sem-tag') ?></span><br>
                    <small class="text-muted"><i class="bi bi-cloud-sun"></i> <?= $txtEstacoes ?></small>
                </td>
                <td>
                    <div class="small fw-bold"><?= htmlspecialchars($row['nome_proprietario']) ?></div>
                </td>
                <td class="text-center">
                    <?php if($totalLooks > 0): ?>
                        <span class="badge bg-dark rounded-pill">Vinculada a <?= $totalLooks ?> look(s)</span>
                    <?php else: ?>
                        <span class="badge bg-secondary rounded-pill text-white">Vinculada a 0 looks</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                        <a href="roupas_editar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-dark">
                             <i class="bi bi-eye"></i>

                        </a>
                        <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalExcluir" data-id="<?= $row['id'] ?>" data-nome="<?= htmlspecialchars($row['nome_categoria']) ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">Confirmar Exclusão</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4 text-center">
        <p>Deseja excluir permanentemente a peça:</p>
        <h5 class="fw-bold" id="nomeRoupaExcluir"></h5>
      </div>
      <div class="modal-footer bg-light justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <a href="#" id="btnConfirmarExcluir" class="btn btn-dark">Excluir Agora</a>
      </div>
    </div>
  </div>
</div>

<script>
const modalExcluir = document.getElementById('modalExcluir');
if (modalExcluir) {
    modalExcluir.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const nome = button.getAttribute('data-nome');
        modalExcluir.querySelector('#nomeRoupaExcluir').textContent = nome;
        modalExcluir.querySelector('#btnConfirmarExcluir').href = 'roupas_excluir.php?id=' + id;
    });
}
</script>

<?php require_once("footer_admin.php"); ?>