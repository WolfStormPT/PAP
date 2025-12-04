<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sobre Nós - OceanBlue Pool</title>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: #f4f9fb;
      color: #333;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0px 50px;
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

    .container {
      max-width: 1000px;
      margin: 40px auto;
      padding: 20px;
    }

    .about-section {
      background: white;
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .about-section h2 {
      color: #0077b6;
      font-size: 28px;
      margin-bottom: 20px;
    }

    .about-section p {
      line-height: 1.7;
      margin-bottom: 15px;
    }

    footer {
      text-align: center;
      padding: 20px;
      background: #023e8a;
      color: white;
      margin-top: 40px;
    }

    @media (max-width: 600px) {
      header h1 {
        font-size: 28px;
      }
      .about-section h2 {
        font-size: 24px;
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
<h1>Sobre a OceanBlue Pool</h1>

</header>
<div class="container">
  <div class="about-section">
  <p>Transformamos o seu espaço num verdadeiro paraíso aquático</p>
    <h2>Quem Somos</h2>
    <p>A OceanBlue Pool é uma empresa especializada na construção, manutenção e renovação de piscinas residenciais e comerciais. Com mais de 10 anos de experiência no mercado, oferecemos soluções personalizadas que unem inovação, segurança e elegância.</p>

    <h2>Missão</h2>
    <p>Proporcionar bem-estar e lazer aos nossos clientes através de serviços de excelência em piscinas e áreas de lazer, garantindo qualidade, durabilidade e design moderno.</p>

    <h2>Visão</h2>
    <p>Ser referência nacional no setor de piscinas, reconhecida pela confiança, compromisso com o cliente e uso das melhores tecnologias do mercado.</p>

    <h2>Valores</h2>
    <ul>
      <li>Compromisso com o cliente</li>
      <li>Qualidade e inovação</li>
      <li>Ética e transparência</li>
      <li>Respeito ao meio ambiente</li>
    </ul>
  </div>
</div>

<footer>
  &copy; <?php echo date("Y"); ?> OceanBlue Pool. Todos os direitos reservados.
</footer>

</body>
</html>
