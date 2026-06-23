<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "autorizacao.php"; 
require_once "conexao.php";

/** * Validação de Parâmetro via GET
 * Verifica se o ID foi enviado pela URL e se é um valor numérico
 * Impede a execução caso o parâmetro seja inválido ou malicioso
 */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: looks.php?msg=" . urlencode("ID inválido."));
    exit;
}

// Converte o ID para inteiro e recupera o ID do usuário da sessão
$id = (int)$_GET['id'];
$userId = $_SESSION['idusuario']; 
$msg = "Erro ao excluir o look.";

try {
    /** * Execução da exclusão com dupla verificação
     * Prepara o SQL para deletar o registro filtrando pelo ID do look e pelo ID do usuário.
     * Garante que um usuário não consiga excluir looks que pertencem a outra pessoa.
     */
    if ($stmt = $con->prepare("DELETE FROM looks WHERE id = ? AND idusuario = ?")) {
        // Vincula os parâmetros inteiros (ii) e executa a query
        $stmt->bind_param("ii", $id, $userId); 
        $stmt->execute();
        
        /** * Verificação de linhas afetadas
         * Avalia se algum registro foi efetivamente removido do banco de dados.
         */
        if ($stmt->affected_rows > 0) {
            $msg = "Look excluído com sucesso!";
        } else {
            $msg = "Look não encontrado ou você não tem permissão.";
        }
        // Fecha o statement para liberar memória
        $stmt->close();
    } else {
        throw new Exception("Falha na preparação da query: " . $con->error);
    }
} catch (Exception $e) {
    // Captura exceções e armazena a mensagem de erro técnico
    $msg = "Erro ao excluir o look: " . $e->getMessage();
}

/** * Redirecionamento com codificação de URL
 * Utiliza urlencode para garantir que caracteres especiais na mensagem sejam transmitidos via GET.
 * Finaliza o script após o redirecionamento.
 */
header("Location: looks.php?msg=" . urlencode($msg));
exit;
?>