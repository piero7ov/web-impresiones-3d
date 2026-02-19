<?php
/**
 * PÁGINA: finalizacion.php
 * Finalidad: Pantalla de agradecimiento tras realizar una compra.
 * Muestra el resumen del pedido leyendo data/compras_ficticias.json pasando el orderId por URL.
 */

declare(strict_types=1);

/**
 * Función auxiliar para sanitizar cadenas de texto (XSS prevention).
 */
function h($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
}

/**
 * Busca una compra específica en el histórico por ID de pedido.
 * @param string $jsonPath Ruta al archivo de compras.
 * @param string $orderId ID único del pedido.
 * @return array|null Datos de la compra o null si no se encuentra.
 */
function load_purchase(string $jsonPath, string $orderId): ?array {
    if (!file_exists($jsonPath)) return null;
    $raw = file_get_contents($jsonPath);
    if ($raw === false) return null;
    $data = json_decode($raw, true);
    if (!is_array($data)) return null;

    foreach ($data as $purchase) {
        if (($purchase['order_id'] ?? '') === $orderId) {
            return $purchase;
        }
    }
    return null;
}

$orderId = trim((string)($_GET['order'] ?? ''));
$DATA_DIR = __DIR__ . "/../data";
$comprasPath = $DATA_DIR . "/compras_ficticias.json";

$purchase = null;
if ($orderId !== "") {
    $purchase = load_purchase($comprasPath, $orderId);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>¡Gracias por tu compra! · Pierodev</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="assets/finalizacion.css">
</head>
<body>
    <?php include 'inc/header.php'; ?>

    <main class="success-page">
        <div class="success-icon">✅</div>
        <h1>¡Gracias por tu compra!</h1>
        <p>Tu pedido ha sido recibido correctamente y ya está en proceso.</p>

        <?php if ($purchase): ?>
            <div class="order-summary-card">
                <h3>Detalles del pedido</h3>
                <div class="summary-item">
                    <span>Número de pedido:</span>
                    <strong>#<?php echo h($orderId); ?></strong>
                </div>
                <div class="summary-item">
                    <span>Producto:</span>
                    <strong><?php echo h($purchase['compra']['product_name'] ?? '—'); ?></strong>
                </div>
                <div class="summary-item">
                    <span>Cantidad:</span>
                    <strong><?php echo h((string)($purchase['compra']['qty'] ?? '—')); ?></strong>
                </div>
                <div class="summary-item">
                    <span>Color:</span>
                    <strong><?php echo h($purchase['compra']['color'] ?? '—'); ?></strong>
                </div>
                <div class="summary-item summary-total">
                    <span>Total pagado:</span>
                    <strong>€ <?php echo h($purchase['compra']['total'] ?? '0.00'); ?></strong>
                </div>
            </div>
        <?php else: ?>
            <p class="muted" style="margin-top:20px;">No se pudo cargar el resumen del pedido, pero este se ha registrado correctamente.</p>
        <?php endif; ?>

        <a href="index.php" class="btn-home">Volver a la tienda</a>
    </main>

    <?php include 'inc/footer.php'; ?>
</body>
</html>
