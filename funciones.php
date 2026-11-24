<?php
// funciones.php — funciones que usan todas las páginas

// PHP no toma la zona horaria de Linux: se la decimos acá, una sola vez
date_default_timezone_set('America/Argentina/Ushuaia');

// e(): escapa un texto antes de mostrarlo en el HTML (protege contra XSS)
// ?string = acepta texto o null (un email vacío llega de la base como NULL)
function e(?string $texto): string
{
    // ?? '' : si es null, usa texto vacío
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

// limpiar(): saca los espacios de los costados; si no es texto, devuelve ''
function limpiar(mixed $valor): string
{
    return is_string($valor) ? trim($valor) : '';
}

// es_actual(): marca en el menú el enlace de la página abierta
// basename($_SERVER['SCRIPT_NAME']) es el nombre del archivo pedido: 'index.php'
function es_actual(string $archivo): string
{
    return basename($_SERVER['SCRIPT_NAME']) === $archivo ? ' aria-current="page"' : '';
}


// FORMULARIO: leer, validar y guardar //

// validar_dni(): 7 u 8 dígitos y nada más (^ inicio, \d dígito, $ fin)
function validar_dni(string $dni): bool
{
    return preg_match('/^\d{7,8}$/', $dni) === 1;
}

// nulo_si_vacio(): un campo opcional vacío se guarda como NULL ("sin dato")
function nulo_si_vacio(string $texto): ?string
{
    return $texto === '' ? null : $texto;
}

// leer_docente(): arma el array del docente con lo que vino del formulario
function leer_docente(array $origen): array
{
    return [
        // en el DNI se sacan puntos y espacios: 28.456.123 → 28456123
        'dni'      => str_replace(['.', ' '], '', limpiar($origen['dni'] ?? '')),
        'apellido' => limpiar($origen['apellido'] ?? ''),
        'nombre'   => limpiar($origen['nombre'] ?? ''),
        'email'    => limpiar($origen['email'] ?? ''),
        'telefono' => limpiar($origen['telefono'] ?? ''),
    ];
}

// validar_docente(): revisa los datos; devuelve los errores (array vacío = todo bien)
// La clave de cada error es el campo, para mostrarlo debajo de su casillero
function validar_docente(array $d): array
{
    $errores = [];
    if (!validar_dni($d['dni'])) {
        $errores['dni'] = 'El DNI tiene que tener 7 u 8 números.';
    }
    // mb_strlen cuenta letras (con tildes y ñ), no bytes; 60 es el largo de la columna
    if ($d['apellido'] === '' || mb_strlen($d['apellido']) > 60) {
        $errores['apellido'] = 'El apellido es obligatorio (hasta 60 letras).';
    }
    if ($d['nombre'] === '' || mb_strlen($d['nombre']) > 60) {
        $errores['nombre'] = 'El nombre es obligatorio (hasta 60 letras).';
    }
    // el email es opcional: solo se valida si se escribió algo
    if ($d['email'] !== '' && (filter_var($d['email'], FILTER_VALIDATE_EMAIL) === false || mb_strlen($d['email']) > 120)) {
        $errores['email'] = 'El email no es válido.';
    }
    if (mb_strlen($d['telefono']) > 20) {
        $errores['telefono'] = 'El teléfono es demasiado largo (hasta 20 caracteres).';
    }
    return $errores;
}

// redirigir(): le dice al navegador que vaya a otra página y corta el script
// never = esta función nunca vuelve: termina con exit
function redirigir(string $url): never
{
    // Cabecera HTTP "Location": el navegador pide esa URL con GET
    header('Location: ' . $url);
    // Sin exit, PHP seguiría ejecutando el resto de la página
    exit;
}


// MODIFICACIONES //

// leer_id(): toma un id de un array ($_GET) y lo devuelve como número entero
// Devuelve null si no vino, si no es un número o si es menor que 1
function leer_id(array $origen): ?int
{
    // FILTER_VALIDATE_INT: '7' da 7; '7abc', '' o un array dan false
    $id = filter_var($origen['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

// pagina_error(): muestra un aviso con el diseño del sitio y corta el script
function pagina_error(int $codigo, string $texto): never
{
    // Código de estado HTTP: 404 = no existe, 500 = error del servidor
    http_response_code($codigo);
    $titulo = 'Aviso';
    require __DIR__ . '/includes/header.php';
    echo '<p class="aviso aviso--error">' . e($texto) . '</p>';
    echo '<p><a href="index.php">Volver al listado</a></p>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
