<?php
require_once "verificar_login.php";
protegerPagina('admin'); // Segurança: Só admins entram aqui
require_once "conexao.php";

// Obter todas as empresas para a listagem
$sql = "SELECT id_empresa, nome, localizacao, avaliacao_media FROM empresas ORDER BY nome ASC";
$resultado = mysqli_query($ligaDB, $sql);
$empresas = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <title>Gestão de Empresas - OceanBlue Pool</title>

  <style>
    /* CSS principal (herdado do teu estilo original) */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background: #73b6fa; /* Cor de fundo do teu site */
      color: #333;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
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
    }

    .auth-buttons button {
      background: white;
      color: #005792;
      border: none;
      padding: 8px 15px;
      cursor: pointer;
      border-radius: 5px;
      font-weight: bold;
    }

    /* Estilos específicos da Tabela de Gestão */
    .admin-section {
      flex: 1;
      padding: 40px 20px;
    }

    .admin-container {
      max-width: 1000px;
      margin: 0 auto;
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        border-bottom: 2px solid #005792;
        padding-bottom: 15px;
    }

    .admin-header h2 {
        color: #005792;
    }

    .btn-add {
      background: #28a745;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      border-radius: 8px;
      font-weight: bold;
      transition: 0.3s;
    }

    .btn-add:hover {
      background: #218838;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th, td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }

    th {
      background: #005792;
      color: white;
    }

    tr:hover {
        background-color: #f9f9f9;
    }

    .actions {
        display: flex;
        gap: 15px;
    }

    .btn-edit {
      color: #005792;
      text-decoration: none;
      font-weight: bold;
    }

    .btn-edit:hover { text-decoration: underline; }

    .btn-del {
      color: #d9534f;
      text-decoration: none;
      font-weight: bold;
      cursor: pointer;
    }

    .btn-del:hover { color: #c9302c; }

    /* ESTILOS DO NOVO POP-UP (MODAL MODERNO) */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(4px);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s ease;
    }

    .modal-overlay.active {
      opacity: 1;
      pointer-events: auto;
    }

    .modal-box {
      background: white;
      padding: 30px;
      border-radius: 12px;
      width: 90%;
      max-width: 420px;
      text-align: center;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
      transform: scale(0.8);
      transition: transform 0.3s ease;
    }

    .modal-overlay.active .modal-box {
      transform: scale(1);
    }

    .modal-box h3 {
      color: #003366;
      font-size: 22px;
      margin-bottom: 12px;
    }

    .modal-box p {
      color: #666;
      font-size: 15px;
      line-height: 1.5;
      margin-bottom: 24px;
    }

    .modal-buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
    }

    .modal-btn {
      padding: 10px 24px;
      border: none;
      border-radius: 6px;
      font-size: 15px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.2s;
    }

    .modal-btn-confirm {
      background: #d9534f;
      color: white;
    }

    .modal-btn-confirm:hover {
      background: #c9302c;
    }

    .modal-btn-cancel {
      background: #e0e0e0;
      color: #333;
    }

    .modal-btn-cancel:hover {
      background: #d4d4d4;
    }

    footer {
      background: #004d80;
      color: #e0f3ff;
      padding: 25px 20px;
      text-align: center;
      font-size: 14px;
    }

    footer .footer-links a {
      color: #cce7f5;
      margin: 0 12px;
      text-decoration: none;
    }

    @media (max-width: 768px) {
      header { padding: 15px; flex-direction: column; }
      .admin-container { padding: 15px; }
      table, thead, tbody, th, td, tr { display: block; }
      th { display: none; }
      td { text-align: right; position: relative; padding-left: 50%; }
      td::before { content: attr(data-label); position: absolute; left: 15px; font-weight: bold; }
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
      <?php if (isset($_SESSION['usuario'])): ?>
        <span style="margin-right:10px;">Olá, <strong><?php echo htmlspecialchars($_SESSION['usuario']['nome']); ?></strong>!</span>
        <button onclick="window.location.href='logout.php'">Logout</button>
      <?php else: ?>
        <button onclick="window.location.href='registar.php'">Registrar</button>
        <button onclick="window.location.href='login.php'">Login</button>
      <?php endif; ?>
    </div>
  </header>

  <section class="admin-section">
    <div class="admin-container">
        <div class="admin-header">
            <h2>Painel de Gestão de Empresas</h2>
            <a href="adicionar_empresa.php" class="btn-add">+ Nova Empresa</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Localização</th>
                    <th>Avaliação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empresas as $e): ?>
                <tr>
                    <td data-label="Nome"><?php echo htmlspecialchars($e['nome']); ?></td>
                    <td data-label="Localização"><?php echo htmlspecialchars($e['localizacao']); ?></td>
                    <td data-label="Avaliação">⭐ <?php echo number_format($e['avaliacao_media'], 1); ?></td>
                    <td class="actions">
                        <a href="editar_empresa.php?id=<?php echo $e['id_empresa']; ?>" class="btn-edit">Editar</a>
                        <a onclick="abrirConfirmacao('<?php echo $e['id_empresa']; ?>', '<?php echo htmlspecialchars(addslashes($e['nome'])); ?>')" class="btn-del">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
  </section>

  <div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
      <h3>Confirmar Remoção</h3>
      <p>Tem a certeza que deseja eliminar a empresa <strong id="nomeEmpresaModal"></strong>?<br>Esta ação não pode ser desfeita.</p>
      <div class="modal-buttons">
        <button class="modal-btn modal-btn-cancel" onclick="fecharConfirmacao()">Cancelar</button>
        <button class="modal-btn modal-btn-confirm" id="btnConfirmarDelete">Eliminar</button>
      </div>
    </div>
  </div>

  <footer>
    <div class="footer-links">
      <a href="index.php">Início</a>
      <a href="listar_empresas.php">Empresas</a> 
      <a href="sobre.php">Sobre</a>
    </div>
    <div class="copy">
      &copy; <?php echo date("Y"); ?> OceanBlue Pool - Área Administrativa
    </div>
  </footer>

  <script>
    function abrirConfirmacao(id, nome) {
      // Injeta o nome da empresa correspondente no texto do modal
      document.getElementById('nomeEmpresaModal').innerText = nome;
      
      // Define dinamicamente o link de redirecionamento com o ID correto no botão de confirmação
      document.getElementById('btnConfirmarDelete').onclick = function() {
        window.location.href = 'eliminar_empresa.php?id=' + id;
      };

      // Mostra o pop-up com efeito suave
      document.getElementById('deleteModal').classList.add('active');
    }

    function fecharConfirmacao() {
      // Oculta o pop-up
      document.getElementById('deleteModal').classList.remove('active');
    }
  </script>

</body>
</html>