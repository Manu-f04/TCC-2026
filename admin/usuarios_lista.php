<?php 
require_once("verificar_admin.php"); 
$pagina_titulo = "Gerenciar Usuários";
$pagina_ativa = "usuarios";
require_once("header_admin.php"); 

// Lógica para alterar nível de acesso via URL (Rápido)
if (isset($_GET['alterar_nivel']) && isset($_GET['id'])) {
    $id_alt = intval($_GET['id']);
    $novo_nivel = ($_GET['alterar_nivel'] == 'admin') ? 'admin' : 'usuario';
    
    // Impede que você tire seu próprio acesso de admin
    if ($id_alt != $_SESSION['idusuario']) {
        $stmt = $con->prepare("UPDATE usuarios SET nivel_acesso = ? WHERE id = ?");
        $stmt->bind_param("si", $novo_nivel, $id_alt);
        $stmt->execute();
        echo "<script>window.location.href='usuarios_lista.php';</script>";
    }
}

// Busca todos os dados do banco
$sql = "SELECT id, nome, email, nivel_acesso FROM usuarios ORDER BY nivel_acesso ASC, nome ASC";
$res = $con->query($sql);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold h3">Controle de Usuários</h1>
        <p class="text-muted">Gerencie permissões e dados cadastrais.</p>
    </div>
    <div class="text-end">
        <span class="badge bg-dark px-3 py-2 rounded-pill">Total: <?= $res->num_rows ?></span>
    </div>
</div>

<div class="f-table-container shadow-sm border rounded-4 bg-white p-3">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                
                
                <th class="text-center">Nível</th>
                <th class="text-center">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $res->fetch_assoc()): ?>
            <tr>
                <td class="fw-bold"><?= htmlspecialchars($row['nome']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                
                
                <td class="text-center">
                    <?php if($row['nivel_acesso'] == 'admin'): ?>
                        <span class="badge bg-dark">Admin</span>
                    <?php else: ?>
                        <span class="badge bg-light text-dark border">Usuário</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                        <a href="usuarios_editar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-dark" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        
                        <?php if($row['id'] != $_SESSION['idusuario']): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    data-bs-toggle="modal" data-bs-target="#modalExcluir" 
                                    data-id="<?= $row['id'] ?>" data-nome="<?= htmlspecialchars($row['nome']) ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalExcluir" tabindex="-1" aria-labelledby="modalExcluirLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="modalExcluirLabel"><i class="bi bi-exclamation-triangle"></i> Confirmar Exclusão</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4 text-center">
        <p class="mb-0">Você está prestes a excluir permanentemente o usuário:</p>
        <h5 class="fw-bold mt-2" id="nomeUsuarioExcluir"></h5>
        <p class="text-muted small">Esta ação não poderá ser desfeita.</p>
      </div>
      <div class="modal-footer bg-light justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <a href="#" id="btnConfirmarExcluir" class="btn btn-danger px-4">Excluir Agora</a>
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
        
        const nomeDisplay = modalExcluir.querySelector('#nomeUsuarioExcluir');
        const btnExcluir = modalExcluir.querySelector('#btnConfirmarExcluir');
        
        nomeDisplay.textContent = nome;
        btnExcluir.href = 'usuarios_excluir.php?id=' + id;
    });
}
</script>

<?php require_once("footer_admin.php"); ?>