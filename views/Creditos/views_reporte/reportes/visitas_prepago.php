<?php
session_start();
include '../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
require '../../../../fpdf/fpdf.php';
require '../../../../vendor/autoload.php';
date_default_timezone_set('America/Guatemala');
$hoy2 = date("Y-m-d H:i:s");
$hoy = date("Y-m-d");

use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Round;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}
//se recibe los datos
$datos = $_POST["datosval"];
$inputs = $datos[0];
$selects = $datos[1];
$radios = $datos[2];
$tipo = $_POST["tipo"];


//SE ORDENA LA CONSULTA
$consulta = "SELECT 
    ag.cod_agenc,
    pg.dfecven AS fecha, 
    cm.CCODCTA AS cuenta, 
    cl.idcod_cliente AS cliente, 
    cl.short_name AS nombre, 
    ((cm.Monsug) - (SELECT IFNULL(SUM(KP),0) FROM CREDKAR WHERE ccodcta=cm.CCODCTA AND ctippag='P' AND cestado!='X')) AS saldo,
    pg.nmorpag AS mora, 
    pg.AhoPrgPag AS pag1, 
    pg.OtrosPagosPag AS pag2, 
    (pg.ncappag + pg.nintpag) AS cuota, 
    pg.ncappag AS capital, 
    pg.nintpag AS interes  
FROM cremcre_meta cm
INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
INNER JOIN tb_usuario us ON cm.CodAnal=us.id_usu
INNER JOIN tb_agencia ag ON cm.CODAgencia=ag.cod_agenc
INNER JOIN Cre_ppg pg ON cm.CCODCTA=pg.ccodcta
WHERE cm.Cestado='F' AND pg.cestado='X' 
AND  (pg.dfecven BETWEEN '" . $inputs[0] . "' AND '" . $inputs[1] . "')";
$texto_reporte = "";
$texto_reporte = "REPORTE DE VISITAS PREPAGO ENTRE LA FECHA ".date("d-m-Y", strtotime($inputs[0]))." AL ".date("d-m-Y", strtotime($inputs[1]));
//SE REALIZAR LA VALIDACION DE SI HAY UN CODIGO DE AGENCIA
if ($radios[0] == '2' || $radios[0] == 'F0' ) {
    if ($selects[0] == '0') {
        echo json_encode(['status' => 0, 'mensaje' => 'Debe seleccionar una agencia']);
        return;
    }
    //consultar la agencia
    $agencia = mysqli_query($conexion, "SELECT * FROM tb_agencia WHERE id_agencia='" . $selects[0] . "' ");
    while ($fil = mysqli_fetch_array($agencia)) {
        $id_agencia = $fil["id_agencia"];
        $nom_agencia = strtoupper($fil["nom_agencia"]);
        $cod_agenc = strtoupper($fil["cod_agenc"]);
    }
    $texto_reporte .= " DE LA AGENCIA " . $cod_agenc;
    //realizar una busqueda mediante agencia
    $consulta .= " AND ag.id_agencia='" . $selects[0] . "' ";
}
if ($radios[0] == 3 ||  $radios[0] == 'F0' ) {
    if ($selects[1] == '0' || $selects[1] == 'F0' ) {
        echo json_encode(['status' => 0, 'mensaje' => 'Debe seleccionar un ejecutivo Valido']);
        return;
    }
    //consultar la agencia
    $usuario = mysqli_query($conexion, "SELECT CONCAT(nombre,' ',apellido) AS nombre FROM tb_usuario WHERE id_usu='" . $selects[1] . "' ");
    while ($fil = mysqli_fetch_array($usuario)) {
        $nombre = strtoupper($fil["nombre"]);
    }
    $texto_reporte .= " DEL EJECUTIVO " . $nombre;
    //realizar la busqueda con ejecutivo
    $consulta .= " AND us.id_usu='" . $selects[1] . "' ";
}
//ORDENAR MEDIANTE FECHA
$consulta .= " ORDER BY cl.idcod_cliente, pg.dfecven ASC";

//SE LEEN LOS datos
$datos = mysqli_query($conexion, $consulta);
$data[] = [];
$i = 0;
while ($fila = mysqli_fetch_array($datos)) {
    $data[$i] = $fila;
    $i++;
}
if ($i == 0) {
    echo json_encode(['status' => 0, 'mensaje' => 'No hay datos para mostrar en el reporte']);
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

if ($j == 0) {
    echo json_encode(['status' => 0, 'mensaje' => 'Institucion asignada a la agencia no encontrada']);
    return;
}
//se manda a impresion
switch ($tipo) {
    case 'xlsx';
        printxls($data, [$texto_reporte, $_SESSION['id'], $hoy, $conexion]);
        break;
    case 'pdf':
        printpdf($data, [$texto_reporte, $_SESSION['id'], $hoy, $conexion], $info);
        break;
}

//FUNCION PARA GENERAR EL REPORTE EN PDF

function printxls($datos, $otros)
{
    $hoy = date("Y-m-d H:i:s");
    // $institucion = "Cooperativa Integral De Ahorro y credito Imperial";
    // $direccionins = "Canton vipila zona 1";
    // $emailins = "fape@gmail.com";
    // $telefonosins = "502 43987876";
    // $nitins = "1323244234";

    $fuente_encabezado = "Arial";
    $fuente = "Courier";
    $tamanioFecha = 9;
    // $tamanioEncabezado = 14;
    $tamanioTabla = 11;

    $spread = new Spreadsheet();
    $spread
        ->getProperties()
        ->setCreator("MICROSYSTEM")
        ->setLastModifiedBy('MICROSYSTEM')
        ->setTitle('Reporte')
        ->setSubject('Visitas prepago')
        ->setDescription('Este reporte fue generado por el sistema MICROSYSTEM')
        ->setKeywords('PHPSpreadsheet')
        ->setCategory('Excel');
    //-----------RELACIONADO CON LAS PROPIEDADES DEL ARCHIVO----------------------------

    //-----------RELACIONADO CON EL ENCABEZADO----------------------------
    # Como ya hay una hoja por defecto, la obtenemos, no la creamos
    $hojaReporte = $spread->getActiveSheet();
    $hojaReporte->setTitle("Reporte de Visitas Prepago");

    //insertarmos la fecha y usuario
    $hojaReporte->setCellValue("A1", $hoy);
    $hojaReporte->setCellValue("A2", $otros[1]);
    //informacion de la agencia o cooperativa
    // $hojaReporte->setCellValue("A4", $institucion);
    // $hojaReporte->setCellValue("A5", $direccionins);
    // $hojaReporte->setCellValue("A6", "Email: " . $emailins);
    // $hojaReporte->setCellValue("A7", "Tel: " . $telefonosins);
    // $hojaReporte->setCellValue("A8", "NIT: " . $nitins);

    //hacer pequeño las letras de la fecha, definir arial como tipo de letra
    $hojaReporte->getStyle("A1:K1")->getFont()->setSize($tamanioFecha)->setName($fuente_encabezado);
    $hojaReporte->getStyle("A2:K2")->getFont()->setSize($tamanioFecha)->setName($fuente_encabezado);
    //centrar el texto de la fecha
    $hojaReporte->getStyle("A1:K1")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $hojaReporte->getStyle("A2:K2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // //hacer grande las letras del encabezado
    // $hojaReporte->getStyle("A4:K4")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
    // $hojaReporte->getStyle("A5:K5")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
    // $hojaReporte->getStyle("A6:K6")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
    // $hojaReporte->getStyle("A7:K7")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
    // $hojaReporte->getStyle("A8:K8")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
    // //centrar el texto del encabezado
    // $hojaReporte->getStyle("A4:K4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    // $hojaReporte->getStyle("A5:K5")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    // $hojaReporte->getStyle("A6:K6")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    // $hojaReporte->getStyle("A7:K7")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    // $hojaReporte->getStyle("A8:K8")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //hacer pequeño las letras del encabezado de titulo
    $hojaReporte->getStyle("A4:K4")->getFont()->setSize($tamanioTabla)->setName($fuente);
    $hojaReporte->getStyle("A5:K5")->getFont()->setSize($tamanioTabla)->setName($fuente);
    //centrar los encabezado de la tabla
    $hojaReporte->getStyle("A4:K4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $hojaReporte->getStyle("A5:K5")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $hojaReporte->setCellValue("A4", "REPORTE");
    $hojaReporte->setCellValue("A5", strtoupper($otros[0]));

    //TITULO DE RECARGOS

    //titulo de recargos
    $hojaReporte->getStyle("A7:K7")->getFont()->setSize($tamanioTabla)->setName($fuente)->setBold(true);
    $hojaReporte->getStyle("A7:K7")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $hojaReporte->setCellValue("F7", "RECARGOS");

    # Escribir encabezado de la tabla
    $encabezado_tabla = ["CÓDIGO CUENTA", "CÓDIGO CLIENTE", "NOMBRE CLIENTE", "FECHA", "SALDO", "MORA", "AHORRO PROGRAMADO", "OTROS", "CUOTA", "CAPITAL", "INTERES"];
    # El último argumento es por defecto A1 pero lo pongo para que se explique mejor
    $hojaReporte->fromArray($encabezado_tabla, null, 'A8')->getStyle('A8:K8')->getFont()->setName($fuente)->setBold(true);

    //combinacion de celdas
    $hojaReporte->mergeCells('A1:K1');
    $hojaReporte->mergeCells('A2:K2');
    $hojaReporte->mergeCells('A4:K4');
    $hojaReporte->mergeCells('A5:K5');
    $hojaReporte->mergeCells('F7:H7');

    //CARGAR LOS DATOS
    $fila = 0;
    $linea = 9;
    while ($fila < count($datos)) {
        // SELECT ag.cod_agenc ,pg.dfecven AS fecha, cm.CCODCTA AS cuenta, cl.idcod_cliente AS cliente, cl.short_name AS nombre, pg.SaldoCapital AS saldo, pg.nintmor AS mora, pg.NAhoProgra AS pag1, pg.OtrosPagos AS pag2, (pg.ncapita + pg.nintere) AS cuota, pg.ncapita AS capital, pg.nintere AS interes
        $hojaReporte->getStyle("A" . $linea)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        $fecha = $datos[$fila]["fecha"];
        $cuenta = utf8_decode($datos[$fila]["cuenta"]);
        $cliente = utf8_decode($datos[$fila]["cliente"]);
        $nombre = strtoupper(utf8_decode($datos[$fila]["nombre"]));
        $saldo = $datos[$fila]["saldo"];
        $mora = $datos[$fila]["mora"];
        $pag1 = $datos[$fila]["pag1"];
        $pag2 = $datos[$fila]["pag2"];
        $cuota = $datos[$fila]["cuota"];
        $capital = $datos[$fila]["capital"];
        $interes = $datos[$fila]["interes"];

        if ($fila == 0) {
            $aux = $cliente;
            $hojaReporte->setCellValueByColumnAndRow(1, $linea, $cuenta);
            $hojaReporte->setCellValueByColumnAndRow(2, $linea, $cliente);
            $hojaReporte->setCellValueByColumnAndRow(3, $linea, $nombre);
            $hojaReporte->setCellValueByColumnAndRow(4, $linea, $fecha);
            $hojaReporte->setCellValueByColumnAndRow(5, $linea, $saldo);
            $hojaReporte->setCellValueByColumnAndRow(6, $linea, $mora);
            $hojaReporte->setCellValueByColumnAndRow(7, $linea, $pag1);
            $hojaReporte->setCellValueByColumnAndRow(8, $linea, $pag2);
            $hojaReporte->setCellValueByColumnAndRow(9, $linea, $cuota);
            $hojaReporte->setCellValueByColumnAndRow(10, $linea, $capital);
            $hojaReporte->setCellValueByColumnAndRow(11, $linea, $interes);
        }

        if ($fila != 0 && $aux == $cliente) {
            $hojaReporte->setCellValueByColumnAndRow(1, $linea, " ");
            $hojaReporte->setCellValueByColumnAndRow(2, $linea, " ");
            $hojaReporte->setCellValueByColumnAndRow(3, $linea, " ");
            $hojaReporte->setCellValueByColumnAndRow(4, $linea, $fecha);
            $hojaReporte->setCellValueByColumnAndRow(5, $linea, $saldo);
            $hojaReporte->setCellValueByColumnAndRow(6, $linea, $mora);
            $hojaReporte->setCellValueByColumnAndRow(7, $linea, $pag1);
            $hojaReporte->setCellValueByColumnAndRow(8, $linea, $pag2);
            $hojaReporte->setCellValueByColumnAndRow(9, $linea, $cuota);
            $hojaReporte->setCellValueByColumnAndRow(10, $linea, $capital);
            $hojaReporte->setCellValueByColumnAndRow(11, $linea, $interes);
        } elseif ($fila != 0 && $aux != $cliente) {
            $aux = $cliente;
            $hojaReporte->setCellValueByColumnAndRow(1, $linea, $cuenta);
            $hojaReporte->setCellValueByColumnAndRow(2, $linea, $cliente);
            $hojaReporte->setCellValueByColumnAndRow(3, $linea, $nombre);
            $hojaReporte->setCellValueByColumnAndRow(4, $linea, $fecha);
            $hojaReporte->setCellValueByColumnAndRow(5, $linea, $saldo);
            $hojaReporte->setCellValueByColumnAndRow(6, $linea, $mora);
            $hojaReporte->setCellValueByColumnAndRow(7, $linea, $pag1);
            $hojaReporte->setCellValueByColumnAndRow(8, $linea, $pag2);
            $hojaReporte->setCellValueByColumnAndRow(9, $linea, $cuota);
            $hojaReporte->setCellValueByColumnAndRow(10, $linea, $capital);
            $hojaReporte->setCellValueByColumnAndRow(11, $linea, $interes);
        }
        $hojaReporte->getStyle("A" . $linea . ":K" . $linea)->getFont()->setName($fuente);
        $fila++;
        $linea++;
    }
    $hojaReporte->getColumnDimension('A')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('B')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('C')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('D')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('E')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('F')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('G')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('H')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('I')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('J')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('K')->setAutoSize(TRUE);

    //SECCION PARA DESCARGA EL ARCHIVO
    ob_start();
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spread, 'Xlsx');
    $writer->save("php://output");
    $xlsData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "reportePrepago" . $otros[2],
        'data' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
    );
    echo json_encode($opResult);
}

function printpdf($datos, $otros, $info)
{
    $oficina = utf8_decode($info[0]["nom_agencia"]);
    $institucion = utf8_decode($info[0]["nomb_comple"]);
    $direccionins = utf8_decode($info[0]["muni_lug"]);
    $emailins = $info[0]["emai"];
    $telefonosins = $info[0]["tel_1"] . '   ' . $info[0]["tel_2"];;
    $nitins = $info[0]["nit"];
    $rutalogomicro = "../../../../includes/img/logomicro.png";
    $rutalogoins = "../../../.." . $info[0]["log_img"];

    class PDF extends FPDF
    {
        //atributos de la clase
        public $oficina;
        public $institucion;
        public $direccion;
        public $email;
        public $telefono;
        public $nit;
        public $pathlogo;
        public $pathlogoins;
        public $titulo;
        public $user;
        public $conexion;

        public function __construct($oficina, $institucion, $direccion, $email, $telefono, $nit, $pathlogo, $pathlogoins, $titulo, $user, $conexion)
        {
            parent::__construct();
            $this->oficina = $oficina;
            $this->institucion = $institucion;
            $this->direccion = $direccion;
            $this->email = $email;
            $this->telefono = $telefono;
            $this->nit = $nit;
            $this->pathlogo = $pathlogo;
            $this->pathlogoins = $pathlogoins;
            $this->titulo = $titulo;
            $this->user = $user;
            $this->conexion = $conexion;
            $this->DefOrientation = 'L';
        }

        // Cabecera de página
        function Header()
        {
            $fuente = "Courier";
            $tamanio_linea = 4; //altura de la linea/celda
            $ancho_linea = 30; //anchura de la linea/celda
            $ancho_linea2 = 20; //anchura de la linea/celda

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
            $this->Cell(0, 5, 'REPORTE', 0, 1, 'C', true);
            $this->Cell(0, 5,  $this->titulo, 1, 1, 'C', true);

            $this->Ln(5);
            //Fuente
            $this->SetFont($fuente, '', 9);
            //encabezado de tabla
            $this->CellFit($ancho_linea + 130, $tamanio_linea + 1, " ", 0, 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea + 30, $tamanio_linea + 1, "RECARGOS", 1, 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea + 42, $tamanio_linea + 1, " ", 0, 0, 'C', 0, '', 1, 0);
            $this->Ln(5);
            $this->CellFit($ancho_linea, $tamanio_linea + 1, utf8_decode("CÓDIGO CUENTA"), 1, 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea, $tamanio_linea + 1, utf8_decode('CÓDIGO CLIENTE'), 1, 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea + 25, $tamanio_linea + 1, 'NOMBRE CLIENTE', 1, 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea - 10, $tamanio_linea + 1, 'FECHA', 1, 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea - 5, $tamanio_linea + 1, 'SALDO', 1, 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea - 10, $tamanio_linea + 1, 'MORA', 1, 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea - 10, $tamanio_linea + 1, 'AHO. PROG.', 1, 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea - 10, $tamanio_linea + 1, 'OTROS', 1, 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea - 13, $tamanio_linea + 1, 'CUOTA', 1, 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea - 10, $tamanio_linea + 1, 'CAPITAL', 1, 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea - 10, $tamanio_linea + 1, utf8_decode('INTERÉS'), 1, 0, 'C', 0, '', 1, 0);
            $this->Ln(7);
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

    $fuente = "Courier";
    $tamanio_linea = 4;
    $ancho_linea = 30;

    // Creación del objeto de la clase heredada
    $pdf = new PDF($oficina, $institucion, $direccionins, $emailins, $telefonosins, $nitins, $rutalogomicro, $rutalogoins, $otros[0], $otros[1], $otros[3]);
    $pdf->AliasNbPages();
    $pdf->AddPage();

    //AQUI COLOCAR TODOS LO DATOS
    $fila = 0;
    while ($fila < count($datos)) {
        // SELECT ag.cod_agenc ,pg.dfecven AS fecha, cm.CCODCTA AS cuenta, cl.idcod_cliente AS cliente, cl.short_name AS nombre, pg.SaldoCapital AS saldo, pg.nintmor AS mora, pg.NAhoProgra AS pag1, pg.OtrosPagos AS pag2, (pg.ncapita + pg.nintere) AS cuota, pg.ncapita AS capital, pg.nintere AS interes

        $fecha = date("d-m-Y", strtotime($datos[$fila]["fecha"]));
        $cuenta = $datos[$fila]["cuenta"];
        $cliente = utf8_decode($datos[$fila]["cliente"]);
        $nombre = strtoupper(utf8_decode($datos[$fila]["nombre"]));
        $saldo = $datos[$fila]["saldo"];
        $mora = $datos[$fila]["mora"];
        $pag1 = $datos[$fila]["pag1"];
        $pag2 = $datos[$fila]["pag2"];
        $cuota = $datos[$fila]["cuota"];
        $capital = $datos[$fila]["capital"];
        $interes = $datos[$fila]["interes"];

        if ($fila == 0) {
            $aux = $cliente;
            $pdf->CellFit($ancho_linea, $tamanio_linea + 1, $cuenta, 0, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea, $tamanio_linea + 1, $cliente, 0, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea + 25, $tamanio_linea + 1, $nombre, 0, 0, 'L', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $fecha, 0, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 5, $tamanio_linea + 1, $saldo, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $mora, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $pag1, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $pag2, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 13, $tamanio_linea + 1, $cuota, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $capital, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $interes, 0, 0, 'R', 0, '', 1, 0);
        }

        if ($fila != 0 && $aux == $cliente) {
            $pdf->CellFit($ancho_linea, $tamanio_linea + 1, ' ', 0, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea, $tamanio_linea + 1, ' ', 0, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea + 25, $tamanio_linea + 1, ' ', 0, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $fecha, 0, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 5, $tamanio_linea + 1, $saldo, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $mora, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $pag1, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $pag2, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 13, $tamanio_linea + 1, $cuota, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $capital, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $interes, 0, 0, 'R', 0, '', 1, 0);
        } elseif ($fila != 0 && $aux != $cliente) {
            $aux = $cliente;
            $pdf->CellFit($ancho_linea, $tamanio_linea + 1, $cuenta, 0, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea, $tamanio_linea + 1, $cliente, 0, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea + 25, $tamanio_linea + 1, $nombre, 0, 0, 'L', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $fecha, 0, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 5, $tamanio_linea + 1, $saldo, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $mora, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $pag1, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $pag2, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 13, $tamanio_linea + 1, $cuota, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $capital, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 10, $tamanio_linea + 1, $interes, 0, 0, 'R', 0, '', 1, 0);
        }
        $pdf->Ln(5);
        $fila++;
    }

    ob_start();
    $pdf->Output();
    $pdfData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "reportePrepago" . $otros[2],
        'data' => "data:application/vnd.ms-word;base64," . base64_encode($pdfData)
    );
    echo json_encode($opResult);
}
