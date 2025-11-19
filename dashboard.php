<?php
// CONEXÃO COM BASE DE DADOS
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'pap';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// TOTAL DE UTILIZADORES
$sql = "SELECT COUNT(*) as total FROM clientes";
$result = $conn->query($sql);
$total_utilizadores = ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;

// TOTAL DE PRODUTOS
$sql = "SELECT COUNT(*) as total FROM produtos";
$result = $conn->query($sql);
$total_produtos = ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;

// TRANSACOES NAS ÚLTIMAS 24H
$sql = "
SELECT t.*, c.nome AS nome_cliente 
FROM transacoes t 
JOIN clientes c ON t.id_cliente = c.id_cliente
WHERE t.data_transação >= NOW() - INTERVAL 1 DAY 
ORDER BY t.data_transação DESC
";
$transacoes_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - E-Shop Admin</title>
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", sans-serif;
      background: #eef1f5;
    }

    header {
      background: #2c3e50;
      color: white;
      padding: 20px 40px;
      font-size: 22px;
    }

    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: 220px;
      height: 100vh;
      background: #34495e;
      padding-top: 70px;
      color: #ecf0f1;
    }

    .sidebar h2 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 20px;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
    }

    .sidebar ul li {
      padding: 12px 25px;
      cursor: pointer;
      border-bottom: 1px solid #2c3e50;
    }

    .sidebar ul li:hover {
      background-color: #2c3e50;
    }

    .main {
      margin-left: 220px;
      padding: 30px;
    }

    .dashboard-section {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }

    .card {
      background-color: #fff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .card h3 {
      font-size: 14px;
      color: #888;
      margin-bottom: 10px;
    }

    .card p {
      font-size: 26px;
      font-weight: bold;
      color: #2c3e50;
    }

    .mini-card {
      background-color: #fff;
      padding: 10px 15px;
      border-left: 4px solid #16a085;
      margin-bottom: 10px;
      font-size: 14px;
      color: #2c3e50;
    }

    footer {
      text-align: center;
      margin-left: 220px;
      padding: 15px;
      font-size: 13px;
      color: #777;
    }

    .transacao {
      padding: 10px 15px;
      background: #f9f9f9;
      border-left: 4px solid #2980b9;
      margin-bottom: 8px;
      font-size: 14px;
    }

    .transacao strong {
      color: #2c3e50;
    }

    @media screen and (max-width: 768px) {
      .sidebar {
        display: none;
      }

      .main {
        margin-left: 0;
      }

      footer {
        margin-left: 0;
      }
    }
  </style>
</head>
<body>

<header>
  Painel de Administração - E-Shop
</header>

<div class="sidebar">
  <h2>Menu</h2>
  <ul>
    <li>Dashboard</li>
    <li>Utilizadores</li>
    <li>Produtos</li>
    <li>Pedidos</li>
    <li>Relatórios</li>
    <li>Configurações</li>
  </ul>
</div>

<div class="main">
  <h1 style="color: #2c3e50;">Resumo Geral</h1>

  <div class="dashboard-section">
    <div class="card">
      <h3>Utilizadores Registados</h3>
      <p><?php echo $total_utilizadores; ?></p>
    </div>
    <div class="card">
      <h3>Produtos Disponíveis</h3>
      <p><?php echo $total_produtos; ?></p>
    </div>
    <div class="card">
      <h3>Pedidos em Aberto</h3>
      <p>3</p>
    </div>
    <div class="card">
      <h3>Vendas nas Últimas 24h</h3>
      <p>
        <?php echo $transacoes_result->num_rows; ?>
      </p>
    </div>
  </div>

  <div class="dashboard-section">
    <div class="card">
      <h3>Últimos Registos</h3>
      <?php
        $sql = "SELECT nome, email FROM clientes ORDER BY id_cliente DESC LIMIT 5";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "<div class='mini-card'>" . htmlspecialchars($row['nome']) . " - " . htmlspecialchars($row['email']) . "</div>";
          }
        } else {
          echo "<div class='mini-card'>Sem registos</div>";
        }
      ?>
    </div>

    <div class="card">
      <h3>Transações nas Últimas 24h</h3>
      <?php
        if ($transacoes_result->num_rows > 0) {
          while ($row = $transacoes_result->fetch_assoc()) {
            echo "<div class='transacao'>";
            echo "<strong>" . htmlspecialchars($row['nome_cliente']) . "</strong> pagou <strong>" . number_format($row['valor_pago'], 2, ',', '.') . "€</strong> via " . htmlspecialchars($row['metodo_pagamento']);
            echo "<br><small>em " . date("d/m/Y H:i", strtotime($row['data_transação'])) . "</small>";
            echo "</div>";
          }
        } else {
          echo "<div class='mini-card'>Nenhuma transação nas últimas 24h</div>";
        }
      ?>
    </div>

    <div class="card">
      <h3>Produtos em Destaque</h3>
      <div class="mini-card">Produto A - 49 unidades</div>
      <div class="mini-card">Produto B - 31 unidades</div>
      <div class="mini-card">Produto C - 18 unidades</div>
    </div>
  </div>
</div>

<footer>
  &copy; <?php echo date("Y"); ?> E-Shop - Sistema Interno
</footer>

</body>
</html>
