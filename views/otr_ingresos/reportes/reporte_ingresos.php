<?php
session_start();
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');
require '../../../fpdf/fpdf.php';
require '../../../vendor/autoload.php';
date_default_timezone_set('America/Guatemala');
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Round;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

$hoy2 = date("Y-m-d H:i:s");
$hoy = date("Y-m-d");
//se recibe los datos
$datos = $_POST["datosval"];
$inputs = $datos[0];
$selects = $datos[1];
$radios = $datos[2];
$archivo = $datos[3];
$tipo = $_POST["tipo"];

$tipoconsulta = 0;

// validar la Fecha
// if ($inputs[0] != $hoy) {
//     echo json_encode(['status' => 0, 'mensaje' => 'La fecha inicial debe ser igual a la de hoy']);
//     return;
// }

if ($inputs[1] < $inputs[0]) {
    echo json_encode(['status' => 0, 'mensaje' => 'La fecha final no debe ser menor que la fecha inicial']);
    return;
}
//VALIDACIONES DE ESTADO Y AGENCIA
// if ($selects[0] == '0') {
//     echo json_encode(['status' => 0, 'mensaje' => 'Debe seleccionar una agencia']);
//     return;
// }

// FORMAR LA CONSULTA
$consulta = "SELECT tp.id AS idfact, tp.recibo AS recibo, tp.cliente AS cli, tp.fecha AS fecha, IF(tpi.tipo = 1, 'INGRESO', 'EGRESO') AS tipomov, tp.descripcion AS descripcion, tpi.nombre_gasto AS detalle, tpm.monto AS monto, (SELECT ag2.nom_agencia FROM tb_agencia ag2 WHERE ag2.id_agencia=tp.agencia) AS agencia FROM otr_pago_mov tpm 
INNER JOIN otr_tipo_ingreso tpi ON tpm.id_otr_tipo_ingreso=tpi.id 
INNER JOIN otr_pago tp ON tpm.id_otr_pago=tp.id WHERE tpi.tipo='$radios[0]' AND (tp.fecha BETWEEN '$inputs[0]' AND '$inputs[1]')";

// Validar el tipo de reporte
if ($radios[0] == 1) {
    $tipo_ingreso = "INGRESOS";
} else {
    $tipo_ingreso = "EGRESOS";
}
//Validar si es de alguna agencia
$bandera_agencia = false;
$nomagencia = "";
if ($selects[0] != '0') {
    $consulta .= " AND tp.agencia='$selects[0]'";
    $bandera_agencia = true;
    // Consultar nombre de agencia
    $queryest = "SELECT UPPER(ag.nom_agencia) AS agencia FROM tb_agencia ag WHERE ag.id_agencia='" . $archivo[0] . "' ";
    $estado = mysqli_query($conexion, $queryest);
    while ($fil = mysqli_fetch_array($estado)) {
        $nomagencia = $fil["agencia"];
        $nomagencia = " DE LA AGENCIA: " . $nomagencia;
    }
} else {
    $nomagencia = " DE TODAS LAS AGENCIAS ";
}

//RECUPERAR LOS DATOS DE LA CONSULTA PRINCIPAL
$query = mysqli_query($conexion, $consulta);
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


// $consulta = "";
//texto de estado
/* echo json_encode(['status' => 0, 'mensaje' => $consulta]);
    return;  */

$texto_reporte = "REPORTE DE " . $tipo_ingreso . " DEL " .  date("d-m-Y", strtotime($inputs[0])) . " AL " . date("d-m-Y", strtotime($inputs[1])) . $nomagencia;

$queryins = mysqli_query($conexion, "SELECT * FROM clhpzzvb_bd_general_coopera.info_coperativa ins
INNER JOIN tb_agencia ag ON ag.id_institucion=ins.id_cop where ag.id_agencia='" . $archivo[0] . "'");
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
    case 'xlsx':
        printxls($data, [$texto_reporte, $archivo[2], $hoy, $conexion, $nomestado]);
        break;
    case 'pdf':

        printpdf($data, [$texto_reporte, $archivo[1], $hoy, $tipo_ingreso, $inputs[1], $conexion], $info);
        break;
}

//FUNCION PARA GENERAR EL REPORTE EN PDF
function printpdf($registro, $datos, $info)
{
    $oficina = utf8_decode($info[0]["nom_agencia"]);
    $institucion = utf8_decode($info[0]["nomb_comple"]);
    $direccionins = utf8_decode($info[0]["muni_lug"]);
    $emailins = $info[0]["emai"];
    $telefonosins = $info[0]["tel_1"] . '   ' . $info[0]["tel_2"];;
    $nitins = $info[0]["nit"];
    $rutalogomicro = "../../../includes/img/logomicro.png";
    $rutalogoins = "../../.." . $info[0]["log_img"];


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
            // $this->DefOrientation = 'P';
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
            $this->SetFont('Arial', '', 8);
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

            $this->SetFont($fuente, 'B', 10);
            //SECCION DE DATOS DEL CLIENTE
            //TITULO DE REPORTE
            $this->SetFillColor(255, 255, 255);
            $this->Cell(0, 5, 'REPORTE', 0, 1, 'C', true);
            $this->Cell(0, 5,  utf8_decode($this->titulo), 0, 1, 'C', true);

            $this->Ln(5);
            //Fuente
            $this->SetFont($fuente, 'B', 9);
            //encabezado de tabla
            // $this->CellFit($ancho_linea + 130, $tamanio_linea + 1, " ", 0, 0, 'C', 0, '', 1, 0);
            // $this->CellFit($ancho_linea + 30, $tamanio_linea + 1, "RECARGOS", 1, 0, 'C', 0, '', 1, 0);
            // $this->CellFit($ancho_linea + 42, $tamanio_linea + 1, " ", 0, 0, 'C', 0, '', 1, 0);
            // $this->Ln(5);
            $this->CellFit($ancho_linea - 20, $tamanio_linea + 1, utf8_decode("No."), 'B', 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea + 110, $tamanio_linea + 1, 'DESCRIPCION', 'B', 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea + 10, $tamanio_linea + 1, 'MONTO', 'B', 0, 'C', 0, '', 1, 0);
            // $this->CellFit(0, $tamanio_linea + 1, ' ', 'B', 'B', 'C', 0, '', 1, 0);
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

    $fuente = "Courier";
    $tamanio_linea = 4;
    $ancho_linea = 30;

    // Creación del objeto de la clase heredada
    $pdf = new PDF($oficina, $institucion, $direccionins, $emailins, $telefonosins, $nitins, $rutalogomicro, $rutalogoins, $datos[0], $datos[1], $datos[3]);
    $pdf->AliasNbPages();
    $pdf->AddPage();

    //AQUI COLOCAR TODOS LO DATOS
    $fila = 0;
    $control = 0;
    $contador = 0;
    $pdf->SetFont($fuente, '', 9);
    while ($fila < count($registro)) {
        $idfact = $registro[$fila]["idfact"];
        $recibo = $registro[$fila]["recibo"];
        $cli = $registro[$fila]["cli"];
        $fecha = $registro[$fila]["fecha"];
        $tipomov = $registro[$fila]["tipomov"];
        $detalle = $registro[$fila]["detalle"];
        $monto = $registro[$fila]["monto"];
        $agencia = $registro[$fila]["agencia"];
        $descripcion = $registro[$fila]["descripcion"];

        $contador++;
        if ($control != $idfact) {
            $control = $idfact;
            // AQUI ES DONDE VA A ESTAR LO DEL ENCABEZADO
            $contador = 1;
            $pdf->SetFont($fuente, 'B', 8);
            $pdf->CellFit($ancho_linea + 12, $tamanio_linea + 1, 'Recibo No.: ' . utf8_decode($recibo), 'B', 0, 'L', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 25, $tamanio_linea + 1, ' ', 'B', 0, 'L', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea + 23, $tamanio_linea + 1, 'Cliente: ' . utf8_decode($cli), 'B', 0, 'L', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 25, $tamanio_linea + 1, ' ', 'B', 0, 'L', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea + 10, $tamanio_linea + 1, 'Fecha: ' . date("d-m-Y", strtotime($fecha)), 'B', 0, 'L', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea - 25, $tamanio_linea + 1, ' ', 'B', 0, 'L', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea + 10, $tamanio_linea + 1, 'Agencia: ' . utf8_decode($agencia), 'B', 0, 'L', 0, '', 1, 0);
            $pdf->Ln(5);
        }

        // AQUI SE DEBE COLOCAR LO DEMAS
        $pdf->SetFont($fuente, '', 9);
        $pdf->CellFit($ancho_linea - 20, $tamanio_linea + 1, $contador, 0, 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 110, $tamanio_linea + 1, $detalle, 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 10, $tamanio_linea + 1, number_format(round($monto, 2), 2), 0, 0, 'R', 0, '', 1, 0);
        $pdf->Ln(5);
        //SUMAR EL ENCABEZADO
        if (($fila + 1) != count($registro)) {
            if ($control != $registro[$fila + 1]['idfact']) {
                $pdf->SetFont($fuente, '', 9);
                $pdf->CellFit($ancho_linea + 90, $tamanio_linea + 1, 'DESCRIPCION:'.utf8_decode($descripcion), 0, 0, 'L', 0, '', 1, 0);
                $pdf->SetFont($fuente, 'B', 9);
                $pdf->CellFit($ancho_linea, $tamanio_linea + 1, 'TOTAL', 0, 0, 'C', 0, '', 1, 0);
               //SUMA LOS VALORES
               $target_id = $registro[$fila]['idfact'];
               $registro_aux = array_filter($registro, function($registro_1) use ($target_id) {
                   return $registro_1['idfact'] == $target_id;
               });
               $suma_valores = array_sum(array_column($registro_aux, 'monto'));
               $pdf->CellFit($ancho_linea + 10, $tamanio_linea + 1, number_format(round($suma_valores, 2), 2), 'BT', 0, 'R', 0, '', 1, 0);
                $pdf->Ln(6);
                $pdf->CellFit($ancho_linea + 120, $tamanio_linea + 1, ' ', 0, 0, 'C', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea + 10, $tamanio_linea + 1, ' ', 'T', 0, 'R', 0, '', 1, 0);
                $pdf->Ln(7);
            }
        } else {
            $pdf->SetFont($fuente, '', 9);
            $pdf->CellFit($ancho_linea + 90, $tamanio_linea + 1, 'DESCRIPCION:'.utf8_decode($descripcion), 0, 0, 'L', 0, '', 1, 0);
            $pdf->SetFont($fuente, 'B', 9);
            $pdf->CellFit($ancho_linea, $tamanio_linea + 1, 'TOTAL', 0, 0, 'C', 0, '', 1, 0);
            //SUMA LOS VALORES
            $target_id = $registro[$fila]['idfact'];
            $registro_aux = array_filter($registro, function($registro_1) use ($target_id) {
                return $registro_1['idfact'] == $target_id;
            });
            $suma_valores = array_sum(array_column($registro_aux, 'monto'));
            $pdf->CellFit($ancho_linea + 10, $tamanio_linea + 1, number_format(round($suma_valores, 2), 2), 'BT', 0, 'R', 0, '', 1, 0);
            $pdf->Ln(6);
            $pdf->CellFit($ancho_linea + 120, $tamanio_linea + 1, ' ', 0, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea + 10, $tamanio_linea + 1, ' ', 'T', 0, 'R', 0, '', 1, 0);
            $pdf->Ln(7);
        }
        $fila++;
    }
    // $pdf->SetFont($fuente, 'B', 8);
    // $pdf->Cell(0, 0, ' ', 1, 1, 'R');
    // $pdf->CellFit($ancho_linea * 2, $tamanio_linea + 1, utf8_decode('NÚMERO DE CASOS: '), 0, 0, 'R', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea, $tamanio_linea + 1, $fila, 0, 0, 'L', 0, '', 1, 0);

    // $sumsolicitado = array_sum(array_column($datos, "montosoli"));
    // $sumaprobado = array_sum(array_column($datos, "montoaprobado"));
    // $sumdesembolso = array_sum(array_column($datos, "montodesembolsado"));
    // $sumgasto = array_sum(array_column($datos, "gastos"));

    // $pdf->CellFit($ancho_linea, $tamanio_linea + 1, number_format($sumsolicitado, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea, $tamanio_linea + 1, number_format($sumaprobado, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea, $tamanio_linea + 1, number_format($sumdesembolso, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea, $tamanio_linea + 1, number_format($sumgasto, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
    // $pdf->Ln(15);
    // $pdf->CellFit($ancho_linea, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea + 78, $tamanio_linea + 1, 'HECHO POR: ', 0, 0, 'L', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    // $pdf->CellFit($ancho_linea + 79, $tamanio_linea + 1, 'REVISADO POR: ', 0, 0, 'L', 0, '', 1, 0);

    ob_start();
    $pdf->Output();
    $pdfData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "REPORTE_" . $datos[3] . "_AL" . $datos[4],
        'data' => "data:application/pdf;base64," . base64_encode($pdfData),
        'tipo' => "pdf"
    );
    echo json_encode($opResult);
}

function printxls($datos, $otros)
{
    $hoy = date("Y-m-d H:i:s");

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
    $hojaReporte->setTitle("Reporte de desembolsos");

    //insertarmos la fecha y usuario
    $hojaReporte->setCellValue("A1", $hoy);
    $hojaReporte->setCellValue("A2", $otros[1]);

    //hacer pequeño las letras de la fecha, definir arial como tipo de letra
    $hojaReporte->getStyle("A1:H1")->getFont()->setSize($tamanioFecha)->setName($fuente_encabezado);
    $hojaReporte->getStyle("A2:H2")->getFont()->setSize($tamanioFecha)->setName($fuente_encabezado);
    //centrar el texto de la fecha
    $hojaReporte->getStyle("A1:H1")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $hojaReporte->getStyle("A2:H2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //hacer pequeño las letras del encabezado de titulo
    $hojaReporte->getStyle("A4:H4")->getFont()->setSize($tamanioTabla)->setName($fuente);
    $hojaReporte->getStyle("A5:H5")->getFont()->setSize($tamanioTabla)->setName($fuente);
    //centrar los encabezado de la tabla
    $hojaReporte->getStyle("A4:H4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $hojaReporte->getStyle("A5:H5")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $hojaReporte->setCellValue("A4", "REPORTE");
    $hojaReporte->setCellValue("A5", strtoupper($otros[0]));

    # Escribir encabezado de la tabla
    $encabezado_tabla = ["CRÉDITO", "CLIENTE", "NOMBRE CLIENTE", "MONTO SOLICITADO", "MONTO APROBADO", "MONTO DESEMBOLSADO", "COMISION A COBRAR", "TIPO DE DOCUMENTO", "FECHA DE SOLICITUD", "FECHA DE DESEMBOLSO", "FECHA DE VENCIMIENTO", "RESPONSABLE"];
    # El último argumento es por defecto A1 pero lo pongo para que se explique mejor
    $hojaReporte->fromArray($encabezado_tabla, null, 'A7')->getStyle('A7:H7')->getFont()->setName($fuente)->setBold(true);

    //combinacion de celdas
    $hojaReporte->mergeCells('A1:H1');
    $hojaReporte->mergeCells('A2:H2');
    $hojaReporte->mergeCells('A4:H4');
    $hojaReporte->mergeCells('A5:H5');

    //CARGAR LOS DATOS
    $sumamonsol = 0;
    $sumamontoapro = 0;
    $sumamontodes = 0;
    $sumaacobrar = 0;
    $fila = 0;
    $linea = 8;
    while ($fila < count($datos)) {
        // SELECT ag.cod_agenc ,pg.dfecven AS fecha, cm.CCODCTA AS cuenta, cl.idcod_cliente AS cliente, cl.short_name AS nombre, pg.SaldoCapital AS saldo, pg.nintmor AS mora, pg.NAhoProgra AS pag1, pg.OtrosPagos AS pag2, (pg.ncapita + pg.nintere) AS cuota, pg.ncapita AS capital, pg.nintere AS interes
        $hojaReporte->getStyle("A" . $linea)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        $cuenta = $datos[$fila]["cuenta"];
        $codigocliente = $datos[$fila]["codigocliente"];
        $nombre = strtoupper($datos[$fila]["nombre"]);
        $montosolicitado = $datos[$fila]["montosoli"];
        $montoaprobado = $datos[$fila]["montoaprobado"];
        $montodesembolsado = $datos[$fila]["montodesembolsado"]; //suma
        $comacobrar = $datos[$fila]["gastos"]; //sumar
        $tipo = $datos[$fila]["tipo"];

        $tipoenti = $datos[$fila]["TipoEnti"];
        $tipoenti = ($tipoenti == "GRUP") ? 'GRUPOS' : 'INDIVIDUAL';
        $nombrefondos = $datos[$fila]["fondesc"];
        $nomgrupo = ($tipoenti == "GRUPOS") ? $datos[$fila]["NombreGrupo"] : ' ';

        if ($datos[$fila]["tipo"] == "E") {
            $tipo = "EFECTIVO";
        }
        if ($datos[$fila]["tipo"] == "T") {
            $tipo = "TRANSFERENCIA";
        }
        if ($datos[$fila]["tipo"] == "C") {
            $tipo = "CHEQUE";
        }
        $fecsolicitud = $datos[$fila]["fecsolicitud"];
        $fecdesembolsado = $datos[$fila]["fecdesembolsado"];
        $fecvencimiento = $datos[$fila]["fecvencimiento"];
        $responsable = strtoupper(utf8_decode($datos[$fila]["responsable"]));

        $sumamonsol = $sumamonsol + $montosolicitado;
        $sumamontoapro = $sumamontoapro + $montoaprobado;
        $sumamontodes = $sumamontodes + $montodesembolsado;
        $sumaacobrar = $sumaacobrar + $comacobrar;
        $hojaReporte->setCellValueByColumnAndRow(1, $linea, $cuenta);
        $hojaReporte->setCellValueByColumnAndRow(2, $linea, $codigocliente);
        $hojaReporte->setCellValueByColumnAndRow(3, $linea, $nombre);
        $hojaReporte->setCellValueByColumnAndRow(4, $linea, $montosolicitado);
        $hojaReporte->setCellValueByColumnAndRow(5, $linea, $montoaprobado);
        $hojaReporte->setCellValueByColumnAndRow(6, $linea, $montodesembolsado);
        $hojaReporte->setCellValueByColumnAndRow(7, $linea, $comacobrar);
        $hojaReporte->setCellValueByColumnAndRow(8, $linea, $tipo);
        $hojaReporte->setCellValueByColumnAndRow(9, $linea, $fecsolicitud);
        $hojaReporte->setCellValueByColumnAndRow(10, $linea, $fecdesembolsado);
        $hojaReporte->setCellValueByColumnAndRow(11, $linea, $fecvencimiento);
        $hojaReporte->setCellValueByColumnAndRow(12, $linea, $responsable);
        $hojaReporte->setCellValueByColumnAndRow(13, $linea, $nombrefondos);
        $hojaReporte->setCellValueByColumnAndRow(14, $linea, $tipoenti);
        $hojaReporte->setCellValueByColumnAndRow(15, $linea, $nomgrupo);

        $hojaReporte->getStyle("A" . $linea . ":M" . $linea)->getFont()->setName($fuente);
        $fila++;
        $linea++;
    }
    //totales
    $hojaReporte->setCellValueByColumnAndRow(2, $linea, "NUM. DE CREDITOS: " . $fila);
    $hojaReporte->setCellValueByColumnAndRow(3, $linea, $sumamontoapro);
    $hojaReporte->setCellValueByColumnAndRow(4, $linea, $sumamonsol);
    $hojaReporte->setCellValueByColumnAndRow(5, $linea, $sumamontodes);
    $hojaReporte->setCellValueByColumnAndRow(6, $linea, $sumaacobrar);
    $hojaReporte->getStyle("A" . $linea . ":P" . $linea)->getFont()->setName($fuente)->setBold(true);
    //totales
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
    $hojaReporte->getColumnDimension('L')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('M')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('N')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('O')->setAutoSize(TRUE);
    $hojaReporte->getColumnDimension('P')->setAutoSize(TRUE);

    //SECCION PARA DESCARGA EL ARCHIVO
    ob_start();
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spread, 'Xlsx');
    $writer->save("php://output");
    $xlsData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "CREDITOS_" . $otros[4] . "_" . $otros[2],
        'data' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
    );
    echo json_encode($opResult);
}
