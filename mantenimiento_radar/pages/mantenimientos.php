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

// historial
$historial = $conexion->prepare("SELECT * FROM mantenimientos WHERE equipo_id = ? ORDER BY fecha DESC");
$historial->bind_param("i", $id);
$historial->execute();
$resultHistorial = $historial->get_result();



$sql = "SELECT * FROM equipos WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$equipo = $result->fetch_assoc();


// 🔹 Obtener configuración (meses)
$config = $conexion->query("SELECT * FROM configuracion LIMIT 1")->fetch_assoc();
$meses = $config['meses_mantenimiento'];

// 🔹 Obtener último mantenimiento
$ultimo = $conexion->prepare("
    SELECT fecha FROM mantenimientos 
    WHERE equipo_id = ? 
    ORDER BY fecha DESC LIMIT 1
");
$ultimo->bind_param("i", $id);
$ultimo->execute();
$resUltimo = $ultimo->get_result()->fetch_assoc();

$proximo = null;

if ($resUltimo) {
    $proximo = date('Y-m-d', strtotime($resUltimo['fecha'] . " +$meses months"));
}



?>

<?php include "../includes/header.php"; ?>
<?php include "../includes/navbar.php"; ?>





<div class="container mt-4">

    <h3>Mantenimiento del equipo</h3>

    <p><strong>Equipo:</strong> <?= $equipo['numero_serie'] ?></p>

    <?php if ($proximo): ?>
    <div class="alert alert-info">
        Próximo mantenimiento estimado: <strong><?= $proximo ?></strong>
    </div>
<?php else: ?>
    <div class="alert alert-warning">
        Este equipo aún no tiene mantenimiento registrado.
    </div>
<?php endif; ?>

    <form action="../actions/guardar_mantenimiento.php" method="POST">

        <input type="hidden" name="id" value="<?= $equipo['id'] ?>">

        <div class="mb-3">
            <label>Fecha de mantenimiento</label>
            <input type="date" name="fecha" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Tipo de mantenimiento</label>
            <select name="tipo" class="form-control" required>
                <option value="INTERNO">Interno</option>
                <option value="EXTERNO">Externo</option>
            </select>
        </div>

        <div class="mb-3">
            <label>¿Autorizado?</label>
            <select name="autorizado" class="form-control">
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Notas del mantenimiento</label>
            <textarea name="notas" class="form-control"></textarea>
        </div>

        <button class="btn btn-success">Guardar mantenimiento</button>
        <a href="equipos.php" class="btn btn-secondary">Cancelar</a>

    </form>

    <h4 class="mt-4">Historial de mantenimientos</h4>

<table class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Autorizado</th>
            <th>Notas</th>
            
        </tr>
    </thead>
    <tbody>

    <?php if ($resultHistorial->num_rows > 0): ?>
        <?php while ($row = $resultHistorial->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['fecha']) ?></td>
                <td><?= htmlspecialchars($row['tipo']) ?></td>
                <td><?= $row['autorizado'] ? 'Sí' : 'No' ?></td>
                <td><?= htmlspecialchars($row['notas']) ?></td>
                
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="5" class="text-center">Sin historial</td>
        </tr>
    <?php endif; ?>

    </tbody>
</table>

</div>

<?php include "../includes/footer.php"; ?>