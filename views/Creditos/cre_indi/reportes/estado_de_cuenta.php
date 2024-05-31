<?php
session_start();
include '../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');
require '../../../../fpdf/fpdf.php';

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

//se recibe los datos
$datos = $_POST["datosval"];
$archivo = $datos[3];
$idagencia = $archivo[1];

$strquery = "SELECT cli.idcod_cliente,cli.short_name,cli.url_img,cli.tel_no1,cli.date_birth,cli.no_identifica,cli.Direccion,
cr.CCODCTA,cr.CCODPRD,cr.Cestado,cr.DFecDsbls,cr.ActoEcono,cr.CtipCre,cr.NtipPerC,cr.Cdescre,cr.CSecEco,cr.CCodGrupo,cr.NCiclo,cr.TipDocDes,cr.noPeriodo,cr.MonSug,cr.NCapDes, cr.TipoEnti,cr.NIntApro,
prod.nombre nnompro,prod.descripcion descriprod,prod.tasa_interes TasaInteres,
ff.descripcion ffondo,CONCAT(usu.nombre,' ',usu.apellido) nomanal,
IFNULL((SELECT dfecven FROM Cre_ppg WHERE ccodcta=cr.CCODCTA ORDER BY dfecven DESC LIMIT 1),0) fechaven,
IFNULL((SELECT SUM(ncapita) FROM Cre_ppg WHERE dfecven<=CURDATE() AND ccodcta=cr.CCODCTA GROUP BY ccodcta),0) capcalafec,
IFNULL((SELECT SUM(nintere) FROM Cre_ppg WHERE ccodcta=cr.CCODCTA GROUP BY ccodcta),0) intcalafec,
IFNULL((SELECT SUM(nmorpag) FROM Cre_ppg WHERE dfecven<=CURDATE() AND ccodcta=cr.CCODCTA AND cestado='X' GROUP BY ccodcta),0) morcal,
cre_dias_atraso(CURDATE(),cr.CCODCTA) atraso,ac.Titulo actividad,dc.DestinoCredito destino,sec.SectoresEconomicos sector
 FROM cremcre_meta cr 
 INNER JOIN tb_cliente cli ON cli.idcod_cliente=cr.CodCli 
 INNER JOIN cre_productos prod ON prod.id=cr.CCODPRD
 INNER JOIN ctb_fuente_fondos ff ON ff.id=prod.id_fondo
 INNER JOIN tb_usuario usu ON usu.id_usu=cr.CodAnal
 INNER JOIN clhpzzvb_bd_general_coopera.tb_ActiEcono ac ON ac.id_ActiEcono=cr.ActoEcono 
 INNER JOIN clhpzzvb_bd_general_coopera.tb_sectoreseconomicos sec ON sec.id_SectoresEconomicos=cr.CSecEco
 INNER JOIN clhpzzvb_bd_general_coopera.tb_destinocredito dc ON dc.id_DestinoCredito=cr.Cdescre 
 WHERE cr.CCODCTA='" . $archivo[0] . "'";

$query = mysqli_query($conexion, $strquery);
$registro[] = [];
$j = 0;
$flag = false;
while ($fil = mysqli_fetch_array($query)) {
    $registro[$j] = $fil;
    $todos = $fil['atraso'];
    $filasaux = substr($todos, 0, -1);
    $filas = explode("#", $filasaux);
    for ($k = 0; $k < count($filas); $k++) {
        $registro[$j]["atrasadas"][$k] = explode("_", $filas[$k]);
    }
    $flag = true;
    $j++;
}

//COMPROBACION: SI SE ENCONTRARON REGISTROS
if ($flag == false) {
    $opResult = array(
        'status' => 0,
        'mensaje' => 'No se encontro la cuenta',
        'dato' => $registro
    );
    echo json_encode($opResult);
    return;
}

//BUSCAR DATOS DE GARANTIAS
$strquery = "SELECT cl.idcod_cliente AS codcli, gr.idGarantia AS idgar, tipgar.id_TiposGarantia AS idtipgar, tipgar.TiposGarantia AS nomtipgar, tipc.idDoc AS idtipdoc, tipc.NombreDoc AS nomtipdoc, 
    gr.descripcionGarantia AS descripcion, gr.direccion AS direccion, gr.montoGravamen AS montogravamen,
    
    IFNULL((SELECT cl2.no_identifica AS dpi FROM tb_cliente cl2 WHERE cl2.idcod_cliente=gr.descripcionGarantia AND tipgar.id_TiposGarantia=1 AND tipc.idDoc=1),'x') AS dpi,

    IFNULL((SELECT cl2.short_name AS nomcli FROM tb_cliente cl2 WHERE cl2.idcod_cliente=gr.descripcionGarantia AND tipgar.id_TiposGarantia=1 AND tipc.idDoc=1),'x') AS nomcli,
    IFNULL((SELECT cl2.Direccion AS direccioncli FROM tb_cliente cl2 WHERE cl2.idcod_cliente=gr.descripcionGarantia AND tipgar.id_TiposGarantia=1 AND tipc.idDoc=1),'x') AS direccioncli,
    IFNULL((SELECT '1' AS marcado FROM tb_garantias_creditos tgc WHERE tgc.id_cremcre_meta='$archivo[0]' AND tgc.id_garantia=gr.idGarantia),0) AS marcado,
    IFNULL((SELECT SUM(cli.montoGravamen) AS totalgravamen FROM tb_garantias_creditos tgc INNER JOIN cli_garantia cli ON cli.idGarantia=tgc.id_garantia WHERE tgc.id_cremcre_meta='$archivo[0]' AND cli.estado=1),0) AS totalgravamen
    FROM tb_cliente cl
    INNER JOIN cli_garantia gr ON cl.idcod_cliente=gr.idCliente
    INNER JOIN clhpzzvb_bd_general_coopera.tb_tiposgarantia tipgar ON gr.idTipoGa=tipgar.id_TiposGarantia
    INNER JOIN clhpzzvb_bd_general_coopera.tb_tiposdocumentosR tipc ON tipc.idDoc=gr.idTipoDoc  
    WHERE cl.estado='1' AND gr.estado=1 AND cl.idcod_cliente='" . $registro[0]['idcod_cliente'] . "'";
$query = mysqli_query($conexion, $strquery);
$garantias[] = [];
$j = 0;
while ($fila = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
    $garantias[$j] = $fila;
    $j++;
}

//-------------------------------------
$pagosquery = "SELECT cred.DFECPRO,cred.CNROCUO,cred.NMONTO,cred.CNUMING,cred.CCONCEP,cred.KP,cred.INTERES,cred.MORA,cred.AHOPRG,cred.OTR,cred.CTIPPAG FROM CREDKAR cred 
WHERE cred.CESTADO!='X' AND cred.CTIPPAG='P' AND cred.CCODCTA='" . $archivo[0] . "' ORDER BY cred.DFECPRO,cred.CNROCUO";

$quee = mysqli_query($conexion, $pagosquery);
$pagos[] = [];
$haypagos = 0;
$j = 0;
while ($fil = mysqli_fetch_array($quee)) {
    $pagos[$j] = $fil;
    $haypagos = 1;
    $j++;
}

//FIN COMPROBACION
$queryins = mysqli_query($conexion, " SELECT * FROM clhpzzvb_bd_general_coopera.info_coperativa cop
INNER JOIN tb_agencia ofi ON ofi.id_institucion=cop.id_cop
 where ofi.id_agencia=$idagencia");
$info[] = [];
$j = 0;
while ($fil = mysqli_fetch_array($queryins)) {
    $info[$j] = $fil;
    $j++;
}
printpdf($registro, $info, $pagos, $haypagos, $garantias);

function printpdf($registro, $info, $pagos, $haypagos, $garantias)
{
    $oficina = utf8_decode($info[0]["nom_agencia"]);
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
        public $pagos;

        public function __construct($institucion, $pathlogo, $pathlogoins, $oficina, $direccion, $email, $telefono, $nit, $datos, $pagos)
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
            $this->pagos = $pagos;
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
            $this->SetFont($fuente, 'B', 7);
            // Título
            $this->Cell(0, 3, $this->institucion, 0, 1, 'C');
            $this->Cell(0, 3, $this->direccion, 0, 1, 'C');
            $this->Cell(0, 3, 'Email: ' . $this->email, 0, 1, 'C');
            $this->Cell(0, 3, 'Tel: ' . $this->telefono, 0, 1, 'C');
            $this->Cell(0, 3, 'NIT: ' . $this->nit, 'B', 1, 'C');
            // Salto de línea
            $this->Ln(3);

            $this->SetFillColor(204, 229, 255);
            $this->Cell(0, 5, 'ESTADO DE CUENTA INDIVIDUAL', 0, 1, 'C');

            $ancho_linea = 40;
            $this->Cell($ancho_linea * 2, 7, 'CUENTA: ' . $this->datos[0]["CCODCTA"], 'B', 0, 'L');
            $this->Cell(0, 7, 'CLIENTE: ' . $this->datos[0]["idcod_cliente"] . ' - ' . (strtoupper($this->datos[0]["short_name"])), 'B', 1, 'L');
            $this->Ln(2);
            //TITULOS DE ENCABEZADO DE TABLA

            $this->Cell($ancho_linea, 5, 'ESTADO DEL CREDITO:', '', 0, 'L');
            $this->Cell($ancho_linea * 1.5, 5, ($this->datos[0]["Cestado"] == "F") ? 'VIGENTE' : 'CANCELADO', '', 0, 'L');

            $this->Cell($ancho_linea, 5, 'EJECUTIVO:', '', 0, 'L');
            $this->Cell($ancho_linea, 5, $this->datos[0]["nomanal"], '', 1, 'L');


            $this->Cell($ancho_linea, 5, 'DIRECCION:', '', 0, 'L');
            $this->Cell($ancho_linea * 1.5, 5, $this->datos[0]["Direccion"], '', 0, 'L');

            $this->Cell($ancho_linea, 5, 'TELEFONO:', '', 0, 'L');
            $this->Cell($ancho_linea, 5, $this->datos[0]["tel_no1"], '', 1, 'L');


            $this->Cell($ancho_linea, 5, 'DESTINO DEL CREDITO:', '', 0, 'L');
            $this->Cell($ancho_linea * 1.5, 5, utf8_decode($this->datos[0]["destino"]), '', 0, 'L');

            $this->Cell($ancho_linea, 5, 'SECTOR ECONOMICO:', '', 0, 'L');
            $this->Cell($ancho_linea, 5,  utf8_decode($this->datos[0]["sector"]), '', 1, 'L');


            $this->Cell($ancho_linea, 5, 'ACTIVIDAD ECONOMICA:', '', 0, 'L');
            $this->Cell($ancho_linea * 1.5, 5,  utf8_decode($this->datos[0]["actividad"]), '', 0, 'L');

            $this->Cell($ancho_linea, 5, 'FUENTE DE FONDOS:', '', 0, 'L');
            $this->Cell($ancho_linea * 2, 5, $this->datos[0]["ffondo"], '', 1, 'L');


            $this->Cell($ancho_linea, 5, 'ENTIDAD: ', '', 0, 'L');
            $this->Cell($ancho_linea * 1.5, 5, ($this->datos[0]["TipoEnti"] == "GRUP") ? 'GRUPAL' : 'INDIVIDUAL', '', 1, 'L');

            $this->Ln(5);

            $this->Cell(0, 5, 'DATOS DEL CREDITO', 'T', 1, 'C');
            $ancho_linea = 30;
            $this->Cell($ancho_linea, 5, 'MONTO APROBADO:', '', 0, 'L');
            $this->Cell($ancho_linea, 5, $this->datos[0]["MonSug"], '', 0, 'L');

            $this->Cell($ancho_linea, 5, 'No.CUOTAS:', '', 0, 'L');
            $this->Cell($ancho_linea, 5,  $this->datos[0]["noPeriodo"], '', 0, 'L');

            $this->Cell($ancho_linea, 5, 'OTORGAMIENTO:', '', 0, 'L');
            $fecha = date("d-m-Y", strtotime($this->datos[0]["DFecDsbls"]));
            $this->Cell($ancho_linea, 5, $fecha, '', 1, 'L');


            $this->Cell($ancho_linea, 5, 'TASA MENSUAL:', '', 0, 'L');
            $this->Cell($ancho_linea, 5, $this->datos[0]["NIntApro"], '', 0, 'L');

            $this->Cell($ancho_linea, 5, 'VENCIMIENTO:', '', 0, 'L');
            $fechaven = date("d-m-Y", strtotime($this->datos[0]["fechaven"]));
            $this->Cell($ancho_linea, 5, $fechaven, '', 1, 'L');


            $this->Cell($ancho_linea, 5, 'DIAS DE ATRASO:', '', 0, 'L');
            $this->Cell($ancho_linea, 5, $this->datos[0]["atrasadas"][0][0], '', 0, 'L');

            $this->Cell($ancho_linea, 5, 'MONTO DESEMBOLSADO:', '', 0, 'L');
            $this->Cell($ancho_linea, 5, $this->datos[0]["NCapDes"], '', 1, 'L');

            $this->Ln(2);

            $this->Cell(0, 5, 'SALDOS DEL CREDITO', 'T', 1, 'C');



            $this->Cell($ancho_linea, 5, 'SALDO INTERES:', '', 0, 'L');
            $intapagar = $this->datos[0]["intcalafec"] - array_sum(array_column($this->pagos, "INTERES"));
            $intapagar = ($intapagar < 0) ? 0 : $intapagar;
            $this->Cell($ancho_linea, 5, $intapagar, '', 0, 'L');

            $this->Cell($ancho_linea, 5, 'MORA:', '', 0, 'L');
            $moracal = array_sum(array_column($this->datos, "morcal"));
            $this->Cell($ancho_linea, 5, $moracal, '', 0, 'L');

            $this->Cell($ancho_linea, 5, 'CAPITAL EN MORA:', '', 0, 'L');
            $sum_capmora = array_sum(array_column($this->datos[0]["atrasadas"], 1));
            $this->Cell($ancho_linea, 5, $sum_capmora, '', 1, 'L');


            $this->Cell($ancho_linea, 5, 'TOTAL:', '', 0, 'L');
            $this->Cell($ancho_linea, 5, ($intapagar + $moracal + $sum_capmora), '', 0, 'L');

            $this->Cell($ancho_linea, 5, 'SALDO CAP:', '', 0, 'L');
            $saldo = $this->datos[0]["NCapDes"] - array_sum(array_column($this->pagos, "KP"));
            $saldo = ($saldo > 0) ? round($saldo, 2)  : 0;
            $this->Cell($ancho_linea, 5, $saldo, '', 0, 'L');

            $this->Cell($ancho_linea, 5, 'SALDO KP+INT:', '', 0, 'L');
            $kpint = $intapagar + $saldo;
            $this->Cell($ancho_linea, 5, round($kpint, 2), '', 1, 'L');


            $this->Ln(2);

            $this->Cell(0, 5, 'HISTORICO DE MOVIMIENTOS', 'T', 1, 'C');
            $this->SetFont($fuente, 'B', 8);
            $ancho_linea = 22;
            $this->Cell($ancho_linea, 6, 'FECHA', 'B', 0, 'C');
            $this->Cell($ancho_linea / 2, 6, 'NO.CUO', 'B', 0, 'C');
            $this->Cell($ancho_linea, 6, 'NUMDOC', 'B', 0, 'C');
            $this->Cell($ancho_linea, 6, 'PAGO', 'B', 0, 'R');
            $this->Cell($ancho_linea, 6, 'CAPITAL', 'B', 0, 'R');
            $this->Cell($ancho_linea, 6, 'INTERES', 'B', 0, 'R');
            $this->Cell($ancho_linea, 6, 'MORA', 'B', 0, 'R');
            $this->Cell($ancho_linea, 6, 'OTROS', 'B', 0, 'R');
            $this->Cell($ancho_linea, 6, 'SALDO', 'B', 1, 'R');
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
    $pdf = new PDF($institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins, $registro, $pagos);
    $pdf->AliasNbPages();
    $pdf->AddPage();

    //if ($tipo == 1) detallado($pdf, $registro);
    if ($haypagos == 1) rpagos($pdf, $registro, $pagos);

    garantias($pdf, $garantias);


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

function rpagos($pdf, $registro, $pagos)
{
    $fuente = "Courier";
    $tamanio_linea = 5;
    $ancho_linea2 = 22;
    $pdf->SetFont($fuente, '', 8);

    $monto = $registro[0]["NCapDes"];
    $saldo = $monto;
    $fila = 0;
    while ($fila < count($pagos)) {
        $fecha = date("d-m-Y", strtotime($pagos[$fila]["DFECPRO"]));
        $nocuo =  $pagos[$fila]["CNROCUO"];

        $tippag =  $pagos[$fila]["CTIPPAG"];
        $montototal =  $pagos[$fila]["NMONTO"];
        $numdoc = ($tippag == "P") ? $pagos[$fila]["CNUMING"] : 'DESEMBOLSO';
        $cappag =  $pagos[$fila]["KP"];
        $intpag = ($tippag == "P") ? number_format($pagos[$fila]["INTERES"], 2, '.', ',') : ' ';
        $morpag = ($tippag == "P") ? number_format($pagos[$fila]["MORA"], 2, '.', ',') : ' ';
        $otrospag = $pagos[$fila]["AHOPRG"] + $pagos[$fila]["OTR"];
        $saldo = ($tippag == "P") ? $saldo - $cappag : $cappag;

        $saldo = ($saldo > 0) ? $saldo : 0;

        $pdf->CellFit($ancho_linea2, $tamanio_linea, $fecha, '', 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2 / 2, $tamanio_linea, $nocuo, '', 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, $numdoc, '', 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($montototal, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($cappag, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, $intpag, '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, $morpag, '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($otrospag, 2, '.', ','), '', 0, 'R', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2, $tamanio_linea, number_format($saldo, 2, '.', ','), '', 1, 'R', 0, '', 1, 0);
        $fila++;
    }
    $pdf->Ln(2);
    $pdf->SetFont($fuente, 'B', 8);
    $sum_montos = array_sum(array_column($pagos, "NMONTO"));
    $sum_cappag = array_sum(array_column($pagos, "KP"));
    $sum_intpag = array_sum(array_column($pagos, "INTERES"));
    $sum_morpag = array_sum(array_column($pagos, "MORA"));
    $sum_otrospag = array_sum(array_column($pagos, "OTR")) + array_sum(array_column($pagos, "AHOPRG"));

    $pdf->CellFit($ancho_linea2 * 2.5, $tamanio_linea + 1, ' ', 'T', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_montos, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_cappag, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_intpag, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_morpag, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, number_format($sum_otrospag, 2, '.', ','), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2, $tamanio_linea + 1, ' ', 'T', 1, 'R', 0, '', 1, 0);
    $pdf->Ln(6);
}

function garantias($pdf, $garantias)
{
    $fuente = "Courier";
    $tamanio_linea = 5;
    $ancho_linea2 = 22;
    $pdf->SetFont($fuente, '', 8);

    //SECCION PARA PONER LAS GARANTIAS
    $ancho_linea2 = 30;
    //GARANTIAS
    $pdf->SetFont($fuente, 'B', 7);
    $pdf->CellFit(0, $tamanio_linea, utf8_decode('GARANTIAS DEL CRÉDITO'), 'B', 0, 'C', 0, '', 1, 0);
    $pdf->Ln(6);

    //ENCABEZADO
    $pdf->SetFont($fuente, 'B', 8);
    $pdf->CellFit($ancho_linea2, $tamanio_linea, utf8_decode('Tip. Garantia'), 'B', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 5, $tamanio_linea, utf8_decode('Tip. Documento'), 'B', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 30, $tamanio_linea, utf8_decode('Descripción'), 'B', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 + 10, $tamanio_linea, utf8_decode('Dirección'), 'B', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea, utf8_decode('Mon. Gravamen'), 'B', 0, 'C', 0, '', 1, 0);
    $pdf->Ln(6);
    //CICLO PARA IMPRESION DE GARANTIAS
    $pdf->SetFont($fuente, '', 8);
    for ($i = 0; $i < count($garantias); $i++) {
        $pdf->SetFillColor(255, 255, 255);
        $resaltado = (($garantias[$i]['marcado'] == 1) ? 1 : 0);
        if ($resaltado == 1) {
            $x = $pdf->GetX();
            $xinicial = $pdf->GetX();
            $yfinal = 0;
            $yinicial = $pdf->GetY();
            //tipo garantia
            $pdf->CellFit($ancho_linea2, $tamanio_linea, (($garantias[$i]['nomtipgar'] == '' || $garantias[$i]['nomtipgar'] == null) ? ' ' : utf8_decode($garantias[$i]['nomtipgar'])), 0, 0, 'L', $resaltado, '', 0, 0);
            $x = $x + ($ancho_linea2);
            $yaux = $pdf->GetY();
            $yfinal = $pdf->GetY();

            if ($yfinal < $yaux) {
                $yfinal = $pdf->GetY();
            }
            $pdf->SetXY($x, $yinicial);
            $x = $pdf->GetX();

            //tipo documento
            $pdf->MultiCell($ancho_linea2 + 5, $tamanio_linea, (($garantias[$i]['nomtipdoc'] == '' || $garantias[$i]['nomtipdoc'] == null) ? ' ' : utf8_decode($garantias[$i]['nomtipdoc'])), 0, 'L', $resaltado);
            $x = $x + ($ancho_linea2 + 5);
            $yaux = $pdf->GetY();

            if ($yfinal < $yaux) {
                $yfinal = $pdf->GetY();
            }
            $pdf->SetXY($x, $yinicial);
            $x = $pdf->GetX();
            if ($garantias[$i]["idtipgar"] == 1 && $garantias[$i]["idtipdoc"] == 1) {
                //nomcliente cuando es fiador
                $pdf->MultiCell($ancho_linea2 + 30, $tamanio_linea, (($garantias[$i]['nomcli'] == '' || $garantias[$i]['nomcli'] == null) ? ' ' : utf8_decode($garantias[$i]['nomcli'])), 0, 'L', $resaltado);
                $auxX = $pdf->GetX() + 65;
                $auxY = $pdf->GetY();

                $pdf->SetXY($auxX, $auxY);

                $pdf->MultiCell($ancho_linea2 + 30, $tamanio_linea, (($garantias[$i]['dpi'] == '' || $garantias[$i]['dpi'] == null) ? ' ' : "DPI: " . utf8_decode($garantias[$i]['dpi'])), 0, 'L', $resaltado);

                $x = $x + ($ancho_linea2 + 30);
                $yaux = $pdf->GetY();

                if ($yfinal < $yaux) {
                    $yfinal = $pdf->GetY();
                }
                $pdf->SetXY($x, $yinicial);
                $x = $pdf->GetX();

                //direccion cuando es fiador
                $pdf->MultiCell($ancho_linea2 + 10, $tamanio_linea, (($garantias[$i]['direccioncli'] == '' || $garantias[$i]['direccioncli'] == null) ? ' ' : utf8_decode($garantias[$i]['direccioncli'])), 0, 'L', $resaltado);
                $x = $x + ($ancho_linea2 + 10);
                $yaux = $pdf->GetY();

                if ($yfinal < $yaux) {
                    $yfinal = $pdf->GetY();
                }
                $pdf->SetXY($x, $yinicial);
                $x = $pdf->GetX();
            } else {
                //descripcion cuando no es fiador
                $pdf->MultiCell($ancho_linea2 + 30, $tamanio_linea, (($garantias[$i]['descripcion'] == '' || $garantias[$i]['descripcion'] == null) ? ' ' : utf8_decode($garantias[$i]['descripcion'])), 0, 'L', $resaltado);
                $x = $x + ($ancho_linea2 + 30);
                $yaux = $pdf->GetY();

                if ($yfinal < $yaux) {
                    $yfinal = $pdf->GetY();
                }
                $pdf->SetXY($x, $yinicial);
                $x = $pdf->GetX();
                //direccion cuando
                $pdf->MultiCell($ancho_linea2 + 10, $tamanio_linea, (($garantias[$i]['direccion'] == '' || $garantias[$i]['direccion'] == null) ? ' ' : utf8_decode($garantias[$i]['direccion'])), 0, 'L', $resaltado);
                $x = $x + ($ancho_linea2 + 10);
                $yaux = $pdf->GetY();

                if ($yfinal < $yaux) {
                    $yfinal = $pdf->GetY();
                }
                $pdf->SetXY($x, $yinicial);
                $x = $pdf->GetX();
            }
            $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea, (($garantias[$i]['montogravamen'] == '' || $garantias[$i]['montogravamen'] == null) ? ' ' : (number_format($garantias[$i]['montogravamen'], 2, '.', ','))), 0, 0, 'R', $resaltado, '', 1, 0);
            //$pdf->Ln(6);
            $pdf->SetXY($xinicial, $yfinal + 1);
        }
    }
    $pdf->SetFont($fuente, 'B', 8);
    $pdf->CellFit($ancho_linea2 + 134, $tamanio_linea, 'TOTAL', 'T', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 29, $tamanio_linea, ' ', 0, 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea, (($garantias[0]['totalgravamen'] == '' || $garantias[0]['totalgravamen'] == null) ? ' ' : (number_format($garantias[0]['totalgravamen'], 2, '.', ','))), 'T', 0, 'R', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->CellFit($ancho_linea2 + 134, $tamanio_linea, ' ', 'T', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 29, $tamanio_linea, ' ', 0, 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea, ' ', 'T', 0, 'R', 0, '', 1, 0);
    $pdf->Ln(1);

    $pdf->CellFit($ancho_linea2 + 134, $tamanio_linea, ' ', 'T', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 29, $tamanio_linea, ' ', 0, 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea2 - 5, $tamanio_linea, ' ', 'T', 0, 'R', 0, '', 1, 0);
    $pdf->Ln(2);
}
