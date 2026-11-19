<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
requiere_login();

$datos   = ['nombre' => '', 'telefono' => '', 'correo' => '', 'ciudad' => ''];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $errores['general'] = 'La solicitud no es válida. Recarga la página e inténtalo de nuevo.';
    } else {
        [$datos, $errores] = validar_contacto($_POST);
        if (!$errores) {
            // id_usuario se toma SIEMPRE de la sesión, nunca del formulario
            $ins = db()->prepare('INSERT INTO contactos (id_usuario, nombre, telefono, correo, ciudad)
                                  VALUES (:u, :n, :t, :c, :ci)');
            $ins->execute([
                ':u'  => $_SESSION['id_usuario'],
                ':n'  => $datos['nombre'],
                ':t'  => $datos['telefono'],
                ':c'  => $datos['correo'],
                ':ci' => $datos['ciudad'],
            ]);
            flash('exito', 'Contacto registrado correctamente.');
            redirigir('panel.php');
        }
    }
}

$titulo     = 'Nuevo contacto';
$accion     = 'contacto_nuevo.php';
$textoBoton = 'Guardar contacto';
require __DIR__ . '/includes/header.php';
?>
<section class="tarjeta tarjeta-angosta">
    <h1>Nuevo contacto</h1>
    <?php if (isset($errores['general'])): ?>
        <div class="alerta alerta-error" role="alert"><?= e($errores['general']) ?></div>
    <?php endif; ?>
    <?php require __DIR__ . '/includes/form_contacto.php'; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
