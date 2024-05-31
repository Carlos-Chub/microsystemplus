<?php
// FUNCION QUE MUSTRA LOS TPOS DE CREDITOS 
function tpscre()
{
    //SELECT abre, Credito FROM `tb_credito` 
    include '../../../includes/BD_con/db_con.php';
    $tpscre = mysqli_query($general, "SELECT abre, Credito FROM `tb_credito`");
    while ($sec = mysqli_fetch_array($tpscre, MYSQLI_NUM)) {
        echo '<option value="' . $sec[0] . '">' . $sec[1] . '</option>';
    }
}

//  FUNCION PARA TRADUCIR QUE TIPOS DE PREDUCTOS Y FORMAS DE PAGO 
function tip_cre_peri($data)
{
    switch ($data) {
            // Pago mensual
        case '1M':
            return 'Mensual';
            break;
            // Pago Bimensual
        case '2M':
            return 'Bimensual';
            break;
            // Pago Trimestral
        case '3M':
            return 'Trimestral';
            break;
            // PAGO Semestral
        case '6M':
            return 'Semestral';
            break;
            // Pago DIARIO, 
        case '1D':
            return 'Diario';
            break;
            // --------------------  semanal 
        case '7D':
            return 'Semanal';
            break;
            // --------------------
        case '15D':
            return 'Quincenal';
            break;
            // y quincenal
        case '14D':
            return 'Catorcenal';
            break;
        case '1C':
            return 'CAPITAL VENCIMIENTO';
            break;
            // POR DEFECTO SE DARA PAGO MENSUAL
        default:
            include '../../../includes/BD_con/db_con.php';
            $tipcre = mysqli_query($general, "SELECT descr FROM `tb_credito` where abre = '$data'");
            $dtcre2 = mysqli_fetch_array($tipcre);
            return $dtcre2["descr"];
            break;
    }
}

//----------------------------
function amortizaadg($CtipCre, $rate, $MonSug, $periods, $future_value, $beginning, $saldo, $diascalculo,$fechadesembolso,$fechaprimeracuota)
{
    $pagoInteres = [];
    $pagoCapital = [];
    $saldocapital = [];

    switch ($CtipCre) {
            // -------------------> TABLA DE AMORTIZACION FRANCESA
        case 'Franc':
            $CAPITAL = $MonSug;
            $MonSug = $MonSug * -1;
            $when = $beginning ? 1 : 0;

            //SE CALCULA LA CUOTA ORIGINAL
            $cuota =  ($future_value + ($CAPITAL * \pow(1 + $rate, $periods)))
                /
                ((1 + $rate * $when) / $rate * (\pow(1 + $rate, $periods) - 1));

            $primeracuota = false;
            $fecaux=$fechaprimeracuota;
            $fecant=$fechadesembolso;// calcula el interes de la primera cuota sobre la diferencia de dias entre el ultimo pago y la fecha de la siguiente cuota
            // $fecant=agregarMes($fecaux,-1);  //calcula el interes de la primera cuota sobre la diferencia de dias entre la fecha de pago establecido(fecpag) del mes que se realizo el pago y la fecha de la siguiente cuota

            $i = 1;
            while ($saldo > 0) {
                 $dias = ($diascalculo == 360) ? 30 :dias_dif($fecant, $fecaux); 
                if ($primeracuota && $i == 1) {
                    $dias = ($diascalculo == 360) ? diferenciaEnDias($fechadesembolso,$fechaprimeracuota) : dias_dif($fechadesembolso, $fechaprimeracuota);
                }
                //CUOTA INTERES
                $ipmt = abs($saldo) * ($rate * 12) / $diascalculo * $dias;
                array_push($pagoInteres, round($ipmt, 2));
                //PAGO CAPITAL
                $ppmt = $cuota - $ipmt;
                array_push($pagoCapital, round($ppmt, 2));
                $saldo  = $saldo - $ppmt;
                array_push($saldocapital, round($saldo, 2));
                $i++;
                $fecant=$fecaux;
                $fecaux=agregarMes($fecaux,1);  
            }
            return array($pagoInteres, $pagoCapital, $saldocapital);
            break;
        default:
            return array($pagoInteres, $pagoCapital, $saldocapital);
            break;
    }
}

//PRUEBA PARA OBTENER DATOS DEPENDIENDO DEL TIPO DE CREDITO,  TABIEN ESTA EN FUN_PPG.PHP  SE COLO AHI
function amortiza($CtipCre, $rate, $MonSug, $periods, $future_value, $beginning, $fechas, $fechadesembolso, $diascalculo, $primeracuota = false)
{
    $pagoInteres = [];
    $pagoCapital = [];
    $saldocapital = [];

    switch ($CtipCre) {
        case 'Amer':
            $CAPITAL = abs($MonSug);
            $ipmt = round(($rate * $CAPITAL), 2);
            for ($i = 1; $i <= ($periods - 1); $i++) {
                //interes
                array_push($pagoInteres, $ipmt);
                array_push($pagoCapital, 0);
                array_push($saldocapital, $CAPITAL);
            }
            array_push($pagoInteres, $ipmt);
            array_push($pagoCapital, $CAPITAL);
            array_push($saldocapital, 0);
            return array($pagoInteres, $pagoCapital, $saldocapital);
            //  HAY QUE VER QUE AL LLGAR AL FINAL SE DEBE DE 
            break;
            // -------------------> TABLA DE AMORTIZACION FRANCESA
        case 'Franc':
            //VERSION ACTUAL
            $CAPITAL = $MonSug;
            $MonSug = $MonSug * -1;
            $when = $beginning ? 1 : 0;


            $rate = ($rate * 12) / 365;
            //SE CALCULA LA CUOTA ORIGINAL
            $cuota = $CAPITAL * ($rate * pow(1 + $rate, $periods)) / (pow(1 + $rate, $periods) - 1);

            $i = 1;
            $rate = $rate * 365;

            // $rate = 0.6;
            $saldo = $CAPITAL;
            // $fechanterior = $fechadesembolso;

            $cuotas = refixedcuotas($cuota, $CAPITAL, $fechas, $fechadesembolso, $rate, $diascalculo, $CAPITAL, $primeracuota);

            return $cuotas;
            // return array($pagoInteres, $pagoCapital, $saldocapital);
            break;
        case 'Francas':
            //VERSION ACTUAL
            $CAPITAL = $MonSug;
            $MonSug = $MonSug * -1;
            $when = $beginning ? 1 : 0;


            $rate = ($rate * 12) / 365;

            // $cuota =  ($future_value + ($CAPITAL * \pow(1 + $rate, $periods)))
            //     /
            //     ((1 + $rate * $when) / $rate * (\pow(1 + $rate, $periods) - 1));
            //SE CALCULA LA CUOTA ORIGINAL
            $cuota = $CAPITAL * ($rate * pow(1 + $rate, $periods)) / (pow(1 + $rate, $periods) - 1);


            $i = 1;
            $rate = $rate * 365;
            // $rate = 0.6;
            $saldo = $CAPITAL;
            $fechanterior = $fechadesembolso;
            foreach ($fechas as $fecha) {
                $dias = ($diascalculo == 360) ? 30 : dias_dif($fechanterior, $fecha);

                //CUOTA INTERES
                $ipmt = abs($saldo) * ($rate) / $diascalculo * $dias;
                array_push($pagoInteres, round($ipmt, 2));
                // array_push($pagoInteres, round($cuota, 2));

                //CUOTA CAPITAL
                $ppmt = $cuota - $ipmt;
                array_push($pagoCapital, round($ppmt, 2));

                //SALDO
                $saldo  = $saldo - $ppmt;
                array_push($saldocapital, round($saldo, 2));

                $fechanterior = $fecha;
            }
            $cuotas = array($pagoInteres, $pagoCapital, $saldocapital);
            $diferencia = (int)($CAPITAL) - (int)(array_sum($pagoCapital));
            if ($diferencia != 0) {
                $cuotas = refixedcuotas($cuota, $diferencia, $fechas, $fechadesembolso, $rate, $diascalculo, $CAPITAL, $primeracuota);
            }

            return $cuotas;
            // return array($pagoInteres, $pagoCapital, $saldocapital);
            break;
            // -------------------> TABLA DE AMORTIZACION NIVELADA
        case 'Flat':
            $CAPITAL   = abs($MonSug);
            $intstotal = round(($CAPITAL * $rate), 2); // interes flat 
            $capflat   = round($CAPITAL / $periods, 2); //capital flat 
            $cuota     = $intstotal + $capflat;
            for ($i = 1; $i <= $periods; $i++) {
                $CAPITAL   = $CAPITAL - $capflat;
                array_push($saldocapital, round($CAPITAL, 2));
                array_push($pagoCapital, round($capflat, 2));
                array_push($pagoInteres, round($intstotal, 2));
            }
            return array($pagoInteres, $pagoCapital, $saldocapital);
            break;
            // -------------------> TABLA DE AMORTIZACION ALEMANA
        case 'Germa':
            $Cap_amrt = round(($MonSug * -1) / $periods, 2);
            for ($i = 1; $i <= $periods; $i++) {
                //interes
                $ipmt = round(($rate * $MonSug), 2);
                array_push($pagoInteres, $ipmt);
                //Saldo Capital
                $MonSug = $Cap_amrt + $MonSug;
                array_push($saldocapital, $MonSug);
                //CUOTA A PAGAR 
                //$ppmt = abs($Cap_amrt) + $ipmt;
                // Cambie $ppmt por $Cap_amrt en el array_push
                array_push($pagoCapital, abs($Cap_amrt));
            }
            return array($pagoInteres, $pagoCapital, $saldocapital);
            break;
            // ------------------->
        default:
            return array($pagoInteres, $pagoCapital, $saldocapital);
            break;
    }
}
function refixedcuotas($cuota, $diferencia, $fechas, $fechadesembolso, $rate, $diascalculo, $CAPITAL, $primeracuota)
{
    $pagoInteres = [];
    $pagoCapital = [];
    $saldocapital = [];

    $cuota2 = $cuota + $diferencia / count($fechas);
    $cuota = ($cuota + $cuota2) / 2;

    $saldo = $CAPITAL;
    $fechanterior = $fechadesembolso;
    $i = 0;
    foreach ($fechas as $fecha) {
        $dias = ($diascalculo == 360) ? 30 : dias_dif($fechanterior, $fecha);
        if ($primeracuota && $i == 0) {
            $dias = ($diascalculo == 360) ? diferenciaEnDias($fechadesembolso, $fecha) : dias_dif($fechadesembolso, $fecha);
        }

        //CUOTA INTERES
        $ipmt = abs($saldo) * ($rate) / $diascalculo * $dias;
        $ipmt = round($ipmt, 2);
        array_push($pagoInteres, $ipmt);

        //CUOTA CAPITAL
        $ppmt = $cuota - $ipmt;
        $ppmt = round($ppmt, 2);
        array_push($pagoCapital, round($ppmt, 2));

        //SALDO
        $saldo  = $saldo - $ppmt;
        $saldo = round($saldo, 2);
        array_push($saldocapital, round($saldo, 2));

        $fechanterior = $fecha;
        $i++;
    }
    $cuotas = array($pagoInteres, $pagoCapital, $saldocapital);
    $diferencia = (int)($CAPITAL) - (int)(array_sum($pagoCapital));
    if ($diferencia != 0) {
        $cuotas = refixedcuotas($cuota, $diferencia, $fechas, $fechadesembolso, $rate, $diascalculo, $CAPITAL, $primeracuota);
    }
    return $cuotas;
}
function amortizaanterior($CtipCre, $rate, $MonSug, $periods, $future_value, $beginning)
{
    $pagoInteres = [];
    $pagoCapital = [];
    $saldocapital = [];

    switch ($CtipCre) {
        case 'Amer':
            $CAPITAL = abs($MonSug);
            $ipmt = round(($rate * $CAPITAL), 2);
            for ($i = 1; $i <= ($periods - 1); $i++) {
                //interes
                array_push($pagoInteres, $ipmt);
                array_push($pagoCapital, 0);
                array_push($saldocapital, $CAPITAL);
            }
            array_push($pagoInteres, $ipmt);
            array_push($pagoCapital, $CAPITAL);
            array_push($saldocapital, 0);
            return array($pagoInteres, $pagoCapital, $saldocapital);
            //  HAY QUE VER QUE AL LLGAR AL FINAL SE DEBE DE 
            break;
            // -------------------> TABLA DE AMORTIZACION FRANCESA
        case 'Franc':
            $interes_calc2 = new Finanza();
            $CAPITAL = $MonSug;
            $MonSug = $MonSug * -1;
            for ($i = 1; $i <= $periods; $i++) {
                //interes
                $ipmt = $interes_calc2->ipmt($rate, $i, $periods, $MonSug, $future_value, $beginning);
                array_push($pagoInteres, round($ipmt, 2));
                //PAGO CAPITAL
                $ppmt = $interes_calc2->ppmt($rate, $i, $periods, $MonSug, $future_value, $beginning);
                array_push($pagoCapital, round($ppmt, 2));
                $CAPITAL  = $CAPITAL - $ppmt;
                array_push($saldocapital, round($CAPITAL, 2));
            }
            return array($pagoInteres, $pagoCapital, $saldocapital);
            break;
            // -------------------> TABLA DE AMORTIZACION NIVELADA
        case 'Flat':
            $CAPITAL   = abs($MonSug);
            $intstotal = round(($CAPITAL * $rate), 2); // interes flat 
            $capflat   = round($CAPITAL / $periods, 2); //capital flat 
            $cuota     = $intstotal + $capflat;
            for ($i = 1; $i <= $periods; $i++) {
                $CAPITAL   = $CAPITAL - $capflat;
                array_push($saldocapital, round($CAPITAL, 2));
                array_push($pagoCapital, round($capflat, 2));
                array_push($pagoInteres, round($intstotal, 2));
            }
            return array($pagoInteres, $pagoCapital, $saldocapital);
            break;
            // -------------------> TABLA DE AMORTIZACION ALEMANA
        case 'Germa':
            $Cap_amrt = round(($MonSug * -1) / $periods, 2);
            for ($i = 1; $i <= $periods; $i++) {
                //interes
                $ipmt = round(($rate * $MonSug), 2);
                array_push($pagoInteres, $ipmt);
                //Saldo Capital
                $MonSug = $Cap_amrt + $MonSug;
                array_push($saldocapital, $MonSug);
                //CUOTA A PAGAR 
                //$ppmt = abs($Cap_amrt) + $ipmt;
                // Cambie $ppmt por $Cap_amrt en el array_push
                array_push($pagoCapital, abs($Cap_amrt));
            }
            return array($pagoInteres, $pagoCapital, $saldocapital);
            break;
            // ------------------->
        default:
            return array($pagoInteres, $pagoCapital, $saldocapital);
            break;
    }
}

// Funcion para obtener los destinos de creditos   --ANDRES
function DestinoCre($general)
{
    // include '../../../includes/BD_con/db_con.php';
    $dtacre = mysqli_query($general, 'SELECT id_DestinoCredito, DestinoCredito FROM `tb_destinocredito`');
    while ($re = mysqli_fetch_array($dtacre, MYSQLI_NUM)) {
        echo '<option value="' . $re[0] . '"> ' . $re[1] . ' </option>';
    }
}
