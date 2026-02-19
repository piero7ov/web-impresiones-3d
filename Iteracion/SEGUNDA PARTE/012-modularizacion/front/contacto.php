<?php
/**
 * PÁGINA: contacto.php
 * Finalidad: Formulario de contacto para usuarios.
 * Los datos se validan y se guardan en un archivo JSON (data/contactos_recibidos.json).
 */

declare(strict_types=1);

/**
 * Función auxiliar para sanitizar cadenas de texto (XSS prevention).
 */
function h($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
}

/**
 * Carga la configuración visual de la página (títulos, canales) desde XML.
 * @param string $xmlPath Ruta al archivo de configuración.
 * @return array [SimpleXMLElement|null, string mensaje de error].
 */
function load_xml(string $xmlPath): array {
  libxml_use_internal_errors(true);

  if (!file_exists($xmlPath)) return [null, "Error al cargar la configuración de contacto."];

  $xml = simplexml_load_file($xmlPath);
  if ($xml === false) return [null, "Error en el formato de configuración."];

  return [$xml, ""];
}

/* ====== Cargar XML ====== */
$DATA_DIR = dirname(__DIR__) . "/data";
$xmlPath = $DATA_DIR . "/contacto.xml";

[$xml, $loadError] = load_xml($xmlPath);

$heroTitle = $xml ? (string)($xml->hero->titulo ?? "Contacto") : "Contacto";
$heroSub   = $xml ? (string)($xml->hero->subtitulo ?? "") : "";

/* ====== PROCESAMIENTO DEL FORMULARIO (POST) ====== */
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
    $savePath = $DATA_DIR . "/contactos_recibidos.json";

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
        $errMsg = "No se pudo guardar el mensaje. Por favor, inténtelo de nuevo más tarde.";
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
  <link rel="stylesheet" href="assets/contacto.css">
</head>

<body>
  <?php include 'inc/header.php'; ?>

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

  <?php include 'inc/footer.php'; ?>
</body>
</html>
