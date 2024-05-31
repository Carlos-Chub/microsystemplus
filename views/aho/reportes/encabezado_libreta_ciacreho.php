<?php
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
require '../../../fpdf/fpdf_js.php';

$datos = $_POST["datosval"];
$inputs = $datos[0];
$selects = $datos[1];
$radios = $datos[2];
$archivo = $datos[3];
$cuenta = $archivo[0];

// $cuenta=$_GET["cod"];
$datoscli = mysqli_query($conexion, "SELECT * FROM `ahomcta` WHERE `ccodaho`='$cuenta'");
$bandera = false;

while ($da = mysqli_fetch_array($datoscli, MYSQLI_ASSOC)) {
    $idcli = utf8_encode($da["ccodcli"]);
    $nit = utf8_encode($da["num_nit"]);
    $nlibreta = utf8_encode($da["nlibreta"]);
    $fecha_apertura = utf8_encode($da["fecha_apertura"]);
    $bandera = true;
}

//COMPROBACION: SI SE ENCONTRARON REGISTROS
if (!$bandera) {
    $opResult = array(
        'status' => 0,
        'mensaje' => 'No se encontraron datos, no se ha seleccionado un cliente o bien no existe su cuenta',
        'dato' => '0'
    );
    echo json_encode($opResult);
    return;
}

$data = mysqli_query($conexion, "SELECT `short_name`, `no_identifica` FROM `tb_cliente` WHERE `idcod_cliente` = '$idcli'");
//$data = mysqli_query($conexion, "SELECT `short_name` FROM `tb_cliente` WHERE `idcod_cliente`='$idcli' OR `no_tributaria` = '$nit'");
$dat = mysqli_fetch_array($data, MYSQLI_ASSOC);
$nombre = ($dat["short_name"]);
$dpi = ($dat["no_identifica"]);

$tip = substr($cuenta, 6, 2);
$queryxy = mysqli_query($conexion, "SELECT `xlibreta`,`ylibreta` FROM `ahomtip` WHERE `ccodtip`='$tip'");
$xy = mysqli_fetch_array($queryxy, MYSQLI_ASSOC);
$x = utf8_encode($xy["xlibreta"]);
$y = utf8_encode($xy["ylibreta"]);

mysqli_close($conexion);
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
$espaciosegundacol = 87;
$pdf = new PDF_AutoPrint();
$pdf->AddPage();
$pdf->SetCompression(false);
$pdf->AddFont('Calibri', '', 'calibri.php');
$pdf->AddFont('Calibri', 'B', 'calibrib.php');
$pdf->SetFont('Calibri', '', 10);
$pdf->Text($x, $y, utf8_decode($nombre));
$fecha_apertura = date("d-m-Y", strtotime($fecha_apertura));
$pdf->Text($x + $espaciosegundacol, $y, $fecha_apertura);
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
