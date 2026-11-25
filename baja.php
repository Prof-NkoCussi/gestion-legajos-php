<?php
// baja.php: baja de un docente en dos pasos:
//   GET  baja.php?id=7 → muestra quién es y pide confirmación (no borra nada)
//   POST baja.php?id=7 → recién ahí hace el DELETE
require __DIR__ . '/funciones.php';
require __DIR__ . '/conexion.php';

// La baja es solo para el admin: sin login, al login; con otro rol, 403
exigir_admin();

$id = leer_id($_GET);
if ($id === null) {
    pagina_error(404, 'Falta el docente a dar de baja.');
}

$pdo = conectar();

// Se busca al docente, para mostrar su nombre en la confirmación
$stmt = $pdo->prepare('SELECT dni, apellido, nombre FROM docentes WHERE id = :id');
$stmt->execute(['id' => $id]);
$docente = $stmt->fetch();
if ($docente === false) {
    pagina_error(404, 'Ese docente no existe (puede que ya se haya dado de baja).');
}
$error = null;

// Solo con POST se borra: un enlace (GET) nunca debe cambiar datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare('DELETE FROM docentes WHERE id = :id');
        $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        // La clave foránea de inscripciones (RESTRICT) no deja borrar a un docente inscripto.
        // MySQL avisa con 1451 (con el detalle) o, según la versión y los permisos, con 1217
        if (in_array($e->errorInfo[1] ?? 0, [1451, 1217], true)) {
            $error = 'No se puede dar de baja: tiene inscripciones cargadas.';
        } else {
            throw $e;
        }
    }
    if ($error === null) {
        redirigir('index.php?ok=baja');
    }
}

$titulo = 'Dar de baja';
require __DIR__ . '/includes/header.php';
?>
<h1>Dar de baja</h1>

<?php if ($error !== null): ?>
    <p class="aviso aviso--error" role="alert"><?= e($error) ?></p>
    <p><a href="index.php">Volver al listado</a></p>
<?php else: ?>
    <!-- La confirmación es un formulario POST: un clic en un enlace no alcanza para borrar -->
    <form class="tarjeta confirmar" action="baja.php?id=<?= $id ?>" method="post">
        <p>¿Seguro que querés dar de baja a
           <strong><?= e(mb_strtoupper($docente['apellido'])) ?>, <?= e($docente['nombre']) ?></strong>
           (DNI <?= e($docente['dni']) ?>)?</p>
        <p class="bajada">Se borran también sus títulos cargados. No se puede deshacer.</p>
        <div class="botones">
            <button class="boton boton--peligro" type="submit">Sí, dar de baja</button>
            <a href="index.php">Cancelar</a>
        </div>
    </form>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
