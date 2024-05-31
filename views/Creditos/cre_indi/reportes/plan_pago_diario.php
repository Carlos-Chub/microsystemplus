<?php
session_start();
// TODAS LAS LIBRERIAS 
include '../../../../includes/BD_con/db_con.php';

require("../../../../fpdf/fpdf.php");
include '../../../../src/funcphp/fun_ppg.php';

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

// TODAS LAS LIBRERIAS 
$datos = $_POST["datosval"];
$inputs = $datos[0];
$archivo = $datos[3];
$codcre = $archivo[0];

if ($codcre == "") {
    echo json_encode(['status' => 0, 'mensaje' => 'No hay ningun código de crédito']);
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

// TRAE LOS DATOS DE LA CONSULTA 
$datos_cliente = mysqli_query($conexion, "SELECT CodCli,CCODCTA,short_name,CCODPRD,MonSug,NIntApro,DfecDsbls,DfecPago,noPeriodo,NtipPerC,CtipCre,P_ahoCr FROM cremcre_meta crem
INNER JOIN tb_cliente cli ON cli.idcod_cliente=crem.CodCli WHERE CCODCTA = '$codcre' ");
$clientedata = mysqli_fetch_array($datos_cliente);
$tipo_credito    = ($clientedata["CtipCre"]); //TIENE QUE SER FLAT SI ES DIARIO
$nombre_cliente = ($clientedata["short_name"]);
$fecpago   = ($clientedata["DfecPago"]);
$nocuotas  = ($clientedata["noPeriodo"]);
$tipo_periodo   = ($clientedata["NtipPerC"]); //TIENE QUE SER 1D SI ES DIARIO
$monto_aprobado     = ($clientedata["MonSug"]);
$interes    = ($clientedata["NIntApro"]);
$cuenta    = ($clientedata["CCODCTA"]);
$id_producto    = ($clientedata["CCODPRD"]); //TIENE QUE SER UN PRODUCTO DE CREDITO DIARIO

if ($tipo_credito != "Flat") {
    echo json_encode(['status' => 0, 'mensaje' => 'Tipo de credito Invalido, tiene que ser Flat ó nivelada']);
    return;
}
if ($tipo_periodo != "1D") {
    echo json_encode(['status' => 0, 'mensaje' => 'Tipo de periodo Invalido, tiene que ser Diario']);
    return;
}

// Trae las variables solo el nombre de los creditos
$tipcre = mysqli_query($general, "SELECT descr FROM `tb_credito` where abre = '$tipo_credito'");
$dtcre2 = mysqli_fetch_array($tipcre);

$titulos = ['Fecha', 'No Cuota', 'Cuota', 'Capital', 'Interes', 'Saldo Cap'];

$amortiza = calculo_montos_diario($monto_aprobado, $interes, $nocuotas,$conexion);
$fechaspago = calculo_fechas_por_nocuota($fecpago, $nocuotas, 1, $conexion);
// echo json_encode(['status' => 0, 'fechas' => $fechaspago,'montos' => $amortiza]);
// return;

class PDF extends FPDF
{
    public $CCODCTA, $DfecPago, $compl_name, $MonSug, $interes, $tipcre, $info, $P_ahoCr, $porCuota;
    //  VARIABLES QUE SE OBTIENEN POR CONSTRUCTOR
    public function __construct($CCODCTA, $DfecPago, $compl_name, $MonSug, $interes, $tipcre, $info, $P_ahoCr, $porCuota)
    {
        parent::__construct();
        $this->CCODCTA = $CCODCTA;
        $this->DfecPago = $DfecPago;
        $this->compl_name = $compl_name;
        $this->MonSug = $MonSug;
        $this->interes = $interes;
        $this->tipcre = $tipcre;
        $this->info = $info;
        $this->P_ahoCr = $P_ahoCr;
        $this->porCuota = $porCuota;
    }

    // Cabecera de página
    function Header()
    {
        $hoy = date("Y-m-d H:i:s");
        // Arial bold 15
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(0, 0, '', 0, 1, 'L', $this->Image('../../../..' . $this->info[0]["log_img"], 170, 12, 19));
        $this->Cell(0, 0, '', 0, 1, 'L', $this->Image('../../../../includes/img/logomicro.png', 20, 12, 19));
        //pruebas
        $this->Cell(190, 3, '' . $this->info[0]["nomb_comple"], 0, 1, 'C');
        $this->Cell(190, 3, '' . $this->info[0]["nomb_cor"], 0, 1, 'C');
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(190, 3, $this->info[0]["muni_lug"], 0, 1, 'C');
        $this->Cell(190, 3, 'Email:' . $this->info[0]["emai"], 0, 1, 'C');
        $this->Cell(190, 3, 'Tel:' . $this->info[0]["tel_1"] . " Y " . $this->info[0]["tel_2"], 0, 1, 'C');
        $this->Cell(190, 3, 'NIT:' . $this->info[0]["nit"], 0, 1, 'C');
        $this->Cell(0, 3, mb_strtoupper($this->info[0]["nom_agencia"], 'utf-8'), 'B', 1, 'C');
        $this->SetFont('Arial', '', 7);
        $this->SetXY(-30, 5);
        $this->Cell(10, 2, $hoy, 0, 1, 'L');
        $this->SetXY(-25, 8);
        $this->Ln(25);
        //************ */
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(50, 5, 'Plan de Pago', 0, 0, 'L');
        $this->Cell(50, 5, '' . $this->tipcre, 0, 1, 'L');
        //$this->Cell(111,25,'Plan de Pago',0,0,'C', $this->Image('../../../includes/img/logomicro.png',20,12,20));
        // Salto de línea
        //$this->Ln(15);
        //   DATOS DEL CREDITO
        $this->SetFont('Arial', '', 9);
        $this->Cell(70, 5, 'Codigo Credito : ' . $this->CCODCTA, 0, 0, 'L');
        $this->Cell(0, 5, 'Cliente : ' . mb_strtoupper($this->compl_name, 'utf-8'), 0, 1, 'L');
        $this->Cell(50, 5, 'Fecha de Pago : ' . date("d-m-Y", strtotime($this->DfecPago)), 0, 0, 'L');
        $this->Cell(40, 5, 'Monto : Q ' . number_format($this->MonSug, 2), 0, 0, 'L');
        $this->Cell(40, 5, 'Interes : ' . number_format($this->interes, 2) . '%', 0, 0, 'L');
        $this->Ln(10);
    }
    // Pie de página
    function Footer()
    {
        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // PARA EL RESTO DEL ENCABEZADO
    function encabezado($CCODCTA, $DfecPago, $compl_name, $MonSug, $interes, $tipcre)
    {
        $this->Cell(60, 5, '' . $tipcre, 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, 'Codigo Credito : ' . $CCODCTA, 0, 1, 'L');
        $this->Cell(50, 5, 'Cliente : ' . $compl_name, 0, 1, 'L');
        $this->Cell(50, 5, 'Fecha de Pago : ' . date("d-m-Y", strtotime($DfecPago)), 0, 0, 'L');
        $this->Cell(50, 5, 'Monto : Q ' . number_format($MonSug), 0, 0, 'L');
        $this->Cell(40, 5, 'Interes : ' . $interes, 0, 0, 'L');
        //$this->Cell(40,5,'Ahorro : '.$interes,0,0,'L');
        $this->Ln(15);
        $this->Line(10, 40, 206, 40);
    }
    //TABLA DE AMORTIZACION GERMAN EDITION
    function htable($titulos)
    {
        $w = array(25, 20, 25, 25, 20, 25, 25, 25);
        //$this->SetFillColor(255,0,0);
        //$this->SetTextColor(255);
        $this->SetDrawColor(0, 128, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');
        // Header
        for ($i = 0; $i < count($titulos); $i++)
            $this->Cell($w[$i], 7, $titulos[$i], 1, 0, 'C');
        $this->Ln();
    }
    //EL CONTENIDO DE LA TABLA 
    function btable($amortiza, $fchspgs, $ahoprgr, $porCuota)
    {
        $w = array(25, 20, 25, 25, 20, 25);
        $amortiza0 = $amortiza[0];
        $amortiza1 = $amortiza[1];
        $amortiza2 = $amortiza[2];
        // $fchspgs1  = $fchspgs[1];
        $fchspgs1  = $fchspgs[1];
        $i = 0;
        //PRUEBA PARA AGREGAR COLORES 
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        $fill = false;
        if ($porCuota != null) {
            array_push($w, 25);
        }
        foreach ($amortiza0 as $row) {
            //AJUSTE INICIO
            if ($i == array_key_last($amortiza0) && $amortiza2[$i] != 0) {
                $amortiza1[$i] = $amortiza1[$i] + $amortiza2[$i];
                $amortiza2[$i] = 0;
            }
            //AJUSTE FIN
            $this->Cell($w[0], 6, date("d-m-Y", strtotime($fchspgs1[$i])), 'LR', 0, 'L', $fill);
            $this->Cell($w[1], 6, $i + 1, 'LR', 0, 'C', $fill);
            $this->Cell($w[2], 6, number_format(($amortiza1[$i] + $row), 2), 'LR', 0, 'R', $fill); // TOTAL DE CUOTA 
            $this->Cell($w[3], 6, number_format($amortiza1[$i], 2), 'LR', 0, 'R', $fill);  //Capital
            $this->Cell($w[4], 6, number_format($row, 2), 'LR', 0, 'R', $fill);    //Interes
            //$this->Cell($w[6], 6, number_format($ahoprgr, 2), 'LR', 0, 'R', $fill);    //AHORRO PROGRA
            if ($porCuota != null) {
                //----------
                $l = 0;
                $montocobro = 0;
                while ($l < count($porCuota)) {
                    $tipo = $porCuota[$l]['tipo_deMonto'];
                    $cant = $porCuota[$l]['monto'];
                    $calculax = $porCuota[$l]['calculox'];
                    if ($tipo == 1) {
                        $mongas = $cant;
                    }
                    if ($tipo == 2) {
                        $mongas = ($calculax == 1) ? ($cant / 100 * $amortiza1[$i]) : (($calculax == 2) ? ($cant / 100 * $row) : (($calculax == 3) ? ($cant / 100 * ($amortiza1[$i] + $row)) : 0));
                    }
                    $montocobro = $montocobro + round($mongas, 2);
                    $l++;
                }
                //----------
                $this->Cell($w[6], 6, number_format(abs($montocobro), 2), 'LR', 0, 'R', $fill);
            }
            $this->Cell($w[5], 6, number_format(abs($amortiza2[$i]), 2), 'LR', 0, 'R', $fill);
            $this->Ln();
            $fill = !$fill;
            $i++;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}
// Creación del objeto de la clase heredada
//$pdf = new PDF();
$porCuota=NULL;
$pdf = new PDF($cuenta, $fecpago, $nombre_cliente, $monto_aprobado, $interes, 'DIARIO', $info, 0, $porCuota);
$pdf->AliasNbPages();
$pdf->AddPage();
//$pdf->encabezado($CCODCTA,$DfecPago,$compl_name,$MonSug,$interes,$dtcre2["descr"]);
$pdf->SetFont('Times', '', 12);
/* if ($porCuota != 0) {
    $pdf->Cell(0, 10, $porCuota, 0, 1);
} */
$pdf->htable($titulos);
$pdf->btable($amortiza, $fechaspago, 0, $porCuota);
$pdf->firmas(1, ['Asesor']);

ob_start();
$pdf->Output();
$pdfData = ob_get_contents();
ob_end_clean();

$opResult = array(
    'status' => 1,
    'mensaje' => 'Reporte generado correctamente',
    'namefile' => "PlanPago_No_" . $archivo[0],
    'tipo' => "pdf",
    'data' => "data:application/pdf;base64," . base64_encode($pdfData)
);
echo json_encode($opResult);
