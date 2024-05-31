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
$strquery = "SELECT cm.CCODCTA AS ccodcta, cm.DFecDsbls AS fecdesem, cm.TipoEnti AS formcredito, dest.DestinoCredito AS destinocred, cm.MonSug AS montoapro, cm.noPeriodo AS cuotas, tbp.nombre, cm.Dictamen AS dictamen,
    cl.idcod_cliente AS codcli, cl.short_name AS nomcli, cl.Direccion AS direccioncliente,
    CONCAT(us.nombre,' ', us.apellido) AS analista,
    pr.id AS codprod, pr.nombre AS nomprod, pr.descripcion AS descprod, cm.NIntApro AS tasaprod, pr.porcentaje_mora AS mora,
    ff.descripcion AS nomfondo,
    (IFNULL((SELECT ppg2.ncapita FROM Cre_ppg ppg2 WHERE ppg2.ccodcta=cm.CCODCTA ORDER BY ppg2.dfecven ASC LIMIT 1),'x')) AS capitalppg,
    (IFNULL((SELECT ppg3.nintere FROM Cre_ppg ppg3 WHERE ppg3.ccodcta=cm.CCODCTA ORDER BY ppg3.dfecven ASC LIMIT 1),'x')) AS interesppg,
    (IFNULL((SELECT dep.nombre FROM clhpzzvb_bd_general_coopera.departamentos dep WHERE dep.codigo_departamento = cl.depa_reside),'-')) AS nomdep,
    (IFNULL((SELECT mun.nombre FROM clhpzzvb_bd_general_coopera.municipios mun WHERE mun.codigo_municipio = cl.muni_reside),'-')) AS nommun
    FROM cremcre_meta cm
    INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
    INNER JOIN tb_usuario us ON cm.CodAnal=us.id_usu
    INNER JOIN cre_productos pr ON cm.CCODPRD=pr.id
    INNER JOIN ctb_fuente_fondos ff ON pr.id_fondo=ff.id
    INNER JOIN clhpzzvb_bd_general_coopera.tb_destinocredito dest ON cm.Cdescre=dest.id_DestinoCredito
    INNER JOIN clhpzzvb_bd_general_coopera.tb_periodo tbp ON cm.NtipPerC=tbp.periodo
    WHERE (cm.Cestado='F' OR cm.Cestado='E' OR cm.Cestado='D') AND cm.CCODCTA='$codcredito'
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
$strquery = "SELECT cpg.id, cpg.id_producto, cpg.id_tipo_deGasto AS tipgasto, cpg.tipo_deCobro AS tipcobro, cpg.tipo_deMonto AS tipmonto, cpg.calculox AS calc, cpg.monto AS monto, ctg.nombre_gasto FROM cre_productos_gastos cpg 
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
INNER JOIN tb_agencia ag ON ag.id_institucion=ins.id_cop where ag.id_agencia=" . $_SESSION['id_agencia']);
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
        'mensaje' => 'No se encontraron datos, o no se cargaron algunos datos correctamente, intente nuevamente' . $flag . "f2" . $flag2 . "f3" . $flag3 . "f4" . $flag4,
        'dato' => $strquery
    );
    echo json_encode($opResult);
    return;
}

printpdf($data, $garantias, $gastos, $info, $flag3, $conexion);

function printpdf($datos, $garantias, $gastos, $info, $flag3, $conexion)
{

    //FIN COMPROBACION
    $oficina = utf8_decode($info[0]["nom_agencia"]);
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
    $pdf->SetStyle("place", $fuente, "U", 0, "0,0,0");
    $pdf->SetStyle("vb", $fuente, "B", 0, "0,0,0");

    //NUMERO DE CREDITO Y ANALISTA
    $pdf->SetFont($fuente, 'B', $tamañofuente);
    $pdf->CellFit($ancho_linea, $tamanio_linea, utf8_decode('No. Crédito:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 30, $tamanio_linea, $datos[0]['ccodcta'], 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(5);
    $pdf->SetFont($fuente, 'B', $tamañofuente);
    $pdf->CellFit($ancho_linea, $tamanio_linea, utf8_decode('Responsable:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 60, $tamanio_linea, (($datos[0]['analista'] == '' || $datos[0]['analista'] == null) ? ' ' : utf8_decode($datos[0]['analista'])), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(10);

    // <p>
    // <vb></vb>
    // </p>
    //<pers></pers>

    //PRIMER PARRAFO
    $pdf->SetFont($fuente, 'B', $tamañofuente - 1);
    $texto = "<p><pers><vb>EL COMITE DE CREDITO DE LA " . utf8_decode($info[0]["nomb_comple"]) . "" . $vlrs[1] . ", CON SEDE EN " . utf8_decode(strtoupper($info[0]["muni_lug"])) . ", DE LA CIUDAD DE GUATEMALA.</vb></pers>Con base al reglamento general de " . utf8_decode('créditos') . " de " . utf8_decode($vlrs[1]) . " y el dictamen No. " . (($datos[0]['dictamen'] == '' || $datos[0]['dictamen'] == null) ? ' ' : utf8_decode($datos[0]['dictamen'])) . " del departamento de " . utf8_decode('créditos') . " con fecha " . $fechahoy . "; el " . utf8_decode('cómite') . " de " . utf8_decode('créditos') . " con la facultad que le confiere.--------</p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(5);

    //DATOS COMPLEMENTARIOS

    //TITULO
    $pdf->SetFont($fuente, 'IBU', $tamañofuente);
    $pdf->Cell(0, 3, 'RESUELVE', 0, 1, 'C');
    $pdf->Ln(3);

    //NOMBRE CLIENTE Y FONDO
    $pdf->SetFont($fuente, 'B', $tamañofuente - 1);
    $texto = "<p><pers>AUTORIZAR " . utf8_decode('CRÉDITO') . " A <vb>" . (($datos[0]['nomcli'] == '' || $datos[0]['nomcli'] == null) ? ' ' : utf8_decode(mb_strtoupper($datos[0]['nomcli'], 'utf-8'))) . ",</vb> CON RECURSOS PROVENIENTES DEL FONDO: <vb>" . (($datos[0]['nomfondo'] == '' || $datos[0]['nomfondo'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['nomfondo']))) . ".</vb></pers></p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(4);

    //MODALIDAD Y TIPO DE OPERACION
    $pdf->SetFont($fuente, 'BI', $tamañofuente);
    $pdf->CellFit($ancho_linea, $tamanio_linea, utf8_decode('MODALIDAD'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 60, $tamanio_linea, (($datos[0]['nomprod'] == '' || $datos[0]['nomprod'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['nomprod']))), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(8);
    $pdf->SetFont($fuente, 'BI', $tamañofuente);
    $pdf->CellFit($ancho_linea, $tamanio_linea, utf8_decode('TIPO DE OPERACIÓN'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $tipocredito = "CRÉDITO INDIVIDUAL";
    if ($datos[0]['formcredito'] != '' || $datos[0]['formcredito'] != null) {
        if ($datos[0]['formcredito'] != 'INDI') {
            $tipocredito = "CRÉDITO GRUPAL";
        }
    }
    $pdf->CellFit($ancho_linea + 60, $tamanio_linea, utf8_decode($tipocredito), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(8);

    //DESTINO
    $pdf->SetFont($fuente, 'BI', $tamañofuente);
    $pdf->CellFit($ancho_linea, $tamanio_linea, utf8_decode('DESTINO'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 60, $tamanio_linea, (($datos[0]['destinocred'] == '' || $datos[0]['destinocred'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['destinocred']))), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(8);

    //GARANTIAS
    $pdf->SetFont($fuente, 'BIU', $tamañofuente);
    $pdf->CellFit($ancho_linea, $tamanio_linea, utf8_decode('GARANTÍAS'), 0, 0, 'L', 0, '', 1, 0);
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
    //LLENAR CADA UNA DE LAS FILAS
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
    //UBICACION DE CLIENTE
    $pdf->Ln(3);
    $pdf->SetFont($fuente, 'BI', $tamañofuente);
    $pdf->CellFit($ancho_linea, $tamanio_linea, utf8_decode('UBICACIÓN'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 130, $tamanio_linea, (($datos[0]['direccioncliente'] == '' || $datos[0]['direccioncliente'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['direccioncliente']))) . (($datos[0]['nommun'] == '' || $datos[0]['nommun'] == null) ? ' ' : ", " . utf8_decode(strtoupper($datos[0]['nommun']))) . (($datos[0]['nomdep'] == '' || $datos[0]['nomdep'] == null) ? ' ' : ", " . utf8_decode(strtoupper($datos[0]['nomdep']))), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(8);

    //FORMA DE ENTREGA

    $texto = "<p>FORMA DE ENTREGA: Posteriormente de firmado el respectivo contrato, pagare o escritura del " . utf8_decode('préstamo') . " a que se refiere la presente " . utf8_decode('resolución') . " se procedera " . utf8_decode('así:') . " <vb><pers>Directamente al solicitante en un sola entrega mediante documento legal de pago (cheque) a nombre de " . (($datos[0]['nomcli'] == '' || $datos[0]['nomcli'] == null) ? ' ' : utf8_decode(mb_strtoupper($datos[0]['nomcli'], 'utf-8'))) . "</pers></vb> por la cantidad de Q. <vb>" . (($datos[0]['montoapro'] == '' || $datos[0]['montoapro'] == null) ? ' ' : utf8_decode(number_format($datos[0]['montoapro'], 2, '.', ','))) . ".</p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(4);

    //TOTAL A DESEMBOLSAR
    $pdf->SetFont($fuente, 'BI', $tamañofuente);
    $pdf->CellFit($ancho_linea + 20, $tamanio_linea, utf8_decode('TOTAL A DESEMBOLSAR:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($fuente, '', $tamañofuente);
    $pdf->CellFit($ancho_linea + 110, $tamanio_linea, "Q " . (($datos[0]['montoapro'] == '' || $datos[0]['montoapro'] == null) ? ' ' : utf8_decode(number_format($datos[0]['montoapro'], 2, '.', ','))), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(8);

    //PLAZO
    $texto = "<p>PLAZO: <vb><pers>" . (($datos[0]['cuotas'] == '' || $datos[0]['cuotas'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['cuotas']))) . " CUOTAS </pers></vb>contados a partir de la fecha del desembolso de los fondos del " . utf8_decode('crédito') . ".</p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(4);

    //TASA DE INTERES
    $texto = "<p>TASA DE " . utf8_decode('INTERÉS: ') . "<vb><pers>" . (($datos[0]['tasaprod'] == '' || $datos[0]['tasaprod'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['tasaprod']))) . "% </pers></vb></p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(4);

    //RECARGO POR MORA
    $texto = "<p>RECARGO POR MORA: De no cancelarse los intereses en la fecha " . utf8_decode('señalada') . ", la <vb>" . utf8_decode($info[0]["nomb_comple"]) . "" . $vlrs[1] . "</vb>, cobrara un recargo anual sobre los intereses vencidos del <vb>" . (($datos[0]['mora'] == '' || $datos[0]['mora'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['mora']))) . "%</vb> sobre la tasa vigente los cuales deberan computarse a partir del primer dia respectivo del vencimiento.</p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(4);

    //FORMA DE RECUPERACION
    $texto = "<p>FORMA DE " . utf8_decode('RECUPERACIÓN: ') . "";
    //recuperar el primer pago o plazo
    $sumacapint = 0;
    if ($datos[0]['capitalppg'] == 'x' || $datos[0]['interesppg'] == 'x') {
        //LLAMAR A LA FUNCION PARA CAPINT
        $datos_sum = creppg_temporal($datos[0]['ccodcta'], $conexion);
        $sumacapint = $datos_sum[0]['nintpag'] + $datos_sum[0]['ncappag'];
    } else {
        $sumacapint = $datos[0]['capitalppg'] + $datos[0]['interesppg'];
        $datos_sum = creppg_get($datos[0]['ccodcta'], $conexion);
    }
    //CUOTAS PPG 
    $idcuotas = array_keys(array_unique(array_column($datos_sum, "cuota")));
    foreach ($idcuotas as $idp) {
        $condicion = $datos_sum[$idp]['cuota'];
        $cant = count(array_filter(array_column($datos_sum, 'cuota'), function ($var) use ($condicion) {
            return ($var == $condicion);
        }));
        $cuotatxt = ($cant > 1) ? ' CUOTAS ' : ' CUOTA ';
        $texto .= "<vb><pers>" . $cant . $cuotatxt . (($datos[0]['nombre'] == '' || $datos[0]['nombre'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['nombre'])));
        $texto .= ($cant > 1) ? 'ES ' : ' ';
        $texto .= "</pers></vb> que incluye el capital e intereses por <vb>Q " . number_format(($condicion), 2, '.', ',') . "</vb>";
        $texto .= ($cant > 1) ? ' cada una, ' : ', ';
    }
    $texto .= " a partir de la fecha de desembolso de los fondos del " . utf8_decode('crédito') . ".</p>";
    //FIN CUOTAS PPG
    //$texto = "<p>FORMA DE " . utf8_decode('RECUPERACIÓN: ') . "<vb><pers>" . (($datos[0]['cuotas'] == '' || $datos[0]['cuotas'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['cuotas']))) . " CUOTAS " . (($datos[0]['nombre'] == '' || $datos[0]['nombre'] == null) ? ' ' : utf8_decode(strtoupper($datos[0]['nombre']))) . "ES</pers></vb> que incluye el capital e intereses por <vb>Q " . ($sumacapint) . "</vb> cada una,  a partir de la fecha de desembolso de los fondos del " . utf8_decode('crédito') . ".</p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(4);

    //COMISION DE FORMALIZACION
    $pdf->CellFit($ancho_linea + 30, $tamanio_linea, utf8_decode('DESCUENTOS:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(5);
    //GASTOS DEL CRÉDITO
    $pdf->SetStyle("p", $fuente, "N", 10, "0,0,0", 5);
    $banderafor = false;
    if ($flag3) {
        for ($i = 0; $i < count($gastos); $i++) {
            $tipomonto = (($gastos[$i]['tipmonto'] == 1) ? "" : "%");
            if ($gastos[$i]['calc'] == 1) {
                //GASTO FIJO
                $texto = "<p>" . ($i + 1) . ". " . (($gastos[$i]['nombre_gasto'] == '' || $gastos[$i]['nombre_gasto'] == null) ? ' ' : utf8_decode(strtoupper($gastos[$i]['nombre_gasto']))) . ": " . (($gastos[$i]['tipmonto'] == 1) ? "Q " : "") . ($gastos[$i]['monto']) . $tipomonto . " fijo(s) del " . utf8_decode('préstamo') . " a que se refiriere la presente " . utf8_decode('resolución ') . ".</p>";
                $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
                $pdf->Ln(3);
                $banderafor = true;
            } elseif ($gastos[$i]['calc'] == 2) {
                //GASTO POR PLAZO
                $texto = "<p>" . ($i + 1) . ". " . (($gastos[$i]['nombre_gasto'] == '' || $gastos[$i]['nombre_gasto'] == null) ? ' ' : utf8_decode(strtoupper($gastos[$i]['nombre_gasto']))) . ": " . (($gastos[$i]['tipmonto'] == 1) ? "Q " : "") . ($gastos[$i]['monto']) . $tipomonto . " sobre el plazo del " . utf8_decode('préstamo') . " a que se refiriere la presente " . utf8_decode('resolución ') . ".</p>";
                $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
                $pdf->Ln(3);
                $banderafor = true;
            } elseif ($gastos[$i]['calc'] == 3) {
                $texto = "<p>" . ($i + 1) . ". " . (($gastos[$i]['nombre_gasto'] == '' || $gastos[$i]['nombre_gasto'] == null) ? ' ' : utf8_decode(strtoupper($gastos[$i]['nombre_gasto']))) . ": " . (($gastos[$i]['tipmonto'] == 1) ? "Q " : "") . ($gastos[$i]['monto']) . $tipomonto . " sobre el plazo por el monto del " . utf8_decode('préstamo') . " a que se refiriere la presente " . utf8_decode('resolución') . ".</p>";
                $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
                $pdf->Ln(3);
                $banderafor = true;
            } elseif ($gastos[$i]['calc'] == 4) {
                $texto = "<p>" . ($i + 1) . ". " . (($gastos[$i]['nombre_gasto'] == '' || $gastos[$i]['nombre_gasto'] == null) ? ' ' : utf8_decode(strtoupper($gastos[$i]['nombre_gasto']))) . ": " . (($gastos[$i]['tipmonto'] == 1) ? "Q " : "") . ($gastos[$i]['monto']) . $tipomonto . " sobre el monto del " . utf8_decode('préstamo') . " a que se refiriere la presente " . utf8_decode('resolución') . ".</p>";
                $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
                $pdf->Ln(3);
                $banderafor = true;
            }
        }
    }

    if ($banderafor) {
        $pdf->Ln(1);
    } else {
        $pdf->Ln(4);
    }

    //OTRAS CONDICIONES
    $pdf->CellFit($ancho_linea + 30, $tamanio_linea, utf8_decode('OTRAS CONDICIONES:'), 0, 0, 'L', 0, '', 1, 0);
    $pdf->Ln(5);
    //condicion a
    $texto = "<p>a) El solicitante se compromete a presentar cualquier otra " . utf8_decode('información') . " que la " . utf8_decode($info[0]["nomb_comple"]) . "" . $vlrs[1] . ", requiera (contable, financiera, administrativa, social o legal) cuando se realicen las inspecciones de campo y se verifique la correcta " . utf8_decode('inversión') . " de los fondos de acuerdo al destino indicado.</p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(3);

    //condicion b
    $texto = "<p>b) La presente " . utf8_decode('resolución') . " tendra un plazo de SESENTA DIAS (60) " . utf8_decode('hábiles') . " para la " . utf8_decode('formalización') . " contados a partir de la fecha de " . utf8_decode('notificación') . " al solicitante.</p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(4);

    $pdf->SetStyle("p", $fuente, "N", 10, "0,0,0", 0);
    $texto = "<p>" . utf8_decode(strtoupper($info[0]["muni_lug"])) . ",CIUDAD DE GUATEMALA, GUATEMALA, " . strtoupper($fechahoy) . "</p>";
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(16);

    //LUGAR DE FIRMAS
    $pdf->CellFit($ancho_linea + 26, $tamanio_linea, 'Presidente', 'T', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea - 20, $tamanio_linea, ' ', 0, 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 26, $tamanio_linea, 'Secretario', 'T', 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea - 20, $tamanio_linea, ' ', 0, 0, 'C', 0, '', 1, 0);
    $pdf->CellFit($ancho_linea + 27, $tamanio_linea, 'Vocal', 'T', 0, 'C', 0, '', 1, 0);
    $pdf->Ln(6);

    ob_start();
    $pdf->Output();
    $pdfData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Comprobante generado correctamente',
        'namefile' => "Contrato-" . (($datos[0]['ccodcta'] == '' || $datos[0]['ccodcta'] == null) ? ' ' : ($datos[0]['ccodcta'])),
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
function resumenpagos($clasdias, $column, $con1)
{
    $keys = array_keys(array_filter($clasdias[$column], function ($var) use ($con1) {
        return ($var == $con1);
    }));
    $fila = 0;
    $sum1 = 0;
    $sum2 = 0;
    while ($fila < count($keys)) {
        $f = $keys[$fila];
        $sum1 += ($clasdias["salcapital"][$f]);
        $sum2 += ($clasdias["capmora"][$f]);
        $fila++;
    }
    return [$sum1, $sum2, $fila];
}
