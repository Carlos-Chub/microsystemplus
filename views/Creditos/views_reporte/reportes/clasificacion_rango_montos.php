<?php
session_start();
include '../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
require '../../../../fpdf/fpdf.php';
require '../../../../vendor/autoload.php';
$hoy = date("Y-m-d");
ini_set('memory_limit', '4096M');
ini_set('max_execution_time', '3600');

use Complex\Functions;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Round;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
//[[`ffin`],[`codofi`,`fondoid`],[`ragencia`,`rfondos`],[ $idusuario; ]]
$datos = $_POST["datosval"];
$inputs = $datos[0];
$selects = $datos[1];
$radios = $datos[2];
$archivo = $datos[3];
$tipo = $_POST["tipo"];

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

if ($radios[1] == "anyf" && $selects[1] == 0) {
    echo json_encode(['status' => 0, 'mensaje' => 'Seleccionar fuente de fondos']);
    return;
}


//*****************ARMANDO LA CONSULTA**************
$condi = "";
//RANGO DE FECHAS
$filtrofecha = $inputs[0];
$titlereport = " AL " . date("d-m-Y", strtotime($filtrofecha));
//FUENTE DE FONDOS
$filfondo = ($radios[1] == "anyf") ? " AND ffon.id=" . $selects[1] : "";

//AGENCIA
$filagencia = ($radios[0] == "anyofi") ? " AND cremi.CODAgencia=" . $selects[0] : "";

//STATUS
$status = ($radios[2] == "allstatus") ? "cremi.CESTADO='F' OR cremi.CESTADO='G'" : " cremi.CESTADO='" . $radios[2] . "'";

//-----------------------------
$strquery = "SELECT 
CASE
     WHEN cremi.NCapDes BETWEEN 1 AND 499 THEN 'Hasta 500'
     WHEN cremi.NCapDes BETWEEN 500 AND 999 THEN 'Mas de 500-1000'
     WHEN cremi.NCapDes BETWEEN 1000 AND 1999 THEN 'Mas de 1000-2000'
     WHEN cremi.NCapDes BETWEEN 2000 AND 2999 THEN 'Mas de 2000-3000'
     WHEN cremi.NCapDes BETWEEN 3000 AND 3999 THEN 'Mas de 3000-4000'
     WHEN cremi.NCapDes BETWEEN 4000 AND 4999 THEN 'Mas de 4000-5000'
     WHEN cremi.NCapDes BETWEEN 5000 AND 5999 THEN 'Mas de 5000-6000'
     WHEN cremi.NCapDes BETWEEN 6000 AND 6999 THEN 'Mas de 6000-7000'
     WHEN cremi.NCapDes BETWEEN 7000 AND 7999 THEN 'Mas de 7000-8000'
     WHEN cremi.NCapDes BETWEEN 8000 AND 8999 THEN 'Mas de 8000-9000'
     WHEN cremi.NCapDes BETWEEN 9000 AND 9999 THEN 'Mas de 9000-10000'
     WHEN cremi.NCapDes BETWEEN 10000 AND 10999 THEN 'Mas de 10000-11000'
     WHEN cremi.NCapDes BETWEEN 11000 AND 11999 THEN 'Mas de 11000-12000'
     WHEN cremi.NCapDes BETWEEN 12000 AND 12999 THEN 'Mas de 12000-13000'
     WHEN cremi.NCapDes BETWEEN 13000 AND 13999 THEN 'Mas de 13000-14000'
     WHEN cremi.NCapDes BETWEEN 14000 AND 14999 THEN 'Mas de 14000-15000'
     WHEN cremi.NCapDes BETWEEN 15000 AND 15999 THEN 'Mas de 15000-16000'
     WHEN cremi.NCapDes BETWEEN 16000 AND 16999 THEN 'Mas de 16000-17000'
     WHEN cremi.NCapDes BETWEEN 17000 AND 17999 THEN 'Mas de 17000-18000'
     WHEN cremi.NCapDes BETWEEN 18000 AND 18999 THEN 'Mas de 18000-19000'
     WHEN cremi.NCapDes BETWEEN 19000 AND 19999 THEN 'Mas de 19000-20000'
     WHEN cremi.NCapDes BETWEEN 20000 AND 20999 THEN 'Mas de 20000-21000'
     WHEN cremi.NCapDes BETWEEN 21000 AND 21999 THEN 'Mas de 21000-22000'
     WHEN cremi.NCapDes BETWEEN 22000 AND 22999 THEN 'Mas de 22000-23000'
     WHEN cremi.NCapDes BETWEEN 23000 AND 23999 THEN 'Mas de 23000-24000'
     WHEN cremi.NCapDes BETWEEN 24000 AND 24999 THEN 'Mas de 24000-25000'
     WHEN cremi.NCapDes BETWEEN 25000 AND 25999 THEN 'Mas de 25000-26000'
     WHEN cremi.NCapDes BETWEEN 26000 AND 26999 THEN 'Mas de 26000-27000'
     WHEN cremi.NCapDes BETWEEN 27000 AND 27999 THEN 'Mas de 27000-28000'
     WHEN cremi.NCapDes BETWEEN 28000 AND 28999 THEN 'Mas de 28000-29000'
     WHEN cremi.NCapDes BETWEEN 29000 AND 29999 THEN 'Mas de 29000-30000'
     WHEN cremi.NCapDes BETWEEN 30000 AND 30999 THEN 'Mas de 30000-31000'
     WHEN cremi.NCapDes BETWEEN 31000 AND 31999 THEN 'Mas de 31000-32000'
     WHEN cremi.NCapDes BETWEEN 32000 AND 32999 THEN 'Mas de 32000-33000'
     WHEN cremi.NCapDes BETWEEN 33000 AND 33999 THEN 'Mas de 33000-34000'
     WHEN cremi.NCapDes BETWEEN 34000 AND 34999 THEN 'Mas de 34000-35000'
     WHEN cremi.NCapDes BETWEEN 35000 AND 35999 THEN 'Mas de 35000-36000'
     WHEN cremi.NCapDes BETWEEN 36000 AND 36999 THEN 'Mas de 36000-37000'
     WHEN cremi.NCapDes BETWEEN 37000 AND 37999 THEN 'Mas de 37000-38000'
     WHEN cremi.NCapDes BETWEEN 38000 AND 38999 THEN 'Mas de 38000-39000'
     WHEN cremi.NCapDes BETWEEN 39000 AND 39999 THEN 'Mas de 39000-40000'
     WHEN cremi.NCapDes BETWEEN 40000 AND 40999 THEN 'Mas de 40000-41000'
     WHEN cremi.NCapDes BETWEEN 41000 AND 41999 THEN 'Mas de 41000-42000'
     WHEN cremi.NCapDes BETWEEN 42000 AND 42999 THEN 'Mas de 42000-43000'
     WHEN cremi.NCapDes BETWEEN 43000 AND 43999 THEN 'Mas de 43000-44000'
     WHEN cremi.NCapDes BETWEEN 44000 AND 44999 THEN 'Mas de 44000-45000'
     WHEN cremi.NCapDes BETWEEN 45000 AND 45999 THEN 'Mas de 45000-46000'
     WHEN cremi.NCapDes BETWEEN 46000 AND 46999 THEN 'Mas de 46000-47000'
     WHEN cremi.NCapDes BETWEEN 47000 AND 47999 THEN 'Mas de 47000-48000'
     WHEN cremi.NCapDes BETWEEN 48000 AND 48999 THEN 'Mas de 48000-49000'
     WHEN cremi.NCapDes BETWEEN 49000 AND 49999 THEN 'Mas de 49000-50000'
     ELSE 'Mas  de  50000'
 END AS rango,
 cremi.DFecDsbls,
 cremi.MonSug,
 cremi.NCapDes,
 SUM(cremi.NCapDes) AS cantidad_Ncapdes,
 SUM(crepg.nmorpag) AS suma_mora,
SUM(crepg.nmorpag > 1) AS cantidad_mora,
 COUNT(*) AS cantidad_registros
 
FROM 
 cremcre_meta cremi 
 INNER JOIN
     Cre_ppg crepg ON cremi.CCODCTA = crepg.CCODCTA
INNER JOIN 
 tb_cliente cli ON cli.idcod_cliente = cremi.CodCli 
INNER JOIN 
 cre_productos prod ON prod.id = cremi.CCODPRD 
INNER JOIN 
 ctb_fuente_fondos ffon ON ffon.id = prod.id_fondo 
INNER JOIN 
 tb_usuario usu ON usu.id_usu = cremi.CodAnal 
LEFT JOIN 
 `clhpzzvb_bd_general_coopera`.tb_destinocredito dest ON dest.id_DestinoCredito=cremi.Cdescre 
LEFT JOIN 
 `clhpzzvb_bd_general_coopera`.`tb_cre_periodos` creper ON creper.cod_msplus=cremi.NtipPerC 
LEFT JOIN 
 (SELECT ccodcta, MAX(dfecven) AS dfecven, SUM(nintere) AS sum_nintere FROM Cre_ppg GROUP BY ccodcta) AS ppg ON ppg.ccodcta = cremi.CCODCTA 
LEFT JOIN 
 (SELECT ccodcta, MAX(dfecven) AS dfecven, SUM(ncapita) AS sum_ncapita, SUM(nintere) AS sum_nintere FROM Cre_ppg WHERE dfecven <= '$filtrofecha' GROUP BY ccodcta) AS ppg_ult ON ppg_ult.ccodcta = cremi.CCODCTA 
LEFT JOIN 
 (SELECT ccodcta, SUM(KP) AS sum_KP, SUM(interes) AS sum_interes, SUM(MORA) AS sum_MORA, SUM(AHOPRG) + SUM(OTR) AS sum_AHOPRG_OTR FROM CREDKAR WHERE dfecpro <= '$filtrofecha' AND cestado != 'X' AND ctippag = 'P' GROUP BY ccodcta) AS kar ON kar.ccodcta = cremi.CCODCTA 
LEFT JOIN 
 tb_grupo grupo ON grupo.id_grupos = cremi.CCodGrupo 
 WHERE (" . $status . ") AND cremi.DFecDsbls <= '$filtrofecha'" . $filfondo . $filagencia ." 
GROUP BY 
 rango
ORDER BY 
  CASE
   WHEN cremi.NCapDes BETWEEN 1 AND 499 THEN 1
     WHEN cremi.NCapDes BETWEEN 500 AND 999 THEN 2
     WHEN cremi.NCapDes BETWEEN 1000 AND 1999 THEN 3
     WHEN cremi.NCapDes BETWEEN 2000 AND 2999 THEN 4
     WHEN cremi.NCapDes BETWEEN 3000 AND 3999 THEN 5
     WHEN cremi.NCapDes BETWEEN 4000 AND 4999 THEN 6
     WHEN cremi.NCapDes BETWEEN 5000 AND 5999 THEN 7
     WHEN cremi.NCapDes BETWEEN 6000 AND 6999 THEN 8
     WHEN cremi.NCapDes BETWEEN 7000 AND 7999 THEN 9
     WHEN cremi.NCapDes BETWEEN 8000 AND 8999 THEN 10
     WHEN cremi.NCapDes BETWEEN 9000 AND 9999 THEN 11
     WHEN cremi.NCapDes BETWEEN 10000 AND 10999 THEN 12
     WHEN cremi.NCapDes BETWEEN 11000 AND 11999 THEN 13
     WHEN cremi.NCapDes BETWEEN 12000 AND 12999 THEN 14
     WHEN cremi.NCapDes BETWEEN 13000 AND 13999 THEN 15
     WHEN cremi.NCapDes BETWEEN 14000 AND 14999 THEN 16
     WHEN cremi.NCapDes BETWEEN 15000 AND 15999 THEN 17
     WHEN cremi.NCapDes BETWEEN 16000 AND 16999 THEN 18
     WHEN cremi.NCapDes BETWEEN 17000 AND 17999 THEN 19
     WHEN cremi.NCapDes BETWEEN 18000 AND 18999 THEN 20
     WHEN cremi.NCapDes BETWEEN 19000 AND 19999 THEN 21
     WHEN cremi.NCapDes BETWEEN 20000 AND 20999 THEN 22
     WHEN cremi.NCapDes BETWEEN 21000 AND 21999 THEN 23
     WHEN cremi.NCapDes BETWEEN 22000 AND 22999 THEN 24
     WHEN cremi.NCapDes BETWEEN 23000 AND 23999 THEN 25
     WHEN cremi.NCapDes BETWEEN 24000 AND 24999 THEN 26
     WHEN cremi.NCapDes BETWEEN 25000 AND 25999 THEN 27
     WHEN cremi.NCapDes BETWEEN 26000 AND 26999 THEN 28
     WHEN cremi.NCapDes BETWEEN 27000 AND 27999 THEN 29
     WHEN cremi.NCapDes BETWEEN 28000 AND 28999 THEN 30
     WHEN cremi.NCapDes BETWEEN 29000 AND 29999 THEN 31
     WHEN cremi.NCapDes BETWEEN 30000 AND 30999 THEN 32
     WHEN cremi.NCapDes BETWEEN 31000 AND 31999 THEN 33
     WHEN cremi.NCapDes BETWEEN 32000 AND 32999 THEN 34
     WHEN cremi.NCapDes BETWEEN 33000 AND 33999 THEN 35
     WHEN cremi.NCapDes BETWEEN 34000 AND 34999 THEN 36
     WHEN cremi.NCapDes BETWEEN 35000 AND 35999 THEN 37
     WHEN cremi.NCapDes BETWEEN 36000 AND 36999 THEN 38
     WHEN cremi.NCapDes BETWEEN 37000 AND 37999 THEN 39
     WHEN cremi.NCapDes BETWEEN 38000 AND 38999 THEN 40
     WHEN cremi.NCapDes BETWEEN 39000 AND 39999 THEN 41
     WHEN cremi.NCapDes BETWEEN 40000 AND 40999 THEN 42
     WHEN cremi.NCapDes BETWEEN 41000 AND 41999 THEN 43
     WHEN cremi.NCapDes BETWEEN 42000 AND 42999 THEN 44
     WHEN cremi.NCapDes BETWEEN 43000 AND 43999 THEN 45
     WHEN cremi.NCapDes BETWEEN 44000 AND 44999 THEN 46
     WHEN cremi.NCapDes BETWEEN 45000 AND 45999 THEN 47
     WHEN cremi.NCapDes BETWEEN 46000 AND 46999 THEN 48
     WHEN cremi.NCapDes BETWEEN 47000 AND 47999 THEN 49
     WHEN cremi.NCapDes BETWEEN 48000 AND 48999 THEN 50
     WHEN cremi.NCapDes BETWEEN 49000 AND 49999 THEN 51
     ELSE 101
     END;
";
//--------------------------------

// echo json_encode(['status' => 0, 'mensaje' => $strquery]);
//     return; 

$resultado = mysqli_query($conexion, $strquery);

if($resultado) {
    // variable global
    $no_creditos_global = 0;
    $mora_global = 0;
    $total_kap = 0;
    $total_mora = 0;
    $total_Cmora=0;

    while($fila = mysqli_fetch_assoc($resultado)) {
        $no_creditos_global += $fila['cantidad_registros'];
        $mora_global += $fila['suma_mora'];
        $total_kap += $fila['cantidad_Ncapdes'];
        $total_mora  += $fila['suma_mora'];
        $total_Cmora  += $fila['cantidad_mora'];
    }
} else {
    echo json_encode(['status' => 0, 'mensaje' => $conexion]);
}



$query = mysqli_query($conexion, $strquery);
$aux = mysqli_error($conexion);
if ($aux) {
    echo json_encode(['status' => 0, 'mensaje' => $aux]);
    return;
}

$data[] = [];
$j = 0;
while ($fil = mysqli_fetch_array($query)) {
    $data[$j] = $fil;
    $j++;
}
if ($j == 0) {
    echo json_encode(['status' => 0, 'mensaje' => 'No hay datos']);
    return;
}
//----------------------
/* $data3 = mysqli_query($conexion, $consulta2);
mysqli_next_result($conexion);
 */
mysqli_next_result($conexion);
$queryins = mysqli_query($conexion, "SELECT * FROM clhpzzvb_bd_general_coopera.info_coperativa ins
INNER JOIN tb_agencia ag ON ag.id_institucion=ins.id_cop where ag.id_agencia=" . $_SESSION['id_agencia']);

/* $aux = mysqli_error($conexion);
if ($aux) {
    echo json_encode(['status' => 0, 'mensaje' => $aux]);
    return;
} */
$info[] = [];
$j = 0;
while ($fil = mysqli_fetch_array($queryins)) {
    $info[$j] = $fil;
    $j++;
}

if ($j == 0) {
    echo json_encode(['status' => 0, 'mensaje' => 'Institucion asignada a la agencia no encontrada']);
    return;
}

switch ($tipo) {
    case 'xlsx';
        printxls($data, $titlereport, $archivo[0]);
        break;
    case 'pdf':
        printpdf($data, [$titlereport], $info,  $no_creditos_global,$mora_global,$total_kap,$total_mora,$total_Cmora );
        break;
}

//funcion para generar pdf
function printpdf($registro, $datos, $info, $no_creditos_global,$mora_global,$total_kap,$total_mora,$total_Cmora )
{
    /*     $oficina = "Coban";
    $institucion = "Cooperativa Integral De Ahorro y credito Imperial";
    $direccionins = "Canton vipila zona 1";
    $emailins = "fape@gmail.com";
    $telefonosins = "502 43987876";
    $nitins = "1323244234";
    $rutalogomicro = "../../../../includes/img/logomicro.png";
    $rutalogoins = "../../../../includes/img/fape.jpeg"; */


    $oficina = utf8_decode($info[0]["nom_agencia"]);
    $institucion = utf8_decode($info[0]["nomb_comple"]);
    $direccionins = utf8_decode($info[0]["muni_lug"]);
    $emailins = $info[0]["emai"];
    $telefonosins = $info[0]["tel_1"] . '   ' . $info[0]["tel_2"];;
    $nitins = $info[0]["nit"];
    $rutalogomicro = "../../../../includes/img/logomicro.png";
    $rutalogoins = "../../../.." . $info[0]["log_img"];

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

        public function __construct($institucion, $pathlogo, $pathlogoins, $oficina, $direccion, $email, $telefono, $nit, $datos, $no_creditos_global)
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
            $this->DefOrientation = 'L';
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

            $this->SetFont($fuente, 'B', 10);
            //TITULO DE REPORTE
            $this->SetFillColor(204, 229, 255);
            $this->Cell(0, 5, 'CLASIFICACION POR RANGO DE MONTOS' . $this->datos[0], 0, 1, 'C', true);
            $this->Ln(2);
            //Color de encabezado de lista
            $this->SetFillColor(555, 255, 204);
            //TITULOS DE ENCABEZADO DE TABLA
            $this->SetFont($fuente, 'B', 7);
            $ancho_linea = 20;
            $this->Cell($ancho_linea * 6 + 15, 5, ' ', '', 0, 'L');
            $this->Cell($ancho_linea * 3 - 2, 5, ' ',0, 1, 'C');

            $this->Cell($ancho_linea*2, 5, ' ', 'B', 0, 'L');
            $this->Cell($ancho_linea * 2 , 5, 'RANGO', 'B', 0, 'L');

            $this->Cell($ancho_linea+10, 5, 'NO. DE CREDITOS', 'B', 0, 'C');
            $this->Cell($ancho_linea, 5, 'SALDO CAPITAL', 'B', 0, 'C'); //
            $this->Cell($ancho_linea, 5, 'PORCENTAJE', 'B', 0, 'R');
            $this->Cell($ancho_linea*2-10, 5, 'CREDITOS MOROSOS', 'B', 0, 'R');
            $this->Cell($ancho_linea*2 -5 , 5, 'SALDO CAPITAL EN MORA', 'B', 0, 'R');
            $this->Cell($ancho_linea , 5, 'PORCENTAJE', 'B', 0, 'R');
            $this->Cell($ancho_linea+10, 5, ' ', 'B', 0, 'L');
            $this->Cell($ancho_linea / 2, 5, ' ', 0, 1, 'R'); //
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
    $pdf = new PDF($institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins, $datos,  $no_creditos_global);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $fuente = "Courier";
    $tamanio_linea = 3;
    $ancho_linea2 = 20;
    $pdf->SetFont($fuente, '', 7);
    $aux = 0;
    $auxgrupo = 0;
    $fila = 0;
    while ($fila < count($registro)) {


        $nombre = utf8_decode($registro[$fila]["rango"]);  
        $cantidad = $registro[$fila]["cantidad_registros"];  
        $cantidad_Ncapdes = $registro[$fila]["cantidad_Ncapdes"];  
        $cantidad_Mora = $registro[$fila]["cantidad_mora"];  
        $cantidad_Mora_kap = $registro[$fila]["suma_mora"];  

        //TITULO GRUPO
        $pdf->CellFit($ancho_linea2*2, $tamanio_linea + 1, ' ', '', 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2 * 2 , $tamanio_linea + 1, strtoupper($nombre), '', 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2+5, $tamanio_linea + 1, number_format($cantidad, 0, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2+5, $tamanio_linea + 1, number_format($cantidad_Ncapdes, 0, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $porcentaje_kap=($cantidad/$no_creditos_global)*100;
        $pdf->CellFit($ancho_linea2+5, $tamanio_linea + 1, number_format($porcentaje_kap, 2, '.', ',') . ' %', 'R', 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2+5, $tamanio_linea + 1, number_format($cantidad_Mora, 0, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2+15, $tamanio_linea + 1, number_format($cantidad_Mora_kap, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);

        $porcentaje_mora = ($cantidad_Mora_kap/$mora_global)*100;
        $pdf->CellFit($ancho_linea2+5, $tamanio_linea + 1, number_format($porcentaje_mora, 2, '.', ',') . ' %', 0, 0, 'C', 0, '', 1, 0);
        $pdf->Ln(4);
        $fila++;

    }
    $pdf->Ln(2);
    $pdf->Cell($ancho_linea2*2, $tamanio_linea, ' ', 'T', 0, 'L');
    $pdf->Cell($ancho_linea2 *2, $tamanio_linea , 'Numero de frecuencias : ' . $fila, 'T', 0, 'L', 0, '', 1, 0);
    $pdf->Cell($ancho_linea2+5, $tamanio_linea, $no_creditos_global, 'T', 0, 'R');
    $pdf->CellFit($ancho_linea2+8, $tamanio_linea , number_format($total_kap, 2, '.', ',') , 'T', 0, 'C', 0, '', 1, 0);
    $pdf->Cell($ancho_linea2-5, $tamanio_linea, ' ', 'T', 0, 'R');
    $pdf->Cell($ancho_linea2+12, $tamanio_linea, $total_Cmora, 'T', 0, 'R');

    $pdf->Cell($ancho_linea2+15, $tamanio_linea, $mora_global, 'T', 0, 'R');
    $pdf->Cell($ancho_linea2*2+10, $tamanio_linea, ' ', 'T', 0, 'R');


    /*FIN PRODUCTOS */
    ob_start();
    $pdf->Output();
    $pdfData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "Cartera General",
        'tipo' => "pdf",
        'data' => "data:application/pdf;base64," . base64_encode($pdfData)
    );
    echo json_encode($opResult);
}
//FUNCIONES PARA DATOS DE RESUMEN
function resumen($clasdias, $column, $con1, $con2)
{
    $keys = array_keys(array_filter($clasdias[$column], function ($var) use ($con1, $con2) {
        return ($var >= $con1 && $var <= $con2);
    }));
    $fila = 0;
    $sum1 = 0;
    $sum2 = 0;
    while ($fila < count($keys)) {
        $f = $keys[$fila];
        $sum1 += ($clasdias["salcapital"][$f]);
        $sum2 += ($clasdias["capmora"][$f]);
        $fila++;
    }
    return [$sum1, $sum2, $fila];
}

//funcion para generar archivo excel
function printxls($registro, $titlereport, $usuario)
{
    require '../../../../vendor/autoload.php';

    $hoy = date("Y-m-d H:i:s");

    $fuente_encabezado = "Arial";
    $fuente = "Courier";
    $tamanioFecha = 9;
    // $tamanioEncabezado = 14;
    $tamanioTabla = 11;

    $excel = new Spreadsheet();
    $activa = $excel->getActiveSheet();
    $activa->setTitle("CarteraGeneral");
    $activa->getColumnDimension("A")->setWidth(20);
    $activa->getColumnDimension("B")->setWidth(20);
    $activa->getColumnDimension("C")->setWidth(5);
    $activa->getColumnDimension("D")->setWidth(15);
    $activa->getColumnDimension("E")->setWidth(25);
    $activa->getColumnDimension("F")->setWidth(15);
    $activa->getColumnDimension("G")->setWidth(15);
    $activa->getColumnDimension("H")->setWidth(15);


    //insertarmos la fecha y usuario
    $activa->setCellValue("A1", $hoy);
    $activa->setCellValue("A2", $usuario);

    //hacer pequeño las letras de la fecha, definir arial como tipo de letra
    $activa->getStyle("A1:X1")->getFont()->setSize($tamanioFecha)->setName($fuente_encabezado);
    $activa->getStyle("A2:X2")->getFont()->setSize($tamanioFecha)->setName($fuente_encabezado);
    //centrar el texto de la fecha
    $activa->getStyle("A1:X1")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $activa->getStyle("A2:X2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //hacer pequeño las letras del encabezado de titulo
    $activa->getStyle("A4:X4")->getFont()->setSize($tamanioTabla)->setName($fuente);
    $activa->getStyle("A5:X5")->getFont()->setSize($tamanioTabla)->setName($fuente);
    //centrar los encabezado de la tabla
    $activa->getStyle("A4:X4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $activa->getStyle("A5:X5")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $activa->setCellValue("A4", "REPORTE");
    $activa->setCellValue("A5", strtoupper("CARTERA GENERAL " . $titlereport));

    //TITULO DE RECARGOS


    # Escribir encabezado de la tabla
    $encabezado_tabla = ["CRÉDITO", "FONDO", "GENERO", "FECHA DE NACIMIENTO", "NOMBRE DEL CLIENTE", "DIRECCION", "TEL1", "TEL2", "OTORGAMIENTO", "VENCIMIENTO", "MONTO OTORGADO", "TOTAL INTERES A PAGAR", "SALDO CAPITAL", "SALDO INTERES", "SALDO MORA", "CAPITAL PAGADO", "INTERES PAGADO", "MORA PAGADO", "OTROS", "DIAS DE ATRASO", "SALDO CAP MAS INTERES", "MORA CAPITAL", "TASA INTERES", "TASA MORA", "PRODUCTO", "AGENCIA", "ASESOR", "TIPO CREDITO", "GRUPO", "ESTADO", "DESTINO", "DIA PAGO", "FRECUENCIA", "NO CUOTAS"];
    # El último argumento es por defecto A1 pero lo pongo para que se explique mejor
    $activa->fromArray($encabezado_tabla, null, 'A8')->getStyle('A8:X8')->getFont()->setName($fuente)->setBold(true);

    //combinacion de celdas
    $activa->mergeCells('A1:X1');
    $activa->mergeCells('A2:X2');
    $activa->mergeCells('A4:X4');
    $activa->mergeCells('A5:X5');
    $activa->mergeCells('M7:O7');

    $fila = 0;
    $i = 9;
    while ($fila < count($registro)) {
      

        //SALDO DE CAPITAL A LA FECHA
        $salcap = ($monto - $cappag);
        $salcap = ($salcap > 0) ? $salcap : 0;

        //SALDO DE INTERES A LA FECHA
        $salint = ($intcal - $intpag);
        $salint = ($salint > 0) ? $salint : 0;

        //CAPITAL EN MORA A LA FECHA
        $capmora = $capcalafec - $cappag;
        $capmora = ($capmora > 0) ? $capmora : 0;

        $registro[$fila]["salcapital"] = $salcap;
        $registro[$fila]["salintere"] = $salint;
        $registro[$fila]["capmora"] = $capmora;


        $activa->setCellValueExplicit('A' . $i, $cuenta, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('B' . $i, $nombrefondos);
        $activa->setCellValue('C' . $i, strtoupper($genero));
        $activa->setCellValue('D' . $i, $date_birth);
        $activa->setCellValue('E' . $i, strtoupper($nombre));
        $activa->setCellValue('F' . $i, $direccion);
        $activa->setCellValue('G' . $i, $tel1);
        $activa->setCellValue('H' . $i, $tel2);
        $activa->setCellValue('I' . $i, $fechades);
        $activa->setCellValue('J' . $i, $fechaven);
        $activa->setCellValueExplicit('K' . $i, $monto, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValueExplicit('L' . $i, $intcal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValueExplicit('M' . $i, $salcap, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValueExplicit('N' . $i, $salint, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValueExplicit('O' . $i, 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValueExplicit('P' . $i, $cappag, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValueExplicit('Q' . $i, $intpag, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValueExplicit('R' . $i, $morpag, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValueExplicit('S' . $i, $otrpag, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValue('T' . $i, $diasatr, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValue('U' . $i, ($salcap + $salint), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValue('V' . $i, $capmora, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValue('W' . $i, $tasa, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValue('X' . $i, $tasamora, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValue('Y' . $i, strtoupper($nameproducto), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('Z' . $i, $CODAgencia, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('AA' . $i, strtoupper($analista), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('AB' . $i, $tipoenti, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('AC' . $i, $nomgrupo, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('AD' . $i, $estado, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('AE' . $i, $destino, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('AF' . $i, $diapago, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('AG' . $i, $frec, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('AH' . $i, $ncuotas, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        $activa->getStyle("A" . $i . ":AH" . $i)->getFont()->setName($fuente);

        $fila++;
        $i++;
    }
    //total de registros
    $sum_monto = array_sum(array_column($registro, "NCapDes"));
    $sum_intcal = array_sum(array_column($registro, "intcal"));
    $sum_cappag = array_sum(array_column($registro, "cappag"));
    $sum_intpag = array_sum(array_column($registro, "intpag"));
    $sum_morpag = array_sum(array_column($registro, "morpag"));
    $sum_salcap = array_sum(array_column($registro, "salcapital"));
    $sum_salint = array_sum(array_column($registro, "salintere"));
    $sum_capmora = array_sum(array_column($registro, "capmora"));
    $sum_otrpag = array_sum(array_column($registro, "otrpag"));
    $sum_capmora = array_sum(array_column($registro, "capmora"));
    $sum_tasa = array_sum(array_column($registro, "tasa"));
    $sum_tasamora = array_sum(array_column($registro, "tasamora"));

    $activa->getStyle("A" . $i . ":AF" . $i)->getFont()->setSize($tamanioTabla)->setName($fuente)->setBold(true);
    $activa->setCellValueExplicit('A' . $i, "Número de créditos: " . $i, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $activa->mergeCells("A" . $i . ":G" . $i);

    $activa->setCellValueExplicit('K' . $i, $sum_monto, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValueExplicit('L' . $i, $sum_intcal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValueExplicit('M' . $i, $sum_salcap, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValueExplicit('N' . $i, $sum_salint, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValueExplicit('O' . $i, 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValueExplicit('P' . $i, $sum_cappag, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValueExplicit('Q' . $i, $sum_intpag, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValueExplicit('R' . $i, $sum_morpag, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValueExplicit('S' . $i, $sum_otrpag, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

    $activa->setCellValue('T' . $i, ($sum_salcap + $sum_salint), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValue('U' . $i, $sum_capmora, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValue('V' . $i, $sum_tasa, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValue('W' . $i, $sum_tasamora, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

    $activa->getStyle("A" . $i . ":AF" . $i)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');

    $columnas = range('A', 'AF');
    foreach ($columnas as $columna) {
        $activa->getColumnDimension($columna)->setAutoSize(TRUE);
    }

    ob_start();
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel, 'Xlsx');
    $writer->save("php://output");
    $xlsData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "Cartera general " . $titlereport,
        'tipo' => "vnd.ms-excel",
        'data' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
    );
    echo json_encode($opResult);
    exit;
}