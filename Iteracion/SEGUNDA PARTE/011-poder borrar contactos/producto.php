<?php
declare(strict_types=1);

function h($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
}

function load_products(string $jsonPath): array {
  if (!file_exists($jsonPath)) return [[], "No se encontró <code>data/productos_enriched.json</code>"];

  $raw = file_get_contents($jsonPath);
  if ($raw === false) return [[], "No se pudo leer <code>data/productos_enriched.json</code>"];

  $data = json_decode($raw, true);
  if (!is_array($data) || !isset($data["products"]) || !is_array($data["products"])) {
    return [[], "El JSON no tiene el formato esperado (falta <code>products</code>)."];
  }
  return [$data["products"], ""];
}

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

$jsonPath = __DIR__ . "/data/productos_enriched.json";
[$products, $loadError] = load_products($jsonPath);

$slug = isset($_GET["slug"]) ? trim((string)$_GET["slug"]) : null;
$ref  = isset($_GET["ref"])  ? trim((string)$_GET["ref"])  : null;
$id   = isset($_GET["id"])   ? (int)$_GET["id"]            : null;

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

  <style>
    /* SOLO para producto.php */

    main{
      max-width: 1200px;
      align-items: center;
    }

    .product-card{
      flex-basis: 100%;
      max-width: 1100px;
      margin: 10px auto;
      text-align: left;
      border-width: 2px;
      border-color: #0ea5e9;
      padding: 18px;
      min-height: 590px;
    }

    .product-card .destacado-inner{
      align-items: center;
      min-height: 520px;
    }

    .product-card .destacado-info{
      gap: 12px;
    }

    .product-media{
      width: 100%;
      max-width: 420px;
      border-radius: 12px;
      overflow: hidden;
      background: #f3f4f6;
      border: 1px solid #e5e7eb;
    }

    .product-media img{
      width: 100%;
      height: 360px;
      object-fit: cover;
      display: block;
      margin: 0;
    }

    .product-short{
      display: block;
      font-size: 0.95rem;
      color: #6b7280;
      margin: 0;
    }

    .bullets{
      margin: 0;
      padding-left: 18px;
      color: #111827;
      line-height: 1.6;
    }

    .tags{
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin: 2px 0 0 0;
    }

    .tag{
      padding: 4px 10px;
      border-radius: 999px;
      background: #eef2ff;
      color: #1e3a8a;
      font-size: 0.85rem;
      white-space: nowrap;
    }

    .msg-ok{
      padding: 12px 14px;
      border-radius: 12px;
      background: rgba(16,185,129,0.12);
      border: 1px solid rgba(16,185,129,0.25);
      color: #065f46;
      font-weight: 800;
      margin-top: 8px;
    }

    .purchase-box{
      margin-top: 6px;
      padding: 14px;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      background: #ffffff;
      box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
      display: grid;
      gap: 12px;
    }

    .purchase-top{
      display: flex;
      align-items: baseline;
      justify-content: space-between;
      gap: 10px;
      flex-wrap: wrap;
    }

    .purchase-price{
      font-weight: bold;
      font-size: 1.35rem;
      color: #1e3a8a;
    }

    .purchase-sub{
      font-size: 0.9rem;
      color: #6b7280;
    }

    .purchase-controls{
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .field label{
      display: block;
      font-size: 0.85rem;
      color: #374151;
      margin-bottom: 6px;
      font-weight: bold;
    }

    .field select{
      width: 100%;
      padding: 10px 12px;
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      background: #ffffff;
      font-size: 1rem;
      outline: none;
    }

    .field select:focus{
      border-color: #0ea5e9;
      box-shadow: 0 0 0 3px rgba(14,165,233,0.20);
    }

    .color-options{
      display: flex;
      gap: 10px;
      align-items: center;
      flex-wrap: wrap;
      padding-top: 2px;
    }

    .color-pill{
      display: inline-flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      user-select: none;
      position: relative;
      padding: 4px 6px;
      border-radius: 10px;
    }

    .color-pill input{
      position: absolute;
      opacity: 0;
      pointer-events: none;
    }

    .swatch{
      width: 22px;
      height: 22px;
      border-radius: 999px;
      border: 2px solid #e5e7eb;
      box-shadow: 0 1px 3px rgba(15, 23, 42, 0.10);
      display: inline-block;
    }

    .swatch.black{ background: #111827; border-color: #111827; }
    .swatch.white{ background: #ffffff; border-color: #d1d5db; }
    .swatch.blue { background: #1e3a8a; border-color: #1e3a8a; }

    .color-pill input:focus + .swatch{
      outline: 2px solid #0ea5e9;
      outline-offset: 2px;
    }

    .color-pill input:checked + .swatch{
      box-shadow: 0 0 0 4px rgba(14,165,233,0.20), 0 1px 3px rgba(15, 23, 42, 0.12);
    }

    .actions-row{
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      margin-top: 12px;
      padding-top: 12px;
      border-top: 1px solid #e5e7eb;
    }

    .actions-left{
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center;
    }

    .btn-buy{
      display: inline-block;
      padding: 11px 20px;
      border-radius: 999px;
      background: linear-gradient(135deg, #1e3a8a, #0ea5e9);
      color: #ffffff;
      font-size: 0.95rem;
      font-weight: bold;
      border: none;
      cursor: pointer;
    }

    .btn-buy:hover{ filter: brightness(1.05); }
    .btn-buy:focus{ outline: 2px solid #0ea5e9; outline-offset: 2px; }

    .btn-secondary{
      display: inline-block;
      padding: 11px 16px;
      border-radius: 999px;
      background: #e5e7eb;
      color: #111827;
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: bold;
      border: none;
      cursor: pointer;
    }

    .btn-secondary:hover{ filter: brightness(0.98); }
    .btn-secondary:focus{ outline: 2px solid #0ea5e9; outline-offset: 2px; }

    @media (max-width: 900px){
      .purchase-controls{ grid-template-columns: 1fr; }
      .product-media img{ height: 280px; }
      .product-card{ min-height: unset; }
      .product-card .destacado-inner{ min-height: unset; }
      .actions-row{ justify-content: center; }
    }
  </style>
</head>

<body>
  <header>
    <h1>Pierodev | Impresiones 3D</h1>
    <nav>
      <ul>
        <li><a href="index.php">Inicio</a></li>
        <li><a href="nosotros.php">Nosotros</a></li>
        <li><a href="contacto.php">Contacto</a></li>
        <li><a href="admin/index.php">Admin</a></li>
      </ul>
    </nav>
    <div class="search">
      <input type="text" placeholder="Buscar...">
    </div>
  </header>

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
              <div class="msg-ok">✅ Compra registrada (demo).</div>
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

  <footer>
    © <?php echo date("Y"); ?> Pierodev · Impresiones 3D
  </footer>
</body>
</html>
