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

$buscar = $_GET['buscar'] ?? '';
$filtro_fecha = $_GET['fecha'] ?? '';

///paginacion

$limite = 10; // equipos por página
$pagina = $_GET['pagina'] ?? 1;
$pagina = (int)$pagina;

$inicio = ($pagina - 1) * $limite;


$sql_total = "SELECT COUNT(*) as total FROM equipos e";

if (!empty($buscar)) {
    $sql_total .= " WHERE 
        e.nombre_usuario LIKE '%$buscar%' OR
        e.numero_serie LIKE '%$buscar%' OR
        e.area LIKE '%$buscar%'";
}


$result_total = $conexion->query($sql_total);
$total_registros = $result_total->fetch_assoc()['total'];

$total_paginas = ceil($total_registros / $limite);




// Consulta equipos
$sql = "
SELECT e.*, 
       m.fecha AS fecha_mantenimiento,
       m.tipo AS tipo_mantenimiento,
       m.autorizado,
       m.proximo_mantenimiento
FROM equipos e
LEFT JOIN mantenimientos m 
    ON e.id = m.equipo_id
    AND m.id = (
        SELECT id FROM mantenimientos 
        WHERE equipo_id = e.id 
        ORDER BY fecha DESC LIMIT 1
    )
";

$where = [];
$params = [];
$types = "";

// 🔍 BÚSQUEDA SEGURA
if (!empty($buscar)) {
    $where[] = "(e.nombre_usuario LIKE ? OR e.numero_serie LIKE ? OR e.area LIKE ?)";
    
    $buscarLike = "%$buscar%";
    $params[] = $buscarLike;
    $params[] = $buscarLike;
    $params[] = $buscarLike;
    
    $types .= "sss";
}

// 📅 FILTRO POR PRÓXIMO MANTENIMIENTO
if (!empty($filtro_fecha)) {
    $where[] = "(m.proximo_mantenimiento IS NOT NULL AND m.proximo_mantenimiento <= DATE_ADD(CURDATE(), INTERVAL ? DAY))";
    
    $params[] = $filtro_fecha;
    $types .= "i";
}

// UNIR CONDICIONES
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// ORDEN Y PAGINACIÓN
$sql .= " ORDER BY 
    (m.proximo_mantenimiento IS NULL) ASC,
    m.proximo_mantenimiento ASC LIMIT ?, ?";
$params[] = $inicio;
$params[] = $limite;
$types .= "ii";

// PREPARE
$stmt = $conexion->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();

$resultado = $stmt->get_result();

?>

<?php include "../includes/header.php"; ?>
<?php include "../includes/navbar.php"; ?>


<main class="main-content">
    <div class="container mt-4">

    <?php if (!empty($mensaje)) : ?>
        <div class="alert alert-success">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold">Gestión de equipos</h3>



        <?php if (!empty($buscar)): ?>
    <p class="text-muted">
        Resultados para: <strong><?= htmlspecialchars($buscar) ?></strong>
    </p>
<?php endif; ?>

        <!--------------- barra de busqueda  ------->

        <form method="GET" class="mb-3">
        <div class="input-group">
            <input 
                type="text" 
                name="buscar" 
                class="form-control" 
                placeholder="Buscar por usuario, serie o área..."
                value="<?= $_GET['buscar'] ?? '' ?>" >

                

            <!-- Botón limpiar -->
            <?php if (!empty($_GET['buscar'])): ?>
                        <a href="equipos.php" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg"></i>
        </a>
            <?php endif; ?>

            <button class="btn btn-dark">Buscar</button>
        </div>
    </form>
<!----------->

<!--filtro de busqueda-->

<form method="GET" class="mb-3 d-flex gap-2">

    <!-- Mantener búsqueda -->
    <input type="hidden" name="buscar" value="<?= htmlspecialchars($buscar) ?>">

    <select name="fecha" class="form-select" style="max-width: 200px;">
        <option value="">Filtrar por fecha</option>
        <option value="7" <?= $filtro_fecha == '7' ? 'selected' : '' ?>>Últimos 7 días</option>
        <option value="30" <?= $filtro_fecha == '30' ? 'selected' : '' ?>>Últimos 30 días</option>
        <option value="90" <?= $filtro_fecha == '90' ? 'selected' : '' ?>>Últimos 90 días</option>
    </select>

    <button class="btn btn-outline-primary">Aplicar</button>

    <?php if (!empty($filtro_fecha)): ?>
        <a href="equipos.php" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg"></i>
        </a>
    <?php endif; ?>

</form>

<!---------------->


        <a href="equipo_nuevo.php" class="btn btn-primary">
            + Nuevo equipo
        </a>
    </div>

<?php
$totalVencidos = $conexion->query("
    SELECT COUNT(*) as total
    FROM mantenimientos
    WHERE proximo_mantenimiento IS NOT NULL
    AND proximo_mantenimiento < CURDATE()
")->fetch_assoc()['total'];
?>

<?php if ($totalVencidos > 0): ?>
    <div class="alert alert-danger">
        ⚠️ <?= $totalVencidos ?> equipos con mantenimiento vencido
    </div>
<?php endif; ?>

  <!--tabla----->   


<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Usuario</th>
                <th>Correo</th>
                <th>No. Serie</th>
                <th>Área</th>
                <th>Supervisor</th>
                <th>Mantenimiento</th>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Autorizado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>

        <?php if ($resultado->num_rows > 0): ?>

            <?php while ($row = $resultado->fetch_assoc()): 
                $hoy = date('Y-m-d');
                $proximo = $row['proximo_mantenimiento'] ?? null;
                ?>
                

            <?php

            $estado = "sin";

            if (!empty($row['proximo_mantenimiento'])) {
                $hoy = date('Y-m-d');

                if ($row['proximo_mantenimiento'] < $hoy) {
                    $estado = "vencido";
                } else {
                    $estado = "ok";
                }
            }


            // clases de fila
            $claseFila = match($estado) {
                "vencido" => "fila-vencido",
                "ok" => "fila-ok",
                default => "fila-sin"
            };

            ?>

            <tr class="<?= $claseFila ?>">
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['nombre_usuario']) ?></td>
                    <td><?= htmlspecialchars($row['correo']) ?></td>
                    <td><?= htmlspecialchars($row['numero_serie']) ?></td>
                    <td><strong><?= htmlspecialchars($row['area']) ?></strong></td>
                    <td><?= htmlspecialchars($row['supervisor']) ?></td>


                                <td>
               <?php 
            if (empty($proximo)) {

                echo '<span class="badge bg-secondary">Sin registro</span>';

            } else {

                if ($proximo < $hoy) {
                    echo '<span class="badge bg-danger">Vencido</span>';
                } else {
                    echo '<span class="badge bg-success">Al día</span>';
                }

                // usar objetos con OTROS nombres
                $hoyObj = new DateTime();
                $proximoObj = new DateTime($proximo);
                $diff = $hoyObj->diff($proximoObj)->days;

                if ($proximoObj < $hoyObj) {
                    echo "<br><small class='text-danger'>Hace $diff días</small>";
                } else {
                    echo "<br><small class='text-muted'>En $diff días</small>";
                }
            }
            ?>
            </td>

                    <td>
                        <?= $row['fecha_mantenimiento'] ? date('d/m/Y', strtotime($row['fecha_mantenimiento'])) : '—' ?>
                    </td>

                    <td><?= $row['tipo_mantenimiento'] ?? '—' ?></td>

                    <td>
                        <?php if ($row['autorizado'] == 1): ?>
                            <span class="badge bg-success">Sí</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">No</span>
                        <?php endif; ?>
                    </td>

                    <td class="text-nowrap">
                        <a href="editar_equipo.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="mantenimientos.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Mantenimiento</a>

                        <a href="../actions/eliminar_equipo.php?id=<?= $row['id'] ?>" 
                        class="btn btn-sm btn-danger btn-eliminar">
                        Eliminar
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="11" class="text-center">
                    😕 No se encontraron resultados
                </td>
            </tr>
        <?php endif; ?>

        </tbody>
    </table>
</div>

<!-- PAGINACIÓN (FUERA DE LA TABLA) -->
<nav class="mt-3">
    <ul class="pagination justify-content-center">

        <?php if ($pagina > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?pagina=<?= $pagina - 1 ?>&buscar=<?= $buscar ?>&fecha=<?= $filtro_fecha ?>">«</a>
            </li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                <a class="page-link" href="?pagina=<?= $i ?>&buscar=<?= $buscar ?>&fecha=<?= $filtro_fecha ?>">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>

        <?php if ($pagina < $total_paginas): ?>
            <li class="page-item">
                <a class="page-link" href="?pagina=<?= $pagina + 1 ?>&buscar=<?= $buscar ?>&fecha=<?= $filtro_fecha ?>">»</a>
            </li>
        <?php endif; ?>

    </ul>
</nav>

<!--fin de paginacion-->

</div>
</main>



<?php include "../includes/footer.php"; ?>