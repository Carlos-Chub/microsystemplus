<?php
session_start();
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../../src/funcphp/func_gen.php';
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

$fuente = "Courier";
$tamanioFuente = 9;
$tamanioTitulo = 11;
$tamanio_linea = 4; //altura de la linea/celda
$ancho_linea = 30; //anchura de la linea/celda
$espacio_blanco = 10; //tamaño del espacio en blanco entre cada celda
$ancho_linea2 = 20; //anchura de la linea/celda
$espacio_blanco2 = 4; //tamaño del espacio en blanco entre cada celda

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
    public $user;
    public $conexion;
    public $estado;
    public $cuenta;
    public $tipo;
    public $transaccion;
    public $r_fecha;
    public $fechainicial;
    public $fechafinal;

    public function __construct($conexion, $institucion, $pathlogo, $pathlogoins, $oficina, $direccion, $email, $telefono, $nit, $user, $transaccion, $cuenta, $tipo, $r_fecha, $fechainicial, $fechafinal)
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
        $this->user = $user;
        $this->conexion = $conexion;
        $this->transaccion = $transaccion;
        $this->cuenta = $cuenta;
        $this->tipo = $tipo;
        $this->r_fecha = $r_fecha;
        $this->fechainicial = $fechainicial;
        $this->fechafinal = $fechafinal;
    }

    // Cabecera de página
    function Header()
    {
        //consultar los estados
        $texto_cuentas = "";
        //consultar la fecha
        $texto_fecha = "";
        //variables para el texto
        $fuente = "Courier";
        $tamanio_linea = 4; //altura de la linea/celda
        $ancho_linea = 30; //anchura de la linea/celda
        $ancho_linea2 = 20; //anchura de la linea/celda

        //consultar todas las cuentas
        if ($this->tipo == '0') {
            $texto_cuentas = 'TODAS LAS CUENTAS';
        } else {
            $data_cuentas = mysqli_query($this->conexion, "SELECT `nombre` FROM `aprtip` WHERE `id_tipo`=$this->tipo");
            while ($rowcuentas = mysqli_fetch_array($data_cuentas, MYSQLI_ASSOC)) {
                $texto_cuentas = strtoupper(utf8_decode($rowcuentas["nombre"]));
            }
        }

        // ACA ES DONDE EMPIEZA LO DEL FORMATO DE REPORTE---------------------------------------------------
        $hoy = date("Y-m-d H:i:s");
        //fecha y usuario que genero el reporte
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 2, $hoy, 0, 1, 'R');
        $this->Ln(1);
        $this->Cell(0, 2, $this->user, 0, 1, 'R');

        // Logo de la agencia
        $this->Image($this->pathlogoins, 10, 13, 33);

        //tipo de letra para el encabezado
        $this->SetFont('Arial', '', 8);
        // Título
        $this->Cell(0, 3, $this->institucion, 0, 1, 'C');
        $this->Cell(0, 3, $this->direccion, 0, 1, 'C');
        $this->Cell(0, 3, 'Email: ' . $this->email, 0, 1, 'C');
        $this->Cell(0, 3, 'Tel: ' . $this->telefono, 0, 1, 'C');
        $this->Cell(0, 3, 'NIT: ' . $this->nit, 0, 1, 'C');
        // Salto de línea
        $this->Ln(3);

        $this->SetFont($fuente, '', 10);
        //SECCION DE DATOS DEL CLIENTE
        //TITULO DE REPORTE
        $this->SetFillColor(255, 255, 255);
        $this->Cell(0, 5, 'LISTADO DE MOVIMIENTOS', 0, 1, 'C', true);
        $this->Cell(0, 5, $texto_cuentas, 0, 1, 'C', true);
        $texto_fecha = "TODAS LAS FECHAS";
        if ($this->r_fecha == '2') {
            $texto_fecha = "DE " . $this->fechainicial . " HASTA " . $this->fechafinal;
        }
        $this->Cell(0, 5, $texto_fecha, 0, 1, 'C', true);

        $this->Ln(5);
        //Fuente
        $this->SetFont($fuente, '', 10);
        //encabezado de tabla
        $this->Cell($ancho_linea + 3, $tamanio_linea + 1, 'CUENTA', 'B', 0, 'C', true); // cuenta
        $this->Cell(($ancho_linea + 22), $tamanio_linea + 1, 'NOMBRE COMPLETO', 'B', 0, 'C', true); //nombre
        $this->Cell($ancho_linea - 6, $tamanio_linea + 1, 'FECHA', 'B', 0, 'C', true); //fecha
        $this->CellFit($ancho_linea - 2, $tamanio_linea + 1, 'DOCUMENTO', 'B', 0, 'C', 0, '', 1, 0); //Estado
        // $this->Cell($ancho_linea2, $tamanio_linea + 1, 'ACTIVACION', 'B', 0, 'C', true); //activacion
        $this->CellFit($ancho_linea2 - 8, $tamanio_linea + 1, ' TIP-DOC', 'B', 0, 'L', 0, '', 1, 0); //apertura
        $this->Cell($ancho_linea2 + 1, $tamanio_linea + 1, 'DEPOSITO', 'B', 0, 'R', true); //apertura
        $this->Cell($ancho_linea2, $tamanio_linea + 1, 'RETIRO', 'B', 0, 'R', true); //cancelacion
        $this->Ln(8);
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
$pdf = new PDF($conexion, $institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins,  $usuario, $transaccion, $cuenta, $tipo, $r_fecha, $fechainicial, $fechafinal);
$pdf->AliasNbPages();
$pdf->AddPage();

//aca colocar todos los registros
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

//se cargan cada una de las filas
$data2 = mysqli_query($conexion, $consulta);
cargar_datos($pdf, $data2, $ancho_linea, $ancho_linea2, $tamanio_linea, $fuente);

//forma de migrar el archivo
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

//Procedimiento para cargar todos los datos al archivo de pdf
function cargar_datos($pdf, $data, $ancho_linea, $ancho_linea2, $tamanio_linea, $fuente)
{
    $total_dep = 0;
    $total_ret = 0;
    while ($rowdata = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
        $bd_ccodaport = $rowdata["ccodaport"];
        $bd_shortname = strtoupper($rowdata["short_name"]);
        $bd_fecha = strtoupper($rowdata["dfecope"]);
        $bd_tipo = strtoupper($rowdata["cnumdoc"]);
        $bd_doc = strtoupper($rowdata["ctipdoc"]);
        $bd_mov = strtoupper($rowdata["ctipope"]);
        $bd_monto = ($rowdata["monto"]);

        //se insertan los registros
        $pdf->CellFit($ancho_linea2 + 13, $tamanio_linea + 1, $bd_ccodaport, 'B', 0, 'C', 0, '', 1, 0); // cuenta
        $pdf->CellFit(($ancho_linea + 22), $tamanio_linea + 1, utf8_decode($bd_shortname), 'B', 0, 'L', 0, '', 1, 0); //nombre
        $pdf->CellFit($ancho_linea - 6, $tamanio_linea + 1,  date("d-m-Y", strtotime($bd_fecha)), 'B', 0, 'C', 0, '', 1, 0); //fecha
        $pdf->CellFit($ancho_linea2 + 7, $tamanio_linea + 1, utf8_decode($bd_tipo), 'B', 0, 'C', 0, '', 1, 0); //documento
        $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 1, utf8_decode($bd_doc), 'B', 0, 'C', 0, '', 1, 0); //documento
        // $this->Cell($ancho_linea2, $tamanio_linea + 1, 'ACTIVACION', 'B', 0, 'C', true); //activacion
        $monto_formateado = number_format($bd_monto, 2, '.', '');
        if ($bd_mov == 'D') {
            $total_dep = $total_dep + $bd_monto;
            $pdf->CellFit($ancho_linea2 - 1, $tamanio_linea + 1, $monto_formateado, 'B', 0, 'R', 0, '', 1, 0); //monto
        } else {
            $pdf->CellFit($ancho_linea2 - 1, $tamanio_linea + 1, '-', 'B', 0, 'R', 0, '', 1, 0); //monto
        }
        if ($bd_mov == 'R') {
            $total_ret = $total_ret + $bd_monto;
            $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $monto_formateado, 'B', 0, 'R', 0, '', 1, 0); //monto
        } else {
            $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, '-', 'B', 0, 'R', 0, '', 1, 0); //monto
        }
        $pdf->Ln(6);
    }
    $pdf->Ln(2);
    $pdf->SetFont($fuente, 'I', 10);
    $pdf->CellFit(($ancho_linea * 4) + 5, $tamanio_linea + 1, ' ', 0, 0, 'R', 0, '', 1, 0); // cuenta
    $pdf->CellFit($ancho_linea - 6, $tamanio_linea + 1, 'TOTALES:', 0, 0, 'C', 0, '', 1, 0); // cuenta
    $pdf->SetFont($fuente, 'B', 10);
    $pdf->CellFit($ancho_linea - 9, $tamanio_linea + 1, number_format($total_dep, 2, '.', ''), 0, 0, 'R', 0, '', 1, 0); // cuenta
    $pdf->CellFit($ancho_linea - 9, $tamanio_linea + 1, number_format($total_ret, 2, '.', ''), 0, 0, 'R', 0, '', 1, 0); // cuenta
}
