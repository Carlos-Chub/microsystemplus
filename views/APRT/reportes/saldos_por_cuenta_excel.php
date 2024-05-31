<?php
session_start();
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');

require "../../../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

$queryins = mysqli_query($conexion, "SELECT * FROM clhpzzvb_bd_general_coopera.info_coperativa ins
INNER JOIN tb_agencia ag ON ag.id_institucion=ins.id_cop where ag.id_agencia=" . $_SESSION['id_agencia']);
$info[] = [];
$j = 0;
while ($fil = mysqli_fetch_array($queryins)) {
    $info[$j] = $fil;
    $j++;
}

$oficina = utf8_decode($info[0]["nom_agencia"]);
$institucion = utf8_decode($info[0]["nomb_comple"]);
$direccionins = utf8_decode($info[0]["muni_lug"]);
$emailins = $info[0]["emai"];
$telefonosins = $info[0]["tel_1"] . '   ' . $info[0]["tel_2"];;
$nitins = $info[0]["nit"];
$rutalogomicro = "../../../includes/img/logomicro.png";
$rutalogoins = "../../.." . $info[0]["log_img"];
$usuario = $_SESSION['id'];


// $oficina = "Coban";
// $institucion = "Cooperativa Integral De Ahorro y credito Imperial";
// $direccionins = "Canton vipila zona 1";
// $emailins = "fape@gmail.com";
// $telefonosins = "502 43987876";
// $nitins = "1323244234";
// $usuario = "9999";

// $rutalogomicro = "../../../includes/img/logomicro.png";
// $rutalogoins = "../../../includes/img/fape.jpeg";

//se crea el array que recibe los datos
$datos = array();
$datos = $_POST["data"];

$estado = $datos[6];
$tipo = $datos[7];
$fecha_final = $datos[5];
$usuario = $datos[8];
$oficina = $datos[9];

$hoy = date("Y-m-d H:i:s");

$fuente_encabezado = "Arial";
$fuente = "Courier";
$tamanioFecha = 9; //tamaño de letra de la fecha y usuario
$tamanioEncabezado = 14; //tamaño de letra del encabezado
$tamanioTabla = 11; //tamaño de letra de la fecha y usuario
$tamanio_linea = 4; //altura de la linea/celda
$ancho_linea = 30; //anchura de la linea/celda
$espacio_blanco = 10; //tamaño del espacio en blanco entre cada celda
$ancho_linea2 = 20; //anchura de la linea/celda
$espacio_blanco2 = 4; //tamaño del espacio en blanco entre cada celda

//-----------RELACIONADO CON LAS PROPIEDADES DEL ARCHIVO----------------------------
$spread = new Spreadsheet();
$spread
    ->getProperties()
    ->setCreator("MICROSYSTEM")
    ->setLastModifiedBy('MICROSYSTEM')
    ->setTitle('Reporte')
    ->setSubject('Saldos por cuenta con fecha')
    ->setDescription('Este reporte fue generado por el sistema MICROSYSTEM')
    ->setKeywords('PHPSpreadsheet')
    ->setCategory('Excel');
//-----------RELACIONADO CON LAS PROPIEDADES DEL ARCHIVO----------------------------

//-----------RELACIONADO CON EL ENCABEZADO----------------------------
# Como ya hay una hoja por defecto, la obtenemos, no la creamos
$hojaReporte = $spread->getActiveSheet();
$hojaReporte->setTitle("Reporte");

//insertarmos la fecha y usuario
$hojaReporte->setCellValue("A1", $hoy);
$hojaReporte->setCellValue("A2", $usuario);
// //informacion de la agencia o cooperativa
$hojaReporte->setCellValue("A4", $institucion);
$hojaReporte->setCellValue("A5", $direccionins);
$hojaReporte->setCellValue("A6", "Email: " . $emailins);
$hojaReporte->setCellValue("A7", "Tel: " . $telefonosins);
$hojaReporte->setCellValue("A8", "NIT: " . $nitins);

//hacer pequeño las letras de la fecha, definir arial como tipo de letra
$hojaReporte->getStyle("A1:E1")->getFont()->setSize($tamanioFecha)->setName($fuente_encabezado);
$hojaReporte->getStyle("A2:E2")->getFont()->setSize($tamanioFecha)->setName($fuente_encabezado);
//centrar el texto de la fecha
$hojaReporte->getStyle("A1:E1")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$hojaReporte->getStyle("A2:E2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// //hacer grande las letras del encabezado
$hojaReporte->getStyle("A4:E4")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
$hojaReporte->getStyle("A5:E5")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
$hojaReporte->getStyle("A6:E6")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
$hojaReporte->getStyle("A7:E7")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
$hojaReporte->getStyle("A8:E8")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
//centrar el texto del encabezado
$hojaReporte->getStyle("A4:E4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$hojaReporte->getStyle("A5:E5")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$hojaReporte->getStyle("A6:E6")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$hojaReporte->getStyle("A7:E7")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$hojaReporte->getStyle("A8:E8")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

//hacer pequeño las letras del encabezado de titulo
$hojaReporte->getStyle("A10:E10")->getFont()->setSize($tamanioTabla)->setName($fuente);
$hojaReporte->getStyle("A11:E11")->getFont()->setSize($tamanioTabla)->setName($fuente);
$hojaReporte->getStyle("A12:E12")->getFont()->setSize($tamanioTabla)->setName($fuente);

//centrar los encabezado de la tabla
$hojaReporte->getStyle("A10:E10")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$hojaReporte->getStyle("A11:E11")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$hojaReporte->getStyle("A12:E12")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

//titulo del reporte
// consultar los estados
$texto_cuentas = "";
$data_cuentas = mysqli_query($conexion, "SELECT `nombre` FROM `aprtip` WHERE `id_tipo`=$tipo");
while ($rowcuentas = mysqli_fetch_array($data_cuentas, MYSQLI_ASSOC)) {
    $texto_cuentas = strtoupper($rowcuentas["nombre"]);
}

$texto_estado = "ACTIVOS E INACTIVOS";
if ($estado == 'A') {
    $texto_estado = "ACTIVOS";
} else if ($estado == 'B') {
    $texto_estado = "INACTIVOS";
}

$hojaReporte->setCellValue("A10", "REPORTE DE SALDO DE CUENTAS DE " . strtoupper($texto_cuentas));
$hojaReporte->setCellValue("A11", strtoupper($texto_estado));

// //combinacion de celdas
$hojaReporte->mergeCells('A1:E1');
$hojaReporte->mergeCells('A2:E2');
$hojaReporte->mergeCells('A4:E4');
$hojaReporte->mergeCells('A5:E5');
$hojaReporte->mergeCells('A6:E6');
$hojaReporte->mergeCells('A7:E7');
$hojaReporte->mergeCells('A8:E8');
$hojaReporte->mergeCells('A10:E10');
$hojaReporte->mergeCells('A11:E11');

# Escribir encabezado de la tabla
$encabezado_tabla = ["CUENTA", "NOMBRE COMPLETO", "FECHA", "SALDO", "TASA"];
# El último argumento es por defecto A1 pero lo pongo para que se explique mejor
$hojaReporte->fromArray($encabezado_tabla, null, 'A13')->getStyle('A13:E13')->getFont()->setName($fuente)->setBold(true);

//realizar la consulta para encontrar el ccodtip mediante el id_tipo
$data_tip = mysqli_query($conexion, "SELECT `ccodtip` FROM `aprtip` WHERE `id_tipo`=$tipo");
while ($rowcuentas = mysqli_fetch_array($data_tip, MYSQLI_ASSOC)) {
    $ccodtip = strtoupper($rowcuentas["ccodtip"]);
}

//ingreso de los datos de tabla
$consulta = "SELECT cta.ccodaport, cl.short_name, calcular_saldo_apr_tipcuenta(cta.ccodaport,'$fecha_final') AS saldo, cta.tasa, cl.genero
FROM aprtip AS tp
INNER JOIN aprcta AS cta ON tp.ccodtip=cta.ccodtip
INNER JOIN tb_cliente AS cl ON cta.ccodcli=cl.idcod_cliente
WHERE tp.ccodtip='$ccodtip'";

if ($estado != "0") {
    $consulta .= " AND cta.estado='$estado'";
}
$consulta .= " ORDER BY cta.ccodaport ASC";

//se hace la consulta segun los tipos de filtro
$linea = 14;
$data2 = mysqli_query($conexion, $consulta);
cargar_datos($hojaReporte, $data2, $linea, $fuente, $fecha_final);

//-----------RELACIONADO CON LA BAJADA O DESCARGA DEL ARCHIVO----------------------------
//crea el archivo para que se descarge
ob_start();
$writer = IOFactory::createWriter($spread, 'Xlsx');
$writer->save("php://output");
$xlsData = ob_get_contents();
ob_end_clean();
//envio de repuesta a ajax para descargarlos
$opResult = array(
    'status' => 1,
    'data' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
);
mysqli_close($conexion);
echo json_encode($opResult);
exit;


//funcion para cargar todos los datos al archivo de excel
function cargar_datos($hojaReporte, $data, $linea, $fuente, $fecha_final)
{
    $total_saldo = 0;
    $conta_mujeres = 0;
    $conta_hombres = 0;
    $total_mujeres = 0;
    $total_hombres = 0;
    while ($rowdata = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
        $bd_ccodaport = $rowdata["ccodaport"];
        $bd_shortname = strtoupper($rowdata["short_name"]);
        $bd_fecha = $fecha_final;
        $bd_saldo = strtoupper($rowdata["saldo"]);
        $bd_tasa = strtoupper($rowdata["tasa"]);
        $bd_genero = strtoupper($rowdata["genero"]);
        $saldo_formateado = number_format($bd_saldo, 2, '.', '');
        $tasa_formateado = number_format($bd_tasa, 2, '.', '');

        $total_saldo = $total_saldo + $bd_saldo;
        if ($bd_genero == "M") {
            $total_hombres = $total_hombres + $bd_saldo;
            $conta_hombres++;
        } else {
            $total_mujeres = $total_mujeres + $bd_saldo;
            $conta_mujeres++;
        }

        // colocar formato de moneda
        $hojaReporte->getStyle('D' . $linea . ':E' . $linea)->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);
        //se insertan los datos
        $hojaReporte->setCellValueByColumnAndRow(1, $linea, $bd_ccodaport);
        $hojaReporte->setCellValueByColumnAndRow(2, $linea, $bd_shortname);
        $hojaReporte->setCellValueByColumnAndRow(3, $linea, $bd_fecha);
        $hojaReporte->setCellValueByColumnAndRow(4, $linea, $saldo_formateado);
        $hojaReporte->setCellValueByColumnAndRow(5, $linea, $tasa_formateado);
        $hojaReporte->getStyle("A" . $linea . ":E" . $linea)->getFont()->setName($fuente);

        $linea++;
    }
    $hojaReporte->getColumnDimension('A')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('B')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('C')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('D')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('E')->setAutoSize(TRUE);

    //TEXTO TOTAL
    $hojaReporte->getStyle('A' . $linea . ':E' . $linea)->getFont()->setName($fuente);
    $hojaReporte->getStyle('E' . $linea)->getFont()->setBold(true);
    $hojaReporte->getStyle('E' . $linea)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    //merge de totales
    $hojaReporte->setCellValueByColumnAndRow(4, $linea, $total_saldo);
    $hojaReporte->getStyle('E' . $linea)
        ->getNumberFormat()
        ->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);

    //RESUMEN DE SALDO
    //colocar letra courier a todos
    $hojaReporte->getStyle('A' . ($linea + 2) . ':E' . ($linea + 2))->getFont()->setName($fuente);
    $hojaReporte->getStyle('A' . ($linea + 3) . ':E' . ($linea + 3))->getFont()->setName($fuente);
    $hojaReporte->getStyle('A' . ($linea + 4) . ':E' . ($linea + 4))->getFont()->setName($fuente);
    //encabezado
    $hojaReporte->getStyle('B' . ($linea + 2) . ':C' . ($linea + 2))->getFont()->setBold(true);
    $hojaReporte->getStyle('B' . ($linea + 2) . ':C' . ($linea + 2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $hojaReporte->setCellValueByColumnAndRow(2, ($linea + 2), 'NUMERO');

    $hojaReporte->getStyle('B' . ($linea + 2) . ':C' . ($linea + 2))->getFont()->setBold(true);
    $hojaReporte->getStyle('B' . ($linea + 2) . ':C' . ($linea + 2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $hojaReporte->setCellValueByColumnAndRow(3, ($linea + 2), 'SALDO');
    //resumen mujeres
    $hojaReporte->getStyle('A' . ($linea + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    $hojaReporte->getStyle('B' . ($linea + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $hojaReporte->getStyle('C' . ($linea + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

    $hojaReporte->getStyle('C' . ($linea + 3))
        ->getNumberFormat()
        ->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);
    $hojaReporte->setCellValueByColumnAndRow(1, ($linea + 3), 'MUJERES: ');
    $hojaReporte->setCellValueByColumnAndRow(2, ($linea + 3), $conta_mujeres);
    $hojaReporte->setCellValueByColumnAndRow(3, ($linea + 3), $total_mujeres);
    //resumen hombres
    $hojaReporte->getStyle('A' . ($linea + 4))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    $hojaReporte->getStyle('B' . ($linea + 4))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $hojaReporte->getStyle('C' . ($linea + 4))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

    $hojaReporte->getStyle('C' . ($linea + 4))
        ->getNumberFormat()
        ->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);
    $hojaReporte->setCellValueByColumnAndRow(1, ($linea + 4), 'HOMBRES: ');
    $hojaReporte->setCellValueByColumnAndRow(2, ($linea + 4), $conta_hombres);
    $hojaReporte->setCellValueByColumnAndRow(3, ($linea + 4), $total_hombres);
}
