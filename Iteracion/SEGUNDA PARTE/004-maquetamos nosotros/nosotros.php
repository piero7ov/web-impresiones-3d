<?php
declare(strict_types=1);

function h($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
}

$xmlPath = __DIR__ . "/data/nosotros.xml";

libxml_use_internal_errors(true);
$xml = null;
$loadError = "";

if (!file_exists($xmlPath)) {
  $loadError = "No se encontró <code>data/nosotros.xml</code>";
} else {
  $xml = simplexml_load_file($xmlPath);
  if ($xml === false) {
    $loadError = "No se pudo leer <code>data/nosotros.xml</code> (XML inválido).";
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

<style>
  /* =========================
     HERO
     ========================= */
  .hero{
    width: 100%;
    background-image:
      linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)),
      url("static/heroe_3d.png");
    background-size: cover;
    background-position: center;
    padding: 90px 20px;
    box-sizing: border-box;
  }

  .hero-inner{
    max-width: 1100px;
    margin: 0 auto;
    color: #ffffff;
  }

  .hero-title{
    margin: 0 0 10px 0;
    font-size: 3rem;
    line-height: 1.05;
    letter-spacing: 0.5px;
    font-weight: 800;
  }

  .hero-accent{
    color: #0ea5e9;
  }

  .hero-sub{
    margin: 0 0 18px 0;
    font-size: 1.15rem;
    line-height: 1.6;
    color: rgba(255,255,255,0.88);
    max-width: 720px;
  }

  .hero-cta{
    display: inline-block;
    padding: 12px 22px;
    border-radius: 999px;
    background: linear-gradient(135deg, #1e3a8a, #0ea5e9);
    color: #ffffff;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 800;
    border: none;
  }

  .hero-cta:hover{ filter: brightness(1.05); }
  .hero-cta:focus{ outline: 2px solid #0ea5e9; outline-offset: 2px; }

  @media (max-width: 900px){
    .hero-title{ font-size: 2.2rem; }
    .hero{ padding: 48px 16px; }
  }

  /* =========================
     Nosotros: anular rejilla nth-child del index
     ========================= */
  main.nosotros{
    max-width: 1100px;
    align-items: flex-start;
  }
  main.nosotros article{
    flex-basis: 100% !important;
    text-align: left;
  }
  main.nosotros article.destacado{
    flex-basis: 100% !important;
  }

  /* Mejor lectura */
  .section-title{
    margin: 0 0 10px 0;
    color: #1e3a8a;
    font-size: 1.1rem;
  }
  .p{
    margin: 0 0 10px 0;
    color: #111827;
    line-height: 1.6;
  }
  .list{
    margin: 0;
    padding-left: 18px;
    line-height: 1.6;
    color: #111827;
  }

  /* Valores en tarjetas */
  .values-grid{
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-top: 10px;
  }
  .value-card{
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px;
    background: #ffffff;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
  }
  .value-card strong{
    display: block;
    margin-bottom: 6px;
    color: #1e3a8a;
    font-size: 1rem;
  }
  .value-card em{
    display: block;
    color: #6b7280;
    font-style: normal;
    line-height: 1.6;
    font-size: 0.92rem;
    margin: 0;
  }

  @media (max-width: 900px){
    .values-grid{ grid-template-columns: 1fr; }
  }

  /* =========================
     Materiales: presentación creativa con fotos (NO cards)
     (VERSIÓN MÁS GRANDE)
     ========================= */
  .materials-showcase{
    margin-top: 14px;
    padding: 18px 16px;
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(30,58,138,0.10), rgba(14,165,233,0.14));
    border: 1px solid rgba(14,165,233,0.22);
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
  }

  .mat-pill{
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    border-radius: 999px;
    background: rgba(255,255,255,0.85);
    border: 1px solid rgba(229,231,235,1);
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.10);
  }

  .mat-pill:nth-child(2){
    transform: translateY(-6px) rotate(-1deg);
  }

  .mat-pill:nth-child(3){
    transform: translateY(5px) rotate(1deg);
  }

  .mat-badge{
    width: 72px;
    height: 72px;
    border-radius: 999px;
    overflow: hidden;
    border: 2px solid #0ea5e9;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .mat-badge img{
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .mat-text{
    display: flex;
    flex-direction: column;
    line-height: 1.05;
  }

  .mat-name{
    font-weight: 800;
    color: #1e3a8a;
    font-size: 1.05rem;
  }

  .mat-sub{
    margin-top: 6px;
    font-size: 0.92rem;
    color: #6b7280;
  }

  /* =========================
     Imagen "Calidad" fuera de las tarjetas
     (max-width 1060px)
     ========================= */
  .quality-wrap{
    flex-basis: 100% !important;
    width: 100%;
    display: flex;
    justify-content: center;
    margin-top: -2px;
    margin-bottom: 2px;
  }

  .quality-hero{
    width: 100%;
    max-width: 1060px;
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.10);
    background: #ffffff;
  }

  .quality-hero img{
    width: 100%;
    height: auto;
    display: block;
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
      </ul>
    </nav>
    <div class="search">
      <input type="text" placeholder="Buscar...">
    </div>
  </header>

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

      <!-- Bloques -->
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
              <!-- Showcase debajo del bloque de materiales -->
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
            <!-- Imagen de calidad (fuera de tarjetas) -->
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

  <footer>
    © <?php echo date("Y"); ?> Pierodev · Impresiones 3D
  </footer>
</body>
</html>
