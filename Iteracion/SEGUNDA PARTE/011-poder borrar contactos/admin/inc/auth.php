<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

/* ✅ Evitar "Cannot redeclare h()" si el archivo que incluye ya la tiene */
if (!function_exists("h")) {
  function h($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
  }
}

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

function require_admin(): void {
  if (is_admin_logged()) return;

  $next = (string)($_SERVER["REQUEST_URI"] ?? "index.php");
  $nextEnc = urlencode($next);
  header("Location: login.php?next=" . $nextEnc);
  exit;
}

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
