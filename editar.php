<?php
// editar.php: modificación de un docente: el id llega por GET (editar.php?id=3)
require __DIR__ . '/funciones.php';
require __DIR__ . '/conexion.php';

exigir_login();

// 1) ¿Qué docente? leer_id() devuelve un entero o null
$id = leer_id($_GET);
if ($id === null) {
    pagina_error(404, 'Falta el docente a modificar.');
}

$pdo = conectar();

// 2) Se busca en la base. El id viene de la URL: también va preparado
$stmt = $pdo->prepare('SELECT dni, apellido, nombre, email, telefono FROM docentes WHERE id = :id');
$stmt->execute(['id' => $id]);
$docente = $stmt->fetch();   // fetch() trae UNA fila, o false si no hay ninguna
if ($docente === false) {
    pagina_error(404, 'Ese docente no existe.');
}
$errores = [];

// 3) Si llegó el formulario, se valida y se guarda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $docente = leer_docente($_POST);
    $errores = validar_docente($docente);

    if (!$errores) {
        // El WHERE es lo más importante: sin él, se cambiarían TODOS los docentes
        $sql = 'UPDATE docentes
                SET dni = :dni, apellido = :apellido, nombre = :nombre,
                    email = :email, telefono = :telefono
                WHERE id = :id';
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'dni'      => $docente['dni'],
                'apellido' => $docente['apellido'],
                'nombre'   => $docente['nombre'],
                'email'    => nulo_si_vacio($docente['email']),
                'telefono' => nulo_si_vacio($docente['telefono']),
                'id'       => $id,
            ]);
        } catch (PDOException $e) {
            // 1062: el DNI nuevo ya lo tiene OTRO docente
            if (($e->errorInfo[1] ?? 0) === 1062) {
                $errores['dni'] = 'Ese DNI ya lo tiene otro docente.';
            } else {
                throw $e;
            }
        }
        if (!$errores) {
            redirigir('index.php?ok=cambio');
        }
    }
}

// El formulario se envía a esta misma página, con el mismo id en la URL
$accion = 'editar.php?id=' . $id;
$boton = 'Guardar cambios';

$titulo = 'Modificar docente';
require __DIR__ . '/includes/header.php';
?>
<h1>Modificar docente</h1>
<?php require __DIR__ . '/includes/form_docente.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
