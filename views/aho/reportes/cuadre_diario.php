<?php
session_start();
include '../../../src/funcphp/func_gen.php';
include '../../../includes/BD_con/db_con.php';
require '../../../fpdf/fpdf.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
date_default_timezone_set('America/Guatemala');

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

$tipo = $_POST["tipo"];
$datos = $_POST["datosval"];

$inputs = $datos[0];
$selects = $datos[1];
$archivo = $datos[3];
$radios = $datos[2];

if ($radios[1] == "frango" && $inputs[0] > $inputs[1]) {
    echo json_encode(['status' => 0, 'mensaje' => 'Rango de fechas inválido']);
    return;
}
if ($radios[0] == "any" && $selects[0] == "0") {
    echo json_encode(['status' => 0, 'mensaje' => 'Seleccione un tipo de cuenta']);
    return;
}
//ARMANDO LA CONSULTA
$condicion = "";
$conditipo = "";
$bandera = 0;
if ($radios[0] == "any") {
    $conditipo = " SUBSTR(`ccodaho`,7,2) =" . $selects[0];
    $tipocuenta = tipocuenta($selects[0], "ahomtip", "nombre",$conexion);
    $bandera++;
} else {
    $tipocuenta = "Todos los tipos de cuenta"; //solo para reporte detalle
}
$condicion = $conditipo;
//-------------condicion de rango de fechas
$and = "";
if ($radios[1] == "frango") {
    if ($bandera > 0) {
        $and = " AND ";
        $condicion = $condicion . " AND ";
    }
    $rango = "" . date("d-m-Y", strtotime($inputs[0])) . " A " . date("d-m-Y", strtotime($inputs[1])); //solo para reporte detalle
    $condicion = $condicion . " (dfecope BETWEEN '" . $inputs[0] . "' AND '" . $inputs[1] . "')";
    $bandera++;
} else {
    $rango = "Todas las fechas"; //solo para reporte detalle
}

$where = ($bandera > 0) ? " AND " : "";
//--------------------

$strquery = "SELECT dfecope,sum(monto) as total,ctipope FROM ahommov WHERE cestado!=2 " . $where . $condicion . " GROUP BY dfecope,ctipope ORDER BY dfecope";

$sql = mysqli_query($conexion, $strquery);
$array[] = [];
$fila = 0;
$bandera = "No hay datos";
while ($registro = mysqli_fetch_array($sql, MYSQLI_ASSOC)) {
    $array[$fila] = $registro;
    $fila++;
    $bandera = "";
}

if ($bandera != "") {
    echo json_encode(['status' => 0, 'mensaje' => $bandera]);
    return;
}
//-----------------SALDO ANTERIOR SUMATORIA
$saldoant = 0;
if ($radios[1] == "frango") {
    $condirango =  "WHERE dfecope<'" . $inputs[0] . "'";
    $ctipo = ($radios[0] == "any") ? " AND " . $conditipo : "";
    $queryant = "SELECT IFNULL(sum(montooo),0) as saldoant FROM data_ahommov " . $condirango . $ctipo . "";
    $sqlsal = mysqli_query($conexion, $queryant);
    while ($registro = mysqli_fetch_array($sqlsal, MYSQLI_ASSOC)) {
        $saldoant =($registro["saldoant"]);
    }
}

$queryins = mysqli_query($conexion, "SELECT * FROM clhpzzvb_bd_general_coopera.info_coperativa ins
INNER JOIN tb_agencia ag ON ag.id_institucion=ins.id_cop where ag.id_agencia=" . $_SESSION['id_agencia']);
$info[] = [];
$j = 0;
while ($fil = mysqli_fetch_array($queryins)) {
    $info[$j] = $fil;
    $j++;
}

mysqli_close($conexion);
switch ($tipo) {
    case 'xlsx':
        //printxls($array);
        break;
    case 'pdf':
        printpdf($array, $rango, $tipocuenta,$saldoant,$info);
        break;
}

//funcion para generar pdf
function printpdf($registro, $rango, $tipocuenta,$saldoant,$info)
{
    date_default_timezone_set('America/Guatemala');
    // $oficina = "Coban";
    // $institucion = "Cooperativa Integral De Ahorro y credito Imperial";
    // $direccionins = "Canton vipila zona 1";
    // $emailins = "fape@gmail.com";
    // $telefonosins = "502 43987876";
    // $nitins = "1323244234";

    // $rutalogomicro = "../../../includes/img/logomicro.png";
    // $rutalogoins = "../../../includes/img/logomicro.png";

    $oficina = utf8_decode($info[0]["nom_agencia"]);
    $institucion = utf8_decode($info[0]["nomb_comple"]);
    $direccionins = utf8_decode($info[0]["muni_lug"]);
    $emailins = $info[0]["emai"];
    $telefonosins = $info[0]["tel_1"] . '   ' . $info[0]["tel_2"];;
    $nitins = $info[0]["nit"];
    $rutalogomicro = "../../../includes/img/logomicro.png";
    $rutalogoins = "../../.." . $info[0]["log_img"];
    //lo que se tiene que repetir en cada una de las hojas
    class PDF extends FPDF
    {
        //atributos de la clase
        public $institucion;
        public $pathlogo;
        public $pathlogoins;
        public $oficina;
        public $direccion;
        public $email;
        public $telefono;
        public $nit;
        public $rango;
        public $tipocuenta;
        public $saldoant;

        public function __construct($institucion, $pathlogo, $pathlogoins, $oficina, $direccion, $email, $telefono, $nit, $rango, $tipocuenta,$saldoant)
        {
            parent::__construct();
            $this->institucion = $institucion;
            $this->pathlogo = $pathlogo;
            $this->pathlogoins = $pathlogoins;
            $this->oficina = $oficina;
            $this->direccion = $direccion;
            $this->email = $email;
            $this->telefono = $telefono;
            $this->nit = $nit;
            $this->rango = $rango;
            $this->tipocuenta = $tipocuenta;
            $this->saldoant = $saldoant;
        }

        // Cabecera de página
        function Header()
        {
            $fuente = "Courier";
            $tamanioTitulo = 10;
            $tamanio_linea = 4; //altura de la linea/celda
            $ancho_linea = 30; //anchura de la linea/celda
            $ancho_linea2 = 20; //anchura de la linea/celda
            $hoy = date("Y-m-d H:i:s");
            //fecha y usuario que genero el reporte
            $this->SetFont($fuente, '', 7);
            $this->Cell(0, 2, $hoy, 0, 1, 'R');
            // Logo de la agencia
            $this->Image($this->pathlogoins, 10, 13, 33);

            //tipo de letra para el encabezado
            $this->SetFont($fuente, 'B', 9);
            // Título
            $this->Cell(0, 3, $this->institucion, 0, 1, 'C');
            $this->Cell(0, 3, $this->direccion, 0, 1, 'C');
            $this->Cell(0, 3, 'Email: ' . $this->email, 0, 1, 'C');
            $this->Cell(0, 3, 'Tel: ' . $this->telefono, 0, 1, 'C');
            $this->Cell(0, 3, 'NIT: ' . $this->nit, 'B', 1, 'C');
            // Salto de línea
            $this->Ln(10);

            $this->SetFont($fuente, 'B', $tamanioTitulo);
            //TITULO DE REPORTE
            $this->SetFillColor(204, 229, 255);
            $this->Cell(0, 5, 'CUADRE DIARIO DE DEPOSITOS/RETIROS', 0, 1, 'C', true);
            $this->Cell(0, 5, 'RANGO: ' . $this->rango, 0, 1, 'L');
            $this->Cell(0, 5, 'TIPO DE CUENTA: ' . $this->tipocuenta, 0, 1, 'L');
            //Color de encabezado de lista
            $this->SetFillColor(555, 255, 204);
            //TITULOS DE ENCABEZADO DE TABLA
            $this->Cell(0, $tamanio_linea + 1, 'Saldo Anterior '.number_format(($this->saldoant), 2, '.', ','), 'B', 1, 'R');
            $this->Cell($ancho_linea, $tamanio_linea + 1, 'Fecha', 'B', 0, 'C', true);
            $this->Cell($ancho_linea * 2, $tamanio_linea + 1, 'Total Depositos', 'B', 0, 'C', true); //
            $this->Cell($ancho_linea * 2, $tamanio_linea + 1, 'Total Retiros', 'B', 0, 'C', true);
            $this->Cell($ancho_linea + 10, $tamanio_linea + 1, 'Saldo', 'B', 1, 'C',true);
        }

        // Pie de página
        function Footer()
        {
            // Posición: a 1 cm del final
            $this->SetY(-15);
            // Logo 
            $this->Image($this->pathlogo, 175, 279, 28);
            // Arial italic 8
            $this->SetFont('Arial', 'I', 8);
            // Número de página
            $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }
    }
    // Creación del objeto de la clase heredada
    $pdf = new PDF($institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins, $rango, $tipocuenta,$saldoant);

    $pdf->AliasNbPages();
    $pdf->AddPage();
    $fuente = "Courier";
    $tamanio_linea = 4; //altura de la linea/celda
    $ancho_linea2 = 25; //anchura de la linea/celda
    $pdf->SetFont($fuente, '', 10);
    $fila = 0;
    $totaldep = 0;
    $totalret = 0;
    $saldo=$saldoant;
    while ($fila < count($registro)) {
        $fec = utf8_encode($registro[$fila]["dfecope"]);
        $total = utf8_encode($registro[$fila]["total"]);
        $tipope = utf8_encode($registro[$fila]["ctipope"]);
        $fecha = date("d-m-Y", strtotime(utf8_encode($fec)));
        $depositos = ($tipope == "D") ? $total : 0;
        $retiros = ($tipope == "R") ? $total : 0;
        $saldo = $saldo+$depositos-$retiros;
        $totaldep = $totaldep + $depositos;
        $totalret = $totalret + $retiros;

        $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $fecha, 0, 0, 'R', 0, '', 1, 0); // cuenta
        $pdf->CellFit($ancho_linea2 * 2+3, $tamanio_linea + 1, number_format($depositos, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0); // cuenta
        $pdf->CellFit($ancho_linea2 * 2+3, $tamanio_linea + 1, number_format($retiros, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0); // cuenta
        $pdf->CellFit($ancho_linea2 * 2+3, $tamanio_linea + 1, number_format($saldo, 2, '.', ','), 0, 1, 'R', 0, '', 1, 0); // cuenta
        $fila++;
    }
    $pdf->SetFont($fuente, 'B', 10);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, "TOTALES: ", 'T', 0, 'R', 0, '', 1, 0); // cuenta
    $pdf->CellFit($ancho_linea2 * 2+3, $tamanio_linea + 1, number_format($totaldep, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0); // cuenta
    $pdf->CellFit($ancho_linea2 * 2+3, $tamanio_linea + 1, number_format($totalret, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0); // cuenta
    $pdf->CellFit($ancho_linea2 * 2+3, $tamanio_linea + 1, '-', 'T', 1, 'R', 0, '', 1, 0); // cuenta

    ob_start();
    $pdf->Output();
    $pdfData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "Cuadre diario",
        'data' => "data:application/vnd.ms-word;base64," . base64_encode($pdfData)
    );
    echo json_encode($opResult);
}

//funcion para generar archivo excel
function printxls($registro)
{
    require '../../../vendor/autoload.php';

    $excel = new Spreadsheet();
    $activa = $excel->getActiveSheet();
    $activa->setTitle("CERTIFICADOS");

    $activa->getColumnDimension("A")->setWidth(20);
    $activa->getColumnDimension("B")->setWidth(20);
    $activa->getColumnDimension("C")->setWidth(60);
    $activa->getColumnDimension("D")->setWidth(20);
    $activa->getColumnDimension("E")->setWidth(20);
    $activa->getColumnDimension("F")->setWidth(20);
    $activa->getColumnDimension("G")->setWidth(15);
    $activa->getColumnDimension("H")->setWidth(15);
    $activa->getColumnDimension("I")->setWidth(15);
    $activa->getColumnDimension("J")->setWidth(20);

    $activa->setCellValue('A1', 'CERTIFICADO');
    $activa->setCellValue('B1', 'CODIGO CUENTA');
    $activa->setCellValue('C1', 'NOMBRE CLIENTE');
    $activa->setCellValue('D1', 'FECHA APERTURA');
    $activa->setCellValue('E1', 'FECHA VENCIMIENTO');
    $activa->setCellValue('F1', 'FECHA CANCELACION');
    $activa->setCellValue('G1', 'MONTO');
    $activa->setCellValue('H1', 'PLAZO');
    $activa->setCellValue('I1', 'INTERES');
    $activa->setCellValue('J1', 'LIQUIDADO');
    $fila = 0;
    $i = 2;
    while ($fila < count($registro)) {
        $crt = utf8_encode($registro[$fila]["ccodcrt"]);
        $cuenta = utf8_encode($registro[$fila]["codaho"]);
        $nombre = utf8_encode($registro[$fila]["short_name"]);
        $apertura = date("d-m-Y", strtotime(utf8_encode($registro[$fila]["fec_apertura"])));
        $vence = date("d-m-Y", strtotime(utf8_encode($registro[$fila]["fec_ven"])));
        $cancel = $registro[$fila]["fec_liq"];
        ($cancel == '0000-00-00') ? $cancelacion = "" : $cancelacion = date("d-m-Y", strtotime(utf8_encode($registro[$fila]["fec_liq"])));

        $monto = utf8_encode($registro[$fila]["montoapr"]);
        $plazo = utf8_encode($registro[$fila]["plazo"]);
        $interes = utf8_encode($registro[$fila]["interes"]);
        $est = utf8_encode($registro[$fila]["liquidado"]);
        ($est == "S") ? $estado = "LIQUIDADO" : $estado = "NO LIQUIDADO";

        $activa->setCellValueExplicit('A' . $i, $crt, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValueExplicit('B' . $i, $cuenta, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('C' . $i, strtoupper($nombre));
        $activa->setCellValue('D' . $i, $apertura);
        $activa->setCellValue('E' . $i, $vence);
        $activa->setCellValue('F' . $i, $cancelacion);
        $activa->setCellValue('G' . $i, $monto);
        $activa->setCellValue('H' . $i, $plazo);
        $activa->setCellValue('I' . $i, $interes);
        $activa->setCellValue('J' . $i, $estado);
        $fila++;
        $i++;
    }

    ob_start();
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel, 'Xlsx');
    $writer->save("php://output");
    $xlsData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "listadocertificados",
        'data' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
    );
    echo json_encode($opResult);
    exit;
}
