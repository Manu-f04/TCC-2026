<?php
chdir(__DIR__ . '/..');
require_once("verificar_admin.php");
require_once("../conexao.php");

$id = (int)($_GET['id'] ?? 0);
$status = "erro";

if ($id > 0) {
    $stmt = $con->prepare("DELETE FROM tendencias WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $status = "sucesso";
    }
    $stmt->close();
}

$pagina_titulo = "Excluindo Tendência";
$pagina_ativa = "tendencias";

require_once("header_admin.php");
?>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($status == "sucesso"): ?>
        Swal.fire({
            title: 'Excluído!',
            text: 'A tendência foi removida com sucesso.',
            icon: 'success',
            confirmButtonColor: '#212529'
        }).then(() => {
            window.location.href = 'index.php';
        });
    <?php else: ?>
        Swal.fire({
            title: 'Erro!',
            text: 'Não foi possível excluir esta tendência.',
            icon: 'error',
            confirmButtonColor: '#212529'
        }).then(() => {
            window.location.href = 'index.php';
        });
    <?php endif; ?>
});
</script>

<?php require_once("footer_admin.php"); ?>