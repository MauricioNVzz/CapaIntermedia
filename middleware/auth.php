
<?php

function verificarAcceso(array $rolesPermitidos = []): void
{
    // Comprobar que exista una sesión iniciada
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Comprobar que el usuario haya iniciado sesión
    if (
        !isset($_SESSION["usuario_id"]) ||
        !isset($_SESSION["usuario_rol"])
    ) {
        header("Location: /CPI/index.php");
        exit;
    }

    // Si se especifican roles, comprobar los permisos
    if (
        !empty($rolesPermitidos) &&
        !in_array(
            (int) $_SESSION["usuario_rol"],
            $rolesPermitidos,
            true
        )
    ) {
        http_response_code(403);

        echo "Acceso denegado. No tienes permisos para entrar a esta página.";
        exit;
    }
}