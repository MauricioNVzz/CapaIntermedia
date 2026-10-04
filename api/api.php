<?php

// CONFIGURACION DE LA API

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// CONEXION A MYSQL

include "../conexion.php";

require_once "../controllers/CursoController.php";

$cursoController = new CursoController($conexion);


// FUNCION PARA RESPONDER EN JSON

function responder($codigo, $datos)
{
    http_response_code($codigo);

    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );

    exit;
}

// METODO OPTIONS

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo === "OPTIONS") {
    http_response_code(200);
    exit;
}

// COMPROBAR AUTORIZACION

function comprobarAutorizacion()
{
    $headers = getallheaders();

    $autorizacion = $headers["Authorization"] ?? "";

    if ($autorizacion !== "Bearer 12345") {

        responder(401, [
            "error" => true,
            "mensaje" => "No autorizado. Se requiere un token valido."
        ]);
    }
}

// GET

if ($metodo === "GET") {

    try {

        // GET /api.php?id=1

        if (isset($_GET["id"])) {

            $id = filter_input(
                INPUT_GET,
                "id",
                FILTER_VALIDATE_INT
            );

            if ($id === false || $id === null || $id <= 0) {

                responder(400, [
                    "error" => true,
                    "mensaje" => "El parametro id debe ser un numero entero positivo."
                ]);
            }

            $curso = $cursoController->obtenerPorId($id);

            if ($curso === null) {

                responder(404, [
                    "error" => true,
                    "mensaje" => "Curso no encontrado."
                ]);
            }

            responder(200, [
                "error" => false,
                "curso" => $curso
            ]);
        }

        // GET /api.php
        // Obtener todos los cursos

        $cursos = $cursoController->obtenerTodos();

        responder(200, [
            "error" => false,
            "total" => count($cursos),
            "cursos" => $cursos
        ]);

    } catch (Throwable $e) {

        responder(500, [
            "error" => true,
            "mensaje" => "Error interno del servidor."
        ]);
    }
}

// POST

if ($metodo === "POST") {

    comprobarAutorizacion();

    $datos = json_decode(
        file_get_contents("php://input"),
        true
    );

    // Validar JSON

    if (!is_array($datos)) {

        responder(400, [
            "error" => true,
            "mensaje" => "Los datos enviados no tienen un formato JSON valido."
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

        if (
            !isset($datos[$campo]) ||
            $datos[$campo] === ""
        ) {

            responder(400, [
                "error" => true,
                "mensaje" => "Falta el campo obligatorio: " . $campo
            ]);
        }
    }

    // Validar precio

    if (
        !is_numeric($datos["precio"]) ||
        $datos["precio"] < 0
    ) {

        responder(400, [
            "error" => true,
            "mensaje" => "El precio debe ser un numero mayor o igual a 0."
        ]);
    }

    // Validar categoria

   if (
    !isset($datos["categoria"]) ||
    $datos["categoria"] === "" ||
    !filter_var($datos["categoria"], FILTER_VALIDATE_INT) ||
    (int)$datos["categoria"] <= 0
) {
    responder(400, [
        "error" => true,
        "mensaje" => "La categoria debe ser un ID valido."
    ]);
}

$categoria = (int)$datos["categoria"];

if (!$cursoController->comprobarCategoria($categoria)) {
    responder(400, [
        "error" => true,
        "mensaje" => "La categoria indicada no existe."
    ]);
}

    // Validar instructor

    if (
    !isset($datos["instructor"]) ||
    $datos["instructor"] === "" ||
    !filter_var($datos["instructor"], FILTER_VALIDATE_INT) ||
    (int)$datos["instructor"] <= 0
) {
    responder(400, [
        "error" => true,
        "mensaje" => "El instructor debe ser un ID valido."
    ]);
}

$instructor = (int)$datos["instructor"];

if (!$cursoController->comprobarInstructor($instructor)) {
    responder(400, [
        "error" => true,
        "mensaje" => "El instructor indicado no existe o no tiene el rol Maestro."
    ]);
}

// Crear curso mediante el controlador

$titulo = $datos["titulo"];
$descripcion = $datos["descripcion"];
$precio = (float)$datos["precio"];
$duracion = $datos["duracion"];
$imagen = $datos["imagen"] ?? "";

try {

    $nuevoId = $cursoController->crearCurso(
        $categoria,
        $instructor,
        $titulo,
        $precio,
        $descripcion,
        $duracion,
        $imagen
    );

} catch (Throwable $e) {

    responder(500, [
        "error" => true,
        "mensaje" => "No fue posible crear el curso."
    ]);
}

    // Obtener curso creado

$nuevoCurso = $cursoController->obtenerPorId($nuevoId);

    responder(201, [
        "error" => false,
        "mensaje" => "Curso creado correctamente.",
        "curso" => $nuevoCurso
    ]);
}

// PUT

if ($metodo === "PUT") {

    comprobarAutorizacion();

    // Comprobar ID

    if (!isset($_GET["id"])) {

        responder(400, [
            "error" => true,
            "mensaje" => "Debe proporcionar el parametro id."
        ]);
    }

    $id = filter_input(
        INPUT_GET,
        "id",
        FILTER_VALIDATE_INT
    );

    if ($id === false || $id === null || $id <= 0) {

        responder(400, [
            "error" => true,
            "mensaje" => "El parametro id debe ser un numero entero positivo."
        ]);
    }

    // Comprobar que el curso existe

    $cursoExistente = $cursoController->obtenerPorId($id);

if ($cursoExistente === null) {

    responder(404, [
        "error" => true,
        "mensaje" => "Curso no encontrado."
    ]);
}

    // Leer JSON

    $datos = json_decode(
        file_get_contents("php://input"),
        true
    );

    if (!is_array($datos)) {

        responder(400, [
            "error" => true,
            "mensaje" => "Los datos enviados no tienen un formato JSON valido."
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

        if (
            !isset($datos[$campo]) ||
            $datos[$campo] === ""
        ) {

            responder(400, [
                "error" => true,
                "mensaje" => "Falta el campo obligatorio: " . $campo
            ]);
        }
    }


    // Validar precio

    if (
        !is_numeric($datos["precio"]) ||
        $datos["precio"] < 0
    ) {

        responder(400, [
            "error" => true,
            "mensaje" => "El precio debe ser un numero mayor o igual a 0."
        ]);
    }

    // Validar categoria

    if (
    !isset($datos["categoria"]) ||
    $datos["categoria"] === "" ||
    !filter_var($datos["categoria"], FILTER_VALIDATE_INT) ||
    (int)$datos["categoria"] <= 0
) {
    responder(400, [
        "error" => true,
        "mensaje" => "La categoria debe ser un ID valido."
    ]);
}

$categoria = (int)$datos["categoria"];

if (!$cursoController->comprobarCategoria($categoria)) {
    responder(400, [
        "error" => true,
        "mensaje" => "La categoria indicada no existe."
    ]);
}

    // Validar instructor

   if (
    !isset($datos["instructor"]) ||
    $datos["instructor"] === "" ||
    !filter_var($datos["instructor"], FILTER_VALIDATE_INT) ||
    (int)$datos["instructor"] <= 0
) {
    responder(400, [
        "error" => true,
        "mensaje" => "El instructor debe ser un ID valido."
    ]);
}

$instructor = (int)$datos["instructor"];

if (!$cursoController->comprobarInstructor($instructor)) {
    responder(400, [
        "error" => true,
        "mensaje" => "El instructor indicado no existe o no tiene el rol Maestro."
    ]);
}

// Actualizar curso mediante el controlador

$titulo = $datos["titulo"];
$descripcion = $datos["descripcion"];
$precio = (float)$datos["precio"];
$duracion = $datos["duracion"];
$imagen = $datos["imagen"] ?? "";

try {

    $cursoController->actualizarCurso(
        $id,
        $categoria,
        $instructor,
        $titulo,
        $precio,
        $descripcion,
        $duracion,
        $imagen
    );

} catch (Throwable $e) {

    responder(500, [
        "error" => true,
        "mensaje" => "No fue posible actualizar el curso."
    ]);
}

    // Obtener curso actualizado

    $cursoActualizado = $cursoController->obtenerPorId($id);

    responder(200, [
    "error" => false,
    "mensaje" => "Curso actualizado correctamente.",
    "curso" => $cursoActualizado
]);
}

// DELETE
if ($metodo === "DELETE") {

    comprobarAutorizacion();

    if (!isset($_GET["id"])) {
        responder(400, [
            "error" => true,
            "mensaje" => "Debe proporcionar el id del curso."
        ]);
    }

    $id = filter_input(
        INPUT_GET,
        "id",
        FILTER_VALIDATE_INT
    );

    if ($id === false || $id === null || $id <= 0) {
        responder(400, [
            "error" => true,
            "mensaje" => "El parametro id debe ser un numero entero positivo."
        ]);
    }

    try {

        // Comprobar que el curso existe
        $cursoExistente = $cursoController->obtenerPorId($id);

        if ($cursoExistente === null) {
            responder(404, [
                "error" => true,
                "mensaje" => "Curso no encontrado."
            ]);
        }

        // Eliminar mediante el controlador
        $cursoController->eliminarCurso($id);

        responder(200, [
            "error" => false,
            "mensaje" => "Curso eliminado correctamente."
        ]);

    } catch (mysqli_sql_exception $e) {

        // Error 1451 = el curso tiene registros relacionados
        if ($e->getCode() == 1451) {

            responder(409, [
                "error" => true,
                "mensaje" => "No se puede eliminar el curso porque tiene registros relacionados."
            ]);
        }

        responder(500, [
            "error" => true,
            "mensaje" => "Error interno del servidor."
        ]);

    } catch (Throwable $e) {

        responder(500, [
            "error" => true,
            "mensaje" => "Error interno del servidor."
        ]);
    }
}

// 405
responder(405, [
    "error" => true,
    "mensaje" => "Metodo HTTP no permitido."
]);

?>
