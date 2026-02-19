<?php
/**
 * CIERRE DE SESIÓN: logout.php
 * Finalidad: Destruir la sesión del administrador y redirigir al login.
 */

require __DIR__ . "/inc/auth.php";
admin_logout(); // Llama a la lógica de destrucción de sesión

header("Location: login.php");
exit;
