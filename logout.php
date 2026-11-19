<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if (usuario_autenticado()) {
    cerrar_sesion();
    flash('exito', 'Cerraste sesión correctamente.');
}
redirigir('login.php');
