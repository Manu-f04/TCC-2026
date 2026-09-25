<?php
session_start();
require_once("conexao.php"); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... (Mantenha a Verificação de Token CSRF que já existe) ...

    $login = trim($_POST['login']); 
    $senha_digitada = $_POST['senha'];

    /** * BUSCA ATUALIZADA: Agora buscamos também o 'nivel_acesso'
     */
    $sql = "SELECT id, nome_usuario, senha, nivel_acesso FROM usuarios WHERE email = ? OR nome_usuario = ? LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

        if (password_verify($senha_digitada, $usuario['senha']) || $senha_digitada === $usuario['senha']) {
            
            // ... (Mantenha sua lógica de Migração Automática para Hash aqui) ...

            // INICIALIZAÇÃO DA SESSÃO
            $_SESSION['idusuario'] = $usuario['id'];
            $_SESSION['nomeusuario'] = $usuario['nome_usuario'];
            $_SESSION['nivel'] = $usuario['nivel_acesso']; // Salvamos se é 'admin' ou 'usuario'
            
            unset($_SESSION['erro'], $_SESSION['form_data']);

            /** * REDIRECIONAMENTO INTELIGENTE
             * Se for admin, vai para a pasta admin. Se não, vai para a index normal.
             */
            if ($_SESSION['nivel'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();

        } else {
            $_SESSION['erro'] = "Senha incorreta.";
        }
    } else {
        $_SESSION['erro'] = "Usuário ou email não encontrado.";
    }
    
    // RETORNO EM CASO DE FALHA
    $_SESSION['form_data']['login'] = $login;
    header("Location: login.php");
    exit();
}
?>