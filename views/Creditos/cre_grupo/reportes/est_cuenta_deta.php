<?php
session_start();
include '../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
require '../../../../fpdf/fpdf.php';

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

//se reciben los datos
$datos = $_POST["datosval"];
$inputs = $datos[0];
$archivo = $datos[3];

$strquery = "SELECT gru.*,cli.url_img, cli.short_name,cli.idcod_cliente,cli.date_birth,cli.no_identifica, cli.genero,
cre.CCODCTA,cre.Cestado,cre.NCiclo,cre.MontoSol,cre.NIntApro,cre.CodAnal,concat(usu.nombre,' ',usu.apellido) nomanal,cre.CCODPRD,cre.CtipCre,cre.NtipPerC,cre.DfecPago,cre.noPeriodo,cre.Dictamen,cre.MonSug,cre.DFecDsbls,cre.NCapDes,
pro.id_fondo id_fondos,ff.descripcion,
IFNULL((SELECT SUM(nintere) FROM Cre_ppg WHERE ccodcta=cre.CCODCTA GROUP BY ccodcta),0) intcal,
IFNULL((SELECT SUM(KP) FROM CREDKAR WHERE ccodcta=cre.CCODCTA AND cestado!='X' AND ctippag='P' GROUP BY ccodcta),0) cappag,
IFNULL((SELECT SUM(interes) FROM CREDKAR WHERE ccodcta=cre.CCODCTA AND cestado!='X' AND ctippag='P' GROUP BY ccodcta),0) intpag
From cremcre_meta cre
INNER JOIN tb_cliente cli ON cli.idcod_cliente=cre.CodCli
INNER JOIN tb_grupo gru ON gru.id_grupos=cre.CCodGrupo
INNER JOIN cre_productos pro ON pro.id=cre.CCODPRD
INNER JOIN ctb_fuente_fondos ff ON ff.id=pro.id_fondo
INNER JOIN tb_usuario usu ON usu.id_usu=cre.CodAnal
WHERE cre.TipoEnti='GRUP' AND (cre.CESTADO='F' OR cre.CESTADO='G') AND cre.CCodGrupo='" . $archivo[0] . "'  AND cre.NCiclo=" . $archivo[1] . " ORDER BY cre.CCODCTA";

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
        'mensaje' => 'No se encontraron datos',
        'dato' => $strquery
    );
    echo json_encode($opResult);
    return;
}


//-------------------------------------
$pagosquery = "SELECT crem.MonSug,cred.DFECPRO,cred.CNROCUO,SUM(cred.NMONTO) montototal,cred.CNUMING,cred.CCONCEP,SUM(cred.KP) capital,SUM(cred.INTERES) interes,SUM(cred.MORA) mora,SUM(cred.OTR) otros FROM CREDKAR cred 
INNER JOIN cremcre_meta crem ON crem.CCODCTA=cred.CCODCTA
INNER JOIN tb_grupo gru ON gru.id_grupos=crem.CCodGrupo
WHERE cred.CESTADO!='X' AND crem.TipoEnti='GRUP' AND crem.CCodGrupo='" . $archivo[0] . "'  AND crem.NCiclo=" . $archivo[1] . " AND cred.CTIPPAG='P' 
GROUP BY cred.CNUMING,cred.CNROCUO ORDER BY cred.DFECPRO,cred.CNROCUO";

$quee = mysqli_query($conexion, $pagosquery);
$pagos[] = [];
$haypagos = 0;
$j = 0;
while ($fil = mysqli_fetch_array($quee)) {
    $pagos[$j] = $fil;
    $haypagos = 1;
    $j++;
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
printpdf($registro, $info, $archivo[2], $pagos, $haypagos);

function printpdf($registro, $info, $tipo, $pagos, $haypagos)
{
    $oficina = "Coban";
    $institucion = $info[0]["nomb_comple"];
    $direccionins = $info[0]["muni_lug"];
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

        public function __construct($institucion, $pathlogo, $pathlogoins, $oficina, $direccion, $email, $telefono, $nit, $datos, $tipo)
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
            $this->tipo = $tipo;
            $this->DefOrientation = 'L';
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
            $this->Cell(0, 3, 'NIT: ' . $this->nit, 'B', 1, 'C');
            // Salto de línea
            $this->Ln(5);

            $this->SetFillColor(204, 229, 255);
            $this->Cell(0, 5, 'ESTADO DE CUENTA GRUPAL', 0, 1, 'C');
            $this->Ln(2);
            //TITULOS DE ENCABEZADO DE TABLA
            $ancho_linea = 40;

            $this->Cell($ancho_linea, 7, 'NOMBRE DEL GRUPO:', '', 0, 'L');
            $this->Cell($ancho_linea * 2, 7, $this->datos[0]["NombreGrupo"], '', 0, 'L');

            $this->Cell($ancho_linea, 7, 'CODIGO DE GRUPO:', '', 0, 'L');
            $this->Cell($ancho_linea, 7, $this->datos[0]["codigo_grupo"], '', 0, 'L');

            $this->Cell($ancho_linea, 7, 'CICLO:', '', 0, 'L');
            $this->Cell($ancho_linea, 7,  $this->datos[0]["NCiclo"], '', 1, 'L');

            $this->Cell($ancho_linea, 7, 'FECHA DE APERTURA:', '', 0, 'L');
            $fechasol = date("d-m-Y", strtotime($this->datos[0]["DFecDsbls"]));
            $this->Cell($ancho_linea * 2, 7,  $fechasol, '', 0, 'L');

            $this->Cell($ancho_linea, 7, 'MESES:', '', 0, 'L');
            $this->Cell($ancho_linea, 7, $this->datos[0]["noPeriodo"], '', 0, 'L');

            $this->Cell($ancho_linea, 7, 'INTERES:', '', 0, 'L');
            $this->Cell($ancho_linea, 7, $this->datos[0]["NIntApro"], '', 1, 'L');


            $this->Cell($ancho_linea, 7, 'ASESOR:', '', 0, 'L');
            $this->Cell($ancho_linea * 2, 7, $this->datos[0]["nomanal"], '', 0, 'L');

            $this->Cell($ancho_linea, 7, 'MONTO TOTAL:', '', 0, 'L');
            $this->Cell($ancho_linea, 7, array_sum(array_column($this->datos, "MonSug")), '', 1, 'L');


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

            $this->Cell($ancho_linea * 2, 6, 'HOMBRES: ' . $men, '', 0, 'L');

            $this->Cell($ancho_linea * 2, 6, 'MUJERES: ' . $women, '', 1, 'L');


            $ancho_linea = 28;
            if ($this->tipo == 1) {
                $this->SetFont($fuente, 'B', 8);
                $this->Cell(8, 6, 'No.', 'B', 0, 'L');
                $this->Cell($ancho_linea + 1, 6, 'CODIGO CREDITO', 'B', 0, 'L');
                $this->Cell($ancho_linea * 3 - 14, 6, 'NOMBRE DEL CLIENTE', 'B', 0, 'L');
                $this->Cell($ancho_linea, 6, 'MONTO OTORGADO', 'B', 0, 'R');
                $this->Cell($ancho_linea, 6, 'CAPITAL PAGADO', 'B', 0, 'R');
                $this->Cell($ancho_linea, 6, 'INTERES GENERADO', 'B', 0, 'R');
                $this->Cell($ancho_linea, 6, 'INTERES PAGADO', 'B', 0, 'R');
                $this->Cell($ancho_linea, 6, 'SALDO CAPITAL', 'B', 0, 'R');
                $this->Cell($ancho_linea, 6, 'SALDO INTERES', 'B', 1, 'R');
            }
            if ($this->tipo == 2) {
                $this->Cell(0, 5, 'HISTORICO DE ABONOS', 'T', 1, 'C');
                $this->Ln(2);
                $this->SetFont($fuente, 'B', 9);
                $ancho_linea = 32;
                $this->Cell($ancho_linea / 2, 6, 'CUOTA', 'B', 0, 'C');
                $this->Cell($ancho_linea, 6, 'FECHA', 'B', 0, 'C');
                $this->Cell($ancho_linea, 6, 'NO. RECIBO', 'B', 0, 'C');
                $this->Cell($ancho_linea, 6, 'PAGO', 'B', 0, 'R');
                $this->Cell($ancho_linea, 6, 'CAPITAL', 'B', 0, 'R');
                $this->Cell($ancho_linea, 6, 'INTERES', 'B', 0, 'R');
                $this->Cell($ancho_linea, 6, 'MORA', 'B', 0, 'R');
                $this->Cell($ancho_linea, 6, 'OTROS', 'B', 0, 'R');
                $this->Cell($ancho_linea, 6, 'SALDO', 'B', 1, 'R');
            }
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
    $pdf = new PDF($institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins, $registro, $tipo);
    $pdf->AliasNbPages();
    $pdf->AddPage();

    if ($tipo == 1) detallado($pdf, $registro);
    if ($tipo == 2 && $haypagos == 1) consolidado($pdf, $registro, $pagos);

    ob_start();
    $pdf->Output();
    $pdfData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "Estado de cuenta Grupal",
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
        $monapr =  $registro[$fila]["NCapDes"];
        $cappag =  $registro[$fila]["cappag"];
        $intcal = $registro[$fila]["intcal"];
        $intpag =  $registro[$fila]["intpag"];
        $salcap =  $registro[$fila]["NCapDes"] - $registro[$fila]["cappag"];
        $salint = $registro[$fila]["intcal"] - $registro[$fila]["intpag"];
        
        $registro[$fila]["salcap"] = $salcap;
        $registro[$fila]["salint"] = $salint;
        $salcap = ($salcap > 0) ? $salcap : 0;
        $salint = ($salint > 0) ? $salint : 0;

        $pdf->CellFit(8, $tamanio_linea + 1, $fila + 1, '', 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2 + 1, $tamanio_linea + 1, $codcta, '', 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2 * 3 - 14, $tamanio_linea + 1, strtoupper($namecli), '', 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($monapr, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($cappag, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($intcal, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($intpag, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($salcap, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($salint, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
        $fila++;
    }
    $pdf->Ln(2);
    $pdf->SetFont($fuente, 'B', 8);
    $sum_montos = array_sum(array_column($registro, "MonSug"));
    $sum_cappag = array_sum(array_column($registro, "cappag"));
    $sum_intcal = array_sum(array_column($registro, "intcal"));
    $sum_intpag = array_sum(array_column($registro, "intpag"));
    $sum_salcap = array_sum(array_column($registro, "salcap"));
    $sum_salint = array_sum(array_column($registro, "salint"));

    $pdf->CellFit($ancho_linea2 * 4 - 5, $tamanio_linea + 1, 'No. Clientes: ' . $fila, 'T', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_montos, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_cappag, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_intcal, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_intpag, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_salcap, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_salint, 2, '.', ','), 'T', 1, 'R', 0, '', 1, 0);
}

function consolidado($pdf, $registro, $pagos)
{
    $fuente = "Courier";
    $tamanio_linea = 5;
    $ancho_linea2 = 32;
    $pdf->SetFont($fuente, '', 8);

    $sum_montos = array_sum(array_column($registro, "NCapDes"));
    $saldo = $sum_montos;
    $fila = 0;
    while ($fila < count($pagos)) {
        $fecha = date("d-m-Y", strtotime($pagos[$fila]["DFECPRO"]));
        $nocuo =  $pagos[$fila]["CNROCUO"];
        //$monapr =  $pagos[$fila]["NCapDes"];
        $montototal =  $pagos[$fila]["montototal"];
        $numdoc = $pagos[$fila]["CNUMING"];
        $cappag =  $pagos[$fila]["capital"];
        $intpag =  $pagos[$fila]["interes"];
        $morpag =  $pagos[$fila]["mora"];
        $otrospag =  $pagos[$fila]["otros"];
        $saldo = $saldo - $cappag;

        $saldo = ($saldo > 0) ? $saldo : 0;

        $pdf->CellFit($ancho_linea2 / 2, $tamanio_linea, $nocuo, '', 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, $fecha, '', 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, $numdoc, '', 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($montototal, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($cappag, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($intpag, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($morpag, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($otrospag, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($saldo, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
        $fila++;
    }
    $pdf->Ln(2);
    $pdf->SetFont($fuente, 'B', 8);
    $sum_montos = array_sum(array_column($pagos, "montototal"));
    $sum_cappag = array_sum(array_column($pagos, "capital"));
    $sum_intpag = array_sum(array_column($pagos, "interes"));
    $sum_morpag = array_sum(array_column($pagos, "mora"));
    $sum_otrospag = array_sum(array_column($pagos, "otros"));

    $pdf->CellFit($ancho_linea2 * 2.5, $tamanio_linea + 1, ' ', 'T', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_montos, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_cappag, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_intpag, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_morpag, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_otrospag, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 'T', 1, 'R', 0, '', 1, 0);
}
