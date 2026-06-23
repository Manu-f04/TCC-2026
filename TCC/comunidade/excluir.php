<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../conexao.php'; 

// Verifica se o usuário está logado
$logado = isset($_SESSION['idusuario']) && !empty($_SESSION['idusuario']);
if (!$logado) {
    http_response_code(403);
    echo json_encode(["erro" => "Não autorizado"]);
    exit;
}

$id_usuario_logado = (int)$_SESSION['idusuario'];
$acao = isset($_GET['acao']) ? $_GET['acao'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["erro" => "ID inválido"]);
    exit;
}

// ==========================================
// AÇÃO: DELETAR COMENTÁRIO
// ==========================================
if ($acao === 'deletar_comentario') {
    // Garante que o comentário pertence ao usuário logado antes de deletar
    $stmt = $con->prepare("DELETE FROM comunidade_comentarios WHERE id = ? AND idusuario = ?");
    $stmt->bind_param("ii", $id, $id_usuario_logado);
    
    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true]);
    } else {
        http_response_code(500);
        echo json_encode(["erro" => "Erro ao deletar no banco"]);
    }
    exit;
}

// ==========================================
// AÇÃO: DELETAR POST (LOOK)
// ==========================================
if ($acao === 'deletar_post') {
    // Altera o look para não publicado (0) apenas se pertencer ao usuário logado
    $stmt = $con->prepare("UPDATE looks SET publicado = 0 WHERE id = ? AND idusuario = ?");
    $stmt->bind_param("ii", $id, $id_usuario_logado);
    
    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true]);
    } else {
        http_response_code(500);
        echo json_encode(["erro" => "Erro ao despublicar no banco"]);
    }
    exit;
}

// Se não entrou em nenhuma ação válida
http_response_code(400);
echo json_encode(["erro" => "Ação inválida"]);
exit;
?>