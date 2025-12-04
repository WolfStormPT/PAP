<?php
session_start();
require_once "conexao.php"; 

// 1. Obter o ID da empresa do URL
// Nota: id_cliente é agora id na sessão
$id_empresa = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_cliente = isset($_SESSION['usuario']['id_cliente']) ? $_SESSION['usuario']['id_cliente'] : 0; 

if ($id_empresa === 0) {
    header("Location: listar_empresas.php");
    exit;
}

$mensagem = "";
$erro_avaliacao = "";
$cliente_ja_avaliou = false;


// --- FUNÇÃO PARA RECALCULAR A MÉDIA ---
function recalcular_media($ligaDB, $id_empresa) {
    // 1. Calcular a nova média
    $sql_media = "SELECT AVG(classificacao) AS nova_media FROM avaliacoes WHERE id_empresa = ?";
    $stmt_media = mysqli_prepare($ligaDB, $sql_media);
    mysqli_stmt_bind_param($stmt_media, "i", $id_empresa);
    mysqli_stmt_execute($stmt_media);
    $resultado_media = mysqli_stmt_get_result($stmt_media);
    $media_data = mysqli_fetch_assoc($resultado_media);
    $nova_media = $media_data['nova_media'] ?? 0;
    
    // 2. Atualizar a tabela empresas
    $sql_update = "UPDATE empresas SET avaliacao_media = ? WHERE id_empresa = ?";
    $stmt_update = mysqli_prepare($ligaDB, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "di", $nova_media, $id_empresa);
    mysqli_stmt_execute($stmt_update);
}


// --- 2. PROCESSAR SUBMISSÃO DA AVALIAÇÃO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_avaliacao'])) {
    if ($id_cliente === 0) {
        $erro_avaliacao = "Precisa de estar logado para avaliar!";
    } else {
        $classificacao = intval($_POST['classificacao']);
        $comentario = trim($_POST['comentario']);

        if ($classificacao < 1 || $classificacao > 5) {
            $erro_avaliacao = "A classificação deve ser entre 1 e 5 estrelas.";
        } else {
            // Tenta inserir/atualizar a avaliação
            $sql_insert = "
                INSERT INTO avaliacoes (id_empresa, id_cliente, classificacao, comentario) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    classificacao = VALUES(classificacao), 
                    comentario = VALUES(comentario),
                    data_avaliacao = CURRENT_TIMESTAMP
            ";
            
            $stmt_insert = mysqli_prepare($ligaDB, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "iiss", $id_empresa, $id_cliente, $classificacao, $comentario);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                recalcular_media($ligaDB, $id_empresa); // Recalcula após nova avaliação
                $mensagem = "A sua avaliação foi submetida com sucesso!";
            } else {
                $erro_avaliacao = "Erro ao submeter avaliação: " . mysqli_error($ligaDB);
            }
        }
    }
}


// --- 3. OBTER DETALHES DA EMPRESA (COM SERVIÇOS AGRUPADOS) ---
$sql_empresa = "
    SELECT 
        e.*,
        GROUP_CONCAT(s.nome_servico SEPARATOR ', ') AS servicos_oferecidos
    FROM 
        empresas e
    LEFT JOIN 
        empresa_servicos es ON e.id_empresa = es.id_empresa
    LEFT JOIN 
        serviços s ON es.id_servico = s.id_servico
    WHERE 
        e.id_empresa = ?
    GROUP BY 
        e.id_empresa
";

$stmt_empresa = mysqli_prepare($ligaDB, $sql_empresa);
mysqli_stmt_bind_param($stmt_empresa, "i", $id_empresa);
mysqli_stmt_execute($stmt_empresa);
$resultado_empresa = mysqli_stmt_get_result($stmt_empresa);
$empresa = mysqli_fetch_assoc($resultado_empresa);

if (!$empresa) {
    echo "<p>Empresa não encontrada.</p>";
    exit;
}

// --- 4. VERIFICAR SE O CLIENTE JÁ AVALIOU ---
$avaliacao_cliente = null;
if ($id_cliente > 0) {
    $sql_check = "SELECT classificacao, comentario FROM avaliacoes WHERE id_empresa = ? AND id_cliente = ?";
    $stmt_check = mysqli_prepare($ligaDB, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "ii", $id_empresa, $id_cliente);
    mysqli_stmt_execute($stmt_check);
    $resultado_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($resultado_check) > 0) {
        $cliente_ja_avaliou = true;
        $avaliacao_cliente = mysqli_fetch_assoc($resultado_check);
    }
}

// --- 5. OBTER TODAS AS AVALIAÇÕES PARA LISTAGEM ---
$sql_avaliacoes = "
    SELECT 
        a.classificacao, 
        a.comentario, 
        a.data_avaliacao, 
        c.nome AS nome_cliente 
    FROM 
        avaliacoes a
    JOIN 
        clientes c ON a.id_cliente = c.id_cliente
    WHERE 
        a.id_empresa = ?
    ORDER BY 
        a.data_avaliacao DESC
";
$stmt_avaliacoes = mysqli_prepare($ligaDB, $sql_avaliacoes);
mysqli_stmt_bind_param($stmt_avaliacoes, "i", $id_empresa);
mysqli_stmt_execute($stmt_avaliacoes);
$resultado_avaliacoes = mysqli_stmt_get_result($stmt_avaliacoes);
$avaliacoes = mysqli_fetch_all($resultado_avaliacoes, MYSQLI_ASSOC);

mysqli_close($ligaDB);
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Empresa - <?php echo htmlspecialchars($empresa['nome']); ?></title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* CSS Base para corresponder ao teu estilo OceanBlue */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Arial', sans-serif; }
        :root { --cor-principal: #005792; --cor-secundaria: #ffcc00; --fundo: #f4f4f4; }
        body { background: var(--fundo); color: #333; }
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        
        /* ESTILOS DO HEADER (COPIADOS DO INDEX.PHP) */
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
        nav { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
        nav a { color: white; text-decoration: none; font-weight: bold; margin: 0 10px; transition: color 0.3s; }
        nav a:hover { color: #ffcc00; }
        .auth-buttons { display: flex; align-items: center; gap: 10px; margin-left: 20px; }
        .auth-buttons button { background: white; color: #005792; border: none; padding: 8px 15px; cursor: pointer; border-radius: 5px; font-weight: bold; transition: background 0.3s; }
        .auth-buttons button:hover { background: #e0e0e0; }
        .admin-btn { background: #ffcc00 !important; color: #005792 !important; transition: background 0.3s; }
        .admin-btn:hover { background: #e0b300 !important; }
        /* FIM ESTILOS DO HEADER */

        /* Cartão de Detalhes da Empresa */
        .empresa-details { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 30px; display: flex; flex-wrap: wrap; gap: 30px; }
        .empresa-logo { max-width: 150px; height: auto; border-radius: 8px; object-fit: cover; }
        .empresa-info-main { flex: 1; min-width: 300px; }
        .empresa-info-main h1 { color: var(--cor-principal); margin-top: 0; font-size: 32px; }
        .rating-box { font-size: 24px; color: var(--cor-secundaria); margin: 10px 0 20px; }
        .rating-box span { color: #333; font-size: 18px; margin-left: 10px; font-weight: bold; }
        .contact-info p { margin: 5px 0; font-size: 16px; }
        .tag { display: inline-block; background: #e0f3ff; color: var(--cor-principal); padding: 5px 10px; border-radius: 4px; font-size: 13px; margin-right: 5px; margin-top: 10px; }
        
        /* Seção de Avaliação */
        .review-section { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .review-section h2 { color: var(--cor-principal); border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        
        /* Formulário de Avaliação */
        .star-rating { direction: rtl; display: inline-block; }
        .star-rating input { display: none; }
        .star-rating label { 
            font-size: 30px; 
            color: #ccc; 
            cursor: pointer; 
            padding: 0 2px;
            transition: color 0.2s;
        }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label { color: var(--cor-secundaria); }

        .review-form textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-top: 10px; resize: vertical; }
        .review-form button { background: var(--cor-principal); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-top: 10px; transition: background 0.3s; }
        .review-form button:hover { background: #003f6b; }
        
        /* Lista de Avaliações */
        .review-list { padding-top: 10px; }
        .review-item { border-left: 3px solid var(--cor-principal); padding-left: 15px; margin-bottom: 20px; background: #fafafa; padding: 15px; border-radius: 4px; }
        .review-item .name { font-weight: bold; color: var(--cor-principal); }
        .review-item .date { font-size: 12px; color: #999; margin-left: 10px; }
        .review-item .stars { color: var(--cor-secundaria); font-size: 16px; margin: 5px 0; }
        .review-item p { margin-top: 5px; font-size: 14px; }

        /* Mensagens de Sucesso/Erro */
        .message-box.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .message-box.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px; }

        /* Media Query para responsividade do header */
        @media (max-width: 768px) {
            header { flex-direction: column; gap: 10px; padding: 15px 20px; }
            nav { flex-direction: column; gap: 5px; }
            .auth-buttons { margin-left: 0; margin-top: 10px; }
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
            <a href="piscinas.php">Piscinas</a>
            <a href="orcamento.php">Orçamento</a>
            <a href="servicos.php">Serviços</a>
        </nav>

        <div class="auth-buttons">
            <?php 
            // BOTÃO ADMIN: Verificação de segurança
            if (isset($_SESSION['usuario']) && isset($_SESSION['usuario']['user_type']) && $_SESSION['usuario']['user_type'] === 'admin') {
                echo '<button onclick="window.location.href=\'adicionar_empresa.php\'" class="admin-btn">+ Adicionar Empresa</button>';
            }
            
            // BOTÕES DE AUTENTICAÇÃO
            if (isset($_SESSION['usuario'])) { ?>
                <span>Olá, <?php echo htmlspecialchars($_SESSION['usuario']['nome']); ?>!</span>
                <button onclick="window.location.href='logout.php'">Logout</button>
            <?php } else { ?>
                <button onclick="window.location.href='registar.php'">Registrar</button>
                <button onclick="window.location.href='login.php'">Login</button>
            <?php } ?>
        </div>
    </header>
    <div class="container">
        
        <div class="empresa-details">
            <img src="<?php echo htmlspecialchars($empresa['imagem'] ?: 'assets/default_empresa.png'); ?>" 
                 alt="Logo da <?php echo htmlspecialchars($empresa['nome']); ?>" 
                 class="empresa-logo">
            
            <div class="empresa-info-main">
                <h1><?php echo htmlspecialchars($empresa['nome']); ?></h1>
                
                <div class="rating-box">
                    <?php 
                    $avg_rating = round($empresa['avaliacao_media']);
                    for ($i = 1; $i <= 5; $i++) {
                        echo ($i <= $avg_rating) ? '★' : '☆';
                    }
                    ?>
                    <span><?php echo number_format($empresa['avaliacao_media'], 2); ?></span>
                </div>
                
                <p><?php echo nl2br(htmlspecialchars($empresa['descricao'])); ?></p>
                
                <div class="contact-info">
                    <p><strong>Localização:</strong> <?php echo htmlspecialchars($empresa['localizacao']); ?></p>
                    <p><strong>Telefone:</strong> <?php echo htmlspecialchars($empresa['telefone']); ?></p>
                    <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($empresa['email']); ?>"><?php echo htmlspecialchars($empresa['email']); ?></a></p>
                    <p><strong>Website:</strong> <a href="<?php echo htmlspecialchars($empresa['site']); ?>" target="_blank"><?php echo htmlspecialchars($empresa['site']); ?></a></p>
                </div>
                
                <div>
                    <?php 
                    // Exibe serviços como tags
                    $servicos_array = explode(', ', $empresa['servicos_oferecidos']);
                    foreach ($servicos_array as $servico_tag) {
                        echo '<span class="tag">' . htmlspecialchars($servico_tag) . '</span>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="review-section">
            <h2>Deixe a sua Avaliação</h2>
            
            <?php 
            if (!empty($mensagem)) echo "<div class='message-box success'>$mensagem</div>";
            if (!empty($erro_avaliacao)) echo "<div class='message-box error'>$erro_avaliacao</div>";
            
            if ($id_cliente > 0) {
                // Se o cliente já avaliou, mostra uma mensagem e carrega a avaliação existente
                if ($cliente_ja_avaliou) {
                    echo "<p>Você já avaliou esta empresa. Use o formulário abaixo para **atualizar** a sua nota.</p>";
                    $default_rating = $avaliacao_cliente['classificacao'];
                    $default_comentario = htmlspecialchars($avaliacao_cliente['comentario']);
                } else {
                    $default_rating = 0;
                    $default_comentario = "";
                }
                
                ?>
                <form action="empresa.php?id=<?php echo $id_empresa; ?>" method="POST" class="review-form">
                    
                    <div class="star-rating">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?php echo $i; ?>" name="classificacao" value="<?php echo $i; ?>" 
                                   <?php if ($default_rating == $i) echo 'checked'; ?> required>
                            <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> estrelas"><i class="fa fa-star"></i></label>
                        <?php endfor; ?>
                    </div>
                    
                    <textarea name="comentario" rows="4" placeholder="Escreva o seu comentário (opcional)"><?php echo $default_comentario; ?></textarea>
                    
                    <button type="submit" name="submit_avaliacao">
                        <?php echo $cliente_ja_avaliou ? 'Atualizar Avaliação' : 'Submeter Avaliação'; ?>
                    </button>
                </form>

            <?php } else { ?>
                <div class="message-box error">
                    <p>Faça <a href="login.php">login</a> para poder avaliar esta empresa.</p>
                </div>
            <?php } ?>
        </div>

        <div class="review-section">
            <h2>Avaliações de Clientes (<?php echo count($avaliacoes); ?>)</h2>
            
            <div class="review-list">
                <?php if (count($avaliacoes) > 0): ?>
                    <?php foreach ($avaliacoes as $avaliacao): ?>
                        <div class="review-item">
                            <span class="name"><?php echo htmlspecialchars($avaliacao['nome_cliente']); ?></span>
                            <span class="date">(<?php echo date('d/m/Y', strtotime($avaliacao['data_avaliacao'])); ?>)</span>
                            
                            <div class="stars">
                                <?php 
                                // Exibir estrelas (Classificação)
                                $rating = $avaliacao['classificacao'];
                                for ($i = 1; $i <= 5; $i++) {
                                    echo ($i <= $rating) ? '★' : '☆';
                                }
                                ?>
                            </div>
                            
                            <?php if (!empty($avaliacao['comentario'])): ?>
                                <p><?php echo nl2br(htmlspecialchars($avaliacao['comentario'])); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Seja o primeiro a avaliar esta empresa!</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    </body>
</html>