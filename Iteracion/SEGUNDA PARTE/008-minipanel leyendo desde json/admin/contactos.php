<?php
declare(strict_types=1);

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }

function load_json_list(string $path, array $possibleKeys = ["contactos","messages","items"]): array {
  if (!file_exists($path)) return [[], "No se encontró <code>" . h(basename($path)) . "</code> en <code>data/</code>."];
  $raw = file_get_contents($path);
  if ($raw === false) return [[], "No se pudo leer <code>" . h(basename($path)) . "</code>."];
  $data = json_decode($raw, true);
  if ($data === null && json_last_error() !== JSON_ERROR_NONE) return [[], "JSON inválido en <code>" . h(basename($path)) . "</code>."];

  if (is_array($data) && array_keys($data) === range(0, count($data) - 1)) return [$data, ""];
  if (is_array($data)) {
    foreach ($possibleKeys as $k) if (isset($data[$k]) && is_array($data[$k])) return [$data[$k], ""];
  }
  return [[], "El JSON no tiene el formato esperado (array o keys típicas)."];
}

function get_field(array $row, array $paths, $default = "") {
  foreach ($paths as $p) {
    $parts = explode(".", $p);
    $cur = $row; $ok = true;
    foreach ($parts as $part) {
      if (!is_array($cur) || !array_key_exists($part, $cur)) { $ok = false; break; }
      $cur = $cur[$part];
    }
    if ($ok) return $cur;
  }
  return $default;
}

function to_ts(string $iso): int {
  $t = strtotime($iso);
  return $t !== false ? $t : 0;
}

$path = __DIR__ . "/../data/contactos_recibidos.json";
[$msgs, $loadError] = load_json_list($path);

$q = trim((string)($_GET["q"] ?? ""));
$iSel = isset($_GET["i"]) ? (int)$_GET["i"] : -1;

if ($loadError === "" && !empty($msgs)) {
  usort($msgs, function($a, $b){
    $da = (string)get_field($a, ["created_at","created","fecha","date"], "");
    $db = (string)get_field($b, ["created_at","created","fecha","date"], "");
    return to_ts($db) <=> to_ts($da);
  });
}

if ($loadError === "" && $q !== "") {
  $qLower = mb_strtolower($q, "UTF-8");
  $msgs = array_values(array_filter($msgs, function($m) use ($qLower){
    $nombre = (string)get_field($m, ["nombre","name"], "");
    $email = (string)get_field($m, ["email","correo"], "");
    $asunto = (string)get_field($m, ["asunto","subject"], "");
    $mensaje = (string)get_field($m, ["mensaje","message"], "");
    $hay = mb_strtolower($nombre." ".$email." ".$asunto." ".$mensaje, "UTF-8");
    return $hay !== "" && mb_strpos($hay, $qLower) !== false;
  }));
}

$msgSel = null;
if ($loadError === "" && $iSel >= 0 && $iSel < count($msgs)) {
  $msgSel = $msgs[$iSel];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title>Contactos · Admin</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/styles.css">
  <link rel="stylesheet" href="assets/admin.css">
</head>

<body>
  <header>
    <h1>Pierodev | Impresiones 3D</h1>
    <nav>
      <ul>
        <li><a href="../index.php">Web</a></li>
        <li><a href="index.php">Admin</a></li>
        <li><a href="compras.php">Compras</a></li>
        <li><a href="clientes.php">Clientes</a></li>
        <li><a href="contactos.php">Contactos</a></li>
      </ul>
    </nav>
    <div class="search">
      <input type="text" placeholder="Admin" disabled>
    </div>
  </header>

  <main class="admin">
    <div class="admin-top">
      <h2>Contactos</h2>
      <span class="admin-badge">REAL (JSON)</span>
    </div>

    <?php if ($loadError !== ""): ?>
      <section class="admin-card">
        <h3>Error</h3>
        <p class="muted"><?php echo $loadError; ?></p>
        <div class="admin-actions">
          <a class="btn-admin-secondary" href="index.php">← Volver</a>
        </div>
      </section>
    <?php else: ?>

      <section class="admin-table-wrap">
        <div class="admin-table-head">
          <div>
            <h3>Mensajes</h3>
            <p><?php echo count($msgs); ?> mensaje(s)</p>
          </div>

          <form class="filters" method="get" action="contactos.php">
            <input type="text" name="q" placeholder="Buscar por nombre / email / asunto" value="<?php echo h($q); ?>">
            <button class="btn-admin" type="submit">Buscar</button>
            <a class="btn-admin-secondary" href="contactos.php">Limpiar</a>
          </form>
        </div>

        <table class="table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Asunto</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($msgs)): ?>
              <tr><td colspan="5">No hay mensajes todavía.</td></tr>
            <?php else: ?>
              <?php foreach ($msgs as $idx => $m): ?>
                <?php
                  $date = (string)get_field($m, ["created_at","created","fecha","date"], "—");
                  $nombre = (string)get_field($m, ["nombre","name"], "—");
                  $email = (string)get_field($m, ["email","correo"], "—");
                  $asunto = (string)get_field($m, ["asunto","subject"], "—");
                ?>
                <tr>
                  <td><?php echo h($date); ?></td>
                  <td><?php echo h($nombre); ?></td>
                  <td><?php echo h($email); ?></td>
                  <td><?php echo h($asunto); ?></td>
                  <td>
                    <a class="btn-admin-secondary" href="contactos.php?i=<?php echo $idx; ?>">Ver</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </section>

      <?php if ($msgSel !== null): ?>
        <?php
          $date = (string)get_field($msgSel, ["created_at","created","fecha","date"], "—");
          $nombre = (string)get_field($msgSel, ["nombre","name"], "—");
          $email = (string)get_field($msgSel, ["email","correo"], "—");
          $asunto = (string)get_field($msgSel, ["asunto","subject"], "—");
          $mensaje = (string)get_field($msgSel, ["mensaje","message"], "—");
        ?>
        <section class="admin-card">
          <h3>Mensaje</h3>

          <div class="detail-row"><div>Fecha</div><strong><?php echo h($date); ?></strong></div>
          <div class="detail-row"><div>Nombre</div><strong><?php echo h($nombre); ?></strong></div>
          <div class="detail-row"><div>Email</div><strong><?php echo h($email); ?></strong></div>
          <div class="detail-row"><div>Asunto</div><strong><?php echo h($asunto); ?></strong></div>
          <div style="margin-top:10px;">
            <p class="muted" style="margin-bottom:6px;"><strong>Mensaje</strong></p>
            <div style="white-space:pre-wrap; line-height:1.6; color:#111827;"><?php echo h($mensaje); ?></div>
          </div>

          <div class="admin-actions">
            <a class="btn-admin-secondary" href="contactos.php">Cerrar</a>
          </div>
        </section>
      <?php endif; ?>

    <?php endif; ?>
  </main>

  <footer>
    © <?php echo date("Y"); ?> Pierodev · Admin
  </footer>
</body>
</html>
