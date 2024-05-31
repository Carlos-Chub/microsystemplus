<?php
session_start();
include '../../../includes/BD_con/db_con.php';
date_default_timezone_set('America/Guatemala');
require '../../../fpdf/fpdf_js.php';

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

$datos = $_POST["datosval"];
$inputs = $datos[0];
$selects = $datos[1];
$radios = $datos[2];
$archivo = $datos[3];
$cuenta = $archivo[0];

$bandera = false;
$codaport = $archivo[0];

$datoscli = mysqli_query($conexion, "SELECT * FROM `aprcta` WHERE `ccodaport`=$codaport");
while ($da = mysqli_fetch_array($datoscli, MYSQLI_ASSOC)) {
    $idcli = utf8_encode($da["ccodcli"]);
    $nit = utf8_encode($da["num_nit"]);
    $fecha_apertura = utf8_encode($da["fecha_apertura"]);
    $bandera = true;
}

if (!$bandera) {
    $opResult = array(
        'status' => 0,
        'mensaje' => 'No se encontraron datos, no se ha seleccionado un cliente o bien no existe su cuenta',
        'dato' => '0'
    );
    echo json_encode($opResult);
    return;
}

//se busca al cliente para extraer su nombre
$dpi="";
$data = mysqli_query($conexion, "SELECT `short_name`,`Direccion`, `no_identifica` FROM `tb_cliente` WHERE `idcod_cliente` = '$idcli'");
while ($dat = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
    $nombre = utf8_encode($dat["short_name"]);
    $direccion = utf8_encode($dat["Direccion"]);
    if ($direccion == "") {
        $direccion = "Sin dirección";
    }
    $dpi = ($dat["no_identifica"]);
}

$tip_aport = substr($codaport, 6, -6);  // devuelve posicion de libreta
$data_coordenadas = mysqli_query($conexion, "SELECT `xlibreta`,`ylibreta` FROM `aprtip` WHERE `ccodtip` = '$tip_aport'");
while ($dat = mysqli_fetch_array($data_coordenadas, MYSQLI_ASSOC)) {
    $x = utf8_encode($dat["xlibreta"]);
    $y = utf8_encode($dat["ylibreta"]);
    if ($x == "" || $x == null || $y == "" || $y == null) {
        $x = "-";
        $y = "-";
    }
}

class PDF_AutoPrint extends PDF_JavaScript
{
    function AutoPrint($printer = '')
    {
        // Open the print dialog
        if ($printer) {
            $printer = str_replace('\\', '\\\\', $printer);
            $script = "var pp = getPrintParams();";
            $script .= "pp.interactive = pp.constants.interactionLevel.full;";
            $script .= "pp.printerName = '$printer'";
            $script .= "print(pp);";
        } else
            $script = 'print(true);';
        $this->IncludeJS($script);
    }
}
$espacio = 4;
$espaciosegundacol = 88;
$pdf = new PDF_AutoPrint();
$pdf->AddPage();
$pdf->SetCompression(false);
$pdf->AddFont('Calibri', '', 'calibri.php');
$pdf->AddFont('Calibri', 'B', 'calibrib.php');
$pdf->SetFont('Calibri', '', 10);
$pdf->Text($x, $y-4, utf8_decode($nombre));
$pdf->Text($x + $espaciosegundacol, $y-4, $fecha_apertura);
$pdf->Text($x + $espaciosegundacol, $y, ' ');
$pdf->Text($x, $y, utf8_decode($direccion));
$pdf->Text($x, $y + $espacio, $cuenta);
$pdf->Text($x + $espaciosegundacol, $y + $espacio, $dpi);

ob_start();
$pdf->Output();
$pdfData = ob_get_contents();
ob_end_clean();

$opResult = array(
    'status' => 1,
    'mensaje' => 'Impresion de encabezado generado correctamente',
    'namefile' => "Encabezado-libreta-" . $cuenta,
    'tipo' => "pdf",
    'data' => "data:application/pdf;base64," . base64_encode($pdfData)
);
echo json_encode($opResult);
