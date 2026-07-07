<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** * Controle de Acesso Visual
 * Verifica a existência da sessão activa para definir a visibilidade de menus restritos.
 */
$logado = isset($_SESSION['idusuario']) && !empty($_SESSION['idusuario']);
$nome = $logado ? ($_SESSION['nomeusuario'] ?? 'Você') : null;
// Nível da sessão para a verificação do botão admin
$nivel = $_SESSION['nivel'] ?? 'usuario'; 

// Verifica se a página atual é a página inicial ou a sobre o site
$page_atual = basename($_SERVER['PHP_SELF']);
$is_public_page = ($page_atual == 'index.php' || $page_atual == 'sobre.php');
?>

<style>
    /* Estilos originais do Header */
    /* REMOVIDO o conflito de max-height e margin-right exagerada daqui para corrigir o afastamento */

    .logo img {
         height: 80px !important;
max-height: 100px;    width: auto;
    }
    .sitename {
        font-size: 34px; /* Aumentado para harmonizar com a nova logo */
        margin-bottom: 0;
    }
    
    /* Estilos do Modal de Logout */
    .modal-logout .modal-content { border-radius: 20px; border: none; box-shadow: 0 15px 50px rgba(0,0,0,0.2); }
    .modal-logout .modal-header { border-bottom: none; padding-top: 30px; }
    .modal-logout .modal-footer { border-top: none; padding-bottom: 30px; justify-content: center; gap: 15px; }
    .modal-logout .logout-icon { font-size: 3.5rem; color: #dc3545; margin-bottom: 15px; }
    .btn-logout-confirm { background-color: #000; color: #fff; border-radius: 50px; padding: 10px 30px; font-weight: 600; text-decoration: none; }
    .btn-logout-cancel { border-radius: 50px; padding: 10px 30px; font-weight: 600; }

    /* --- NOVOS ESTILOS PARA O BOTÃO VOLTAR (ADMIN) --- */
    .navmenu ul {
        display: flex;
        align-items: center;
        width: 100%;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    /* BOTÕES ENTRAR/CADASTRAR EM TAMANHO INTERMEDIÁRIO EQUILIBRADO */
    .nav-direct-buttons .btn {
        padding: 6px 18px !important; 
        font-size: 0.95rem !important;
        font-weight: 600;
    }

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

    .btn-voltar-painel i {
        font-size: 1.2rem;
    }

    /* Ajuste para mobile */
    @media (max-width: 1199px) {
        .navmenu ul {
            display: block;
        }
        .ms-auto {
            margin-left: 0 !important;
            margin-top: 15px;
            display: inline-block;
        }
        .nav-direct-buttons {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
    }
</style>
<header id="header" class="header sticky-top">
    <div class="main-header">
        <div class="container-fluid container-xl">
            <div class="d-flex py-3 align-items-center justify-content-between">

                <a href="index.php" class="logo d-flex align-items-center text-decoration-none">
                    <img src="/manu.Info31/TCC/logo.jpg" alt="Logo FashionStyle">
                    <h1 class="sitename">FashionStyle</h1>
                </a>

                <div class="header-actions d-flex align-items-center justify-content-end">
                    
                    <?php if (!$logado && $is_public_page): ?>
                        <div class="nav-direct-buttons d-flex align-items-center me-2">
                            <a href="login.php" class="btn btn-outline-dark rounded-pill">Entrar</a>
                            <a href="cadastro.php" class="btn btn-dark rounded-pill">Cadastrar</a>
                        </div>
                    <?php else: ?>
                        <div class="dropdown account-dropdown">
                            <button class="header-action-btn" data-bs-toggle="dropdown">
                                <i class="bi bi-person"></i>
                            </button>

                            <div class="dropdown-menu">
                                <div class="dropdown-header">
                                    <?php if ($logado): ?>
                                        <h6>Bem-vindo(a), <?= htmlspecialchars($nome) ?></h6>
                                        <p class="mb-0">Informações do seu perfil</p>
                                    <?php else: ?>
                                        <h6>Acesso à <span class="sitename">FashionStyle</span></h6>
                                        <p class="mb-0">Faça login ou cadastre-se</p>
                                    <?php endif; ?>
                                </div>

                                <div class="dropdown-body">
                                    <?php if ($logado): ?>
                                        <a class="dropdown-item d-flex align-items-center" href="configuracoes_perfil.php">
                                            <i class="bi bi-person-circle me-2"></i>
                                            <span>Meu perfil</span>
                                        </a>
                                    <?php else: ?>
                                        <a class="dropdown-item d-flex align-items-center" href="cadastro.php">
                                            <i class="bi bi-person-add me-2"></i>
                                            <span>Cadastre-se</span>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <div class="dropdown-footer">
                                    <?php if ($logado): ?>
                                        <button type="button" 
                                                class="btn btn-outline-dark w-100 rounded-pill" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalConfirmarSaida">
                                            Sair da conta
                                        </button>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-outline-dark w-100 rounded-pill">Entrar na conta</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <i class="mobile-nav-toggle d-xl-none bi bi-list me-0"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="header-nav">
        <div class="container-fluid container-xl position-relative">
            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="index.php">Página Inicial</a></li>
                    <li><a href="sobre.php">Sobre o site</a></li>

                    <?php if ($logado): ?>
                        <li><a href="guardaroupa.php">Guarda-roupa</a></li>
                        <li><a href="looks.php">Looks</a></li>
                        <li><a href="comunidade/comunidade.php">Comunidade</a></li>
                        
                        <?php if ($nivel === 'admin'): ?>
                            <li class="ms-auto">
                                <a href="admin/dashboard.php" class="btn-voltar-painel">
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