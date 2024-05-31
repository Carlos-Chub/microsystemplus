<?php

use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\NumberFormat;

session_start();
include '../../includes/BD_con/db_con.php';
include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];

switch ($condi) {
        //Intereses Manuales
    case 'IntrsManal':

        $agencia = $_SESSION['agencia'];
        $codusu = $_SESSION['id'];
?>

        <!--AHO-4-Clclintrs Cuenta Ahorros-->
        <input type="text" id="file" value="aho_05" style="display: none;">
        <input type="text" id="condi" value="IntrsManal" style="display: none;">
        <div class="text" style="text-align:center">INTERESES MANUALES</div>
        <div class="card">
            <div class="card-header">Intereses Manuales</div>
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
                                <?php echo $agencia; ?>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div>
                                            <span class="input-group-addon col-2">Tipo de Cuenta</span>
                                            <select class="form-select" id="tipcuenta" required placeholder="" disabled>
                                                <option value="0" selected disabled>Seleccionar tipo de cuenta</option>

                                                <?php
                                                $cuentas = mysqli_query($conexion, "SELECT * FROM `ahomtip` WHERE ccodofi='" . $agencia . "'");
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

                <!--Botones-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center" id="modal_footer">
                        <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="obtiene([`finicio`,`ffin`],[`tipcuenta`],[`r1`,`r2`],`process`,`0`,['<?php echo $codusu; ?>'])">
                            <i class="fa-solid fa-file-export"></i> Procesar
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="salir()">
                            <i class="fa-solid fa-circle-xmark"></i> Salir
                        </button>
                    </div>
                </div>

                <div class="contenedort mt-2" style="padding: 8px !important;">
                    <div class="table-responsive">
                        <table id="table_id2" class="table table-hover table-border">
                            <thead class="text-light table-head-aho" style="font-size: 0.8rem;">
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha y hora</th>
                                    <th>Rango</th>
                                    <th>Tipo</th>
                                    <th>Total inte.</th>
                                    <th>Total isr</th>
                                    <th>Acreditado</th>
                                    <th>Partida Prov.</th>
                                    <th>Reportes</th>
                                </tr>
                            </thead>
                            <tbody id="categoria_tb">
                                <?php
                                $check = '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-circle-check" width="40" height="40" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00b341" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <circle cx="12" cy="12" r="9" />
                                <path d="M9 12l2 2l4 -4" />
                              </svg>';
                                $query = mysqli_query($conexion, "SELECT id, round(int_total,5) AS intotal,round(isr_total,5) AS isrtotal,fecmod,rango,tipo,partida,acreditado,codusu,fechacorte from `ahointeredetalle` order by fecmod DESC");
                                while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
                                    $idcal = utf8_encode($row["id"]);
                                    $fecha = utf8_encode($row["fecmod"]);
                                    $intereses = utf8_encode($row["intotal"]);
                                    $isrcal = utf8_encode($row["isrtotal"]);
                                    $rango = utf8_encode($row["rango"]);
                                    $tipcuenta = utf8_encode($row["tipo"]);
                                    $partida = utf8_encode($row["partida"]);
                                    $acreditado = utf8_encode($row["acreditado"]);
                                    $usuario = utf8_encode($row["codusu"]);
                                    $fechacorte = utf8_encode($row["fechacorte"]);

                                    ($acreditado == 1) ? $acre = $check : $acre = '<button type="button" class="btn btn-outline-dark" title="Acreditacr" onclick="obtiene([`finicio`],[`tipcuenta`],[`r1`],`acredita`,`0`,[' . $idcal . ',' . $codusu . ',`' . $fechacorte . '`,`' . $agencia . '`,`' . $rango . '`])">
                                        <i class="fa-solid fa-money-bill-transfer"  style="color: rgb(29, 232, 38 );"></i>
                                    </button>';

                                    ($partida == 1) ? $part = $check : $part = '<button type="button" class="btn btn-outline-primary" title="Partida de provision" onclick="obtiene([`finicio`],[`tipcuenta`],[`r1`],`partidaprov`,`0`,[' . $idcal . ',' . $codusu . ',`' . $fechacorte . '`,`' . $rango . '`])">
                                        <i class="fa-solid fa-file-invoice-dollar"></i>
                                    </button>';

                                    echo '<tr>
                                                <td>' . $idcal . ' </td>
                                                <td>' . $fecha . ' </td>
                                                <td>' . $rango . ' </td>
                                                <td>' . $tipcuenta . ' </td>
                                                <td>' . number_format((float)$intereses, 2) . '</td>

                                                <td>' . number_format((float)$isrcal, 2) . '</td>
                                                <td align="center">' . $acre . '</td>
                                                <td align="center">' . $part . '</td>
                                                <td> <button type="button" class="btn btn-outline-success" title="Reporte Excel" onclick="reportes([[`finicio`,`ffin`],[],[`r1`,`r2`],[' . $idcal . ']],`xlsx`,`ahocalculo`)">
                                                        <i class="fa-solid fa-file-excel"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" title="Reporte pdf" onclick="reportes([[`finicio`,`ffin`],[],[`r1`,`r2`],[' . $idcal . ']],`pdf`,`ahocalculo`)">
                                                        <i class="fa-solid fa-file-pdf"></i>
                                                    </button>
                                                </td>
                                            </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <script>
                    //Datatable para parametrizacion
                    $(document).ready(function() {
                        convertir_tabla_a_datatable("table_id2");
                    });
                </script>

            </div>
        </div>
    <?php
        break;

    case 'intmanualindi':
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

    ?>
        <!--DEPOSITO DE AHORROS-->
        <div class="card">
            <input type="text" id="file" value="aho_05" style="display: none;">
            <input type="text" id="condi" value="intmanualindi" style="display: none;">
            <div class="card-header">Acreditacion de interés manual individual</div>

            <div class="card-body">
                <!--Aho_1_DepoAhorr Busqueda Cuenta Ahorro-->
                <div class="container contenedort">
                    <div class="row mb-3">
                        <div class="col-sm-9 col-md-8 col-lg-5">
                            <div>
                                <span class="input-group-addon col-8">Cuenta de Ahorro</span>
                                <input type="text" class="form-control" onkeydown="keypress(event)" id="ccodaho" required placeholder="   -   -  -  " value="<?php if ($bandera == "" && $id != "0") echo $id; ?>">
                            </div>
                        </div>
                        <div class="col-sm-3 col-md-4 col-lg-5">
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
                        <div class="col-sm-9 col-md-8 col-lg-6">
                            <span class="input-group-addon col-8">Nombre</span>
                            <input type="text" class="form-control " id="name" value="<?php if ($bandera == ""  && $id != "0") echo $nombre; ?>" readonly>
                        </div>
                        <div class="col-sm-3 col-md-4 col-lg-4">
                            <span class="input-group-addon col-8">Libreta</span>
                            <input type="number" class="form-control" id="lib" value="<?php if ($bandera == ""  && $id != "0") echo $nlibreta; ?>" readonly>
                        </div>
                    </div>
                    <?php if ($bandera != "" && $id != "0") {
                        echo '<div class="alert alert-danger" role="alert">' . $bandera . '</div>';
                    }
                    ?>
                </div>
                <div class="container contenedort" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-sm-5 col-md-4 col-lg-4">
                            <span class="input-group-addon col-8">Fecha inicio</span>
                            <input type="date" class="form-control " id="fecini" value="<?php echo date("Y-m-d"); ?>">
                        </div>
                        <div class="col-sm-5 col-md-4 col-lg-4">
                            <span class="input-group-addon col-8">Fecha fin</span>
                            <input type="date" class="form-control " id="fecfin" value="<?php echo date("Y-m-d"); ?>">
                        </div>
                        <div class="col-sm-2 col-md-4 col-lg-2">
                            <br>
                            <button class="btn btn-outline-secondary" type="button" id="button-addon1" title="Calcular interés" onclick="calcular()">
                                <i class="fa fa-check-to-slot"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="container contenedort">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <span class="input-group-addon col-8">Interés</span>
                            <input type="number" step="0.01" class="form-control" id="monint" required placeholder="0.00" min="0.01">
                        </div>
                        <div class="col-sm-4">
                            <span class="input-group-addon col-8">Impuesto</span>
                            <input type="number" step="0.01" class="form-control" id="monipf" placeholder="0.00" min="0.00">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-8 col-md-6 col-lg-4">
                            <span class="input-group-addon col-8">Fecha de acreditacion</span>
                            <input type="date" class="form-control " id="dfecope" value="<?php echo date("Y-m-d"); ?>">
                        </div>
                    </div>
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
            function keypress(event) {
                // Verificar si la tecla presionada es "Enter" (código 13)
                if (event.keyCode === 13) {
                    aplicarcod('ccodaho')
                }
            }
            function calcular() {

            }
            function confirmSave(action) {
                var cantidad = document.getElementById("monint").value;
                Swal.fire({
                    title: "Deseas acreditar la cantidad de Q." + cantidad + "?",
                    text: " ",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Sí ",
                    confirmButtonColor: '#4CAF50', // Color verde
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        obtiene(['dfecope', 'monint', 'monipf'], [], [], 'acreditaindi', '0', ['<?php echo $id; ?>']);
                    }
                });
            }
        </script>
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