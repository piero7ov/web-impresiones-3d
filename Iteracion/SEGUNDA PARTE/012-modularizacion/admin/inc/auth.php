<?php
/**
 * SISTEMA DE AUTENTICACIÓN: auth.php
 * Finalidad: Gestionar las sesiones de administrador y el control de acceso.
 * Proporciona funciones para login, logout y protección de rutas.
 */

declare(strict_types=1);

// Inicio de sesión para manejar variables de sesión de forma global
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

/* ✅ Evitar "Cannot redeclare h()" si el archivo que incluye ya la tiene */
if (!function_exists("h")) {
  function h($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
  }
}

/**
 * Obtiene las credenciales de administrador desde el archivo de configuración.
 * @return array [username, password].
 */
function auth_config(): array {
  $path = __DIR__ . "/auth_config.php";
  if (!file_exists($path)) {
    return ["username" => "admin", "password" => "admin123"];
  }
  $cfg = require $path;
  return is_array($cfg) ? $cfg : ["username" => "admin", "password" => "admin123"];
}

function is_admin_logged(): bool {
  return !empty($_SESSION["admin_auth"]) && $_SESSION["admin_auth"] === true;
}

function admin_username(): string {
  return (string)($_SESSION["admin_user"] ?? "admin");
}

/**
 * Protege una página: si el usuario no está logueado, redirige al login.
 */
function require_admin(): void {
  if (is_admin_logged()) return;

  $next = (string)($_SERVER["REQUEST_URI"] ?? "index.php");
  $nextEnc = urlencode($next);
  header("Location: login.php?next=" . $nextEnc);
  exit;
}

/**
 * Intenta iniciar sesión comparando usuario y contraseña con la configuración.
 * @param string $user Usuario introducido.
 * @param string $pass Contraseña introducida.
 * @param string &$err Variable para devolver mensaje de error.
 * @return bool True si el login es correcto.
 */
function try_login(string $user, string $pass, string &$err): bool {
  $cfg = auth_config();

  $uOk = hash_equals((string)$cfg["username"], $user);
  $pOk = hash_equals((string)$cfg["password"], $pass);

  if (!$uOk || !$pOk) {
    $err = "Usuario o contraseña incorrectos.";
    return false;
  }

  $_SESSION["admin_auth"] = true;
  $_SESSION["admin_user"] = $user;
  return true;
}

function admin_logout(): void {
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), "", time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
  }
  session_destroy();
}
