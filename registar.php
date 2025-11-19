<?php
session_start(); // Inicia a sessão
require_once "conexao.php"; // Conexão com o banco de dados

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Verifica se o formulário foi enviado
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);
    $confirmar_senha = trim($_POST["confirmar_senha"]);

    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $erro = "Todos os campos são obrigatórios!";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem!";
    } else {
        // Verifica se o e-mail já está cadastrado
        $sql = "SELECT id_cliente FROM clientes WHERE email = ?";
        $stmt = mysqli_prepare($ligaDB, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $erro = "E-mail já cadastrado!";
            } else {
                // Cria o hash da senha e insere o novo usuário
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $sql = "INSERT INTO clientes (nome, email, senha) VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($ligaDB, $sql);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "sss", $nome, $email, $senha_hash);
                    if (mysqli_stmt_execute($stmt)) {
                        $_SESSION["usuario"] = ["id_cliente" => mysqli_insert_id($ligaDB), "nome" => $nome, "email" => $email];
                        header("Location: index.php");
                        exit();
                    } else {
                        $erro = "Erro ao registrar. Tente novamente!";
                    }
                } else {
                    $erro = "Erro na preparação da query.";
                }
            }
            mysqli_stmt_close($stmt);
        } else {
            $erro = "Erro na preparação da consulta.";
        }
    }
}

mysqli_close($ligaDB); // Fecha a conexão com o banco
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar - OceanBlue Pool</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="styles.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Arial', sans-serif; }
        body { display: flex; justify-content: center; align-items: center; height: 100vh; background: linear-gradient(135deg, #004d66, #0099cc); }
        .container { background: white; padding: 30px; width: 350px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); text-align: center; }
        h2 { color: #003366; margin-bottom: 10px; }
        p { color: #666; margin-bottom: 20px; }
        .input-group { position: relative; margin-bottom: 15px; }
        .input-group i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #777; }
        .input-group input { width: 100%; padding: 12px 40px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px; background-color: #f9f9f9; }
        .btn { width: 100%; padding: 12px; background: #005792; color: white; border: none; cursor: pointer; border-radius: 6px; font-size: 16px; transition: background 0.3s; }
        .btn:hover { background: #003f6b; }
        .toggle-link { display: block; margin-top: 10px; color: #005792; text-decoration: none; font-size: 14px; cursor: pointer; }
        .toggle-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <form id="register-form" action="registar.php" method="POST">
            <h2>Crie sua conta</h2>
            <p>Preencha os campos abaixo para se registrar</p>
            <?php if (isset($erro)) echo "<p class='error' style='color:red;'>$erro</p>"; ?>

            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="nome" placeholder="Nome" required>
            </div>

            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="E-mail" required>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="senha" placeholder="Senha" required>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="confirmar_senha" placeholder="Confirmar Senha" required>
            </div>

            <button type="submit" class="btn">Registrar</button>
            <p class="toggle-link">Já tem uma conta? <a href="login.php">Faça login</a></p>
        </form>
    </div>
</body>
</html>
