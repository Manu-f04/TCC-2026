<?php
require_once("verificar_admin.php");
require_once("../conexao.php");

$status = "erro";

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    try {
        if (!isset($con)) {
            throw new Exception("Conexão com o banco de dados não encontrada.");
        }

        // Excluir o registro do look do banco de dados
        $stmt_del = $con->prepare("DELETE FROM looks WHERE id = ?");
        $stmt_del->bind_param("i", $id);

        if ($stmt_del->execute()) {
            $status = "sucesso";
        } else {
            $status = "erro";
        }
    } catch (Exception $e) {
        $status = "erro";
    }
} else {
    header("Location: looks_lista.php");
    exit();
}

// Configurações para o header_admin não quebrar ou dar erro de variáveis vazias
$pagina_titulo = "Excluindo Look";
$pagina_ativa = "looks"; 

require_once("header_admin.php");
?>

<!-- Importação da biblioteca de animações SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($status == "sucesso"): ?>
            Swal.fire({
                title: 'Excluído!',
                text: 'O look foi removido com sucesso.',
                icon: 'success',
                confirmButtonColor: '#212529' // Cor preta padrão do seu admin
            }).then(() => {
                window.location.href = 'looks_lista.php';
            });
        <?php else: ?>
            Swal.fire({
                title: 'Erro!',
                text: 'Não foi possível excluir este look.',
                icon: 'error',
                confirmButtonColor: '#212529'
            }).then(() => {
                window.location.href = 'looks_lista.php';
            });
        <?php endif; ?>
    });
</script>

<?php require_once("footer_admin.php"); ?>