<?php
session_start();
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');

require "../../../vendor/autoload.php";

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

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

//se obtienen las variables del post
$transaccion = $datos[7];
$cuenta = $datos[8];
$tipo = $datos[10];
$r_fecha = $datos[9];
$fechainicial = $datos[5];
$fechafinal = $datos[6];
$usuario = $datos[11];
$oficina = $datos[12];

$hoy = date("Y-m-d H:i:s");
$hoy_archivo = date("d-m-Y");

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
    ->setSubject('Listado de Cuentas Activas e Inactivas')
    ->setDescription('Este reporte fue generado por el sistema MICROSYSTEM')
    ->setKeywords('PHPSpreadsheet')
    ->setCategory('Excel');
//-----------RELACIONADO CON LAS PROPIEDADES DEL ARCHIVO----------------------------

//-----------RELACIONADO CON EL ENCABEZADO----------------------------
# Como ya hay una hoja por defecto, la obtenemos, no la creamos
$hojaReporte = $spread->getActiveSheet();
$hojaReporte->setTitle("Reporte de listado del dia");

//insertarmos la fecha y usuario
$hojaReporte->setCellValue("A1", $hoy);
$hojaReporte->setCellValue("A2", $usuario);
//informacion de la agencia o cooperativa
$hojaReporte->setCellValue("A4", $institucion);
$hojaReporte->setCellValue("A5", $direccionins);
$hojaReporte->setCellValue("A6", "Email: " . $emailins);
$hojaReporte->setCellValue("A7", "Tel: " . $telefonosins);
$hojaReporte->setCellValue("A8", "NIT: " . $nitins);

//hacer pequeño las letras de la fecha, definir arial como tipo de letra
$hojaReporte->getStyle("A1:G1")->getFont()->setSize($tamanioFecha)->setName($fuente_encabezado);
$hojaReporte->getStyle("A2:G2")->getFont()->setSize($tamanioFecha)->setName($fuente_encabezado);
//centrar el texto de la fecha
$hojaReporte->getStyle("A1:G1")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$hojaReporte->getStyle("A2:G2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

//hacer grande las letras del encabezado
$hojaReporte->getStyle("A4:G4")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
$hojaReporte->getStyle("A5:G5")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
$hojaReporte->getStyle("A6:G6")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
$hojaReporte->getStyle("A7:G7")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
$hojaReporte->getStyle("A8:G8")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
//centrar el texto del encabezado
$hojaReporte->getStyle("A4:G4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$hojaReporte->getStyle("A5:G5")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$hojaReporte->getStyle("A6:G6")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$hojaReporte->getStyle("A7:G7")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$hojaReporte->getStyle("A8:G8")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

//hacer pequeño las letras del encabezado de titulo
$hojaReporte->getStyle("A10:G10")->getFont()->setSize($tamanioTabla)->setName($fuente);
$hojaReporte->getStyle("A11:G11")->getFont()->setSize($tamanioTabla)->setName($fuente);
$hojaReporte->getStyle("A12:G12")->getFont()->setSize($tamanioTabla)->setName($fuente);

//centrar los encabezado de la tabla
$hojaReporte->getStyle("A10:G10")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$hojaReporte->getStyle("A11:G11")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$hojaReporte->getStyle("A12:G12")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

//titulo del reporte
//consultar los estados
$texto_cuentas = "";
if ($tipo == '0') {
    $texto_cuentas = 'TODAS LAS CUENTAS';
} else {
    $data_cuentas = mysqli_query($conexion, "SELECT `nombre` FROM `aprtip` WHERE `id_tipo`=$tipo");
    while ($rowcuentas = mysqli_fetch_array($data_cuentas, MYSQLI_ASSOC)) {
        $texto_cuentas = strtoupper($rowcuentas["nombre"]);
    }
}
$texto_fecha = "TODAS LAS FECHAS";
if ($r_fecha == '2') {
    $texto_fecha = "DE " . $fechainicial . " HASTA " . $fechafinal;
}
$hojaReporte->setCellValue("A10", "LISTADO DE CUENTAS ACTIVAS/INACTIVAS");
$hojaReporte->setCellValue("A11", strtoupper($texto_cuentas));
$hojaReporte->setCellValue("A12", strtoupper($texto_fecha));

//combinacion de celdas
$hojaReporte->mergeCells('A1:G1');
$hojaReporte->mergeCells('A2:G2');
$hojaReporte->mergeCells('A4:G4');
$hojaReporte->mergeCells('A5:G5');
$hojaReporte->mergeCells('A6:G6');
$hojaReporte->mergeCells('A7:G7');
$hojaReporte->mergeCells('A8:G8');
$hojaReporte->mergeCells('A10:G10');
$hojaReporte->mergeCells('A11:G11');
$hojaReporte->mergeCells('A12:G12');

# Escribir encabezado de la tabla
$encabezado_tabla = ["CUENTA", "NOMBRE COMPLETO", "FECHA", "DOCUMENTO", "TIPO DE DOCUMENTO", "DEPOSITO", "RETIRO"];
# El último argumento es por defecto A1 pero lo pongo para que se explique mejor
$hojaReporte->fromArray($encabezado_tabla, null, 'A14')->getStyle('A14:G14')->getFont()->setName($fuente)->setBold(true);

//ingreso de los datos de tabla
$contador = 0;
$consulta = "SELECT cta.ccodaport, cl.short_name, mov.dfecope, mov.cnumdoc, mov.ctipdoc, mov.ctipope, mov.monto
FROM `tb_cliente` AS cl
    INNER JOIN `aprcta` AS cta ON cl.idcod_cliente = cta.ccodcli
    INNER JOIN `aprmov` AS mov ON mov.ccodaport = cta.ccodaport";

if ($transaccion != "0" || $tipo != "0" || $r_fecha != "1") {
    $consulta .= " WHERE";
    if ($transaccion != "0") {
        $consulta .= " mov.ctipope='$transaccion'";
    }
    if ($tipo != "0") {
        //obtener el codtip
        $data = mysqli_query($conexion, "SELECT `ccodtip` FROM `aprtip` WHERE `id_tipo`='$tipo'");
        while ($rowdata = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
            $ccodtip = $rowdata["ccodtip"];
        }
        if ($transaccion == "0") {
            $consulta .= " cta.ccodtip='$ccodtip'";
        } else {
            $consulta .= " AND cta.ccodtip='$ccodtip'";
        }
    }

    if ($r_fecha != "1") {
        if ($transaccion == "0" && $tipo == "0") {
            $consulta .= " mov.dfecope BETWEEN '$fechainicial' AND '$fechafinal'";
        } else {
            $consulta .= " AND mov.dfecope BETWEEN '$fechainicial' AND '$fechafinal'";
        }
    }
}
$consulta .= " ORDER BY mov.dfecope ASC, mov.ccodaport";
//se hace la consulta segun los tipos de filtro

$linea = 15;
$data2 = mysqli_query($conexion, $consulta);
cargar_datos($hojaReporte, $data2, $linea, $fuente);
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
function cargar_datos($hojaReporte, $data, $linea, $fuente)
{
    $total_dep = 0;
    $total_ret = 0;
    while ($rowdata = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
        $bd_ccodaport = $rowdata["ccodaport"];
        $bd_shortname = strtoupper($rowdata["short_name"]);
        $bd_fecha = date("d-m-Y", strtotime(strtoupper($rowdata["dfecope"])));
        $bd_tipo = strtoupper($rowdata["cnumdoc"]);
        $bd_doc = strtoupper($rowdata["ctipdoc"]);
        $bd_mov = strtoupper($rowdata["ctipope"]);
        $bd_monto = ($rowdata["monto"]);

        //colocar formato de moneda
        $hojaReporte->getStyle('F' . $linea . ':G' . $linea)
            ->getNumberFormat()
            ->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);
        //se insertan los datos
        $hojaReporte->setCellValueByColumnAndRow(1, $linea, $bd_ccodaport);
        $hojaReporte->setCellValueByColumnAndRow(2, $linea, $bd_shortname);
        $hojaReporte->setCellValueByColumnAndRow(3, $linea, $bd_fecha);
        $hojaReporte->setCellValueByColumnAndRow(4, $linea, $bd_tipo);
        $hojaReporte->setCellValueByColumnAndRow(5, $linea, $bd_doc);

        $monto_formateado = number_format($bd_monto, 2, '.', '');
        if ($bd_mov == 'D') {
            $total_dep = $total_dep + $bd_monto;
            $hojaReporte->setCellValueByColumnAndRow(6, $linea, $monto_formateado);
        } else {
            $hojaReporte->setCellValueByColumnAndRow(6, $linea, 0.00);
        }
        if ($bd_mov == 'R') {
            $total_ret = $total_ret + $bd_monto;
            $hojaReporte->setCellValueByColumnAndRow(7, $linea, $monto_formateado);
        } else {
            $hojaReporte->setCellValueByColumnAndRow(7, $linea, 0.00);
        }

        $hojaReporte->getStyle("A" . $linea . ":G" . $linea)->getFont()->setName($fuente);

        $linea++;
    }

    $hojaReporte->getColumnDimension('B')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('C')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('D')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('E')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('F')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('G')->setAutoSize(TRUE);

    //mostrar el total de retiros y depositos
    //texto totales
    $hojaReporte->getStyle('A' . $linea . ':E' . $linea)->getFont()->setName($fuente)->setItalic(true);
    $hojaReporte->setCellValue("A" . $linea, "TOTALES: ");
    //alineacion al centro
    $hojaReporte->getStyle('A' . $linea . ':E' . $linea)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    //merge de totales
    $hojaReporte->mergeCells('A' . $linea . ':E' . $linea);

    //texto total de retiro y deposito
    $hojaReporte->setCellValueByColumnAndRow(6, $linea, $total_dep);
    $hojaReporte->setCellValueByColumnAndRow(7, $linea, $total_ret);
    //colocar formato de moneda
    $hojaReporte->getStyle('F' . $linea . ':G' . $linea)->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);
    $linea++;
    $hojaReporte->getStyle('F' . $linea . ':G' . $linea)->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);
    //estilo de retiro
    $hojaReporte->getStyle('F' . $linea . ':G' . $linea)->getFont()->setName($fuente)->setBold(true);
}
