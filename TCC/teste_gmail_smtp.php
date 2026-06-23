<?php
// Inclua os arquivos do PHPMailer (seus caminhos)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/phpmailer/Exception.php';
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';

// ** SEUS DADOS **
$remetente = 'emanuelle.2023325655@aluno.iffar.edu.br';
$senha_app = 'eafw fbij qdhg btwk';
$destinatario = 'SEU_EMAIL_PARA_TESTE'; // Coloque um email seu para receber a prova

$mail = new PHPMailer(true);
$mail->SMTPDebug = 2; // DEBUG DETALHADO (mostra na tela o processo de conexão)
$mail->isSMTP();

try {
    // Configurações do Gmail
    $mail->Host       = 'smtp.gmail.com'; 
    $mail->SMTPAuth   = true;
    $mail->Username   = $remetente;
    $mail->Password   = $senha_app;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    
    // Configuração para evitar erros de certificado
    $mail->SMTPOptions = [
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true],
    ];

    // Conteúdo do Email
    $mail->setFrom($remetente, 'Teste FashionStyle');
    $mail->addAddress($destinatario);
    $mail->isHTML(true);
    $mail->Subject = 'Teste SMTP Conectado com Sucesso!';
    $mail->Body    = '<h1>Sucesso!</h1><p>O PHPMailer conseguiu se conectar ao Gmail e usar a Senha de App.</p>';

    $mail->send();
    echo '✅ Mensagem de teste enviada para ' . $destinatario . '!';
} catch (Exception $e) {
    echo "❌ O Email não pôde ser enviado. Erro do Mailer: {$mail->ErrorInfo}";
}
?>