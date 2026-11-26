<?php
// index.php: listado de docentes (leer: lo que se ve antes de dar altas, bajas o cambios)
require __DIR__ . '/../src/funciones.php';
require __DIR__ . '/../src/conexion.php';

// Sin login, al login: va antes de tocar la base
exigir_login();
$pdo = conectar();

// Esta consulta no lleva ningún dato del usuario: alcanza con query(), sin preparar
$sql = 'SELECT id, dni, apellido, nombre, email, telefono
        FROM docentes
        ORDER BY apellido, nombre';

// fetchAll() trae todas las filas juntas: un array de arrays, una fila por docente
$docentes = $pdo->query($sql)->fetchAll();

// Después de guardar, las otras páginas vuelven acá con ?ok=alta, ?ok=cambio u ?ok=baja
// Lista blanca: solo se muestra un mensaje de este array, nunca el texto de la URL
$mensajes = [
    'alta'   => 'Docente cargado.',
    'cambio' => 'Cambios guardados.',
    'baja'   => 'Docente dado de baja.',
];
// limpiar() descarta lo que no sea texto; si la clave no existe, $aviso queda en null
$aviso = $mensajes[limpiar($_GET['ok'] ?? '')] ?? null;

$titulo = 'Docentes';
require __DIR__ . '/../src/plantillas/header.php';
?>
<div class="titular">
    <h1>Docentes</h1>
    <a class="boton" href="alta.php">+ Nuevo docente</a>
</div>

<?php if ($aviso !== null): ?>
    <!-- role="status": los lectores de pantalla anuncian el mensaje -->
    <p class="aviso aviso--ok" role="status"><?= e($aviso) ?></p>
<?php endif; ?>

<?php if (count($docentes) === 0): ?>
    <p class="tarjeta">Todavía no hay docentes cargados.</p>
<?php else: ?>
    <p class="bajada"><?= count($docentes) ?> docentes, por apellido.</p>
    <div class="tabla-scroll">
        <table class="tabla">
            <thead>
                <tr>
                    <th scope="col">Apellido y nombre</th>
                    <th scope="col">DNI</th>
                    <th scope="col">Email</th>
                    <th scope="col">Teléfono</th>
                    <th scope="col"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($docentes as $d): ?>
                <!-- $d es una fila: ['id' => 1, 'dni' => '28456123', 'apellido' => 'Gómez', …] -->
                <tr>
                    <td><strong><?= e(mb_strtoupper($d['apellido'])) ?></strong>, <?= e($d['nombre']) ?></td>
                    <td><?= e($d['dni']) ?></td>
                    <!-- ?? '—': si el campo es NULL en la base, muestra una raya -->
                    <td><?= e($d['email'] ?? '—') ?></td>
                    <td><?= e($d['telefono'] ?? '—') ?></td>
                    <td class="acciones">
                        <!-- el id viaja por GET: editar.php?id=3 -->
                        <a href="editar.php?id=<?= $d['id'] ?>">Editar</a>
                        <!-- la baja solo le aparece al admin (y baja.php también lo controla) -->
                        <?php if (es_admin()): ?>
                            <a class="peligro" href="baja.php?id=<?= $d['id'] ?>">Baja</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../src/plantillas/footer.php'; ?>
