<?php
require_once "verificar_login.php";
protegerPagina('admin'); // Segurança: Apenas administradores
require_once "conexao.php";

$id_empresa = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_empresa <= 0) {
    header("Location: admin_empresas.php");
    exit();
}

$erro = "";
$sucesso = "";

// --- Processamento do formulário ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $localizacao = trim($_POST['localizacao']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $site = trim($_POST['site']);
    $servicos_selecionados = $_POST['servicos'] ?? [];

    if (empty($nome) || empty($descricao) || empty($localizacao) || empty($email)) {
        $erro = "Os campos Nome, Descrição, Localização e Email são obrigatórios.";
    } elseif (empty($servicos_selecionados)) {
        $erro = "Deve selecionar pelo menos um serviço oferecido pela empresa.";
    } else {
        mysqli_begin_transaction($ligaDB);

        try {
            $sql_up = "UPDATE empresas SET nome = ?, descricao = ?, localizacao = ?, telefone = ?, email = ?, site = ? WHERE id_empresa = ?";
            $stmt_up = mysqli_prepare($ligaDB, $sql_up);
            mysqli_stmt_bind_param($stmt_up, "ssssssi", $nome, $descricao, $localizacao, $telefone, $email, $site, $id_empresa);
            
            if (!mysqli_stmt_execute($stmt_up)) {
                throw new Exception("Erro ao atualizar os dados da empresa.");
            }

            $sql_del_servicos = "DELETE FROM empresa_servicos WHERE id_empresa = ?";
            $stmt_del = mysqli_prepare($ligaDB, $sql_del_servicos);
            mysqli_stmt_bind_param($stmt_del, "i", $id_empresa);
            mysqli_stmt_execute($stmt_del);

            $sql_ins_servico = "INSERT INTO empresa_servicos (id_empresa, id_servico) VALUES (?, ?)";
            $stmt_ins = mysqli_prepare($ligaDB, $sql_ins_servico);

            foreach ($servicos_selecionados as $id_servico) {
                mysqli_stmt_bind_param($stmt_ins, "ii", $id_empresa, $id_servico);
                if (!mysqli_stmt_execute($stmt_ins)) {
                    throw new Exception("Erro ao atualizar os serviços da empresa.");
                }
            }

            mysqli_commit($ligaDB);
            $sucesso = "Empresa atualizada com sucesso!";
            
        } catch (Exception $e) {
            mysqli_rollback($ligaDB); // Desfaz tudo em caso de falha
            $erro = $e->getMessage();
        }
    }
}

$sql_empresa = "SELECT * FROM empresas WHERE id_empresa = ? LIMIT 1";
$stmt_empresa = mysqli_prepare($ligaDB, $sql_empresa);
mysqli_stmt_bind_param($stmt_empresa, "i", $id_empresa);
mysqli_stmt_execute($stmt_empresa);
$res_empresa = mysqli_stmt_get_result($stmt_empresa);
$empresa = mysqli_fetch_assoc($res_empresa);

if (!$empresa) {
    header("Location: admin_empresas.php");
    exit();
}

// --- Obtem todos os serviços do sistema ---
$sql_todos_servicos = "SELECT id_servico, nome_servico FROM serviços ORDER BY nome_servico";
$res_todos_servicos = mysqli_query($ligaDB, $sql_todos_servicos);
$servicos_disponiveis = mysqli_fetch_all($res_todos_servicos, MYSQLI_ASSOC);

$sql_servicos_atuais = "SELECT id_servico FROM empresa_servicos WHERE id_empresa = ?";
$stmt_atuais = mysqli_prepare($ligaDB, $sql_servicos_atuais);
mysqli_stmt_bind_param($stmt_atuais, "i", $id_empresa);
mysqli_stmt_execute($stmt_atuais);
$res_atuais = mysqli_stmt_get_result($stmt_atuais);
$servicos_id_atuais = [];
while ($row = mysqli_fetch_assoc($res_atuais)) {
    $servicos_id_atuais[] = $row['id_servico'];
}

mysqli_close($ligaDB);
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empresa - OceanBlue Pool</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Arial', sans-serif; }
        
        body { 
            background: #73b6fa; /* Mantido o azul padrão do teu site */
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
        .logo a { display: flex; align-items: center; text-decoration: none; color: white; }
        .logo img { height: 50px; margin-right: 10px; }
        nav a { color: white; text-decoration: none; font-weight: bold; margin: 0 10px; transition: color 0.3s; }
        nav a:hover { color: #ffcc00; }
        .auth-buttons button { background: white; color: #005792; border: none; padding: 8px 15px; cursor: pointer; border-radius: 5px; font-weight: bold; }

        .admin-section { flex: 1; padding: 40px 20px; }
        
        .container { 
            background: white; padding: 40px; margin: 0 auto; 
            width: 100%; max-width: 900px; 
            border-radius: 12px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); 
        }
        
        h2 { color: #005792; margin-bottom: 25px; border-bottom: 2px solid #005792; padding-bottom: 15px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
        
        input[type="text"], input[type="email"], input[type="url"], input[type="tel"], textarea {
            width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px; background-color: #f9f9f9; box-sizing: border-box;
        }
        textarea { resize: vertical; min-height: 120px; }
        
        .checkbox-group { 
            display: flex; flex-wrap: wrap; gap: 15px; 
            border: 1px solid #ddd; padding: 15px; border-radius: 6px; background: #fafafa;
        }
        .checkbox-item { display: flex; align-items: center; }
        .checkbox-item input { margin-right: 8px; cursor: pointer; }
        
        .btn-submit { 
            width: 100%; padding: 14px; background: #005792; color: white; border: none; 
            cursor: pointer; border-radius: 6px; font-size: 16px; font-weight: bold; transition: background 0.3s; 
            margin-top: 15px;
        }
        .btn-submit:hover { background: #003f6b; }
        
        .message-erro { color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; text-align: center; }
        .message-sucesso { color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; text-align: center; }
        
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-row > .form-group { flex: 1; }
        
        .back-link { display: inline-block; margin-top: 20px; color: #005792; text-decoration: none; font-weight: bold; }
        .back-link:hover { text-decoration: underline; }

        footer { background: #004d80; color: #e0f3ff; padding: 25px 20px; text-align: center; font-size: 14px; margin-top: 40px; }
        footer .footer-links a { color: #cce7f5; margin: 0 12px; text-decoration: none; }
        
        @media (max-width: 600px) { .form-row { flex-direction: column; } }
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
      <a href="orcamento.php">Orçamento</a>
    </nav>
    <div class="auth-buttons">
        <span style="margin-right:10px;">Olá, Admin!</span>
        <button onclick="window.location.href='logout.php'">Logout</button>
    </div>
  </header>

  <section class="admin-section">
    <div class="container">
        <h2>Editar Empresa Partner</h2>

        <?php 
        if (!empty($erro)) { echo "<div class='message-erro'>$erro</div>"; } 
        if (!empty($sucesso)) { echo "<div class='message-sucesso'>$sucesso</div>"; } 
        ?>

        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="nome">Nome da Empresa</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($empresa['nome']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email de Contacto</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($empresa['email']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição Detalhada</label>
                <textarea id="descricao" name="descricao" required><?php echo htmlspecialchars($empresa['descricao']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="localizacao">Localização/Distrito</label>
                    <input type="text" id="localizacao" name="localizacao" value="<?php echo htmlspecialchars($empresa['localizacao']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="tel" id="telefone" name="telefone" value="<?php echo htmlspecialchars($empresa['telefone']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="site">Website Oficial (URL)</label>
                <input type="url" id="site" name="site" value="<?php echo htmlspecialchars($empresa['site']); ?>">
            </div>

            <div class="form-group">
                <label>Serviços Prestados:</label>
                <div class="checkbox-group">
                    <?php 
                    foreach ($servicos_disponiveis as $servico): 
                        // Verifica se este ID de serviço específico está no array de serviços que a empresa já oferece
                        $checked = in_array($servico['id_servico'], $servicos_id_atuais) ? 'checked' : '';
                    ?>
                        <div class="checkbox-item">
                            <input type="checkbox" id="servico_<?php echo $servico['id_servico']; ?>" 
                                   name="servicos[]" value="<?php echo $servico['id_servico']; ?>" <?php echo $checked; ?>>
                            <label for="servico_<?php echo $servico['id_servico']; ?>">
                                <?php echo htmlspecialchars($servico['nome_servico']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn-submit">Guardar Alterações</button>
        </form>

        <a href="admin_empresas.php" class="back-link">← Voltar à Lista de Gestão</a>
    </div>
  </section>

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

</body>
</html>