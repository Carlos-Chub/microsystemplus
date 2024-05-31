<?php
class profit_cmpst
{
  public function calcudate2($fechaini, $NoCuota, $periodo, $diaslaborales)
  {
    $fechaini2 = $fechaini;
    $fchspgs = [];
    $fchsreal = [];
    $daY=$fechaini;
    for ($i = 1; $i <= $NoCuota; $i++) {
      /* ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                            AGREGADO POR BENEQ*/
      $cantdias = 0;
      $numdia = date('N', strtotime($fechaini));
      $indice = array_search($numdia, array_column($diaslaborales, 'id'));
      if ($diaslaborales[$indice]['laboral'] == 0) {
        $diareemplazo = $diaslaborales[$indice]['id_dia_ajuste'];
        $j = $indice;
        $flag = false;
        $cont = 0;
        while (!$flag) {
          $j = ($j >= 6) ? 0 : $j + 1;
          if ($diaslaborales[$j]['id'] == $diareemplazo) {
            $flag = true;
          }
          $cont++;
        }
        $cantdias = ($cont <= 3) ? '+ ' . $cont : '- ' . ($numdia - ($cont - (7 - $numdia)));
        $daY = date('Y-m-d', strtotime($fechaini . ' ' . $cantdias . ' day'));
        $dia = date('D', strtotime($daY));
      }
      /*                    FIN AGREGADO POR BENEQ
        ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
      array_push($fchspgs, $daY);
      $daY = date('Y-m-d', strtotime($fechaini . $periodo));
      //$timestamp = strtotime($daY);
      $fechaini = $daY;
      $dia = date('D', strtotime($fechaini));
  
      //echo "".$i." -- ".$daY."  / ".$dia."<br>";  //FECHAS REALES 
      array_push($fchsreal, $daY);
    }

    $interes_calc = new profit_cmpst();
    $diasntrfchs = $interes_calc->calcuentreFCHAS($fchsreal, $fechaini2);
    return array($diasntrfchs, $fchspgs, $fchsreal);
  } //calcudate

  //CALCULAR LOS DIAS ENTRE FECHAS REALES. pero no desde la fecha de pago 
  public function calcuentreFCHAS($fchspgs, $fechaini)
  {
    $dtini = 0;
    $difinicial = [];
    $canti1 = count($fchspgs);

    //CALCULA  ENTRE FECHAS DE PAGO 
    for ($i = 0; $i < $canti1 - 1; $i++) {
      if ($i == 0) {
        $datediff = strtotime($fchspgs[$i]) - strtotime($fechaini);
        // echo $fchspgs[$i] ." - ".$fechaini . "<br>";
        $fchcv = round($datediff / (60 * 60 * 24));
        array_push($difinicial, $fchcv);
        // echo  " " .$i ." ---- ".$fchcv."<br>" ; 
        $datediff = strtotime($fchspgs[$i + 1]) - strtotime($fchspgs[$i]);
        //echo $fchspgs[$i+1]." - ".$fchspgs[$i] . "<br>";
        $fchcv = round($datediff / (60 * 60 * 24));
        array_push($difinicial, $fchcv);
        // echo  " " .$i ." ---- ".$fchcv."<br>" ; 
      } else {
        $datediff = strtotime($fchspgs[$i + 1]) - strtotime($fchspgs[$i]);
        //echo $fchspgs[$i+1]." - ".$fchspgs[$i] . "<br>";
        $fchcv = round($datediff / (60 * 60 * 24));
        //echo  " " .$i ." ---- ".$fchcv."<br>" ; 
        array_push($difinicial, $fchcv);
      }
      //IMPRIMER LA RESTA
      $dtini = $datediff;
    }
    //CALCULO ENTRE LA PRIMERA FECHA DE PAGO Y LOS DEMAS PAGOS 
    //print_r($difinicial); 
    return $difinicial;
  }

  // ESTA FUNCION ES PAR CAMBIAR EL TIPO DE PERIODO, SI PAGARA POR MESNUALIDAS, BI, TRI. 
  public function ntipPerc($periodo)
  {

    switch ($periodo) {
        // Pago mensual
      case '1M':
        $mes = 1;
        $frecuencia = 12 / $mes;
        $periodo = ' + 1 months';
        return array($mes, $frecuencia, $periodo);
        break;
        //// Pago Bimensual
      case '2M':
        $mes = 2;
        $frecuencia = 12 / $mes;
        $periodo = ' + 2 months';
        return array($mes, $frecuencia, $periodo);
        break;
        //// Pago Trimestral
      case '3M':
        $mes = 3;
        $frecuencia = 12 / $mes;
        $periodo = ' + 3 months';
        return array($mes, $frecuencia, $periodo);
        break;
        // PAGO Semestral
      case '6M':
        $mes = 6;
        $frecuencia = 12 / $mes;
        $periodo = ' + 6 months';
        return array($mes, $frecuencia, $periodo);
        break;
        // Pago DIARIO, falta semanal y quincenal
      case '1D':
        $mes = 1;
        $frecuencia = 12 * 30; // 
        $periodo = ' + 1 Day';
        return array($mes, $frecuencia, $periodo);
        break;
        //  PAGO semanal 
      case '7D':
        $mes = 1;
        $frecuencia = 12 * 4;
        $periodo = ' + 7 Day';
        return array($mes, $frecuencia, $periodo);
        break;
        // PAGO quincenal
      case '15D':
        $mes = 2;
        $frecuencia = 12 * 2;
        $periodo = ' + 15 Day';
        return array($mes, $frecuencia, $periodo);
        break;
        // PAGO Catorcenal
      case '14D':
        $mes = 1;
        $frecuencia = 12 * 2;;
        $periodo = ' + 14 Day';
        return array($mes, $frecuencia, $periodo);
        break;
        // POR DEFECTO SE DARA PAGO MENSUAL
      default:
        $mes = 1;
        $frecuencia = 12 / $mes;
        $periodo = ' + 1 months';
        return array($mes, $frecuencia, $periodo);
        break;
    }
  }
}
