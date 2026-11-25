<?php
// includes/header.php: cabecera común: <head>, marca del sistema y menú
// Cada página lo trae con require, después de definir $titulo
?>
<!DOCTYPE html>
<html lang="es-AR">
<head>
    <meta charset="utf-8">
    <!-- en el celular, que use el ancho real de la pantalla -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- $titulo lo definió la página que hizo el require -->
    <title><?= e($titulo) ?> · Gestión de Legajos</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header class="cabecera">
    <div class="cabecera__marca">
        <!-- aria-hidden: la sigla es decoración, el nombre ya está al lado -->
        <span class="cabecera__sigla" aria-hidden="true">GL</span>
        <div>
            <strong>Gestión de Legajos</strong>
            <span>Alta, modificación y baja</span>
        </div>
    </div>
    <!-- es_actual() agrega aria-current="page" al enlace de la página abierta -->
    <nav class="menu" aria-label="Menú principal">
        <a href="index.php"<?= es_actual('index.php') ?>>Docentes</a>
        <a href="alta.php"<?= es_actual('alta.php') ?>>Nuevo docente</a>
    </nav>
</header>
<main class="contenido">
