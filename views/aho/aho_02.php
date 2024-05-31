<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];

switch ($condi) {
        //Desposito de ahorro
    case 'DepoAhorr':
        $id = $_POST["xtra"];
        $usuario = $_SESSION['id'];
        $ofi = $_SESSION['agencia'];
        $datos = [
            "id_tipo" => "",
        ];
        $bandera = "";
        if ($id != '0') {
            try {
                $query = "SELECT cta.ccodcli,cta.estado,cta.nlibreta,cli.no_tributaria num_nit,cli.short_name 
                            FROM `ahomcta` cta INNER JOIN tb_cliente cli ON cli.idcod_cliente=cta.ccodcli 
                            WHERE `ccodaho`=? AND cli.estado=1";

                $response = executequery($query, [$id], ['s'], $conexion);
                if (!$response[1]) {
                    throw new Exception($response[0]);
                }
                $data = $response[0];
                $flag = ((count($data)) > 0) ? true : false;
                if (!$flag) {
                    throw new Exception("Cuenta de ahorro no existe");
                }
                $da = $data[0];
                $idcli = utf8_encode($da["ccodcli"]);
                $nit = utf8_encode($da["num_nit"]);
                $nlibreta = ($da["nlibreta"]);
                $ultimonum = lastnumlin($id, $nlibreta, "ahommov", "ccodaho", $conexion);
                $numlib = numfront(substr($id, 6, 2), "ahomtip") + numdorsal(substr($id, 6, 2), "ahomtip");
                if ($ultimonum >= $numlib) {
                    throw new Exception("El número de líneas en libreta ha llegado a su límite, se recomienda abrir otra libreta");
                }
                $estado = ($da["estado"]);
                if ($estado != "A") {
                    throw new Exception("Cuenta de ahorros Inactiva");
                }
                $nombre = ($da["short_name"]);
            } catch (Exception $e) {
                //Captura el error
                $bandera = $e->getMessage();
                $bandera_error = true;
            }
        }
        //Flag para cativar el correlativo automaticamente (1 activo, 0 desactivado)
        $flag_correlativo = 1;
        if ($flag_correlativo == 1 && $id != NULL) {
            //Consulta para obtener el numero correlativo
            // $dato = 2 ;
            $sql = "SELECT MAX(CAST(mov.cnumdoc AS SIGNED)) FROM ahommov mov
                        INNER JOIN tb_usuario usu ON usu.id_usu=mov.codusu
                        INNER JOIN tb_agencia ofi ON ofi.id_agencia=usu.id_agencia
                        WHERE ctipope = 'D' AND crazon = 'DEPOSITO' AND cestado = 1 AND ofi.id_agencia=?";
            $stmt1 = $conexion->prepare($sql);

            $stmt1->bind_param("i", $ofi);
            $dato = $stmt1->execute();

            $stmt1->bind_result($correlativo);
            $stmt1->fetch();
            $stmt1->close();
        }

?>
        <!--DEPOSITO DE AHORROS-->
        <div class="card">
            <input type="text" id="file" value="aho_02" style="display: none;">
            <input type="text" id="condi" value="DepoAhorr" style="display: none;">
            <div class="card-header">Deposito de Ahorro </div>

            <div class="card-body">
                <!--Aho_1_DepoAhorr Busqueda Cuenta Ahorro-->
                <div class="container contenedort">
                    <div class="row mb-3">
                        <div class="col-sm-5">
                            <div>
                                <span class="input-group-addon col-8">Cuenta de Ahorro</span>
                                <input type="text" class="form-control " id="ccodaho" required placeholder="   -   -  -  " value="<?php if ($bandera == "" && $id != "0") echo $id; ?>">
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <br>
                            <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Aplicar cuenta ingresada" onclick="aplicarcod('ccodaho')">
                                <i class="fa fa-check-to-slot"></i>
                            </button>
                            <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Buscar cuenta" data-bs-toggle="modal" data-bs-target="#findahomcta">
                                <i class="fa fa-magnifying-glass"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <div>
                                <span class="input-group-addon col-8">Nombre</span>
                                <input type="text" class="form-control " id="name" value="<?php if ($bandera == ""  && $id != "0") echo $nombre; ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <?php if ($bandera != "" && $id != "0") {
                        echo '<div class="alert alert-danger" role="alert">' . $bandera . '</div>';
                    }
                    ?>
                </div>
                <!--Aho_1_DepoAhorr Nombre-->
                <div class="container contenedort">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <div>
                                <span class="input-group-addon col-8">No.Documento</span>
                                <input type="text" class="form-control " id="cnumdoc" required value="<?php echo ($id != 0) ? (($flag_correlativo == 1) ? ((int)$correlativo + 1) : '') : '' ?>">
                            </div>
                        </div>
                        <div class="col-sm-1"></div>
                    </div>
                    <!--Aho_1_DepoAhorr Cantidad-Libreta-->
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <div>
                                <span class="input-group-addon col-8">Cantidad</span>
                                <input type="number" step="0.01" class="form-control " id="monto" required placeholder="0.00" min="0.01">
                            </div>
                        </div>
                        <div class="col-sm-1"></div>
                        <div class="col-sm-3">
                            <div>
                                <span class="input-group-addon col-8">Libreta</span>
                                <input type="number" class="form-control" id="lib" value="<?php if ($bandera == ""  && $id != "0") echo $nlibreta; ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <!--Aho_1_DepoAhorr Fecha-Compensado-->
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <div>
                                <span class="input-group-addon col-8">Fecha</span>
                                <input type="date" class="form-control " id="dfecope" value="<?php echo date("Y-m-d"); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <!--Aho_1_DepoAhorr Transacción-Salida-No.Linea-Boton-->
                <div class="container contenedort">
                    <br>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <span class="input-group-addon col-8">Transacción</span>
                            <select class="form-select  col-md-12" aria-label="Default select example">
                                <?php if ($bandera == "" && $id != "0")
                                    echo '<option selected>' . tipocuenta(SUBSTR($id, 6, 2), "ahomtip", "nombre", $conexion) . '</option>';
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div>
                                <span class="input-group-addon col-8">Salida</span>
                                <select class="form-select  col-md-12" aria-label="Default select example" id="salida">
                                    <option value="1" selected>Con Libreta</option>
                                    <option value="0">Sin Libreta</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div>
                                <span class="input-group-addon col-8">Tipo de Doc.</span>
                                <select class="form-select  col-sm-12" aria-label="Default select example" id="tipdoc" onchange="tipdoc(this.value)">
                                    <option value="E" selected>EFECTIVO</option>
                                    <option value="D">CON BOLETA DE BANCO</option>
                                    <?php
                                    // $tipdoc = mysqli_query($conexion, "SELECT * FROM `ahotipdoc` WHERE id<=3");
                                    // while ($tip = mysqli_fetch_array($tipdoc)) {
                                    //     $tipo = ($tip['codtip'] == "B") ? "D" : $tip['codtip'];
                                    //     $destipo = ($tip['codtip'] == "B") ? "CON BOLETA DE BANCO" : $tip['descripcion'];
                                    //     echo '<option value="' . $tipo . '">' . $destipo . '</option>';
                                    // }
                                    ?>
                                </select>
                            </div>
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
                <!--Aho_1_DepoAhorr No.Cheque-Tipo de Cheque-->
                <div style="display: none;">
                    <input class="form-check-input" type="radio" name="nada" id=" " value="nada" checked>
                    <label class="form-check-label col">Todo</label>
                </div>
                <!--Aho_1_DepoAhorr Botones-->
                <div class="row mb-3 justify-items-md-center">
                    <div class="col align-items-center" id="modal_footer">
                        <?php if ($bandera == "" && $id != "0") {
                        ?>
                            <button type="button" id="btnSave" class="btn btn-outline-success" onclick="confirmSave('D')">
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
            function confirmSave(action) {
                var cantidad = document.getElementById("monto").value;
                Swal.fire({
                    title: "Deseas " + (action === 'D' ? "Depositar" : "Retirar") + " la cantidad de Q." + cantidad + "?",
                    text: " ",
                    icon: "question",

                    showCancelButton: true,
                    confirmButtonText: "Sí, " + (action === 'D' ? "Depositar" : "Retirar"),
                    confirmButtonColor: '#4CAF50', // Color verde
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        obtiene(['ccodaho', 'dfecope', 'cnumdoc', 'monto', 'cnumdocboleta'], ['salida', 'tipdoc', 'bancoid', 'cuentaid'], [], 'cdahommov', '0', ['<?php echo $id; ?>', action]);
                    }
                });
            }
        </script>
    <?php
        break;
    case 'RetiAhorr':
        $id = $_POST["xtra"];
        $usuario = $_SESSION['id'];
        $ofi = $_SESSION['agencia'];
        $datos = [
            "id_tipo" => "",
        ];
        $bandera = "";
        if ($id != '0') {
            try {
                //Validar si la cuenta esta como garantia
                $res = $conexion->query("SELECT cta.ret AS std  FROM tb_garantias_creditos tgc 
                INNER JOIN cli_garantia cg ON cg.idGarantia = tgc.id_garantia 
                INNER JOIN ahomcta cta ON cta.ccodaho = cg.descripcionGarantia
                INNER JOIN cremcre_meta cm ON cm.CCODCTA = tgc.id_cremcre_meta
                WHERE cta.ccodaho = '$id'");

                $aux = mysqli_error($conexion);
                if (!$res || $aux) {
                    echo json_encode(['Erro 2000', '0']);
                    $conexion->rollback();
                    return;
                }


                $std = $res->fetch_assoc();

                $query = "SELECT cta.ccodcli,cta.estado,cta.nlibreta,cli.no_tributaria num_nit,cli.short_name 
                        FROM `ahomcta` cta INNER JOIN tb_cliente cli ON cli.idcod_cliente=cta.ccodcli 
                        WHERE `ccodaho`=? AND cli.estado=1";

                $response = executequery($query, [$id], ['s'], $conexion);
                if (!$response[1]) {
                    throw new Exception($response[0]);
                }
                $data = $response[0];
                $flag = ((count($data)) > 0) ? true : false;
                if (!$flag) {
                    throw new Exception("Cuenta de ahorro no existe");
                }
                $da = $data[0];
                $idcli = utf8_encode($da["ccodcli"]);
                $nit = utf8_encode($da["num_nit"]);
                $nlibreta = ($da["nlibreta"]);
                $ultimonum = lastnumlin($id, $nlibreta, "ahommov", "ccodaho", $conexion);
                $numlib = numfront(substr($id, 6, 2), "ahomtip") + numdorsal(substr($id, 6, 2), "ahomtip");
                if ($ultimonum >= $numlib) {
                    throw new Exception("El número de líneas en libreta ha llegado a su límite, se recomienda abrir otra libreta");
                }
                $estado = ($da["estado"]);
                if ($estado != "A") {
                    throw new Exception("Cuenta de ahorros Inactiva");
                }
                $nombre = ($da["short_name"]);
            } catch (Exception $e) {
                //Captura el error
                $bandera = $e->getMessage();
                $bandera_error = true;
            }
        }

        //Flag para cativar el correlativo automaticamente (1 activo, 0 desactivado)
        $flag_correlativo = 1;
        if ($flag_correlativo == 1 && $id != NULL) {
            //Consulta para obtener el numero correlativo
            // $dato = 2;
            $sql = "SELECT MAX(CAST(mov.cnumdoc AS SIGNED)) FROM ahommov mov
                        INNER JOIN tb_usuario usu ON usu.id_usu=mov.codusu
                        INNER JOIN tb_agencia ofi ON ofi.id_agencia=usu.id_agencia
                        WHERE ctipope = 'R' AND crazon = 'RETIRO' AND cestado = 1 AND ofi.id_agencia=?";
            $stmt1 = $conexion->prepare($sql);

            $stmt1->bind_param("i", $ofi);
            $dato = $stmt1->execute();

            $stmt1->bind_result($correlativo);
            $stmt1->fetch();
            $stmt1->close();
        }

    ?>
        <!--Aho_1_DepoAhorr Retiro DE AHORROS-->
        <input type="text" id="idControl" hidden value="<?php echo (isset($std['std'])) ? $std['std'] : '' ?>">
        <div class="card" id='carPrincipal'>
            <input type="text" id="file" value="aho_02" style="display: none;">
            <input type="text" id="condi" value="RetiAhorr" style="display: none;">
            <div class="card-header">Retiro de Ahorro</div>
            <div class="card-body">
                <!--Aho_1_DepoAhorr Busqueda Cuenta Ahorro-->
                <div class="container contenedort">
                    <div class="row mb-3">
                        <div class="col-sm-5">
                            <div>
                                <span class="input-group-addon col-8">Cuenta de Ahorro</span>
                                <input type="text" class="form-control " id="ccodaho" required placeholder="   -   -  -  " value="<?php if ($bandera == "" && $id != 0) echo $id; ?>">
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <br>
                            <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Aplicar cuenta ingresada" onclick="aplicarcod('ccodaho')">
                                <i class="fa fa-check-to-slot"></i>
                            </button>
                            <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Buscar cuenta" data-bs-toggle="modal" data-bs-target="#findahomcta">
                                <i class="fa fa-magnifying-glass"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <div>
                                <span class="input-group-addon col-8">Nombre</span>
                                <input type="text" class="form-control " id="name" value="<?php if ($bandera == ""  && $id != 0) echo $nombre; ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <?php if ($bandera != "" && $id != "0") {
                        echo '<div class="alert alert-danger" role="alert">' . $bandera . '</div>';
                    }
                    ?>
                </div>
                <!--Aho_1_DepoAhorr Nombre-->
                <div class="container contenedort">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <div>
                                <span class="input-group-addon col-8">No.Documento</span>
                                <input type="text" class="form-control " id="cnumdoc" required value="<?php echo ($id != 0) ? (($flag_correlativo == 1) ? ((int)$correlativo + 1) : '') : '' ?>">
                            </div>
                        </div>
                        <div class="col-sm-1"></div>
                    </div>
                    <!--Aho_1_DepoAhorr Cantidad-Libreta-->
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <div>
                                <span class="input-group-addon col-8">Cantidad a Retirar</span>
                                <input type="number" step="any" class="form-control " id="monto" required placeholder="0.00" min="1">
                            </div>
                        </div>
                        <div class="col-sm-1"></div>
                        <div class="col-sm-3">
                            <div>
                                <span class="input-group-addon col-8">Libreta</span>
                                <input type="number" class="form-control" id="lib" value="<?php if ($bandera == ""  && $id != 0) echo $nlibreta; ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <!--Aho_1_DepoAhorr Fecha-Compensado-->
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <div>
                                <span class="input-group-addon col-8">Fecha</span>
                                <input type="date" class="form-control " id="dfecope" value="<?php echo date("Y-m-d"); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <!--Aho_1_DepoAhorr Transacción-Salida-No.Linea-Boton-->
                <div class="container contenedort">
                    <br>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <div>
                                <span class="input-group-addon col-8">Transacción</span>
                                <select class="form-select  col-md-12" aria-label="Default select example">
                                    <?php if ($bandera == ""  && $id != 0)
                                        echo '<option selected>' . tipocuenta(SUBSTR($id, 6, 2), "ahomtip", "nombre", $conexion) . '</option>';
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div>
                                <span class="input-group-addon col-8">Salida</span>
                                <select class="form-select  col-md-12" aria-label="Default select example" id="salida">
                                    <option value="1" selected>Con Libreta</option>
                                    <option value="0">Sin Libreta</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div>
                                <span class="input-group-addon col-8">Tipo de Doc.</span>
                                <select class="form-select  col-sm-12" aria-label="Default select example" id="tipdoc" onchange="tipdoc(this.value)">
                                    <option value="E" selected>EFECTIVO</option>
                                    <option value="C">CON CHEQUE</option>
                                    <?php
                                    // $tipdoc = mysqli_query($conexion, "SELECT * FROM `ahotipdoc` WHERE id<=3");
                                    // while ($tip = mysqli_fetch_array($tipdoc)) {
                                    //     $tipo = ($tip['codtip'] == "B") ? "C" : $tip['codtip'];
                                    //     $destipo = ($tip['codtip'] == "B") ? "CON CHEQUE" : $tip['descripcion'];
                                    //     echo '<option value="' . $tipo . '">' . $destipo . '</option>';
                                    // }
                                    ?>
                                </select>
                            </div>
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

                <!--Aho_1_DepoAhorr Botones-->
                <div class="row mb-3 justify-items-md-center">
                    <div class="col align-items-center" id="modal_footer">
                        <?php if ($bandera == "" && $id != "0") {
                        ?>
                            <button type="button" id="btnSave" class="btn btn-outline-success" onclick="confirmSave()">
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

        <div class="card" id='car_alt1'>
            <div class="card">
                <div class="card-body">
                    <!-- INI ALERTA -->
                    <div class="alert alert-success" role="alert">
                        <h1 class="alert-heading">Alerta... !!!</h1>
                        <h5 class="alert-heading">Cliente: <?= $da['short_name'] ?></h5>
                        <h5 class="alert-heading">Codigo de cliente: <?= $id ?></h5>
                        <p>En la cuenta <?= $id ?> no se pueden realizar retiros, ya que se encuentra vinculada a un crédito.</p>
                        <hr>
                        <p class="mb-0">El cliente tiene que terminar de cancelar el crédito para realizar retiros en su cuenta.</p>
                    </div>
                    <!-- FIN ALERTA -->
                </div>
            </div>
        </div>

        <script>
            function confirmSave() {
                var cantidad = document.getElementById("monto").value;
                Swal.fire({
                    title: "Deseas Retirar la cantidad de " + "Q." + cantidad + "?",
                    text: " ",
                    icon: "question",
                    showCancelButton: true,
                    showCloseButton: true,
                    confirmButtonText: "Sí, Retirar",
                    confirmButtonColor: '#28B463',
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        //  saveahommov() 
                        //saveahommov(' echo $bandera; ?>', ' echo $usuario; ?>', ' echo $ofi; ?>', 'R');
                        obtiene(['ccodaho', 'dfecope', 'cnumdoc', 'monto', 'numcheque'], ['salida', 'tipdoc', 'bancoid', 'cuentaid', 'negociable'], [], 'cdahommov', '0', ['<?php echo $id; ?>', 'R']);
                    }
                });
            }

            $(document).ready(function() {
                var bandera = $('#idControl').val();
                switch (bandera) {
                    case '0':
                        $("#carPrincipal").hide();
                        $("#car_alt1").show();
                        break;
                    default:
                        $("#carPrincipal").show();
                        $("#car_alt1").hide();
                        break;
                }
            });
        </script>

    <?php
        break;
        //Listado del día
    case 'ListadoDelDia':
        $id = $_POST["xtra"];
        $usuario = $_SESSION['id'];
        $ofi = $_SESSION['agencia'];
    ?>
        <!-- APR_05_LstdCntsActvsDspnbls -->
        <div class="text" style="text-align:center">LISTADO DEL DÍA</div>
        <input type="text" value="ListadoDelDia" id="condi" style="display: none;">
        <input type="text" value="aho_02" id="file" style="display: none;">

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
                                            <input class="form-check-input" type="radio" name="filter_cuenta" id="r_cuenta" value="2" onclick="activar_select_cuentas(this, false,'tipcuenta')">
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
                    <!-- card para seleccionar una cuenta -->
                    <div class="col-6">
                        <div class="card" style="height: 100% !important;">
                            <div class="card-header"><b>Filtro por fecha</b></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col d-flex justify-content-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="filter_fecha" id="r_nofecha" value="1" checked onclick="habdeshab([],['fechaInicio','fechaFinal'])">
                                            <label class="form-check-label" for="r_nofecha">Todo</label>
                                        </div>
                                    </div>
                                    <div class="col d-flex justify-content-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="filter_fecha" id="r_fecha" value="2" onclick="habdeshab(['fechaInicio','fechaFinal'],[])">
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
                            <i class="fa-solid fa-print"></i> Reporte en Excel
                        </button>

                        <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="obtiene([`fechaInicio`,`fechaFinal`],[`tipcuenta`],[`filter_transaccion`,`filter_cuenta`,`filter_fecha`],`reporte_listado_dia`,`pdf`,['<?php echo $usuario; ?>','<?php echo $ofi; ?>'])">
                            <i class="fa-solid fa-print"></i> Reporte en PDF
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
        //Imprime Operaciones Libreta
    case 'updateLibreta':
        $id = $_POST["xtra"];
        $datos = [
            "id_tipo" => "",
        ];
        $datoscli = mysqli_query($conexion, "SELECT * FROM `ahomcta` WHERE `ccodaho`=$id");
        $bandera = "Cuenta de ahorro no existe";
        while ($da = mysqli_fetch_array($datoscli, MYSQLI_ASSOC)) {
            $idcli = ($da["ccodcli"]);
            $nit = ($da["num_nit"]);
            $nlibreta = ($da["nlibreta"]);
            $estado = ($da["estado"]);
            ($estado != "A") ? $bandera = "Cuenta de ahorros Inactiva" : $bandera = "";
            $bandera = "";
        }
        if ($bandera == "") {
            $data = mysqli_query($conexion, "SELECT `short_name` FROM `tb_cliente` WHERE `idcod_cliente`='$idcli'");
            //$data = mysqli_query($conexion, "SELECT `short_name` FROM `tb_cliente` WHERE `idcod_cliente`='$idcli' OR `no_tributaria` = '$nit'");
            $dat = mysqli_fetch_array($data, MYSQLI_ASSOC);
            $nombre = ($dat["short_name"]);
        }
    ?>
        <!--Aho-1-ImprsnLbrta Impresión Libreta -->
        <div class="card">
            <input type="text" id="file" value="aho_02" style="display: none;">
            <input type="text" id="condi" value="updateLibreta" style="display: none;">
            <div class="card-header">Actualizacion de libreta</div>
            <div class="card-body">
                <div class="row contenedort">
                    <!--Aho_0_iMprsnLbrt Libreta-->
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <div>
                                <span class="input-group-addon col-8">Cuenta de Ahorros</span>
                                <input type="text" class="form-control " id="ccodaho" required placeholder="000-000-00-000000" value="<?php if ($bandera == "") echo $id; ?>">
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
                    <?php if ($bandera != "" && $id != "0") {
                        echo '<div class="alert alert-danger" role="alert">' . $bandera . '</div>';
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
        break;
} //FINAL DEL SWITCH
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