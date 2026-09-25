<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../conexao.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['idusuario']) || empty($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'nao_autorizado']);
    exit;
}

$idusuario = (int)$_SESSION['idusuario'];

// ==========================================
// FUNÇÃO DE NORMALIZAÇÃO DE TEXTO (LEETSPEAK / CARACTERES)
// ==========================================
function normalizarTexto($texto) {
    $texto = mb_strtolower($texto, 'UTF-8');

    // Substituição de símbolos, acentos e números por letras normais
    $substituicoes = [
        '@' => 'a', '4' => 'a', 'ã' => 'a', 'á' => 'a', 'à' => 'a', 'â' => 'a',
        '3' => 'e', 'é' => 'e', 'ê' => 'e',
        '1' => 'i', '!' => 'i', 'í' => 'i', '|' => 'i',
        '0' => 'o', 'ô' => 'o', 'ó' => 'o', 'õ' => 'o',
        '5' => 's', '$' => 's',
        '7' => 't',
        'u' => 'u', 'ú' => 'u'
    ];

    return strtr($texto, $substituicoes);
}

// ==========================================
// LÓGICA DE CURTIR (GET ou POST com acao=curtir)
// ==========================================
if (isset($_GET['acao']) && $_GET['acao'] === 'curtir' && isset($_GET['look'])) {
    $idlook = (int)$_GET['look'];

    // Verificar se já curtiu
    $stmt_check = $con->prepare("SELECT id FROM comunidade_curtidas WHERE idlook = ? AND idusuario = ?");
    $stmt_check->bind_param("ii", $idlook, $idusuario);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows > 0) {
        // Se já curtiu, descurte
        $stmt_del = $con->prepare("DELETE FROM comunidade_curtidas WHERE idlook = ? AND idusuario = ?");
        $stmt_del->bind_param("ii", $idlook, $idusuario);
        $stmt_del->execute();
        echo json_encode(['status' => 'descurtido']);
    } else {
        // Se não curtiu, insere a curtida
        $stmt_ins = $con->prepare("INSERT INTO comunidade_curtidas (idlook, idusuario) VALUES (?, ?)");
        $stmt_ins->bind_param("ii", $idlook, $idusuario);
        $stmt_ins->execute();
        echo json_encode(['status' => 'curtido']);
    }
    exit;
}

// ==========================================
// LÓGICA DE PUBLICAR COMENTÁRIO (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['comentar']) || isset($_POST['comentario']))) {
    
    $idlook = isset($_POST['idlook']) ? (int)$_POST['idlook'] : 0;
    $comentarioOriginal = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

    if ($idlook <= 0 || empty($comentarioOriginal)) {
        http_response_code(400);
        echo json_encode(['erro' => 'dados_invalidos']);
        exit;
    }

    // Normaliza o comentário inserido pelo usuário
    $comentarioTratado = normalizarTexto($comentarioOriginal);

    // Buscar palavras bloqueadas no banco de dados
    $sql_palavras = "SELECT palavra FROM palavras_bloqueadas";
    $result_palavras = $con->query($sql_palavras);

    $bloqueado = false;

    if ($result_palavras && $result_palavras->num_rows > 0) {
        while ($row = $result_palavras->fetch_assoc()) {
            // Normaliza a palavra proibida vinda do banco também
            $palavraProibida = normalizarTexto($row['palavra']);

            if (!empty($palavraProibida) && mb_strpos($comentarioTratado, $palavraProibida) !== false) {
                $bloqueado = true;
                break;
            }
        }
    }

    // Se contiver termo bloqueado, interrompe e avisa a interface
    if ($bloqueado) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['erro' => 'bloqueado']);
        exit;
    }

    // Se estiver limpo, grava o comentário na tabela
    $stmt_insert = $con->prepare("INSERT INTO comunidade_comentarios (idlook, idusuario, comentario) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("iis", $idlook, $idusuario, $comentarioOriginal);

    if ($stmt_insert->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['sucesso' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['erro' => 'erro_ao_salvar']);
    }
    exit;
}