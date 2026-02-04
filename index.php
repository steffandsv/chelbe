<?php

/**
 * Laravel - Shared Hosting Bootstrap
 * 
 * Este arquivo permite que o Laravel funcione em hospedagem compartilhada
 * onde o servidor procura por index.php na raiz do projeto.
 * 
 * Não altere este arquivo. A lógica real está em /public/index.php
 */

// Redireciona para o index.php real no diretório public
require __DIR__ . '/public/index.php';
