<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tenta incluir a conexão com o banco se ela não tiver sido iniciada antes
if (!isset($con)) {
    if (file_exists(__DIR__ . '/conexao.php')) {
        include_once __DIR__ . '/conexao.php';
    } elseif (file_exists(__DIR__ . '/../conexao.php')) {
        include_once __DIR__ . '/../conexao.php';
    }
}

// Verificação de login
$logado = isset($_SESSION['idusuario']) && !empty($_SESSION['idusuario']);

// Variáveis Padrão
$fotoUsuarioNav = '/manu.Info31/TCC/assets/img/default_profile.png';
$nomeExibicaoNav = $_SESSION['nomeusuario'] ?? $_SESSION['nome'] ?? 'Usuário';
$nivel = $_SESSION['nivel'] ?? 'usuario'; 

if ($logado) {
    $id_nav = $_SESSION['idusuario'];
    
    // Se a conexão com o banco existir, consulta os dados atualizados
    if (isset($con) && $con) {
        $sqlNav = "SELECT nome, nome_usuario, foto, nivel_acesso FROM usuarios WHERE id = ? LIMIT 1";
        if ($stmtNav = $con->prepare($sqlNav)) {
            $stmtNav->bind_param("i", $id_nav);
            $stmtNav->execute();
            $resNav = $stmtNav->get_result();
            
            if ($userNav = $resNav->fetch_assoc()) {
                $nivel = $userNav['nivel_acesso'];
                $_SESSION['nivel'] = $nivel;

                // Força o nome para 'Administrador' se for admin
                if ($nivel === 'admin') {
                    $nomeExibicaoNav = 'Admin';
                } else {
                    $nomeExibicaoNav = !empty($userNav['nome']) ? $userNav['nome'] : $userNav['nome_usuario'];
                }

                // Ajusta o caminho da foto de perfil
                if (!empty($userNav['foto'])) {
                    $caminhoFoto = $userNav['foto'];
                    if (file_exists(__DIR__ . '/' . $caminhoFoto) || file_exists($caminhoFoto)) {
                        // Garante barra no início para buscar na raiz do projeto
                        $fotoUsuarioNav = (strpos($caminhoFoto, '/') === 0 ? '' : '/manu.Info31/TCC/') . $caminhoFoto;
                    }
                }
            }
            $stmtNav->close();
        }
    } else {
        // Fallback: se o banco não estiver conectado, valida pelo nível que já está na Sessão
        if ($nivel === 'admin') {
            $nomeExibicaoNav = 'Admin';
        }
    }
}

// Página atual
$page_atual = basename($_SERVER['PHP_SELF']);
$is_public_page = ($page_atual == 'index.php' || $page_atual == 'sobre.php');
?>

<style>
    .logo img {
        height: 80px !important;
        max-height: 100px;
        width: auto;
    }
    .sitename {
        font-size: 34px;
        margin-bottom: 0;
    }

    /* ESTILOS FIÉIS AO CANVA */
    .user-profile-widget {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    /* Texto Administrador / Nome maior */
    .user-profile-name {
        font-size: 1.5rem; 
        font-weight: 400;
        color: #111;
        margin: 0;
        white-space: nowrap;
    }

    /* Foto de Perfil Redonda (Sem Clique) */
    .user-profile-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        cursor: default;
        user-select: none;
    }

    /* Botão do Ícone Menu (Lado direito) */
    .header-action-btn-img {
        background: transparent;
        border: none;
        padding: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease;
    }

    .header-action-btn-img:hover {
        transform: scale(1.05);
    }

    .header-action-btn-img img {
        height: 47.2px;
        margin-left: -2vh;
        object-fit: contain;
    }

    /* Dropdown no formato de card limpo */
    .account-dropdown .dropdown-menu {
        min-width: 260px;
        border-radius: 14px;
        border: 1px solid rgba(0,0,0,0.08);
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        padding: 15px 0;
        margin-top: 12px !important;
    }

    .account-dropdown .dropdown-header {
        padding: 0 20px 12px 20px;
    }

    .account-dropdown .dropdown-header h6 {
        font-weight: 700;
        color: #111;
        margin-bottom: 2px;
        font-size: 0.95rem;
    }

    .account-dropdown .dropdown-header p {
        font-size: 0.82rem;
        color: #777;
    }

    .account-dropdown .dropdown-item {
        padding: 10px 20px;
        font-weight: 500;
        color: #333;
    }

    .account-dropdown .dropdown-item:hover {
        background-color: #f5f5f5;
    }

    .btn-sair-custom {
        background-color: #1f1f1f;
        color: #fff;
        font-weight: 600;
        border: none;
        padding: 10px;
        border-radius: 50px;
        transition: background-color 0.2s;
    }

    .btn-sair-custom:hover {
        background-color: #000;
        color: #fff;
    }

    /* Modal Logout */
    .modal-logout .modal-content { border-radius: 20px; border: none; box-shadow: 0 15px 50px rgba(0,0,0,0.2); }
    .modal-logout .modal-header { border-bottom: none; padding-top: 30px; }
    .modal-logout .modal-footer { border-top: none; padding-bottom: 30px; justify-content: center; gap: 15px; }
    .modal-logout .logout-icon { font-size: 3.5rem; color: #dc3545; margin-bottom: 15px; }
    .btn-logout-confirm { background-color: #000; color: #fff; border-radius: 50px; padding: 10px 30px; font-weight: 600; text-decoration: none; }
    .btn-logout-cancel { border-radius: 50px; padding: 10px 30px; font-weight: 600; }

    /* Botão Voltar ao Painel */
    .btn-voltar-painel {
        color: #444 !important;
        font-weight: 700 !important;
        background: #f8f9fa;
        padding: 8px 18px !important;
        border-radius: 50px;
        border: 1px solid #ddd;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        text-decoration: none;
    }

    .btn-voltar-painel:hover {
        background: #000 !important;
        color: #fff !important;
        border-color: #000;
        transform: translateY(-2px);
    }
</style>

<header id="header" class="header sticky-top">
    <div class="main-header">
        <div class="container-fluid container-xl">
            <div class="d-flex py-3 align-items-center justify-content-between">

                <!-- Logo -->
                <a href="index.php" class="logo d-flex align-items-center text-decoration-none">
                    <img src="/manu.Info31/TCC/logo.jpg" alt="Logo FashionStyle">
                    <h1 class="sitename">FashionStyle</h1>
                </a>

                <div class="header-actions d-flex align-items-center justify-content-end">
                    
                    <?php if (!$logado && $is_public_page): ?>
                        <div class="nav-direct-buttons d-flex align-items-center me-2">
                            <a href="login.php" class="btn btn-outline-dark rounded-pill me-2">Entrar</a>
                            <a href="cadastro.php" class="btn btn-dark rounded-pill">Cadastrar</a>
                        </div>
                    <?php elseif ($logado): ?>
                        
                        <!-- WIDGET DO USUÁRIO (TEXTO + FOTO + ÍCONE) -->
                        <div class="user-profile-widget">
                            
                            <!-- 1. Nome/Administrador -->
                            <span class="user-profile-name">
                                <?= htmlspecialchars($nomeExibicaoNav) ?>
                            </span>

                            <!-- 2. Foto de Perfil (Não Clicável) -->
                            <img src="<?= htmlspecialchars($fotoUsuarioNav) ?>?t=<?= time() ?>" 
                                 alt="Foto de Perfil" 
                                 class="user-profile-avatar"
                                 onerror="this.src='/manu.Info31/TCC/assets/img/default_profile.png';">

                            <!-- 3. Ícone Clicável (Abre o Dropdown) -->
                            <div class="dropdown account-dropdown">
                                <button class="header-action-btn-img" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Menu do Usuário">
                                    <img src="/manu.Info31/TCC/assets/img/IconMenu2.PNG" alt="Menu Icon" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                                    <i class="bi bi-grid-3x3-gap-fill fs-3" style="display: none;"></i>
                                </button>

                                <!-- Menu Dropdown -->
                                <div class="dropdown-menu dropdown-menu-end shadow">
                                    <div class="dropdown-header border-bottom pb-2">
                                        <h6>Bem-vindo(a), <?= htmlspecialchars($_SESSION['nomeusuario'] ?? $_SESSION['nome'] ?? 'Usuário') ?></h6>
                                        <p class="mb-0">Informações do seu perfil</p>
                                    </div>

                                    <div class="dropdown-body py-1">
                                        <a class="dropdown-item d-flex align-items-center" href="configuracoes_perfil.php">
                                            <i class="bi bi-person-circle me-2"></i>
                                            <span>Meu perfil</span>
                                        </a>
                                    </div>

                                    <div class="dropdown-footer border-top pt-2">
                                        <button type="button" 
                                                class="btn btn-sair-custom w-100" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalConfirmarSaida">
                                            Sair da conta
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>

                    <?php endif; ?>

                    <i class="mobile-nav-toggle d-xl-none bi bi-list me-0 ms-2"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegação -->
    <div class="header-nav">
        <div class="container-fluid container-xl position-relative">
            <nav id="navmenu" class="navmenu">
                <ul class="d-flex align-items-center m-0 p-0 list-unstyled">
                    <li><a href="index.php">Página Inicial</a></li>
                    <li><a href="sobre.php">Sobre o site</a></li>

                    <?php if ($logado): ?>
                        <li><a href="guardaroupa.php">Guarda-roupa</a></li>
                        <li><a href="looks.php">Looks</a></li>
                        <li><a href="comunidade/comunidade.php">Comunidade</a></li>
                        
                        <?php if ($nivel === 'admin'): ?>
                            <li class="ms-auto">
                                <a href="/manu.Info31/TCC/admin/dashboard.php" class="btn-voltar-painel">
                                    <i class="bi bi-arrow-left-short me-1"></i> Voltar ao Painel
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</header>

<!-- Modal de Logout -->
<?php if ($logado): ?>
<div class="modal fade modal-logout" id="modalConfirmarSaida" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-header d-flex justify-content-center">
                <div class="logout-icon">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
            </div>
            <div class="modal-body pt-0">
                <h4 class="fw-bold">Até logo!</h4>
                <p class="text-muted">Você tem certeza que deseja sair da sua conta?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-logout-cancel" data-bs-dismiss="modal">Ficar</button>
                <a href="/manu.Info31/TCC/logout.php" class="btn btn-logout-confirm">Sair agora</a>            
            </div>
        </div>
    </div>
</div>
<?php endif; ?>