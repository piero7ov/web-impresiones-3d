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
        // Cargar el XML de productos 3D desde el archivo productos3d.xml
        // simplexml_load_file devuelve un objeto SimpleXMLElement con todos los nodos del XML
        $xml = simplexml_load_file("productos3d.xml");

        // Inicializamos un contador para saber en qué posición vamos (1, 2, 3...)
        // Esto se usa para aplicar el diseño especial al primer producto (destacado)
        $index = 0;

        // Recorremos cada nodo <producto> dentro del XML
        foreach ($xml->producto as $producto) {

            // Aumentamos el contador en 1 por cada producto
            $index++;

            // Convertimos cada campo del XML a string para trabajar más cómodo en PHP
            $nombre      = (string)$producto->nombre;
            $descripcion = (string)$producto->descripcion;
            $imagen      = (string)$producto->imagen;
            $enlace      = (string)$producto->enlace;    // Enlace a más detalles del producto
            $precio      = (string)$producto->precio;
            $material    = (string)$producto->material;
            $tamano      = (string)$producto->tamano;
            $categoria   = (string)$producto->categoria;

            // Si es el primer producto (index === 1), lo mostramos con el diseño "destacado"
            // - Imagen a la izquierda
            // - Información a la derecha
            // - Borde azul más marcado
            if ($index === 1) {
                echo "<article class='destacado'>";
                echo "  <div class='destacado-inner'>";

                // Columna izquierda: imagen grande del producto
                echo "    <div class='destacado-img'>";
                echo "      <img src='$imagen' alt='$nombre'>";
                echo "    </div>";

                // Columna derecha: nombre, descripción, meta y precio + botón
                echo "    <div class='destacado-info'>";
                echo "      <strong>$nombre</strong>";
                echo "      <em>$descripcion</em>";

                // Bloque de información adicional en formato "píldoras":
                // material, tamaño y categoría
                echo "      <div class='product-meta'>";
                echo "        <span class='meta-tag'><span class='meta-label'>Material:</span> $material</span>";
                echo "        <span class='meta-tag'><span class='meta-label'>Tamaño:</span> $tamano</span>";
                echo "        <span class='meta-tag'><span class='meta-label'>Categoría:</span> $categoria</span>";
                echo "      </div>";

                // Bloque inferior: precio + botón "Más información"
                echo "      <div class='product-price-cta'>";
                echo "        <span class='product-price'>Precio: $precio €</span>";
                $enlace = "out_pages/" . ltrim($enlace, "/");
                echo "        <a class='btn-more' href='$enlace'>Más información</a>";
                echo "      </div>";

                echo "    </div>";   // cierre .destacado-info

                echo "  </div>";     // cierre .destacado-inner
                echo "</article>";

            } else {
                // Para el resto de productos usamos la tarjeta "normal"
                // que se adapta a la rejilla (2 columnas, 4 columnas, etc.)
                echo "<article>";

                // Imagen del producto
                echo "<img src='$imagen' alt='$nombre'>";

                // Nombre del producto
                echo "<strong>$nombre</strong>";

                // Descripción corta del producto
                echo "<em>$descripcion</em>";

                // Píldoras con material, tamaño y categoría
                echo "<div class='product-meta'>";
                echo "  <span class='meta-tag'><span class='meta-label'>Material:</span> $material</span>";
                echo "  <span class='meta-tag'><span class='meta-label'>Tamaño:</span> $tamano</span>";
                echo "  <span class='meta-tag'><span class='meta-label'>Categoría:</span> $categoria</span>";
                echo "</div>";

                // Zona inferior con precio y botón de acción
                echo "<div class='product-price-cta'>";
                echo "<span class='product-price'>Precio: $precio €</span>";
                $enlace = "out_pages/" . ltrim($enlace, "/");
                echo "        <a class='btn-more' href='$enlace'>Más información</a>";
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
