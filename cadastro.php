<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fashion Style - Criar Conta</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body { background: #fff; font-family: "Poppins", sans-serif; }
        .section { width: 100%; display: flex; justify-content: center; margin-top: 40px; }
        .container { text-align: center; }
        .titulo { font-size: 2.3rem; font-weight: 700; }
        .subtitulo { margin-top: -10px; font-size: 1.1rem; color: #555; }
        .form-box { margin: 40px auto; width: 600px; background: #fff; padding: 50px 55px; border-radius: 25px; box-shadow: 0 0 40px rgba(0,0,0,0.1); }
        .row { display: flex; gap: 35px; margin-bottom: 25px; }
        .col { width: 50%; text-align: left; }
        .col-full { width: 100%; text-align: left; }
        
        .form-control { 
            height: 45px; width: 100%; border-radius: 12px; border: 1px solid #ddd; 
            padding-left: 15px; font-size: 0.95rem; transition: all 0.3s;
        }

        /* --- SISTEMA DE CORES --- */
        .form-control { border-color: #ddd !important; background-color: #fff !important; }
        .form-control:focus { border-color: #000 !important; outline: none; }
        .campo-erro { border-color: #ff3b3b !important; background-color: #fff9f9 !important; }
        .campo-sucesso { border-color: #28a745 !important; }

        .help-text { font-size: 0.75rem; color: #888; margin-top: 4px; display: block; }
        .senha-box { position: relative; }
        .icon-eye { position: absolute; right: 15px; top: 35px; cursor: pointer; font-size: 20px; opacity: 0.75; }
        .preview-img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; display: block; margin: 10px auto; border: 2px solid #ddd; }
        .btn-delete-photo { margin-top: 5px; background: #ff3b3b; color: white; border: none; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; cursor: pointer; }
        .file-center { text-align: center; margin-top: 15px; }
        
        /* Ajuste para 3 botões na mesma linha */
        .botoes { display: flex; justify-content: space-between; margin-top: 25px; gap: 10px; }
        .btn { width: 31%; padding: 12px; border-radius: 30px; font-size: 0.9rem; font-weight: 500; border: none; cursor: pointer; transition: 0.3s; }
        .btn-dark { background: #000; color: #fff; }
        .btn-outline-dark { background: #fff; border: 2px solid #000; color: #000; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 5px; }
        .btn:hover { opacity: 0.8; }
    </style>
</head>
<body>

<section class="section">
    <div class="container">
        <h1 class="titulo">Fashion Style</h1>
        <p class="subtitulo">Criar sua conta</p>

        <?php if (isset($_SESSION['erro'])): ?>
            <p style="color: red; font-weight: bold;"><?= $_SESSION['erro']; unset($_SESSION['erro']); ?></p>
        <?php endif; ?>

        <form class="form-box" id="formCadastro" method="POST" enctype="multipart/form-data" action="processa_cadastro.php">
            <div class="row">
                <div class="col">
                    <label>Nome completo</label>
                    <input type="text" class="form-control" name="nome" placeholder="Digite seu nome" required>
                </div>
                <div class="col">
                    <label>Nome de usuário</label>
                    <input type="text" class="form-control" name="nome_usuario" placeholder="Digite seu usuário" required>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label>E-mail</label>
                    <input type="email" class="form-control" name="email" placeholder="Digite seu e-mail" required>
                </div>
                <div class="col senha-box">
                    <label>Senha</label>
                    <input type="password" id="senha" class="form-control" name="senha" placeholder="Digite sua senha" required minlength="6">
                    <i class="bi bi-eye-slash icon-eye" id="toggleSenha"></i>
                </div>
            </div>

            <div class="row">
                <div class="col-full">
                    <label>CPF</label>
                    <input type="text" class="form-control" name="cpf" id="cpf" placeholder="000.000.000-00" required>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label>Telefone</label>
                    <input type="text" class="form-control" name="telefone" id="telefone" placeholder="(00) 00000-0000">
                </div>
                <div class="col">
                    <label>Data de Nascimento</label>
                    <input type="date" id="nasc" class="form-control" name="data_nascimento" required>
                </div>
            </div>

            <img id="preview" class="preview-img" src="assets/img/default_profile.png">
            <button type="button" id="delFoto" class="btn-delete-photo">Remover foto</button>

            <div class="file-center">
                <label><strong>Foto de Perfil (Opcional)</strong></label><br>
                <input type="file" class="file-center" id="foto" name="foto" accept="image/*">
            </div>

            <div class="botoes">
                <a href="index.php" class="btn btn-outline-dark"><i class="bi bi-arrow-left"></i> Voltar</a>
                <button type="submit" class="btn btn-dark">Cadastrar</button>
                <a href="login.php" class="btn btn-outline-dark">Login</a>
            </div>
        </form>
    </div>
</section>

<script>
    // --- LÓGICA DE DATA MÁXIMA ---
    const campoData = document.getElementById('nasc');
    const hoje = new Date().toISOString().split("T")[0]; 
    campoData.setAttribute('max', hoje); 

    // Monitoramento de campos para erro visual
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.required && this.value === "") {
                this.classList.add('campo-erro');
            } else {
                this.classList.remove('campo-erro');
            }
        });

        input.addEventListener('input', function() {
            this.classList.remove('campo-erro');
        });
    });

    // Validação no envio do formulário
    document.getElementById('formCadastro').onsubmit = function(e) {
        let erro = false;

        if (campoData.value === "") {
            campoData.classList.add('campo-erro');
            alert("Por favor, preencha a data de nascimento.");
            erro = true;
        } 
        else if (campoData.value > hoje) {
            campoData.classList.add('campo-erro');
            alert("A data de nascimento não pode ser maior que hoje.");
            erro = true;
        }

        if (erro) {
            e.preventDefault();
            return false;
        }
    };

    // Toggle de visualização da senha
    document.getElementById("toggleSenha").addEventListener("click", function() {
        const input = document.getElementById("senha");
        input.type = input.type === "password" ? "text" : "password";
        this.classList.toggle("bi-eye");
        this.classList.toggle("bi-eye-slash");
    });

    // Máscara de CPF
    document.getElementById('cpf').addEventListener('input', function (e) {
        let v = e.target.value.replace(/\D/g, '');
        v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        e.target.value = v.substring(0, 14);
    });

    // Preview de Imagem
    document.getElementById("foto").addEventListener("change", function(e) {
        if (this.files[0]) document.getElementById("preview").src = URL.createObjectURL(this.files[0]);
    });

    // Remover Foto
    document.getElementById("delFoto").addEventListener("click", function() {
        document.getElementById("preview").src = "assets/img/default_profile.png";
        document.getElementById("foto").value = "";
    });
</script>
</body>
</html>