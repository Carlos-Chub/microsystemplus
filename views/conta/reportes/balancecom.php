<?php
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '3600');
session_start();
include '../../../src/funcphp/func_gen.php';
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
require '../../../fpdf/fpdf.php';

require '../../../vendor/autoload.php';
$hoy = date("Y-m-d");

use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Round;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Trim;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$datos = $_POST["datosval"];
$inputs = $datos[0];
$selects = $datos[1];
$radios = $datos[2];
$archivo = $datos[3];
$tipo = $_POST["tipo"];

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}
//FECHA
if ($radios[1] == "frango" && $inputs[0] > $inputs[1]) {
    echo json_encode(['status' => 0, 'mensaje' => 'Rango de fechas inválido']);
    return;
}
//-------ARMANDO LA CONSULTA------------
$condi = "";
//AGENCIA
$condi = $condi . " AND id_agencia=" . $selects[0];
//FUENTE DE FONDOS
if ($radios[1] == "anyf") {
    $condi = $condi . " AND id_fuente_fondo=" . $selects[1];
}

//RANGO DE FECHAS
$titlereport = " AL " . date("d-m-Y", strtotime($hoy));
if ($radios[1] == "frango") {
    $condi = $condi . " AND feccnt BETWEEN '" . $inputs[0] . "' AND '" . $inputs[1] . "'";
    $titlereport = " DEL " . date("d-m-Y", strtotime($inputs[0])) . " AL " . date("d-m-Y", strtotime($inputs[1]));
}


$fechaini = strtotime($inputs[0]);
$fechafin = strtotime($inputs[1]);
$mesini = date("m", $fechaini);
$anioini = date("Y", $fechaini);
$mesfin = date("m", $fechafin);
$aniofin = date("Y", $fechafin);

/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    ++++ CONSULTA FINAL SIN LA PARTIDA DE APERTURA Y EN EL RANGO DE FECHAS INDICADO+++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */

$strquery = "SELECT ccodcta,id_ctb_nomenclatura,cdescrip,SUM(debe)-SUM(haber) saldo,SUM(debe) debe,SUM(haber) haber from ctb_diario_mov WHERE estado=1 AND id_tipopol != 9 AND id_tipopol!=13 " . $condi . " GROUP BY ccodcta ORDER BY ccodcta";
$querypol = mysqli_query($conexion, $strquery);
$ctbmovdata[] = [];
$j = 0;
while ($fil = mysqli_fetch_array($querypol)) {
    $ctbmovdata[$j] = $fil;
    $j++;
}
//COMPROBAR SI HAY REGISTROS
if ($j == 0) {
    echo json_encode(['status' => 0, 'mensaje' => 'No hay datos']);
    return;
}
//$ctbmovdata[$j] = $ctbmovdata[0];


/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++ CONSULTA PARTIDA DE APERTURA INGRESADA EN ENERO DEL AÑO DEL BALANCE+++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */

$inianio = $anioini . '-01-01';
$finanio = $anioini . '-01-30';
$dated = strtotime($inputs[0]);
$lastdate = strtotime(date("Y-m-t", $dated));
$lastday = date("d", $lastdate);

$qparapr = "SELECT ccodcta,id_ctb_nomenclatura,cdescrip,SUM(debe)-SUM(haber) saldo,SUM(debe) debe,SUM(haber) haber from ctb_diario_mov WHERE estado=1 AND id_tipopol = 9 AND feccnt BETWEEN '" . $inianio . "' AND '" . $inputs[1] . "' GROUP BY ccodcta ORDER BY ccodcta";
$querys = mysqli_query($conexion, $qparapr);
$apertura[] = [];
$j = 0;
while ($fil = mysqli_fetch_array($querys)) {
    $apertura[$j] = $fil;
    $j++;
}
$hayapertura = ($j == 0) ? false : true;

/*  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++ CONSULTA DE REGISTROS ANTES DE LA FECHA QUE SE INGRESO SIN LA PARTIDA DE APERTURA +++++++
     +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$querysali = "SELECT ccodcta,id_ctb_nomenclatura,cdescrip,SUM(debe)-SUM(haber) saldo,SUM(debe) debe,SUM(haber) haber from ctb_diario_mov WHERE estado=1 AND id_tipopol != 9 AND id_tipopol != 13 AND feccnt >= '" . $inianio . "' AND feccnt < '" . $inputs[0] . "' GROUP BY ccodcta ORDER BY ccodcta";
$querys = mysqli_query($conexion, $querysali);
$salinidata[] = [];
$j = 0;
while ($fil = mysqli_fetch_array($querys)) {
    $salinidata[$j] = $fil;
    $j++;
}
$hayanteriores = ($j == 0) ? false : true;

/*  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++ INFO DE LA INSTITUCION +++++++++++++++++++++++++++++++++++++
    +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$queryins = mysqli_query($conexion, "SELECT * FROM clhpzzvb_bd_general_coopera.info_coperativa ins
INNER JOIN tb_agencia ag ON ag.id_institucion=ins.id_cop where ag.id_agencia=" . $_SESSION['id_agencia']);
$info[] = [];
$j = 0;
while ($fil = mysqli_fetch_array($queryins)) {
    $info[$j] = $fil;
    $j++;
}

if ($j == 0) {
    echo json_encode(['status' => 0, 'mensaje' => 'Institucion asignada a la agencia no encontrada']);
    return;
}



//TIPO DE ARCHIVO A IMPRIMIR
switch ($tipo) {
    case 'xlsx';
        printxls($ctbmovdata, $hayapertura, $apertura, $hayanteriores, $salinidata);
        break;
    case 'pdf':
        printpdf($ctbmovdata, [$titlereport], $info, $hayapertura, $apertura, $hayanteriores, $salinidata);
        break;
}

//funcion para generar pdf
function printpdf($registro, $datos, $info, $hayapertura, $apertura, $hayanteriores, $salinidata)
{
    /*     $oficina = "Coban";
    $institucion = "Cooperativa Integral De Ahorro y credito Imperial";
    $direccionins = "Canton vipila zona 1";
    $emailins = "fape@gmail.com";
    $telefonosins = "502 43987876";
    $nitins = "1323244234";
    $rutalogomicro = "../../../includes/img/logomicro.png";
    $rutalogoins = "../../../includes/img/fape.jpeg"; */

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
        public $datos;

        public function __construct($institucion, $pathlogo, $pathlogoins, $oficina, $direccion, $email, $telefono, $nit, $datos)
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
            $this->datos = $datos;
            $this->DefOrientation = 'L';
        }

        // Cabecera de página
        function Header()
        {
            $fuente = "Courier";
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

            $this->SetFont($fuente, 'B', 10);
            //TITULO DE REPORTE
            $this->SetFillColor(204, 229, 255);
            $this->Cell(0, 5, 'BALANCE DE COMPROBACION ' . $this->datos[0], 0, 1, 'C', true);
            $this->Cell(0, 5, '(CIFRAS EN QUETZALES)', 0, 1, 'C', true);
            //Color de encabezado de lista
            $this->SetFillColor(555, 255, 204);
            //TITULOS DE ENCABEZADO DE TABLA
            $ancho_linea = 31;

            $this->Cell($ancho_linea, 5, 'CUENTA', 'B', 0, 'L');
            $this->Cell($ancho_linea * 2, 5, 'DESCRIPCION', 'B', 0, 'L');
            $this->Cell($ancho_linea, 5, 'Saldo Anterior', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'Debe', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'Haber', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'Deudor', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'Acreedor', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'Saldo', 'B', 1, 'R');
            $this->Ln(2);
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
    $pdf = new PDF($institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins, $datos);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $fuente = "Courier";
    $tamanio_linea = 4;
    $ancho_linea2 = 31;
    $pdf->SetFont($fuente, '', 8);

    $totaldebe = 0;
    $totalhaber = 0;
    $sumsalant = 0;

    $f = 0;
    while ($f < count($registro)) {
        $codcta = $registro[$f]["ccodcta"];
        $nombre = $registro[$f]["cdescrip"];
        $saldo = $registro[$f]["saldo"];
        $debe = $registro[$f]["debe"];
        $haber = $registro[$f]["haber"];

        $salapertura = ($hayapertura) ? array_sum(array_column(calculo2($apertura, $codcta), 'saldo')) : 0;
        $salanterior = ($hayanteriores) ? array_sum(array_column(calculo2($salinidata, $codcta), 'saldo')) : 0;
        $saldoanterior = ($salapertura)  + $salanterior;
        $saldofinal = $saldoanterior + $debe - $haber;

        $debe = number_format($debe, 2);
        $haber = number_format($haber, 2);
        $sumsalant += $saldoanterior;
        $saldoanterior = number_format($saldoanterior, 2);
        $saldofinal = number_format($saldofinal, 2);

        $saldeu = ($saldo >= 0) ? number_format($saldo, 2, '.', ',') : " ";
        $salacre = ($saldo < 0) ? number_format(abs($saldo), 2, '.', ',') : " ";
        $pdf->CellFit($ancho_linea2, $tamanio_linea, $codcta, '', 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea, utf8_decode($nombre), '', 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, $saldoanterior, '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, $debe, '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, $haber, '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, $saldeu, '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, $salacre, '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, $saldofinal, '', 1, 'R', 0, '', 1, 0);

        //***************SUMATORIAS*********************
        $aux = ($saldo > 0) ? $saldo : 0;
        $totaldebe = $totaldebe + $aux;

        $aux = ($saldo < 0) ? abs($saldo) : 0;
        $totalhaber = $totalhaber + $aux;
        if ($f != array_key_last($registro)) {
            $nextcuenta = $registro[$f + 1]["ccodcta"];
            if (substr($nextcuenta, 0, 1) != substr($codcta, 0, 1)) {
                $pdf->Ln(2);
            }
        }
        $f++;
    }
    $debetotal = array_sum(array_column($registro, 'debe'));
    $habertotal = array_sum(array_column($registro, 'haber'));
    $pdf->Ln(4);
    $pdf->SetFont($fuente, 'B', 9);
    $pdf->Cell($ancho_linea2 * 3, $tamanio_linea, 'TOTAL GENERAL: ', '', 0, 'R');
    $pdf->Cell($ancho_linea2, $tamanio_linea + 2, number_format($sumsalant, 2, '.', ','), 'BT', 0, 'R');
    $pdf->Cell($ancho_linea2, $tamanio_linea + 2, number_format($debetotal, 2, '.', ','), 'BT', 0, 'R');
    $pdf->Cell($ancho_linea2, $tamanio_linea + 2, number_format($habertotal, 2, '.', ','), 'BT', 0, 'R');
    $pdf->Cell($ancho_linea2, $tamanio_linea + 2, number_format($totaldebe, 2, '.', ','), 'BT', 0, 'R');
    $pdf->Cell($ancho_linea2, $tamanio_linea + 2, number_format($totalhaber, 2, '.', ','), 'BT', 1, 'R');
    $pdf->Cell($ancho_linea2 * 3, $tamanio_linea, ' ', '', 0, 'R');
    $pdf->Cell($ancho_linea2 * 5, $tamanio_linea / 4, ' ', 'B', 1, 'R');

    // $pdf->Ln(20);
    // $pdf->CellFit($ancho_linea2 + 15, $tamanio_linea, 'PRESIDENTE', 'T', 0, 'C', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea2 / 2, $tamanio_linea, ' ', '', 0, 'L', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea2 + 15, $tamanio_linea, 'GERENTE', 'T', 0, 'C', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea2 / 2, $tamanio_linea, ' ', '', 0, 'L', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea2 + 15, $tamanio_linea, 'CONTADOR', 'T', 1, 'C', 0, '', 1, 0);

    $pdf->firmas(1, ['PRESIDENTE', 'GERENTE', 'CONTADOR']);
    ob_start();
    $pdf->Output();
    $pdfData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "Balance de Comprobacion",
        'tipo' => "pdf",
        'data' => "data:application/pdf;base64," . base64_encode($pdfData)
    );
    echo json_encode($opResult);
}
function calculo2($data, $cuenta)
{
    return (array_filter($data, function ($var) use ($cuenta) {
        return ($var['ccodcta']  == $cuenta);
    }));
}

//funcion para generar archivo excel
function printxls($registro, $hayapertura, $apertura, $hayanteriores, $salinidata)
{
    require '../../../vendor/autoload.php';

    $excel = new Spreadsheet();
    $activa = $excel->getActiveSheet();
    $activa->setTitle("Balance_Comprobacion");
    $activa->getColumnDimension("A")->setWidth(15);
    $activa->getColumnDimension("B")->setWidth(65);
    $activa->getColumnDimension("C")->setWidth(20);
    $activa->getColumnDimension("D")->setWidth(20);
    $activa->getColumnDimension("E")->setWidth(20);
    $activa->getColumnDimension("F")->setWidth(20);
    $activa->getColumnDimension("G")->setWidth(20);
    $activa->getColumnDimension("H")->setWidth(20);

    $activa->setCellValue('A1', 'CUENTA');
    $activa->setCellValue('B1', 'NOMBRE CUENTA');
    $activa->setCellValue('C1', 'SALDO ANTERIOR');
    $activa->setCellValue('D1', 'DEBE');
    $activa->setCellValue('E1', 'HABER');
    $activa->setCellValue('F1', 'DEUDOR');
    $activa->setCellValue('G1', 'ACREEDOR');
    $activa->setCellValue('H1', 'SALDO FINAL');
    //-------
    $totaldebe = 0;
    $totalhaber = 0;
    $f = 0;
    $i = 2;
    while ($f < count($registro)) {
        $codcta = $registro[$f]["ccodcta"];
        $nombre = $registro[$f]["cdescrip"];
        $saldo = $registro[$f]["saldo"];
        $debe = $registro[$f]["debe"];
        $haber = $registro[$f]["haber"];

        $salapertura = ($hayapertura) ? array_sum(array_column(calculo2($apertura, $codcta), 'saldo')) : 0;
        $salanterior = ($hayanteriores) ? array_sum(array_column(calculo2($salinidata, $codcta), 'saldo')) : 0;
        $saldoanterior = ($salapertura)  + $salanterior;
        $saldofinal = $saldoanterior + $debe - $haber;

        $saldeu = ($saldo >= 0) ? $saldo : "";
        $salacre = ($saldo < 0) ? abs($saldo) : "";
        $activa->setCellValueExplicit('A' . $i, $codcta, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('B' . $i, $nombre);
        $activa->setCellValue('C' . $i, $saldoanterior);
        $activa->setCellValue('D' . $i, $debe);
        $activa->setCellValue('E' . $i, $haber);
        $activa->setCellValue('F' . $i, $saldeu);
        $activa->setCellValue('G' . $i, $salacre);
        $activa->setCellValue('H' . $i, $saldofinal);

        $i++;

        $f++;
    }
    $activa->setCellValue('B' . ($i), 'TOTALES');
    $activa->setCellValueExplicit('C' . ($i), '=SUM(C2:C' . ($i - 1) . ')', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
    $activa->setCellValueExplicit('D' . ($i), '=SUM(D2:D' . ($i - 1) . ')', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
    $activa->setCellValueExplicit('E' . ($i), '=SUM(E2:E' . ($i - 1) . ')', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
    $activa->setCellValueExplicit('F' . ($i), '=SUM(F2:F' . ($i - 1) . ')', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
    $activa->setCellValueExplicit('G' . ($i), '=SUM(G2:G' . ($i - 1) . ')', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
    $activa->setCellValueExplicit('H' . ($i), '=SUM(H2:H' . ($i - 1) . ')', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);

    ob_start();
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel, 'Xlsx');
    $writer->save("php://output");
    $xlsData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "Balance de Comprobacion",
        'tipo' => "vnd.ms-excel",
        'data' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
    );
    echo json_encode($opResult);
    exit;
}
