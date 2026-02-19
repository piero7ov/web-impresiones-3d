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
        flex-wrap: wrap;         /* permite varias filas */
        gap: 20px;               /* espacio entre tarjetas */
        padding: 20px;
        box-sizing: border-box;
    }

    /* Base: por defecto ocupan toda la fila (móvil/escritorio simple) */
    main article {
        border: 1px solid #dddddd;
        padding: 10px;
        box-sizing: border-box;
        flex: 1 1 100%;
        text-align: center;
    }

    /* Imagen adaptada al ancho de la tarjeta
       (sobrescribe el style="width:150px") */
    main article img {
        height: auto;
        display: block;
        width: 200px;
        margin: 0 auto 8px auto; 
    }

    /* Producto 1: tarjeta principal grande (toda la fila) */
    main article:nth-child(1) {
        flex-basis: 100%;
    }

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

    /* Productos 8 y 9: cierro con dos columnas anchas */
    main article:nth-child(8),
    main article:nth-child(9) {
        flex-basis: calc(50% - 20px);
    }

    /* RESPONSIVE:
       - Tablets: casi todo en dos columnas
       - Móvil: una columna
    */
    @media (max-width: 900px) {
        main article:nth-child(1) {
            flex-basis: 100%;
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
    }

    /* FOOTER sencillo */
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

            echo "<img src='$imagen' alt='$nombre'><br>";
            echo "<strong>$nombre</strong><br>";
            echo "<em>$descripcion</em><br>";
            echo "Precio: $precio €<br>";
            echo "Material: $material<br>";
            echo "Tamaño: $tamano<br>";
            echo "Categoría: $categoria<br>";
            if (!empty($enlace)) {
                echo "<a href='$enlace'>Más información</a><br>";
            }
            echo "</article>";
        }
      ?>
    </main>

    <footer>
        © <?php echo date("Y"); ?> Pierodev · Impresiones 3D
    </footer>
</body>
</html>
