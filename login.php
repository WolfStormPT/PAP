<?php
session_start(); // Inicia a sessão para utilizar variáveis de sessão
require_once "conexao.php"; // Importa o arquivo de conexão com o banco de dados

$erro = ""; // Inicializa a variável de erro

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Verifica se o formulário foi enviado via método POST
    $email = trim($_POST["email"]); // Captura o e-mail do formulário e remove espaços em branco
    $senha = $_POST["senha"]; // Captura a senha do formulário

    // CORREÇÃO AQUI: Adicionar 'user_type' na query
    $sql = "SELECT id_cliente, nome, senha, user_type FROM clientes WHERE email = ?";
    $stmt = mysqli_prepare($ligaDB, $sql); // Prepara a query para evitar SQL Injection
    mysqli_stmt_bind_param($stmt, "s", $email); // Substitui o ? pelo valor do e-mail
    mysqli_stmt_execute($stmt); // Executa a query
    mysqli_stmt_store_result($stmt); // Armazena os resultados

    if (mysqli_stmt_num_rows($stmt) > 0) { // Verifica se encontrou algum usuário
        
        // CORREÇÃO AQUI: Adicionar a nova variável $user_type
        mysqli_stmt_bind_result($stmt, $id_cliente, $nome, $senha_hash, $user_type); // Associa os dados retornados às variáveis
        mysqli_stmt_fetch($stmt); // Busca os dados

        // Verifica se a senha digitada bate com a hash do banco
        if (password_verify($senha, $senha_hash)) {
            
            // CORREÇÃO AQUI: Armazena o 'user_type' na sessão
            $_SESSION["usuario"] = [
                "id_cliente" => $id_cliente, 
                "nome" => $nome, 
                "email" => $email,
                "user_type" => $user_type // AGORA ESTÁ COMPLETO
            ]; 
            
            header("Location: index.php"); // Redireciona para a página principal
            exit(); 
        } else {
            $erro = "E-mail ou senha incorretos!"; // Mensagem de erro se a senha estiver errada
        }
    } else {
        $erro = "E-mail ou senha incorretos!"; // Mensagem de erro se o e-mail não for encontrado
    }
    mysqli_stmt_close($stmt);
}

// Fechar conexão aqui, se o HTML for exibido
if (isset($ligaDB)) {
    mysqli_close($ligaDB);
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8"> <!-- Define o conjunto de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - OceanBlue Pool</title> 
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> <!-- Ícones FontAwesome -->
    <style>
        /* Estilização global */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        /* Corpo da página */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #004d66, #0099cc); /* Fundo gradiente azul */
        }

        /* Container do formulário */
        .container {
            background: white;
            padding: 30px;
            width: 350px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Sombra suave */
            text-align: center;
        }

        /* Título */
        h2 {
            color: #003366;
            margin-bottom: 10px;
        }

        /* Subtítulo */
        p {
            color: #666;
            margin-bottom: 20px;
        }

        /* Grupo de inputs */
        .input-group {
            position: relative;
            margin-bottom: 15px;
        }

        /* Ícone dentro do input */
        .input-group i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
        }

        /* Input de e-mail e senha */
        .input-group input {
            width: 100%;
            padding: 12px 40px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            background-color: #f9f9f9;
        }

        /* Botão de login */
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
            background: #003f6b; /* Cor ao passar o mouse */
        }

        /* Links de recuperação e registro */
        .toggle-link {
            display: block;
            margin-top: 10px;
            color: #005792;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }

        .toggle-link:hover {
            text-decoration: underline;
        }

        /* Classe auxiliar para esconder elementos */
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Formulário de login -->
        <form id="login-form" action="login.php" method="POST">
            <h2>Bem-vindo</h2>
            <p>Faça login para continuar</p>

            <!-- Exibe erro caso haja -->
            <?php if (isset($erro)) echo "<p class='error' style='color:red;'>$erro</p>"; ?>

            <!-- Campo de e-mail -->
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="E-mail" required>
            </div>

            <!-- Campo de senha -->
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="senha" placeholder="Senha" required>
            </div>

            <!-- Botão de envio -->
            <button type="submit" class="btn">Entrar</button>

            <!-- Link de recuperação de senha -->
            <a class="toggle-link" href="recuperar_senha.php">Esqueceu a senha?</a>

            <!-- Link para registro -->
            <p class="toggle-link">Ainda não tem conta? <a href="registar.php">Registre-se</a></p>
        </form>
    </div>
</body>
</html>
