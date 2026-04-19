<nav class="navbar navbar-expand-lg navbar-dark custom-navbar px-4">

    <div class="container-fluid">

        <!-- TEXTO IZQUIERDA -->
        <a href="../pages/dashboard.php" class="nav-brand-clean text-decoration-none">
            Gestión de mantenimiento
        </a>

        <!-- BOTÓN MOBILE -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>



        <!-- LINKS -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-4">

                <li class="nav-item">
                    <a class="nav-link" href="../pages/dashboard.php">Dashboard</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="../pages/equipos.php">Equipos</a>
                </li>
                 <!-- Lboton solo para admin-->

                 <?php if ($_SESSION['rol'] == 'supervisor'): ?>
            <li class="nav-item">
                <a class="nav-link" href="../pages/configuracion.php">
                    Plan de mantenimiento
                </a>
            </li>
        <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link logout" href="../auth/logout.php">Cerrar sesión</a>
                </li>

            </ul>
        </div>

    </div>

</nav>