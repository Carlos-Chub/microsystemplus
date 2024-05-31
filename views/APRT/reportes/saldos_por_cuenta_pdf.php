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

// $rutalogomicro = "../../../includes/img/logomicro.png";
// $rutalogoins = "../../../includes/img/fape.jpeg";

//se crea el array que recibe los datos
$datos = array();
$datos = $_POST["data"];

//se obtienen las variables del get
$estado = $datos[6];
$tipo = $datos[7];
$fecha_final = $datos[5];
$usuario = $datos[8];
$oficina = $datos[9];

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
    public $tipo;

    public function __construct($conexion, $institucion, $pathlogo, $pathlogoins, $oficina, $direccion, $email, $telefono, $nit, $user, $estado, $tipo)
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
        $this->tipo = $tipo;
        $this->estado = $estado;
    }

    // Cabecera de página
    function Header()
    {
        //consultar los estados
        $texto_cuentas = "";
        //variables para el texto
        $fuente = "Courier";
        $tamanio_linea = 4; //altura de la linea/celda
        $ancho_linea = 30; //anchura de la linea/celda

        //consultar todas las cuentas
        $data_cuentas = mysqli_query($this->conexion, "SELECT `nombre` FROM `aprtip` WHERE `id_tipo`=$this->tipo");
        while ($rowcuentas = mysqli_fetch_array($data_cuentas, MYSQLI_ASSOC)) {
            $texto_cuentas = strtoupper(utf8_decode($rowcuentas["nombre"]));
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
        $this->Cell(0, 5, 'REPORTE DE SALDO DE CUENTAS DE ' . $texto_cuentas, 0, 1, 'C', true);
        //ver que estados de la cuenta se mostraran
        $texto_estado = "ACTIVOS E INACTIVOS";
        if ($this->estado == 'A') {
            $texto_estado = "ACTIVOS";
        } else if ($this->estado == 'B') {
            $texto_estado = "INACTIVOS";
        }
        $this->Cell(0, 5, $texto_estado, 0, 1, 'C', true);
        $this->Ln(5);
        //Fuente
        $this->SetFont($fuente, '', 10);
        //encabezado de tabla
        $this->Cell($ancho_linea + 3, $tamanio_linea + 1, 'CUENTA', 0, 0, 'C', true); // cuenta
        $this->Cell(($ancho_linea + 30), $tamanio_linea + 1, 'NOMBRE COMPLETO', 0, 0, 'C', true); //nombre
        $this->Cell($ancho_linea - 2, $tamanio_linea + 1, 'FECHA', 0, 0, 'C', true); //fecha
        $this->Cell($ancho_linea - 5, $tamanio_linea + 1, 'SALDO', 0, 0, 'R', true); //saldo
        $this->Cell($ancho_linea + 7, $tamanio_linea + 1, 'TASA', 0, 0, 'R', true); //tasa
        $this->Ln(6);
        $this->Cell(0, 0, '', 'B', 0, 'R', true); //tasa
        $this->Ln(1);
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

//realizar la consulta para encontrar el ccodtip mediante el id_tipo
$data_tip = mysqli_query($conexion, "SELECT `ccodtip` FROM `aprtip` WHERE `id_tipo`=$tipo");
while ($rowcuentas = mysqli_fetch_array($data_tip, MYSQLI_ASSOC)) {
    $ccodtip = $rowcuentas["ccodtip"];
}

// Creación del objeto de la clase heredada
$pdf = new PDF($conexion, $institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins,  $usuario, $estado, $tipo);
$pdf->AliasNbPages();
$pdf->AddPage();

//aca colocar todos los registros
$consulta = "SELECT cta.ccodaport, cl.short_name, calcular_saldo_apr_tipcuenta(cta.ccodaport,'$fecha_final') AS saldo, cta.tasa, cl.genero
FROM aprtip AS tp
INNER JOIN aprcta AS cta ON tp.ccodtip=cta.ccodtip
INNER JOIN tb_cliente AS cl ON cta.ccodcli=cl.idcod_cliente
WHERE tp.ccodtip='$ccodtip'";

if ($estado != "0") {
    $consulta .= " AND cta.estado='$estado'";
}
$consulta .= " ORDER BY cta.ccodaport ASC;";

//se cargan cada una de las filas
$data2 = mysqli_query($conexion, $consulta);
// si no hay datos 
if (mysqli_num_rows($data2) == 0) {
    $opResult = array(
        'status' => 0,
        'mensaje' => 'No se encontraron datos para el informe.'
    );
    echo json_encode($opResult);
}else {

cargar_datos($pdf, $data2, $ancho_linea, $ancho_linea2, $tamanio_linea, $fuente, $fecha_final);

//forma de migrar el archivo
ob_start();
$pdf->Output();
$pdfData = ob_get_contents();
ob_end_clean();

$opResult = array(
    'status' => 1,
    'data' => "data:application/vnd.ms-word;base64," . base64_encode($pdfData)
);
}
mysqli_close($conexion);
echo json_encode($opResult);

//Procedimiento para cargar todos los registros en el archivo pdf
function cargar_datos($pdf, $data, $ancho_linea, $ancho_linea2, $tamanio_linea, $fuente, $fecha_final)
{
    $total_saldo = 0;
    $conta_mujeres = 0;
    $conta_hombres = 0;
    $total_mujeres = 0;
    $total_hombres = 0;
    while ($rowdata = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
        $bd_ccodaport = $rowdata["ccodaport"];
        $bd_shortname = strtoupper(utf8_decode($rowdata["short_name"]));
        $bd_fecha = $fecha_final;
        $bd_saldo = strtoupper(utf8_encode($rowdata["saldo"]));
        $bd_tasa = strtoupper($rowdata["tasa"]);
        $bd_genero = strtoupper(utf8_decode($rowdata["genero"]));
        $saldo_formateado = number_format($bd_saldo, 2, '.', '');
        $tasa_formateado = number_format($bd_tasa, 2, '.', '');

        $total_saldo = $total_saldo + $bd_saldo;
        if ($bd_genero == "M") {
            $total_hombres = $total_hombres + $bd_saldo;
            $conta_hombres++;
        } else {
            $total_mujeres = $total_mujeres + $bd_saldo;
            $conta_mujeres++;
        }

        //se insertan los registros
        $pdf->CellFit($ancho_linea2 + 14, $tamanio_linea + 1, $bd_ccodaport, 0, 0, 'C', 0, '', 1, 0); // cuenta
        $pdf->CellFit(($ancho_linea + 31), $tamanio_linea + 1, $bd_shortname, 0, 0, 'L', 0, '', 1, 0); //nombre
        $pdf->CellFit($ancho_linea - 5, $tamanio_linea + 1,  date("d-m-Y", strtotime($bd_fecha)), 0, 0, 'C', 0, '', 1, 0); //fecha
        $pdf->CellFit($ancho_linea2 + 20, $tamanio_linea + 1, $saldo_formateado, 0, 0, 'R', 0, '', 1, 0); //documento
        $pdf->CellFit($ancho_linea2 + 10, $tamanio_linea + 1, $tasa_formateado, 0, 0, 'R', 0, '', 1, 0); //documento
        $pdf->Ln(5);
    }
    $pdf->Cell(0, 0, ' ', 'B', 0, 'R', true);
    $pdf->Ln(1);
    $pdf->SetFont($fuente, 'B', 9);
    $pdf->CellFit(($ancho_linea + 92), $tamanio_linea + 1, ' ', 0, 0, 'R', 0, '', 1, 0); // cuenta
    $pdf->CellFit(($ancho_linea + 8), $tamanio_linea + 1, number_format($total_saldo, 2, '.', ''), 0, 0, 'R', 0, '', 1, 0); // cuenta
    $pdf->Ln(8);
    //resumen de saldo
    $pdf->SetFont($fuente, '', 11);
    $pdf->Ln(4);
    $pdf->CellFit($ancho_linea + 40, $tamanio_linea, " ", 0, 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea, $tamanio_linea, "NUMERO", 0, 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 5, $tamanio_linea, "SALDO", 0, 0, 'R', 0, '', 1, 0);
    $pdf->Ln(4);
    $pdf->CellFit($ancho_linea + 40, $tamanio_linea, "MUJERES:", 0, 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea, $tamanio_linea, $conta_mujeres, 0, 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 5, $tamanio_linea, number_format($total_mujeres, 2, '.', ''), 0, 0, 'R', 0, '', 1, 0);
    $pdf->Ln(4);
    $pdf->CellFit($ancho_linea + 40, $tamanio_linea, "HOMBRES:", 0, 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea, $tamanio_linea, $conta_hombres, 0, 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 5, $tamanio_linea, number_format($total_hombres, 2, '.', ''), 0, 0, 'R', 0, '', 1, 0);
}
