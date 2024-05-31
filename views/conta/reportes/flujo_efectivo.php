<?php
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '3600');
session_start();
include '../../../src/funcphp/func_gen.php';
include '../funciones/func_ctb.php';
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
require '../../../fpdf/fpdf.php';
date_default_timezone_set('America/Guatemala');
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

//FECHA
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
$mesini = date("m", $fechaini);
$anioini = date("Y", $fechaini);
$mesfin = date("m", $fechafin);
$aniofin = date("Y", $fechafin);

if ($anioini != $aniofin) {
    echo json_encode(['status' => 0, 'mensaje' => 'Las fechas tienen que ser del mismo año']);
    return;
}
/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    ++++ CONSULTA LOS DATOS SEGUN LAS FECHAS DADAS SIN TOMAR EN CUENTA LAS PARTIDAS DE APERTURA Y CIERRE +++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$valparams = [];
$typparams = [];
$key = 0;
$condi = "";
if ($radios[0] == "anyofi") {
    $condi = $condi . " AND id_agencia=?";
    $valparams[$key] = $selects[0];
    $typparams[$key] = 'i';
    $key++;
}
$valparams[$key] = $inputs[0];
$typparams[$key] = 's';
$valparams[$key + 1] = $inputs[1];
$typparams[$key + 1] = 's';

$titlereport = " DEL " . date("d-m-Y", strtotime($inputs[0])) . " AL " . date("d-m-Y", strtotime($inputs[1]));

$query = "SELECT ccodcta,id_ctb_nomenclatura,cdescrip,SUM(debe)-SUM(haber) saldo,SUM(debe) debe,SUM(haber) haber 
                from ctb_diario_mov 
                WHERE estado=1 " . $condi . " AND (id_tipopol != 9 AND id_tipopol != 13) AND (feccnt BETWEEN ? AND ?) 
                AND SUBSTR(ccodcta,1,1) IN (SELECT clase FROM ctb_parametros_cuentas WHERE id_tipo>=1 AND id_tipo<=5) 
                GROUP BY ccodcta ORDER BY ccodcta";
$response = executequery($query, $valparams, $typparams, $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$ctbmovdata = $response[0];
$haydata = ((count($ctbmovdata)) > 0) ? true : false;
// if (!$haydata) {
//     echo json_encode(['status' => 0, 'mensaje' => 'No hay datos en la fecha indicada']);
//     return;
// }

/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++ CONSULTA PARTIDA DE APERTURA INGRESADA EN LA FECHA DEL BALANCE+++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */

$inianio = $anioini . '-01-01';
$finanio = $anioini . '-01-30';
$dated = strtotime($inputs[0]);
$lastdate = strtotime(date("Y-m-t", $dated));
$lastday = date("d", $lastdate);

//$qparapr = "SELECT ccodcta,id_ctb_nomenclatura,cdescrip,SUM(debe)-SUM(haber) saldo,SUM(debe) debe,SUM(haber) haber from ctb_diario_mov WHERE estado=1 AND id_tipopol = 9 AND feccnt BETWEEN '" . $inianio . "' AND '" . $inputs[1] . "' GROUP BY ccodcta ORDER BY ccodcta";
$query = "SELECT ccodcta,id_ctb_nomenclatura,cdescrip,SUM(debe)-SUM(haber) saldo,SUM(debe) debe,SUM(haber) haber 
                from ctb_diario_mov 
                WHERE estado=1 " . $condi . " AND id_tipopol = 9 AND (feccnt BETWEEN ? AND ?) GROUP BY ccodcta ORDER BY ccodcta";
$response = executequery($query, $valparams, $typparams, $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$apertura = $response[0];
$flag = ((count($apertura)) > 0) ? true : false;
if (!$flag) {
    echo json_encode(['status' => 0, 'mensaje' => 'No hay partida de apertura']);
    return;
}
/*  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++ CONSULTA DE REGISTROS ANTES DE LA FECHA QUE SE INGRESO SIN LA PARTIDA DE APERTURA +++++++
    +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$valparams[$key] = $inianio;
$typparams[$key] = 's';
$valparams[$key + 1] = $inputs[0];
$typparams[$key + 1] = 's';
$query = "SELECT ccodcta,id_ctb_nomenclatura,cdescrip,SUM(debe)-SUM(haber) saldo,SUM(debe) debe,SUM(haber) haber 
    from ctb_diario_mov 
    WHERE estado=1 " . $condi . " AND id_tipopol != 9 AND id_tipopol != 13 
    AND SUBSTR(ccodcta,1,1) IN (SELECT clase FROM ctb_parametros_cuentas WHERE id_tipo>=1 AND id_tipo<=3) AND (feccnt >=? AND feccnt < ?) 
    GROUP BY ccodcta ORDER BY ccodcta";
$response = executequery($query, $valparams, $typparams, $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$salinidata = $response[0];
$flag = ((count($salinidata)) > 0) ? true : false;
$hayanteriores = $flag;
$hayanteriores = false; //COMENTAR LA LINEA SI SE NECESITE TRAER LOS DATOS DE PARTIDAS ANTERIORES
/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++ CONSULTA DE TODAS LAS CUENTAS DE ACTIVO, PASIVO, PATRIMONIO, INGRESOS Y EGRESOS ++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$query = "SELECT * from ctb_nomenclatura 
            WHERE estado=? AND substr(ccodcta,1,1) IN (SELECT clase FROM ctb_parametros_cuentas WHERE id_tipo>=1 AND id_tipo<=5)
            ORDER BY ccodcta";

$response = executequery($query, [1], ['i'], $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$nomenclatura = $response[0];
$flag = ((count($nomenclatura)) > 0) ? true : false;

if (count(array_filter(array_column($nomenclatura, 'categoria_flujo'), function ($var) {
    return ($var > 0);
})) < 1) {
    echo json_encode(['status' => 0, 'mensaje' => 'No hay Cuentas Parametrizadas para el calculo del Flujo de Efectivo, Favor parametrizar las cuentas y volver a intentar<br> 
    <button type="button" class="btn btn-outline-primary" onclick="printdiv(`paramflujoefectivo`, `#cuadro`, `ctb008`, `0`)">Parametrizar</button>']);
    return;
}
/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++++++++++++++++++ CUENTAS PARAMETRIZADAS PAL BALANCE Y ER ++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$query = "SELECT * FROM ctb_parametros_cuentas WHERE id_tipo>=? AND id_tipo<=5;";
$response = executequery($query, [1], ['i'], $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$parametros = $response[0];
$flag = ((count($parametros)) > 0) ? true : false;
if (!$flag) {
    echo json_encode(['status' => 0, 'mensaje' => 'No hay cuentas configuradas para el calculo de balances y ER']);
    return;
}
$cuentasingreso = array_filter($parametros, function ($var) {
    return $var['id_tipo'] == 4;
});
$cuentasegreso = array_filter($parametros, function ($var) {
    return $var['id_tipo'] == 5;
});

/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++++++ INFORMACION DE LA INSTITUCION Y AGENCIA  +++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
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
        printxls($ctbmovdata, $titlereport, $apertura, $salinidata, $info, $hayanteriores, $haydata, $nomenclatura, $parametros, $cuentasingreso, $cuentasegreso);
        break;
    case 'pdf':
        printpdf($ctbmovdata, $titlereport, $apertura, $salinidata, $info, $hayanteriores, $haydata, $nomenclatura, $parametros, $cuentasingreso, $cuentasegreso);
        break;
}


//funcion para generar pdf
function printpdf($registro, $titlereport, $apertura, $salinidata, $info, $hayanteriores, $haydata, $nomenclatura, $parametros, $cuentasingreso, $cuentasegreso)
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
            $this->Cell(0, 5, 'FLUJO DE EFECTIVO' . $this->datos, 0, 1, 'C', true);
            $this->Cell(0, 5, '(CIFRAS EN QUETZALES)', 0, 1, 'C', true);
            //Color de encabezado de lista
            $this->SetFillColor(555, 255, 204);
            //TITULOS DE ENCABEZADO DE TABLA
            $ancho_linea = 32;

            $this->Cell($ancho_linea * 2, 5, 'DESCRIPCION', 'B', 0, 'C');
            $this->Cell($ancho_linea, 5, 'SALDO INICIAL', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'SALDO FINAL', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'DIFERENCIAS', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'SALDO FINAL', 'B', 1, 'R');
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
    $pdf = new PDF($institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins, $titlereport);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $fuente = "Courier";
    $tamanio_linea = 4;
    $ancho_linea2 = 32;
    $pdf->SetFont($fuente, 'B', 11);
    $totalini = 0;
    $totalaumento = 0;
    $totaldismin = 0;
    $totalsaldofin = 0;

    $aumento_disminucion = 0;
    $efectivo_inicio = 0;
    /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++ PARTE 0: RESULTADO DEL EJERCICIO DEL ER FECHA ACTUAL +++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $ingresos = 0;
    foreach ($cuentasingreso as $ingreso) {
        $ingresos += array_sum(array_column(calculo($registro, $ingreso['clase'], 1), 'saldo'));
    }
    $egresos = 0;
    foreach ($cuentasegreso as $egreso) {
        $egresos += array_sum(array_column(calculo($registro, $egreso['clase'], 1), 'saldo'));
    }

    //$ingresos = array_sum(array_column(calculo($registro, 6, 1), 'saldo'));
    $ingresos = $ingresos * (-1);
    //$egresos = array_sum(array_column(calculo($registro, 7, 1), 'saldo'));

    $pdf->CellFit($ancho_linea2 / 3, $tamanio_linea, ' ', '', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 * 5, $tamanio_linea, 'RESULTADO DEL EJERCICIO', '', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit(0, $tamanio_linea, number_format(($ingresos - $egresos), 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
    $pdf->Ln(4);

    $aumento_disminucion += ($ingresos - $egresos);
    /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++ PARTE 1: GASTOS QUE NO REQUIRIERON DE EFECTIVO +++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $pdf->CellFit(0, $tamanio_linea, '1.    GASTOS QUE NO REQUIRIERON DE EFECTIVO', '', 1, 'L', 0, '', 1, 0);
    $pdf->Ln(2);
    $i = 0;
    $sumadif = 0;

    $pdf->SetFont($fuente, '', 11);
    while ($i < count($nomenclatura)) {
        $id = $nomenclatura[$i]['id'];
        $cuenta = $nomenclatura[$i]['ccodcta'];
        $nombrecuenta = utf8_decode($nomenclatura[$i]['cdescrip']);
        $tipo = $nomenclatura[$i]['tipo'];
        $categoria = $nomenclatura[$i]['categoria_flujo'];
        if ($categoria == 1 && $tipo == "D") {
            $salapertura = array_sum(array_column(calculo2($apertura, $cuenta), 'saldo'));
            $salanterior = ($hayanteriores) ? array_sum(array_column(calculo2($salinidata, $cuenta), 'saldo')) : 0;
            $salapertura += $salanterior;

            $salfecha = array_sum(array_column(calculo2($registro, $cuenta), 'saldo'));
            $salfecha = $salapertura + $salfecha;
            $diferencia = $salfecha - $salapertura;
            $sumadif += $diferencia;
            $efectivo_inicio += $salapertura;

            if ($salapertura != 0 || $salfecha != 0) {
                $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea, $nombrecuenta, '', 0, 'L', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($salapertura, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($salfecha, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($diferencia, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
            }
        }
        $i++;
    }
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea, ' ', '', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit(0, $tamanio_linea, number_format($sumadif, 2, '.', ','), 'B', 1, 'R', 0, '', 1, 0);
    $pdf->Ln(4);
    $aumento_disminucion += ($sumadif);
    /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++ PARTE 2: EFECTIVOS GENERADOS POR ACTIVIDADES DE OPERACION ++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $pdf->CellFit(0, $tamanio_linea, '2.    FLUJO DE EFECTIVOS POR ACTIVIDADES DE OPERACION', '', 1, 'L', 0, '', 1, 0);
    $pdf->Ln(2);
    $pdf->SetFont($fuente, '', 11);
    $i = 0;
    $sumadif = 0;
    while ($i < count($nomenclatura)) {
        $id = $nomenclatura[$i]['id'];
        $cuenta = $nomenclatura[$i]['ccodcta'];
        $nombrecuenta = utf8_decode($nomenclatura[$i]['cdescrip']);
        $tipo = $nomenclatura[$i]['tipo'];
        $categoria = $nomenclatura[$i]['categoria_flujo'];
        if ($categoria == 2 && $tipo == "D") {
            $salapertura = array_sum(array_column(calculo2($apertura, $cuenta), 'saldo'));
            $salanterior = ($hayanteriores) ? array_sum(array_column(calculo2($salinidata, $cuenta), 'saldo')) : 0;
            $salapertura += $salanterior;

            $salfecha = array_sum(array_column(calculo2($registro, $cuenta), 'saldo'));
            $salfecha = $salapertura + $salfecha;
            $diferencia = $salfecha - $salapertura;
            $sumadif += $diferencia;
            $efectivo_inicio += $salapertura;

            if ($salapertura != 0 || $salfecha != 0) {
                $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea, $nombrecuenta, '', 0, 'L', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($salapertura, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($salfecha, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($diferencia, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
            }
        }
        $i++;
    }
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea, ' ', '', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit(0, $tamanio_linea, number_format($sumadif, 2, '.', ','), 'B', 1, 'R', 0, '', 1, 0);
    $pdf->Ln(4);

    $aumento_disminucion += ($sumadif);
    /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++ PARTE 3: FLUJO DE EFECTIVOS POR ACTIVIDADES DE INVERSION +++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $pdf->CellFit(0, $tamanio_linea, '3.    FLUJO DE EFECTIVOS POR ACTIVIDADES DE INVERSION', '', 1, 'L', 0, '', 1, 0);
    $pdf->Ln(2);
    $pdf->SetFont($fuente, '', 11);
    $i = 0;
    $sumadif = 0;
    while ($i < count($nomenclatura)) {
        $id = $nomenclatura[$i]['id'];
        $cuenta = $nomenclatura[$i]['ccodcta'];
        $nombrecuenta = utf8_decode($nomenclatura[$i]['cdescrip']);
        $tipo = $nomenclatura[$i]['tipo'];
        $categoria = $nomenclatura[$i]['categoria_flujo'];
        if ($categoria == 3 && $tipo == "D") {
            $salapertura = array_sum(array_column(calculo2($apertura, $cuenta), 'saldo'));
            $salanterior = ($hayanteriores) ? array_sum(array_column(calculo2($salinidata, $cuenta), 'saldo')) : 0;
            $salapertura += $salanterior;

            $salfecha = array_sum(array_column(calculo2($registro, $cuenta), 'saldo'));
            $salfecha = $salapertura + $salfecha;
            $diferencia = $salfecha - $salapertura;
            $sumadif += $diferencia;
            $efectivo_inicio += $salapertura;

            if ($salapertura != 0 || $salfecha != 0) {
                $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea, $nombrecuenta, '', 0, 'L', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($salapertura, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($salfecha, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($diferencia, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
            }
        }
        $i++;
    }
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea, ' ', '', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit(0, $tamanio_linea, number_format($sumadif, 2, '.', ','), 'B', 1, 'R', 0, '', 1, 0);
    $pdf->Ln(4);

    $aumento_disminucion += ($sumadif);
    /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++ PARTE 4: FLUJO DE EFECTIVOS POR ACTIVIDADES DE FINANCIAMENTO +++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $pdf->CellFit(0, $tamanio_linea, '4.    FLUJO DE EFECTIVOS POR ACTIVIDADES DE FINANCIAMIENTO', '', 1, 'L', 0, '', 1, 0);
    $pdf->Ln(2);
    $pdf->SetFont($fuente, '', 11);
    $i = 0;
    $sumadif = 0;
    while ($i < count($nomenclatura)) {
        $id = $nomenclatura[$i]['id'];
        $cuenta = $nomenclatura[$i]['ccodcta'];
        $nombrecuenta = utf8_decode($nomenclatura[$i]['cdescrip']);
        $tipo = $nomenclatura[$i]['tipo'];
        $categoria = $nomenclatura[$i]['categoria_flujo'];
        if ($categoria == 4 && $tipo == "D") {
            $salapertura = array_sum(array_column(calculo2($apertura, $cuenta), 'saldo'));
            $salanterior = ($hayanteriores) ? array_sum(array_column(calculo2($salinidata, $cuenta), 'saldo')) : 0;
            $salapertura += $salanterior;

            $salfecha = array_sum(array_column(calculo2($registro, $cuenta), 'saldo'));
            $salfecha = $salapertura + $salfecha;
            $diferencia = $salfecha - $salapertura;
            $sumadif += $diferencia;
            $efectivo_inicio += $salapertura;

            if ($salapertura != 0 || $salfecha != 0) {
                $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea, $nombrecuenta, '', 0, 'L', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($salapertura, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($salfecha, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($diferencia, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
            }
        }
        $i++;
    }
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea, ' ', '', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit(0, $tamanio_linea, number_format($sumadif, 2, '.', ','), 'B', 1, 'R', 0, '', 1, 0);
    $pdf->Ln(2);

    $aumento_disminucion += ($sumadif);

    $pdf->CellFit($ancho_linea2 * 5, $tamanio_linea, 'AUMENTO O DISMINUCION EN EFECTIVO', '', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($aumento_disminucion, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
    $pdf->Ln(2);

    $efectivo_inicio = $efectivo_inicio * (-1);
    $pdf->CellFit($ancho_linea2 * 5, $tamanio_linea, utf8_decode('EFECTIVO AL INICIO DEL AÑO'), '', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($efectivo_inicio, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
    $pdf->Ln(2);

    $efectivo_final = $efectivo_inicio + $aumento_disminucion;
    $pdf->CellFit($ancho_linea2 * 5, $tamanio_linea, 'EFECTIVO AL FINAL DEL PROCESO', '', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($efectivo_final, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
    $pdf->Ln(2);

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
//funcion para generar archivo excel
function printxls($registro, $titlereport, $apertura, $salinidata, $info, $hayanteriores, $haydata, $nomenclatura, $parametros, $cuentasingreso, $cuentasegreso)
{
    require '../../../vendor/autoload.php';

    $excel = new Spreadsheet();
    $activa = $excel->getActiveSheet();
    $activa->setTitle("Flujo de efectivo");
    $activa->getColumnDimension("A")->setWidth(50);
    $activa->getColumnDimension("B")->setWidth(25);
    $activa->getColumnDimension("C")->setWidth(25);
    $activa->getColumnDimension("D")->setWidth(25);
    $activa->getColumnDimension("E")->setWidth(25);

    $activa->setCellValue('A1', 'DESCRIPCION');
    $activa->setCellValue('B1', 'SALDO INICIAL');
    $activa->setCellValue('C1', 'SALDO FINAL');
    $activa->setCellValue('D1', 'DIFERENCIA');
    $activa->setCellValue('E1', 'SALDO FINAL');

    $linea = 3;

    $aumento_disminucion = 0;
    $efectivo_inicio = 0;
    /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++ PARTE 0: RESULTADO DEL EJERCICIO DEL ER FECHA ACTUAL +++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $ingresos = 0;
    foreach ($cuentasingreso as $ingreso) {
        $ingresos += array_sum(array_column(calculo($registro, $ingreso['clase'], 1), 'saldo'));
    }
    $egresos = 0;
    foreach ($cuentasegreso as $egreso) {
        $egresos += array_sum(array_column(calculo($registro, $egreso['clase'], 1), 'saldo'));
    }

    //$ingresos = array_sum(array_column(calculo($registro, 6, 1), 'saldo'));
    $ingresos = $ingresos * (-1);
    //$egresos = array_sum(array_column(calculo($registro, 7, 1), 'saldo'));

    $activa->setCellValue('A' . $linea, 'RESULTADO DEL EJERCICIO');
    $activa->setCellValueExplicit('E' . $linea, ($ingresos - $egresos), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

    $aumento_disminucion += ($ingresos - $egresos);
    /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++ PARTE 1: GASTOS QUE NO REQUIRIERON DE EFECTIVO +++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $linea += 2;
    $activa->setCellValue('A' . $linea, 'GASTOS QUE NO REQUIRIERON DE EFECTIVO');
    $linea++;

    $ini = $linea;
    $i = 0;
    $sumadif = 0;
    while ($i < count($nomenclatura)) {
        $id = $nomenclatura[$i]['id'];
        $cuenta = $nomenclatura[$i]['ccodcta'];
        $nombrecuenta = $nomenclatura[$i]['cdescrip'];
        $tipo = $nomenclatura[$i]['tipo'];
        $categoria = $nomenclatura[$i]['categoria_flujo'];
        if ($categoria == 1 && $tipo == "D") {
            $salapertura = array_sum(array_column(calculo2($apertura, $cuenta), 'saldo'));
            $salanterior = ($hayanteriores) ? array_sum(array_column(calculo2($salinidata, $cuenta), 'saldo')) : 0;
            $salapertura += $salanterior;

            $salfecha = array_sum(array_column(calculo2($registro, $cuenta), 'saldo'));
            $salfecha = $salapertura + $salfecha;
            $diferencia = $salfecha - $salapertura;
            $sumadif += $diferencia;
            $efectivo_inicio += $salapertura;
            if ($salapertura != 0 || $salfecha != 0) {
                $activa->setCellValue('A' . $linea, $nombrecuenta);
                $activa->setCellValueExplicit('B' . $linea, $salapertura, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $activa->setCellValueExplicit('C' . $linea, $salfecha, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $activa->setCellValueExplicit('D' . ($linea), '=C' . $linea . '-B' . $linea . '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
                $linea++;
            }
        }
        $i++;
    }
    $activa->setCellValueExplicit('E' . ($linea), '=SUM(D' . $ini . ':D' . $linea . ')', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);

    //=SUMA(D6:D8)
    $aumento_disminucion += ($sumadif);
    /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++ PARTE 2: EFECTIVOS GENERADOS POR ACTIVIDADES DE OPERACION ++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $linea += 2;
    $activa->setCellValue('A' . $linea, 'FLUJO DE EFECTIVOS POR ACTIVIDADES DE OPERACION');
    $linea++;
    $ini = $linea;

    $i = 0;
    $sumadif = 0;
    while ($i < count($nomenclatura)) {
        $id = $nomenclatura[$i]['id'];
        $cuenta = $nomenclatura[$i]['ccodcta'];
        $nombrecuenta = $nomenclatura[$i]['cdescrip'];
        $tipo = $nomenclatura[$i]['tipo'];
        $categoria = $nomenclatura[$i]['categoria_flujo'];
        if ($categoria == 2 && $tipo == "D") {
            $salapertura = array_sum(array_column(calculo2($apertura, $cuenta), 'saldo'));
            $salanterior = ($hayanteriores) ? array_sum(array_column(calculo2($salinidata, $cuenta), 'saldo')) : 0;
            $salapertura += $salanterior;

            $salfecha = array_sum(array_column(calculo2($registro, $cuenta), 'saldo'));
            $salfecha = $salapertura + $salfecha;
            $diferencia = $salfecha - $salapertura;
            $sumadif += $diferencia;
            $efectivo_inicio += $salapertura;

            if ($salapertura != 0 || $salfecha != 0) {
                $activa->setCellValue('A' . $linea, $nombrecuenta);
                $activa->setCellValueExplicit('B' . $linea, $salapertura, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $activa->setCellValueExplicit('C' . $linea, $salfecha, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $activa->setCellValueExplicit('D' . ($linea), '=C' . $linea . '-B' . $linea . '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
                $linea++;
            }
        }
        $i++;
    }
    $activa->setCellValueExplicit('E' . ($linea), '=SUM(D' . $ini . ':D' . $linea . ')', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);

    $aumento_disminucion += ($sumadif);
    /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++ PARTE 3: FLUJO DE EFECTIVOS POR ACTIVIDADES DE INVERSION +++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $linea += 2;
    $activa->setCellValue('A' . $linea, 'FLUJO DE EFECTIVOS POR ACTIVIDADES DE INVERSION');
    $linea++;
    $ini = $linea;

    $i = 0;
    $sumadif = 0;
    while ($i < count($nomenclatura)) {
        $id = $nomenclatura[$i]['id'];
        $cuenta = $nomenclatura[$i]['ccodcta'];
        $nombrecuenta = $nomenclatura[$i]['cdescrip'];
        $tipo = $nomenclatura[$i]['tipo'];
        $categoria = $nomenclatura[$i]['categoria_flujo'];
        if ($categoria == 3 && $tipo == "D") {
            $salapertura = array_sum(array_column(calculo2($apertura, $cuenta), 'saldo'));
            $salanterior = ($hayanteriores) ? array_sum(array_column(calculo2($salinidata, $cuenta), 'saldo')) : 0;
            $salapertura += $salanterior;

            $salfecha = array_sum(array_column(calculo2($registro, $cuenta), 'saldo'));
            $salfecha = $salapertura + $salfecha;
            $diferencia = $salfecha - $salapertura;
            $sumadif += $diferencia;
            $efectivo_inicio += $salapertura;

            if ($salapertura != 0 || $salfecha != 0) {
                $activa->setCellValue('A' . $linea, $nombrecuenta);
                $activa->setCellValueExplicit('B' . $linea, $salapertura, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $activa->setCellValueExplicit('C' . $linea, $salfecha, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $activa->setCellValueExplicit('D' . ($linea), '=C' . $linea . '-B' . $linea . '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
                $linea++;
            }
        }
        $i++;
    }
    $activa->setCellValueExplicit('E' . ($linea), '=SUM(D' . $ini . ':D' . $linea . ')', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);

    $aumento_disminucion += ($sumadif);
    /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++ PARTE 4: FLUJO DE EFECTIVOS POR ACTIVIDADES DE FINANCIAMENTO +++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $linea += 2;
    $activa->setCellValue('A' . $linea, 'FLUJO DE EFECTIVOS POR ACTIVIDADES DE FINANCIAMIENTO');
    $linea++;
    $ini = $linea;

    $i = 0;
    $sumadif = 0;
    while ($i < count($nomenclatura)) {
        $id = $nomenclatura[$i]['id'];
        $cuenta = $nomenclatura[$i]['ccodcta'];
        $nombrecuenta = $nomenclatura[$i]['cdescrip'];
        $tipo = $nomenclatura[$i]['tipo'];
        $categoria = $nomenclatura[$i]['categoria_flujo'];
        if ($categoria == 4 && $tipo == "D") {
            $salapertura = array_sum(array_column(calculo2($apertura, $cuenta), 'saldo'));
            $salanterior = ($hayanteriores) ? array_sum(array_column(calculo2($salinidata, $cuenta), 'saldo')) : 0;
            $salapertura += $salanterior;

            $salfecha = array_sum(array_column(calculo2($registro, $cuenta), 'saldo'));
            $salfecha = $salapertura + $salfecha;
            $diferencia = $salfecha - $salapertura;
            $sumadif += $diferencia;
            $efectivo_inicio += $salapertura;

            if ($salapertura != 0 || $salfecha != 0) {
                $activa->setCellValue('A' . $linea, $nombrecuenta);
                $activa->setCellValueExplicit('B' . $linea, $salapertura, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $activa->setCellValueExplicit('C' . $linea, $salfecha, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $activa->setCellValueExplicit('D' . ($linea), '=C' . $linea . '-B' . $linea . '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
                $linea++;
            }
        }
        $i++;
    }
    $activa->setCellValueExplicit('E' . ($linea), '=SUM(D' . $ini . ':D' . $linea . ')', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);

    $aumento_disminucion += ($sumadif);
    $linea += 2;
    $linaumendis = $linea;
    $activa->setCellValue('A' . $linea, 'AUMENTO O DISMINUCION EN EFECTIVO');
    $activa->setCellValueExplicit('E' . ($linea), '=SUM(E3:E' . ($linea - 1) . ')', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
    $linea++;


    $efectivo_inicio = $efectivo_inicio * (-1);
    $linea += 2;
    $linefini = $linea;
    $activa->setCellValue('A' . $linea, 'EFECTIVO AL INICIO DEL AÑO');
    $activa->setCellValueExplicit('E' . ($linea), '=SUM(B5:B' . ($linea - 5) . ')*(-1)', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
    $linea++;


    $efectivo_final = $efectivo_inicio + $aumento_disminucion;

    $linea += 2;
    $activa->setCellValue('A' . $linea, 'EFECTIVO AL FINAL DEL PROCESO');
    $activa->setCellValueExplicit('E' . ($linea), '=E' . ($linaumendis) . '+E' . $linefini, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA);
    $linea++;

    ob_start();
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel, 'Xlsx');
    $writer->save("php://output");
    $xlsData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "Flujo de efectivo",
        'tipo' => "vnd.ms-excel",
        'data' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
    );
    echo json_encode($opResult);
    exit;
}
