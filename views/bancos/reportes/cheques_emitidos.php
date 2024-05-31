<?php
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

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

$datos = $_POST["datosval"];
$inputs = $datos[0];
$selects = $datos[1];
$radios = $datos[2];
$archivo = $datos[3];
$tipo = $_POST["tipo"];
//-------VALIDACIONES------------------([[`finicio`,`ffin`,idcuenta],[`codofi`,`fondoid`],[`rcuentas`,`rfondos`,`rfechas`],[ $idusuario]],`pdf`,`libro_diario`,0)
if ($radios[0] == "anycuen" && $inputs[2] == 0) {
    echo json_encode(['status' => 0, 'mensaje' => 'Seleccione una cuenta contable']);
    return;
}
if ($radios[2] == "frango" && $inputs[0] > $inputs[1]) {
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

//CUENTA CONTABLE
if ($radios[0] == "anycuen") {
    $condi = $condi . " AND id_ctb_nomenclatura=" . $inputs[2];
}

//RANGO DE FECHAS
$titlereport = " AL " . date("d-m-Y", strtotime($hoy));
if ($radios[2] == "frango") {
    $condi = $condi . " AND feccnt BETWEEN '" . $inputs[0] . "' AND '" . $inputs[1] . "'";
    $titlereport = " DEL " . date("d-m-Y", strtotime($inputs[0])) . " AL " . date("d-m-Y", strtotime($inputs[1]));
}
//CONSULTA FINAL
$strquery = "SELECT * from ctb_diario_mov WHERE estado=1" . $condi . " ORDER BY id_ctb_nomenclatura,feccnt";
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

//SE AGREGA UNA ULTIMA FILA, PERO NO SE IMPRIME
$ctbmovdata[$j] = $ctbmovdata[0];

//SALDO ANTERIOR
$salantdata[] = [];
$flag = 0;
if ($radios[2] == "frango") {
    $strque = "SELECT sum(debe) sdebe,sum(haber) shaber,id_ctb_nomenclatura idcuensal from ctb_diario_mov 
    WHERE estado=1 AND feccnt< '" . $inputs[0] . "' GROUP BY id_ctb_nomenclatura ORDER BY id_ctb_nomenclatura";
    $querysalant = mysqli_query($conexion, $strque);
    $j = 0;
    while ($fil = mysqli_fetch_array($querysalant)) {
        $salantdata[$j] = $fil;
        $j++;
        $flag = 1;
    }
}


//TIPO DE ARCHIVO A IMPRIMIR
switch ($tipo) {
    case 'xlsx';
        printxls($ctbmovdata, [$flag, $salantdata]);
        break;
    case 'pdf':
        printpdf($ctbmovdata, [$titlereport, $flag, $salantdata]);
        break;
}

//funcion para generar pdf
function printpdf($registro, $datos)
{
    $oficina = "Coban";
    $institucion = "Cooperativa Integral De Ahorro y credito Imperial";
    $direccionins = "Canton vipila zona 1";
    $emailins = "fape@gmail.com";
    $telefonosins = "502 43987876";
    $nitins = "1323244234";
    $rutalogomicro = "../../../includes/img/logomicro.png";
    $rutalogoins = "../../../includes/img/fape.jpeg";
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
            $this->Cell(0, 5, 'LIBRO MAYOR ' . $this->datos[0], 0, 1, 'C', true);
            //Color de encabezado de lista
            $this->SetFillColor(555, 255, 204);
            //TITULOS DE ENCABEZADO DE TABLA
            $ancho_linea = 30;

            $this->Cell($ancho_linea - 15, 5, 'FECHA', 'B', 0, 'L');
            $this->Cell($ancho_linea - 10, 5, 'PARTIDA', 'B', 0, 'L');
            $this->Cell($ancho_linea + 55, 5, 'DESCRIPCION', 'B', 0, 'L');
            $this->Cell($ancho_linea - 5, 5, 'DEBE', 'B', 0, 'R');
            $this->Cell($ancho_linea - 5, 5, 'HABER', 'B', 0, 'R');
            $this->Cell($ancho_linea - 5, 5, 'SALDO', 'B', 1, 'R');
            $this->Ln(4);
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
    $tamanio_linea = 3;
    $ancho_linea2 = 30;
    $pdf->SetFont($fuente, '', 8);
    $flag = $datos[1];
    $saldant = $datos[2];
    $fila = 0;
    $aux = 0;
    $sumd = 0;
    $sumh = 0;
    $saldo = 0;
    $sumtd = 0;
    $sumth = 0;
    while ($fila < count($registro)) {
        $partida = $registro[$fila]["numcom"];
        $fecha = date("d-m-Y", strtotime($registro[$fila]["feccnt"]));
        $numdoc = $registro[$fila]["numdoc"];
        $glosa = utf8_decode(trim($registro[$fila]["glosa"]));
        $idcuenta = $registro[$fila]["id_ctb_nomenclatura"];
        $codcta = $registro[$fila]["ccodcta"];
        $nomcuenta =utf8_decode($registro[$fila]["cdescrip"]) ;
        $debe = $registro[$fila]["debe"];
        $haber = $registro[$fila]["haber"];
        $idnumcom = $registro[$fila]["id_ctb_diario"];

        if ($idcuenta != $aux) {
            if ($fila != 0) {
                $pdf->Ln(1);
                $pdf->Cell($ancho_linea2 * 4, $tamanio_linea, ' ', '', 0, 'R');
                $pdf->Cell($ancho_linea2 - 5, $tamanio_linea + 2, number_format($sumd, 2, '.', ','), 'BT', 0, 'R');
                $pdf->Cell($ancho_linea2 - 5, $tamanio_linea + 2, number_format($sumh, 2, '.', ','), 'BT', 1, 'R');
                $pdf->Cell($ancho_linea2 * 4, $tamanio_linea, ' ', '', 0, 'R');
                $pdf->Cell($ancho_linea2 * 2 - 10, $tamanio_linea / 4, ' ', 'B', 1, 'R');
                $sumd = 0;
                $sumh = 0;
                $pdf->Ln(5);
            }
            if ($fila != array_key_last($registro)) {
                //ENCABEZADOS CUENTAS INDIVIDUALES
                $pdf->SetFont($fuente, 'B', 8);
                $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea + 1, 'Cuenta: ' . $codcta, 'B', 0, 'L', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2 * 2 + 3, $tamanio_linea + 1, 'Nombre: ' . $nomcuenta, 'B', 0, 'L', 0, '', 1, 0);

                //VERIFICAR SI TIENE SALDO ANTERIOR
                if ($flag == 1) {
                    $isal = array_search($idcuenta, array_column($saldant, 'idcuensal'));
                    $saldo = ($isal != false) ? ($saldant[$isal]["sdebe"] - $saldant[$isal]["shaber"]) : 0;
                } else {
                    $saldo = 0;
                }

                $pdf->CellFit($ancho_linea2 * 2 + 12, $tamanio_linea + 1, 'Saldo Ant.:' . number_format($saldo, 2, '.', ','), 'B', 1, 'R', 0, '', 1, 0);
                $aux = $idcuenta;
            }
        }
        if ($fila != array_key_last($registro)) {
            //DETALLES PARTIDAS INDIVIDUALES
            $pdf->SetFont($fuente, '', 8);
            $pdf->CellFit($ancho_linea2 - 10, $tamanio_linea, $fecha, '', 0, 'L', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea2 - 15, $tamanio_linea, $partida, '', 0, 'L', 0, '', 1, 0);

            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->MultiCell($ancho_linea2 + 55, 3, $glosa . ' - ' . $numdoc);
            $x += $ancho_linea2 + 55;
            $y2 = $pdf->GetY();
            if ($y > $y2) {
                $y3 = 3;
                $y = $y2;
            } else {
                $y3 = $y2 - $y;
            }
            $pdf->SetXY($x, $pdf->GetY() - $y3);
            $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea, number_format($debe, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea, number_format($haber, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
            //SALDO 
            $sumd = $sumd + $debe;
            $sumh = $sumh + $haber;
            $saldo = $saldo + $debe - $haber;
            $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea, number_format($saldo, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
            $pdf->SetY($y + $y3);

            $sumtd = $sumtd + $debe;
            $sumth = $sumth + $haber;
        }
        $fila++;
    }
    $pdf->Cell($ancho_linea2 + 90, $tamanio_linea, 'TOTAL GENERAL: ', '', 0, 'R');
    $pdf->Cell($ancho_linea2 - 5, $tamanio_linea + 2, number_format($sumtd, 2, '.', ','), 'BT', 0, 'R');
    $pdf->Cell($ancho_linea2 - 5, $tamanio_linea + 2, number_format($sumth, 2, '.', ','), 'BT', 1, 'R');
    $pdf->Cell($ancho_linea2 + 90, $tamanio_linea, ' ', '', 0, 'R');
    $pdf->Cell($ancho_linea2 * 2 - 10, $tamanio_linea / 4, ' ', 'B', 1, 'R');

    ob_start();
    $pdf->Output();
    $pdfData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "Libro Mayor",
        'tipo' => "pdf",
        'data' => "data:application/pdf;base64," . base64_encode($pdfData)
    );
    echo json_encode($opResult);
}

//funcion para generar archivo excel
function printxls($registro, $datos)
{
    require '../../../vendor/autoload.php';

    $excel = new Spreadsheet();
    $activa = $excel->getActiveSheet();
    $activa->setTitle("LibroMayor");


    $activa->getColumnDimension("A")->setWidth(15);
    $activa->getColumnDimension("B")->setWidth(25);
    $activa->getColumnDimension("C")->setWidth(2);
    $activa->getColumnDimension("D")->setWidth(15);
    $activa->getColumnDimension("E")->setWidth(10);
    $activa->getColumnDimension("F")->setWidth(70);
    $activa->getColumnDimension("G")->setWidth(15);
    $activa->getColumnDimension("H")->setWidth(15);
    $activa->getColumnDimension("I")->setWidth(15);

    $activa->setCellValue('A1', 'CUENTA');
    $activa->setCellValue('B1', 'NOMBRE CUENTA');
    $activa->setCellValue('C1', ' ');
    $activa->setCellValue('D1', 'FECHA');
    $activa->setCellValue('E1', 'PARTIDA');
    $activa->setCellValue('F1', 'DESCRIPCION');
    $activa->setCellValue('G1', 'DEBE');
    $activa->setCellValue('H1', 'HABER');
    $activa->setCellValue('I1', 'SALDO');
    $activa->setCellValue('J1', 'NOMBRE');
    $activa->setCellValue('K1', 'NUMDOC');
    $flag = $datos[0];
    $saldant = $datos[1];

    $saldo = 0;
    $iniciom=4;
    $aux = 0;
    $sumd = 0;
    $sumh = 0;
    $sumtd = 0;
    $sumth = 0;
    $fila = 0;
    $i = 2;
    while ($fila < count($registro)) {
        $partida = $registro[$fila]["numcom"];
        $fecha = date("d-m-Y", strtotime($registro[$fila]["feccnt"]));
        $numdoc = $registro[$fila]["numdoc"];
        $numdoc=($numdoc=="" || $numdoc==NULL)?" ":$numdoc;
        $glosa = trim($registro[$fila]["glosa"]);
        $idcuenta = $registro[$fila]["id_ctb_nomenclatura"];
        $codcta = $registro[$fila]["ccodcta"];
        $nomcuenta = $registro[$fila]["cdescrip"];
        $debe = $registro[$fila]["debe"];
        $haber = $registro[$fila]["haber"];
        $nomchq = $registro[$fila]["nombrecheque"];
        $idnumcom = $registro[$fila]["id_ctb_diario"];
        if ($idcuenta != $aux) {
            if($fila!=0){
                $finm=$i-1;//para el fin del merge
                $activa->mergeCells('A'.$iniciom.':A'.$finm);
                $activa->mergeCells('B'.$iniciom.':B'.$finm);
                $activa->getStyle('A'.$iniciom.':A'.$finm)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                $activa->getStyle('B'.$iniciom.':B'.$finm)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            }
            
            $i++;
            $i++;
            $sumd = 0;
            $sumh = 0;
            $aux = $idcuenta;
            if ($fila != array_key_last($registro)) {
                
                $activa->setCellValueExplicit('A' . $i, $codcta, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $activa->setCellValue('B' . $i, $nomcuenta);
                
                //VERIFICAR SI TIENE SALDO ANTERIOR
                if ($flag == 1) {
                    $isal = array_search
                    ($idcuenta, array_column($saldant, 'idcuensal'));
                    $saldo = ($isal != false) ? ($saldant[$isal]["sdebe"] - $saldant[$isal]["shaber"]) : 0;
                } else {
                    $saldo = 0;
                }
                $activa->setCellValueExplicit('F' . ($i - 1), 'SALDO ANT.:', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $activa->setCellValue('I' . ($i - 1), $saldo);
                $iniciom=$i;
            }
        }
        if ($fila != array_key_last($registro)) {
            $activa->setCellValue('D' . $i, $fecha);
            $activa->setCellValueExplicit('E' . $i, $partida, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $activa->setCellValue('F' . $i, $glosa);
            $activa->setCellValue('G' . $i, $debe);
            $activa->setCellValue('H' . $i, $haber);
            $activa->setCellValue('J' . $i, $nomchq);
            $activa->setCellValue('K' . $i, $numdoc);
            //SALDO
            $sumd = $sumd + $debe;
            $sumh = $sumh + $haber;
            $saldo = $saldo + $debe - $haber;
            //$activa->setCellValue('I' . $i, $saldo);
            $activa->setCellValue('I' . $i, '=I' . ($i - 1) . '+ G' . $i . '-H' . $i);
        }
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
        'namefile' => "Libro Mayor",
        'tipo' => "vnd.ms-excel",
        'data' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
    );
    echo json_encode($opResult);
    exit;
}
