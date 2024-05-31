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
	((SELECT IFNULL(SUM(ck2.NMONTO),0) AS montocapital FROM CREDKAR ck2 WHERE ck2.CTIPPAG='D' AND ck2.CCODCTA=cm.CCODCTA AND ck2.CESTADO!='X')-(SELECT IFNULL(SUM(ck3.KP),0) AS totalpagado FROM CREDKAR ck3 WHERE ck3.CTIPPAG='P' AND ck3.CESTADO!='X' AND ck3.CCODCTA=cm.CCODCTA AND ck3.CNROCUO<='" . $numerocuota . "')) AS saldo
    FROM cremcre_meta cm
    INNER JOIN CREDKAR ck ON cm.CCODCTA=ck.CCODCTA
    INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
    INNER JOIN cre_productos pd ON cm.CCODPRD=pd.id
    INNER JOIN ctb_fuente_fondos ctf ON pd.id_fondo=ctf.id
    WHERE cm.CCODCTA='" . $codigocredito . "' AND ck.CNUMING='" . $cnuming . "'";
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
    $pdf->SetCompression(true);
    $pdf->AddFont('Calibri', '', 'calibri.php');
    $pdf->AddFont('Calibri', 'B', 'calibrib.php');
    reciboprimavera($pdf, $registro, $hoy, $usuario,$info);

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

function reciboprimavera($pdf, $registro, $hoy, $usuario,$info)
{
     $oficina = utf8_decode($info[0]["nom_agencia"]);
     $direccionins = utf8_decode($info[0]["muni_lug"]);
     $emailins = $info[0]["emai"];
     $telefonosins = $info[0]["tel_1"] . '   ' . $info[0]["tel_2"];;
     $nitins = $info[0]["nit"];
     $rutalogomicro = "../../../../includes/img/logomicro.png";
     $rutalogoins = "../../../.." . $info[0]["log_img"];

     $fuente = "Calibri";
    $tamanio_linea = 4;
    $ancho_linea2 = 30;
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2+130, $tamanio_linea + 1, ' ', 0, 0, 'C', 0, '', 1, 0);
    // $pdf->CellFit(0, $tamanio_linea + 1, ' ', 1, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(13);
    

    $pdf->CellFit($ancho_linea2 -15, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 10, $tamanio_linea + 1, date("d-m-Y", strtotime($registro[0][0])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 -15, $tamanio_linea + 1, 'ADG', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2+55 , $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $registro[0][11], 0, 0, 'R', 0, '', 1, 0);
    $pdf->Ln(6);
    $pdf->CellFit($ancho_linea2 -15, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 +85, $tamanio_linea + 1, utf8_decode(mb_strtoupper($registro[0][2], 'utf-8')), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);
    $pdf->CellFit($ancho_linea2 -15, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 +85, $tamanio_linea + 1, '2a. Calle 01-0310 Zona 4 Tecpan Guatemala Chimaltenango', 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);
    $format_monto = new NumeroALetras();
    $decimal = explode(".", $registro[0][11]);
    $res = ($decimal[1] == 0) ? 0 : $decimal[1];
    $pdf->CellFit($ancho_linea2 -15, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->Multicell($ancho_linea2+80, $tamanio_linea + 1, utf8_decode($format_monto->toMoney($decimal[0], 2, '', '')) . $res . "/100", 0, 'L');

    //BODY
    $pdf->Ln(13);
    $pdf->CellFit($ancho_linea2 -25, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2+5, $tamanio_linea + 1, 'CAPITAL', 'B', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 +83, $tamanio_linea + 1, ' ', 'B', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit(5, $tamanio_linea + 1, 'Q', 'B', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $registro[0][7], 'B', 0, 'R', 0, '', 1, 0);
    $pdf->Ln(6);
    $pdf->CellFit($ancho_linea2 -25, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, 'INTERESES', 'B', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 +83, $tamanio_linea + 1, ' ', 'B', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit(5, $tamanio_linea + 1, 'Q', 'B', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $registro[0][8], 'B', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);
    $pdf->CellFit($ancho_linea2 -25, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, 'MORA', 'B', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 +83, $tamanio_linea + 1, ' ', 'B', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit(5, $tamanio_linea + 1, 'Q', 'B', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $registro[0][9], 'B', 0, 'R', 0, '', 1, 0);
    $pdf->Ln(6);
    $pdf->CellFit($ancho_linea2 -25, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, 'OTROS', 'B', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 +83, $tamanio_linea + 1, ' ', 'B', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit(5, $tamanio_linea + 1, 'Q', 'B', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $registro[0][10], 'B', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit(1, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);
    $pdf->CellFit($ancho_linea2 -25, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, 'TOTAL', 'B', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 +83, $tamanio_linea + 1, ' ', 'B', 0, 'L', 0, '', 1, 0);
    $pdf->CellFit(5, $tamanio_linea + 1, 'Q', 'B', 0, 'C', 0, '', 1, 0);
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $registro[0][11], 'B', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit(1, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, 'B', 1);
    $pdf->CellFit(5, $tamanio_linea + 1, 'Q', 0, 0, 'C', 0, '', 1, 0);
    $pdf->SetFont($fuente, 'B', 11);
    $pdf->Ln(10);

    $pdf->CellFit($ancho_linea2 -25, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, 'SALDO', 'L-R-T', 0, 'C', 0, '', 1, 0);
    $pdf->Ln(5);
    $pdf->CellFit($ancho_linea2 -25, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit(5, $tamanio_linea + 1, 'Q', 'L-B', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 , $tamanio_linea + 1, $registro[0][12],'B-R', 0, 'R', 0, '', 1, 0);
    $pdf->Ln(25);
    $pdf->CellFit($ancho_linea2 + 120, $tamanio_linea + 1, ' ', 0, 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $registro[0][11], 0, 0, 'R', 0, '', 1, 0);

    // //FECHA DOCTO Y FUENTES
    // $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 1, 'FECHA DOCTO.',0, 0, 'L', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea2 - 28, $tamanio_linea + 1, ' ',0, 0, 'L', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, 'FECHA APLICA:', 0, 0, 'L', 0, '', 1, 0);
    // $pdf->SetFont($fuente, 'B', 10);
    // //FONDOS
    // $pdf->CellFit($ancho_linea2 +1, $tamanio_linea + 1, mb_strtoupper($registro[0][6], 'utf-8'), 0, 0, 'L', 0, '', 1, 0);
    // $pdf->SetFont($fuente, 'B', 11);
    // //DESCRP
    // $pdf->CellFit($ancho_linea2 - 28, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, 'CONCEPTO', 0, 0, 'C', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, 'CANTIDAD', 0, 0, 'C', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    // $pdf->Ln(4);
    //FECHA DOCTO
    // $pdf->SetFont($fuente, 'B', 11);
    // $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea + 1, date("d-m-Y", strtotime($registro[0][0])), 0, 0, 'L', 0, '', 1, 0);
    // $pdf->SetFont($fuente, 'B', 11);
    // $pdf->CellFit($ancho_linea2 - 28, $tamanio_linea + 1, ' ',0, 0, 'L', 0, '', 1, 0);
    // //FECHA APLI
    // $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, date("d-m-Y", strtotime($registro[0][1])), 0, 0, 'L', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea2 + 3, $tamanio_linea + 1, ' ', 0, 0, 'C', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea2+5, $tamanio_linea + 1, 'CAPITAL', 0, 0, 'L', 0, '', 1, 0);
    // $pdf->CellFit(5, $tamanio_linea + 1, 'Q', 'L', 0, 'C', 0, '', 1, 0);
    // $pdf->SetFont($fuente, 'B', 11);
    // $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $registro[0][7], 'R', 0, 'R', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    // $pdf->Ln(6);

//     //FECHA APLICA Y CAPITAL
//     $pdf->SetFont($fuente, 'B', 11);
//     $pdf->CellFit($ancho_linea2 + 53, $tamanio_linea + 1, 'ASOCIADO:', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, 'INTERESES', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit(5, $tamanio_linea + 1, 'Q', 'L', 0, 'C', 0, '', 1, 0);
//     $pdf->SetFont($fuente, 'B', 10);
//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $registro[0][8], 'R', 0, 'R', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->Ln(6);
//     $pdf->SetFont($fuente, 'B', 11);
//     //NOMBRE Y MORA
//     $pdf->CellFit($ancho_linea2 + 53, $tamanio_linea + 1, utf8_decode(mb_strtoupper($registro[0][2], 'utf-8')), 'L-B-R', 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, 'MORA', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit(5, $tamanio_linea + 1, 'Q', 'L', 0, 'C', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $registro[0][9], 'R', 0, 'R', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->Ln(6);

//     //PRESTAMO Y OTROS
//     $pdf->CellFit($ancho_linea2 + 53, $tamanio_linea + 1, 'PRESTAMO:', 'L-T-R', 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, 'OTROS', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit(5, $tamanio_linea + 1, 'Q', 'L', 0, 'C', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $registro[0][10], 'R', 0, 'R', 0, '', 1, 0);
//     $pdf->CellFit(1, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 - 1, $tamanio_linea + 1, 'SALDO', 'L-R-T', 0, 'C', 0, '', 1, 0);
//     $pdf->Ln(6);
    
//     //CANTIDAD EN LETRAS
//     $pdf->SetFont($fuente, 'B', 11);
//     $pdf->CellFit($ancho_linea2 + 53, $tamanio_linea + 1, $registro[0][3], 'L-B-R', 0, 'L', 0, '', 1, 0);
//    // $pdf->CellFit($ancho_linea2 + 53, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//    $pdf->SetFont($fuente, 'B', 11);
//     $pdf->CellFit($ancho_linea2 - 23, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea + 1, 'TOTAL', 'L-R-B', 0, 'C', 0, '', 1, 0);
//     $pdf->CellFit(5, $tamanio_linea + 1, 'Q', 'L-B', 0, 'C', 0, '', 1, 0);
//     $pdf->SetFont($fuente, 'B', 11);
//     $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $registro[0][11], 'R-B', 0, 'R', 0, '', 1, 0);
//     $pdf->CellFit(1, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
//     $pdf->SetFont($fuente, 'B', 1);
//     $pdf->CellFit(5, $tamanio_linea + 1, 'Q', 'L-B', 0, 'C', 0, '', 1, 0);
//     $pdf->SetFont($fuente, 'B', 11);
//     $pdf->CellFit($ancho_linea2 - 6, $tamanio_linea + 1, $registro[0][12], 'R-B', 0, 'R', 0, '', 1, 0);
//     $pdf->Ln(7);

    //TOTAL EN LETRAS
    // // $format_monto = new NumeroALetras();
    // // $decimal = explode(".", $registro[0][11]);
    // // $res = ($decimal[1] == 0) ? 0 : $decimal[1];
    // // $pdf->CellFit($ancho_linea2 + 160, $tamanio_linea + 1, 'CANTIDAD EN LETRAS:', 'L-T-R', 0, 'L', 0, '', 1, 0);
    // // $pdf->Ln(4);
    // // $pdf->MultiCell(0, $tamanio_linea + 1, utf8_decode($format_monto->toMoney($decimal[0], 2, '', '')) . $res . "/100", 'L-B-R', 'L');
    // // $pdf->SetFont($fuente, 'B', 10);
    // // $pdf->Ln(1);
    // // $pdf->MultiCell(0, $tamanio_linea + 1, utf8_decode(mb_strtoupper($registro[0][5], 'utf-8')), 0, 'L');
    // // $pdf->Ln(13);
    // // //USUARIO
    // // $pdf->SetFont($fuente, 'B', 10);
    // // $pdf->CellFit($ancho_linea2 + 20, $tamanio_linea , 'FIRMA Y SELLO DEL OPERADOR', 'T', 0, 'L', 0, '', 1, 0);

    // $pdf->CellFit($ancho_linea2 - 28, $tamanio_linea , ' ', 0, 0, 'L', 0, '', 1, 0);

    // $pdf->SetFont($fuente, 'B', 11);
    // $pdf->CellFit(0, $tamanio_linea + 1,utf8_decode(mb_strtoupper( 'USUARIO:' . $usuario,'utf-8' )), 0, 0, 'C', 0, '', 1, 0);
}