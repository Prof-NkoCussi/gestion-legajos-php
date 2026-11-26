<?php
// exportar.php: descarga el listado de docentes por materia como CSV, listo para abrir en Excel
require __DIR__ . '/../src/funciones.php';
require __DIR__ . '/../src/conexion.php';

exigir_login();
$pdo = conectar();

// El mismo filtro que el listado, con la misma lista blanca
$materias = $pdo->query('SELECT DISTINCT cargo_materia FROM inscripciones ORDER BY cargo_materia')
                ->fetchAll(PDO::FETCH_COLUMN);
$filtro = leer_filtro($_GET, $materias);
$filas = buscar_docentes_por_materia($pdo, $filtro);

// Cabeceras: es un CSV en UTF-8 y se descarga como archivo (no se abre en la pestaña)
$archivo = 'docentes-por-materia-' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $archivo . '"');

// php://output: escribir directo en la respuesta, como hace echo
$salida = fopen('php://output', 'w');
// BOM: tres bytes al principio que le avisan a Excel que el texto está en UTF-8 (si no, "GÃ³mez")
fwrite($salida, "\xEF\xBB\xBF");

// Excel en español separa las columnas con punto y coma: la coma es la de los decimales
// fputcsv(archivo, fila, separador, comillas, escape): arma una línea y pone comillas donde haga falta
fputcsv($salida, ['Apellido', 'Nombre', 'DNI', 'Email', 'Materia'], ';', '"', '');
foreach ($filas as $f) {
    fputcsv($salida, [
        celda_csv($f['apellido']),
        celda_csv($f['nombre']),
        $f['dni'],
        celda_csv($f['email'] ?? ''),
        celda_csv($f['cargo_materia']),
    ], ';', '"', '');
}
fclose($salida);
