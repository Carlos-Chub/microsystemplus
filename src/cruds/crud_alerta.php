<?php
include '../funcphp/func_gen.php';
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
date_default_timezone_set('America/Guatemala');
$hoy = date("Y-m-d");
$hoy2 = date("Y-m-d H:i:s");

$condi = $_POST["condi"];

switch ($condi) {
    case 'proceIVE': //Aprovar alerta del ive o denegar proceso
        $dato = $_POST['datos'];
        $codusu = $_POST['archivo'];

        // echo json_encode(['La petición fue aprobada ', '1']);
        // return; 

        $infoPro = '';
        // $consulta = mysqli_query($conexion, "SELECT MAX(proceso) AS pro FROM  `tb_alerta` WHERE `fecha` = '$hoy'");
        $consulta = mysqli_query($conexion, "SELECT MAX(proceso) AS pro FROM  `tb_alerta` WHERE `id` = '$dato[0]'");
        $fila =  mysqli_affected_rows($conexion);
        if ($fila > 0) {
            $datoAlerta = mysqli_fetch_assoc($consulta);
            $infoPro = $datoAlerta['pro'];
            if($infoPro == 'EP1')$dato[1] = 'A1';
        }

        $consulta = mysqli_query($conexion, "UPDATE tb_alerta SET `proceso` = '$dato[1]', `estado` = 0, `updated_by` = '$codusu[0]', `updated_at` = '$hoy2' WHERE `id` = '$dato[0]'");

        if(mysqli_error($conexion) || !$consulta){
            echo json_encode(['Error … !!!,  comunicarse con soporte. ', '0']);
            return;
        }

        echo json_encode(['La petición fue aprobada ', '1']);
        return; 

        break;

    case '':
        break;
}
