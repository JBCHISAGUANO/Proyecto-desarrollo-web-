<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
solo_invitados();

$error  = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $error = 'La solicitud no es válida. Recarga la página e inténtalo de nuevo.';
    } else {
        $correo   = mb_strtolower(limpiar($_POST['correo'] ?? ''));
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        if ($correo === '' || $password === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $error = 'Ingresa tu correo electrónico y tu contraseña.';
        } else {
            $st = db()->prepare('SELECT id, nombre, correo, password FROM usuarios WHERE correo = :c LIMIT 1');
            $st->execute([':c' => $correo]);
            $usuario = $st->fetch();

            // Si el correo no existe se verifica contra un hash falso para que el
            // tiempo de respuesta sea similar y no se pueda enumerar usuarios.
            $hash = $usuario['password'] ?? '$2y$10$usesomesillystringforsaleuF3m2Qe8z8tG2pI3o1o2t9uXH5eG';

            if (password_verify($password, $hash) && $usuario) {
                // Actualiza el hash si PHP ahora recomienda un algoritmo/costo más fuerte
                if (password_needs_rehash($usuario['password'], PASSWORD_DEFAULT)) {
                    $up = db()->prepare('UPDATE usuarios SET password = :p WHERE id = :id');
                    $up->execute([':p' => password_hash($password, PASSWORD_DEFAULT), ':id' => $usuario['id']]);
                }
                iniciar_sesion_usuario($usuario);
                redirigir('panel.php');
            }
            // Mensaje único: no indica si falló el correo o la contraseña
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}

$titulo = 'Iniciar sesión';
require __DIR__ . '/includes/header.php';
?>
<section class="tarjeta tarjeta-angosta">
    <h1>Iniciar sesión</h1>
    <?php if ($error): ?>
        <div class="alerta alerta-error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="login.php" novalidate>
        <?= csrf_campo() ?>

        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo" maxlength="150" required autofocus value="<?= e($correo) ?>">

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" maxlength="72" required autocomplete="current-password">

        <button class="btn" type="submit">Entrar</button>
    </form>
    <p class="centro">¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
