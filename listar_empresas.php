<?php
session_start();
require_once "conexao.php"; // Tua conexão mysqli procedural

// Variáveis para filtros
$filtro_servico_id = isset($_GET['servico_id']) ? intval($_GET['servico_id']) : 0;

// --- A. OBTER TODOS OS SERVIÇOS (PARA O FILTRO) ---
$sql_servicos = "SELECT id_servico, nome_servico FROM serviços ORDER BY nome_servico";
$resultado_servicos = mysqli_query($ligaDB, $sql_servicos);
$servicos_disponiveis = mysqli_fetch_all($resultado_servicos, MYSQLI_ASSOC);


// --- B. CONSTRUIR A QUERY PRINCIPAL PARA LISTAR EMPRESAS ---
$query = "
    SELECT 
        e.id_empresa, 
        e.nome, 
        e.descricao, 
        e.localizacao,
        e.avaliacao_media,
        e.imagem,
        GROUP_CONCAT(s.nome_servico SEPARATOR ', ') AS servicos_oferecidos
    FROM 
        empresas e
    LEFT JOIN 
        empresa_servicos es ON e.id_empresa = es.id_empresa
    LEFT JOIN 
        serviços s ON es.id_servico = s.id_servico
";

// Adicionar a lógica de filtro
if ($filtro_servico_id > 0) {
    // A query deve filtrar apenas empresas que oferecem o ID de serviço selecionado
    $query .= " WHERE e.id_empresa IN (
        SELECT id_empresa FROM empresa_servicos WHERE id_servico = ?
    )";
}


// Agrupar e Ordenar
$query .= " GROUP BY e.id_empresa 
             ORDER BY e.avaliacao_media DESC, e.nome ASC";


// --- C. EXECUTAR A QUERY ---
// Esta parte requer ajuste para lidar com o filtro
if ($filtro_servico_id > 0) {
    $stmt = mysqli_prepare($ligaDB, $query);
    mysqli_stmt_bind_param($stmt, "i", $filtro_servico_id);
    mysqli_stmt_execute($stmt);
    $resultado_empresas = mysqli_stmt_get_result($stmt);
} else {
    $resultado_empresas = mysqli_query($ligaDB, $query);
}

$empresas = mysqli_fetch_all($resultado_empresas, MYSQLI_ASSOC);


mysqli_close($ligaDB);
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encontre a Melhor Empresa de Piscinas</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <style>
        /* INÍCIO DO CSS DO LISTAR_EMPRESAS.PHP (estilos base + listagem) */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Arial', sans-serif; }
        :root { --fundo: #73b6fa; --cor-primaria: #005792; }
        
        body { background: #f4f4f4; color: #333; }
        
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
        
        /* LISTAGEM */
        .page-title { text-align: center; color: var(--cor-primaria); padding: 30px 20px 0; font-size: 28px; }
        .container-main { max-width: 1200px; margin: 20px auto 40px; padding: 0 20px; display: flex; gap: 30px; }
        
        /* Sidebar de Filtros */
        .sidebar { flex: 0 0 250px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); height: fit-content; }
        .sidebar h3 { color: #005792; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .filter-item { margin-bottom: 5px; }
        .filter-item a { text-decoration: none; color: #333; display: block; padding: 5px; border-radius: 4px; transition: background 0.2s; }
        .filter-item a:hover { background: #e0f3ff; color: #005792; }
        .filter-item a.active { background: #005792; color: white; font-weight: bold; }

        /* Área de Listagem */
        .listing-area { flex-grow: 1; }
        .empresa-card { background: white; padding: 25px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: flex; gap: 20px; align-items: flex-start; }
        .empresa-card h2 { color: #005792; margin-top: 0; font-size: 24px; }
        .empresa-card p { font-size: 14px; color: #555; margin: 5px 0; }
        .empresa-info { flex-grow: 1; }
        .empresa-image { width: 100px; height: 100px; border-radius: 8px; object-fit: cover; border: 1px solid #ccc; }
        .rating { font-size: 18px; color: #ffcc00; margin-bottom: 10px; }
        .rating span { color: #333; font-weight: bold; margin-left: 5px; }
        .servicos-tag { display: inline-block; background: #e0f3ff; color: #005792; padding: 3px 8px; border-radius: 4px; font-size: 12px; margin-right: 5px; margin-top: 5px; }
        
        /* Media Queries */
        @media (max-width: 800px) {
            .container-main { flex-direction: column; }
            .sidebar { margin-bottom: 20px; }
            .empresa-card { flex-direction: column; align-items: center; text-align: center; }
            .empresa-image { margin-bottom: 15px; }
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
            <a href="produtos.php">Produtos</a>
            <a href="piscinas.php">Piscinas</a>
            <a href="orcamento.php">Orçamento</a>
            <a href="servicos.php">Serviços</a>
            <a href="listar_empresas.php">Empresas</a> 
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
    <h1 class="page-title">Encontre a Empresa Ideal para a sua Piscina</h1>

    <div class="container-main">
        
        <div class="sidebar">
            <h3>Filtrar por Serviço:</h3>
            <div class="filter-item">
                <a href="listar_empresas.php" class="<?php echo ($filtro_servico_id == 0) ? 'active' : ''; ?>">
                    Mostrar Todas
                </a>
            </div>
            
            <?php foreach ($servicos_disponiveis as $servico): ?>
                <div class="filter-item">
                    <a href="listar_empresas.php?servico_id=<?php echo $servico['id_servico']; ?>"
                       class="<?php echo ($filtro_servico_id == $servico['id_servico']) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($servico['nome_servico']); ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="listing-area">
            <?php if (count($empresas) > 0): ?>
                <?php foreach ($empresas as $empresa): ?>
                    <div class="empresa-card">
                        
                        <img src="<?php echo htmlspecialchars($empresa['imagem'] ?: 'assets/default_empresa.png'); ?>" 
                             alt="Logo da <?php echo htmlspecialchars($empresa['nome']); ?>" 
                             class="empresa-image">

                        <div class="empresa-info">
                            <h2><?php echo htmlspecialchars($empresa['nome']); ?></h2>
                            
                            <div class="rating">
                                <?php 
                                $rating = round($empresa['avaliacao_media']);
                                for ($i = 1; $i <= 5; $i++) {
                                    echo ($i <= $rating) ? '★' : '☆';
                                }
                                ?> (<?php echo number_format($empresa['avaliacao_media'], 2); ?>) 
                            </div>
                            
                            <p><strong>Localização:</strong> <?php echo htmlspecialchars($empresa['localizacao']); ?></p>
                            <p><?php echo nl2br(htmlspecialchars(substr($empresa['descricao'], 0, 100))) . (strlen($empresa['descricao']) > 100 ? '...' : ''); ?></p>
                            
                            <div>
                                <?php 
                                // Divide a string de serviços e exibe como tags
                                $servicos_array = explode(', ', $empresa['servicos_oferecidos']);
                                foreach ($servicos_array as $servico_tag) {
                                    echo '<span class="servicos-tag">' . htmlspecialchars($servico_tag) . '</span>';
                                }
                                ?>
                            </div>
                            
                        </div>

                        <a href="empresa.php?id=<?php echo $empresa['id_empresa']; ?>" class="btn">
                            Ver Detalhes
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhuma empresa encontrada com os filtros selecionados.</p>
            <?php endif; ?>
        </div>

    </div>
    
    </body>
</html>