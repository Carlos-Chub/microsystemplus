<?php
require_once(__DIR__ . '/../../vendor/autoload.php');
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$db_host = $_ENV['DDBB_HOST'];
$db_user = $_ENV['DDBB_USER'];
$db_password = $_ENV['DDBB_PASSWORD'];
$db_name = $_ENV['DDBB_NAME'];
$db_name_general = $_ENV['DDBB_NAME_GENERAL'];
$type_host = $_ENV['BANDERA'];
$type_timezone = $_ENV['BANDERA_TIMEZONE'];

$key1 = $_ENV['MYKEYPASS'];
$key2 = $_ENV['MYKEYALEATORIO'];
try {
    $conexion = mysqli_connect($db_host, $db_user, $db_password, $db_name);
    if (mysqli_connect_errno()) {
        exit();
        echo 'Hay problemas con la red. Si el percance persiste, comunicarse con el área de soporte técnico. ';
    }
    if ($type_timezone == '1') {
        $conexion->query("SET time_zone = 'America/Guatemala'");
    }
    $general = mysqli_connect($db_host, $db_user, $db_password, $db_name_general);
    if (mysqli_connect_errno()) {
        exit();
        echo 'Hay problemas con la red. Si el percance persiste, comunicarse con el área de soporte técnico. ';
    }
    if ($type_timezone == '1') {
        $general->query("SET time_zone = 'America/Guatemala'");
    }
} catch (Exception $e) {
    echo 'Hay problemas con la red. Si el percance persiste, comunicarse con el área de soporte técnico. ';
}
