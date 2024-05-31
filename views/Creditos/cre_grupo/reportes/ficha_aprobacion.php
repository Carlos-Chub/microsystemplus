<?php
session_start();
include '../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
require '../../../../fpdf/fpdf.php';
date_default_timezone_set('America/Guatemala');
//se recibe los datos

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

$datos = $_POST["datosval"];
$inputs = $datos[0];
$archivo = $datos[3];

$strquery = "SELECT gru.*,cli.url_img, cli.short_name,cli.idcod_cliente,cli.date_birth,cli.no_identifica, cli.genero,
cre.CCODCTA,cre.Cestado,cre.NCiclo,cre.MontoSol,cre.NIntApro,cre.CodAnal,concat(usu.nombre,' ',usu.apellido) nomanal,cre.CCODPRD,cre.CtipCre,cre.NtipPerC,cre.DfecPago,cre.noPeriodo,cre.Dictamen,cre.MonSug,cre.DFecDsbls,
pro.id_fondo id_fondos,ff.descripcion
From cremcre_meta cre
INNER JOIN tb_cliente cli ON cli.idcod_cliente=cre.CodCli
INNER JOIN tb_grupo gru ON gru.id_grupos=cre.CCodGrupo
INNER JOIN cre_productos pro ON pro.id=cre.CCODPRD
INNER JOIN ctb_fuente_fondos ff ON ff.id=pro.id_fondo
INNER JOIN tb_usuario usu ON usu.id_usu=cre.CodAnal
WHERE cre.TipoEnti='GRUP' AND cre.CESTADO='E' AND cre.CCodGrupo='" . $archivo[0] . "'  AND cre.NCiclo=" . $archivo[1] . " ORDER BY cre.CCODCTA";

$query = mysqli_query($conexion, $strquery);
$registro[] = [];
$j = 0;
$flag = false;
while ($fil = mysqli_fetch_array($query)) {
    $registro[$j] = $fil;
    $flag = true;
    $j++;
}
//COMPROBACION: SI SE ENCONTRARON REGISTROS
if ($flag == false) {
    $opResult = array(
        'status' => 0,
        'mensaje' => 'No se encontraron datos del grupo en estado de Aprobacion',
        'dato' => $strquery
    );
    echo json_encode($opResult);
    return;
}

/* $opResult = array(
    'status' => 0,
    'mensaje' => 'No se encontraron datos',
    'dato' => $registro
);
echo json_encode($opResult);
return; */
//FIN COMPROBACION
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
printpdf($registro, $info);

function printpdf($registro, $info)
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
        public $tipo;

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
            //$this->DefOrientation = 'L';
        }

        // Cabecera de página
        function Header()
        {
            $fuente = "Courier";
            $hoy = date("Y-m-d H:i:s");
            //fecha y usuario que genero el reporte
            $this->SetFont($fuente, '', 7);
            //$this->Cell(0, 2, $hoy, 0, 1, 'R');
            // Logo de la agencia
            $this->Image($this->pathlogoins, 10, 10, 33);

            //tipo de letra para el encabezado
            $this->SetFont($fuente, 'B', 9);
            // Título
            $this->Cell(0, 3, $this->institucion, 0, 1, 'C');
            $this->Cell(0, 3, $this->direccion, 0, 1, 'C');
            $this->Cell(0, 3, 'Email: ' . $this->email, 0, 1, 'C');
            $this->Cell(0, 3, 'Tel: ' . $this->telefono, 0, 1, 'C');
            $this->Cell(0, 3, 'NIT: ' . $this->nit, 0, 1, 'C');
            $this->Cell(0, 3, strtoupper($this->oficina), 'B', 1, 'C');
            $this->SetFont('Arial', '', 7);
            $this->SetXY(-30, 5);
            $this->Cell(10, 2, $hoy, 0, 1, 'L');
            $this->SetXY(-25, 8);
            $this->Ln(15);
            // Salto de línea
            $this->Ln(5);
            $this->SetFont($fuente, 'B', 9);
            $this->Cell(0, 5, 'COMPROBANTE DE APROBACION PARA CREDITO GRUPAL', 0, 1, 'C');
            $this->Ln(2);
            //TITULOS DE ENCABEZADO DE TABLA
            $ancho_linea = 40;

            $this->Cell($ancho_linea, 7, 'NOMBRE DEL GRUPO:', '', 0, 'L');
            $this->Cell($ancho_linea * 2, 7, $this->datos[0]["NombreGrupo"], '', 0, 'L');

            $this->Cell($ancho_linea, 7, 'CODIGO DE GRUPO:', '', 0, 'L');
            $this->Cell($ancho_linea, 7, $this->datos[0]["codigo_grupo"], '', 1, 'L');

            $this->Cell($ancho_linea, 7, 'CICLO:', '', 0, 'L');
            $this->Cell($ancho_linea, 7,  $this->datos[0]["NCiclo"], '', 0, 'L');

            $this->Cell($ancho_linea, 7, 'FECHA DE APERTURA:', '', 0, 'L');
            $this->Cell($ancho_linea * 2, 7,  $this->datos[0]["DFecDsbls"], '', 1, 'L');

            $this->Cell($ancho_linea, 7, 'MESES:', '', 0, 'L');
            $this->Cell($ancho_linea, 7, $this->datos[0]["noPeriodo"], '', 0, 'L');

            $this->Cell($ancho_linea, 7, 'INTERES:', '', 0, 'L');
            $this->Cell($ancho_linea, 7, $this->datos[0]["NIntApro"], '', 1, 'L');

            $this->Cell($ancho_linea, 7, 'ASESOR:', '', 0, 'L');
            $this->Cell($ancho_linea, 7, $this->datos[0]["nomanal"], '', 0, 'L');

            $this->Cell($ancho_linea, 7, 'TOTAL APROBADO:', '', 0, 'L');
            $this->Cell($ancho_linea, 7, number_format(array_sum(array_column($this->datos, "MonSug")), 2, '.', ','), '', 1, 'L');


            $this->Ln(5);

            $this->Cell(0, 5, 'COMPOSICION DEL GRUPO', 0, 1, 'C');
            $this->Ln(2);
            $men = count(array_filter(array_column($this->datos, "genero"), function ($var) {
                return ($var == "M");
            }));
            $women = count(array_filter(array_column($this->datos, "genero"), function ($var) {
                return ($var == "F");
            }));
            $this->Cell($ancho_linea * 2, 6, 'TOTAL DE CLIENTES: ' . count($this->datos), '', 0, 'L');

            $this->Cell($ancho_linea, 6, 'HOMBRES: ' . $men, '', 0, 'L');

            $this->Cell($ancho_linea, 6, 'MUJERES: ' . $women, '', 1, 'L');


            $ancho_linea = 28;
            $this->SetFont($fuente, 'B', 8);
            $this->Cell(8, 6, 'No.', 'B', 0, 'L');
            $this->Cell($ancho_linea, 6, 'CODIGO CREDITO', 'B', 0, 'L');
            $this->Cell($ancho_linea * 3 - 14, 6, 'NOMBRE DEL CLIENTE', 'B', 0, 'L');
            $this->Cell($ancho_linea, 6, 'IDENTIFICACION', 'B', 0, 'L');
            $this->Cell($ancho_linea, 6, 'SOLICITADO', 'B', 0, 'R');
            $this->Cell($ancho_linea, 6, 'APROBADO', 'B', 1, 'R');
            $this->Ln(2);
        }

        // Pie de página
        function Footer()
        {
            // Posición: a 1 cm del final
            $this->SetY(-15);
            // Logo 
            //$this->Image($this->pathlogo, 175, 279, 28);
            // Arial italic 8
            $this->SetFont('Arial', 'I', 8);
            // Número de página
            $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }
    }
    $pdf = new PDF($institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins, $registro);
    $pdf->AliasNbPages();
    $pdf->AddPage();

    detallado($pdf, $registro);
    $pdf->firmas(3,[' ',' ',' ']);

    ob_start();
    $pdf->Output();
    $pdfData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "Ficha de aprobacion Grupal",
        'tipo' => "pdf",
        'data' => "data:application/pdf;base64," . base64_encode($pdfData)
    );
    echo json_encode($opResult);
}

function detallado($pdf, $registro)
{
    $fuente = "Courier";
    $tamanio_linea = 5;
    $ancho_linea2 = 28;
    $pdf->SetFont($fuente, '', 9);

    $fila = 0;
    while ($fila < count($registro)) {
        $codcta = $registro[$fila]["CCODCTA"];
        $namecli =  $registro[$fila]["short_name"];
        $identificacion =  ($registro[$fila]["no_identifica"]==null) ? ' ' : $registro[$fila]["no_identifica"];
        $monsol =  $registro[$fila]["MontoSol"];
        $monapr =  $registro[$fila]["MonSug"];


        $pdf->CellFit(8, $tamanio_linea + 1, $fila + 1, '', 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $codcta, '', 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2 * 3 - 14, $tamanio_linea + 1, strtoupper($namecli), '', 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, $identificacion, '', 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($monsol, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($monapr, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
        $fila++;
    }
    $pdf->Ln(2);
    $pdf->SetFont($fuente, 'B', 9);
    $sum_montos = array_sum(array_column($registro, "MonSug"));
    $sum_soli = array_sum(array_column($registro, "MontoSol"));

    $pdf->CellFit($ancho_linea2 * 5 - 6, $tamanio_linea + 1, 'No. Clientes: ' . $fila, 'T', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_montos, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_soli, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
}