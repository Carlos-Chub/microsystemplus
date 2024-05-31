<?php
session_start();
include '../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');
include '../../../../src/funcphp/fun_ppg.php';
require '../../../../fpdf/WriteTag.php';
// require '../../../../fpdf/fpdf.php';
require '../../../../vendor/autoload.php';

use Luecano\NumeroALetras\NumeroALetras;

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

//se recibe los datos
$datos = $_POST["datosval"];
$inputs = $datos[0];
$selects = $datos[1];
$radios = $datos[2];
$archivo = $datos[3];
$tipo = $_POST["tipo"];
$codcredito = $archivo[0];

//SE CARGAN LOS DATOS
$strquery = "SELECT cm.CCODCTA AS ccodcta, cm.DFecDsbls AS fecdesem, cm.TipoEnti AS formcredito, dest.DestinoCredito AS destinocred, cm.MontoSol AS montosol, cm.MonSug AS montoapro, cm.noPeriodo AS cuotas, tbp.nombre, tbc.Credito AS tipperiodo, cm.Dictamen AS dictamen,
    cl.idcod_cliente AS codcli, cl.short_name AS nomcli, 
    (IFNULL((SELECT dep.nombre FROM clhpzzvb_bd_general_coopera.departamentos dep WHERE dep.codigo_departamento=cl.depa_reside),'--')) AS nomdep, 
	(IFNULL((SELECT mun.nombre FROM clhpzzvb_bd_general_coopera.municipios mun WHERE mun.codigo_municipio=cl.muni_reside),'--')) AS nommun, 
    cl.Direccion AS direccioncliente, cl.date_birth AS fecnacimiento, cl.no_identifica AS dpi, cl.estado_civil AS estcivil, 
    CONCAT((IFNULL((SELECT mun2.nombre FROM clhpzzvb_bd_general_coopera.municipios mun2 WHERE mun2.codigo_municipio=cl.muni_nacio),'--')),', ',(IFNULL((SELECT dep2.nombre FROM clhpzzvb_bd_general_coopera.departamentos dep2 WHERE dep2.codigo_departamento=cl.depa_nacio),'--'))) AS dirorigen,
    CONCAT((IFNULL((SELECT mun3.nombre FROM clhpzzvb_bd_general_coopera.municipios mun3 WHERE mun3.codigo_municipio=cl.muni_reside),'--')),', ',(IFNULL((SELECT dep3.nombre FROM clhpzzvb_bd_general_coopera.departamentos dep3 WHERE dep3.codigo_departamento=cl.depa_reside),'--')),', ',(IFNULL((cl.aldea_reside),'--'))) AS direside,
    cl.profesion AS profesion, cl.tel_no1 AS telefono, CONCAT((IFNULL(cl.Nomb_Ref1, 'NA')),', ',(IFNULL( cl.Tel_Ref1, 'NA'))) AS ref1, CONCAT((IFNULL(cl.Nomb_Ref2, 'NA')),', ',(IFNULL( cl.Tel_Ref2, 'NA'))) AS ref2, CONCAT((IFNULL(cl.Nomb_Ref3, 'NA')),', ',(IFNULL( cl.Tel_Ref3, 'NA'))) AS ref3, cl.no_tributaria AS nit,
    CONCAT(us.nombre,' ', us.apellido) AS analista,
    pr.id AS codprod, pr.nombre AS nomprod, pr.descripcion AS descprod, cm.NIntApro AS tasaprod, pr.porcentaje_mora AS mora, pr.tipo_mora AS tipmora, pr.tipo_calculo AS tipocalculo,
    ff.descripcion AS nomfondo, tbc.Credito AS formpago,
    (IFNULL((SELECT ppg2.ncapita FROM Cre_ppg ppg2 WHERE ppg2.ccodcta=cm.CCODCTA ORDER BY ppg2.dfecven ASC LIMIT 1),'x')) AS capitalppg,
    (IFNULL((SELECT ppg3.nintere FROM Cre_ppg ppg3 WHERE ppg3.ccodcta=cm.CCODCTA ORDER BY ppg3.dfecven ASC LIMIT 1),'x')) AS interesppg
    FROM cremcre_meta cm
    INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
    INNER JOIN tb_usuario us ON cm.CodAnal=us.id_usu
    INNER JOIN cre_productos pr ON cm.CCODPRD=pr.id
    INNER JOIN ctb_fuente_fondos ff ON pr.id_fondo=ff.id
    INNER JOIN clhpzzvb_bd_general_coopera.tb_destinocredito dest ON cm.Cdescre=dest.id_DestinoCredito
    INNER JOIN clhpzzvb_bd_general_coopera.tb_periodo tbp ON cm.NtipPerC=tbp.periodo
    INNER JOIN clhpzzvb_bd_general_coopera.tb_credito tbc ON cm.CtipCre=tbc.abre
    WHERE (cm.Cestado='F' OR cm.Cestado='D') AND cm.CCODCTA='$codcredito'
    GROUP BY tbp.periodo";
$query = mysqli_query($conexion, $strquery);
$data[] = [];
$j = 0;
$flag = false;
$codcli = "";
$codprod = "";
while ($fila = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
    $data[$j] = $fila;
    $codcli = $fila['codcli'];
    $codprod = $fila['codprod'];
    $flag = true;
    $j++;
}
//BUSCAR DATOS DE GARANTIAS
$strquery = "SELECT cl.idcod_cliente AS codcli, gr.idGarantia AS idgar, tipgar.id_TiposGarantia AS idtipgar, tipgar.TiposGarantia AS nomtipgar, tipc.idDoc AS idtipdoc, tipc.NombreDoc AS nomtipdoc, 
    gr.descripcionGarantia AS descripcion, gr.direccion AS direccion, gr.montoGravamen AS montogravamen,
    IFNULL((SELECT cl2.short_name AS nomcli FROM tb_cliente cl2 WHERE cl2.idcod_cliente=gr.descripcionGarantia AND tipgar.id_TiposGarantia=1 AND tipc.idDoc=1),'x') AS nomcli,
    IFNULL((SELECT cl2.Direccion AS direccioncli FROM tb_cliente cl2 WHERE cl2.idcod_cliente=gr.descripcionGarantia AND tipgar.id_TiposGarantia=1 AND tipc.idDoc=1),'x') AS direccioncli,
    IFNULL((SELECT '1' AS marcado FROM tb_garantias_creditos tgc WHERE tgc.id_cremcre_meta='$codcredito' AND tgc.id_garantia=gr.idGarantia),0) AS marcado,
    IFNULL((SELECT SUM(cli.montoGravamen) AS totalgravamen FROM tb_garantias_creditos tgc INNER JOIN cli_garantia cli ON cli.idGarantia=tgc.id_garantia WHERE tgc.id_cremcre_meta='$codcredito' AND cli.estado=1),0) AS totalgravamen
    FROM tb_cliente cl
    INNER JOIN cli_garantia gr ON cl.idcod_cliente=gr.idCliente
    INNER JOIN clhpzzvb_bd_general_coopera.tb_tiposgarantia tipgar ON gr.idTipoGa=tipgar.id_TiposGarantia
    INNER JOIN clhpzzvb_bd_general_coopera.tb_tiposdocumentosR tipc ON tipc.idDoc=gr.idTipoDoc
    WHERE cl.estado='1' AND gr.estado=1 AND cl.idcod_cliente='$codcli'";
$query = mysqli_query($conexion, $strquery);
$garantias[] = [];
$j = 0;
$flag2 = false;
while ($fila = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
    $garantias[$j] = $fila;
    $flag2 = true;
    $j++;
}

//BUSCAR GASTOS
$strquery = "SELECT cpg.id, cpg.id_producto, cpg.id_tipo_deGasto AS tipgasto, cpg.tipo_deCobro AS tipcobro, cpg.tipo_deMonto AS tipmonto, cpg.calculox AS calc, cpg.monto AS monto, ctg.nombre_gasto 
FROM cre_productos_gastos cpg 
INNER JOIN cre_tipogastos ctg ON cpg.id_tipo_deGasto=ctg.id
WHERE cpg.estado=1 AND ctg.estado=1 AND cpg.tipo_deCobro='1' AND cpg.id_producto='$codprod'";
$query = mysqli_query($conexion, $strquery);
$gastos[] = [];
$j = 0;
$flag3 = false;
while ($fila = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
    $gastos[$j] = $fila;
    $flag3 = true;
    $j++;
}

//BUSCAR DATOS DE INSTITUCION
$queryins = mysqli_query($conexion, "SELECT * FROM clhpzzvb_bd_general_coopera.info_coperativa ins
INNER JOIN tb_agencia ag ON ag.id_institucion=ins.id_cop where ag.id_agencia=".$_SESSION['id_agencia']);
$info[] = [];
$j = 0;
$flag4 = false;
while ($fil = mysqli_fetch_array($queryins, MYSQLI_ASSOC)) {
    $info[$j] = $fil;
    $flag4 = true;
    $j++;
}

//COMPROBACION: SI SE ENCONTRARON REGISTROS
if (!$flag || !$flag2 || !$flag4) {
    $opResult = array(
        'status' => 0,
        'mensaje' => 'No se encontraron datos, o no se cargaron algunos datos correctamente, intente nuevamente'. $flag.'f2'.$flag2.'f4'.$flag4,
        'dato' => $strquery
    );
    echo json_encode($opResult);
    return;
}

printpdf($data, $garantias, $gastos, $info, $flag3, $conexion);

function printpdf($datos, $garantias, $gastos, $info, $flag3, $conexion)
{

    //FIN COMPROBACION
    $oficina = "Coban";
    $institucion = utf8_decode($info[0]["nomb_comple"]);
    $direccionins = utf8_decode($info[0]["muni_lug"]);
    $emailins = $info[0]["emai"];
    $telefonosins = $info[0]["tel_1"] . '  ' . $info[0]["tel_2"];;
    $nitins = $info[0]["nit"];
    $rutalogomicro = "../../../../includes/img/logomicro.png";
    $rutalogoins = "../../../.." . $info[0]["log_img"];
    //lo que se tiene que repetir en cada una de las hojas
    class PDF extends PDF_WriteTag
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

        public function __construct($institucion, $pathlogo, $pathlogoins, $oficina, $direccion, $email, $telefono, $nit)
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
        }

        // Cabecera de página
        function Header()
        {
            $fuente = "Courier";
            $hoy = date("Y-m-d H:i:s");
            //fecha y usuario que genero el reporte
            $this->SetFont($fuente, '', 8);
            //$this->Cell(0, 2, $hoy, 0, 1, 'R');
            // Logo de la agencia
            $this->Image($this->pathlogoins, 10, 10, 33);

            //tipo de letra para el encabezado
            $this->SetFont($fuente, 'B', 7);

            $this->Cell(0, 2, $hoy, 0, 1, 'R');
            $this->Ln(1);
            $this->Cell(0, 2, $_SESSION['id'], 0, 1, 'R');

            // Logo de la agencia
            // $this->Image($this->pathlogoins, 10, 13, 33);

            // Título
            $this->Cell(0, 3, $this->institucion, 0, 1, 'C');
            $this->Cell(0, 3, $this->direccion, 0, 1, 'C');
            $this->Cell(0, 3, 'Email: ' . $this->email, 0, 1, 'C');
            $this->Cell(0, 3, 'Tel: ' . $this->telefono, 0, 1, 'C');
            $this->Cell(0, 3, 'NIT: ' . $this->nit, 'B', 1, 'C');
            // Salto de línea
            $this->Ln(3);
        }

        // Pie de página
        function Footer()
        {
            // Posición: a 1 cm del final
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            // Número de página
            $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }
    }
    $pdf = new PDF($institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins);
    $fuente = "Courier";
    $tamanio_linea = 5;
    $ancho_linea = 30;
    $tamañofuente = 10;

    $hoy = date('Y-m-d');
    $vlrs = [$info[0]["nomb_comple"] . ' (' . $info[0]["nomb_cor"] . ').', '(' . $info[0]["nomb_cor"] . ')', $info[0]["nomb_cor"], 'créditos'];
    $fechahoy = fechaletras($hoy);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->AliasNbPages();
    $pdf->AddPage();

    // Stylesheet
    $pdf->SetStyle("p", $fuente, "N", 10, "0,0,0", 0);
    $pdf->SetStyle("h1", $fuente, "N", 10, "0,0,0", 0);
    $pdf->SetStyle("a", $fuente, "BU", 10, "0,0,0");
    $pdf->SetStyle("pers", $fuente, "I", 0, "0,0,0");
    $pdf->SetStyle("place", "arial", "U", 0, "0,0,0");
    $pdf->SetStyle("vb", $fuente, "B", 0, "0,0,0");

    //DICTAMEN DE CREDITO Y FONDOS
    $pdf->SetFont($fuente, 'B', $tamañofuente);
    $pdf->CellFit($ancho_linea + 25, $tamanio_linea, utf8_decode('Dictamen de crédito No.:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 30, $tamanio_linea, $datos[0]['dictamen'], 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(5);
    $pdf->SetFont($fuente, 'B', $tamañofuente);
    $pdf->CellFit($ancho_linea + 25, $tamanio_linea, utf8_decode('Fondos Propios:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 60, $tamanio_linea, (($datos[0]['nomfondo'] == '' || $datos[0]['nomfondo'] == null) ? ' ' : utf8_decode($datos[0]['nomfondo'])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(2);
    $pdf->Cell(0, 3, ' ', 'B', 1, 'C');
    $pdf->Ln(4);

    //DATOS DEL SOLICITANTE
    $pdf->CellFit($ancho_linea + 30, $tamanio_linea, '1. DATOS DEL SOLICITANTE:', 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->Ln(6);
    //SUBINCISOS DEL SOLICITANTE
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('a) Código de crédito:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['ccodcta'] == '' || $datos[0]['ccodcta'] == null) ? ' ' : $datos[0]['ccodcta']), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('b) Nombre completo:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['nomcli'] == '' || $datos[0]['nomcli'] == null) ? ' ' : utf8_decode(mb_strtoupper($datos[0]['nomcli'],'utf-8'))), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('c) Código de cliente:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['codcli'] == '' || $datos[0]['codcli'] == null) ? ' ' : utf8_decode($datos[0]['codcli'])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('d) DPI:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['dpi'] == '' || $datos[0]['dpi'] == null) ? ' ' : utf8_decode($datos[0]['dpi'])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('e) Fecha de nacimiento:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['fecnacimiento'] == '' || $datos[0]['fecnacimiento'] == null || $datos[0]['fecnacimiento'] == '0000-00-00') ? ' ' : date("d-m-Y", strtotime($datos[0]['fecnacimiento']))), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('f) Edad:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['fecnacimiento'] == '' || $datos[0]['fecnacimiento'] == null || $datos[0]['fecnacimiento'] == '0000-00-00') ? ' ' : (obtener_edad_segun_fecha($datos[0]['fecnacimiento']) . utf8_decode(' Años'))), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('g) Estado civil:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['estcivil'] == '' || $datos[0]['estcivil'] == null) ? ' ' : utf8_decode($datos[0]['estcivil'])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('h) Originario de:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['dirorigen'] == '' || $datos[0]['dirorigen'] == null) ? ' ' : utf8_decode($datos[0]['dirorigen'])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('i) Residencia actual:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['direside'] == '' || $datos[0]['direside'] == null) ? ' ' : utf8_decode($datos[0]['direside'])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('j) Profesión:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['profesion'] == '' || $datos[0]['profesion'] == null) ? ' ' : utf8_decode($datos[0]['profesion'])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('k) Teléfono:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['telefono'] == '' || $datos[0]['telefono'] == null) ? ' ' : utf8_decode($datos[0]['telefono'])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('l) Referencia familiar:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['ref1'] == '' || $datos[0]['ref1'] == null) ? ' ' : utf8_decode($datos[0]['ref1'])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('m) Referencia comercial:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['ref2'] == '' || $datos[0]['ref2'] == null) ? ' ' : utf8_decode($datos[0]['ref2'])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('m) Referencia bancaria:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['ref3'] == '' || $datos[0]['ref3'] == null) ? ' ' : utf8_decode($datos[0]['ref3'])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('n) Identificación tributaria (NIT):'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['nit'] == '' || $datos[0]['nit'] == null) ? ' ' : utf8_decode($datos[0]['nit'])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(7);

    // DESTINO DEL CREDITO
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 52, $tamanio_linea, utf8_decode('2) DESTINO DEL CRÉDTIO:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['destinocred'] == '' || $datos[0]['destinocred'] == null) ? ' ' : utf8_decode($datos[0]['destinocred'])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    // MONTO DEL PRESTAMOS
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 52, $tamanio_linea, utf8_decode('3) MONTO DEL PRÉSTAMO:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['montoapro'] == '' || $datos[0]['montoapro'] == null) ? ' ' : 'Q ' . utf8_decode(number_format($datos[0]['montoapro'], 2, '.', ','))), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    // FORMA DE DESEMBOLSO
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 52, $tamanio_linea, utf8_decode('4) FORMA DE DESEMBOLSO:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, utf8_decode('Un desembolso después de la firma del contrato o escritura.'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);
    // <vb></vb>
    // FORMA DE PAGO
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 52, $tamanio_linea, utf8_decode('5) FORMA DE PAGO:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $sumacapint = 0;
    if ($datos[0]['capitalppg'] == 'x' || $datos[0]['interesppg'] == 'x') {
        //LLAMAR A LA FUNCION PARA CAPINT
        $datos_sum = creppg_temporal($datos[0]['ccodcta'], $conexion);
        $sumacapint = $datos_sum[0]['nintpag'] + $datos_sum[0]['ncappag'];
    } else {
        $sumacapint = $datos[0]['capitalppg'] + $datos[0]['interesppg'];
    }
    $texto = "<p>Cuotas " . (($datos[0]['tipperiodo'] == '' || $datos[0]['tipperiodo'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['tipperiodo']))) . " " . (($datos[0]['nombre'] == '' || $datos[0]['nombre'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['nombre']))) . "(ES) que incluyen capital e intereses por un valor de <vb>Q " . (number_format($sumacapint, 2, '.', ',')) . "</vb>.</p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(5);

    // TASA DE INTERES
    // (($datos[0]['intprod'] == '' || $datos[0]['intprod'] == null) ? ' ' : utf8_decode($datos[0]['intprod']) . '%')
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 52, $tamanio_linea, utf8_decode('6) TASA DE INTERÉS:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['tasaprod'] == '' || $datos[0]['tasaprod'] == null) ? ' ' : utf8_decode($datos[0]['tasaprod']) . '%'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    // PLAZO
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 52, $tamanio_linea, utf8_decode('7) PLAZO:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['cuotas'] == '' || $datos[0]['cuotas'] == null) ? ' ' : utf8_decode($datos[0]['cuotas'])) . " CUOTAS " . (($datos[0]['nombre'] == '' || $datos[0]['nombre'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['nombre']))) . "(ES) a partir del primer y " . utf8_decode('único') . " desembolso.", 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    //GARANTIAS
    $pdf->SetFont($fuente, 'BU', $tamañofuente);
    $pdf->CellFit($ancho_linea, $tamanio_linea, utf8_decode('8) GARANTÍAS'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);
    //ENCABEZADO DE GARANTIAS
    $pdf->SetFillColor(204, 229, 255);
    $pdf->SetFont($fuente, 'B', 9);
    $pdf->CellFit($ancho_linea, $tamanio_linea, utf8_decode('Tip. Garantia'), 'B', 0, 'C', 1, '', 1, 0);
    $pdf->CellFit($ancho_linea + 10, $tamanio_linea, utf8_decode('Tip. Documento'), 'B', 0, 'C', 1, '', 1, 0);
    $pdf->CellFit($ancho_linea + 40, $tamanio_linea, utf8_decode('Descripción'), 'B', 0, 'C', 1, '', 1, 0);
    $pdf->CellFit($ancho_linea + 20, $tamanio_linea, utf8_decode('Dirección'), 'B', 0, 'C', 1, '', 1, 0);
    $pdf->Ln(6);
    $pdf->SetFont($fuente, '', 9);
    // LLENAR CADA UNA DE LAS FILAS
    for ($i = 0; $i < count($garantias); $i++) {
        $resaltado = (($garantias[$i]['marcado'] == 1) ? 1 : 0);
        if ($resaltado == 1) {
            $resaltado = 0;
            $x = $pdf->GetX();
            $xinicial = $pdf->GetX();
            $yfinal = 0;
            $yinicial = $pdf->GetY();
            //tipo garantia
            $pdf->CellFit($ancho_linea, $tamanio_linea, (($garantias[$i]['nomtipgar'] == '' || $garantias[$i]['nomtipgar'] == null) ? ' ' : utf8_decode($garantias[$i]['nomtipgar'])), 0, 0, 'L', $resaltado, '', 0, 0);
            $x = $x + ($ancho_linea);
            $yaux = $pdf->GetY();
            $yfinal = $pdf->GetY();

            if ($yfinal < $yaux) {
                $yfinal = $pdf->GetY();
            }
            $pdf->SetXY($x, $yinicial);
            $x = $pdf->GetX();

            //tipo documento
            $pdf->MultiCell($ancho_linea + 10, $tamanio_linea, (($garantias[$i]['nomtipdoc'] == '' || $garantias[$i]['nomtipdoc'] == null) ? ' ' : utf8_decode($garantias[$i]['nomtipdoc'])), 0, 'L', $resaltado);
            $x = $x + ($ancho_linea + 10);
            $yaux = $pdf->GetY();

            if ($yfinal < $yaux) {
                $yfinal = $pdf->GetY();
            }
            $pdf->SetXY($x, $yinicial);
            $x = $pdf->GetX();
            if ($garantias[$i]["idtipgar"] == 1 && $garantias[$i]["idtipdoc"] == 1) {
                //nomcliente cuando es fiador
                $pdf->MultiCell($ancho_linea + 40, $tamanio_linea, (($garantias[$i]['nomcli'] == '' || $garantias[$i]['nomcli'] == null) ? ' ' : utf8_decode($garantias[$i]['nomcli'])), 0, 'L', $resaltado);
                $x = $x + ($ancho_linea + 40);
                $yaux = $pdf->GetY();

                if ($yfinal < $yaux) {
                    $yfinal = $pdf->GetY();
                }
                $pdf->SetXY($x, $yinicial);
                $x = $pdf->GetX();

                //direccion cuando es fiador
                $pdf->MultiCell($ancho_linea + 20, $tamanio_linea, (($garantias[$i]['direccioncli'] == '' || $garantias[$i]['direccioncli'] == null) ? ' ' : utf8_decode($garantias[$i]['direccioncli'])), 0, 'L', $resaltado);
                $x = $x + ($ancho_linea + 20);
                $yaux = $pdf->GetY();

                if ($yfinal < $yaux) {
                    $yfinal = $pdf->GetY();
                }
                $pdf->SetXY($x, $yinicial);
                $x = $pdf->GetX();
            } else {
                //descripcion cuando no es fiador
                $pdf->MultiCell($ancho_linea + 40, $tamanio_linea, (($garantias[$i]['descripcion'] == '' || $garantias[$i]['descripcion'] == null) ? ' ' : utf8_decode($garantias[$i]['descripcion'])), 0, 'L', $resaltado);
                $x = $x + ($ancho_linea + 40);
                $yaux = $pdf->GetY();

                if ($yfinal < $yaux) {
                    $yfinal = $pdf->GetY();
                }
                $pdf->SetXY($x, $yinicial);
                $x = $pdf->GetX();
                //direccion cuando
                $pdf->MultiCell($ancho_linea + 20, $tamanio_linea, (($garantias[$i]['direccion'] == '' || $garantias[$i]['direccion'] == null) ? ' ' : utf8_decode($garantias[$i]['direccion'])), 0, 'L', $resaltado);
                $x = $x + ($ancho_linea + 20);
                $yaux = $pdf->GetY();

                if ($yfinal < $yaux) {
                    $yfinal = $pdf->GetY();
                }
                $pdf->SetXY($x, $yinicial);
                $x = $pdf->GetX();
            }
            $pdf->SetXY($xinicial, $yfinal + 1);
            $pdf->Cell(0, 0, ' ', 'B', 1, 'C');
            $pdf->SetXY($xinicial, $yfinal + 2);
        }
    }

    // UBICACION DE CLIENTE
    $pdf->Ln(2);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 52, $tamanio_linea, utf8_decode('9) UBICACIÓN:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['direccioncliente'] == '' || $datos[0]['direccioncliente'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['direccioncliente']))) . (($datos[0]['nommun'] == '' || $datos[0]['nommun'] == null) ? ' ' : ", " . utf8_decode(strtoupper($datos[0]['nommun']))) . (($datos[0]['nomdep'] == '' || $datos[0]['nomdep'] == null) ? ' ' : ", " . utf8_decode(strtoupper($datos[0]['nomdep']))), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    //COMISION DE FORMALIZACION
    $pdf->CellFit($ancho_linea + 30, $tamanio_linea, utf8_decode('10) DESCUENTOS:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(5);
    //GASTOS DEL CRÉDITO
    $pdf->SetStyle("p", $fuente, "N", 10, "0,0,0", 5);
    $banderafor = false;
    if ($flag3) {
        for ($i = 0; $i < count($gastos); $i++) {
            $tipomonto = (($gastos[$i]['tipmonto'] == 1) ? "" : "%");
            if ($gastos[$i]['calc'] == 1) {
                //GASTO FIJO
                $texto = "<p>10." . ($i + 1) . ". " . (($gastos[$i]['nombre_gasto'] == '' || $gastos[$i]['nombre_gasto'] == null) ? ' ' : utf8_decode(strtoupper($gastos[$i]['nombre_gasto']))) . ": " . (($gastos[$i]['tipmonto'] == 1) ? "Q " : "") . ($gastos[$i]['monto']) . $tipomonto . " fijo(s) del " . utf8_decode('préstamo') . " a que se refiriere la presente " . utf8_decode('resolución ') . ".</p>";
                $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
                $pdf->Ln(3);
                $banderafor = true;
            } elseif ($gastos[$i]['calc'] == 2) {
                //GASTO POR PLAZO
                $texto = "<p>10." . ($i + 1) . ". " . (($gastos[$i]['nombre_gasto'] == '' || $gastos[$i]['nombre_gasto'] == null) ? ' ' : utf8_decode(strtoupper($gastos[$i]['nombre_gasto']))) . ": " . (($gastos[$i]['tipmonto'] == 1) ? "Q " : "") . ($gastos[$i]['monto']) . $tipomonto . " sobre el plazo del " . utf8_decode('préstamo') . " a que se refiriere la presente " . utf8_decode('resolución ') . ".</p>";
                $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
                $pdf->Ln(3);
                $banderafor = true;
            } elseif ($gastos[$i]['calc'] == 3) {
                $texto = "<p>10." . ($i + 1) . ". " . (($gastos[$i]['nombre_gasto'] == '' || $gastos[$i]['nombre_gasto'] == null) ? ' ' : utf8_decode(strtoupper($gastos[$i]['nombre_gasto']))) . ": " . (($gastos[$i]['tipmonto'] == 1) ? "Q " : "") . ($gastos[$i]['monto']) . $tipomonto . " sobre el plazo por el monto del " . utf8_decode('préstamo') . " a que se refiriere la presente " . utf8_decode('resolución') . ".</p>";
                $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
                $pdf->Ln(3);
                $banderafor = true;
            } elseif ($gastos[$i]['calc'] == 4) {
                $texto = "<p>10." . ($i + 1) . ". " . (($gastos[$i]['nombre_gasto'] == '' || $gastos[$i]['nombre_gasto'] == null) ? ' ' : utf8_decode(strtoupper($gastos[$i]['nombre_gasto']))) . ": " . (($gastos[$i]['tipmonto'] == 1) ? "Q " : "") . ($gastos[$i]['monto']) . $tipomonto . " sobre el monto del " . utf8_decode('préstamo') . " a que se refiriere la presente " . utf8_decode('resolución') . ".</p>";
                $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
                $pdf->Ln(3);
                $banderafor = true;
            }
        }
    }

    if ($banderafor) {
        $pdf->Ln(1);
    } else {
        $pdf->Ln(2);
    }

    // CONDICIONES GENERALES
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 52, $tamanio_linea, utf8_decode('11) CONDICIONES GENERALES:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);
    //primera condicion
    $pdf->SetFont($fuente, 'B', $tamañofuente);
    $pdf->SetStyle("p", $fuente, "N", 10, "0,0,0", 0);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $texto = "<p>11.1. El atraso de una sola cuota de  <vb>" . utf8_decode($info[0]["nomb_comple"]) . "" . $vlrs[1] . "</vb> " . utf8_decode('podrá') . " dar por plazo vencido el contrato pagare o escritura y " . utf8_decode('solicitará') . " por la " . utf8_decode('vía') . " correspondiente el pago inmediato de los saldos del " . utf8_decode('crédito, así') . " como el pago de los intereses convenidos.</p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(5);

    // segunda condicion
    $mensajetipcalc = "";
    if ($datos[0]['tipocalculo'] == 1) {
        $mensajetipcalc = $datos[0]['mora'] . "% al total de la cuota vencida.";
    } elseif ($datos[0]['tipocalculo'] == 2) {
        $mensajetipcalc = $datos[0]['mora'] . "% al capital de la cuota vencida.";
    } elseif ($datos[0]['tipocalculo'] == 3) {
        $mensajetipcalc = $datos[0]['mora'] . "% al saldo de capital.";
    } elseif ($datos[0]['tipocalculo'] == 4) {
        $mensajetipcalc = "saldo de capital por un valor de Q. " . $datos[0]['mora'] . "";
    }
    if ($datos[0]['tipmora'] == 2) {
        $pdf->SetFont($fuente, '', $tamañofuente);
        $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
        $texto = "<p>11.2. El pago del " . utf8_decode('préstamo') . " se realizara en la oficinas de <vb>" . utf8_decode($info[0]["nomb_comple"]) . "" . $vlrs[1] . "</vb>  sin necesidad de cobro y un recargo al " . $mensajetipcalc . "</p>";
        $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
        $pdf->Ln(5);
    } elseif ($datos[0]['tipmora'] == 1) {
        $pdf->SetFont($fuente, '', $tamañofuente);
        $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
        $texto = "<p>11.2. Recargo por mora, si llegara a retrasar los pagos se realizara un recargo por mora del " . $mensajetipcalc . "</p>";
        $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
        $pdf->Ln(5);
    }

    //tercera condicion
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $texto = "<p> 11.3. En base al " . utf8_decode('análisis') . " efectuado a la " . utf8_decode('señor/a') . " <pers><vb>" . (($datos[0]['nomcli'] == '' || $datos[0]['nomcli'] == null) ? ' ' : utf8_decode(mb_strtoupper($datos[0]['nomcli'],'utf-8'))) . "</vb></pers> (deudor), posterior a la " . utf8_decode('obtención de la información') . " financiera, se " . utf8_decode('comprobó') . " que la diferencia entre sus ingresos y egreso, arroja un saldo solvente, " . utf8_decode('determinándose') . " que es mismo posee la capacidad suficiente para cubrir mediante cuotas mensuales el " . utf8_decode('crédtio') . " que solicita.</p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(5);

    // cuarta condicion
    $textdescgar = "";
    for ($i = 0; $i < count($garantias); $i++) {
        $resaltado = (($garantias[$i]['marcado'] == 1) ? 1 : 0);
        if ($resaltado == 1) {
            if ($i > 0) {
                $textdescgar .= ", ";
            }
            if ($garantias[$i]["idtipgar"] == 1 && $garantias[$i]["idtipdoc"] == 1) {
                $textdescgar .= (($garantias[$i]['nomcli'] == '' || $garantias[$i]['nomcli'] == null) ? ' ' : utf8_decode($garantias[$i]['nomcli'])) . "(fiador)";
            } else {
                $textdescgar .= (($garantias[$i]['descripcion'] == '' || $garantias[$i]['descripcion'] == null) ? ' ' : utf8_decode($garantias[$i]['descripcion']));
            }
        }
    }
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $texto = "<p> 11.4. Asimismo el financiamiento a obtener, " . utf8_decode('será') . " retornado mediante la " . utf8_decode('garantías') . " siguientes: " . $textdescgar . utf8_decode('; las cuales se mantendrán') . " vigentes hasta la " . utf8_decode('cancelación') . " total del financiamiento, " . utf8_decode('las cuales') . ", poseen la capacidad necesaria para crubrir cualquier saldo insoluto, que dejare la normal " . utf8_decode('recuperación') . " del mismo, tanto en capital como en intereses.</p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(5);

    //CONDICION APARTE DE GENERALES
    $pdf->SetFont($fuente, '', $tamañofuente);
    $texto = "<p> 12) Se aprueba la solicitud al " . utf8_decode('crédito') . " requerido, en virtud que dentro del reglamento <pers><vb>" . (($datos[0]['nomfondo'] == '' || $datos[0]['nomfondo'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['nomfondo']))) . "</vb></pers> el mismo cumple con los requisitos exigidos para ser beneficiario a " . utf8_decode('través') . " del mismo, por lo que se eleva al respetable " . utf8_decode('comité de créditos para su aprobación') . " correspondiente.</p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(5);

    // CONCLUSION
    $pdf->SetFont($fuente, '', $tamañofuente);
    $texto = "<p> 13) En " . utf8_decode('conclusión el análisis') . " efectuado sobre la " . utf8_decode('información') . " financiera presentada por la empresaria/o: <pers><vb>" . (($datos[0]['nomcli'] == '' || $datos[0]['nomcli'] == null) ? ' ' : utf8_decode(mb_strtoupper($datos[0]['nomcli'],'utf-8'))) . "</vb></pers> se eleva la solicitud de " . utf8_decode('préstamo al comité de créditos') . ", en las condiciones siguientes: </p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(5);

    //DETALLE CONCLUSION
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('a) MONTO DEL FINANCIAMIENTO:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, 'BI', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['montoapro'] == '' || $datos[0]['montoapro'] == null) ? ' ' : 'Q ' . utf8_decode(number_format($datos[0]['montoapro'], 2, '.', ','))), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('b) TASA DE INTERÉS:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, 'BI', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['tasaprod'] == '' || $datos[0]['tasaprod'] == null) ? ' ' : utf8_decode($datos[0]['tasaprod']) . '%'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(6);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea - 23, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 45, $tamanio_linea, utf8_decode('c) PLAZO AUTORIZADO:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, 'BI', $tamañofuente);
    $pdf->CellFit($ancho_linea + 78, $tamanio_linea, (($datos[0]['cuotas'] == '' || $datos[0]['cuotas'] == null) ? ' ' : utf8_decode($datos[0]['cuotas'])) . " CUOTAS " . (($datos[0]['nombre'] == '' || $datos[0]['nombre'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['nombre']))) . "(ES)", 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(7);

    // FINANCIAMIENTO
    $pdf->SetFont($fuente, '', $tamañofuente);
    $texto = "<p>14) Financiamiento a otorgarse con recursos del Fideicomiso fondo: <pers><vb>" . (($datos[0]['nomfondo'] == '' || $datos[0]['nomfondo'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['nomfondo']))) . "</vb></pers>.</p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(7);

    //FIRMAS
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 10, $tamanio_linea, utf8_decode('APROBADO POR:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 25, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 10, $tamanio_linea, utf8_decode('VISTO BUENO:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 25, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(10);

    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 10, $tamanio_linea, utf8_decode('ASESOR DE CRÉDITO:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 25, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 10, $tamanio_linea, utf8_decode('GERENCIA:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 25, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(12);

    // OBSERVACIONES
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea, $tamanio_linea, utf8_decode('OBSERVACIONES:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 130, $tamanio_linea, ' ', 'B', 0, 'L', 0, '', 1, 0);
    $pdf->Ln(5);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit(0, $tamanio_linea, ' ', 'B', 0, 'L', 0, '', 1, 0);
    $pdf->Ln(5);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit(0, $tamanio_linea, ' ', 'B', 0, 'L', 0, '', 1, 0);
    $pdf->Ln(5);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit(0, $tamanio_linea, ' ', 'B', 0, 'L', 0, '', 1, 0);
    $pdf->Ln(9);

    // DIRECCION Y FECHA
    // <p>
    // <vb></vb>
    // </p>
    //<pers><vb></vb></pers>

    $pdf->SetStyle("p", $fuente, "N", 8, "0,0,0", 0);
    $texto = "<p>" . utf8_decode($info[0]["nomb_comple"]) . "" . $vlrs[1] . ", " . utf8_decode(strtoupper($info[0]["muni_lug"])) . ", GUATEMALA. <pers><vb>" . $fechahoy . "</vb></pers></p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(5);

    ob_start();
    $pdf->Output();
    $pdfData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Comprobante generado correctamente',
        'namefile' => "Dictamen-" . (($datos[0]['ccodcta'] == '' || $datos[0]['ccodcta'] == null) ? ' ' : ($datos[0]['ccodcta'])),
        'tipo' => "pdf",
        'data' => "data:application/pdf;base64," . base64_encode($pdfData)
    );
    echo json_encode($opResult);
}

function fechaletras($date)
{
    $date = substr($date, 0, 10);
    $numeroDia = date('d', strtotime($date));
    $mes = date('F', strtotime($date));
    $anio = date('Y', strtotime($date));
    $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $nombreMes = str_replace($meses_EN, $meses_ES, $mes);
    return $numeroDia . " de " . $nombreMes . " de " . $anio;
}

function obtener_edad_segun_fecha($fecha_nacimiento)
{
    $nacimiento = new DateTime($fecha_nacimiento);
    $ahora = new DateTime(date("Y-m-d"));
    $diferencia = $ahora->diff($nacimiento);
    return $diferencia->format("%y");
}
