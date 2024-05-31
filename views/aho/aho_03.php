<?php
session_start();
include '../../includes/BD_con/db_con.php';
include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];

switch ($condi) {
    case 'EstadoCuentaAhorros':
        $id = $_POST["xtra"];
        $usuario = $_SESSION['id'];
        $ofi = $_SESSION['agencia'];

        $datoscli = mysqli_query($conexion, "SELECT * FROM `ahomcta` WHERE `ccodaho`=$id AND estado IN ('A','B')");
        $bandera = "Cuenta de ahorro no existe";
        while ($da = mysqli_fetch_array($datoscli, MYSQLI_ASSOC)) {
            $idcli = utf8_encode($da["ccodcli"]);
            $nit = utf8_encode($da["num_nit"]);
            $nlibreta = utf8_encode($da["nlibreta"]);
            $bandera = "";
        }
        if ($bandera == "") {
            $data = mysqli_query($conexion, "SELECT `short_name` FROM `tb_cliente` WHERE `idcod_cliente` = '$idcli'");
            $dat = mysqli_fetch_array($data, MYSQLI_ASSOC);
            $nombre = utf8_encode($dat["short_name"]);
        }
?>
        <!-- APR_03_StdCnt -->
        <div class="text" style="text-align:center">ESTADO DE CUENTA</div>
        <input type="text" id="file" value="aho_03" style="display: none;">
        <input type="text" id="condi" value="EstadoCuentaAhorros" style="display: none;">
        <div class="card">
            <div class="card-header">Estado de cuenta</div>
            <div class="card-body">
                <div class="container contenedort">
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <span class="input-group-addon col-8">Cuenta de Ahorros</span>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control " id="ccodaho" required placeholder="000-000-00-000000" value="<?php if ($bandera == "") echo $id; ?>">
                                <span class="input-group-text" id="basic-addon1">
                                    <?php if ($bandera == "") {
                                        echo '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-circle-check" width="26" height="26" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00b341" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <circle cx="12" cy="12" r="9" />
                                            <path d="M9 12l2 2l4 -4" />
                                            </svg>';
                                    } else {
                                        echo '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-circle-x" width="26" height="26"  viewBox="0 0 24 24" stroke-width="1.5" stroke="#ff2825" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <circle cx="12" cy="12" r="9" />
                                            <path d="M10 10l4 4m0 -4l-4 4" />
                                          </svg>';
                                    }
                                    ?></span>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <br>
                            <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Aplicar cuenta ingresada" onclick="aplicarcod('ccodaho')">
                                <i class="fa fa-check-to-slot"></i>
                            </button>
                            <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Buscar cuenta" data-bs-toggle="modal" data-bs-target="#findahomcta">
                                <i class="fa fa-magnifying-glass"></i>
                            </button>
                        </div>
                    </div>
                    <!-- alerta para cuando no encuentra una cuenta -->
                    <?php if ($bandera != "" && $id != "0") {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $bandera . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
                    }
                    ?>
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <div>
                                <span class="input-group-addon col-8">Libreta</span>
                                <input type="text" class="form-control " id="libreta" required readonly value="<?php if ($bandera == "") echo $nlibreta; ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div>
                                <span class="input-group-addon col-8">NIT</span>
                                <input type="text" class="form-control " id="nit" required readonly value="<?php if ($bandera == "") echo $nit; ?>">
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div>
                                <span class="input-group-addon col-8">Nombre</span>
                                <input type="text" class="form-control " id="name" readonly value="<?php if ($bandera == "") echo $nombre; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- fin de row contenedort -->
                <!-- inicio de contenedor para la seleccion de una fecha o no -->
                <div class="row d-flex align-items-stretch mb-3">
                    <div class="col-6">
                        <div class="container contenedort" style="height: 100% !important;">
                            <div class="row mb-3">
                                <div class="col">
                                    <div>
                                        <span class="input-group-addon">Filtro de fecha</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col d-flex justify-content-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="filter_fecha" id="r_nofecha" value="1" checked onclick="activar_input_dates(this, true,'fechaInicio','fechaFinal')">
                                        <label class="form-check-label" for="r_nofecha">Todo</label>
                                    </div>
                                </div>
                                <div class="col d-flex justify-content-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="filter_fecha" id="r_fecha" value="2" onclick="activar_input_dates(this,false,'fechaInicio','fechaFinal')">
                                        <label class="form-check-label" for="r_fecha">Rango</label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- seccion de rango de fecha -->
                    <div class="col-6">
                        <div class="container contenedort" style="height: 100% !important;">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <span class="input-group-addon">Desde:</span>
                                </div>
                                <div class="col-6">
                                    <span class="input-group-addon">Hasta:</span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="input-group">
                                        <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?php echo date("Y-m-d"); ?>" id="fechaInicio" disabled>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="input-group">
                                        <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?php echo date("Y-m-d"); ?>" id="fechaFinal" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- select que no se usa -->
                <select name="select" id="nada" style="display: none;">
                    <option value="value1">Value 1</option>
                </select>

                <!-- botones de imprimir, cancelar y salir -->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center" id="modal_footer">
                        <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[`ccodaho`,`libreta`,`fechaInicio`,`fechaFinal`],[],[`filter_fecha`],['<?php echo $usuario; ?>','<?php echo $ofi; ?>']], 'xlsx', 'estado_cuenta_aho', 1)">
                            <i class="fa-solid fa-file-excel"></i> Reporte en Excel
                        </button>
                        <!-- <button type="button" id="btnSave" class="btn btn-outline-success"
                    onclick="obtiene([`ccodaho`,`libreta`,`fechaInicio`,`fechaFinal`],[`nada`],[`filter_fecha`],`reporte_estado_cuenta_aho`,`excel`,['<?php echo $usuario; ?>','<?php echo $ofi; ?>'])">
                    <i class="fa-solid fa-file-excel"></i> Reporte en Excel
                </button> -->
                        <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[`ccodaho`,`libreta`,`fechaInicio`,`fechaFinal`],[],[`filter_fecha`],['<?php echo $usuario; ?>','<?php echo $ofi; ?>']], 'pdf', 'estado_cuenta_aho', 0)">
                            <i class="fa-solid fa-print"></i> Reporte en PDF
                        </button>
                        <!-- <button type="button" id="btnSave" class="btn btn-outline-success"
                    onclick="obtiene([`ccodaho`,`libreta`,`fechaInicio`,`fechaFinal`],[`nada`],[`filter_fecha`],`reporte_estado_cuenta_aho`,`pdf`,['<?php echo $usuario; ?>','<?php echo $ofi; ?>'])">
                    <i class="fa-solid fa-print"></i> Reporte en PDF 2
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
        //Estado de cuenta por fecha
    case 'Saldos_de_cuentas':
        $id = $_POST["xtra"];
        $usuario = $_SESSION['id'];
        $ofi = $_SESSION['agencia'];
    ?>
        <!-- APR_03_VrfcrSlds -->
        <div class="text" style="text-align:center">SALDOS DE CUENTAS</div>
        <input type="text" value="Saldos_de_cuentas" id="condi" style="display: none;">
        <input type="text" value="aho_03" id="file" style="display: none;">

        <div class="card">
            <div class="card-header">Reporte de saldos por cuenta</div>
            <div class="card-body">
                <div class="row d-flex align-items-stretch mb-3">
                    <!-- columnas de estados de cuenta -->
                    <div class="col-3">
                        <div class="card" style="height: 100% !important;">
                            <div class="card-header"><b>Estado de cuenta</b></div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col d-flex justify-content-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="filter_estado" id="r_todos" checked value="0">
                                            <label class="form-check-label" for="r_todos">Todos</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col d-flex justify-content-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="filter_estado" id="r_activos" value="A">
                                            <label class="form-check-label" for="r_activos">Activos</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col d-flex justify-content-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="filter_estado" id="r_inactivos" value="0">
                                            <label class="form-check-label" for="r_inactivos">Inactivos</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- columna de cuentas -->
                    <div class="col-6">
                        <div class="card" style="height: 100% !important;">
                            <div class="card-header"><b>Tipo de cuenta</b></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <select class="form-select" aria-label="Default select example" id="tipcuenta">
                                            <?php
                                            $tipdoc = mysqli_query($conexion, "SELECT * FROM `ahomtip`");
                                            echo '<option selected value="0">Todos los tipos de cuentas</option>';
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
                    <div class="col-3">
                        <div class="card" style="height: 100% !important;">
                            <div class="card-header"><b>Filtro de fecha</b></div>
                            <div class="card-body">
                                <div class="row mb-1">
                                    <div class="col">
                                        <span class="input-group-addon">Fecha final:</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="input-group">
                                            <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?php echo date("Y-m-d"); ?>" id="fechaFinal">
                                        </div>
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
                        <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[`fechaFinal`],[`tipcuenta`],[`filter_estado`],['<?php echo $usuario; ?>','<?php echo $ofi; ?>']], 'xlsx', 'saldo_de_cuentas', 1)">
                            <i class="fa-solid fa-file-excel"></i> Reporte en Excel
                        </button>
                        <!-- <button type="button" id="btnSave" class="btn btn-outline-success"
                    onclick="obtiene([`fechaFinal`],[`tipcuenta`],[`filter_estado`],`saldo_de_cuentas`,`excel`,['<?php echo $usuario; ?>','<?php echo $ofi; ?>'])">
                    <i class="fa-solid ddddddfa-print"></i> Reporte en Excel
                </button> -->

                        <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[`fechaFinal`],[`tipcuenta`],[`filter_estado`],['<?php echo $usuario; ?>','<?php echo $ofi; ?>']], 'pdf', 'saldo_de_cuentas', 0)">
                            <i class="fa-solid fa-file-print"></i> Reporte en PDF
                        </button>
                        <!-- <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="obtiene([`fechaFinal`],[`tipcuenta`],[`filter_estado`],`saldo_de_cuentas`,`pdf`,['<?php echo $usuario; ?>','<?php echo $ofi; ?>'])">
                            <i class="fa-solid fa-print"></i> Reporte gtrten PDF
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
} //FINAL DEL SWITCH
?>