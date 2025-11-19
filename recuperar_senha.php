<?php
require_once "conexao.php"; // Arquivo de conexão com banco

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    // Verifica se o e-mail existe
    $sql = "SELECT nome FROM clientes WHERE email = ?";
    $stmt = mysqli_prepare($ligaDB, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $nome);
        mysqli_stmt_fetch($stmt);

        // Aqui você pode enviar um e-mail com um token único para redefinir a senha
        // Exemplo: gerar token e redirecionar para nova_senha.php?token=XYZ
        // Simulação simples de envio
        $mensagem = "Um link de redefinição foi enviado para seu e-mail.";

        // Aqui você pode adicionar o envio real de e-mail.
    } else {
        $mensagem = "E-mail não encontrado no sistema.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
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
    }

    input[type="email"] {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
      margin-bottom: 20px;
      background-color: #f9f9f9;
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
    }

    .btn:hover {
      background: #003f6b;
    }

    .message {
      margin-top: 15px;
      color: #004d66;
      font-weight: bold;
    }

    a {
      color: #005792;
      font-size: 14px;
      text-decoration: none;
      display: inline-block;
      margin-top: 15px;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Recuperar Senha</h2>
    <p>Digite seu e-mail para redefinir sua senha</p>

    <?php if (!empty($mensagem)) echo "<div class='message'>$mensagem</div>"; ?>

    <form action="recuperar_senha.php" method="POST">
      <input type="email" name="email" placeholder="Seu e-mail" required>
      <button type="submit" class="btn">Enviar</button>
    </form>

    <a href="login.php">← Voltar para o login</a>
  </div>
</body>
</html>
