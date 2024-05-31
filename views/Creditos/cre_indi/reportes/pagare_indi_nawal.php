<?php
session_start();
include '../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');
require('../../../../fpdf/WriteTag.php');
require '../../../../vendor/autoload.php';
// include '../../../../src/funcphp/valida.php';
include '../../../../src/funcphp/func_gen.php';

use Luecano\NumeroALetras\NumeroALetras;

$datos = $_POST["datosval"];
$inputs = $datos[0];
$archivo = $datos[3];

$strquery = "SELECT cli.short_name,cli.idcod_cliente,cli.tel_no1,cli.date_birth,cli.no_identifica, cli.genero,cli.estado_civil,cli.Direccion, cli.profesion, pro.tasa_interes, cre.NCapDes,
cre.CCODCTA,cre.Cestado,cre.NCiclo,cre.MontoSol,cre.NIntApro,cre.CodAnal,concat(usu.nombre,' ',usu.apellido) nomanal,cre.CCODPRD,cre.CtipCre,cre.NtipPerC,cre.DfecPago,cre.noPeriodo,cre.Dictamen,cre.MonSug,cre.DFecDsbls,cre.DFecVen,
pro.id_fondo id_fondos,ff.descripcion,pro.porcentaje_mora,
IFNULL((SELECT nombre FROM clhpzzvb_bd_general_coopera.municipios WHERE codigo_municipio=cli.muni_reside LIMIT 1),'-') municipio,
IFNULL((SELECT nombre FROM clhpzzvb_bd_general_coopera.departamentos WHERE codigo_departamento=cli.depa_reside LIMIT 1),'-') departamento,
IFNULL((SELECT nombre FROM clhpzzvb_bd_general_coopera.municipios WHERE codigo_municipio=cli.muni_extiende LIMIT 1),'-') muniextiende,
IFNULL((SELECT nombre FROM clhpzzvb_bd_general_coopera.departamentos WHERE codigo_departamento=cli.depa_extiende LIMIT 1),'-') depaextiende,
IFNULL((SELECT SUM(nintere) FROM Cre_ppg WHERE ccodcta=cre.CCODCTA GROUP BY ccodcta),0) intcal
From cremcre_meta cre
INNER JOIN tb_cliente cli ON cli.idcod_cliente=cre.CodCli
INNER JOIN cre_productos pro ON pro.id=cre.CCODPRD
INNER JOIN ctb_fuente_fondos ff ON ff.id=pro.id_fondo
INNER JOIN tb_usuario usu ON usu.id_usu=cre.CodAnal
WHERE cre.TipoEnti='INDI' AND cre.CESTADO='F' AND cre.CCODCTA='" . $archivo[0] . "'";

// echo json_encode(['status' => 0, 'mensaje' => $strquery]);
//     return; 

$query = mysqli_query($conexion, $strquery);
$registro[] = [];
$j = 0;
$flag = false;
while ($fil = mysqli_fetch_array($query)) {
    $registro[$j] = $fil;
    $flag = true;
    $j++;
}




//BUSCAR DATOS DE INSTITUCION
$queryins = mysqli_query($conexion, "SELECT ins.*, ag.*,
IFNULL((SELECT nombre FROM clhpzzvb_bd_general_coopera.municipios WHERE codigo_municipio=ag.municipio LIMIT 1),'-') municipioagencia,
IFNULL((SELECT nombre FROM clhpzzvb_bd_general_coopera.departamentos WHERE codigo_departamento=ag.departamento LIMIT 1),'-') departamentoagencia
FROM clhpzzvb_bd_general_coopera.info_coperativa ins
INNER JOIN tb_agencia ag ON ag.id_institucion=ins.id_cop where ag.id_agencia=" . $_SESSION['id_agencia']);
$info[] = [];
$j = 0;
$flag2 = false;
while ($fil = mysqli_fetch_array($queryins, MYSQLI_ASSOC)) {
    $info[$j] = $fil;
    $flag2 = true;
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

$queryfiador = "SELECT cli.*,
IFNULL((SELECT nombre FROM clhpzzvb_bd_general_coopera.municipios WHERE codigo_municipio=cli.muni_reside LIMIT 1),'-') municipio,
IFNULL((SELECT nombre FROM clhpzzvb_bd_general_coopera.departamentos WHERE codigo_departamento=cli.depa_reside LIMIT 1),'-') departamento,
IFNULL((SELECT nombre FROM clhpzzvb_bd_general_coopera.municipios WHERE codigo_municipio=cli.muni_extiende LIMIT 1),'-') muniextiende,
IFNULL((SELECT nombre FROM clhpzzvb_bd_general_coopera.departamentos WHERE codigo_departamento=cli.depa_extiende LIMIT 1),'-') depaextiende
FROM tb_garantias_creditos tgc 
INNER JOIN cli_garantia clig ON clig.idGarantia=tgc.id_garantia 
INNER JOIN tb_cliente cli ON cli.idcod_cliente=clig.idCliente
WHERE tgc.id_cremcre_meta='" . $archivo[0] . "'";

$query = mysqli_query($conexion, $queryfiador);
$fiadores[] = [];
$j = 0;  
$flag = false;
while ($fil = mysqli_fetch_array($query)) {
    $fiadores[$j] = $fil;
    $flag = true;
    $j++;
}
//COMPROBACION: SI SE ENCONTRARON REGISTROS
if ($flag == false) {
    $opResult = array(
        'status' => 0,
        'mensaje' => 'El credito no tiene fiadores como Garantia',
        'dato' => $queryfiador
    );
    echo json_encode($opResult);
  
}
//BUSCAR DATOS DE PLANES DE PLAGO
$querycreppg = "SELECT * FROM Cre_ppg cp WHERE cp.ccodcta = '" . $archivo[0] . "'";
$query = mysqli_query($conexion, $querycreppg);
$creppg[] = [];
$j = 0;
$flag = false;
while ($fil = mysqli_fetch_array($query)) {
    $creppg[$j] = $fil;
    $creppg[$j]['totalcuota'] = $creppg[$j]['ncapita'] + $creppg[$j]['nintere'] + $creppg[$j]['OtrosPagos'];
    $flag = true;
    $j++;
}

//COMPROBACION: SI SE ENCONTRARON REGISTROS
if ($flag == false) {
    $opResult = array(
        'status' => 0,
        'mensaje' => 'El credito no se le encontro su plan de pago',
        'dato' => $querycreppg
    );
    echo json_encode($opResult);
    return;
}

$querycreppgII = "SELECT ncapita FROM Cre_ppg cp WHERE cp.ccodcta = '" . $archivo[0] . "'  LIMIT 1"; 
$query = mysqli_query($conexion, $querycreppgII);

$ncapita = null; 

if ($query) { // Verificar si la consulta se realizó correctamente
    $fil = mysqli_fetch_array($query); // Obtener la primera fila
    if ($fil) { // Verificar si se obtuvo algún resultado
        $ncapita = $fil['ncapita']; // Obtener el valor de ncapita
    }
}

// echo json_encode(['status' => 0, 'mensaje' => $querycreppgII]);
// return;

// **** VARIABLE DE LOS DATOS BASICOS DEL CREDITO ****
$dtCre = [array_sum(array_column($registro, 'MonSug')), $registro[0]['NIntApro'] / 12, round($registro[0]['porcentaje_mora'] / 12, 2)]; // MONTO, INT, MORA, 
// **** FUNCION PARA LLAMAR LOS DATOS DE LOS INTERGRANTES DEL GRUPO ****

$vlrs = ['PAGARE LIBRE DE PROTESTO'];

class PDF extends PDF_WriteTag
{
    public function __construct()
    {
        parent::__construct();
    }

    // Cabecera de página
    function Header()
    {
        // Posición: a 1 cm del final
        // $this->SetY(10);
        // Arial italic 8
        $this->SetFont('Arial', '', 10);
        // Número de página
        $this->Cell(0, 5, 'Pagina No.  ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
        $this->Ln(6);
    }
}

$pdf = new PDF();
// $pdf->SetMargins(8,8,8);
$pdf->SetFont('courier', '', 11);
$pdf->AliasNbPages();
$pdf->AddPage();
// Stylesheet
$pdf->SetStyle("p", "times", "N", 10, "0,0,0", 0);
$pdf->SetStyle("h1", "times", "N", 10, "0,0,0", 0);
$pdf->SetStyle("a", "times", "BU", 9, "0,0,0");
$pdf->SetStyle("pers", "times", "I", 0, "0,0,0");
$pdf->SetStyle("place", "times", "U", 0, "0,0,0");
$pdf->SetStyle("vb", "times", "B", 10, "0,0,0");

// $pdf->Ln(10);
// $pdf->SetLineWidth(0.1);
headermanual($pdf, $dtCre, $info, $registro, $creppg);
$txt = "";
$pdf->SetTextColor(255, 0, 0);
$txt .= parr1($dtCre, $vlrs, $registro, $fiadores, $info, $creppg, $ncapita );
$pdf->SetTextColor(0, 0, 0);
$pdf->WriteTag(0, 5, $txt, 0, "J", 0, 7);
$pdf->Ln(2);
//ESPACIO PARA CUADRITOS
plandepagos($pdf, $registro, $creppg);

//SEGUNDA PARTE LO QUE LE SIGUE A LOS PLANES DE PAGO
$txt2 = "";
$txt2 .= parr2($registro);
$pdf->WriteTag(0, 5, $txt2, 0, "J", 0, 7);

//CUADRITOS
$pdf->Ln(1);
$pdf->WriteTag(0, 5, '<p>ACEPTO LIBRE DE PROTESTO</p>', 0, "J", 0, 7);
$pdf->Ln(1);
$lineaarriba = "";
$lineaabajo = "";
for ($i = 0; $i < 5; $i++) {
    if ($i == 0) {
        $lineaarriba = "T";
    }
    if ($i == 4) {
        $lineaabajo = "B";
    }
    $pdf->Cell(28, 5, ' ', 0, 0, 'R');
    $pdf->Cell(40, 5, ' ', "$lineaabajo" . "RL$lineaarriba", 0, 'C');
    $pdf->Cell(50, 5, ' ', 0, 0, 'R');
    $pdf->Cell(40, 5, ' ', "$lineaabajo" . "RL$lineaarriba", 0, 'C');
    $pdf->Ln(5);
    $lineaarriba = "";
    $lineaabajo = "";
}

//firmas
$pdf->Ln(4);
$pdf->CellFit(8, 5, ' ', 0, 0, 'L', 0, '', 1, 0);
$pdf->CellFit(5, 5, 'F.', 0, 0, 'R', 0, '', 1, 0);
$pdf->CellFit(72, 5, ' ', 'B', 0, 'C', 0, '', 1, 0);
$pdf->CellFit(20, 5, ' ', 0, 0, 'L', 0, '', 1, 0);
$pdf->CellFit(5, 5, 'F.', 0, 0, 'R', 0, '', 1, 0);
$pdf->CellFit(72, 5, ' ', 'B', 0, 'C', 0, '', 1, 0);
$pdf->Ln(6);
$pdf->SetFont('Times', '', 9);
$pdf->CellFit(8, 5, ' ', 0, 0, 'L', 0, '', 1, 0);
$pdf->CellFit(77, 5, '(Deudor) ' . mb_strtoupper(utf8_decode($registro[0]['short_name'])), 0, 0, 'C', 0, '', 1, 0);
$pdf->CellFit(20, 5, ' ', 0, 0, 'L', 0, '', 1, 0);
$pdf->CellFit(77, 5, '(AVAL) ' , 'B', 0, 'L', 0, '', 1, 0);
$pdf->Ln(5);
$pdf->SetFont('Times', 'B', 11);
$pdf->CellFit(8, 5, ' ', 0, 0, 'L', 0, '', 1, 0);
$pdf->CellFit(77, 5, '(DPI) ' . $registro[0]['no_identifica'], 0, 0, 'C', 0, '', 1, 0);
$pdf->CellFit(20, 5, ' ', 0, 0, 'L', 0, '', 1, 0);
$pdf->CellFit(77, 5, '(DPI) ' , 'B', 0, 'L', 0, '', 1, 0);
$pdf->Ln(5);
$pdf->CellFit(5, 5, ' ', 0, 0, 'L', 0, '', 1, 0);
$pdf->CellFit(77, 5, '' . mb_strtoupper(utf8_decode($registro[0]['Direccion'])), 0, 0, 'C', 0, '', 1, 0);
$pdf->CellFit(23, 5, ' ', 0, 0, 'L', 0, '', 1, 0);
$pdf->CellFit(79, 5, '(DOMICILIO)' , 'B', 0, 'L', 0, '', 1, 0);

// firmas($pdf, $registro, $fiadores);

ob_start();
$pdf->Output();
$pdfData = ob_get_contents();
ob_end_clean();

$opResult = array(
    'status' => 1,
    'mensaje' => 'Pagaré generado correctamente',
    'namefile' => "Pagare",
    'tipo' => "pdf",
    'data' => "data:application/pdf;base64," . base64_encode($pdfData)
);
echo json_encode($opResult);

//FUNCIONES
function headermanual($fpdf, $dtCre, $info, $registro, $creppg)
{
    $ancholinea = 4;
    $i = 0;
    // $fpdf->Image("../../../.." . $info[0]["log_img"], 164, 16, 30);
    $sumacapconinteres=($registro[0]['NCapDes'])+(array_sum(array_column($creppg,'nintere')));
    $fpdf->SetFont('Arial', 'B', 11);
    $fpdf->SetTextColor(255, 0, 0);
    $fpdf->Cell(0, 3, 'PAGARE LIBRE DE PROTESTO', 0, 1, 'C');
    $fpdf->SetTextColor(0, 0, 0);
    $fpdf->ln(2);
    $fpdf->SetFont('Arial', '', 10);
    $fpdf->Cell(92,  $ancholinea , 'MUNICIPIO: '. utf8_decode(mb_strtoupper($registro[$i]['Direccion'])) , 0, 0, 'L');
    $fpdf->Cell(90,  $ancholinea , 'No. '.$registro[0]['Dictamen'], 0, 0, 'R');
    $fpdf->ln(5);
    $fpdf->Cell(92,  $ancholinea , 'TELEFONO: '. utf8_decode(mb_strtoupper($registro[$i]['tel_no1'])) , 0, 0, 'L');
    $fpdf->Cell(90,  $ancholinea , 'BUENO POR: Q.                          ' , 0, 0, 'R');
    $fpdf->ln(2);
}

function parr1($datacre, $vlrs, $registro, $fiadores, $info, $creppg, $ncapita)
{

    $fechainicio = date("d-m-Y", strtotime($registro[0]["DfecPago"]));
    $fechafin = date("d-m-Y", strtotime($registro[0]["DFecVen"]));
    $format_monto = new NumeroALetras();
    $sumacapconinteres=($datacre[0])+(array_sum(array_column($creppg,'nintere')));
    $montoletra = $format_monto->toMoney($sumacapconinteres, 2, 'QUETZALES', 'CENTAVOS');
    //fecha en letras
    $meses = array("ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE");
    $fechadesembolso = strtotime($registro[0]['DFecDsbls']);
    $dia_desembolso = new NumeroALetras();
    $dia_desembolsoaux = mb_strtolower($dia_desembolso->toWords((date("d", $fechadesembolso))), 'utf-8');
    $anodesembolso = new NumeroALetras();
    $anodesembolsoaux = mb_strtolower($anodesembolso->toWords((date("Y", $fechadesembolso))), 'utf-8');
    //DIRECCION AGENCIA
    $muniagencia = ucfirst(mb_strtolower($info[0]['municipioagencia']));
    $depaagencia = ucfirst(mb_strtolower($info[0]['departamentoagencia']));
    //edad en letras
    $edadletras = new NumeroALetras();
    //numero de cuotas
    $numerocuotas = new NumeroALetras();
    $numerocuotasaux = mb_strtoupper($numerocuotas->toWords($registro[0]["noPeriodo"]), 'utf-8');


    $datos = (date("d", $fechadesembolso) == 1) ? utf8_decode("<p> ") : utf8_decode("<p> $dia_desembolsoaux días del mes ");
    $datos .= "Por este medio acepto y me obligo a pagar incondicionalmente a partir del";
    $datos .= " de " . mb_strtolower($meses[date("m", $fechadesembolso) - 1], 'utf-8') . utf8_decode(' del año ') . utf8_decode($anodesembolsoaux).'. ';
    $datos .= "A la orden de INJELIFID S.A, en la Ciudad de Guatemala, Guatemala";
    $datos .=utf8_decode(" La suma de <vb>' . $montoletra . '</vb> más intereses");
    $i = 0;
    $nombresdetalles = '';
    $dias =0;
    $dias = $registro[$i]['noPeriodo'] *30;

    while ($i < count($registro)) {
        $nombresdetalles .= ' con numero de DPI ' . $registro[$i]['no_identifica'] . ' Dicha cantidad se amortizará de la siguiente manera '. $registro[$i]['noPeriodo'].' pagos en ' . $dias .' dias, en cuotas de '.   $ncapita .' 0 a partir de la presente fecha deuda.
        Valor recibido a mi satisfacción. Este pagare forma parte de una serie numerada de 1 al 1 por lo que, en caso de la falta de pago oportuno, dará 
        facultades al beneficiario del mismo, para que, desde la fecha de vencimiento de este documento hasta el día de su cancelación, cause intereses 
        moratorios a razón del 7% mensuales pagaderos en esta ciudad conjuntamente con el principal.
        En caso de incumplimiento RENUNCIO al fuero de mi domicilio y me someto a los tribunales que elija la entidad prestadora de servicio, para la ejecución 
        del presente título. Señalo como lugar de recibir notificaciones mi residencia.' ;
        $i++;
    }
    $datos .= utf8_decode($nombresdetalles);


    $datos .= utf8_decode('  ');
    $i = 0;
    $nombresdetalles = '';
    while ($i < count($registro)) {
        $edadletrasaux = $edadletras->toWords((calcular_edad($registro[$i]['date_birth'])));
        $nombresdetalles .= '  ';
        $i++;
    }
    $datos .= $nombresdetalles;
    $datos .= '  '.utf8_decode(" ").'.</vb></p>   ';
    //HASTA ACA ES FUNCIONAL

    return $datos;
}

function parr2($registro)
{
    $datos = "";
    //LO QUE SIGUE DESPUES DE LOS PLANES DE PAGO
    $datos .= '<p>'  . '</p>';

   
    return $datos;
}

function plandepagos($pdf, $registro, $creppg)
{
    $divperiodo = (($registro[0]['noPeriodo'] / 4));
    $parteentera = $divperiodo;
    $partedecimal = 0;
    $banderadecimal = false;
    if (is_float($divperiodo)) {
        $banderadecimal = true;
        $parteentera = (int)$divperiodo;
        $partedecimal = $divperiodo - $parteentera;
    }

    $pdf->SetFont('Times', 'B', 7);
 

    $pdf->Ln(-18);
    $pdf->SetFont('Times', '', 7);
  

}
