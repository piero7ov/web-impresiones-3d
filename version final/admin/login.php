<?php
/**
 * PÁGINA DE LOGIN: login.php
 * Finalidad: Formulario de acceso al panel de administración.
 * Gestiona la autenticación del usuario y la redirección post-login.
 */
declare(strict_types=1);

require __DIR__ . "/inc/auth.php";

if (is_admin_logged()) {
  header("Location: index.php");
  exit;
}

$err = "";
$user = "";
$next = trim((string)($_GET["next"] ?? "index.php"));
if ($next === "") $next = "index.php";

/* Seguridad mínima: evitar redirects raros */
if (preg_match('#^(https?:)?//#i', $next)) $next = "index.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $user = trim((string)($_POST["user"] ?? ""));
  $pass = (string)($_POST["pass"] ?? "");
  $nextPost = trim((string)($_POST["next"] ?? "index.php"));
  if ($nextPost !== "") $next = $nextPost;

  if ($user === "" || $pass === "") {
    $err = "Completa usuario y contraseña.";
  } else {
    if (try_login($user, $pass, $err)) {
      header("Location: " . $next);
      exit;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title>Login · Admin</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../front/assets/styles.css">
  <link rel="stylesheet" href="assets/admin.css">
  <link rel="stylesheet" href="assets/login.css">
</head>

<body>
  <?php include 'inc/header.php'; ?>

  <main class="admin-login">
    <section class="login-card">
      <h2>Acceso Admin</h2>

      <?php if ($err !== ""): ?>
        <div class="msg-err"><?php echo h($err); ?></div>
      <?php endif; ?>

      <form method="post" action="login.php<?php echo $next !== "" ? ("?next=" . urlencode($next)) : ""; ?>">
        <input type="hidden" name="next" value="<?php echo h($next); ?>">

        <div class="field">
          <label for="user">Usuario</label>
          <input id="user" name="user" type="text" value="<?php echo h($user); ?>" required>
        </div>

        <div class="field">
          <label for="pass">Contraseña</label>
          <input id="pass" name="pass" type="password" required>
        </div>

        <div class="actions">
          <button class="btn-admin" type="submit">Entrar</button>
          <a class="btn-admin-secondary" href="../index.php">← Volver a la web</a>
        </div>
      </form>
    </section>
  </main>

  <?php include 'inc/footer.php'; ?>
</body>
</html>
