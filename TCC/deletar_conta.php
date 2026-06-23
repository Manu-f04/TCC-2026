<?php
session_start();
require_once 'conexao.php'; 
 /**
 * Garante que ninguém consiga rodar esse script apenas digitando a URL.
 * O script só funciona se houver uma sessão de usuário ativa.
 */
if (!isset($_SESSION['idusuario'])) {
    header('Location: login.php');
    exit();
}

$id_usuario = $_SESSION['idusuario'];

try {
     /**
     *  Se no seu banco de dados as tabelas 'roupas' e 'looks' foram criadas
     * com a regra FOREIGN KEY (...) REFERENCES usuarios(id) ON DELETE CASCADE,
     * este único comando abaixo limpa TUDO o que pertence ao usuário automaticamente.
     */
    $stmt = $con->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id_usuario);
    
    if ($stmt->execute()) {
       /**
         * Após deletar do banco, precisamos "matar" a sessão no navegador,
         * caso contrário o site continuaria achando que o usuário está logado até ele fechar a aba.
         */
        session_destroy();
        
        // Redireciona para a home com uma mensagem de sucesso na URL
        header('Location: index.php?msg=' . urlencode('Sua conta foi removida com sucesso.'));
        exit();
    } else {
        throw new Exception("Erro ao excluir.");
    }
} catch (Exception $e) {
    /** 
     * Se algo der errado (ex: banco fora do ar), o usuário é mandado de volta
     * para o perfil com um aviso de erro, evitando que a página fique em branco.
     */
    header('Location: configuracoes_perfil.php?erro=Erro ao deletar conta.');
    exit();
}
?>