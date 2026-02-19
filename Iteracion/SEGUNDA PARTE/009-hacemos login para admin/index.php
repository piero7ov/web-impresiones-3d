<!DOCTYPE html>
<html lang="es">
<head>
    <title>Pierodev</title>
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
          url("static/heroe_index.png");
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

    <!-- HERO -->
    <section class="hero" aria-label="Hero Inicio">
      <div class="hero-inner">
        <h2 class="hero-title">Impresiones <span class="hero-accent">3D</span> · Catálogo</h2>
        <p class="hero-sub">Explora nuestros productos, materiales y opciones. Todo en una demo dinámica desde JSON.</p>
        <a class="hero-cta" href="#productos">VER PRODUCTOS</a>
      </div>
    </section>

    <main id="productos">
      <?php
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
            $index = 0;

            foreach ($products as $p) {
              $index++;

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
              } else {
                // fallback por si algún producto viniera sin slug
                $enlace = (string)($p["enlace"] ?? "");
                $href = "producto.php?ref=" . urlencode($enlace);
              }

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
