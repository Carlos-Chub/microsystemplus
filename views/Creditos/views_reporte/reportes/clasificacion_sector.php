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
$strquery=" SELECT 
    cremi.CODAgencia, 
    CONCAT(usu.nombre, ' ', usu.apellido) AS analista, 
    cremi.CCODCTA, 
    cremi.NtipPerC, 
    prod.id_fondo AS id_fondos, 
    ffon.descripcion AS nombre_fondo, 
    prod.id AS id_producto, 
    prod.descripcion AS nombre_producto, 
    prod.tasa_interes AS tasa, 
    prod.porcentaje_mora AS tasamora, 
    cli.short_name, 
    cli.date_birth, 
    cli.genero, 
    cli.estado_civil,
    cli.Direccion AS direccion,
    cli.tel_no1 AS tel1,
    cli.tel_no2 AS tel2, 
    cremi.DFecDsbls, 
    cremi.MonSug, 
    cremi.NCapDes, 
    SUM(cremi.NCapDes) AS cantidad_Ncapdes,
    SUM(crepg.nmorpag) AS suma_mora,
    SUM(crepg.nmorpag > 1) AS cantidad_mora,
    cremi.DfecPago AS fecpago, 
    IFNULL(ppg.dfecven, 0) AS fechaven, 
    IFNULL(ppg.sum_nintere, 0) AS intcal, 
    IFNULL(ppg_ult.dfecven, 0) AS fechacalult, 
    IFNULL(ppg_ult.sum_ncapita, 0) AS capcalafec, 
    IFNULL(ppg_ult.sum_nintere, 0) AS intcalafec, 
    IFNULL(kar.sum_KP, 0) AS cappag, 
    IFNULL(kar.sum_interes, 0) AS intpag, 
    IFNULL(kar.sum_MORA, 0) AS morpag, 
    IFNULL(kar.sum_AHOPRG_OTR, 0) AS otrpag, 
    IFNULL(grupo.NombreGrupo, ' ') AS NombreGrupo, 
    cremi.TipoEnti, 
    IFNULL(cremi.CCodGrupo, ' ') AS CCodGrupo, 
    cremi.ActoEcono,
    cremi.Cestado,
    ae.Titulo AS Titulo_Actividad,
    COUNT(*) AS cantidad_datos  
FROM 
    cremcre_meta cremi 
INNER JOIN 
    tb_cliente cli ON cli.idcod_cliente = cremi.CodCli 
INNER JOIN 
    cre_productos prod ON prod.id = cremi.CCODPRD 
INNER JOIN 
    ctb_fuente_fondos ffon ON ffon.id = prod.id_fondo 
INNER JOIN 
    tb_usuario usu ON usu.id_usu = cremi.CodAnal 
INNER JOIN
    Cre_ppg crepg ON cremi.CCODCTA = crepg.CCODCTA
LEFT JOIN 
    (SELECT ccodcta, MAX(dfecven) AS dfecven, SUM(nintere) AS sum_nintere FROM Cre_ppg GROUP BY ccodcta) AS ppg ON ppg.ccodcta = cremi.CCODCTA 
LEFT JOIN 
    (SELECT ccodcta, MAX(dfecven) AS dfecven, SUM(ncapita) AS sum_ncapita, SUM(nintere) AS sum_nintere FROM Cre_ppg WHERE dfecven <= '$filtrofecha' GROUP BY ccodcta) AS ppg_ult ON ppg_ult.ccodcta = cremi.CCODCTA 
LEFT JOIN 
    (SELECT ccodcta, SUM(KP) AS sum_KP, SUM(interes) AS sum_interes, SUM(MORA) AS sum_MORA, SUM(AHOPRG) + SUM(OTR) AS sum_AHOPRG_OTR FROM CREDKAR WHERE dfecpro <= '$filtrofecha' AND cestado != 'X' AND ctippag = 'P' GROUP BY ccodcta) AS kar ON kar.ccodcta = cremi.CCODCTA 
LEFT JOIN 
    tb_grupo grupo ON grupo.id_grupos = cremi.CCodGrupo 
LEFT JOIN 
    `clhpzzvb_bd_general_coopera`.tb_ActiEcono ae ON ae.id_ActiEcono=cremi.ActoEcono
    WHERE (".$status.") AND cremi.DFecDsbls <= '$filtrofecha'" . $filfondo . $filagencia . " 
GROUP BY 
    cremi.ActoEcono
ORDER BY 
    prod.id_fondo, 
    cremi.TipoEnti, 
    cremi.CCodGrupo, 
    prod.id, 
    cremi.DFecDsbls; 
";
//--------------------------------

// echo json_encode(['status' => 0, 'mensaje' => $strquery]);
//     return;  

$resultado = mysqli_query($conexion, $strquery);

if($resultado) {
    // variable global
    $no_creditos_global = 0;
    $capital_global = 0;
    $mora_global = 0;
    $total_cremorosos = 0;
    $total_cantcreditos = 0;


    while($fila = mysqli_fetch_assoc($resultado)) {
        $no_creditos_global += $fila['cantidad_datos'];
        $capital_global += $fila['cantidad_Ncapdes'];
        $mora_global += $fila['suma_mora'];  
        $total_cremorosos += $fila['cantidad_mora']; 
        $total_cantcreditos += $fila['cantidad_datos']; 
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
        printpdf($data, [$titlereport], $info, $no_creditos_global,$capital_global,$mora_global,$total_cremorosos, $total_cantcreditos);
        break;
}

//funcion para generar pdf
function printpdf($registro, $datos, $info,$no_creditos_global,$capital_global,$mora_global,$total_cremorosos, $total_cantcreditos)
{
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

        public function __construct($institucion, $pathlogo, $pathlogoins, $oficina, $direccion, $email, $telefono, $nit, $datos)
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
            $this->Cell(0, 5, 'CLASIFICACION DE LOS CREDITOS POR ACTIVIDAD ECONOMICA' . $this->datos[0], 0, 1, 'C', true);
            $this->Ln(2);
            //Color de encabezado de lista
            $this->SetFillColor(555, 255, 204);
            //TITULOS DE ENCABEZADO DE TABLA
            $this->SetFont($fuente, 'B', 7);
            $ancho_linea = 20;
            $this->Cell($ancho_linea-10 * 6 + 15, 5, ' ', '', 0, 'L');
            $this->Cell($ancho_linea * 3 - 2, 5, ' ', 0, 1, 'C');

            $this->Cell($ancho_linea-5, 5, ' ', 'B', 0, 'L');
            $this->Cell($ancho_linea , 5, ' ', 'B', 0, 'L');
            $this->Cell($ancho_linea * 2 + 15, 5, ' ACTIVIDAD ECONOMICA', 'B', 0, 'L');
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
    $pdf = new PDF($institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins, $datos);
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
        $cuenta = $registro[$fila]["CCODCTA"];
        $cantidad_mora = utf8_decode($registro[$fila]["cantidad_mora"]);
        $nombre = utf8_decode($registro[$fila]["Titulo_Actividad"]);
        $sector_cantidad = $registro[$fila]["cantidad_datos"];
        $total_cap = $registro[$fila]["cantidad_Ncapdes"];
        $suma_mora = $registro[$fila]["suma_mora"];
        $genero =  $registro[$fila]["genero"];
        $fechades = date("d-m-Y", strtotime($registro[$fila]["DFecDsbls"]));
        $fechaven = $registro[$fila]["fechaven"];
        $fechaven = ($fechaven != "0") ? date("d-m-Y", strtotime($fechaven)) : "-";
        $monto = $registro[$fila]["NCapDes"];
        $intcal = $registro[$fila]["intcal"];
        $capcalafec = $registro[$fila]["capcalafec"];
        $intcalafec = $registro[$fila]["intcalafec"];
        $cappag = $registro[$fila]["cappag"];
        $intpag = $registro[$fila]["intpag"];
        $morpag = $registro[$fila]["morpag"];
        $idfondos = $registro[$fila]["id_fondos"];
        $nombrefondos = $registro[$fila]["nombre_fondo"];
        $idproducto = $registro[$fila]["id_producto"];
        $nameproducto = $registro[$fila]["nombre_producto"];
        $grupo = $registro[$fila]["NombreGrupo"];
        $codgrupo = ($registro[$fila]["CCodGrupo"] == NULL) ? ' ' : $registro[$fila]["CCodGrupo"];
        $tipoenti = $registro[$fila]["TipoEnti"];

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
        $pdf->CellFit($ancho_linea2, $tamanio_linea + 1,' ', '', 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2-5, $tamanio_linea + 1, ' ', '', 0, 'C', 0, '', 1, 0);//vacio
        $pdf->CellFit($ancho_linea2 * 2 + 19, $tamanio_linea + 1, strtoupper($nombre), '', 0, 'L', 0, '', 1, 0);//SECTOR
        $pdf->CellFit($ancho_linea2 - 2, $tamanio_linea + 1, $sector_cantidad, '', 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2+5, $tamanio_linea + 1, number_format($total_cap, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);//capital total
        $porcentaje_kap= ($total_cap/$capital_global)*100;
        $pdf->CellFit($ancho_linea2+5, $tamanio_linea + 1, number_format($porcentaje_kap, 2, '.', ',') . ' %', 'R', 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2+5, $tamanio_linea + 1, $cantidad_mora, '', 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2+5, $tamanio_linea + 1, number_format($suma_mora, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);//suma total
        $porcentaje_mora= ($suma_mora/ $mora_global)*100;
        $pdf->CellFit($ancho_linea2+10, $tamanio_linea + 1, number_format($porcentaje_mora, 2, '.', ',') . ' %', 0, 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2 / 2, $tamanio_linea + 1, ' ', '', 1, 'R', 0, '', 1, 0);
        $fila++;
    }
    $pdf->Ln(2);
    $pdf->SetFont($fuente, 'B', 7);
    $sum_montos = array_sum(array_column($registro, "NCapDes"));
    $sum_cappag = array_sum(array_column($registro, "cappag"));
    $sum_intpag = array_sum(array_column($registro, "intpag"));
    $sum_morpag = array_sum(array_column($registro, "morpag"));
    $sum_salcap = array_sum(array_column($registro, "salcapital"));
    $sum_salint = array_sum(array_column($registro, "salintere"));
    $sum_capmora = array_sum(array_column($registro, "capmora"));

    $pdf->CellFit($ancho_linea2 *3+10, $tamanio_linea , 'Numero de  ACTIVIDADES ECONOMICAS: ' . $fila, 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2*2-4 , $tamanio_linea , $total_cantcreditos , 'T', 0, 'R', 0, '', 1, 0);

$pdf->CellFit($ancho_linea2 * 2-6, $tamanio_linea, number_format($capital_global, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
$pdf->CellFit($ancho_linea2+10 , $tamanio_linea , ' ' , 'T', 0, 'R', 0, '', 1, 0);
$pdf->CellFit($ancho_linea2-12 , $tamanio_linea , $total_cremorosos , 'T', 0, 'R', 0, '', 1, 0);
$pdf->CellFit($ancho_linea2 * 2-6, $tamanio_linea, number_format($mora_global, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
$pdf->CellFit($ancho_linea2*3 , $tamanio_linea , ' ' , 'T', 0, 'R', 0, '', 1, 0);



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

    //titulo de recargos
    $activa->getStyle("A7:X7")->getFont()->setSize($tamanioTabla)->setName($fuente)->setBold(true);
    $activa->getStyle("A7:X7")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $activa->setCellValue("P7", "RECUPERACIONES");

    # Escribir encabezado de la tabla
    $encabezado_tabla = ["CRÉDITO", "FONDO", "GENERO", "FECHA DE NACIMIENTO", "NOMBRE DEL CLIENTE","DIRECCION","TEL1","TEL2", "OTORGAMIENTO", "VENCIMIENTO", "MONTO OTORGADO", "TOTAL INTERES A PAGAR", "SALDO CAPITAL", "SALDO INTERES", "SALDO MORA", "CAPITAL PAGADO", "INTERES PAGADO", "MORA PAGADO", "OTROS", "DIAS DE ATRASO", "SALDO CAP MAS INTERES", "MORA CAPITAL", "TASA INTERES", "TASA MORA", "PRODUCTO", "AGENCIA", "ASESOR", "TIPO CREDITO", "GRUPO","ESTADO","DESTINO","DIA PAGO"];
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
        $cuenta = $registro[$fila]["CCODCTA"];
        $nombre =  $registro[$fila]["short_name"];
        $direccion =  $registro[$fila]["direccion"];
        $tel1 =  $registro[$fila]["tel1"];
        $tel2 =  $registro[$fila]["tel2"];
        $genero =  $registro[$fila]["genero"];
        $date_birth =  $registro[$fila]["date_birth"];
        $fechades = date("d-m-Y", strtotime($registro[$fila]["DFecDsbls"]));
        $fechaven = $registro[$fila]["fechaven"];
        $fechaven = ($fechaven != "0") ? date("d-m-Y", strtotime($fechaven)) : "-";
        $monto = $registro[$fila]["NCapDes"];
        $intcal = $registro[$fila]["intcal"];
        $capcalafec = $registro[$fila]["capcalafec"];
        $intcalafec = $registro[$fila]["intcalafec"];
        $cappag = $registro[$fila]["cappag"];
        $intpag = $registro[$fila]["intpag"];
        $morpag = $registro[$fila]["morpag"];

        $idfondos = $registro[$fila]["id_fondos"];
        $nombrefondos = $registro[$fila]["nombre_fondo"];
        $idproducto = $registro[$fila]["id_producto"];
        $nameproducto = $registro[$fila]["nombre_producto"];
        $analista = $registro[$fila]["analista"];
        $CODAgencia = $registro[$fila]["CODAgencia"];
        $tasa = $registro[$fila]["tasa"];
        $tasamora = $registro[$fila]["tasamora"];
        $otrpag = $registro[$fila]["otrpag"];
        $tipoenti = $registro[$fila]["TipoEnti"];
        $nomgrupo = $registro[$fila]["NombreGrupo"];
        $estado = $registro[$fila]["Cestado"];
        $destino = $registro[$fila]["destino"];
        $diapago =date('d', strtotime($registro[$fila]["fecpago"]));
        $estado=($estado=="F")?"VIGENTE":"CANCELADO";

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

        $activa->getStyle("A" . $i . ":AF" . $i)->getFont()->setName($fuente);

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