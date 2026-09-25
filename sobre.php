<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Sobre o site</title>
  
  <meta name="description" content="Conheça o FashionStyle: seu guarda-roupa virtual para otimizar seu tempo, organizar looks, acompanhar tendências mensais, cadastrar roupas e interagir com uma comunidade segura e moderada.">
  <meta name="keywords" content="moda, guarda-roupa virtual, otimizar tempo, tendências mensais, looks, comunidade, moderação, organização">

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

    /* Container dos Cards: Usando Flexbox com justify-content: center para centralizar perfeitamente todos os cards */
    .features-grid {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
      margin-top: 40px;
    }

    /* Estilo do Card: Possui borda suave, alinhamento centralizado e largura proporcional */
    .feature-card {
      text-align: center;
      padding: 30px;
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease;
      flex: 1 1 280px;
      max-width: 350px; /* Mantém tamanho padronizado e centralizado */
    }
    .feature-card:hover {
      transform: translateY(-10px); /* Efeito visual onde o card sobe ao passar o mouse */
    }
    .feature-card i {
      font-size: 2.5rem;
      color: #000;
      margin-bottom: 15px;
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

  <?php include 'nav.php'; ?>

  <main class="main">

    <section class="about-hero">
      <div class="container" data-aos="fade-up">
        <h1>Sobre a FashionStyle</h1>
        <p>Seu guarda-roupa virtual para otimizar seu tempo, organizar, combinar, acompanhar tendências mensais e compartilhar produções em uma comunidade segura.</p>
      </div>
    </section>

    <section class="about-content section light-background">
      <div class="container" data-aos="fade-up">

        <div class="row">
          <div class="col-lg-8 mx-auto text-center">
            <h2>Organize sua vida com mais praticidade, agilidade e estilo</h2>
            <p>
              A <strong>FashionStyle</strong> foi criada para quem busca praticidade, <strong>otimização de tempo</strong> e intencionalidade na hora de se vestir. 
              Cadastre suas roupas no acervo digital, categorize por tipo, cor, estação ou ocasião, e monte composições visuais sem perder tempo em frente ao armário.
            </p>
            <p>
              Economize minutos valiosos da sua rotina diária planejando seus looks com antecedência. Além disso,
              nossa plataforma traz <strong>tendências de moda atualizadas todo mês</strong>, oferecendo inspirações exclusivas para renovar o seu estilo.
              Contamos também com um <strong>módulo de comunidade com moderação ativa de conteúdo</strong>,
              permitindo que você publique seus looks, curta, comente e interaja com segurança e tranquilidade.
            </p>
          </div>
        </div>

        <div class="features-grid">
          
          <div class="feature-card" data-aos="fade-up">
            <i class="bi bi-clock-history"></i>
            <h4>Otimização do Tempo</h4>
            <p>Economize tempo precioso no seu dia a dia planejando e escolhendo seus looks de forma rápida e prática.</p>
          </div>

          <div class="feature-card" data-aos="fade-up">
            <i class="bi bi-journal-album"></i>
            <h4>Guarda-Roupa Digital</h4>
            <p>Cadastre suas peças com fotos, cores, estações e tags personalizadas por você mesma(o).</p>
          </div>

          <div class="feature-card" data-aos="fade-up">
            <i class="bi bi-stars"></i>
            <h4>Tendências Mensais</h4>
            <p>Fique por dentro das novidades e tendências de moda atualizadas todo mês para inspirar seus looks.</p>
          </div>

          <div class="feature-card" data-aos="fade-up">
            <i class="bi bi-palette"></i>
            <h4>Monte Looks</h4>
            <p>Combine roupas para qualquer ocasião em um painel digital interativo e prático.</p>
          </div>

          <div class="feature-card" data-aos="fade-up">
            <i class="bi bi-people-fill"></i>
            <h4>Comunidade & Feed</h4>
            <p>Compartilhe suas combinações e acompanhe os looks mais curtidos da semana no feed da comunidade.</p>
          </div>

          <div class="feature-card" data-aos="fade-up">
            <i class="bi bi-heart-fill"></i>
            <h4>Interação Social</h4>
            <p>Curta e comente nas produções de outros usuários para trocar referências de estilo diárias.</p>
          </div>

          <div class="feature-card" data-aos="fade-up">
            <i class="bi bi-shield-check"></i>
            <h4>Ambiente Moderado</h4>
            <p>Moderação ativa para garantir uma comunidade acolhedora, respeitosa e livre de conteúdos impróprios.</p>
          </div>

          <div class="feature-card" data-aos="fade-up">
            <i class="bi bi-phone-vibrate"></i>
            <h4>100% Responsivo</h4>
            <p>Acesse seu estilo de qualquer lugar com navegação otimizada para celular, tablet ou desktop.</p>
          </div>

        </div>

      </div>
    </section>

  </main>

  <?php include 'footer.php'; ?>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
  </a>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/js/main.js"></script>

  <script>
    // Inicialização da biblioteca AOS sem exigir scroll
    AOS.init({
      duration: 800,
      once: true
    });

    // Força a execução das animações em TODOS os elementos ao carregar a página
    window.addEventListener('load', function() {
      document.querySelectorAll('[data-aos]').forEach(function(element) {
        element.classList.add('aos-animate');
      });
    });
  </script>

</body>
</html>