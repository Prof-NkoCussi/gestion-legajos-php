<?php
// alta.php — alta de un docente: formulario + INSERT
require __DIR__ . '/funciones.php';
require __DIR__ . '/conexion.php';

// La primera vez el formulario arranca vacío y sin errores
$docente = leer_docente([]);
$errores = [];

// ¿Llegó el formulario? POST = enviar datos que cambian algo en el servidor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $docente = leer_docente($_POST);       // toma y limpia lo que vino
    $errores = validar_docente($docente);  // valida en el servidor

    // Un array vacío cuenta como false: !$errores = "no hay errores"
    if (!$errores) {
        // Consulta preparada: el SQL lleva marcadores (:dni…), nunca los datos pegados
        $sql = 'INSERT INTO docentes (dni, apellido, nombre, email, telefono)
                VALUES (:dni, :apellido, :nombre, :email, :telefono)';
        try {
            $stmt = conectar()->prepare($sql);   // MySQL recibe y revisa el SQL
            $stmt->execute([                     // los datos viajan aparte, en execute()
                'dni'      => $docente['dni'],
                'apellido' => $docente['apellido'],
                'nombre'   => $docente['nombre'],
                'email'    => nulo_si_vacio($docente['email']),
                'telefono' => nulo_si_vacio($docente['telefono']),
            ]);
        } catch (PDOException $e) {
            // errorInfo[1] es el código de MySQL: 1062 = valor repetido en un UNIQUE
            if (($e->errorInfo[1] ?? 0) === 1062) {
                $errores['dni'] = 'Ya hay un docente cargado con ese DNI.';
            } else {
                throw $e;   // cualquier otro error no se esconde: va al log de Apache
            }
        }
        // Si se guardó, se redirige: un F5 en el listado no vuelve a enviar el formulario
        if (!$errores) {
            redirigir('index.php?ok=alta');
        }
    }
}

// Lo que necesita includes/form_docente.php
$accion = 'alta.php';
$boton = 'Guardar docente';

$titulo = 'Nuevo docente';
require __DIR__ . '/includes/header.php';
?>
<h1>Nuevo docente</h1>
<?php require __DIR__ . '/includes/form_docente.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
