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

//se crea el array y se reciben los datos del post
$datos = array();
$datos = $_POST["data"];

//se asignan variables locales a los datos recibidos
$ccodaport = $datos[5];
$r_fecha = $datos[8];
$fechainicial = $datos[6];
$fechafinal = $datos[7];
$usuario = $datos[9];
$oficina = $datos[10];

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
    public $user;
    public $conexion;
    public $ccodaport;
    public $r_fecha;
    public $fechainicial;
    public $fechafinal;

    public function __construct($conexion, $institucion, $pathlogo, $pathlogoins, $oficina, $direccion, $email, $telefono, $nit, $user, $ccodaport, $r_fecha, $fechainicial, $fechafinal)
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
        $this->ccodaport = $ccodaport;
        $this->r_fecha = $r_fecha;
        $this->fechainicial = $fechainicial;
        $this->fechafinal = $fechafinal;
    }

    // Cabecera de página
    function Header()
    {
        //------------------datos de la cuenta en aprcta
        $libreta = 0;
        $codcli = "";
        $nit = "";
        $datalib = mysqli_query($this->conexion, "SELECT `nlibreta`,`ccodcli`,`num_nit` FROM `aprcta` WHERE `ccodaport`=$this->ccodaport");
        while ($rowlib = mysqli_fetch_array($datalib, MYSQLI_ASSOC)) {
            $libreta = $rowlib["nlibreta"];
            $codcli = $rowlib["ccodcli"];
            $nit = $rowlib["num_nit"];
        }

        //---------------------datos del cliente
        $depadom = "00";
        $munidom = "0000";
        $nombre = "";
        $identificacion = "";
        $direccion = "";
        $noNit = "";
        $tel1 = "";
        $genero = "";
        $estado_civil = "";

        $fuente = "Courier";
        $tamanioFuente = 9;
        $tamanioTitulo = 11;
        $tamanio_linea = 4; //altura de la linea/celda
        $ancho_linea = 30; //anchura de la linea/celda
        $espacio_blanco = 10; //tamaño del espacio en blanco entre cada celda
        $ancho_linea2 = 20; //anchura de la linea/celda
        $espacio_blanco2 = 4; //tamaño del espacio en blanco entre cada celda

        $sql = mysqli_query($this->conexion, "SELECT * FROM tb_cliente WHERE idcod_cliente='$codcli'");
        while ($row = mysqli_fetch_array($sql, MYSQLI_ASSOC)) {
            $nombre = $row['compl_name'];
            $rutaFoto = $row['url_img'];
            if ($rutaFoto == 'url' || $rutaFoto == '') {
                $rutaFoto = '../../../includes/img/fotoClienteDefault.png';
            } else {
                $rutaFoto = "../../../../../" . $rutaFoto;
            }
            $fecha = $row['date_birth'];
            $fechaNacimiento = date("d-m-Y", strtotime($fecha)); //formatear fecha en dia/mes/año
            $genero = $row['genero'];
            $estado_civil = $row['estado_civil'];
            $identificacion = $row['no_identifica'];
            $direccion = $row['Direccion'];
            $noNit = $row['no_tributaria'];
            $email = $row['email'];
            $depadom = $row['depa_reside'];
            $munidom = $row['muni_reside'];
            $tel1 = $row['tel_no1'];
            $tel2 = $row['tel_no2'];
        }

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
        $this->Cell(0, 3, 'NIT: ' . $this->nit, 'B', 1, 'C');
        // Salto de línea
        $this->Ln(15);

        //SECCION DE DATOS DEL CLIENTE
        $this->SetY(30);
        //NUMERO DE CUENTA
        $this->SetFont($fuente, 'B', $tamanioTitulo);
        $this->Cell(0, 10, utf8_decode('Cuenta de aportación:  ') . $this->ccodaport, 0, 1);
        //TITULO DE REPORTE
        $this->SetFillColor(204, 229, 255);
        $this->Cell(0, 5, 'HISTORIAL DE CUENTA DE APORTACIONES', 0, 1, 'C', true);
        $texto_fecha = "TODAS LAS FECHAS";
        if ($this->r_fecha == '2') {
            $texto_fecha = "DE " . $this->fechainicial . " HASTA " . $this->fechafinal;
        }
        $this->Cell(0, 5, $texto_fecha, 0, 1, 'C', true);
        $this->Ln(3);
        //Fuente
        $this->SetFont($fuente, '', 9);
        //DATOS DEL CLIENTE
        $this->Cell(40, 6, 'Nombre: ', 0, 0);
        $this->Cell(40, 6, $nombre, 0, 1);
        $this->Cell(40, 6, utf8_decode('Identificación: '), 0, 0);
        $this->Cell(50, 6, $identificacion, 0, 0);
        $this->Cell(30, 6, 'NIT: ', 0, 0);
        $this->Cell(40, 6,  $noNit, 0, 1);
        $this->Cell(40, 6, 'Domicilio: ', 0, 0);
        $this->Cell(40, 6, municipio($munidom) . ', ' . departamento($depadom), 0, 1);
        $this->Cell(40, 6, utf8_decode('Dirección: '), 0, 0);
        $this->Cell(40, 6, $direccion, 0, 1);
        $this->Cell(40, 6, utf8_decode('Teléfono: '), 0, 0);
        $this->Cell(40, 6,  $tel1, 0, 1);
        $this->Cell(40, 6, 'Sexo: ', 0, 0);
        $this->Cell(50, 6, $genero, 0, 0);
        $this->Cell(30, 6, 'Estado civil: ', 0, 0);
        $this->Cell(40, 6, $estado_civil, 0, 1);
        $this->Ln(3);

        //Color de encabezado de lista
        $this->SetFillColor(555, 255, 204);
        //TITULOS DE ENCABEZADO DE TABLA
        $this->CellFit($ancho_linea2, $tamanio_linea + 1, ' Fecha', 'B', 0, 'C', 0, '', 1, 0);
        $this->CellFit($ancho_linea2 / 2, $tamanio_linea + 1, 'Num', 'B', 0, 'C', 0, '', 1, 0); //
        $this->CellFit($ancho_linea2 / 3, $tamanio_linea + 1, 'D/R', 'B', 0, 'C', 0, '', 1, 0);
        $this->CellFit($ancho_linea2, $tamanio_linea + 1, 'Doc', 'B', 0, 'C', 0, '', 1, 0);
        $this->CellFit($ancho_linea2 / 2, $tamanio_linea + 1, 'Tipo', 'B', 0, 'C', 0, '', 1, 0);
        $this->CellFit($ancho_linea, $tamanio_linea + 1, utf8_decode('Créditos'), 'B', 0, 'C', 0, '', 1, 0); //
        $this->CellFit($ancho_linea, $tamanio_linea + 1, utf8_decode('Debitos'), 'B', 0, 'C', 0, '', 1, 0);
        $this->CellFit($ancho_linea2 - 3, $tamanio_linea + 1, 'Cheque', 'B', 0, 'C', 0, '', 1, 0);
        $this->CellFit($ancho_linea2 - 3, $tamanio_linea + 1, 'Partida', 'B', 0, 'C', 0, '', 1, 0);
        $this->CellFit($ancho_linea, $tamanio_linea + 1, 'Saldo', 'B', 0, 'C', 0, '', 1, 0);
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

// Creación del objeto de la clase heredada
$pdf = new PDF($conexion, $institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins, $usuario, $ccodaport, $r_fecha, $fechainicial, $fechafinal);

$pdf->AliasNbPages();
$pdf->AddPage();

//CONSULTA A LA BASE DE DATOS
$consulta = "SELECT * FROM aprmov WHERE ccodaport = " . $ccodaport;
$consulta2 = "CALL obtener_saldo_ant_fecha('$ccodaport', '$fechainicial')";
$data3 = "";
$data2 = "";

if ($r_fecha == '2') {
    //se hace la consulta del saldo anterior
    $data3 = mysqli_query($conexion, $consulta2);
    mysqli_next_result($conexion);

    //se hace la consulta entre fechas
    $consulta .= " AND (aprmov.dfecope BETWEEN '$fechainicial' AND '$fechafinal')";
}
$consulta .= " ORDER BY aprmov.correlativo ASC";

$data2 = mysqli_query($conexion, $consulta);
cargar_datos($pdf, $data2, $tamanio_linea, $data3, $r_fecha);

//forma de migrar el archivo
ob_start();
$pdf->Output();
$pdfData = ob_get_contents();
ob_end_clean();

$opResult = array(
    'status' => 1,
    'data' => "data:application/vnd.ms-word;base64," . base64_encode($pdfData)
);
echo json_encode($opResult);


//procedimiento para cargar los datos en el archivo de pdf
function cargar_datos($pdf, $data2, $tamanio_linea, $data3, $r_fecha)
{
    $ancho_linea = 30;
    $ancho_linea2 = 20;
    $saldo = 0;

    if ($r_fecha == '2') {
        while ($ant = mysqli_fetch_array($data3, MYSQLI_ASSOC)) {
            $fecha_ant = date("d-m-Y", strtotime($ant['fecha_anterior']));
            $total_ant = $ant['total'];

            //colocar datos en reporte
            $pdf->CellFit($ancho_linea2, $tamanio_linea, $fecha_ant, 0, 0, 'C', 0, '', 1, 0); //fecha
            $pdf->CellFit($ancho_linea2 / 2, $tamanio_linea, ' ', 0, 0, 'C', 0, '', 1, 0); //num
            $pdf->CellFit(($ancho_linea2 / 3), $tamanio_linea, ' ', 0, 0, 'C', 0, '', 1, 0); //D/R
            $pdf->CellFit($ancho_linea2, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0); //Doc
            $pdf->CellFit($ancho_linea2 / 2, $tamanio_linea, ' ', 0, 0, 'C', 0, '', 1, 0); //Tipo
            $pdf->CellFit($ancho_linea, $tamanio_linea, ' ', 0, 0, 'R', 0, '', 1, 0); // 
            $pdf->CellFit($ancho_linea, $tamanio_linea, ' ', 0, 0, 'R', 0, '', 1, 0); //
            $pdf->CellFit($ancho_linea2 - 3, $tamanio_linea, ' ', 0, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea2 - 3, $tamanio_linea, ' ', 0, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea, $tamanio_linea, number_format($total_ant, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
            $saldo = $saldo + $total_ant;
            $pdf->Ln(4);
        }
    }
    // $pdf->Cell($ancho_linea2 / 2, $tamanio_linea, $data3, 0, 0, 'C'); //num

    while ($mov = mysqli_fetch_array($data2, MYSQLI_ASSOC)) {
        $fech = $mov['dfecope'];
        $fecha = date("d-m-Y", strtotime($fech));
        $num = $mov['correlativo'];
        $tipope = $mov['ctipope'];
        $numdoc = $mov['cnumdoc'];
        $tipdoc = utf8_encode($mov['ctipdoc']);
        $ncheque = $mov['nrochq'];
        $tipchq = $mov['tipchq'];
        $partida = $mov['numpartida'];
        $monto = $mov['monto'];

        //IMPRESION DE LISTA DE TRANSACCIONES DE CUENTA
        $pdf->CellFit($ancho_linea2, $tamanio_linea, $fecha, 0, 0, 'C', 0, '', 1, 0); //fecha
        $pdf->CellFit($ancho_linea2 / 2, $tamanio_linea, $num, 0, 0, 'C', 0, '', 1, 0); //num
        $pdf->CellFit(($ancho_linea2 / 3), $tamanio_linea, $tipope, 0, 0, 'C', 0, '', 1, 0); //D/R
        $pdf->CellFit($ancho_linea2, $tamanio_linea, $numdoc, 0, 0, 'L', 0, '', 1, 0); //Doc
        $pdf->CellFit($ancho_linea2 / 2, $tamanio_linea, $tipdoc, 0, 0, 'C', 0, '', 1, 0); //Tipo

        $pdf->CellFit($ancho_linea, $tamanio_linea, ($tipope == "D") ? 'Q ' . number_format($monto, 2, '.', ',') : ' ', 0, 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea, $tamanio_linea, ($tipope == "R") ? 'Q ' . number_format($monto, 2, '.', ',') : ' ', 0, 0, 'R', 0, '', 1, 0); // 
        
        ($tipope == "D") ? $saldo = $saldo + $monto : $saldo;
        ($tipope == "R") ? $saldo = $saldo - $monto : $saldo;

        $pdf->CellFit($ancho_linea2 - 3, $tamanio_linea, ($ncheque) ? $ncheque : ' ', 0, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2 - 3, $tamanio_linea, ($partida) ? $partida : ' ', 0, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea, $tamanio_linea, 'Q ' . number_format($saldo, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
        $pdf->Ln(4);
    }
    //fin ingresos
}
