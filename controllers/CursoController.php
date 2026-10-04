<?php

require_once "../models/Curso.php";

class CursoController
{
    private $curso;

    public function __construct($conexion)
    {
        $this->curso = new Curso($conexion);
    }

    // GET: obtener todos los cursos
    public function obtenerTodos()
    {
        return $this->curso->obtenerTodos();
    }

    // Obtener todas las categorías
public function obtenerCategorias()
{
    return $this->curso->obtenerCategorias();
}

    // GET: obtener un curso por ID
    public function obtenerPorId($id)
    {
        return $this->curso->obtenerPorId($id);
    }
    // Comprobar que la categoría existe
public function comprobarCategoria($categoria)
{
    return $this->curso->comprobarCategoria($categoria);
}

// Comprobar que el instructor existe y tiene rol Maestro
public function comprobarInstructor($instructor)
{
    return $this->curso->comprobarInstructor($instructor);
}
// Crear un nuevo curso
public function crearCurso(
    $categoria,
    $instructor,
    $titulo,
    $precio,
    $descripcion,
    $duracion,
    $imagen
) {
    return $this->curso->crearCurso(
        $categoria,
        $instructor,
        $titulo,
        $precio,
        $descripcion,
        $duracion,
        $imagen
    );
}
// Actualizar un curso
public function actualizarCurso(
    $id,
    $categoria,
    $instructor,
    $titulo,
    $precio,
    $descripcion,
    $duracion,
    $imagen
) {
    return $this->curso->actualizarCurso(
        $id,
        $categoria,
        $instructor,
        $titulo,
        $precio,
        $descripcion,
        $duracion,
        $imagen
    );
}
// Eliminar un curso
public function eliminarCurso($id)
{
    return $this->curso->eliminarCurso($id);
}
}
?>