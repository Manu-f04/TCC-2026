<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexao.php'; // Conexão MySQLi

// Mês atual (1 a 12)
$mesAtual = (int)date('n');

// Busca as tendências cadastradas no banco para o mês vigente
$tendencias = [];
$stmt = $con->prepare("SELECT * FROM tendencias WHERE mes = ?");
$stmt->bind_param("i", $mesAtual);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $tendencias[$row['categoria']] = $row;
}
$stmt->close();

// Função auxiliar com tratativa de imagem e fallbacks
function getTendencia($categoria, $campo, $valorPadrao, $listaTendencias) {
    if (isset($listaTendencias[$categoria][$campo]) && !empty($listaTendencias[$categoria][$campo])) {
        if ($campo === 'imagem') {
            $img = $listaTendencias[$categoria]['imagem'];
            return (strpos($img, 'http') === 0) ? $img : 'uploads/' . $img;
        }
        return htmlspecialchars($listaTendencias[$categoria][$campo]);
    }
    return $valorPadrao;
}

$usuarioLogado = isset($_SESSION['idusuario']);
?> 
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
    /* Card de Destaque Grande (Esquerda) */
    .promo-cards .category-featured {
      background: #fff;
      border-radius: 20px;
      overflow: hidden;
      display: flex;
      flex-direction: row;
      height: 420px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .promo-cards .featured-text-side {
      flex: 1.2;
      padding: 35px;
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
      object-position: center;
    }

    .btn-amendoado {
      background-color: #000;
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

    /* Estrutura Flexbox para expandir a coluna da direita */
    .promo-cards .row.gy-4 > .col-lg-6 {
      display: flex;
      flex-direction: column;
    }

    /* Empurra os 4 cards mais para a direita criando um recuo */
    @media (min-width: 992px) {
      .promo-cards .col-grid-direita {
        padding-left: 35px !important; 
      }
    }

    /* Cards em Grade 2x2 (Direita) */
    .promo-cards .category-card-box {
      background-color: #f4f5f7 !important;
      border-radius: 18px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      height: 100%;
      width: 100%;
      transition: all 0.3s ease;
      cursor: pointer;
      border: 1px solid #e9ecef;
    }

    .promo-cards .category-card-box:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 22px rgba(0,0,0,0.08);
      background-color: #ebedf0 !important;
    }

    .promo-cards .card-box-img {
      height: 130px;
      width: 100%;
      overflow: hidden;
    }

    .promo-cards .card-box-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
    }

    .promo-cards .card-box-content {
      padding: 10px 14px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      flex: 1;
    }

    .promo-cards .card-box-content h4 {
      font-size: 0.95rem;
      font-weight: 700;
      margin: 0;
      color: #1a1a1a;
      display: -webkit-box;
      -webkit-line-clamp: 1;
      -webkit-box-orient: vertical;
      overflow: hidden;
      line-height: 1.2;
    }

    .modal-link { 
      font-size: 0.8rem; 
      font-weight: 600; 
      text-decoration: none; 
      color: #555; 
    }

    /* Modais */
    .modal-content { border-radius: 25px; border: none; }
    .modal-img-top { width: 100%; height: 280px; object-fit: cover; object-position: center; border-radius: 25px 25px 0 0; }
  </style>
</head>

<body class="index-page">

<?php include 'nav.php'; ?> 

<main class="main">
  <section id="promo-cards" class="promo-cards section mt-5">
    <div class="container" data-aos="fade-up">
      <div class="row gy-4">

        <!-- DESTAQUE PRINCIPAL DA ESQUERDA -->
        <div class="col-lg-6">
          <div class="category-featured">
            <div class="featured-text-side">
              <h2 class="fw-bold h3"><?= getTendencia('destaque', 'titulo', 'tendências de moda em 2026', $tendencias) ?></h2>
              <p><?= getTendencia('destaque', 'descricao', 'As tendências de moda em 2026 priorizam o conforto, o movimento e a sofisticação leve. Destacam-se o off-white suave, tons terrosos e vinhos, além de texturas artesanais, alfaiataria fluida e maxibrincos.', $tendencias) ?></p>
              
              <?php if (!$usuarioLogado): ?>
                <a href="cadastro.php" class="btn-amendoado">Se interessou? Cadastre-se</a>
              <?php endif; ?>
            </div>
            <div class="featured-image-side">
              <img src="<?= getTendencia('destaque', 'imagem', 'assets/img/destaque2026.png', $tendencias) ?>" alt="Destaque Principal">
            </div>
          </div>
        </div>

        <!-- GRADE 2x2 DA DIREITA (Empurrada mais para a direita com col-grid-direita e ps-lg-4) -->
        <div class="col-lg-6 col-grid-direita ps-lg-4">
          <div class="row g-3 h-100">
            
            <!-- CARD 1 -->
            <div class="col-6 h-50">
              <div class="category-card-box" data-bs-toggle="modal" data-bs-target="#modalCard1">
                <div class="card-box-img">
                  <img src="<?= getTendencia('card1', 'imagem', 'modafeminina.png', $tendencias) ?>">
                </div>
                <div class="card-box-content">
                  <h4><?= getTendencia('card1', 'titulo', 'Moda Feminina', $tendencias) ?></h4>
                  <span class="modal-link">Ver informações <i class="bi bi-arrow-right-short"></i></span>
                </div>
              </div>
            </div>

            <!-- CARD 2 -->
            <div class="col-6 h-50">
              <div class="category-card-box" data-bs-toggle="modal" data-bs-target="#modalCard2">
                <div class="card-box-img">
                  <img src="<?= getTendencia('card2', 'imagem', 'modamasculina.png', $tendencias) ?>">
                </div>
                <div class="card-box-content">
                  <h4><?= getTendencia('card2', 'titulo', 'Moda Masculina', $tendencias) ?></h4>
                  <span class="modal-link">Ver informações <i class="bi bi-arrow-right-short"></i></span>
                </div>
              </div>
            </div>

            <!-- CARD 3 -->
            <div class="col-6 h-50">
              <div class="category-card-box" data-bs-toggle="modal" data-bs-target="#modalCard3">
                <div class="card-box-img">
                  <img src="<?= getTendencia('card3', 'imagem', 'modainfantil2.png', $tendencias) ?>">
                </div>
                <div class="card-box-content">
                  <h4><?= getTendencia('card3', 'titulo', 'Moda Infantil', $tendencias) ?></h4>
                  <span class="modal-link">Ver informações <i class="bi bi-arrow-right-short"></i></span>
                </div>
              </div>
            </div>

            <!-- CARD 4 -->
            <div class="col-6 h-50">
              <div class="category-card-box" data-bs-toggle="modal" data-bs-target="#modalCard4">
                <div class="card-box-img">
                  <img src="<?= getTendencia('card4', 'imagem', 'Acessórios_Beleza.png', $tendencias) ?>">
                </div>
                <div class="card-box-content">
                  <h4><?= getTendencia('card4', 'titulo', 'Acessórios & Beleza', $tendencias) ?></h4>
                  <span class="modal-link">Ver informações <i class="bi bi-arrow-right-short"></i></span>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </section>
</main>

<!-- MODAIS DE INFORMAÇÕES -->
<div class="modal fade" id="modalCard1" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <img src="<?= getTendencia('card1', 'imagem', 'modafeminina.png', $tendencias) ?>" class="modal-img-top">
      <div class="modal-body p-4">
        <h4 class="fw-bold mb-3"><?= getTendencia('card1', 'titulo', 'Moda Feminina', $tendencias) ?></h4>
        <p class="text-muted"><?= getTendencia('card1', 'descricao', 'A moda feminina destaca peças em alfaiataria fluida, tons terrosos, tecidos naturais e acessórios artesanais para um visual sofisticado e confortável.', $tendencias) ?></p>
        <button type="button" class="btn btn-dark rounded-pill px-4 mt-2" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalCard2" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <img src="<?= getTendencia('card2', 'imagem', 'modamasculina.png', $tendencias) ?>" class="modal-img-top">
      <div class="modal-body p-4">
        <h4 class="fw-bold mb-3"><?= getTendencia('card2', 'titulo', 'Moda Masculina', $tendencias) ?></h4>
        <p class="text-muted"><?= getTendencia('card2', 'descricao', 'O guarda-roupa masculino aposta em cortes soltos, tecidos respiráveis como linho, sobreposições leves e paletas neutras e elegantes.', $tendencias) ?></p>
        <button type="button" class="btn btn-dark rounded-pill px-4 mt-2" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalCard3" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <img src="<?= getTendencia('card3', 'imagem', 'modainfantil2.png', $tendencias) ?>" class="modal-img-top">
      <div class="modal-body p-4">
        <h4 class="fw-bold mb-3"><?= getTendencia('card3', 'titulo', 'Moda Infantil', $tendencias) ?></h4>
        <p class="text-muted"><?= getTendencia('card3', 'descricao', 'A moda infantil traz estampas lúdicas, algodão orgânico, modelagens confortáveis e peças práticas focadas na liberdade de movimento.', $tendencias) ?></p>
        <button type="button" class="btn btn-dark rounded-pill px-4 mt-2" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalCard4" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <img src="<?= getTendencia('card4', 'imagem', 'Acessórios_Beleza.png', $tendencias) ?>" class="modal-img-top">
      <div class="modal-body p-4">
        <h4 class="fw-bold mb-3"><?= getTendencia('card4', 'titulo', 'Acessórios & Beleza', $tendencias) ?></h4>
        <p class="text-muted"><?= getTendencia('card4', 'descricao', 'Em alta os maxibrincos geométricos, bolsas estruturadas em tons neutros, maquiagem com acabamento glow natural e texturas artesanais.', $tendencias) ?></p>
        <button type="button" class="btn btn-dark rounded-pill px-4 mt-2" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/aos/aos.js"></script>
<script>AOS.init();</script>

</body>
</html>