<?php


ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

require_once "../config/conexion.php";

// Total equipos
$totalEquipos = $conexion->query("SELECT COUNT(*) as total FROM equipos")->fetch_assoc()['total'];

// Equipos con mantenimiento


// Equipos con mantenimiento vigente
$conMantenimiento = $conexion->query("
    SELECT COUNT(*) as total 
    FROM mantenimientos 
    WHERE proximo_mantenimiento >= CURDATE()
")->fetch_assoc()['total'];

// Equipos sin mantenimiento o vencidos
$sinMantenimiento = $conexion->query("
    SELECT COUNT(*) as total 
    FROM equipos e
    LEFT JOIN mantenimientos m ON e.id = m.equipo_id
    WHERE m.proximo_mantenimiento IS NULL 
    OR m.proximo_mantenimiento < CURDATE()
")->fetch_assoc()['total'];



// Últimos equipos
$ultimos = $conexion->query("SELECT * FROM equipos ORDER BY id DESC LIMIT 5");

// 🔴 Equipos vencidos
$vencidos = $conexion->query("
    SELECT COUNT(*) as total 
    FROM mantenimientos 
    WHERE proximo_mantenimiento IS NOT NULL 
    AND proximo_mantenimiento < CURDATE()
")->fetch_assoc()['total'];

// 🟡 Próximos a vencer (en 30 días)
$proximos = $conexion->query("
    SELECT COUNT(*) as total 
    FROM mantenimientos 
    WHERE proximo_mantenimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
")->fetch_assoc()['total'];

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$config = $conexion->query("SELECT * FROM configuracion LIMIT 1")->fetch_assoc();


$modoRonda = $config['modo_ronda'];

$fechaRonda = $config['proxima_ronda'];


$hoy = date('Y-m-d');
$diasRestantes = (strtotime($fechaRonda) - strtotime($hoy)) / 86400;

$equiposPendientes = $totalEquipos - $conMantenimiento;

$porDia = $diasRestantes > 0 ? ceil($equiposPendientes / $diasRestantes) : 0;

// 🔵 Equipos sugeridos para hoy
$sugeridos = [];

if ($modoRonda == 1 && $porDia > 0) {
    $sqlSugeridos = $conexion->prepare("
    SELECT e.id, e.nombre_usuario, e.numero_serie, e.area 
    FROM equipos e
    LEFT JOIN mantenimientos m 
        ON e.id = m.equipo_id
        AND m.id = (
            SELECT id FROM mantenimientos 
            WHERE equipo_id = e.id 
            ORDER BY fecha DESC LIMIT 1
        )
    WHERE m.proximo_mantenimiento IS NULL
       OR m.proximo_mantenimiento < CURDATE()
    ORDER BY e.id ASC
    LIMIT ?
");
    $sqlSugeridos->bind_param("i", $porDia);
    $sqlSugeridos->execute();
    $sugeridos = $sqlSugeridos->get_result();
}



?>

<?php include "../includes/header.php"; ?>
<?php include "../includes/navbar.php"; ?>


<div class="container mt-4">

    <h3 class="mb-3">Bienvenida, <?= htmlspecialchars($_SESSION['usuario']) ?></h3>

    
<!--inicio de tarjetas-->
<div class="row">

    <div class="col-md-4">
        <div class="card radar-card radar-blue shadow mb-3">
            <div class="card-body">
                <h6>Total equipos</h6>
                <h2><?= $totalEquipos ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card radar-card radar-green shadow mb-3">
            <div class="card-body">
                <h6>Equipos al día</h6>
                <h2><?= $conMantenimiento ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card radar-card radar-red shadow mb-3">
            <div class="card-body">
                <h6>Equipos pendientes</h6>
                <h2><?= $sinMantenimiento ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card radar-card radar-red shadow mb-3">
            <div class="card-body">
                <h6>Equipos vencidos</h6>
                <h2><?= $vencidos ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card radar-card radar-blue shadow mb-3">
            <div class="card-body">
                <h6>Próximos a vencer</h6>
                <h2><?= $proximos ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card radar-card shadow mb-3">
            <div class="card-body">
                <h6>Plan de mantenimiento</h6>
                <p>Fecha límite: <?= $fechaRonda ?></p>
                <p>Equipos/día: <strong><?= $porDia ?></strong></p>
            </div>
        </div>
    </div>

</div>

<!--fin de tarjetas-->

<div class="alert alert-info">
    El estado de los equipos se calcula automáticamente según la fecha de su último mantenimiento.
</div>

<a href="equipos.php" class="btn btn-dark mb-3">Ver todos los equipos</a>
<!-- 🔵 MODO RONDA (FUERA DEL ROW) -->
<div class="card shadow mt-4">
    <div class="card-body d-flex justify-content-between align-items-center">
        
        <div>
            <h5 class="mb-1">Modo ronda automática</h5>
            <small class="text-muted">
                Activa para planificar mantenimiento de todos los equipos
            </small>
        </div>

        <form action="../actions/toggle_ronda.php" method="POST">
            <input type="hidden" name="modo" value="<?= $modoRonda ? 0 : 1 ?>">

            <button class="btn <?= $modoRonda ? 'btn-success' : 'btn-secondary' ?> btn-ronda">
                <?= $modoRonda ? 'ACTIVO' : 'INACTIVO' ?>
            </button>
        </form>

    </div>
</div>

<!--TABLA DE PLAN DIARIO -->

<?php if ($modoRonda == 1): ?>

<div class="card shadow mt-4">
    <div class="card-body">
        <h5>📋 Plan diario de mantenimiento</h5>

        <div class="alert alert-info">
            Este sistema calcula automáticamente cuántos equipos debes atender por día 
            para completar el mantenimiento antes de la fecha límite.
            <br><br>
            <strong>Hoy deberías atender <?= $porDia ?> equipos.</strong>
        </div>

        <?php if (!empty($sugeridos) && $sugeridos->num_rows > 0): ?>

            <table class="table table-bordered mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>No. Serie</th>
                        <th>Área</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>

                <?php while ($row = $sugeridos->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nombre_usuario']) ?></td>
                        <td><?= $row['numero_serie'] ?></td>
                        <td><?= $row['area'] ?></td>
                        <td>
                            <a href="mantenimientos.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success">
                                Dar mantenimiento
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>

                </tbody>
            </table>

        <?php else: ?>
            <p class="text-success">🎉 Todos los equipos están al día</p>
        <?php endif; ?>

    </div>
</div>

<?php endif; ?>


<!-- 🔵 TABLA DE VENCIDOS (FUERA DEL ROW) -->
<?php
$listaVencidos = $conexion->query("
    SELECT e.numero_serie, m.proximo_mantenimiento 
    FROM equipos e
    JOIN mantenimientos m ON e.id = m.equipo_id
    WHERE m.proximo_mantenimiento < CURDATE()
    ORDER BY m.proximo_mantenimiento ASC
    LIMIT 5
");
?>

<div class="card shadow mt-3">
    <div class="card-body">
        <h5>Equipos vencidos (detalle)</h5>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Equipo</th>
                    <th>Fecha vencida</th>
                </tr>
            </thead>
            <tbody>

            <?php if ($listaVencidos->num_rows > 0): ?>
                <?php while ($row = $listaVencidos->fetch_assoc()): ?>
                    <tr class="table-danger">
                        <td><?= $row['numero_serie'] ?></td>
                        <td><?= $row['proximo_mantenimiento'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2" class="text-center">Todo en orden</td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>

    <!-- TABLA -->
    <div class="card shadow">
        <div class="card-body">
            <h5>Últimos equipos registrados</h5>

            <table class="table table-bordered mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>No. Serie</th>
                        <th>Área</th>
                        <th>Mantenimiento</th>
                    </tr>
                </thead>
                <tbody>

                <?php if ($ultimos->num_rows > 0): ?>
                    <?php while ($row = $ultimos->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['nombre_usuario']) ?></td>
                            <td><?= htmlspecialchars($row['numero_serie']) ?></td>
                            <td><?= htmlspecialchars($row['area']) ?></td>
                            <td>
                                <?php if ($row['mantenimiento'] == 'SI'): ?>
                                    <span class="badge bg-success">SI</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">NO</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No hay registros</td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>

        </div>
    </div>

</div>

<?php include "../includes/footer.php"; ?>

