<?php
/**
 * PÁGINA: producto.php
 * Finalidad: Mostrar la ficha técnica de un producto individual.
 * Permite seleccionar cantidad y color para proceder a la compra.
 */

declare(strict_types=1);

/**
 * Función auxiliar para sanitizar cadenas de texto (XSS prevention).
 */
function h($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
}

/**
 * Carga el catálogo completo de productos desde un archivo JSON.
 * @param string $jsonPath Ruta al archivo JSON.
 * @return array [array de productos, string mensaje de error].
 */
function load_products(string $jsonPath): array {
  if (!file_exists($jsonPath)) return [[], "Lo sentimos, el catálogo no está disponible actualmente."];

  $raw = file_get_contents($jsonPath);
  if ($raw === false) return [[], "Error al cargar los datos del producto."];

  $data = json_decode($raw, true);
  if (!is_array($data) || !isset($data["products"]) || !is_array($data["products"])) {
    return [[], "Error interno en el catálogo de productos."];
  }
  return [$data["products"], ""];
}

/**
 * Busca un producto específico en el catálogo por slug, referencia o ID.
 * @param array $products Array de productos cargado.
 * @param string|null $slug Identificador amigable.
 * @param string|null $ref Referencia del enlace antiguo.
 * @param int|null $id Posición en el array (1-indexed).
 * @return array|null Datos del producto encontrado o null.
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

// 1) Carga del catálogo
$DATA_DIR = dirname(__DIR__) . "/data";
$jsonPath = $DATA_DIR . "/productos_enriched.json";
[$products, $loadError] = load_products($jsonPath);

// 2) Obtención de parámetros de búsqueda
$slug = isset($_GET["slug"]) ? trim((string)$_GET["slug"]) : null;
$ref  = isset($_GET["ref"])  ? trim((string)$_GET["ref"])  : null;
$id   = isset($_GET["id"])   ? (int)$_GET["id"]            : null;

// 3) Identificación del producto
$product = null;
if ($loadError === "") $product = find_product($products, $slug, $ref, $id);

$nombre   = (string)($product["nombre"] ?? "");
$seoTitle = (string)($product["seo_title"] ?? $nombre);
$seoDescription = (string)($product["seo_description"] ?? ($product["short_desc"] ?? "")); // ✅ NO descripcion

// ✅ Mensaje opcional al volver desde compra.php
$backOk = (isset($_GET["buy_ok"]) && (string)$_GET["buy_ok"] === "1");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title><?php echo h($seoTitle !== "" ? $seoTitle : "Producto"); ?> · Pierodev</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <?php if ($seoDescription !== ""): ?>
    <meta name="description" content="<?php echo h($seoDescription); ?>">
  <?php endif; ?>

  <link rel="stylesheet" href="assets/styles.css">
  <link rel="stylesheet" href="assets/producto.css">
</head>

<body>
  <?php include 'inc/header.php'; ?>

  <main>
    <?php if ($loadError !== ""): ?>
      <article>
        <strong>Error</strong>
        <em><?php echo $loadError; ?></em>
        <div style="margin-top:10px;">
          <a class="btn-secondary" href="index.php">Volver al inicio</a>
        </div>
      </article>

    <?php elseif ($product === null): ?>
      <?php http_response_code(404); ?>
      <article>
        <strong>Producto no encontrado</strong>
        <em>No existe o el enlace está incompleto.</em>
        <div style="margin-top:10px;">
          <a class="btn-secondary" href="index.php">Volver al inicio</a>
        </div>
      </article>

    <?php else:
      $shortDesc  = (string)($product["short_desc"] ?? "");
      $imagen     = (string)($product["imagen"] ?? "");
      $precio     = (string)($product["precio"] ?? "");
      $material   = (string)($product["material"] ?? "");
      $tamano     = (string)($product["tamano"] ?? "");
      $categoria  = (string)($product["categoria"] ?? "");
      $bullets    = is_array($product["bullets"] ?? null) ? $product["bullets"] : [];
      $tags       = is_array($product["tags"] ?? null) ? $product["tags"] : [];
      $legacyHtml = (string)($product["enlace"] ?? "");
      $legacyPath = __DIR__ . "/out_pages/" . ltrim($legacyHtml, "/");
      $legacyHref = "out_pages/" . ltrim($legacyHtml, "/");

      $productSlug = (string)($product["slug"] ?? "");
      $productRef  = (string)($product["enlace"] ?? "");
      $productIdFallback = ($id !== null ? (string)$id : "");
    ?>
      <article class="destacado product-card">
        <div class="destacado-inner">
          <div class="destacado-img">
            <div class="product-media">
              <?php if ($imagen !== ""): ?>
                <img src="<?php echo h($imagen); ?>" alt="<?php echo h($nombre); ?>">
              <?php else: ?>
                <div style="height:360px; display:flex; align-items:center; justify-content:center;">
                  <em>Sin imagen</em>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="destacado-info">
            <strong><?php echo h($nombre); ?></strong>

            <?php if ($shortDesc !== ""): ?>
              <p class="product-short"><?php echo h($shortDesc); ?></p>
            <?php endif; ?>

            <div class="product-meta">
              <?php if ($material !== ""): ?>
                <span class="meta-tag"><span class="meta-label">Material:</span> <?php echo h($material); ?></span>
              <?php endif; ?>
              <?php if ($tamano !== ""): ?>
                <span class="meta-tag"><span class="meta-label">Tamaño:</span> <?php echo h($tamano); ?></span>
              <?php endif; ?>
              <?php if ($categoria !== ""): ?>
                <span class="meta-tag"><span class="meta-label">Categoría:</span> <?php echo h($categoria); ?></span>
              <?php endif; ?>
            </div>

            <?php if (!empty($bullets)): ?>
              <ul class="bullets">
                <?php foreach ($bullets as $b): ?>
                  <?php if (trim((string)$b) !== ""): ?>
                    <li><?php echo h($b); ?></li>
                  <?php endif; ?>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

            <?php if (!empty($tags)): ?>
              <div class="tags">
                <?php foreach ($tags as $t): ?>
                  <?php if (trim((string)$t) !== ""): ?>
                    <span class="tag"><?php echo h($t); ?></span>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php if ($backOk): ?>
              <div class="msg-ok">✅ Producto añadido a la cesta de compra correctamente.</div>
            <?php endif; ?>

            <!-- ✅ Checkout: enviar a compra.php -->
            <form method="post" action="compra.php" aria-label="Ir a compra">
              <input type="hidden" name="action" value="start">
              <?php if ($productSlug !== ""): ?>
                <input type="hidden" name="slug" value="<?php echo h($productSlug); ?>">
              <?php endif; ?>
              <?php if ($productSlug === "" && $productRef !== ""): ?>
                <input type="hidden" name="ref" value="<?php echo h($productRef); ?>">
              <?php endif; ?>
              <?php if ($productSlug === "" && $productRef === "" && $productIdFallback !== ""): ?>
                <input type="hidden" name="id" value="<?php echo h($productIdFallback); ?>">
              <?php endif; ?>

              <section class="purchase-box" aria-label="Opciones de compra">
                <div class="purchase-top">
                  <div class="purchase-price">
                    <?php echo $precio !== "" ? ("€ " . h($precio)) : "€ —"; ?>
                  </div>
                  <div class="purchase-sub">Opciones</div>
                </div>

                <div class="purchase-controls">
                  <div class="field">
                    <label for="qty">Cantidad</label>
                    <select id="qty" name="qty">
                      <option value="1">1 unidad</option>
                      <option value="2">2 unidades</option>
                      <option value="3">3 unidades</option>
                      <option value="4">4 unidades</option>
                      <option value="5">5 unidades</option>
                    </select>
                  </div>

                  <div class="field">
                    <label>Color</label>
                    <div class="color-options" role="radiogroup" aria-label="Color">
                      <label class="color-pill">
                        <input type="radio" name="color" value="negro" checked>
                        <span class="swatch black" aria-hidden="true"></span>
                        <span style="font-size:.9rem; color:#374151;">Negro</span>
                      </label>

                      <label class="color-pill">
                        <input type="radio" name="color" value="blanco">
                        <span class="swatch white" aria-hidden="true"></span>
                        <span style="font-size:.9rem; color:#374151;">Blanco</span>
                      </label>

                      <label class="color-pill">
                        <input type="radio" name="color" value="azul">
                        <span class="swatch blue" aria-hidden="true"></span>
                        <span style="font-size:.9rem; color:#374151;">Azul</span>
                      </label>
                    </div>
                  </div>
                </div>
              </section>

              <div class="actions-row">
                <div class="actions-left">
                  <button type="submit" class="btn-buy">Comprar</button>
                </div>

                <div class="actions-left">
                  <a class="btn-secondary" href="index.php">← Volver</a>

                  <?php if ($legacyHtml !== "" && file_exists($legacyPath)): ?>
                    <a class="btn-secondary" href="<?php echo h($legacyHref); ?>">Ver versión antigua</a>
                  <?php endif; ?>
                </div>
              </div>
            </form>

          </div>
        </div>
      </article>
    <?php endif; ?>
  </main>

  <?php include 'inc/footer.php'; ?>
</body>
</html>
