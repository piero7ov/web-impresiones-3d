<?php
declare(strict_types=1);

function h($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
}

function load_xml(string $xmlPath): array {
  libxml_use_internal_errors(true);

  if (!file_exists($xmlPath)) return [null, "No se encontró <code>data/contacto.xml</code>"];

  $xml = simplexml_load_file($xmlPath);
  if ($xml === false) return [null, "No se pudo leer <code>data/contacto.xml</code> (XML inválido)."];

  return [$xml, ""];
}

/* ====== Cargar XML ====== */
$xmlPath = __DIR__ . "/data/contacto.xml";
[$xml, $loadError] = load_xml($xmlPath);

$heroTitle = $xml ? (string)($xml->hero->titulo ?? "Contacto") : "Contacto";
$heroSub   = $xml ? (string)($xml->hero->subtitulo ?? "") : "";

/* ====== POST: guardar contacto en JSON ====== */
$okMsg = "";
$errMsg = "";

$nombre  = "";
$email   = "";
$asunto  = "";
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nombre  = trim((string)($_POST["nombre"] ?? ""));
  $email   = trim((string)($_POST["email"] ?? ""));
  $asunto  = trim((string)($_POST["asunto"] ?? ""));
  $mensaje = trim((string)($_POST["mensaje"] ?? ""));

  if ($nombre === "" || $email === "" || $mensaje === "") {
    $errMsg = "Completa los campos obligatorios: Nombre, Email y Mensaje.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errMsg = "El email no parece válido.";
  } else {
    $savePath = __DIR__ . "/data/contactos_recibidos.json";

    // Cargar lista actual (si existe)
    $list = [];
    if (file_exists($savePath)) {
      $raw = file_get_contents($savePath);
      if ($raw !== false) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) $list = $decoded;
      }
    }

    // Añadir nuevo registro
    $list[] = [
      "created_at" => gmdate("c"),
      "nombre"     => $nombre,
      "email"      => $email,
      "asunto"     => $asunto,
      "mensaje"    => $mensaje
    ];

    // Guardar con lock
    $json = json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
      $errMsg = "No se pudo generar el JSON.";
    } else {
      $ok = @file_put_contents($savePath, $json . PHP_EOL, LOCK_EX);
      if ($ok === false) {
        $errMsg = "No se pudo guardar el mensaje. Revisa permisos de escritura en <code>data/</code>.";
      } else {
        $okMsg = "¡Listo! Tu mensaje se envió correctamente.";
        // limpiar campos
        $nombre = $email = $asunto = $mensaje = "";
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title><?php echo h($heroTitle); ?> · Contacto</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/styles.css">

  <style>
    /* =========================
       HERO simple (full width)
       ========================= */
    .hero{
      width: 100%;
      background-image:
        linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)),
        url("static/heroe_contacto.png");
      background-size: cover;
      background-position: center;
      padding: 60px 20px;
      box-sizing: border-box;
    }

    .hero-inner{
      max-width: 1100px;
      margin: 0 auto;
      color: #ffffff;
    }

    .hero-title{
      margin: 0 0 10px 0;
      font-size: 2.6rem;
      line-height: 1.1;
      font-weight: 800;
      letter-spacing: 0.4px;
    }

    .hero-sub{
      margin: 0;
      font-size: 1.05rem;
      line-height: 1.6;
      color: rgba(255,255,255,0.88);
      max-width: 780px;
    }

    /* =========================
       Contacto: no usar nth-child del index
       ========================= */
    main.contacto{
      max-width: 1100px;
      align-items: flex-start;
    }

    main.contacto article{
      flex-basis: 100% !important;
      text-align: left;
    }

    /* Layout 2 columnas (form + info) */
    .contact-grid{
      width: 100%;
      display: grid;
      grid-template-columns: 1.15fr 0.85fr;
      gap: 20px;
    }

    @media (max-width: 900px){
      .contact-grid{ grid-template-columns: 1fr; }
      .hero{ padding: 44px 16px; }
      .hero-title{ font-size: 2.1rem; }
    }

    /* Caja del form */
    .form-box{
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      background: #ffffff;
      box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
      padding: 16px;
    }

    .section-title{
      margin: 0 0 10px 0;
      color: #1e3a8a;
      font-size: 1.1rem;
    }

    .hint{
      margin: 0 0 12px 0;
      color: #6b7280;
      line-height: 1.6;
      font-size: 0.95rem;
    }

    .msg-ok{
      padding: 12px 14px;
      border-radius: 12px;
      background: rgba(16,185,129,0.12);
      border: 1px solid rgba(16,185,129,0.25);
      color: #065f46;
      margin-bottom: 12px;
      font-weight: 700;
    }

    .msg-err{
      padding: 12px 14px;
      border-radius: 12px;
      background: rgba(239,68,68,0.10);
      border: 1px solid rgba(239,68,68,0.25);
      color: #7f1d1d;
      margin-bottom: 12px;
      font-weight: 700;
    }

    .field{
      margin-bottom: 12px;
    }

    .field label{
      display: block;
      font-size: 0.9rem;
      font-weight: 800;
      color: #111827;
      margin-bottom: 6px;
    }

    .field input,
    .field textarea{
      width: 100%;
      box-sizing: border-box;
      padding: 11px 12px;
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      background: #ffffff;
      font-size: 1rem;
      outline: none;
      font-family: inherit;
    }

    .field textarea{
      min-height: 140px;
      resize: vertical;
    }

    .field input:focus,
    .field textarea:focus{
      border-color: #0ea5e9;
      box-shadow: 0 0 0 3px rgba(14,165,233,0.20);
    }

    .actions{
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
      margin-top: 6px;
    }

    .btn-send{
      padding: 11px 20px;
      border-radius: 999px;
      background: linear-gradient(135deg, #1e3a8a, #0ea5e9);
      color: #ffffff;
      font-weight: 800;
      border: none;
      cursor: pointer;
    }
    .btn-send:hover{ filter: brightness(1.05); }
    .btn-send:focus{ outline: 2px solid #0ea5e9; outline-offset: 2px; }

    .btn-secondary{
      display: inline-block;
      padding: 11px 16px;
      border-radius: 999px;
      background: #e5e7eb;
      color: #111827;
      text-decoration: none;
      font-size: 0.95rem;
      font-weight: 800;
      border: none;
      cursor: pointer;
    }
    .btn-secondary:hover{ filter: brightness(0.98); }
    .btn-secondary:focus{ outline: 2px solid #0ea5e9; outline-offset: 2px; }

    /* Panel de canales */
    .channels{
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      background: #ffffff;
      box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
      padding: 16px;
    }

    .channel{
      display: flex;
      gap: 10px;
      align-items: flex-start;
      padding: 10px 0;
      border-bottom: 1px solid #f3f4f6;
    }

    .channel:last-child{
      border-bottom: none;
    }

    .ch-icon{
      width: 34px;
      height: 34px;
      border-radius: 12px;
      background: rgba(14,165,233,0.12);
      border: 1px solid rgba(14,165,233,0.20);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      flex: 0 0 auto;
    }

    .ch-body{
      line-height: 1.3;
    }

    .ch-label{
      font-weight: 900;
      color: #1e3a8a;
      margin: 0 0 4px 0;
    }

    .ch-value{
      margin: 0;
      color: #111827;
    }

    .ch-note{
      margin: 4px 0 0 0;
      color: #6b7280;
      font-size: 0.9rem;
    }

    /* =========================
       MAPA (iframe)
       ========================= */
    .map-block{
      margin-top: 14px;
      padding-top: 14px;
      border-top: 1px solid #f3f4f6;
    }

    .map-title{
      margin: 0 0 10px 0;
      font-weight: 900;
      color: #1e3a8a;
      font-size: 1rem;
    }

    .map-frame{
      width: 100%;
      height: 260px;
      border: 0;
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 10px 26px rgba(15, 23, 42, 0.10);
      background: #f3f4f6;
    }

    .map-caption{
      margin: 10px 0 0 0;
      color: #6b7280;
      font-size: 0.92rem;
      line-height: 1.5;
    }

    .map-link{
      color: #0ea5e9;
      text-decoration: none;
      font-weight: 800;
    }
    .map-link:hover{ text-decoration: underline; }
    .map-link:focus{ outline: 2px solid #0ea5e9; outline-offset: 2px; }
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
    <section class="hero" aria-label="Hero Contacto">
      <div class="hero-inner">
        <h2 class="hero-title"><?php echo h($heroTitle); ?></h2>
        <?php if ($heroSub !== ""): ?>
          <p class="hero-sub"><?php echo h($heroSub); ?></p>
        <?php endif; ?>
      </div>
    </section>
  <?php endif; ?>

  <main class="contacto">
    <?php if ($loadError !== ""): ?>
      <article>
        <strong>Error</strong>
        <em><?php echo $loadError; ?></em>
      </article>
    <?php else: ?>

      <div class="contact-grid">

        <!-- FORM -->
        <article class="form-box">
          <h3 class="section-title"><?php echo h((string)($xml->form["titulo"] ?? "Escríbenos")); ?></h3>

          <?php if (isset($xml->form->hint) && (string)$xml->form->hint !== ""): ?>
            <p class="hint"><?php echo h((string)$xml->form->hint); ?></p>
          <?php endif; ?>

          <?php if ($okMsg !== ""): ?>
            <div class="msg-ok"><?php echo h($okMsg); ?></div>
          <?php endif; ?>

          <?php if ($errMsg !== ""): ?>
            <div class="msg-err"><?php echo h($errMsg); ?></div>
          <?php endif; ?>

          <form method="post" action="contacto.php" novalidate>
            <div class="field">
              <label for="nombre">Nombre *</label>
              <input id="nombre" name="nombre" type="text" value="<?php echo h($nombre); ?>" required>
            </div>

            <div class="field">
              <label for="email">Email *</label>
              <input id="email" name="email" type="email" value="<?php echo h($email); ?>" required>
            </div>

            <div class="field">
              <label for="asunto">Asunto</label>
              <input id="asunto" name="asunto" type="text" value="<?php echo h($asunto); ?>">
            </div>

            <div class="field">
              <label for="mensaje">Mensaje *</label>
              <textarea id="mensaje" name="mensaje" required><?php echo h($mensaje); ?></textarea>
            </div>

            <div class="actions">
              <button class="btn-send" type="submit">Enviar mensaje</button>
              <a class="btn-secondary" href="index.php">← Volver</a>
            </div>
          </form>
        </article>

        <!-- INFO + MAPA -->
        <article class="channels">
          <h3 class="section-title"><?php echo h((string)($xml->canales["titulo"] ?? "Canales")); ?></h3>

          <?php if (isset($xml->canales->canal)): ?>
            <?php foreach ($xml->canales->canal as $c): ?>
              <?php
                $icon  = (string)($c["icon"] ?? "ℹ️");
                $label = (string)($c["label"] ?? "");
                $value = (string)($c["value"] ?? "");
                $note  = (string)($c["note"] ?? "");
              ?>
              <div class="channel">
                <div class="ch-icon" aria-hidden="true"><?php echo h($icon); ?></div>
                <div class="ch-body">
                  <p class="ch-label"><?php echo h($label); ?></p>
                  <p class="ch-value"><?php echo h($value); ?></p>
                  <?php if ($note !== ""): ?>
                    <p class="ch-note"><?php echo h($note); ?></p>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="hint">No hay canales configurados en el XML.</p>
          <?php endif; ?>

          <!-- MAPA (iframe) -->
          <div class="map-block" aria-label="Mapa">
            <p class="map-title">📍 Encuéntranos</p>

            <iframe
              class="map-frame"
              title="Mapa - Valencia"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              src="https://www.openstreetmap.org/export/embed.html?bbox=-0.4190%2C39.4500%2C-0.3350%2C39.4900&amp;layer=mapnik&amp;marker=39.4699%2C-0.3763">
            </iframe>

            <p class="map-caption">
              Valencia, España ·
              <a class="map-link" href="https://www.openstreetmap.org/?mlat=39.4699&amp;mlon=-0.3763#map=13/39.4699/-0.3763" target="_blank" rel="noopener">
                Abrir en el mapa
              </a>
            </p>
          </div>
        </article>

      </div>

    <?php endif; ?>
  </main>

  <footer>
    © <?php echo date("Y"); ?> Pierodev · Impresiones 3D
  </footer>
</body>
</html>
