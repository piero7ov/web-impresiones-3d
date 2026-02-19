<?php
declare(strict_types=1);

require_once __DIR__ . "/inc/auth.php";
require_admin();

if (!function_exists("h")) {
  function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }
}

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

function set_field(array &$row, string $path, $value): void {
  $parts = explode(".", $path);
  $cur = &$row;
  foreach ($parts as $i => $part) {
    $isLast = ($i === count($parts) - 1);
    if (!is_array($cur)) $cur = [];

    if ($isLast) {
      $cur[$part] = $value;
      return;
    }

    if (!isset($cur[$part]) || !is_array($cur[$part])) {
      $cur[$part] = [];
    }
    $cur = &$cur[$part];
  }
}

function fmt_money($v): string {
  if (is_numeric($v)) return number_format((float)$v, 2, ".", "");
  return (string)$v;
}

function status_class(string $estado): string {
  $e = mb_strtolower(trim($estado), "UTF-8");
  if ($e === "pendiente") return "status-pendiente";
  if ($e === "en proceso") return "status-proceso";
  if ($e === "completado") return "status-completado";
  if ($e === "cancelado") return "status-cancelado";
  return "status-pendiente";
}

/* =========================
   CSRF simple
   ========================= */
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
if (empty($_SESSION["csrf_admin"])) {
  $_SESSION["csrf_admin"] = bin2hex(random_bytes(16));
}
$csrf = (string)$_SESSION["csrf_admin"];

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
   POST: cambiar estado
   ========================= */
$saveOkMsg = "";
$saveErrMsg = "";

if ($comprasError === "" && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["set_estado"])) {
  $token = (string)($_POST["csrf"] ?? "");
  $orderPost = trim((string)($_POST["order"] ?? ""));
  $estadoNew = trim((string)($_POST["estado"] ?? ""));

  $allowed = ["pendiente","en proceso","completado","cancelado"];

  if (!hash_equals($csrf, $token)) {
    $saveErrMsg = "Token inválido. Recarga la página.";
  } elseif ($orderPost === "") {
    $saveErrMsg = "Falta el pedido.";
  } elseif (!in_array(mb_strtolower($estadoNew, "UTF-8"), $allowed, true)) {
    $saveErrMsg = "Estado no válido.";
  } else {
    // Buscar y actualizar en $compras
    $updated = false;

    foreach ($compras as $i => $c) {
      $oid = (string)get_field($c, ["order_id","id"], "");
      if ($oid === $orderPost) {
        // Preferimos guardar dentro de compra.estado (tu estructura)
        set_field($compras[$i], "compra.estado", $estadoNew);
        $updated = true;
        break;
      }
    }

    if (!$updated) {
      $saveErrMsg = "No se encontró la compra para actualizar.";
    } else {
      $json = json_encode($compras, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
      if ($json === false) {
        $saveErrMsg = "No se pudo generar el JSON.";
      } else {
        $ok = @file_put_contents($comprasPath, $json . PHP_EOL, LOCK_EX);
        if ($ok === false) {
          $saveErrMsg = "No se pudo guardar. Revisa permisos en <code>data/</code>.";
        } else {
          header("Location: compra.php?order=" . urlencode($orderPost) . "&saved=1");
          exit;
        }
      }
    }
  }
}

$saved = (int)($_GET["saved"] ?? 0);

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
    $clienteEmail = (string)get_field($found, ["cliente_email","cliente.email","email"], "");

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

    <?php if ($saved === 1): ?>
      <section class="admin-card">
        <h3>Guardado</h3>
        <p class="muted">Estado actualizado correctamente.</p>
      </section>
    <?php endif; ?>

    <?php if ($saveErrMsg !== ""): ?>
      <section class="admin-card">
        <h3>Error</h3>
        <p class="muted"><?php echo $saveErrMsg; ?></p>
      </section>
    <?php endif; ?>

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
        $clienteEmail = (string)get_field($found, ["cliente_email","cliente.email","email"], "");

        // Tu JSON usa compra.*
        $prodNombre = (string)get_field($found, ["compra.product_name","product_name"], "—");
        $prodMaterial = (string)get_field($found, ["compra.material","material"], "—");
        $prodTamano = (string)get_field($found, ["compra.tamano","tamano","compra.size"], "—");
        $prodCategoria = (string)get_field($found, ["compra.categoria","categoria"], "—");
        $precioUnit = get_field($found, ["compra.precio_unit","precio_unit"], "");
        $qty = (int)get_field($found, ["compra.qty","qty"], 1);
        $color = (string)get_field($found, ["compra.color","color"], "—");
        $total = get_field($found, ["compra.total","total"], "");

        $estadoActual = (string)get_field($found, ["compra.estado","compra.status","estado","status"], "pendiente");
        $stClass = status_class($estadoActual);
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

          <?php $stClass = status_class((string)$estadoActual); ?>
          <p class="muted" style="margin-top:10px;">
            Actual: <span class="status-pill <?php echo h($stClass); ?>"><?php echo h($estadoActual); ?></span>
          </p>

          <form method="post" action="compra.php?order=<?php echo urlencode($orderId); ?>" style="margin-top:10px;">
            <input type="hidden" name="csrf" value="<?php echo h($csrf); ?>">
            <input type="hidden" name="order" value="<?php echo h($orderId); ?>">
            <input type="hidden" name="set_estado" value="1">

            <div class="field" style="max-width:360px;">
              <label for="estado">Cambiar estado</label>
              <select id="estado" name="estado">
                <?php
                  $opts = ["pendiente","en proceso","completado","cancelado"];
                  foreach ($opts as $op):
                    $sel = (mb_strtolower($estadoActual,"UTF-8") === $op) ? "selected" : "";
                ?>
                  <option value="<?php echo h($op); ?>" <?php echo $sel; ?>><?php echo h($op); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="admin-actions" style="margin-top:10px;">
              <button class="btn-admin" type="submit">Guardar estado</button>
              <a class="btn-admin-secondary" href="compras.php">← Volver</a>
            </div>
          </form>
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
