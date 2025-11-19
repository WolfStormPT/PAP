<?php
// Definição das constantes do banco de dados
define("SERVER", "localhost");
define("USERNAME", "root");
define("PASSWORD", "");
define("DATABASE", "pap");

// Conectar diretamente ao banco de dados
$ligaDB = mysqli_connect(SERVER, USERNAME, PASSWORD, DATABASE);

// Verificar se a conexão foi bem-sucedida
if (!$ligaDB) {
    die("ERRO! Falha na conexão: " . mysqli_connect_error());
}

// Definir charset para evitar problemas com acentuação
mysqli_set_charset($ligaDB, "utf8");

// Mensagem opcional para indicar sucesso
// echo "Conexão bem-sucedida!";
?>