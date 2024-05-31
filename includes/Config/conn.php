<?php
include '../../includes/Config/database.php';
$database = new Database($db_host, $db_name, $db_user, $db_password, $db_name_general);
try {
    //PRUEBAS Y EJEMPLOS DE USO
    $database->openConnection();
    $rows = $database->selectAll('ctb_fuente_fondos');
    echo "<pre>";
    echo print_r($rows);
    echo "</pre>";

    $rows = $database->selectById('ctb_fuente_fondos',1);
    echo "<pre>";
    echo print_r($rows);
    echo "</pre>";
    
    $result = $database->selectColumns('tb_cliente', ['short_name', 'direccion', 'no_identifica'], 'estado=?', [1]);
    echo "<pre>";
    echo print_r($result);
    echo "</pre>";

    $query = $database->joinQuery(
        ['cremcre_meta'], // Tablas a unir
        ['tb_cliente.short_name', 'tb_cliente.no_identifica', 'cremcre_meta.ccodcta', 'cremcre_meta.NCapDes'], // Columnas a seleccionar
        [
            ['type' => 'INNER', 'table' => 'tb_cliente', 'condition' => 'tb_cliente.idcod_cliente = cremcre_meta.CodCli', 'next_condition' => 'cremcre_meta.TipoEnti = "INDI"']
        ], // Joins
        'tb_cliente.estado = ? AND cremcre_meta.Cestado = ?', // Condición
        [1, 'F'] // Parámetros
    );

    echo "<pre>";
    echo print_r($query);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    $database->closeConnection();
}
