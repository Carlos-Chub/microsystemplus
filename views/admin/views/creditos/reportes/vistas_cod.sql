<?php
/*
AQUI VOY A AGREGAR TODAS LAS CONSULTAS DE VISTAS Y PROTOTIPOS DE CONSULTAS
 */

 /* VISTA DE LOS CLIENTES  */

CREATE OR REPLACE  VIEW clientesCrediref AS
SELECT cli.idcod_cliente,cli.id_tipoCliente tipo,cli.primer_name,cli.segundo_name,cli.tercer_name,cli.primer_last,cli.segundo_last,cli.casada_last,cli.date_birth,
   cli.genero,cli.estado_civil,cli.type_doc,cli.no_identifica,cli.no_tributaria,cli.no_igss,cli.nacionalidad,cli.muni_extiende,cli.tel_no1,cli.tel_no2,cli.Direccion,
   cm.CCODCTA, cm.CESTADO, cm.DFecDsbls,cm.fecha_operacion,
COALESCE(m1.cod_crediref, '10001') AS municipio,
COALESCE(m1.cod_crediref,'X') AS codigo_postal
FROM tb_cliente cli
LEFT JOIN clhpzzvb_bd_general_coopera.tb_municipios m1 ON m1.codigo_municipio = cli.muni_extiende
LEFT JOIN cremcre_meta cm on  cm.CodCli = cli.idcod_cliente 
/*¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬*/

/* +++++++++++++++ CONSULTA DE LOS CLIENTES QUE ESTAN CON CREDITOS EN EL RANGO DE FECHAS DADO +++++++++++++++ */
 SELECT c.idcod_cliente,c.id_tipoCliente AS tipo,c.primer_name,c.segundo_name,
    c.tercer_name,c.primer_last,c.segundo_last,c.casada_last,c.date_birth,c.genero,c.estado_civil,c.type_doc,
    c.no_identifica,c.no_tributaria,c.no_igss,c.nacionalidad,c.muni_extiende,c.tel_no1,c.tel_no2,c.Direccion,
    COALESCE(m1.cod_crediref, '10001') AS municipio,COALESCE(m2.cod_crediref,'X') AS codigo_postal
  FROM tb_cliente c
  LEFT JOIN ".$db_name_general.".tb_municipios m1 ON m1.codigo_municipio = c.muni_extiende
  LEFT JOIN ".$db_name_general.".tb_municipios m2 ON m2.codigo_municipio = c.muni_reside
  WHERE c.idcod_cliente IN ( SELECT CodCli FROM cremcre_meta
    WHERE
    (CESTADO='F' AND DFecDsbls <= ?)
    OR (CESTADO='G' AND fecha_operacion BETWEEN ? AND ? ) ); 

    /*++++++++++++++++++++++ CONSULTA DE LOS CLIENTES QUE ESTAN COMO FIADORES +++++++++++++++++++++++++++++ */
SELECT DISTINCT idcod_cliente,id_tipoCliente tipo,primer_name,segundo_name, tercer_name,primer_last, segundo_last,
casada_last,date_birth,genero,estado_civil,type_doc,no_identifica,no_tributaria,no_igss,nacionalidad, muni_extiende, 
IFNULL((SELECT cod_crediref FROM " . $db_name_general . ".municipios WHERE codigo_municipio=cli.muni_extiende),'10001') municipio,
tel_no1,tel_no2,cli.Direccion,
IFNULL((SELECT cod_crediref FROM " . $db_name_general . ".tb_municipios WHERE codigo_municipio=cli.muni_reside),'X') codigo_postal
FROM cli_garantia gar 
INNER JOIN tb_garantias_creditos creg ON creg.id_garantia=gar.idGarantia
INNER JOIN tb_cliente cli ON cli.idcod_cliente=gar.descripcionGarantia 
WHERE creg.id_cremcre_meta IN 
(SELECT CCODCTA FROM cremcre_meta WHERE (CESTADO='F' AND DFecDsbls<=?) OR (CESTADO='G' AND fecha_operacion BETWEEN ? AND ?)); ); 

/* 
 <!-- <div class="row container contenedort">
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="ragencia" id="allofi" value="allofi" checked onclick="changedisabled(`#codofi`,0)">
                                        <label for="allofi" class="form-check-label">Consolidado </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="ragencia" id="anyofi" value="anyofi" onclick="changedisabled(`#codofi`,1)">
                                        <label for="anyofi" class="form-check-label"> Por Agencia</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <span class="input-group-addon col-2">Agencia</span>
                            <select class="form-select" id="codofi" style="max-width: 70%;" disabled>
                                <?php
                                //     $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                // ON ofi.id_agencia = usu.id_agencia GROUP BY ofi.id_agencia");
                                //     while ($ofi = mysqli_fetch_array($ofis)) {
                                //         echo '<option value="' . $ofi['id_agencia'] . '" selected>' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                //     }
                                ?>
                            </select>
                        </div>
                    </div> -->

<!-- <div class="col-sm-6">
                            <div class="card text-bg-light" style="height: 100%;">
                                <div class="card-header">Filtro por Fuente de fondos</div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="rfondos" id="allf" value="allf" checked onclick="changedisabled(`#fondoid`,0)">
                                                <label for="allf" class="form-check-label">Todo </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="rfondos" id="anyf" value="anyf" onclick="changedisabled(`#fondoid`,1)">
                                                <label for="anyf" class="form-check-label"> Por Fuente de fondos</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-floating mb-3">
                                                <select class="form-select" id="fondoid" disabled>
                                                    <option value="0" selected disabled>Seleccionar fuente de Fondos</option>
                                                    <?php
                                                    // $fons = mysqli_query($conexion, "SELECT * FROM `ctb_fuente_fondos` where estado=1");
                                                    // while ($fon = mysqli_fetch_array($fons)) {
                                                    //     echo '<option value="' . $fon['id'] . '">' . $fon['descripcion'] . '</option>';
                                                    // }
                                                    ?>
                                                </select>
                                                <label class="text-primary" for="fondoid">Fondos</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->


eliminado del formularop de benito, CREDITOS_03
  <!-- <style>
  .ventana { width: 500px; height: 500px; border: 1px solid #000; position: relative; }
  .etiqueta { width: 80px; height: 30px; background-color: #3498db; color: #fff; text-align: center; line-height:30px; border-radius: 5px; position: absolute; cursor: pointer; }
  .etiqueta-arrastre { cursor: grab; }
	.etiqueta-arrastre:active { cursor: grabbing; }
  </style>
  <div class="contenedort"> <div id="ventana" class="ventana"> <input type="text"> </div>
  <div class="row"> <div class="col-4"> <div class="card"> <div class="card-header">FACTORES 1</div>
  <div class="card-body"> <div class="container"> <div id="etiqueta1" class="etiqueta etiqueta-arrastre"> Monto desembolsado </div>
                                    <div id="etiqueta2" class="etiqueta etiqueta-arrastre">
                                        %ss
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">OPERADORES</div>
                            <div class="card-body">
                                <div class="container">
                                    <div id="etiqueta1" class="etiqueta etiqueta-arrastre">
                                        /
                                    </div>
                                    <div id="etiqueta2" class="etiqueta etiqueta-arrastre">
                                        -
                                    </div>
                                    <div id="etiqueta2" class="etiqueta etiqueta-arrastre">
                                        *
                                    </div>
                                    <div id="etiqueta2" class="etiqueta etiqueta-arrastre">
                                        +
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">FACTORES 2</div>
                            <div class="card-body">
                                <div class="container">
                                    <div id="etiqueta1" class="etiqueta etiqueta-arrastre">
                                        Monto desembolsado
                                    </div>

                                    <div id="etiqueta2" class="etiqueta etiqueta-arrastre">
                                        %ss
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                $(document).ready(function() {
                    $(".etiqueta-arrastre").draggable({
                        containment: "#ventana",
                        stack: ".etiqueta-arrastre"
                    });
                });
            </script> -->



    <div class="row">	<div class="col-sm-4">
    <label for="finicio">Fecha de mes seleccionado</label>
    <input type="date" class="form-control" id="dateburo" value="<?= date("Y-m-d"); ?>">
    </div>	</div>	<br>


       <div class="card">
        <div class="card-header">FILTROS</div>
        <div class="card-body">
        <div class="row container contenedort">
          <div class="col-sm-6">
          <div class="card text-bg-light" style="height: 100%;">
          <div class="card-header">FECHA DE PROCESO</div>
          <div class="card-body">
            <div class="row" id="filfechas">
              <div class="col-sm-6">
                <label for="finicio">Inicio</label>
                <input type="date" class="form-control" id="finicio" value="<?= date("Y-m-d"); ?>">
              </div>
              <div class="col-sm-6">
                <label for="ffin">Fin</label>
                <input type="date" class="form-control" id="ffin" value="<?= date("Y-m-d"); ?>">
              </div>
            </div>
          </div> </div> </div>
        </div>
        <div class="row justify-items-md-center">
          <div class="col align-items-center">
            <button type="button" class="btn btn-outline-danger" title="Generar archivo de texto" onclick="reportes([[`finicio`,`ffin`],[],[],[0]],`txt`,`reporte_crediref`,1)">
            <i class="fa-solid fa-file-pdf"></i> Generar
            </button>
            <button type="button" class="btn btn-outline-danger" onclick="printdiv2('#cuadro','0')">
              <i class="fa-solid fa-ban"></i> Cancelar
            </button>
            <button type="button" class="btn btn-outline-warning" onclick="salir()">
              <i class="fa-solid fa-circle-xmark"></i> Salir
            </button>
          </div>
        </div>
        </div>
      </div>		
*/