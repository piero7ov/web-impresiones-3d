<?php
declare(strict_types=1);
require __DIR__ . "/inc/auth.php";
require_admin();


function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }

function load_json_list(string $path, array $possibleKeys = ["compras","orders","items"]): array {
  if (!file_exists($path)) return [[], "No se encontró <code>" . h(basename($path)) . "</code> en <code>data/</code>."];

  $raw = file_get_contents($path);
  if ($raw === false) return [[], "No se pudo leer <code>" . h(basename($path)) . "</code>."];

  $data = json_decode($raw, true);
  if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    return [[], "JSON inválido en <code>" . h(basename($path)) . "</code>."];
  }

  // Caso A: el JSON es un array directo []
  if (is_array($data) && array_keys($data) === range(0, count($data) - 1)) {
    return [$data, ""];
  }

  // Caso B: objeto con keys típicas { compras:[], orders:[], items:[] }
  if (is_array($data)) {
    foreach ($possibleKeys as $k) {
      if (isset($data[$k]) && is_array($data[$k])) return [$data[$k], ""];
    }
  }

  return [[], "El JSON no tiene el formato esperado (array o keys: compras/orders/items)."];
}

function get_field(array $row, array $paths, $default = "") {
  foreach ($paths as $p) {
    $parts = explode(".", $p);
    $cur = $row;
    $ok = true;
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

function fmt_money($v): string {
  if (is_numeric($v)) return number_format((float)$v, 2, ".", "");
  return (string)$v;
}

/* =========================
   Cargar compras reales
   ========================= */
$comprasPath = __DIR__ . "/../data/compras_ficticias.json";
[$compras, $loadError] = load_json_list($comprasPath);

/* =========================
   Filtro
   ========================= */
$q = trim((string)($_GET["q"] ?? ""));
$qLower = mb_strtolower($q, "UTF-8");

if ($loadError === "" && $q !== "") {
  $compras = array_values(array_filter($compras, function($c) use ($qLower){
    $orderId = (string)get_field($c, ["order_id","id"], "");
    $clienteId = (string)get_field($c, ["cliente_id","cliente.id"], "");
    $clienteEmail = (string)get_field($c, ["cliente_email","cliente.email"], "");

    // ✅ ahora soporta compra.product_name
    $productName = (string)get_field($c, [
      "compra.product_name",
      "product_name",
      "producto.nombre","producto.name",
      "product.nombre","product.name"
    ], "");

    $haystack = mb_strtolower($orderId . " " . $clienteId . " " . $clienteEmail . " " . $productName, "UTF-8");
    return $haystack !== "" && mb_strpos($haystack, $qLower) !== false;
  }));
}

/* =========================
   Orden por fecha desc
   ========================= */
if ($loadError === "") {
  usort($compras, function($a, $b){
    $da = (string)get_field($a, ["created_at","created","fecha","date"], "");
    $db = (string)get_field($b, ["created_at","created","fecha","date"], "");
    return to_ts($db) <=> to_ts($da);
  });
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title>Compras · Admin</title>
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
      <h2>Compras</h2>
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
            <p><?php echo count($compras); ?> compra(s)</p>
          </div>

          <form class="filters" method="get" action="compras.php">
            <input type="text" name="q" placeholder="Buscar por pedido / cliente / producto" value="<?php echo h($q); ?>">
            <button class="btn-admin" type="submit">Buscar</button>
            <a class="btn-admin-secondary" href="compras.php">Limpiar</a>
          </form>
        </div>

        <table class="table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Pedido</th>
              <th>Producto</th>
              <th>Cliente</th>
              <th>Qty</th>
              <th>Color</th>
              <th>Total</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($compras)): ?>
              <tr>
                <td colspan="8">No hay compras registradas todavía.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($compras as $c): ?>
                <?php
                  $orderId = (string)get_field($c, ["order_id","id"], "");
                  $created = (string)get_field($c, ["created_at","created","fecha","date"], "");
                  $clienteId = (string)get_field($c, ["cliente_id","cliente.id"], "");
                  $clienteEmail = (string)get_field($c, ["cliente_email","cliente.email"], "");
                  $clienteShow = $clienteId !== "" ? $clienteId : ($clienteEmail !== "" ? $clienteEmail : "—");

                  // ✅ compra.*
                  $productName = (string)get_field($c, [
                    "compra.product_name",
                    "product_name",
                    "producto.nombre","producto.name",
                    "product.nombre","product.name"
                  ], "—");

                  $qty = (int)get_field($c, ["compra.qty","qty","producto.qty","product.qty"], 1);
                  $color = (string)get_field($c, ["compra.color","color","producto.color","product.color"], "—");
                  $total = get_field($c, ["compra.total","total","producto.total","product.total"], "");
                ?>
                <tr>
                  <td><?php echo h($created !== "" ? $created : "—"); ?></td>
                  <td><span class="kpill"><?php echo h($orderId !== "" ? $orderId : "—"); ?></span></td>
                  <td><?php echo h($productName); ?></td>
                  <td><?php echo h($clienteShow); ?></td>
                  <td><?php echo h((string)$qty); ?></td>
                  <td><?php echo h($color); ?></td>
                  <td><strong><?php echo $total !== "" ? ("€ " . h(fmt_money($total))) : "—"; ?></strong></td>
                  <td>
                    <?php if ($orderId !== ""): ?>
                      <a class="btn-admin-secondary" href="compra.php?order=<?php echo urlencode($orderId); ?>">Ver ficha</a>
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

    <?php endif; ?>
  </main>

  <footer>
    © <?php echo date("Y"); ?> Pierodev · Admin
  </footer>
</body>
</html>
