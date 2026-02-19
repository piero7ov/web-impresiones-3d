  <!-- 
    CABECERA ADMIN: header.php
    Muestra el menú de navegación del panel de administración y control de acceso visual.
-->
<header>
    <h1>Pierodev | Impresiones 3D</h1>
    <nav>
      <ul>
        <li><a href="../front/index.php">Web</a></li>
        <?php if (is_admin_logged()): ?>
          <li><a href="index.php">Admin</a></li>
          <li><a href="compras.php">Compras</a></li>
          <li><a href="clientes.php">Clientes</a></li>
          <li><a href="contactos.php">Contactos</a></li>
          <li><a href="logout.php">Salir</a></li>
        <?php else: ?>
          <li><a href="login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </nav>
    <div class="search">
      <input type="text" placeholder="Admin" disabled>
    </div>
  </header>
