<?php
session_start();

// Proteger acceso
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$tipo = "";

if (isset($_GET['error'])) {
    if ($_GET['error'] == "campos") {
        $mensaje = "⚠️ Todos los campos obligatorios deben llenarse.";
        $tipo = "error";
    }

    if ($_GET['error'] == "duplicado") {
        $mensaje = "❌ El número de serie ya existe en el sistema.";
        $tipo = "error";
    }

    if ($_GET['error'] == "bd") {
        $mensaje = "❌ Error al guardar en la base de datos.";
        $tipo = "error";
    }
}

if (isset($_GET['success'])) {
    $mensaje = "✅ Equipo registrado correctamente.";
    $tipo = "success";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Equipo | Sistema Radar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "../includes/navbar.php"; ?>




<div class="container mt-4">

    <h3 class="mb-4">Registrar nuevo equipo</h3>

     <?php if (!empty($mensaje)) : ?>
    <div class="alert alert-<?= $tipo == 'success' ? 'success' : 'danger' ?>">
        <?= $mensaje ?>
    </div>
<?php endif; ?>

    <form action="../actions/guardar_equipo.php" method="POST">

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nombre del usuario</label>
                <input type="text" name="nombre_usuario" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Correo</label>
                <input type="email" name="correo" class="form-control" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Número de serie</label>
                <input type="text" name="numero_serie" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Área</label>
                <input type="text" name="area" class="form-control">
            </div>
        </div>


        <div class="mb-3">
            <label class="form-label">Supervisor</label>
            <input type="text" name="supervisor" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea name="notas" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-success">
            Guardar equipo
        </button>
        <a href="equipos.php" class="btn btn-secondary">
            Cancelar
        </a>

    </form>

</div>

</body>
</html>