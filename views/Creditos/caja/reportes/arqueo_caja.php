<?php

declare(strict_types=1);
session_start();
include '../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
require '../../../../fpdf/fpdf.php';
require '../../../../vendor/autoload.php';
include '../../../../src/funcphp/fun_ppg.php';
use Luecano\NumeroALetras\NumeroALetras;

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

//se recibe los datos
$datos = $_POST["datosval"];
//Informacion de datosval 
$archivo = $datos[3];
//Informacion de archivo 
$usuario = $archivo[0];
$codregistro = $archivo[1];

printpdf($usuario, $codregistro, $conexion);

function printpdf($usuario, $codregistro, $conexion)
{
    // Lanza excepciones en notices y warnings 
    set_error_handler(function ($severity, $message, $file, $line) {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    });
    try {
        //validar datos de la cooperativa
        $info[] = [];
        $i = 0;
        $stmt = $conexion->prepare("SELECT * FROM clhpzzvb_bd_general_coopera.info_coperativa ins
        INNER JOIN tb_agencia ag ON ag.id_institucion=ins.id_cop where ag.id_agencia= ?");
        if (!$stmt) {
            throw new ErrorException("Error en la consulta 1: " . $conexion->error);
        }
        $stmt->bind_param("s", $_SESSION['id_agencia']); //El arroba omite el warning de php
        if (!$stmt->execute()) {
            throw new ErrorException("Error en la ejecucion de la consulta 1: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $numFilas = $result->num_rows;
        if ($numFilas < 1) {
            throw new ErrorException("Institucion asignada a la agencia no encontrada");
        }
        while ($fila = $result->fetch_assoc()) {
            $info[$i] = $fila;
            $i++;
        }
        //Validar si de casualidad ya se hizo el cierre otro usuario
        $stmt2 = $conexion->prepare("SELECT tcac.estado FROM tb_caja_apertura_cierre tcac WHERE tcac.id = ? AND tcac.estado='2'");
        if (!$stmt2) {
            throw new ErrorException("Error en la consulta 2: " . $conexion->error);
        }
        $stmt2->bind_param("s", $codregistro); //El arroba omite el warning de php
        if (!$stmt2->execute()) {
            throw new ErrorException("Error en la ejecucion de la consulta 2: " . $stmt2->error);
        }
        $result = $stmt2->get_result();
        $numFilas = $result->num_rows;
        if ($numFilas < 1) {
            throw new ErrorException("No se encontro registro con el identificador del arqueo");
        }
        //consultando los montos para hacer los calculos
        $datos[] = [];
        $stmt3 = $conexion->prepare("SELECT tcac.*, tu.nombre AS nombres, tu.apellido AS apellidos, tu.usu AS usuario, CAST(tcac.created_at AS TIME) AS hora_apertura, CAST(tcac.updated_at AS TIME) AS hora_cierre,
            (SELECT IFNULL(SUM(a.monto) ,0) FROM ahommov a WHERE a.cestado!=2 AND a.ctipope = 'D' AND a.created_at = tcac.fecha_apertura AND a.created_by = tcac.id_usuario) AS ingresos_ahorros,
            (SELECT IFNULL(SUM(b.monto) ,0) FROM ahommov b WHERE b.cestado!=2 AND b.ctipope = 'R' AND b.created_at = tcac.fecha_apertura AND b.created_by = tcac.id_usuario) AS egresos_ahorros,
            (SELECT IFNULL(SUM(c.monto) ,0) FROM aprmov c WHERE c.cestado!=2 AND c.ctipope = 'D' AND c.created_at = tcac.fecha_apertura AND c.created_by = tcac.id_usuario) AS ingresos_aportaciones,
            (SELECT IFNULL(SUM(d.monto) ,0) FROM aprmov d WHERE d.cestado!=2 AND d.ctipope = 'R' AND d.created_at = tcac.fecha_apertura AND d.created_by = tcac.id_usuario) AS egresos_aportaciones,
            (SELECT IFNULL(SUM(ck.KP) ,0) FROM CREDKAR ck WHERE ck.CTIPPAG = 'D' AND  CAST(ck.DFECSIS AS DATE)= tcac.fecha_apertura AND ck.CESTADO != 'X' AND ck.CCODUSU = tcac.id_usuario) AS desembolsos_creditos,
            (SELECT IFNULL(SUM(ck2.NMONTO) ,0)  FROM CREDKAR ck2 WHERE ck2.CTIPPAG = 'P' AND  CAST(ck2.DFECSIS AS DATE) = tcac.fecha_apertura AND ck2.CESTADO != 'X' AND ck2.CCODUSU = tcac.id_usuario) AS pagos_creditos,
            (SELECT IFNULL(SUM(opm.monto) ,0)  FROM otr_pago_mov opm INNER JOIN otr_pago op ON opm.id_otr_pago = op.id INNER JOIN otr_tipo_ingreso oti ON opm.id_otr_tipo_ingreso = oti.id WHERE op.estado = '1' AND opm.estado = '1' AND oti.estado = '1' AND oti.tipo = '1' AND CAST(op.created_at AS DATE) = tcac.fecha_apertura AND op.created_by = tcac.id_usuario) AS otros_ingresos,
            (SELECT IFNULL(SUM(opm.monto) ,0)  FROM otr_pago_mov opm INNER JOIN otr_pago op ON opm.id_otr_pago = op.id INNER JOIN otr_tipo_ingreso oti ON opm.id_otr_tipo_ingreso = oti.id WHERE op.estado = '1' AND opm.estado = '1' AND oti.estado = '1' AND oti.tipo = '2' AND CAST(op.created_at AS DATE) = tcac.fecha_apertura AND op.created_by = tcac.id_usuario) AS otros_egresos
            FROM tb_caja_apertura_cierre tcac INNER JOIN tb_usuario tu ON tcac.id_usuario = tu.id_usu 
            WHERE tcac.id = ? AND tcac.estado='2'");
        if (!$stmt3) {
            throw new ErrorException("Error en la consulta 3: " . $conexion->error);
        }
        $stmt3->bind_param("s", $codregistro); //El arroba omite el warning de php
        if (!$stmt3->execute()) {
            throw new ErrorException("Error en la ejecucion de la consulta 3: " . $stmt3->error);
        }
        $result = $stmt3->get_result();
        $numFilas = $result->num_rows;
        if ($numFilas < 1) {
            throw new ErrorException("No se encontraron registros");
        }
        $i = 0;
        while ($fila = $result->fetch_assoc()) {
            $datos[$i] = $fila;
            $i++;
        }
        $datos[0]['sumaingresos'] = ($datos[0]['ingresos_ahorros'] + $datos[0]['ingresos_aportaciones'] + $datos[0]['pagos_creditos'] + $datos[0]['otros_ingresos']);
        $datos[0]['sumaegresos'] = ($datos[0]['egresos_ahorros'] + $datos[0]['egresos_aportaciones'] + $datos[0]['desembolsos_creditos'] + $datos[0]['otros_egresos']);
        $datos[0]['saldofinal'] = (($datos[0]['sumaingresos']) - ($datos[0]['sumaegresos']));
        $datos[0]['sumasiguales'] = ($datos[0]['saldo_inicial'] - abs($datos[0]['saldofinal']));
        $datos[0]['saldofinal'] = round($datos[0]['saldofinal'], 2);
        $datos[0]['sumasiguales'] = round($datos[0]['sumasiguales'], 2);

        //INICIO PARA FORMAR EL REPORTE
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
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $fuente = "Courier";
        $tamanio_linea = 5;
        $ancho_linea = 30;

        //CUERPO DEL REPORTE
        $pdf->SetFont($fuente, 'B', 11);
        $pdf->Cell(0, $tamanio_linea, utf8_decode("ARQUEO DE CAJA"), 0, 0, 'C');
        $pdf->Ln(3);
        $pdf->Cell(0, $tamanio_linea, ' ', 'B', 1, 'C');
        $pdf->SetFont($fuente, 'B', 9);
        $pdf->Ln(5);
        $pdf->CellFit($ancho_linea - 14, $tamanio_linea, 'CAJERO:', 0, 0, 'L', 0, '', 1, 0);
        $pdf->SetFont($fuente, '', 9);
        $pdf->CellFit($ancho_linea + 79, $tamanio_linea, (($datos[0]['nombres'] == '' || $datos[0]['nombres'] == null) ? ' ' : utf8_decode($datos[0]['nombres'])) . ' ' . (($datos[0]['apellidos'] == '' || $datos[0]['apellidos'] == null) ? ' ' : utf8_decode($datos[0]['apellidos'])), 0, 0, 'L', 0, '', 1, 0);
        $pdf->SetFont($fuente, 'B', 9);
        $pdf->CellFit($ancho_linea - 10, $tamanio_linea, 'USUARIO:', 0, 0, 'R', 0, '', 1, 0);
        $pdf->SetFont($fuente, '', 9);
        $pdf->CellFit($ancho_linea + 15, $tamanio_linea, (($datos[0]['usuario'] == '' || $datos[0]['usuario'] == null) ? ' ' : utf8_decode($datos[0]['usuario'])), 0, 0, 'L', 0, '', 1, 0);
        $pdf->Ln(6);

        $pdf->SetFont($fuente, 'B', 9);
        $pdf->CellFit($ancho_linea + 5, $tamanio_linea, 'FECHA DE APERTURA:', 0, 0, 'L', 0, '', 1, 0);
        $pdf->SetFont($fuente, '', 9);
        $pdf->CellFit($ancho_linea + 8, $tamanio_linea, (($datos[0]['fecha_apertura'] == '' || $datos[0]['fecha_apertura'] == null) ? ' ' : (date('d-m-Y', strtotime($datos[0]['fecha_apertura'])))) . ' ' . (($datos[0]['hora_apertura'] == '' || $datos[0]['hora_apertura'] == null) ? ' ' : utf8_decode($datos[0]['hora_apertura'])), 0, 0, 'L', 0, '', 1, 0);
        $pdf->SetFont($fuente, 'B', 9);
        $pdf->CellFit($ancho_linea, $tamanio_linea, 'FECHA DE CIERRE:', 0, 0, 'L', 0, '', 1, 0);
        $pdf->SetFont($fuente, '', 9);
        $pdf->CellFit($ancho_linea + 7, $tamanio_linea, (($datos[0]['fecha_cierre'] == '' || $datos[0]['fecha_cierre'] == null) ? ' ' : (date('d-m-Y', strtotime($datos[0]['fecha_cierre'])))) . ' ' . (($datos[0]['hora_cierre'] == '' || $datos[0]['hora_cierre'] == null) ? ' ' : utf8_decode($datos[0]['hora_cierre'])), 0, 0, 'L', 0, '', 1, 0);
        $pdf->SetFont($fuente, 'B', 9);
        $pdf->CellFit($ancho_linea - 6, $tamanio_linea, 'ARQUEO No.:', 0, 0, 'R', 0, '', 1, 0);
        $pdf->SetFont($fuente, '', 9);
        $pdf->CellFit($ancho_linea - 4, $tamanio_linea, 'A-0' . (($datos[0]['id'] == '' || $datos[0]['id'] == null) ? ' ' : ($datos[0]['id'])) . '0', 'B', 0, 'C', 0, '', 1, 0);

        $pdf->Ln(4);
        $pdf->Cell(0, $tamanio_linea, ' ', 'B', 1, 'C');
        $pdf->SetFont($fuente, 'B', 9);
        $pdf->Ln(5);

        //SALDO INICIAL
        $pdf->SetFont($fuente, 'B', 9);
        $pdf->CellFit($ancho_linea + 125, $tamanio_linea, '1. SALDO INICIAL DEL SISTEMA', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 5, $tamanio_linea, (($datos[0]['saldo_inicial'] == '' || $datos[0]['saldo_inicial'] == null) ? ' ' : 'Q ' . number_format($datos[0]['saldo_inicial'], 2, '.', '')), 1, 0, 'R', 0, '', 1, 0);
        $pdf->SetFont($fuente, 'B', 9);
        $pdf->Ln(6);
        $pdf->CellFit($ancho_linea - 20, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 80, $tamanio_linea, 'Saldo inicial', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 5, $tamanio_linea, (($datos[0]['saldo_inicial'] == '' || $datos[0]['saldo_inicial'] == null) ? ' ' : 'Q ' . number_format($datos[0]['saldo_inicial'], 2, '.', '')), 'B', 0, 'R', 0, '', 1, 0);
        $pdf->Ln(7);

        //SALDO FINAL
        $pdf->SetFont($fuente, 'B', 9);
        $pdf->CellFit($ancho_linea + 125, $tamanio_linea, '2. MOVIMIENTOS EN EL SISTEMA', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 5, $tamanio_linea, ('Q ' . number_format($datos[0]['saldofinal'], 2, '.', '')), 1, 0, 'R', 0, '', 1, 0);
        $pdf->Ln(6);
        //Ingresos
        $pdf->CellFit($ancho_linea - 20, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 80, $tamanio_linea, 'Ingresos', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 5, $tamanio_linea, (($datos[0]['sumaingresos'] == '' || $datos[0]['sumaingresos'] == null) ? ' ' : 'Q ' . number_format($datos[0]['sumaingresos'], 2, '.', '')), 'B', 0, 'R', 0, '', 1, 0);
        $pdf->Ln(6);

        $pdf->SetFont($fuente, '', 9);
        $pdf->CellFit($ancho_linea - 10, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 35, $tamanio_linea, 'Ahorros', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 5, $tamanio_linea, ('Q ' . number_format($datos[0]['ingresos_ahorros'], 2, '.', '')), 'B', 0, 'R', 0, '', 1, 0);
        $pdf->Ln(6);

        $pdf->CellFit($ancho_linea - 10, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 35, $tamanio_linea, 'Aportaciones', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 5, $tamanio_linea, ('Q ' . number_format($datos[0]['ingresos_aportaciones'], 2, '.', '')), 'B', 0, 'R', 0, '', 1, 0);
        $pdf->Ln(6);

        $pdf->CellFit($ancho_linea - 10, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 35, $tamanio_linea, utf8_decode('Pagos créditos'), 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 5, $tamanio_linea, ('Q ' . number_format($datos[0]['pagos_creditos'], 2, '.', '')), 'B', 0, 'R', 0, '', 1, 0);
        $pdf->Ln(6);

        $pdf->CellFit($ancho_linea - 10, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 35, $tamanio_linea, 'Otros', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 5, $tamanio_linea, ('Q ' . number_format($datos[0]['otros_ingresos'], 2, '.', '')), 'B', 0, 'R', 0, '', 1, 0);
        $pdf->Ln(6);

        //Egresos
        $pdf->SetFont($fuente, 'B', 9);
        $pdf->CellFit($ancho_linea - 20, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 80, $tamanio_linea, 'Egresos', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 5, $tamanio_linea, ('Q ' . number_format($datos[0]['sumaegresos'], 2, '.', '')), 'B', 0, 'R', 0, '', 1, 0);
        $pdf->Ln(6);

        $pdf->SetFont($fuente, '', 9);
        $pdf->CellFit($ancho_linea - 10, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 35, $tamanio_linea, 'Ahorros', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 5, $tamanio_linea, ('Q ' . number_format($datos[0]['egresos_ahorros'], 2, '.', '')), 'B', 0, 'R', 0, '', 1, 0);
        $pdf->Ln(6);

        $pdf->CellFit($ancho_linea - 10, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 35, $tamanio_linea, 'Aportaciones', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 5, $tamanio_linea, ('Q ' . number_format($datos[0]['egresos_aportaciones'], 2, '.', '')), 'B', 0, 'R', 0, '', 1, 0);
        $pdf->Ln(6);

        $pdf->CellFit($ancho_linea - 10, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 35, $tamanio_linea, utf8_decode('Desembolso créditos'), 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 5, $tamanio_linea, ('Q ' . number_format($datos[0]['desembolsos_creditos'], 2, '.', '')), 'B', 0, 'R', 0, '', 1, 0);
        $pdf->Ln(6);

        $pdf->CellFit($ancho_linea - 10, $tamanio_linea, ' ', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 35, $tamanio_linea, 'Otros', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 5, $tamanio_linea, ('Q ' . number_format($datos[0]['otros_egresos'], 2, '.', '')), 'B', 0, 'R', 0, '', 1, 0);

        //LINEA SEPARADORA
        $pdf->Ln(4);
        $pdf->Cell(0, $tamanio_linea, ' ', 'B', 1, 'C');
        $pdf->SetFont($fuente, 'B', 9);
        $pdf->Ln(5);

        $pdf->SetFont($fuente, 'B', 10);
        $pdf->CellFit($ancho_linea + 125, $tamanio_linea, 'TOTAL CAJA ARQUEO', 0, 0, 'L', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea + 5, $tamanio_linea, ('Q ' . number_format($datos[0]['sumasiguales'], 2, '.', '')), 0, 0, 'R', 0, '', 1, 0);
        $pdf->Ln(4);
        $pdf->Cell(0, $tamanio_linea, ' ', 'B', 1, 'C');
        $pdf->Ln(6);

        //FIRMAS
        $pdf->Ln(2);
        $pdf->firmas(2, ['Cajero', 'Gerencia']);
        $pdf->Ln(25);

        //OBSERVACIONES
        $pdf->SetFont($fuente, 'B', 10);
        $pdf->Cell(0, $tamanio_linea, 'OBSERVACIONES', 0, 0, 'L');
        $pdf->Ln(8);
        $pdf->Cell(0, $tamanio_linea, ' ', 'B', 0, 'C');
        $pdf->Ln(7);
        $pdf->Cell(0, $tamanio_linea, ' ', 'B', 0, 'C');
        $pdf->Ln(7);
        $pdf->Cell(0, $tamanio_linea, ' ', 'B', 0, 'C');
        $pdf->Ln(7);
        $pdf->Cell(0, $tamanio_linea, ' ', 'B', 0, 'C');

        ob_start();
        $pdf->Output();
        $pdfData = ob_get_contents();
        ob_end_clean();

        $opResult = array(
            'status' => 1,
            'mensaje' => 'Comprobante generado correctamente',
            'namefile' => "Arqueo_caja_no-00" . $datos[0]['id'] . "0",
            'tipo' => "pdf",
            'data' => "data:application/pdf;base64," . base64_encode($pdfData)
        );
        echo json_encode($opResult);
    } catch (\ErrorException $e) {
        //Captura el error
        $mensaje_error = $e->getMessage();
        echo json_encode(array(
            'status' => 0,
            'mensaje' => $mensaje_error,
            'dato' => '0'
        ));
    } finally {
        if ($stmt !== false) {
            $stmt->close();
        }
        $conexion->close();
    }
}
