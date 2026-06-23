<?php
session_start();
require_once "conexao.php"; 

// Importa PHPMailer para o escopo global
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Carrega as bibliotecas do PHPMailer
require_once __DIR__ . '/phpmailer/Exception.php';
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';

// Gerencia mensagens de feedback vindas da sessão
$msg = $_SESSION['sucesso_recuperacao'] ?? $_SESSION['erro_recuperacao'] ?? "";
unset($_SESSION['sucesso_recuperacao'], $_SESSION['erro_recuperacao']);

$email = "";

// PROCESSAMENTO DO FORMULÁRIO DE ENVIO
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? '');

    // Verifica a existência do e-mail na tabela de usuários
    $sql = "SELECT id, nome FROM usuarios WHERE email = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        $idusuario = $usuario["id"];
        $nome_usuario = $usuario["nome"];

        /** * Geração de Código Aleatório
         * Gera um número aleatório e preenche com zeros à esquerda para garantir 6 dígitos.
         */
        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        /** * Segurança e Expiração
         * Cria um hash seguro do código para armazenamento.
         * Define o tempo de expiração para 10 minutos (600 segundos) a partir do horário atual.
         */
        $hash_codigo = password_hash($codigo, PASSWORD_DEFAULT);
        $expira = date("Y-m-d H:i:s", time() + 600); 
        
        // Remove registros de recuperação anteriores para este usuário
        $con->query("DELETE FROM senha_reset WHERE idusuario = $idusuario");

        // Insere o novo hash e o prazo de expiração na tabela senha_reset
        $sql_insert = "INSERT INTO senha_reset (idusuario, token, expira) VALUES (?, ?, ?)";
        $stmt_insert = $con->prepare($sql_insert);
        $stmt_insert->bind_param("iss", $idusuario, $hash_codigo, $expira);
        $stmt_insert->execute();

        // CONFIGURAÇÃO E ENVIO DO E-MAIL
        $mail = new PHPMailer(true);
        try {
            /** * Parâmetros de Servidor SMTP
             * Define host, credenciais e protocolo de segurança STARTTLS.
             */
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'emanuelle.2023325655@aluno.iffar.edu.br'; 
            $mail->Password   = 'eafw fbij qdhg btwk'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            /** * Ajustes de SSL para ambiente local
             * Desativa a verificação rígida de certificado para evitar erros em servidores locais (XAMPP/WAMP).
             */
            $mail->SMTPOptions = [
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true],
            ];

            // Define remetente e destinatário
            $mail->setFrom($mail->Username, 'FashionStyle Recuperacao');
            $mail->addAddress($email, $nome_usuario);

            // Define formato, codificação e conteúdo do e-mail
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Código de Recuperaçãoo de Senha - FashionStyle';
            
            $mail->Body = "<div style='font-family: Arial, sans-serif; text-align: center; padding: 20px; border: 1px solid #ccc; border-radius: 10px; background-color: #f9f9f9;'>
                            <h2 style='color: #333;'>Olá, {$nome_usuario}!</h2>
                            <p style='color: #555;'>Você solicitou a recuperação de senha. Seu código é:</p>
                            <h1 style='font-size: 3rem; letter-spacing: 10px; color: #000; margin: 20px 0; background-color: #eee; padding: 10px; border-radius: 5px; display: inline-block;'>{$codigo}</h1>
                            <p style='color: #777; font-size: 0.9em;'>Este código é válido por 10 minutos.</p>
                           </div>";
            
            $mail->send();
            
            // Define variáveis de sessão para controle da próxima etapa
            $_SESSION['email_redefinicao'] = $email;
            $_SESSION['codigo_enviado'] = true;
            $_SESSION['sucesso_recuperacao'] = "✅ Código de verificação enviado com sucesso!";

            // Redireciona para a página de validação
            header("Location: verificar_codigo.php");
            exit();

        } catch (Exception $e) {
            $_SESSION['erro_recuperacao'] = "❌ Erro ao enviar código: {$mail->ErrorInfo}";
        }

    } else {
        // Mensagem genérica por segurança para não confirmar existência de e-mails
        $_SESSION['sucesso_recuperacao'] = "✅ Se o e-mail estiver cadastrado, um código foi enviado.";
    }
    
    header("Location: enviar_codigo.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 

    <style>
        body { background:#fff; font-family:"Poppins", sans-serif; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; padding:20px; }
        .container { max-width:400px; width:100%; }
        .card { background:white; border-radius:20px; box-shadow:0 10px 25px rgba(0,0,0,0.1); padding:40px; }
        .card h2 { text-align:center; margin-bottom:20px; font-weight:700; }
        .form-control { width:100%; padding:12px 15px; border-radius:10px; border:1px solid #ccc; margin-bottom:15px; font-size:1rem; }
        .form-control:focus { border-color:#000; outline:none; }
        .btn-custom { width:100%; padding:12px; border:none; border-radius:25px; font-weight:600; font-size:1rem; cursor:pointer; background:#000; color:#fff; transition: background 0.3s; }
        .btn-custom:hover { background:#333; }
        
        .msg-erro { color:red; font-weight:600; margin-bottom:15px; text-align:center; }
        .login-links { margin-top:15px; text-align:center; }
        .login-links a { color:#000; text-decoration:none; font-weight:500; }
        .login-links a:hover { text-decoration:underline; }

        .alert { padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>

<div class="container">
<div class="card">
    <h2>Recuperar Senha</h2>
    
    <?php 
    if(isset($_SESSION['sucesso_recuperacao'])){
        echo '<div class="alert alert-success">'.$_SESSION['sucesso_recuperacao'].'</div>';
        unset($_SESSION['sucesso_recuperacao']);
    }
    if(isset($_SESSION['erro_recuperacao'])){
        echo '<div class="alert alert-danger">'.$_SESSION['erro_recuperacao'].'</div>';
        unset($_SESSION['erro_recuperacao']);
    }
    ?>

    <form method="POST" id="formEmail">
        <input type="email" name="email" class="form-control" 
               value="<?php echo htmlspecialchars($email); ?>"
               placeholder="Email cadastrado" required
               autocomplete="email">
        
        <button type="submit" class="btn-custom mb-3" id="submitBtn">
            <i class="fas fa-paper-plane me-2"></i> Enviar Código de Verificação
        </button>
        
        <div class="login-links">
            <a href="login.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Voltar para o login
            </a>
        </div>
    </form>
</div>
</div>

<script>
/** * Feedback visual de carregamento
 * Altera o texto do botão e desabilita o clique no momento do envio.
 * Evita múltiplas requisições simultâneas enquanto o servidor processa o e-mail.
 */
document.addEventListener('DOMContentLoaded', function() {
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('formEmail');
    
    form.addEventListener('submit', function() {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Enviando...';
        submitBtn.disabled = true;
    });
});
</script>
</body>
</html>