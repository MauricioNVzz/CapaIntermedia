<?php

require_once __DIR__ . "/middleware/auth.php";

verificarAcceso([1, 2, 3]);

include "conexion.php";


// ==========================================
// OBTENER CURSOS
// ==========================================

$sql = "
    SELECT
        c.Curso_id,
        c.Curso_nombre,
        c.Curso_desc,
        c.Curso_precio,
        c.Curso_imagen,
        u.Usuario_nombre
    FROM Cursos c
    INNER JOIN Usuarios u
        ON c.Curso_id_maestro = u.Usuario_id
    ORDER BY c.Curso_fecha DESC
";

$resultado = $conexion->query($sql);

if (!$resultado) {

    die("Error al obtener los cursos.");

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

    <title>Inicio - CPI Cursos</title>

    <link
        rel="stylesheet"
        href="css/cursos_inicio.css"
    >

</head>

<body>


    <!-- ==========================================
         BARRA DE NAVEGACIÓN
         ========================================== -->

    <header class="barra-navegacion">

        <div class="logo">

            <a href="cursos_inicio.php">
                CPI
            </a>

        </div>


        <nav>

            <a href="cursos_inicio.php">
                Inicio
            </a>

            <a href="#cursos">
                Cursos
            </a>


            <?php if (isset($_SESSION["usuario_id"])): ?>

                <a href="perfil.php">
                    Mi perfil
                </a>

                <a href="cerrar_sesion.php">
                    Cerrar sesión
                </a>

            <?php else: ?>

                <a href="index.php">
                    Iniciar sesión
                </a>

                <a href="registro.php">
                    Crear cuenta
                </a>

            <?php endif; ?>

        </nav>

    </header>


    <!-- ==========================================
         PRESENTACIÓN
         ========================================== -->

    <main>

        <section class="presentacion">

            <div class="presentacion-texto">

                <h1>
                    Aprende algo nuevo
                </h1>

                <p>
                    Encuentra cursos, desarrolla nuevas
                    habilidades y aprende a tu propio ritmo.
                </p>

                <a
                    href="#cursos"
                    class="boton-principal"
                >
                    Explorar cursos
                </a>

            </div>


            <div class="presentacion-imagen">

                <img
                    src="images/imagen.jpg"
                    alt="Cursos CPI"
                >

            </div>

        </section>


        <!-- ==========================================
             CURSOS
             ========================================== -->

        <section
            id="cursos"
            class="seccion-cursos"
        >

            <h2>
                Cursos disponibles
            </h2>

            <p class="subtitulo">

                Aprende con nuestros cursos y
                comienza a desarrollar nuevas habilidades.

            </p>


            <div class="contenedor-cursos">


                <?php if ($resultado->num_rows > 0): ?>


                    <?php while ($curso = $resultado->fetch_assoc()): ?>


                        <article class="tarjeta-curso">


                            <!-- IMAGEN -->

                            <div class="imagen-curso">

                                <?php if (
                                    !empty($curso["Curso_imagen"])
                                ): ?>

                                    <img
                                        src="<?php
                                        echo htmlspecialchars(
                                            $curso["Curso_imagen"]
                                        );
                                        ?>"
                                        alt="<?php
                                        echo htmlspecialchars(
                                            $curso["Curso_nombre"]
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
                                        $curso["Curso_nombre"]
                                    );
                                    ?>

                                </h3>


                                <p>

                                    <?php
                                    echo htmlspecialchars(
                                        $curso["Curso_desc"]
                                    );
                                    ?>

                                </p>


                                <p>

                                    Instructor:

                                    <?php
                                    echo htmlspecialchars(
                                        $curso["Usuario_nombre"]
                                    );
                                    ?>

                                </p>


                                <span class="precio">

                                    $
                                    <?php
                                    echo number_format(
                                        $curso["Curso_precio"],
                                        2
                                    );
                                    ?>
                                    MXN

                                </span>


                                <a
                                    href="curso_ver.php?id=<?php
                                    echo $curso["Curso_id"];
                                    ?>"
                                    class="boton-curso"
                                >
                                    Ver curso
                                </a>

                            </div>


                        </article>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div class="sin-cursos">

                        <h3>
                            No hay cursos disponibles
                        </h3>

                        <p>
                            Actualmente no hay cursos
                            registrados en la plataforma.
                        </p>

                    </div>


                <?php endif; ?>


            </div>

        </section>


        <!-- ==========================================
             INFORMACIÓN
             ========================================== -->

        <section class="informacion">

            <h2>
                Aprende a tu propio ritmo
            </h2>

            <p>

                Explora nuestros cursos, encuentra
                el contenido que necesitas y desarrolla
                nuevas habilidades desde cualquier lugar.

            </p>

        </section>

    </main>


    <!-- ==========================================
         FOOTER
         ========================================== -->

    <footer>

        <p>
            © 2026 CPI Cursos
        </p>

    </footer>


</body>

</html>