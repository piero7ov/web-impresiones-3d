<?php
/**
 * PÁGINA: nosotros.php
 * Finalidad: Mostrar información sobre la empresa, materiales y valores.
 * Carga la información desde un archivo XML (data/nosotros.xml).
 */

declare(strict_types=1);

/**
 * Función auxiliar para sanitizar cadenas de texto (XSS prevention).
 */
function h($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
}

$DATA_DIR = dirname(__DIR__) . "/data";
$xmlPath = $DATA_DIR . "/nosotros.xml";


// Carga segura del XML utilizando libxml
libxml_use_internal_errors(true);
$xml = null;
$loadError = "";

if (!file_exists($xmlPath)) {
  $loadError = "No se pudo cargar la información sobre nosotros actualmente.";
} else {
  $xml = simplexml_load_file($xmlPath);
  if ($xml === false) {
    $loadError = "Error de formato en la información corporativa.";
  }
}

$tituloHero = $xml ? (string)($xml->hero->titulo ?? "Nosotros") : "Nosotros";
$subHero    = $xml ? (string)($xml->hero->subtitulo ?? "") : "";

$tituloEsc = h($tituloHero);
$tituloHeroHtml = str_replace("3D", '<span class="hero-accent">3D</span>', $tituloEsc);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title><?php echo h($tituloHero); ?> · Nosotros</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/styles.css">
  <link rel="stylesheet" href="assets/nosotros.css">

</head>

<body>
  <?php include 'inc/header.php'; ?>

  <?php if ($loadError === ""): ?>
    <!-- HERO -->
    <section class="hero" aria-label="Hero Nosotros">
      <div class="hero-inner">
        <h2 class="hero-title"><?php echo $tituloHeroHtml; ?></h2>
        <?php if ($subHero !== ""): ?>
          <p class="hero-sub"><?php echo h($subHero); ?></p>
        <?php endif; ?>
        <a class="hero-cta" href="index.php">VER PRODUCTOS</a>
      </div>
    </section>
  <?php endif; ?>

  <main class="nosotros">
    <?php if ($loadError !== ""): ?>
      <article>
        <strong>Error</strong>
        <em><?php echo $loadError; ?></em>
      </article>
    <?php else: ?>

      <!-- Renderizado dinámico de los bloques de contenido definidos en el XML -->
      <?php if (isset($xml->bloques->bloque)): ?>
        <?php foreach ($xml->bloques->bloque as $b): ?>
          <?php $blockId = (string)($b["id"] ?? ""); ?>

          <article>
            <h3 class="section-title"><?php echo h((string)($b["titulo"] ?? "Sección")); ?></h3>

            <?php if (isset($b->p)): ?>
              <?php foreach ($b->p as $p): ?>
                <p class="p"><?php echo h((string)$p); ?></p>
              <?php endforeach; ?>
            <?php endif; ?>

            <?php if (isset($b->pasos->paso)): ?>
              <ul class="list">
                <?php foreach ($b->pasos->paso as $paso): ?>
                  <li><?php echo h((string)$paso); ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

            <?php if (isset($b->lista->item)): ?>
              <ul class="list">
                <?php foreach ($b->lista->item as $it): ?>
                  <li><?php echo $it->asXML() ? h(strip_tags($it->asXML())) : h((string)$it); ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

            <?php if ($blockId === "materiales"): ?>
              <!-- Showcase de materiales: Bloque visual que muestra muestras reales -->
              <div class="materials-showcase" aria-label="Materiales en fotos">
                <div class="mat-pill">
                  <div class="mat-badge">
                    <img src="static/pla.png" alt="Material PLA">
                  </div>
                  <div class="mat-text">
                    <div class="mat-name">PLA</div>
                    <div class="mat-sub">Acabado limpio</div>
                  </div>
                </div>

                <div class="mat-pill">
                  <div class="mat-badge">
                    <img src="static/petg.png" alt="Material PETG">
                  </div>
                  <div class="mat-text">
                    <div class="mat-name">PETG</div>
                    <div class="mat-sub">Más resistente</div>
                  </div>
                </div>

                <div class="mat-pill">
                  <div class="mat-badge">
                    <img src="static/resina.png" alt="Material Resina">
                  </div>
                  <div class="mat-text">
                    <div class="mat-name">Resina</div>
                    <div class="mat-sub">Máximo detalle</div>
                  </div>
                </div>
              </div>
            <?php endif; ?>

          </article>

          <?php if ($blockId === "compromiso"): ?>
            <!-- Imagen promocional de calidad fuera del flujo de tarjetas -->
            <div class="quality-wrap" aria-label="Imagen de calidad">
              <div class="quality-hero">
                <img src="static/calidad.png" alt="Muestra de calidad de impresión">
              </div>
            </div>
          <?php endif; ?>

        <?php endforeach; ?>
      <?php endif; ?>

      <!-- Valores -->
      <?php if (isset($xml->valores->valor)): ?>
        <article>
          <h3 class="section-title"><?php echo h((string)($xml->valores["titulo"] ?? "Valores")); ?></h3>

          <div class="values-grid">
            <?php foreach ($xml->valores->valor as $v): ?>
              <div class="value-card">
                <strong><?php echo h((string)($v["titulo"] ?? "Valor")); ?></strong>
                <em><?php echo h((string)$v); ?></em>
              </div>
            <?php endforeach; ?>
          </div>
        </article>
      <?php endif; ?>

    <?php endif; ?>
  </main>

  <?php include 'inc/footer.php'; ?>
</body>
</html>
