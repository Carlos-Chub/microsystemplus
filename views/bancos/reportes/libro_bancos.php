<?php
session_start();
include '../../../src/funcphp/func_gen.php';
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
require '../../../fpdf/fpdf.php';

require '../../../vendor/autoload.php';
$hoy = date("Y-m-d");

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Round;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Trim;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$datos = $_POST["datosval"];
$inputs = $datos[0];
$selects = $datos[1];
$radios = $datos[2];
$tipo = $_POST["tipo"];
//-------VALIDACIONES------------------([[`finicio`,`ffin`,idcuenta],[`codofi`,`fondoid`],[`rcuentas`,`rfondos`,`rfechas`],[ $idusuario]],`pdf`,`libro_diario`,0)
if ($radios[0] == "anycuen" && $inputs[2] == 0) {
    echo json_encode(['status' => 0, 'mensaje' => 'Seleccione una cuenta contable']);
    return;
}
if ($inputs[0] > $inputs[1]) {
    echo json_encode(['status' => 0, 'mensaje' => 'Rango de fechas inválido']);
    return;
}

//-------ARMANDO LA CONSULTA------------
$condi = "";
//AGENCIA
if ($radios[2] == "anyofi") {
    $condi = $condi . " AND id_agencia=" . $selects[0];
}

//FUENTE DE FONDOS
if ($radios[1] == "anyf") {
    $condi = $condi . " AND id_fuente_fondo=" . $selects[1];
}

//CUENTA CONTABLE

if ($radios[0] == "anycuen") {
    $condi = $condi . " AND id_ctb_nomenclatura=" . $inputs[2];
}

//RANGO DE FECHAS
$condi = $condi . " AND feccnt BETWEEN '" . $inputs[0] . "' AND '" . $inputs[1] . "'";
$titlereport = " DEL " . date("d-m-Y", strtotime($inputs[0])) . " AL " . date("d-m-Y", strtotime($inputs[1]));

//CONSULTA FINAL
// $strquery = "SELECT cmov.* from ctb_diario_mov cmov INNER JOIN ctb_bancos ban ON ban.id_nomenclatura=cmov.id_ctb_nomenclatura WHERE ban.estado=1 AND cmov.estado=1" . $condi . " ORDER BY id_ctb_nomenclatura,feccnt";
$strquery = "SELECT cmov.* from ctb_diario_mov cmov WHERE id_ctb_nomenclatura IN (SELECT id_nomenclatura FROM ctb_bancos WHERE estado=1) 
AND cmov.estado=1 " . $condi . "
ORDER BY id_ctb_nomenclatura,feccnt";
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

/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++ CONSULTA PARTIDA DE APERTURA INGRESADA EN ENERO DEL AÑO DEL REPORTE+++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$fechaini = strtotime($inputs[0]);
$anioini = date("Y", $fechaini);
$inianio = $anioini . '-01-01';
$finanio = $anioini . '-01-30';
/* $dated = strtotime($inputs[0]);
$lastdate = strtotime(date("Y-m-t", $dated));
$lastday = date("d", $lastdate); */

$qparapr = "SELECT ccodcta,id_ctb_nomenclatura,cdescrip,SUM(debe)-SUM(haber) saldo,SUM(debe) debe,SUM(haber) haber from ctb_diario_mov WHERE estado=1 AND id_tipopol = 9 AND feccnt BETWEEN '" . $inianio . "' AND '" . $inputs[0] . "' GROUP BY ccodcta ORDER BY ccodcta";
$querys = mysqli_query($conexion, $qparapr);
$apertura[] = [];
$j = 0;
while ($fil = mysqli_fetch_array($querys)) {
    $apertura[$j] = $fil;
    $j++;
}
$haypartidaapr = ($j == 0) ? false : true;
$haypartidaapr = false;
//COMPROBAR SI HAY PARTIDA DE APERTURA
/* if ($j == 0) {
    echo json_encode(['status' => 0, 'mensaje' => 'No hay partida de apertura']);
    return;
} */

/*  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++ CONSULTA DE REGISTROS ANTES DE LA FECHA QUE SE INGRESO SIN LA PARTIDA DE APERTURA +++++++
    +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */

// $querysali = "SELECT ccodcta,id_ctb_nomenclatura,cdescrip,SUM(debe)-SUM(haber) saldo,SUM(debe) debe,SUM(haber) haber from ctb_diario_mov cmov INNER JOIN ctb_bancos ban ON ban.id_nomenclatura=cmov.id_ctb_nomenclatura WHERE ban.estado=1 AND cmov.estado=1 AND id_tipopol != 9 AND (feccnt >= '" . $inianio . "' AND feccnt < '" . $inputs[0] . "') GROUP BY ccodcta ORDER BY ccodcta";
$querysali = "SELECT ccodcta,cmov.id,cdescrip,ban.saldo_ini saldo from ctb_nomenclatura cmov 
                INNER JOIN ctb_bancos ban ON ban.id_nomenclatura=cmov.id 
                WHERE ban.estado=1 AND cmov.estado=1 ORDER BY ccodcta";
$querys = mysqli_query($conexion, $querysali);
$salinidata[] = [];
$j = 0;
while ($fil = mysqli_fetch_array($querys)) {
    $salinidata[$j] = $fil;
    $j++;
}
$hayanteriores = ($j == 0) ? false : true;

/* //VERIFICAR SI TIENE PARTIDA DE APERTURA
$idcuenta = $inputs[2];
if ($haypartidaapr) {
    $isal = array_search($idcuenta, array_column($apertura, 'id_ctb_nomenclatura'));
    $saldoapr = ($isal !== false) ? ($apertura[$isal]["debe"] - $apertura[$isal]["haber"]) : 0;
} else {
    $saldoapr = 0;
}
//VERIFICAR SI TIENE SALDO ANTERIOR
if ($hayanteriores) {
    $isal = array_search($idcuenta, array_column($salinidata, 'id_ctb_nomenclatura'));
    $saldoanterior = ($isal !== false) ? ($salinidata[$isal]["debe"] - $salinidata[$isal]["haber"]) : 56;
} else {
    $saldoanterior = 0;
}

echo json_encode(['status' => 0, 'mensaje' => $saldoanterior]);
return; */

/*  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++++++++++++ INFORMACION DE LA INSTITUCION +++++++++++++++++++++++++++++++++++++++++
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
        //printxls($ctbmovdata, [$flag, $salantdata]);
        printxls($ctbmovdata, $titlereport, $apertura, $salinidata, $info, $hayanteriores, $haypartidaapr);
        break;
    case 'pdf':
        //printpdf($ctbmovdata, [$titlereport, $flag, $salantdata], $info);
        printpdf($ctbmovdata, $titlereport, $apertura, $salinidata, $info, $hayanteriores, $haypartidaapr);
        break;
}

//funcion para generar pdf
function printpdf($registro, $titlereport, $apertura, $salinidata, $info, $hayanteriores, $haypartidaapr)
{

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
            $this->Cell(0, 5, 'LIBRO BANCOS ' . $this->datos, 0, 1, 'C', true);
            //Color de encabezado de lista
            $this->SetFillColor(555, 255, 204);
            //TITULOS DE ENCABEZADO DE TABLA
            $ancho_linea = 26;

            $this->Cell($ancho_linea - 5, 5, 'FECHA', 'B', 0, 'L');
            $this->Cell($ancho_linea - 5, 5, 'PARTIDA', 'B', 0, 'L');
            $this->Cell($ancho_linea + 10, 5, 'DESTIN.', 'B', 0, 'L');
            $this->Cell($ancho_linea * 3, 5, 'DESCRIPCION', 'B', 0, 'L');
            $this->Cell($ancho_linea * 1.5, 5, 'DEBE', 'B', 0, 'R');
            $this->Cell($ancho_linea * 1.5, 5, 'HABER', 'B', 0, 'R');
            $this->Cell($ancho_linea * 1.5, 5, 'SALDO', 'B', 1, 'R');
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
    $pdf = new PDF($institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins, $titlereport);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $fuente = "Courier";
    $tamanio_linea = 3;
    $ancho_linea2 = 26;
    $pdf->SetFont($fuente, '', 9);

    $saldant = $salinidata;
    $fila = 0;
    $aux = 0;
    $sumd = 0;
    $sumh = 0;
    $saldo = 0;
    $sumtd = 0;
    $sumth = 0;
    $header = true;
    $footer = false;
    while ($fila < count($registro)) {
        $partida = $registro[$fila]["numcom"];
        $fecha = date("d-m-Y", strtotime($registro[$fila]["feccnt"]));
        $numdoc = $registro[$fila]["numdoc"];
        $glosa = utf8_decode(trim($registro[$fila]["glosa"]));
        $idcuenta = $registro[$fila]["id_ctb_nomenclatura"];
        $codcta = $registro[$fila]["ccodcta"];
        $nomcuenta = utf8_decode($registro[$fila]["cdescrip"]);
        $nomcheque = utf8_decode($registro[$fila]["nombrecheque"]);
        $debe = $registro[$fila]["debe"];
        $haber = $registro[$fila]["haber"];
        $idnumcom = $registro[$fila]["id_ctb_diario"];

        if ($header) {
            //ENCABEZADOS CUENTAS INDIVIDUALES
            $pdf->SetFont($fuente, 'B', 10);
            $pdf->CellFit($ancho_linea2 * 3, $tamanio_linea + 1, 'Cuenta: ' . $codcta, 'B', 0, 'L', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea2 * 3, $tamanio_linea + 1, 'Nombre: ' . $nomcuenta, 'B', 0, 'L', 0, '', 1, 0);

            //VERIFICAR SI TIENE PARTIDA DE APERTURA
            if ($haypartidaapr) {
                $isal = array_search($idcuenta, array_column($apertura, 'id_ctb_nomenclatura'));
                $saldoapr = ($isal !== false) ? ($apertura[$isal]["debe"] - $apertura[$isal]["haber"]) : 0;
            } else {
                $saldoapr = 0;
            }
            //VERIFICAR SI TIENE SALDO ANTERIOR
            if ($hayanteriores) {
                $isal = array_search($idcuenta, array_column($saldant, 'id'));
                $saldoanterior = ($isal !== false) ? ($saldant[$isal]["saldo"]) : 0;
            } else {
                $saldoanterior = 0;
            }
            $saldo = $saldoapr + $saldoanterior;

            $pdf->CellFit($ancho_linea2 * 4 + 14, $tamanio_linea + 1, 'Saldo Inicial: ' . number_format($saldo, 2, '.', ','), 'B', 1, 'R', 0, '', 1, 0);
            $header = false;
        }

        //DETALLES PARTIDAS INDIVIDUALES
        $pdf->SetFont($fuente, '', 9);
        $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea, $fecha, '', 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea, $partida, '', 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2 + 10, $tamanio_linea, $nomcheque, '', 0, 'L', 0, '', 1, 0);

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->MultiCell($ancho_linea2 * 3, 3, $glosa . ' - ' . $numdoc);
        $x += $ancho_linea2 * 3;
        $y2 = $pdf->GetY();
        if ($y > $y2) {
            //CUANDO SE PASA A LA SIGUIENTE HOJA LA DESCRIPCION
            $y3 = 3;
            $y = $y2;
        } else {
            $y3 = $y2 - $y;
        }
        $pdf->SetXY($x, $pdf->GetY() - $y3);
        $pdf->CellFit($ancho_linea2 * 1.5, $tamanio_linea, number_format($debe, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2 * 1.5, $tamanio_linea, number_format($haber, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
        //SALDO 
        $sumd = $sumd + $debe;
        $sumh = $sumh + $haber;
        $saldo = $saldo + $debe - $haber;
        $pdf->CellFit($ancho_linea2 * 1.5, $tamanio_linea, number_format($saldo, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
        $pdf->SetY($y + $y3);
        $pdf->Ln(2);

        $sumtd = $sumtd + $debe;
        $sumth = $sumth + $haber;

        if ($fila != array_key_last($registro)) {
            if ($idcuenta != $registro[$fila + 1]["id_ctb_nomenclatura"]) {
                $header = true;
                $footer = true;
            }
        } else {
            $footer = true;
        }
        if ($footer) {
            $pdf->Ln(1);
            $pdf->Cell($ancho_linea2 * 6, $tamanio_linea, ' ', '', 0, 'R');
            $pdf->Cell($ancho_linea2 * 1.5, $tamanio_linea + 2, number_format($sumd, 2, '.', ','), 'BT', 0, 'R');
            $pdf->Cell($ancho_linea2 * 1.5, $tamanio_linea + 2, number_format($sumh, 2, '.', ','), 'BT', 1, 'R');
            $pdf->Cell($ancho_linea2 * 6, $tamanio_linea, ' ', '', 0, 'R');
            $pdf->Cell($ancho_linea2 * 3, $tamanio_linea / 4, ' ', 'B', 1, 'R');
            $sumd = 0;
            $sumh = 0;
            $pdf->Ln(5);
            $footer = false;
        }
        $fila++;
    }
    $pdf->Cell($ancho_linea2 * 6, $tamanio_linea, 'TOTAL GENERAL: ', '', 0, 'R');
    $pdf->Cell($ancho_linea2 * 1.5, $tamanio_linea + 2, number_format($sumtd, 2, '.', ','), 'BT', 0, 'R');
    $pdf->Cell($ancho_linea2 * 1.5, $tamanio_linea + 2, number_format($sumth, 2, '.', ','), 'BT', 1, 'R');
    $pdf->Cell($ancho_linea2 * 6, $tamanio_linea, ' ', '', 0, 'R');
    $pdf->Cell($ancho_linea2 * 3, $tamanio_linea / 4, ' ', 'B', 1, 'R');

    ob_start();
    $pdf->Output();
    $pdfData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "Libro Bancos",
        'tipo' => "pdf",
        'data' => "data:application/pdf;base64," . base64_encode($pdfData)
    );
    echo json_encode($opResult);
}

//funcion para generar archivo excel
function printxls($registro, $titlereport, $apertura, $salinidata, $info, $hayanteriores, $haypartidaapr)
{
    require '../../../vendor/autoload.php';

    $excel = new Spreadsheet();
    $activa = $excel->getActiveSheet();
    $activa->setTitle("Libro Bancos");


    $activa->getColumnDimension("A")->setWidth(15);
    $activa->getColumnDimension("B")->setWidth(25);
    $activa->getColumnDimension("C")->setWidth(15);
    $activa->getColumnDimension("D")->setWidth(10);
    $activa->getColumnDimension("E")->setWidth(70);
    $activa->getColumnDimension("F")->setWidth(15);
    $activa->getColumnDimension("G")->setWidth(15);
    $activa->getColumnDimension("H")->setWidth(15);
    $activa->getColumnDimension("I")->setWidth(25);
    $activa->getColumnDimension("J")->setWidth(15);

    $activa->setCellValue('A1', 'CUENTA');
    $activa->setCellValue('B1', 'NOMBRE CUENTA');
    $activa->setCellValue('C1', 'FECHA');
    $activa->setCellValue('D1', 'PARTIDA');
    $activa->setCellValue('E1', 'DESCRIPCION');
    $activa->setCellValue('F1', 'DEBE');
    $activa->setCellValue('G1', 'HABER');
    $activa->setCellValue('H1', 'SALDO');
    $activa->setCellValue('I1', 'NOMBRE CHEQUE');
    $activa->setCellValue('J1', 'NUMDOC');
    $saldant = $salinidata;

    $saldo = 0;
    $iniciom = 4;
    $aux = 0;
    $sumd = 0;
    $sumh = 0;
    $sumtd = 0;
    $sumth = 0;
    $fila = 0;
    $i = 2;
    $header = true;
    $footer = false;
    while ($fila < count($registro)) {
        $partida = $registro[$fila]["numcom"];
        $fecha = date("d-m-Y", strtotime($registro[$fila]["feccnt"]));
        $numdoc = $registro[$fila]["numdoc"];
        $numdoc = ($numdoc == "" || $numdoc == NULL) ? " " : $numdoc;
        $glosa = trim($registro[$fila]["glosa"]);
        $idcuenta = $registro[$fila]["id_ctb_nomenclatura"];
        $codcta = $registro[$fila]["ccodcta"];
        $nomcuenta = $registro[$fila]["cdescrip"];
        $debe = $registro[$fila]["debe"];
        $haber = $registro[$fila]["haber"];
        $nomchq = $registro[$fila]["nombrecheque"];
        $idnumcom = $registro[$fila]["id_ctb_diario"];

        if ($header) {
            //VERIFICAR SI TIENE PARTIDA DE APERTURA
            if ($haypartidaapr) {
                $isal = array_search($idcuenta, array_column($apertura, 'id_ctb_nomenclatura'));
                $saldoapr = ($isal !== false) ? ($apertura[$isal]["debe"] - $apertura[$isal]["haber"]) : 0;
            } else {
                $saldoapr = 0;
            }
            //VERIFICAR SI TIENE SALDO ANTERIOR
            if ($hayanteriores) {
                $isal = array_search($idcuenta, array_column($saldant, 'id'));
                $saldoanterior = ($isal !== false) ? ($saldant[$isal]["saldo"]) : 0;
            } else {
                $saldoanterior = 0;
            }
            $saldo = $saldoapr + $saldoanterior;
            $i++;
            $finm = $i - 1; //para el fin del merge
            // $activa->mergeCells('A' . $iniciom . ':A' . $finm);
            // $activa->mergeCells('B' . $iniciom . ':B' . $finm);
            // $activa->getStyle('A' . $iniciom . ':A' . $finm)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            // $activa->getStyle('B' . $iniciom . ':B' . $finm)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

            $i++;
            $sumd = 0;
            $sumh = 0;
            $aux = $idcuenta;
            $activa->getStyle('A' . $i . ':B' . $i)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('CCCCCC');
            $activa->getStyle('A' . $i . ':B' . $i)->getFont()->setBold(true);
            $activa->setCellValueExplicit('A' . $i, $codcta, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $activa->setCellValue('B' . $i, $nomcuenta);

            $activa->setCellValueExplicit('E' . ($i - 1), 'SALDO ANT.:', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $activa->setCellValue('H' . ($i - 1), $saldo);
            $iniciom = $i;
            $header = false;
        }

        $activa->setCellValue('C' . $i, $fecha);
        $activa->setCellValueExplicit('D' . $i, $partida, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('E' . $i, $glosa);
        $activa->setCellValue('F' . $i, $debe);
        $activa->setCellValue('G' . $i, $haber);
        $activa->setCellValue('I' . $i, $nomchq);
        $activa->setCellValueExplicit('J' . $i, $numdoc, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        //SALDO
        $sumd = $sumd + $debe;
        $sumh = $sumh + $haber;
        $saldo = $saldo + $debe - $haber;
        //$activa->setCellValue('I' . $i, $saldo);
        $activa->setCellValue('H' . $i, '=H' . ($i - 1) . '+ F' . $i . '-G' . $i);

        if ($fila != array_key_last($registro)) {
            if ($idcuenta != $registro[$fila + 1]["id_ctb_nomenclatura"]) {
                $header = true;
                $footer = true;
            }
        } else {
            $footer = true;
        }
        if ($footer) {
            $i++;
            $activa->setCellValue('E' . $i, 'RESUMEN CUENTAS');
            $activa->setCellValue('F' . $i, $sumd);
            $activa->setCellValue('G' . $i, $sumh);
            $activa->getStyle('E' . $i . ':G' . $i)->getFont()->setBold(true);
            $footer = false;
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
        'namefile' => "Libro Bancos",
        'tipo' => "xlsx",
        'data' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
    );
    echo json_encode($opResult);
    exit;
}
