<?php
if (!isset($_SESSION)) {
    session_start();
}
include_once '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
date_default_timezone_set('America/Guatemala');

if (!isset($_SESSION['usu'])) {
    header('location: ' . BASE_URL);
} else
?>
<nav class="sidebar ">
    <header>
        <div class="image-text">

            <div class="text logo-text">
                <span class="name">MICROSYSTEM</span>
                <span class="profession"><?php echo utf8_decode($_SESSION['nombre']) . ' ' . utf8_decode($_SESSION['apellido']); ?></span>
            </div>
        </div>

        <i class='bx bx-chevron-right toggle'></i>
    </header>

    <div class="menu-bar">
        <div class="menu">
            <ul class="menu-links" style="padding-left: 0px !important;">
                <li class="nav-link">
                    <a href="./creditos.php">
                        <i class="fa-solid fa-house fa-xl" id="ico2"></i>
                        <span class="text nav-text" id="txtmenu">Home</span>
                    </a>
                </li>

                <!-- IMPRESION DE OPCIONES DE ADMIN -->
                <?php
                $consulta = "SELECT tbp.id_usuario, tbs.id_modulo, tbo.descripcion, tbo.icon, tbo.ruta, tbo.rama FROM tb_usuario tbu
                INNER JOIN tb_permisos2 tbp ON tbu.id_usu=tbp.id_usuario
                INNER JOIN clhpzzvb_bd_general_coopera.tb_submenus tbm ON tbp.id_submenu=tbm.id
                INNER JOIN clhpzzvb_bd_general_coopera.tb_menus tbs ON tbm.id_menu =tbs.id
                INNER JOIN clhpzzvb_bd_general_coopera.tb_modulos tbo ON tbs.id_modulo =tbo.id
                INNER JOIN clhpzzvb_bd_general_coopera.tb_permisos_modulos tbps ON tbo.id=tbps.id_modulo
                WHERE tbu.id_usu=" . $_SESSION['id'] . " AND tbo.estado='1' AND tbs.estado='1' AND tbm.estado='1' AND tbps.estado='1' AND
                    tbps.id_cooperativa=(SELECT ag1.id_institucion FROM tb_agencia ag1 LIMIT 1) 
                AND tbo.rama='R' GROUP BY tbo.id ORDER BY tbo.orden, tbs.orden, tbm.orden ASC";

            $resultado = mysqli_query($conexion, $consulta);
            while ($fila = mysqli_fetch_array($resultado, MYSQLI_ASSOC)) {
                $descripcion = $fila['descripcion'];
                $icon = $fila['icon'];
                $ruta = $fila['ruta'];
                echo '<li class="nav-link">
                        <a href="' . $ruta . '">
                        <i class="' . $icon . '" id="ico2"></i>
                            <span class="text nav-text"  id="txtmenu">' . $descripcion . '</span>
                        </a>
                        </li>';
            }
                ?>
            </ul>
        </div>

        <div class="bottom-content">
            <li class="">
                <a id="eliminarsesion2" style="cursor: pointer;">
                    <i class='bx bx-log-out icon'></i>
                    <span class="text nav-text">Cerrar sesión</span>
                </a>
            </li>
            <li class="">
                <a href="../index.php">
                    <i class='bx bx-log-out icon'></i>
                    <span class="text nav-text">INICIO</span>
                </a>
            </li>

            <li class="mode">
                <div class="sun-moon">
                    <i class='bx bx-moon icon moon'></i>
                    <i class='bx bx-sun icon sun'></i>
                </div>
                <span class="mode-text text">Modo Oscuro</span>

                <div class="toggle-switch" onclick="active_modo(1,'../../')">
                    <span class="switch"></span>
                </div>
            </li>

        </div>
    </div>
</nav>