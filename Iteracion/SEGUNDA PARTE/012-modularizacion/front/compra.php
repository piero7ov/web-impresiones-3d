<?php
/**
 * PÁGINA: compra.php
 * Finalidad: Gestionar el proceso de compra de un producto.
 * Realiza el registro de clientes y pedidos en archivos JSON independientes.
 */

declare(strict_types=1);

/**
 * Función auxiliar para sanitizar cadenas de texto (XSS prevention).
 */
function h($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
}

/**
 * Carga los productos desde el catálogo JSON.
 */
function load_products(string $jsonPath): array {
  if (!file_exists($jsonPath)) return [[], "Servicio de catálogo no disponible."];

  $raw = file_get_contents($jsonPath);
  if ($raw === false) return [[], "Error de conexión con la base de datos de productos."];

  $data = json_decode($raw, true);
  if (!is_array($data) || !isset($data["products"]) || !is_array($data["products"])) {
    return [[], "Error en la carga del catálogo."];
  }
  return [$data["products"], ""];
}

/**
 * Busca un producto en el catálogo.
 */
function find_product(array $products, ?string $slug, ?string $ref, ?int $id): ?array {
  if ($slug !== null && $slug !== "") {
    foreach ($products as $p) {
      if ((string)($p["slug"] ?? "") === $slug) return $p;
    }
  }

  if ($ref !== null && $ref !== "") {
    $refBase = basename($ref);
    foreach ($products as $p) {
      $enlace = (string)($p["enlace"] ?? "");
      if ($enlace !== "" && basename($enlace) === $refBase) return $p;
    }
  }

  if ($id !== null && $id >= 1 && $id <= count($products)) {
    return $products[$id - 1];
  }

  return null;
}

/**
 * Mantiene un valor entero dentro de un rango definido.
 */
function clamp_int($v, int $min, int $max, int $default): int {
  $n = (int)$v;
  if ($n < $min || $n > $max) return $default;
  return $n;
}

function normalize_email(string $email): string {
  $email = trim($email);
  // lowercase seguro para emails
  $email = function_exists("mb_strtolower") ? mb_strtolower($email, "UTF-8") : strtolower($email);
  return $email;
}

/**
 * Lee una lista de objetos desde un archivo JSON.
 */
function read_json_list(string $path): array {
  if (!file_exists($path)) return [];
  $raw = file_get_contents($path);
  if ($raw === false) return [];
  $decoded = json_decode($raw, true);
  return is_array($decoded) ? $decoded : [];
}

/**
 * Guarda una lista de objetos en un archivo JSON con bloqueo exclusivo.
 */
function write_json_list(string $path, array $list): bool {
  $json = json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  if ($json === false) return false;
  return (@file_put_contents($path, $json . PHP_EOL, LOCK_EX) !== false);
}

function new_order_id(): string {
  return gmdate("Ymd-His") . "-" . bin2hex(random_bytes(4));
}

function new_client_id(): string {
  return "cli_" . bin2hex(random_bytes(5));
}

/**
 * Upsert cliente por email:
 * - si existe, devuelve su id y actualiza campos (updated_at)
 * - si no existe, crea uno nuevo (created_at/updated_at)
 */
/**
 * Gestiona la persistencia del cliente: crea uno nuevo o lo actualiza si ya existe por email.
 * @param array $clientes Referencia al array cargado de clientes.
 * @param array $payload Datos del formulario.
 * @return string ID del cliente (nuevo o existente).
 */
function upsert_client(array &$clientes, array $payload): string {
  $emailNorm = normalize_email((string)($payload["email"] ?? ""));
  $now = gmdate("c");

  foreach ($clientes as $i => $c) {
    $cEmail = normalize_email((string)($c["email"] ?? ""));
    if ($cEmail !== "" && $cEmail === $emailNorm) {
      // Actualización de datos opcionales preservando los existentes
      $clientes[$i]["nombre"]    = (string)($payload["nombre"] ?? $clientes[$i]["nombre"] ?? "");
      $clientes[$i]["telefono"]  = (string)($payload["telefono"] ?? $clientes[$i]["telefono"] ?? "");
      $clientes[$i]["direccion"] = (string)($payload["direccion"] ?? $clientes[$i]["direccion"] ?? "");
      $clientes[$i]["notas"]     = (string)($payload["notas"] ?? $clientes[$i]["notas"] ?? "");
      $clientes[$i]["email"]     = (string)($payload["email"] ?? $clientes[$i]["email"] ?? "");
      $clientes[$i]["updated_at"] = $now;

      return (string)($clientes[$i]["id"] ?? "");
    }
  }

  // Registro de nuevo cliente
  $id = new_client_id();
  $clientes[] = [
    "id"         => $id,
    "created_at" => $now,
    "updated_at" => $now,
    "nombre"     => (string)($payload["nombre"] ?? ""),
    "email"      => (string)($payload["email"] ?? ""),
    "telefono"   => (string)($payload["telefono"] ?? ""),
    "direccion"  => (string)($payload["direccion"] ?? ""),
    "notas"      => (string)($payload["notas"] ?? "")
  ];

  return $id;
}

/* =========================
   Cargar productos
   ========================= */
$DATA_DIR = dirname(__DIR__) . "/data";
$jsonPath = $DATA_DIR . "/productos_enriched.json";
[$products, $loadError] = load_products($jsonPath);

/* =========================
   Entrada (desde producto.php)
   ========================= */
$action = trim((string)($_POST["action"] ?? $_GET["action"] ?? "start"));

$slug = trim((string)($_POST["slug"] ?? $_GET["slug"] ?? ""));
$ref  = trim((string)($_POST["ref"]  ?? $_GET["ref"]  ?? ""));
$idRaw = ($_POST["id"] ?? $_GET["id"] ?? "");
$id = ($idRaw !== "" ? (int)$idRaw : null);

$product = null;
if ($loadError === "") {
  $product = find_product($products, $slug !== "" ? $slug : null, $ref !== "" ? $ref : null, $id);
}

/* =========================
   Opciones compra
   ========================= */
$allowedColors = ["negro", "blanco", "azul"];
$qty = clamp_int($_POST["qty"] ?? $_GET["qty"] ?? 1, 1, 5, 1);
$color = trim((string)($_POST["color"] ?? $_GET["color"] ?? "negro"));
if (!in_array($color, $allowedColors, true)) $color = "negro";

/* =========================
   Datos cliente
   ========================= */
$okMsg = "";
$errMsg = "";

$cliente_nombre = trim((string)($_POST["cliente_nombre"] ?? ""));
$cliente_email  = trim((string)($_POST["cliente_email"] ?? ""));
$cliente_tel    = trim((string)($_POST["cliente_tel"] ?? ""));
$cliente_dir    = trim((string)($_POST["cliente_dir"] ?? ""));
$cliente_notas  = trim((string)($_POST["cliente_notas"] ?? ""));

/* =========================
   Confirmar compra: guardar JSON separado
   ========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST" && $action === "confirm") {
  if ($loadError !== "" || $product === null) {
    $errMsg = "No se pudo confirmar la compra (producto inválido).";
  } elseif ($cliente_nombre === "" || $cliente_email === "" || $cliente_dir === "") {
    $errMsg = "Completa los campos obligatorios: Nombre, Email y Dirección.";
  } elseif (!filter_var($cliente_email, FILTER_VALIDATE_EMAIL)) {
    $errMsg = "El email no parece válido.";
  } else {
    $clientesPath = $DATA_DIR . "/clientes.json";
    $comprasPath  = $DATA_DIR . "/compras_ficticias.json";

    $precioStr  = (string)($product["precio"] ?? "");
    $precioUnit = is_numeric($precioStr) ? (float)$precioStr : 0.0;
    $total      = $precioUnit * $qty;

    // 1) Upsert cliente por email
    $clientes = read_json_list($clientesPath);

    $clienteId = upsert_client($clientes, [
      "nombre"    => $cliente_nombre,
      "email"     => $cliente_email,
      "telefono"  => $cliente_tel,
      "direccion" => $cliente_dir,
      "notas"     => $cliente_notas
    ]);


    if ($clienteId === "") {
      $errMsg = "No se pudo generar el cliente.";
    } else {
      $okClientes = write_json_list($clientesPath, $clientes);
      if (!$okClientes) {
        $errMsg = "Error al procesar el pedido. Por favor, inténtelo de nuevo.";
      } else {
        // 2) Guardar compra referenciando cliente_id
        $compras = read_json_list($comprasPath);

        $orderId = new_order_id();
        $compras[] = [
          "order_id"   => $orderId,
          "created_at" => gmdate("c"),
          "cliente_id" => $clienteId,
          "compra" => [
            "product_slug" => (string)($product["slug"] ?? ""),
            "product_name" => (string)($product["nombre"] ?? ""),
            "material"     => (string)($product["material"] ?? ""),
            "tamano"       => (string)($product["tamano"] ?? ""),
            "categoria"    => (string)($product["categoria"] ?? ""),
            "precio_unit"  => number_format($precioUnit, 2, ".", ""),
            "qty"          => $qty,
            "color"        => $color,
            "total"        => number_format($total, 2, ".", "")
          ]
        ];

        $okCompras = write_json_list($comprasPath, $compras);
        if (!$okCompras) {
          $errMsg = "Hubo un problema al finalizar su compra. Contacte con nosotros si el problema persiste.";
        } else {
          // PRG: redirect para evitar reenvío
          $qs = [
            "order" => $orderId
          ];
          header("Location: finalizacion.php?" . http_build_query($qs));
          exit;
        }
      }
    }
  }
}


/* Calcular resumen si hay producto */
$precioUnit = 0.0;
$total = 0.0;
if ($product !== null) {
  $precioStr = (string)($product["precio"] ?? "");
  $precioUnit = is_numeric($precioStr) ? (float)$precioStr : 0.0;
  $total = $precioUnit * $qty;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title>Compra · Pierodev</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/styles.css">
  <link rel="stylesheet" href="assets/compra.css">
</head>

<body>
  <?php include 'inc/header.php'; ?>

  <main class="checkout">
    <?php if ($loadError !== ""): ?>
      <article class="box">
        <strong>Error</strong>
        <em>Servicio fuera de línea temporalmente.</em>
      </article>

    <?php elseif ($product === null): ?>
      <?php http_response_code(404); ?>
      <article class="box">
        <strong>Producto no encontrado</strong>
        <p class="hint">El producto no existe o el enlace está incompleto.</p>
        <a class="btn-secondary" href="index.php">← Volver</a>
      </article>

    <?php else: ?>
      <div class="checkout-grid">

        <!-- FORM CLIENTE -->
        <article class="box">
          <h3 class="section-title">Datos del cliente</h3>
          <p class="hint">Por favor, introduzca sus datos para completar el envío.</p>


          <?php if ($errMsg !== ""): ?>
            <div class="msg-err"><?php echo $errMsg; ?></div>
          <?php endif; ?>

          <form method="post" action="compra.php" novalidate>
            <input type="hidden" name="action" value="confirm">

            <?php
              $pslug = (string)($product["slug"] ?? "");
              $pref  = (string)($product["enlace"] ?? "");
            ?>
            <?php if ($pslug !== ""): ?>
              <input type="hidden" name="slug" value="<?php echo h($pslug); ?>">
            <?php endif; ?>
            <?php if ($pslug === "" && $pref !== ""): ?>
              <input type="hidden" name="ref" value="<?php echo h($pref); ?>">
            <?php endif; ?>
            <?php if ($pslug === "" && $pref === "" && $id !== null): ?>
              <input type="hidden" name="id" value="<?php echo h((string)$id); ?>">
            <?php endif; ?>

            <input type="hidden" name="qty" value="<?php echo h((string)$qty); ?>">
            <input type="hidden" name="color" value="<?php echo h($color); ?>">

            <div class="field">
              <label for="cliente_nombre">Nombre *</label>
              <input id="cliente_nombre" name="cliente_nombre" type="text" value="<?php echo h($cliente_nombre); ?>" required>
            </div>

            <div class="field">
              <label for="cliente_email">Email *</label>
              <input id="cliente_email" name="cliente_email" type="email" value="<?php echo h($cliente_email); ?>" required>
            </div>

            <div class="field">
              <label for="cliente_tel">Teléfono</label>
              <input id="cliente_tel" name="cliente_tel" type="text" value="<?php echo h($cliente_tel); ?>">
            </div>

            <div class="field">
              <label for="cliente_dir">Dirección *</label>
              <input id="cliente_dir" name="cliente_dir" type="text" value="<?php echo h($cliente_dir); ?>" required>
            </div>

            <div class="field">
              <label for="cliente_notas">Notas (opcional)</label>
              <textarea id="cliente_notas" name="cliente_notas"><?php echo h($cliente_notas); ?></textarea>
            </div>

            <div class="actions">
              <button class="btn-primary" type="submit">Confirmar compra</button>

              <?php
                // Volver al producto
                $backHref = "producto.php";
                if ($pslug !== "") $backHref .= "?slug=" . urlencode($pslug);
                elseif ($ref !== "") $backHref .= "?ref=" . urlencode($ref);
                elseif ($id !== null) $backHref .= "?id=" . urlencode((string)$id);
              ?>
              <a class="btn-secondary" href="<?php echo h($backHref); ?>">← Volver al producto</a>
            </div>
          </form>
        </article>

        <!-- RESUMEN -->
        <article class="box">
          <h3 class="section-title">Resumen</h3>

          <div class="summary-row">
            <div>Producto</div>
            <strong><?php echo h((string)($product["nombre"] ?? "")); ?></strong>
          </div>

          <div class="summary-row">
            <div>Cantidad</div>
            <strong><?php echo h((string)$qty); ?></strong>
          </div>

          <div class="summary-row">
            <div>Color</div>
            <strong><?php echo h($color); ?></strong>
          </div>

          <div class="summary-row">
            <div>Precio unit</div>
            <strong>€ <?php echo h(number_format($precioUnit, 2, ".", "")); ?></strong>
          </div>

          <div class="summary-row">
            <div>Total</div>
            <strong>€ <?php echo h(number_format($total, 2, ".", "")); ?></strong>
          </div>

          <div style="margin-top:10px;">
            <?php if ((string)($product["material"] ?? "") !== ""): ?>
              <span class="badge"><?php echo h((string)$product["material"]); ?></span>
            <?php endif; ?>
            <?php if ((string)($product["tamano"] ?? "") !== ""): ?>
              <span class="badge"><?php echo h((string)$product["tamano"]); ?></span>
            <?php endif; ?>
            <?php if ((string)($product["categoria"] ?? "") !== ""): ?>
              <span class="badge"><?php echo h((string)$product["categoria"]); ?></span>
            <?php endif; ?>
          </div>
        </article>

      </div>
    <?php endif; ?>
  </main>

  <?php include 'inc/footer.php'; ?>
</body>
</html>
