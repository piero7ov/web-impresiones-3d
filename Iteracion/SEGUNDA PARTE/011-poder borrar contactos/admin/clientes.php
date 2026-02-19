<?php
declare(strict_types=1);
require __DIR__ . "/inc/auth.php";
require_admin();


function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }

function load_json_list(string $path, array $possibleKeys = ["clientes","customers","items"]): array {
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

$clientesPath = __DIR__ . "/../data/clientes.json";
[$clientes, $loadError] = load_json_list($clientesPath);

$q = trim((string)($_GET["q"] ?? ""));
$idSel = trim((string)($_GET["id"] ?? ""));

if ($loadError === "" && $q !== "") {
  $qLower = mb_strtolower($q, "UTF-8");
  $clientes = array_values(array_filter($clientes, function($c) use ($qLower){
    $id = (string)get_field($c, ["id","cliente_id"], "");
    $nombre = (string)get_field($c, ["nombre","name"], "");
    $email = (string)get_field($c, ["email","correo"], "");
    $hay = mb_strtolower($id." ".$nombre." ".$email, "UTF-8");
    return $hay !== "" && mb_strpos($hay, $qLower) !== false;
  }));
}

$clienteSel = null;
if ($loadError === "" && $idSel !== "") {
  foreach ($clientes as $c) {
    $id = (string)get_field($c, ["id","cliente_id"], "");
    if ($id !== "" && $id === $idSel) { $clienteSel = $c; break; }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title>Clientes · Admin</title>
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
        <li><a href="logout.php">Salir</a></li>
      </ul>
    </nav>
    <div class="search">
      <input type="text" placeholder="Admin" disabled>
    </div>
  </header>

  <main class="admin">
    <div class="admin-top">
      <h2>Clientes</h2>
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
            <h3>Listado</h3>
            <p><?php echo count($clientes); ?> cliente(s)</p>
          </div>

          <form class="filters" method="get" action="clientes.php">
            <input type="text" name="q" placeholder="Buscar por id / nombre / email" value="<?php echo h($q); ?>">
            <button class="btn-admin" type="submit">Buscar</button>
            <a class="btn-admin-secondary" href="clientes.php">Limpiar</a>
          </form>
        </div>

        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($clientes)): ?>
              <tr><td colspan="5">No hay clientes todavía.</td></tr>
            <?php else: ?>
              <?php foreach ($clientes as $c): ?>
                <?php
                  $id = (string)get_field($c, ["id","cliente_id"], "—");
                  $nombre = (string)get_field($c, ["nombre","name"], "—");
                  $email = (string)get_field($c, ["email","correo"], "—");
                  $tel = (string)get_field($c, ["telefono","phone"], "—");
                ?>
                <tr>
                  <td><span class="kpill"><?php echo h($id); ?></span></td>
                  <td><?php echo h($nombre); ?></td>
                  <td><?php echo h($email); ?></td>
                  <td><?php echo h($tel); ?></td>
                  <td>
                    <?php if ($id !== "—"): ?>
                      <a class="btn-admin-secondary" href="clientes.php?id=<?php echo urlencode($id); ?>">Ver ficha</a>
                    <?php else: ?>
                      —
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </section>

      <?php if ($clienteSel !== null): ?>
        <?php
          $id = (string)get_field($clienteSel, ["id","cliente_id"], "—");
          $nombre = (string)get_field($clienteSel, ["nombre","name"], "—");
          $email = (string)get_field($clienteSel, ["email","correo"], "—");
          $tel = (string)get_field($clienteSel, ["telefono","phone"], "—");
          $dir = (string)get_field($clienteSel, ["direccion","address"], "—");
        ?>
        <section class="admin-card">
          <h3>Ficha de cliente</h3>

          <div class="detail-row"><div>ID</div><strong><?php echo h($id); ?></strong></div>
          <div class="detail-row"><div>Nombre</div><strong><?php echo h($nombre); ?></strong></div>
          <div class="detail-row"><div>Email</div><strong><?php echo h($email); ?></strong></div>
          <div class="detail-row"><div>Teléfono</div><strong><?php echo h($tel); ?></strong></div>
          <div class="detail-row"><div>Dirección</div><strong><?php echo h($dir); ?></strong></div>

          <div class="admin-actions">
            <a class="btn-admin-secondary" href="clientes.php">Cerrar ficha</a>
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
