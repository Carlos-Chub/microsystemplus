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

// //SE CARGAN LOS DATOS
$strquery = "SELECT 
        cm.CCODCTA AS ccodcta, 
        cm.DFecDsbls AS fecdesem, 
        cm.DFecVen AS fecven,
        cm.TipoEnti AS formcredito, 
        dest.DestinoCredito AS destinocred, 
        cm.NCapDes AS montodesem, 
        cm.noPeriodo AS cuotas, 
        tbp.nombre AS frecuencia, 
        cm.Dictamen AS dictamen,
        cl.idcod_cliente AS codcli, 
        cl.short_name AS nomcli, 
        cl.no_identifica AS numdpi, 
        cl.Direccion AS direccioncliente, 
        cl.date_birth, 
        cl.estado_civil, 
        cl.profesion,
        cm.NIntApro AS tasaprod, 
        cl.aldea,
        IFNULL(
            (SELECT dep.nombre 
             FROM clhpzzvb_bd_general_coopera.departamentos dep 
             WHERE dep.codigo_departamento = cl.depa_reside), 
            '-'
        ) AS nomdep,
        IFNULL(
            (SELECT mun.nombre 
             FROM clhpzzvb_bd_general_coopera.municipios mun 
             WHERE mun.codigo_municipio = cl.muni_reside), 
            '-'
        ) AS nommun,
        IFNULL(
            (SELECT dep.nombre 
             FROM clhpzzvb_bd_general_coopera.departamentos dep 
             WHERE dep.codigo_departamento = cl.depa_extiende), 
            '-'
        ) AS nomdepext,
        IFNULL(
            (SELECT SUM(ncapita) + SUM(nintere) 
             FROM Cre_ppg 
             WHERE ccodcta = cm.CCODCTA), 
            0
        ) AS moncuota,
        IFNULL(
            (SELECT SUM(OTR) 
             FROM CREDKAR 
             WHERE CCODCTA = cm.CCODCTA 
               AND CTIPPAG = 'D' 
               AND CESTADO != 'X'), 
            0
        ) AS mongasto,
        IFNULL(
            (SELECT CNUMING 
             FROM CREDKAR 
             WHERE CCODCTA = cm.CCODCTA 
               AND CTIPPAG = 'D' 
               AND CESTADO != 'X'), 
            0
        ) AS nocheque,
        IFNULL(
            (SELECT tbb.nombre 
             FROM tb_bancos tbb 
             INNER JOIN CREDKAR kk ON kk.CBANCO = tbb.id 
             WHERE CCODCTA = cm.CCODCTA 
               AND CTIPPAG = 'D' 
               AND CESTADO != 'X'), 
            0
        ) AS nombanco,
        CONCAT(usu.nombre, ' ', usu.apellido) AS analista, 
        usu.dpi AS dpianal
    FROM 
        cremcre_meta cm
    INNER JOIN 
        tb_cliente cl ON cm.CodCli = cl.idcod_cliente
    INNER JOIN 
        tb_usuario usu ON cm.CodAnal = usu.id_usu
    INNER JOIN 
        clhpzzvb_bd_general_coopera.tb_destinocredito dest ON cm.Cdescre = dest.id_DestinoCredito
    INNER JOIN 
        clhpzzvb_bd_general_coopera.tb_periodo tbp ON cm.NtipPerC = tbp.periodo
    WHERE 
        cm.Cestado = 'F' 
        AND cm.CCODCTA = '$codcredito'
    GROUP BY 
        CCODCTA
";


$query = mysqli_query($conexion, $strquery);
$data[] = [];
$j = 0;
while ($fila = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
    $data[$j] = $fila;
    $j++;
}
//BUSCAR DATOS DE PLANES DE PLAGO
$querycreppg = "SELECT * FROM Cre_ppg cp WHERE cp.ccodcta = '" . $codcredito . "'";
$query = mysqli_query($conexion, $querycreppg);
$creppg[] = [];
$j = 0;
while ($fil = mysqli_fetch_array($query)) {
    $creppg[$j] = $fil;
    $creppg[$j]['totalcuota'] = $creppg[$j]['ncapita'] + $creppg[$j]['nintere'] + $creppg[$j]['OtrosPagos'];
    $j++;
}

// //BUSCAR DATOS DE GARANTIAS
$strquery = "SELECT clig.* FROM tb_garantias_creditos tgc
    INNER JOIN cli_garantia clig ON clig.idGarantia=tgc.id_garantia
    WHERE tgc.id_cremcre_meta='$codcredito' AND clig.idTipoGa IN (1,3,12);";
$query = mysqli_query($conexion, $strquery);
$j = 0;
while ($fila = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
    $garantias[$j] = $fila;
    $j++;
}

// if (!isset($garantias)) {
//     $opResult = array(
//         'status' => 0,
//         'mensaje' => 'No se encontraron garantias vinculadas con el crédito'
//         // 'mensaje' => $strquery
//     );
//     echo json_encode($opResult);
//     return;
// }

// $indexprendaria = array_search(3, array_column($garantias, 'idTipoGa'));
// $indicepersonal = array_search(1, array_column($garantias, 'idTipoGa'));

// if (is_bool($indexprendaria)) {
//     $opResult = array(
//         'status' => 0,
//         'mensaje' => 'No hay garantias prendarias'
//     );
//     echo json_encode($opResult);
//     return;
// }

// $gprendaria = $garantias[$indexprendaria];
$gprendaria = 1;

// if (!is_bool($indicepersonal)) {
//     $strquery = "SELECT cl.*,
//     (IFNULL((SELECT dep.nombre FROM clhpzzvb_bd_general_coopera.departamentos dep WHERE dep.codigo_departamento = cl.depa_reside),'-')) AS nomdep,
//     (IFNULL((SELECT mun.nombre FROM clhpzzvb_bd_general_coopera.municipios mun WHERE mun.codigo_municipio = cl.muni_reside),'-')) AS nommun
//     FROM tb_cliente cl WHERE cl.idcod_cliente='" . $garantias[$indicepersonal]['descripcionGarantia'] . "';";
//     $query = mysqli_query($conexion, $strquery);
//     // $datosfiador[] = [];
//     $j = 0;
//     while ($fila = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
//         $datosfiador[$j] = $fila;
//         $j++;
//     }
// }
$gfiador = (!isset($datosfiador)) ? null : $datosfiador[0];

// if (!isset($datosfiador)) {
//     $opResult = array(
//         'status' => 0,
//         'mensaje' => 'No se encontró el fiador'
//     );
//     echo json_encode($opResult);
//     return;
// }


// //BUSCAR DATOS DE INSTITUCION
$queryins = mysqli_query($conexion, "SELECT * FROM clhpzzvb_bd_general_coopera.info_coperativa ins
INNER JOIN tb_agencia ag ON ag.id_institucion=ins.id_cop where ag.id_agencia=" . $_SESSION['id_agencia']);
$info[] = [];
$j = 0;
while ($fil = mysqli_fetch_array($queryins, MYSQLI_ASSOC)) {
    $info[$j] = $fil;
    $j++;
}


printpdf($info, $data, $gprendaria, $gfiador, $creppg);

function printpdf($info, $data, $gprendaria, $gfiador, $creppg)
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

            //tipo de letra para el encabezado
            $this->SetFont($fuente, 'B', 12);
            // $this->Cell(0, 10, 'CONTRATO MUTUO CON GARANTIA', 'T', 1, 'C');
            // $this->Cell(0, 10, 'PRENDARIA', 'B', 1, 'C');
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
    $tamanofuente = 10;
    // $pdf->SetMargins(30, 25, 25);
    $pdf->SetFont($fuente, '', $tamanofuente);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    // Stylesheet

    $pdf->SetStyle("p", $fuente, "N", 11, "0,0,0", 0);
    $pdf->SetStyle("h1", $fuente, "N", 11, "0,0,0", 0);
    $pdf->SetStyle("a", $fuente, "BU", 11, "0,0,0");
    $pdf->SetStyle("pers", $fuente, "I", 0, "0,0,0");
    $pdf->SetStyle("place", $fuente, "U", 0, "0,0,0");
    $pdf->SetStyle("vb", $fuente, "B", 0, "0,0,0");

    //DATOS AVAL1
    $nombreaval1 = $data[0]["analista"];
    $dpi1 = $data[0]["dpianal"];
    $dpiletra1 = dpiletra($dpi1);
    $dpi1 = dpiformat($dpi1);
    $direccion1 = " ";
    //DATOS AVAL2
    $nombreaval2 = "_________________________";
    $dpi2 = "_____________";
    $dpiletra2 = "_______________";
    $dpi2 = "_______________";
    // $dpiletra2 = dpiletra($dpi2);
    // $dpi2 = dpiformat($dpi2);
    $direccion2 = "_______________";

    //DATOS CLIENTE
    $nombrecliente = $data[0]["nomcli"];
    $dpi = $data[0]["numdpi"];
    $dpiletras = dpiletra($dpi);
    $dpi = dpiformat($dpi);
    $direccion = $data[0]["direccioncliente"];
    $referencia = $data[0]["aldea"];
    $fechanacimiento = $data[0]["date_birth"];
    $edad = calcular_edad($fechanacimiento);
    $edadletra = numtoletras($edad);

    $estadocivil = $data[0]["estado_civil"];
    $profesion = $data[0]["profesion"];
    $depadomicilio = $data[0]["nomdep"];
    $depaextiende = $data[0]["nomdepext"];
    $munidomicilio = $data[0]["nommun"];


    //DATOS CREDITO
    $destinocredito = $data[0]["destinocred"];
    $ccodcta = $data[0]["ccodcta"];
    $frecuencia = $data[0]["frecuencia"];
    $plazo = $data[0]["cuotas"];
    $plazoletras = numtoletras($plazo);
    $moncuotas = round($data[0]["moncuota"]);
    $decimal = explode(".", $moncuotas);
    $res = isset($decimal[1]) ? " con " . $decimal[1] . "/100" : "";
    $mondecimal = numtoletras($decimal[0]);
    $moncuotasletra = $mondecimal . " " . $res;

    $montogasto = round($data[0]["mongasto"]);
    $decimal = explode(".", $montogasto);
    $res = isset($decimal[1]) ? " con " . $decimal[1] . "/100" : "";
    $mondecimal = numtoletras($decimal[0]);
    $gastoletra = $mondecimal . " " . $res;

    $fechadesembolso =date('d-m-Y', strtotime($data[0]["fecdesem"])) ;
    $fechavenorigin =date('d-m-Y', strtotime($data[0]["fecven"])) ;
    $fechaven = fechaletras($fechavenorigin);
    $fechaletras = fechaletras($fechadesembolso);

    $monto = round($data[0]['montodesem'], 2);
    $decimal = explode(".", $monto);
    $res = isset($decimal[1]) ? " con " . $decimal[1] . "/100" : "";
    $mondecimal = numtoletras($decimal[0]);
    $montoletras = $mondecimal . " " . $res;

    $tasa = $data[0]["tasaprod"];
    $tasaletra = numtoletras($tasa);
    //GARANTIAS
    // $descripciongarantia = $gprendaria["descripcionGarantia"];

    //DATOS CHEQUE
    $nombanco = $data[0]["nombanco"];
    $nocheque = $data[0]["nocheque"];
    // $text = '“LA PARTE ACREEDORA”';
    // $text = convert_smart_quotes_to_regular($text);
    // $pdf->WriteTag(0, 5, $text, 0, "J", 0, 0);

    encabezado($pdf, 125);
    //PARTE INICIAL
    $texto = limpiar(introduccion($nombrecliente, $dpi, $dpiletras, $edadletra, $depadomicilio, $depaextiende, $munidomicilio, $direccion, $montoletras, $monto, $nocheque, $nombanco, $destinocredito, $referencia , $profesion , $estadocivil, $ccodcta));
    $texto .= limpiar(puntoa1($plazoletras, $plazo));
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(6);
    //ESPACIO PARA CUADRITOS
    plandepagos($pdf, $plazo, $creppg);
    $texto = limpiar(puntoa2($fechaven));
    $texto .= limpiar(puntob($tasaletra, $tasa, $gastoletra, $montogasto, $frecuencia));
    $texto .= limpiar(puntosc1());
    $texto .= limpiar(puntoscgarantiag());
    $texto .= limpiar(puntosc2($fechadesembolso));

    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(6);

    $pdf->firmas(1, [$nombrecliente]);
    $pdf->Ln(25);

    //PARTE FINAL
    $texto = limpiar(puntofinal($nombreaval1, $dpiletra1, $dpi1, $direccion1, $nombreaval2, $dpiletra2, $dpi2, $direccion2, $fechadesembolso));
    $pdf->WriteTag(0, 5, $texto, 0, "J", 0, 0);
    $pdf->Ln(6);
    $pdf->firmas(2, [$nombreaval1, $nombreaval2]);
    $pdf->Ln(25);

    ob_start();
    $pdf->Output();
    $pdfData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Comprobante generado correctamente',
        'namefile' => "Contrato-",
        'tipo' => "pdf",
        'data' => "data:application/pdf;base64," . base64_encode($pdfData)
    );
    echo json_encode($opResult);
}
function encabezado($pdf, $nopagare)
{
    $pdf->Cell(0, 10, utf8_decode('PAGARÉ NO: _________________ ')  . ' ', '', 1, 'C');
    $pdf->Cell(0, 10, 'Libre de Protesto', '', 1, 'C');
    $pdf->Ln(3);
}
function introduccion($name, $dpi, $dpiletra, $edadletra, $depadomicilio, $depaextiende, $munidomicilio, $direccion, $montoletra, $monto, $nocheque, $nombanco, $destinocredito, $referencia = "", $profesion, $estadocivil, $ccodcta )
{
    $data = utf8_decode('<p>Yo: ' . $name . ' de ' . $edadletra . ' años de edad, ' . $estadocivil . ', Guatemalteco, ' . $profesion . ', con domicilio en el departamento 
    de ' . $depaextiende . ', me identifico con Documento Personal de Identificación (DPI), Código Único de Identificación (CUI) 
    números: ' . $dpiletra . ' (' . $dpi . '), extendido por el Registro Nacional de las Personas de la República de Guatemala.<p>');


    $data .= utf8_decode('<p>En adelante denominado "la parte deudora o librador", y lugar para recibir comunicaciones y/o notificaciones que 
    para efectos de este título será en ' . $direccion . ' jurisdicción del municipio de ' . $munidomicilio . ' departamento de ' . $depadomicilio . '; 
    referencia ' . $referencia . ' por el presente <vb>PAGARÉ</vb> libre de protesto, prometo pagar incondicionalmente a la orden o endoso de <vb>"ASOCIACIÓN DE 
    DESARROLLO GUATEMALTECO"</vb> la que podrá abreviarse <vb>"ADG"</vb>, en adelante llamada "Acreedor y/o entidad Acreedora", la suma 
    de ' . $montoletra . ' (Q ' . $monto . '), los que recibo a través del cheque número ' . $nocheque . ', del Banco ' . $nombanco . ', Sociedad Anónima, de la cual me 
    declaro liso y llano deudor, a su vez declaro bajo juramento de ley que utilizaré este financiamiento única y exclusivamente para: ' . $destinocredito . ', 
    el pago de la suma adeudada lo haré bajo las siguientes condiciones:</p>');
    return $data;
}
function puntoa1($plazoletras, $plazo)
{
    $data = utf8_decode('<p><vb>a. DEL PLAZO Y FORMA DE PAGO:</vb></p>');

    $data .= utf8_decode('<p>Me obligo a pagar la suma adeudada de este título en el plazo de ' . $plazoletras . ' meses (' . $plazo . '), 
    contados  a partir de la presente fecha, cantidad que pagaré sin necesidad de previo cobro o requerimiento, mediante el pago de amortizaciones 
    mensuales y consecutivas de conformidad a la siguiente tabla de amortizaciones:</p>');
    return $data;
}
function puntoa2($fechaven)
{
    //hasta aqui va bien
    $data = utf8_decode('<p>Cuotas que incluyen capital e intereses, cuyo monto se obtiene de acuerdo a la fórmula que se utiliza para calcular 
    las cuotas aprobadas por el sistema financiero del país, que conozco y acepto plenamente, cada una se harán efectivas dentro de los primeros 
    diez días calendario de cada mes, y el saldo al vencimiento del plazo, que principiarán a hacerse efectivas a partir del día de hoy culminando 
    el ' . $fechaven . '. Todo pago lo hare en efectivo y al contado en moneda de curso legal en las oficinas centrales de 
    la entidad acreedora, situada en la segunda calle uno guión treinta y uno de la zona cuatro, Barrio San Antonio del municipio de Tecpán Guatemala, 
    departamento de Chimaltenango, de preferencia, y hago constar que el lugar descrito lo conozco plenamente, o a través de depósito Bancario de la 
    entidad acreedora cuenta monetaria número <vb>3374016203</vb> del Banco de Desarrollo Rural, Sociedad Anónima.</p>');
    return $data;
}
function puntob($tasaletra, $tasa, $montogastoletra, $montogasto,$frecuencia)
{
    $data = utf8_decode('<p><vb>b. INTERESES:</vb></p>');

    $data .= utf8_decode('<p>Reconozco y me obligo a pagar los intereses calculados bajo la tasa del ' . $tasaletra . ' (' . $tasa . ' %), 
    '. $frecuencia .' dicha tasa de interés es pactada por ambas partes. La tasa del interés en la presente operación corresponderá exactamente con la 
    tasa de interés nominal anteriormente indicada y aceptada, siempre y cuando los abonos a capital y pagos de intereses se realicen exactamente 
    en la forma y tiempo aquí establecido. La parte Deudora estará obligada al pago de la nueva tasa desde la fecha en que cobre vigencia a 
    disposición de la resolución de la entidad Administrativa de la entidad acreedora, para aumentarla o reducirla. En ningún caso la variación 
    de la tasa de interés constituye novación. Los intereses se liquidarán y pagarán en cada amortización mensual. La falta de pago de los intereses y 
    de capital en las fechas pactadas facultará al acreedor, para cobrar un recargo sobre intereses y capital en mora del ocho por ciento (8%) mensual 
    sobre cuota o cuotas atrasadas cuantificando de mes a mes la que es aceptada por la parte deudora. b.1): Gastos administrativos comisiones y 
    descuentos. El deudor (a) desde ya acepta y autoriza el descuento por gastos administrativos, comisiones y manejo de cuenta de un 
    monto de ' . $montogastoletra . ' quetzales exactos (Q.' . $montogasto . ') ) equivalentes al 4% del total otorgado. </p>');
    return $data;
}
function puntosc1()
{
    $data = utf8_decode('<p><vb>c. ACEPTACION Y OBLIGACION DE LA PARTE DEUDORA:</vb></p>');

    $data .= utf8_decode('<p>La entidad acreedora podrá dar por vencido el plazo de este título en forma anticipada y exigir ejecutivamente el pago 
    total del saldo adeudado, en los siguientes casos: a.1) Si no se cumple cualesquiera de las obligaciones aquí contraídas y las que se establecen 
    en las leyes de la República de Guatemala y las que rigen a la entidad acreedora; a.2) Si se dictare mandamiento de embargo en contra del deudor 
    o del avalista (s); a.3) Si dejare de pagar puntualmente una sola de las amortizaciones al capital y los respectivos intereses; y a.4) Si la 
    entidad acreedora comprobare que se utilizó el financiamiento para fines distintos a lo antes consignado, b) Renuncio al fuero de mi respectivo 
    domicilio; me someto y sujeto a la jurisdicción de los Tribunales que elija La entidad acreedora; y éste puede utilizar a su elección y para el 
    caso de ejecución el procedimiento y esencia de este título de crédito, y/o el Código Procesal Civil y Mercantil y/o Código de Comercio, así como 
    para señalar los bienes objeto de embargo, secuestro, depósito e intervención, sin sujetarse a orden legal alguno, c) Acepto como buenas y exactas 
    las cuentas que la entidad acreedora formule acerca de este título y como líquido, exigible y de plazo vencido la cantidad que se exija, d) Acepto 
    que se tenga como válidas y bien hechas legalmente las comunicaciones y/o notificaciones que se realicen y/o dirijan al lugar indicado como 
    domicilio, a no ser que notifique por escrito a la entidad acreedora, de cualquier cambio en la misma y que obre en su poder aviso de recepción 
    de la entidad acreedora de lo contrario serán bien hechas las que ahí me realicen, e) Acepto todos los gastos que se ocasione o motive este 
    negocio en el que incurra la entidad acreedora, directa o indirectamente son por mi cuenta inclusive lo de cobranza judicial o extrajudicial 
    honorarios de abogado, f) Acepto que para el caso de ejecución, la entidad acreedora no está obligada a prestar fianza o garantía alguna, 
    exoneración que se hará extensiva a los depositarios e interventores nombrados, no quedando el acreedor responsable por las actuaciones de 
    éstos, y que para el caso de remate sirva de base el valor de los bienes embargados o el monto total de la demanda incluyendo intereses y 
    costas, a elección de la entidad acreedora, garantizando la presente obligación con todos mis bienes presentes y futuros;');
    return $data;
}


//agregar la variable qque trrae la garantia
function puntoscgarantiag()
{
    $data = utf8_decode('De la misma manera yo el (la) deudor (a) dejo como garantía prendaria el primer  testimonio de la Escritura Pública 
    número MIL CUATROCEINTOS NUEVE  (1409), autorizada en el municipio de Tecpán Guatemala, departamento de Chimaltenango, el día nueve de 
    agosto del año dos mil veintidós, por el notario WILLIAM MERARI CHOCOJ TUN, ubicado en la Aldea Paraxquin, jurisdicción del Municipio 
    de Tecpán Guatemala, Departamento de Chimaltenango, el inmueble carece de registro y matrícula fiscal con un área superficial de cuatro 
    mil siete punto cuarenta y seis varas cuadradas (4,007.46 varas  2 equivalente a 2.50 cuerdas de 40 varas por lado), y demás características 
    que constan en el referido instrumento, del cual desde este momento faculto al acreedor para que pueda solicitar la escrituración sin 
    objeción alguna en caso de incumplimiento con el pago de lo que consta en este título, y que tome de forma inmediata la posesión del 
    bien inmueble sin limitación alguna, mientras se tramita judicialmente el proceso de escrituración y estoy sabido de las repercusiones 
    legales del quebrantamiento de esta disposición;');
    return $data;
}
function puntosc2($fecha)
{
    $data = utf8_decode('h) Acepto que este título es cedible o negociable, mediante simple endoso; sin necesidad previa o posterior aviso 
    o notificación; i) Expresamente dejo constancia que todos y cada uno de los datos que he proporcionado a la entidad acreedora, en la 
    inexactitud o falta de veracidad en los mismos que determine deberá tenerse con una acción dolosa de mi parte generadora de prejuicios 
    a su patrimonio y susceptible del ejercicio de acción penal que en derecho corresponda a esta última; j) Renuncio expresamente a los 
    derechos que pudieren conferirme las leyes vigentes o que en el futuro entraren en vigor y que pudieren permitirme cumplir las obligaciones 
    contraídas en este documento en forma distinta a la pactada, cuyo contenido declara conocer y entender,  para para lo cual signo la presente, 
    de la misma manera dejo la impresión dactilar de mi dedo pulgar derecho al pie de la misma.</p>');
    $data .= utf8_decode('<p>Lugar y fecha de Emisión:</p>');
    $data .= utf8_decode('<p>Ciudad de Tecpán Guatemala,' . $fecha . ' </p>');
    return $data;
}
function puntofinal($nombreaval1, $dpiletra1, $dpi1, $direccion1, $nombreaval2, $dpiletra2, $dpi2, $direccion2, $fecha)
{
    $data = utf8_decode('<p><vb>VALIDO POR AVAL DEL LIBRADOR</vb></p>');

    $data .= utf8_decode('<p>A) ' . $nombreaval1 . ', me identifico con el Documento Personal de Identificación (DPI), Código Único de 
    Identificación (CUI) número ' . $dpiletra1 . ' (' . $dpi1 . ') extendido por el Registro Nacional de las Personas de la República de Guatemala, 
    con domicilio en el departamento de Chimaltenango y residencia en ' . $direccion1 . ', jurisdicción del municipio de Tecpán Guatemala. </p>');

    $data .= utf8_decode('<p><vb>VALIDO POR AVAL DEL LIBRADOR</vb></p>');

    $data .= utf8_decode('<p>A) ' . $nombreaval2 . ', me identifico con el Documento Personal de Identificación (DPI), Código Único de 
    Identificación (CUI) número ' . $dpiletra2 . ' (' . $dpi2 . ') extendido por el Registro Nacional de las Personas de la República de Guatemala, 
    con domicilio en el departamento de Chimaltenango y residencia en ' . $direccion2 . ', jurisdicción del municipio de Tecpán Guatemala. </p>');

    $data .= utf8_decode('<p>Nos constituimos como AVALISTAS del librador y garantizamos en forma mancomunada solidaria, renunciando a cualquier 
    derecho de exclusión y orden que pudiere correspondernos, el total del pago del anterior pagaré y nos obligamos a pagar el título de crédito 
    en su totalidad bajo las mismas condiciones aceptadas por el librador, las cuales declaramos que conocemos, entendemos y aceptamos expresamente 
    sin reserva alguna de todas las obligaciones aceptadas por el librador en este título, renunciando al fuero de nuestro domicilio, sometiéndonos 
    a los tribunales que elija la entidad acreedora y señalando como lugar para recibir comunicaciones o notificaciones el (los) domicilio (s) antes 
    indicados (s), obligándonos a comunicar de inmediato al acreedor de cualquier cambio de la misma, la prueba de la comunicación corre a nuestro 
    cargo, aceptando para el caso de no dar este aviso como válida cualquier notificación que se nos haga en la dirección que hemos señalado en 
    este título, desde ya aceptamos y respondemos con nuestros bienes presentes y futuros, y para lo cual signamos la presente y dejamos la impresión 
    dactilar de nuestros dedos pulgares derechos donde corresponde, en constancia de aceptación.</p>');
    $data .= utf8_decode('<p>Lugar y fecha</p>');
    $data .= utf8_decode('<p>Ciudad de Tecpán Guatemala,' . $fecha . ' </p>');
    return $data;
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

function dpiletra($numdpi)
{
    $texto = preg_replace('/\s+/', '', $numdpi);
    $parte1 = substr($texto, 0, 4);
    $parte2 = substr($texto, 4, 5);
    $parte3 = substr($texto, 9, 4);
    $letra_dpi1 = numtoletras($parte1);
    $letra_dpi2 = numtoletras($parte2);
    $letra_dpi3 = numtoletras($parte3);
    $resultado = ("{$letra_dpi1}, {$letra_dpi2}, {$letra_dpi3}");
    return $resultado;
}
function dpiformat($numdpi)
{
    $texto = preg_replace('/\s+/', '', $numdpi);
    $parte1 = substr($texto, 0, 4);
    $parte2 = substr($texto, 4, 5);
    $parte3 = substr($texto, 9, 4);
    $resultado = ("{$parte1} {$parte2} {$parte3}");
    return $resultado;
}
function numtoletras($numero)
{
    $letra = new NumeroALetras();
    $letra_d = mb_strtolower($letra->toWords(intval(trim($numero))));
    return $letra_d;
}
function plazos($plazos)
{
    $result = "Meses";
    switch ($plazos) {
        case 'Mensual':
            $result = "Meses";
            break;
        case 'Semanal':
            $result = "Semanas";
            break;
    }
    return $result;
}
function limpiar($text)
{
    // $strash = array("\r", "\n", "\t");
    // $texto_limpio = str_replace($strash, '', $text);
    $texto_limpio = preg_replace('/\s+/', ' ', $text);
    return $texto_limpio;
}
function plandepagos($pdf, $nocuotas, $creppg)
{
    $divperiodo = (($nocuotas / 4));
    $parteentera = $divperiodo;
    $partedecimal = 0;
    $banderadecimal = false;
    if (is_float($divperiodo)) {
        $banderadecimal = true;
        $parteentera = (int)$divperiodo;
        $partedecimal = $divperiodo - $parteentera;
    }

    $pdf->SetFont('Times', 'B', 7);
    if ($parteentera == 0) {
        if ($partedecimal <= 0.25 || $partedecimal <= 0.50 || $partedecimal <= 0.75) {
            $pdf->CellFit(7, 4, ' ', 0, 0, 'L', 0, '', 1, 0);
            $pdf->CellFit(6, 4, 'No', 1, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit(17, 4, 'Total cuota', 1, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit(17, 4, 'Fecha de pago', 1, 0, 'C', 0, '', 1, 0);
        }
        if (($partedecimal > 0.25 && $partedecimal <= 0.50) || ($partedecimal > 0.50 && $partedecimal <= 0.75)) {
            $pdf->CellFit(5, 4, ' ', 0, 0, 'L', 0, '', 1, 0);
            $pdf->CellFit(6, 4, 'No', 1, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit(17, 4, 'Total cuota', 1, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit(17, 4, 'Fecha de pago', 1, 0, 'C', 0, '', 1, 0);
        }
        if (($partedecimal >= 0.75)) {
            $pdf->CellFit(5, 4, ' ', 0, 0, 'L', 0, '', 1, 0);
            $pdf->CellFit(6, 4, 'No', 1, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit(17, 4, 'Total cuota', 1, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit(17, 4, 'Fecha de pago', 1, 0, 'C', 0, '', 1, 0);
        }
    } else {
        $pdf->CellFit(7, 4, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit(6, 4, 'No', 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit(17, 4, 'Total cuota', 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit(17, 4, 'Fecha de pago', 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit(5, 4, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit(6, 4, 'No', 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit(17, 4, 'Total cuota', 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit(17, 4, 'Fecha de pago', 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit(5, 4, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit(6, 4, 'No', 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit(17, 4, 'Total cuota', 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit(17, 4, 'Fecha de pago', 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit(5, 4, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit(6, 4, 'No', 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit(17, 4, 'Total cuota', 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit(17, 4, 'Fecha de pago', 1, 0, 'C', 0, '', 1, 0);
    }

    $pdf->Ln(4);
    $pdf->SetFont('Times', '', 7);
    for ($i = 0; $i < $parteentera; $i++) {
        $pdf->CellFit(7, 4, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit(6, 4, ($i + 1), 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit(17, 4, (isset($creppg[$i]['totalcuota'])) ? 'Q ' . number_format($creppg[$i]['totalcuota'], 2) : 'Q 00.00', 1, 0, 'R', 0, '', 1, 0);
        $pdf->CellFit(17, 4, (isset($creppg[$i]['dfecven'])) ? date('d-m-Y', strtotime($creppg[$i]['dfecven'])) : '00-00-0000', 1, 0, 'R', 0, '', 1, 0);
        if ($banderadecimal) {
            if ($partedecimal <= 0.25 || $partedecimal <= 0.50 || $partedecimal <= 0.75) {
                $i++;
            }
        }
        $pdf->CellFit(5, 4, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit(6, 4, ($i + 1 + ($parteentera)), 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit(17, 4, (isset($creppg[($i + $parteentera)]['totalcuota'])) ? 'Q ' . number_format($creppg[($i + $parteentera)]['totalcuota'], 2) : 'Q 00.00', 1, 0, 'R', 0, '', 1, 0);
        $pdf->CellFit(17, 4, (isset($creppg[($i + $parteentera)]['dfecven'])) ? date('d-m-Y', strtotime($creppg[($i + $parteentera)]['dfecven'])) : '00-00-0000', 1, 0, 'R', 0, '', 1, 0);
        if ($banderadecimal) {
            if (($partedecimal > 0.25 && $partedecimal <= 0.50) || ($partedecimal > 0.50 && $partedecimal <= 0.75)) {
                $i++;
            }
        }
        $pdf->CellFit(5, 4, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit(6, 4, ($i + 1 + ($parteentera * 2)), 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit(17, 4, (isset($creppg[($i + ($parteentera * 2))]['totalcuota'])) ? 'Q ' . number_format($creppg[($i + ($parteentera * 2))]['totalcuota'], 2) : 'Q 00.00', 1, 0, 'R', 0, '', 1, 0);
        $pdf->CellFit(17, 4, (isset($creppg[($i + ($parteentera * 2))]['dfecven'])) ? date('d-m-Y', strtotime($creppg[($i + ($parteentera * 2))]['dfecven'])) : '00-00-0000', 1, 0, 'R', 0, '', 1, 0);
        if ($banderadecimal) {
            if (($partedecimal >= 0.75)) {
                $i++;
            }
        }
        $pdf->CellFit(5, 4, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit(6, 4, ($i + 1 + ($parteentera * 3)), 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit(17, 4, (isset($creppg[($i + ($parteentera * 3))]['totalcuota'])) ? 'Q ' . number_format($creppg[($i + ($parteentera * 3))]['totalcuota'], 2) : 'Q 00.00', 1, 0, 'R', 0, '', 1, 0);
        $pdf->CellFit(17, 4, (isset($creppg[($i + ($parteentera * 3))]['dfecven'])) ? date('d-m-Y', strtotime($creppg[($i + ($parteentera * 3))]['dfecven'])) : '00-00-0000', 1, 0, 'R', 0, '', 1, 0);
        $pdf->Ln(4);
        if ($banderadecimal) {
            if ($partedecimal <= 0.25 || $partedecimal <= 0.50 || $partedecimal <= 0.75) {
                $i--;
            }
            if (($partedecimal > 0.25 && $partedecimal <= 0.50) || ($partedecimal > 0.50 && $partedecimal <= 0.75)) {
                $i--;
            }
            if (($partedecimal >= 0.75)) {
                $i--;
            }
        }
    }
    if ($banderadecimal) {
        $i = $parteentera;
        if ($parteentera == 0) {
            $i = 0;
        }
        if ($partedecimal <= 0.25 || $partedecimal <= 0.50 || $partedecimal <= 0.75) {
            $pdf->CellFit(7, 4, ' ', 0, 0, 'L', 0, '', 1, 0);
            $pdf->CellFit(6, 4, ($i + 1), 1, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit(17, 4, (isset($creppg[($i)]['totalcuota'])) ? 'Q ' . number_format($creppg[($i)]['totalcuota'], 2) : 'Q 00.00', 1, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit(17, 4, (isset($creppg[($i)]['dfecven'])) ? date('d-m-Y', strtotime($creppg[($i)]['dfecven'])) : '00-00-0000', 1, 0, 'R', 0, '', 1, 0);
        }
        if (($partedecimal > 0.25 && $partedecimal <= 0.50) || ($partedecimal > 0.50 && $partedecimal <= 0.75)) {
            $pdf->CellFit(5, 4, ' ', 0, 0, 'L', 0, '', 1, 0);
            $pdf->CellFit(6, 4, ($i + 2 + ($parteentera)), 1, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit(17, 4, (isset($creppg[($i + 1 + ($parteentera))]['totalcuota'])) ? 'Q ' . number_format($creppg[($i + 1 + ($parteentera))]['totalcuota'], 2) : 'Q 00.00', 1, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit(17, 4, (isset($creppg[($i + 1 + ($parteentera))]['dfecven'])) ? date('d-m-Y', strtotime($creppg[($i + 1 + ($parteentera))]['dfecven'])) : '00-00-0000', 1, 0, 'R', 0, '', 1, 0);
        }
        if (($partedecimal >= 0.75)) {
            $pdf->CellFit(5, 4, ' ', 0, 0, 'L', 0, '', 1, 0);
            $pdf->CellFit(6, 4, ($i + 3 + ($parteentera * 2)), 1, 0, 'C', 0, '', 1, 0);
            $pdf->CellFit(17, 4, (isset($creppg[($i + 2 + ($parteentera * 2))]['totalcuota'])) ? 'Q ' . number_format($creppg[($i + (2 + $parteentera * 2))]['totalcuota'], 2) : 'Q 00.00', 1, 0, 'R', 0, '', 1, 0);
            $pdf->CellFit(17, 4, (isset($creppg[($i + 2 + ($parteentera * 2))]['dfecven'])) ? date('d-m-Y', strtotime($creppg[($i + 2 + ($parteentera * 2))]['dfecven'])) : '00-00-0000', 1, 0, 'R', 0, '', 1, 0);
        }
        $pdf->Ln(4);
    }
}
