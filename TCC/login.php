<?php
session_start();

/** * Gestão de Token CSRF */
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Fashion Style</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        body { background: #fff; font-family: "Poppins", sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        
        .container { text-align: center; width: 100%; max-width: 600px; padding: 20px; }
        .titulo { font-size: 2.3rem; font-weight: 700; margin-bottom: 5px; }
        .subtitulo { margin-top: -10px; font-size: 1.1rem; color: #555; margin-bottom: 20px; }
        
        .form-box { 
            margin: 20px auto; 
            background: #fff; 
            padding: 50px 55px; 
            border-radius: 25px; 
            box-shadow: 0 0 40px rgba(0,0,0,0.1); 
            text-align: left;
        }

        label { display: block; margin-bottom: 8px; font-weight: 500; }

        .form-control { 
            height: 45px; width: 100%; border-radius: 12px; border: 1px solid #ddd; 
            padding-left: 15px; font-size: 0.95rem; transition: all 0.3s;
            margin-bottom: 25px;
            box-sizing: border-box;
        }

        .form-control:focus { border-color: #000 !important; outline: none; box-shadow: 0 0 5px rgba(0,0,0,0.1); }

        /* --- SISTEMA DE BOTOES (Igual ao Cadastro, mas com 2 unidades) --- */
        .botoes { display: flex; justify-content: space-between; margin-top: 10px; gap: 15px; }
        
        .btn { 
            width: 48%; /* Largura para 2 botões ficarem perfeitos lado a lado */
            padding: 12px; 
            border-radius: 30px; 
            font-size: 1rem; 
            font-weight: 500; 
            border: none; 
            cursor: pointer; 
            transition: 0.3s;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-dark { background: #000; color: #fff; }
        .btn-outline-dark { background: #fff; border: 2px solid #000; color: #000; }
        
        .btn:hover { opacity: 0.8; transform: translateY(-1px); }

        .msg-erro { color: #ff3b3b; font-weight: bold; text-align: center; margin-bottom: 15px; }
        .msg-sucesso { color: #28a745; font-weight: bold; text-align: center; margin-bottom: 15px; }
        
        .esqueci-senha { display: block; text-align: center; margin-top: 25px; color: #888; text-decoration: none; font-size: 0.9rem; }
        .esqueci-senha:hover { color: #000; text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <h1 class="titulo">Fashion Style</h1>
    <p class="subtitulo">Acesse sua conta</p>

    <div class="form-box">
        <?php
        if(isset($_SESSION['erro'])){
            echo '<div class="msg-erro">'.$_SESSION['erro'].'</div>';
            unset($_SESSION['erro']);
        }
        if(isset($_SESSION['sucesso'])){
            echo '<div class="msg-sucesso">'.$_SESSION['sucesso'].'</div>';
            unset($_SESSION['sucesso']);
        }
        ?>

        <form action="processa_login.php" method="POST">
            <label>E-mail ou Usuário</label>
            <input type="text" name="login" class="form-control" placeholder="Digite seu acesso" required
                   value="<?= isset($_SESSION['form_data']['login']) ? htmlspecialchars($_SESSION['form_data']['login']) : '' ?>">
            
            <label>Senha</label>
            <input type="password" name="senha" class="form-control" placeholder="Digite sua senha" required>
            
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="botoes">
                <a href="cadastro.php" class="btn btn-outline-dark">
                    <i class="bi bi-arrow-left"></i> Voltar para o cadastro
                </a>

                <button type="submit" class="btn btn-dark">Entrar na conta</button>
            </div>
        </form>

        <a href="enviar_codigo.php" class="esqueci-senha">Esqueci minha senha</a>
    </div>
</div>

</body>
</html>