<?php

require_once __DIR__ . "/../models/multimedia.php";

class MultimediaController
{
    private $multimedia;

    public function __construct($conexion)
    {
        $this->multimedia =
            new Multimedia($conexion);
    }

    public function obtenerPorCurso($cursoId)
    {
        return $this->multimedia
            ->obtenerPorCurso($cursoId);
    }

    public function agregar(
        $cursoId,
        $tipo,
        $archivo,
        $titulo,
        $descripcion,
        $orden,
        $gratuito
    ) {
        return $this->multimedia->agregar(
            $cursoId,
            $tipo,
            $archivo,
            $titulo,
            $descripcion,
            $orden,
            $gratuito
        );
    }
    public function obtenerCursoDeMaestro($cursoId, $maestroId)
{
    return $this->multimedia->obtenerCursoDeMaestro(
        $cursoId,
        $maestroId
    );
}
}
?>