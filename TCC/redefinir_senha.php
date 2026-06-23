<?php
// Configurações de depuração para ambiente de desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "conexao.php";

/** * Verificação de Segurança
 * Garante que o usuário passou pelas etapas anteriores de validação (e-mail e código) 
 * antes de permitir o acesso à troca de senha.
 */
if (!isset($_SESSION['idusuario_redefinir'])) {
    header("Location: login.php");
    exit();
}

$idusuario = $_SESSION['idusuario_redefinir'];
$msg = "";

/** * Processamento do Formulário de Redefinição
 * Valida a integridade da nova senha e realiza a atualização no banco de dados.
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nova = trim($_POST["senha"] ?? '');
    $confirmar = trim($_POST["confirmar_senha"] ?? '');
    
    // VALIDAÇÃO DE CRITÉRIOS MÍNIMOS
    if (strlen($nova) < 6) {
        $msg = "<div class='alert alert-danger'>❌ A senha deve ter no mínimo 6 caracteres.</div>";
    } elseif ($nova !== $confirmar) {
        $msg = "<div class='alert alert-danger'>❌ As senhas não coincidem.</div>";
    } else {
        /** * Criptografia e Persistência
         * Gera um hash seguro da nova senha para armazenamento conforme as melhores práticas de segurança.
         */
        $hash = password_hash($nova, PASSWORD_DEFAULT);
        
        $sql = "UPDATE usuarios SET senha = ? WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("si", $hash, $idusuario);
        
        if ($stmt->execute()) {
            /** * Limpeza de Dados Temporários
             * Remove os registros de solicitações de reset e limpa as variáveis de sessão de controle.
             */
            $stmt_del = $con->prepare("DELETE FROM senha_reset WHERE idusuario = ?");
            $stmt_del->bind_param("i", $idusuario);
            $stmt_del->execute();

            unset($_SESSION['email_redefinicao']);
            unset($_SESSION['codigo_enviado']);
            unset($_SESSION['idusuario_redefinir']);

            $_SESSION['sucesso'] = "✅ Senha alterada com sucesso! Faça login.";
            header("Location: login.php");
            exit();
        } else {
            $msg = "<div class='alert alert-danger'>❌ Erro ao atualizar o banco de dados.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f4f4f4; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card { width: 100%; max-width: 400px; padding: 20px; border-radius: 15px; border: none; }
    </style>
</head>
<body>
    <div class="card shadow">
        <h2 class="text-center mb-4">Nova Senha</h2>
        
        <?php echo $msg; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Digite a nova senha:</label>
                <input type="password" name="senha" class="form-control" required minlength="6">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirme a nova senha:</label>
                <input type="password" name="confirmar_senha" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-dark w-100">Alterar Senha</button>
        </form>
    </div>
</body>
</html>