<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <title>OceanBlue Pool</title>

  <style>
    /* CSS principal (mantido do original) */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background: var(--fundo, #73b6fa);
      color: var(--cor-primaria, #333);
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 50px;
      background: #005792;
      color: white;
      flex-wrap: wrap;
    }

    .logo {
      display: flex;
      align-items: center;
    }

    .logo a {
      display: flex;
      align-items: center;
      text-decoration: none;
      color: white;
    }

    .logo img {
      height: 50px;
      margin-right: 10px;
    }

    nav {
      display: flex;
      align-items: center;
      gap: 15px;
      flex-wrap: wrap;
    }

    nav a {
      color: white;
      text-decoration: none;
      font-weight: bold;
      margin: 0 10px;
      transition: color 0.3s;
    }

    nav a:hover {
      color: #ffcc00;
    }

    .auth-buttons {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-left: 20px;
    }

    .auth-buttons button {
      background: white;
      color: #005792;
      border: none;
      padding: 8px 15px;
      cursor: pointer;
      border-radius: 5px;
      font-weight: bold;
      transition: background 0.3s;
    }

    .auth-buttons button:hover {
      background: #e0e0e0;
    }
    
    /* Novo estilo para o botão Admin */
    .admin-btn {
        background: #ffcc00 !important; /* Cor de destaque */
        color: #005792 !important;
        transition: background 0.3s;
    }
    .admin-btn:hover {
        background: #e0b300 !important;
    }

    .hero {
      background: var(--fundo, #73b6fa);
      padding: 60px 20px;
      text-align: center;
    }

    .video-box {
      max-width: 960px;
      margin: 0 auto;
      position: relative;
      overflow: hidden;
      border-radius: 20px;
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.3);
    }

    .video-box video {
      width: 100%;
      height: auto;
      display: block;
    }

    .hero-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.6);
      z-index: 1;
      padding: 20px;
    }

    .hero-content h1 {
      font-size: 48px;
      margin-bottom: 15px;
    }

    .hero-content p {
      font-size: 20px;
    }

    .section {
      padding: 60px 40px;
      text-align: center;
    }

    .section h2 {
      color: #005792;
      margin-bottom: 20px;
    }

    .section p {
      font-size: 18px;
      color: #333;
      max-width: 800px;
      margin: 0 auto;
    }

    .container {
      display: flex;
      justify-content: space-between;
      padding: 40px;
      gap: 20px;
      flex-wrap: wrap;
    }

    .container .column {
      flex: 1;
      background: #b8b8b8;
      padding: 20px 15px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      text-align: center;
      min-width: 260px;
    }

    .container .column h2 {
      color: #005792;
      margin-bottom: 20px;
    }

    .container .column img {
      width: 100%;
      height: auto;
      border-radius: 10px;
      margin-bottom: 20px;
    }

    .btn {
      display: inline-block;
      background: #005792;
      color: white;
      text-decoration: none;
      padding: 10px 20px;
      border-radius: 10px;
      font-size: 16px;
      font-weight: bold;
      transition: 0.3s;
      margin-bottom: 25px;
    }

    .btn:hover {
      background: #003f6b;
    }

    .columns {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 40px;
      flex-wrap: wrap;
    }

    .columns .column {
      flex: 1;
      background: #b8b8b8;
      padding: 20px 15px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      min-width: 260px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      align-items: center;
      text-align: center;
    }

    .columns .icon-img {
      max-width: 300px;
      height: auto;
      object-fit: contain;
      margin-bottom: 15px;
      display: block;
      margin-left: auto;
      margin-right: auto;
    }

    .columns .column .btn {
      margin-top: auto;
      align-self: center;
    }

    footer {
      background: #004d80;
      color: #e0f3ff;
      padding: 25px 20px;
      text-align: center;
      margin-top: 50px;
      font-size: 14px;
    }

    footer .footer-links {
      margin-bottom: 10px;
    }

    footer .footer-links a {
      color: #cce7f5;
      margin: 0 12px;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s;
    }

    footer .footer-links a:hover {
      color: white;
    }

    footer .copy {
      font-size: 13px;
      color: #a2d3f3;
    }

    @media (max-width: 768px) {
      .container, .columns {
        flex-direction: column;
      }

      header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
      }

      nav {
        flex-direction: column;
        gap: 10px;
      }

      .auth-buttons {
        justify-content: center;
      }

      .hero-content h1 {
        font-size: 32px;
      }

      .hero-content p {
        font-size: 16px;
      }
    }
  </style>
</head>
<body>

  <header>
    <div class="logo">
      <a href="index.php">
        <img src="assets/logoPAP.png" alt="OceanBlue Pool">
        <span>OceanBlue Pool</span>
      </a>
    </div>

    <nav>
      <a href="produtos.php">Produtos</a>
      <a href="piscinas.php">Piscinas</a>
      <a href="orcamento.php">Orçamento</a>
      <a href="servicos.php">Serviços</a>
    </nav>

    <div class="auth-buttons">
      
      <?php 
      if (
        isset($_SESSION['usuario']) &&
        isset($_SESSION['usuario']['user_type']) &&
        $_SESSION['usuario']['user_type'] === 'admin'
      ) {
      ?>
          <button onclick="window.location.href='adicionar_empresa.php'" class="admin-btn">
              + Adicionar Empresa
          </button>
      <?php 
      }
      ?>
      
      <?php 
      if (isset($_SESSION['usuario'])) { ?>
        <span>Olá, <?php echo htmlspecialchars($_SESSION['usuario']['nome']); ?>!</span>
        <button onclick="window.location.href='logout.php'">Logout</button>
      <?php } else { ?>
        <button onclick="window.location.href='registar.php'">Registrar</button>
        <button onclick="window.location.href='login.php'">Login</button>
      <?php } ?>

    </div>
  </header>

  <section class="hero">
    <div class="video-box">
      <video autoplay muted loop playsinline>
        <source src="assets/video-fundo.mp4" type="video/mp4">
        Seu navegador não suporta vídeos em HTML5.
      </video>
      <div class="hero-content">
        <h1>Bem-vindo à OceanBlue Pool</h1>
        <p>Especialistas em piscinas e bem-estar aquático</p>
      </div>
    </div>
  </section>

  <section class="section">
    <h2>Quem Somos</h2>
    <p>Estamos apenas a começar, mas já garantimos qualidade, compromisso e um serviço ao cliente de excelência. Na OceanBlue Pool, você encontra mais do que produtos – encontra confiança.</p>
  </section>

  <section class="section">
    <h2>Os Nossos Produtos</h2>
    <div class="columns">
      <div class="column">
        <img src="assets/cloro.png" alt="Produtos de Piscinas" class="icon-img">
        <h3>Produtos de Piscinas</h3>
        <p>Os melhores produtos para manter sua piscina sempre limpa e cristalina.</p>
        <a href="produtos.php" class="btn">Ver Produtos</a>
      </div>
      <div class="column">
        <img src="assets/piscina.png" alt="Piscinas" class="icon-img">
        <h3>Piscinas</h3>
        <p>Modelos modernos e duradouros para transformar seu espaço de lazer.</p>
        <a href="piscinas.php" class="btn">Ver Piscinas</a>
      </div>
      <div class="column">
        <img src="assets/robo.png" alt="Acessórios" class="icon-img">
        <h3>Acessórios</h3>
        <p>Robôs, escovas, filtros e tudo o que você precisa para manter sua piscina impecável.</p>
        <a href="produtos.php" class="btn">Ver Acessórios</a>
      </div>
      <div class="column">
        <img src="assets/entrega.png" alt="Entrega Rápida" class="icon-img">
        <h3>Entrega Rápida</h3>
        <p>Receba seus pedidos com agilidade e segurança em sua casa.</p>
      </div>
    </div>
  </section>

  <section class="section">
    <h2>Por que escolher a OceanBlue Pool?</h2>
    <div class="columns">
      <div class="column">
        <img src="assets/suporte.png" alt="Atendimento" class="icon-img">
        <h3>Atendimento Especializado</h3>
        <p>Estamos aqui para ajudar em cada etapa do seu projeto aquático.</p>
      </div>
      <div class="column">
        <img src="assets/qualidade.png" alt="Qualidade" class="icon-img">
        <h3>Produtos de Qualidade</h3>
        <p>Trabalhamos com as melhores marcas e fornecedores do mercado.</p>
      </div>
      <div class="column">
        <img src="assets/entrega.png" alt="Entrega Rápida" class="icon-img">
        <h3>Entrega Rápida</h3>
        <p>Receba seus pedidos com agilidade e segurança em sua casa.</p>
      </div>
      <div class="column">
        <img src="assets/entrega.png" alt="Entrega Rápida" class="icon-img">
        <h3>Entrega Rápida</h3>
        <p>Receba seus pedidos com agilidade e segurança em sua casa.</p>
      </div>
    </div>
  </section>

  <footer>
    <div class="footer-links">
      <a href="index.php">Início</a>
      <a href="produtos.php">Produtos</a>
      <a href="sobre.php">Sobre</a>
      <a href="contato.php">Contato</a>
    </div>
    <div class="copy">
      &copy; <?php echo date("Y"); ?> OceanBlue Pool - Todos os direitos reservados
    </div>
  </footer>

</body>
</html>
