<?php
session_start(); 
require_once "conexao.php"; 

$erro = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $email = trim($_POST["email"]); 
    $senha = $_POST["senha"]; 

    // Prepara a query para buscar os dados do utilizador 
    $sql = "SELECT id_cliente, nome, senha, user_type FROM clientes WHERE email = ? LIMIT 1";
    $stmt = mysqli_prepare($ligaDB, $sql); 
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email); 
        mysqli_stmt_execute($stmt); 
        $resultado = mysqli_stmt_get_result($stmt);

        // Verifica se o e-mail existe
        if ($user = mysqli_fetch_assoc($resultado)) { 
            
            // password_verify compara a senha digitada com o HASH da BD
            if (password_verify($senha, $user['senha'])) {
                
                // Se a password estiver correta, guardamos os dados na sessão
                $_SESSION["usuario"] = [
                    "id_cliente" => $user['id_cliente'], 
                    "nome"       => $user['nome'], 
                    "email"      => $email,
                    "user_type"  => $user['user_type'] 
                ]; 
                
                header("Location: index.php"); 
                exit(); 
            } else {
                $erro = "E-mail ou senha incorretos!"; 
            }
        } else {
            $erro = "E-mail ou senha incorretos!"; 
        }
        mysqli_stmt_close($stmt);
    } else {
        $erro = "Erro interno no servidor.";
    }
}

if (isset($ligaDB)) {
    mysqli_close($ligaDB);
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - OceanBlue Pool</title> 
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Arial', sans-serif; }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #004d66, #0099cc); 
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

        .input-group { position: relative; margin-bottom: 15px; }

        .input-group i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
        }

        .input-group input {
            width: 100%;
            padding: 12px 40px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
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
            font-weight: bold;
        }

        .btn:hover { background: #003f6b; }

        .toggle-link {
            display: block;
            margin-top: 15px;
            color: #005792;
            text-decoration: none;
            font-size: 14px;
        }

        .toggle-link:hover { text-decoration: underline; }
        .error { color: #dc3545; font-weight: bold; margin-bottom: 15px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <form id="login-form" action="login.php" method="POST">
            <h2>Bem-vindo</h2>
            <p>Faça login para continuar</p>

            <?php if (!empty($erro)) echo "<p class='error'>$erro</p>"; ?>

            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="E-mail" required>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="senha" placeholder="Senha" required>
            </div>

            <button type="submit" class="btn">Entrar</button>

            <a class="toggle-link" href="recuperar_senha.php">Esqueceu a senha?</a>

            <p style="margin-top: 15px; font-size: 14px; color: #666;">
                Ainda não tem conta? <a href="registar.php" style="color:#005792; text-decoration:none; font-weight:bold;">Registre-se</a>
            </p>
        </form>
    </div>
</body>
</html>