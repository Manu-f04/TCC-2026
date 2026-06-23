<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>FashionStyle</title>
  
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  
  <style>
    /* Definição de variáveis de cores para os fundos das categorias laterais */
    :root {
      --bg-men: #e3f2fd;
      --bg-kids: #fff3e0;
      --bg-beauty: #fce4ec;
      --bg-acc: #e8f5e9;
    }

    /* Estilização do Card de Destaque (Tendências Verão) */
    .promo-cards .category-featured {
      background: #fff;
      border-radius: 20px;
      overflow: hidden;
      display: flex;
      flex-direction: row;
      height: 350px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.11);
    }

    .promo-cards .featured-text-side {
      flex: 1;
      padding: 30px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      z-index: 2;
    }

    .promo-cards .featured-image-side {
      flex: 1;
    }

    .promo-cards .featured-image-side img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    
    .btn-amendoado {
      background-color: #000000ff;
      color: #fff;
      padding: 10px 25px;
      border-radius: 12px;
      text-decoration: none;
      display: inline-block;
      font-weight: 500;
      transition: 0.3s;
      border: none;
      margin-top: 15px;
      width: fit-content;
    }

    .btn-amendoado:hover {
      background-color: #333; 
      color: #fff;
    }

    /* Configuração dos Cards Laterais de Categorias */
    .promo-cards .category-card {
      border-radius: 20px;
      overflow: hidden;
      display: flex;
      flex-direction: row;
      height: 120px;
      transition: all 0.3s ease;
      cursor: pointer;
      border: none;
      margin-bottom: 12px;
    }

    /* Efeito de movimento ao passar o mouse nos cards laterais */
    .promo-cards .category-card:hover {
      transform: translateX(5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    .promo-cards .category-content {
      flex: 1.2;
      padding: 15px 20px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .promo-cards .category-image {
      flex: 1;
    }

    .promo-cards .category-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    /* Classes auxiliares para as cores de fundo de cada categoria */
    .cat-men { background-color: var(--bg-men) !important; }
    .cat-kids { background-color: var(--bg-kids) !important; }
    .cat-cosmetics { background-color: var(--bg-beauty) !important; }
    .cat-accessories { background-color: var(--bg-acc) !important; }

    h4 { font-size: 1.1rem; font-weight: 700; margin-bottom: 2px; color: rgb(255, 255, 255); }
    .small-text { font-size: 0.85rem; color: #000000ff; margin: 0; line-height: 1.2; }
    .modal-link { font-size: 0.8rem; font-weight: 600; text-decoration: none; color: #000000ff; margin-top: 5px; }

    /* Estilização dos Modais (Janelas Pop-up) */
    .modal-content { border-radius: 25px; border: none; }
    .modal-img-top { width: 100%; height: 250px; object-fit: cover; border-radius: 25px 25px 0 0; }
  </style>
</head>

<body class="index-page">

<?php 
// Inicia a sessão para verificar se o usuário está autenticado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'nav.php'; // Inclui o cabeçalho/navegação padrão

// Verifica se existe um ID de usuário na sessão para decidir o que mostrar (como o botão de cadastro)
$usuarioLogado = isset($_SESSION['idusuario']);
?> 

<main class="main">
  <section id="promo-cards" class="promo-cards section mt-5">
    <div class="container" data-aos="fade-up">
      <div class="row gy-4">

        <div class="col-lg-6">
          <div class="category-featured">
            <div class="featured-text-side">
              <h2 class="fw-bold h3">Tendências Verão 2025</h2>
              <p>Para o verão 2025, a moda aposta em cores neutras (bege, marrom Mocha Mousse) e vibrantes (azul, amarelo, laranja).
                 Nos tecidos, dominam os leves e confortáveis, como linho, viscose e crochê.</p>
              
              <?php if (!$usuarioLogado): ?>
                <a href="cadastro.php" class="btn-amendoado">Se interessou? Cadastre-se</a>
              <?php endif; ?>
            </div>
            <div class="featured-image-side">
              <img src="tendenciasverão.png" alt="Verão 2025">
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          
          <div class="category-card cat-men" data-bs-toggle="modal" data-bs-target="#modalMen">
            <div class="category-content">
              <h4>Moda Masculina</h4>
              <p class="small-text">O que está em alta na moda masculina?</p>
              <span class="modal-link">Ver informações <i class="bi bi-arrow-right-short"></i></span>
            </div>
            <div class="category-image">
              <img src="modamasculina.avif">
            </div>
          </div>

          <div class="category-card cat-kids" data-bs-toggle="modal" data-bs-target="#modalKids">
            <div class="category-content">
              <h4>Moda Infantil</h4>
              <p class="small-text">O que está em alta na moda infantil?</p>
              <span class="modal-link">Ver informações <i class="bi bi-arrow-right-short"></i></span>
            </div>
            <div class="category-image">
              <img src="modainfantil.png">
            </div>
          </div>

          <div class="category-card cat-cosmetics" data-bs-toggle="modal" data-bs-target="#modalBeauty">
            <div class="category-content">
              <h4>Produtos de Beleza</h4>
              <p class="small-text">O que está em alta nos produtos de beleza?</p>
              <span class="modal-link">Ver informações <i class="bi bi-arrow-right-short"></i></span>
            </div>
            <div class="category-image">
              <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?q=80&w=400">
            </div>
          </div>

          <div class="category-card cat-accessories" data-bs-toggle="modal" data-bs-target="#modalAcc">
            <div class="category-content">
              <h4>Acessórios</h4>
              <p class="small-text">O que está em alta nos acessórios?</p>
              <span class="modal-link">Ver informações <i class="bi bi-arrow-right-short"></i></span>
            </div>
            <div class="category-image">
              <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=400">
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>
</main>

<div class="modal fade" id="modalMen" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <img src="modamasculina.avif" class="modal-img-top">
      <div class="modal-body p-4">
        <h3 class="fw-bold">Tendências da Moda Masculina</h3>
        <p class="text-muted">O foco da temporada é o conforto autêntico e a sustentabilidade. A tendência une clássicos renovados a uma alfaiataria casual em tons terrosos.</p>
        <button type="button" class="btn btn-dark rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalKids" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <img src="modainfantil.png" class="modal-img-top">
      <div class="modal-body p-4">
        <h3 class="fw-bold">Tendências da Moda Infantil</h3>
        <p class="text-muted">A temporada celebra a liberdade de movimento com muita criatividade. O destaque fica para o mix de cores vibrantes com tons terrosos naturais.</p>
        <button type="button" class="btn btn-dark rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalBeauty" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?q=80&w=800" class="modal-img-top">
      <div class="modal-body p-4">
        <h3 class="fw-bold">Tendências em Produtos de Beleza</h3>
        <p class="text-muted">A era da "Quiet Beauty" prioriza a beleza autêntica e natural, com maquiagens que tratam a pele (Skinificação) enquanto realçam o visual.</p>
        <button type="button" class="btn btn-dark rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAcc" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=800" class="modal-img-top">
      <div class="modal-body p-4">
        <h3 class="fw-bold">Tendências em Acessórios</h3>
        <p class="text-muted">O ano é do Maximalismo. Peças grandes, esculturais e com formatos orgânicos dominam o visual, celebrando a ousadia em cada detalhe.</p>
        <button type="button" class="btn btn-dark rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/aos/aos.js"></script>
<script>AOS.init(); // Inicializa o motor de animação AOS </script>

</body>
</html>