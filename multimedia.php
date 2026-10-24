<?php

session_start();

include "conexion.php";

require_once "controllers/CursoController.php";
require_once "controllers/MultimediaController.php";


// ==========================================
// COMPROBAR SESIÓN
// ==========================================

if (!isset($_SESSION["usuario_id"])) {

    header("Location: index.php");
    exit;
}


// ==========================================
// COMPROBAR QUE SEA MAESTRO
// ==========================================

if (
    !isset($_SESSION["usuario_rol"]) ||
    (int)$_SESSION["usuario_rol"] !== 2
) {

    header("Location: cursos_inicio.php");
    exit;
}


$maestroId =
    (int)$_SESSION["usuario_id"];


// ==========================================
// OBTENER ID DEL CURSO
// ==========================================

$cursoId =
    $_GET["id"] ?? "";

if (
    !filter_var(
        $cursoId,
        FILTER_VALIDATE_INT
    ) ||
    (int)$cursoId <= 0
) {

    die("ID de curso no válido.");

}

$cursoId = (int)$cursoId;


// ==========================================
// CONTROLADORES
// ==========================================

$cursoController =
    new CursoController($conexion);

$multimediaController =
    new MultimediaController($conexion);


// ==========================================
// COMPROBAR QUE EL CURSO SEA DEL MAESTRO
// ==========================================

try {
    $curso = $multimediaController->obtenerCursoDeMaestro(
        $cursoId,
        $maestroId
    );

    if ($curso === null) {
        header("Location: maestro.php");
        exit;
    }
} catch (Throwable $e) {
    error_log("Error al verificar el curso: " . $e->getMessage());

    http_response_code(500);
    exit("Ocurrió un error al verificar el curso.");
}


// ==========================================
// VARIABLES
// ==========================================

$mensaje = "";
$tipoMensaje = "";


// ==========================================
// PROCESAR FORMULARIO
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $titulo =
        trim(
            $_POST["titulo"] ?? ""
        );

    $descripcion =
        trim(
            $_POST["descripcion"] ?? ""
        );

    $tipo =
        $_POST["tipo"] ?? "";

    $orden =
        $_POST["orden"] ?? 1;

    $gratuito = 0;

$gratuito = isset($_POST["gratuito"]) ? 1 : 0;


    // ======================================
    // VALIDAR DATOS
    // ======================================

    if (
        $titulo === "" ||
        $tipo === ""
    ) {

        $mensaje =
            "El título y el tipo son obligatorios.";

        $tipoMensaje =
            "error";

    } elseif (
        !in_array(
            $tipo,
            ["video", "imagen", "pdf"],
            true
        )
    ) {

        $mensaje =
            "El tipo de multimedia no es válido.";

        $tipoMensaje =
            "error";

    } elseif (
        !filter_var(
            $orden,
            FILTER_VALIDATE_INT
        ) ||
        (int)$orden <= 0
    ) {

        $mensaje =
            "El orden debe ser un número entero mayor que cero.";

        $tipoMensaje =
            "error";

    } elseif (
        !isset(
            $_FILES["archivo"]
        ) ||
        $_FILES["archivo"]["error"] !== UPLOAD_ERR_OK
    ) {

        $mensaje =
            "Debes seleccionar un archivo.";

        $tipoMensaje =
            "error";

    } else {

        $archivoSubido =
            $_FILES["archivo"];


        // ==================================
        // EXTENSIONES PERMITIDAS
        // ==================================

        $extensionesPermitidas = [
            "video" => [
                "mp4",
                "webm",
                "ogg"
            ],

            "imagen" => [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ],

            "pdf" => [
                "pdf"
            ]
        ];


        $nombreOriginal =
            $archivoSubido["name"];

        $extension =
            strtolower(
                pathinfo(
                    $nombreOriginal,
                    PATHINFO_EXTENSION
                )
            );


        if (
            !in_array(
                $extension,
                $extensionesPermitidas[$tipo],
                true
            )
        ) {

            $mensaje =
                "El formato del archivo no corresponde al tipo seleccionado.";

            $tipoMensaje =
                "error";

        } else {

            // ==============================
            // GENERAR NOMBRE ÚNICO
            // ==============================

            $nombreArchivo =
                uniqid(
                    "multimedia_",
                    true
                )
                . "."
                . $extension;


            $carpeta =
                "uploads/multimedia/";

            $ruta =
                $carpeta .
                $nombreArchivo;


            // ==============================
            // MOVER ARCHIVO
            // ==============================

            if (
                move_uploaded_file(
                    $archivoSubido["tmp_name"],
                    $ruta
                )
            ) {

                try {

                    $multimediaController
                        ->agregar(
                            $cursoId,
                            $tipo,
                            $ruta,
                            $titulo,
                            $descripcion,
                            (int)$orden,
                            $gratuito
                        );


                    $mensaje =
                        "El contenido se agregó correctamente.";

                    $tipoMensaje =
                        "exito";

                } catch (Throwable $e) {

                    // Si falló MySQL,
                    // eliminamos el archivo
                    // que acabamos de subir.

                    if (
                        file_exists($ruta)
                    ) {

                        unlink($ruta);

                    }

                    $mensaje =
                        "Ocurrió un error al guardar el contenido.";

                    $tipoMensaje =
                        "error";
                }

            } else {

                $mensaje =
                    "No se pudo guardar el archivo.";

                $tipoMensaje =
                    "error";
            }
        }
    }
}


// ==========================================
// OBTENER MULTIMEDIA
// ==========================================

try {

    $multimedia =
        $multimediaController
            ->obtenerPorCurso($cursoId);

} catch (Throwable $e) {

    $multimedia = [];

    $mensaje =
        "No se pudo obtener el contenido.";

    $tipoMensaje =
        "error";
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

    <title>
        Administrar contenido
    </title>

    <link
        rel="stylesheet"
        href="css/multimedia.css"
    >

</head>

<body>

    <!-- ==================================
         BARRA
         ================================== -->

    <header class="barra-navegacion">

        <div class="logo">

            <a href="maestro.php">
                CPI
            </a>

        </div>

        <nav>

            <a href="maestro.php">
                Mis cursos
            </a>

            <a href="cursos_inicio.php">
                Inicio
            </a>

            <a href="cerrar_sesion.php">
                Cerrar sesión
            </a>

        </nav>

    </header>


    <main class="contenedor">

        <!-- ==================================
             ENCABEZADO
             ================================== -->

        <section class="encabezado">

            <a
                href="maestro.php"
                class="regresar"
            >
                ← Regresar a mis cursos
            </a>

            <h1>

                <?php
                echo htmlspecialchars(
                    $curso["Curso_nombre"]
                );
                ?>

            </h1>

            <p>
                Administración de contenido
            </p>

        </section>


        <!-- ==================================
             MENSAJE
             ================================== -->

        <?php if ($mensaje !== ""): ?>

            <div
                class="mensaje
                <?php
                echo $tipoMensaje;
                ?>"
            >

                <?php
                echo htmlspecialchars(
                    $mensaje
                );
                ?>

            </div>

        <?php endif; ?>


        <!-- ==================================
             FORMULARIO
             ================================== -->

        <section class="formulario-contenedor">

            <h2>
                Agregar multimedia
            </h2>

            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <label for="titulo">
                    Título
                </label>

                <input
                    type="text"
                    id="titulo"
                    name="titulo"
                    required
                >


                <label for="tipo">
                    Tipo de contenido
                </label>

                <select
                    id="tipo"
                    name="tipo"
                    required
                >

                    <option value="">
                        Selecciona un tipo
                    </option>

                    <option value="video">
                        Video
                    </option>

                    <option value="imagen">
                        Imagen
                    </option>

                    <option value="pdf">
                        PDF
                    </option>

                </select>


                <label for="archivo">
                    Archivo
                </label>

                <input
                    type="file"
                    id="archivo"
                    name="archivo"
                    required
                >


                <label for="descripcion">
                    Descripción
                </label>

                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="4"
                ></textarea>


                <label for="orden">
                    Orden de la lección
                </label>

                <input
                    type="number"
                    id="orden"
                    name="orden"
                    min="1"
                    value="1"
                    required
                >


                <label class="opcion">

                    <input
                        type="checkbox"
                        name="gratuito"
                        value="1"
                    >

                    Contenido gratuito

                </label>


                <button type="submit">
                    Agregar contenido
                </button>

            </form>

        </section>


        <!-- ==================================
             CONTENIDO EXISTENTE
             ================================== -->

        <section class="contenido-existente">

            <h2>
                Contenido del curso
            </h2>


            <?php if (
                count($multimedia) > 0
            ): ?>

                <div class="lista-multimedia">

                    <?php foreach (
                        $multimedia
                        as $elemento
                    ): ?>

                        <article
                            class="elemento-multimedia"
                        >

                            <div class="informacion">

                                <h3>

                                    <?php
                                    echo htmlspecialchars(
                                        $elemento["titulo"]
                                    );
                                    ?>

                                </h3>

                                <p>

                                    Tipo:
                                    <?php
                                    echo htmlspecialchars(
                                        $elemento["tipo"]
                                    );
                                    ?>

                                </p>

                                <p>

                                    Orden:
                                    <?php
                                    echo $elemento["orden"];
                                    ?>

                                </p>

                                <?php if (
                                    $elemento["gratuito"]
                                ): ?>

                                    <span class="gratis">
                                        Contenido gratuito
                                    </span>

                                <?php else: ?>

                                    <span class="privado">
                                        Contenido privado
                                    </span>

                                <?php endif; ?>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="sin-contenido">

                    <p>
                        Este curso todavía no tiene
                        contenido multimedia.
                    </p>

                </div>

            <?php endif; ?>

        </section>

    </main>

</body>

</html>