<?php

require_once __DIR__ . "/../models/curso.php";

class CursoController
{
    private $curso;

    public function __construct($conexion)
    {
        $this->curso = new Curso($conexion);
    }

    public function obtenerTodos()
    {
        return $this->curso->obtenerTodos();
    }

    public function obtenerPorId($id)
    {
        return $this->curso->obtenerPorId($id);
    }

    public function comprobarCategoria($categoria)
    {
        return $this->curso->comprobarCategoria($categoria);
    }

    public function comprobarInstructor($instructor)
    {
        return $this->curso->comprobarInstructor($instructor);
    }

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

    public function eliminarCurso($id)
    {
        return $this->curso->eliminarCurso($id);
    }

    public function obtenerCategorias()
    {
        return $this->curso->obtenerCategorias();
    }

    public function obtenerPorMaestro($maestroId)
    {
        return $this->curso
            ->obtenerPorMaestro($maestroId);
    }
}

?>
