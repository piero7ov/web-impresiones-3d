<?php
/**
 * DASHBOARD ADMIN: index.php
 * Finalidad: Panel principal de administración que muestra estadísticas rápidas.
 * Verifica la sesión del admin antes de permitir el acceso.
 */

declare(strict_types=1);
require __DIR__ . "/inc/auth.php";
require_admin(); // Protección de ruta


function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }

/**
 * Carga un archivo JSON y lo convierte en un array, buscando claves típicas si el JSON es un objeto.
 */
function load_json_list(string $path, array $possibleKeys = ["items","compras","orders","clientes","customers"]): array {
  if (!file_exists($path)) return [[], "Lo sentimos, no se encontró el conjunto de datos deseado."];

  $raw = file_get_contents($path);
  if ($raw === false) return [[], "Error al leer los datos."];

  $data = json_decode($raw, true);
  if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    return [[], "Error de formato en los datos recibidos."];
  }

  // Si es un array simple (lista plana), lo devolvemos directamente
  if (is_array($data) && array_keys($data) === range(0, count($data) - 1)) return [$data, ""];

  // Si es un objeto, buscamos dentro de las claves conocidas
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

/* ====== Cargar datasets ====== */
$comprasPath   = __DIR__ . "/../data/compras_ficticias.json";
$clientesPath  = __DIR__ . "/../data/clientes.json";
$contactosPath = __DIR__ . "/../data/contactos_recibidos.json";

[$compras, $errCompras]     = load_json_list($comprasPath, ["compras","orders","items"]);
[$clientes, $errClientes]   = load_json_list($clientesPath, ["clientes","customers","items"]);
[$contactos, $errContactos] = load_json_list($contactosPath, ["contactos","messages","items"]);

$comprasCount   = ($errCompras === "") ? count($compras) : 0;
$clientesCount  = ($errClientes === "") ? count($clientes) : 0;
$contactosCount = ($errContactos === "") ? count($contactos) : 0;

/* ====== última compra ====== */
$lastOrderId = "—";
$lastOrderAt = "—";
if ($errCompras === "" && !empty($compras)) {
  usort($compras, function($a, $b){
    $da = (string)get_field($a, ["created_at","created","fecha","date"], "");
    $db = (string)get_field($b, ["created_at","created","fecha","date"], "");
    return to_ts($db) <=> to_ts($da);
  });
  $last = $compras[0];
  $lastOrderId = (string)get_field($last, ["order_id","id"], "—");
  $lastOrderAt = (string)get_field($last, ["created_at","created","fecha","date"], "—");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title>Admin · Pierodev</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../front/assets/styles.css">
  <link rel="stylesheet" href="assets/admin.css">
</head>

<body>
  <?php include 'inc/header.php'; ?>

  <main class="admin">
    <div class="admin-top">
      <h2>Panel de control</h2>
    </div>

    <section class="dash-grid">
      <div class="dash-box">
        <p class="dash-label">Compras</p>
        <p class="dash-value"><?php echo h((string)$comprasCount); ?></p>
      </div>
      <div class="dash-box">
        <p class="dash-label">Clientes</p>
        <p class="dash-value"><?php echo h((string)$clientesCount); ?></p>
      </div>
      <div class="dash-box">
        <p class="dash-label">Contactos</p>
        <p class="dash-value"><?php echo h((string)$contactosCount); ?></p>
      </div>
    </section>

    <section class="admin-card">
      <h3>Estado</h3>
      <p class="muted">
        Última compra: <span class="kpill"><?php echo h($lastOrderId); ?></span>
        · <?php echo h($lastOrderAt); ?>
      </p>

      <div class="admin-actions">
        <a class="btn-admin" href="compras.php">Ver compras</a>
        <a class="btn-admin-secondary" href="clientes.php">Ver clientes</a>
        <a class="btn-admin-secondary" href="contactos.php">Ver contactos</a>
      </div>

      <?php if ($errCompras !== "" || $errClientes !== "" || $errContactos !== ""): ?>
        <div style="margin-top:12px;">
          <?php if ($errCompras !== ""): ?><p class="muted">Compras: <?php echo $errCompras; ?></p><?php endif; ?>
          <?php if ($errClientes !== ""): ?><p class="muted">Clientes: <?php echo $errClientes; ?></p><?php endif; ?>
          <?php if ($errContactos !== ""): ?><p class="muted">Contactos: <?php echo $errContactos; ?></p><?php endif; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>

  <?php include 'inc/footer.php'; ?>
</body>
</html>
