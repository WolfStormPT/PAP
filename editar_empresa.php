<?php
require_once "verificar_login.php";
protegerPagina('admin');
require_once "conexao.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Carregar dados atuais
$sql = "SELECT * FROM empresas WHERE id_empresa = $id";
$res = mysqli_query($ligaDB, $sql);
$empresa = mysqli_fetch_assoc($res);

if (!$empresa) { die("Empresa não encontrada."); }

// Processar atualização
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $local = $_POST['localizacao'];
    $desc = $_POST['descricao'];

    $sql_up = "UPDATE empresas SET nome = ?, localizacao = ?, descricao = ? WHERE id_empresa = ?";
    $stmt = mysqli_prepare($ligaDB, $sql_up);
    mysqli_stmt_bind_param($stmt, "sssi", $nome, $local, $desc, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Dados atualizados!'); window.location.href='admin_empresas.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Empresa</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; display: flex; justify-content: center; padding: 50px; }
        .form-box { background: white; padding: 30px; border-radius: 8px; width: 500px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { background: #005792; color: white; border: none; padding: 12px; width: 100%; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Editar: <?php echo htmlspecialchars($empresa['nome']); ?></h2>
        <form method="POST">
            <label>Nome da Empresa:</label>
            <input type="text" name="nome" value="<?php echo htmlspecialchars($empresa['nome']); ?>" required>
            
            <label>Localização:</label>
            <input type="text" name="localizacao" value="<?php echo htmlspecialchars($empresa['localizacao']); ?>" required>
            
            <label>Descrição:</label>
            <textarea name="descricao" rows="5"><?php echo htmlspecialchars($empresa['descricao']); ?></textarea>
            
            <button type="submit">Guardar Alterações</button>
        </form>
        <br>
        <a href="admin_empresas.php" style="color: #666; text-decoration: none;">Cancelar</a>
    </div>
</body>
</html>