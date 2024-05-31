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

$strquery = "SELECT 
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
cremi.DFecDsbls,
cremi.MonSug,
cremi.NCapDes,
IFNULL((SELECT dfecven FROM Cre_ppg WHERE ccodcta=cremi.CCODCTA ORDER BY dfecven DESC LIMIT 1),0) AS fechaven,
IFNULL((SELECT SUM(nintere) FROM Cre_ppg WHERE ccodcta=cremi.CCODCTA),0) AS intcal,
IFNULL((SELECT dfecven FROM Cre_ppg WHERE dfecven<='$filtrofecha' AND ccodcta=cremi.CCODCTA ORDER BY dfecven DESC LIMIT 1),0) AS fechacalult,
IFNULL((SELECT SUM(ncapita) FROM Cre_ppg WHERE dfecven<='$filtrofecha' AND ccodcta=cremi.CCODCTA),0) AS capcalafec,
IFNULL((SELECT SUM(nintere) FROM Cre_ppg WHERE dfecven<='$filtrofecha' AND ccodcta=cremi.CCODCTA),0) AS intcalafec,
IFNULL((SELECT SUM(KP) FROM CREDKAR WHERE dfecpro<='$filtrofecha' AND ccodcta=cremi.CCODCTA AND cestado!='X' AND ctippag='P'),0) AS cappag,
IFNULL((SELECT SUM(interes) FROM CREDKAR WHERE dfecpro<='$filtrofecha' AND ccodcta=cremi.CCODCTA AND cestado!='X' AND ctippag='P'),0) AS intpag,
IFNULL((SELECT SUM(MORA) FROM CREDKAR WHERE dfecpro<='$filtrofecha' AND ccodcta=cremi.CCODCTA AND cestado!='X' AND ctippag='P'),0) AS morpag,
IFNULL((SELECT SUM(AHOPRG)+SUM(OTR) FROM CREDKAR WHERE dfecpro<='$filtrofecha' AND ccodcta=cremi.CCODCTA AND cestado!='X' AND ctippag='P'),0) AS otrpag,
cre_dias_atraso('$filtrofecha', cremi.CCODCTA) AS todos,
CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(cre_dias_atraso('$filtrofecha',cremi.CCODCTA),'#',1),'_',1) AS SIGNED) AS atraso,
IFNULL((SELECT NombreGrupo from tb_grupo where id_grupos=cremi.CCodGrupo),' ') AS NombreGrupo, 
cremi.TipoEnti,
IFNULL((cremi.CCodGrupo),' ') AS CCodGrupo, 
cremi.Cestado, 
cp.descripcion AS descripcion_periodo,
COUNT(cp.descripcion) AS cantidad_registros_periodo,
COUNT(cremi.NCapDes) AS cantidad_registros_NCapDes,
SUM(crepg.nmorpag) AS suma_mora,
SUM(crepg.nmorpag > 1) AS cantidad_mora,
SUM(cremi.NCapDes) AS suma_NCapDes,
(SELECT SUM(cremi2.NCapDes) FROM cremcre_meta cremi2 WHERE cremi2.DFecDsbls <= '$filtrofecha') AS total_suma_NCapDes
FROM        
cremcre_meta cremi 
INNER JOIN  
tb_cliente cli ON cli.idcod_cliente=cremi.CodCli
INNER JOIN  
cre_productos prod ON prod.id=cremi.CCODPRD
INNER JOIN  
ctb_fuente_fondos ffon ON ffon.id=prod.id_fondo 
INNER JOIN  
tb_usuario usu ON usu.id_usu=cremi.CodAnal
INNER JOIN 
clhpzzvb_bd_general_coopera.tb_cre_periodos cp ON cod_msplus = cremi.NtipPerC
INNER JOIN
     Cre_ppg crepg ON cremi.CCODCTA = crepg.CCODCTA
WHERE 
(".$status.") AND cremi.DFecDsbls<='$filtrofecha'" . $filfondo . $filagencia . " 
GROUP BY
NtipPerC, cp.descripcion 
ORDER BY 
prod.id_fondo,
cremi.TipoEnti,
cremi.CCodGrupo,
prod.id,
cremi.DFecDsbls;
";

$resultado = mysqli_query($conexion, $strquery);

// echo json_encode(['status' => 0, 'mensaje' => $strquery]);
//     return; 
$total_cantidad = 0;
$total_cantidad_mora = 0;

while($fila = mysqli_fetch_assoc($resultado)) {
    $total_cantidad += $fila['cantidad_registros_NCapDes'];
    $total_cantidad_mora += $fila['cantidad_mora'];
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
        printpdf($data, [$titlereport], $info, $total_cantidad,$total_cantidad_mora  );
        break;
}

//funcion para generar pdf
function printpdf($registro, $datos, $info,$total_cantidad,$total_cantidad_mora )
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

        public function __construct($institucion, $pathlogo, $pathlogoins, $oficina, $direccion, $email, $telefono, $nit, $datos,$total_cantidad )
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
            $this->Cell(0, 5, 'CLASIFICACION POR FRECUENCIA DE PAGO DE CREDITOS' . $this->datos[0], 0, 1, 'C', true);
            $this->Ln(2);
            //Color de encabezado de lista
            $this->SetFillColor(555, 255, 204);
            //TITULOS DE ENCABEZADO DE TABLA
            $this->SetFont($fuente, 'B', 7);
            $ancho_linea = 20;
            $this->Cell($ancho_linea + 45, 5, ' ', '', 0, 'L');
        //    $this->Cell($ancho_linea * 3 - 2, 5, 'RECUPERACIONES', 'TRL', 1, 'C');

            $this->Cell($ancho_linea * 3 - 2, 5, ' ', 0, 1, 'C');
            $this->Cell($ancho_linea, 5, ' ', 'B', 0, 'C');
            $this->Cell($ancho_linea * 2 + 18, 5, 'FRECUENCIA DE PAGO', 'B', 0, 'L');    
            $this->Cell($ancho_linea, 5, 'NO. CREDITOS', 'B-L-R', 0, 'C'); //
            $this->Cell($ancho_linea , 5, 'PORCENTAJE', 'B-L-R', 0, 'R');
            $this->Cell($ancho_linea+15, 5, 'SALDO CAPITAL', 'B-L-R', 0, 'C'); //
            $this->Cell($ancho_linea+15, 5, 'No. CREDITOS MOROSOS', 'B-L-R', 0, 'C'); //
            $this->Cell($ancho_linea+15, 5, 'PORCENTAJE', 'B-L-R', 0, 'C');
            $this->Cell($ancho_linea +15+ 5, 5, 'Saldo en Mora', 'BR', 0, 'C');
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
    $pdf = new PDF($institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins, $datos,$total_cantidad,$total_cantidad_mora );
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
        $frecuencia = utf8_decode($registro[$fila]["descripcion_periodo"]);
        $fechaven = $registro[$fila]["fechaven"];
        $fechaven = ($fechaven != "0") ? date("d-m-Y", strtotime($fechaven)) : "-";
        $monto = $registro[$fila]["suma_NCapDes"];
        $capcalafec = $registro[$fila]["capcalafec"];
        $cantidad_monto = $registro[$fila]["cantidad_registros_NCapDes"];
        $diasatr = $registro[$fila]["atraso"];
        $cappag = $registro[$fila]["cappag"];
        $intpag = $registro[$fila]["intpag"];
        $idfondos = $registro[$fila]["id_fondos"];
        $nombrefondos = $registro[$fila]["nombre_fondo"];
        $cantidad_mora = $registro[$fila]["cantidad_mora"];
        $suma_mora = $registro[$fila]["suma_mora"];
      


    
        $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', '', 0, 'L', 0, '', 1, 0); 
        $pdf->CellFit($ancho_linea2 * 2 + 19, $tamanio_linea + 1, strtoupper($frecuencia), '', 0, 'T', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $cantidad_monto, 0, 0, 'C', 0, '', 1, 0);//NO. DE CREDITOS
        $porcentaje_cantidad =  ($cantidad_monto/ $total_cantidad)*100;
        $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($porcentaje_cantidad, 2, '.', ',') . ' %', 0, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2+15, $tamanio_linea + 1, number_format($monto, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);//KP
        $pdf->CellFit($ancho_linea2+15, $tamanio_linea + 1, $cantidad_mora, 0, 0, 'R', 0, '', 1, 0);//NO. DE CREDITOS con mora 
        $porcentaje_cantidad_mora = ($cantidad_mora/$total_cantidad_mora)*100;
        $pdf->CellFit($ancho_linea2+10, $tamanio_linea + 1, number_format($porcentaje_cantidad_mora, 2, '.', ',') . ' %', 0, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2+15, $tamanio_linea + 1, $suma_mora, 0, 0, 'R', 0, '', 1, 0);

    
        $pdf->CellFit($ancho_linea2 / 2, $tamanio_linea + 1, ' ', '', 1, 'R', 0, '', 1, 0);
        $fila++;
    }

    $pdf->Ln(2);
    $pdf->SetFont($fuente, 'B', 7);
    $no_creditos = array_sum(array_column($registro, "cantidad_registros_NCapDes"));
    $total_saldo = array_sum(array_column($registro, "suma_NCapDes"));

    

    

    $sum_cappag = array_sum(array_column($registro, "cappag"));
    $sum_intpag = array_sum(array_column($registro, "intpag"));
    $sum_morpag = array_sum(array_column($registro, "morpag"));
    $sum_salcap = array_sum(array_column($registro, "salcapital"));
    $sum_salint = array_sum(array_column($registro, "salintere"));
    $sum_capmora = array_sum(array_column($registro, "capmora"));

    $pdf->Ln(2);
    $pdf->CellFit($ancho_linea2*4-4 , $tamanio_linea + 1, ' ' , 'T', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2-2, $tamanio_linea + 1, number_format($no_creditos, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2*2 , $tamanio_linea + 1, ' ' . ' ', 'T', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($total_saldo, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);

    
    $pdf->CellFit($ancho_linea2*2+13 , $tamanio_linea + 1, ' ' . ' ', 'T', 0, 'C', 0, '', 1, 0);

    
    $pdf->CellFit($ancho_linea2 , $tamanio_linea + 1, ' ' , 'T', 0, 'C', 0, '', 1, 0);
    //RESUMEN DIAS
    //0, 'C', 0, '', 1, 0
    $pdf->Ln(4);
    $pdf->SetFont($fuente, 'B', 9);



   
    //FIN RESUMEN DIAS
    // $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $cuenta, '', 0, 'L', 0, '', 1, 0);
    /* SALDO INTERESES */
    $pdf->Ln(6);


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
    $activa->setCellValue("M7", "RECUPERACIONES");

    # Escribir encabezado de la tabla
    $encabezado_tabla = ["CRÉDITO", "FONDO", "GENERO", "FECHA DE NACIMIENTO", "NOMBRE DEL CLIENTE", "OTORGAMIENTO", "VENCIMIENTO", "MONTO OTORGADO", "TOTAL INTERES A PAGAR", "SALDO CAPITAL", "SALDO INTERES", "SALDO MORA", "CAPITAL PAGADO", "INTERES PAGADO", "MORA PAGADO", "OTROS", "DIAS DE ATRASO", "SALDO CAP MAS INTERES", "MORA CAPITAL", "TASA INTERES", "TASA MORA", "PRODUCTO", "AGENCIA", "ASESOR", "TIPO CREDITO", "GRUPO", "ESTADO"];
    # El último argumento es por defecto A1 pero lo pongo para que se explique mejor
    $activa->fromArray($encabezado_tabla, null, 'A8')->getStyle('A8:X8')->getFont()->setName($fuente)->setBold(true);

    //combinacion de celdas
    $activa->mergeCells('A1:X1');
    $activa->mergeCells('A2:X2');
    $activa->mergeCells('A4:X4');
    $activa->mergeCells('A5:X5');
    $activa->mergeCells('M7:O7');

    // $activa->setCellValue('A1', 'CREDITO');
    // $activa->setCellValue('B1', 'FONDO');
    // $activa->setCellValue('C1', 'GENERO');
    // $activa->setCellValue('D1', 'FECHA DE NACIMIENTO');
    // $activa->setCellValue('E1', 'NOMBRE DEL CLIENTE');
    // $activa->setCellValue('F1', 'OTORGAMIENTO');
    // $activa->setCellValue('G1', 'VENCIMIENTO');
    // $activa->setCellValue('H1', 'MONTO OTORGADO');
    // $activa->setCellValue('I1', 'TOTAL INTERES A PAGAR');
    // $activa->setCellValue('J1', 'SALDO CAPITAL');
    // $activa->setCellValue('K1', 'SALDO INTERES');
    // $activa->setCellValue('L1', 'SALDO MORA');
    // $activa->setCellValue('M1', 'CAPITAL PAGADO');
    // $activa->setCellValue('N1', 'INTERES PAGADO');
    // $activa->setCellValue('O1', 'MORA PAGADO');
    // $activa->setCellValue('P1', 'OTROS');
    // $activa->setCellValue('Q1', 'DIAS DE ATRASO');
    // $activa->setCellValue('R1', 'MORA CAPITAL');
    // $activa->setCellValue('S1', 'TASA INTERES');
    // $activa->setCellValue('T1', 'TASA MORA');
    // $activa->setCellValue('U1', 'PRODUCTO');
    // $activa->setCellValue('V1', 'AGENCIA');
    // $activa->setCellValue('W1', 'ASESOR');


    $fila = 0;
    $i = 9;
    while ($fila < count($registro)) {
        $cuenta = $registro[$fila]["CCODCTA"];
        $nombre =  $registro[$fila]["short_name"];
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
        $diasatr = $registro[$fila]["atraso"];
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
        $estado = ($estado == "F") ? "VIGENTE" : "CANCELADO";

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
        $activa->setCellValue('F' . $i, $fechades);
        $activa->setCellValue('G' . $i, $fechaven);
        $activa->setCellValueExplicit('H' . $i, $monto, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValueExplicit('I' . $i, $intcal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValueExplicit('J' . $i, $salcap, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValueExplicit('K' . $i, $salint, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValueExplicit('L' . $i, 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValueExplicit('M' . $i, $cappag, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValueExplicit('N' . $i, $intpag, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValueExplicit('O' . $i, $morpag, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValueExplicit('P' . $i, $otrpag, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValue('Q' . $i, $diasatr, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValue('R' . $i, ($salcap + $salint), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValue('S' . $i, $capmora, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValue('T' . $i, $tasa, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValue('U' . $i, $tasamora, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $activa->setCellValue('V' . $i, strtoupper($nameproducto), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('W' . $i, $CODAgencia, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('X' . $i, strtoupper($analista), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('Y' . $i, $tipoenti, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('Z' . $i, $nomgrupo, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $activa->setCellValue('AA' . $i, $estado, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        $activa->getStyle("A" . $i . ":X" . $i)->getFont()->setName($fuente);

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

    $activa->getStyle("A" . $i . ":X" . $i)->getFont()->setSize($tamanioTabla)->setName($fuente)->setBold(true);
    $activa->setCellValueExplicit('A' . $i, "Número de créditos: " . $i, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $activa->mergeCells("A" . $i . ":G" . $i);

    $activa->setCellValueExplicit('H' . $i, $sum_monto, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValueExplicit('I' . $i, $sum_intcal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValueExplicit('J' . $i, $sum_salcap, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValueExplicit('K' . $i, $sum_salint, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValueExplicit('L' . $i, 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValueExplicit('M' . $i, $sum_cappag, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValueExplicit('N' . $i, $sum_intpag, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValueExplicit('O' . $i, $sum_morpag, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValueExplicit('P' . $i, $sum_otrpag, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

    $activa->setCellValue('R' . $i, ($sum_salcap + $sum_salint), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValue('S' . $i, $sum_capmora, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValue('T' . $i, $sum_tasa, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $activa->setCellValue('U' . $i, $sum_tasamora, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

    $activa->getStyle("A" . $i . ":X" . $i)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');

    $activa->getColumnDimension('A')->setAutoSize(TRUE);
    $activa->getColumnDimension('B')->setAutoSize(TRUE);
    $activa->getColumnDimension('C')->setAutoSize(TRUE);
    $activa->getColumnDimension('D')->setAutoSize(TRUE);
    $activa->getColumnDimension('E')->setAutoSize(TRUE);
    $activa->getColumnDimension('F')->setAutoSize(TRUE);
    $activa->getColumnDimension('G')->setAutoSize(TRUE);
    $activa->getColumnDimension('H')->setAutoSize(TRUE);
    $activa->getColumnDimension('I')->setAutoSize(TRUE);
    $activa->getColumnDimension('J')->setAutoSize(TRUE);
    $activa->getColumnDimension('K')->setAutoSize(TRUE);
    $activa->getColumnDimension('L')->setAutoSize(TRUE);
    $activa->getColumnDimension('M')->setAutoSize(TRUE);
    $activa->getColumnDimension('N')->setAutoSize(TRUE);
    $activa->getColumnDimension('O')->setAutoSize(TRUE);
    $activa->getColumnDimension('P')->setAutoSize(TRUE);
    $activa->getColumnDimension('Q')->setAutoSize(TRUE);
    $activa->getColumnDimension('R')->setAutoSize(TRUE);
    $activa->getColumnDimension('S')->setAutoSize(TRUE);
    $activa->getColumnDimension('T')->setAutoSize(TRUE);
    $activa->getColumnDimension('U')->setAutoSize(TRUE);
    $activa->getColumnDimension('V')->setAutoSize(TRUE);
    $activa->getColumnDimension('W')->setAutoSize(TRUE);
    $activa->getColumnDimension('X')->setAutoSize(TRUE);
    $activa->getColumnDimension('AA')->setAutoSize(TRUE);

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
