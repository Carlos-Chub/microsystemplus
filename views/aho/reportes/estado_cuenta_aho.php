<?php
session_start();
include '../../../includes/BD_con/db_con.php';
include '../../../src/funcphp/func_gen.php';
require '../../../fpdf/fpdf.php';
require "../../../vendor/autoload.php";
date_default_timezone_set('America/Guatemala');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
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
$datos = $_POST["datosval"];
$inputs = $datos[0];
$selects = $datos[1];
$radios = $datos[2];
$archivo = $datos[3];
$tipo = $_POST["tipo"];

// $inputs = $_POST["inputs"];
// $archivos = $_POST["archivo"];
// $radioss = $_POST["radios"];
// $radiosn = $_POST["radiosn"];
// $tipo_doc = $_POST["id"];

//validar si ingreso un cuenta de aportacion
if ($inputs[0] == "" && $inputs[1] == "") {
    echo json_encode(["Debe cargar una cuenta de ahorro", '0']);
    return;
}

//validar si la cuenta de ahorro existe
$datoscli = mysqli_query($conexion, "SELECT * FROM `ahomcta` WHERE `ccodaho`='$inputs[0]'");
$bandera = true;
while ($da = mysqli_fetch_array($datoscli, MYSQLI_ASSOC)) {
    $bandera = false;
}
if ($bandera) {
    echo json_encode(["Debe cargar una cuenta de ahorro válida", '0']);
    return;
}

//validaciones en cuanto a fechas
$fecha_actual = (date("Y-m-d"));
$fecha_1 = ($inputs[2]);
$fecha_2 = ($inputs[3]);

if ($radios[0] == "2") {
    //validacion de fechas
    if ($fecha_2 > $fecha_actual) {
        echo json_encode(["La fecha de hasta no puede ser mayor a la fecha de hoy", '0']);
        return;
    }
    if ($fecha_1 > $fecha_2) {
        echo json_encode(["La fecha inicial no puede ser mayor a la fecha final", '0']);
        return;
    }
}

if ($radios[0] == "1") {
    //validacion de fechas
    $fecha_actual = (date("Y-m-d"));
    if ($fecha_2 != $fecha_actual && $fecha_1 != $fecha_actual) {
        echo json_encode(["Error en su solicitud", '0']);
        return;
    }
}
$formato = $tipo;

//se crea el array y se reciben los datos del post
$datos = array();
// $datos = $_POST["data"];

//se asignan variables locales a los datos recibidos
$ccodaho = $inputs[0];
$r_fecha = $radios[0];
$fechainicial = $fecha_1;
$fechafinal = $fecha_2;
$usuario = $archivo[0];
$oficina = $archivo[1];
$tip_report = $formato;

$fuente = "Courier";
$tamanioFuente = 9;
$tamanioTitulo = 11;
$tamanio_linea = 4; //altura de la linea/celda
$ancho_linea = 30; //anchura de la linea/celda
$espacio_blanco = 10; //tamaño del espacio en blanco entre cada celda
$ancho_linea2 = 20; //anchura de la linea/celda
$espacio_blanco2 = 4; //tamaño del espacio en blanco entre cada celda

//CONSULTA A LA BASE DE DATOS
$consulta = "SELECT * FROM ahommov WHERE cestado!=2 AND ccodaho = " . $ccodaho;
$consulta2 = "CALL obtener_saldo_ant_fecha_aho('$ccodaho', '$fechainicial')";
// $data3 = "";
$data = "";

if ($r_fecha == '2') {
    //se hace la consulta del saldo anterior
    $data3 = mysqli_query($conexion, $consulta2);
    mysqli_next_result($conexion);

    //se hace la consulta entre fechas
    $consulta .= " AND (ahommov.dfecope BETWEEN '$fechainicial' AND '$fechafinal')";
}
$consulta .= " ORDER BY ahommov.correlativo ASC";

$data = mysqli_query($conexion, $consulta);


switch ($tip_report) {
    case "pdf": {
            //clase para el header y footer de fdpf
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
                public $ccodaho;
                public $r_fecha;
                public $fechainicial;
                public $fechafinal;

                public function __construct($conexion, $institucion, $pathlogo, $pathlogoins, $oficina, $direccion, $email, $telefono, $nit, $user, $ccodaho, $r_fecha, $fechainicial, $fechafinal)
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
                    $this->ccodaho = $ccodaho;
                    $this->r_fecha = $r_fecha;
                    $this->fechainicial = $fechainicial;
                    $this->fechafinal = $fechafinal;
                }

                // Cabecera de página
                function Header()
                {
                    //------------------datos de la cuenta en aprcta
                    $libreta = 0;
                    $codcli = "";
                    $nit = "";
                    $datalib = mysqli_query($this->conexion, "SELECT `nlibreta`,`ccodcli`,`num_nit` FROM `ahomcta` WHERE `ccodaho`=$this->ccodaho");
                    while ($rowlib = mysqli_fetch_array($datalib, MYSQLI_ASSOC)) {
                        $libreta = utf8_encode($rowlib["nlibreta"]);
                        $codcli = utf8_encode($rowlib["ccodcli"]);
                        $nit = utf8_encode($rowlib["num_nit"]);
                    }

                    //---------------------datos del cliente
                    $depadom = "00";
                    $munidom = "0000";
                    $nombre = "";
                    $identificacion = "";
                    $direccion = "";
                    $noNit = "";
                    $tel1 = "";
                    $genero = "";
                    $estado_civil = "";

                    $fuente = "Courier";
                    $tamanioTitulo = 11;
                    $tamanio_linea = 4; //altura de la linea/celda
                    $ancho_linea = 30; //anchura de la linea/celda
                    $ancho_linea2 = 20; //anchura de la linea/celda

                    $sql = mysqli_query($this->conexion, "SELECT * FROM tb_cliente WHERE idcod_cliente='$codcli'");
                    while ($row = mysqli_fetch_array($sql, MYSQLI_ASSOC)) {
                        $nombre = $row['compl_name'];
                        $rutaFoto = $row['url_img'];
                        if ($rutaFoto == 'url' || $rutaFoto == '') {
                            $rutaFoto = '../../../includes/img/fotoClienteDefault.png';
                        } else {
                            $rutaFoto = "../../../../../" . $rutaFoto;
                        }
                        $fecha = $row['date_birth'];
                        $fechaNacimiento = date("d-m-Y", strtotime($fecha)); //formatear fecha en dia/mes/año
                        $genero = $row['genero'];
                        $estado_civil = $row['estado_civil'];
                        $identificacion = $row['no_identifica'];
                        $direccion = $row['Direccion'];
                        $noNit = $row['no_tributaria'];
                        $email = $row['email'];
                        $depadom = $row['depa_reside'];
                        $munidom = $row['muni_reside'];
                        $tel1 = $row['tel_no1'];
                        $tel2 = $row['tel_no2'];
                    }

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
                    $this->Cell(0, 3, 'NIT: ' . $this->nit, 'B', 1, 'C');
                    // Salto de línea
                    $this->Ln(15);

                    //SECCION DE DATOS DEL CLIENTE
                    $this->SetY(30);
                    //NUMERO DE CUENTA
                    $this->SetFont($fuente, 'B', $tamanioTitulo);
                    $this->Cell(0, 10, 'Cuenta de ahorros:  ' . $this->ccodaho, 0, 1);
                    //TITULO DE REPORTE
                    $this->SetFillColor(204, 229, 255);
                    $this->Cell(0, 5, 'HISTORIAL DE CUENTA DE AHORROS', 0, 1, 'C', true);
                    $texto_fecha = "TODAS LAS FECHAS";
                    if ($this->r_fecha == '2') {
                        $texto_fecha = "DE " . $this->fechainicial . " HASTA " . $this->fechafinal;
                    }
                    $this->Cell(0, 5, $texto_fecha, 0, 1, 'C', true);
                    $this->Ln(3);
                    //Fuente
                    $this->SetFont($fuente, '', 9);
                    //DATOS DEL CLIENTE
                    $this->Cell(40, 6, 'Nombre: ', 0, 0);
                    $this->Cell(40, 6, $nombre, 0, 1);
                    $this->Cell(40, 6, 'Identificacion: ', 0, 0);
                    $this->Cell(50, 6, $identificacion, 0, 0);
                    $this->Cell(30, 6, 'NIT: ', 0, 0);
                    $this->Cell(40, 6,  $noNit, 0, 1);
                    $this->Cell(40, 6, 'Domicilio: ', 0, 0);
                    $this->Cell(40, 6, municipio($munidom) . ', ' . departamento($depadom), 0, 1);
                    $this->Cell(40, 6, 'Direccion: ', 0, 0);
                    $this->Cell(40, 6, $direccion, 0, 1);
                    $this->Cell(40, 6, 'Telefono: ', 0, 0);
                    $this->Cell(40, 6,  $tel1, 0, 1);
                    $this->Cell(40, 6, 'Sexo: ', 0, 0);
                    $this->Cell(50, 6, $genero, 0, 0);
                    $this->Cell(30, 6, 'Estado civil: ', 0, 0);
                    $this->Cell(40, 6, $estado_civil, 0, 1);
                    $this->Ln(3);

                    //Color de encabezado de lista
                    $this->SetFillColor(555, 255, 204);
                    //TITULOS DE ENCABEZADO DE TABLA
                    $this->CellFit($ancho_linea2, $tamanio_linea + 1, ' Fecha', 'B', 0, 'C', 0, '', 1, 0);
                    $this->CellFit($ancho_linea2 / 2, $tamanio_linea + 1, 'Num', 'B', 0, 'C', 0, '', 1, 0); //
                    $this->CellFit($ancho_linea2 / 3, $tamanio_linea + 1, 'D/R', 'B', 0, 'C', 0, '', 1, 0);
                    $this->CellFit($ancho_linea2, $tamanio_linea + 1, 'Doc', 'B', 0, 'C', 0, '', 1, 0);
                    $this->CellFit($ancho_linea2 / 2, $tamanio_linea + 1, 'Tipo', 'B', 0, 'C', 0, '', 1, 0);
                    $this->CellFit($ancho_linea, $tamanio_linea + 1, 'Creditos', 'B', 0, 'C', 0, '', 1, 0); //
                    $this->CellFit($ancho_linea, $tamanio_linea + 1, 'Debitos', 'B', 0, 'C', 0, '', 1, 0);
                    $this->CellFit($ancho_linea2 - 3, $tamanio_linea + 1, 'Cheque', 'B', 0, 'C', 0, '', 1, 0);
                    $this->CellFit($ancho_linea2 - 3, $tamanio_linea + 1, 'Partida', 'B', 0, 'C', 0, '', 1, 0);
                    $this->CellFit($ancho_linea, $tamanio_linea + 1, 'Saldo', 'B', 0, 'C', 0, '', 1, 0);
                    $this->Ln(6);
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

            // Creación del objeto de la clase heredada
            $pdf = new PDF($conexion, $institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins, $usuario, $ccodaho, $r_fecha, $fechainicial, $fechafinal);

            $pdf->AliasNbPages();
            $pdf->AddPage();

            $ancho_linea = 30;
            $ancho_linea2 = 20;
            $saldo = 0;

            if ($r_fecha == '2') {
                while ($ant = mysqli_fetch_array($data3, MYSQLI_ASSOC)) {
                    $fecha_ant = date("d-m-Y", strtotime($ant['fecha_anterior']));
                    $total_ant = ($ant['total']);

                    //colocar datos en reporte
                    $pdf->CellFit($ancho_linea2, $tamanio_linea, $fecha_ant, 0, 0, 'C', 0, '', 1, 0); //fecha
                    $pdf->CellFit($ancho_linea2 / 2, $tamanio_linea, ' ', 0, 0, 'C', 0, '', 1, 0); //num
                    $pdf->CellFit(($ancho_linea2 / 3), $tamanio_linea, ' ', 0, 0, 'C', 0, '', 1, 0); //D/R
                    $pdf->CellFit($ancho_linea2, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0); //Doc
                    $pdf->CellFit($ancho_linea2 / 2, $tamanio_linea, ' ', 0, 0, 'C', 0, '', 1, 0); //Tipo
                    $pdf->CellFit($ancho_linea, $tamanio_linea, ' ', 0, 0, 'R', 0, '', 1, 0); // 
                    $pdf->CellFit($ancho_linea, $tamanio_linea, ' ', 0, 0, 'R', 0, '', 1, 0); //
                    $pdf->CellFit($ancho_linea2 - 3, $tamanio_linea, ' ', 0, 0, 'C', 0, '', 1, 0);
                    $pdf->CellFit($ancho_linea2 - 3, $tamanio_linea, ' ', 0, 0, 'C', 0, '', 1, 0);
                    $pdf->CellFit($ancho_linea, $tamanio_linea, number_format($total_ant, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
                    $saldo = $saldo + $total_ant;
                    $pdf->Ln(4);
                }
            }
            // $pdf->Cell($ancho_linea2 / 2, $tamanio_linea, $data3, 0, 0, 'C'); //num

            while ($mov = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                $fech = utf8_encode($mov['dfecope']);
                $fecha = date("d-m-Y", strtotime($fech));
                $num = utf8_encode($mov['correlativo']);
                $tipope = utf8_encode($mov['ctipope']);
                $numdoc = utf8_encode($mov['cnumdoc']);
                $tipdoc = utf8_encode($mov['ctipdoc']);
                $ncheque = utf8_encode($mov['nrochq']);
                $tipchq = utf8_encode($mov['tipchq']);
                $partida = utf8_encode($mov['numpartida']);
                $monto = utf8_encode($mov['monto']);

                //IMPRESION DE LISTA DE TRANSACCIONES DE CUENTA
                $pdf->CellFit($ancho_linea2, $tamanio_linea, $fecha, 0, 0, 'C', 0, '', 1, 0); //fecha
                $pdf->CellFit($ancho_linea2 / 2, $tamanio_linea, $num, 0, 0, 'C', 0, '', 1, 0); //num
                $pdf->CellFit(($ancho_linea2 / 3), $tamanio_linea, $tipope, 0, 0, 'C', 0, '', 1, 0); //D/R
                $pdf->CellFit($ancho_linea2, $tamanio_linea, $numdoc, 0, 0, 'L', 0, '', 1, 0); //Doc
                $pdf->CellFit($ancho_linea2 / 2, $tamanio_linea, $tipdoc, 0, 0, 'C', 0, '', 1, 0); //Tipo

                $pdf->CellFit($ancho_linea, $tamanio_linea, ($tipope == "D") ? 'Q ' . number_format($monto, 2, '.', ',') : ' ', 0, 0, 'R', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea, $tamanio_linea, ($tipope == "R") ? 'Q ' . number_format($monto, 2, '.', ',') : ' ', 0, 0, 'R', 0, '', 1, 0); // 
                ($tipope == "D") ? $saldo = $saldo + $monto : $saldo;
                ($tipope == "R") ? $saldo = $saldo - $monto : $saldo;

                $pdf->CellFit($ancho_linea2 - 3, $tamanio_linea, ($ncheque) ? $ncheque : ' ', 0, 0, 'C', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea2 - 3, $tamanio_linea, ($partida) ? $partida : ' ', 0, 0, 'C', 0, '', 1, 0);
                $pdf->CellFit($ancho_linea, $tamanio_linea, 'Q ' . number_format($saldo, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
                $pdf->Ln(4);
            }
            //fin ingresos
            //forma de migrar el archivo
            ob_start();
            $pdf->Output();
            $pdfData = ob_get_contents();
            ob_end_clean();
            $opResult = array(
                'status' => 1,
                'mensaje' => 'Reporte generado correctamente',
                'namefile' => "Estado de cuenta",
                'tipo' => "pdf",
                'data' => "data:application/pdf;base64," . base64_encode($pdfData)
            );
            mysqli_close($conexion);
            echo json_encode($opResult);
        }
        break;
    case "xlsx": {
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
            $hoy = date("Y-m-d H:i:s");
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
            $hojaReporte->setCellValue("A1", iconv('UTF-8', 'ISO-8859-1', $hoy));
            $hojaReporte->setCellValue("A2", $usuario);
            // //informacion de la agencia o cooperativa
            $hojaReporte->setCellValue("A4", iconv('UTF-8', 'ISO-8859-1', $institucion));
            $hojaReporte->setCellValue("A5", $direccionins);
            $hojaReporte->setCellValue("A6", "Email: " . $emailins);
            $hojaReporte->setCellValue("A7", "Tel: " . $telefonosins);
            $hojaReporte->setCellValue("A8", "NIT: " . $nitins);

            //hacer pequeño las letras de la fecha, definir arial como tipo de letra
            $hojaReporte->getStyle("A1:J1")->getFont()->setSize($tamanioFecha)->setName($fuente_encabezado);
            $hojaReporte->getStyle("A2:J2")->getFont()->setSize($tamanioFecha)->setName($fuente_encabezado);
            //centrar el texto de la fecha
            $hojaReporte->getStyle("A1:J1")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $hojaReporte->getStyle("A2:J2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // //hacer grande las letras del encabezado
            $hojaReporte->getStyle("A4:J4")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
            $hojaReporte->getStyle("A5:J5")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
            $hojaReporte->getStyle("A6:J6")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
            $hojaReporte->getStyle("A7:J7")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
            $hojaReporte->getStyle("A8:J8")->getFont()->setSize($tamanioEncabezado)->setName($fuente_encabezado);
            //centrar el texto del encabezado
            $hojaReporte->getStyle("A4:J4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $hojaReporte->getStyle("A5:J5")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $hojaReporte->getStyle("A6:J6")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $hojaReporte->getStyle("A7:J7")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $hojaReporte->getStyle("A8:J8")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            //hacer pequeño las letras del encabezado de titulo
            $hojaReporte->getStyle("A10:J10")->getFont()->setSize($tamanioTabla)->setName($fuente);
            $hojaReporte->getStyle("A11:J11")->getFont()->setSize($tamanioTabla)->setName($fuente);
            $hojaReporte->getStyle("A12:J12")->getFont()->setSize($tamanioTabla)->setName($fuente);

            //centrar los encabezado de la tabla
            $hojaReporte->getStyle("A10:J10")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $hojaReporte->getStyle("A11:J11")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $hojaReporte->getStyle("A12:J12")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            //titulo del reporte

            $hojaReporte->setCellValue("A10", "ESTADO DE CUENTA ");

            // //combinacion de celdas
            $hojaReporte->mergeCells('A1:J1');
            $hojaReporte->mergeCells('A2:J2');
            $hojaReporte->mergeCells('A4:J4');
            $hojaReporte->mergeCells('A5:J5');
            $hojaReporte->mergeCells('A6:J6');
            $hojaReporte->mergeCells('A7:J7');
            $hojaReporte->mergeCells('A8:J8');
            $hojaReporte->mergeCells('A10:J10');
            $hojaReporte->mergeCells('A11:J11');

            # Escribir encabezado de la tabla
            $encabezado_tabla = ["FECHA", "NO.", "D/R", "DOC", "TIPO", "CREDITOS", "DEBITOS", "CHEQUE", "PARTIDA", "SALDO"];
            $hojaReporte->fromArray($encabezado_tabla, null, 'A13')->getStyle('A13:E13')->getFont()->setName($fuente)->setBold(true);
            $saldo = 0;
            if ($r_fecha == '2') {
                while ($ant = mysqli_fetch_array($data3, MYSQLI_ASSOC)) {
                    $fecha_ant = date("d-m-Y", strtotime($ant['fecha_anterior']));
                    $total_ant = ($ant['total']);

                    //colocar datos en reporte
                    $hojaReporte->setCellValueByColumnAndRow(1, $linea, $fecha_ant);
                    $hojaReporte->setCellValueByColumnAndRow(10, $linea, $total_ant);
                    $hojaReporte->getStyle('F' . $linea . ':J' . $linea)->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);
                    $saldo = $saldo + $total_ant;
                }
            }
            $linea++;
            $ancho_linea = 30;
            $ancho_linea2 = 20;

            while ($mov = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                $fech = utf8_encode($mov['dfecope']);
                $fecha = date("d-m-Y", strtotime($fech));
                $num = utf8_encode($mov['correlativo']);
                $tipope = utf8_encode($mov['ctipope']);
                $numdoc = utf8_encode($mov['cnumdoc']);
                $tipdoc = utf8_encode($mov['ctipdoc']);
                $ncheque = utf8_encode($mov['nrochq']);
                $tipchq = utf8_encode($mov['tipchq']);
                $partida = utf8_encode($mov['numpartida']);
                $monto = utf8_encode($mov['monto']);

                // colocar formato de moneda
                $hojaReporte->getStyle('F' . $linea . ':J' . $linea)->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_GT_SIMPLE);

                //se insertan los datos
                $hojaReporte->setCellValueByColumnAndRow(1, $linea, $fech);
                $hojaReporte->setCellValueByColumnAndRow(2, $linea, $num);
                $hojaReporte->setCellValueByColumnAndRow(3, $linea, $tipope);
                $hojaReporte->setCellValueByColumnAndRow(4, $linea, $numdoc);
                $hojaReporte->setCellValueByColumnAndRow(5, $linea, $tipdoc);
                ($tipope == "D") ? $saldo = $saldo + $monto : $saldo;
                ($tipope == "R") ? $saldo = $saldo - $monto : $saldo;
                $hojaReporte->setCellValueByColumnAndRow(6, $linea, $saldo);

                if ($tipope == "D") {
                    $hojaReporte->setCellValueByColumnAndRow(6, $linea, $monto);
                } elseif ($tipope == "R") {
                    $hojaReporte->setCellValueByColumnAndRow(7, $linea, $monto);
                }

                $hojaReporte->setCellValueByColumnAndRow(10, $linea, $saldo);
                $hojaReporte->getStyle("A" . $linea . ":F" . $linea)->getFont()->setName($fuente);
                $linea++;
                $hojaReporte->getColumnDimension('A')->setWidth(15);
                $hojaReporte->getColumnDimension('B')->setWidth(7);
                $hojaReporte->getColumnDimension('C')->setWidth(7);
                $hojaReporte->getColumnDimension('D')->setWidth(15);
                $hojaReporte->getColumnDimension('E')->setWidth(7);
                $hojaReporte->getColumnDimension('F')->setWidth(15);
                $hojaReporte->getColumnDimension('G')->setWidth(15);
                $hojaReporte->getColumnDimension('I')->setWidth(10);
                $hojaReporte->getColumnDimension('H')->setWidth(10);
                $hojaReporte->getColumnDimension('J')->setWidth(15);
            }

            ob_start();
            $writer = IOFactory::createWriter($spread, 'Xlsx');
            $writer->save("php://output");
            $xlsData = ob_get_contents();
            ob_end_clean();
            //envio de repuesta a ajax para descargarlos
            $opResult = array(
                'status' => 1,
                'mensaje' => 'Reporte generado correctamente',
                'namefile' => "Estado de cuenta",
                'tipo' => "vnd.ms-excel",
                'data' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
            );

            mysqli_close($conexion);
            echo json_encode($opResult);
        }
        break;
}
