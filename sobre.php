<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sobre Nós - OceanBlue Pool</title>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background: #f4f9fb;
      color: #333;
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

    .admin-btn {
      background: #ffcc00 !important;
      color: #005792 !important;
    }
    .admin-btn:hover {
      background: #e0b300 !important;
    }

    .container {
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .about-section {
      background: white;
      border-radius: 10px;
      padding: 40px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .about-section .sub {
      font-size: 18px;
      color: #005792;
      font-weight: bold;
      text-align: center;
      margin-bottom: 30px;
    }

    .about-section h2 {
      color: #005792;
      font-size: 26px;
      margin-top: 30px;
      margin-bottom: 15px;
      border-bottom: 2px solid #e0f3ff;
      padding-bottom: 8px;
    }

    .about-section p {
      line-height: 1.8;
      margin-bottom: 15px;
      font-size: 16px;
      color: #555;
    }

    .about-section ul {
      margin-left: 20px;
      margin-bottom: 15px;
      line-height: 1.8;
      color: #555;
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
      <a href="listar_empresas.php">Empresas</a> 
      <a href="recomendacao.php">Conselheiro</a>
    </nav>

    <div class="auth-buttons">
      <?php 
      if (isset($_SESSION['usuario']) && isset($_SESSION['usuario']['user_type']) && $_SESSION['usuario']['user_type'] === 'admin') {
          echo '<button onclick="window.location.href=\'admin_empresas.php\'" class="admin-btn">Painel Admin</button>';
      }
      
      if (isset($_SESSION['usuario'])) { ?>
          <span>Olá, <?php echo htmlspecialchars($_SESSION['usuario']['nome']); ?>!</span>
          <button onclick="window.location.href='logout.php'">Logout</button>
      <?php } else { ?>
          <button onclick="window.location.href='registar.php'">Registrar</button>
          <button onclick="window.location.href='login.php'">Login</button>
      <?php } ?>
    </div>
  </header>

  <div class="container">
    <div class="about-section">
      <p class="sub">Ligamos proprietários de piscinas às melhores soluções do mercado.</p>
      
      <h2>Quem Somos</h2>
      <p>A <strong>OceanBlue Pool</strong> nasceu com o objetivo de simplificar o cuidado e a manutenção de piscinas. Não somos uma construtora, mas sim uma plataforma de recomendação inteligente que funciona como a ponte ideal entre clientes e prestadores de serviços de confiança.</p>
      <p>Através do nosso sistema, qualquer utilizador pode encontrar os parceiros certos para limpeza, manutenção ou reparações técnicas, com base em filtros de proximidade geográfica e nas avaliações reais deixadas pela nossa comunidade.</p>

      <h2>Missão</h2>
      <p>Proporcionar tranquilidade e facilidade de escolha aos donos de piscinas, garantindo o acesso a serviços de qualidade através de uma pesquisa rápida, transparente e totalmente focada na localização do utilizador.</p>

      <h2>Visão</h2>
      <p>Tornarmo-nos a plataforma de referência em Portugal no setor de piscinas, promovendo o crescimento de negócios locais de confiança e a total satisfação de quem procura serviços especializados.</p>

      <h2>Valores</h2>
      <ul>
        <li><strong>Transparência:</strong> Avaliações e notas reais deixadas por clientes de forma imparcial.</li>
        <li><strong>Inovação:</strong> Uso de geolocalização exata para recomendar o parceiro mais próximo.</li>
        <li><strong>Simplicidade:</strong> Um design limpo e intuitivo para que a pesquisa demore apenas alguns segundos.</li>
        <li><strong>Confiança:</strong> Listagem cuidada de empresas para garantir um serviço seguro ao cliente final.</li>
      </ul>
    </div>
  </div>

  <footer>
    <div class="footer-links">
      <a href="index.php">Início</a>
      <a href="listar_empresas.php">Empresas</a> 
      <a href="sobre.php">Sobre</a>
    </div>
    <div class="copy">
      &copy; <?php echo date("Y"); ?> OceanBlue Pool. Todos os direitos reservados.
    </div>
  </footer>

</body>
</html>