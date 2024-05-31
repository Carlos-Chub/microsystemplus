<?php
session_start();
include '../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');
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
$estado = $selects[0][0];
$radios = $datos[2];
$tipo = $_POST["tipo"];
$tipoconsulta = 0;

// validarlaFecha//if($inputs[0]!= $hoy){//echojson_encode(['status'=>0,'mensaje'=>'Lafechainicialdebeser igualaladehoy']);//return;// }

if ($inputs[1] < $inputs[0]) {
    echo json_encode(['status' => 0, 'mensaje' => 'La fecha final no debe ser menor que la fecha inicial']);
    return;
}
//VALIDACIONES DE ESTADO Y AGENCIA
if ($selects[0] == '0') {
    echo json_encode(['status' => 0, 'mensaje' => 'Debe seleccionar un estado de crédito' . $selects[0]]);
    return;
}
// if ($selects[1] == '0') {
//   echo json_encode(['status' => 0, 'mensaje' => 'Debe seleccionar una agencia']);
//   return;
// }
$codusu = '';
// PRUEBA DE FILTRO negroy
if ($inputs[2] == "CRE_desembol_Filtro") { //CRE_desembol_Filtro
    $agencia = $selects[1];
    $codusu = $inputs[3];
    $estado = $selects[0];
    // SE DEVERA DE HACER EL FILTRO POR USUARIO O SABER SI SOLO VA MOSTRAR LOS DATOS DE LA CONSULTA TRAIDA
    $query2 = "SELECT COUNT(CCODCTA) FROM cremcre_meta 
	WHERE CodAnal=" . $codusu . " AND CODAgencia='" . $agencia . "' AND Cestado ='" . $estado . "' 
	AND (DfecAnal BETWEEN '" . $inputs[0] . "' AND '" . $inputs[1] . "') ";
    $agencia = mysqli_query($conexion, $query2) or die(mysqli_error($conexion));
    // Obtén el resultado de la consulta
    $resultado = mysqli_fetch_array($agencia);
    // Verifica si el resultado es mayor que cero
    if ($resultado[0] > 0) {
        // Hay datos, establece la bandera a true y muestra un mensaje
        $codusu = "AND cm.CodAnal=" . $codusu;
        //echo json_encode(["status" => 0, 'mensaje' => 'HAY DATOS']);  return;
    } else {
        // No hay datos, establece la bandera a false y muestra un mensaje
        $codusu = '';
        echo json_encode(["status" => 0, 'mensaje' => 'NO HAY DATOS']);
        return;
    }
}

// SI ES DE CRE
$filtroentidad = ($radios[0] == "ALL") ? "" : " AND cm.TipoEnti='" . $radios[0] . "' ";
$filtroagencia = ($selects[1] == "0") ? "" : " AND ag.id_agencia='" . $selects[1] . "' ";
$filtrostatus = ($selects[0] == "FG") ? " AND (cm.Cestado= 'G' OR cm.Cestado='F') " : " AND cm.Cestado='" . $selects[0] . "' ";

//CONSULTA PRINCIPAL

$cestado = reset($selects);
$DFec = 0;

switch ($cestado) {
    case 'D':
        $DFec = 'DFecAnal'; //analisis
        break;
    case 'E':
        $DFec = 'DFecApr'; //aprobacion 
        break;
    case 'F':
        $DFec = 'DFecDsbls'; //desembolso
        break;
    case 'A':
        $DFec = 'DfecSol'; //solicitud
        break;
    case 'G':
        $DFec = 'fecha_operacion';
        break;
    case 'L':
        $DFec = 'fecha_operacion';
        break;
    case 'X':
        $DFec = 'fecha_operacion';
        break;
    case 'FG':
        $DFec = 'DFecDsbls';
        break;
}
//echo json_encode(['status' => 0, 'mensaje' => $DFec]);
//return;

$consulta = "SELECT 
    cm.TipoEnti,us.puesto, 
    ag.id_agencia,
    ag.cod_agenc,
    cm.CCODCTA AS cuenta,
    cm.CodCli AS codigocliente,  
    cl.short_name AS nombre,
    cm.Cestado AS estado,
    cm.DFecSol AS fecsolicitud,
    IFNULL(cm.DFecDsbls,'-') AS fecdesembolsado,
    cm.DFecVen AS fecvencimiento,
    cm.MontoSol AS montosoli,
    cm.MonSug AS montoaprobado,
    cm.NCapDes AS montodesembolsado,
    cm.TipDocDes AS tipo,
    cm.DFecDsbls AS fecdes,
    IFNULL((SELECT SUM(OTR) from CREDKAR where CCODCTA=cm.CCODCTA AND CTIPPAG='D'),0) gastos,
    IFNULL((SELECT NombreGrupo from tb_grupo where id_grupos=cm.CCodGrupo),' ') NombreGrupo,
    IFNULL((SELECT f.descripcion FROM ctb_fuente_fondos f INNER JOIN cre_productos c ON c.id_fondo=f.id WHERE c.id=cm.CCODPRD),' - ') fondesc,
    IFNULL((SELECT f.id FROM ctb_fuente_fondos f INNER JOIN cre_productos c ON c.id_fondo=f.id WHERE c.id=cm.CCODPRD),' - ') fondoid,
    IFNULL((SELECT descripcion FROM `clhpzzvb_bd_general_coopera`.`tb_cre_periodos` WHERE cod_msplus=cm.NtipPerC),' - ') frecuencia,
    cm.CCodGrupo id_grupos,
    cm.noPeriodo numcuotas,
    (CONCAT(us.nombre,' ',us.apellido)) AS responsable
        FROM cremcre_meta cm
        INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
        INNER JOIN tb_usuario us ON cm.CodAnal=us.id_usu
        INNER JOIN tb_agencia ag ON cm.CODAgencia=ag.cod_agenc
        INNER JOIN cre_productos prod ON prod.id=cm.CCODPRD
        INNER JOIN ctb_fuente_fondos ff ON ff.id=prod.id_fondo
        WHERE (DATE($DFec) BETWEEN '" . $inputs[0] . "' AND '" . $inputs[1] . "') " . $filtrostatus . " " . $filtroagencia . " " . $filtroentidad . " 
		" . $codusu . "
        ORDER BY ff.id,cm.CCodGrupo,cm.DFecDsbls,cm.CCODCTA";
//texto de estado/* echo json_encode(['status' => 0, 'mensaje' => $consulta]); return;  */
// echo json_encode(["status" => 0, 'mensaje' => $consulta]);return;

// DfecAnal
$texto_reporte = "REPORTE DE CRÉDITOS DEL PERIODO " .  date("d-m-Y", strtotime($inputs[0])) . " AL " . date("d-m-Y", strtotime($inputs[1])) . " ";
$queryest = "SELECT EstadoCredito AS est FROM tb_estadocredito WHERE id_EstadoCredito='" . $selects[0] . "' ";
$estado = mysqli_query($general, $queryest);
$texto_reporte .= " COLOCADOS";
$nomestado = " ";
while ($fil = mysqli_fetch_array($estado)) {
    $nomestado = strtoupper($fil["est"]);
    if (strtoupper($fil["est"]) == 'VIGENTE') {
        $nomestado = "DESEMBOLSADO";
    }
    $texto_reporte .=" CON ESTADO DE ". $nomestado;
}


//consultar la agencia
$nom_agencia = "";
$agencia = mysqli_query($conexion, "SELECT * FROM tb_agencia WHERE id_agencia='" . $selects[1] . "' ");
while ($fil = mysqli_fetch_array($agencia)) {
    $nom_agencia = strtoupper($fil["nom_agencia"]);
    $cod_agenc = strtoupper($fil["cod_agenc"]);
}
$texto_reporte .= ($selects[1] == "0") ? " CONSOLIDADO " : " DE LA AGENCIA: " . $nom_agencia;
//LECTURA DE DATOS
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
    case 'xlsx':
        printxls($data, [$texto_reporte, $_SESSION['id'], $hoy, $conexion, $nomestado]);
        break;
    case 'pdf':

        printpdf($data, [$texto_reporte, $_SESSION['id'], $hoy, $conexion, $nomestado], $info);
        break;
}

//FUNCION PARA GENERAR EL REPORTE EN PDF
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
            $ancho_linea = 25; //anchura de la linea/celda
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
            $this->Cell(0, 5,  utf8_decode($this->titulo), 0, 1, 'C', true);

            $this->Ln(5);
            //Fuente
            $this->SetFont($fuente, '', 8);
            //encabezado de tabla
            // $this->CellFit($ancho_linea + 130, $tamanio_linea + 1, " ", 0, 0, 'C', 0, '', 1, 0);
            // $this->CellFit($ancho_linea + 30, $tamanio_linea + 1, "RECARGOS", 1, 0, 'C', 0, '', 1, 0);
            // $this->CellFit($ancho_linea + 42, $tamanio_linea + 1, " ", 0, 0, 'C', 0, '', 1, 0);
            // $this->Ln(5);
            $this->CellFit($ancho_linea, $tamanio_linea + 1, utf8_decode("CRÉDITO"), 'B', 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea * 2, $tamanio_linea + 1, 'NOMBRE CLIENTE', 'B', 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea, $tamanio_linea + 1, 'SOLICITADO', 'B', 0, 'R', 0, '', 1, 0);
            $this->CellFit($ancho_linea, $tamanio_linea + 1, 'APROBADO', 'B', 0, 'R', 0, '', 1, 0);
            $this->CellFit($ancho_linea, $tamanio_linea + 1, 'DESEMBOLSADO', 'B', 0, 'R', 0, '', 1, 0);
            $this->CellFit($ancho_linea, $tamanio_linea + 1, 'GASTOS', 'B', 0, 'R', 0, '', 1, 0);
            $this->CellFit($ancho_linea, $tamanio_linea + 1, 'TIP. DOC.', 'B', 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea, $tamanio_linea + 1, 'F.SOLICITUD', 'B', 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea, $tamanio_linea + 1, 'F.DESEMBOLSO', 'B', 0, 'C', 0, '', 1, 0);
            $this->CellFit($ancho_linea + 5, $tamanio_linea + 1, 'RESPONSABLE', 'B', 0, 'L', 0, '', 1, 0);
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
    $ancho_linea = 25;

    // Creación del objeto de la clase heredada
    $pdf = new PDF($oficina, $institucion, $direccionins, $emailins, $telefonosins, $nitins, $rutalogomicro, $rutalogoins, $otros[0], $otros[1], $otros[3]);
    $pdf->AliasNbPages();
    $pdf->AddPage();

    //AQUI COLOCAR TODOS LO DATOS
    $sumasoli = 0;
    $sumamontoapro = 0;
    $sumamontodes = 0;
    $sumaacobrar = 0;
    $fila = 0;
    $totalcl = 0;
    $auxfondo = null;
    $auxgrupo = -1;
    $resumen = false;
    while ($fila < count($datos)) {
        $cuenta = $datos[$fila]["cuenta"];
        $nombre = strtoupper(utf8_decode($datos[$fila]["nombre"]));
        $montosolicitado = $datos[$fila]["montosoli"];
        $montoaprobado = $datos[$fila]["montoaprobado"];
        $montodesembolsado = $datos[$fila]["montodesembolsado"]; //suma

        $tipoenti = $datos[$fila]["TipoEnti"];
        $idfondos = $datos[$fila]["fondoid"];
        $idgrupo = ($tipoenti == "GRUP") ? $datos[$fila]["id_grupos"] : 0;
        $nombrefondos = $datos[$fila]["fondesc"];
        $nomgrupo = $datos[$fila]["NombreGrupo"];
        $comacobrar = $datos[$fila]["gastos"];
        $tipo = $datos[$fila]["tipo"];
        $frec = $datos[$fila]["frecuencia"];
        $ncuotas = $datos[$fila]["numcuotas"];
        $tipdoc = " ";
        switch ($tipo) {
            case "E":
                $tipdoc = "EFECTIVO";
                break;
            case "T":
                $tipdoc = "TRANSFERENCIA";
                break;
            case "C":
                $tipdoc = "CHEQUE";
                break;
        }
        $fecsolicitud = date("d-m-Y", strtotime($datos[$fila]["fecsolicitud"]));
        $fecdesembolsado = ($datos[$fila]["fecdesembolsado"] == '-') ? '-' : date("d-m-Y", strtotime($datos[$fila]["fecdesembolsado"]));
        $responsable = strtoupper(utf8_decode($datos[$fila]["responsable"]));

        $sumasoli = $sumasoli + $montosolicitado;
        $sumamontoapro = $sumamontoapro + $montoaprobado;
        $sumamontodes = $sumamontodes + $montodesembolsado;
        $sumaacobrar = $sumaacobrar + $comacobrar;


        //TITULO FONDO
        if ($idfondos != $auxfondo) {
            $pdf->Ln(2);
            $pdf->SetFont($fuente, 'B', 9);
            $pdf->Cell($ancho_linea * 2, 5, 'FUENTE DE FONDOS: ', '', 0, 'R');
            $pdf->Cell(0, 5, strtoupper($nombrefondos), '', 1, 'L');
            $pdf->SetFont($fuente, '', 8);
            $auxfondo = $idfondos;
            $auxgrupo = -1;
        }
        //TITULO GRUPO
        if ($idgrupo != $auxgrupo) {
            $pdf->Ln(2);
            $pdf->SetFont($fuente, 'B', 8);
            $pdf->Cell($ancho_linea * 2, 5, ($tipoenti == 'GRUP') ? 'GRUPO: ' : 'INDIVIDUALES ', '', 0, 'R');
            $pdf->Cell(0, 5, strtoupper($nomgrupo), '', 1, 'L');
            $pdf->SetFont($fuente, '', 8);
            $auxgrupo = $idgrupo;
        }

        $pdf->CellFit($ancho_linea, $tamanio_linea + 1, $cuenta, 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea * 2, $tamanio_linea + 1, $nombre, 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea, $tamanio_linea + 1, number_format($montosolicitado, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea, $tamanio_linea + 1, number_format($montoaprobado, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea, $tamanio_linea + 1, number_format($montodesembolsado, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea, $tamanio_linea + 1, number_format($comacobrar, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea, $tamanio_linea + 1, $tipdoc, 0, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea, $tamanio_linea + 1, $fecsolicitud, 0, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea, $tamanio_linea + 1, $fecdesembolsado, 0, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 5, $tamanio_linea + 1, $responsable, 0, 0, 'L', 0, '', 1, 0);
        $totalcl++;
        //RESUMEN POR GRUPO
        $pdf->SetFont($fuente, 'B', 8);
        if ($fila != array_key_last($datos)) {
            if ($idgrupo != $datos[$fila + 1]["id_grupos"]) {
                $resumen = true;
            }
        } else {
            $resumen = true;
        }
        if ($resumen == true) {
            $pdf->Ln(6);
            $pdf->CellFit($ancho_linea * 3, $tamanio_linea + 1, 'TOTAL CREDITOS: ' . $totalcl, 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea, $tamanio_linea + 1, number_format($sumasoli, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea, $tamanio_linea + 1, number_format($sumamontoapro, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea, $tamanio_linea + 1, number_format($sumamontodes, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit($ancho_linea, $tamanio_linea + 1, number_format($sumaacobrar, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
            $sumasoli = 0;
            $sumamontoapro = 0;
            $sumamontodes = 0;
            $sumaacobrar = 0;
            $totalcl = 0;
            $resumen = false;
        }

        $pdf->SetFont($fuente, '', 8);
        //FIN RESUMEN X GRUPO
        $pdf->Ln(5);
        $fila++;
    }
    $pdf->SetFont($fuente, 'B', 8);
    $pdf->Cell(0, 0, ' ', 1, 1, 'R');
    $pdf->CellFit($ancho_linea * 2, $tamanio_linea + 1, utf8_decode('NÚMERO DE CASOS: '), 0, 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea, $tamanio_linea + 1, $fila, 0, 0, 'L', 0, '', 1, 0);

    $sumsolicitado = array_sum(array_column($datos, "montosoli"));
    $sumaprobado = array_sum(array_column($datos, "montoaprobado"));
    $sumdesembolso = array_sum(array_column($datos, "montodesembolsado"));
    $sumgasto = array_sum(array_column($datos, "gastos"));

    $pdf->CellFit($ancho_linea, $tamanio_linea + 1, number_format($sumsolicitado, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea, $tamanio_linea + 1, number_format($sumaprobado, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea, $tamanio_linea + 1, number_format($sumdesembolso, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea, $tamanio_linea + 1, number_format($sumgasto, 2, '.', ','), 0, 0, 'R', 0, '', 1, 0);
    $pdf->Ln(15);
    $pdf->CellFit($ancho_linea, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea + 1, 'HECHO POR: ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea, $tamanio_linea + 1, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 79, $tamanio_linea + 1, 'REVISADO POR: ', 0, 0, 'L', 0, '', 1, 0);

    ob_start();
    $pdf->Output();
    $pdfData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "CREDITOS_" . $otros[4] . "_" . $otros[2],
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
    $encabezado_tabla = ["CRÉDITO", "CLIENTE", "NOMBRE CLIENTE", "MONTO SOLICITADO", "MONTO APROBADO", "MONTO DESEMBOLSADO", "COMISION A COBRAR", "TIPO DE DOCUMENTO", "FECHA DE SOLICITUD", "FECHA DE DESEMBOLSO", "FECHA DE VENCIMIENTO", "RESPONSABLE", "FUENTE DE FONDOS", "TIPO", "NOMBRE GRUPO","FRECUENCIA", "CUOTAS"];
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
        $frec = $datos[$fila]["frecuencia"];
        $ncuotas = $datos[$fila]["numcuotas"];

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
        $hojaReporte->setCellValueByColumnAndRow(16, $linea, $frec);
        $hojaReporte->setCellValueByColumnAndRow(17, $linea, $ncuotas);

        $hojaReporte->getStyle("A" . $linea . ":Q" . $linea)->getFont()->setName($fuente);
        $fila++;
        $linea++;
    }
    //totales
    $hojaReporte->setCellValueByColumnAndRow(2, $linea, "NUM. DE CREDITOS: " . $fila);
    $hojaReporte->setCellValueByColumnAndRow(3, $linea, $sumamontoapro);
    $hojaReporte->setCellValueByColumnAndRow(4, $linea, $sumamonsol);
    $hojaReporte->setCellValueByColumnAndRow(5, $linea, $sumamontodes);
    $hojaReporte->setCellValueByColumnAndRow(6, $linea, $sumaacobrar);
    $hojaReporte->getStyle("A" . $linea . ":Q" . $linea)->getFont()->setName($fuente)->setBold(true);
    //totales
    $columnas = range('A', 'Q');
    foreach ($columnas as $columna) {
        $hojaReporte->getColumnDimension($columna)->setAutoSize(TRUE);
    }

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
