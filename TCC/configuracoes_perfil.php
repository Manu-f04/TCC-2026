<?php
//  INÍCIO DA SESSÃO
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// INCLUIR CONEXÃO COM O BANCO DE DADOS
require_once 'conexao.php'; // Fornece $con (MySQLi)

//  VERIFICAR LOGIN E REDIRECIONAR
if (!isset($_SESSION['idusuario'])) {
    header('Location: login.php');
    exit();
}

$id_usuario_logado = $_SESSION['idusuario'];
$usuario = null;

try {
    $sql = "SELECT nome, nome_usuario, email, telefone, data_nascimento, foto 
            FROM usuarios WHERE id = ?";
            
    if ($stmt = $con->prepare($sql)) {
        $stmt->bind_param("i", $id_usuario_logado); 
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc(); 
        $stmt->close();
    } else {
        throw new Exception("Falha na preparação da query: " . $con->error);
    }

    if (!$usuario) {
        session_destroy();
        header('Location: login.php?msg=Usuário não encontrado.');
        exit();
    }
} catch (Exception $e) {
    die("Erro ao carregar perfil: " . htmlspecialchars($e->getMessage()));
}

// Formatação de Data por Extenso
$data_formatada = 'Não informada';
if (!empty($usuario['data_nascimento'])) {
    try {
        $data_obj = new DateTime($usuario['data_nascimento']);
        $meses = [
            '01' => 'janeiro', '02' => 'fevereiro', '03' => 'março', '04' => 'abril',
            '05' => 'maio', '06' => 'junho', '07' => 'julho', '08' => 'agosto',
            '09' => 'setembro', '10' => 'outubro', '11' => 'novembro', '12' => 'dezembro'
        ];
        $dia = $data_obj->format('d');
        $mes = $data_obj->format('m');
        $ano = $data_obj->format('Y');
        $data_formatada = "{$dia} de {$meses[$mes]} de {$ano}";
    } catch (Exception $e) { }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Meu Perfil</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <style>
    .profile-photo {
      width: 150px; height: 150px;
      object-fit: cover;
      border-radius: 50%;
      border: 5px solid #fff;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    .profile-header { background-color: #f8f9fa; padding: 50px 0; text-align: center; }
    
    .alert-perfil {
        border-radius: 15px;
        margin-bottom: 25px;
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .btn-perfil-action {
        min-width: 220px;
    }
  </style>
</head>
<body>
  <?php include 'nav.php'; ?>

  <main class="main">
    <div class="profile-header">
      <div class="container">
        <?php 
          $fotoExibir = 'assets/img/default_profile.png'; 
          // Ajuste fino na validação do caminho físico do upload
          if (!empty($usuario['foto']) && (file_exists($usuario['foto']) || file_exists(__DIR__ . '/' . $usuario['foto']))) {
              $fotoExibir = $usuario['foto'];
          }
        ?>
        <img src="<?= htmlspecialchars($fotoExibir) ?>?t=<?= time() ?>" alt="Foto de Perfil" class="profile-photo">
        <h2 class="mt-3"><?= htmlspecialchars($usuario['nome']) ?></h2>
        <p class="text-muted">@<?= htmlspecialchars($usuario['nome_usuario']) ?></p>
      </div>
    </div>

    <section class="section py-5">
      <div class="container">
        
        <?php if (isset($_SESSION['sucesso_perfil'])): ?>
          <div class="alert alert-success alert-dismissible fade show alert-perfil" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= $_SESSION['sucesso_perfil'] ?>
            <?php unset($_SESSION['sucesso_perfil']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <div class="card shadow-sm p-4" style="border-radius: 20px; border: none;">
          <h4 class="mb-4 pb-2 border-bottom">Informações de Contato</h4>
          <div class="row g-4 mb-5">
            <div class="col-md-6">
              <strong class="text-muted">Nome completo</strong>
              <p class="mb-0"><?= htmlspecialchars($usuario['nome']) ?></p>
            </div>
            <div class="col-md-6">
              <strong class="text-muted">E-mail</strong>
              <p class="mb-0"><?= htmlspecialchars($usuario['email']) ?></p>
            </div>
            <div class="col-md-6">
              <strong class="text-muted">Nome de Usuário</strong>
              <p class="mb-0">@<?= htmlspecialchars($usuario['nome_usuario']) ?></p>
            </div>
            <div class="col-md-6">
              <strong class="text-muted">Telefone</strong>
              <p class="mb-0"><?= !empty($usuario['telefone']) ? htmlspecialchars($usuario['telefone']) : 'Não informado' ?></p>
            </div>
            <div class="col-md-6">
              <strong class="text-muted">Data de Nascimento</strong>
              <p class="mb-0"><?= $data_formatada ?></p>
            </div>
          </div>

          <h4 class="mb-4 pb-2 border-bottom text-center">Gerenciar Conta</h4>
          
          <div class="d-grid gap-3 d-md-flex justify-content-center mt-3">
            <a href="editar_perfil.php" class="btn btn-dark rounded-pill px-5 py-3 btn-perfil-action">
                Editar perfil
            </a>
            <button type="button" class="btn btn-outline-dark rounded-pill px-5 py-3 btn-perfil-action" data-bs-toggle="modal" data-bs-target="#modalDeletarConta">
                Deletar Conta
            </button>
          </div>
        </div>
      </div>
    </section>
  </main>

  <div class="modal fade" id="modalDeletarConta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold w-100 text-center">Confirmar Exclusão</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center p-4">
          <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
          <p class="mt-3"><strong>Atenção:</strong> Você está prestes a eliminar sua conta permanentemente. Todos os seus dados serão perdidos.</p>
        </div>
        <div class="modal-footer border-0 d-flex justify-content-center pb-4 gap-2">
          <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <a href="deletar_conta.php" class="btn btn-danger rounded-pill px-4">Sim, Eliminar Minha Conta</a>
        </div>
      </div>
    </div>
  </div>

  <?php include 'footer.php'; ?>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>