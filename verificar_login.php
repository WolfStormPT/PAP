<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Função para proteger páginas
 * @param string $tipo_requerido 
 */
function protegerPagina($tipo_requerido = null) {
    // Verifica se o utilizador está logado
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php?erro=restrito");
        exit();
    }

    // Se a página for apenas para Admin e o utilizador for Cliente
    if ($tipo_requerido === 'admin' && $_SESSION['usuario']['user_type'] !== 'admin') {
        header("Location: index.php?erro=sem_permissao");
        exit();
    }
}
?>