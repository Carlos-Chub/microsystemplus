<?php 
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require '../../includes/BD_con/db_con.php';

// Obtener token de la cabecera
// $token = getallheaders()['Authorization']; 

$query = "SELECT tc.idcod_cliente, tc.short_name FROM tb_cliente tc
WHERE tc.estado = '1'
LIMIT 10;"; // Agregamos el punto y coma al final de la línea

if ($is_query_run = mysqli_query($conexion, $query)) {
    $userData = [];
    while ($query_executed = mysqli_fetch_assoc($is_query_run)) {
        $userData[] = $query_executed;
    }
} else {
    echo "Error en la ejecución de la consulta!";
}
echo json_encode($userData);