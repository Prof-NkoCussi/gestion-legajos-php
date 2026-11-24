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
