<?php
session_start();
$conn = new mysqli("localhost", "root", "", "pap");
if ($conn->connect_error) {
  die("Erro na conexão: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT nome_produto AS nome, descricao, imagem, preco FROM produtos WHERE id_produto = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
  echo "Produto não encontrado.";
  exit;
}

$produto = $result->fetch_assoc();
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="icon" href="favicon.ico">
  <title><?php echo htmlspecialchars($produto['nome']); ?> - OceanBlue Pool</title>
  <style>
    <?php // Insere todo o CSS do index aqui... para evitar repetição, você pode separar num arquivo externo como style.css ?>
    /* --- Início do CSS do index.php --- */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }
    body {
      background: var(--fundo, #f2f2f2);
      color: var(--cor-primaria, #333);
    }
    header { display: flex; justify-content: space-between; align-items: center; padding: 15px 50px; background: #005792; color: white; flex-wrap: wrap; }
    .logo { display: flex; align-items: center; }
    .logo a { display: flex; align-items: center; text-decoration: none; color: white; }
    .logo img { height: 50px; margin-right: 10px; }
    nav { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
    nav a { color: white; text-decoration: none; font-weight: bold; margin: 0 10px; transition: color 0.3s; }
    nav a:hover { color: #ffcc00; }
    .auth-buttons { display: flex; align-items: center; gap: 10px; margin-left: 20px; }
    .auth-buttons button { background: white; color: #005792; border: none; padding: 8px 15px; cursor: pointer; border-radius: 5px; font-weight: bold; transition: background 0.3s; }
    .auth-buttons button:hover { background: #e0e0e0; }
    footer { background: #004d80; color: #e0f3ff; padding: 25px 20px; text-align: center; margin-top: 50px; font-size: 14px; }
    footer .footer-links { margin-bottom: 10px; }
    footer .footer-links a { color: #cce7f5; margin: 0 12px; text-decoration: none; font-weight: 500; transition: color 0.3s; }
    footer .footer-links a:hover { color: white; }
    footer .copy { font-size: 13px; color: #a2d3f3; }

    /* --- Estilos da página de produto --- */
    .main {
      max-width: 1200px;
      margin: 40px auto;
      background: #fff;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
      display: flex;
      gap: 40px;
      flex-wrap: wrap;
    }
    .image-area { flex: 1 1 40%; text-align: center; }
    .image-area img { max-width: 100%; border-radius: 10px; border: 1px solid #ccc; }
    .info-area { flex: 1 1 55%; display: flex; flex-direction: column; justify-content: space-between; }
    .info-area h1 { font-size: 28px; margin-bottom: 10px; color: #222; }
    .descricao { font-size: 15px; line-height: 1.6; color: #555; margin: 20px 0; }
    .preco { font-size: 30px; color: #d60000; font-weight: bold; margin-top: 10px; }
    .quantidade { margin: 20px 0; }
    .quantidade input { width: 60px; padding: 5px; font-size: 16px; text-align: center; }
    .btn-comprar {
      background-color: #ff3e00; color: white; padding: 14px; font-size: 16px;
      border: none; border-radius: 6px; cursor: pointer; width: 100%; transition: 0.3s;
    }
    .btn-comprar:hover { background-color: #cc2d00; }
    .total { margin-top: 10px; font-size: 18px; color: #333; font-weight: 500; }
    .voltar {
      display: inline-block; margin-top: 30px; text-decoration: none; color: #007acc; font-weight: bold;
    }
    @media (max-width: 768px) {
      header { flex-direction: column; gap: 15px; text-align: center; }
      nav { flex-direction: column; gap: 10px; }
      .auth-buttons { justify-content: center; }
      .main { flex-direction: column; padding: 20px; }
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
    <?php if (isset($_SESSION['usuario'])): ?>
      <span>Olá, <?php echo htmlspecialchars($_SESSION['usuario']['nome']); ?>!</span>
      <button onclick="window.location.href='logout.php'">Logout</button>
    <?php else: ?>
      <button onclick="window.location.href='registar.php'">Registrar</button>
      <button onclick="window.location.href='login.php'">Login</button>
    <?php endif; ?>
  </div>
</header>

<div class="main">
  <div class="image-area">
    <img src="<?php echo htmlspecialchars($produto['imagem']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
  </div>

  <div class="info-area">
    <div>
      <h1><?php echo htmlspecialchars($produto['nome']); ?></h1>
      <div class="preco">€<span id="preco"><?php echo number_format($produto['preco'], 2, ',', '.'); ?></span></div>

      <div class="quantidade">
        <label for="qtd"><strong>Quantidade:</strong></label>
        <input type="number" id="qtd" value="1" min="1">
      </div>

      <div class="total">Total: €<span id="total"><?php echo number_format($produto['preco'], 2, ',', '.'); ?></span></div>

      <button class="btn-comprar" onclick="adicionarAoCarrinho()">Comprar</button>
      <a href="produtos.php" class="voltar">← Voltar aos produtos</a>
    </div>

    <div class="descricao">
      <h3>Descrição do Produto</h3>
      <p><?php echo nl2br(htmlspecialchars($produto['descricao'])); ?></p>
    </div>
  </div>
</div>

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
  const precoUnit = <?php echo number_format($produto['preco'], 2, '.', ''); ?>;
  const qtdInput = document.getElementById('qtd');
  const totalSpan = document.getElementById('total');

  qtdInput.addEventListener('input', () => {
    const qtd = Math.max(1, parseInt(qtdInput.value) || 1);
    qtdInput.value = qtd;
    const total = precoUnit * qtd;
    totalSpan.textContent = total.toFixed(2).replace('.', ',');
  });

  function adicionarAoCarrinho() {
    const qtd = parseInt(qtdInput.value);
    alert(`✅ Produto adicionado ao carrinho!\nQuantidade: ${qtd}`);
    // Aqui poderá ser feita uma chamada AJAX real para o carrinho
  }
</script>

</body>
</html>
