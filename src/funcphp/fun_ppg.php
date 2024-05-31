<?php
// AQUI SE ENCUENTRAN TODAS LAS FUNCIONES REALACIONADAS CON LAS LLMADAS Y QUE SE MUESTREN LOS VALORES DE LOS CALCULOS DE LOS CREDITOS
// TANTO FECHAS COMO TIPOS DE AMORTIZACION Y SUS RESPECTIVOS CALCULOS 
// SE DEBERA DE TENER CUIDADO AL MOMENTO DE EDITAR CUALQUIER FUNCION. 

use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Month;

include 'cuota_fija.php';
include 'calcuFechas.php';
include 'func_gen.php';
include 'valida.php';

date_default_timezone_set('America/Guatemala');
function reestructura($codcuenta, $fechadesembolso, $ppganterior, $conexion, $database)
{
    /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        ++++++++++++++++++++++++++  DATOS DE LA CUENTA DE AHORROS ++++++++++++++++++++++++
        ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    try {
        $database->openConnection();
        $database->beginTransaction();
        $datacreditos = $database->getAllResults("SELECT CodCli,CCODCTA,CCODPRD,NCapDes,NIntApro,DfecDsbls,DfecPago,noPeriodo,NtipPerC,CtipCre,
            (select SUM(KP) FROM CREDKAR WHERE CCODCTA=crem.CCODCTA AND CTIPPAG='P' AND CESTADO!='X') pagadokp,
            (select SUM(INTERES) FROM CREDKAR WHERE CCODCTA=crem.CCODCTA AND CTIPPAG='P' AND CESTADO!='X') pagadoint,pr.dias_calculo
            FROM cremcre_meta crem 
            INNER JOIN cre_productos pr on pr.id=crem.CCODPRD
            WHERE CCODCTA =?", [$codcuenta]);

        //eliminar las cuotas mayores a la fecha de pago
        $database->delete("Cre_ppg", "ccodcta=? AND cestado='X'", [$codcuenta]); //eliminar cuotas no pagadas despues del pago
        $database->delete("Cre_ppg", "ccodcta=? AND dfecven>=? AND cestado='P'", [$codcuenta, $fechadesembolso]); //eliminar cuotas pagadas mayores a la fecha de pago

        //$ultimo = $database->getAllResults("SELECT id FROM Cre_ppg WHERE CCODCTA = ? ORDER BY id DESC LIMIT 1, 1", [$codcuenta]);
        $datappg = $database->getAllResults("SELECT IFNULL(SUM(ncapita),0) sumkp,IFNULL(SUM(nintere),0) sumint,IFNULL(MAX(cnrocuo),0) cnrocuo FROM Cre_ppg WHERE ccodcta =? AND cestado='P'", [$codcuenta]);

        // $kpnuevo = $datacreditos[0]['pagadokp'] - array_sum(array_column($ppganterior, 'ncapita'));
        // $intnuevo = $datacreditos[0]['pagadoint'] - array_sum(array_column($ppganterior, 'nintere'));
        $kpnuevo = $datacreditos[0]['pagadokp'] - $datappg[0]['sumkp'];
        $intnuevo = $datacreditos[0]['pagadoint'] - $datappg[0]['sumint'];
        $saldo = $datacreditos[0]['NCapDes'] - $datacreditos[0]['pagadokp'];
        // $saldo=$saldo-$kpnuevo*$kpnuevo;
        $nrocuo = $datappg[0]['cnrocuo'] + 1;
        $datos = array(
            'ccodcta' => $codcuenta,
            'dfecven' => $fechadesembolso,
            'dfecpag' => "0000-00-00",
            'cestado' => "P",
            'ctipope' => "0",
            'cnrocuo' => $nrocuo,
            'SaldoCapital' => $saldo,
            'nmorpag' => 0,
            'ncappag' => $kpnuevo,
            'nintpag' => $intnuevo,
            'AhoPrgPag' => 0,
            'OtrosPagosPag' => 0,
            'ccodusu' => 4,
            'dfecmod' => "0000-00-00",
            'cflag' => "0",
            'codigo' => "S",
            'creditosaf' => "1",
            'saldo' => 0,
            'nintmor' => 0,
            'ncapita' => $kpnuevo,
            'nintere' => $intnuevo,
            'NAhoProgra' => 0,
            'OtrosPagos' => 0
        );
        $database->insert('Cre_ppg', $datos);

        //A PARTIR DE ACA, GENERAR EL NUEVO

        $CtipCre    = ($datacreditos[0]["CtipCre"]);
        $DfecPago   = ($datacreditos[0]["DfecPago"]);
        $noPeriodo  = ($datacreditos[0]["noPeriodo"]);
        $NtipPerC   = ($datacreditos[0]["NtipPerC"]);
        $ncapdes     = ($datacreditos[0]["NCapDes"]);
        $interes    = ($datacreditos[0]["NIntApro"]);
        $CCODCTA    = ($datacreditos[0]["CCODCTA"]);
        $CODPRDT    = ($datacreditos[0]["CCODPRD"]);
        $pagado    = ($datacreditos[0]["pagadokp"]);
        $diascalculo    = ($datacreditos[0]["dias_calculo"]);
        // $saldo    = $ncapdes - $pagado;
        //-----------------------------------------------------
        $interes_calc = new profit_cmpst(); //inizializa mi clase
        $NtipPerC2 = $interes_calc->ntipPerc($NtipPerC);    //$mes, $frecuencia, $periodo
        // $rate  = (($interes / 100) / $NtipPerC2[1]);
        // $interes=($interes/12/30*365);
        $rate  = (($interes / 100) / $NtipPerC2[1]);
        $future_value = 0;        //// OJO!!!! ESTE VALOR ES MUY IMPORTANTE YA QUE CON ESTE SE PODRAN REALIZAR DESEMBOLSOS A FUTURO DESEMBOLSOS PARCIALES 
        $beginning = false;
        $Cap_amrt = round($ncapdes / $noPeriodo, 2);
        //TITULOS PARA CABEZERA //AGREGAR AHORRO

        $datalaboral = dias_habiles($conexion);
        if ($datalaboral == false) {
            echo json_encode(['status' => 0, 'mensaje' => 'Falló al recuperar dias laborales']);
            return;
        }

        // Convertir las cadenas de fecha en objetos DateTime
        $fechaPagoObj = DateTime::createFromFormat('Y-m-d', $DfecPago);
        $fechaDesembolsoObj = DateTime::createFromFormat('Y-m-d', $fechadesembolso);

        // Obtener el mes siguiente al de la fecha de desembolso
        $mesSiguiente = (int) $fechaDesembolsoObj->format('m') + 1;
        $anioSiguiente = $fechaDesembolsoObj->format('Y');
        if ($mesSiguiente > 12) {
            $mesSiguiente = 1;
            $anioSiguiente++;
        }

        // Crear la fecha de la primera cuota usando el día de pago del mes siguiente
        $fechaPrimeraCuotaObj = new DateTime("$anioSiguiente-$mesSiguiente-" . $fechaPagoObj->format('d'));

        // Formatear la fecha de la primera cuota como año-mes-día
        $fechaprimeracuota = $fechaPrimeraCuotaObj->format('Y-m-d');

        // $fechaprimeracuota =
        $diasprimeracuota = dias_dif($fechadesembolso, $fechaprimeracuota);

        // $amortiza = amortiza($CtipCre, $rate, $saldo, $plnuevo[0]['sinpagar'], $future_value, $beginning);
        $amortiza = amortizaadg($CtipCre, $rate, $ncapdes, $noPeriodo, $future_value, $beginning, $saldo, $diascalculo, $fechadesembolso, $fechaprimeracuota);
        $fchspgs = $interes_calc->calcudate2($fechaprimeracuota, count($amortiza[0]), $NtipPerC2[2], $datalaboral);


        $intereses = $amortiza[0];
        $capital = $amortiza[1];
        $saldos = $amortiza[2];
        $fechascuotas = $fchspgs[1];
        $j = 0;
        foreach ($intereses as $int) {
            $nrocuo++;
            $saldo = $saldo - $capital[$j];
            //AJUSTE INICIO
            if ($j == array_key_last($intereses) && $saldo != 0) {
                $capital[$j] = $capital[$j] + $saldo;
                $saldo = 0;
            }
            //AJUSTE FIN

            $datos = array(
                'ccodcta' => $codcuenta,
                'dfecven' => $fechascuotas[$j],
                'dfecpag' => "0000-00-00",
                'cestado' => "X",
                'ctipope' => "0",
                'cnrocuo' => $nrocuo,
                'SaldoCapital' => $saldo,
                'nmorpag' => 0,
                'ncappag' => $capital[$j],
                'nintpag' => $int,
                'AhoPrgPag' => 0,
                'OtrosPagosPag' => 0,
                'ccodusu' => 4,
                'dfecmod' => "0000-00-00",
                'cflag' => "0",
                'codigo' => "Y",
                'creditosaf' => "1",
                'saldo' => 0,
                'nintmor' => 0,
                'ncapita' => $capital[$j],
                'nintere' => $int,
                'NAhoProgra' => 0,
                'OtrosPagos' => 0,
                'OtrosPagos' => 0
            );
            $database->insert('Cre_ppg', $datos);
            $j++;
        }
        $database->executeQuery('CALL update_ppg_account(?);', [$codcuenta]);
        $database->commit();
        $mensaje = "Registro grabado correctamente";
        $status = 1;
    } catch (Exception $e) {
        $database->rollback();
        $codigoError = logerrores($e->getMessage(), __FILE__, __LINE__, $e->getFile(), $e->getLine());
        $mensaje = "Error: Intente nuevamente, o reporte este codigo de error($codigoError)";
        $status = 0;
    } finally {
        $database->closeConnection();
    }
}

//FUNCION PARA INSERTAR EN LA  CRED PPG 
function creppg_INST($ccodcta, $conexion, $capDes = 0)
{
    //CONSULTAR LOS DATOS NECESARIOS PARA EL DATACRE
    $consulta2 = mysqli_query($conexion, "SELECT cm.CCODCTA, cli.short_name, cm.CodCli, productos.descripcion, cm.MonSug, cm.DfecDsbls, cm.NIntApro
    ,cm.CtipCre,cm.NtipPerC,cm.DfecPago,cm.noPeriodo,productos.id,productos.dias_calculo FROM cremcre_meta cm  
    INNER JOIN tb_cliente cli on cli.idcod_cliente=cm.CodCli
    INNER JOIN cre_productos productos on 
    productos.id= cm.CCODPRD  WHERE cm.CCODCTA = '$ccodcta'");
    /*         $consulta2 = mysqli_query($conexion, "SELECT CCODCTA,short_name,CodCli,descriprod,MonSug,DfecDsbls,NIntApro
        ,CtipCre,NtipPerC,DfecPago,noPeriodo,P_ahoCr,ccodprdct FROM cremcre INNER JOIN  productos on 
        productos.ccodprdct= cremcre.CCODPRD  WHERE CCODCTA = '$ccodcta'"); */
    if (!$consulta2) {
        return false;
        exit();
    }

    $re = mysqli_fetch_array($consulta2, MYSQLI_NUM);
    $capital = (($capDes > 0) ? $capDes : $re[4]);
    $dtcre = array($re[0], $re[1], $re[2], $re[3], $capital, $re[5], $re[6], $re[7], $re[8], $re[9], $re[10], $re[11]);

    //SE DEBE DE AGREGAR EL TOTAL DE LOS GASTOS, DESDE CREDGAS (cambiar el 420 a total de garantias)
    // Realizar la validacion 
    // $dtcre =  CCODCTA0, CodCli1, CtipCre7, NIntApro6, NtipPerC8, DfecPago9, noPeriodo10, MonSug4, P_ahoCr11  (ANTES)
    //   CCODCTA0, short_name1, CodCli2, descriprod3, MonSug4, DfecDsbls5, NIntApro6, CtipCre7, NtipPerC8, DfecPago9, noPeriodo10,
    //   P_ahoCr11, CCODPRD12  (DESPUES)
    // include '../../../includes/BD_con/db_con.php';
    $i = 0;
    $future_value = 0;  // si se desea cambiar el valor de del desembolso a un pago parcial se cambia a 0 
    $beginning = false;
    $fechanow = date("Y-m-d H:i:s");
    $hoy = date("Y-m-d");
    // $pagAho = $dtcre[11] / $dtcre[10]; //$dtcre[8]/$dtcre[6] (ANTES)
    $pagAho = 0; //$dtcre[8]/$dtcre[6] (ANTES)

    $interes_calc = new profit_cmpst();
    //OBTENGO LOS TIPOS PERIODO Y LA FRECUENCIA  ($mes, $frecuencia, $periodo)
    $tipcre = $interes_calc->ntipPerc($dtcre[8]);
    $rate  = (($dtcre[6] / 100) / $tipcre[1]);

    $datalaboral = dias_habiles($conexion);
    if ($datalaboral == false) {
        return [false, 'Falló al recuperar dias laborales'];
    }
    $primeracuota = false; // PONER EN TRUE SI SE CALCULA LA PRIMERA CUOTA POR LA DIFERENCIA DE DIAS ENTRE FECDES Y FECPAG
    /* +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ 
    ++++++++++++++++++ SE CAMBIA LA CONSULTA SI ES DIARIO ++++++++++++++++++++++
    +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++  */
    if (($dtcre[8] == "1D" || $dtcre[8] == "7D") && $dtcre[7] == "Flat") {
        $amortiza = calculo_montos_diario($dtcre[4], $dtcre[6], $dtcre[10], $conexion);
        $fechas = calculo_fechas_por_nocuota($dtcre[9], $dtcre[10], 1, $conexion);
    } else {
        //VERSION ACTUAL, SE CALCULA SOBRE LAS FECHAS CALCULADAS EN EL PLAN DE PAGO,
        $fechas = $interes_calc->calcudate2($dtcre[9], $dtcre[10], $tipcre[2], $datalaboral);;
        $amortiza = amortiza($dtcre[7], $rate, $dtcre[4],  $dtcre[10], $future_value, $beginning, $fechas[1], $dtcre[5], $re[12], $primeracuota);

        //VERSION ANTERIOR, ESTATICO SOBRE 360 DIAS, 30 DIAS POR MES
        // $amortiza = amortiza($dtcre[7], $rate, $dtcre[4], $dtcre[10], $future_value, $beginning);
        // $fechas = $interes_calc->calcudate($dtcre[9], $dtcre[10], $tipcre[2]); //$diasntrfchs, $fchspgs, $fchsreal
    }
    //me devulve las fechas de pago, ($diasntrfchs, $fchspgs, $fchsreal)
    //$fechas = $interes_calc->calcudate($dtcre[9], $dtcre[10], $tipcre[2]);  //$dtcre[5], $dtcre[6], $tipcre[2] (ANTES)
    //AHORA DEBEMOS OBTENER EL INTERES, CAPITAL Y SALDO CAPITAL
    // $amortiza = amortiza($dtcre[7], $rate, $dtcre[4], $dtcre[10], $future_value, $beginning);
    // TODOS LOS DATOS DEL AMORTIZAR (INTERES, CAPITAL Y SALDO CAPITAL )
    $amortiza0 = $amortiza[0];
    $amortiza1 = $amortiza[1];
    $amortiza2 = $amortiza[2];
    $fechas2 = $fechas[1];
    // LLAMA EL TOTAL DE LAS CUOTAS, TAMBIEN APROVECHAR Y AGREGAR UN CAMPO A CREMCRE_META PARA INSERTAR 
    //$calcu = gstAdmin($dtcre[0], $dtcre[12], "calc", $conexion); // $ccodcta,$CODPRDT, $tipo  --  $Dscnt, $cuota, $name 
    $calcu = gastoscuota($dtcre[11], $dtcre[0], $conexion);
    $porCuota = $calcu;
    //$ttlCuota = array_sum($calcu[1]); // Suma de todas cuotas
    //$porCuota = $ttlCuota / $dtcre[10];
    //$porCuota = number_format($porCuota, 2);
    // echo implode(" - ",$dtcre) ;

    // CON LOS DATOS DEBEMOS DE INGRESAR TODA LA INFO A LA BASE DE DATOS , SON MULTIPLES INSERT UNO POR CADA CUOTA (12, 24, 36 DEPENDE DEL PRODUCTO CRED)
    // agregar un array y un foreach para ingresar todos los datos 
    $fechaven = $re[9];
    $montocobro = 0;
    $i = 0;
    foreach ($amortiza0 as $row) {
        //AJUSTE INICIO
        if ($i == array_key_last($amortiza0) && $amortiza2[$i] != 0) {
            $amortiza1[$i] = $amortiza1[$i] + $amortiza2[$i];
            $amortiza2[$i] = 0;
        }
        //AJUSTE FIN
        //*************** */
        if ($porCuota != null) {
            //----------
            $l = 0;
            $montocobro = 0;
            while ($l < count($porCuota)) {
                $tipo = $porCuota[$l]['tipo_deMonto'];
                $cant = $porCuota[$l]['monto'];
                $calculax = $porCuota[$l]['calculox'];
                $monsugc = $porCuota[$l]['MonSug'];
                if ($tipo == 1) {
                    // $mongas = $cant;
                    $mongas = ($calculax == 1) ? $cant : (($calculax == 2) ? ($cant / count($amortiza0)) : $cant);
                }
                if ($tipo == 2) {
                    // $mongas = ($calculax == 1) ? ($cant / 100 * $amortiza1[$i]) : (($calculax == 2) ? ($cant / 100 * $row) : (($calculax == 3) ? ($cant / 100 * ($amortiza1[$i] + $row)) : 0));
                    $mongas = ($calculax == 1) ? ($cant / 100 * $amortiza1[$i]) : (($calculax == 2) ? ($cant / 100 * $row) : (($calculax == 3) ? ($cant / 100 * ($amortiza1[$i] + $row)) : (($calculax == 4) ? ($monsugc * $cant / 100 / 12) : 0)));
                }
                $montocobro = $montocobro + round($mongas, 2);
                $l++;
            }
            //----------
        }
        /* -------------- */
        //$cap =$amortiza1[$i]+$row;
        $consulta2 = mysqli_query(
            $conexion,
            'INSERT INTO `Cre_ppg` (`ccodcta`,`dfecven`,`cestado`,`ctipope`,`cnrocuo`,`SaldoCapital`,`nintere`,`ncapita`,`NAhoProgra`,`OtrosPagos`, `nintpag`,`ncappag`,`AhoPrgPag`,`OtrosPagosPag`,`dfecmod`)
        VALUES ( "' . $dtcre[0] . '","' . $fechas2[$i] . '","X","0",' . ($i + 1) . ',' . $amortiza2[$i] . ',' . $row . ',' . $amortiza1[$i] . ',' . $pagAho . ',' . $montocobro . ',' . $row . ',' . $amortiza1[$i] . ',' . $pagAho . ',' . $montocobro . ',"' . $hoy . '");'
        );

        $aux = mysqli_error($conexion);
        if ($aux) {
            return [false, $aux];
        }

        $fechaven = $fechas2[$i];
        if (!$consulta2) {
            // echo '<div class="alert alert-danger" role="alert"> "ERROR" </div>';
            return [false, 'test'];
            exit();
        } else {
            $i++;
        }
    }
    //  SE DEBE DE ACTIVAR A ESTADO F  
    $consulta3 = mysqli_query($conexion, "UPDATE `cremcre_meta` SET `Cestado` = 'F', DsmblsAproba='" . $fechanow . "', DFecVen='" . $fechaven . "' WHERE `cremcre_meta`.`CCODCTA` = '$dtcre[0]'; ");
    if (!$consulta3) {
        return [false, 'despues de true'];
        exit();
    } else {
        // echo '<div class="alert alert-success" role="alert"> "Datos Ingresados" </div>' ;
        return [true, 'test'];
    }
}

//FUNCION PARA OBTENER LOS DATOS DE PRIMER PAGO DE LA CREEPPG
function creppg_temporal($ccodcta, $conexion)
{
    //CONSULTAR LOS DATOS NECESARIOS PARA EL DATACRE
    $consulta2 = mysqli_query($conexion, "SELECT cm.CCODCTA,cli.short_name,cm.CodCli,productos.descripcion,cm.MonSug,cm.DfecDsbls,cm.NIntApro
    ,cm.CtipCre,cm.NtipPerC,cm.DfecPago,cm.noPeriodo,productos.id,productos.dias_calculo FROM cremcre_meta cm  
    INNER JOIN tb_cliente cli on cli.idcod_cliente=cm.CodCli
    INNER JOIN cre_productos productos on 
    productos.id= cm.CCODPRD  WHERE cm.CCODCTA = '$ccodcta'");
    /*         $consulta2 = mysqli_query($conexion, "SELECT CCODCTA,short_name,CodCli,descriprod,MonSug,DfecDsbls,NIntApro
        ,CtipCre,NtipPerC,DfecPago,noPeriodo,P_ahoCr,ccodprdct FROM cremcre INNER JOIN  productos on 
        productos.ccodprdct= cremcre.CCODPRD  WHERE CCODCTA = '$ccodcta'"); */
    if (!$consulta2) {
        return false;
        exit();
    }

    $re = mysqli_fetch_array($consulta2, MYSQLI_NUM);
    $dtcre = array($re[0], $re[1], $re[2], $re[3], $re[4], $re[5], $re[6], $re[7], $re[8], $re[9], $re[10], $re[11]);
    //SE DEBE DE AGREGAR EL TOTAL DE LOS GASTOS, DESDE CREDGAS (cambiar el 420 a total de garantias)
    // Realizar la validacion 
    // $dtcre =  CCODCTA0, CodCli1, CtipCre7, NIntApro6, NtipPerC8, DfecPago9, noPeriodo10, MonSug4, P_ahoCr11  (ANTES)
    //   CCODCTA0, short_name1, CodCli2, descriprod3, MonSug4, DfecDsbls5, NIntApro6, CtipCre7, NtipPerC8, DfecPago9, noPeriodo10,
    //   P_ahoCr11, CCODPRD12  (DESPUES)
    // include '../../../includes/BD_con/db_con.php';
    $i = 0;
    $future_value = 0;  // si se desea cambiar el valor de del desembolso a un pago parcial se cambia a 0 
    $beginning = false;
    $fechanow = date("Y-m-d H:i:s");
    $hoy = date("Y-m-d");
    //$pagAho = $dtcre[11] / $dtcre[10]; //$dtcre[8]/$dtcre[6] (ANTES)
    $pagAho = 0; //$dtcre[8]/$dtcre[6] (ANTES)

    $interes_calc = new profit_cmpst();
    //OBTENGO LOS TIPOS PERIODO Y LA FRECUENCIA  ($mes, $frecuencia, $periodo)
    $tipcre = $interes_calc->ntipPerc($dtcre[8]);
    $rate  = (($dtcre[6] / 100) / $tipcre[1]);

    $datalaboral = dias_habiles($conexion);
    if ($datalaboral == false) {
        echo json_encode(['status' => 0, 'mensaje' => 'Falló al recuperar dias laborales']);
        return;
    }

    $primeracuota = false; // PONER EN TRUE SI SE CALCULA LA PRIMERA CUOTA POR LA DIFERENCIA DE DIAS ENTRE FECDES Y FECPAG
    /* +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ 
    ++++++++++++++++++ SE CAMBIA LA CONSULTA SI ES DIARIO ++++++++++++++++++++++
    +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++  */
    if (($dtcre[8] == "1D" || $dtcre[8] == "7D") && $dtcre[7] == "Flat") {
        $amortiza = calculo_montos_diario($dtcre[4], $dtcre[6], $dtcre[10], $conexion);
        $fechas = calculo_fechas_por_nocuota($dtcre[9], $dtcre[10], 1, $conexion);
    } else {
        $fechas = $interes_calc->calcudate2($dtcre[9], $dtcre[10], $tipcre[2], $datalaboral);
        $amortiza = amortiza($dtcre[7], $rate, $dtcre[4], $dtcre[10], $future_value, $beginning, $fechas[1], $dtcre[5], $re[12], $primeracuota);

        // $amortiza = amortiza($dtcre[7], $rate, $dtcre[4], $dtcre[10], $future_value, $beginning);
        // $fechas = $interes_calc->calcudate($dtcre[9], $dtcre[10], $tipcre[2]); //$diasntrfchs, $fchspgs, $fchsreal
    }

    // //me devulve las fechas de pago, ($diasntrfchs, $fchspgs, $fchsreal)
    // $fechas = $interes_calc->calcudate($dtcre[9], $dtcre[10], $tipcre[2]);  //$dtcre[5], $dtcre[6], $tipcre[2] (ANTES)
    // //AHORA DEBEMOS OBTENER EL INTERES, CAPITAL Y SALDO CAPITAL
    // $amortiza = amortiza($dtcre[7], $rate, $dtcre[4], $dtcre[10], $future_value, $beginning);
    // TODOS LOS DATOS DEL AMORTIZAR (INTERES, CAPITAL Y SALDO CAPITAL )
    $amortiza0 = $amortiza[0];
    $amortiza1 = $amortiza[1];
    $amortiza2 = $amortiza[2];
    $fechas2 = $fechas[1];
    // LLAMA EL TOTAL DE LAS CUOTAS, TAMBIEN APROVECHAR Y AGREGAR UN CAMPO A CREMCRE_META PARA INSERTAR 
    //$calcu = gstAdmin($dtcre[0], $dtcre[12], "calc", $conexion); // $ccodcta,$CODPRDT, $tipo  --  $Dscnt, $cuota, $name 
    $calcu = gastoscuota($dtcre[11], $dtcre[0], $conexion);
    $porCuota = $calcu;
    //$ttlCuota = array_sum($calcu[1]); // Suma de todas cuotas
    //$porCuota = $ttlCuota / $dtcre[10];
    //$porCuota = number_format($porCuota, 2);
    // echo implode(" - ",$dtcre) ;

    // CON LOS DATOS DEBEMOS DE INGRESAR TODA LA INFO A LA BASE DE DATOS , SON MULTIPLES INSERT UNO POR CADA CUOTA (12, 24, 36 DEPENDE DEL PRODUCTO CRED)
    // agregar un array y un foreach para ingresar todos los datos 
    $fechaven = $re[9];
    $i = 0;
    $auxiliar[] = [];
    $montocobro = 0;
    while ($i < count($amortiza0)) {
        //AJUSTE INICIO
        if ($i == array_key_last($amortiza0) && $amortiza2[$i] != 0) {
            $amortiza1[$i] = $amortiza1[$i] + $amortiza2[$i];
            $amortiza2[$i] = 0;
        }
        //AJUSTE FIN
        //*************** */
        if ($porCuota != null) {
            //----------
            $l = 0;
            $montocobro = 0;
            while ($l < count($porCuota)) {
                $tipo = $porCuota[$l]['tipo_deMonto'];
                $cant = $porCuota[$l]['monto'];
                $calculax = $porCuota[$l]['calculox'];
                $monsugc = $porCuota[$l]['MonSug'];
                if ($tipo == 1) {
                    // $mongas = $cant;
                    $mongas = ($calculax == 1) ? $cant : (($calculax == 2) ? ($cant / count($amortiza0)) : $cant);
                }
                if ($tipo == 2) {
                    // $mongas = ($calculax == 1) ? ($cant / 100 * $amortiza1[$i]) : (($calculax == 2) ? ($cant / 100 * $amortiza0[$i]) : (($calculax == 3) ? ($cant / 100 * ($amortiza1[$i] + $amortiza0[$i])) : 0));
                    $mongas = ($calculax == 1) ? ($cant / 100 * $amortiza1[$i]) : (($calculax == 2) ? ($cant / 100 * $amortiza0[$i]) : (($calculax == 3) ? ($cant / 100 * ($amortiza1[$i] + $amortiza0[$i])) : (($calculax == 4) ? ($monsugc * $cant / 100 / 12) : 0)));
                }
                $montocobro = $montocobro + round($mongas, 2);
                $l++;
            }
            //----------
        }
        /* -------------- */
        $auxiliar[$i]['ccodcta'] = $dtcre[0];
        $auxiliar[$i]['dfecven'] = $fechas2[$i];
        $auxiliar[$i]['cestado'] = 'X';
        $auxiliar[$i]['ctipope'] = '0';
        $auxiliar[$i]['cnrocuo'] = ($i + 1);
        $auxiliar[$i]['SaldoCapital'] = $amortiza2[$i];
        $auxiliar[$i]['nintere'] = $amortiza0[$i];
        $auxiliar[$i]['ncapita'] = $amortiza1[$i];
        $auxiliar[$i]['NAhoProgra'] = $pagAho;
        $auxiliar[$i]['OtrosPagos'] = $montocobro;
        $auxiliar[$i]['nintpag'] = $amortiza0[$i];
        $auxiliar[$i]['ncappag'] = $amortiza1[$i];
        $auxiliar[$i]['AhoPrgPag'] = $pagAho;
        $auxiliar[$i]['OtrosPagosPag'] = $montocobro;
        $auxiliar[$i]['dfecmod'] = $hoy;
        $auxiliar[$i]['cuota'] = $amortiza1[$i] + $amortiza0[$i];
        $i++;
    }
    return $auxiliar;
}
function creppg_get($ccodcta, $conexion)
{
    $consulta2 = mysqli_query($conexion, "SELECT * FROM Cre_ppg WHERE ccodcta = '$ccodcta'");
    if (!$consulta2) {
        return false;
        exit();
    }
    $data[] = [];
    $j = 0;
    while ($fila = mysqli_fetch_array($consulta2, MYSQLI_ASSOC)) {
        $data[$j] = $fila;
        $data[$j]['cuota'] = $data[$j]['ncapita'] + $data[$j]['nintere'];
        $j++;
    }
    return $data;
}
function calculo_montos_diario($monto, $interes, $no_cuotas, $conexion)
{
    $datos = dias_habiles($conexion);
    if ($datos == false) {
        return false;
    }
    $pagoInteres = [];
    $pagoCapital = [];
    $saldocapital = [];
    $capital   = abs($monto);
    $ganancia = round(($capital * $interes / 100), 2);
    $capcuo   = round($capital / $no_cuotas, 2);
    // $intcuo   = round($ganancia / (array_sum(array_column($datos,'laboral'))*4), 2);
    $intcuo   = round($ganancia / $no_cuotas, 2);
    $saldoint = $ganancia;
    $cuota     = $intcuo + $capcuo;
    for ($i = 1; $i <= $no_cuotas; $i++) {
        $capital = $capital - $capcuo;
        $saldoint = $saldoint - $intcuo;
        if ($i == $no_cuotas && $saldoint != 0) {
            $intcuo = $intcuo + $saldoint;
        }
        array_push($saldocapital, round($capital, 2));
        array_push($pagoCapital, round($capcuo, 2));
        array_push($pagoInteres, $intcuo);
    }
    return array($pagoInteres, $pagoCapital, $saldocapital);
}
function calculo_fechas_diario($fechainicio, $fechafin, $frecuencia, $conexion)
{
    $datos = dias_habiles($conexion);
    if ($datos == false) {
        return false;
    }

    $dateDifference = abs(strtotime($fechainicio) - strtotime($fechafin));
    $dias_diferencia = $dateDifference / (60 * 60 * 24);
    $dias_diferencia = abs($dias_diferencia); //valor absoluto y quitar posible negativo
    $dias_diferencia = floor($dias_diferencia); //quito los decimales a los días de diferencia
    $registros = array();

    $i = 0;
    $j = 0;
    $nuevafecha = $fechainicio;
    while ($i < $dias_diferencia) {
        $dia = dia($nuevafecha);
        $indice = array_search($dia, array_column($datos, 'id'));
        if ($datos[$indice]['laboral'] == 1) {
            $registros[$j] = $nuevafecha;
            $j++;
        }
        $nuevafecha = strtotime('+ ' . $frecuencia . ' day', strtotime($nuevafecha));
        $nuevafecha = date('Y-m-j', $nuevafecha);
        $i++;
    }
    return $registros;
}
function calculo_fechas_por_nocuota($fechainicio, $nocuotas, $frecuencia, $conexion)
{
    $datos = dias_habiles($conexion);
    if ($datos == false) {
        return false;
    }

    $registros = array();
    $i = 0;
    $j = 0;
    $nuevafecha = $fechainicio;

    while ($j < $nocuotas) {
        $dia = dia($nuevafecha);
        $indice = array_search($dia, array_column($datos, 'id'));
        if ($datos[$indice]['laboral'] == 1) {
            $registros[$j] = $nuevafecha;
            $j++;
        }
        $nuevafecha = strtotime('+ ' . $frecuencia . ' day', strtotime($nuevafecha));
        $nuevafecha = date('Y-m-j', $nuevafecha);
        $i++;
    }
    return [$registros, $registros];
}
function dia($fecha)
{
    $numdia = date('N', strtotime($fecha));
    return $numdia;
}
function dias_habiles($conexion)
{
    $datos[] = [];
    $consulta = mysqli_query($conexion, "SELECT * FROM tb_dias_laborales ORDER BY id");
    $i = 0;
    while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
        $datos[$i] = $fila;
        $i++;
    }
    return ($i == 0) ? false : $datos;
}
