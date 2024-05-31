<?php
session_start();
include '../../includes/BD_con/db_con.php';
include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];

switch ($condi) {
        //Listado de cuentas activas/disponibles
    case 'ListadoCuentasActivas':
        $id = $_POST["xtra"];
        $usuario = $_SESSION['id'];
        $ofi = $_SESSION['agencia'];
?>
        <!-- APR_05_LstdCntsActvsDspnbls -->
        <div class="text" style="text-align:center">LISTADO DE CUENTAS ACTIVAS/INACTIVAS</div>
        <input type="text" value="ListadoCuentasActivas" id="condi" style="display: none;">
        <input type="text" value="aho_06" id="file" style="display: none;">

        <div class="card">
            <div class="card-header">Listado de Cuentas Activas/Inactivas</div>
            <div class="card-body">
                <!-- Card para los estados de cuenta -->
                <div class="row mb-2">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header"><b>Estados de cuenta</b></div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- radio button de todas las cuentas -->
                                    <div class="col d-flex justify-content-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="filter_estado" id="r_todos" checked value="0">
                                            <label class="form-check-label" for="r_todos">Todos</label>
                                        </div>
                                    </div>

                                    <div class="col d-flex justify-content-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="filter_estado" id="r_activos" value="A">
                                            <label class="form-check-label" for="r_activos">Activos</label>
                                        </div>
                                    </div>
                                    <div class="col d-flex justify-content-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="filter_estado" id="r_inactivos" value="B">
                                            <label class="form-check-label" for="r_inactivos">Inactivos</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- segunda linea -->
                <div class="row d-flex align-items-stretch mb-3">
                    <!-- card para filtrar cuentas -->
                    <div class="col-6">
                        <div class="card" style="height: 100% !important;">
                            <div class="card-header"><b>Filtro de cuentas</b></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col d-flex justify-content-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="filter_cuenta" id="r_cuentas" value="1" checked onclick="activar_select_cuentas(this, true,'tipcuenta')">
                                            <label class="form-check-label" for="r_cuentas">Todos</label>
                                        </div>
                                    </div>
                                    <div class="col d-flex justify-content-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="filter_cuenta" id="r_cuenta" value="2" onclick="activar_select_cuentas(this,false,'tipcuenta')">
                                            <label class="form-check-label" for="r_cuenta">Una cuenta</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- card para seleccionar una cuenta -->
                    <div class="col-6">
                        <div class="card" style="height: 100% !important;">
                            <div class="card-header"><b>Filtrar por una cuenta</b></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <select class="form-select" aria-label="Default select example" id="tipcuenta" disabled>
                                            <?php
                                            $tipdoc = mysqli_query($conexion, "SELECT * FROM `ahomtip`");
                                            echo '<option selected value="0">Seleccione un tipo de cuenta</option>';
                                            while ($tip = mysqli_fetch_array($tipdoc)) {
                                                echo '<option value="' . $tip['id_tipo'] . '">' . $tip['nombre'] . ' - ' . $tip['cdescripcion'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- inputs que no sirven para nada -->
                <input type="text" name="nada" id="nada" checked style="display: none;">
                <!-- ------------------------------ -->

                <div class="row justify-items-md-center">
                    <div class="col align-items-center" id="modal_footer">
                        <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[],[`tipcuenta`],[`filter_estado`,`filter_cuenta`],['<?php echo $usuario; ?>','<?php echo $ofi; ?>']], 'xlsx', 'listado_cuentas_aho', 1)">
                            <i class="fa-solid fa-file-excel"></i> Reporte en Excel
                        </button>
                        <!-- <button type="button" id="btnSave" class="btn btn-outline-success" onclick="obtiene([`nada`],[`tipcuenta`],[`filter_estado`,`filter_cuenta`],`reporte_cuentas_act_inact_aho`,`excel`,['<?php echo $usuario; ?>','<?php echo $ofi; ?>'])">
                            <i class="fa-solid fa-print"></i> Reporte en Excel
                        </button> -->
                        <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[],[`tipcuenta`],[`filter_estado`,`filter_cuenta`],['<?php echo $usuario; ?>','<?php echo $ofi; ?>']], 'pdf', 'listado_cuentas_aho', 0)">
                            <i class="fa-solid fa-file-print"></i> Reporte en PDF
                        </button>
                        <!-- <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="obtiene([`nada`],[`tipcuenta`],[`filter_estado`,`filter_cuenta`],`reporte_cuentas_act_inact_aho`,`pdf`,['<?php echo $usuario; ?>','<?php echo $ofi; ?>'])">
                            <i class="fa-solid fa-print"></i> Reporte en PDF
                        </button> -->

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
    case 'cuadrediario':
        $id = $_POST["xtra"];
        $usuario = $_SESSION['id'];
        $ofi = $_SESSION['agencia'];
    ?>
        <!-- APR_05_LstdCntsActvsDspnbls -->
        <div class="text" style="text-align:center">CUADRE DIARIO DE DEPOSITOS Y RETIROS</div>
        <input type="text" value="cuadrediario" id="condi" style="display: none;">
        <input type="text" value="aho_06" id="file" style="display: none;">

        <div class="card">
            <div class="card-header">Cuadre diario de depositos y retiros</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="card text-bg-light mb-3" style="max-width: 30rem;">
                            <div class="card-header">Filtro por tipos de cuentas</div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="r1" id="all" value="all" checked onclick="habdeshab([],['tipcuenta'])">
                                            <label for="Ting" class="form-check-label">Todo </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="r1" id="any" value="any" onclick="habdeshab(['tipcuenta'],[])">
                                            <label for="Tsal" class="form-check-label"> Por Tipo de Cuenta</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div>
                                            <span class="input-group-addon col-2">Tipo de Cuenta</span>
                                            <select class="form-select" id="tipcuenta" required placeholder="" disabled>
                                                <option value="0" selected disabled>Seleccionar tipo de cuenta</option>
                                                <?php
                                                $cuentas = mysqli_query($conexion, "SELECT * FROM `ahomtip`");
                                                while ($cuenta = mysqli_fetch_array($cuentas, MYSQLI_ASSOC)) {
                                                    echo '<option value="' . $cuenta['ccodtip'] . '">' . $cuenta['ccodtip'] . " - " . $cuenta['nombre'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card text-bg-light mb-3" style="max-width: 40rem;">
                            <div class="card-header">Filtro por fechas</div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="r2" id="ftodo" value="ftodo" checked onclick="habdeshab([],['finicio','ffin'])">
                                            <label for="ftodo" class="form-check-label">Todo</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="r2" id="frango" value="frango" onclick="habdeshab(['finicio','ffin'],[])">
                                            <label for="frango" class="form-check-label">Rango</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class=" col-sm-5">
                                        <label for="finicio">Desde</label>
                                        <input type="date" class="form-control" id="finicio" min="1950-01-01" disabled value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                    <div class=" col-sm-5">
                                        <label for="ffin">Hasta</label>
                                        <input type="date" class="form-control" id="ffin" min="1950-01-01" disabled value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ------------------------------ -->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center" id="modal_footer">
                        <button type="button" id="btnSave" class="btn btn-outline-danger" onclick="reportes([[`finicio`,`ffin`],[`tipcuenta`],[`r1`,`r2`],['']], 'pdf', 'cuadre_diario')">
                            <i class="fa-solid fa-file-pdf"></i> Generar Pdf
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
} //FINAL DEL SWITCH
?>