<?php
// La API siempre responderá en formato JSON
header("Content-Type: application/json; charset=UTF-8");

// Permitir solicitudes desde otras páginas
header("Access-Control-Allow-Origin: *");

// Métodos HTTP permitidos
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Headers permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$archivo = "datos/cursos.json";

function responder($codigo, $datos)
{
    http_response_code($codigo);

    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );

    exit;
}

if (!file_exists($archivo)) {

    responder(500, [
        "error" => true,
        "mensaje" => "Error interno del servidor: no se encontró el archivo de cursos."
    ]);
}

$contenido = file_get_contents($archivo);

$cursos = json_decode($contenido, true);


// Comprobar que el JSON sea válido
if ($cursos === null && json_last_error() !== JSON_ERROR_NONE) {

    responder(500, [
        "error" => true,
        "mensaje" => "Error interno del servidor: los datos de cursos no son válidos."
    ]);
}

$metodo = $_SERVER["REQUEST_METHOD"];

function comprobarAutorizacion()
{
    $headers = getallheaders();

    $autorizacion = $headers["Authorization"] ?? "";

    if ($autorizacion !== "Bearer 12345") {

        responder(401, [
            "error" => true,
            "mensaje" => "No autorizado. Se requiere un token válido."
        ]);
    }
}

if ($metodo === "GET") {

    if (isset($_GET["id"])) {

        $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

        // ID inválido
        if ($id === false || $id === null || $id <= 0) {

            responder(400, [
                "error" => true,
                "mensaje" => "El parámetro id debe ser un número entero positivo."
            ]);
        }


        $cursoEncontrado = null;


        foreach ($cursos as $curso) {

            if ($curso["id"] == $id) {

                $cursoEncontrado = $curso;

                break;
            }
        }


        // Curso no encontrado
        if ($cursoEncontrado === null) {

            responder(404, [
                "error" => true,
                "mensaje" => "Curso no encontrado."
            ]);
        }


        // Curso encontrado
        responder(200, [
            "error" => false,
            "curso" => $cursoEncontrado
        ]);
    }

    responder(200, [
        "error" => false,
        "total" => count($cursos),
        "cursos" => $cursos
    ]);
}

if ($metodo === "POST") {

    // Comprobar autorización
    comprobarAutorizacion();


    // Leer información enviada en JSON
    $datos = json_decode(file_get_contents("php://input"), true);


    // Comprobar que recibimos JSON válido
    if (!is_array($datos)) {

        responder(400, [
            "error" => true,
            "mensaje" => "Los datos enviados no tienen un formato JSON válido."
        ]);
    }


    // Campos obligatorios
    $camposObligatorios = [
        "titulo",
        "descripcion",
        "categoria",
        "instructor",
        "precio",
        "duracion"
    ];


    // Comprobar campos
    foreach ($camposObligatorios as $campo) {

        if (!isset($datos[$campo]) || $datos[$campo] === "") {

            responder(400, [
                "error" => true,
                "mensaje" => "Falta el campo obligatorio: " . $campo
            ]);
        }
    }


    // Comprobar precio
    if (!is_numeric($datos["precio"]) || $datos["precio"] < 0) {

        responder(400, [
            "error" => true,
            "mensaje" => "El precio debe ser un número mayor o igual a 0."
        ]);
    }


    // Generar nuevo ID
    $nuevoId = 1;

    if (count($cursos) > 0) {

        $ids = array_column($cursos, "id");

        $nuevoId = max($ids) + 1;
    }


    // Crear curso
    $nuevoCurso = [
        "id" => $nuevoId,
        "titulo" => $datos["titulo"],
        "descripcion" => $datos["descripcion"],
        "categoria" => $datos["categoria"],
        "instructor" => $datos["instructor"],
        "precio" => (float)$datos["precio"],
        "duracion" => $datos["duracion"],
        "imagen" => $datos["imagen"] ?? ""
    ];


    // Agregar curso al arreglo
    $cursos[] = $nuevoCurso;


    // Guardar archivo
    $resultado = file_put_contents(
        $archivo,
        json_encode(
            $cursos,
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        )
    );


    // Error al guardar
    if ($resultado === false) {

        responder(500, [
            "error" => true,
            "mensaje" => "No fue posible guardar el nuevo curso."
        ]);
    }


    // Curso creado
    responder(201, [
        "error" => false,
        "mensaje" => "Curso creado correctamente.",
        "curso" => $nuevoCurso
    ]);
}

if ($metodo === "PUT") {

    // Comprobar autorización
    comprobarAutorizacion();


    // Comprobar ID
    if (!isset($_GET["id"])) {

        responder(400, [
            "error" => true,
            "mensaje" => "Debe proporcionar el parámetro id."
        ]);
    }


    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);


    if ($id === false || $id === null || $id <= 0) {

        responder(400, [
            "error" => true,
            "mensaje" => "El parámetro id debe ser un número entero positivo."
        ]);
    }


    // Buscar curso
    $indice = -1;

    foreach ($cursos as $i => $curso) {

        if ($curso["id"] == $id) {

            $indice = $i;

            break;
        }
    }


    // Curso no encontrado
    if ($indice === -1) {

        responder(404, [
            "error" => true,
            "mensaje" => "Curso no encontrado."
        ]);
    }


    // Leer datos enviados
    $datos = json_decode(file_get_contents("php://input"), true);


    if (!is_array($datos)) {

        responder(400, [
            "error" => true,
            "mensaje" => "Los datos enviados no tienen un formato JSON válido."
        ]);
    }


    // Campos obligatorios
    $camposObligatorios = [
        "titulo",
        "descripcion",
        "categoria",
        "instructor",
        "precio",
        "duracion"
    ];


    foreach ($camposObligatorios as $campo) {

        if (!isset($datos[$campo]) || $datos[$campo] === "") {

            responder(400, [
                "error" => true,
                "mensaje" => "Falta el campo obligatorio: " . $campo
            ]);
        }
    }


    // Comprobar precio
    if (!is_numeric($datos["precio"]) || $datos["precio"] < 0) {

        responder(400, [
            "error" => true,
            "mensaje" => "El precio debe ser un número mayor o igual a 0."
        ]);
    }


    // Actualizar curso
    $cursos[$indice] = [
        "id" => $id,
        "titulo" => $datos["titulo"],
        "descripcion" => $datos["descripcion"],
        "categoria" => $datos["categoria"],
        "instructor" => $datos["instructor"],
        "precio" => (float)$datos["precio"],
        "duracion" => $datos["duracion"],
        "imagen" => $datos["imagen"] ?? ""
    ];


    // Guardar cambios
    $resultado = file_put_contents(
        $archivo,
        json_encode(
            $cursos,
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        )
    );


    if ($resultado === false) {

        responder(500, [
            "error" => true,
            "mensaje" => "No fue posible actualizar el curso."
        ]);
    }


    responder(200, [
        "error" => false,
        "mensaje" => "Curso actualizado correctamente.",
        "curso" => $cursos[$indice]
    ]);
}

if ($metodo === "DELETE") {

    // Comprobar autorización
    comprobarAutorizacion();


    // Comprobar ID
    if (!isset($_GET["id"])) {

        responder(400, [
            "error" => true,
            "mensaje" => "Debe proporcionar el parámetro id."
        ]);
    }


    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);


    if ($id === false || $id === null || $id <= 0) {

        responder(400, [
            "error" => true,
            "mensaje" => "El parámetro id debe ser un número entero positivo."
        ]);
    }


    // Buscar curso
    $indice = -1;

    foreach ($cursos as $i => $curso) {

        if ($curso["id"] == $id) {

            $indice = $i;

            break;
        }
    }


    // Curso no encontrado
    if ($indice === -1) {

        responder(404, [
            "error" => true,
            "mensaje" => "Curso no encontrado."
        ]);
    }


    // Guardar curso eliminado para responder
    $cursoEliminado = $cursos[$indice];


    // Eliminar
    array_splice($cursos, $indice, 1);


    // Guardar archivo
    $resultado = file_put_contents(
        $archivo,
        json_encode(
            $cursos,
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        )
    );


    if ($resultado === false) {

        responder(500, [
            "error" => true,
            "mensaje" => "No fue posible eliminar el curso."
        ]);
    }


    responder(200, [
        "error" => false,
        "mensaje" => "Curso eliminado correctamente.",
        "curso" => $cursoEliminado
    ]);
}

responder(405, [
    "error" => true,
    "mensaje" => "Método HTTP no permitido."
]);

?>