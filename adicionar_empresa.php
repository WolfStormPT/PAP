<?php
session_start();
require_once "conexao.php"; // Tua conexão mysqli procedural

// --- 1. VERIFICAR PERMISSÃO DE ADMIN ---
// Se não estiver logado ou não for admin, redireciona.
// Nota: Tu deves ter um utilizador onde 'user_type' é 'admin' para testar esta página.
// Como não temos o header/footer, vamos assumir que o admin tem de estar logado.
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['user_type'] !== 'admin') {
    // Para simplificar o teste, podes comentar o 'exit()' se não tiveres um admin configurado.
    //header("Location: index.php"); 
    //exit();
}

$erro = "";
$sucesso = "";

// --- 2. OBTER SERVIÇOS DISPONÍVEIS ---
$sql_servicos = "SELECT id_servico, nome_servico FROM serviços ORDER BY nome_servico";
$resultado_servicos = mysqli_query($ligaDB, $sql_servicos);
$servicos_disponiveis = mysqli_fetch_all($resultado_servicos, MYSQLI_ASSOC);


// --- 3. PROCESSAMENTO DO FORMULÁRIO ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpeza e coleta dos dados
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $localizacao = trim($_POST['localizacao']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $site = trim($_POST['site']);
    $servicos_selecionados = $_POST['servicos'] ?? [];
    $imagem = $_POST['imagem'] ?? 'assets/default_empresa.png';

    if (empty($nome) || empty($descricao) || empty($localizacao) || empty($email)) {
        $erro = "Os campos Nome, Descrição, Localização e Email são obrigatórios.";
    } elseif (empty($servicos_selecionados)) {
        $erro = "Deve selecionar pelo menos um serviço oferecido pela empresa.";
    } else {
        // Início da transação para garantir que ambas as inserções (empresa e serviços) funcionem
        mysqli_begin_transaction($ligaDB);

        try {
            // A. INSERIR NA TABELA EMPRESAS
            $sql_empresa = "INSERT INTO empresas (nome, descricao, localizacao, telefone, email, site, imagem) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_empresa = mysqli_prepare($ligaDB, $sql_empresa);
            
            mysqli_stmt_bind_param($stmt_empresa, "sssssss", $nome, $descricao, $localizacao, $telefone, $email, $site, $imagem);
            
            if (!mysqli_stmt_execute($stmt_empresa)) {
                throw new Exception("Erro ao inserir dados da empresa: " . mysqli_error($ligaDB));
            }
            $id_empresa = mysqli_insert_id($ligaDB);

            // B. INSERIR NA TABELA EMPRESA_SERVICOS (ligação N:N)
            $sql_servico = "INSERT INTO empresa_servicos (id_empresa, id_servico) VALUES (?, ?)";
            $stmt_servico = mysqli_prepare($ligaDB, $sql_servico);

            foreach ($servicos_selecionados as $id_servico) {
                // 'ii' significa que ambos os parâmetros são inteiros
                mysqli_stmt_bind_param($stmt_servico, "ii", $id_empresa, $id_servico);
                if (!mysqli_stmt_execute($stmt_servico)) {
                    throw new Exception("Erro ao ligar serviço: " . mysqli_error($ligaDB));
                }
            }

            // Se tudo correu bem, faz commit e confirma o sucesso
            mysqli_commit($ligaDB);
            $sucesso = "Empresa '$nome' e os seus serviços foram cadastrados com sucesso!";

            // Limpa as variáveis para o formulário ficar limpo
            unset($nome, $descricao, $localizacao, $telefone, $email, $site, $servicos_selecionados);

        } catch (Exception $e) {
            mysqli_rollback($ligaDB); // Se houver erro, desfaz todas as alterações
            $erro = "Falha no cadastro: " . $e->getMessage();
        }
    }
}

mysqli_close($ligaDB); // Fecha a conexão
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Empresa - Admin</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="styles.css"> 
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Arial', sans-serif; }
        body { background: #f4f4f4; padding-top: 50px; }
        .container { 
            background: white; padding: 40px; margin: 0 auto 50px; 
            width: 80%; max-width: 900px; 
            border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); 
        }
        h2 { color: #003366; margin-bottom: 25px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
        input[type="text"], input[type="email"], input[type="url"], input[type="tel"], textarea {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px; background-color: #f9f9f9;
        }
        textarea { resize: vertical; min-height: 100px; }
        .checkbox-group { 
            display: flex; flex-wrap: wrap; gap: 15px; 
            border: 1px solid #ddd; padding: 15px; border-radius: 6px; 
        }
        .checkbox-item { display: flex; align-items: center; }
        .checkbox-item input { margin-right: 8px; width: auto; }
        .btn { 
            width: 100%; padding: 12px; background: #005792; color: white; border: none; 
            cursor: pointer; border-radius: 6px; font-size: 16px; transition: background 0.3s; 
            margin-top: 15px;
        }
        .btn:hover { background: #003f6b; }
        .message-erro { color: red; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .message-sucesso { color: green; margin-bottom: 20px; text-align: center; font-weight: bold; }
        
        /* Layout de duas colunas para campos básicos */
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-row > .form-group { flex: 1; }
        
        /* Ajuste para telas menores */
        @media (max-width: 600px) {
            .form-row { flex-direction: column; }
        }

    </style>
</head>
<body>
    
    <div class="container">
        <h2>Adicionar Nova Empresa Parceira</h2>

        <?php 
        // Exibe mensagens de erro ou sucesso
        if (!empty($erro)) { echo "<p class='message-erro'>$erro</p>"; } 
        if (!empty($sucesso)) { echo "<p class='message-sucesso'>$sucesso</p>"; } 
        ?>

        <form action="adicionar_empresa.php" method="POST">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="nome">Nome da Empresa</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição (Serviços e Foco)</label>
                <textarea id="descricao" name="descricao" required><?php echo htmlspecialchars($descricao ?? ''); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="localizacao">Localização/Morada</label>
                    <input type="text" id="localizacao" name="localizacao" value="<?php echo htmlspecialchars($localizacao ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="tel" id="telefone" name="telefone" value="<?php echo htmlspecialchars($telefone ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="site">Site da Empresa (URL)</label>
                <input type="url" id="site" name="site" value="<?php echo htmlspecialchars($site ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Serviços Oferecidos:</label>
                <div class="checkbox-group">
                    <?php 
                    foreach ($servicos_disponiveis as $servico): 
                        // Mantém a seleção se houver um erro de validação
                        $checked = isset($servicos_selecionados) && in_array($servico['id_servico'], $servicos_selecionados) ? 'checked' : '';
                    ?>
                        <div class="checkbox-item">
                            <input type="checkbox" id="servico_<?php echo $servico['id_servico']; ?>" 
                                   name="servicos[]" value="<?php echo $servico['id_servico']; ?>" <?php echo $checked; ?>>
                            <label for="servico_<?php echo $servico['id_servico']; ?>"><?php echo htmlspecialchars($servico['nome_servico']); ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn">Cadastrar Empresa</button>
        </form>
    </div>

    </body>
</html>