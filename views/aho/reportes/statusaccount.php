<?php
session_start();
include '../../../includes/BD_con/db_con.php';
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

$ccodaho = $_GET["id"];
//------------------datos de la cuenta en ahomcta
$libreta = 0;
$codcli = "";
$nit = "";
$datalib = mysqli_query($conexion, "SELECT `nlibreta`,`ccodcli`,`num_nit` FROM `ahomcta` WHERE `ccodaho`=$ccodaho");
while ($rowlib = mysqli_fetch_array($datalib, MYSQLI_ASSOC)) {
    $libreta = utf8_encode($rowlib["nlibreta"]);
    $codcli = utf8_encode($rowlib["ccodcli"]);
    $nit = utf8_encode($rowlib["num_nit"]);
}

//---------------------datos del cliente
$depadom = "00";
$munidom = "0000";
//$sql = mysqli_query($conexion, "SELECT * FROM tb_cliente WHERE idcod_cliente = " . $codcli." OR no_tributaria=".$nit);
$sql = mysqli_query($conexion, "SELECT * FROM tb_cliente WHERE idcod_cliente='$codcli'");
//echo $codcli;
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


//---------------------- movimientos de la cuenta
$movimientos = mysqli_query($conexion, "SELECT * FROM ahommov WHERE cestado!=2 AND ccodaho = " . $ccodaho);

class PDF extends FPDF
{
    public $institucion, $pathlogo, $pathlogoins, $oficina, $direccion, $email, $telefonos, $nit, $user;
    public function __construct($institucion, $pathlogo, $pathlogoins, $oficina, $dire, $email, $tel, $nit, $user)
    {
        parent::__construct();
        $this->institucion = $institucion;
        $this->pathlogo = $pathlogo;
        $this->pathlogoins = $pathlogoins;
        $this->oficina = $oficina;
        $this->direccion = $dire;
        $this->email = $email;
        $this->telefonos = $tel;
        $this->nit = $nit;
        $this->user = $user;
    }

    // Cabecera de página
    function Header()
    {
        $hoy = date("Y-m-d H:i:s");
        // Logo 
        $this->Image($this->pathlogoins, 10, 8, 33);


        $this->SetFont('Arial', '', 8);
        // Movernos a la derecha
        //$this->Cell(80);

        // Título
        $this->Cell(0, 3, $this->institucion, 0, 1, 'C');
        $this->Cell(0, 3, $this->direccion, 0, 1, 'C');
        $this->Cell(0, 3, 'Email: ' . $this->email, 0, 1, 'C');
        $this->Cell(0, 3, 'Tel: ' . $this->telefonos, 0, 1, 'C');
        $this->Cell(0, 3, 'NIT: ' . $this->nit, 'B', 1, 'C');

        $this->SetFont('Arial', '', 7);
        $this->SetXY(-30, 5);
        $this->Cell(10, 2, $hoy, 0, 1, 'L');
        $this->SetXY(-25, 7);
        $this->Cell(10, 2, $this->user, 0, 1, 'L');

        // Salto de línea
        $this->Ln(15);
    }

    // Pie de página
    function Footer()
    {

        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Logo 
        $this->Image($this->pathlogo, 165, 275, 20);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}
$fuente = "Courier";

$tamanioFuente = 9;
$tamanioTitulo = 11;
$tamanio_linea = 4; //altura de la linea/celda
$ancho_linea = 30; //anchura de la linea/celda
$espacio_blanco = 10; //tamaño del espacio en blanco entre cada celda
$ancho_linea2 = 20; //anchura de la linea/celda
$espacio_blanco2 = 4; //tamaño del espacio en blanco entre cada celda
// Creación del objeto de la clase heredada
$pdf = new PDF($institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins, $usuario);
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetY(30);

$pdf->SetFont($fuente, 'B', $tamanioTitulo);
$pdf->Cell(0, 10, 'Cuenta de ahorros:  ' . $ccodaho, 0, 1);

$pdf->SetFillColor(204, 229, 255);
$pdf->Cell(0, 5, 'HISTORIAL DE AHORRO', 0, 1, 'C', true);
$pdf->Ln(3);
$pdf->SetFont($fuente, '', 9);

$pdf->Cell(40, 6, 'Nombre: ', 0, 0);
$pdf->Cell(40, 6, $nombre, 0, 1);

$pdf->Cell(40, 6, 'Identificacion: ', 0, 0);
$pdf->Cell(50, 6, $identificacion, 0, 0);

$pdf->Cell(30, 6, 'NIT: ', 0, 0);
$pdf->Cell(40, 6,  $noNit, 0, 1);

$pdf->Cell(40, 6, 'Domicilio: ', 0, 0);
$pdf->Cell(40, 6, municipio($munidom) . ', ' . departamento($depadom), 0, 1);

$pdf->Cell(40, 6, 'Direccion: ', 0, 0);
$pdf->Cell(40, 6, $direccion, 0, 1);

$pdf->Cell(40, 6, 'Telefono: ', 0, 0);
$pdf->Cell(40, 6,  $tel1, 0, 1);

$pdf->Cell(40, 6, 'Sexo: ', 0, 0);
$pdf->Cell(50, 6, $genero, 0, 0);

$pdf->Cell(30, 6, 'Estado civil: ', 0, 0);
$pdf->Cell(40, 6, $estado_civil, 0, 1);

$pdf->Ln(3);

$pdf->SetFillColor(555, 255, 204);
$pdf->Cell($ancho_linea2, $tamanio_linea, ' Fecha', 'B', 0, 'C', true);
$pdf->Cell($ancho_linea2 / 2, $tamanio_linea, 'Num', 'B', 0, 'C', true); //
$pdf->Cell($ancho_linea2 / 3, $tamanio_linea, 'D/R', 'B', 0, 'C', true);
$pdf->Cell($ancho_linea2, $tamanio_linea, 'Doc', 'B', 0, 'C', true);
$pdf->Cell($ancho_linea2 / 2, $tamanio_linea, 'Tipo', 'B', 0, 'C', true);
$pdf->Cell($ancho_linea, $tamanio_linea, 'Creditos', 'B', 0, 'C', true); //
$pdf->Cell($ancho_linea, $tamanio_linea, 'Debitos', 'B', 0, 'C', true);
$pdf->Cell($ancho_linea2, $tamanio_linea, 'Cheque', 'B', 0, 'C', true);
$pdf->Cell($ancho_linea2, $tamanio_linea, 'Partida', 'B', 0, 'C', true);
$pdf->Cell($ancho_linea, $tamanio_linea, 'Saldo', 'B', 1, 'C', true);

$saldo = 0;
while ($mov = mysqli_fetch_array($movimientos, MYSQLI_ASSOC)) {
    $fech = utf8_encode($mov['dfecope']);
    $fecha = date("d-m-Y", strtotime($fech));
    $num = utf8_encode($mov['correlativo']);
    $tipope = utf8_encode($mov['ctipope']);
    $numdoc = utf8_encode($mov['cnumdoc']);
    $tipdoc = utf8_encode($mov['ctipdoc']);
    $ncheque = utf8_encode($mov['nrochq']);
    $tipchq = utf8_encode($mov['tipchq']);
    $partida = utf8_encode($mov['numpartida']);
    $monto = utf8_encode($mov['monto']);

    $pdf->Cell($ancho_linea2, $tamanio_linea, $fecha, 0, 0, 'C');
    $pdf->Cell($ancho_linea2 / 2, $tamanio_linea, $num, 0, 0, 'C'); //
    $pdf->Cell($ancho_linea2 / 3, $tamanio_linea, $tipope, 0, 0, 'C');
    $pdf->Cell($ancho_linea2, $tamanio_linea, $numdoc, 0, 0, 'L');
    $pdf->Cell($ancho_linea2 / 3, $tamanio_linea, $tipdoc, 0, 0, 'C');
    if ($tipope == "D") {
        $pdf->Cell($ancho_linea, $tamanio_linea, 'Q ' . number_format($monto, 2, '.', ','), 0, 0, 'R'); // 
        $saldo = $saldo + $monto;
    }
    $pdf->Cell($ancho_linea, $tamanio_linea, "", 0, 0, 'R'); //
    if ($tipope == "R") {
        $pdf->Cell($ancho_linea, $tamanio_linea, 'Q ' . number_format($monto, 2, '.', ','), 0, 0, 'R'); //   
        $saldo = $saldo - $monto;
    }

    $pdf->Cell($ancho_linea2, $tamanio_linea, $ncheque, 0, 0, 'C');
    $pdf->Cell($ancho_linea2, $tamanio_linea, $partida, 0, 0, 'C');
    $pdf->Cell($ancho_linea, $tamanio_linea, 'Q ' . number_format($saldo, 2, '.', ','), 0, 1, 'R');
}
//fin ingresos

$pdf->Output();
