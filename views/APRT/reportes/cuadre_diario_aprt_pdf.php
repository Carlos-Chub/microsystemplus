<?php
session_start();
include '../../../src/funcphp/func_gen.php';
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
require '../../../fpdf/fpdf.php';
date_default_timezone_set('America/Guatemala');

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

// echo json_encode(["reportes_aportaciones", "cuadre_diario_aprt", $tipo_doc, $formato, date("d-m-Y"), $inputs[0], $inputs[1], $radioss[0], $radioss[1], $selects[0], $archivos[0], $archivos[1]]);
//se crea el array que recibe los datos
$datos = array();
$datos = $_POST["data"];

//se obtienen las variables del post
$cuenta = $datos[7];
$tipo = $datos[9];
$r_fecha = $datos[8];
$fechainicial = $datos[5];
$fechafinal = $datos[6];
$usuario = $datos[10];
$oficina = $datos[11];

$fuente = "Courier";
$tamanioFuente = 9;
$tamanioTitulo = 11;
$tamanio_linea = 4; //altura de la linea/celda
$ancho_linea = 30; //anchura de la linea/celda
$espacio_blanco = 10; //tamaño del espacio en blanco entre cada celda
$ancho_linea2 = 20; //anchura de la linea/celda
$espacio_blanco2 = 4; //tamaño del espacio en blanco entre cada celda

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

    public function __construct($institucion, $pathlogo, $pathlogoins, $oficina, $direccion, $email, $telefono, $nit, $rango, $tipocuenta, $saldoant)
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
        // $this->Cell(0, $tamanio_linea + 1, 'Saldo Anterior ' . number_format(($this->saldoant), 2, '.', ','), 'B', 1, 'R');
        $this->Cell(0, $tamanio_linea + 1, 'Saldo Anterior ' . $this->saldoant, 'B', 1, 'R');
        $this->Cell($ancho_linea, $tamanio_linea + 1, 'Fecha', 'B', 0, 'C', true);
        $this->Cell($ancho_linea2 * 3 - 4, $tamanio_linea + 1, 'Total Depositos', 'B', 0, 'C', true); //
        $this->Cell($ancho_linea2 * 3 - 4, $tamanio_linea + 1, 'Total Retiros', 'B', 0, 'C', true);
        $this->Cell($ancho_linea2 + 28, $tamanio_linea + 1, 'Saldo', 'B', 1, 'C', true);
        $this->SetFont($fuente, '', $tamanioTitulo);
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

//ARMANDO LA CONSULTA
$condicion = "";
$conditipo = "";
$bandera = 0;
if ($cuenta == "2") {
    //consultar el ccodtip mediante el id tipo
    $data_aux = mysqli_query($conexion, "SELECT ccodtip FROM aprtip WHERE id_tipo='$tipo'");
    while ($reg = mysqli_fetch_array($data_aux, MYSQLI_ASSOC)) {
        $tipo_aux = $reg["ccodtip"];
    }
    //-------
    $conditipo = " SUBSTR(`ccodaport`,7,2) =" . $tipo_aux;
    $tipocuenta = utf8_decode(tipocuenta($tipo_aux, "aprtip", "nombre", $conexion));
    $bandera++;
} else {
    $tipocuenta = "Todos los tipos de cuenta"; //solo para reporte detalle
}
$condicion = $conditipo;
//-------------condicion de rango de fechas
$and = "";
if ($r_fecha == "2") {
    if ($bandera > 0) {
        $and = " AND ";
        $condicion = $condicion . " AND ";
    }
    $rango = "" . date("d-m-Y", strtotime($fechainicial)) . " A " . date("d-m-Y", strtotime($fechafinal)); //solo para reporte detalle
    $condicion = $condicion . " (dfecope BETWEEN '" . $fechainicial . "' AND '" . $fechafinal . "')";
    $bandera++;
} else {
    $rango = "Todas las fechas"; //solo para reporte detalle
}
$where = ($bandera > 0) ? " WHERE " : "";

//seccion
$consulta = "SELECT dfecope,sum(monto) as total,ctipope FROM aprmov" . $where . $condicion . " GROUP BY dfecope,ctipope ORDER BY dfecope";

$data = mysqli_query($conexion, $consulta);
$array[] = [];
$fila = 0;
while ($registro = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
    $array[$fila] = $registro;
    $fila++;
}

//-----------------SALDO ANTERIOR SUMATORIA
$saldoant = 0;
if ($r_fecha == "2") {
    $condirango =  "WHERE dfecope<'" . $fechainicial . "'";
    $ctipo = ($cuenta == "2") ? " AND " . $conditipo : "";
    $queryant = "SELECT sum(montooo) as saldoant FROM data_aprmov " . $condirango . $ctipo . "";
    $sqlsal = mysqli_query($conexion, $queryant);
    while ($registro = mysqli_fetch_array($sqlsal, MYSQLI_ASSOC)) {
        $saldoant = $registro["saldoant"];
    }
}



// Creación del objeto de la clase heredada
$pdf = new PDF($institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins, $rango, $tipocuenta, $saldoant);

$pdf->AliasNbPages();
$pdf->AddPage();

cargar_datos($pdf, $array, $ancho_linea2, $ancho_linea, $tamanio_linea, $fuente, $saldoant);

ob_start();
$pdf->Output();
$pdfData = ob_get_contents();
ob_end_clean();

$opResult = array(
    'status' => 1,
    'data' => "data:application/vnd.ms-word;base64," . base64_encode($pdfData)
);
mysqli_close($conexion);
echo json_encode($opResult);

function cargar_datos($pdf, $registro, $ancho_linea2, $ancho_linea, $tamanio_linea, $fuente, $saldoant)
{
    $fila = 0;
    $totaldep = 0;
    $totalret = 0;
    $saldo = $saldoant;
    while ($fila < count($registro) && count($registro) != 1) {
        $fec = $registro[$fila]["dfecope"];
        $total = $registro[$fila]["total"];
        $tipope = $registro[$fila]["ctipope"];
        $fecha = date("d-m-Y", strtotime($fec));
        $depositos = ($tipope == "D") ? $total : 0;
        $retiros = ($tipope == "R") ? $total : 0;
        $saldo = $saldo + $depositos - $retiros;
        $totaldep = $totaldep + $depositos;
        $totalret = $totalret + $retiros;

        $pdf->CellFit($ancho_linea, $tamanio_linea + 1, $fecha, 0, 0, 'C', 0, '', 1, 0); // cuenta
        $pdf->CellFit($ancho_linea2 * 3 - 4, $tamanio_linea + 1, number_format($depositos, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0); // cuenta
        $pdf->CellFit($ancho_linea2 * 3 - 4, $tamanio_linea + 1, number_format($retiros, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0); // cuenta
        $pdf->CellFit($ancho_linea2 + 28, $tamanio_linea + 1, number_format($saldo, 2, '.', ','), 0, 1, 'R', 0, '', 1, 0); // cuenta
        $fila++;
    }

    $pdf->SetFont($fuente, 'B', 10);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, "TOTALES: ", 'T', 0, 'R', 0, '', 1, 0); // cuenta
    $pdf->CellFit($ancho_linea2 * 3 + 6, $tamanio_linea + 1, number_format($totaldep, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0); // cuenta
    $pdf->CellFit($ancho_linea2 * 3 - 4, $tamanio_linea + 1, number_format($totalret, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0); // cuenta
    $pdf->CellFit($ancho_linea2 * 2 + 8, $tamanio_linea + 1, ' ', 'T', 1, 'R', 0, '', 1, 0); // cuenta
}
