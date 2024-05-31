<?php
session_start();
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
date_default_timezone_set('America/Guatemala');
$idusuario = $_SESSION['id'];
$condi = $_POST["condi"];

switch ($condi) {
    case 'cartera_fuenteFondos':
?>
        <input type="text" id="file" value="reporte001" style="display: none;">
        <input type="text" id="condi" value="cartera_fuenteFondos" style="display: none;">
        <div class="text" style="text-align:center">SALDOS DE CARTERA</div>
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
                                            <input class="form-check-input" type="radio" name="ragencia" id="allofi" value="allofi" onclick="changedisabled(`#codofi`,0)" hidden>
                                            <!-- <label for="allofi" class="form-check-label">Consolidado </label> -->
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="ragencia" id="anyofi" value="anyofi" checked onclick="changedisabled(`#codofi`,1)">
                                            <label for="anyofi" class="form-check-label"> Por Agencia</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <span class="input-group-addon col-2">Agencia</span>


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
                                                    <select class="form-select" id="codofi" style="max-width: 98%;">
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
                                                    <select class="form-select" id="codofi" style="max-width: 98%;">
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

                                        <!-- <select class="form-select" id="codofi" style="max-width: 70%;" disabled>
                                            <?php
                                            // $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                            //                                     ON ofi.id_agencia = usu.id_agencia GROUP BY ofi.id_agencia");
                                            // while ($ofi = mysqli_fetch_array($ofis)) {
                                            //     echo '<option value="' . $ofi['id_agencia'] . '" selected>' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                            // }
                                            ?>
                                        </select> -->

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por Estados</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="status" id="allstatus" value="allstatus" checked>
                                            <label for="allstatus" class="form-check-label">Vigentes y Cancelados </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="status" id="F" value="F">
                                            <label for="F" class="form-check-label"> Vigentes</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="status" id="G" value="G">
                                            <label for="G" class="form-check-label"> Cancelados</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row container contenedort">
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
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">FECHA DE PROCESO</div>
                            <div class="card-body">
                                <div class="row" id="filfechas">
                                    <div class=" col-sm-6">
                                        <label for="ffin">Fecha</label>
                                        <input type="date" class="form-control" id="ffin" value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row container contenedort">
                    <div class="col-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por Asesor</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rasesor" id="allasesor" value="allasesor" checked onclick="changedisabled(`#codanal`,0)">
                                            <label for="allasesor" class="form-check-label">Consolidado </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rasesor" id="anyasesor" value="anyasesor" onclick="changedisabled(`#codanal`,1)">
                                            <label for="anyasesor" class="form-check-label"> Por Agencia</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <span class="input-group-addon col-2">Asesor</span>
                                        <select class="form-select" id="codanal" style="max-width: 100%;" disabled>
                                            <option value="0" disabled selected>Seleccione un asesor</option>
                                            <?php
                                            $anali = mysqli_query($conexion, "SELECT CONCAT(nombre, ' ', apellido) AS nameusu , id_usu FROM tb_usuario WHERE puesto='ANA'");
                                            while ($ofi = mysqli_fetch_array($anali)) {
                                                $nombre = $ofi["nameusu"];
                                                $id_usu = $ofi["id_usu"];
                                                echo '<option value="' . $id_usu . '">' . $nombre . '</option>';
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
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-danger" title="Cartera en pdf" onclick="reportes([[`ffin`],[`codofi`,`fondoid`,`codanal`],[`ragencia`,`rfondos`,`status`,`rasesor`],[<?php echo $idusuario; ?>]],`pdf`,`cartera_fondos`,0)">
                            <i class="fa-solid fa-file-pdf"></i> Pdf
                        </button>
                        <button type="button" class="btn btn-outline-success" title="Cartera en Excel" onclick="reportes([[`ffin`],[`codofi`,`fondoid`,`codanal`],[`ragencia`,`rfondos`,`status`,`rasesor`],[<?php echo $idusuario; ?>]],`xlsx`,`cartera_fondos`,1)">
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
    case 'ingresos':
        $puesto = $_SESSION['puesto'];
        $flag = ($puesto == 'ADM' || $puesto == 'GER') ? true : false;
    ?>
        <input type="text" id="file" value="reporte001" style="display: none;">
        <input type="text" id="condi" value="ingresos" style="display: none;">
        <div class="text" style="text-align:center">INGRESOS DIARIOS</div>
        <div class="card">
            <div class="card-header">FILTROS</div>
            <div class="card-body">
                <div class="row container contenedort" style="align-content: center;">
                    <div class="col-sm-12">
                        <div class="card text-bg-light" style="width:20rem;margin-left: auto; margin-right: auto;">
                            <div class="card-header">Filtro por Oficina</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <span class="input-group-addon col-2">Agencia</span>

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
                                                    <select class="form-select" id="codofi" style="max-width: 98%;">
                                                        <option value="0">Consolidado</option>
                                                        <?php
                                                        $where = ($flag) ? "" : " WHERE usu.id_usu=" . $idusuario . "";
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
                                                    <select class="form-select" id="codofi" style="max-width: 98%;">
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



                                        <!-- <select class="form-select" id="codofi"> -->
                                        <?php
                                        // $where=($flag)?"":" WHERE usu.id_usu=" . $idusuario . "";
                                        // $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                        //                     ON ofi.id_agencia = usu.id_agencia " . $where . " GROUP BY id_agencia;");
                                        // while ($ofi = mysqli_fetch_array($ofis)) {
                                        //     echo '<option value="' . $ofi['id_agencia'] . '" selected>' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                        // }
                                        ?>
                                        <!-- </select> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row container contenedort">
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por fechas</div>
                            <div class="card-body">
                                <div class="row" id="filfechas">
                                    <div class=" col-sm-6">
                                        <label for="finicio">Desde</label>
                                        <input type="date" class="form-control" id="finicio" min="1950-01-01" value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                    <div class=" col-sm-6">
                                        <label for="ffin">Hasta</label>
                                        <input type="date" class="form-control" id="ffin" min="1950-01-01" value="<?php echo date("Y-m-d"); ?>">
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
                <!--Botones-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-danger" title="Reporte de ingresos en pdf" onclick="reportes([[`finicio`,`ffin`],[`codofi`,`fondoid`],[`rfondos`],[<?php echo $idusuario; ?>]],`pdf`,`ingresos_diarios`,0)">
                            <i class="fa-solid fa-file-pdf"></i> Pdf
                        </button>
                        <button type="button" class="btn btn-outline-success" title="Reporte de ingresos en Excel" onclick="reportes([[`finicio`,`ffin`],[`codofi`,`fondoid`],[`rfondos`],[<?php echo $idusuario; ?>]],`xlsx`,`ingresos_diarios`,1)">
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
    case 'cartera_mora': {
            $id = $_POST["xtra"];
            $codusu = $_SESSION['id'];
            $agencia = $_SESSION['agencia'];
            $puesto = $_SESSION['puesto'];
            $flag = ($puesto == 'ADM' || $puesto == 'GER') ? true : false;
        ?>
            <!-- APR_05_LstdCntsActvsDspnbls -->
            <div class="text" style="text-align:center">REPORTE DE CARTERA EN MORA</div>
            <input type="text" value="cartera_mora" id="condi" style="display: none;">
            <input type="text" value="reporte001" id="file" style="display: none;">
            <div class="card">
                <div class="card-header">REPORTE DE CARTERA EN MORA</div>
                <div class="card-body">
                    <!-- segunda linea -->
                    <div class="row d-flex align-items-stretch mb-3">
                        <!-- card para seleccionar una cuenta -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro por fecha</b></div>
                                <div class="list-group list-group-flush card-body ps-3">
                                    <div class="row mb-1">
                                        <div class="col-12">
                                            <span class="input-group-addon">Ingrese una fecha:</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="input-group">
                                                <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?php echo date("Y-m-d"); ?>" id="fechaFinal">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- card para filtrar cuentas -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro de fuente de fondo</b></div>
                                <div class="card-body">
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_fuente" id="r_fuente" value="allf" checked onclick="activar_select_cuentas(this, true,'fondoid')">
                                                <label class="form-check-label" for="r_cuentas">Todo</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_fuente" id="r_fuente" value="anyf" onclick="activar_select_cuentas(this, false,'fondoid')">
                                                <label class="form-check-label" for="r_cuentas">Por fuente de Fondo</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col d-flex justify-content-center">
                                            <select class="form-select" id="fondoid" disabled>
                                                <option value="0">Seleccione un fuente de fondo</option>
                                                <?php
                                                $fons = mysqli_query($conexion, "SELECT * FROM `ctb_fuente_fondos` where estado=1");
                                                while ($fon = mysqli_fetch_array($fons)) {
                                                    echo '<option value="' . $fon['id'] . '">' . $fon['descripcion'] . '</option>';
                                                }
                                                ?>
                                            </select>
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
                                <div class="card-header"><b>Filtro de agencia</b></div>
                                <div class="card-body">
                                    <div class="row mt-2">
                                        <!-- --REQ--crediprendas--1--restrinccion de reportes -->
                                        <?php
                                        $query = "SELECT id_usu, puesto, id_agencia
                                        FROM tb_usuario
                                        WHERE id_usu = '$idusuario'";
                                        $resultado = mysqli_query($conexion, $query);

                                        if ($resultado) {
                                            $fila = mysqli_fetch_assoc($resultado);
                                            if ($fila) {
                                                $puestosP = array("ADM", "GER", "AUD");
                                        ?>
                                                <div class="col-12" <?= (in_array($fila['puesto'], $puestosP) && $flag) ? '' : 'style="display: none;"'; ?>>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="filter_agencia" id="r_agencia" value="allg" <?= ($flag) ? '' : 'disabled'; ?> onclick="activar_select_cuentas(this, true, 'agencia')">
                                                        <label class="form-check-label" for="r_cuentas">Todo</label>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-12">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="filter_agencia" id="r_agencia" value="anyg" <?= ($flag) ? 'checked' : ''; ?> checked onclick="activar_select_cuentas(this, false, 'agencia')">
                                                            <label class="form-check-label" for="r_cuentas">Por agencia</label>
                                                        </div>
                                                    </div>
                                                </div>
                                        <?php
                                            } else {
                                                echo "No se encontraron resultados para el usuario con ID: $idusuario";
                                            }
                                        } else {
                                            echo "Error en la consulta: " . mysqli_error($conexion);
                                        }
                                        ?>
                                        <div class="row mt-3">
                                            <!-- radio button para los tipos de transacciones -->
                                            <div class="col d-flex justify-content-center">

                                                <?php
                                                $query =  "SELECT id_usu, puesto, id_agencia
                                            FROM tb_usuario
                                            WHERE id_usu = '$idusuario'";
                                                $resultado = mysqli_query($conexion, $query);

                                                if ($resultado) {
                                                    $fila = mysqli_fetch_assoc($resultado);

                                                    if ($fila) {
                                                        $puestosP = array("ADM", "GER", "AUD");

                                                        if (in_array($fila['puesto'], $puestosP)) {
                                                ?>

                                                            <select class="form-select" aria-label="Default select example" id="agencia">
                                                                <option selected value="0">Seleccione una oficina</option>
                                                                <?php

                                                                $query = ($flag) ? "SELECT id_agencia,cod_agenc, CONCAT(nom_agencia,' - ',cod_agenc) AS nombre FROM tb_agencia" : "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia nombre FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                                                        ON ofi.id_agencia = usu.id_agencia WHERE usu.id_usu=" . $idusuario . "";
                                                                $data = mysqli_query($conexion, $query);
                                                                $selected = ($flag) ? '' : 'selected';
                                                                while ($dato = mysqli_fetch_array($data, MYSQLI_ASSOC)) { ?>
                                                                    <option value="<?= $dato["id_agencia"] ?>" <?= $selected ?>><?= $dato["nombre"] ?></option>

                                                                <?php } ?>
                                                            </select>
                                                            <?php
                                                        } else {
                                                            if ($fila) {
                                                                $id_agencia_usuario = $fila['id_agencia'];
                                                            ?>

                                                                <select class="form-select" aria-label="Default select example" id="agencia" <?php echo ($flag) ? '' : ''; ?>>
                                                                    <option selected disabled value="0">Seleccione una oficina</option>
                                                                    <?php
                                                                    $query = "SELECT id_agencia, cod_agenc, CONCAT(nom_agencia,' - ',cod_agenc) AS nombre FROM tb_agencia WHERE id_agencia = $id_agencia_usuario";
                                                                    $data = mysqli_query($conexion, $query);
                                                                    $selected = ($flag) ? '' : 'selected';
                                                                    while ($dato = mysqli_fetch_array($data, MYSQLI_ASSOC)) { ?>
                                                                        <option value="<?= $dato["id_agencia"] ?>" <?= $selected ?>><?= $dato["nombre"] ?></option>
                                                                    <?php } ?>
                                                                </select>

                                                <?php
                                                            }
                                                        }
                                                    } else {
                                                        echo "No se encontraron resultados para el usuario con ID: $idusuario";
                                                    }
                                                } else {
                                                    echo "Error en la consulta: " . mysqli_error($conexion);
                                                }

                                                ?>





                                                <!-- $ofis = mysqli_query($conexion, "");
                                                while ($ofi = mysqli_fetch_array($ofis)) {
                                                echo '<option value="' . $ofi['id_agencia'] . '" selected>' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                                }

                                            </select> -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Botones -->
                        <div class="row justify-items-md-center mt-3">
                            <div class="col align-items-center" id="modal_footer">
                                <!-- en el metodo onclick se envian usuario y oficina para saber las cuentas de agencia a generar -->
                                <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[`fechaFinal`],[`fondoid`,`agencia`],[`filter_fuente`,`filter_agencia`],[<?php echo $codusu; ?>]],`xlsx`,`cartera_en_mora`,1)">
                                    <i class="fa-solid fa-file-excel"></i> Reporte en Excel
                                </button>
                                <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="reportes([[`fechaFinal`],[`fondoid`,`agencia`],[`filter_fuente`,`filter_agencia`],[<?php echo $codusu; ?>]],`pdf`,`cartera_en_mora`,0)">
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

    case 'salconsolida':
            ?>
            <input type="text" id="file" value="reporte001" style="display: none;">
            <input type="text" id="condi" value="salconsolida" style="display: none;">
            <div class="text" style="text-align:center">CARTERA GENERAL</div>
            <div class="card">
                <div class="card-header">FILTROS</div>
                <div class="card-body">
                    <div class="row container contenedort" style="display: none;">
                        <div class="col-sm-6">
                            <div class="card text-bg-light" style="height: 100%;">
                                <div class="card-header">Filtro por Oficina</div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <span class="input-group-addon col-2">Agencia</span>


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
                                                        <select class="form-select" id="codofi" style="max-width: 98%;">
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
                                                        <select class="form-select" id="codofi" style="max-width: 98%;">
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
                                            <!-- <select class="form-select" id="codofi" style="max-width: 70%;">
                                            
                                            $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                                                        ON ofi.id_agencia = usu.id_agencia WHERE usu.id_usu=" . $idusuario . "");
                                            while ($ofi = mysqli_fetch_array($ofis)) {
                                                echo '<option value="' . $ofi['id_agencia'] . '" selected>' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                            }
                                        </select> -->

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row container contenedort">
                        <div class="col-sm-6">
                            <div class="card text-bg-light" style="height: 100%;">
                                <div class="card-header">FECHA DE PROCESO</div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-sm-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="rfechas" id="ftodo" value="actual" checked onclick="changedisabled(`#filfechas *`,0)">
                                                <label for="ftodo" class="form-check-label">A la fecha </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="rfechas" id="frango" value="afecha" onclick="changedisabled(`#filfechas *`,1)">
                                                <label for="frango" class="form-check-label">Ingresar fecha</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="filfechas">
                                        <div class=" col-sm-6">
                                            <label for="ffin">A la fecha</label>
                                            <input type="date" class="form-control" id="ffin" disabled value="<?php echo date("Y-m-d"); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--Botones-->
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center">
                            <button type="button" class="btn btn-outline-danger" title="Cartera en pdf" onclick="reportes([[`ffin`],[`codofi`],[`rfechas`],[<?php echo $idusuario; ?>]],`pdf`,`saldo_cartera`,1)">
                                <i class="fa-solid fa-file-pdf"></i> Pdf
                            </button>
                            <button type="button" class="btn btn-outline-success" title="Cartera en Excel" onclick="reportes([[`ffin`],[`codofi`],[`rfechas`],[<?php echo $idusuario; ?>]],`xlsx`,`saldo_cartera`,1)">
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

            /* NEGROY NUEVOS REPORTES MAMONES */
        case 'mora2_filtro': {
                $id = $_POST["xtra"];
                $codusu = $_SESSION['id'];
                $agencia = $_SESSION['agencia'];
                $puesto = $_SESSION['puesto'];
                $flag = ($puesto == 'ADM' || $puesto == 'GER') ? true : false;
            ?>
                <!-- APR_05_LstdCntsActvsDspnbls -->
                <div class="text" style="text-align:center">REPORTE DE CARTERA EN MORA (Autoasignada)</div>
                <input type="text" value="cartera_mora" id="condi" style="display: none;">
                <input type="text" value="reporte001" id="file" style="display: none;">
                <div class="card">
                    <div class="card-header">REPORTE DE CARTERA EN MORA (Autoasignada)</div>
                    <div class="card-body"> <!-- segunda linea -->
                        <div class="row d-flex align-items-stretch mb-3"> <!-- card para seleccionar una cuenta -->
                            <div class="col-6">
                                <div class="card" style="height: 100% !important;">
                                    <div class="card-header"><b>Filtro por fecha</b></div>
                                    <div class="list-group list-group-flush card-body ps-3">
                                        <div class="row mb-1">
                                            <div class="col-12"> <span class="input-group-addon">Ingrese una fecha:</span> </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="input-group">
                                                    <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?= date("Y-m-d"); ?>" id="fechaFinal">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- card para filtrar cuentas -->
                            <div class="col-6">
                                <div class="card" style="height: 100% !important;">
                                    <div class="card-header"><b>Filtro de fuente de fondo</b></div>
                                    <div class="card-body">

                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="filter_fuente" id="r_fuente" value="allf" checked onclick="activar_select_cuentas(this, true,'fondoid')"> <label class="form-check-label" for="r_cuentas">Todo</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="filter_fuente" id="r_fuente" value="anyf" onclick="activar_select_cuentas(this, false,'fondoid')"> <label class="form-check-label" for="r_cuentas">Por fuente de Fondo</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3"> <!--Seleccione un fuente  -->
                                            <div class="col d-flex justify-content-center">
                                                <select class="form-select" id="fondoid" disabled>
                                                    <option value="0">Seleccione un fuente de fondo</option>
                                                    <?php $fons = mysqli_query($conexion, "SELECT * FROM `ctb_fuente_fondos` where estado=1");
                                                    while ($fon = mysqli_fetch_array($fons)) {
                                                        echo '<option value="' . $fon['id'] . '">' . $fon['descripcion'] . '</option>';
                                                    } ?>
                                                </select>
                                            </div>
                                        </div> <!--Seleccione un fuente  -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Card para las transacciones -->
                        <div class="row mb-2">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header"><b>Filtro de agencia</b></div>
                                    <div class="card-body">

                                        <div class="d-none">
                                            <input class="form-check-input" type="radio" name="filter_agencia" id="r_agencia" value="F0" checked>
                                        </div><!--  TODOS  filter_agencia -->

                                        <div class="row mt-3"> <!-- AGENCIA -->
                                            <div class="col d-flex justify-content-center">
                                                <select class="form-select" disabled id="agencia">
                                                    <option selected disabled value="0">Seleccione una oficina</option>
                                                    <option value="<?= $agencia ?>" selected> <?= $agencia ?></option>
                                                </select>
                                            </div>
                                        </div> <!-- AGENCIA -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Botones -->
                        <div class="row justify-items-md-center mt-3">
                            <div class="col align-items-center" id="modal_footer">
                                <!-- en el metodo onclick se envian usuario y oficina para saber las cuentas de agencia a generar -->
                                <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[`fechaFinal`],[`fondoid`,`agencia`],[`filter_fuente`,`filter_agencia`],[<?= $codusu; ?>]],`xlsx`,`cartera_en_mora`,1)"> <i class="fa-solid fa-file-excel"></i> Reporte en Excel </button>

                                <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="reportes([[`fechaFinal`],[`fondoid`,`agencia`],[`filter_fuente`,`filter_agencia`],[<?= $codusu; ?>]],`pdf`,`cartera_en_mora`,0)"> <i class="fa-solid fa-file-pdf"></i> Reporte en PDF </button>

                                <button type="button" class="btn btn-outline-danger" onclick="printdiv2('#cuadro','0')"> <i class="fa-solid fa-ban"></i> Cancelar </button>

                                <button type="button" class="btn btn-outline-warning" onclick="salir()"> <i class="fa-solid fa-circle-xmark"></i> Salir </button>
                            </div>
                        </div>
                    </div>
                </div>
    <?php
            }
            break;
    }



    ?>