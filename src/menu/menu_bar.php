<?php
if (!isset($_SESSION)) {
    session_start();
}
// include_once '../includes/Config/config.php'; //COMENTADO PORQUE NO ES NECESARIO INCLUIRLO DE NUEVO
if (!isset($_SESSION['usu'])) {
    header('location: ' . BASE_URL);
} else {
    // include_once '../includes/BD_con/db_con.php'; //COMENTADO PORQUE NO ES NECESARIO INCLUIRLO DE NUEVO
    mysqli_set_charset($conexion, 'utf8');
    mysqli_set_charset($general, 'utf8');
    date_default_timezone_set('America/Guatemala');
    $rutalogomicro = '../includes/img/logomicro.png';
?>
    <!--  MENU PRINCIPAL PARA LOS MENUS DE CLIENTES, AHORROS, HOMEPAGE -->
    <nav class="sidebar">
        <header>
            <div class="image-text">
                <span class="image">
                    <img src="<?php echo $rutalogomicro; ?>" alt="">
                </span>

                <?php
                //Informacion de la entidad
                $consulta = mysqli_query($conexion, "SELECT cope.nomb_cor AS nomAge
                    FROM tb_agencia AS agen	
                    INNER JOIN tb_usuario AS usu ON agen.id_agencia = usu.id_agencia
                    INNER JOIN clhpzzvb_bd_general_coopera.info_coperativa AS cope ON agen.id_institucion = cope.id_cop
                    WHERE   usu.id_usu =" . $_SESSION["id"]);
                $infoEnti = mysqli_fetch_assoc($consulta);
                ?>

                <div class="text logo-text">
                    <span class="name">MICROSYSTEM </span>
                    <span class="profession"><?php echo utf8_decode($_SESSION['nombre']) . ' ' . utf8_decode($_SESSION['apellido']); ?></span>
                </div>
            </div>

            <i class='bx bx-chevron-right toggle'></i>
        </header>

        <div class="menu-bar">
            <div class="menu">
                <?php     //Cantidad de alerta //SE MODIFICO YA QUE NO VI QUE SE UTILIZARA LA CANTIDAD DE ALERTAS
                $query = "SELECT tipo_alerta FROM tb_alerta WHERE estado = 1 AND cod_aux = " . $_SESSION['id'] . ";";
                $consulta = mysqli_query($conexion, $query);
                $numRows = mysqli_num_rows($consulta);  //	$alerta = mysqli_affected_rows($conexion);
                /* VERIFICAR QUE EXISTAN ALERTAS DE PASS */

                ?>
                <!-- INI alertas -->
                <li class="d-flex justify-content-start">
                    <button type="button" class="btn btn-warning position-relative" onclick="abrirModal();"> Alerta
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="id_con_alt">

                        </span>
                    </button>
                    <!-- <button type="button" class="btn btn-warning position-relative" onclick="inyecCod();" data-bs-toggle="modal" data-bs-target="#myModal"> Alerta <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"> <span class="visually-hidden">unread messages</span> </span> </button> -->
                </li>
                <!-- FIN alerta -->

                <ul class="menu-links ps-3">
                    <li class="nav-link">
                        <a href="./">
                            <i class="fa-solid fa-house fa-xl" id="ico"></i>
                            <span class="text nav-text" id="txtmenu">Home</span>
                        </a>
                    </li>
                    <!-- impresion de opciones de clientes hasta aportaciones -->
                    <?php
                    $consulta = "SELECT tbp.id_usuario, tbs.id_modulo, tbo.descripcion, tbo.icon, tbo.ruta, tbo.rama FROM tb_usuario tbu
                    INNER JOIN tb_permisos2 tbp ON tbu.id_usu=tbp.id_usuario
                    INNER JOIN clhpzzvb_bd_general_coopera.tb_submenus tbm ON tbp.id_submenu=tbm.id
                    INNER JOIN clhpzzvb_bd_general_coopera.tb_menus tbs ON tbm.id_menu =tbs.id
                    INNER JOIN clhpzzvb_bd_general_coopera.tb_modulos tbo ON tbs.id_modulo =tbo.id
                    INNER JOIN clhpzzvb_bd_general_coopera.tb_permisos_modulos tbps ON tbo.id=tbps.id_modulo
                    WHERE tbu.id_usu=" . $_SESSION['id'] . " AND tbo.estado='1' AND tbs.estado='1' AND tbm.estado='1' AND tbps.estado='1' AND
                        tbps.id_cooperativa=(SELECT ag1.id_institucion FROM tb_agencia ag1 LIMIT 1) 
                    AND tbo.rama='G' AND tbo.id>0 AND tbo.id<4 GROUP BY tbo.id ORDER BY tbo.orden ASC";

                    $resultado = mysqli_query($conexion, $consulta);
                    while ($fila = mysqli_fetch_array($resultado, MYSQLI_ASSOC)) {
                        $descripcion = $fila['descripcion'];
                        $icon = $fila['icon'];
                        $ruta = $fila['ruta'];
                        echo '<li class="nav-link">
                        <a href="' . $ruta . '">
                        <i class="' . $icon . '" id="ico"></i>
                            <span class="text nav-text"  id="txtmenu">' . $descripcion . '</span>
                        </a>
                        </li>';
                    }
                    ?>
                    <!-- impresion de creditos -->
                    <?php
                    $fila2 = mysqli_fetch_array((mysqli_query($conexion, "SELECT COUNT(*) AS total FROM tb_usuario tbu
                    INNER JOIN tb_permisos2 tbp ON tbu.id_usu=tbp.id_usuario
                    INNER JOIN clhpzzvb_bd_general_coopera.tb_submenus tbm ON tbp.id_submenu=tbm.id
                    INNER JOIN clhpzzvb_bd_general_coopera.tb_menus tbs ON tbm.id_menu =tbs.id
                    INNER JOIN clhpzzvb_bd_general_coopera.tb_modulos tbo ON tbs.id_modulo =tbo.id
                    INNER JOIN clhpzzvb_bd_general_coopera.tb_permisos_modulos tbps ON tbo.id=tbps.id_modulo
                    WHERE tbu.id_usu=" . $_SESSION['id'] . " AND tbo.estado='1' AND tbs.estado='1' AND tbm.estado='1' AND tbps.estado='1' AND
                        tbps.id_cooperativa=(SELECT ag1.id_institucion FROM tb_agencia ag1 LIMIT 1) 
                    AND tbo.rama='R' ORDER BY tbo.orden, tbs.orden, tbm.orden ASC")), MYSQLI_ASSOC);

                    if ($fila2['total'] > 0) {
                        $fila2 = mysqli_fetch_array((mysqli_query($general, "SELECT * FROM tb_modulos tbo
                        WHERE tbo.id=4")), MYSQLI_ASSOC);
                        echo '<li class="nav-link">
                            <a href="' . $fila2['ruta'] . '">
                            <i class="' . $fila2['icon'] . '" id="ico"></i>
                            <span class="text nav-text" id="txtmenu">' . $fila2['descripcion'] . '</span>
                            </a>
                        </li>';
                    }
                    ?>
                    <!-- IMPRESION A PARTIR DE CONTABILIDAD -->
                    <!-- impresion de opciones de clientes hasta aportaciones -->
                    <?php
                    $consulta = "SELECT tbp.id_usuario, tbs.id_modulo, tbo.descripcion, tbo.icon, tbo.ruta, tbo.rama FROM tb_usuario tbu
                    INNER JOIN tb_permisos2 tbp ON tbu.id_usu=tbp.id_usuario
                    INNER JOIN clhpzzvb_bd_general_coopera.tb_submenus tbm ON tbp.id_submenu=tbm.id
                    INNER JOIN clhpzzvb_bd_general_coopera.tb_menus tbs ON tbm.id_menu =tbs.id
                    INNER JOIN clhpzzvb_bd_general_coopera.tb_modulos tbo ON tbs.id_modulo =tbo.id
                    INNER JOIN clhpzzvb_bd_general_coopera.tb_permisos_modulos tbps ON tbo.id=tbps.id_modulo
                    WHERE tbu.id_usu=" . $_SESSION['id'] . " AND tbo.estado='1' AND tbs.estado='1' AND tbm.estado='1' AND tbps.estado='1' AND
                        tbps.id_cooperativa=(SELECT ag1.id_institucion FROM tb_agencia ag1 LIMIT 1) 
                    AND tbo.rama='G' AND tbo.orden>4 GROUP BY tbo.id ORDER BY tbo.orden ASC";

                    $resultado = mysqli_query($conexion, $consulta);
                    while ($fila = mysqli_fetch_array($resultado, MYSQLI_ASSOC)) {
                        $descripcion = $fila['descripcion'];
                        $icon = $fila['icon'];
                        $ruta = $fila['ruta'];
                        echo '<li class="nav-link">
                        <a href="' . $ruta . '">
                        <i class="' . $icon . '" id="ico"></i>
                            <span class="text nav-text"  id="txtmenu">' . $descripcion . '</span>
                        </a>
                        </li>';
                    }
                    ?>
                    <!-- Impresion de boton de admin -->
                    <?php
                    $fila2 = mysqli_fetch_array((mysqli_query($conexion, "SELECT COUNT(*) AS total FROM tb_usuario tbu
                    INNER JOIN tb_permisos2 tbp ON tbu.id_usu=tbp.id_usuario
                    INNER JOIN clhpzzvb_bd_general_coopera.tb_submenus tbm ON tbp.id_submenu=tbm.id
                    INNER JOIN clhpzzvb_bd_general_coopera.tb_menus tbs ON tbm.id_menu =tbs.id
                    INNER JOIN clhpzzvb_bd_general_coopera.tb_modulos tbo ON tbs.id_modulo =tbo.id
                    INNER JOIN clhpzzvb_bd_general_coopera.tb_permisos_modulos tbps ON tbo.id=tbps.id_modulo
                    WHERE tbu.id_usu=" . $_SESSION['id'] . " AND tbo.estado='1' AND tbs.estado='1' AND tbm.estado='1' AND tbps.estado='1' AND
                        tbps.id_cooperativa=(SELECT ag1.id_institucion FROM tb_agencia ag1 LIMIT 1) 
                    AND tbo.rama='A' ORDER BY tbo.orden, tbs.orden, tbm.orden ASC")), MYSQLI_ASSOC);

                    if ($fila2['total'] > 0) {
                        $fila2 = mysqli_fetch_array((mysqli_query($general, "SELECT * FROM tb_modulos tbo
                        WHERE tbo.id=11")), MYSQLI_ASSOC);
                        echo '<li class="nav-link">
                            <a href="' . $fila2['ruta'] . '">
                            <i class="' . $fila2['icon'] . '" id="ico"></i>
                            <span class="text nav-text" id="txtmenu">' . $fila2['descripcion'] . '</span>
                            </a>
                        </li>';
                    }
                    ?>
                </ul>
            </div>

            <div class="bottom-content">
                <li class="">
                    <a id="eliminarsesion" style="cursor: pointer;">
                        <i class='bx bx-log-out icon'></i>
                        <span class="text nav-text">Cerrar sesión</span>
                    </a>
                </li>

                <li class="mode">
                    <div class="sun-moon">
                        <i class='bx bx-moon icon moon'></i>
                        <i class='bx bx-sun icon sun'></i>
                    </div>
                    <span class="mode-text text">Modo Oscuro</span>

                    <div class="toggle-switch" onclick="active_modo()">
                        <span class="switch"></span>
                    </div>
                </li>

            </div>
        </div>
    </nav>
<?php
}
?>

<!-- **************************************ALERTAS -->
<div class="class" id="tbAlerta"></div>