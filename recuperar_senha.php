<?php
require_once "conexao.php";

$mensagem = "";
$status = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validação do input
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Por favor, introduza um e-mail válido.";
        $status = "erro";
    } else {
        // Verifica a existência do cliente 
        $sql = "SELECT id_cliente FROM clientes WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($ligaDB, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($resultado)) {
            // Gera o token único 
            $token = bin2hex(random_bytes(32));
            $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Atualiza a base de dados com o token e validade
            $sql_update = "UPDATE clientes SET reset_token = ?, reset_token_expira = ? WHERE email = ?";
            $stmt_update = mysqli_prepare($ligaDB, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "sss", $token, $expira, $email);
            
            if (mysqli_stmt_execute($stmt_update)) {
                $diretorio = dirname($_SERVER['PHP_SELF']);
                $url_base = "http://" . $_SERVER['HTTP_HOST'] . ($diretorio == DIRECTORY_SEPARATOR ? "" : $diretorio);
                $link = $url_base . "/nova_senha.php?token=" . $token;

                // Mensagem de sucesso com link de simulação
                $mensagem = "Link de redefinição gerado!<br>";
                $mensagem .= "<a href='$link' class='sim-link'>[Simular clique no E-mail]</a>";
                $status = "sucesso";
            } else {
                $mensagem = "Erro técnico ao gerar pedido. Tente mais tarde.";
                $status = "erro";
            }
        } else {
            $mensagem = "Este e-mail não está registado no sistema.";
            $status = "erro";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar Senha - OceanBlue Pool</title>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #004d66, #0099cc);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .container {
      background: white;
      padding: 30px;
      width: 350px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      text-align: center;
    }

    h2 {
      color: #003366;
      margin-bottom: 10px;
    }

    p {
      color: #666;
      margin-bottom: 20px;
      font-size: 14px;
      line-height: 1.4;
    }

    input[type="email"] {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
      margin-bottom: 20px;
      background-color: #f9f9f9;
      box-sizing: border-box;
    }

    .btn {
      width: 100%;
      padding: 12px;
      background: #005792;
      color: white;
      border: none;
      cursor: pointer;
      border-radius: 6px;
      font-size: 16px;
      transition: background 0.3s;
      font-weight: bold;
    }

    .btn:hover {
      background: #003f6b;
    }

    .message {
      margin-top: 15px;
      padding: 12px;
      border-radius: 6px;
      font-size: 14px;
      font-weight: bold;
      word-wrap: break-word;
      line-height: 1.5;
      margin-bottom: 20px;
      color: <?php echo ($status == "sucesso") ? "#155724" : "#721c24"; ?>;
      background-color: <?php echo ($status == "sucesso") ? "#d4edda" : "#f8d7da"; ?>;
      border: 1px solid <?php echo ($status == "sucesso") ? "#c3e6cb" : "#f5c6cb"; ?>;
    }

    .sim-link {
      display: block;
      margin-top: 8px;
      color: #003366;
      text-decoration: underline;
      font-size: 12px;
    }

    .back-link {
      color: #005792;
      font-size: 14px;
      text-decoration: none;
      display: inline-block;
      margin-top: 20px;
    }

    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Recuperar Senha</h2>
    <p>Introduza o seu e-mail para receber as instruções de redefinição.</p>

    <?php if (!empty($mensagem)): ?>
      <div class="message">
        <?php echo $mensagem; ?>
      </div>
    <?php endif; ?>

    <form action="recuperar_senha.php" method="POST">
      <input type="email" name="email" placeholder="O seu e-mail" required>
      <button type="submit" class="btn">Enviar Pedido</button>
    </form>

    <a href="login.php" class="back-link">← Voltar para o login</a>
  </div>
</body>
</html>