<?php

session_start();

include "conexion.php";

$mensaje = "";


// ==========================================
// PROCESAR INICIO DE SESIÓN
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = trim($_POST["correo"] ?? "");
    $contrasena = $_POST["contrasena"] ?? "";


    // ==========================================
    // VALIDAR CAMPOS
    // ==========================================

    if ($correo === "" || $contrasena === "") {

        $mensaje = "Debes ingresar correo y contraseña.";

    }

    else {

        // ==========================================
        // BUSCAR USUARIO
        // ==========================================

        $sql = "
    SELECT
        Usuario_id,
        Usuario_nombre,
        Usuario_email,
        Usuario_contrasena,
        Usuario_id_rol,
        Usuario_intentos_fallidos,
        Usuario_bloqueado
    FROM Usuarios
    WHERE Usuario_email = ?
";

        $stmt = $conexion->prepare($sql);

        if (!$stmt) {

            $mensaje =
                "Ocurrió un error al iniciar sesión.";

        }

        else {

            $stmt->bind_param(
                "s",
                $correo
            );

            $stmt->execute();

            $resultado =
                $stmt->get_result();


            // ==========================================
            // COMPROBAR USUARIO
            // ==========================================

            if ($resultado->num_rows === 0) {

                $mensaje =
                    "El correo o la contraseña son incorrectos.";

            }

            else {

                $usuario =
                    $resultado->fetch_assoc();

// ==========================================
// COMPROBAR SI LA CUENTA ESTA BLOQUEADA
// ==========================================

if ((int)$usuario["Usuario_bloqueado"] === 1) {

    $mensaje = "Tu cuenta está bloqueada por varios intentos fallidos. Contacta al administrador.";

} else {

    // ==========================================
    // COMPROBAR CONTRASEÑA
    // ==========================================

    $contrasenaGuardada = $usuario["Usuario_contrasena"];
    $infoHash = password_get_info($contrasenaGuardada);

    $esHash = (
        $infoHash["algo"] !== null &&
        $infoHash["algo"] !== 0
    );

    if ($esHash) {
        $contrasenaCorrecta = password_verify(
            $contrasena,
            $contrasenaGuardada
        );
    } else {
        // Compatibilidad temporal con cuentas antiguas
        $contrasenaCorrecta = hash_equals(
            $contrasenaGuardada,
            $contrasena
        );
    }

    // ==========================================
    // CONTRASEÑA INCORRECTA
    // ==========================================

    if (!$contrasenaCorrecta) {

        $intentos = (int)$usuario["Usuario_intentos_fallidos"] + 1;

        if ($intentos >= 3) {

            $sqlBloquear = "
                UPDATE Usuarios
                SET Usuario_intentos_fallidos = ?,
                    Usuario_bloqueado = 1
                WHERE Usuario_id = ?
            ";

            $mensaje = "Tu cuenta ha sido bloqueada por tres intentos fallidos. Contacta al administrador.";

        } else {

            $sqlBloquear = "
                UPDATE Usuarios
                SET Usuario_intentos_fallidos = ?
                WHERE Usuario_id = ?
            ";

            $mensaje = "El correo o la contraseña son incorrectos. Intento "
                . $intentos . " de 3.";
        }

        $stmtBloquear = $conexion->prepare($sqlBloquear);

        if ($stmtBloquear) {
            $stmtBloquear->bind_param(
                "ii",
                $intentos,
                $usuario["Usuario_id"]
            );

            $stmtBloquear->execute();
            $stmtBloquear->close();
        }

    } else {

        // ==========================================
        // ACTUALIZAR CONTRASEÑA ANTIGUA A HASH
        // ==========================================

        if (!$esHash) {

            $nuevoHash = password_hash(
                $contrasena,
                PASSWORD_DEFAULT
            );

            $sqlHash = "
                UPDATE Usuarios
                SET Usuario_contrasena = ?
                WHERE Usuario_id = ?
            ";

            $stmtHash = $conexion->prepare($sqlHash);

            if ($stmtHash) {
                $stmtHash->bind_param(
                    "si",
                    $nuevoHash,
                    $usuario["Usuario_id"]
                );

                $stmtHash->execute();
                $stmtHash->close();
            }
        }

        // ==========================================
        // REINICIAR INTENTOS FALLIDOS
        // ==========================================

        $sqlReiniciar = "
            UPDATE Usuarios
            SET Usuario_intentos_fallidos = 0
            WHERE Usuario_id = ?
        ";

        $stmtReiniciar = $conexion->prepare($sqlReiniciar);

        if ($stmtReiniciar) {
            $stmtReiniciar->bind_param(
                "i",
                $usuario["Usuario_id"]
            );

            $stmtReiniciar->execute();
            $stmtReiniciar->close();
        }

        // ==========================================
        // CREAR SESION
        // ==========================================

        session_regenerate_id(true);

        $_SESSION["usuario_id"] = $usuario["Usuario_id"];
        $_SESSION["usuario_nombre"] = $usuario["Usuario_nombre"];
        $_SESSION["usuario_rol"] = $usuario["Usuario_id_rol"];

        $stmt->close();

        header("Location: cursos_inicio.php");
        exit;
    }
}


            }

            $stmt->close();

        }

    }

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Iniciar sesión</title>

    <link
        rel="stylesheet"
        href="css/index.css"
    >

</head>

<body>

    <div class="ventana">

        <!-- Parte izquierda -->

        <div class="izquierda">

            <h1>
                inicia sesión ahora mismo
            </h1>


            <?php if ($mensaje !== ""): ?>

                <p class="error">

                    <?php
                    echo htmlspecialchars($mensaje);
                    ?>

                </p>

            <?php endif; ?>


            <form
                method="POST"
                action="index.php"
            >

                <label for="correo">
                    correo
                </label>

                <input
                    type="email"
                    id="correo"
                    name="correo"
                    required
                >


                <label for="contrasena">
                    contraseña
                </label>

                <input
                    type="password"
                    id="contrasena"
                    name="contrasena"
                    required
                >


                <button type="submit">
                    Iniciar sesión
                </button>


                <a
                    href="registro.php"
                    class="boton-cuenta"
                >
                    Crear una cuenta
                </a>

            </form>

        </div>


        <!-- Parte derecha -->

        <div class="derecha">

            <img
                src="images/imagen.jpg"
                alt="Imagen"
            >

        </div>

    </div>

</body>

</html>