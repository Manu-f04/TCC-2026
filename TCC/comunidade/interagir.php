<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../conexao.php';

if (!isset($_SESSION['idusuario'])) {
    header("Location: ../login.php");
    exit;
}

$idusuario = $_SESSION['idusuario'];

if (isset($_GET['acao']) && $_GET['acao'] === 'curtir' && isset($_GET['look'])) {
    $idlook = intval($_GET['look']);
    
    $stmt_check = $con->prepare("SELECT id FROM comunidade_curtidas WHERE idusuario = ? AND idlook = ?");
    $stmt_check->bind_param("ii", $idusuario, $idlook);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows > 0) {
        $stmt_del = $con->prepare("DELETE FROM comunidade_curtidas WHERE idusuario = ? AND idlook = ?");
        $stmt_del->bind_param("ii", $idusuario, $idlook);
        $stmt_del->execute();
    } else {
        $stmt_add = $con->prepare("INSERT INTO comunidade_curtidas (idusuario, idlook) VALUES (?, ?)");
        $stmt_add->bind_param("ii", $idusuario, $idlook);
        $stmt_add->execute();
    }
    header("Location: comunidade.php#look-" . $idlook);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentar'])) {
    $idlook = intval($_POST['idlook']);
    $comentario = trim($_POST['comentario']);
    
    if (!empty($comentario)) {
        $stmt_com = $con->prepare("INSERT INTO comunidade_comentarios (idusuario, idlook, comentario) VALUES (?, ?, ?)");
        $stmt_com->bind_param("iis", $idusuario, $idlook, $comentario);
        $stmt_com->execute();
    }
    header("Location: comunidade.php#look-" . $idlook);
    exit;
}

header("Location: comunidade.php");
exit;