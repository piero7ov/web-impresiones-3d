<!DOCTYPE html>
<html lang="es">
<head>
    <title>Pierodev</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles.css">
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

    <main>
      <?php
        // Escape básico para evitar problemas al imprimir texto
        function h($s){
          return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
        }

        // =========================
        // Cargar JSON enriquecido
        // =========================
        $jsonPath = __DIR__ . "/data/productos_enriched.json";

        if (!file_exists($jsonPath)) {
          echo "<article>";
          echo "<strong>Error</strong>";
          echo "<em>No se encontró <code>data/productos_enriched.json</code></em>";
          echo "</article>";
        } else {
          $raw = file_get_contents($jsonPath);
          $data = json_decode($raw, true);

          if (!is_array($data) || !isset($data["products"]) || !is_array($data["products"])) {
            echo "<article>";
            echo "<strong>Error</strong>";
            echo "<em>El JSON no tiene el formato esperado (falta <code>products</code>).</em>";
            echo "</article>";
          } else {

            $products = $data["products"];

            // Contador para aplicar diseño destacado al primer producto
            $index = 0;

            foreach ($products as $p) {
              $index++;

              // Campos del JSON (con fallback)
              $nombre      = (string)($p["nombre"] ?? "");
              $descripcion = (string)($p["descripcion"] ?? "");
              $imagen      = (string)($p["imagen"] ?? "");
              $enlace      = (string)($p["enlace"] ?? "");
              $precio      = (string)($p["precio"] ?? "");
              $material    = (string)($p["material"] ?? "");
              $tamano      = (string)($p["tamano"] ?? "");
              $categoria   = (string)($p["categoria"] ?? "");

              // Por ahora seguimos apuntando a legacy out_pages (comparador)
              // Luego lo cambiaremos a producto.php dinámico
              $href = "out_pages/" . ltrim($enlace, "/");

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

    <footer>
        © <?php echo date("Y"); ?> Pierodev · Impresiones 3D
    </footer>
</body>
</html>
