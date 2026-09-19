<?php
include 'conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>

    <link rel="stylesheet" href="index.css">
</head>

<body>

    <div class="ventana">

        <!-- Parte izquierda -->
        <div class="izquierda">

            <h1>inicia sesión ahora mismo</h1>

            <label>correo</label>
            <input type="email">

            <label>contraseña</label>
            <input type="password">

            <button>Iniciar sesión</button>

            <a href="registro/registro.html" class="boton-cuenta">Crear una cuenta</a>

        </div>

        <!-- Parte derecha -->
        <div class="derecha">

            <img src="images/imagen.jpg" alt="Imagen">

        </div>

    </div>

</body>
</html>
