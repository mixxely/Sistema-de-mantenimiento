<?php


ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

require_once "../config/conexion.php";



if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";

if (isset($_GET['success'])) {
    if ($_GET['success'] == "eliminado") {
        $mensaje = "🗑️ Equipo eliminado correctamente.";
    }

    if ($_GET['success'] == "editado") {
        $mensaje = "✏️ Equipo actualizado correctamente.";
    }

    if ($_GET['success'] == "1") {
        $mensaje = "✅ Equipo registrado correctamente.";
    }
}


// Consulta equipos
$sql = "SELECT * FROM equipos ORDER BY created_at DESC";
$resultado = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Equipos | Sistema Radar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">

    <?php if (!empty($mensaje)) : ?>
        <div class="alert alert-success">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Equipos registrados</h3>

        <a href="equipo_nuevo.php" class="btn btn-primary">
            + Nuevo equipo
        </a>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Usuario</th>
                <th>Correo</th>
                <th>No. Serie</th>
                <th>Área</th>
                <th>Supervisor</th>
                <th>Mantenimiento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>

        <?php if ($resultado->num_rows > 0): ?>
            <?php while ($row = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['nombre_usuario']) ?></td>
                    <td><?= htmlspecialchars($row['correo']) ?></td>
                    <td><?= htmlspecialchars($row['numero_serie']) ?></td>
                    <td><?= $row['area'] ?></td>
                    <td><?= $row['supervisor'] ?></td>
                    <td>
                        <?php if ($row['mantenimiento'] == 'SI'): ?>
                            <span class="badge bg-success">SI</span>
                        <?php else: ?>
                            <span class="badge bg-danger">NO</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="editar_equipo.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Editar</a>

                        <a href="mantenimientos.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">
                            Mantenimiento
                        </a>

                        <a href="../actions/eliminar_equipo.php?id=<?= $row['id'] ?>" 
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('¿Eliminar equipo?')">
                        Eliminar
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="text-center">No hay equipos registrados</td>
            </tr>
        <?php endif; ?>

        </tbody>
    </table>

</div>

</body>
</html>