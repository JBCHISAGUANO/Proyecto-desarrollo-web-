<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
requiere_login();

// El ID viene de la URL (GET) o del campo oculto (POST); en ambos casos se
// verifica la propiedad contra el usuario en sesión antes de continuar.
$id = filter_var($_GET['id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false || $id === null) {
    contacto_no_encontrado();
}

$contacto = contacto_del_usuario($id);
if ($contacto === null) {
    contacto_no_encontrado();
}

$datos   = $contacto;
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $errores['general'] = 'La solicitud no es válida. Recarga la página e inténtalo de nuevo.';
    } else {
        [$datos, $errores] = validar_contacto($_POST);
        if (!$errores) {
            // La cláusula id_usuario garantiza que solo se modifiquen filas propias
            $up = db()->prepare('UPDATE contactos
                                    SET nombre = :n, telefono = :t, correo = :c, ciudad = :ci
                                  WHERE id = :id AND id_usuario = :u');
            $up->execute([
                ':n'  => $datos['nombre'],
                ':t'  => $datos['telefono'],
                ':c'  => $datos['correo'],
                ':ci' => $datos['ciudad'],
                ':id' => $id,
                ':u'  => $_SESSION['id_usuario'],
            ]);
            flash('exito', 'Contacto actualizado correctamente.');
            redirigir('panel.php');
        }
    }
}

$titulo     = 'Editar contacto';
$accion     = 'contacto_editar.php?id=' . $id;
$textoBoton = 'Guardar cambios';
require __DIR__ . '/includes/header.php';
?>
<section class="tarjeta tarjeta-angosta">
    <h1>Editar contacto</h1>
    <?php if (isset($errores['general'])): ?>
        <div class="alerta alerta-error" role="alert"><?= e($errores['general']) ?></div>
    <?php endif; ?>
    <?php require __DIR__ . '/includes/form_contacto.php'; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
