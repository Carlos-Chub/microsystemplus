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

$datos = $_POST["datosval"];
$inputs = $datos[0];
$selects = $datos[1];
$radios = $datos[2];
$archivo = $datos[3];
$tipo = $_POST["tipo"];
$nivelinit = $selects[2];
$nivelfin = $selects[3];

/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++++++++++++++++++++++++++++ VALIDACIONES +++++++++++++++++++++++++++++++++++++++++++++++++++
    +++[`finicio`,`ffin`],[`codofi`,`fondoid`,`nivelinit`,`nivelfin`],[`rfondos`,`ragencia`],[$idusuario ] +++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
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
//NIVELES
if ($nivelinit > $nivelfin) {
    echo json_encode(['status' => 0, 'mensaje' => 'Rango de niveles inválido']);
    return;
}
/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++ CUENTAS PARAMETRIZADAS PAL BALANCE ++++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
// $query = "SELECT * FROM ctb_parametros_cuentas WHERE (id_tipo>=? AND id_tipo<=3) || id_tipo=6;";
$query = "SELECT * FROM ctb_parametros_cuentas WHERE (id_tipo>=? AND id_tipo<=6)";
$response = executequery($query, [1], ['i'], $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$parametros = $response[0];
$flag = ((count($parametros)) > 0) ? true : false;
if (!$flag) {
    echo json_encode(['status' => 0, 'mensaje' => 'No hay cuentas configuradas para el calculo del balance']);
    return;
}

$cuentasactivo = array_filter($parametros, function ($var) {
    return $var['id_tipo'] == 1;
});
$reguladorasactivo = array_filter($parametros, function ($var) {
    return $var['id_tipo'] == 6;
});
$cuentaspasivo = array_filter($parametros, function ($var) {
    return $var['id_tipo'] == 2;
});
$cuentascapital = array_filter($parametros, function ($var) {
    return $var['id_tipo'] == 3;
});
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

$query = "SELECT ccodcta,id_ctb_nomenclatura,SUM(debe) sumdeb, SUM(haber) sumhab from ctb_diario_mov 
    WHERE estado=1 " . $condi . " AND (feccnt BETWEEN ? AND ?) 
    AND substr(ccodcta,1,1) IN (SELECT clase FROM ctb_parametros_cuentas WHERE (id_tipo>=1 AND id_tipo<=3) || id_tipo=6)
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
    ++++++++++++++++++++++++++++++++++ ARMANDO LA QUERY FINAL ER +++++++++++++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$query = "SELECT cg.id_tipo idparam,cc.id,ccodcta,cdescrip from ctb_nomenclatura cc 
INNER JOIN ctb_parametros_general cg ON cg.id_ctb_nomenclatura=cc.id
WHERE cg.id_tipo>=1 AND cg.id_tipo<=2 AND cc.estado=?";
$response = executequery($query, [1], ['i'], $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$cuentacontableer = $response[0];
$flager = ((count($cuentacontableer)) > 0) ? true : false;
$resultadoer = 0;
if ($flager) {
    $query = "SELECT ccodcta,id_ctb_nomenclatura,SUM(debe) sumdeb, SUM(haber) sumhab,SUM(debe)-SUM(haber) saldo from ctb_diario_mov 
    WHERE estado=1 " . $condi . " AND (feccnt BETWEEN ? AND ?) 
    AND substr(ccodcta,1,1) IN (SELECT clase FROM ctb_parametros_cuentas WHERE (id_tipo>=4 AND id_tipo<=5))
    GROUP BY ccodcta ORDER BY ccodcta;";

    $response = executequery($query, $valparams, $typparams, $conexion);
    if (!$response[1]) {
        echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
        return;
    }
    $registroer = $response[0];
    $flager = ((count($registroer)) > 0) ? true : false;
    /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++++++++++  RESULTADO DEL EJERCICIO DEL ER FECHA ACTUAL +++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $ingresos = 0;
    foreach ($cuentasingreso as $ingreso) {
        $ingresos += array_sum(array_column(calculo($registroer, $ingreso['clase'], 1), 'saldo'));
    }
    $egresos = 0;
    foreach ($cuentasegreso as $egreso) {
        $egresos += array_sum(array_column(calculo($registroer, $egreso['clase'], 1), 'saldo'));
    }
    $ingresos = $ingresos * (-1);

    $resultadoer = $ingresos - $egresos;
    // echo json_encode(['status' => 0, 'mensaje' => $resultadoer]);
    // return;
    $idcuentaganancia=buscarDatoPorId($cuentacontableer, 1,'idparam','id');
    $idcuentaperdida=buscarDatoPorId($cuentacontableer, 2,'idparam','id');

    $cuentaganancia=buscarDatoPorId($cuentacontableer, 1,'idparam','ccodcta');
    $cuentaperdida=buscarDatoPorId($cuentacontableer, 2,'idparam','ccodcta');
    $cuentacontableshow=($resultadoer>0)?$cuentaganancia:$cuentaperdida;
    $idcuentacontableshow=($resultadoer>0)?$idcuentaganancia:$idcuentaperdida;

    $keyregistro=buscarDatoPorId($ctbmovdata, ($resultadoer>0)?$idcuentaganancia:$idcuentaperdida,'id_ctb_nomenclatura');
    if($keyregistro!==false){
        $ctbmovdata[$keyregistro]['sumdeb']=$egresos;
        $ctbmovdata[$keyregistro]['sumhab']=$ingresos;
    }
    else{
        //ccodcta,id_ctb_nomenclatura,SUM(debe) sumdeb, SUM(haber) sumhab
        $ctbmovdata[] = ['ccodcta' => $cuentacontableshow, 'id_ctb_nomenclatura' =>$idcuentacontableshow, 'sumdeb' => $egresos, 'sumhab' => $ingresos];
    }
}
// echo json_encode(['status' => 0, 'mensaje' => $ctbmovdata]);
//     return;
// $resultadoer = $ingresos - $egresos;

/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++++++++++++++++++++++++++ CUENTAS CONTABLES ++++++++++++++++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$query = "SELECT id,ccodcta,cdescrip from ctb_nomenclatura 
WHERE estado=? AND substr(ccodcta,1,1) IN (SELECT clase FROM ctb_parametros_cuentas WHERE (id_tipo>=1 AND id_tipo<=3) || id_tipo=6) AND LENGTH(ccodcta) <=10 
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
        printxls($ctbmovdata, [$nomenclatura], $cuentasactivo, $cuentaspasivo, $cuentascapital, $reguladorasactivo,$cuentacontableer,$resultadoer);
        break;
    case 'pdf':
        printpdf($ctbmovdata, [$titlereport, $nomenclatura], $info, $nivelinit, $nivelfin, $cuentasactivo, $cuentaspasivo, $cuentascapital, $reguladorasactivo,$cuentacontableer,$resultadoer);
        break;
}
//funcion para generar pdf
function printpdf($registro, $datos, $info, $nivelinit, $nivelfin,  $cuentasactivo, $cuentaspasivo, $cuentascapital, $reguladorasactivo,$cuentacontableer,$resultadoer)
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
        public $nivelfin;
        public $nivelinit;

        public function __construct($institucion, $pathlogo, $pathlogoins, $oficina, $direccion, $email, $telefono, $nit, $datos, $nivelinit, $nivelfin)
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
            $this->nivelfin = $nivelfin;
            $this->nivelinit = $nivelinit;
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

            $this->SetFont($fuente, 'B', 8);
            //TITULO DE REPORTE
            $this->SetFillColor(204, 229, 255);
            $this->Cell(0, 5, 'BALANCE GENERAL ' . $this->datos[0], 0, 1, 'C', true);
            $this->Cell(0, 5, '(CIFRAS EN QUETZALES)', 0, 1, 'C', true);
            //Color de encabezado de lista
            $this->SetFillColor(555, 255, 204);
            //TITULOS DE ENCABEZADO DE TABLA
            /*  $ancho_linea = 21; */
            $ancho_linea = 195 / ($this->nivelfin - $this->nivelinit + 4);
            $this->Cell($ancho_linea, 5, 'CUENTA', 'B', 0, 'L');
            $this->Cell($ancho_linea * 2, 5, 'DESCRIPCION', 'B', 0, 'L');
            $monbal = ['Nivel 1', 'Nivel 2', 'Nivel 3', 'Nivel 4', 'Nivel 5', 'Nivel 6'];
            $niv = $this->nivelfin;
            while ($niv >= $this->nivelinit) {
                $this->CellFit($ancho_linea, 5, $monbal[$niv - 1], 'B', 0, 'R', 0, '', 1, 0);
                $niv--;
            }

            /* 
            $this->Cell($ancho_linea, 5, 'Nivel 6', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'Nivel 5', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'Nivel 4', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'Nivel 3', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'Nivel 2', 'B', 0, 'R');
            $this->Cell($ancho_linea, 5, 'Nivel 1', 'B', 1, 'R'); */
            $this->Ln(6);
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
    $pdf = new PDF($institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins, $datos, $nivelinit, $nivelfin);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $fuente = "Courier";
    $tamanio_linea = 3;
    $ancho_linea2 = 195 / ($nivelfin - $nivelinit + 4);
    $pdf->SetFont($fuente, '', 7);
    //1 3 4 6 8 10
    $printresumenactivo = false;
    $nivelant = 0;
    $totalactivo = 0;
    $totalpasivo = 0;
    $totalcapital = 0;
    $cuentas = $datos[1];
    $f = 0;
    while ($f < count($cuentas)) {
        $id = $cuentas[$f]["id"];
        $cuenta = trim($cuentas[$f]["ccodcta"]);
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
                $clase = substr($codcta, 0, 1);
                $result = array_search($clase, array_column($cuentasactivo, 'clase'));
                if ($result !== false) {
                    $sal = $sdebe - $shaber;
                } else {
                    $sal = $shaber - $sdebe;
                }
                // if (substr($codcta, 0, 1) >= 1 && substr($codcta, 0, 1) <= 2) {
                //     $sal = $sdebe - $shaber;
                // }
                // if (substr($codcta, 0, 1) >= 3 && substr($codcta, 0, 1) <= 5) {
                //     $sal = $shaber - $sdebe;
                // }
                $monto = $monto + $sal;
            }
            $fila++;
        }

        $nivel1 = ($nivel == 1) ? number_format($monto, 2, '.', ',') : " ";
        $nivel2 = ($nivel == 2 || $nivel == 3) ? number_format($monto, 2, '.', ',') : " ";
        $nivel3 = ($nivel == 4 || $nivel == 5) ? number_format($monto, 2, '.', ',') : " ";
        $nivel4 = ($nivel == 6 || $nivel == 7) ? number_format($monto, 2, '.', ',') : " ";
        $nivel5 = ($nivel == 8 || $nivel == 9) ? number_format($monto, 2, '.', ',') : " ";
        $nivel6 = ($nivel == 10 || $nivel == 11) ? number_format($monto, 2, '.', ',') : " ";

        if ($monto != 0) {
            $monbal = [$nivel1, $nivel2, $nivel3, $nivel4, $nivel5, $nivel6];
            $niveles = [[1, 2, 4, 6, 8, 10, 12], [1, 3, 5, 7, 9, 11, 13]];
            $niv = $nivelfin;
            $flag = false;
            //SE VAN A IMPRIMIR LOS NIVELES QUE ESTAN EN EL RANGO SELECCIONADO, PARA ESO SE ACTIVA EL FLAG
            while ($niv >= $nivelinit) {
                if ($niveles[0][$niv - 1] == $nivel || $niveles[1][$niv - 1] == $nivel) {
                    $flag = true;
                }
                $niv--;
            }
            //SE VAN A IMPRIMIR LOS NIVELES QUE ESTAN EN EL RANGO SELECCIONADO SI LA BANDERA ESTA ACTIVA
            if ($flag) {
                if ($nivel < $nivelant) {
                    $pdf->Ln(3);
                }
                $pdf->CellFit($ancho_linea2, $tamanio_linea, $cuenta, '', 0, 'L', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea, utf8_decode($nombre), '', 0, 'L', 0, '', 1, 0);

                $niv = $nivelfin;
                while ($niv >= $nivelinit) {
                    $pdf->CellFit($ancho_linea2, $tamanio_linea, $monbal[$niv - 1], '', 0, 'R', 0, '', 1, 0);
                    $niv--;
                }
                $pdf->Ln(3);
                $nivelant = $nivel;
            }

            //***************SUMATORIAS*********************
            $result = array_search($cuenta, array_column($cuentasactivo, 'clase'));
            if ($result !== false) {
                $totalactivo = $totalactivo + $monto;
            }
            $result = array_search($cuenta, array_column($reguladorasactivo, 'clase'));
            if ($result !== false) {
                $totalactivo = $totalactivo + $monto;
            }
            $result = array_search($cuenta, array_column($cuentaspasivo, 'clase'));
            if ($result !== false) {
                $totalpasivo = $totalpasivo + $monto;
            }
            $result = array_search($cuenta, array_column($cuentascapital, 'clase'));
            if ($result !== false) {
                $totalcapital = $totalcapital + $monto;
            }
            //else {
            //     $sal = $shaber - $sdebe;
            // }

            // $activo = ($cuenta <= 2) ? $monto : 0;
            // $totalactivo = $totalactivo + $activo;

            // $pasivo = ($cuenta >= 3 && $cuenta <= 5) ? $monto : 0;
            // $totalpasivo = $totalpasivo + $pasivo;
        }
        $pdf->SetFont($fuente, 'B', 9);
        if ($f != array_key_last($cuentas)) {
            //----------
            $nextcuenta = $cuentas[$f + 1]["ccodcta"];
            $result = array_search($nextcuenta, array_column($cuentaspasivo, 'clase'));
            if ($result !== false && $printresumenactivo == false) {
                $printresumenactivo = true;
                $pdf->Ln(2);
                $pdf->CellFit($ancho_linea2, $tamanio_linea, ' ', '', 0, 'L', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea, 'TOTAL ACTIVO', '', 0, 'L', 0, '', 1, 0);
                $pdf->CellFit(($nivelfin - $nivelinit + 1) * $ancho_linea2, $tamanio_linea, number_format($totalactivo, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
                $pdf->Ln(3);
            }
        } else {
            $pdf->Ln(2);
            $pdf->CellFit($ancho_linea2, $tamanio_linea, ' ', '', 0, 'L', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea, 'TOTAL PASIVO Y CAPITAL', '', 0, 'L', 0, '', 1, 0);
            $pdf->CellFit(($nivelfin - $nivelinit + 1) * $ancho_linea2, $tamanio_linea, number_format($totalpasivo + $totalcapital, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
            $pdf->Ln(3);
        }
        $pdf->SetFont($fuente, '', 8);
        $f++;
    }
    $pdf->Ln(4);
    $pdf->MultiCell(0, 4, strtoupper(utf8_decode('El infrascrito perito contador, con número de registro xxxx ante la Superintendencia de Administración Tributaria certifica: que el balance general que antecede muestra la situación financiera de la cooperativa ' . $datos[0] . ', el cual fue obtenido de los registros contables de la entidad.')));

    $pdf->firmas(2, ['CONTADOR', 'REPRESENTANTE LEGAL']);

    ob_start();
    $pdf->Output();
    $pdfData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "Balance General",
        'tipo' => "pdf",
        'data' => "data:application/pdf;base64," . base64_encode($pdfData)
    );
    echo json_encode($opResult);
}

//funcion para generar archivo excel
function printxls($registro, $datos,  $cuentasactivo, $cuentaspasivo, $cuentascapital, $reguladorasactivo,$cuentacontableer,$resultadoer)
{
    require '../../../vendor/autoload.php';

    $excel = new Spreadsheet();
    $activa = $excel->getActiveSheet();
    $activa->setTitle("BalanceGeneral");

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
    $activa->setCellValue('C1', 'NIVEL 6');
    $activa->setCellValue('D1', 'NIVEL 5');
    $activa->setCellValue('E1', 'NIVEL 4');
    $activa->setCellValue('F1', 'NIVEL 3');
    $activa->setCellValue('G1', 'NIVEL 2');
    $activa->setCellValue('H1', 'NIVEL 1');

    $totalactivo = 0;
    $totalpasivo = 0;
    $totalcapital = 0;
    $nivelant = 0;
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
                $clase = substr($codcta, 0, 1);
                $result = array_search($clase, array_column($cuentasactivo, 'clase'));
                if ($result !== false) {
                    $sal = $sdebe - $shaber;
                } else {
                    $sal = $shaber - $sdebe;
                }
                // if (substr($codcta, 0, 1) >= 1 && substr($codcta, 0, 1) <= 2) {
                //     $sal = $sdebe - $shaber;
                // }
                // if (substr($codcta, 0, 1) >= 3 && substr($codcta, 0, 1) <= 5) {
                //     $sal = $shaber - $sdebe;
                // }
                $monto = $monto + $sal;
            }
            $fila++;
        }
        $nivel1 = ($nivel == 1) ? $monto : " ";
        $nivel2 = ($nivel == 2 || $nivel == 3) ? $monto : " ";
        $nivel3 = ($nivel == 4 || $nivel == 5) ? $monto : " ";
        $nivel4 = ($nivel == 6 || $nivel == 7) ? $monto : " ";
        $nivel5 = ($nivel == 8 || $nivel == 9) ? $monto : " ";
        $nivel6 = ($nivel == 10 || $nivel == 11) ? $monto : " ";
        //----
        /*         if ($monto != 0) {
            $monbal = [$nivel1, $nivel2, $nivel3, $nivel4, $nivel5, $nivel6];
            $niveles = [[1, 3, 4, 6, 8, 10, 12], [1, 3, 5, 7, 9, 11]];
            $niv = $nivelfin;
            $flag = false;
            //SE VAN A IMPRIMIR LOS NIVELES QUE ESTAN EN EL RANGO SELECCIONADO, PARA ESO SE ACTIVA EL FLAG
            while ($niv >= $nivelinit) {
                if ($niveles[0][$niv - 1] == $nivel || $niveles[1][$niv - 1] == $nivel) $flag = true;
                $niv--;
            }
            //SE VAN A IMPRIMIR LOS NIVELES QUE ESTAN EN EL RANGO SELECCIONADO SI LA BANDERA ESTA ACTIVA
            if ($flag) {
                if($nivel<$nivelant){
                    $pdf->Ln(3); 
                }
                $pdf->CellFit($ancho_linea2, $tamanio_linea, $cuenta, '', 0, 'L', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea, utf8_decode($nombre), '', 0, 'L', 0, '', 1, 0);

                $niv = $nivelfin;
                while ($niv >= $nivelinit) {
                    $pdf->CellFit($ancho_linea2, $tamanio_linea, $monbal[$niv - 1], '', 0, 'R', 0, '', 1, 0);
                    $niv--;
                }
                $pdf->Ln(3); 
                $nivelant=$nivel;
            }

            //***************SUMATORIAS*********************
            $activo = ($cuenta <= 2) ? $monto : 0;
            $totalactivo = $totalactivo + $activo;

            $pasivo = ($cuenta >= 3 && $cuenta <= 5) ? $monto : 0;
            $totalpasivo = $totalpasivo + $pasivo;
        } */
        //++++

        // if ($f != array_key_last($cuentas)) {
        if ($monto != 0) {
            if ($nivel < $nivelant) {
                $i++;
            }
            $nivelant = $nivel;
            $activa->setCellValueExplicit('A' . $i, $cuenta, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $activa->setCellValue('B' . $i, $nombre);
            $activa->setCellValue('C' . $i, $nivel6);
            $activa->setCellValue('D' . $i, $nivel5);
            $activa->setCellValue('E' . $i, $nivel4);
            $activa->setCellValue('F' . $i, $nivel3);
            $activa->setCellValue('G' . $i, $nivel2);
            $activa->setCellValue('H' . $i, $nivel1);
            //***************SUMATORIAS*********************
            $result = array_search($cuenta, array_column($cuentasactivo, 'clase'));
            if ($result !== false) {
                $totalactivo = $totalactivo + $monto;
            }
            $result = array_search($cuenta, array_column($reguladorasactivo, 'clase'));
            if ($result !== false) {
                $totalactivo = $totalactivo + $monto;
            }
            $result = array_search($cuenta, array_column($cuentaspasivo, 'clase'));
            if ($result !== false) {
                $totalpasivo = $totalpasivo + $monto;
            }
            $result = array_search($cuenta, array_column($cuentascapital, 'clase'));
            if ($result !== false) {
                $totalcapital = $totalcapital + $monto;
            }
            $i++;
        }
        // }
        $f++;
    }
    //-------

    ob_start();
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel, 'Xlsx');
    $writer->save("php://output");
    $xlsData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "Balance General",
        'tipo' => "vnd.ms-excel",
        'data' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
    );
    echo json_encode($opResult);
    exit;
}
