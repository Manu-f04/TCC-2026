<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "autorizacao.php";   
require_once "conexao.php";       

// Recupera o ID do usuário e o ID da peça a ser deletada
$userId = $_SESSION['idusuario'];
$pecaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verifica se o ID da peça é válido, caso contrário interrompe a execução
if ($pecaId <= 0) {
    header("Location: guardaroupa.php?msg=" . urlencode("Erro: ID da peça inválido."));
    exit;
}

try {
    // Inicia transação para garantir a integridade dos dados no banco
    $con->begin_transaction();

    /**
     * Remove os looks que utilizam a peça.
     * Filtra as colunas idroupa1, 2, 3 e 5 conforme a estrutura
     */
    $sql_looks = "DELETE FROM looks WHERE idusuario = ? AND (idroupa1 = ? OR idroupa2 = ? OR idroupa3 = ? OR idroupa5 = ?)";
    $stmt_l = $con->prepare($sql_looks);
    
    // Vincula os parâmetros e executa a remoção dos looks relacionados
    $stmt_l->bind_param("iiiii", $userId, $pecaId, $pecaId, $pecaId, $pecaId);
    $stmt_l->execute();
    $looks_affected = $stmt_l->affected_rows;
    $stmt_l->close();

    /**
     * Remove o registro da peça de roupa.
     */
    $sql_roupa = "DELETE FROM roupas WHERE id = ? AND idusuario = ?";
    $stmt_r = $con->prepare($sql_roupa);
    $stmt_r->bind_param("ii", $pecaId, $userId);
    $stmt_r->execute();
    $roupa_deletada = $stmt_r->affected_rows;
    $stmt_r->close();

    // Confirma as alterações (Commit) se a roupa foi removida com sucesso
    if ($roupa_deletada > 0) {
        $con->commit();
        
        $msg = "Peça excluída com sucesso!";
        if ($looks_affected > 0) {
            $msg .= " " . $looks_affected . " look(s) que usavam esta peça também foram removidos.";
        }
    } else {
        // Cancela as alterações (Rollback) caso a peça não exista ou pertença a outro usuário
        $con->rollback();
        $msg = "Erro: Peça não encontrada ou você não tem permissão.";
    }

} catch (Exception $e) {
    // Reverte operações em caso de erro técnico durante o processo
    $con->rollback();
    $msg = "Erro técnico ao excluir: " . $e->getMessage();
}

// Redireciona para a página principal do guarda-roupa com a mensagem de retorno
header("Location: guardaroupa.php?msg=" . urlencode($msg));
exit;
?>