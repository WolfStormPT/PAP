<?php
session_start();

// Conexão com a base de dados
$conn = new mysqli("localhost", "root", "", "pap");
if ($conn->connect_error) {
  die("Erro na conexão: " . $conn->connect_error);
}

$sql = "SELECT id_produto, nome_produto AS nome, descricao, imagem FROM produtos";
$result = $conn->query($sql);

$produtos = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $produtos[] = $row;
  }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Produtos - OceanBlue Pool</title>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <style>
    :root {
      --cor-primaria: #005792;
      --cor-secundaria: #003f6b;
      --fundo: #73b6fa;
      --branco: #ffffff;
      --sombra: rgba(0, 0, 0, 0.1);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background: var(--fundo, #73b6fa);
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 50px;
      background: var(--cor-primaria);
      color: white;
      flex-wrap: wrap;
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

    .search-container {
      position: relative;
      display: flex;
      align-items: center;
    }

    .search-icon {
      width: 35px;
      height: 35px;
      background: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background 0.3s;
      margin-left: 10px;
    }

    .search-icon:hover {
      background: #ffcc00;
    }

    .search-icon img {
      width: 20px;
      height: 20px;
    }

    .search-bar {
      width: 0;
      opacity: 0;
      border: none;
      outline: none;
      transition: width 0.4s ease, opacity 0.4s ease;
      position: absolute;
      right: 40px;
      height: 35px;
      border-radius: 20px;
      padding-left: 10px;
      background: white;
      color: #005792;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
    }

    .search-container.active .search-bar {
      width: 200px;
      opacity: 1;
      padding: 5px 10px;
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

    section.busca {
      background: #e8f3fa;
      padding: 30px 20px;
      display: flex;
      justify-content: center;
    }

    .search-bar-main {
      width: 100%;
      max-width: 600px;
    }

    .search-bar-main input {
      width: 100%;
      padding: 10px 15px;
      font-size: 18px;
      border: 2px solid var(--cor-primaria);
      border-radius: 18px;
      outline: none;
    }

    section.produtos {
      max-width: 1200px;
      margin: 50px auto;
      padding: 0 20px;
    }

    .produtos-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 60px;
    }

    .produto {
      background: var(--branco);
      border-radius: 12px;
      box-shadow: 0 8px 20px var(--sombra);
      padding: 20px;
      text-decoration: none;
      color: inherit;
      transition: transform 0.2s, box-shadow 0.3s;
      display: flex;
      flex-direction: column;
    }

    .produto:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
    }

    .produto img {
      width: 100%;
      max-height: 180px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 15px;
    }

    .produto h3 {
      font-size: 20px;
      margin-bottom: 10px;
      color: var(--cor-secundaria);
    }

    .produto p {
      font-size: 15px;
      color: #333;
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

      .produtos-grid {
        grid-template-columns: 1fr;
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
    <div class="search-container">
      
    </div>
  </nav>

  <div class="auth-buttons">
    <?php if (isset($_SESSION['usuario'])): ?>
      <span>Olá, <?php echo htmlspecialchars($_SESSION['usuario']['nome']); ?>!</span>
      <form action="logout.php" method="post" style="display:inline;">
        <button type="submit">Logout</button>
      </form>
    <?php else: ?>
      <button onclick="window.location.href='registar.php'">Registrar</button>
      <button onclick="window.location.href='login.php'">Login</button>
    <?php endif; ?>
  </div>
</header>

<section class="busca">
  <div class="search-bar-main">
    <input type="text" id="searchInput" placeholder="Buscar produto...">
  </div>
</section>

<section class="produtos">
  <div class="produtos-grid" id="produtosGrid">
    <?php foreach ($produtos as $produto): ?>
      <a href="compra.php?id=<?php echo $produto['id_produto']; ?>" class="produto" data-nome="<?php echo strtolower($produto['nome']); ?>">
        <img src="<?php echo htmlspecialchars($produto['imagem']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
        <h3><?php echo htmlspecialchars($produto['nome']); ?></h3>
        <p><?php echo htmlspecialchars($produto['descricao']); ?></p>
      </a>
    <?php endforeach; ?>
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

<script>
  function toggleSearch() {
    document.querySelector('.search-container').classList.toggle('active');
  }

  const input = document.getElementById('searchInput');
  const produtos = document.querySelectorAll('.produto');

  input.addEventListener('input', function () {
    const valor = this.value.toLowerCase();
    produtos.forEach(produto => {
      const nome = produto.getAttribute('data-nome');
      produto.style.display = nome.includes(valor) ? 'flex' : 'none';
    });
  });
</script>

</body>
</html>
