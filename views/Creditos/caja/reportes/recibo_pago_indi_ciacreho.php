<?php
include '../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
require '../../../../fpdf/fpdf.php';
require '../../../../vendor/autoload.php';
session_start();
date_default_timezone_set('America/Guatemala');
use Luecano\NumeroALetras\NumeroALetras;

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}
//se recibe los datos
$datos = $_POST["datosval"];

//Informacion de datosval 
$inputs = $datos[0];
$archivo = $datos[3];

//Informacion de archivo 
$usuario = $archivo[0];
$codigocredito = $archivo[1];
$numerocuota = $archivo[2];
$cnuming = $archivo[3];

printpdf($usuario, $codigocredito, $numerocuota, $cnuming, $conexion);

function printpdf($usuario, $codigocredito, $numerocuota, $cnuming, $conexion)
{
    $consulta = "SELECT ck.DFECPRO AS fechadoc, CAST(ck.DFECSIS as Date) AS fechaaplica, cl.short_name AS nombre, cm.CCODCTA AS ccodcta, ck.CNUMING AS numdoc, ck.CCONCEP AS concepto, 
	ctf.descripcion AS fuente, ck.KP AS capital, ck.INTERES AS interes, ck.MORA AS mora, (IFNULL(ck.AHOPRG,0) + IFNULL(ck.OTR,0)) AS otros,
	(IFNULL(ck.KP,0) + IFNULL(ck.INTERES,0) + IFNULL(ck.MORA,0) + IFNULL(ck.AHOPRG,0) + IFNULL(ck.OTR,0)) AS total,
	((SELECT IFNULL(SUM(ck2.NMONTO),0) AS montocapital FROM CREDKAR ck2 WHERE ck2.CTIPPAG='D' AND ck2.CCODCTA=cm.CCODCTA AND ck2.CESTADO!='X')-(SELECT IFNULL(SUM(ck3.KP),0) AS totalpagado FROM CREDKAR ck3 WHERE ck3.CTIPPAG='P' AND ck3.CESTADO!='X' AND ck3.CCODCTA=cm.CCODCTA AND ck3.CNROCUO<='" . $numerocuota . "')) AS saldo, cl.no_identifica AS dpi
    FROM cremcre_meta cm
    INNER JOIN CREDKAR ck ON cm.CCODCTA=ck.CCODCTA
    INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
    INNER JOIN cre_productos pd ON cm.CCODPRD=pd.id
    INNER JOIN ctb_fuente_fondos ctf ON pd.id_fondo=ctf.id
    WHERE cm.CCODCTA='" . $codigocredito . "' AND ck.CNUMING='" . $cnuming . "' AND ck.CESTADO!='X' AND ck.CTIPPAG='P'";
    $datos = mysqli_query($conexion, $consulta);
    $aux = mysqli_error($conexion);
    if ($aux) {
        $opResult = array(
            'status' => 0,
            'mensaje' => 'Fallo en la consulta de los datos',
            'dato' => $datos
        );
        echo json_encode($opResult);
        return;
    }
    if (!$datos) {
        $opResult = array(
            'status' => 0,
            'mensaje' => 'No se logro consultar los datos',
            'dato' => $datos
        );
        echo json_encode($opResult);
    }
    $registro[] = [];
    $j = 0;
    $flag = false;
    while ($fila = mysqli_fetch_array($datos)) {
        $registro[$j] = $fila;
        $flag = true;
        $j++;
    }
    //COMPROBACION: SI SE ENCONTRARON REGISTROS
    if ($flag == false) {
        $opResult = array(
            'status' => 0,
            'mensaje' => 'No se encontraron datos',
            'dato' => $datos
        );
        echo json_encode($opResult);
        return;
    }
    //FIN COMPROBACION
    $queryins = mysqli_query($conexion, "SELECT * FROM clhpzzvb_bd_general_coopera.info_coperativa ins
    INNER JOIN tb_agencia ag ON ag.id_institucion=ins.id_cop where ag.id_agencia=" . $_SESSION['id_agencia']);
    $info[] = [];
    $j = 0;
    while ($fil = mysqli_fetch_array($queryins)) {
        $info[$j] = $fil;
        $j++;
    }
    $hoy = date("d-m-Y H:i:s");
    $institucion = utf8_decode($info[0]["nomb_comple"]);

    //lo que se tiene que repetir en cada una de las hojas
    class PDF extends FPDF
    {
        // atributos de la clase
        public $institucion;

        public function __construct($institucion)
        {
            parent::__construct();
            $this->institucion = $institucion;
        }
    }

    $pdf = new PDF($institucion);
    $pdf->AliasNbPages();
    $pdf->AddPage();

    reciboCiacreho($pdf, $registro, $hoy, $usuario, $info);

    ob_start();
    $pdf->Output();
    $pdfData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Comprobante generado correctamente',
        'namefile' => "Comprobanteindividual",
        'tipo' => "pdf",
        'data' => "data:application/pdf;base64," . base64_encode($pdfData)
    );
    echo json_encode($opResult);
}

// function recibo($pdf, $registro, $hoy, $usuario,$info)
// {
//     // $oficina = utf8_decode($info[0]["nom_agencia"]);
//     // $direccionins = utf8_decode($info[0]["muni_lug"]);
//     // $emailins = $info[0]["emai"];
//     // $telefonosins = $info[0]["tel_1"] . '   ' . $info[0]["tel_2"];;
//     // $nitins = $info[0]["nit"];
//     // $rutalogomicro = "../../../../includes/img/logomicro.png";
//     // $rutalogoins = "../../../.." . $info[0]["log_img"];

//     $fuente = "Courier";
//     $tamanio_linea = 3;
//     $ancho_linea2 = 30;
//     $pdf->SetFont($fuente, '', 9);

//     // $pdf->CellFit(0, $tamanio_linea + 1, ' ', 1, 0, 'L', 0, '', 1, 0);
//     $pdf->Ln(5);
//     //NUMERO DE DOCUMENTO
//     $pdf->CellFit($ancho_linea2 + 70, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, 'Documento No.', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->SetFont($fuente, 'B', 9);
//     $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 1, $registro[0][4], 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, $hoy, 0, 0, 'L', 0, '', 1, 0);
//     $pdf->SetFont($fuente, '', 9);
//     $pdf->Ln(5);

//     //FECHA DOCTO Y FUENTES
//     $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 1, 'FECHA DOCTO.', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->SetFont($fuente, 'B', 9);
//     $pdf->CellFit($ancho_linea2 - 7, $tamanio_linea + 1, date("d-m-Y", strtotime($registro[0][0])), 0, 0, 'L', 0, '', 1, 0);
//     $pdf->SetFont($fuente, '', 9);
//     $pdf->CellFit($ancho_linea2 - 27, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, mb_strtoupper($registro[0][6], 'utf-8'), 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 - 26, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'CONCEPTO', 1, 0, 'C', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'CANTIDAD', 1, 0, 'C', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->Ln(5);

//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, 'FECHA APLICA:', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 + 23, $tamanio_linea + 1, date("d-m-Y", strtotime($registro[0][1])), 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'CAPITAL', 'L-R', 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L', 0, 'C', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, $registro[0][7], 'R', 0, 'R', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->Ln(5);

//     //FECHA APLICA Y CAPITAL
//     $pdf->CellFit($ancho_linea2 + 53, $tamanio_linea + 1, 'NOMBRE:', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'INTERESES', 'L-R', 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L', 0, 'C', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, $registro[0][8], 'R', 0, 'R', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->Ln(5);

//     //NOMBRE Y MORA
//     $pdf->CellFit($ancho_linea2 + 53, $tamanio_linea + 1, utf8_decode(mb_strtoupper($registro[0][2], 'utf-8')), 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'MORA', 'L-R', 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L', 0, 'C', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, $registro[0][9], 'R', 0, 'R', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->Ln(5);

//     //PRESTAMO Y OTROS
//     $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 1, 'CUENTA:', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 + 28, $tamanio_linea + 1, $registro[0][3], 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'OTROS', 'L-R', 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L', 0, 'C', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, $registro[0][10], 'R', 0, 'R', 0, '', 1, 0);
//     $pdf->CellFit(1, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 - 1, $tamanio_linea + 2, 'SALDO', 'L-R-T', 0, 'C', 0, '', 1, 0);
//     $pdf->Ln(5);

//     //CANTIDAD EN LETRAS
//     $pdf->CellFit($ancho_linea2 + 53, $tamanio_linea + 1, 'Cantidad en letras:', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'TOTAL', 'L-R-B', 0, 'C', 0, '', 1, 0);
//     $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L-B', 0, 'C', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, $registro[0][11], 'R-B', 0, 'R', 0, '', 1, 0);
//     $pdf->CellFit(1, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L-B', 0, 'C', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 - 6, $tamanio_linea + 2, $registro[0][12], 'R-B', 0, 'R', 0, '', 1, 0);
//     $pdf->Ln(6);

//     //TOTAL EN LETRAS
//     $format_monto = new NumeroALetras();
//     $decimal = explode(".", $registro[0][11]);
//     $res = ($decimal[1] == 0) ? 0 : $decimal[1];
//     $pdf->MultiCell(0, $tamanio_linea + 1, utf8_decode($format_monto->toMoney($decimal[0], 2, '', '')) . $res . "/100", 0, 'L');
//     $pdf->Ln(5);
//     $pdf->MultiCell(0, $tamanio_linea + 1, utf8_decode(mb_strtoupper($registro[0][5], 'utf-8')), 0, 'L');
//     $pdf->Ln(6);

//     //USUARIO
//     $pdf->CellFit(0, $tamanio_linea + 1, 'USUARIO:' . $usuario, 0, 0, 'C', 0, '', 1, 0);
// }

function reciboCiacreho($pdf, $registro, $hoy, $usuario, $info)
{
    // $oficina = utf8_decode($info[0]["nom_agencia"]);
    // $direccionins = utf8_decode($info[0]["muni_lug"]);
    // $emailins = $info[0]["emai"];
    // $telefonosins = $info[0]["tel_1"] . '   ' . $info[0]["tel_2"];;
    // $nitins = $info[0]["nit"];
    // $rutalogomicro = "../../../../includes/img/logomicro.png";
    // $rutalogoins = "../../../.." . $info[0]["log_img"];

    $pdf->AddFont('Calibri', '', 'calibri.php');
    $pdf->AddFont('Calibri', 'B', 'calibrib.php');
    $fuente = "Calibri";

    $tabular = 60;
    $tamanio_linea = 3;
    $ancho_linea2 = 30;
    $pdf->ln(-4);
    $pdf->SetFont($fuente, '', 9);
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2*2, $tamanio_linea + 2, "  ", 0, 0, 'L', 0, '', 1, 0); 
    $pdf->CellFit($ancho_linea2*7+10, $tamanio_linea + 1, 'Documento No. ' . $registro[0][4], 0, 0, 'C', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', 11);
    $pdf->ln(8);
    //Nombre del cliente
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2*2, $tamanio_linea + 2, "  ", 0, 0, 'L', 0, '', 1, 0); 
    $pdf->CellFit($ancho_linea2 + 75, $tamanio_linea + 1, ' ' . (utf8_decode(mb_strtoupper($registro[0][2], 'utf-8'))), 0, 0, 'C', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', 11);
    $pdf->ln(8);
    //CANTIDAD EN LETRAS
    $pdf->SetFont($fuente, 'B', 11);
    $decimal = explode(".", $registro[0][11]);
    $res = ($decimal[1] == 0) ? 0 : $decimal[1];
    $pdf->Ln(0);
    $format_monto = new NumeroALetras();
    $pdf->CellFit($ancho_linea2*2+15, $tamanio_linea + 2, "  ", 0, 0, 'L', 0, '', 1, 0); 
  //  $pdf->CellFit($ancho_linea2 + 34, $tamanio_linea + 2, utf8_decode($format_monto->toMoney($decimal[0])). $res . "/100", 0, 0, 'L', 0, '', 1, 0);
  $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 2, $registro[0][11], '', 0, 'L', 0, '', 1, 0); //TOTAL   
  $pdf->ln(6);
    //Tipo de fondo
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2*2+5, $tamanio_linea + 2, "  ", 0, 0, 'L', 0, '', 1, 0); //celda bacia 
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, mb_strtoupper($registro[0][6], 'utf-8'), 0, 0, 'L', 0, '', 1, 0); //tipo de fondo
    $pdf->SetFont($fuente, '', 11);
    $pdf->CellFit(4, $tamanio_linea + 2, '  ', 0, 0, 'C', 0, '', 1, 0); //ESPACIO
    //$pdf->ln(5);//FORMATO DE CAP, INT, MORA, OTR,
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, 'CONCEPTO', 1, 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, 'CANTIDAD', 1, 0, 'C', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', 11);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, '  ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->ln(5);

    //Fecha ***********
    $pdf->CellFit($ancho_linea2*2+5, $tamanio_linea + 2, "  ", 0, 0, 'L', 0, '', 1, 0); //celda vacia 
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2 - 20, $tamanio_linea + 2, 'FECHA: ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2 - 10, $tamanio_linea + 2, date("d-m-Y", strtotime($registro[0][0])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', 9);
    $pdf->CellFit(4, $tamanio_linea + 2, '  ', 0, 0, 'C', 0, '', 1, 0); //ESPACIO
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, 'CAPITAL', 'L-R', 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', 11);
    $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 2, $registro[0][7], 'R', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->ln(5);

    //No. de cuenta **********
    $pdf->CellFit($ancho_linea2*2+5, $tamanio_linea + 2, "  ", 0, 0, 'L', 0, '', 1, 0); //celda vacia 
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, 'CUENTA: ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', 11);
    $pdf->CellFit(4, $tamanio_linea + 2, '  ', 0, 0, 'C', 0, '', 1, 0); //ESPACIO
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, 'INTERESES', 'L-R', 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', 11);
    $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 2, $registro[0][8], 'R', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->ln(5);

    //Cuenta************************
    $pdf->CellFit($ancho_linea2*2+5, $tamanio_linea + 2, "  ", 0, 0, 'L', 0, '', 1, 0); //celda vacia 
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2+4, $tamanio_linea + 2, $registro[0][3], 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', 11);
   // $pdf->CellFit(4, $tamanio_linea + 2, '  ', 0, 0, 'C', 0, '', 1, 0); //ESPACIO
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, 'MORA', 'L-R', 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', 11);
    $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 2, $registro[0][9], 'R', 0, 'R', 0, '', 1, 0);
    // $pdf->CellFit(1, $tamanio_linea + 2, ' ', 0, 0, 'L', 0, '', 1, 0);

    if($registro[0][10] < 1){
        $pdf->CellFit(1, $tamanio_linea + 2, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit(23, $tamanio_linea + 2, 'SALDO', 'L-R-T', 0, 'L', 0, '', 1, 0);
        $pdf->SetFont($fuente, '', 11);
    }
    $pdf->Ln(5);

    //OTROS
    if ($registro[0][10] > 0) {
        $pdf->CellFit($ancho_linea2*3+5 + 4, $tamanio_linea + 2, "  ", 0, 0, 'L', 0, '', 1, 0); //celda bacia 
        $pdf->SetFont($fuente, 'B', 11);
        $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, 'OTROS', 'L-R', 0, 'L', 0, '', 1, 0);
        $pdf->SetFont($fuente, '', 11);
        $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L', 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 2, $registro[0][10], 'R', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit(1, $tamanio_linea + 2, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->SetFont($fuente, 'B', 11);
        $pdf->CellFit(23, $tamanio_linea + 2, 'SALDO', 'L-R-T', 0, 'C', 0, '', 1, 0);
        $pdf->SetFont($fuente, '', 11);
        $pdf->Ln(5);
    }

    //*****
    $pdf->CellFit($ancho_linea2*2+5 + 34, $tamanio_linea + 2, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, 'TOTAL', 'L-R-B', 0, 'C', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', 11);
    $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L-B', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 2, $registro[0][11], 'R-B', 0, 'R', 0, '', 1, 0); //TOTAL 

    $pdf->CellFit(1, $tamanio_linea + 2, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L-B', 0, 'C', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', 11);
    $pdf->CellFit(18, $tamanio_linea + 2, $registro[0][12], 'R-B', 0, 'R', 0, '', 1, 0);
    $pdf->Ln(6);

    if ($registro[0][10] > 0) {
        // Si $registro[0][10] es mayor que 0
    } else {
        // Si $registro[0][10] NO es mayor que 0
        $pdf->Ln(2);
        $pdf->CellFit($ancho_linea2 * 3 + 10 + 4, $tamanio_linea + 2, "  ", 0, 0, 'L', 0, '', 1, 0); //celda vacía 
        $pdf->Ln(2);
    }
    
    //TOTAL EN LETRAS
    $pdf->SetFont($fuente, '', 11);
    $decimal = explode(".", $registro[0][11]);
    $res = ($decimal[1] == 0) ? 0 : $decimal[1];
    $pdf->Ln(1);
     $pdf->CellFit($ancho_linea2*2 + 14, $tamanio_linea + 2, utf8_decode($format_monto->toMoney($decimal[0])). $res . "/100", 0, 0, 'C', 0, '', 1, 0);

    $pdf->CellFit($tabular + 40, $tamanio_linea + 2, 'AMORTIZACION A CREDITO / PAGO DE INTERES DE CREDITO DE', 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(4);

    //CLIENTE
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2-5, $tamanio_linea + 2, " ", 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($tabular + 52, $tamanio_linea + 2, (utf8_decode(mb_strtoupper($registro[0][2], 'utf-8'))), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(4);
    //DPI 
    $pdf->CellFit($ancho_linea2-5, $tamanio_linea + 2, " ", 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($tabular + 33, $tamanio_linea + 2, $registro[0][13], 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(1);
    
    $pdf->CellFit($ancho_linea2*3+40, $tamanio_linea + 2, "  ", 0, 0, 'L', 0, '', 1, 0); 
    $pdf->CellFit($ancho_linea2*2, $tamanio_linea + 2, 'USUARIO: ' . utf8_decode(utf8_decode(mb_strtoupper($usuario, 'utf-8'))), 0, 0, 'L', 0, '', 1, 0);



    //     $pdf->ln(40); //formato original *******************************************************************************
    //     $pdf->CellFit($ancho_linea2 + 70, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, 'Documento No. ', 0, 0, 'R', 0, '', 1, 0);
    //     $pdf->SetFont($fuente, 'B', 9);

    //     $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 1, $registro[0][4], 0, 0, 'L', 0, '', 1, 0);

    //     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, $hoy, 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->SetFont($fuente, '', 9);
    //     $pdf->Ln(5);

    //     //FECHA DOCTO Y FUENTES
    //     $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 1, 'FECHA DOCTO.', 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->SetFont($fuente, 'B', 9);
    //     $pdf->CellFit($ancho_linea2 - 7, $tamanio_linea + 1, date("d-m-Y", strtotime($registro[0][0])), 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->SetFont($fuente, '', 9);
    //     $pdf->CellFit($ancho_linea2 - 27, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, mb_strtoupper($registro[0][6], 'utf-8'), 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2 - 26, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);

    //     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'CONCEPTO', 1, 0, 'C', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'CANTIDAD', 1, 0, 'C', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, '  ', 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->Ln(5);
    //     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, 'FECHA APLICA:', 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2 + 23, $tamanio_linea + 1, date("d-m-Y", strtotime($registro[0][1])), 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);

    //     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'CAPITAL', 'L-R', 0, 'L', 0, '', 1, 0);
    //     $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L', 0, 'C', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, $registro[0][7], 'R', 0, 'R', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->Ln(5);

    //     //FECHA APLICA Y CAPITAL
    //     $pdf->CellFit($ancho_linea2 + 53, $tamanio_linea + 1, 'NOMBRE:', 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);

    //     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'INTERESES', 'L-R', 0, 'L', 0, '', 1, 0);
    //     $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L', 0, 'C', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, $registro[0][8], 'R', 0, 'R', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->Ln(5);

    //     //NOMBRE Y MORA
    //     $pdf->CellFit($ancho_linea2 + 53, $tamanio_linea + 1, utf8_decode(mb_strtoupper($registro[0][2], 'utf-8')), 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);

    //     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'MORA', 'L-R', 0, 'L', 0, '', 1, 0);
    //     $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L', 0, 'C', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, $registro[0][9], 'R', 0, 'R', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->Ln(5);

    //     //PRESTAMO Y OTROS
    //     $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 1, 'CUENTA:', 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2 + 28, $tamanio_linea + 1, $registro[0][3], 0, 0, 'L', 0, '', 1, 0);

    //     $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' *.* ', 0, 0, 'L', 0, '', 1, 0);

    //     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'OTROS', 'L-R', 0, 'L', 0, '', 1, 0);
    //     $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L', 0, 'C', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, $registro[0][10], 'R', 0, 'R', 0, '', 1, 0);
    //     $pdf->CellFit(1, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);

    //     $pdf->CellFit($ancho_linea2 - 1, $tamanio_linea + 2, 'SALDO', 'L-R-T', 0, 'C', 0, '', 1, 0);
    //     $pdf->Ln(5);

    //     //CANTIDAD EN LETRAS
    //     $pdf->CellFit($ancho_linea2 + 53, $tamanio_linea + 1, 'Cantidad en letras:', 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'TOTAL', 'L-R-B', 0, 'C', 0, '', 1, 0);
    //     $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L-B', 0, 'C', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, '185000', 'R-B', 0, 'R', 0, '', 1, 0);
    //     $pdf->CellFit(1, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);

    //     $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L-B', 0, 'C', 0, '', 1, 0);
    //     $pdf->CellFit($ancho_linea2 - 6, $tamanio_linea + 2, $registro[0][12], 'R-B', 0, 'R', 0, '', 1, 0);
    //     $pdf->Ln(6);





    //     //TOTAL EN LETRAS
    //     $format_monto = new NumeroALetras();
    //     $decimal = explode(".", $registro[0][11]);
    //     $res = ($decimal[1] == 0) ? 0 : $decimal[1];
    //     $pdf->MultiCell(0, $tamanio_linea + 1, utf8_decode($format_monto->toMoney($decimal[0], 2, '', '')) . $res . "/100", 0, 'L');
    //     $pdf->Ln(5);
    //     $pdf->MultiCell(0, $tamanio_linea + 1, utf8_decode(mb_strtoupper($registro[0][5], 'utf-8')), 0, 'L');
    //     $pdf->Ln(6);

    //     //USUARIO
    //     $pdf->CellFit(0, $tamanio_linea + 1, 'USUARIO:' . $usuario, 0, 0, 'C', 0, '', 1, 0);
}


function recibo_credireforma($pdf, $registro, $hoy, $usuario, $info)
{
    $institucion = utf8_decode($info[0]["nomb_comple"]);
    $dire = utf8_decode($info[0]["direccion"]);
    $direccionins = utf8_decode($info[0]["muni_lug"]);
    $emailins = $info[0]["emai"];
    $telefonosins = $info[0]["tel_1"] . '   ' . $info[0]["tel_2"];
    $pdf->SetCompression(false);
    $pdf->AddFont('Calibri', '', 'calibri.php');
    $pdf->AddFont('Calibri', 'B', 'calibrib.php');
    $fuente = "Calibri";
    $pdf->SetFont($fuente, 'B', 11);

    $tamanio_linea = 3;
    $ancho_linea2 = 30;
    $pdf->Ln(3); //AUMENTAR O DISMINUIR EL MARGEN TOP

    //--REQ--CREDIREFORMA--1--Impresion de datos de la institucion en el comprobante de pago
    $pdf->Cell(0, 3, $institucion, 0, 1, 'C');
    $pdf->Cell(0, 3, $dire, 0, 1, 'C');
    $pdf->Cell(0, 3, $direccionins, 0, 1, 'C');
    $pdf->Cell(0, 3, $telefonosins, 0, 1, 'C');

    $tamaniofuente = 11;
    $pdf->SetFont($fuente, '', $tamaniofuente);
    $pdf->Ln(3);
    //NUMERO DE DOCUMENTO
    // $pdf->CellFit($ancho_linea2 + 70, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, 'Documento No.', 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, 'B', $tamaniofuente);
    $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea + 1, trim($registro[0][4]), 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, $hoy, 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamaniofuente);
    $pdf->Ln(5);

    //FECHA DOCTO Y FUENTES
    $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 1, 'FECHA DOCTO.', 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, 'B', $tamaniofuente);
    $pdf->CellFit($ancho_linea2 - 7, $tamanio_linea + 1, date("d-m-Y", strtotime($registro[0][0])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamaniofuente);
    $pdf->CellFit($ancho_linea2 - 27, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, mb_strtoupper($registro[0][6], 'utf-8'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 26, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'CONCEPTO', 1, 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'CANTIDAD', 1, 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(5);

    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, 'FECHA APLICA:', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 23, $tamanio_linea + 1, date("d-m-Y", strtotime($registro[0][1])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'CAPITAL', 'L-R', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, $registro[0][7], 'R', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(5);

    //FECHA APLICA Y CAPITAL
    $pdf->CellFit($ancho_linea2 + 53, $tamanio_linea + 1, 'NOMBRE:', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'INTERESES', 'L-R', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, $registro[0][8], 'R', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(5);

    //NOMBRE Y MORA
    $pdf->CellFit($ancho_linea2 + 53, $tamanio_linea + 1, utf8_decode(mb_strtoupper($registro[0][2], 'utf-8')), 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'MORA', 'L-R', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, $registro[0][9], 'R', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(5);

    //PRESTAMO Y OTROS
    $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 1, 'CUENTA:', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 28, $tamanio_linea + 1, $registro[0][3], 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'OTROS', 'L-R', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, $registro[0][10], 'R', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit(1, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 1, $tamanio_linea + 2, 'SALDO', 'L-R-T', 0, 'C', 0, '', 1, 0);
    $pdf->Ln(5);

    //CANTIDAD EN LETRAS
    $pdf->CellFit($ancho_linea2 + 53, $tamanio_linea + 1, 'Cantidad en letras:', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 2, 'TOTAL', 'L-R-B', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L-B', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 2, $registro[0][11], 'R-B', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit(1, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit(5, $tamanio_linea + 2, 'Q', 'L-B', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 6, $tamanio_linea + 2, $registro[0][12], 'R-B', 0, 'R', 0, '', 1, 0);
    $pdf->Ln(5);

    //TOTAL EN LETRAS
    $format_monto = new NumeroALetras();
    $decimal = explode(".", $registro[0][11]);
    $res = ($decimal[1] == 0) ? 0 : $decimal[1];
    $pdf->MultiCell(0, $tamanio_linea + 1, utf8_decode($format_monto->toMoney($decimal[0], 2, '', '')) . $res . "/100", 0, 'L');
    $pdf->Ln(2);
    $pdf->MultiCell(0, $tamanio_linea + 1, utf8_decode(mb_strtoupper(trim($registro[0][5]), 'utf-8')), 0, 'L');
    $pdf->Ln(2);

    //USUARIO
    $pdf->CellFit(0, $tamanio_linea + 1, 'USUARIO:' . utf8_decode($usuario), 0, 0, 'C', 0, '', 1, 0);
    //--REQ--CREDIREFORMA--2-- Firma del cajero y el cliente en el comprobante de pago
    $pdf->firmas(2, ['Cajero', 'Cliente'], 'Calibri');
}
