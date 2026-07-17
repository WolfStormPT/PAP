<?php
session_start();
require_once "conexao.php"; 

require_once "verificar_login.php";
protegerPagina('admin'); // Verifica se está logado E se é admin

// --- 1. VERIFICAR PERMISSÃO DE ADMIN ---
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['user_type'] !== 'admin') {
    // Redirecionamento gerido pelo verificar_login.php
}

$erro = "";
$sucesso = "";

// --- 2. Obtem serviços desponiveis ---
$sql_servicos = "SELECT id_servico, nome_servico FROM serviços ORDER BY nome_servico";
$resultado_servicos = mysqli_query($ligaDB, $sql_servicos);
$servicos_disponiveis = mysqli_fetch_all($resultado_servicos, MYSQLI_ASSOC);


// --- 3. Processamento do formulário ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $localizacao = trim($_POST['localizacao']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $site = trim($_POST['site']);
    $servicos_selecionados = $_POST['servicos'] ?? [];
    $imagem = $_POST['imagem'] ?? 'assets/default_empresa.png';
    $latitude = isset($_POST['latitude']) && $_POST['latitude'] !== "" ? floatval($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) && $_POST['longitude'] !== "" ? floatval($_POST['longitude']) : null;

    if (empty($nome) || empty($descricao) || empty($localizacao) || empty($email)) {
        $erro = "Os campos Nome, Descrição, Localização e Email são obrigatórios.";
    } elseif (empty($servicos_selecionados)) {
        $erro = "Deve selecionar pelo menos um serviço oferecido pela empresa.";
    } else {
        mysqli_begin_transaction($ligaDB);

        try {
            $sql_empresa = "INSERT INTO empresas (nome, descricao, localizacao, telefone, email, site, imagem, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_empresa = mysqli_prepare($ligaDB, $sql_empresa);
            
            // "sssssssdd" -> adicionamos 'd' (double) para latitude e longitude
            mysqli_stmt_bind_param($stmt_empresa, "sssssssdd", $nome, $descricao, $localizacao, $telefone, $email, $site, $imagem, $latitude, $longitude);
            
            if (!mysqli_stmt_execute($stmt_empresa)) {
                throw new Exception("Erro ao inserir dados da empresa: " . mysqli_error($ligaDB));
            }
            $id_empresa = mysqli_insert_id($ligaDB);

            $sql_servico = "INSERT INTO empresa_servicos (id_empresa, id_servico) VALUES (?, ?)";
            $stmt_servico = mysqli_prepare($ligaDB, $sql_servico);

            foreach ($servicos_selecionados as $id_servico) {
                mysqli_stmt_bind_param($stmt_servico, "ii", $id_empresa, $id_servico);
                if (!mysqli_stmt_execute($stmt_servico)) {
                    throw new Exception("Erro ao ligar serviço: " . mysqli_error($ligaDB));
                }
            }

            mysqli_commit($ligaDB);
            $sucesso = "Empresa '$nome' e os seus serviços foram cadastrados com sucesso!";
            unset($nome, $descricao, $localizacao, $telefone, $email, $site, $servicos_selecionados);

        } catch (Exception $e) {
            mysqli_rollback($ligaDB); 
            $erro = "Falha no cadastro: " . $e->getMessage();
        }
    }
}

mysqli_close($ligaDB); 
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
        
        body { 
            background: linear-gradient(135deg, #004d66, #0099cc); 
            padding-top: 50px; 
            min-height: 100vh;
        }

        .container { 
            background: white; padding: 40px; margin: 0 auto 50px; 
            width: 80%; max-width: 900px; 
            border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
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
            margin-top: 15px; font-weight: bold;
        }
        .btn:hover { background: #003f6b; }
        .message-erro { color: red; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .message-sucesso { color: green; margin-bottom: 20px; text-align: center; font-weight: bold; }
        
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-row > .form-group { flex: 1; }
        
        /* Estilo do aviso de carregamento geográfico */
        .geo-status { font-size: 13px; font-weight: bold; margin-top: 5px; color: #005792; display: none; }

        @media (max-width: 600px) {
            .form-row { flex-direction: column; }
        }
    </style>
</head>
<body>
    
    <div class="container">
        <h2>Adicionar Nova Empresa Parceira</h2>

        <?php 
        if (!empty($erro)) { echo "<p class='message-erro'>$erro</p>"; } 
        if (!empty($sucesso)) { echo "<p class='message-sucesso'>$sucesso</p>"; } 
        ?>

        <form action="adicionar_empresa.php" method="POST" id="formEmpresa">
            
            <input type="hidden" id="latitude" name="latitude" value="">
            <input type="hidden" id="longitude" name="longitude" value="">

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
                    <label for="localizacao">Localização/Morada (Ex: Coimbra, Portugal)</label>
                    <input type="text" id="localizacao" name="localizacao" value="<?php echo htmlspecialchars($localizacao ?? ''); ?>" onchange="buscarCoordenadas()" required>
                    <div id="geo-status" class="geo-status">A mapear coordenadas geográficas...</div>
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

    <script>
    function buscarCoordenadas() {
        const localizacao = document.getElementById('localizacao').value.trim();
        const statusDiv = document.getElementById('geo-status');
        const inputLat = document.getElementById('latitude');
        const inputLng = document.getElementById('longitude');

        if (localizacao.length < 3) return;

        statusDiv.style.display = 'block';
        statusDiv.style.color = '#005792';
        statusDiv.innerText = "🌐 A localizar coordenadas...";

        // Faz uma requisição assíncrona (Fetch API) para o serviço Nominatim do OpenStreetMap
        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(localizacao)}&limit=1`;

        fetch(url, {
            headers: { 'User-Agent': 'OceanBluePoolPAP' } // Boa prática exigida pela API
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                // Injeta as coordenadas encontradas diretamente nos inputs ocultos
                inputLat.value = data[0].lat;
                inputLng.value = data[0].lon;
                
                statusDiv.style.color = 'green';
                statusDiv.innerText = `✅ Coordenadas mapeadas! (Lat: ${parseFloat(data[0].lat).toFixed(4)}, Lng: ${parseFloat(data[0].lon).toFixed(4)})`;
            } else {
                // Caso o administrador digite algo irreconhecível, limpamos os campos
                inputLat.value = "";
                inputLng.value = "";
                statusDiv.style.color = '#orange';
                statusDiv.innerText = "⚠️ Localização textual guardada, mas coordenadas exatas não mapeadas.";
            }
        })
        .catch(error => {
            console.error("Erro na geocodificação:", error);
            statusDiv.style.color = 'red';
            statusDiv.innerText = "❌ Falha na ligação ao serviço de mapas.";
        });
    }
    </script>
</body>
</html>