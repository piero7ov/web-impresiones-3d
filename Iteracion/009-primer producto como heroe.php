<!DOCTYPE html>
<html lang="es">
<head>
    <title>Pierodev</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    body, html {
        padding: 0px;
        margin: 0px;
        font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;
    }
    header {
        background: linear-gradient(135deg, #1e3a8a, #0ea5e9);
        color: white;
        display: flex;
        align-items: center;
        padding: 10px 20px;
    }
    header h1 {
        margin: 0;
        font-size: 1.5rem;
    }
    header nav {
        flex-grow: 1;
        display: flex;
        justify-content: center;
    }
    header nav ul {
        display: flex;
        gap: 20px;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    header a {
        color: inherit;
        text-decoration: none;
        font-size: 1.3em;
    }
    .search input {
        padding: 8px 15px;
        border-radius: 15px;
        border: none;
        outline: none;
        font-size: 1rem;
    }

    /* -------- REJILLA COMPLEJA CON FLEX SOLO EN MAIN -------- */

    main {
        display: flex;
        flex-wrap: wrap;         
        gap: 20px;               
        padding: 20px;
        box-sizing: border-box;
        max-width: 1200px;
        margin: 0 auto;
        align-items: stretch;
    }

    /* Tarjeta base */
    main article {
        border: 1px solid #dddddd;
        padding: 15px;
        box-sizing: border-box;
        flex: 1 1 100%;
        text-align: center;
        background: #ffffff;
        border-radius: 10px;

        display: flex;
        flex-direction: column;

        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    main article:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.18);
        border-color: #0ea5e9;
    }

    main article img {
        height: auto;
        display: block;
        width: 200px;
        margin: 0 auto 10px auto;
    }

    main article strong {
        display: block;
        font-size: 1.05rem;
        margin-bottom: 4px;
    }

    main article em {
        display: block;
        font-size: 0.9rem;
        color: #4b5563;
        margin-bottom: 8px;
    }

    .product-meta {
        font-size: 0.85rem;
        color: #374151;
        line-height: 1.4;
        margin-bottom: 8px;
    }

    .product-price-cta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;

        margin-top: auto;   /* deja precio+botón pegados al fondo */
        padding-top: 8px;
        border-top: 1px solid #e5e7eb;
    }

    .product-price {
        font-weight: bold;
        font-size: 1rem;
        color: #1e3a8a;
    }

    .btn-more {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 999px;
        background: linear-gradient(135deg, #1e3a8a, #0ea5e9);
        color: #ffffff;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: bold;
        border: none;
    }

    .btn-more:hover {
        filter: brightness(1.05);
    }

    /* ======== TARJETA DESTACADA (PRODUCTO 1) ======== */

    main article.destacado {
        flex-basis: 100%;
        text-align: left; /* el texto a la izquierda en escritorio */
    }

    .destacado-inner {
        display: flex;
        gap: 20px;
        align-items: stretch;
    }

    .destacado-img {
        flex: 0 0 40%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .destacado-img img {
        width: 100%;
        max-width: 260px;
        height: auto;
        margin: 0 auto;
    }

    .destacado-info {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    /* Producto 1 sigue usando las mismas clases internas:
       strong, em, .product-meta, .product-price-cta */

    /* ======== REJILLA COMPLEJA (ANCHO DE CADA TARJETA) ======== */

    /* Producto 1 ya controlado con .destacado (100%) */

    /* Productos 2 y 3: dos tarjetas anchas (media fila cada una) */
    main article:nth-child(2),
    main article:nth-child(3) {
        flex-basis: calc(50% - 20px);
    }

    /* Productos 4, 5, 6 y 7: cuatro columnas */
    main article:nth-child(4),
    main article:nth-child(5),
    main article:nth-child(6),
    main article:nth-child(7) {
        flex-basis: calc(25% - 20px);
    }

    /* Productos 8 y 9: dos columnas anchas */
    main article:nth-child(8),
    main article:nth-child(9) {
        flex-basis: calc(50% - 20px);
    }

    /* RESPONSIVE */
    @media (max-width: 900px) {
        main article.destacado {
            flex-basis: 100%;
        }
        .destacado-inner {
            flex-direction: column;
        }
        .destacado-info {
            text-align: center;
        }

        main article:nth-child(2),
        main article:nth-child(3),
        main article:nth-child(4),
        main article:nth-child(5),
        main article:nth-child(6),
        main article:nth-child(7),
        main article:nth-child(8),
        main article:nth-child(9) {
            flex-basis: calc(50% - 20px);
        }
    }

    @media (max-width: 600px) {
        main article {
            flex-basis: 100%;
        }
        .product-price-cta {
            justify-content: center;
        }
    }

    footer {
        text-align: center;
        padding: 12px 0px;
        margin-top: 20px;
        font-size: 0.9rem;
        background: linear-gradient(135deg, #152a60, #0a70a0);
        color: white;
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

    <main>
      <?php
        // Cargar el XML de productos 3D
        $xml = simplexml_load_file("productos3d.xml");
        $index = 0;

        foreach ($xml->producto as $producto) {

            $index++;

            $nombre      = (string)$producto->nombre;
            $descripcion = (string)$producto->descripcion;
            $imagen      = (string)$producto->imagen;
            $enlace      = (string)$producto->enlace;    // Puede estar vacío
            $precio      = (string)$producto->precio;
            $material    = (string)$producto->material;
            $tamano      = (string)$producto->tamano;
            $categoria   = (string)$producto->categoria;

            // PRIMER PRODUCTO: DESTACADO
            if ($index === 1) {
                echo "<article class='destacado'>";
                echo "  <div class='destacado-inner'>";

                echo "    <div class='destacado-img'>";
                echo "      <img src='$imagen' alt='$nombre'>";
                echo "    </div>";

                echo "    <div class='destacado-info'>";
                echo "      <strong>$nombre</strong>";
                echo "      <em>$descripcion</em>";

                echo "      <div class='product-meta'>";
                echo "        Material: $material<br>";
                echo "        Tamaño: $tamano<br>";
                echo "        Categoría: $categoria";
                echo "      </div>";

                echo "      <div class='product-price-cta'>";
                echo "        <span class='product-price'>Precio: $precio €</span>";
                if (!empty($enlace)) {
                    echo "    <a class='btn-more' href='$enlace'>Más información</a>";
                }
                echo "      </div>"; // product-price-cta

                echo "    </div>";   // destacado-info

                echo "  </div>";     // destacado-inner
                echo "</article>";

            } else {
                // RESTO DE PRODUCTOS: TARJETA NORMAL
                echo "<article>";

                echo "<img src='$imagen' alt='$nombre'>";
                echo "<strong>$nombre</strong>";
                echo "<em>$descripcion</em>";

                echo "<div class='product-meta'>";
                echo "Material: $material<br>";
                echo "Tamaño: $tamano<br>";
                echo "Categoría: $categoria";
                echo "</div>";

                echo "<div class='product-price-cta'>";
                echo "<span class='product-price'>Precio: $precio €</span>";
                if (!empty($enlace)) {
                    echo "<a class='btn-more' href='$enlace'>Más información</a>";
                }
                echo "</div>";

                echo "</article>";
            }
        }
      ?>
    </main>

    <footer>
        © <?php echo date("Y"); ?> Pierodev · Impresiones 3D
    </footer>
</body>
</html>
