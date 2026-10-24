<?php

require_once __DIR__ . "/middleware/auth.php";

verificarAcceso([2]);

include "conexion.php";

require_once "controllers/CursoController.php";


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


// ==========================================
// OBTENER DATOS DEL MAESTRO
// ==========================================

$maestroId =
    (int)$_SESSION["usuario_id"];

$nombreMaestro =
    $_SESSION["usuario_nombre"] ?? "Maestro";


// ==========================================
// OBTENER SUS CURSOS
// ==========================================

$cursoController =
    new CursoController($conexion);

try {

    $cursos =
        $cursoController
            ->obtenerPorMaestro($maestroId);

} catch (Throwable $e) {
    error_log("Error al obtener cursos del maestro: " . $e->getMessage());
    $cursos = [];
    $errorCursos = "No fue posible cargar tus cursos. Intenta nuevamente.";
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
        Panel del maestro
    </title>

    <link
        rel="stylesheet"
        href="css/maestro.css"
    >

</head>

<body>

    <!-- ==================================
         BARRA SUPERIOR
         ================================== -->

    <header class="barra-navegacion">

        <div class="logo">

            <a href="maestro.php">
                CPI
            </a>

        </div>

        <nav>

            <a href="cursos_inicio.php">
                Inicio
            </a>

            <a href="perfil.php">
                Mi perfil
            </a>

            <a href="cerrar_sesion.php">
                Cerrar sesión
            </a>

        </nav>

    </header>


    <!-- ==================================
         CONTENIDO
         ================================== -->

    <main class="contenedor">

        <section class="encabezado">

            <h1>
                Panel del maestro
            </h1>

            <p>
                Bienvenido,
                <?php
                echo htmlspecialchars(
                    $nombreMaestro
                );
                ?>.
            </p>

            <p>
                Aquí puedes administrar tus cursos
                y su contenido.
            </p>

        </section>


        <!-- ==================================
             CURSOS
             ================================== -->

        <section class="seccion-cursos">

            <h2>
                Mis cursos
            </h2>


            <?php if (count($cursos) > 0): ?>

                <div class="contenedor-cursos">

                    <?php foreach ($cursos as $curso): ?>

                        <article class="tarjeta-curso">


                            <!-- IMAGEN -->

                            <div class="imagen-curso">

                                <?php if (
                                    !empty(
                                        $curso["imagen"]
                                    )
                                ): ?>

                                    <img
                                        src="<?php
                                        echo htmlspecialchars(
                                            $curso["imagen"]
                                        );
                                        ?>"
                                        alt="<?php
                                        echo htmlspecialchars(
                                            $curso["titulo"]
                                        );
                                        ?>"
                                    >

                                <?php else: ?>

                                    <img
                                        src="images/imagen.jpg"
                                        alt="Curso"
                                    >

                                <?php endif; ?>

                            </div>


                            <!-- INFORMACIÓN -->

                            <div class="informacion-curso">

                                <h3>

                                    <?php
                                    echo htmlspecialchars(
                                        $curso["titulo"]
                                    );
                                    ?>

                                </h3>


                                <p class="categoria">

                                    Categoría:
                                    <?php
                                    echo htmlspecialchars(
                                        $curso["categoria"]
                                    );
                                    ?>

                                </p>


                                <p>

                                    <?php
                                    echo htmlspecialchars(
                                        $curso["descripcion"]
                                    );
                                    ?>

                                </p>


                                <p>

                                    Duración:
                                    <?php
                                    echo htmlspecialchars(
                                        $curso["duracion"]
                                    );
                                    ?>

                                </p>


                                <p class="precio">

                                    $
                                    <?php
                                    echo number_format(
                                        $curso["precio"],
                                        2
                                    );
                                    ?>
                                    MXN

                                </p>


                                <!-- BOTONES -->

                                <div class="botones">

                                    <a
                                        href="curso_ver.php?id=<?php
                                        echo $curso["id"];
                                        ?>"
                                        class="boton"
                                    >
                                        Ver curso
                                    </a>


                                    <a
                                        href="multimedia.php?id=<?php
                                        echo $curso["id"];
                                        ?>"
                                        class="boton administrar"
                                    >
                                        Administrar contenido
                                    </a>

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>


            <?php else: ?>

                <div class="sin-cursos">

                    <h3>
                        Todavía no tienes cursos
                    </h3>

                    <p>
                        Actualmente no tienes cursos
                        registrados como maestro.
                    </p>

                </div>

            <?php endif; ?>

        </section>

    </main>


    <footer>

        <p>
            © 2026 CPI Cursos
        </p>

    </footer>

</body>

</html>