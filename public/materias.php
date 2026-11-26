<?php
// materias.php: qué docentes dan cada materia, con filtro y orden
require __DIR__ . '/../src/funciones.php';
require __DIR__ . '/../src/conexion.php';

exigir_login();
$pdo = conectar();

// Las materias salen de la base: son la lista blanca del filtro
// FETCH_COLUMN: un array simple con la primera columna, ['Base de Datos', 'Programación I', …]
$materias = $pdo->query('SELECT DISTINCT cargo_materia FROM inscripciones ORDER BY cargo_materia')
                ->fetchAll(PDO::FETCH_COLUMN);

// El filtro llega por GET (materias.php?materia=Redes+I&orden=apellido)
$filtro = leer_filtro($_GET, $materias);
$filas = buscar_docentes_por_materia($pdo, $filtro);

// El enlace de exportar lleva el mismo filtro; http_build_query arma y codifica la parte del ?
$enlace_csv = 'exportar.php?' . http_build_query($filtro);

$titulo = 'Docentes por materia';
require __DIR__ . '/../src/plantillas/header.php';
?>
<div class="titular">
    <h1>Docentes por materia</h1>
    <a class="boton" href="<?= e($enlace_csv) ?>">Exportar a CSV</a>
</div>

<!-- method="get": el filtro queda en la URL, se puede recargar y compartir -->
<form class="tarjeta filtros" action="materias.php" method="get">
    <div class="campo">
        <label for="materia">Materia</label>
        <select id="materia" name="materia">
            <option value="">Todas</option>
            <!-- selected: la opción elegida queda marcada después de filtrar -->
            <?php foreach ($materias as $m): ?>
                <option value="<?= e($m) ?>"<?= $m === $filtro['materia'] ? ' selected' : '' ?>><?= e($m) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="campo">
        <label for="orden">Ordenar por</label>
        <select id="orden" name="orden">
            <option value="apellido"<?= $filtro['orden'] === 'apellido' ? ' selected' : '' ?>>Apellido</option>
            <option value="materia"<?= $filtro['orden'] === 'materia' ? ' selected' : '' ?>>Materia</option>
        </select>
    </div>
    <button class="boton" type="submit">Filtrar</button>
</form>

<?php if (count($filas) === 0): ?>
    <p class="tarjeta">No hay docentes para ese filtro.</p>
<?php else: ?>
    <p class="bajada">
        <?= $filtro['materia'] !== ''
            ? count($filas) . ' docentes en ' . e($filtro['materia']) . '.'
            : count($filas) . ' filas: cada docente aparece una vez por materia.' ?>
    </p>
    <div class="tabla-scroll">
        <table class="tabla">
            <thead>
                <tr>
                    <th scope="col">Apellido y nombre</th>
                    <th scope="col">DNI</th>
                    <th scope="col">Email</th>
                    <th scope="col">Materia</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($filas as $f): ?>
                <tr>
                    <td><strong><?= e(mb_strtoupper($f['apellido'])) ?></strong>, <?= e($f['nombre']) ?></td>
                    <td><?= e($f['dni']) ?></td>
                    <td><?= e($f['email'] ?? '—') ?></td>
                    <td><?= e($f['cargo_materia']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../src/plantillas/footer.php'; ?>
