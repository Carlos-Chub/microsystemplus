<?php
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '3600');
session_start();
include '../../../src/funcphp/func_gen.php';
include '../funciones/func_ctb.php';
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
/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++++++++++++++++++++++++++++ VALIDACIONES +++++++++++++++++++++++++++++++++++++++++++++++++++
    ++++++++++++++ [`finicio`,`ffin`],[`codofi`,`fondoid`],[`rfondos`,`ragencia`,[ $idusuario]] ++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$datos = $_POST["datosval"];
$inputs = $datos[0];
$selects = $datos[1];
$radios = $datos[2];
$archivo = $datos[3];
$tipo = $_POST["tipo"];
if (!validateDate($inputs[0], 'Y-m-d') || !validateDate($inputs[1], 'Y-m-d')) {
    echo json_encode(['status' => 0, 'mensaje' => 'Fecha inválida, ingrese una fecha correcta']);
    return;
}
if ($inputs[0] > $inputs[1]) {
    echo json_encode(['status' => 0, 'mensaje' => 'Rango de fechas inválido']);
    return;
}
$fechaini = strtotime($inputs[0]);
$fechafin = strtotime($inputs[1]);
$anioini = date("Y", $fechaini);
$aniofin = date("Y", $fechafin);

if ($anioini != $aniofin) {
    echo json_encode(['status' => 0, 'mensaje' => 'Las fechas tienen que ser del mismo año']);
    return;
}
/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++ CUENTAS PARAMETRIZADAS PAL BALANCE ++++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$query = "SELECT * FROM ctb_parametros_cuentas WHERE id_tipo>=? AND id_tipo<=5;";
$response = executequery($query, [4], ['i'], $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$parametros = $response[0];
$flag = ((count($parametros)) > 0) ? true : false;
if (!$flag) {
    echo json_encode(['status' => 0, 'mensaje' => 'No hay cuentas configuradas para el calculo del Estado de Resultados']);
    return;
}
$cuentasingreso = array_filter($parametros, function ($var) {
    return $var['id_tipo'] == 4;
});
$cuentasegreso = array_filter($parametros, function ($var) {
    return $var['id_tipo'] == 5;
});
/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++ ARMANDO LA QUERY FINAL ++++++++++++++++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$valparams = [];
$typparams = [];
$key = 0;
$condi = "";
//AGENCIA
if ($radios[1] == "anyofi") {
    $condi = $condi . " AND id_agencia=?";
    $valparams[$key] = $selects[0];
    $typparams[$key] = 'i';
    $key++;
}
//FUENTE DE FONDOS
if ($radios[0] == "anyf") {
    $condi = $condi . " AND id_fuente_fondo=?";
    $valparams[$key] = $selects[1];
    $typparams[$key] = 'i';
    $key++;
}
$titlereport = " DEL " . date("d-m-Y", strtotime($inputs[0])) . " AL " . date("d-m-Y", strtotime($inputs[1]));

$valparams[$key] = $inputs[0];
$typparams[$key] = 's';
$valparams[$key + 1] = $inputs[1];
$typparams[$key + 1] = 's';
//CONSULTA FINAL
$query = "SELECT ccodcta,id_ctb_nomenclatura,SUM(debe) sumdeb, SUM(haber) sumhab from ctb_diario_mov 
    WHERE estado=1 " . $condi . " AND (feccnt BETWEEN ? AND ?) 
    AND substr(ccodcta,1,1) IN (SELECT clase FROM ctb_parametros_cuentas WHERE id_tipo>=4 AND id_tipo<=5)
    GROUP BY ccodcta ORDER BY ccodcta;";

$response = executequery($query, $valparams, $typparams, $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$ctbmovdata = $response[0];
$flag = ((count($ctbmovdata)) > 0) ? true : false;
if (!$flag) {
    echo json_encode(['status' => 0, 'mensaje' => 'No hay datos en la fecha indicada']);
    return;
}
/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++++++++++++++++++++++++++ CUENTAS CONTABLES ++++++++++++++++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$query = "SELECT id,ccodcta,cdescrip from ctb_nomenclatura 
    WHERE estado=? AND substr(ccodcta,1,1) IN (SELECT clase FROM ctb_parametros_cuentas WHERE id_tipo>=4 AND id_tipo<=5) AND LENGTH(ccodcta) <=10 
    ORDER BY ccodcta;";
$response = executequery($query, [1], ['i'], $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$nomenclatura = $response[0];

/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++ INFO INSTITUCION ++++++++++++++++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$query = "SELECT * FROM clhpzzvb_bd_general_coopera.info_coperativa ins
    INNER JOIN tb_agencia ag ON ag.id_institucion=ins.id_cop where ag.id_agencia=?";
$response = executequery($query, [$_SESSION['id_agencia']], ['i'], $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$info = $response[0];
$flag = ((count($info)) > 0) ? true : false;
if (!$flag) {
    echo json_encode(['status' => 0, 'mensaje' => 'Institucion asignada a la agencia no encontrada']);
    return;
}
//TIPO DE ARCHIVO A IMPRIMIR
switch ($tipo) {
    case 'xlsx';
        printxls($ctbmovdata, [$nomenclatura], $cuentasingreso, $cuentasegreso);
        break;
    case 'pdf':
        printpdf($ctbmovdata, [$titlereport, $nomenclatura], $info, $cuentasingreso, $cuentasegreso);
        break;
}

//funcion para generar pdf
function printpdf($registro, $datos, $info, $cuentasingreso, $cuentasegreso)
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
            $this->Cell(0, 5, 'ESTADO DE RESULTADOS ' . $this->datos[0], 0, 1, 'C', true);
            $this->Cell(0, 5, '(CIFRAS EN QUETZALES)', 0, 1, 'C', true);
            //Color de encabezado de lista
            $this->SetFillColor(555, 255, 204);
            //TITULOS DE ENCABEZADO DE TABLA
            $ancho_linea = 24;

            $this->Cell($ancho_linea - 5, 5, 'CUENTA', 'B', 0, 'L');
            $this->Cell($ancho_linea * 2 + 5, 5, 'DESCRIPCION', 'B', 0, 'L');
            $this->Cell($ancho_linea, 5, 'SUB-CUENTA', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'CUENTA', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'SUBGRUPO', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'GRUPO', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'CLASE', 'B', 1, 'R');
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
    $tamanio_linea = 3;
    $ancho_linea2 = 24;
    $pdf->SetFont($fuente, '', 8);
    $nivelant = 0;
    $totali = 0;
    $totalg = 0;
    $totales = 0;
    $cuentas = $datos[1];
    $printresumeningreso = false;
    $f = 0;
    while ($f < count($cuentas)) {
        $id = $cuentas[$f]["id"];
        $cuenta = $cuentas[$f]["ccodcta"];
        $nombre = $cuentas[$f]["cdescrip"];
        $nivel = strlen($cuenta);
        $monto = 0;
        //BUSCAR CUENTA EN LOS MOVIMIENTOS
        $fila = 0;
        while ($fila < count($registro)) {
            $codcta = $registro[$fila]["ccodcta"];
            $sdebe = $registro[$fila]["sumdeb"];
            $shaber = $registro[$fila]["sumhab"];
            if ($cuenta == substr($codcta, 0, $nivel)) {
                /*                 if (substr($codcta, 0, 1) == 6) {
                    $sal = $shaber - $sdebe;
                    //$sal = $sdebe - $shaber;
                }
                if (substr($codcta, 0, 1) == 7) {
                     $sal = $sdebe - $shaber;
                    //$sal = $shaber - $sdebe;
                } */
                $sal = $sdebe - $shaber;
                $monto = $monto + $sal;
            }
            $fila++;
        }
        $clase = substr($cuenta, 0, 1);
        $result = array_search($clase, array_column($cuentasingreso, 'clase'));
        if ($result !== false) {
            $monto = $monto * (-1);
        }

        $nivel1 = ($nivel == 1) ? number_format($monto, 2, '.', ',') : " ";
        $nivel2 = ($nivel == 2 || $nivel == 3) ? number_format($monto, 2, '.', ',') : " ";
        $nivel3 = ($nivel == 4 || $nivel == 5) ? number_format($monto, 2, '.', ',') : " ";
        $nivel4 = ($nivel == 6 || $nivel == 7) ? number_format($monto, 2, '.', ',') : " ";
        $nivel5 = ($nivel == 8 || $nivel == 9) ? number_format($monto, 2, '.', ',') : " ";

        if ($monto != 0) {
            if ($nivel < $nivelant) {
                $pdf->Ln(3);
            }
            $nivelant = $nivel;
            $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea, $cuenta, '', 0, 'L', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea2 * 2 + 5, $tamanio_linea, utf8_decode($nombre), '', 0, 'L', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea2, $tamanio_linea, $nivel5, '', 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea2, $tamanio_linea, $nivel4, '', 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea2, $tamanio_linea, $nivel3, '', 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea2, $tamanio_linea, $nivel2, '', 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea2, $tamanio_linea, $nivel1, '', 1, 'R', 0, '', 1, 0);

            //***************SUMATORIAS*********************
            $result = array_search($cuenta, array_column($cuentasingreso, 'clase'));
            if ($result !== false) {
                $totali = $totali + $monto;
            }
            $result = array_search($cuenta, array_column($cuentasegreso, 'clase'));
            if ($result !== false) {
                $totalg = $totalg + $monto;
            }
            $pdf->Ln(1);
        }
        if ($f != array_key_last($cuentas)) {
            $nextcuenta = $cuentas[$f + 1]["ccodcta"];
            $result = array_search($nextcuenta, array_column($cuentasegreso, 'clase'));
            if ($result !== false && $printresumeningreso == false) {
                $printresumeningreso = true;
                $pdf->SetFont($fuente, 'B', 9);
                $pdf->Ln(2);
                $pdf->CellFit($ancho_linea2 * 6, $tamanio_linea, 'TOTAL INGRESOS: ', '', 0, 'L', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2 * 2 - 1, $tamanio_linea, number_format($totali, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
                $pdf->SetFont($fuente, '', 8);
                $pdf->Ln(2);
            }
            // if (strlen($nextcuenta) < $nivel && $monto != 0) {
            //     $pdf->Ln(2);
            // }
        } else {
            $pdf->Ln(2);
            $pdf->SetFont($fuente, 'B', 9);
            $pdf->CellFit($ancho_linea2 * 6, $tamanio_linea, 'TOTAL EGRESOS: ', '', 0, 'L', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea2 * 2 - 1, $tamanio_linea, number_format($totalg, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
            $pdf->Ln(3);
        }
        $f++;
    }
    $pdf->Ln(4);
    $pdf->CellFit($ancho_linea2 * 6, $tamanio_linea, 'RESULTADO NETO: ', '', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 * 2 - 1, $tamanio_linea, number_format($totali - $totalg, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
    $pdf->Ln(4);
    $pdf->SetFont($fuente, '', 9);
    $pdf->MultiCell(0, 4, strtoupper(utf8_decode('El infrascrito perito contador, con número de registro xxxx ante la Superintendencia de Administración Tributaria certifica: que el estado de resultados que antecede muestra resultado de las operaciones de la cooperativa por el periodo comprendido ' . $datos[0] . ', el cual fue obtenido de los registros contables de la entidad.')));
    // $pdf->Ln(15);
    // $pdf->CellFit($ancho_linea2 - 7, $tamanio_linea, ' ', '', 0, 'L', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea, 'CONTADOR', 'T', 0, 'C', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea, ' ', '', 0, 'L', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea, 'REPRESENTANTE LEGAL', 'T', 1, 'C', 0, '', 1, 0);
    $pdf->firmas(2, ['CONTADOR', 'REPRESENTANTE LEGAL']);

    ob_start();
    $pdf->Output();
    $pdfData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "Estado de resultados",
        'tipo' => "pdf",
        'data' => "data:application/pdf;base64," . base64_encode($pdfData)
    );
    echo json_encode($opResult);
}

//funcion para generar archivo excel
function printxls($registro, $datos, $cuentasingreso, $cuentasegreso)
{
    require '../../../vendor/autoload.php';

    $excel = new Spreadsheet();
    $activa = $excel->getActiveSheet();
    $activa->setTitle("Estado de resultados");

    $activa->getColumnDimension("A")->setWidth(15);
    $activa->getColumnDimension("B")->setWidth(65);
    $activa->getColumnDimension("C")->setWidth(20);
    $activa->getColumnDimension("D")->setWidth(20);
    $activa->getColumnDimension("E")->setWidth(20);
    $activa->getColumnDimension("F")->setWidth(20);
    $activa->getColumnDimension("G")->setWidth(20);

    $activa->setCellValue('A1', 'CUENTA');
    $activa->setCellValue('B1', 'NOMBRE CUENTA');
    $activa->setCellValue('C1', 'SUB-CUENTA');
    $activa->setCellValue('D1', 'CUENTA');
    $activa->setCellValue('E1', 'SUBGRUPO');
    $activa->setCellValue('F1', 'GRUPO');
    $activa->setCellValue('G1', 'CLASE');
    //-------
    $nivelant = 0;
    $totali = 0;
    $totalg = 0;
    $cuentas = $datos[0];
    $f = 0;
    $i = 2;
    while ($f < count($cuentas)) {
        $id = $cuentas[$f]["id"];
        $cuenta = $cuentas[$f]["ccodcta"];
        $nombre = $cuentas[$f]["cdescrip"];
        $nivel = strlen($cuenta);
        $monto = 0;
        //BUSCAR CUENTA EN LOS MOVIMIENTOS
        $fila = 0;
        while ($fila < count($registro)) {
            $codcta = $registro[$fila]["ccodcta"];
            $sdebe = $registro[$fila]["sumdeb"];
            $shaber = $registro[$fila]["sumhab"];

            if ($cuenta == substr($codcta, 0, $nivel)) {
                $sal = $sdebe - $shaber;
                $monto = $monto + $sal;
            }
            $fila++;
        }
        $clase = substr($cuenta, 0, 1);
        $result = array_search($clase, array_column($cuentasingreso, 'clase'));
        if ($result !== false) {
            $monto = $monto * (-1);
        }

        $nivel1 = ($nivel == 1) ? $monto : " ";
        $nivel2 = ($nivel == 2 || $nivel == 3) ? $monto : " ";
        $nivel3 = ($nivel == 4 || $nivel == 5) ? $monto : " ";
        $nivel4 = ($nivel == 6 || $nivel == 7) ? $monto : " ";
        $nivel5 = ($nivel == 8 || $nivel == 9) ? $monto : " ";

        if ($monto != 0) {
            if ($nivel < $nivelant) {
                $i++;
            }
            $nivelant = $nivel;
            $activa->setCellValueExplicit('A' . $i, $cuenta, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $activa->setCellValue('B' . $i, $nombre);
            $activa->setCellValue('C' . $i, $nivel5);
            $activa->setCellValue('D' . $i, $nivel4);
            $activa->setCellValue('E' . $i, $nivel3);
            $activa->setCellValue('F' . $i, $nivel2);
            $activa->setCellValue('G' . $i, $nivel1);

            //***************SUMATORIAS*********************
            $result = array_search($cuenta, array_column($cuentasingreso, 'clase'));
            if ($result !== false) {
                $totali = $totali + $monto;
            }
            $result = array_search($cuenta, array_column($cuentasegreso, 'clase'));
            if ($result !== false) {
                $totalg = $totalg + $monto;
            }
            $i++;
        }
        // if ($f != array_key_last($cuentas)) {
        //     $nextcuenta = $cuentas[$f + 1]["ccodcta"];
        //     if (strlen($nextcuenta) < $nivel && $monto != 0) {
        //         $i++;
        //     }
        // }

        $f++;
    }


    ob_start();
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel, 'Xlsx');
    $writer->save("php://output");
    $xlsData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "Estado de resultados",
        'tipo' => "vnd.ms-excel",
        'data' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
    );
    echo json_encode($opResult);
    exit;
}