<?php
session_start();

use PhpOffice\PhpSpreadsheet\Calculation\Logical\Conditional;

include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$idusuario = $_SESSION['id'];
$condi = $_POST["condi"];

switch ($condi) {
    case 'libdiario':
?>
        <!--AHO-4-Clclintrs Cuenta Ahorros-->
        <input type="text" id="file" value="ctb004" style="display: none;">
        <input type="text" id="condi" value="libdiario" style="display: none;">
        <div class="text" style="text-align:center">GENERACION DE LIBRO DIARIO</div>
        <div class="card">
            <div class="card-header">FILTROS</div>
            <div class="card-body">
                <div class="row container contenedort">
                    <div class="col-sm-6">
                        <div class="row">
                            <?php

                            //   <!-- --REQ--crediprendas--1--restrinccion de reportes -->
                            $query = "SELECT id_usu, puesto, id_agencia
                                FROM tb_usuario
                                WHERE id_usu = '$idusuario'";
                            $resultado = mysqli_query($conexion, $query);

                            $puestosP = array("ADM", "GER", "AUD", "CNT");
                            if ($resultado) {
                                $fila = mysqli_fetch_assoc($resultado);

                                // Verificar si la fila existe 
                                $mostrarTodo = ($fila && in_array($fila['puesto'], $puestosP));
                            ?>
                                <div class="col-sm-12">
                                    <?php if ($mostrarTodo) : ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="ragencia" id="allofi" value="allofi" onclick="changedisabled(`#codofi`,0)">
                                            <label for="allofi" class="form-check-label">Consolidado</label>
                                        </div>
                                    <?php endif; ?>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="ragencia" id="anyofi" value="anyofi" checked onclick="changedisabled(`#codofi`,1)">
                                        <label for="anyofi" class="form-check-label"> Por Agencia</label>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>

                        </div>
                    </div>
                    <div class="col-sm-6">
                        <span class="input-group-addon col-2">Agencia</span>



                        <?php
                        $sql = "SELECT id_usu, puesto, id_agencia
                                     FROM tb_usuario
                                     WHERE id_usu = '$idusuario'";
                        $resultado = mysqli_query($conexion, $sql);

                        if ($resultado) {
                            $fila = mysqli_fetch_assoc($resultado);

                            if ($fila) {
                                $puestosP = array("ADM", "GER", "AUD", "CNT");

                                if (in_array($fila['puesto'], $puestosP)) {
                                    //permisos v
                        ?>
                                    <select class="form-select" id="codofi" style="max-width: 70%;">
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
                                    //caso contario
                                ?>

                                    <select class="form-select" id="codofi" style="max-width: 70%;">
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


                        <!-- codigo anterior -->
                        <!-- <select class="form-select" id="codofi" style="max-width: 70%;" disabled> -->
                        <?php
                        /* $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                                    ON ofi.id_agencia = usu.id_agencia WHERE usu.id_usu=" . $idusuario . ""); */
                        // $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                        // ON ofi.id_agencia = usu.id_agencia GROUP BY ofi.id_agencia");
                        // while ($ofi = mysqli_fetch_array($ofis)) {
                        //     echo '<option value="' . $ofi['id_agencia'] . '" selected>' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                        // }
                        ?>
                        <!-- </select> -->

                    </div>
                    <div class="col-sm-6 g-4" style="display:none;">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="radio" role="switch" name="rtipo" id="c" value="c" onclick="">
                            <input style="display: none;" class="form-check-input" type="radio" role="switch" name="rtipo" id="n" value="n" checked>
                            <label class="form-check-label" for="c">Libro Diario Concentrado</label>
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
                            <div class="card-header">Filtro por fechas</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rfechas" id="ftodo" value="ftodo" checked onclick="changedisabled(`#filfechas *`,0)">
                                            <label for="ftodo" class="form-check-label">Todo</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rfechas" id="frango" value="frango" onclick="changedisabled(`#filfechas *`,1)">
                                            <label for="frango" class="form-check-label">Rango</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="filfechas">
                                    <div class=" col-sm-6">
                                        <label for="finicio">Desde</label>
                                        <input type="date" class="form-control" id="finicio" min="1950-01-01" disabled value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                    <div class=" col-sm-6">
                                        <label for="ffin">Hasta</label>
                                        <input type="date" class="form-control" id="ffin" min="1950-01-01" disabled value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Botones-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-danger" title="Libro Diario en pdf" onclick="reportes([[`finicio`,`ffin`],[`codofi`,`fondoid`],[`rtipo`,`rfondos`,`rfechas`,`ragencia`],[<?php echo $idusuario; ?>]],`pdf`,`libro_diario`,0)">
                            <i class="fa-solid fa-file-pdf"></i> Pdf
                        </button>
                        <button type="button" class="btn btn-outline-success" title="Libro Diario en Excel" onclick="reportes([[`finicio`,`ffin`],[`codofi`,`fondoid`],[`rtipo`,`rfondos`,`rfechas`,`ragencia`],[<?php echo $idusuario; ?>]],`xlsx`,`libro_diario`,1)">
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
    case 'libmayor':
    ?>
        <input type="text" id="file" value="ctb004" style="display: none;">
        <input type="text" id="condi" value="libmayor" style="display: none;">
        <div class="text" style="text-align:center">GENERACION DE LIBRO MAYOR</div>
        <div class="card">
            <div class="card-header">FILTROS</div>
            <div class="card-body">
                <div class="row container contenedort">
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por Oficina</div>
                            <div class="card-body">
                                <div class="row">
                                    <?php

                                    //   <!-- --REQ--crediprendas--1--restrinccion de reportes -->
                                    $query = "SELECT id_usu, puesto, id_agencia
          FROM tb_usuario
          WHERE id_usu = '$idusuario'";
                                    $resultado = mysqli_query($conexion, $query);

                                    $puestosP = array("ADM", "GER", "AUD", "CNT");
                                    if ($resultado) {
                                        $fila = mysqli_fetch_assoc($resultado);

                                        // Verificar si la fila existe 
                                        $mostrarTodo = ($fila && in_array($fila['puesto'], $puestosP));
                                    ?>
                                        <div class="col-sm-12">
                                            <?php if ($mostrarTodo) : ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="ragencia" id="allofi" value="allofi" onclick="changedisabled(`#codofi`,0)">
                                                    <label for="allofi" class="form-check-label">Consolidado</label>
                                                </div>
                                            <?php endif; ?>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="ragencia" id="anyofi" value="anyofi" checked onclick="changedisabled(`#codofi`,1)">
                                                <label for="anyofi" class="form-check-label"> Por Agencia.</label>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>


                                    <div class="col-sm-12">
                                        <span class="input-group-addon col-2">Agencia</span>

                                        <?php
                                        $sql = "SELECT id_usu, puesto, id_agencia
                                     FROM tb_usuario
                                     WHERE id_usu = '$idusuario'";
                                        $resultado = mysqli_query($conexion, $sql);

                                        if ($resultado) {
                                            $fila = mysqli_fetch_assoc($resultado);

                                            if ($fila) {
                                                $puestosP = array("ADM", "GER", "AUD", "CNT");

                                                if (in_array($fila['puesto'], $puestosP)) {
                                                    //permisos v
                                        ?>
                                                    <select class="form-select" id="codofi" style="max-width: 70%;">
                                                        <?php
                                                        /* $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                                                ON ofi.id_agencia = usu.id_agencia WHERE usu.id_usu=" . $idusuario . ""); */
                                                        $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                                                ON ofi.id_agencia = usu.id_agencia GROUP BY ofi.id_agencia");
                                                        while ($ofi = mysqli_fetch_array($ofis)) {
                                                            echo '<option value="' . $ofi['id_agencia'] . '">' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>

                                                <?php
                                                } else {
                                                    //caso contario
                                                ?>
                                                    <select class="form-select" id="codofi" style="max-width: 70%;">
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
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por Cuentas</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rcuentas" id="allcuen" value="allcuen" checked onclick="changedisabled(`#btncuenid`,0)">
                                            <label for="allcuen" class="form-check-label">Todo </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rcuentas" id="anycuen" value="anycuen" onclick="changedisabled(`#btncuenid`,1)">
                                            <label for="anycuen" class="form-check-label"> Una cuenta</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-floating mb-3">
                                            <div class="input-group" style="width:min(70%,32rem);">
                                                <input style="display:none;" type="text" class="form-control" id="idcuenta" value="0">
                                                <input type="text" disabled readonly class="form-control" id="cuenta">
                                                <button disabled id="btncuenid" class="btn btn-outline-success" type="button" onclick="abrir_modal(`#modal_nomenclatura`, `show`, `#id_modal_hidden`, `idcuenta,cuenta`)" title="Buscar Cuenta contable"><i class="fa fa-magnifying-glass"></i></button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por fechas</div>
                            <div class="card-body">
                                <!-- <div class="row" style="display: none;">
                                    <div class="col-sm-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rfechas" id="ftodo" value="ftodo" onclick="changedisabled(`#filfechas *`,0)">
                                            <label for="ftodo" class="form-check-label">Todo</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rfechas" id="frango" value="frango" checked onclick="changedisabled(`#filfechas *`,1)">
                                            <label for="frango" class="form-check-label">Rango</label>
                                        </div>
                                    </div>
                                </div> -->
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
                </div>
                <!--Botones-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-danger" title="Libro Mayor en pdf" onclick="reportes([[`finicio`,`ffin`,`idcuenta`,`cuenta`],[`codofi`,`fondoid`],[`rcuentas`,`rfondos`,`ragencia`],[<?php echo $idusuario; ?>]],`pdf`,`libro_mayor`,0)">
                            <i class="fa-solid fa-file-pdf"></i> Pdf
                        </button>
                        <button type="button" class="btn btn-outline-success" title="Libro Mayor en Excel" onclick="reportes([[`finicio`,`ffin`,`idcuenta`,`cuenta`],[`codofi`,`fondoid`],[`rcuentas`,`rfondos`,`ragencia`],[<?php echo $idusuario; ?>]],`xlsx`,`libro_mayor`,1)">
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
    case 'libcaja':
    ?>
        <input type="text" id="file" value="ctb004" style="display: none;">
        <input type="text" id="condi" value="libcaja" style="display: none;">
        <div class="text" style="text-align:center">GENERACION DE LIBRO CAJA</div>
        <div class="card">
            <div class="card-header">FILTROS</div>
            <div class="card-body">
                <div class="row container contenedort">
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por Oficina</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <span class="input-group-addon col-2">Agencia</span>
                                        <select class="form-select" id="codofi" style="max-width: 70%;">
                                        <option value="0" selected>Consolidado</option>
                                            <?php
                                            // $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                            //                             ON ofi.id_agencia = usu.id_agencia WHERE usu.id_usu=" . $idusuario . "");
                                            $ofis = mysqli_query($conexion, "SELECT id_agencia,cod_agenc,nom_agencia FROM tb_agencia");
                                            while ($ofi = mysqli_fetch_array($ofis)) {
                                                echo '<option value="' . $ofi['id_agencia'] . '" >' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                            }
                                            ?>
                                        </select>
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
                </div>
                <!--Botones-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-danger" title="Libro Caja en pdf" onclick="reportes([[`finicio`,`ffin`],[`codofi`,`fondoid`],[`rfondos`],[<?php echo $idusuario; ?>]],`pdf`,`libro_caja`,0)">
                            <i class="fa-solid fa-file-pdf"></i> Pdf
                        </button>
                        <button type="button" class="btn btn-outline-success" title="Libro Caja en Excel" onclick="reportes([[`finicio`,`ffin`],[`codofi`,`fondoid`],[`rfondos`],[<?php echo $idusuario; ?>]],`xlsx`,`libro_caja`,1)">
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
    case 'balcomprobacion':
    ?>
        <input type="text" id="file" value="ctb004" style="display: none;">
        <input type="text" id="condi" value="balcomprobacion" style="display: none;">
        <div class="text" style="text-align:center">BALANCE DE COMPROBACION</div>
        <div class="card">
            <div class="card-header">FILTROS</div>
            <div class="card-body">
                <div class="row container contenedort" style="display: none;">
                    <div class="col-sm-12">
                        <div class="card text-bg-light" style="width:20rem;margin-left: auto; margin-right: auto;">
                            <div class="card-header">Filtro por Oficina</div>
                            <div class="card-body">
                                <div class="row">
                                    <div>
                                        <span class="input-group-addon col-2">Agencia</span>
                                        <select class="form-select" id="codofi">
                                            <?php
                                            $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                                        ON ofi.id_agencia = usu.id_agencia WHERE usu.id_usu=" . $idusuario . "");
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
                            <div class="card-header">Filtro por fechas</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rfechas" id="ftodo" value="ftodo" checked onclick="changedisabled(`#filfechas *`,0)">
                                            <label for="ftodo" class="form-check-label">Todo</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rfechas" id="frango" value="frango" onclick="changedisabled(`#filfechas *`,1)">
                                            <label for="frango" class="form-check-label">Rango</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="filfechas">
                                    <div class=" col-sm-6">
                                        <label for="finicio">Desde</label>
                                        <input type="date" class="form-control" id="finicio" min="1950-01-01" disabled value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                    <div class=" col-sm-6">
                                        <label for="ffin">Hasta</label>
                                        <input type="date" class="form-control" id="ffin" min="1950-01-01" disabled value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Botones-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-danger" title="Balance de comprobacion en pdf" onclick="reportes([[`finicio`,`ffin`],[`codofi`,`fondoid`],[`rfondos`,`rfechas`],[<?php echo $idusuario; ?>]],`pdf`,`balancecom`,0)">
                            <i class="fa-solid fa-file-pdf"></i> Pdf
                        </button>
                        <button type="button" class="btn btn-outline-success" title="Balance de comprobacion en Excel" onclick="reportes([[`finicio`,`ffin`],[`codofi`,`fondoid`],[`rfondos`,`rfechas`],[<?php echo $idusuario; ?>]],`xlsx`,`balancecom`,1)">
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
    case 'balgen':
        //BALANCE GENERAL
    ?>
        <input type="text" id="file" value="ctb004" style="display: none;">
        <input type="text" id="condi" value="balgen" style="display: none;">
        <div class="text" style="text-align:center">GENERACION DE BALANCE GENERAL</div>
        <div class="card">
            <div class="card-header">FILTROS</div>
            <div class="card-body">
                <div class="row container contenedort">
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por Oficina</div>
                            <div class="card-body">
                                <div class="row">
                                    <?php

                                    //   <!-- --REQ--crediprendas--1--restrinccion de reportes -->
                                    $query = "SELECT id_usu, puesto, id_agencia
          FROM tb_usuario
          WHERE id_usu = '$idusuario'";
                                    $resultado = mysqli_query($conexion, $query);

                                    $puestosP = array("ADM", "GER", "AUD", "CNT");
                                    if ($resultado) {
                                        $fila = mysqli_fetch_assoc($resultado);

                                        // Verificar si la fila existe 
                                        $mostrarTodo = ($fila && in_array($fila['puesto'], $puestosP));
                                    ?>
                                        <div class="col-sm-12">
                                            <?php if ($mostrarTodo) : ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="ragencia" id="allofi" value="allofi" onclick="changedisabled(`#codofi`,0)">
                                                    <label for="allofi" class="form-check-label">Consolidado</label>
                                                </div>
                                            <?php endif; ?>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="ragencia" id="anyofi" value="anyofi" checked onclick="changedisabled(`#codofi`,1)">
                                                <label for="anyofi" class="form-check-label"> Por Agencia.</label>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>


                                    <div class="col-sm-12">
                                        <span class="input-group-addon col-2">Agencia</span>


                                        <?php
                                        $sql = "SELECT id_usu, puesto, id_agencia
                                     FROM tb_usuario
                                     WHERE id_usu = '$idusuario'";
                                        $resultado = mysqli_query($conexion, $sql);

                                        if ($resultado) {
                                            $fila = mysqli_fetch_assoc($resultado);

                                            if ($fila) {
                                                $puestosP = array("ADM", "GER", "AUD", "CNT");

                                                if (in_array($fila['puesto'], $puestosP)) {
                                                    //permisos v
                                        ?>
                                                    <select class="form-select" id="codofi" style="max-width: 70%;">
                                                        <?php
                                                        /* $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                                                ON ofi.id_agencia = usu.id_agencia WHERE usu.id_usu=" . $idusuario . ""); */
                                                        $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                                                ON ofi.id_agencia = usu.id_agencia GROUP BY ofi.id_agencia");
                                                        while ($ofi = mysqli_fetch_array($ofis)) {
                                                            echo '<option value="' . $ofi['id_agencia'] . '">' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>

                                                <?php
                                                } else {
                                                    //caso contario
                                                ?>
                                                    <select class="form-select" id="codofi" style="max-width: 70%;">
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



                                        <!-- <select class="form-select" id="codofi" disabled>
                                            <?php
                                            // $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                            //     ON ofi.id_agencia = usu.id_agencia GROUP BY ofi.id_agencia");
                                            // while ($ofi = mysqli_fetch_array($ofis)) {
                                            //     echo '<option value="' . $ofi['id_agencia'] . '">' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                            // }
                                            ?>
                                        </select> -->


                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">NIVELES</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="nivelinit">
                                                <option value="1">Nivel 1</option>
                                                <option value="2">Nivel 2</option>
                                                <option value="3">Nivel 3</option>
                                                <option value="4">Nivel 4</option>
                                                <option value="5">Nivel 5</option>
                                                <!-- <option value="6">Nivel 6</option> -->
                                            </select>
                                            <label class="text-primary" for="fondoid">INICIO</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="nivelfin">
                                                <option value="1">Nivel 1</option>
                                                <option value="2">Nivel 2</option>
                                                <option value="3">Nivel 3</option>
                                                <option value="4">Nivel 4</option>
                                                <option value="5" selected>Nivel 5</option>
                                                <!-- <option value="6" selected>Nivel 6</option> -->
                                            </select>
                                            <label class="text-primary" for="fondoid">FIN</label>
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
                            <div class="card-header">Filtro por fechas</div>
                            <div class="card-body">
                                <!-- <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rfechas" id="ftodo" value="ftodo" checked onclick="changedisabled(`#filfechas *`,0)">
                                            <label for="ftodo" class="form-check-label">Todo</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rfechas" id="frango" value="frango" onclick="changedisabled(`#filfechas *`,1)">
                                            <label for="frango" class="form-check-label">Rango</label>
                                        </div>
                                    </div>
                                </div> -->
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
                </div>
                <!--Botones-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-danger" title="Balance General en pdf" onclick="reportes([[`finicio`,`ffin`],[`codofi`,`fondoid`,`nivelinit`,`nivelfin`],[`rfondos`,`ragencia`],[<?php echo $idusuario; ?>]],`pdf`,`balancegen`,0)">
                            <i class="fa-solid fa-file-pdf"></i> Pdf
                        </button>
                        <button type="button" class="btn btn-outline-success" title="Balance General en Excel" onclick="reportes([[`finicio`,`ffin`],[`codofi`,`fondoid`,`nivelinit`,`nivelfin`],[`rfondos`,`ragencia`],[<?php echo $idusuario; ?>]],`xlsx`,`balancegen`,1)">
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
    case 'estresul':
    ?>
        <input type="text" id="file" value="ctb004" style="display: none;">
        <input type="text" id="condi" value="estresul" style="display: none;">
        <div class="text" style="text-align:center">ESTADO DE RESULTADOS</div>
        <div class="card">
            <div class="card-header">FILTROS</div>
            <div class="card-body">
                <div class="row container contenedort">
                    <div class="col-sm-12">
                        <div class="card text-bg-light" style="width:20rem;margin-left: auto; margin-right: auto;">
                            <div class="card-header">Filtro por Oficina</div>
                            <div class="card-body">
                                <div class="row">
                                    <?php

                                    //   <!-- --REQ--crediprendas--1--restrinccion de reportes -->
                                    $query = "SELECT id_usu, puesto, id_agencia
                                            FROM tb_usuario
                                            WHERE id_usu = '$idusuario'";
                                    $resultado = mysqli_query($conexion, $query);

                                    $puestosP = array("ADM", "GER", "AUD", "CNT");
                                    if ($resultado) {
                                        $fila = mysqli_fetch_assoc($resultado);

                                        // Verificar si la fila existe 
                                        $mostrarTodo = ($fila && in_array($fila['puesto'], $puestosP));
                                    ?>
                                        <div class="col-sm-12">
                                            <?php if ($mostrarTodo) : ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="ragencia" id="allofi" value="allofi" onclick="changedisabled(`#codofi`,0)">
                                                    <label for="allofi" class="form-check-label">Consolidado</label>
                                                </div>
                                            <?php endif; ?>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="ragencia" id="anyofi" value="anyofi" checked onclick="changedisabled(`#codofi`,1)">
                                                <label for="anyofi" class="form-check-label"> Por Agencia.</label>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                    <div class="col-sm-12">
                                        <span class="input-group-addon col-2">Agencia</span>


                                        <?php
                                        $sql = "SELECT id_usu, puesto, id_agencia
                                     FROM tb_usuario
                                     WHERE id_usu = '$idusuario'";
                                        $resultado = mysqli_query($conexion, $sql);

                                        if ($resultado) {
                                            $fila = mysqli_fetch_assoc($resultado);

                                            if ($fila) {
                                                $puestosP = array("ADM", "GER", "AUD", "CNT");

                                                if (in_array($fila['puesto'], $puestosP)) {
                                                    //permisos v
                                        ?>
                                                    <select class="form-select" id="codofi" disabled>
                                                        <?php
                                                        $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                                ON ofi.id_agencia = usu.id_agencia GROUP BY ofi.id_agencia");
                                                        while ($ofi = mysqli_fetch_array($ofis)) {
                                                            echo '<option value="' . $ofi['id_agencia'] . '">' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                <?php
                                                } else {
                                                    //caso contario
                                                ?>
                                                    <select class="form-select" id="codofi" style="max-width: 70%;">
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
                                            echo "Error en la consulta:OFIS2 " . mysqli_error($conexion);
                                        }

                                        ?>
                                        <!-- CODIGO ANTERIOR-->
                                        <!-- <select class="form-select" id="codofi" disabled>
                                            <?php
                                            // $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                            //     ON ofi.id_agencia = usu.id_agencia GROUP BY ofi.id_agencia");
                                            // while ($ofi = mysqli_fetch_array($ofis)) {
                                            //     echo '<option value="' . $ofi['id_agencia'] . '">' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                            // }
                                            ?>
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
                            <div class="card-header">Filtro por fechas</div>
                            <div class="card-body">
                                <!-- <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rfechas" id="ftodo" value="ftodo" checked onclick="changedisabled(`#filfechas *`,0)">
                                            <label for="ftodo" class="form-check-label">Todo</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rfechas" id="frango" value="frango" onclick="changedisabled(`#filfechas *`,1)">
                                            <label for="frango" class="form-check-label">Rango</label>
                                        </div>
                                    </div>
                                </div> -->
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
                </div>
                <!--Botones-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-danger" title="Estado de resultados en pdf" onclick="reportes([[`finicio`,`ffin`],[`codofi`,`fondoid`],[`rfondos`,`ragencia`],[<?php echo $idusuario; ?>]],`pdf`,`estadoresul`,0)">
                            <i class="fa-solid fa-file-pdf"></i> Pdf
                        </button>
                        <button type="button" class="btn btn-outline-success" title="Estado de resultados en Excel" onclick="reportes([[`finicio`,`ffin`],[`codofi`,`fondoid`],[`rfondos`,`ragencia`],[<?php echo $idusuario; ?>]],`xlsx`,`estadoresul`,1)">
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
    case 'CatalogoCuentas': {
            $id = $_POST["xtra"];
            $usuario = "4";
            $ofi = "002";
        ?>
            <!-- APR_05_LstdCntsActvsDspnbls -->
            <div class="text" style="text-align:center">CATALOGO DE CUENTAS</div>
            <input type="text" value="CatalogoCuentas" id="condi" style="display: none;">
            <input type="text" value="ctb004" id="file" style="display: none;">

            <div class="card">
                <div class="card-header">Catalogo de Cuentas</div>
                <div class="card-body">
                    <!-- segunda linea -->
                    <div class="row d-flex align-items-stretch mb-3">
                        <!-- card para filtrar cuentas -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro de clases</b></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col d-flex justify-content-center">
                                            <select class="form-select" id="clase" aria-label="Default select example">
                                                <option selected value="0">Todos</option>
                                                <option value="1">1 - Activo</option>
                                                <option value="2">2 - Cuentas regulizadoras de activo</option>
                                                <option value="3">3 - Pasivo</option>
                                                <option value="4">4 - Otras cuentas acreedoras</option>
                                                <option value="5">5 - Capital contable</option>
                                                <option value="6">6 - Productos</option>
                                                <option value="7">7 - Gastos</option>
                                                <option value="8">8 - Contingencias</option>
                                                <option value="9">9 - Cuentas de orden</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- card para seleccionar una cuenta -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtrar de niveles</b></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <select class="form-select" id="nivel" aria-label="Default select example">
                                                <option selected value="0">Todos</option>
                                                <option value="1">Nivel 1</option>
                                                <option value="3">Nivel 2</option>
                                                <option value="4">Nivel 3</option>
                                                <option value="6">Nivel 4</option>
                                                <option value="8">Nivel 5</option>
                                                <option value="10">Nivel 6</option>
                                                <option value="12">Nivel 7</option>
                                                <option value="14">Nivel 8</option>
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
                            <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[],['clase','nivel'],[],[<?php echo $usuario; ?>,'<?php echo $ofi; ?>']], 'xlsx', 'catalogo_cuentas', 1)">
                                <i class="fa-solid fa-file-excel"></i> Reporte en Excel
                            </button>

                            <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="reportes([[],['clase','nivel'],[],['<?php echo $usuario; ?>','<?php echo $ofi; ?>']], 'pdf', 'catalogo_cuentas', 0)">
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
    case 'patrimonio':
        ?>
        <input type="text" id="file" value="ctb004" style="display: none;">
        <input type="text" id="condi" value="patrimonio" style="display: none;">
        <div class="text" style="text-align:center">ESTADO DE CAMBIOS EN EL PATRIMONIO</div>
        <div class="card">
            <div class="card-header">FILTROS</div>
            <div class="card-body">
                <div class="row container contenedort">
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por Oficina</div>
                            <div class="card-body">
                                <div class="row">
                                    <?php

                                    //   <!-- --REQ--crediprendas--1--restrinccion de reportes -->
                                    $query = "SELECT id_usu, puesto, id_agencia
                                        FROM tb_usuario
                                        WHERE id_usu = '$idusuario'";
                                    $resultado = mysqli_query($conexion, $query);

                                    $puestosP = array("ADM", "GER", "AUD", "CNT");
                                    if ($resultado) {
                                        $fila = mysqli_fetch_assoc($resultado);

                                        // Verificar si la fila existe 
                                        $mostrarTodo = ($fila && in_array($fila['puesto'], $puestosP));
                                    ?>
                                        <div class="col-sm-12">
                                            <?php if ($mostrarTodo) : ?>


                                                <div class="col-sm-12">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="ragencia" id="allofi" value="allofi" checked onclick="changedisabled(`#codofi`,0)">
                                                        <label for="allofi" class="form-check-label">Consolidado </label>
                                                    </div>

                                                </div>

                                            <?php endif; ?>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="ragencia" id="anyofi" value="anyofi" checked onclick="changedisabled(`#codofi`,1)">
                                                <label for="anyofi" class="form-check-label"> Por Agencia.</label>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>


                                    <!-- COIDGO ANTERIOR
                                    <div class="col-sm-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="ragencia" id="allofi" value="allofi" checked onclick="changedisabled(`#codofi`,0)">
                                            <label for="allofi" class="form-check-label">Consolidado </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="ragencia" id="anyofi" value="anyofi" onclick="changedisabled(`#codofi`,1)">
                                            <label for="anyofi" class="form-check-label"> Por Agencia</label>
                                        </div>
                                 </div> -->

                                    <div class="col-sm-12">
                                        <span class="input-group-addon col-2">Agencia</span>

                                        <?php
                                        $sql = "SELECT id_usu, puesto, id_agencia
                                     FROM tb_usuario
                                     WHERE id_usu = '$idusuario'";
                                        $resultado = mysqli_query($conexion, $sql);

                                        if ($resultado) {
                                            $fila = mysqli_fetch_assoc($resultado);

                                            if ($fila) {
                                                $puestosP = array("ADM", "GER", "AUD", "CNT");

                                                if (in_array($fila['puesto'], $puestosP)) {
                                                    //permisos v
                                        ?>
                                                    <select class="form-select" id="codofi">
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
                                                    //caso contario
                                                ?>

                                                    <select class="form-select" id="codofi">
                                                        <?php
                                                        $ofis2 = mysqli_query($conexion, "SELECT usu.id_agencia, ofi.cod_agenc, ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi
                                                ON ofi.id_agencia = usu.id_agencia
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
                                        <!-- CODIGO ANTERIOR 
                               <select class="form-select" id="codofi" >
                                            <?php
                                            // $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                            //     ON ofi.id_agencia = usu.id_agencia GROUP BY ofi.id_agencia");
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
                </div>
                <!--Botones-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-danger" title="Estado de cambios en el patrimonio en pdf" onclick="reportes([[`finicio`,`ffin`],[`codofi`],[`ragencia`],[<?php echo $idusuario; ?>]],`pdf`,`estado_patrimonio`,0)">
                            <i class="fa-solid fa-file-pdf"></i> Pdf
                        </button>
                        <button type="button" class="btn btn-outline-success" title="Estado de cambios en el patrimonio en Excel" onclick="reportes([[`finicio`,`ffin`],[`codofi`],[`ragencia`],[<?php echo $idusuario; ?>]],`xlsx`,`estado_patrimonio`,1)">
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
    case 'flujo_efectivo':
    ?>
        <input type="text" id="file" value="ctb004" style="display: none;">
        <input type="text" id="condi" value="flujo_efectivo" style="display: none;">
        <div class="text" style="text-align:center">ESTADO DE FLUJO DE EFECTIVO</div>
        <div class="card">
            <div class="card-header">FILTROS</div>
            <div class="card-body">
                <div class="row container contenedort">
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por Oficina</div>
                            <div class="card-body">
                                <div class="row">

                                    <?php

                                    //   <!-- --REQ--crediprendas--1--restrinccion de reportes -->
                                    $query = "SELECT id_usu, puesto, id_agencia
        FROM tb_usuario
        WHERE id_usu = '$idusuario'";
                                    $resultado = mysqli_query($conexion, $query);

                                    $puestosP = array("ADM", "GER", "AUD", "CNT");
                                    if ($resultado) {
                                        $fila = mysqli_fetch_assoc($resultado);

                                        // Verificar si la fila existe 
                                        $mostrarTodo = ($fila && in_array($fila['puesto'], $puestosP));
                                    ?>
                                        <div class="col-sm-12">
                                            <?php if ($mostrarTodo) : ?>



                                            <?php endif; ?>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="ragencia" id="anyofi" value="anyofi" checked onclick="changedisabled(`#codofi`,1)">
                                                <label for="anyofi" class="form-check-label"> Por Agencia.</label>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>



                                    <div class="col-sm-12">
                                        <span class="input-group-addon col-2">Agencia</span>

                                        <?php
                                        $sql = "SELECT id_usu, puesto, id_agencia
                                     FROM tb_usuario
                                     WHERE id_usu = '$idusuario'";
                                        $resultado = mysqli_query($conexion, $sql);

                                        if ($resultado) {
                                            $fila = mysqli_fetch_assoc($resultado);

                                            if ($fila) {
                                                $puestosP = array("ADM", "GER", "AUD", "CNT");

                                                if (in_array($fila['puesto'], $puestosP)) {
                                                    //permisos v
                                        ?>
                                                    <select class="form-select" id="codofi" disabled>
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
                                                    //caso contario
                                                ?>

                                                    <select class="form-select" id="codofi">
                                                        <?php
                                                        $ofis2 = mysqli_query($conexion, "SELECT usu.id_agencia, ofi.cod_agenc, ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi
                                                ON ofi.id_agencia = usu.id_agencia
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


                                        <!-- <select class="form-select" id="codofi" disabled>
                                            <?php
                                            // $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                            //         ON ofi.id_agencia = usu.id_agencia GROUP BY ofi.id_agencia");
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
                </div>
                <!--Botones-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-danger" title="Flujo de efectivo en pdf" onclick="reportes([[`finicio`,`ffin`],[`codofi`],[`ragencia`],[<?php echo $idusuario; ?>]],`pdf`,`flujo_efectivo`,0)">
                            <i class="fa-solid fa-file-pdf"></i> Pdf
                        </button>
                        <button type="button" class="btn btn-outline-success" title="Flujo de efectivo en Excel" onclick="reportes([[`finicio`,`ffin`],[`codofi`],[`ragencia`],[<?php echo $idusuario; ?>]],`xlsx`,`flujo_efectivo`,1)">
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
        <div class="div" id="dataflujo">
        </div>
        <script>
            function loaddata() {
                inputs = getinputsval([`finicio`, `ffin`]);
                selects = getselectsval([`codofi`]);
                radios = getradiosval([`ragencia`]);
                printdiv('dataflujo', '#dataflujo', 'ctb004', [inputs, selects, radios])
            }
        </script>

    <?php
        break;
    case 'balcomparativo':
        $hoy = date("Y-m-d");

        // echo ('<pre>');
        // print_r($mesesant);
        // echo ('</pre>');
    ?>

        <input type="text" id="file" value="ctb004" style="display: none;">
        <input type="text" id="condi" value="balcomparativo" style="display: none;">
        <div class="text" style="text-align:center">BALANCE COMPARATIVO</div>
        <div class="card">
            <div class="card-header">FILTROS</div>
            <div class="card-body">
                <div class="row container contenedort">
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Fecha de balance 1</div>
                            <div class="card-body">
                                <div class="row" id="fechas1">
                                    <div class="col-sm-6">
                                        <label for="finicio1">Desde</label>
                                        <input type="date" class="form-control" id="finicio1" min="1950-01-01" value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="ffin1">Hasta</label>
                                        <input type="date" class="form-control" id="ffin1" min="1950-01-01" value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Fecha de balance 2</div>
                            <div class="card-body">
                                <div class="row" id="fechas2">
                                    <div class="col-sm-6">
                                        <label for="finicio2">Desde</label>
                                        <input type="date" class="form-control" id="finicio2" value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="ffin2">Hasta</label>
                                        <input type="date" class="form-control" id="ffin2" value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Botones-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-danger" title="Balance Comparativo en Pdf" onclick="reportes([[`finicio1`,`ffin1`,`finicio2`,`ffin2`],[],[],[<?php echo $idusuario; ?>]],`pdf`,`balcomparativo`,0)">
                            <i class="fa-solid fa-file-pdf"></i> Pdf
                        </button>
                        <button type="button" class="btn btn-outline-success" title="Balance Comparativo en Excel" onclick="reportes([[`finicio1`,`ffin1`,`finicio2`,`ffin2`],[],[],[<?php echo $idusuario; ?>]],`xlsx`,`balcomparativo`,1)">
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
    case 'ercomparativo':
        $hoy = date("Y-m-d");

        // echo ('<pre>');
        // print_r($mesesant);
        // echo ('</pre>');
    ?>
        <input type="text" id="file" value="ctb004" style="display: none;">
        <input type="text" id="condi" value="ercomparativo" style="display: none;">
        <div class="text" style="text-align:center">ESTADO DE RESULTADOS COMPARATIVO</div>
        <div class="card">
            <div class="card-header">FILTROS</div>
            <div class="card-body">
                <div class="row container contenedort">
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Fecha de Estado de Resultados 1</div>
                            <div class="card-body">
                                <div class="row" id="fechas1">
                                    <div class="col-sm-6">
                                        <label for="finicio1">Desde</label>
                                        <input type="date" class="form-control" id="finicio1" min="1950-01-01" value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="ffin1">Hasta</label>
                                        <input type="date" class="form-control" id="ffin1" min="1950-01-01" value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Fecha de Estado de Resultados 2</div>
                            <div class="card-body">
                                <div class="row" id="fechas2">
                                    <div class="col-sm-6">
                                        <label for="finicio2">Desde</label>
                                        <input type="date" class="form-control" id="finicio2" value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="ffin2">Hasta</label>
                                        <input type="date" class="form-control" id="ffin2" value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Botones-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-danger" title="Estado de Resultados Comparativo en Pdf" onclick="reportes([[`finicio1`,`ffin1`,`finicio2`,`ffin2`],[],[],[<?php echo $idusuario; ?>]],`pdf`,`ercomparativo`,0)">
                            <i class="fa-solid fa-file-pdf"></i> Pdf
                        </button>
                        <button type="button" class="btn btn-outline-success" title="Estado de Resultados Comparativo en Excel" onclick="reportes([[`finicio1`,`ffin1`,`finicio2`,`ffin2`],[],[],[<?php echo $idusuario; ?>]],`xlsx`,`ercomparativo`,1)">
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