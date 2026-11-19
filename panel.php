<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
requiere_login();

// Solo los contactos del usuario autenticado
$st = db()->prepare('SELECT id, nombre, telefono, correo, ciudad
                       FROM contactos
                      WHERE id_usuario = :u
                      ORDER BY nombre ASC');
$st->execute([':u' => $_SESSION['id_usuario']]);
$contactos = $st->fetchAll();

$titulo = 'Mi agenda';
require __DIR__ . '/includes/header.php';
?>
<section class="tarjeta">
    <h1>Bienvenido, <?= e($_SESSION['nombre']) ?></h1>
    <p><strong>Correo:</strong> <?= e($_SESSION['correo']) ?></p>
</section>

<section class="tarjeta">
    <div class="fila-titulo">
        <h2>Mis contactos (<?= count($contactos) ?>)</h2>
        <a class="btn" href="contacto_nuevo.php">+ Nuevo contacto</a>
    </div>

    <?php if (!$contactos): ?>
        <p class="vacio">Aún no tienes contactos registrados. Agrega el primero con el botón “Nuevo contacto”.</p>
    <?php else: ?>
        <div class="tabla-scroll">
        <table>
            <thead>
                <tr><th>Nombre</th><th>Teléfono</th><th>Correo</th><th>Ciudad</th><th>Acciones</th></tr>
            </thead>
            <tbody>
            <?php foreach ($contactos as $c): ?>
                <tr>
                    <td><?= e($c['nombre']) ?></td>
                    <td><?= e($c['telefono']) ?></td>
                    <td><?= e($c['correo']) ?></td>
                    <td><?= e($c['ciudad']) ?></td>
                    <td class="acciones">
                        <a class="btn btn-sec" href="contacto_editar.php?id=<?= (int) $c['id'] ?>">Editar</a>
                        <a class="btn btn-peligro" href="contacto_eliminar.php?id=<?= (int) $c['id'] ?>">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
