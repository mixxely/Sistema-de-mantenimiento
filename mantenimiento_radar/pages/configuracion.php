<?php
session_start();
require_once "../config/conexion.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

// 🔒 SOLO ADMIN
if ($_SESSION['rol'] != 'supervisor') {
    die("No tienes permiso para acceder aquí");
}

// GUARDAR CAMBIOS
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $meses = $_POST['meses'];
    $fecha = $_POST['fecha'];

    $stmt = $conexion->prepare("UPDATE configuracion SET meses_mantenimiento = ?, proxima_ronda = ?");
    $stmt->bind_param("is", $meses, $fecha);
    $stmt->execute();

    $mensaje = "Configuración actualizada correctamente";
}

// OBTENER DATOS ACTUALES
$config = $conexion->query("SELECT meses_mantenimiento, proxima_ronda FROM configuracion LIMIT 1")->fetch_assoc();
?>

<?php include "../includes/header.php"; ?>
<?php include "../includes/navbar.php"; ?>

<main class="flex-grow-1">
<div class="container mt-4">

    <h3>⚙️ Configuración del sistema</h3>
    

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label>Meses entre mantenimientos</label>
            <input type="number" name="meses" class="form-control"
                value="<?= $config['meses_mantenimiento'] ?>" required>
        </div>

        <div class="mb-3">
            <label>Fecha límite del plan</label>
            <input type="date" name="fecha" class="form-control"
                value="<?= $config['proxima_ronda'] ?>" required>
        </div>

        <button class="btn btn-primary">Guardar cambios</button>

    </form>

</div>
</main>

<?php include "../includes/footer.php"; ?>