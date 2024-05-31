<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];

switch ($condi) {
    case 'Saldos_de_cuentas': {
            $id = $_POST["xtra"];
            $usuario = $_SESSION['id'];
            $ofi = $_SESSION['agencia'];
?>
            <!-- APR_03_VrfcrSlds -->
            <div class="text" style="text-align:center">SALDO DE CUENTA</div>
            <input type="text" value="Saldos_de_cuentas" id="condi" style="display: none;">
            <input type="text" value="APRT_5" id="file" style="display: none;">

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
                                                <input class="form-check-input" type="radio" name="filter_estado" id="r_inactivos" value="B">
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
                                                $tipdoc = mysqli_query($conexion, "SELECT * FROM `aprtip`");
                                                echo '<option selected value="0">Todos</option>';
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


                    <!-- ------------------------------ -->
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[`fechaFinal`],[`tipcuenta`],[`filter_estado`],['<?php echo $usuario; ?>','<?php echo $ofi; ?>']], 'xlsx', 'saldo_de_cuentas', 1)">
                                <i class="fa-solid fa-file-excel"></i> Reporte en Excel
                            </button>

                            <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[`fechaFinal`],[`tipcuenta`],[`filter_estado`],['<?php echo $usuario; ?>','<?php echo $ofi; ?>']], 'pdf', 'saldo_de_cuentas', 0)">
                                <i class="fa-solid fa-file-print"></i> Reporte en PDF
                            </button>


                            <!-- <button type="button" id="btnSave" class="btn btn-outline-success" onclick="obtiene([`fechaFinal`],[`tipcuenta`],[`filter_estado`],`reporte_saldos_de_cuenta`,`excel`,['<?php echo $usuario; ?>','<?php echo $ofi; ?>'])">
                                <i class="fa-solid fa-file-excel"></i> Reporte en Excel
                            </button>

                            <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="obtiene([`fechaFinal`],[`tipcuenta`],[`filter_estado`],`reporte_saldos_de_cuenta`,`pdf`,['<?php echo $usuario; ?>','<?php echo $ofi; ?>'])">
                                <i class="fa-solid fa-file-pdf"></i> Reporte en PDF
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
        }
        break;
    case 'EstadoCuentaAportaciones': {
            $id = $_POST["xtra"];
            $usuario = $_SESSION['id'];
            $ofi = $_SESSION['agencia'];

            $datoscli = mysqli_query($conexion, "SELECT * FROM `aprcta` WHERE `ccodaport`=$id");
            $bandera = "Cuenta de aportación no existe";
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
            <input type="text" id="file" value="APRT_5" style="display: none;">
            <input type="text" id="condi" value="EstadoCuentaAportaciones" style="display: none;">
            <div class="card">
                <div class="card-header">Estado de cuenta</div>
                <div class="card-body">
                    <div class="container contenedort">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="row">
                                    <span class="input-group-addon col-12">Cuenta de aportaciones</span>
                                    <div class="input-group">
                                        <input type="text" class="form-control " id="ccodaport" required placeholder="000-000-00-000000" value="<?php if ($bandera == "") echo $id; ?>">
                                        <span class="input-group-text" id="basic-addon1">
                                            <?php
                                            if ($bandera == "") {
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
                                            ?>
                                        </span>
                                    </div>

                                </div>
                            </div>
                            <!-- botones para imprimir, cancelar y salir -->
                            <div class="col-md-6">
                                <br>
                                <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Aplicar cuenta ingresada" onclick="aplicarcod('ccodaport')">
                                    <i class="fa fa-check-to-slot"></i>
                                </button>
                                <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Buscar cuenta" data-bs-toggle="modal" data-bs-target="#findaportcta">
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
                    <!-- botones de imprimir, cancelar y salir -->
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[`ccodaport`,`libreta`,`fechaInicio`,`fechaFinal`],[],[`filter_fecha`],['<?php echo $usuario; ?>','<?php echo $ofi; ?>']], 'xlsx', 'estado_cuenta_apr', 1)">
                                <i class="fa-solid fa-file-excel"></i> Reporte en Excel
                            </button>

                            <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[`ccodaport`,`libreta`,`fechaInicio`,`fechaFinal`],[],[`filter_fecha`],['<?php echo $usuario; ?>','<?php echo $ofi; ?>']], 'pdf', 'estado_cuenta_apr', 0)">
                                <i class="fa-solid fa-print"></i> Reporte en PDF
                            </button>

                            <!-- <button type="button" id="btnSave" class="btn btn-outline-success" onclick="obtiene([`ccodaport`,`libreta`,`fechaInicio`,`fechaFinal`],[],[`filter_fecha`],`reporte_estado_cuenta_aprt`,`excel`,['<?php echo $usuario; ?>','<?php echo $ofi; ?>'])">
                                <i class="fa-solid fa-file-excel"></i> Reporte en Excel
                            </button>
                            <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="obtiene([`ccodaport`,`libreta`,`fechaInicio`,`fechaFinal`],[],[`filter_fecha`],`reporte_estado_cuenta_aprt`,`pdf`,['<?php echo $usuario; ?>','<?php echo $ofi; ?>'])">
                                <i class="fa-solid fa-file-pdf"></i> Reporte en PDF
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
        }
        break;
    case 'ListadoDelDia': {
            $id = $_POST["xtra"];
            $usuario = $_SESSION['id'];
            $ofi = $_SESSION['agencia'];
        ?>
            <!-- APR_05_LstdCntsActvsDspnbls -->
            <div class="text" style="text-align:center">LISTADO DEL DÍA</div>
            <input type="text" value="ListadoDelDia" id="condi" style="display: none;">
            <input type="text" value="APRT_5" id="file" style="display: none;">

            <div class="card">
                <div class="card-header">Listado del día</div>
                <div class="card-body">
                    <!-- Card para las transacciones -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><b>Transacciones</b></div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- radio button para los tipos de transacciones -->
                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_transaccion" id="r_todos" checked value="0">
                                                <label class="form-check-label" for="r_todos">Todos</label>
                                            </div>
                                        </div>

                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_transaccion" id="r_depositos" value="D">
                                                <label class="form-check-label" for="r_depositos">Depósito</label>
                                            </div>
                                        </div>
                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_transaccion" id="r_retiros" value="R">
                                                <label class="form-check-label" for="r_retiros">Retiro</label>
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
                                <div class="card-header"><b>Filtro de tipo de cuenta</b></div>
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
                                                <label class="form-check-label" for="r_cuenta">Tipo de cuenta</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="list-group list-group-flush card-body ps-3">
                                    <div class="row mb-1">
                                        <div class="col-12">
                                            <span class="input-group-addon">Tipo:</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 d-flex justify-content-center">

                                            <select class="form-select" aria-label="Default select example" id="tipcuenta" disabled>
                                                <?php
                                                $tipdoc = mysqli_query($conexion, "SELECT * FROM `aprtip`");
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
                        <!-- card para seleccionar una cuenta -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro por fecha</b></div>
                                <div class="card-body">
                                    <div class="row">
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
                    </div>

                    <div class="row justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <!-- en el metodo onclick se envian usuario y oficina para saber las cuentas de agencia a generar -->
                            <button type="button" id="btnSave" class="btn btn-outline-success" onclick="obtiene([`fechaInicio`,`fechaFinal`],[`tipcuenta`],[`filter_transaccion`,`filter_cuenta`,`filter_fecha`],`reporte_listado_dia`,`excel`,['<?php echo $usuario; ?>','<?php echo $ofi; ?>'])">
                                <i class="fa-solid fa-file-excel"></i> Reporte en Excel
                            </button>

                            <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="obtiene([`fechaInicio`,`fechaFinal`],[`tipcuenta`],[`filter_transaccion`,`filter_cuenta`,`filter_fecha`],`reporte_listado_dia`,`pdf`,['<?php echo $usuario; ?>','<?php echo $ofi; ?>'])">
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

    case 'Cuadre_de_diario': {
            $id = $_POST["xtra"];
            $usuario = $_SESSION['id'];
            $ofi = $_SESSION['agencia'];
        ?>
            <!-- APR_05_LstdCntsActvsDspnbls -->
            <div class="text" style="text-align:center">CUADRE DIARIO DE DEPOSITOS Y RETIROS</div>
            <input type="text" value="Cuadre_de_diario" id="condi" style="display: none;">
            <input type="text" value="APRT_5" id="file" style="display: none;">

            <div class="card">
                <div class="card-header">Cuadre diario de depositos y retiros</div>
                <div class="card-body">
                    <!-- segunda linea -->
                    <div class="row d-flex align-items-stretch mb-3">
                        <!-- card para filtrar cuentas -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro de tipo de cuenta</b></div>
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
                                                <label class="form-check-label" for="r_cuenta">Tipo de cuenta</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="list-group list-group-flush card-body ps-3">
                                    <div class="row mb-1">
                                        <div class="col-12">
                                            <span class="input-group-addon">Tipo:</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 d-flex justify-content-center">

                                            <select class="form-select" aria-label="Default select example" id="tipcuenta" disabled>
                                                <?php
                                                $tipdoc = mysqli_query($conexion, "SELECT * FROM `aprtip`");
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
                        <!-- card para seleccionar una cuenta -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro por fecha</b></div>
                                <div class="card-body">
                                    <div class="row">
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
                    </div>
                    <!-- ------------------------------ -->

                    <div class="row justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <!-- en el metodo onclick se envian usuario y oficina para saber las cuentas de agencia a generar -->
                            <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="obtiene([`fechaInicio`,`fechaFinal`],[`tipcuenta`],[`filter_cuenta`,`filter_fecha`],`cuadre_de_diario`,`pdf`,['<?php echo $usuario; ?>','<?php echo $ofi; ?>'])">
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
    case 'ListadoCuentasActivas': {
            $id = $_POST["xtra"];
            $usuario = $_SESSION['id'];
            $ofi = $_SESSION['agencia'];
        ?>
            <!-- APR_05_LstdCntsActvsDspnbls -->
            <div class="text" style="text-align:center">LISTADO DE CUENTAS ACTIVAS/INACTIVAS</div>
            <input type="text" value="ListadoCuentasActivas" id="condi" style="display: none;">
            <input type="text" value="APRT_5" id="file" style="display: none;">

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
                                                $tipdoc = mysqli_query($conexion, "SELECT * FROM `aprtip`");
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

                    <div class="row justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <!-- en el metodo onclick se envian usuario y oficina para saber las cuentas de agencia a generar -->
                            <button type="button" id="btnSave" class="btn btn-outline-success" onclick="obtiene([],[`tipcuenta`],[`filter_estado`,`filter_cuenta`],`reporte_cuentas_act_inact_aprt`,`excel`,['<?php echo $usuario; ?>','<?php echo $ofi; ?>'])">
                                <i class="fa-solid fa-file-excel"></i> Reporte en Excel
                            </button>

                            <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="obtiene([],[`tipcuenta`],[`filter_estado`,`filter_cuenta`],`reporte_cuentas_act_inact_aprt`,`pdf`,['<?php echo $usuario; ?>','<?php echo $ofi; ?>'])">
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
}
?>