<?php

class Comentario
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerPorMultimedia($multimediaId)
    {
        $sql = "
            SELECT
                c.Comentario_id,
                c.Comentario_contenido,
                c.Comentario_fecha,
                u.Usuario_id,
                u.Usuario_nombre,
                u.Usuario_foto
            FROM Comentarios c
            INNER JOIN Usuarios u
                ON c.Comentario_id_usuario = u.Usuario_id
            WHERE c.Comentario_id_multimedia = ?
            ORDER BY c.Comentario_fecha DESC
        ";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            throw new Exception(
                "No se pudo preparar la consulta de comentarios."
            );
        }

        $stmt->bind_param("i", $multimediaId);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $comentarios = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $comentarios;
    }

    public function crear($usuarioId, $multimediaId, $contenido)
    {
        $contenido = trim($contenido);

        if ($contenido === "") {
            throw new InvalidArgumentException(
                "Escribe un comentario antes de enviarlo."
            );
        }

        if (mb_strlen($contenido) > 2000) {
            throw new InvalidArgumentException(
                "El comentario no puede superar los 2000 caracteres."
            );
        }

        $sql = "
            INSERT INTO Comentarios (
                Comentario_id_usuario,
                Comentario_id_multimedia,
                Comentario_contenido
            )
            VALUES (?, ?, ?)
        ";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            throw new Exception(
                "No se pudo preparar el registro del comentario."
            );
        }

        $stmt->bind_param(
            "iis",
            $usuarioId,
            $multimediaId,
            $contenido
        );

        $stmt->execute();

        $id = $stmt->insert_id;

        $stmt->close();

        return $id;
    }
}
