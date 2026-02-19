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

        foreach ($xml->producto as $producto) {

            $nombre      = (string)$producto->nombre;
            $descripcion = (string)$producto->descripcion;
            $imagen      = (string)$producto->imagen;
            $enlace      = (string)$producto->enlace;    // Puede estar vacío
            $precio      = (string)$producto->precio;
            $material    = (string)$producto->material;
            $tamano      = (string)$producto->tamano;
            $categoria   = (string)$producto->categoria;

            echo "<article>";

            echo "<img src='$imagen' alt='$nombre' style='width:150px'><br>";
            echo "<strong>$nombre</strong><br>";
            echo "<em>$descripcion</em><br>";
            echo "Precio: $precio €<br>";
            echo "Material: $material<br>";
            echo "Tamaño: $tamano<br>";
            echo "Categoría: $categoria<br>";
            echo "<a href='$enlace'>Más información</a><br>";
            echo "</article>";
        }
      ?>
    </main>

    <footer>

    </footer>
</body>
</html>
