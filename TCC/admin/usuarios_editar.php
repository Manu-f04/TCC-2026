<?php 
require_once("verificar_admin.php"); 
require_once("../conexao.php");

$pagina_titulo = "Editar Usuário";
$pagina_ativa = "usuarios";

if (!isset($_GET['id'])) {
    header("Location: usuarios_lista.php");
    exit();
}

$id = intval($_GET['id']);
$stmt = $con->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) { die("Usuário não encontrado."); }

$foto_atual = (!empty($user['foto']) && file_exists("../assets/img/usuarios/" . $user['foto']))
    ? $user['foto']
    : "default.jpg";

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $nome_usuario = trim($_POST['nome_usuario']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $cpf = $_POST['cpf'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $data_nascimento = $_POST['data_nascimento'] ?? null;
    $nivel_acesso = $_POST['nivel_acesso'];
    
    // Lógica para manter a foto atual ou definir como vazio/padrão
    $foto_nome = $user['foto'];

    // Se o usuário clicou para remover a foto (via campo hidden preenchido pelo JS)
    if (isset($_POST['remover_foto']) && $_POST['remover_foto'] === 'sim') {
        $foto_nome = ""; // Limpa o banco de dados
    }

    if (!$email) {
        $mensagem = "erro";
    } else {
        // Se houver upload de nova foto
        if (!empty($_FILES['foto']['name'])) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $foto_nome = uniqid() . "." . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], "../assets/img/usuarios/" . $foto_nome);
            }
        }

        if (!empty($_POST['senha'])) {
            $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nome=?, nome_usuario=?, email=?, cpf=?, senha=?, telefone=?, data_nascimento=?, nivel_acesso=?, foto=? WHERE id=?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("sssssssssi", $nome, $nome_usuario, $email, $cpf, $senha, $telefone, $data_nascimento, $nivel_acesso, $foto_nome, $id);
        } else {
            $sql = "UPDATE usuarios SET nome=?, nome_usuario=?, email=?, cpf=?, telefone=?, data_nascimento=?, nivel_acesso=?, foto=? WHERE id=?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("ssssssssi", $nome, $nome_usuario, $email, $cpf, $telefone, $data_nascimento, $nivel_acesso, $foto_nome, $id);
        }

        $mensagem = $stmt->execute() ? "sucesso" : "erro";
    }
}

require_once("header_admin.php"); 
?>

<div class="mb-4">
    <a href="usuarios_lista.php" class="btn btn-light btn-sm mb-3"><i class="bi bi-arrow-left"></i> Voltar</a>
    <h1 class="fw-bold h3">Editar Perfil do Usuário</h1>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white">
    <div class="card-body p-4">
        <form id="formUsuario" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="remover_foto" id="inputRemoverFoto" value="nao">
            
            <div class="row g-4">
                <div class="col-md-4 text-center border-end">
                    <label class="form-label fw-bold d-block text-start">Foto de Perfil</label>
                    <img id="preview" src="../assets/img/usuarios/<?= $foto_atual ?>" class="rounded-circle border shadow-sm mb-3" style="width: 180px; height: 180px; object-fit: cover;">
                    
                    <div class="px-3">
                        <input type="file" name="foto" id="inputFoto" class="form-control form-control-sm mb-2" onchange="previewImagem(event)">
                        
                        <?php if($foto_atual != "default.jpg"): ?>
                        <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removerFoto()">
                            <i class="bi bi-trash"></i> Remover Foto Atual
                        </button>
                        <?php endif; ?>
                        
                        <small class="text-muted d-block mt-2">Formatos: JPG, PNG ou WebP</small>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nome Completo</label>
                            <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($user['nome']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Usuário (@)</label>
                            <input type="text" name="nome_usuario" class="form-control" value="<?= htmlspecialchars($user['nome_usuario']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">E-mail</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">CPF</label>
                            <input type="text" name="cpf" class="form-control" value="<?= htmlspecialchars($user['cpf']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nível de Acesso</label>
                            <select name="nivel_acesso" class="form-select">
                                <option value="usuario" <?= $user['nivel_acesso'] == 'usuario' ? 'selected' : '' ?>>Usuário Comum</option>
                                <option value="admin" <?= $user['nivel_acesso'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-danger">Nova Senha (opcional)</label>
                            <input type="password" name="senha" class="form-control" placeholder="Deixe em branco para manter">
                        </div>
                    </div>
                    <div class="mt-5 pt-3 border-top text-end">
                        <button type="submit" class="btn btn-dark px-5 py-2 rounded-3 shadow-sm">
                            <i class="bi bi-check-lg"></i> Salvar Alterações
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Função de Preview de imagem
function previewImagem(event) {
    const input = event.target;
    const preview = document.getElementById('preview');
    if (input.files && input.files[0]) {
        preview.src = URL.createObjectURL(input.files[0]);
        // Se selecionar nova foto, cancela a intenção de remover a anterior
        document.getElementById('inputRemoverFoto').value = 'nao';
    }
}

// Função para remover a foto (visual e lógica)
function removerFoto() {
    Swal.fire({
        title: 'Remover foto?',
        text: "A imagem será excluída ao salvar as alterações.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, remover',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('preview').src = '../assets/img/usuarios/default.jpg';
            document.getElementById('inputRemoverFoto').value = 'sim';
            document.getElementById('inputFoto').value = ''; // Limpa o input file
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    <?php if ($mensagem == "sucesso"): ?>
        Swal.fire({
            title: 'Sucesso!',
            text: 'As informações do usuário foram atualizadas.',
            icon: 'success',
            confirmButtonColor: '#212529'
        }).then(() => { window.location.href = "usuarios_lista.php"; });
    <?php elseif ($mensagem == "erro"): ?>
        Swal.fire({ 
            title: 'Erro!', 
            text: 'Não foi possível salvar os dados. Verifique os campos.', 
            icon: 'error', 
            confirmButtonColor: '#212529' 
        });
    <?php endif; ?>
});
</script>

<?php require_once("footer_admin.php"); ?>