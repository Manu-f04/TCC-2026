<?php
require_once("verificar_admin.php");
require_once("../conexao.php");

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    // 1. Buscar o nome da imagem para deletar do servidor também
    $stmt = $con->prepare("SELECT foto FROM roupas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();

    if ($resultado) {
        $caminho_foto = "../" . $resultado['foto'];

        // 2. Excluir o registro do banco de dados
        $stmt_del = $con->prepare("DELETE FROM roupas WHERE id = ?");
        $stmt_del->bind_param("i", $id);

        if ($stmt_del->execute()) {
            // 3. Deletar o arquivo físico de imagem se ele existir e não for uma imagem padrão
            if (!empty($resultado['foto']) && file_exists($caminho_foto)) {
                // Evita deletar uma imagem padrão caso você use "default.jpg"
                if (strpos($resultado['foto'], 'default.jpg') === false) {
                    unlink($caminho_foto);
                }
            }
            $status = "sucesso";
        } else {
            $status = "erro";
        }
    } else {
        $status = "nao_encontrado";
    }
} else {
    header("Location: roupas_lista.php");
    exit();
}

// --- AQUI ESTÁ A CORREÇÃO ---
$pagina_titulo = "Excluindo Peça";
$pagina_ativa = "roupas"; // Definimos aqui para o header não dar erro
// ----------------------------

require_once("header_admin.php");
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($status == "sucesso"): ?>
            Swal.fire({
                title: 'Excluído!',
                text: 'A peça foi removida com sucesso.',
                icon: 'success',
                confirmButtonColor: '#212529'
            }).then(() => {
                window.location.href = 'roupas_lista.php';
            });
        <?php else: ?>
            Swal.fire({
                title: 'Erro!',
                text: 'Não foi possível excluir esta peça.',
                icon: 'error',
                confirmButtonColor: '#212529'
            }).then(() => {
                window.location.href = 'roupas_lista.php';
            });
        <?php endif; ?>
    });
</script>

<?php require_once("footer_admin.php"); ?>