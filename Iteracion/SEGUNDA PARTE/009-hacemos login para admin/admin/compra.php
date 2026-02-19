<?php
declare(strict_types=1);
require __DIR__ . "/inc/auth.php";
require_admin();


function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }

function load_json_list(string $path, array $possibleKeys = ["compras","orders","items","clientes","customers"]): array {
  if (!file_exists($path)) return [[], "No se encontró <code>" . h(basename($path)) . "</code> en <code>data/</code>."];

  $raw = file_get_contents($path);
  if ($raw === false) return [[], "No se pudo leer <code>" . h(basename($path)) . "</code>."];

  $data = json_decode($raw, true);
  if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    return [[], "JSON inválido en <code>" . h(basename($path)) . "</code>."];
  }

  if (is_array($data) && array_keys($data) === range(0, count($data) - 1)) {
    return [$data, ""];
  }

  if (is_array($data)) {
    foreach ($possibleKeys as $k) {
      if (isset($data[$k]) && is_array($data[$k])) return [$data[$k], ""];
    }
  }

  return [[], "El JSON no tiene el formato esperado (array o keys típicas)."];
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

function fmt_money($v): string {
  if (is_numeric($v)) return number_format((float)$v, 2, ".", "");
  return (string)$v;
}

/* =========================
   Entrada
   ========================= */
$order = trim((string)($_GET["order"] ?? ""));

/* =========================
   Cargar compras
   ========================= */
$comprasPath = __DIR__ . "/../data/compras_ficticias.json";
[$compras, $comprasError] = load_json_list($comprasPath, ["compras","orders","items"]);

/* =========================
   Buscar compra
   ========================= */
$found = null;
if ($comprasError === "" && $order !== "") {
  foreach ($compras as $c) {
    $oid = (string)get_field($c, ["order_id","id"], "");
    if ($oid === $order) { $found = $c; break; }
  }
}

/* =========================
   Cargar clientes y match
   ========================= */
$cliente = null;
$clientesError = "";

if ($found !== null) {
  $clientesPath = __DIR__ . "/../data/clientes.json";
  [$clientes, $clientesError] = load_json_list($clientesPath, ["clientes","customers","items"]);

  if ($clientesError === "") {
    $clienteId = (string)get_field($found, ["cliente_id","cliente.id"], "");
    $clienteEmail = (string)get_field($found, ["cliente_email","cliente.email"], "");

    if ($clienteId !== "") {
      foreach ($clientes as $cl) {
        $id = (string)get_field($cl, ["id","cliente_id"], "");
        if ($id === $clienteId) { $cliente = $cl; break; }
      }
    }

    if ($cliente === null && $clienteEmail !== "") {
      foreach ($clientes as $cl) {
        $em = (string)get_field($cl, ["email","correo"], "");
        if ($em !== "" && mb_strtolower($em, "UTF-8") === mb_strtolower($clienteEmail, "UTF-8")) {
          $cliente = $cl; break;
        }
      }
    }
  }
}

$http404 = ($comprasError === "" && $order !== "" && $found === null);
if ($http404) http_response_code(404);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title>Ficha compra · Admin</title>
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
      <h2>Ficha de compra</h2>
      <span class="admin-badge">REAL (JSON)</span>
    </div>

    <?php if ($comprasError !== ""): ?>
      <section class="admin-card">
        <h3>Error</h3>
        <p class="muted"><?php echo $comprasError; ?></p>
        <div class="admin-actions">
          <a class="btn-admin-secondary" href="compras.php">← Volver</a>
        </div>
      </section>

    <?php elseif ($order === ""): ?>
      <section class="admin-card">
        <h3>Falta parámetro</h3>
        <p class="muted">Abre esta página desde el listado de compras.</p>
        <div class="admin-actions">
          <a class="btn-admin-secondary" href="compras.php">← Volver</a>
        </div>
      </section>

    <?php elseif ($found === null): ?>
      <section class="admin-card">
        <h3>No encontrada</h3>
        <p class="muted">No existe la compra: <span class="kpill"><?php echo h($order); ?></span></p>
        <div class="admin-actions">
          <a class="btn-admin-secondary" href="compras.php">← Volver</a>
        </div>
      </section>

    <?php else: ?>
      <?php
        $orderId = (string)get_field($found, ["order_id","id"], "");
        $created = (string)get_field($found, ["created_at","created","fecha","date"], "");
        $clienteId = (string)get_field($found, ["cliente_id","cliente.id"], "");
        $clienteEmail = (string)get_field($found, ["cliente_email","cliente.email"], "");

        // ✅ campos dentro de compra.*
        $prodNombre = (string)get_field($found, [
          "compra.product_name",
          "product_name",
          "producto.nombre","producto.name",
          "product.nombre","product.name"
        ], "—");

        $prodMaterial = (string)get_field($found, ["compra.material","material","producto.material","product.material"], "—");
        $prodTamano = (string)get_field($found, ["compra.tamano","tamano","producto.tamano","producto.size","product.size"], "—");
        $prodCategoria = (string)get_field($found, ["compra.categoria","categoria","producto.categoria","product.categoria"], "—");
        $precioUnit = get_field($found, ["compra.precio_unit","precio_unit","producto.precio_unit","producto.price_unit","product.price_unit"], "");
        $qty = (int)get_field($found, ["compra.qty","qty","producto.qty","product.qty"], 1);
        $color = (string)get_field($found, ["compra.color","color","producto.color","product.color"], "—");
        $total = get_field($found, ["compra.total","total","producto.total","product.total"], "");

        // ✅ fallback email: si la compra no trae email, usa el del cliente encontrado
        if ($clienteEmail === "" && $cliente !== null) {
          $clienteEmail = (string)get_field($cliente, ["email","correo"], "");
        }
      ?>

      <section class="detail-grid">
        <div class="admin-card">
          <h3>Compra</h3>
          <p class="muted">Pedido: <span class="kpill"><?php echo h($orderId); ?></span></p>

          <div class="detail-row"><div>Fecha</div><strong><?php echo h($created !== "" ? $created : "—"); ?></strong></div>
          <div class="detail-row"><div>Producto</div><strong><?php echo h($prodNombre); ?></strong></div>
          <div class="detail-row"><div>Material</div><strong><?php echo h($prodMaterial); ?></strong></div>
          <div class="detail-row"><div>Tamaño</div><strong><?php echo h($prodTamano); ?></strong></div>
          <div class="detail-row"><div>Categoría</div><strong><?php echo h($prodCategoria); ?></strong></div>
          <div class="detail-row"><div>Precio unit</div><strong><?php echo $precioUnit !== "" ? ("€ " . h(fmt_money($precioUnit))) : "—"; ?></strong></div>
          <div class="detail-row"><div>Cantidad</div><strong><?php echo h((string)$qty); ?></strong></div>
          <div class="detail-row"><div>Color</div><strong><?php echo h($color); ?></strong></div>
          <div class="detail-row"><div>Total</div><strong><?php echo $total !== "" ? ("€ " . h(fmt_money($total))) : "—"; ?></strong></div>

          <div class="admin-actions">
            <a class="btn-admin-secondary" href="compras.php">← Volver</a>
          </div>
        </div>

        <div class="admin-card">
          <h3>Cliente</h3>

          <div class="detail-row"><div>ID</div><strong><?php echo h($clienteId !== "" ? $clienteId : "—"); ?></strong></div>
          <div class="detail-row"><div>Email</div><strong><?php echo h($clienteEmail !== "" ? $clienteEmail : "—"); ?></strong></div>

          <?php if ($clientesError !== ""): ?>
            <p class="muted"><?php echo h($clientesError); ?></p>

          <?php elseif ($cliente === null): ?>
            <p class="muted">No se encontró el cliente en <code>data/clientes.json</code>.</p>

          <?php else: ?>
            <?php
              $clNombre = (string)get_field($cliente, ["nombre","name"], "—");
              $clEmail  = (string)get_field($cliente, ["email","correo"], "—");
              $clTel    = (string)get_field($cliente, ["telefono","phone"], "—");
              $clDir    = (string)get_field($cliente, ["direccion","address"], "—");
            ?>
            <div class="detail-row"><div>Nombre</div><strong><?php echo h($clNombre); ?></strong></div>
            <div class="detail-row"><div>Email</div><strong><?php echo h($clEmail); ?></strong></div>
            <div class="detail-row"><div>Teléfono</div><strong><?php echo h($clTel); ?></strong></div>
            <div class="detail-row"><div>Dirección</div><strong><?php echo h($clDir); ?></strong></div>
          <?php endif; ?>

          <div class="admin-actions">
            <a class="btn-admin-secondary" href="clientes.php">Ver clientes</a>
          </div>
        </div>
      </section>
    <?php endif; ?>

  </main>

  <footer>
    © <?php echo date("Y"); ?> Pierodev · Admin
  </footer>
</body>
</html>
