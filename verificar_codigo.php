<?php
session_start();
require_once "conexao.php";

// Verifica se veio da etapa anterior (enviar_codigo.php)
if (!isset($_SESSION['email_redefinicao'])) {
    header("Location: login.php"); 
    exit();
}

$email = $_SESSION['email_redefinicao'];
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $codigo_digitado = trim($_POST["codigo"] ?? '');
    
    if (empty($codigo_digitado) || strlen($codigo_digitado) !== 6 || !is_numeric($codigo_digitado)) {
        $msg = "<div class='msg-erro'>❌ O código deve conter 6 dígitos numéricos.</div>";
    } else {
        //   busca na tabela 'senha_reset' fazendo um JOIN com 'usuarios'
        $sql = "SELECT sr.idusuario, sr.token, sr.expira 
                FROM senha_reset sr 
                JOIN usuarios u ON sr.idusuario = u.id 
                WHERE u.email = ? AND sr.expira > NOW()";
        
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $dados = $result->fetch_assoc();
            $hash_codigo = $dados['token'];
            
            // Verifica código usando o hash (segurança!)
            if (password_verify($codigo_digitado, $hash_codigo)) {
                
                // Código correto! Salva ID na sessão e vai para redefinir senha
                $_SESSION['idusuario_redefinir'] = $dados['idusuario'];
                
                //  Limpa o token da tabela 'senha_reset' (não mais da 'usuarios')
                $sql_clear = "DELETE FROM senha_reset WHERE idusuario = ?";
                $stmt_clear = $con->prepare($sql_clear);
                $stmt_clear->bind_param("i", $dados['idusuario']);
                $stmt_clear->execute();

                header("Location: redefinir_senha.php");
                exit();
                
            } else {
                $msg = "<div class='msg-erro'>❌ Código inválido ou incorreto.</div>";
            }
        } else {
            $msg = "<div class='msg-erro'>❌ Código inválido ou expirado. Tente enviar um novo código.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verificar Código</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 

<style>

body { background:#fff; font-family:"Poppins", sans-serif; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; padding:20px; }
.container { max-width:400px; width:100%; }
.card { background:white; border-radius:20px; box-shadow:0 10px 25px rgba(0,0,0,0.1); padding:40px; }
.card h2 { text-align:center; margin-bottom:20px; font-weight:700; }
.form-control { width:100%; padding:12px 15px; border-radius:10px; border:1px solid #ccc; margin-bottom:15px; font-size:1rem; }
.form-control:focus { border-color:#000; outline:none; }
.btn { width:100%; padding:12px; border:none; border-radius:25px; font-weight:600; font-size:1rem; cursor:pointer; background:#000; color:#fff; transition: background 0.3s; }
.btn:hover { background:#333; }
.msg-erro { color:red; font-weight:600; margin-bottom:15px; text-align:center; }
.login-links { margin-top:15px; text-align:center; }
.login-links a { color:#000; text-decoration:none; font-weight:500; }
.login-links a:hover { text-decoration:underline; }

.input-codigo { 
    font-size: 2rem !important; 
    letter-spacing: 15px !important; 
    text-align: center !important; 
    padding: 15px 10px !important;
}
.small-text { font-size: 0.9rem; color: #555; text-align: center; margin-bottom: 20px; }
</style>
</head>
<body>
<div class="container">
<div class="card">
    <h2>Verificação de Código</h2>
    
    <?php echo $msg; ?>

    <p class="small-text">
        <i class="fas fa-envelope-open-text me-1"></i> 
        Enviamos um código de 6 dígitos para o email: **<?= htmlspecialchars($email) ?>**
    </p>

    <form method="POST" id="formCodigo">
        <input type="text" name="codigo" class="form-control input-codigo" 
               maxlength="6" placeholder="______" required
               inputmode="numeric" pattern="[0-9]*" autocomplete="off">
        
        <button type="submit" class="btn" id="submitBtn">
            <i class="fas fa-check-circle"></i> Verificar Código
        </button>
        
        <div class="login-links">
            <a href="enviar_codigo.php">
                <i class="fas fa-redo"></i> Reenviar Código
            </a>
        </div>
    </form>
</div>
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const submitBtn = document.getElementById('submitBtn');
    const codigoInput = document.querySelector('input[name="codigo"]');
    
    submitBtn.disabled = true;

    codigoInput.addEventListener('input', function() {
        const code = this.value.replace(/[^0-9]/g, ''); 
        this.value = code;
        
        if (code.length === 6) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    });
    
    document.getElementById('formCodigo').addEventListener('submit', function() {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
        submitBtn.disabled = true;
    });
});
</script>
</body>
</html>