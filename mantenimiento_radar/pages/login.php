<?php include "../includes/header.php"; ?>

<div class="login-container">

<div class="login-card">

    <img src="../assets/img/radar_logo_v2.png" class="login-logo">

    <p class="login-subtitle">
        Sistema de gestión de mantenimiento
    </p>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center">
            Usuario o contraseña incorrectos
        </div>
    <?php endif; ?>

    <form action="../auth/validar_login.php" method="POST">
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>

        <button>Iniciar sesión</button>
    </form>

    <!-- REDES -->
    <div class="login-social">
        <a href="https://www.radarholding.com/" target="_blank">
            <i class="bi bi-globe"></i>
        </a>
        <a href="https://www.youtube.com/channel/UCnZ__HMaCy2jBPc2LcMkpdA" target="_blank">
            <i class="bi bi-youtube"></i>
        </a>
        <a href="https://www.facebook.com/radarcustomslogistics" target="_blank">
            <i class="bi bi-facebook"></i>
        </a>
    </div>

</div>
</div>
<?php include "../includes/footer.php"; ?>