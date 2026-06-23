<?php
session_start();
require_once "conexao.php";

$msg = "";
$senha_gerada = "";
$email_usuario = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $email_usuario = $email;

    // Verifica a existência do e-mail na base de dados
    $sql = "SELECT id, nome FROM usuarios WHERE email = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        /** * Algoritmo de Geração de Senha Semântica
         * Combina adjetivos, substantivos e numerais para criar senhas legíveis.
         * Facilita a memorização e digitação por parte do usuário.
         */
        $adjetivos = ['Legal', 'Rapido', 'Feliz', 'Forte', 'Doce', 'Lindo', 'Bom', 'Grande'];
        $substantivos = ['Gato', 'Cachorro', 'Passaro', 'Fluxo', 'Rio', 'Mar', 'Sol', 'Lua'];
        $numero = rand(10,99);
        
        /** * Seleção Aleatória e Concatenação
         * Seleciona índices aleatórios dos arrays de palavras.
         * Agrupa as partes para formar a string da senha temporária.
         */
        $senha_temporaria = $adjetivos[array_rand($adjetivos)].$substantivos[array_rand($substantivos)].$numero;
        $senha_gerada = $senha_temporaria;

        /** * Atualização de Segurança com Hash
         * Aplica criptografia password_hash na senha gerada antes do armazenamento.
         * Atualiza o registro do usuário com o novo hash de segurança.
         */
        $hash = password_hash($senha_temporaria, PASSWORD_DEFAULT);
        $sql_update = "UPDATE usuarios SET senha = ? WHERE id = ?";
        $stmt_update = $con->prepare($sql_update);
        $stmt_update->bind_param("si", $hash, $usuario['id']);
        $stmt_update->execute();

        $_SESSION['senha_temp'] = $senha_temporaria;
        $_SESSION['email_temp'] = $email;
    } else {
        $msg = "<div class='msg-erro'>Email não encontrado. Verifique se digitou corretamente.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar Senha</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
body { background:#fff; font-family:"Poppins", sans-serif; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; padding:20px; }
.container { max-width:400px; width:100%; }
.card { background:white; border-radius:20px; box-shadow:0 10px 25px rgba(0,0,0,0.1); padding:40px; }
.card h2 { text-align:center; margin-bottom:20px; font-weight:700; }
.form-control { width:100%; padding:12px 15px; border-radius:10px; border:1px solid #ccc; margin-bottom:15px; font-size:1rem; }
.form-control:focus { border-color:#000; outline:none; }
.btn { width:100%; padding:12px; border:none; border-radius:25px; font-weight:600; font-size:1rem; cursor:pointer; background:#000; color:#fff; }
.btn:hover { background:#333; }
.msg-erro { color:red; font-weight:600; margin-bottom:15px; text-align:center; }
.senha-gerada { text-align:center; margin-top:20px; padding:20px; border:2px dashed #000; border-radius:15px; }
.senha-texto { font-family:'Courier New', monospace; font-size:20px; font-weight:700; margin:10px 0; }
.copy-btn { padding:8px 12px; border-radius:10px; border:none; background:#000; color:#fff; cursor:pointer; }
.copy-btn:hover { background:#333; }
.login-links { text-align:center; margin-top:15px; }
.login-links a { color:#000; text-decoration:none; font-weight:500; }
.login-links a:hover { text-decoration:underline; }
</style>
</head>
<body>
<div class="container">
<div class="card">
<h2>Recuperar Senha</h2>

<?php echo $msg; ?>

<?php if(empty($senha_gerada)) { ?>
<form method="POST">
<input type="email" name="email" class="form-control" placeholder="Digite seu email" required value="<?=htmlspecialchars($email_usuario)?>">
<button type="submit" class="btn">Gerar Nova Senha</button>
<div class="login-links">
<a href="login.php"><i class="bi bi-arrow-left"></i> Voltar para Login</a>
</div>
</form>
<?php } else { ?>
<div class="senha-gerada">
<p>Senha temporária gerada:</p>
<div class="senha-texto" id="senhaTexto"><?=htmlspecialchars($senha_gerada)?></div>

<button class="copy-btn" onclick="copiarSenha()">Copiar</button>

<div class="login-links">
<a href="login.php"><i class="bi bi-box-arrow-in-right"></i> Ir para Login</a>
</div>
</div>
<script>
/** * Funcionalidade de Transferência para Área de Transferência
 * Realiza a cópia do texto da senha para o clipboard do navegador.
 */
function copiarSenha(){
    const senhaTexto = document.getElementById('senhaTexto').textContent;
    navigator.clipboard.writeText(senhaTexto);
    alert('Senha copiada!');
}
</script>
<?php } ?>
</div>
</div>
</body>
</html>