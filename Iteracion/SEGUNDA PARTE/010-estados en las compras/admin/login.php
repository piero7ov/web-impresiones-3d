<?php
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
  <link rel="stylesheet" href="../assets/styles.css">
  <link rel="stylesheet" href="assets/admin.css">

  <style>
    main.admin-login{
      max-width: 520px;
      margin: 0 auto;
      padding: 26px 20px;
      box-sizing: border-box;
      display: block;
    }
    .login-card{
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      background: #ffffff;
      box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
      padding: 16px;
    }
    .login-card h2{
      margin: 0 0 10px 0;
      color: #1e3a8a;
      font-size: 1.4rem;
    }
    .field{
      margin-bottom: 12px;
    }
    .field label{
      display: block;
      font-size: 0.9rem;
      font-weight: 900;
      color: #111827;
      margin-bottom: 6px;
    }
    .field input{
      width: 100%;
      box-sizing: border-box;
      padding: 11px 12px;
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      outline: none;
      font-family: inherit;
    }
    .field input:focus{
      border-color: #0ea5e9;
      box-shadow: 0 0 0 3px rgba(14,165,233,0.20);
    }
    .msg-err{
      padding: 12px 14px;
      border-radius: 12px;
      background: rgba(239,68,68,0.10);
      border: 1px solid rgba(239,68,68,0.25);
      color: #7f1d1d;
      margin-bottom: 12px;
      font-weight: 800;
    }
    .actions{
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      margin-top: 8px;
    }
  </style>
</head>

<body>
  <header>
    <h1>Pierodev | Impresiones 3D</h1>
    <nav>
      <ul>
        <li><a href="../index.php">Web</a></li>
        <li><a href="login.php">Login</a></li>
      </ul>
    </nav>
    <div class="search">
      <input type="text" placeholder="Admin" disabled>
    </div>
  </header>

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

        <p class="muted" style="margin-top:12px;">
          Demo local: cambia credenciales en <code>admin/inc/auth_config.php</code>.
        </p>
      </form>
    </section>
  </main>

  <footer>
    © <?php echo date("Y"); ?> Pierodev · Admin
  </footer>
</body>
</html>
