<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Sobre o site</title>
  
  <meta name="description" content="Conheça o FashionStyle: seu guarda-roupa virtual para organizar looks, cadastrar roupas e se inspirar com tendências.">
  <meta name="keywords" content="moda, guarda-roupa virtual, looks, tendências, organização">

  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">

  <link href="assets/css/main.css" rel="stylesheet">

  <style>
    /* Estilização da Seção Hero: Usa um gradiente sobre a imagem de fundo para garantir contraste do texto */
    .about-hero {
      background: linear-gradient(rgba(255, 255, 255, 0.35), rgba(0, 0, 0, 0.7)), url('assets/img/about-bg.jpg') center/cover no-repeat;
      color: #fff;
      padding: 100px 0;
      text-align: center;
    }
    .about-hero h1 {
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 20px;
    }

    /* Grid de Funcionalidades: Organiza os cards de forma automática conforme o tamanho da tela */
    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
      margin-top: 40px;
    }

    /* Estilo do Card: Possui uma borda suave e efeito de levitação (hover) para interatividade */
    .feature-card {
      text-align: center;
      padding: 30px;
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease;
    }
    .feature-card:hover {
      transform: translateY(-10px); /* Efeito visual onde o card sobe ao passar o mouse */
    }

    /* Botão com bordas totalmente arredondadas (estilo amendoado) conforme o padrão do site */
    .btn-primary-custom {
      background-color: #000;
      color: #fff;
      border: none;
      border-radius: 50px;
      padding: 12px 30px;
      font-weight: 500;
      transition: all 0.3s ease;
    }
  </style>
</head>

<body class="about-page">

  <?php 
  // O comando include permite que o menu seja alterado em um só lugar e mude em todo o site
  include 'nav.php'; 
  ?>

  <main class="main">

    <section class="about-hero">
      <div class="container" data-aos="fade-up">
        <h1>Sobre a FashionStyle</h1>
        <p>Seu guarda-roupa virtual para organizar, combinar e se inspirar todos os dias.</p>
      </div>
    </section>

    <section class="about-content section light-background">
      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row">
          <div class="col-lg-8 mx-auto text-center">
            <h2>Organize sua vida com mais praticidade</h2>
            <p>
              A <strong>FashionStyle</strong> foi criado para quem quer praticidade na hora de se vestir e montar looks. 
              Cadastre suas roupas, categorize por tipo, cor, estação e ocasião, e monte looks incríveis com apenas alguns cliques.
            </p>
            <p>
              Nosso objetivo é simples: <strong>economizar seu tempo</strong>. 
              Nada de abrir o armário e não saber o que usar, aqui, tudo está organizado e pronto para inspirar.
            </p>
          </div>
        </div>

        <div class="features-grid">
          
          <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
            <h4>Guarda-Roupa Digital</h4>
            <p>Cadastre suas peças com fotos, cores, estações e tags personalizadas por ti mesma(o).</p>
          </div>

          <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
            <h4>Monte Looks</h4>
            <p>Combine roupas para qualquer ocasião.</p>
          </div>

          <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
            <h4>Tendências por Estação</h4>
            <p>Filtre por verão, inverno, primavera e outono.</p>
          </div>

          <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
            <h4>100% Responsivo</h4>
            <p>Acesse seu estilo de qualquer lugar: celular, tablet ou desktop.</p>
          </div>

        </div>

      </div>
    </section>

  </main>

  <?php 
  include 'footer.php'; 
  ?>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
  </a>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/js/main.js"></script>

  <script>
    // Inicialização da biblioteca AOS para as animações de surgimento dos elementos
    AOS.init();
  </script>

</body>
</html>