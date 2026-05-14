<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Função para proteger páginas
 * @param string $tipo_requerido (Opcional) 'admin' ou 'cliente'
 */
function protegerPagina($tipo_requerido = null) {
    // 1. Verifica se o utilizador está logado
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php?erro=restrito");
        exit();
    }

    // 2. Se a página for apenas para Admin e o utilizador for Cliente
    if ($tipo_requerido === 'admin' && $_SESSION['usuario']['user_type'] !== 'admin') {
        header("Location: index.php?erro=sem_permissao");
        exit();
    }
}
?>