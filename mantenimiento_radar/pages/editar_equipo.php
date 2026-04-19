<?php
session_start();
require_once "../config/conexion.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: equipos.php");
    exit;
}

$id = $_GET['id'];

$sql = "SELECT * FROM equipos WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: equipos.php");
    exit;
}

$equipo = $result->fetch_assoc();
?>

<?php include "../includes/header.php"; ?>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">
    <h3>Editar equipo</h3>

    <form action="../actions/actualizar_equipo.php" method="POST">

        <input type="hidden" name="id" value="<?= $equipo['id'] ?>">

        <input type="text" name="nombre_usuario" value="<?= $equipo['nombre_usuario'] ?>" class="form-control mb-2" required>
        <input type="email" name="correo" value="<?= $equipo['correo'] ?>" class="form-control mb-2" required>
        <input type="text" name="numero_serie" value="<?= $equipo['numero_serie'] ?>" class="form-control mb-2" required>
        <input type="text" name="area" value="<?= $equipo['area'] ?>" class="form-control mb-2">
        <input type="text" name="supervisor" value="<?= $equipo['supervisor'] ?>" class="form-control mb-2">
        <textarea name="notas" class="form-control mb-2"><?= $equipo['notas'] ?></textarea>

        <button class="btn btn-success">Actualizar</button>
        <a href="equipos.php" class="btn btn-secondary">Cancelar</a>

    </form>
</div>

<?php include "../includes/footer.php"; ?>