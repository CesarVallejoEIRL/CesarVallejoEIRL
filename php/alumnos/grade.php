<?php
session_start();
include '../database/conexion.php';
$current_page = basename($_SERVER['PHP_SELF']);
$niveles = ["Inicial", "Secundaria"];
if (isset($_GET['nivel']) && in_array($_GET['nivel'], $niveles)) {
    $nivel = $_GET['nivel'];
    $query = "SELECT aula, seccion FROM estudiantes WHERE nivel_educativo = ? AND delete_status = 0 GROUP BY aula, seccion";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 's', $nivel);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $aulas_nivel = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $clave = $fila['aula'] . '|' . $fila['seccion'];
        $aulas_nivel[$clave] = ['aula' => $fila['aula'], 'seccion' => $fila['seccion']];
    }
    $alumnos_por_aula = [];
    foreach ($aulas_nivel as $clave => $info) {
        $query_alumnos = "SELECT id, nombre, aula, seccion, estado, fecha_ingreso 
                          FROM estudiantes 
                          WHERE nivel_educativo = ? AND aula = ? AND seccion = ? AND delete_status = 0";
        $stmt = mysqli_prepare($conexion, $query_alumnos);
        mysqli_stmt_bind_param($stmt, 'sss', $nivel, $info['aula'], $info['seccion']);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $alumnos_por_aula[$clave] = mysqli_fetch_all($res, MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Aulas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/php/img/cesar.png">
    <link rel="stylesheet" href="../alumnos/css/grade.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div id="wrapper">
    <nav class="navbar navbar-default navbar-cls-top" role="navigation">
        <div class="navbar-header"></div>
    </nav>
    <nav class="navbar-default navbar-side">
        <div class="sidebar-collapse">
            <h3 class="sidebar-title">CEBA Cesar Vallejo</h3>
            <ul class="nav" id="main-menu">
                <li>
                    <div class="user-img-div text-center">
                        <img src="../img/cesar.png" class="img" alt="Imagen del Colegio">
                        <h5 style="color:white;"><?php echo $_SESSION['usuario']; ?></h5>
                    </div>
                </li>
                <li><a href="../dashboard.php"><i class="fa fa-home"></i> Inicio</a></li>
                    <li><a href="../alumnos/student.php" class="active-menu"><i class="fa fa-users"></i> Estudiantes</a></li>
                    <li><a href="../alumnos/inactivestd.php"><i class="fa fa-toggle-off"></i> Estudiantes Inactivos</a></li>
                    <li><a href="../alumnos/grade.php"><i class="fa fa-th-large"></i> Grado Escolar</a></li>
                    <li><a href="../alumnos/fees.php"><i class="fa fa-credit-card"></i> Pagos</a></li>
                    <li><a href="../alumnos/report.php"><i class="fa fa-file-pdf"></i> Reportes</a></li>
                    <li><a href="../alumnos/setting.html"><i class="fa fa-cogs"></i> Cuenta</a></li>
                    <li><a href="../login.html" id="logoutBtn"><i class="fa fa-power-off"></i> Cerrar Sesión</a></li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <h2><i class="fas fa-layer-group"></i> Gestión de Grado</h2>
        <div class="breadcrumb">
            <?php if (isset($_GET['nivel'])): ?>
                <a href="grade.php"><i class="fas fa-home"></i> Niveles</a> > 
                <span><?= htmlspecialchars($_GET['nivel']) ?></span>
            <?php else: ?>
                <span><i class="fas fa-home"></i> Niveles Educativos</span>
            <?php endif; ?>
        </div>
        <?php if (isset($_GET['nivel'])): ?>
            <button class="back-btn" onclick="window.location.href='grade.php'"><i class="fas fa-arrow-left"></i> Volver</button>
            <h3><i class="fas fa-school"></i> Aulas de <?= htmlspecialchars($_GET['nivel']) ?></h3>
            <div class="cards-container " >
                <?php foreach ($alumnos_por_aula as $clave => $alumnos): ?>
                    <?php list($aula, $seccion) = explode('|', $clave); ?>
                    <div class="card aula-card" 
                         data-aula="<?= htmlspecialchars($aula) ?>" 
                         data-seccion="<?= htmlspecialchars($seccion) ?>" 
                         data-alumnos='<?= json_encode($alumnos) ?>'>
                        <h3><i class="fas fa-door-open"></i> <?= htmlspecialchars($aula) ?> <?= htmlspecialchars($seccion) ?> (<?= count($alumnos) ?> alumnos)</h3>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="cards-container main-view" style="text-align: center;">
                <?php foreach ($niveles as $nivel): ?>
                    <div class="card" onclick="window.location.href='grade.php?nivel=<?= urlencode($nivel) ?>'">
                        <h3><?= htmlspecialchars($nivel) ?></h3>
                        <i class="fas fa-school fa-3x"></i>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<!-- Modal -->
<div id="modal" class="modal hidden">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3 id="modal-title"></h3>
        <table id="alumnos-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Aula</th>
                    <th>Sección</th>
                    <th>Fecha Ingreso</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<script>
// Modal funcionalidad
document.querySelectorAll('.aula-card').forEach(card => {
    card.addEventListener('click', () => {
        const aula = card.dataset.aula;
        const seccion = card.dataset.seccion;
        const alumnos = JSON.parse(card.dataset.alumnos);
        document.getElementById('modal-title').textContent = `${aula} - Sección ${seccion}`;
        const tbody = document.querySelector('#alumnos-table tbody');
        tbody.innerHTML = '';
        alumnos.forEach(al => {
            tbody.innerHTML += `
                <tr>
                    <td>${al.id}</td>
                    <td>${al.nombre}</td>
                    <td>${al.aula}</td>
                    <td>${al.seccion}</td>
                    <td>${al.fecha_ingreso}</td>
                    <td><span class="badge ${al.estado.toLowerCase() === 'activo' ? 'bg-success' : 'bg-danger'}">${al.estado}</span></td>
                </tr>
            `;
        });
        document.getElementById('modal').classList.remove('hidden');
    });
});
document.querySelector('.close').addEventListener('click', () => {
    document.getElementById('modal').classList.add('hidden');
});
</script>
</body>
</html>
