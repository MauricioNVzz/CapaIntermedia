<?php
session_start();

include "conexion.php";
require_once "controllers/MultimediaController.php";

$multimediaController = new MultimediaController($conexion);

// Obtener y validar el ID del curso
$id = $_GET["id"] ?? "";

if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {
    die("ID de curso no válido.");
}

// Consultar el curso y su maestro
$sql = "
    SELECT
        c.Curso_id,
        c.Curso_nombre,
        c.Curso_desc,
        c.Curso_precio,
        c.Curso_duracion,
        c.Curso_imagen,
        u.Usuario_nombre
    FROM Cursos c
    INNER JOIN Usuarios u
        ON c.Curso_id_maestro = u.Usuario_id
    WHERE c.Curso_id = ?
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error al preparar la consulta del curso.");
}

$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("El curso no existe.");
}

$curso = $resultado->fetch_assoc();
$stmt->close();

// Comprobar si el usuario compró el curso
$cursoComprado = false;

if (isset($_SESSION["usuario_id"])) {
    $usuarioId = (int) $_SESSION["usuario_id"];

    $sqlCompra = "
        SELECT Venta_id
        FROM Ventas
        WHERE Venta_id_usuario = ?
          AND Venta_id_curso = ?
          AND Venta_estado = 'Completada'
    ";

    $stmtCompra = $conexion->prepare($sqlCompra);

    if (!$stmtCompra) {
        die("Error al comprobar la compra.");
    }

    $stmtCompra->bind_param("ii", $usuarioId, $id);
    $stmtCompra->execute();

    $resultadoCompra = $stmtCompra->get_result();

    if ($resultadoCompra->num_rows > 0) {
        $cursoComprado = true;
    }

    $stmtCompra->close();
}

// Obtener la multimedia del curso
$multimedia = $multimediaController->obtenerPorCurso($id);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($curso["Curso_nombre"], ENT_QUOTES, "UTF-8"); ?>
    </title>

    <link rel="stylesheet" href="css/curso_ver.css">
</head>

<body>

    <div class="ventana-curso">

        <!-- BARRA SUPERIOR -->
        <div class="barra-superior">

            <h1>
                <?php echo htmlspecialchars($curso["Curso_nombre"], ENT_QUOTES, "UTF-8"); ?>
            </h1>

            <div class="foto-perfil">
                <img src="" alt="Foto de perfil">
            </div>

        </div>

        <!-- CONTENIDO DEL CURSO -->
        <div class="contenido">

            <!-- INFORMACIÓN DEL TUTOR -->
            <div class="informacion-tutor">

                <div class="datos-tutor">
                    <p class="tutor">
                        Tutor:
                        <a href="#">
                            <?php echo htmlspecialchars($curso["Usuario_nombre"], ENT_QUOTES, "UTF-8"); ?>
                        </a>
                    </p>
                </div>

                <!-- PERFIL DEL TUTOR Y COMPRA -->
                <div class="perfil-tutor">

                    <div class="foto-juan">
                        <img src="" alt="Foto de perfil del tutor">
                    </div>

                    <?php if ($cursoComprado): ?>

                        <p class="curso-adquirido">
                            Curso adquirido
                        </p>

                    <?php else: ?>

                        <a
                            href="curso_venta.php?id=<?php echo (int) $curso["Curso_id"]; ?>"
                            class="boton-adquirir"
                        >
                            Adquirir curso
                        </a>

                    <?php endif; ?>

                    <p class="precio">
                        $<?php echo number_format((float) $curso["Curso_precio"], 2); ?>
                    </p>

                </div>

            </div>

            <!-- DESCRIPCIÓN -->
            <p class="descripcion">
                <?php echo htmlspecialchars($curso["Curso_desc"], ENT_QUOTES, "UTF-8"); ?>
            </p>

            <!-- DURACIÓN -->
            <p class="duracion">
                Duración:
                <?php echo htmlspecialchars($curso["Curso_duracion"], ENT_QUOTES, "UTF-8"); ?>
            </p>

            <!-- LISTA DE LECCIONES -->
            <div class="cuadro-lecciones">

                <?php if (count($multimedia) > 0): ?>

                    <?php foreach ($multimedia as $leccion): ?>

                        <?php
                        $puedeVer = $cursoComprado || $leccion["gratuito"];
                        $tipo = strtolower($leccion["tipo"]);

                        // Codificar los datos para pasarlos de forma segura a JavaScript.
                        $datosLeccion = htmlspecialchars(
    json_encode([
        "id" => (int) $leccion["id"],
        "archivo" => $leccion["archivo"],
        "tipo" => $tipo,
        "titulo" => $leccion["titulo"]
    ], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS |
       JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP),
    ENT_QUOTES,
    "UTF-8"
);
                        ?>

                        <?php if ($puedeVer): ?>

                            <button
                                class="leccion"
                                type="button"
                                onclick='abrirLeccion(<?php echo $datosLeccion; ?>)'
                            >

                                <div class="video">
                                    <?php if ($tipo === "imagen"): ?>
                                        <span>🖼️</span>
                                    <?php elseif ($tipo === "pdf"): ?>
                                        <span>📄</span>
                                    <?php else: ?>
                                        <span>▶</span>
                                    <?php endif; ?>
                                </div>

                                <div class="texto-leccion">
                                    <p>
                                        <?php echo htmlspecialchars($leccion["titulo"], ENT_QUOTES, "UTF-8"); ?>
                                    </p>
                                </div>

                            </button>

                        <?php else: ?>

                            <button class="leccion" type="button" disabled>

                                <div class="video">
                                    <span>🔒</span>
                                </div>

                                <div class="texto-leccion">
                                    <p>
                                        <?php echo htmlspecialchars($leccion["titulo"], ENT_QUOTES, "UTF-8"); ?>
                                    </p>
                                </div>

                            </button>

                        <?php endif; ?>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p>Este curso todavía no tiene lecciones disponibles.</p>

                <?php endif; ?>

            </div>

        </div><!-- Fin de .contenido -->

        <!-- VISOR ÚNICO DE IMÁGENES -->
        <div
            id="visor-imagen"
            class="visor-imagen"
            style="display: none;"
        >

            <div class="visor-imagen-contenido">

                <button
                    type="button"
                    class="cerrar-visor"
                    onclick="cerrarLeccion()"
                >
                    Cerrar ✕
                </button>

                <h2 id="titulo-leccion"></h2>

                <img
                    id="imagen-leccion"
                    src=""
                    alt="Contenido del curso"
                >

                <video
    id="video-leccion"
    controls
    playsinline
    preload="metadata"
    style="display: none;"
>
    Tu navegador no puede reproducir este video.
</video>


<!-- Sección de comentarios -->
<section class="seccion-comentarios">
    <h3>Comentarios de la lección</h3>

    <div id="lista-comentarios">
        <p>Cargando comentarios...</p>
    </div>

    <form id="form-comentario">
        <label for="contenido-comentario">
            Escribe tu comentario
        </label>

        <textarea
            id="contenido-comentario"
            name="contenido"
            maxlength="2000"
            rows="3"
            placeholder="Comparte tu opinión sobre esta lección..."
            required
        ></textarea>

        <button type="submit" id="btn-comentar">
            Publicar comentario
        </button>

        <p id="mensaje-comentario" role="status"></p>
    </form>
</section>

            </div>

        </div><!-- Fin del visor -->

    </div><!-- Fin de .ventana-curso -->

    
<script>
const endpointComentarios = "/CPI/api/comentarios.php";

let multimediaActual = null;

async function cargarComentarios(multimediaId) {
    const lista = document.getElementById("lista-comentarios");
    lista.textContent = "Cargando comentarios...";

    try {
        const respuesta = await fetch(
            `${endpointComentarios}?multimedia_id=${encodeURIComponent(multimediaId)}`
        );

        const datos = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(datos.error || "No se pudieron cargar los comentarios.");
        }

        // Evitar mostrar comentarios de una lección que ya se cerró
        // o se cambió mientras se realizaba la petición.
        if (String(multimediaActual) !== String(multimediaId)) {
            return;
        }

        lista.replaceChildren();

        if (!datos.comentarios || datos.comentarios.length === 0) {
            const mensaje = document.createElement("p");
            mensaje.textContent = "Todavía no hay comentarios. ¡Sé el primero!";
            lista.appendChild(mensaje);
            return;
        }

        datos.comentarios.forEach(comentario => {
            const elemento = document.createElement("article");
            elemento.className = "comentario";

            const nombre = document.createElement("strong");
            nombre.textContent = comentario.Usuario_nombre || "Usuario";

            const fecha = document.createElement("small");
            fecha.textContent = comentario.Comentario_fecha;

            const contenido = document.createElement("p");
            contenido.textContent = comentario.Comentario_contenido;

            elemento.append(nombre, fecha, contenido);
            lista.appendChild(elemento);
        });

    } catch (error) {
        if (String(multimediaActual) !== String(multimediaId)) {
            return;
        }

        lista.textContent = error.message;
    }
}

async function abrirLeccion(leccion) {
    const visor = document.getElementById("visor-imagen");
    const imagen = document.getElementById("imagen-leccion");
    const video = document.getElementById("video-leccion");
    const titulo = document.getElementById("titulo-leccion");

    titulo.textContent = leccion.titulo;

    // El ID debe venir de la lección seleccionada en curso_ver.php.
    multimediaActual = Number(leccion.id);

if (!Number.isInteger(multimediaActual) || multimediaActual <= 0) {
    multimediaActual = null;
    console.error("ID de lección inválido:", leccion);
    document.getElementById("lista-comentarios").textContent =
        "No se pudo identificar esta lección.";
    return;
}
    const lista = document.getElementById("lista-comentarios");
    lista.textContent = "Cargando comentarios...";

    if (leccion.tipo === "imagen") {
        video.pause();
        video.removeAttribute("src");
        video.load();
        video.style.display = "none";

        imagen.src = leccion.archivo;
        imagen.style.display = "block";

        visor.style.display = "flex";
        cargarComentarios(leccion.id);
        return;
    }

    if (leccion.tipo === "video") {
        imagen.removeAttribute("src");
        imagen.style.display = "none";

        video.src = leccion.archivo;
        video.style.display = "block";

        visor.style.display = "flex";
        video.load();

        cargarComentarios(leccion.id);
        return;
    }

    multimediaActual = null;
    alert("Este tipo de contenido todavía no tiene visor.");
}

async function publicarComentario(evento) {
    evento.preventDefault();

    const formulario = document.getElementById("form-comentario");
    const campo = document.getElementById("contenido-comentario");
    const boton = document.getElementById("btn-comentar");
    const mensaje = document.getElementById("mensaje-comentario");

    if (!multimediaActual) {
        mensaje.textContent = "Primero abre una lección.";
        return;
    }

    const contenido = campo.value.trim();

    if (!contenido) {
        mensaje.textContent = "Escribe un comentario antes de publicarlo.";
        return;
    }

    boton.disabled = true;
    mensaje.textContent = "Publicando comentario...";

    try {
        const respuesta = await fetch(endpointComentarios, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                multimedia_id: multimediaActual,
                contenido: contenido
            })
        });

        const datos = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(datos.error || "No se pudo publicar el comentario.");
        }

        campo.value = "";
        mensaje.textContent = datos.mensaje || "Comentario publicado.";

        await cargarComentarios(multimediaActual);

    } catch (error) {
        mensaje.textContent = error.message;
    } finally {
        boton.disabled = false;
    }
}

function cerrarLeccion() {
    const visor = document.getElementById("visor-imagen");
    const imagen = document.getElementById("imagen-leccion");
    const video = document.getElementById("video-leccion");

    multimediaActual = null;

    video.pause();
    video.removeAttribute("src");
    video.load();

    imagen.removeAttribute("src");

    video.style.display = "none";
    imagen.style.display = "none";
    visor.style.display = "none";
}

document
    .getElementById("form-comentario")
    .addEventListener("submit", publicarComentario);
</script>

</body>
</html>