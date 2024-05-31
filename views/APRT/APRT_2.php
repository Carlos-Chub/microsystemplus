<?php
session_start();
include '../../includes/BD_con/db_con.php';
include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];

switch ($condi) {
    case 'DepoAportaciones': {
            $id = $_POST["xtra"];
            $usuario = $_SESSION['id'];
            $ofi = $_SESSION['agencia'];
            $bandera = "";
            if ($id != '0') {
                try {
                    $query = "SELECT cta.ccodcli,cta.estado,cta.nlibreta,cli.no_tributaria num_nit,cli.short_name 
                            FROM `aprcta` cta INNER JOIN tb_cliente cli ON cli.idcod_cliente=cta.ccodcli 
                            WHERE `ccodaport`=?";
                    $response = executequery($query, [$id], ['s'], $conexion);
                    if (!$response[1]) {
                        throw new Exception($response[0]);
                    }
                    $data = $response[0];
                    $flag = ((count($data)) > 0) ? true : false;
                    if (!$flag) {
                        throw new Exception("Cuenta de aportaciones no existe");
                    }
                    $da = $data[0];
                    $idcli = utf8_encode($da["ccodcli"]);
                    $nit = utf8_encode($da["num_nit"]);
                    $nlibreta = ($da["nlibreta"]);
                    $ultimonum = lastnumlin($id, $nlibreta, "aprmov", "ccodaport", $conexion);
                    $numlib = numfront(substr($id, 6, 2), "aprtip") + numdorsal(substr($id, 6, 2), "aprtip");
                    if ($ultimonum >= $numlib) {
                        throw new Exception("El número de líneas en libreta ha llegado a su límite, se recomienda abrir otra libreta");
                    }
                    $estado = ($da["estado"]);
                    if ($estado != "A") {
                        throw new Exception("Cuenta de aportaciones Inactiva");
                    }
                    $nombre = ($da["short_name"]);
                } catch (Exception $e) {
                    //Captura el error
                    $bandera = $e->getMessage();
                    $bandera_error = true;
                }
            }

            $flag_correlativo = 1;
            if ($flag_correlativo == 1 && $id != NULL) {
                //Consulta para obtener el numero correlativo
                // $dato = 2;
                $sql = "SELECT MAX(CAST(mov.cnumdoc AS SIGNED)) FROM aprmov mov
                            INNER JOIN tb_usuario usu ON usu.id_usu=mov.codusu
                            INNER JOIN tb_agencia ofi ON ofi.id_agencia=usu.id_agencia
                            WHERE ctipope = 'D' AND crazon = 'DEPOSITO' AND cestado = 1 AND ofi.id_agencia=? AND cestado!=2";
                $stmt1 = $conexion->prepare($sql);

                $stmt1->bind_param("i", $ofi);
                $dato = $stmt1->execute();

                $stmt1->bind_result($correlativo);
                $stmt1->fetch();
                $stmt1->close();
            }
?>

            <!-- APR_2_AODpstAprtcns -->
            <div class="text" style="text-align:center">DEPÓSITO DE APORTACIONES</div>
            <input type="text" id="file" value="APRT_2" style="display: none;">
            <input type="text" id="condi" value="DepoAportaciones" style="display: none;">
            <div class="card mb-2">
                <div class="card-header">Depósito Aportaciones</div>
                <div class="card-body">
                    <!-- seccion de cuenta y nombre, PRIMERA LINEA -->
                    <div class="container contenedort">
                        <div class="row">
                            <div class="col-md-5">
                                <!-- titulo -->
                                <span class="input-group-addon col-8">Cuenta de Aportación</span>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control " id="ccodaport" required placeholder="000-000-00-000000" value="<?php if ($bandera == "" && $id != 0) echo $id; ?>">
                                    <span class="input-group-text" id="basic-addon1">
                                        <?php
                                        if ($bandera == ""  && $id != 0) {
                                            echo '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-circle-check" width="26" height="26" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00b341" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <circle cx="12" cy="12" r="9" />
                                            <path d="M9 12l2 2l4 -4" />
                                            </svg>';
                                        } else {
                                            echo '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-circle-x" width="26" height="26" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ff2825" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <circle cx="12" cy="12" r="9" />
                                            <path d="M10 10l4 4m0 -4l-4 4" />
                                          </svg>';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-7">
                                <br>
                                <!-- boton para aplicar cuenta ingresada -->
                                <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Validar cuenta de aportación digitada" onclick="aplicarcod('ccodaport')">
                                    <i class="fa fa-check-to-slot"></i>
                                </button>
                                <!-- boton para buscar cuenta de ahorro -->
                                <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Buscar cuenta" data-bs-toggle="modal" data-bs-target='#findaportcta'>
                                    <i class="fa fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </div>
                        <!-- input para nombre de aportacion -->
                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <span class="input-group-addon col-8">Nombre</span>
                                <input type="text" class="form-control " id="name" value="<?php if ($bandera == ""  && $id != 0) echo $nombre; ?>" readonly>
                            </div>
                        </div>
                        <?php if ($bandera != "" && $id != "0") {
                            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $bandera . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
                        }
                        ?>
                    </div>
                    <!-- seccion de numero de documento hasta fecha de deposito -->
                    <div class="container contenedort">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <span class="input-group-addon">No. Documento:</span>
                                <input type="text" class="form-control " id="cnumdoc" required value="<?php echo ($id != 0) ? (($flag_correlativo == 1) ? ((int)$correlativo + 1) : '') : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <span class="input-group-addon">Fecha:</span>
                                <input type="date" class="form-control" id="dfecope" value="<?php echo date("Y-m-d"); ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <span class="input-group-addon">Cantidad:</span>
                                <input type="number" step="any" class="form-control " id="monto" required placeholder="0.00" min="1">
                            </div>
                            <div class="col-md-4">
                                <span class="input-group-addon">Cuota de ingreso:</span>
                                <input type="number" step="any" class="form-control " id="cuotaIngreso" placeholder="0.00" min="1">
                            </div>
                            <div class="col-md-3">
                                <span class="input-group-addon">Libreta:</span>
                                <input type="number" class="form-control" id="lib" value="<?php if ($bandera == "" && $id != 0) echo $nlibreta; ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <!-- seccion de numero de documento hasta fecha de deposito -->
                    <div class="container contenedort">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <span>Transacción:</span>
                                <select id='aprtip' class="form-select mt-1" aria-label="Default select example">
                                    <?php if ($bandera == "" && $id != 0)
                                        echo '<option selected>' . tipocuenta(SUBSTR($id, 6, 2), "aprtip", "nombre", $conexion) . '</option>';
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <span>Salida:</span>
                                <select id="salida" class="form-select" aria-label="Default select example" required>
                                    <option value="1" selected>Con Libreta</option>
                                    <option value="0">Sin Libreta</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <span>Tipo de documeto:</span>
                                <select id="tipdoc" class="form-select" aria-label="Default select example" required onchange="tipdoc(this.value)">
                                    <option value="E" selected>EFECTIVO</option>
                                    <option value="D">CON BOLETA DE BANCO</option>
                                    <?php
                                    // $tipdoc = mysqli_query($conexion, "SELECT * FROM `aprtipdoc`");
                                    // while ($tip = mysqli_fetch_array($tipdoc)) {
                                    //     echo '<option value="' . $tip['codtip'] . '">' . utf8_encode($tip['descripcion']) . '</option>';
                                    // }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="container contenedort" id="region_cheque" style="display: none; max-width: 100% !important;">
                        <h6>DATOS DEL BANCO DONDE SE HIZO EL DEPÓSITO</h6>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="bancoid" onchange="buscar_cuentas()">
                                        <option value="0" disabled selected>Seleccione un banco</option>
                                        <?php
                                        $bancos = mysqli_query($conexion, "SELECT * FROM tb_bancos WHERE estado='1'");
                                        while ($banco = mysqli_fetch_array($bancos)) {
                                            echo '<option  value="' . $banco['id'] . '">' . $banco['id'] . " - " . $banco['nombre'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="bancoid">Banco</label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="cuentaid">
                                        <option value="0">Seleccione una cuenta</option>
                                    </select>
                                    <label for="cuentaid">No. de Cuenta</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="cnumdocboleta" placeholder="No de Boleta">
                                    <label for="cnumdocboleta">No.Boleta Banco</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--Aho_1_DepoAportaciones Botones-->
                    <div class="row mb-1 pt-2 justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <?php if ($bandera == "" && $id != "0") {
                            ?>
                                <button type="button" id="btnSave" class="btn btn-outline-success" onclick="confirmSaveaprt('D')">
                                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                                </button>
                            <?php
                            }
                            ?>
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
            <script>
                function confirmSaveaprt(action) {
                    var cuota_ingreso = parseFloat($("#cuotaIngreso").val());
                    var cantidad = parseFloat($("#monto").val());
                    var aprtip = $('#aprtip').val();
                    // console.log(cuota_ingreso);

                    if (!isNaN(cantidad) && !isNaN(cuota_ingreso)) {
                        cantidad = cantidad + cuota_ingreso;
                    }
                    if (!isNaN(cantidad)) {
                        Swal.fire({
                            title: "Deseas " + "Depositar" + " la cantidad de Q." + cantidad + "?",
                            text: " ",
                            icon: "question",
                            showCancelButton: true,
                            confirmButtonText: "Sí, " + (action === 'D' ? "Guardar" : ""),
                            confirmButtonColor: '#4CAF50',
                            cancelButtonText: "Cancelar"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                //saveaprmov(' echo $bandera; ?>', ' echo $usuario; ?>', ' echo $ofi; ?>', action);
                                //obtiene(['ccodaport', 'dfecope', 'cnumdoc', 'monto', 'numpartida', 'feccom', 'nrochq', 'cuotaIngreso'], ['salida', 'tipdoc', 'tipchq'], [], 'cdaportmov', '0', [usu, ofi, tipotransaction]);
                                obtiene(['ccodaport', 'dfecope', 'cnumdoc', 'monto', 'cuotaIngreso','cnumdocboleta'], ['salida', 'tipdoc', 'bancoid', 'cuentaid', 'aprtip'], [], 'cdaportmov', '0', ['<?php echo $id; ?>', action]);
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            text: "Tiene que ingresar un monto"
                        });
                    }
                }
            </script>


        <?php
        }
        break;

    case 'RetiroAportaciones': {
            $id = $_POST["xtra"];
            $usuario = $_SESSION['id'];
            $ofi = $_SESSION['agencia'];
            $bandera = "";
            if ($id != '0') {
                try {
                    $query = "SELECT cta.ccodcli,cta.estado,cta.nlibreta,cli.no_tributaria num_nit,cli.short_name 
                        FROM `aprcta` cta INNER JOIN tb_cliente cli ON cli.idcod_cliente=cta.ccodcli 
                        WHERE `ccodaport`=?";
                    $response = executequery($query, [$id], ['s'], $conexion);
                    if (!$response[1]) {
                        throw new Exception($response[0]);
                    }
                    $data = $response[0];
                    $flag = ((count($data)) > 0) ? true : false;
                    if (!$flag) {
                        throw new Exception("Cuenta de aportaciones no existe");
                    }
                    $da = $data[0];
                    $idcli = utf8_encode($da["ccodcli"]);
                    $nit = utf8_encode($da["num_nit"]);
                    $nlibreta = ($da["nlibreta"]);
                    $ultimonum = lastnumlin($id, $nlibreta, "aprmov", "ccodaport", $conexion);
                    $numlib = numfront(substr($id, 6, 2), "aprtip") + numdorsal(substr($id, 6, 2), "aprtip");
                    if ($ultimonum >= $numlib) {
                        throw new Exception("El número de líneas en libreta ha llegado a su límite, se recomienda abrir otra libreta");
                    }
                    $estado = ($da["estado"]);
                    if ($estado != "A") {
                        throw new Exception("Cuenta de aportaciones Inactiva");
                    }
                    $nombre = ($da["short_name"]);
                } catch (Exception $e) {
                    //Captura el error
                    $bandera = $e->getMessage();
                    $bandera_error = true;
                }
            }

            $flag_correlativo = 1;
            if ($flag_correlativo == 1 && $id != NULL) {
                $sql = "SELECT MAX(CAST(mov.cnumdoc AS SIGNED)) FROM aprmov mov
                            INNER JOIN tb_usuario usu ON usu.id_usu=mov.codusu
                            INNER JOIN tb_agencia ofi ON ofi.id_agencia=usu.id_agencia
                            WHERE ctipope = 'R' AND crazon = 'RETIRO' AND cestado = 1 AND ofi.id_agencia=? AND cestado!=2";
                $stmt1 = $conexion->prepare($sql);

                $stmt1->bind_param("i", $ofi);
                $dato = $stmt1->execute();

                $stmt1->bind_result($correlativo);
                $stmt1->fetch();
                $stmt1->close();
            }

        ?>
            <!-- APR_2_AODpstAprtcns -->
            <div class="text" style="text-align:center">RETIRO DE APORTACIONES</div>
            <input type="text" id="file" value="APRT_2" style="display: none;">
            <input type="text" id="condi" value="RetiroAportaciones" style="display: none;">
            <div class="card mb-2">
                <div class="card-header">Retiro Aportaciones</div>
                <div class="card-body">
                    <!-- seccion de cuenta y nombre, PRIMERA LINEA -->
                    <div class="container contenedort">
                        <div class="row">
                            <div class="col-md-5">
                                <!-- titulo -->
                                <span class="input-group-addon col-8">Cuenta de Aportación</span>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control " id="ccodaport" required placeholder="000-000-00-000000" value="<?php if ($bandera == "" && $id != 0) echo $id; ?>">
                                    <span class="input-group-text" id="basic-addon1">
                                        <?php
                                        if ($bandera == "" && $id != 0) {
                                            echo '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-circle-check" width="26" height="26" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00b341" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <circle cx="12" cy="12" r="9" />
                                            <path d="M9 12l2 2l4 -4" />
                                            </svg>';
                                        } else {
                                            echo '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-circle-x" width="26" height="26" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ff2825" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <circle cx="12" cy="12" r="9" />
                                            <path d="M10 10l4 4m0 -4l-4 4" />
                                          </svg>';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-7">
                                <br>
                                <!-- boton para aplicar cuenta ingresada -->
                                <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Validar cuenta de aportación digitada" onclick="aplicarcod('ccodaport')">
                                    <i class="fa fa-check-to-slot"></i>
                                </button>
                                <!-- boton para buscar cuenta de ahorro -->
                                <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Buscar cuenta" data-bs-toggle="modal" data-bs-target='#findaportcta'>
                                    <i class="fa fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </div>
                        <!-- input para nombre de aportacion -->
                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <span class="input-group-addon col-8">Nombre</span>
                                <input type="text" class="form-control " id="name" value="<?php if ($bandera == "" && $id != 0) echo $nombre; ?>" readonly>
                            </div>
                        </div>
                        <?php if ($bandera != "" && $id != "0") {
                            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $bandera . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
                        }
                        ?>
                    </div>

                    <!-- seccion de numero de documento hasta fecha de deposito -->
                    <div class="container contenedort">
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <span class="input-group-addon">No. Documento:</span>
                                <input type="text" class="form-control " id="cnumdoc" required value="<?php echo ($id != 0) ? (($flag_correlativo == 1) ? ((int)$correlativo + 1) : '') : '' ?>">
                            </div>
                            <div class="col-md-5">
                                <span class="input-group-addon">Fecha:</span>
                                <input type="date" class="form-control" id="dfecope" value="<?php echo date("Y-m-d"); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-5">
                                <span class="input-group-addon">Cantidad:</span>
                                <input type="number" step="any" class="form-control " id="monto" required placeholder="0.00" min="1">
                            </div>
                            <div class="col-md-5">
                                <span class="input-group-addon">Libreta:</span>
                                <input type="number" class="form-control" id="lib" value="<?php if ($bandera == "" && $id != 0) echo $nlibreta; ?>" readonly>
                            </div>
                        </div>
                        <div class="row mb-3" style="display: none;">
                            <div class="col-md-4">
                                <span class="input-group-addon">Cuota</span>
                                <input type="number" step="any" class="form-control " id="cuotaIngreso" placeholder="0.00" min="1">
                            </div>
                        </div>
                    </div>

                    <!-- seccion de numero de documento hasta fecha de deposito -->
                    <div class="container contenedort">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <span>Transacción:</span>
                                <select class="form-select mt-1" aria-label="Default select example">
                                    <?php if ($bandera == "" && $id != 0)
                                        echo '<option selected>' . tipocuenta(SUBSTR($id, 6, 2), "aprtip", "nombre", $conexion) . '</option>';
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <span>Salida:</span>
                                <select id="salida" class="form-select" aria-label="Default select example" required>
                                    <option value="1" selected>Con Libreta</option>
                                    <option value="0">Sin Libreta</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <span>Tipo de documento</span>
                                <select id="tipdoc" class="form-select" aria-label="Default select example" required onchange="tipdoc(this.value)">
                                    <option value="E" selected>EFECTIVO</option>
                                    <option value="C">CON CHEQUE</option>
                                    <?php
                                    // $tipdoc = mysqli_query($conexion, "SELECT * FROM `aprtipdoc`");
                                    // while ($tip = mysqli_fetch_array($tipdoc)) {
                                    //     echo '<option value="' . $tip['codtip'] . '">' . $tip['descripcion'] . '</option>';
                                    // }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="container contenedort" id="region_cheque" style="display: none; max-width: 100% !important;">
                        <h6>DATOS DEL BANCO DONDE SE EMITIRÁ EL CHEQUE</h6>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="bancoid" onchange="buscar_cuentas()">
                                        <option value="0" disabled selected>Seleccione un banco</option>
                                        <?php
                                        $bancos = mysqli_query($conexion, "SELECT * FROM tb_bancos WHERE estado='1'");
                                        while ($banco = mysqli_fetch_array($bancos)) {
                                            echo '<option  value="' . $banco['id'] . '">' . $banco['id'] . " - " . $banco['nombre'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="bancoid">Banco</label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="cuentaid" onchange="cheque_automatico(this.value,0)">
                                        <option value="0">Seleccione una cuenta</option>
                                    </select>
                                    <label for="cuentaid">No. de Cuenta</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mt-2">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="negociable">
                                        <option value="0">No Negociable</option>
                                        <option value="1">Negociable</option>
                                    </select>
                                    <label for="negociable">Tipo cheque</label>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3 mt-2">
                                <div class="form-floating">
                                    <input type="number" class="form-control" id="numcheque" placeholder="Numero de cheque" step="1">
                                    <label for="numcheque">No. de Cheque</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-1 pt-2 justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <?php if ($bandera == "" && $id != "0") {
                            ?>
                                <button type="button" id="btnSave" class="btn btn-outline-success" onclick="confirmSaveaprt('R')">
                                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                                </button>
                            <?php
                            }
                            ?>
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
            <script>
                function confirmSaveaprt(action) {

                    var cantidad = document.getElementById("monto").value;

                    Swal.fire({
                        title: "Deseas " + "Retirar" + " la cantidad de Q." + cantidad + "?",
                        text: " ",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonText: "Sí, " + (action === 'R' ? "Guardar" : ""),
                        confirmButtonColor: '#4CAF50',
                        cancelButtonText: "Cancelar"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            obtiene(['ccodaport', 'dfecope', 'cnumdoc', 'monto', 'cuotaIngreso', 'numcheque'], ['salida', 'tipdoc', 'bancoid', 'cuentaid', 'negociable'], [], 'cdaportmov', '0', ['<?php echo $id; ?>', action]);
                        }
                    });
                }
            </script>
        <?php
        }
        break;

    case 'ActualizacionLibreta': {
            $id = $_POST["xtra"];
            $datoscli = mysqli_query($conexion, "SELECT * FROM `aprcta` WHERE `ccodaport`=$id");
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
            <!--Aho-1-ImprsnLbrta Impresión Libreta -->
            <div class="card">
                <input type="text" id="file" value="APRT_2" style="display: none;">
                <input type="text" id="condi" value="ActualizacionLibreta" style="display: none;">
                <div class="card-header">Actualización de libreta</div>
                <div class="card-body">
                    <div class="container contenedort">
                        <!--Aho_0_iMprsnLbrt Libreta-->
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <div class="row">
                                    <span class="input-group-addon col-8">Cuenta de Aportación</span>
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
                            <div class="col-md-5">
                                <br>
                                <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Aplicar cuenta ingresada" onclick="aplicarcod('ccodaport')">
                                    <i class="fa fa-check-to-slot"></i>
                                </button>
                                <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Buscar cuenta" data-bs-toggle="modal" data-bs-target="#findaportcta">
                                    <i class="fa fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </div>
                        <?php if ($bandera != "" && $id != "0") {
                            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $bandera . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
                        }
                        ?>
                        <!--Aho_0_iMprsnLbrt Libreta-->
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
                    <!--Aho_0_iMprsnLbrt Borones, Imprimir, Cancelar, Salir-->
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <button type="button" id="btnSave" class="btn btn-outline-success" onclick="creaLib('<?php echo $id; ?>')">
                                <i class="fa-solid fa-print"></i> Imprimir
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

    case 'Certificados_aprt': {
            $codusu = $_SESSION['id'];
            $id  = $_POST["xtra"];
        ?>

            <div class="card">
                <input type="text" id="file" value="APRT_2" style="display: none;">
                <input type="text" id="condi" value="Certificados_aprt" style="display: none;">
                <div class="card-header">Actualización de libreta</div>
                <div class="card-body">
                    <div class="container contenedort" style="padding: 10px 8px 10px 8px !important;">
                        <!--Aho_0_iMprsnLbrt Libreta-->
                        <div class="row">
                            <div class="col">
                                <div class="table-responsive">
                                    <table id="table_id2" class="table table-hover table-border">
                                        <thead class="text-light table-head-aprt">
                                            <tr>
                                                <th>Crt.</th>
                                                <th>Codigo de cliente</th>
                                                <th>Cuenta</th>
                                                <th>Monto</th>
                                                <th>Fec. Certificado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="categoria_tb">
                                            <?php
                                            $query = mysqli_query($conexion, "SELECT apr.* FROM `aprcrt` apr INNER JOIN tb_cliente cli on cli.idcod_cliente=apr.ccodcli");
                                            while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
                                                $idcrt = utf8_encode($row["id_crt"]);
                                                $codcrt = utf8_encode($row["ccodcrt"]);
                                                $codcli = utf8_encode($row["ccodcli"]);
                                                $ccodaport = utf8_encode($row["ccodaport"]);
                                                $monto = utf8_encode($row["montoapr"]);
                                                $fecap = utf8_encode($row["fec_crt"]);
                                                $estado = utf8_encode($row["estado"]);

                                                ($estado == "I" || $estado == "R") ? $bt = '<button type="button" class="btn btn-warning ms-1" title="Imprimir certificado" onclick="modal_cambio_certif(' . $idcrt . ',' . $ccodaport . ',' . $codusu . ')">
                                        <i class="fa-solid fa-arrow-rotate-left"></i>
                                        </button>
                                        </td>
                                        </tr>' : $bt = '</td></tr>';

                                                echo '<tr>
                                                <td>' . $codcrt . ' </td>
                                                <td>' . $codcli . ' </td>
                                                <td>' . $ccodaport . ' </td>
                                                <td>' . $monto . '</td>
                                                <td>' . $fecap . '</td>
                                                <td>
                                                    <button type="button" class="btn btn-primary" title="Añadir beneficiarios" onclick="printdiv(`benaport`, `#cuadro`, `APRT_0`,`' . $ccodaport . '`)">
                                                        <i class="fa-solid fa-people-line"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-success" title="Imprimir certificado" onclick="imprimir_certificado_aprt(' . $idcrt . ',`crud_aportaciones`,`pdf_certificado_aprt`,`I`,`0`,' . $codusu . ')">
                                                        <i class="fa-solid fa-print"></i>
                                                    </button>' . $bt;
                                            }
                                            ?>
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!--botones de nuevo certificado, cancelar y salir-->
                    <div class="row mt-2 justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <button type="button" id="btnnew" class="btn btn-outline-success" onclick="printdiv('nuevoCertificado', '#cuadro', 'APRT_2', '0')">
                                <i class="fa fa-file"></i> Nuevo certificado
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
                <script>
                    //Datatable para parametrizacion
                    $(document).ready(function() {
                        convertir_tabla_a_datatable("table_id2");
                    });
                </script>
            </div>
        <?php
        }
        break;
    case 'nuevoCertificado': {
            $id  = $_POST["xtra"];
            $codusu = $_SESSION['id'];

            $datoscli = mysqli_query($conexion, "SELECT * FROM `aprcta` WHERE `ccodaport`=$id");
            $bandera = "Cuenta de aportación no existe";
            while ($da = mysqli_fetch_array($datoscli, MYSQLI_ASSOC)) {
                $idcli = utf8_encode($da["ccodcli"]);
                $nit = utf8_encode($da["num_nit"]);
                $nlibreta = utf8_encode($da["nlibreta"]);
                $fecha_apertura = utf8_encode($da["fecha_apertura"]);
                $bandera = "";
            }
            if ($bandera == "") {
                $data = mysqli_query($conexion, "SELECT `short_name` FROM `tb_cliente` WHERE `idcod_cliente`='$idcli'");
                $nombre = "";
                $bandera = "No existe el cliente relacionado a la cuenta de aportación";
                while ($dat = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                    $nombre = utf8_encode($dat["short_name"]);
                    $bandera = "";
                }
            }
        ?>

            <div class="container">
                <div class="text" style="text-align:center">ADICION DE CERTIFICADOS DE APORTACIONES</div>
                <input type="text" id="condi" value="nuevoCertificado" hidden>
                <input type="text" id="file" value="APRT_2" hidden>
                <div class="card">
                    <div class="card-header">Adición de certificados</div>
                    <div class="card-body">
                        <div class="accordion" id="accordionExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" aria-expanded="true" aria-controls="collapseOne">
                                        IDENTIFICACION DEL CLIENTE
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <div class="row mb-2">
                                            <div class="col-md-5">
                                                <div class="row">
                                                    <span class="input-group-addon col-8">Cuenta de Aportación</span>
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
                                            <div class="col-md-5">
                                                <br>
                                                <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Aplicar cuenta ingresada" onclick="aplicarcod('ccodaport')">
                                                    <i class="fa fa-check-to-slot"></i>
                                                </button>
                                                <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Buscar cuenta" data-bs-toggle="modal" data-bs-target="#findaportcta">
                                                    <i class="fa fa-magnifying-glass"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!--Aho_0_ApertCuenAhor Búsqueda NIT-->
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <div>
                                                    <span class="input-group-addon col-8">Cliente</span>
                                                    <input type="text" class="form-control " id="nomcli" placeholder="" value="<?php if ($bandera == "") echo $nombre; ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div>
                                                    <span class="input-group-addon col-8">Codigo de cliente</span>
                                                    <input type="text" class="form-control " id="codcli" placeholder="" value="<?php if ($bandera == "") echo $idcli; ?>" readonly>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <div>
                                                    <span class="input-group-addon col-8">NIT</span>
                                                    <input type="text" class="form-control " id="nit" placeholder="" value="<?php if ($bandera == "") echo $nit; ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="input-group-addon col-8">Fecha apertura de cuenta</span>
                                                <input type="date" class="form-control" id="fecaper" required="required" value="<?php if ($bandera == "") echo $fecha_apertura; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <?php if ($bandera != "" && $id != "0") {
                                                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $bandera . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button" type="button" aria-expanded="false" aria-controls="collapseTwo">
                                        DATOS DE LA CUENTA
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <span class="input-group-addon col-8">Certificado</span>
                                                <input type="text" aria-label="Certificado" id="certif_n" class="form-control  col" placeholder="" required>
                                            </div>
                                            <div class="col-sm-4">
                                                <span class="input-group-addon col-8">Monto</span>
                                                <input type="number"  class="form-control" step="0.01" id="monapr_n" placeholder="0.00" required="required">
                                            </div>
                                            <div class="col-sm-4">
                                                <span class="input-group-addon col-8">Comprobante de caja </span>
                                                <input type="text" class="form-control" id="norecibo">
                                            </div>

                                        </div>
                                        <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" id="toastalert">
                                            <div class="toast-header">
                                                <strong class="me-auto">Advertencia</strong>
                                                <small class="text-muted">Tomar en cuenta</small>
                                                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                                            </div>
                                            <div class="toast-body bg-danger text-white" id="body_text">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button" type="button" aria-expanded="false" aria-controls="collapseTwo">
                                        BENEFICIARIOS DE LA CUENTA
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <table id="table_id2" class="table table-hover table-border">
                                                <thead class="text-light table-head-aprt">
                                                    <tr>
                                                        <th>DPI</th>
                                                        <th>Nombre Completo</th>
                                                        <th>Fec. Nac.</th>
                                                        <th>Parentesco</th>
                                                        <th>%</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="categoria_tb">
                                                    <?php
                                                    $filas = 0;
                                                    $total = 0;
                                                    if ($bandera == "") {
                                                        $queryben = mysqli_query($conexion, "SELECT * FROM `aprben` WHERE `codaport`='$id'");
                                                        $filas = mysqli_fetch_assoc($queryben);
                                                        if ($filas == null) {
                                                            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">No puede generar su certificado, tiene que agregar beneficiarios a la cuenta
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                        </div>';
                                                        } else {
                                                            $queryben = mysqli_query($conexion, "SELECT * FROM `aprben` WHERE `codaport`='$id'");
                                                            $filas = "1";
                                                            while ($rowq = mysqli_fetch_array($queryben, MYSQLI_ASSOC)) {
                                                                $idaprben = utf8_encode($rowq["id_ben"]);
                                                                $bennom = utf8_encode($rowq["nombre"]);
                                                                $bendpi = utf8_encode($rowq["dpi"]);
                                                                $bendire = utf8_encode($rowq["direccion"]);
                                                                $benparent = utf8_encode($rowq["codparent"]);
                                                                $parentdes = parenteco($benparent);
                                                                $benfec = utf8_encode($rowq["fecnac"]);
                                                                $benporcent = utf8_encode($rowq["porcentaje"]);
                                                                $total = $total + $benporcent;
                                                                $bentel = utf8_encode($rowq["telefono"]);
                                                                echo '<tr>
                                                                    <td>' . $bendpi . ' </td>
                                                                    <td>' . $bennom . ' </td>
                                                                    <td>' . $benfec . ' </td>
                                                                    <td>' . $parentdes . '</td>
                                                                    <td>' . $benporcent . '</td>
                                                                    </tr>';
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>

                                        </div>
                                        <div class="row">
                                            <!--TOTAL-->
                                            <div class="col-md-3">
                                                <label for="">Total: <?php if ($bandera == "") echo $total; ?> %</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-items-md-center mt-3">
                                <div class="col align-items-center" id="modal_footer">
                                    <button type="button" class="btn btn-outline-success" onclick="obtiene([`certif_n`,`ccodaport`,`codcli`,`nit`,`monapr_n`,`fecaper`,`norecibo`],[],[],`create_certificado_aprt`,`0`,['<?php echo $id; ?>','<?php echo $bandera; ?>','<?php echo $codusu; ?>','<?php echo $filas; ?>','<?php echo $total; ?>'])">
                                        <i class="fa fa-floppy-disk"></i> Guardar
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="printdiv('Certificados_aprt', '#cuadro', 'APRT_2', '0')">
                                        <i class="fa-solid fa-ban"></i> Cancelar
                                    </button>
                                    <button type="button" class="btn btn-outline-warning" onclick="salir()">
                                        <i class="fa-solid fa-circle-xmark"></i> Salir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php
        }
        break;
    case 'actualizar_Certificado': {
            $id  = $_POST["xtra"];
            $codusu = $_SESSION['id'];

            $datoscli = mysqli_query($conexion, "SELECT crt.ccodcrt, cta.ccodaport, cl.short_name, cl.idcod_cliente, cl.no_tributaria, crt.fec_crt, crt.montoapr, cta.fecha_apertura,cta.norecibo
        FROM
          aprcta AS cta 
          INNER JOIN aprcrt AS crt 
            ON cta.ccodaport = crt.ccodaport 
          INNER JOIN tb_cliente AS cl 
            ON crt.ccodcli = cl.idcod_cliente
            WHERE crt.id_crt='$id'");

            while ($da = mysqli_fetch_array($datoscli, MYSQLI_ASSOC)) {
                $codcertif = utf8_encode($da["ccodcrt"]);
                $norecibo = utf8_encode($da["norecibo"]);
                $ccodaport = utf8_encode($da["ccodaport"]);
                $nombre = utf8_encode($da["short_name"]);
                $idcli = utf8_encode($da["idcod_cliente"]);
                $nit = utf8_encode($da["no_tributaria"]);
                $fecha_apertura = utf8_encode($da["fecha_apertura"]);
                $fecha_creacion = utf8_encode($da["fec_crt"]);
                $monto = utf8_encode($da["montoapr"]);
                $bandera = "";
            }
            $hoy = date("Y-m-d");

        ?>
            <!--Aho_0_ApertCuenAhor Inicio de Ahorro Sección 0 Apertura de Cuenta-->
            <div class="container">
                <div class="text" style="text-align:center">ADICION DE CERTIFICADOS DE PLAZO FIJO</div>
                <input type="text" id="condi" value="actualizar_Certificado" hidden>
                <input type="text" id="file" value="APRT_2" hidden>
                <div class="card">
                    <div class="card-header">Adición de certificados</div>
                    <div class="card-body">
                        <div class="accordion" id="accordionExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" aria-expanded="true" aria-controls="collapseOne">
                                        IDENTIFICACION DEL CLIENTE
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <div>
                                                    <span class="input-group-addon col-8">Codigo certificado</span>
                                                    <input type="text" aria-label="Certificado" id="certif" class="form-control  col" value="<?php echo $codcertif; ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div>
                                                    <span class="input-group-addon col-8">Cuenta de aportación</span>
                                                    <input type="text" class="form-control " id="ccodaport" placeholder="" value="<?php echo $ccodaport; ?>" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <!--Aho_0_ApertCuenAhor Búsqueda NIT-->
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <div>
                                                    <span class="input-group-addon col-8">Cliente</span>
                                                    <input type="text" class="form-control " id="nomcli" placeholder="" value="<?php echo $nombre; ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div>
                                                    <span class="input-group-addon col-8">Codigo de cliente</span>
                                                    <input type="text" class="form-control " id="codcli" placeholder="" value="<?php echo $idcli; ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <div>
                                                    <span class="input-group-addon col-8">NIT</span>
                                                    <input type="text" class="form-control " id="nit" placeholder="" value="<?php echo $nit; ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="input-group-addon col-8">Fecha apertura de cuenta</span>
                                                <input type="date" class="form-control" id="fecaper" required="required" value="<?php echo $fecha_apertura; ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button" type="button" aria-expanded="false" aria-controls="collapseTwo">
                                        DATOS DE LA CUENTA
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <span class="input-group-addon col-8">Fecha creacion de certificado</span>
                                                <input type="date" class="form-control" id="feccrt" required="required" value="<?php echo $fecha_creacion; ?>" readonly>
                                            </div>
                                            <div class="col-sm-4">
                                                <span class="input-group-addon col-8">Monto</span>
                                                <input type="number" step="0.01" class="form-control" id="monapr" placeholder="0.00" required="required" value="<?php echo $monto; ?>">
                                            </div>
                                            <div class="col-sm-4">
                                                <span class="input-group-addon col-8">Comprobante de caja</span>
                                                <input type="text" class="form-control" id="monapr" value="<?php echo $norecibo; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button" type="button" aria-expanded="false" aria-controls="collapseTwo">
                                        BENEFICIARIOS DE LA CUENTA
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <table id="table_id2" class="table table-hover table-border">
                                                <thead class="text-light table-head-aprt">
                                                    <tr>
                                                        <th>DPI</th>
                                                        <th>Nombre Completo</th>
                                                        <th>Fec. Nac.</th>
                                                        <th>Parentesco</th>
                                                        <th>%</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="categoria_tb">
                                                    <?php
                                                    $total = 0;

                                                    $queryben = mysqli_query($conexion, "SELECT * FROM `aprben` WHERE `codaport`='$ccodaport'");
                                                    while ($rowq = mysqli_fetch_array($queryben, MYSQLI_ASSOC)) {
                                                        $idaprben = utf8_encode($rowq["id_ben"]);
                                                        $bennom = utf8_encode($rowq["nombre"]);
                                                        $bendpi = utf8_encode($rowq["dpi"]);
                                                        $bendire = utf8_encode($rowq["direccion"]);
                                                        $benparent = utf8_encode($rowq["codparent"]);
                                                        $parentdes = parenteco($benparent);
                                                        $benfec = utf8_encode($rowq["fecnac"]);
                                                        $benporcent = utf8_encode($rowq["porcentaje"]);
                                                        $total = $total + $benporcent;
                                                        $bentel = utf8_encode($rowq["telefono"]);
                                                        echo '<tr>
                                            <td>' . $bendpi . ' </td>
                                            <td>' . $bennom . ' </td>
                                            <td>' . $benfec . ' </td>
                                            <td>' . $parentdes . '</td>
                                            <td>' . $benporcent . '</td>
                                            </tr>';
                                                    }


                                                    ?>
                                                </tbody>
                                            </table>

                                        </div>
                                        <div class="row">
                                            <!--TOTAL-->
                                            <div class="col-md-3">
                                                <label for="">Total: <?php echo $total; ?> %</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-items-md-center mt-3">
                                <div class="col align-items-center" id="modal_footer">
                                    <button type="button" class="btn btn-outline-success" onclick="obtiene([`monapr`],[],[],`update_certificado_aprt`,`0`,['<?php echo $id; ?>','<?php echo $codusu; ?>','<?php echo $total; ?>'])">
                                        <i class="fa fa-floppy-disk"></i> Guardar
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="printdiv('Certificados_aprt', '#cuadro', 'APRT_2', '0')">
                                        <i class="fa-solid fa-ban"></i> Cancelar
                                    </button>
                                    <button type="button" class="btn btn-outline-warning" onclick="salir()">
                                        <i class="fa-solid fa-circle-xmark"></i> Salir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php
        }
        break;
}
function executequery($query, $params, $typparams, $conexion)
{
    $stmt = $conexion->prepare($query);
    $aux = mysqli_error($conexion);
    if ($aux) {
        return ['ERROR: ' . $aux, false];
    }
    $types = '';
    $bindParams = [];
    $bindParams[] = &$types;
    $i = 0;
    foreach ($params as &$param) {
        // $types .= 's';
        $types .= $typparams[$i];
        $bindParams[] = &$param;
        $i++;
    }
    call_user_func_array(array($stmt, 'bind_param'), $bindParams);
    if (!$stmt->execute()) {
        return ["Error en la ejecución de la consulta: " . $stmt->error, false];
    }
    $data = [];
    $resultado = $stmt->get_result();
    $i = 0;
    while ($fila = $resultado->fetch_assoc()) {
        $data[$i] = $fila;
        $i++;
    }
    $stmt->close();
    return [$data, true];
}
?>

<!-- moda para cambio de certificado -->
<div class="modal fade" id="cambio_certif" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Reimpresion de certificado</h1>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-12">
                        <!-- titulo -->
                        <span class="input-group-addon col-8">Cuenta de Aportación</span>
                        <div class="input-group">
                            <input type="text" class="form-control " id="id_modal_crt" readonly hidden>
                            <input type="text" class="form-control " id="id_codusu_crt" readonly hidden>
                            <input type="text" class="form-control " id="ccodaport_modal_crt" readonly>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <span class="input-group-addon col-8">Nuevo código de certificado</span>
                        <input type="text" aria-label="Certificado" id="certif_modal" class="form-control  col" placeholder="" required>
                    </div>
                    <div class="col-sm-6" hidden>
                        <span class="input-group-addon col-8">Monto</span>
                        <input type="float" class="form-control" id="monapr_n" placeholder="0.00" required="required">
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="cancelar_ben" onclick="create_cambio_certif()">Reimpresión certificado</button>
                <button type="button" class="btn btn-secondary" id="cancelar_ben" onclick="cancelar_cambio_certif()">Cancelar</button>
            </div>
        </div>
    </div>
</div>