<?php
declare(strict_types=1);

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }

$clientes = [
  ["id"=>"cli_a1b2c3d4e5","nombre"=>"Carlos Pérez","email"=>"carlos@mail.com","telefono"=>"600123456","direccion"=>"C/ Demo 12, Valencia","notas"=>"Cliente recurrente"],
  ["id"=>"cli_f6g7h8i9j0","nombre"=>"Lucía Torres","email"=>"lucia@mail.com","telefono"=>"611222333","direccion"=>"Av. Test 4, Valencia","notas"=>""],
  ["id"=>"cli_k1l2m3n4o5","nombre"=>"Mario Díaz","email"=>"mario@mail.com","telefono"=>"622333444","direccion"=>"C/ Azul 8, Valencia","notas"=>"Entrega por la tarde"],
];

$compras = [
  ["order_id"=>"20260218-124500-acde55cc","created_at"=>"2026-02-18T12:45:00Z","cliente_id"=>"cli_a1b2c3d4e5","product_name"=>"Organizador modular de escritorio","total"=>"59.70"],
  ["order_id"=>"20260218-101500-acde12ff","created_at"=>"2026-02-18T10:15:00Z","cliente_id"=>"cli_a1b2c3d4e5","product_name"=>"Soporte ergonómico para monitor","total"=>"49.80"],
  ["order_id"=>"20260218-114200-acde88aa","created_at"=>"2026-02-18T11:42:00Z","cliente_id"=>"cli_f6g7h8i9j0","product_name"=>"Llavero personalizado con nickname","total"=>"7.50"],
  ["order_id"=>"20260218-120900-acde77bb","created_at"=>"2026-02-18T12:09:00Z","cliente_id"=>"cli_k1l2m3n4o5","product_name"=>"Miniatura de personaje gamer","total"=>"22.00"],
];

$id = trim((string)($_GET["id"] ?? ""));
$cliente = null;

foreach ($clientes as $c) {
  if ($c["id"] === $id) { $cliente = $c; break; }
}

$comprasCliente = [];
$totalGastado = 0.0;
if ($cliente) {
  foreach ($compras as $o) {
    if ($o["cliente_id"] === $cliente["id"]) {
      $comprasCliente[] = $o;
      $totalGastado += (float)$o["total"];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title>Ficha cliente · Admin</title>
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
      <input type="text" placeholder="Mockup admin" disabled>
    </div>
  </header>

  <main class="admin">
    <div class="admin-top">
      <h2>Ficha de cliente</h2>
      <span class="admin-badge">MOCKUP</span>
    </div>

    <?php if (!$cliente): ?>
      <section class="admin-card">
        <h3>No encontrado</h3>
        <p class="muted">No existe ese cliente en el mockup.</p>
        <div class="admin-actions">
          <a class="btn-admin-secondary" href="clientes.php">← Volver</a>
        </div>
      </section>
    <?php else: ?>

      <section class="detail-grid">
        <div class="admin-card">
          <h3>Cliente</h3>
          <div class="detail-row"><div>ID</div><strong><?php echo h($cliente["id"]); ?></strong></div>
          <div class="detail-row"><div>Nombre</div><strong><?php echo h($cliente["nombre"]); ?></strong></div>
          <div class="detail-row"><div>Email</div><strong><?php echo h($cliente["email"]); ?></strong></div>
          <div class="detail-row"><div>Teléfono</div><strong><?php echo h($cliente["telefono"]); ?></strong></div>
          <div class="detail-row"><div>Dirección</div><strong><?php echo h($cliente["direccion"]); ?></strong></div>
          <p class="note"><?php echo h($cliente["notas"]); ?></p>

          <div class="admin-actions">
            <a class="btn-admin-secondary" href="clientes.php">← Volver</a>
          </div>
        </div>

        <div class="admin-card">
          <h3>Resumen</h3>
          <div class="detail-row"><div>Compras</div><strong><?php echo h((string)count($comprasCliente)); ?></strong></div>
          <div class="detail-row"><div>Total (demo)</div><strong>€ <?php echo h(number_format($totalGastado, 2, ".", "")); ?></strong></div>
          <p class="note">En la versión real, esto saldrá de <code>compras_ficticias.json</code>.</p>
        </div>
      </section>

      <section class="admin-table-wrap" style="margin-top:14px;">
        <div class="admin-table-head">
          <h3>Compras del cliente</h3>
          <p>Listado asociado por <span class="kpill">cliente_id</span></p>
        </div>

        <table class="table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Pedido</th>
              <th>Producto</th>
              <th>Total</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($comprasCliente)): ?>
              <tr><td colspan="5">Sin compras (mockup)</td></tr>
            <?php else: ?>
              <?php foreach ($comprasCliente as $o): ?>
                <tr>
                  <td><?php echo h($o["created_at"]); ?></td>
                  <td><span class="kpill"><?php echo h($o["order_id"]); ?></span></td>
                  <td><?php echo h($o["product_name"]); ?></td>
                  <td><strong>€ <?php echo h($o["total"]); ?></strong></td>
                  <td>
                    <a class="btn-admin-secondary" href="compra.php?order=<?php echo urlencode($o["order_id"]); ?>">Ver compra</a>
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
    © <?php echo date("Y"); ?> Pierodev · Admin (Mockup)
  </footer>
</body>
</html>
