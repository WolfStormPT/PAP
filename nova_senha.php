<?php
require_once "conexao.php";

$token = $_GET['token'] ?? '';
$valido = false;

// Verifica se o token existe e se é válido 
if (!empty($token)) {
    // Procura um cliente que tenha este token
    $sql = "SELECT id_cliente FROM clientes WHERE reset_token = ? AND reset_token_expira > NOW()";
    $stmt = mysqli_prepare($ligaDB, $sql);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($resultado) > 0) {
        $user = mysqli_fetch_assoc($resultado);
        $valido = true;
    }
}

// Processa a troca de password
if ($_SERVER["REQUEST_METHOD"] == "POST" && $valido) {
    $nova_senha = $_POST['senha']; 
    
    // É gerado o HASH para que a senha não fique em texto limpo na base de dados
    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

    // Atualiza para a password encriptada e limpa o token
    $sql_up = "UPDATE clientes SET senha = ?, reset_token = NULL, reset_token_expira = NULL WHERE id_cliente = ?";
    $stmt_up = mysqli_prepare($ligaDB, $sql_up);
    
    if ($stmt_up) {
        // "s" para a string do hash, "i" para o ID inteiro
        mysqli_stmt_bind_param($stmt_up, "si", $senha_hash, $user['id_cliente']);
        
        if (mysqli_stmt_execute($stmt_up)) {
            echo "<script>alert('Senha alterada com sucesso! Agora está encriptada.'); window.location.href='login.php';</script>";
            exit();
        } else {
            echo "<script>alert('Erro ao atualizar a base de dados.');</script>";
        }
    } else {
        echo "<script>alert('Erro na preparação da consulta.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
  <meta charset="UTF-8">
  <title>Nova Senha - OceanBlue Pool</title>
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
    h2 { color: #003366; margin-bottom: 10px; }
    p { color: #666; margin-bottom: 20px; }
    input[type="password"] {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
      margin-bottom: 20px;
      box-sizing: border-box;
    }
    .btn {
      width: 100%;
      padding: 12px;
      background: #28a745; 
      color: white;
      border: none;
      cursor: pointer;
      border-radius: 6px;
      font-size: 16px;
      font-weight: bold;
    }
    .btn:hover { background: #218838; }
    .error-msg { color: #dc3545; font-weight: bold; }
  </style>
</head>
<body>
  <div class="container">
    <?php if ($valido): ?>
        <h2>Nova Senha</h2>
        <p>Introduza a sua nova palavra-passe abaixo.</p>
        
        <form method="POST">
            <input type="password" name="senha" placeholder="Nova Senha" required minlength="6">
            <button type="submit" class="btn">Atualizar Senha</button>
        </form>
    <?php else: ?>
        <h2 class="error-msg">Link Inválido</h2>
        <p>Este link de recuperação expirou ou é incorreto.</p>
        <a href="recuperar_senha.php" style="color:#005792; text-decoration:none;">Pedir novo link</a>
    <?php endif; ?>
  </div>
</body>
</html>