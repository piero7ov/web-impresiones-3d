<?php
declare(strict_types=1);

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }

$contactos = [
  ["created_at"=>"2026-02-18T12:01:00Z","nombre"=>"Lucía Torres","email"=>"lucia@mail.com","asunto"=>"Material","mensaje"=>"¿PETG aguanta exterior?"],
  ["created_at"=>"2026-02-18T10:05:00Z","nombre"=>"Carlos Pérez","email"=>"carlos@mail.com","asunto"=>"Pedido","mensaje"=>"Quiero confirmar el tamaño del soporte para monitor."],
  ["created_at"=>"2026-02-18T09:30:00Z","nombre"=>"Andrea","email"=>"andrea@mail.com","asunto"=>"Consulta","mensaje"=>"Hola, ¿hacen envíos a Valencia?"],
];

$clientes = [
  ["id"=>"cli_a1b2c3d4e5","nombre"=>"Carlos Pérez","email"=>"carlos@mail.com"],
  ["id"=>"cli_f6g7h8i9j0","nombre"=>"Lucía Torres","email"=>"lucia@mail.com"],
];

$i = (int)($_GET["i"] ?? -1);
$msg = ($i >= 0 && $i < count($contactos)) ? $contactos[$i] : null;

$clienteMatch = null;
if ($msg) {
  foreach ($clientes as $c) {
    if ($c["email"] === $msg["email"]) { $clienteMatch = $c; break; }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title>Mensaje · Admin</title>
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
      <h2>Mensaje</h2>
      <span class="admin-badge">MOCKUP</span>
    </div>

    <?php if (!$msg): ?>
      <section class="admin-card">
        <h3>No encontrado</h3>
        <p class="muted">Ese mensaje no existe en el mockup.</p>
        <div class="admin-actions">
          <a class="btn-admin-secondary" href="contactos.php">← Volver</a>
        </div>
      </section>
    <?php else: ?>
      <section class="detail-grid">
        <div class="admin-card">
          <h3>Detalle</h3>
          <div class="detail-row"><div>Fecha</div><strong><?php echo h($msg["created_at"]); ?></strong></div>
          <div class="detail-row"><div>Nombre</div><strong><?php echo h($msg["nombre"]); ?></strong></div>
          <div class="detail-row"><div>Email</div><strong><?php echo h($msg["email"]); ?></strong></div>
          <div class="detail-row"><div>Asunto</div><strong><?php echo h($msg["asunto"]); ?></strong></div>
          <p class="note" style="margin-top:10px;"><?php echo h($msg["mensaje"]); ?></p>

          <div class="admin-actions">
            <a class="btn-admin-secondary" href="contactos.php">← Volver</a>
          </div>
        </div>

        <div class="admin-card">
          <h3>Vincular a cliente</h3>
          <?php if (!$clienteMatch): ?>
            <p class="muted">No hay cliente con ese email (mockup).</p>
          <?php else: ?>
            <p class="muted">Encontrado por email:</p>
            <div class="detail-row"><div>Cliente</div><strong><?php echo h($clienteMatch["nombre"]); ?></strong></div>
            <div class="detail-row"><div>ID</div><strong><?php echo h($clienteMatch["id"]); ?></strong></div>

            <div class="admin-actions">
              <a class="btn-admin" href="cliente.php?id=<?php echo urlencode($clienteMatch["id"]); ?>">Ver ficha cliente</a>
            </div>
          <?php endif; ?>
        </div>
      </section>
    <?php endif; ?>

  </main>

  <footer>
    © <?php echo date("Y"); ?> Pierodev · Admin (Mockup)
  </footer>
</body>
</html>
