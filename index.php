<?php

/**
 * Laravel - Shared Hosting Bootstrap
 * 
 * Este arquivo permite que o Laravel funcione em hospedagem compartilhada
 * onde o servidor procura por index.php na raiz do projeto.
 * 
 * Não altere este arquivo. A lógica real está em /public/index.php
 */

// Define o diretório base para o Laravel reconhecer o subdiretório
$_SERVER['SCRIPT_NAME'] = '/chelbe/index.php';
$_SERVER['PHP_SELF'] = '/chelbe/index.php';

// Remove o prefixo /chelbe da REQUEST_URI se presente
// para que as rotas do Laravel funcionem corretamente
$uri = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($uri, '/chelbe') === 0) {
    $_SERVER['REQUEST_URI'] = substr($uri, strlen('/chelbe')) ?: '/';
}

// Redireciona para o index.php real no diretório public
require __DIR__ . '/public/index.php';

