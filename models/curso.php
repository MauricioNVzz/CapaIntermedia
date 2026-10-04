<?php

class Curso
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    // Obtener todos los cursos
    public function obtenerTodos()
    {
        $sql = "SELECT 
                    c.Curso_id,
                    c.Curso_nombre,
                    c.Curso_desc,
                    c.Curso_precio,
                    c.Curso_duracion,
                    c.Curso_imagen,
                    cat.Categoria_nombre,
                    u.Usuario_nombre
                FROM Cursos c
                INNER JOIN Categorias cat
                    ON c.Curso_id_cat = cat.Categoria_id
                INNER JOIN Usuarios u
                    ON c.Curso_id_maestro = u.Usuario_id";

        $resultado = $this->conexion->query($sql);

        if (!$resultado) {
            throw new Exception("Error al obtener los cursos.");
        }

        $cursos = [];

        while ($fila = $resultado->fetch_assoc()) {
            $cursos[] = [
                "id" => (int)$fila["Curso_id"],
                "titulo" => $fila["Curso_nombre"],
                "descripcion" => $fila["Curso_desc"],
                "categoria" => $fila["Categoria_nombre"],
                "instructor" => $fila["Usuario_nombre"],
                "precio" => (float)$fila["Curso_precio"],
                "duracion" => $fila["Curso_duracion"],
                "imagen" => $fila["Curso_imagen"]
            ];
        }

        return $cursos;
    }

    // Obtener todas las categorías
public function obtenerCategorias()
{
    $sql = "
        SELECT Categoria_id, Categoria_nombre
        FROM Categorias
    ";

    $resultado = $this->conexion->query($sql);

    if (!$resultado) {
        throw new Exception("Error al obtener las categorías.");
    }

    $categorias = [];

    while ($fila = $resultado->fetch_assoc()) {
        $categorias[] = [
            "id" => (int)$fila["Categoria_id"],
            "nombre" => $fila["Categoria_nombre"]
        ];
    }

    return $categorias;
}

    // Obtener un curso por ID
    public function obtenerPorId($id)
    {
        $sql = "SELECT 
                    c.Curso_id,
                    c.Curso_nombre,
                    c.Curso_desc,
                    c.Curso_precio,
                    c.Curso_duracion,
                    c.Curso_imagen,
                    cat.Categoria_nombre,
                    u.Usuario_nombre
                FROM Cursos c
                INNER JOIN Categorias cat
                    ON c.Curso_id_cat = cat.Categoria_id
                INNER JOIN Usuarios u
                    ON c.Curso_id_maestro = u.Usuario_id
                WHERE c.Curso_id = ?";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            throw new Exception("Error al preparar la consulta.");
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {
            return null;
        }

        $fila = $resultado->fetch_assoc();

        return [
            "id" => (int)$fila["Curso_id"],
            "titulo" => $fila["Curso_nombre"],
            "descripcion" => $fila["Curso_desc"],
            "categoria" => $fila["Categoria_nombre"],
            "instructor" => $fila["Usuario_nombre"],
            "precio" => (float)$fila["Curso_precio"],
            "duracion" => $fila["Curso_duracion"],
            "imagen" => $fila["Curso_imagen"]
        ];
    }
    // Comprobar que una categoría existe
public function comprobarCategoria($categoria)
{
    $sql = "
        SELECT Categoria_id
        FROM Categorias
        WHERE Categoria_id = ?
    ";

    $stmt = $this->conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de categoría.");
    }

    $stmt->bind_param("i", $categoria);
    $stmt->execute();

    $resultado = $stmt->get_result();

    $existe = $resultado->num_rows > 0;

    $stmt->close();

    return $existe;
}

// Comprobar que un instructor existe y tiene rol Maestro
public function comprobarInstructor($instructor)
{
    $sql = "
        SELECT u.Usuario_id
        FROM Usuarios u
        INNER JOIN Roles r
            ON u.Usuario_id_rol = r.Rol_id
        WHERE u.Usuario_id = ?
        AND r.Rol_nombre = 'Maestro'
    ";

    $stmt = $this->conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta del instructor.");
    }

    $stmt->bind_param("i", $instructor);
    $stmt->execute();

    $resultado = $stmt->get_result();

    $esInstructor = $resultado->num_rows > 0;

    $stmt->close();

    return $esInstructor;
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
    $sql = "
        INSERT INTO Cursos
        (
            Curso_id_cat,
            Curso_id_maestro,
            Curso_nombre,
            Curso_precio,
            Curso_desc,
            Curso_duracion,
            Curso_imagen
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $this->conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error al preparar el registro del curso.");
    }

    $stmt->bind_param(
        "iisdsss",
        $categoria,
        $instructor,
        $titulo,
        $precio,
        $descripcion,
        $duracion,
        $imagen
    );

    $stmt->execute();

    $nuevoId = $stmt->insert_id;

    $stmt->close();

    return $nuevoId;
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
    $sql = "
        UPDATE Cursos
        SET
            Curso_id_cat = ?,
            Curso_id_maestro = ?,
            Curso_nombre = ?,
            Curso_precio = ?,
            Curso_desc = ?,
            Curso_duracion = ?,
            Curso_imagen = ?
        WHERE Curso_id = ?
    ";

    $stmt = $this->conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error al preparar la actualización.");
    }

    $stmt->bind_param(
        "iisdsssi",
        $categoria,
        $instructor,
        $titulo,
        $precio,
        $descripcion,
        $duracion,
        $imagen,
        $id
    );

    $stmt->execute();

    $stmt->close();

    return true;
}
// Eliminar un curso
public function eliminarCurso($id)
{
    $sql = "
        DELETE FROM Cursos
        WHERE Curso_id = ?
    ";

    $stmt = $this->conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error al preparar la eliminación.");
    }

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $stmt->close();

    return true;
}
}
?>