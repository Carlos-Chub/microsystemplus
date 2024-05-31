<?php
session_start();
include '../../../src/funcphp/func_gen.php';
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
require '../../../fpdf/fpdf.php';

require '../../../vendor/autoload.php';
$hoy = date("Y-m-d");

use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Round;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Trim;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

$datos = $_POST["datosval"];
$inputs = $datos[0];
$archivo = $datos[3];
$tipo = $_POST["tipo"];

$condi = " AND id_ctb_nomenclatura=" . $archivo[0]; //FILTRAR LA CUENTA
$condi = $condi . " AND feccnt BETWEEN '" . $archivo[1] . "' AND '" . $archivo[2] . "'"; //FILTRAR POR RANGO DE FECHAS
$strquery = "SELECT cmov.* from ctb_diario_mov cmov INNER JOIN ctb_bancos ban ON ban.id_nomenclatura=cmov.id_ctb_nomenclatura 
                WHERE ban.estado=1 AND id_tipopol != 9 AND cmov.estado=1" . $condi . " ORDER BY id_ctb_nomenclatura,feccnt";

$querypol = mysqli_query($conexion, $strquery);
$ctbmovdata[] = [];
$j = 0;
while ($fil = mysqli_fetch_array($querypol)) {
    $ctbmovdata[$j] = $fil;
    $j++;
}
//COMPROBAR SI HAY REGISTROS
if ($j == 0) {
    echo json_encode(['status' => 0, 'mensaje' => 'No hay datos']);
    return;
}


$datosbancos = "SELECT banc.nombre,cb.numcuenta FROM ctb_bancos cb INNER JOIN tb_bancos banc ON banc.id=cb.id_banco WHERE cb.id_nomenclatura=" . $archivo[0];
$querybanco = mysqli_query($conexion, $datosbancos);
$databancos[] = [];
$j = 0;
while ($fil = mysqli_fetch_array($querybanco)) {
    $databancos[$j] = $fil;
    $j++;
}


/*  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++++++++++++ INFORMACION DE LA INSTITUCION +++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */

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

//TIPO DE ARCHIVO A IMPRIMIR
switch ($tipo) {
    case 'xlsx';
        printxls($ctbmovdata,  $info, $datos, $databancos);
        break;
    case 'pdf':
        //printpdf($ctbmovdata, $apertura, $salinidata, $info, $hayanteriores, $haypartidaapr);
        break;
}

//funcion para generar archivo excel
function printxls($ctbmovdata, $info, $parametros, $databancos)
{
    require '../../../vendor/autoload.php';
    $inputs = $parametros[0];
    $archivo = $parametros[3];
    $transito = (isset($archivo[3])) ? $archivo[3] : NULL;

    $excel = new Spreadsheet();
    $activa = $excel->getActiveSheet();
    $activa->setTitle("Conciliacion");

    $activa->getColumnDimension("A")->setWidth(5);
    $activa->getColumnDimension("B")->setWidth(15);
    $activa->getColumnDimension("C")->setWidth(35);
    $activa->getColumnDimension("D")->setWidth(15);
    $activa->getColumnDimension("E")->setWidth(30);
    $activa->getColumnDimension("F")->setWidth(10);
    $activa->getColumnDimension("G")->setWidth(20);

    //APLICAR BORDE A LA CELDA
    $styleArray = [
        'borders' => [
            'outline' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                'color' => ['argb' => '00000000'],
            ],
        ],
    ];

    // CENTRAR TEXTO EN LA CELDA
    $estilocentrar = [
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
    ];
    //$sheet->getStyle('A1')->applyFromArray($styleArray);
    /*  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++ DATOS DE ENCABEZADDO +++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $mes = date('F', strtotime($archivo[1]));
    $anio = date('Y', strtotime($archivo[1]));
    $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $nombreMes = str_replace($meses_EN, $meses_ES, $mes);

    $activa->getStyle('A1:G2')->applyFromArray($styleArray);
    $activa->getStyle('A3:B5')->applyFromArray($styleArray);

    $activa->getStyle('A1:A5')->getFont()->setBold(true);
    $activa->mergeCells('A1:G2')->setCellValue('A1', 'CONCILIACION BANCARIA')->getStyle('A1')->applyFromArray($estilocentrar);
    $activa->mergeCells('A3:B3')->setCellValue('A3', 'EMPRESA:');
    $activa->mergeCells('A4:B4')->setCellValue('A4', 'BANCO');
    $activa->mergeCells('A5:B5')->setCellValue('A5', 'CORRESPONDIENTE AL MES DE');

    $activa->mergeCells('C3:G3')->setCellValue('C3', $info[0]['nomb_comple']);
    $activa->mergeCells('C4:D4')->setCellValue('C4', $databancos[0]['nombre']);
    $activa->mergeCells('C5:D5')->setCellValue('C5', $nombreMes . ' ' . $anio);

    $activa->getStyle('E4:E6')->getFont()->setBold(true);
    $activa->setCellValue('E4', 'CUENTA');
    $activa->setCellValue('E5', 'EXPRESADO EN');
    $activa->setCellValue('E6', 'SALDO ANTERIOR');

    $activa->mergeCells('F4:G4')->setCellValue('F4', $databancos[0]['numcuenta']);
    $activa->mergeCells('F5:G5')->setCellValue('F5', 'Quetzales');
    $activa->mergeCells('F6:G6')->setCellValue('F6', 0);

    /*  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        +++++++++++++++++++++++++++++++++ DETALLES CHEQUES EN TRANSITO+++++++++++++++++++++++++++++++++++
        +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */

    $activa->getStyle('A6:D7')->getFont()->setBold(true);
    $activa->mergeCells('A6:D6')->setCellValue('A6', 'CHEQUES EN TRANSITO')->getStyle('A6')->applyFromArray($estilocentrar);
    $activa->setCellValue('A7', 'No.');
    $activa->setCellValue('B7', 'FECHA');
    $activa->setCellValue('C7', 'NOMBRE');
    $activa->setCellValue('D7', 'MONTO');



    $linea = 8;
    $partidasvalidas = $ctbmovdata;

    $partidastransito = [];
    $idsentransito = [];
    if ($transito != NULL) {
        $i = 0;
        while ($i < count($transito)) {
            $tr = $transito[$i];
            $idcirculacion = array_search($tr, array_column($partidasvalidas, 'id_ctb_diario'));
            $circulacion = $partidasvalidas[$idcirculacion];
            $partidastransito[$i] = $circulacion;
            $idsentransito[$i] = $idcirculacion;

            $monto =  $circulacion['haber'];
            $activa->setCellValue('A' . ($linea), ($i + 1));
            $activa->setCellValue('B' . ($linea),  date("d-m-Y", strtotime($circulacion['feccnt'])));
            $activa->setCellValue('C' . ($linea), $circulacion['nombrecheque']);
            $activa->setCellValue('D' . ($linea), $monto);
            $linea++;
            $i++;
        }
        foreach ($idsentransito as $key) {
            if (isset($partidasvalidas[$key])) {
                unset($partidasvalidas[$key]);
            }
        }
    }

    $linea = ($linea > 29) ? $linea : 29;

    $activa->mergeCells('A' . ($linea) . ':C' . ($linea))->setCellValue('A' . ($linea), 'Total Cheques en Transito');
    $activa->setCellValue('D' . ($linea), array_sum(array_column($partidastransito, 'haber')));
    $activa->getStyle('A' . ($linea) . ':D' . ($linea))->getFont()->setBold(true);
    $activa->getStyle('A6:D' . ($linea))->applyFromArray($styleArray);
    /*  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        +++++++++++++++++ DETALLES BANCO (LLENADO SEGUN DATOS PROPORCIONADOS POR EL BANCO) ++++++++++++++
        +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $activa->getStyle('E4:G5')->applyFromArray($styleArray);
    $activa->getStyle('E6:G15')->applyFromArray($styleArray);
    $activa->setCellValue('E7', 'Notas de Crédito');
    $activa->setCellValue('E8', 'Depósitos');
    $activa->setCellValue('E9', 'Total Depósitos y N/C')->getStyle('E9:G9')->getFont()->setBold(true);
    $activa->setCellValue('E10', 'Sub-Total');
    $activa->setCellValue('E11', 'Nota de Débito');
    $activa->setCellValue('E12', 'Cheques');
    $activa->setCellValue('E13', 'Cheques en circulacion mes anterior');
    $activa->setCellValue('E14', 'Total Cheques y N/D')->getStyle('E14:G14')->getFont()->setBold(true);
    $activa->setCellValue('E15', 'Saldo de Banco Ajustado');

    /*  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        +++++++++++++++++++++++++++++++++ DETALLES LIBRO AUXILIAR DE BANCO ++++++++++++++++++++++++++++++
        +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $activa->getStyle('E16:G29')->applyFromArray($styleArray);
    $creditos = array_filter($partidasvalidas, function ($var) {
        return ($var['debe'] > 0);
    });
    $depositos = array_filter($creditos, function ($var) {
        return ($var['id_tipopol'] == 10);
    });
    $notas_credito = array_filter($creditos, function ($var) {
        return ($var['id_tipopol'] == 11);
    });
    $otros = array_filter($creditos, function ($var) {
        return ($var['id_tipopol'] != 10 && $var['id_tipopol'] != 11);
    });


    $debitos = array_filter($partidasvalidas, function ($var) {
        return ($var['haber'] > 0);
    });
    $cheques = array_filter($debitos, function ($var) {
        return ($var['nombrecheque'] != '-' || $var['id_tipopol'] == 7);
    });
    $notas_debito = array_filter($debitos, function ($var) {
        return ($var['nombrecheque'] == '-' && $var['id_tipopol'] != 7);
    });

    $activa->getStyle('E15:G18')->getFont()->setBold(true);
    $activa->mergeCells('E16:G17')->setCellValue('E16', 'OPERACIONES DEL LIBRO AUXILIAR DE BANCO')->getStyle('E16')->applyFromArray($estilocentrar);

    $activa->setCellValue('E18', 'Saldo Ant. S/Libro Auxiliar Banco')->setCellValue('G18', $inputs[0]);
    $activa->setCellValue('E19', 'Depósitos')->setCellValue('F19', count($depositos))->setCellValue('G19', array_sum(array_column($depositos, 'debe')));
    $activa->setCellValue('E20', 'Traslados y transferencias')->setCellValue('F20', count($otros))->setCellValue('G20', array_sum(array_column($otros, 'debe')));
    $activa->setCellValue('E21', 'Notas de Credito')->setCellValue('F21', count($notas_credito))->setCellValue('G21', array_sum(array_column($notas_credito, 'debe')));
    $activa->setCellValue('E22', 'Total Depósitos y N/C')->setCellValue('F22', '=SUM(F19:F21)')->setCellValue('G22', '=SUM(G19:G21)')->getStyle('E22:G22')->getFont()->setBold(true);

    $activa->setCellValue('E23', 'Cheques')->setCellValue('F23', count($cheques))->setCellValue('G23',  array_sum(array_column($cheques, 'haber')));
    $activa->setCellValue('E24', 'Traslados y transferencias')->setCellValue('F24', 0)->setCellValue('G24', 0);
    $activa->setCellValue('E25', 'Notas de Débito')->setCellValue('F25', count($notas_debito))->setCellValue('G25',  array_sum(array_column($notas_debito, 'haber')));
    $activa->setCellValue('E26', 'Total Cheques y N/D')->setCellValue('F26', '=SUM(F23:F25)')->setCellValue('G26', '=SUM(G23:G25)')->getStyle('E26:G26')->getFont()->setBold(true);
    $activa->setCellValue('E27', 'Cheques en circulacion')->setCellValue('F27', count($partidastransito))->setCellValue('G27', '=D' . ($linea))->getStyle('E27:G27')->getFont()->setBold(true);
    $activa->setCellValue('E28', 'Saldo Libro Auxiliar Banco')->setCellValue('G28', '=G18+G22-G26-G27')->getStyle('E28:G28')->getFont()->setBold(true);
    $activa->setCellValue('E29', 'Total Libro Auxiliar Banco')->setCellValue('G29', '=G28')->getStyle('E29:G29')->getFont()->setBold(true);

    /*  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        +++++++++++++++++++++++++++++++++ PIE DE PAGINA (FIRMAS, ETC) +++++++++++++++++++++++++++++++++++
        +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $linea++;
    $activa->mergeCells('A' . ($linea) . ':B' . ($linea + 6))->setCellValue('A' . ($linea), 'Hecho Por: ');
    $activa->mergeCells('C' . ($linea) . ':D' . ($linea + 6))->setCellValue('C' . ($linea), 'Nombre del Contador');
    $activa->mergeCells('E' . ($linea) . ':G' . ($linea + 6))->setCellValue('E' . ($linea), 'Vo.Bo.');

    $linea = $linea + 15;

    $activa->getStyle('D' . ($linea) . ':E' . ($linea))->getFont()->setBold(true);
    $activa->mergeCells('C' . ($linea) . ':G' . ($linea + 1))->setCellValue('C' . ($linea), 'Aprobado Por:')->getStyle('C' . ($linea))->applyFromArray($estilocentrar);
    $linea++;
    $linea++;
    $activa->mergeCells('C' . ($linea) . ':G' . ($linea))->setCellValue('C' . ($linea), 'Gerencia General')->getStyle('C' . ($linea))->applyFromArray($estilocentrar);

    ob_start();
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel, 'Xlsx');
    $writer->save("php://output");
    $xlsData = ob_get_contents();
    ob_end_clean();

    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => "Conciliacion Bancaria",
        'tipo' => "xlsx",
        'data' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
    );
    echo json_encode($opResult);
    exit;
}
