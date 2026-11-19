<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
requiere_login();

$id = filter_var($_GET['id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false || $id === null) {
    contacto_no_encontrado();
}

$contacto = contacto_del_usuario($id);
if ($contacto === null) {
    contacto_no_encontrado();
}

// La eliminación solo se ejecuta por POST (con token CSRF) tras la
// pantalla de confirmación. Un enlace GET nunca borra datos.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        flash('error', 'La solicitud no es válida. Inténtalo de nuevo.');
        redirigir('panel.php');
    }
    $del = db()->prepare('DELETE FROM contactos WHERE id = :id AND id_usuario = :u');
    $del->execute([':id' => $id, ':u' => $_SESSION['id_usuario']]);
    flash('exito', 'Contacto eliminado correctamente.');
    redirigir('panel.php');
}

$titulo = 'Eliminar contacto';
require __DIR__ . '/includes/header.php';
?>
<section class="tarjeta tarjeta-angosta">
    <h1>Eliminar contacto</h1>
    <p>¿Seguro que deseas eliminar este contacto? Esta acción no se puede deshacer.</p>
    <ul class="detalle">
        <li><strong>Nombre:</strong> <?= e($contacto['nombre']) ?></li>
        <li><strong>Teléfono:</strong> <?= e($contacto['telefono']) ?></li>
        <li><strong>Correo:</strong> <?= e($contacto['correo']) ?></li>
        <li><strong>Ciudad:</strong> <?= e($contacto['ciudad']) ?></li>
    </ul>
    <form method="post" action="contacto_eliminar.php?id=<?= (int) $id ?>">
        <?= csrf_campo() ?>
        <div class="acciones">
            <button class="btn btn-peligro" type="submit">Sí, eliminar</button>
            <a class="btn btn-sec" href="panel.php">Cancelar</a>
        </div>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
