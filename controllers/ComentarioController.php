<?php

require_once __DIR__ . "/../models/Comentario.php";

class ComentarioController
{
    private $comentario;

    public function __construct($conexion)
    {
        $this->comentario = new Comentario($conexion);
    }

    public function obtenerPorMultimedia($multimediaId)
    {
        return $this->comentario
            ->obtenerPorMultimedia((int) $multimediaId);
    }

    public function crear($usuarioId, $multimediaId, $contenido)
    {
        if ((int) $usuarioId <= 0 || (int) $multimediaId <= 0) {
            throw new InvalidArgumentException(
                "El usuario o la lección no son válidos."
            );
        }

        return $this->comentario->crear(
            (int) $usuarioId,
            (int) $multimediaId,
            $contenido
        );
    }
}