<?php
require_once("verificar_admin.php");
require_once("../conexao.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Impede que você exclua a si mesmo acidentalmente
    if ($id == $_SESSION['idusuario']) {
        header("Location: usuarios_lista.php?erro=voce_nao_pode_se_excluir");
        exit();
    }

    $stmt = $con->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: usuarios_lista.php?sucesso=usuario_removido");
    } else {
        header("Location: usuarios_lista.php?erro=falha_ao_excluir");
    }
}
?>