<?php
session_start();
include '../../includes/BD_con/db_con.php';
//utf8mb4 solucion de error ":"1","message":"Error al ejecutar la consulta: COLLATION 'utf8mb4_general_ci' is not valid for CHARACTER SET 'utf8mb3'"}
mysqli_set_charset($conexion, 'utf8mb4');
mysqli_set_charset($general, 'utf8');
include '../../src/funcphp/fun_ppg.php';

date_default_timezone_set('America/Guatemala');
$hoy2 = date("Y-m-d H:i:s");
$hoy = date("Y-m-d");

$idgrup = isset($_POST['idgrup']) ? $_POST['idgrup'] : '';
$NCiclo = isset($_POST['NCiclo']) ? $_POST['NCiclo'] : '';

    $idgrup =mysqli_real_escape_string($conexion, $_POST["idgrup"]);
    $NCiclo =mysqli_real_escape_string ($conexion, $_POST["NCiclo"]);

    $sql_queries = "
        SET @codgrupo ='$idgrup';
        SET @ciclogrupo ='$NCiclo';
        UPDATE cremcre_meta cm3 SET cm3.Cestado = 'D' WHERE cm3.CCodGrupo COLLATE utf8mb4_general_ci = @codgrupo AND cm3.NCiclo COLLATE utf8mb4_general_ci = @ciclogrupo;
        DELETE FROM CREDKAR WHERE CCODCTA IN (SELECT cm.CCODCTA FROM cremcre_meta cm WHERE cm.CCodGrupo COLLATE utf8mb4_general_ci = @codgrupo AND cm.NCiclo COLLATE utf8mb4_general_ci = @ciclogrupo);
        DELETE FROM Cre_ppg  WHERE ccodcta IN (SELECT cm.CCODCTA FROM cremcre_meta cm WHERE cm.CCodGrupo COLLATE utf8mb4_general_ci = @codgrupo AND cm.NCiclo COLLATE utf8mb4_general_ci = @ciclogrupo);
        DELETE FROM ctb_mov WHERE id_ctb_diario  IN (SELECT cd.id FROM ctb_diario cd INNER JOIN cremcre_meta cm ON cd.cod_aux = cm.CCODCTA WHERE cm.CCodGrupo COLLATE utf8mb4_general_ci = @codgrupo AND cm.NCiclo COLLATE utf8mb4_general_ci = @ciclogrupo);
        DELETE FROM ctb_chq WHERE id_ctb_diario  IN (SELECT cd.id FROM ctb_diario cd INNER JOIN cremcre_meta cm ON cd.cod_aux = cm.CCODCTA WHERE cm.CCodGrupo COLLATE utf8mb4_general_ci = @codgrupo AND cm.NCiclo COLLATE utf8mb4_general_ci = @ciclogrupo); 
        DELETE FROM ctb_diario WHERE cod_aux IN (SELECT cm.CCODCTA FROM cremcre_meta cm WHERE cm.CCodGrupo COLLATE utf8mb4_general_ci = @codgrupo AND cm.NCiclo COLLATE utf8mb4_general_ci = @ciclogrupo);
    ";

    $queries = explode(';', $sql_queries);

   
foreach ($queries as $query) {
    if (!empty($query)) {
        if (mysqli_query($conexion, $query) === false) {
            $response = array(
                'status' => '0',
                'message' => 'finish: ' . mysqli_error($conexion)
            );
            echo json_encode($response);
            mysqli_close($conexion);
            exit();
        }
    }
}

    header('Content-Type: application/json');
    //  respuesta  JSON
    echo json_encode($response);
    mysqli_close($conexion);
    exit();
?>
