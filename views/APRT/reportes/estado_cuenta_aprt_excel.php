<?php
session_start();
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../../src/funcphp/func_gen.php';
require '../../../fpdf/fpdf.php';
date_default_timezone_set('America/Guatemala');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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

//se crea el array y se reciben los datos del post
$datos = array();
$datos = $_POST["data"];

//se asignan variables locales a los datos recibidos
$ccodaho = $datos[5];
$r_fecha = $datos[8];
$fechainicial = $datos[6];
$fechafinal = $datos[7];
$usuario = $datos[9];
$oficina = $datos[10];
$tip_report = $datos[2];


$hoy = date("Y-m-d H:i:s");
$fuente_encabezado = "Arial";
$fuente = "Courier";
$tamanioFecha = 9; //tamaño de letra de la fecha y usuario
$tamanioEncabezado = 14; //tamaño de letra del encabezado
$tamanioTabla = 11; //tamaño de letra de la fecha y usuario
$tamanio_linea = 4; //altura de la linea/celda
$ancho_linea = 25; //anchura de la linea/celda
$espacio_blanco = 10; //tamaño del espacio en blanco entre cada celda
$ancho_linea2 = 20; //anchura de la linea/celda
$espacio_blanco2 = 4; //tamaño del espacio en blanco entre cada celda
$linea = 14;


//CONSULTA A LA BASE DE DATOS
$consulta = "SELECT * FROM ahommov WHERE cestado!=2 AND ccodaho = " . $ccodaho;
$consulta2 = "CALL obtener_saldo_ant_fecha_aho('$ccodaho', '$fechainicial')";
$data3 = "";
$data = "";




if ($r_fecha == '2') {
    //se hace la consulta del saldo anterior
    $data3 = mysqli_query($conexion, $consulta2);
    mysqli_next_result($conexion);

    //se hace la consulta entre fechas
    $consulta .= " AND (ahommov.dfecope BETWEEN '$fechainicial' AND '$fechafinal')";
}
$consulta .= " ORDER BY ahommov.correlativo ASC";
$data = mysqli_query($conexion, $consulta);
$data2 = mysqli_query($conexion, $consulta);


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

$hojaReporte->setCellValue("A10", "ESTADO DE CUENTA " );

// //combinacion de celdas
$hojaReporte->mergeCells('A1:J1');
$hojaReporte->mergeCells('A2:J2');
$hojaReporte->mergeCells('A4:J4');
$hojaReporte->mergeCells('A5:J5');
$hojaReporte->mergeCells('A6:J6');
$hojaReporte->mergeCells('A7:J7');
$hojaReporte->mergeCells('A8:J8');
$hojaReporte->mergeCells('A10:J10');
$hojaReporte->mergeCells('A11:J11');

# Escribir encabezado de la tabla
$encabezado_tabla = ["FECHA", "NO.", "D/R", "DOC", "TIPO", "CREDITOS", "DEBITOS", "CHEQUE", "PARTIDA", "SALDO"];
$hojaReporte->fromArray($encabezado_tabla, null, 'A13')->getStyle('A13:E13')->getFont()->setName($fuente)->setBold(true);

$contador = 0;
//CONSULTA A LA BASE DE DATOS
$consulta = "SELECT * FROM aprmov WHERE ccodaport = " . $ccodaho;
$consulta2 = "CALL obtener_saldo_ant_fecha('$ccodaho', '$fechainicial')";
$data3 = "";
$data2 = "";


if ($r_fecha == '2') {
    //se hace la consulta del saldo anterior
    $data3 = mysqli_query($conexion, $consulta2);
    mysqli_next_result($conexion);

    //se hace la consulta entre fechas
    $consulta .= " AND (aprmov.dfecope BETWEEN '$fechainicial' AND '$fechafinal')";
}
$consulta .= " ORDER BY aprmov.correlativo ASC";

$data2 = mysqli_query($conexion, $consulta);
cargar_datos($data2, $tamanio_linea, $data3, $r_fecha,$hojaReporte, $linea, $fuente);
$contador = 0;

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
//-----------RELACIONADO CON LA BAJADA O DESCARGA DEL ARCHIVO----------------------------


//funcion para cargar todos los datos al archivo de excel
function cargar_datos($data2, $tamanio_linea, $data3, $r_fecha,$hojaReporte, $linea, $fuente)
{
    $total_dep = 0;
    $total_ret = 0;
    $saldo = 0;
    $TOTALsaldo = 0.00;
    while ($rowdata = mysqli_fetch_array($data2, MYSQLI_ASSOC)) {
        $fech = $rowdata["dfecope"];
        $num = $rowdata["correlativo"];
        $tipope = $rowdata['ctipope'];
        $numdoc = $rowdata['cnumdoc'];
        $tipdoc = utf8_encode($rowdata['ctipdoc']);
        $ncheque = $rowdata['nrochq'];
        $tipchq = $rowdata['tipchq'];
        $partida = $rowdata['numpartida'];
        $monto = $rowdata['monto'];
       
        //colocar formato de moneda
        $hojaReporte->getStyle('F' . $linea . ':G' . $linea)
            ->getNumberFormat()
            ->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);
    // colocar formato de moneda
    $hojaReporte->getStyle('F' . $linea . ':J' . $linea)->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);

    //se insertan los datos
    $hojaReporte->setCellValueByColumnAndRow(1, $linea, $fech);
    $hojaReporte->setCellValueByColumnAndRow(2, $linea, $num);
    $hojaReporte->setCellValueByColumnAndRow(3, $linea, $tipope);
    $hojaReporte->setCellValueByColumnAndRow(4, $linea, $numdoc);
    $hojaReporte->setCellValueByColumnAndRow(5, $linea, $tipdoc);

    $hojaReporte->setCellValueByColumnAndRow(6, $linea, $saldo);

     if ($tipope == "D") {
         $hojaReporte->setCellValueByColumnAndRow(6, $linea, $monto);
     } elseif ($tipope == "R") {
         $hojaReporte->setCellValueByColumnAndRow(7, $linea, $monto);
     }
     ($tipope == "D") ? $saldo = $saldo + $monto : $saldo;
     ($tipope == "R") ? $saldo = $saldo - $monto : $saldo;

        $hojaReporte->getStyle("A" . $linea . ":G" . $linea)->getFont()->setName($fuente);
        $hojaReporte->setCellValueByColumnAndRow(10, $linea, $saldo);
        $hojaReporte->getStyle("A" . $linea . ":F" . $linea)->getFont()->setName($fuente);
        $linea++;

// Sumar todos los saldos en la fila 10
$hojaReporte->setCellValueByColumnAndRow(11, 10, 'Q' . number_format($TOTALsaldo, 2));
$hojaReporte->getStyle("A10:K10")->getFont()->setName($fuente);

    }


    $hojaReporte->getColumnDimension('A')->setWidth(15); 
    $hojaReporte->getColumnDimension('B')->setWidth(7); 
    $hojaReporte->getColumnDimension('C')->setWidth(7); 
    $hojaReporte->getColumnDimension('D')->setWidth(15); 
    $hojaReporte->getColumnDimension('E')->setWidth(7); 
    $hojaReporte->getColumnDimension('F')->setWidth(15); 
    $hojaReporte->getColumnDimension('G')->setWidth(15); 
    $hojaReporte->getColumnDimension('I')->setWidth(10); 
    $hojaReporte->getColumnDimension('H')->setWidth(10); 
    $hojaReporte->getColumnDimension('J')->setWidth(15); 

    //mostrar el total de retiros y depositos


    //colocar formato de moneda
    $hojaReporte->getStyle('F' . $linea . ':G' . $linea)->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);
    $linea++;
    $hojaReporte->getStyle('F' . $linea . ':G' . $linea)->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);
    //estilo de retiro
    $hojaReporte->getStyle('F' . $linea . ':G' . $linea)->getFont()->setName($fuente)->setBold(true);
}


       
