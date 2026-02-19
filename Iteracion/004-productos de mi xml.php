<!DOCTYPE html>
<html lang="es">
<head>
    <title>Pierodev</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <header>
        <h1>Pierodev | Tienda de Impresiones 3D</h1>
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="nosotros.php">Nosotros</a></li>
                <li><a href="contacto.php">Contacto</a></li>
            </ul>
        </nav>
    </header>

    <main>
      <?php
        // Cargar el XML de productos 3D
        $xml = simplexml_load_file("productos3d.xml");

        foreach ($xml->producto as $producto) {

            $nombre      = (string)$producto->nombre;
            $descripcion = (string)$producto->descripcion;
            $imagen      = (string)$producto->imagen;
            $enlace      = (string)$producto->enlace;    // Puede estar vacío
            $precio      = (string)$producto->precio;
            $material    = (string)$producto->material;
            $tamano      = (string)$producto->tamano;
            $categoria   = (string)$producto->categoria;

            echo "<div class='producto'>";

            echo "<img src='$imagen' alt='$nombre' style='width:150px'><br>";
            echo "<strong>$nombre</strong><br>";
            echo "<em>$descripcion</em><br>";
            echo "Precio: $precio €<br>";
            echo "Material: $material<br>";
            echo "Tamaño: $tamano<br>";
            echo "Categoría: $categoria<br>";

            echo "</div>\n";
        }
      ?>
    </main>

    <footer>

    </footer>
</body>
</html>
