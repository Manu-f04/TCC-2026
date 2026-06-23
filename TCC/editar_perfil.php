<?php
session_start();
require_once 'conexao.php'; 

// Verifica se existe um usuário logado, caso contrário redireciona para o login
if (!isset($_SESSION['idusuario'])) {
    header('Location: login.php'); 
    exit();
}

$id_usuario_logado = $_SESSION['idusuario'];
$mensagem_erro = '';

// LÓGICA PARA REMOÇÃO DE FOTO DE PERFIL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deletar_foto'])) {
    $stmt_old = $con->prepare("SELECT foto FROM usuarios WHERE id = ?");
    $stmt_old->bind_param("i", $id_usuario_logado);
    $stmt_old->execute();
    $foto_atual = $stmt_old->get_result()->fetch_assoc()['foto'];
    
    if ($foto_atual && file_exists($foto_atual)) { @unlink($foto_atual); }
    if ($foto_atual && file_exists(__DIR__ . '/' . $foto_atual)) { @unlink(__DIR__ . '/' . $foto_atual); }
    
    $stmt_del = $con->prepare("UPDATE usuarios SET foto = NULL WHERE id = ?");
    $stmt_del->bind_param("i", $id_usuario_logado);
    if ($stmt_del->execute()) {
        $_SESSION['sucesso_perfil'] = "Foto removida com sucesso!";
        header('Location: configuracoes_perfil.php');
        exit();
    }
}

// LÓGICA PARA ATUALIZAÇÃO DOS DADOS DO PERFIL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['deletar_foto'])) {
    $nome = trim($_POST['nome'] ?? '');
    $nome_usuario = trim($_POST['nome_usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $data_nascimento = $_POST['data_nascimento'] ?? NULL;

    $hoje_php = date('Y-m-d');
    if (!empty($data_nascimento) && $data_nascimento > $hoje_php) {
        $mensagem_erro = "A data de nascimento não pode ser uma data futura.";
    }

    if (empty($mensagem_erro)) {
        $caminho_foto = NULL;
        $foto_enviada = false;
        
        // Validação aprofundada do arquivo de upload
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $foto_enviada = true;
            $extensao = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
            
            // Extensões permitidas
            $extensoes_validas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($extensao, $extensoes_validas)) {
                $caminho_foto = 'uploads/perfis/' . $id_usuario_logado . '_' . time() . '.' . $extensao;
                
                // Força a criação da pasta com permissão máxima de escrita caso não exista
                if (!is_dir(__DIR__ . '/uploads/perfis/')) {
                    mkdir(__DIR__ . '/uploads/perfis/', 0777, true);
                }
                
                // Tenta mover o arquivo temporário para o destino final absoluto
                if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], __DIR__ . '/' . $caminho_foto)) {
                    $stmt_old = $con->prepare("SELECT foto FROM usuarios WHERE id = ?");
                    $stmt_old->bind_param("i", $id_usuario_logado);
                    $stmt_old->execute();
                    $foto_antiga = $stmt_old->get_result()->fetch_assoc()['foto'];
                    
                    if ($foto_antiga && file_exists(__DIR__ . '/' . $foto_antiga)) {
                        @unlink(__DIR__ . '/' . $foto_antiga);
                    }
                } else {
                    $mensagem_erro = "Erro do Servidor: Não foi possível gravar o arquivo na pasta 'uploads/perfis/'. Verifique as permissões de gravação do diretório.";
                    $foto_enviada = false;
                }
            } else {
                $mensagem_erro = "Extensão de arquivo inválida. Por favor, envie uma foto JPG, JPEG, PNG, GIF ou WEBP.";
                $foto_enviada = false;
            }
        } elseif (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Captura erros nativos de tamanho do PHP (Ex: upload_max_filesize excedido)
            $code_error = $_FILES['foto_perfil']['error'];
            if ($code_error === UPLOAD_ERR_INI_SIZE || $code_error === UPLOAD_ERR_FORM_SIZE) {
                $mensagem_erro = "A imagem selecionada excede o tamanho máximo permitido pelo servidor.";
            } else {
                $mensagem_erro = "Falha no envio do arquivo físico (Código de Erro PHP: " . $code_error . ").";
            }
        }

        if (empty($mensagem_erro)) {
            $set_clauses = "nome=?, nome_usuario=?, email=?, telefone=?, data_nascimento=?";
            $params = [$nome, $nome_usuario, $email, $telefone, $data_nascimento];
            $tipos = "sssss";

            if ($foto_enviada) {
                $set_clauses .= ", foto=?";
                $params[] = $caminho_foto;
                $tipos .= "s";
            }

            $senha_atual = $_POST['senha_atual'] ?? '';
            $nova_senha = $_POST['nova_senha'] ?? '';
            $confirma = $_POST['confirma_nova_senha'] ?? '';

            if (!empty($senha_atual) || !empty($nova_senha) || !empty($confirma)) {
                if (empty($senha_atual) || empty($nova_senha) || empty($confirma)) {
                    $mensagem_erro = 'Para alterar a senha, deve preencher a Senha Atual, a Nova Senha e a Confirmação.';
                } elseif ($nova_senha !== $confirma) {
                    $mensagem_erro = 'A nova senha e a confirmação de senha não coincidem.';
                } else {
                    $stmt_pw = $con->prepare("SELECT senha FROM usuarios WHERE id = ?");
                    $stmt_pw->bind_param("i", $id_usuario_logado);
                    $stmt_pw->execute();
                    $resultado_pw = $stmt_pw->get_result()->fetch_assoc();
                    $pw_db = isset($resultado_pw['senha']) ? $resultado_pw['senha'] : '';

                    if (password_verify($senha_atual, $pw_db)) {
                        $set_clauses .= ", senha=?";
                        $params[] = password_hash($nova_senha, PASSWORD_DEFAULT);
                        $tipos .= "s";
                    } else {
                        $mensagem_erro = 'A senha atual introduzida está incorreta.';
                    }
                }
            }

            if (empty($mensagem_erro)) {
                $sql = "UPDATE usuarios SET {$set_clauses} WHERE id=?";
                $params[] = $id_usuario_logado;
                $tipos .= "i";
                
                $stmt = $con->prepare($sql);
                $stmt->bind_param($tipos, ...$params);
                
                if ($stmt->execute()) {
                    $_SESSION['nomeusuario'] = $nome;
                    $_SESSION['sucesso_perfil'] = "Perfil atualizado com sucesso!";
                    header('Location: configuracoes_perfil.php');
                    exit();
                } else {
                    $mensagem_erro = "Erro ao salvar as informações no banco de dados: " . $con->error;
                }
            }
        }
    }
}

$stmt_fetch = $con->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt_fetch->bind_param("i", $id_usuario_logado);
$stmt_fetch->execute();
$usuario_form = $stmt_fetch->get_result()->fetch_assoc();

$html_foto   = isset($usuario_form['foto']) ? htmlspecialchars($usuario_form['foto']) : '';
$html_nome   = isset($usuario_form['nome']) ? htmlspecialchars($usuario_form['nome']) : '';
$html_user   = isset($usuario_form['nome_usuario']) ? htmlspecialchars($usuario_form['nome_usuario']) : '';
$html_email  = isset($usuario_form['email']) ? htmlspecialchars($usuario_form['email']) : '';
$html_tel    = isset($usuario_form['telefone']) ? htmlspecialchars($usuario_form['telefone']) : '';
$html_nasc   = isset($usuario_form['data_nascimento']) ? htmlspecialchars($usuario_form['data_nascimento']) : '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet"> 
    <style>
        .edit-section { padding: 60px 0; background: #f9f9f9; }
        .form-box { max-width: 850px; margin: 0 auto; background: #fff; padding: 45px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .row-custom { display: flex; gap: 25px; margin-bottom: 20px; }
        .col-custom { flex: 1; text-align: left; }
        .form-control-custom { height: 48px; width: 100%; border-radius: 10px; border: 1px solid #ddd; padding: 0 15px; transition: 0.3s; }
        .form-control-custom:focus { border-color: #000 !important; outline: none; }
        .profile-img-edit { width: 130px; height: 130px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .btn-save { background: #000; color: #fff; border: none; padding: 12px 35px; border-radius: 50px; font-weight: 600; cursor: pointer; }
        .btn-back { background: transparent; color: #000; border: 2px solid #000; padding: 10px 30px; border-radius: 50px; text-decoration: none; display: inline-block; }
    </style>
</head>
<body class="index-page">
    <?php include 'nav.php'; ?>
    <main class="main">
        <div class="page-title dark-background">
            <div class="container text-center"><h1>Configurações de Perfil</h1></div>
        </div>
        <section class="edit-section">
            <div class="container">
                <div class="form-box">
                    <?php if (!empty($mensagem_erro)): ?> 
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $mensagem_erro ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div> 
                    <?php endif; ?>
                    
                    <form action="editar_perfil.php" id="formEditar" method="POST" enctype="multipart/form-data">
                        <div class="text-center mb-5">
                            <?php 
                              $fotoPreview = 'assets/img/default_profile.png';
                              if (!empty($html_foto) && (file_exists($html_foto) || file_exists(__DIR__ . '/' . $html_foto))) {
                                  $fotoPreview = $html_foto;
                              }
                            ?>
                            <img src="<?= htmlspecialchars($fotoPreview) ?>?t=<?= time() ?>" id="foto_preview" class="profile-img-edit">
                            <div class="mt-3">
                                <label class="btn btn-sm btn-outline-dark rounded-pill" for="foto_perfil">Escolher Foto</label>
                                <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" style="display:none" onchange="preview(this)">
                                <button type="button" id="btn_remover_foto" class="btn btn-sm btn-danger rounded-pill ms-2" style="<?= ($fotoPreview === 'assets/img/default_profile.png') ? 'display:none;' : '' ?>" data-bs-toggle="modal" data-bs-target="#modalDelete">Remover</button>
                            </div>
                        </div>
                        <div class="row-custom">
                            <div class="col-custom"><label class="fw-bold mb-1">Nome Completo</label><input type="text" class="form-control-custom" name="nome" value="<?= $html_nome ?>" required></div>
                            <div class="col-custom"><label class="fw-bold mb-1">Usuário</label><input type="text" class="form-control-custom" name="nome_usuario" value="<?= $html_user ?>" required></div>
                        </div>
                        <div class="row-custom">
                            <div class="col-custom"><label class="fw-bold mb-1">E-mail</label><input type="email" class="form-control-custom" name="email" value="<?= $html_email ?>" required></div>
                            <div class="col-custom"><label class="fw-bold mb-1">Telefone</label><input type="text" class="form-control-custom" name="telefone" value="<?= $html_tel ?>"></div>
                        </div>
                        <div class="row-custom">
                            <div class="col-custom" style="max-width: 48%;">
                                <label class="fw-bold mb-1">Data de Nascimento</label>
                                <input type="date" id="nasc" class="form-control-custom" name="data_nascimento" value="<?= $html_nasc ?>">
                            </div>
                        </div>
                        <hr class="my-5">
                        <h5 class="mb-4">Segurança (Opcional)</h5>
                        <div class="row-custom">
                            <div class="col-custom"><label class="fw-bold mb-1">Senha Atual</label><input type="password" class="form-control-custom" name="senha_atual"></div>
                        </div>
                        <div class="row-custom">
                            <div class="col-custom"><label class="fw-bold mb-1">Nova Senha</label><input type="password" class="form-control-custom" name="nova_senha"></div>
                            <div class="col-custom"><label class="fw-bold mb-1">Confirmar Nova Senha</label><input type="password" class="form-control-custom" name="confirma_nova_senha"></div>
                        </div>
                        <div class="d-flex justify-content-end gap-3 mt-5">
                            <a href="configuracoes_perfil.php" class="btn-back">Voltar ao Perfil</a>
                            <button type="submit" class="btn-save">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <?php include 'footer.php'; ?>

    <div class="modal fade" id="modalDelete" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-4 text-center">
                    <h5>Remover foto?</h5>
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Não</button>
                        <form method="POST" action="editar_perfil.php">
                            <button type="submit" name="deletar_foto" class="btn btn-danger rounded-pill px-4">Sim, remover</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const campoData = document.getElementById('nasc');
        const hoje = new Date().toISOString().split("T")[0];
        if (campoData) { campoData.setAttribute('max', hoje); }

        document.getElementById('formEditar').onsubmit = function(e) {
            if (campoData && campoData.value > hoje) {
                e.preventDefault();
                alert("A data de nascimento não pode ser superior a hoje.");
                campoData.focus();
                return false;
            }
            return true;
        };

        function preview(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) { 
                    document.getElementById('foto_preview').src = e.target.result;
                    document.getElementById('btn_remover_foto').style.display = 'inline-block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>