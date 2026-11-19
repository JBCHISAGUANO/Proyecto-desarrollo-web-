<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
solo_invitados();

$errores = [];
$datos   = ['nombre' => '', 'correo' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $errores['general'] = 'La solicitud no es válida. Recarga la página e inténtalo de nuevo.';
    } else {
        $datos['nombre'] = limpiar($_POST['nombre'] ?? '');
        $datos['correo'] = mb_strtolower(limpiar($_POST['correo'] ?? ''));
        $password  = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $confirmar = is_string($_POST['confirmar'] ?? null) ? $_POST['confirmar'] : '';

        // --- Validaciones en el servidor ---
        if (mb_strlen($datos['nombre']) < 3 || mb_strlen($datos['nombre']) > 100) {
            $errores['nombre'] = 'Ingresa tu nombre completo (entre 3 y 100 caracteres).';
        }
        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL) || mb_strlen($datos['correo']) > 150) {
            $errores['correo'] = 'Ingresa un correo electrónico válido.';
        }
        if (strlen($password) < 8 || strlen($password) > 72
            || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errores['password'] = 'La contraseña debe tener entre 8 y 72 caracteres e incluir letras y números.';
        }
        if ($password !== $confirmar) {
            $errores['confirmar'] = 'Las contraseñas no coinciden.';
        }

        // --- Correo no registrado previamente ---
        if (!$errores) {
            $st = db()->prepare('SELECT id FROM usuarios WHERE correo = :c LIMIT 1');
            $st->execute([':c' => $datos['correo']]);
            if ($st->fetch()) {
                $errores['correo'] = 'Este correo ya está registrado. Intenta iniciar sesión.';
            }
        }

        // --- Inserción con la contraseña protegida mediante password_hash() ---
        if (!$errores) {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins  = db()->prepare('INSERT INTO usuarios (nombre, correo, password)
                                       VALUES (:n, :c, :p)');
                $ins->execute([':n' => $datos['nombre'], ':c' => $datos['correo'], ':p' => $hash]);
                flash('exito', 'Cuenta creada correctamente. Ya puedes iniciar sesión.');
                redirigir('login.php');
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {      // UNIQUE: otra petición registró el mismo correo
                    $errores['correo'] = 'Este correo ya está registrado. Intenta iniciar sesión.';
                } else {
                    error_log('Registro: ' . $e->getMessage());
                    $errores['general'] = 'No se pudo completar el registro. Inténtalo más tarde.';
                }
            }
        }
    }
}

$titulo = 'Crear cuenta';
require __DIR__ . '/includes/header.php';
?>
<section class="tarjeta tarjeta-angosta">
    <h1>Crear cuenta</h1>
    <?php if (isset($errores['general'])): ?>
        <div class="alerta alerta-error" role="alert"><?= e($errores['general']) ?></div>
    <?php endif; ?>
    <form method="post" action="registro.php" novalidate>
        <?= csrf_campo() ?>

        <label for="nombre">Nombre completo</label>
        <input type="text" id="nombre" name="nombre" maxlength="100" required value="<?= e($datos['nombre']) ?>">
        <?php if (isset($errores['nombre'])): ?><p class="error"><?= e($errores['nombre']) ?></p><?php endif; ?>

        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo" maxlength="150" required value="<?= e($datos['correo']) ?>">
        <?php if (isset($errores['correo'])): ?><p class="error"><?= e($errores['correo']) ?></p><?php endif; ?>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" maxlength="72" required autocomplete="new-password">
        <p class="ayuda">Mínimo 8 caracteres, con letras y números.</p>
        <?php if (isset($errores['password'])): ?><p class="error"><?= e($errores['password']) ?></p><?php endif; ?>

        <label for="confirmar">Confirmar contraseña</label>
        <input type="password" id="confirmar" name="confirmar" maxlength="72" required autocomplete="new-password">
        <?php if (isset($errores['confirmar'])): ?><p class="error"><?= e($errores['confirmar']) ?></p><?php endif; ?>

        <button class="btn" type="submit">Registrarme</button>
    </form>
    <p class="centro">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
