<?php
/**
 * ARCHIVO PRINCIPAL: index.php
 * Finalidad: Página de inicio que muestra el catálogo de productos.
 * Carga los datos desde data/productos_enriched.json y renderiza las tarjetas de producto.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Pierodev</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="assets/index.css">
</head>

<body>
    <?php include 'inc/header.php'; ?>

    <!-- HERO -->
    <section class="hero" aria-label="Hero Inicio">
      <div class="hero-inner">
        <h2 class="hero-title">Impresiones <span class="hero-accent">3D</span> · Catálogo</h2>
        <p class="hero-sub">Explora nuestros productos, materiales y opciones de personalización con la mejor calidad del mercado.</p>
        <a class="hero-cta" href="#productos">VER PRODUCTOS</a>
      </div>
    </section>

    <main id="productos">
      <?php
        /**
         * Función auxiliar para sanitizar cadenas de texto y evitar ataques XSS.
         * @param mixed $s El texto a sanitizar.
         * @return string Texto seguro para incrustar en HTML.
         */
        function h($s){
          return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
        }

        // Localización del directorio de datos y el archivo del catálogo
        $DATA_DIR = dirname(__DIR__) . "/data";
        $jsonPath = $DATA_DIR . "/productos_enriched.json";

        if (!file_exists($jsonPath)) {
          echo "<article>";
          echo "<strong>Error</strong>";
          echo "<em>No se pudo cargar el catálogo de productos en este momento.</em>";
          echo "</article>";
        } else {
          $raw = file_get_contents($jsonPath);
          $data = json_decode($raw, true);

          if (!is_array($data) || !isset($data["products"]) || !is_array($data["products"])) {
            echo "<article>";
            echo "<strong>Error</strong>";
            echo "<em>Error en el formato del catálogo. Por favor, contacte con soporte.</em>";
            echo "</article>";
          } else {

            $products = $data["products"];
            $index = 0;

            // Recorremos el array de productos cargado desde el JSON
            foreach ($products as $p) {
              $index++; // Contador para identificar el primer producto (destacado)

              $nombre      = (string)($p["nombre"] ?? "");
              $descripcion = (string)($p["descripcion"] ?? "");
              $imagen      = (string)($p["imagen"] ?? "");
              $precio      = (string)($p["precio"] ?? "");
              $material    = (string)($p["material"] ?? "");
              $tamano      = (string)($p["tamano"] ?? "");
              $categoria   = (string)($p["categoria"] ?? "");

              // ✅ Enlace a producto dinámico
              $slug = (string)($p["slug"] ?? "");
              if ($slug !== "") {
                $href = "producto.php?slug=" . urlencode($slug);
                // Fallback por si algún producto viniera sin slug, usamos el enlace directo
                $enlace = (string)($p["enlace"] ?? "");
                $href = "producto.php?ref=" . urlencode($enlace);
              }

              // El primer producto se muestra con un diseño "destacado" más grande

              if ($index === 1) {
                echo "<article class='destacado'>";
                echo "  <div class='destacado-inner'>";

                echo "    <div class='destacado-img'>";
                echo "      <img src='" . h($imagen) . "' alt='" . h($nombre) . "'>";
                echo "    </div>";

                echo "    <div class='destacado-info'>";
                echo "      <strong>" . h($nombre) . "</strong>";
                echo "      <em>" . h($descripcion) . "</em>";

                echo "      <div class='product-meta'>";
                echo "        <span class='meta-tag'><span class='meta-label'>Material:</span> " . h($material) . "</span>";
                echo "        <span class='meta-tag'><span class='meta-label'>Tamaño:</span> " . h($tamano) . "</span>";
                echo "        <span class='meta-tag'><span class='meta-label'>Categoría:</span> " . h($categoria) . "</span>";
                echo "      </div>";

                echo "      <div class='product-price-cta'>";
                echo "        <span class='product-price'>Precio: " . h($precio) . " €</span>";
                echo "        <a class='btn-more' href='" . h($href) . "'>Más información</a>";
                echo "      </div>";

                echo "    </div>";

                echo "  </div>";
                echo "</article>";

              } else {
                echo "<article>";

                echo "<img src='" . h($imagen) . "' alt='" . h($nombre) . "'>";
                echo "<strong>" . h($nombre) . "</strong>";
                echo "<em>" . h($descripcion) . "</em>";

                echo "<div class='product-meta'>";
                echo "  <span class='meta-tag'><span class='meta-label'>Material:</span> " . h($material) . "</span>";
                echo "  <span class='meta-tag'><span class='meta-label'>Tamaño:</span> " . h($tamano) . "</span>";
                echo "  <span class='meta-tag'><span class='meta-label'>Categoría:</span> " . h($categoria) . "</span>";
                echo "</div>";

                echo "<div class='product-price-cta'>";
                echo "  <span class='product-price'>Precio: " . h($precio) . " €</span>";
                echo "  <a class='btn-more' href='" . h($href) . "'>Más información</a>";
                echo "</div>";

                echo "</article>";
              }
            }
          }
        }
      ?>
    </main>

    <?php include 'inc/footer.php'; ?>
</body>
</html>
