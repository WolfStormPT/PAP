<?php
require_once "verificar_login.php";
protegerPagina('admin');
require_once "conexao.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // 1. Primeiro eliminar as relações (se tiveres a tabela empresa_servicos)
    mysqli_query($ligaDB, "DELETE FROM empresa_servicos WHERE id_empresa = $id");
    
    // 2. Eliminar a empresa
    $sql = "DELETE FROM empresas WHERE id_empresa = $id";
    if (mysqli_query($ligaDB, $sql)) {
        header("Location: admin_empresas.php?msg=eliminada");
    } else {
        echo "Erro ao eliminar empresa.";
    }
}
?>