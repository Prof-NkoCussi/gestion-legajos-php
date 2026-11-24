<?php
// includes/form_docente.php — formulario de docente, para el alta y para la modificación
// Antes del require, la página define:
//   $accion  a dónde se envía (alta.php o editar.php?id=3)
//   $docente los valores de cada campo
//   $errores los mensajes de error, con el nombre del campo como clave
//   $boton   el texto del botón

// Los campos del formulario: name => etiqueta, tipo de <input> y largo máximo (el de la columna)
$campos = [
    'dni'      => ['etiqueta' => 'DNI (sin puntos)',    'tipo' => 'text',  'max' => 10],
    'apellido' => ['etiqueta' => 'Apellido',            'tipo' => 'text',  'max' => 60],
    'nombre'   => ['etiqueta' => 'Nombre',              'tipo' => 'text',  'max' => 60],
    'email'    => ['etiqueta' => 'Email (opcional)',    'tipo' => 'email', 'max' => 120],
    'telefono' => ['etiqueta' => 'Teléfono (opcional)', 'tipo' => 'tel',   'max' => 20],
];
?>
<!-- novalidate: que valide PHP, así se ven siempre los mensajes del servidor -->
<form class="tarjeta formulario" action="<?= e($accion) ?>" method="post" novalidate>
<?php foreach ($campos as $campo => $c): ?>
    <div class="campo">
        <label for="<?= $campo ?>"><?= e($c['etiqueta']) ?></label>
        <!-- value: lo que ya estaba (al editar) o lo que se escribió (si hubo un error) -->
        <input id="<?= $campo ?>" name="<?= $campo ?>" type="<?= $c['tipo'] ?>"
               maxlength="<?= $c['max'] ?>" value="<?= e($docente[$campo]) ?>">
        <?php if (isset($errores[$campo])): ?>
            <p class="error"><?= e($errores[$campo]) ?></p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
    <div class="botones">
        <button class="boton" type="submit"><?= e($boton) ?></button>
        <a href="index.php">Cancelar</a>
    </div>
</form>
