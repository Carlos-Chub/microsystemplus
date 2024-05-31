<?php
session_start();
include '../../../includes/BD_con/db_con.php';
include '../../../src/funcphp/func_gen.php';
require '../../../fpdf/fpdf.php';
require "../../../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

date_default_timezone_set('America/Guatemala');

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['Sesion expirada, vuelve a iniciar sesion e intente nuevamente', 0]);
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
// $rutalogoins = "../../../includes/img/logomicro.png";

//se crea el array que recibe los datos
// $datos = array();
// $datos = $_POST["data"];
$hoy = date("Y-m-d H:i:s");

$datos = $_POST["datosval"];
$inputs = $datos[0];
$selects = $datos[1];
$radios = $datos[2];
$archivo = $datos[3];
$tipo = $_POST["tipo"];
//se obtienen las variables del get
// $estado = $datos[6];
// $tipo = $datos[7];
// $fecha_final = $datos[5];
// $usuario = $datos[8];
// $oficina = $datos[9];
// $tip_report = $datos[2];
$estado = $radios[0];
$tipo = $selects[0];
$fecha_final = $inputs[0];
$usuario = $_SESSION['id'];
$oficina = $_SESSION['agencia'];
$tip_report = $_POST["tipo"];




if ($inputs[0] > $hoy) {
    echo json_encode(['status' => 0, 'mensaje' => 'Debe ingresar una fecha no mayor al de hoy']);
    return;
}

$fuente_encabezado = "Arial";
$fuente = "Courier";
$tamanioFecha = 9; //tamaño de letra de la fecha y usuario
$tamanioEncabezado = 14; //tamaño de letra del encabezado
$tamanioTabla = 11; //tamaño de letra de la fecha y usuario
$tamanio_linea = 4; //altura de la linea/celda
$ancho_linea = 25; //anchura de la linea/celda
$espacio_blanco = 10; //tamaño del espacio en blanco entre cada celda
$ancho_linea2 = 20; //anchura de la linea/celda
$espacio_blanco2 = 4; //tamaño del espacio en blanco entre cada celda

$linea = 14;

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
    public $user;
    public $conexion;
    public $estado;
    public $tipo;

    public function __construct($conexion, $institucion, $pathlogo, $pathlogoins, $oficina, $direccion, $email, $telefono, $nit, $user, $estado, $tipo)
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
        $this->user = $user;
        $this->conexion = $conexion;
        $this->tipo = $tipo;
        $this->estado = $estado;
    }

    // Cabecera de página
    function Header()
    {
        //consultar los estados
        $texto_cuentas = "";
        //variables para el texto
        $fuente = "Courier";
        $tamanio_linea = 4; //altura de la linea/celda
        $ancho_linea = 25; //anchura de la linea/celda

        //consultar todas las cuentas
        $data_cuentas = mysqli_query($this->conexion, "SELECT `nombre` FROM `ahomtip` WHERE `id_tipo`=$this->tipo");
        while ($rowcuentas = mysqli_fetch_array($data_cuentas, MYSQLI_ASSOC)) {
            $nomtipo = strtoupper(utf8_encode($rowcuentas["nombre"]));
        }
        $texto_cuentas = ($this->tipo == 0) ? ' ' : ' DE ' . $nomtipo;

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
        $this->Cell(0, 5, 'REPORTE DE SALDO DE CUENTAS ' . $texto_cuentas, 0, 1, 'C', true);
        //ver que estados de la cuenta se mostraran
        $texto_estado = "ACTIVOS E INACTIVOS";
        if ($this->estado == 'A') {
            $texto_estado = "ACTIVOS";
        } else if ($this->estado == 'B') {
            $texto_estado = "INACTIVOS";
        }
        $this->Cell(0, 5, $texto_estado, 0, 1, 'C', true);
        $this->Ln(5);
        //Fuente
        $this->SetFont($fuente, '', 10);
        //encabezado de tabla
        $this->Cell($ancho_linea, $tamanio_linea + 1, 'CUENTA', 0, 0, 'C', true); // cuenta
        $this->Cell(($ancho_linea * 2), $tamanio_linea + 1, 'NOMBRE COMPLETO', 0, 0, 'C', true); //nombre
        $this->Cell($ancho_linea, $tamanio_linea + 1, 'FECHA', 0, 0, 'C', true); //fecha
        $this->Cell($ancho_linea, $tamanio_linea + 1, 'SALDO', 0, 0, 'R', true); //saldo
        $this->Cell($ancho_linea, $tamanio_linea + 1, 'TASA', 0, 0, 'C', true); //tasa
        $this->Cell($ancho_linea * 2, $tamanio_linea + 1, 'TIPO', 0, 0, 'C', true); //tasa
        $this->Ln(6);
        $this->Cell(0, 0, '', 'B', 0, 'R', true); //tasa
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

$where = "";
if ($tipo != 0) {
    //para encontrar el codigo de tipo
    $data_tip = mysqli_query($conexion, "SELECT `ccodtip` FROM `ahomtip` WHERE `id_tipo`=$tipo");
    while ($rowcuentas = mysqli_fetch_array($data_tip, MYSQLI_ASSOC)) {
        $ccodtip = strtoupper(utf8_encode($rowcuentas["ccodtip"]));
    }
    $where = " AND tp.ccodtip='$ccodtip'";
}

//se desarrolla la consulta
$consulta = "SELECT tp.nombre,cta.ccodaho, cl.short_name, calcular_saldo_aho_tipcuenta(cta.ccodaho,'$fecha_final') AS saldo, cta.tasa, cl.genero
FROM ahomtip AS tp
INNER JOIN ahomcta AS cta ON tp.ccodtip=SUBSTRING( cta.ccodaho ,7 , 2)  
INNER JOIN tb_cliente AS cl ON cta.ccodcli=cl.idcod_cliente
WHERE 1=1 " . $where;

if ($estado != "0") {
    $consulta .= " AND cta.estado='$estado'";
}
$consulta .= " ORDER BY cta.ccodaho ASC;";

$data = mysqli_query($conexion, $consulta);

//CASO PARA LOS REPORTES
switch ($tip_report) {
    case "pdf": {
            // Creación del objeto de la clase heredada
            $pdf = new PDF($conexion, $institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins,  $usuario, $estado, $tipo);
            $pdf->AliasNbPages();
            $pdf->AddPage();

            $total_saldo = 0;
            $conta_mujeres = 0;
            $conta_hombres = 0;
            $total_mujeres = 0;
            $total_hombres = 0;
            while ($rowdata = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                $bd_ccodaho = utf8_encode($rowdata["ccodaho"]);
                $bd_shortname = strtoupper(utf8_encode($rowdata["short_name"]));
                $bd_fecha = $fecha_final;
                $bd_saldo = strtoupper(utf8_encode($rowdata["saldo"]));
                $bd_tasa = strtoupper(utf8_encode($rowdata["tasa"]));
                $bd_genero = strtoupper(utf8_encode($rowdata["genero"]));
                $bd_tipo = strtoupper(utf8_encode($rowdata["nombre"]));
                $saldo_formateado = number_format($bd_saldo, 2, '.', '');
                $tasa_formateado = number_format($bd_tasa, 2, '.', '');

                $total_saldo = $total_saldo + $bd_saldo;
                if ($bd_genero == "M") {
                    $total_hombres = $total_hombres + $bd_saldo;
                    $conta_hombres++;
                } else if ($bd_genero == "F") {
                    $total_mujeres = $total_mujeres + $bd_saldo;
                    $conta_mujeres++;
                }

                //se insertan los registros
                $pdf->CellFit($ancho_linea, $tamanio_linea + 1, $bd_ccodaho, 0, 0, 'C', 0, '', 1, 0); // cuenta
                $pdf->CellFit(($ancho_linea * 2), $tamanio_linea + 1, $bd_shortname, 0, 0, 'L', 0, '', 1, 0); //nombre
                $pdf->CellFit($ancho_linea, $tamanio_linea + 1,  date("d-m-Y", strtotime($bd_fecha)), 0, 0, 'C', 0, '', 1, 0); //fecha
                $pdf->CellFit($ancho_linea, $tamanio_linea + 1, $saldo_formateado, 0, 0, 'R', 0, '', 1, 0); //documento
                $pdf->CellFit($ancho_linea, $tamanio_linea + 1, $tasa_formateado, 0, 0, 'R', 0, '', 1, 0); //documento
                $pdf->CellFit($ancho_linea2 * 2, $tamanio_linea + 1, $bd_tipo, 0, 0, 'L', 0, '', 1, 0); //documento
                $pdf->Ln(5);
            }

            $pdf->Cell(0, 0, ' ', 'B', 0, 'R', true);
            $pdf->Ln(1);
            $pdf->SetFont($fuente, 'B', 9);
            $pdf->CellFit(($ancho_linea + 92), $tamanio_linea + 1, ' ', 0, 0, 'R', 0, '', 1, 0); // cuenta
            $pdf->CellFit(($ancho_linea + 8), $tamanio_linea + 1, number_format($total_saldo, 2, '.', ''), 0, 0, 'R', 0, '', 1, 0); // cuenta
            $pdf->Ln(8);
            //resumen de saldo
            $pdf->SetFont($fuente, '', 11);
            $pdf->Ln(4);
            $pdf->CellFit($ancho_linea + 40, $tamanio_linea, " ", 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea, $tamanio_linea, "NUMERO", 0, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea + 5, $tamanio_linea, "SALDO", 0, 0, 'R', 0, '', 1, 0);
            $pdf->Ln(4);
            $pdf->CellFit($ancho_linea + 40, $tamanio_linea, "MUJERES:", 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea, $tamanio_linea, $conta_mujeres, 0, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea + 5, $tamanio_linea, number_format($total_mujeres, 2, '.', ''), 0, 0, 'R', 0, '', 1, 0);
            $pdf->Ln(4);
            $pdf->CellFit($ancho_linea + 40, $tamanio_linea, "HOMBRES:", 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea, $tamanio_linea, $conta_hombres, 0, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea + 5, $tamanio_linea, number_format($total_hombres, 2, '.', ''), 0, 0, 'R', 0, '', 1, 0);

            //forma de migrar el archivo
            ob_start();
            $pdf->Output();
            $pdfData = ob_get_contents();
            ob_end_clean();

            $opResult = array(
                'status' => 1,
                'mensaje' => 'Reporte generado correctamente',
                'namefile' => "Saldos de cuentas",
                'tipo' => "pdf",
                'data' => "data:application/pdf;base64," . base64_encode($pdfData)
            );
            mysqli_close($conexion);
            echo json_encode($opResult);
        }
        break;
    case "xlsx":
        $spread = new Spreadsheet();
        $spread
            ->getProperties()
            ->setCreator("MICROSYSTEM")
            ->setLastModifiedBy('MICROSYSTEM')
            ->setTitle('Reporte')
            ->setSubject('Saldos por cuenta con fecha')
            ->setDescription('Este reporte fue generado por el sistema MICROSYSTEM')
            ->setKeywords('PHPSpreadsheet')
            ->setCategory('Excel');
        //-----------RELACIONADO CON LAS PROPIEDADES DEL ARCHIVO----------------------------

        //-----------RELACIONADO CON EL ENCABEZADO----------------------------
        # Como ya hay una hoja por defecto, la obtenemos, no la creamos
        $hojaReporte = $spread->getActiveSheet();
        $hojaReporte->setTitle("Reporte");

        //insertarmos la fecha y usuario
        $hojaReporte->setCellValue("A1", $hoy);
        $hojaReporte->setCellValue("A2", $usuario);
        // //informacion de la agencia o cooperativa
        $hojaReporte->setCellValue("A4", $institucion);
        $hojaReporte->setCellValue("A5", $direccionins);
        $hojaReporte->setCellValue("A6", "Email: " . $emailins);
        $hojaReporte->setCellValue("A7", "Tel: " . $telefonosins);
        $hojaReporte->setCellValue("A8", "NIT: " . $nitins);

        //hacer pequeño las letras de la fecha, definir arial como tipo de letra
        $hojaReporte->getStyle("A1:E1")->getFont()->setSize($tamanioFecha)->setName($fuente_encabezado);
        $hojaReporte->getStyle("A2:E2")->getFont()->setSize($tamanioFecha)->setName($fuente_encabezado);
        //centrar el texto de la fecha
        $hojaReporte->getStyle("A1:E1")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hojaReporte->getStyle("A2:E2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // //hacer grande las letras del encabezado
        $hojaReporte->getStyle("A4:E4")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
        $hojaReporte->getStyle("A5:E5")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
        $hojaReporte->getStyle("A6:E6")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
        $hojaReporte->getStyle("A7:E7")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
        $hojaReporte->getStyle("A8:E8")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
        //centrar el texto del encabezado
        $hojaReporte->getStyle("A4:E4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hojaReporte->getStyle("A5:E5")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hojaReporte->getStyle("A6:E6")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hojaReporte->getStyle("A7:E7")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hojaReporte->getStyle("A8:E8")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        //hacer pequeño las letras del encabezado de titulo
        $hojaReporte->getStyle("A10:E10")->getFont()->setSize($tamanioTabla)->setName($fuente);
        $hojaReporte->getStyle("A11:E11")->getFont()->setSize($tamanioTabla)->setName($fuente);
        $hojaReporte->getStyle("A12:E12")->getFont()->setSize($tamanioTabla)->setName($fuente);

        //centrar los encabezado de la tabla
        $hojaReporte->getStyle("A10:E10")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hojaReporte->getStyle("A11:E11")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hojaReporte->getStyle("A12:E12")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        //titulo del reporte
        // consultar los estados
        $texto_cuentas = "";
        $data_cuentas = mysqli_query($conexion, "SELECT `nombre` FROM `ahomtip` WHERE `id_tipo`=$tipo");
        while ($rowcuentas = mysqli_fetch_array($data_cuentas, MYSQLI_ASSOC)) {
            $texto_cuentas = strtoupper(utf8_encode($rowcuentas["nombre"]));
        }

        $texto_estado = "ACTIVOS E INACTIVOS";
        if ($estado == 'A') {
            $texto_estado = "ACTIVOS";
        } else if ($estado == 'B') {
            $texto_estado = "INACTIVOS";
        }

        $hojaReporte->setCellValue("A10", "REPORTE DE SALDO DE CUENTAS DE " . strtoupper($texto_cuentas));
        $hojaReporte->setCellValue("A11", strtoupper($texto_estado));

        // //combinacion de celdas
        $hojaReporte->mergeCells('A1:E1');
        $hojaReporte->mergeCells('A2:E2');
        $hojaReporte->mergeCells('A4:E4');
        $hojaReporte->mergeCells('A5:E5');
        $hojaReporte->mergeCells('A6:E6');
        $hojaReporte->mergeCells('A7:E7');
        $hojaReporte->mergeCells('A8:E8');
        $hojaReporte->mergeCells('A10:E10');
        $hojaReporte->mergeCells('A11:E11');

        # Escribir encabezado de la tabla
        $encabezado_tabla = ["CUENTA", "NOMBRE COMPLETO", "FECHA", "SALDO", "TASA", "TIPO"];
        # El último argumento es por defecto A1 pero lo pongo para que se explique mejor
        $hojaReporte->fromArray($encabezado_tabla, null, 'A13')->getStyle('A13:E13')->getFont()->setName($fuente)->setBold(true);


        $total_saldo = 0;
        $conta_mujeres = 0;
        $conta_hombres = 0;
        $total_mujeres = 0;
        $total_hombres = 0;
        while ($rowdata = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
            $bd_ccodaho = utf8_encode($rowdata["ccodaho"]);
            $bd_shortname = strtoupper(utf8_encode($rowdata["short_name"]));
            $bd_fecha = $fecha_final;
            $bd_saldo = strtoupper(utf8_encode($rowdata["saldo"]));
            $bd_tasa = strtoupper(utf8_encode($rowdata["tasa"]));
            $bd_genero = strtoupper(utf8_encode($rowdata["genero"]));
            $bd_tipo = strtoupper(utf8_encode($rowdata["nombre"]));
            $saldo_formateado = number_format($bd_saldo, 2, '.', '');
            $tasa_formateado = number_format($bd_tasa, 2, '.', '');

            $total_saldo = $total_saldo + $bd_saldo;
            if ($bd_genero == "M") {
                $total_hombres = $total_hombres + $bd_saldo;
                $conta_hombres++;
            } else if ($bd_genero == "F") {
                $total_mujeres = $total_mujeres + $bd_saldo;
                $conta_mujeres++;
            }

            // colocar formato de moneda
            $hojaReporte->getStyle('D' . $linea . ':E' . $linea)->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);
            //se insertan los datos
            $hojaReporte->setCellValueByColumnAndRow(1, $linea, $bd_ccodaho);
            $hojaReporte->setCellValueByColumnAndRow(2, $linea, $bd_shortname);
            $hojaReporte->setCellValueByColumnAndRow(3, $linea, $bd_fecha);
            $hojaReporte->setCellValueByColumnAndRow(4, $linea, $saldo_formateado);
            $hojaReporte->setCellValueByColumnAndRow(5, $linea, $tasa_formateado);
            $hojaReporte->setCellValueByColumnAndRow(6, $linea, $bd_tipo);
            $hojaReporte->getStyle("A" . $linea . ":F" . $linea)->getFont()->setName($fuente);

            $linea++;
        }
        $hojaReporte->getColumnDimension('A')->setAutoSize(TRUE);
        $hojaReporte->getColumnDimension('B')->setAutoSize(TRUE);
        $hojaReporte->getColumnDimension('C')->setAutoSize(TRUE);
        $hojaReporte->getColumnDimension('D')->setAutoSize(TRUE);
        $hojaReporte->getColumnDimension('E')->setAutoSize(TRUE);
        $hojaReporte->getColumnDimension('F')->setAutoSize(TRUE);

        //TEXTO TOTAL
        $hojaReporte->getStyle('A' . $linea . ':F' . $linea)->getFont()->setName($fuente);
        $hojaReporte->getStyle('F' . $linea)->getFont()->setBold(true);
        $hojaReporte->getStyle('F' . $linea)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        //merge de totales
        $hojaReporte->setCellValueByColumnAndRow(4, $linea, $total_saldo);
        $hojaReporte->getStyle('F' . $linea)
            ->getNumberFormat()
            ->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);

        //RESUMEN DE SALDO
        //colocar letra courier a todos
        $hojaReporte->getStyle('A' . ($linea + 2) . ':F' . ($linea + 2))->getFont()->setName($fuente);
        $hojaReporte->getStyle('A' . ($linea + 3) . ':F' . ($linea + 3))->getFont()->setName($fuente);
        $hojaReporte->getStyle('A' . ($linea + 4) . ':F' . ($linea + 4))->getFont()->setName($fuente);
        //encabezado
        $hojaReporte->getStyle('B' . ($linea + 2) . ':C' . ($linea + 2))->getFont()->setBold(true);
        $hojaReporte->getStyle('B' . ($linea + 2) . ':C' . ($linea + 2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hojaReporte->setCellValueByColumnAndRow(2, ($linea + 2), 'NUMERO');

        $hojaReporte->getStyle('B' . ($linea + 2) . ':C' . ($linea + 2))->getFont()->setBold(true);
        $hojaReporte->getStyle('B' . ($linea + 2) . ':C' . ($linea + 2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hojaReporte->setCellValueByColumnAndRow(3, ($linea + 2), 'SALDO');
        //resumen mujeres
        $hojaReporte->getStyle('A' . ($linea + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $hojaReporte->getStyle('B' . ($linea + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hojaReporte->getStyle('C' . ($linea + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $hojaReporte->getStyle('C' . ($linea + 3))
            ->getNumberFormat()
            ->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);
        $hojaReporte->setCellValueByColumnAndRow(1, ($linea + 3), 'MUJERES: ');
        $hojaReporte->setCellValueByColumnAndRow(2, ($linea + 3), $conta_mujeres);
        $hojaReporte->setCellValueByColumnAndRow(3, ($linea + 3), $total_mujeres);
        //resumen hombres
        $hojaReporte->getStyle('A' . ($linea + 4))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $hojaReporte->getStyle('B' . ($linea + 4))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hojaReporte->getStyle('C' . ($linea + 4))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $hojaReporte->getStyle('C' . ($linea + 4))
            ->getNumberFormat()
            ->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);
        $hojaReporte->setCellValueByColumnAndRow(1, ($linea + 4), 'HOMBRES: ');
        $hojaReporte->setCellValueByColumnAndRow(2, ($linea + 4), $conta_hombres);
        $hojaReporte->setCellValueByColumnAndRow(3, ($linea + 4), $total_hombres);

        ob_start();
        $writer = IOFactory::createWriter($spread, 'Xlsx');
        $writer->save("php://output");
        $xlsData = ob_get_contents();
        ob_end_clean();
        //envio de repuesta a ajax para descargarlos

        $opResult = array(
            'status' => 1,
            'data' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData),
            'mensaje' => 'Reporte generado correctamente',
            'namefile' => "Saldos de cuentas",
            'tipo' => "xlsx"
        );
        mysqli_close($conexion);
        echo json_encode($opResult);
        break;
}
