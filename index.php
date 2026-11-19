<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

redirigir(usuario_autenticado() ? 'panel.php' : 'login.php');
