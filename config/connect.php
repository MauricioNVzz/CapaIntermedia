<?php

class Conexion
{
    private $servidor = "localhost";
    private $usuario = "root";
    private $password = "";
    private $base_datos = "cursos_cpi";

    private $conexion;

    public function conectar()
    {
        if ($this->conexion instanceof mysqli) {
            return $this->conexion;
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->conexion = new mysqli(
                $this->servidor,
                $this->usuario,
                $this->password,
                $this->base_datos
            );

            $this->conexion->set_charset("utf8mb4");

            return $this->conexion;

        } catch (mysqli_sql_exception $e) {
            error_log("Error de conexión MySQL: " . $e->getMessage());

            throw new RuntimeException(
                "No fue posible conectar con la base de datos."
            );
        }
    }
}
