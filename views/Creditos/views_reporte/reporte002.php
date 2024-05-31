<?php
session_start();
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];
$idusuario = $_SESSION['id'];

switch ($condi) {
    case 'reportePrepago': {
            $id = $_POST["xtra"];
            $codusu = $_SESSION['id'];
            $agencia = $_SESSION['agencia'];
?>
            <!-- APR_05_LstdCntsActvsDspnbls -->
            <div class="text" style="text-align:center">REPORTE DE VISITAS PREPAGO</div>
            <input type="text" value="reportePrepago" id="condi" style="display: none;">
            <input type="text" value="reporte002" id="file" style="display: none;">

            <div class="card">
                <div class="card-header">REPORTE DE VISITAS PREPAGO</div>
                <div class="card-body">
                    <!-- segunda linea -->
                    <div class="row d-flex align-items-stretch mb-3">
                        <!-- card para seleccionar una cuenta -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro por fecha</b></div>
                                <div class="list-group list-group-flush card-body ps-3">
                                    <div class="row mb-1">
                                        <div class="col-6">
                                            <span class="input-group-addon">Desde:</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="input-group-addon">Hasta:</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="input-group">
                                                <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?php echo date("Y-m-d"); ?>" id="fechaInicio">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group">
                                                <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?php echo date("Y-m-d"); ?>" id="fechaFinal">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- consultas -->

                        <!-- card para filtrar cuentas -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro de tipo de prepago</b></div>
                                <div class="card-body">
                                    <div class="row mt-3">
                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_ejecutivo" id="r_ejecutivo" value="1" checked onclick="activar_select_cuentas(this, true,'ejecutivo'); activar_select_cuentas(this, true,'oficina')">
                                                <label class="form-check-label" for="r_cuentas">Todo</label>
                                            </div>
                                        </div>
                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_ejecutivo" id="r_ejecutivo" value="2" onclick="activar_select_cuentas(this, true,'ejecutivo'); activar_select_cuentas(this, false,'oficina')">
                                                <label class="form-check-label" for="r_cuentas">Oficina/Ejecutivo</label>
                                            </div>
                                        </div>
                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_ejecutivo" id="r_ejecutivo" value="3" onclick="activar_select_cuentas(this,false,'ejecutivo'); activar_select_cuentas(this, true,'oficina')">
                                                <label class="form-check-label" for="r_cuenta">Ejecutivo</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card para las transacciones -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><b>Filtro de Oficina</b></div>
                                <div class="card-body">
                                    <div class="row">

                                       <!-- --REQ--crediprendas--1--restrinccion de reportes -->
                                    <?php 
                                      $sql = "SELECT id_usu, puesto, id_agencia
                                     FROM tb_usuario
                                     WHERE id_usu = '$idusuario'";
                                     $resultado = mysqli_query($conexion, $sql);

                                     if ($resultado) {
                                        $fila = mysqli_fetch_assoc($resultado);
                                    
                                        if ($fila) {
                                            $puestosP = array("ADM", "GER", "AUD");
                                       
                                            if (in_array($fila['puesto'], $puestosP)) {
                                        ?>
                                            <select class="form-select" aria-label="Default select example" id="oficina" disabled>
                                                <option selected value="0">Seleccione una oficina</option>
                                                <?php
                                                $data = mysqli_query($conexion, "SELECT id_agencia, CONCAT(nom_agencia,' - ',cod_agenc) AS nombre FROM tb_agencia");
                                                while ($dato = mysqli_fetch_array($data, MYSQLI_ASSOC)) { ?>
                                                    <option value="<?= $dato["id_agencia"]; ?>"><?= $dato["nombre"] ?></option>
                                                <?php } ?>


                                            </select>

                                        <?php
                                   } else {
                                    ?>
                                            <select class="form-select" aria-label="Default select example" id="oficina" disabled>
                                    <?php
                                    $ofis2 = mysqli_query($conexion, "SELECT usu.id_agencia, ofi.cod_agenc, ofi.nom_agencia
                                                                      FROM tb_usuario AS usu
                                                                      INNER JOIN tb_agencia AS ofi ON ofi.id_agencia = usu.id_agencia
                                                                     WHERE usu.id_usu = '$idusuario'");

                                    $filaOfis2 = mysqli_fetch_assoc($ofis2);

                                    echo '<option value="' . $filaOfis2['id_agencia'] . '" selected>' . $filaOfis2['cod_agenc'] . " - " . $filaOfis2['nom_agencia'] . '</option>';
                                    ?>
                                </select>
                                    <?php
                               }
                                } else {
                                    echo "No se encontraron resultados para el usuario con ID: $idusuario";
                                }
                            } else {
                                echo "Error en la consulta: " . mysqli_error($conexion);
                            }
                            
                            ?>


                                        <!--CODIGO ANTERIOR 
                                            radio button para los tipos de transacciones -->
                                        <!-- <div class="col d-flex justify-content-center"> -->
                                            <!-- <select class="form-select" aria-label="Default select example" id="oficina" disabled>
                                                <option selected value="0">Seleccione una oficina</option>
                                                <?php
                                                // $data = mysqli_query($conexion, "SELECT id_agencia, CONCAT(nom_agencia,' - ',cod_agenc) AS nombre FROM tb_agencia");
                                                // while ($dato = mysqli_fetch_array($data, MYSQLI_ASSOC)) { ?>
                                                    <option value="<?//= $dato["id_agencia"]; ?>"><?//= $dato["nombre"] ?></option>
                                                <?php //} ?>
                                            </select> -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- Card para las transacciones -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><b>Filtro de Ejecutivo</b></div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- radio button para los tipos de transacciones -->
                                        <div class="col d-flex justify-content-center">
                                            <select class="form-select" aria-label="Default select example" id="ejecutivo" disabled>
                                                <option selected value="0">Seleccione un ejecutivo</option>
                                                <?php
                                                $data = mysqli_query($conexion, "SELECT id_usu, CONCAT(nombre, ' ' ,apellido) AS nombre FROM tb_usuario WHERE estado=1 AND puesto='ANA'");
                                                while ($dato = mysqli_fetch_array($data, MYSQLI_ASSOC)) { ?>
                                                    <option value="<?= $dato["id_usu"]; ?>"><?= $dato["nombre"] ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="row justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <!-- en el metodo onclick se envian usuario y oficina para saber las cuentas de agencia a generar -->
                            <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[`fechaInicio`,`fechaFinal`],[`oficina`,`ejecutivo`],[`filter_ejecutivo`]],`xlsx`,`visitas_prepago`,1)">
                                <i class="fa-solid fa-file-excel"></i> Reporte en Excel
                            </button>

                            <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="reportes([[`fechaInicio`,`fechaFinal`],[`oficina`,`ejecutivo`],[`filter_ejecutivo`]],`pdf`,`visitas_prepago`,1)">
                                <i class="fa-solid fa-file-pdf"></i> Reporte en PDF
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
        <?php
        }
        break;
    case 'creditos_vencer': {
            $id = $_POST["xtra"];
            $codusu = $_SESSION['id'];
            $agencia = $_SESSION['agencia'];
        ?>
            <!-- APR_05_LstdCntsActvsDspnbls -->
            <div class="text" style="text-align:center">REPORTE DE CRÉDITOS A VENCER POR RANGO DE FECHAS</div>
            <input type="text" value="reportePrepago" id="condi" style="display: none;">
            <input type="text" value="reporte002" id="file" style="display: none;">

            <div class="card">
                <div class="card-header">REPORTE DE CRÉDITOS A VENCER POR RANGO DE FECHAS</div>
                <div class="card-body">
                    <!-- segunda linea -->
                    <div class="row d-flex align-items-stretch mb-3">
                        <!-- card para seleccionar una cuenta -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro por fecha</b></div>
                                <div class="list-group list-group-flush card-body ps-3">
                                    <div class="row mb-1">
                                        <div class="col-6">
                                            <span class="input-group-addon">Desde:</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="input-group-addon">Hasta:</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="input-group">
                                                <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?php echo date("Y-m-d"); ?>" id="fechaInicio">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group">
                                                <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?php echo date("Y-m-d"); ?>" id="fechaFinal">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro de tipo de crédito</b></div>
                                <div class="card-body">
                                    <div class="row mt-3">
                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_credito" id="r_credito" value="0" checked>
                                                <label class="form-check-label" for="r_cuentas">Todos</label>
                                            </div>
                                        </div>
                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_credito" id="r_credito" value="1">
                                                <label class="form-check-label" for="r_cuentas">Individual</label>
                                            </div>
                                        </div>
                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_credito" id="r_credito" value="2">
                                                <label class="form-check-label" for="r_cuenta">Grupal</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Card para las transacciones -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><b>Filtro de Analista</b></div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- radio button para los tipos de transacciones -->
                                        <div class="col d-flex justify-content-center">
                                            <select class="form-select" aria-label="Default select example" id="analista">
                                                <option selected value="0">Todos</option>
                                                <?php
                                                $data = mysqli_query($conexion, "SELECT id_usu, CONCAT(nombre, ' ' ,apellido) AS nombre FROM tb_usuario WHERE estado=1 AND puesto='ANA'");
                                                while ($dato = mysqli_fetch_array($data, MYSQLI_ASSOC)) { ?>
                                                    <option value="<?= $dato["id_usu"]; ?>"><?= $dato["nombre"] ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="row justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <!-- en el metodo onclick se envian usuario y oficina para saber las cuentas de agencia a generar -->
                            <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[`fechaInicio`,`fechaFinal`],[`analista`],[`filter_credito`]],`xlsx`,`creditos_a_vencer`,1)">
                                <i class="fa-solid fa-file-excel"></i> Reporte en Excel
                            </button>

                            <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="reportes([[`fechaInicio`,`fechaFinal`],[`analista`],[`filter_credito`]],`pdf`,`creditos_a_vencer`,0)">
                                <i class="fa-solid fa-file-pdf"></i> Reporte en PDF
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
        <?php
        }
        break;
    case 'creditos_desembolsados': {
            $id = $_POST["xtra"];
            $codusu = $_SESSION['id'];
            $agencia = $_SESSION['agencia'];
        ?>
            <!-- APR_05_LstdCntsActvsDspnbls -->
            <div class="text" style="text-align:center">REPORTE DE CRÉDITOS DESEMBOLSADOS</div>
            <input type="text" value="creditos_desembolsados" id="condi" style="display: none;">
            <input type="text" value="reporte002" id="file" style="display: none;">

            <div class="card">
                <div class="card-header">REPORTE DE CRÉDITOS DESEMBOLSADOS</div>
                <div class="card-body">
                    <!-- segunda linea -->
                    <div class="row d-flex align-items-stretch mb-3">
                        <!-- card para seleccionar una cuenta -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro por fecha de desembolso</b></div>
                                <div class="list-group list-group-flush card-body ps-3">
                                    <div class="row mb-1">
                                        <div class="col-6">
                                            <span class="input-group-addon">Desde:</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="input-group-addon">Hasta:</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="input-group">
                                                <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?php echo date("Y-m-d"); ?>" id="fechaInicio">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group">
                                                <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?php echo date("Y-m-d"); ?>" id="fechaFinal">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- consultas -->

                        <!-- card para filtrar cuentas -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro de tipo de crédito</b></div>
                                <div class="card-body">
                                    <div class="row mt-3">
                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_credito" id="r_credito" value="ALL" checked>
                                                <label class="form-check-label" for="r_cuentadd">Todos</label>
                                            </div>
                                        </div>
                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_credito" id="r_credito" value="INDI">
                                                <label class="form-check-label" for="r_cuentas">Crédito Individual</label>
                                            </div>
                                        </div>
                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_credito" id="r_credito" value="GRUP">
                                                <label class="form-check-label" for="r_cuenta">Crédito Grupal</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Card para las transacciones -->
                    <div class="row mb-2">
                        <!-- <div class="col-6">
                            <div class="card">
                                <div class="card-header"><b>Filtro de estado de Crédito</b></div>
                                <div class="card-body">
                                    <div class="row">
                                         radio button para los tipos de transacciones -->
                        <!-- <div class="col d-flex justify-content-center">
                                            <select class="form-select" aria-label="Default select example" id="estado">
                                                <option selected value="0">Seleccione un estado</option>
                                                <option value="A">Solicitud</option>
                                                <option value="D">Analisis</option>
                                                <option value="E">Aprobación</option>
                                                <option value="F">Desembolsado o Vigente</option>
                                                <option value="G">Cancelado</option>
                                                <option value="L">Rechazado</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <div class="col-6">
                            <div class="card">
                                <div class="card-header"><b>Filtro de estado de Crédito</b></div>
                                <div class="card-body">
                                    <div class="row">

                                        <!-- radio button para los tipos de transacciones -->
                                        <div class="col d-flex justify-content-center">
                                            <select class="form-select" aria-label="Default select example" id="estado">
                                                <option selected value="FG">**Colocados**</option>
                                                <?php
                                                $data = mysqli_query($conexion, "SELECT * FROM clhpzzvb_bd_general_coopera.tb_estadocredito WHERE id_EstadoCredito IN ('A', 'D', 'E', 'F','G','L');");
                                                while ($dato = mysqli_fetch_array($data, MYSQLI_ASSOC)) { ?>
                                                    <option value="<?= $dato["id_EstadoCredito"]; ?>"><?= $dato["EstadoCredito"] ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="card">
                                <div class="card-header"><b>Filtro de Agencia</b></div>
                                <div class="card-body">
                                    <div class="row">

                                        <!-- radio button para los tipos de transacciones -->
                                        <div class="col d-flex justify-content-center">

 <!-- --REQ--crediprendas--1--restrinccion de reportes -->
                 <?php 
                                      $sql = "SELECT id_usu, puesto, id_agencia
                                     FROM tb_usuario
                                     WHERE id_usu = '$idusuario'";
                                     $resultado = mysqli_query($conexion, $sql);

                                     if ($resultado) {
                                        $fila = mysqli_fetch_assoc($resultado);
                                    
                                        if ($fila) {
                                            $puestosP = array("ADM", "GER", "AUD");
                                       
                                            if (in_array($fila['puesto'], $puestosP)) {
                                        ?>
                                        <select class="form-select" id="agencia" style="max-width: 98%;">
                                            <?php
                                            $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                                                                ON ofi.id_agencia = usu.id_agencia GROUP BY ofi.id_agencia");
                                            while ($ofi = mysqli_fetch_array($ofis)) {
                                                echo '<option value="' . $ofi['id_agencia'] . '" selected>' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                           }
                                            ?>
                                        </select>
                                        <?php
                                   } else {
                                    ?>
                                    <select class="form-select" aria-label="Default select example" id="agencia">
                                    <?php
                                    $ofis2 = mysqli_query($conexion, "SELECT usu.id_agencia, ofi.cod_agenc, ofi.nom_agencia
                                                                      FROM tb_usuario AS usu
                                                                      INNER JOIN tb_agencia AS ofi ON ofi.id_agencia = usu.id_agencia
                                                                     WHERE usu.id_usu = '$idusuario'");

                                    $filaOfis2 = mysqli_fetch_assoc($ofis2);

                                    echo '<option value="' . $filaOfis2['id_agencia'] . '" selected>' . $filaOfis2['cod_agenc'] . " - " . $filaOfis2['nom_agencia'] . '</option>';
                                    ?>
                                </select>
                                    <?php
                               }
                                } else {
                                    echo "No se encontraron resultados para el usuario con ID: $idusuario";
                                }
                            } else {
                                echo "Error en la consulta: " . mysqli_error($conexion);
                            }
                            
                            ?>
                              


                                            <!-- <select class="form-select" aria-label="Default select example" id="agencia">
                                                <option selected value="0">Seleccione una agencia</option>
                                                <?php
                                                // $data = mysqli_query($conexion, "SELECT id_agencia, CONCAT(nom_agencia,' - ',cod_agenc) AS nombre FROM tb_agencia");
                                                // while ($dato = mysqli_fetch_array($data, MYSQLI_ASSOC)) { ?>
                                                <option value="<?
                                                //= $dato["id_agencia"]; ?>"><?
                                                //= $dato["nombre"] ?></option>
                                                <?php
                                            // } ?>
                                            </select> -->

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="row justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <!--HERE -->
                            <!-- en el metodo onclick se envian usuario y oficina para saber las cuentas de agencia a generar -->
                            <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[`fechaInicio`,`fechaFinal`,`condi`],[`estado`,`agencia`],[`filter_credito`]],`xlsx`,`creditos_desembolsados`,1)">
                                <i class="fa-solid fa-file-excel"></i> Reporte en Excel
                            </button>

                            <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="reportes([[`fechaInicio`,`fechaFinal`,`condi`],[`estado`,`agencia`],[`filter_credito`]],`pdf`,`creditos_desembolsados`,0)">
                                <i class="fa-solid fa-file-pdf"></i> Reporte en PDF
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
        <?php
        }
        break;
        //NEGROY filtro creditos desembolsados por agencia y usuario
    case "CRE_desembol_Filtro": {
            $id = $_POST["xtra"];
            $codusu = $_SESSION['id'];
            $agencia = $_SESSION['agencia'];
        ?>
            <!-- APR_05_LstdCntsActvsDspnbls -->
            <div class="text" style="text-align:center">REPORTE DE CRÉDITOS DESEMBOLSADOS (Autoasignada)</div>
            <input type="text" value="CRE_desembol_Filtro" id="condi" class="d-none">
            <input type="text" value="reporte002" id="file" class="d-none">
            <input type="text" value="<?= $codusu ?>" id="usuid" class="d-none">

            <div class="card">
                <div class="card-header">REPORTE DE CRÉDITOS DESEMBOLSADOS (Autoasignada)</div>
                <div class="card-body">
                    <!-- segunda linea -->
                    <div class="row d-flex align-items-stretch mb-3">
                        <!-- card para seleccionar una cuenta -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro por fecha de desembolso</b></div>
                                <div class="list-group list-group-flush card-body ps-3">
                                    <div class="row mb-1">
                                        <div class="col-6"> <span class="input-group-addon">Desde:</span> </div>
                                        <div class="col-6"> <span class="input-group-addon">Hasta:</span> </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="input-group">
                                                <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?= date("Y-m-d"); ?>" id="fechaInicio">
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="input-group">
                                                <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?= date("Y-m-d"); ?>" id="fechaFinal">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- consultas -->

                        <!-- card para filtrar cuentas -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro de tipo de crédito</b></div>
                                <div class="card-body">
                                    <div class="row mt-3">

                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_credito" id="r_credito" value="ALL" checked>
                                                <label class="form-check-label" for="r_cuentadd">Todos</label>
                                            </div>
                                        </div>

                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_credito" id="r_credito" value="INDI">
                                                <label class="form-check-label" for="r_cuentas">Crédito Individual</label>
                                            </div>
                                        </div>

                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_credito" id="r_credito" value="GRUP">
                                                <label class="form-check-label" for="r_cuenta">Crédito Grupal</label>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card para las transacciones -->
                    <div class="row mb-2">
                        <div class="col-6">
                            <div class="card">
                                <div class="card-header"><b>Filtro de estado de Crédito</b></div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- radio button para los tipos de transacciones -->
                                        <div class="col d-flex justify-content-center">
                                            <select class="form-select" aria-label="Default select example" id="estado">
                                                <option selected value="0">Seleccione un estado</option>
                                                <option value="A">Solicitud</option>
                                                <option value="D">Analisis</option>
                                                <option value="E">Aprobación</option>
                                                <option value="F">Desembolsado o Vigente</option>
                                                <option value="G">Cancelado</option>
                                                <option value="L">Rechazado</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="card">
                                <div class="card-header"><b>Agencia (Autoasignada)</b></div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- radio button para los tipos de transacciones -->
                                        <div class="col d-flex justify-content-center">
                                            <select class="form-select" aria-label="Default select example" disabled id="agencia">
                                                <option disabled value="0">Seleccione una agencia</option>
                                                <option disabled value="<?= $agencia ?>" selected> <?= $agencia ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <!-- en el metodo onclick se envian usuario y oficina para saber las cuentas de agencia a generar -->
                            <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[`fechaInicio`,`fechaFinal`,`condi`,`usuid`],[`estado`,`agencia`],[`filter_credito`]],`xlsx`,`creditos_desembolsados`,1)"> <i class="fa-solid fa-file-excel"></i> Reporte en Excel </button>

                            <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="reportes([[`fechaInicio`,`fechaFinal`,`condi`,`usuid`],[`estado`,`agencia`],[`filter_credito`]],`pdf`,`creditos_desembolsados`,0)"> <i class="fa-solid fa-file-pdf"></i> Reporte en PDF </button>

                            <button type="button" class="btn btn-outline-danger" onclick="printdiv2('#cuadro','0')"> <i class="fa-solid fa-ban"></i> Cancelar</button>

                            <button type="button" class="btn btn-outline-warning" onclick="salir()"> <i class="fa-solid fa-circle-xmark"></i> Salir </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
        break;
        //NEGROY filtro creditos VISITAS por agencia y usuario
    case "Prepago_Filtro": {
            $id = $_POST["xtra"];
            $codusu = $_SESSION['id'];
            $agencia = $_SESSION['agencia'];
        ?>
            <div class="text" style="text-align:center">REPORTE DE VISITAS PREPAGO (Autoasignada)</div>
            <input type="text" value="reportePrepago" id="condi" style="display: none;">
            <input type="text" value="reporte002" id="file" style="display: none;">
            <div class="card">
                <div class="card-header">REPORTE DE VISITAS PREPAGO (Autoasignada)</div>
                <div class="card-body">
                    <!-- segunda linea -->
                    <div class="row d-flex align-items-stretch mb-3">
                        <!-- card para seleccionar una cuenta -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro por fecha</b></div>
                                <div class="list-group list-group-flush card-body ps-3">
                                    <div class="row mb-1">
                                        <div class="col-6"><span class="input-group-addon">Desde:</span> </div>
                                        <div class="col-6"><span class="input-group-addon">Hasta:</span> </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="input-group">
                                                <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?= date("Y-m-d"); ?>" id="fechaInicio">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group">
                                                <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?= date("Y-m-d"); ?>" id="fechaFinal">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- consultas -->
                        <!-- card para filtrar cuentas -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro de tipo de prepago (Bloqueado)</b></div>
                                <div class="card-body">
                                    <div class="row mt-3">
                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_ejecutivo" id="r_ejecutivo" value="F0" checked>
                                                <label class="form-check-label" for="r_cuentas">Autoasignado</label>
                                            </div>
                                        </div>

                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" disabled>
                                                <label class="form-check-label" for="r_cuentas">Agencia (Auto)</label>
                                            </div>
                                        </div>

                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" disabled>
                                                <label class="form-check-label" for="r_cuenta">Ejecutivo (Auto)</label>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card para las transacciones -->
                    <div class="row mb-2">
                        <div class="col-6">
                            <div class="card">
                                <div class="card-header"><b>Filtro de Oficina</b></div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- radio button para los tipos de transacciones -->
                                        <div class="col d-flex justify-content-center">
                                            <select class="form-select" aria-label="Default select example" id="oficina" disabled>
                                                <option disabled value="0">Seleccione una oficina</option>
                                                <?php
                                                $query = "SELECT id_agencia, CONCAT(nom_agencia,' -',cod_agenc) AS nombre FROM tb_agencia WHERE cod_agenc='" . $agencia . "'";
                                                $data = mysqli_query($conexion, $query);
                                                while ($dato = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                                                    echo '<option value="' . $dato["id_agencia"] . '"> ' . $dato["nombre"] . ' </option> ';
                                                } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card para las transacciones -->
                        <div class="col-6">
                            <div class="card">
                                <div class="card-header"><b>Filtro de Ejecutivo</b></div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- radio button para los tipos de transacciones -->
                                        <div class="col d-flex justify-content-center">
                                            <select class="form-select" aria-label="Default select example" id="ejecutivo" disabled>
                                                <option disabled value="0">Seleccione un ejecutivo </option>
                                                <?php
                                                $query = "SELECT id_usu, CONCAT(nombre, ' ' ,apellido) AS nombre FROM tb_usuario WHERE estado=1 AND puesto='ANA' AND id_usu=" . $codusu;
                                                $resultado = mysqli_query($conexion, $query);
                                                // Verificar si hay resultados
                                                $num_filas = mysqli_num_rows($resultado);
                                                if ($num_filas > 0) {
                                                    // Si hay resultados, imprimir las opciones
                                                    while ($dato = mysqli_fetch_array($resultado, MYSQLI_ASSOC)) {
                                                        echo '<option selected disabled value="' . $dato["id_usu"] . '"> ' . $dato["nombre"] . ' </option>';
                                                    }
                                                } else {
                                                    // Si no hay resultados
                                                    echo '<option selected value="F0">USUARIO NO ANALISTA</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <!-- en el metodo onclick se envian usuario y oficina para saber las cuentas de agencia a generar -->
                            <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[`fechaInicio`,`fechaFinal`],[`oficina`,`ejecutivo`],[`filter_ejecutivo`]],`xlsx`,`visitas_prepago`,1)"> <i class="fa-solid fa-file-excel"></i> Reporte en Excel </button>

                            <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="reportes([[`fechaInicio`,`fechaFinal`],[`oficina`,`ejecutivo`],[`filter_ejecutivo`]],`pdf`,`visitas_prepago`,1)"> <i class="fa-solid fa-file-pdf"></i> Reporte en PDF </button>

                            <button type="button" class="btn btn-outline-danger" onclick="printdiv2('#cuadro','0')"> <i class="fa-solid fa-ban"></i> Cancelar </button>

                            <button type="button" class="btn btn-outline-warning" onclick="salir()"> <i class="fa-solid fa-circle-xmark"></i> Salir </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
        break;
    case 'incobrables':
        ?>
        <input type="text" id="file" value="reporte001" style="display: none;">
        <input type="text" id="condi" value="cartera_fuenteFondos" style="display: none;">
        <div class="text" style="text-align:center">CREDITOS INCOBRABLES</div>
        <div class="card">
            <div class="card-header">FILTROS</div>
            <div class="card-body">
                <div class="row container contenedort">
                    <div class="col-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por Agencias</div>
                            <div class="card-body">
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
                                <div class="row">
                                    <div class="col-sm-12">
                                        <span class="input-group-addon col-2">Agencia</span>
                                        <select class="form-select" id="codofi" style="max-width: 70%;" disabled>
                                            <?php
                                            $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                                                                            ON ofi.id_agencia = usu.id_agencia GROUP BY ofi.id_agencia");
                                            while ($ofi = mysqli_fetch_array($ofis)) {
                                                echo '<option value="' . $ofi['id_agencia'] . '" selected>' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
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
                                                $fons = mysqli_query($conexion, "SELECT * FROM `ctb_fuente_fondos` where estado=1");
                                                while ($fon = mysqli_fetch_array($fons)) {
                                                    echo '<option value="' . $fon['id'] . '">' . $fon['descripcion'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <label class="text-primary" for="fondoid">Fondos</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-danger" title="Cartera en pdf" onclick="reportes([[],[`codofi`,`fondoid`],[`ragencia`,`rfondos`],[]],`pdf`,`incobrables`,0)">
                            <i class="fa-solid fa-file-pdf"></i> Pdf
                        </button>
                        <button type="button" class="btn btn-outline-success" title="Cartera en Excel" onclick="reportes([[],[`codofi`,`fondoid`],[`ragencia`,`rfondos`],[]],`xlsx`,`incobrables`,1)">
                            <i class="fa-solid fa-file-excel"></i>Excel
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
    <?php
        break;
    case 'juridicos':
        ?>
        <input type="text" id="file" value="reporte001" style="display: none;">
        <input type="text" id="condi" value="cartera_fuenteFondos" style="display: none;">
        <div class="text" style="text-align:center">CARTERA JURÍDICA</div>
        <div class="card">
            <div class="card-header">FILTROS</div>
            <div class="card-body">
                <div class="row container contenedort">
                    <div class="col-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por Agencias</div>
                            <div class="card-body">
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
                                <div class="row">
                                    <div class="col-sm-12">
                                        <span class="input-group-addon col-2">Agencia</span>
                                        <select class="form-select" id="codofi" style="max-width: 70%;" disabled>
                                            <?php
                                            $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                                                                            ON ofi.id_agencia = usu.id_agencia GROUP BY ofi.id_agencia");
                                            while ($ofi = mysqli_fetch_array($ofis)) {
                                                echo '<option value="' . $ofi['id_agencia'] . '" selected>' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
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
                                                $fons = mysqli_query($conexion, "SELECT * FROM `ctb_fuente_fondos` where estado=1");
                                                while ($fon = mysqli_fetch_array($fons)) {
                                                    echo '<option value="' . $fon['id'] . '">' . $fon['descripcion'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <label class="text-primary" for="fondoid">Fondos</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-danger" title="Cartera en pdf" onclick="reportes([[],[`codofi`,`fondoid`],[`ragencia`,`rfondos`],[]],`pdf`,`juridicos`,0)">
                            <i class="fa-solid fa-file-pdf"></i> Pdf
                        </button>
                        <button type="button" class="btn btn-outline-success" title="Cartera en Excel" onclick="reportes([[],[`codofi`,`fondoid`],[`ragencia`,`rfondos`],[]],`xlsx`,`juridicos`,1)">
                            <i class="fa-solid fa-file-excel"></i>Excel
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
    <?php
        break;
    case 'prepago_recuperado':
    ?>
        <input type="text" id="file" value="reporte001" style="display: none;">
        <input type="text" id="condi" value="cartera_fuenteFondos" style="display: none;">
        <div class="text" style="text-align:center">PROYECCION VS RECUPERADO</div>
        <div class="card">
            <div class="card-header">FILTROS</div>
            <div class="card-body">
                <div class="row container contenedort">
                    <div class="col-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por Agencias</div>
                            <div class="card-body">
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
                                <div class="row">
                                    <div class="col-sm-12">
                                        <span class="input-group-addon col-2">Agencia</span>
                                        <select class="form-select" id="codofi" style="max-width: 70%;" disabled>
                                            <?php
                                            $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                                                                            ON ofi.id_agencia = usu.id_agencia GROUP BY ofi.id_agencia");
                                            while ($ofi = mysqli_fetch_array($ofis)) {
                                                echo '<option value="' . $ofi['id_agencia'] . '" selected>' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
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
                                                $fons = mysqli_query($conexion, "SELECT * FROM `ctb_fuente_fondos` where estado=1");
                                                while ($fon = mysqli_fetch_array($fons)) {
                                                    echo '<option value="' . $fon['id'] . '">' . $fon['descripcion'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <label class="text-primary" for="fondoid">Fondos</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row container contenedort">
                    <div class="col-6">
                        <div class="card" style="height: 100% !important;">
                            <div class="card-header"><b>Filtro por fecha</b></div>
                            <div class="list-group list-group-flush card-body ps-3">
                                <div class="row mb-1">
                                    <div class="col-6">
                                        <span class="input-group-addon">Desde:</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="input-group-addon">Hasta:</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="input-group">
                                            <input type="date" class="form-control" aria-describedby="basic-addon1" value="<?php echo date("Y-m-d"); ?>" id="fecinicio">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group">
                                            <input type="date" class="form-control" aria-describedby="basic-addon1" value="<?php echo date("Y-m-d"); ?>" id="fecfinal">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header"><b>Filtro de Ejecutivo</b></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rasesor" id="allasesor" value="allasesor" checked onclick="changedisabled(`#ejecutivo`,0)">
                                            <label for="allasesor" class="form-check-label">Consolidado </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rasesor" id="anyasesor" value="anyasesor" onclick="changedisabled(`#ejecutivo`,1)">
                                            <label for="anyasesor" class="form-check-label"> Por Ejecutivo</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <!-- radio button para los tipos de transacciones -->
                                    <div class="col d-flex justify-content-center">
                                        <select class="form-select" aria-label="Default select example" id="ejecutivo" disabled>
                                            <option selected value="0">Seleccione un ejecutivo</option>
                                            <?php
                                            $data = mysqli_query($conexion, "SELECT id_usu, CONCAT(nombre, ' ' ,apellido) AS nombre FROM tb_usuario WHERE estado=1 AND puesto='ANA'");
                                            while ($dato = mysqli_fetch_array($data, MYSQLI_ASSOC)) { ?>
                                                <option value="<?= $dato["id_usu"]; ?>"><?= $dato["nombre"] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row container contenedort">
                    <div class="col-12">
                        <div class="card" style="height: 100% !important;">
                            <div class="card-header"><b>Tipo Créditos</b></div>
                            <div class="card-body">
                                <div class="row mt-3">
                                    <div class="col d-flex justify-content-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="tipoentidad" id="ctodos" value="call" checked>
                                            <label class="form-check-label" for="ctodos">Todos</label>
                                        </div>
                                    </div>
                                    <div class="col d-flex justify-content-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="tipoentidad" id="cindi" value="INDI">
                                            <label class="form-check-label" for="cindi">Individuales</label>
                                        </div>
                                    </div>
                                    <div class="col d-flex justify-content-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="tipoentidad" id="cgrup" value="GRUP">
                                            <label class="form-check-label" for="cgrup">Grupales</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-danger" title="Cartera en pdf" onclick="reportes([[`fecinicio`,`fecfinal`],[`codofi`,`fondoid`,`ejecutivo`],[`ragencia`,`rfondos`,`rasesor`,`tipoentidad`],[]],`pdf`,`prepago_recuperado`,0)">
                            <i class="fa-solid fa-file-pdf"></i> Pdf
                        </button>
                        <button type="button" class="btn btn-outline-success" title="Cartera en Excel" onclick="reportes([[`fecinicio`,`fecfinal`],[`codofi`,`fondoid`,`ejecutivo`],[`ragencia`,`rfondos`,`rasesor`,`tipoentidad`],[]],`xlsx`,`prepago_recuperado`,1)">
                            <i class="fa-solid fa-file-excel"></i>Excel
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
<?php
        break;
}

?>