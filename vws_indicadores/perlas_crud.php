<?php
//AQUI INICIARA EL MODULO DE PERLAS 
include '../includes/BD_con/db_con.php';
include '../src/funcphp/valida.php';
include '../src/funcphp/func_gen.php';
include './formulas.php';

$condi = $_POST["condi"];

switch ($condi) {
  //modulo de prottecion de perlas  
  case "proteccion":
  ?>
    <div class="card crdbody">
      <div class="card-header panelcolor">PROTECION.</div>
      <div class="card-body">

        <input id="condi" value="proteccion" readonly hidden>
        <div class="row crdbody">
          <div class="accordion" id="accordionExample">
            <div class="accordion-item">
              <!--p1-->
              <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#p1" aria-expanded="true" aria-controls="p1">
                  P1. PROVISION PARA PRESTAMOS INCOBRABLES/PROVISION REQUERIDA PARA PRESTAMO CON MOROSIDAD</button>
              </h2>
              <div id="p1" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                  <strong>INDICADDORES DE PROTECCION.</strong> DEBE DE INSERTAR LOS VALORES DE LA FORMULA A/B*C para realizar la formula <br>
                  <strong>a.</strong>Provision para Prestamos incobrables<br>
                  <strong>b.</strong>Porcenaje provisiones para cubrir los prestamos con morosidad mayor a 12 meses(recomendado 100%)<br>
                  <strong>c.</strong>saldos de prestamos morosidad mayor 12 meses<br>
                    <?php echo$P1;?><br>
                    <strong>META</strong>100%<br>
                </div>
              </div>
            </div>
            <!--fin p1-->
            <!--p2-->
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p2" aria-expanded="false" aria-controls="p2">
                  P2. PROVISION NETA PARA PRESTAMOS INCORPORABLES /MOROSOS MENOR A 12 MESES
                </button>
              </h2>
              <div id="p2" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                  <strong>a.</strong>Total de Provision para Prestamos incobrables<br>
                  <strong>b.</strong>Provisiones usadas para cubrir prestamos con morosidad mayor a 12 meses<br>
                  <strong>c.</strong>porcentaje recomendado de 35%<br>
                  <strong>d.</strong>saldos total de los prestasmos pendientes con morosidad de 1-12 meses<br>
                  <strong>e.</strong>Provision para Prestamos incobrables<br>
                  <strong>f.</strong>Provision para Prestamos incobrables<br>
                <?php echo $P2;?>
                <strong>META.</strong>35% DE PROVISIONES CON MOROSIDAD MENOR A 12 MESES<br>
                </div>
              </div>
            </div>
            <!--fin p2-->
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pU2" aria-expanded="false" aria-controls="pU2">
                  PU2. PROVISION NETA PARA PRESTAMOS INCORPORABLES /MOROSOS MENOR A 12 DE 1 A 12 MESES DEFINIDO POR EL USUARIO
                  
                </button>
              </h2>
              <div id="pU2" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                <strong>a.</strong>Total de Provision para Prestamos incobrables<br>
                  <strong>b.</strong>Provisiones usadas para cubrir prestamos con morosidad mayor a 12 meses<br>
                  <strong>c.</strong>porcentaje recomendado de 35%<br>
                  <strong>d.</strong>saldos total de los prestasmos pendientes con morosidad de 1-12 meses<br>
                  <strong>e.</strong>Provision para Prestamos incobrables<br>
                  <strong>f.</strong>Provision para Prestamos incobrables<br>
                <?php echo $P2;?>
                <strong>META: </strong>35% DE PROVISIONES CON MOROSIDAD MENOR A 12 MESES<br>
                </div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p3" aria-expanded="false" aria-controls="p3">
                  P3.TOTAL CASTIGO DE PRESTAMOS MOROSOS MAYOR A 12 MESES
                </button>
              </h2>
              <div id="p3" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                <strong>a.</strong>Total de prestamo con morosidad mayor a 12 meses <br>
                si (a)=0 entonces si a ≠ 0 entoces no 
                </div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p4" aria-expanded="false" aria-controls="p4">
                  P4.PRESTAMOS CASTIGADOS /TOTAL DE CARTERA DE PRESTAMOS
                </button>
              </h2>
              <div id="p4" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                <strong>a.</strong>Castigos acomulados del ejercicio en curso<br>
                  <strong>b.</strong>Castigos acumulados del ejercicio anterior<br>
                  <strong>c.</strong>Cartera de Prestamos Bruta(menos provisiones)al final del ejercicio en curso<br>
                  <strong>d.</strong>Cartera de Prestamos Bruta(menos provisiones)al final del ejercicio anterior<br>
                <?php echo$P4;?><br>
                <strong>META: </strong>minimizar<br>
                
                </div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p5" aria-expanded="false" aria-controls="p5">
                  P5. RECUPERACION ACUMULADA DE CARTERA CATIGADA/CARTERA CASTIGADA ANULADA
                </button>
              </h2>
              <div id="p5" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                  <strong>a.</strong>Recuperacion acomulada de catigos<br>
                  <strong>b.</strong>Castigos aumulados<br>
                <?php echo$P5;?>
                <strong>META: </strong>Mayor al 75%<br>
                  
                </div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p7" aria-expanded="false" aria-controls="p7">
                  P6. SOLVENCIA
                </button>
              </h2>
              <div id="p7" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                  <strong>a.</strong>Total activo<br>
                  <strong>b.</strong>Provisiones para activos en riesgo<br>
                  <strong>c.</strong>slado  de prestamos con morosidad mayor a 12 meses<br>
                  <strong>d.</strong>saldo de prestamos con morosidad de 1 a 12 meses<br>
                  <strong>e.</strong>total de pasivos<br>
                  <strong>f.</strong>Activos problemanticos<br>
                  <strong>g.</strong>total de ahorros<br>
                  <strong>h.</strong>Total de aportaciones<br>
                <?php echo $P6;?>
                <strong>META: </strong>111%<br>
                </div>
              </div>
            </div>
          </div>
        </div> <!--FIN DEL ROW-->
      </div>
    </div> <!-- FIN DEL CARDBODY -->
  <?php
  break;

  //ventana de estructuras
  case "estructura":
  ?>
    <div class="card crdbody">
      <div class="card-header panelcolor">ESTRUCTURA.</div>
      <div class="card-body">

   <input id="condi" value="proteccion" readonly hidden>
   <div class="row crdbody">
    <div class="accordion" id="accordionExample">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#p1" aria-expanded="true" aria-controls="p1">
        
            E1. PRESTAMOS NETOS/ACTIVO TOTAL
          </button>
        </h2>
        <div id="p1" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
          <div class="accordion-body">
          <strong>a.</strong>Total de cartera de prestamos bruta pendiente<br>
          <strong>b.</strong>Total de provisiones para prestamos incobrables<br>
          <strong>c.</strong>Total de arechivos<br>
            <?php echo $E1 ?>
            <strong>META: </strong>70-80%<br>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingTwo">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p2" aria-expanded="false" aria-controls="p2">
            E2. INVERSIONES LIQUIDAS / TOTAL ACTIVO
          </button>
        </h2>
        <div id="p2" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
          <div class="accordion-body">
          <strong>a.</strong>Total de inversiones liquidas<br>
          <strong>b.</strong>Total de activos<br>
          <?php echo $P5 ?>
          <strong>META.</strong>menor o igula a 16%<br>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingTwo">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pU2" aria-expanded="false" aria-controls="pU2">
            E3. INVERSIONES FINANCIERAS /TOTAL ACTIVO
          </button>
        </h2>
        <div id="pU2" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
          <div class="accordion-body">
          <strong>a.</strong>Total de inversiones financieras<br>
          <strong>b.</strong>Total de activos<br>
          <?php echo $P5 ?>
          <strong>META.</strong>menor o igula a 2%<br>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingTwo">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p3" aria-expanded="false" aria-controls="p3">
            E4. IVERSIONES NO FINANCIERAS/TOTAL ACTIVO
          </button>
        </h2>
        <div id="p3" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
          <div class="accordion-body">
          <strong>a.</strong>Total de inversiones no financieras<br>
          <strong>b.</strong>Total de activos<br>
          <?php echo $P5 ?>
          <strong>META.</strong>0%<br>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingTwo">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p4" aria-expanded="false" aria-controls="p4">
            E5. DEPOSITOS DE AHORRO
          </button>
        </h2>
        <div id="p4" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
          <div class="accordion-body">
          <strong>a.</strong>Total de depositos de ahorro<br>
          <strong>b.</strong>Total de activos<br>
          <?php echo $P5 ?>
          <strong>META.</strong>70-80%<br>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingTwo">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p5" aria-expanded="false" aria-controls="p5">
            E6 CREDITO EXTERNO
          </button>
        </h2>
        <div id="p5" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
          <div class="accordion-body">
          <strong>a.</strong>Total de prestamos a corto plazo<br>
          <strong>b.</strong>Total de prestamos a largo plazo<br>
          <strong>a.</strong>Total de activos<br>      
          <?php echo $E6 ?><br> 
          <strong>META.</strong>0-5%<br>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingThree">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p7" aria-expanded="false" aria-controls="p7">
            E7 APORTACIONES DE ASOCIADOS
          </button>
        </h2>
        <div id="p7" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
          <div class="accordion-body">
          <strong>a.</strong>Total de aportaciones de asociados<br>
          <strong>b.</strong>Total de activos<br>
          <?php echo $P5 ?>
          <strong>META.</strong>menor o igual al 20%<br>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingTwo">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p9" aria-expanded="false" aria-controls="p9">
            E8. CAPITAL INSTITUCIONAL
          </button>
        </h2>
        <div id="p9" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
          <div class="accordion-body">
          <strong>a.</strong>Total de Capital Institucional<br>
          <strong>b.</strong>Total de activos<br>
          <?php echo $P5 ?>
          <strong>META.</strong>mayo o igual al 10%<br>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingTwo">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#P10" aria-expanded="false" aria-controls="P10">
            E9. CAPITAL INSTITUCIONAL NETO
          </button>
        </h2>
        <div id="P10" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
          <div class="accordion-body">
          <strong>a.</strong>Capital institucional<br>
          <strong>b.</strong>Provisiones de Riesgos<br>
          <strong>c.</strong>Saldo con morosidad mayor a 12 meses<br>
          <strong>d.</strong>Saldo con morosidad de 1 a 12 meses<br>
          <strong>e.</strong>activos problematicos(perdidas que seran liquidadas)<br>
          <strong>f.</strong>Total de activos<br>
          <?php echo $E9 ?>
          <strong>META.</strong>mayor o igual al 10%<br>
          </div>
        </div>
      </div>
    </div>
    </div> <!--FIN DEL ROW-->
    </div>
    </div> <!-- FIN DEL CARDBODY -->
  <?php
  break;

  //ventana de activos
  case "activos":
  ?>
  <div class="card crdbody">
    <div class="card-header panelcolor">ACTIVOS.</div>
    <div class="card-body">
      <input id="condi" value="proteccion" readonly hidden>
      <div class="row crdbody">
        <div class="accordion" id="accordionExample">
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#p1" aria-expanded="true" aria-controls="p1">
                A1. MOROSIDAD TOTAL
              </button>
            </h2>
            <div id="p1" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
              <div class="accordion-body">
              <strong>a.</strong>Total de todos los saldos de morosidad en la cartera de prestamos morosos(control no contable)<br>
                <strong>b.</strong>Total de la cartera de prestamos pendientes(bruta)<br>
                <?php echo $P5 ?><br>
            <strong>META: </strong>menor o igual al 5%%<br>
              
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p2" aria-expanded="false" aria-controls="p2">
                A1U. MOROSIDAD TOTAL DADA POR EL USUARIO
              </button>
            </h2>
            <div id="p2" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
              <div class="accordion-body">
              <strong>a.</strong>Total de todos los saldos de morosidad en la cartera de prestamos morosos(control no contable)<br>
                <strong>b.</strong>Total de la cartera de prestamos pendientes(bruta)<br>
                <?php echo $P5 ?><br>
            <strong>META: </strong>menor o igual al 5%%<br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pU2" aria-expanded="false" aria-controls="pU2">
                A2. ACTIVOS IMPRODUCTIVOS
              </button>
            </h2>
            <div id="pU2" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
              <div class="accordion-body">
              <strong>a.</strong>Total de Activos Improductivos<br>
                <strong>b.</strong>Total activos<br>
                <?php echo $P5 ?><br>
            <strong>META: </strong>menor o igual a 5%<br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p3" aria-expanded="false" aria-controls="p3">
              A3.CAPITAL INSTITUCIONAL NETO+ CAPITAL TRASITORIO
              </button>
            </h2>
            <div id="p3" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <strong>a.</strong>Total de Capital Institucional neta<br>
                <strong>b.</strong>Total de Capital Transitorio<br>
                <strong>c.</strong>Total de pasivos que no producen interees<br>
                <strong>d.</strong>Total de Activos Improductivos<br>
                <?php echo $A3 ?>
            <strong>META: </strong>menor o igual al 200%<br>
              </div>
            </div>
          </div>
        </div>
      </div> <!--FIN DEL ROW-->
    </div>
  </div> <!-- FIN DEL CARDBODY -->
  <?php
  break;

  //ventana de rendimiento
  //PENDIENTE
  case "rendimiento":
  ?>
  <div class="card crdbody">
    <div class="card-header panelcolor">RENDIMIENTO.</div>
    <div class="card-body">
      <input id="condi" value="proteccion" readonly hidden>
      <div class="row crdbody">
        <div class="accordion" id="accordionExample">
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#p1" aria-expanded="true" aria-controls="p1">
                R1. INGRESO NETO DE PRESTAMOS
              </button>
            </h2>
            <div id="p1" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
              <div class="accordion-body">
              <strong>a.</strong>Total de Capital Institucional neta<br>
                <strong>b.</strong>Total de Capital Transitorio<br>
                <strong>c.</strong>Total de pasivos que no producen interees<br>
                <strong>d.</strong>Total de Activos Improductivos<br>
              <?php echo $R1 ?><br>
              <strong>METAS </strong> tasa empresarial que cubra los gastos financieros y operativos, gastos de provisiones para los gastos financieros y operaivos de Riesgos de riesgos<br>
              y gastos que contribuyen a los niveles de capital institucional para mantenerls en la norma de E9(mayor al 10%)
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p2" aria-expanded="false" aria-controls="p2">
              R2.INGRESO POR INVERSIONES DE VENTAS LIQUIDAS
              </button>
            </h2>
            <div id="p2" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample"><div class="accordion-body">
                <strong>a.</strong>Total de ingresos por inversiones liquidadas<br>
                <strong>b.</strong>Total de inversiones al final del ejercicio en curso<br>
                <strong>c.</strong>Total de inversiones al final del ejercicio anterior<br>
                <?php echo $R2 ?><br>
                <strong>METAS </strong> Las tasas mas altas del mercado sin correr riesgo indebido<br>
                
            </div></div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pU2" aria-expanded="false" aria-controls="pU2">
                R3. INGRESO POR INVERSIONES FINANCIERAS
              </button>
            </h2>
            <div id="pU2" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <strong>a.</strong>Total de ingresos por inversiones financieras<br>
                <strong>b.</strong>Total de inversiones al final del ejercicio en curso<br>
                <strong>c.</strong>Total de inversiones al final del ejercicio anterior<br>
                <?php echo $R2 ?><br>
                <strong>METAS </strong> Las tasas mas altas del mercado sin correr riesgo indebido<br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p3" aria-expanded="false" aria-controls="p3">
                R4.ingreso total por inversiones no financieras
              </button>
            </h2>
            <div id="p3" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <strong>a.</strong>Total de ingresos por inversiones no financieras<br>
                <strong>b.</strong>Total de inversiones al final del ejercicio en curso<br>
                <strong>c.</strong>Total de inversiones al final del ejercicio anterior<br>
                <?php echo $R2 ?><br>
                <strong>METAS </strong> Mayor o igual a R1<br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p4" aria-expanded="false" aria-controls="p4">
              R5. COSTO FINANCIERO:NTERESES SOBRE DEPOSITOS DE AHORRO
              </button>
            </h2>
            <div id="p4" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <strong>a.</strong>Total de ingresos por inversiones no financieras<br>
                <strong>b.</strong>Total de inversiones al final del ejercicio en curso<br>
                <strong>c.</strong>Total de inversiones al final del ejercicio anterior<br>
                <strong>d.</strong>Total de depositos de ahorro al final del ejercicio en curso<br>
                <strong>e.</strong>Total de depositos de ahorro al final del ejercicio anterior<br>
              <?php echo $R5 ?><br>
              <strong>METAS</strong>Tasas del mercado que protegan el valor nominal de depositos de ahorro<br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p5" aria-expanded="false" aria-controls="p5">
                R6.COSTO FINANCIERO:INTRERESES SOBRE CERDITO EXTERNO
              </button>
            </h2>
            <div id="p5" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
              <div class="accordion-body">
            <strong>a.</strong>Total de ingresos por inversiones no financieras<br>
                <strong>b.</strong>Total de credito externo al final del ejercicio en curso<br>
                <strong>c.</strong>Total de credito externo al final del ejercicio anterior<br>
              <?php echo $R2 ?><br>
              <strong>META: </strong> TASAS DEL MERCADO <br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p7" aria-expanded="false" aria-controls="p7">
              R7.COSTO FINANCIERO:DIVIDIENDOSE SOBRE APORTACIONES
              </button>
            </h2>
            <div id="p7" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <strong>a.</strong>Total de dividendos pagados sobre aportaciones de asociados<br>
                <strong>b.</strong>Total de primas de seguro pagadas para las aportaciones de asociados<br>
                <strong>c.</strong>Total de Impuestos pagados por cooperativa de ahorro y credito sobre los dividendos de aportaciones<br>
                <strong>d.</strong>Total de aportaciones al final del ejercicio en curso<br>
                <strong>e.</strong>Total de aportaciones al final del ejercicio anterior<br>
              <?php echo $R5 ?><br>
              <strong>META</strong> limitado a la tasa pasiva y ≥ R5
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p8" aria-expanded="false" aria-controls="p8">
                R8. MARGEN BRUTO/PORMEDIO TOTAL ACTIVO
              </button>
            </h2>
            <div id="p8" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <strong>a.</strong>ingresos por inversiones de prestamos<br>
                <strong>b.</strong>ingresos por inversiones liquidas<br>
                <strong>c.</strong>ingresos por inversiones financieras<br>
                <strong>d.</strong>ingresos por inversiones no financieras<br>
                <strong>e.</strong>Otros Ingresos<br>
                <strong>f.</strong>costo de intereses para deposito de ahorro<br>
                <strong>g.</strong>costo de dividendos o intereses de las aportaciones de asociados<br>
                <strong>h.</strong>costo de intereses sobre  el credito<br>
                <strong>i.</strong>Total de activos al final del ejercicio en curso<br>
                <strong>j.</strong>Total de activos al final del ejercicio pasado<br>
              <?php echo $R8 ?><br>
              <strong>META: </strong>Genera suficiente ingreso para cubrir todos los gastos operativos y provisiones para prestamos incobrables y asegurar aumentos adecuados del capital institucional y cumplir con la meta de E9≥10%<br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p9" aria-expanded="false" aria-controls="p9">
                R9: GASTOS OPERATIVOS/PROMEDIO TOTAL ACTIVO
              </button>
            </h2>
            <div id="p9" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <strong>a.</strong>Total de gastos Operativos(menos las provisiones para prestamos incobrables)<br>
                <strong>b.</strong>Total de activos al final del año en curso<br>
                <strong>c.</strong>Total de activos al final del año pasado<br>
              <?php echo $R2 ?><br>
              <strong>META: </strong>≤5%<br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p10" aria-expanded="false" aria-controls="p10">
                R10 PROVISION PARA PRESTAMOS INCOBRABLES
              </button>
            </h2>
            <div id="p10" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <strong>a.</strong>ingreso neto(Despues de dividendos)<br>
                <strong>b.</strong>Total de activos al final del año en curso<br>
                <strong>c.</strong>Total de activos al final del año pasado<br>
              <?php echo $R2 ?><br>
              <strong>META:</strong> Lo suficiente para cubrir el 100%  de prestamos morosos > 12 meses y el 35% de prestamos morosos entre 1-12 meses
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p11" aria-expanded="false" aria-controls="p11">
                R11 INGRESO O GASTOS EXTRAORDINARIOS
              </button>
            </h2>
            <div id="p11" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <strong>a.</strong>Total de ingresos o gastos extraordinarios(ejercicio en curso)<br>
                <strong>b.</strong>Total de activos al final del año en curso<br>
                <strong>c.</strong>Total de activos al final del año pasado<br>
              <?php echo $R2 ?><br>
              <strong>META:</strong> Minimizar
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p12" aria-expanded="false" aria-controls="p12">
                R12 INGRESO NETO /PROMEDIO DE ACTIVO TOTAL
              </button>
            </h2>
            <div id="p12" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <strong>a.</strong>ingreso neto(Despues de dividendos)<br>
                <strong>b.</strong>Total de activos al final del año en curso<br>
                <strong>c.</strong>Total de activos al final del año pasado<br>
              <?php echo $R2 ?><br>
              <strong>METAS:</strong>" >1% suficiente para alcnazar el indicador E8 " <br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p13" aria-expanded="false" aria-controls="p13">
              R13 EXEDENTE DEL PROMEDIO NETO
              </button>
            </h2>
            <div id="p13" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <strong>a.</strong>Exedente neto(Despues de dividendos)<br>
                <strong>b.</strong>Total de institucional al final del año en curso<br>
                <strong>c.</strong>Total de institucional al final del año pasado<br>
                <strong>d.</strong>Total de inversiones al final del ejercicio en curso<br>
                <strong>e.</strong>Total de inversiones al final del ejercicio anterior<br>
                <?php echo $R13 ?><br>
                <strong>METAS</strong>Mayor Inflacion<br>
              </div>
            </div>
          </div>
        </div>
      </div> <!--FIN DEL ROW-->
    </div>
  </div> <!-- FIN DEL CARDBODY -->
  <?php
  break;

  //ventana de liquidez
  case "liquidez":
  ?>
  <div class="card crdbody">
    <div class="card-header panelcolor">LIQUIDEZ.</div>
    <div class="card-body">
      <input id="condi" value="proteccion" readonly hidden>
      <div class="row crdbody">
        <div class="accordion" id="accordionExample">
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#p1" aria-expanded="true" aria-controls="p1">
              L1. INVERSWIONES LIQUIDAS + ACTIVOS LIQUIDOS - CUENTAS POR PAGAR
              </button>
            </h2>
            <div id="p1" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <strong>a.</strong>Total de inversiones liquidas productivas<br>
                <strong>b.</strong>Total de activos liquidos improductivos<br>
                <strong>c.</strong>Total de cuentas por pagar a cotor plazo menor a 30 dias<br>
                <strong>d.</strong>Total de depositos de ahorro<br>
              <?php echo $L1 ?>
              <strong>META.</strong>15-20%<br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p2" aria-expanded="false" aria-controls="p2">
                L2. RESERVAS DE LIQUIDEZ
              </button>
            </h2>
            <div id="p2" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <strong>a.</strong>Total de inversiones liquidas productivas<br>
                <strong>b.</strong>Total de activos liquidos improductivos<br>
                <strong>c.</strong>Total de cuentas por pagar a cotor plazo menor a 30 dias<br>
              <?php echo $E6 ?>
              <strong>METAS.</strong>Total de cuentas por pagar a cotor plazo menor a 30 dias<br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pU2" aria-expanded="false" aria-controls="pU2">
                L3. ACTIVOS LIQUIDOS IMPRODUCTIVOS
              </button>
            </h2>
            <div id="pU2" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
              <div class="accordion-body">
              <strong>a.</strong>Total de inversiones liquidas productivas<br>
              <strong>b.</strong>Total de activos liquidos improductivos<br>
              <?php echo $P5?>
              <strong>METAS.</strong>Total de cuentas por pagar a cotor plazo menor a 30 dias<br>
              </div>
            </div>
          </div>
        </div>
      </div> <!--FIN DEL ROW-->
    </div>
  </div> <!-- FIN DEL CARDBODY -->
  <?php
  break;

  //aqui se da la ventana de señles
  //PENDIENTE
  case "señales":
  ?>
    <div class="card crdbody">
    <div class="card-header panelcolor">SEÑALES.</div>
    <div class="card-body">
      <input id="condi" value="proteccion" readonly hidden>
      <div class="row crdbody">
        <div class="accordion" id="accordionExample">
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#p1" aria-expanded="true" aria-controls="p1">
              S1 CRECIMIENTO DE PRESTAMOS
              </button>
            </h2>
            <div id="p1" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <strong>a.</strong>Saldo actual de la cartera de prestamos<br>
                <strong>b.</strong>Saldo de la cartera de prestamos final del ejercicio anterior<br>
              <?php echo $S1 ?><br>
                <strong>META:</strong><br>
                para aumentar la estructura de prestamos (E1),el S1 debe de ser mayor al S11<br>
                Para Mantener la estructura de prestamos (E1),el s1 debe de sar igual al S11 <br>
                Para disminuir la estructura de prestamos (E1),el s1 debe de sar menor al S11 <br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p2" aria-expanded="false" aria-controls="p2">
                S2 CRECIMIENTO DE INVERSIONES LIQUIDAS
              </button>
            </h2>
            <div id="p2" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
              <div class="accordion-body">
              <strong>a.</strong>Total de inversiones liquidas actuales<br>
              <strong>b.</strong>Total de inversiones liquidas final del ejercicio anterior<br>
              <?php echo $S1 ?><br>
              <strong>META:</strong><br>
                para aumentar la estructura de inversiones liqudas(E2),el S2 debe de ser mayor al S11<br>
                Para Mantener la estructura de inversiones liqudas(E2),el s2 debe de sar igual al S11 <br>
              Para disminuir la estructura de inversiones liqudas (E2),el s2 debe de sar menor al S11 <br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pU2" aria-expanded="false" aria-controls="pU2">
                S3 CRECIMIENTO DE INVERSIONES FINANCIERAS
              </button>
            </h2>
            <div id="pU2" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
              <div class="accordion-body">
              <strong>a.</strong>Total de inversiones finacnieras actuales<br>
              <strong>b.</strong>Total de inversiones finacnieras final del ejercicio anterior<br>
              <?php echo $S1 ?><br>
              <strong>META:</strong><br>
                para aumentar la estructura de inversiones financieras (E3),el S3 debe de ser mayor al S11<br>
                Para Mantener la estructura de inversiones financieras (E3),el s3 debe de sar igual al S11 <br>
               Para disminuir la estructura de inversiones financieras (E3),el s3 debe de sar menor al S11 <br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p3" aria-expanded="false" aria-controls="p3">
                S4 CRECIMIENTO DE INVERSIONES NO FINANCIERAS
              </button>
            </h2>
            <div id="p3" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
              <div class="accordion-body">
              <strong>a.</strong>Total de inversiones no financieras actuales<br>
              <strong>b.</strong>Total de inversiones no financieras final del ejercicio anterior<br>
              <?php echo $S1 ?><br>
              <strong>META:</strong><br>
                para aumentar la estructura inversiones no fincacnieras (E4),el S4 debe de ser mayor al S11<br>
                Para Mantener la estructura inversiones no fincacnieras (E4),el S4 debe de sar igual al S11 <br>
               Para disminuir la estructura inversiones no fincacnieras (E4),el S4 debe de sar menor al S11 <br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p4" aria-expanded="false" aria-controls="p4">
                S5. CRECIMIENTO DE DEPOSITOS DE AHORRO
              </button>
            </h2>
            <div id="p4" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
              <div class="accordion-body">
              <strong>a.</strong>Total de depositos de ahorro actuales<br>
              <strong>b.</strong>Total de depositos de ahorro final del ejercicio anterior<br>
              <?php echo $S1 ?><br>
              <strong>META:</strong><br>
                para aumentar la estructura de depositos de ahorro (E5),el S5 debe de ser mayor al S11<br>
                Para Mantener la estructura de depositos de ahorro (E5),el s5 debe de sar igual al S11 <br>
               Para disminuir la estructura de depositos de ahorro (E5),el s5 debe de sar menor al S11 <br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p5" aria-expanded="false" aria-controls="p5">
                S6. CRECIMIENTO DE CREDITO EXTERNO
              </button>
            </h2>
            <div id="p5" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
              <div class="accordion-body">
              <strong>a.</strong>Total de credito externo actuales<br>
              <strong>b.</strong>Total de credito externo final del ejercicio anterior<br>
              <?php echo $S1 ?><br>
              <strong>META:</strong><br>
                para aumentar la estructura de Credito Externo (E6),el S6 debe de ser mayor al S11<br>
                Para Mantener la estructura de Credito Externo (E6),el s6 debe de sar igual al S11 <br>
               Para disminuir la estructura de Credito Externo (E6),el s6 debe de sar menor al S11 <br>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p7" aria-expanded="false" aria-controls="p7">
              S7. CRECIMIENTO DE APORTACIONES DE ASOCIADOS
              </button>
            </h2>
            <div id="p7" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <div class="minotacionmatematica2">
                <strong>a.</strong>Total de aportaciones de asociados actual<br>
                <strong>b.</strong>Total de aportaciones de asociados final del ejercicio anterior<br>
                <?php echo $S1 ?><br>
                <strong>META:</strong><br>
                para aumentar la estructura de aportaciones (E7),el S7 debe de ser mayor al S11<br>
                Para Mantener la estructura de aportaciones (E7),el s7 debe de sar igual al S11 <br>
               Para disminuir la estructura de aportaciones (E7),el s7 debe de sar menor al S11 <br>
                </div>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p8" aria-expanded="false" aria-controls="p8">
              S8. CRECIMIENTO DE CAPITAL INSTITUCIONAL
              </button>
            </h2>
            <div id="p8" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <div class="minotacionmatematica2">
                <strong>a.</strong>Capital institucional actual<br>
                <strong>b.</strong>Capital institucional final del ejercicio anterior<br>
                <?php echo $S1 ?><br>
                <strong>META:</strong><br>
                para aumentar la estructura de Capital Institucional (E8),el S8 debe de ser mayor al S11<br>
                Para Mantener la estructura de Capital Institucional (E8),el s8 debe de sar igual al S11 <br>
               Para disminuir la estructura de Capital Institucional (E8),el s8 debe de sar menor al S11 <br>
                </div>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p9" aria-expanded="false" aria-controls="p9">
              S9. CRECIMIENTO DE CAPITAL INSTITUCIONAL NETO
              </button>
            </h2>
            <div id="p9" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <div class="minotacionmatematica2">
                <strong>a.</strong>Capital Institucional neto actual(La definicion del capital institucional neto del E9)<br>
                <strong>b.</strong>Capital Institucional neto final del ejercicio anterior<br>
                <?php echo $S1 ?><br>
                <strong>META:</strong><br>
                para aumentar la estructura de Capital Institucional Neto (E9),el S9 debe de ser mayor al S11<br>
                Para Mantener la estructura de Capital Institucional Neto (E9),el s9 debe de sar igual al S11 <br>
               Para disminuir la estructura de Capital Institucional Neto (E9),el s9 debe de sar menor al S11 <br>
                </div>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p10" aria-expanded="false" aria-controls="p10">
              S10. CRECIMIENTO DEL NUMERO DE ASOCIADOS
              </button>
            </h2>
            <div id="p10" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <div class="minotacionmatematica2">
                <?php echo $S1 ?><br>
                <strong>METAS: </strong><br>
                ≥15%
                </div>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p11" aria-expanded="false" aria-controls="p11">
              S11. CRECIMIENTO DEL ACTIVO TOTAL
              </button>
            </h2>
            <div id="p11" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <div class="minotacionmatematica2">
                <?php echo $S1 ?><br>
                <strong>METAS: </strong>
                > inflacion + 10%
                </div>
              </div>
            </div>
          </div>
        </div>
      </div> <!--FIN DEL ROW-->
    </div>
    </div> <!-- FIN DEL CARDBODY -->
  <?php
  break;
}
//FIN DE MODULO DE PERLAS
?>