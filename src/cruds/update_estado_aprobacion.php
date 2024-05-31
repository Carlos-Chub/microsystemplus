<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');
// include '../../src/funcphp/func_gen.php';
include '../../src/funcphp/fun_ppg.php';
date_default_timezone_set('America/Guatemala');
$hoy2 = date("Y-m-d H:i:s");
$hoy = date("Y-m-d");
$ccodcta = isset($_POST['ccodcta']) ? $_POST['ccodcta'] : '';

$codcreditoupdate = mysqli_real_escape_string($conexion, $_POST["ccodcta"]);


$update_estado  = "SET @codcreditoupdate = '$codcreditoupdate';
    UPDATE cremcre_meta cm3 SET cm3.Cestado = 'D' WHERE cm3.CCODCTA COLLATE utf8mb4_general_ci = @codcreditoupdate;
    DELETE FROM CREDKAR WHERE CCODCTA COLLATE utf8mb4_general_ci = @codcreditoupdate;
    DELETE FROM Cre_ppg WHERE ccodcta COLLATE utf8mb4_general_ci = @codcreditoupdate;
    DELETE FROM ctb_mov WHERE id_ctb_diario IN (SELECT cd.id FROM ctb_diario cd WHERE cd.cod_aux COLLATE utf8mb4_general_ci = @codcreditoupdate);
    DELETE FROM ctb_chq WHERE id_ctb_diario IN (SELECT cd.id FROM ctb_diario cd WHERE cd.cod_aux COLLATE utf8mb4_general_ci = @codcreditoupdate);
    DELETE FROM ctb_diario WHERE cod_aux COLLATE utf8mb4_general_ci = @codcreditoupdate;";


if (mysqli_multi_query($conexion , $update_estado)) {
} else {
    echo "Error al ejecutar la consulta: update_estado_aprobacion " . mysqli_error($conexion);
}


mysqli_multi_query($conexion, $update_estado);

mysqli_close($conexion);
?>