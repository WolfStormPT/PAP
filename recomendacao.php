<?php
session_start();
require_once "conexao.php"; 

$recomendacoes = [];
$servicos_disponiveis = [];
$erro = "";
$foco_escolhido = "";
$servico_id_escolhido = 0;
$tipo_piscina_escolhido = "";

// Variáveis para guardar a localização do utilizador enviada pelo JS
$user_lat = isset($_POST['user_lat']) ? floatval($_POST['user_lat']) : 0;
$user_lng = isset($_POST['user_lng']) ? floatval($_POST['user_lng']) : 0;

// =========================================================================
// CORREÇÃO: MECANISMO DE FALLBACK PARA COMPUTAÇÃO COM BLOQUEIO DE PRIVACIDADE
// Se o sistema operativo bloquear as coordenadas (virem a 0), injetamos Lisboa.
// =========================================================================
if ($user_lat === 0.0 && $user_lng === 0.0) {
    $user_lat = 38.7223; // Centro Geográfico de Lisboa
    $user_lng = -9.1393;
}

// --- 1. OBTER SERVIÇOS DISPONÍVEIS ---
$sql_servicos = "SELECT id_servico, nome_servico FROM serviços ORDER BY nome_servico";
$resultado_servicos = mysqli_query($ligaDB, $sql_servicos);
$servicos_disponiveis = mysqli_fetch_all($resultado_servicos, MYSQLI_ASSOC);


// --- 2. PROCESSAR O FORMULÁRIO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_recomendacao'])) {
    
    $servico_id_escolhido = isset($_POST['servico_id']) ? intval($_POST['servico_id']) : 0;
    $foco_escolhido = $_POST['foco'] ?? 'avaliacao';
    $tipo_piscina_escolhido = trim($_POST['tipo_piscina'] ?? '');
    
    if ($servico_id_escolhido === 0) {
        $erro = "Por favor, selecione o tipo de serviço que precisa.";
    } else {
        
        // --- 3. CONSTRUIR A CONSULTA COM A FÓRMULA DE HAVERSINE ---
        $query = "
            SELECT 
                e.id_empresa, 
                e.nome, 
                e.localizacao,
                e.avaliacao_media,
                e.descricao,
                GROUP_CONCAT(s.nome_servico SEPARATOR ', ') AS servicos_oferecidos,
                (6371 * ACOS(
                    COS(RADIANS(?)) * COS(RADIANS(e.latitude)) * COS(RADIANS(e.longitude) - RADIANS(?)) + 
                    SIN(RADIANS(?)) * SIN(RADIANS(e.latitude))
                )) AS distancia_km
            FROM 
                empresas e
            JOIN 
                empresa_servicos es ON e.id_empresa = es.id_empresa
            JOIN 
                serviços s ON es.id_servico = s.id_servico
            WHERE 
                es.id_servico = ? 
        ";
        
        $params = [$user_lat, $user_lng, $user_lat, $servico_id_escolhido];
        $types = "dddi"; 

        if (!empty($tipo_piscina_escolhido) && $tipo_piscina_escolhido !== 'nao_quero') {
            $query .= " AND e.descricao LIKE ?";
            $params[] = "%" . $tipo_piscina_escolhido . "%";
            $types .= "s";
        }
        
        $query .= " GROUP BY e.id_empresa";
        
        // ORDENAÇÃO INTELIGENTE
        if ($foco_escolhido === 'avaliacao') {
            $query .= " ORDER BY e.avaliacao_media DESC, e.nome ASC";
        } elseif ($foco_escolhido === 'localizacao') {
            $query .= " ORDER BY distancia_km ASC, e.avaliacao_media DESC";
        }
        
        $query .= " LIMIT 3";

        // --- 4. EXECUTAR A CONSULTA ---
        $stmt = mysqli_prepare($ligaDB, $query);
        
        if (!empty($types)) {
            $bind_params = array_merge([$types], $params);
            $refs = [];
            foreach ($bind_params as $key => $value) {
                $refs[$key] = &$bind_params[$key];
            }
            call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $refs));
        }

        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $recomendacoes = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    }
}

mysqli_close($ligaDB);
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conselheiro de Empresas de Piscinas</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Arial', sans-serif; }
        :root { --cor-principal: #005792; --cor-secundaria: #ffcc00; --fundo: #f4f4f4; }
        
        body { background: var(--fundo); color: #333; min-height: 100vh; display: flex; flex-direction: column; }
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; flex: 1; }
        
        header { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 15px 50px; background: #005792; color: white; flex-wrap: wrap; 
        }
        .logo a { display: flex; align-items: center; text-decoration: none; color: white; }
        .logo img { height: 50px; margin-right: 10px; }
        nav { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
        nav a { color: white; text-decoration: none; font-weight: bold; margin: 0 10px; }
        .auth-buttons button { background: white; color: #005792; border: none; padding: 8px 15px; cursor: pointer; border-radius: 5px; font-weight: bold; }
        .admin-btn { background: #ffcc00 !important; color: #005792 !important; }
        
        .form-section { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .form-section h2 { color: var(--cor-principal); border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
        select, .radio-group { padding: 10px; border: 1px solid #ccc; border-radius: 6px; background-color: #f9f9f9; width: 100%; max-width: 400px; }
        .radio-group label { display: inline-block; font-weight: normal; margin-right: 20px; cursor: pointer; }

        .btn { background: var(--cor-principal); color: white; border: none; padding: 12px 30px; border-radius: 6px; cursor: pointer; margin-top: 15px; font-weight: bold; }
        .btn:hover { background: #003f6b; }
        .message-erro { color: red; margin-bottom: 20px; text-align: center; font-weight: bold; }

        .results-section { margin-top: 40px; }
        .results-section h2 { color: #388e3c; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        
        .empresa-card { 
            background: white; padding: 20px; border-radius: 8px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 15px; 
            border-left: 5px solid var(--cor-secundaria); position: relative; transition: transform 0.2s; cursor: pointer; 
        }
        .empresa-card:hover { transform: translateY(-5px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .card-link { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10; }

        .empresa-card h3 { color: var(--cor-principal); margin-bottom: 5px; position: relative; z-index: 2; }
        .rating { color: var(--cor-secundaria); font-weight: bold; margin-bottom: 5px; position: relative; z-index: 2; }
        .servicos-tag { display: inline-block; background: #e0f3ff; color: var(--cor-principal); padding: 3px 8px; border-radius: 4px; font-size: 12px; margin-right: 5px; margin-top: 5px; position: relative; z-index: 11; }
        .recommendation-reason { font-style: italic; margin-top: 10px; color: #555; position: relative; z-index: 2; }
        .top-label { background: #ffcc00; color: #005792; padding: 3px 8px; border-radius: 4px; font-weight: bold; margin-bottom: 5px; display: inline-block; position: relative; z-index: 2; }

        .match-badge { float: right; background: #388e3c; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; position: relative; z-index: 2; }
        .distancia-badge { float: right; background: #005792; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; position: relative; z-index: 2; margin-right: 10px; }

        footer { background: #004d80; color: #e0f3ff; padding: 25px 20px; text-align: center; margin-top: auto; font-size: 14px; }
        footer .footer-links a { color: #cce7f5; margin: 0 12px; text-decoration: none; }
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
            <?php 
            if (isset($_SESSION['usuario']) && isset($_SESSION['usuario']['user_type']) && $_SESSION['usuario']['user_type'] === 'admin') {
                echo '<button onclick="window.location.href=\'admin_empresas.php\'" class="admin-btn">⚙️ Painel Admin</button>';
            }
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
        <div class="form-section">
            <h2>Diga-nos o que precisa:</h2>
            <?php if (!empty($erro)) { echo "<p class='message-erro'>$erro</p>"; } ?>
            
            <form action="recomendacao.php" method="POST" id="formRecomendacao">
                <!-- CAMPOS OCULTOS PARA GUARDAR A GEOLOCALIZAÇÃO -->
                <input type="hidden" name="user_lat" id="user_lat" value="0">
                <input type="hidden" name="user_lng" id="user_lng" value="0">

                <div class="form-group">
                    <label for="servico_id">1. Qual tipo de serviço você procura?</label>
                    <select id="servico_id" name="servico_id" required>
                        <option value="">-- Selecione um Serviço --</option>
                        <?php foreach ($servicos_disponiveis as $servico): ?>
                            <option value="<?php echo $servico['id_servico']; ?>" <?php echo ($servico_id_escolhido == $servico['id_servico']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($servico['nome_servico']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tipo_piscina">2. Se for instalação/manutenção, qual o tipo de piscina?</label>
                    <select id="tipo_piscina" name="tipo_piscina">
                        <option value="">-- Qualquer Tipo --</option>
                        <option value="nao_quero" <?php echo ($tipo_piscina_escolhido === 'nao_quero') ? 'selected' : ''; ?>>Não quero / Não se aplica</option>
                        <option value="Betão" <?php echo ($tipo_piscina_escolhido === 'Betão') ? 'selected' : ''; ?>>Betão / Alvenaria</option>
                        <option value="Fibra" <?php echo ($tipo_piscina_escolhido === 'Fibra') ? 'selected' : ''; ?>>Fibra</option>
                        <option value="Vinil" <?php echo ($tipo_piscina_escolhido === 'Vinil') ? 'selected' : ''; ?>>Vinil</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>3. Qual é o seu foco principal?</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="foco" value="avaliacao" required <?php echo ($foco_escolhido === 'avaliacao' || $foco_escolhido === '') ? 'checked' : ''; ?>>
                            Melhor Avaliação / Qualidade
                        </label>
                        <label>
                            <input type="radio" name="foco" value="localizacao" id="radioLocalizacao" required <?php echo ($foco_escolhido === 'localizacao') ? 'checked' : ''; ?>>
                            Empresa Mais Próxima (Geolocalização)
                        </label>
                    </div>
                </div>

                <button type="submit" name="submit_recomendacao" class="btn">
                    Encontrar as Melhores Opções
                </button>
            </form>
        </div>

        <?php if (!empty($recomendacoes)): ?>
            <div class="results-section">
                <h2>⭐ Top <?php echo count($recomendacoes); ?> Empresas Recomendadas</h2>

                <?php foreach ($recomendacoes as $key => $empresa): 
                    $match = ($empresa['avaliacao_media'] / 5) * 100;
                ?>
                    <div class="empresa-card">
                        <a href="empresa.php?id=<?php echo $empresa['id_empresa']; ?>" class="card-link"></a>

                        <div class="match-badge">
                            <?php echo round($match); ?>% Match
                        </div>

                        <?php if (isset($empresa['distancia_km']) && $empresa['distancia_km'] !== null): ?>
                            <div class="distancia-badge">
                                📍 a <?php echo number_format($empresa['distancia_km'], 1); ?> km
                            </div>
                        <?php endif; ?>

                        <div class="top-label">
                             Recomendação #<?php echo $key + 1; ?>
                        </div>
                        
                        <h3><?php echo htmlspecialchars($empresa['nome']); ?></h3>
                        <div class="rating">Nota Média: <?php echo number_format($empresa['avaliacao_media'], 2); ?></div>
                        <p style="position:relative; z-index:2;"><strong>Localização:</strong> <?php echo htmlspecialchars($empresa['localizacao']); ?></p>

                        <div>
                            <?php 
                            $servicos_array = explode(', ', $empresa['servicos_oferecidos']);
                            foreach ($servicos_array as $servico_tag) {
                                echo '<span class="servicos-tag">' . htmlspecialchars($servico_tag) . '</span>';
                            }
                            ?>
                        </div>

                        <div class="recommendation-reason">
                            <?php 
                            if ($foco_escolhido === 'localizacao') {
                                if ($key === 0) {
                                    echo "Esta é a empresa parceira mais próxima calculada a partir de Lisboa (Ponto de Origem Padrão) que realiza o serviço pretendido.";
                                } else {
                                    echo "Excelente alternativa regional mapeada pelo nosso conselheiro.";
                                }
                            } else {
                                if ($key === 0) {
                                    echo "É a nossa melhor escolha com base nas suas preferências e avaliação global.";
                                } else {
                                    echo "Alta classificação de clientes que procuram qualidade e consistência no serviço.";
                                }
                            }
                            ?>
                        </div>
                        <div class="btn" style="width: auto; margin-top: 15px; display: inline-block; position: relative; z-index: 5;">Ver Detalhes e Contactar</div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && empty($erro)): ?>
            <div class="results-section">
                <p>Nenhuma empresa encontrada que corresponda aos serviços e tipo de piscina solicitados.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <footer>
        <div class="footer-links">
            <a href="index.php">Início</a>
            <a href="listar_empresas.php">Empresas</a>
            <a href="sobre.php">Sobre</a>
            <a href="contato.php">Contato</a>
        </div>
        <div class="copy">&copy; <?php echo date("Y"); ?> OceanBlue Pool - Todos os direitos reservados</div>
    </footer>

    <script>
        window.onload = function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('user_lat').value = position.coords.latitude;
                    document.getElementById('user_lng').value = position.coords.longitude;
                }, function(error) {
                    console.log("Localização bloqueada. O sistema assumirá Lisboa automaticamente.");
                });
            }
        };
    </script>
</body>
</html>