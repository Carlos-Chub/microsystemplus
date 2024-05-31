<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');
include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];

switch ($condi) {
        //Apertura de cuenta de ahorro
    case 'ApertCuenAhor':
        $institucion = $_SESSION['agencia'];;
        $agencia = $_SESSION['agencia'];
        $ccodusu = $_SESSION['id'];

        $idcli = $_POST["xtra"];
        $datos = [
            "id_tipo" => "",
        ];
        if ($idcli != "0") {
            $consulta = mysqli_query($conexion, "SELECT * FROM `tb_cliente` WHERE `estado`=1 AND `idcod_cliente`=$idcli ");
            $cliente = mysqli_fetch_array($consulta);
            $datos = $cliente;
        }

        $hoy = date("Y-m-d");
        $fec1anio = strtotime('+365 day', strtotime($hoy));
        $fec1anio = date('Y-m-j', $fec1anio);
?>
        <!--Aho_0_ApertCuenAhor Inicio de Ahorro Sección 0 Apertura de Cuenta-->
        <div class="text" style="text-align:center">APERTURA DE CUENTA DE AHORRO</div>
        <input type="text" id="condi" value="ApertCuenAhor" hidden>
        <input type="text" id="file" value="aperturas" hidden>
        <div class="card">
            <div class="card-header">Apertura de cuenta de ahorro</div>
            <div class="card-body">
                <div class="row mb-3 contenedort" hidden>
                    <div class="col-md-3">
                        <div style="display: none;">
                            <label for="usu">codusu</label>
                            <input type="text" class="form-control " id="usu" value="<?php echo $ccodusu; ?>">
                        </div>
                    </div>
                    <div class="col-md-2" style="display: none;">
                        <div>
                            <label for="ins">Institucion</label>
                            <input type="text" class="form-control " id="ins" required value="<?php echo $institucion; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-2" style="display: none;">
                        <div>
                            <label for="ccodofi">Agencia</label>
                            <input type="text" class="form-control" id="ccodofi" readonly>
                        </div>
                    </div>
                </div>
                <div class="container contenedort">
                    <div class="row mb-3">
                        <div class="col-sm-7">
                            <div>
                                <span class="input-group-addon col-8">Cliente</span>
                                <input type="text" aria-label="Cliente" id="client" class="form-control  col" placeholder="" required value="<?php if ($idcli != "0") echo $datos['short_name']; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <br>
                            <button title="Buscar cliente" class="btn btn-outline-secondary" type="button" id="button-addon1" data-bs-toggle="modal" data-bs-target="#buscar_cli_gen">
                                <i class="fa fa-magnifying-glass"></i>
                            </button>
                        </div>
                    </div>
                    <!--Aho_0_ApertCuenAhor Búsqueda NIT-->
                    <div class="row">
                        <div class="col-md-4">
                            <div>
                                <span class="input-group-addon col-8">Codigo De cliente</span>
                                <input type="text" class="form-control " id="ccodcli" required placeholder="" value="<?php if ($idcli != "0") echo $datos['idcod_cliente']; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div>
                                <span class="input-group-addon col-8">NIT</span>
                                <input type="text" class="form-control " id="nit" required placeholder="" value="<?php if ($idcli != "0") echo $datos['no_tributaria']; ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Aho_0_ApertCuenAhor Tipo de Cuenta-->
                <div style="display:none;" class="row">
                    <div class="col-md-6">
                        <div>
                            <span class="input-group-addon col-2">Tipo de Cuenta</span>
                            <select class="form-control " id="tipCuenta" required placeholder="" onchange="correltipcuenta(this.value,'<?php echo $institucion; ?>','<?php echo $agencia; ?>')">
                                <option value="0" selected>Seleccione un tipo de producto</option>
                                <?php
                                //$cuentas = mysqli_query($conexion, "SELECT * FROM `ahomtip` WHERE ccodofi='" . $agencia . "'");
                                $cuentas = mysqli_query($conexion, "SELECT * FROM `ahomtip` WHERE ccodofi='" . $agencia . "'");
                                while ($cuenta = mysqli_fetch_array($cuentas)) {
                                    echo '<option value="' . $cuenta['ccodtip'] . '">' . $cuenta['ccodtip'] . " - " . $cuenta['nombre'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="container contenedort">
                    <h3>PRODUCTOS</h3>
                    <div class="gridtarjetas">
                        <?php
                        $cuentas = mysqli_query($conexion, "SELECT * FROM `ahomtip` WHERE ccodofi='" . $agencia . "'");
                        while ($cuenta = mysqli_fetch_array($cuentas)) {
                            echo '
                                        <div style="cursor:pointer;" name="targets" id="' . $cuenta['ccodtip'] . '" class="tarjeta" onclick="correltipcuenta(`' . $cuenta['ccodtip'] . '`,`' . $institucion . '`,`' . $agencia . '`)">
                                            <div class="titulo">' . utf8_encode($cuenta['nombre']) . '</div>
                                            <div class="cuerpo">
                                                <i class="fa-sharp fa-solid fa-piggy-bank"></i>
                                                
                                                ' . utf8_encode($cuenta['cdescripcion']) . '
                                            </div>
                                            <div class="pie">
                                                
                                            </div>
                                        </div>
                                        
                                        ';
                        }
                        $cuen = mysqli_query($conexion, "SELECT * FROM `ahomtip` WHERE ccodtip='99'");
                        while ($rowcuenta = mysqli_fetch_array($cuen)) {
                            echo '
                                        <div style="cursor:pointer;" name="targets" id="99" class="tarjeta" onclick="ahoprogramado(`' . $rowcuenta['ccodtip'] . '`,`' . $institucion . '`,`' . $agencia . '`)">
                                            <div class="titulo">' . $rowcuenta['nombre'] . '</div>
                                            <div class="cuerpo">
                                                <i class="fa-sharp fa-solid fa-piggy-bank"></i>
                                                
                                                ' . $rowcuenta['cdescripcion'] . '
                                            </div>
                                            <div class="pie">
                                                
                                            </div>
                                        </div>
                                        
                                        ';
                        }
                        ?>
                    </div>
                    <br>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <div>
                                <span class="input-group-addon col-8">Correlativo</span>
                                <input type="text" aria-label="correl" id="correla" name="correla" class="form-control" placeholder="" readonly>
                            </div>
                        </div>
                    </div>

                </div>
                <!--Aho_0_ApertCuenAhor Préstamo-->
                <div class="container contenedort">
                    <!--Aho_0_ApertCuenAhor Fecha, Tasa, Libreta-->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div>
                                <span class="input-group-addon col-8">Tasa %</span>
                                <input type="float" class="form-control" id="tasa" placeholder="0.00" required="required">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div>
                                <span class="input-group-addon col-8">Libreta</span>
                                <input type="text" class="form-control" id="libreta" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="radio" class="form-control " id=" " name="nada" checked style="display: none;">
                <!--Aho_0_ApertCuenAhor BOTONES: Print, Save, Discard, Export-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center" id="modal_footer">
                        <button type="button" class="btn btn-outline-success" onclick="obtiene([`ins`,`ccodofi`,`tasa`,`ccodcli`,`nit`,`libreta`,`usu`],[`tipCuenta`],[`nada`],`cahomcta`,`0`,`aperturas`)">
                            <i class="fa fa-floppy-disk"></i> Guardar
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
        <!-- Modal -->
        <div class="modal fade" id="aperahoprog" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog  modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Datos de Beneficiario</h1>
                    </div>
                    <div class="modal-body">
                        <div class="row contenedort">
                            <!-- partial:index.partial.html -->
                            <div class="modal-wrapp">
                                <div class="modal-header"><span class="is-active"></span><span></span><span></span><span></span></div>
                                <div class="modal-bodies contenedort">
                                    <div class="modal-bodyy modal-bodyy-step-1 is-showing contenedort">
                                        <div class="title">Paso 1</div>
                                        <div class="description">Nombre del objetivo y origen de fondos</div>
                                        <form>
                                            <span class="input-group-addon col-8">Nombre del objetivo</span>
                                            <input class="form-control" type="text" placeholder="Ahorro Programado Marta" id="nomaho" />
                                            <br>
                                            <h5>ORIGEN DE FONDOS</h5>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="account" onclick="">
                                                <label class="form-check-label" for="account">Seleccionar una cuenta de la cual se debitaran las cuotas de ahorro</label>
                                            </div>
                                            <div class="form-control">
                                                <select class="form-select" aria-label="Default select example" id="prgaccount">
                                                    <option value="X" selected disabled>Seleccionar una cuenta</option>
                                                    <?php
                                                    $cuentac = mysqli_query($conexion, "SELECT * FROM `ahomcta` WHERE num_nit='" . $datos['idcod_cliente'] . "'");
                                                    while ($rowaccount = mysqli_fetch_array($cuentac)) {
                                                        $codigo = tipocuenta(substr($rowaccount['ccodaho'], 6, 2), "ahomtip", "nombre", $conexion);
                                                        echo '<option value="' . $rowaccount['id_cuenta'] . '">' . $rowaccount['ccodaho'] . ' ' . $codigo . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <br>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="depositos" checked>
                                                <label class="form-check-label" for="depositos">Depositos directos</label>
                                            </div>

                                            <div class="text-center">
                                                <div class="button" onclick="btnclick(this)">Siguente</div>
                                            </div>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="printdiv2('#cuadro','<?php echo $idcli; ?>')">Cancelar</button>
                                        </form>
                                    </div>
                                    <div class="modal-bodyy modal-bodyy-step-2 contenedort">
                                        <div class="title">Paso 2: Calendarizacion y definicion de objetivo</div>
                                        <div class="description">De cuanto es la meta</div>
                                        <form>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <span class="input-group-addon">Monto objetivo</span>
                                                    <input type="number" aria-label="Monto objetivo" id="montoobj" class="form-control  col" placeholder="0.00">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <span class="input-group-addon col-8">Fecha inicio</span>
                                                    <input type="date" aria-label="Fecha inicio" id="fini" class="form-control  col" value="<?php echo $hoy; ?>">
                                                </div>
                                                <div class="col-sm-6">
                                                    <span class="input-group-addon col-8">Fecha Fin</span>
                                                    <input type="date" aria-label="Fecha inicio" id="ffin" class="form-control  col" value="<?php echo $fec1anio; ?>">
                                                </div>
                                            </div>
                                            <br>
                                            <div class="text-center fade-in">
                                                <div class="button" onclick="btnclick(this)">Siguiente</div>
                                            </div>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="printdiv2('#cuadro','<?php echo $idcli; ?>')">Cancelar</button>
                                        </form>
                                    </div>
                                    <div class="modal-bodyy modal-bodyy-step-3 contenedort">
                                        <div class="title">Calendarizacion y definicion de objetivo</div>
                                        <div class="description">Frecuencia de ahorro</div>
                                        <form>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" name="frec" type="radio" role="switch" value="day7" checked>
                                                <label class="form-check-label" for="day7">Semanal</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" name="frec" type="radio" role="switch" value="day15">
                                                <label class="form-check-label" for="day15">Quincenal</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" name="frec" type="radio" role="switch" value="day30">
                                                <label class="form-check-label" for="day30">Mensual</label>
                                            </div>
                                            <br>
                                            <div class="text-center fade-in">
                                                <div class="button" onclick="calculoprog(this)">Calcular</div>
                                            </div>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="printdiv2('#cuadro','<?php echo $idcli; ?>')">Cancelar</button>
                                        </form>
                                    </div>
                                    <div class="modal-bodyy modal-bodyy-step-4 contenedort">
                                        <div class="title">Step 3</div>
                                        <div class="description">Fechas estimadas para acreditar en la cuenta de ahorro</div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <label for="" id="lblini">Inicio:</label>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="" id="lblfin">Finalizacion</label>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="" id="lblmon">Monto</label>
                                            </div>
                                        </div>
                                        <div>
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">No.</th>
                                                        <th scope="col">Fecha</th>
                                                        <th scope="col">Monto</th>
                                                        <th scope="col">Saldo</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbcuotas">
                                                </tbody>
                                            </table>
                                        </div>
                                        <button id="createahoprog" type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="obtiene(['nomaho', 'montoobj', 'fini', 'ffin','tasa'], ['tipCuenta','prgaccount'], ['frec'], 'cahoprog', '<?php echo $idcli; ?>', ['0','<?php echo $institucion; ?>','<?php echo $agencia; ?>','<?php echo $idcli; ?>','<?php echo $ccodusu; ?>'])">
                                            <i class="fa fa-floppy-disk"></i> Guardar
                                        </button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="printdiv2('#cuadro','<?php echo $idcli; ?>')">Cancelar</button>
                                    </div>
                                </div>
                            </div>
                            <!-- partial -->
                        </div>
                    </div>
                    <input type="radio" name="nada" id=" 0" checked style="display: none;">
                    <div class="modal-footer">

                    </div>
                </div>
            </div>
        </div>
    <?php
        break;
        //Beneficiarios de ahorro
    case 'BeneAho':
        $id = $_POST["xtra"];
        $datos = [
            "id_tipo" => "",
        ];
        $datoscli = mysqli_query($conexion, "SELECT * FROM `ahomcta` WHERE `ccodaho`=$id");
        $bandera = "Cuenta de ahorro no existe";
        while ($da = mysqli_fetch_array($datoscli, MYSQLI_ASSOC)) {
            $idcli = utf8_encode($da["ccodcli"]);
            $nit = utf8_encode($da["num_nit"]);
            $nlibreta = utf8_encode($da["nlibreta"]);
            $estado = utf8_encode($da["estado"]);
            ($estado != "A") ? $bandera = "Cuenta de ahorros Inactiva" : $bandera = "";
        }
        if ($bandera == "") {
            $data = mysqli_query($conexion, "SELECT `short_name` FROM `tb_cliente` WHERE estado=1 AND `idcod_cliente`='$idcli'");
            $nombre = "";
            $bandera = "No existe el cliente relacionado a la cuenta de ahorro";
            while ($dat = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                $nombre = utf8_encode($dat["short_name"]);
                $bandera = "";
            }
        }
    ?>
        <!--Aho_0_BeneAho Inicio de Ahorro Sección 0 Beneficiario de Ahorro-->
        <div class="text" style="text-align:center">BENEFICIARIOS DE AHORRO</div>
        <div class="card">
            <input type="text" id="file" value="aperturas" style="display: none;">
            <input type="text" id="condi" value="BeneAho" style="display: none;">
            <div class="card-header">Beneficiarios de ahorro</div>
            <div class="card-body">
                <!--Aho_0_BeneAho Cta.Ahorros-->
                <div class="container contenedort">
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <span class="input-group-addon col-8">Cuenta de Ahorros</span>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control " value="0" id="ccodcrt" style="display: none;">
                                <input type="text" class="form-control " id="ccodaho" required placeholder="000-000-00-000000" value="<?php if ($bandera == "") echo $id; ?>">
                                <span class="input-group-text" id="basic-addon1">
                                    <?php if ($bandera == "") {
                                        echo '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-circle-check" width="32" height="32" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00b341" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M9 12l2 2l4 -4" />
                                        </svg>';
                                    } else {
                                        echo '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-circle-x" width="32" height="32" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ff2825" fill="none" stroke-linecap="round" stroke-linejoin="round">
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
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <span class="input-group-addon col-8">Nombre</span>
                            <input type="text" class="form-control " id="name" value="<?php if ($bandera == "") {
                                                                                            echo $nombre;
                                                                                        } else {
                                                                                            echo $nombre = "";
                                                                                        } ?>" readonly>
                        </div>
                    </div>
                    <?php if ($bandera != "" && $id != "0") {
                        echo '<div class="alert alert-danger" role="alert">' . $bandera . '</div>';
                    }
                    ?>
                </div>
                <!--Aho_0_BeneAho Tabla de Datos-->
                <div class="container contenedort" style="padding: 8px !important;">
                    <div class="table-responsive">
                        <table id="table_id2" class="table table-hover table-border">
                            <thead class="text-light table-head-aho">
                                <tr>
                                    <th>DPI</th>
                                    <th>Nombre Completo</th>
                                    <th>Fec. Nac.</th>
                                    <th>Parentesco</th>
                                    <th>%</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="categoria_tb">
                                <?php
                                $total = 0;
                                if ($bandera == "") {
                                    $queryben = mysqli_query($conexion, "SELECT * FROM `ahomben` WHERE `codaho`='$id'");
                                    while ($rowq = mysqli_fetch_array($queryben, MYSQLI_ASSOC)) {
                                        $idahomben = utf8_encode($rowq["id_ben"]);
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
                                            <td> <button type="button" class="btn btn-warning" title="Editar Beneficiario" onclick="editben(' . $idahomben . ',`' . $bennom . '`,`' . $bendpi . '`,`' . $bendire . '`,' . $benparent . ',`' . $benfec . '`,' . $benporcent . ',`' . $bentel . '`)">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" title="Eliminar Beneficiario" onclick="eliminar(' . $idahomben . ',`crud_ahorro`,`' . $id . '`,`dahomben`)">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </td>
                                            </tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <script>
                        //Datatable para parametrizacion
                        $(document).ready(function() {
                            convertir_tabla_a_datatable("table_id2");
                        });
                    </script>

                    <div class="row mt-2">
                        <!--TOTAL-->
                        <div class="col-md-3">
                            <label for="">Total: <?php echo $total; ?> %</label>
                        </div>
                    </div>
                </div>
                <div class="row justify-items-md-center">
                    <div class="col align-items-center" id="modal_footer">
                        <button type="button" id="btnnew" class="btn btn-outline-success" onclick="crear_editar_beneficiario('<?php echo $id; ?>','<?php echo $nombre ?>')">
                            <i class="fa fa-file"></i> Agregar o editar
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
        <!-- Modal -->
        <div class="modal fade" id="databen" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog  modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Datos de Beneficiario</h1>
                    </div>
                    <div class="modal-body">
                        <!-- COD APORTACION Y NOMBRE -->
                        <div class="container contenedort">
                            <div class="row mb-2">
                                <div class="col-sm-6">
                                    <!-- titulo -->
                                    <span class="input-group-addon col-8">Cuenta de Ahorro</span>
                                    <div class="input-group">
                                        <input type="text" class="form-control " id="ccodaho_modal" readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <span class="input-group-addon col-8">Nombre</span>
                                    <input type="text" class="form-control " id="name_modal" readonly>
                                </div>
                            </div>
                        </div>
                        <!-- AGREGAR LA TABLA DE BENEFICIARIOS -->
                        <!-- MOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOODAAAAAAAAAAAAAAAAAAAAAAAAAAAAAALLLLLLLLLL -->
                        <div class="container contenedort recar_c" style="padding: 0px 0px 3px 0px !important;">
                            <div class="table-responsive">
                                <table id="tabla_ben" class="table table-hover table-border" style="max-width: 100% !important;">
                                    <thead class="text-light table-head-aho">
                                        <tr>
                                            <th>DPI</th>
                                            <th>Nombre Completo</th>
                                            <th>Fec. Nac.</th>
                                            <th>Parentesco</th>
                                            <th>%</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="">

                                    </tbody>
                                </table>
                            </div>
                            <div class="row mt-1 mb-1 ms-2">
                                <!--TOTAL-->
                                <div class="col-md-3">
                                    <label for="" id="total">Total</label>
                                </div>
                            </div>
                        </div>
                        <!-- MODAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAALLLLLLLLLLLLLLLLLLLLLLLLLL -->
                        <div class="container contenedort">
                            <div class="row">
                                <!--Aho_0_BeneAho Nombre-->
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <span class="input-group-addon">Nombre</span>
                                        <input type="text" aria-label="Nombre Ben" id="benname" class="form-control col" placeholder="" required>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="input-group-addon">Dpi</span>
                                        <input type="text" aria-label="Cliente" id="bendpi" class="form-control col" placeholder="">
                                    </div>

                                </div>
                                <!--Aho_0_BeneAho Nacimiento, parentesco, porcentaje-->
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <span class="input-group-addon">Direccion</span>
                                        <input type="text" aria-label="Direccion Ben" id="bendire" class="form-control col" placeholder="" required>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="input-group-addon col-8">Parentesco</span>
                                        <select class="form-select  col-sm-12" id="benparent">
                                            <option value="0" selected disabled>Seleccione parentesco</option>
                                            <?php
                                            $parent = mysqli_query($general, "SELECT * FROM `tb_parentesco`");
                                            while ($tip = mysqli_fetch_array($parent)) {
                                                echo '<option value="' . $tip['id_parent'] . '">' . $tip['descripcion'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <span class="input-group-addon">Telefono</span>
                                        <input type="text" aria-label="Tel Ben" id="bentel" class="form-control col" placeholder="">
                                    </div>
                                    <div class="col-md-3">
                                        <span class="input-group-addon">Nacimiento</span>
                                        <input type="date" class="form-control  col-10" id="bennac" value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                    <div class="col-md-1">
                                    </div>
                                    <div class="col-md-2">
                                        <span class="input-group-addon">Porcentaje</span>
                                        <input type="number" class="form-control  col-10" id="benporcent" required placeholder="0.00">
                                    </div>
                                    <div style="display:none;" class="col-md-2">
                                        <span class="input-group-addon">anterior</span>
                                        <input type="number" class="form-control  col-10" id="benporcentant">
                                        <input type="number" class="form-control  col-10" id="idben">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <input type="radio" name="nada" id="0" checked style="display: none;">
                    <div class="modal-footer">
                        <button id="createben" type="button" class="btn btn-primary" onclick="obtiene(['benname', 'bendpi', 'bendire', 'bentel', 'bennac', 'benporcent'], ['benparent'], [], 'create_aho_ben', '<?php echo $id; ?>', ['<?php echo $id; ?>','<?php echo $bandera; ?>'])">
                            <i class="fa fa-floppy-disk"></i> Guardar
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="cancelar_crear_editar_beneficiario('lista_beneficiarios','<?php echo $id; ?>')">Cancelar</button>
                        <button id="updateben" style="display:none;" type="button" class="btn btn-primary" onclick="obtiene(['benname', 'bendpi', 'bendire', 'bentel', 'bennac', 'benporcent','benporcentant','idben'], ['benparent'], [], 'update_aho_ben', '<?php echo $id; ?>', ['<?php echo $id; ?>',<?php echo $id; ?>])">
                            <i class="fa fa-floppy-disk"></i> Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <?php
        break;
        //Tipo de ahorros
    case 'TpsAhrrs':
        $idtip = $_POST["xtra"];
        $datos = [
            "id_tipo" => "",
        ];
        if ($idtip != "0") {
            $tipos = mysqli_query($conexion, "SELECT * FROM `ahomtip` WHERE `id_tipo`=$idtip");
            $tipo = mysqli_fetch_array($tipos);
            $datos = $tipo;
        }
    ?>

        <!--Aho_0_TpsAhrrs Inicio de Ahorro Sección 0 Tipos de Ahorros-->
        <div class="text" style="text-align:center">TIPOS DE AHORRO</div>
        <input type="text" value="TpsAhrrs" id="condi" style="display: none;">
        <input type="text" value="aperturas" id="file" style="display: none;">
        <div class="card">
            <div class="card-header">Tipos de ahorro</div>
            <div class="card-body">
                <!--Aho_0_TpsAhrrs Tablas Tipos de Ahorros-->
                <div class="container contenedort">
                    <h3>Tipos de Ahorros</h3>
                    <div class="table-responsive">
                        <table id="tiposahorros" class="table table-hover table-border">
                            <thead class="text-light table-head-aho">
                                <tr>
                                    <th>Agencia</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Tasa</th>
                                    <th>Opciones</th>
                                </tr>
                            </thead>
                            <tbody id="categoria_tb">
                                <?php
                                $tiposahorros = mysqli_query($conexion, "SELECT * FROM `ahomtip`");
                                while ($tipo = mysqli_fetch_array($tiposahorros, MYSQLI_ASSOC)) {
                                    $id = utf8_encode($tipo["id_tipo"]);
                                    $ccodofi = utf8_encode($tipo["ccodofi"]);
                                    $codigo = $tipo["ccodtip"];
                                    $nametip = utf8_encode($tipo["nombre"]);
                                    $descripcion = utf8_encode($tipo["cdescripcion"]);
                                    $tasa = $tipo["tasa"];
                                    echo '<tr> <td>' . $ccodofi . '</td>';
                                    echo '<td>' . $codigo . '</td>';
                                    echo '<td>' . $nametip . '</td>';
                                    echo '<td>' . $descripcion . '</td>';
                                    echo '<td>' . $tasa . '</td>';
                                    echo '<td>
                                                <button type="button" class="btn btn-default" title="Editar" onclick="printdiv2(`#cuadro`,' . $id . ')"> <i class="fa-solid fa-pen"></i></button>
                                                <button type="button" class="btn btn-default" title="Eliminar" onclick="eliminar(' . $id . ',`crud_ahorro`,`0`,`dahomtip`)"> <i class="fa-solid fa-trash-can"></i></button>
                                             </td></tr> ';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <script>
                    //Datatable para parametrizacion
                    $(document).ready(function() {
                        convertir_tabla_a_datatable("tiposahorros");
                    });
                </script>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div>
                            <span>Agencia</span>
                            <select id="ccodofi" class="form-select">
                                <?php
                                $agencias = mysqli_query($conexion, "SELECT * FROM `tb_agencia`");
                                $selected = "";
                                while ($agencia = mysqli_fetch_array($agencias)) {
                                    ($agencia['cod_agenc'] == $datos[1]) ? $selected = "selected" : $selected = "";
                                    echo '<option value="' . $agencia['cod_agenc'] . '" ' . $selected . ' > ' . $agencia['cod_agenc'] . " - " . $agencia['nom_agencia'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <div>
                            <?php
                            $numeros = range(1, 99);
                            shuffle($numeros);
                            // $numeros = [1, 1,2,3,5];
                            $ult_codigo = 0;

                            for ($i = 0; $i < count($numeros); $i++) {
                                $j = 0;
                                $bandera = false;
                                $consulta = (mysqli_query($conexion, "SELECT `ccodtip` as cod FROM `ahomtip`"));
                                while ($consulta_aux = mysqli_fetch_array($consulta)) {
                                    if ($numeros[$i] == $consulta_aux['cod']) {
                                        $bandera = true;
                                    }
                                    $j++;
                                }
                                if (!$bandera) {
                                    $ult_codigo = $numeros[$i];
                                    $i = count($numeros) + 1;
                                    if ($ult_codigo < 10) {
                                        $ult_codigo = "0" . $ult_codigo;
                                    }
                                }
                            }
                            ?>
                        </div>
                        <span>Codigo</span>
                        <input type="text" class="form-control " id="ccodtip" value="<?php if ($idtip != "0") {
                                                                                            echo $datos[2];
                                                                                        } else {
                                                                                            echo $ult_codigo;
                                                                                        } ?>" required readonly>
                    </div>
                </div>
                <div class="col-sm-5">
                    <div>
                        <span>Nombre</span>
                        <input type="text" class="form-control " id="nombre" value="<?php if ($idtip != "0") echo utf8_encode($datos[3]); ?>" required>
                    </div>
                </div>


                <div class="row mb-3 mt-3">
                    <div class="col-md-8">
                        <div>
                            <span>Descripción</span>
                            <input type="text" class="form-control " id="cdescripcion" value="<?php if ($idtip != "0") echo utf8_encode($datos[4]); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div>
                            <span>Tasa %</span>
                            <input type="float" class="form-control " id="tasa" value="<?php if ($idtip != "0") echo $datos[5]; ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <span>Seleccionar clase</span>
                        <select class="form-select" name="" id="tipcuen">
                            <?php $selected = "selected";
                            ($idtip != "0") ? $dato = $datos[7] : $dato = "nothing"; ?>
                            <option value="cr" <?php if ($dato == "cr") echo $selected; ?>>Corriente</option>
                            <option value="pf" <?php if ($dato == "pf") echo $selected; ?>>Plazo fijo</option>
                            <option value="pr" <?php if ($dato == "pr") echo $selected; ?>>Programado</option>
                            <option value="vi" <?php if ($dato == "vi") echo $selected; ?>>Vinculado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <span>Saldo minimo</span>
                        <input type="float" class="form-control" title="Saldo minimo para calcular interes" id="mincalc" value="<?php if ($idtip != "0") echo $datos[8];
                                                                                                                                else echo '0'; ?>">
                    </div>
                </div>
                <div class="row" style="display: none;">
                    <div class="col-md-2">
                        <div>
                            <input type="radio" class="form-control" id=" " name="nada" checked>
                            <input type="radio" class="form-control" id="activado" name="activado">
                        </div>
                    </div>
                </div>
                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <?php
                        if ($idtip == "0") {
                            echo '<button onclick="obtiene([`ccodtip`,`cdescripcion`,`tasa`,`nombre`,`mincalc`],[`ccodofi`,`tipcuen`],[`nada`],`cahomtip`,`0`,`aperturas`)" type="button" class="btn btn-outline-success" data-dismiss="modal">
                            <i class="fa fa-floppy-disk"></i> Guardar
                            </button>';
                        } else {
                            echo '<button onclick="obtiene([`ccodtip`,`cdescripcion`,`tasa`,`nombre`,`mincalc`],[`ccodofi`,`tipcuen`],[`nada`],`uahomtip`,`0`,' . $idtip . ')" type="button" class="btn btn-outline-primary" data-dismiss="modal">
                            <i class="fa fa-floppy-disk"></i> Actualizar
                            </button>';
                        }
                        ?>
                        <button type="button" id="undo" class="btn btn-outline-danger" onclick="printdiv2(`#cuadro`,'0')">
                            <i class="fa fa-ban"></i> Cancelar
                        </button>


                        <button type="button" class="btn btn-outline-warning" onclick="salir()">
                            <i class="fa-solid fa-circle-xmark"></i> Salir
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </div>
    <?php
        break;
        //Impresion de Libreta
    case 'iMprsnLbrt':
        $id = $_POST["xtra"];
        $datos = [
            "id_tipo" => "",
        ];
        $datoscli = mysqli_query($conexion, "SELECT * FROM `ahomcta` WHERE `ccodaho`=$id");
        $bandera = "Cuenta de ahorro no existe";
        while ($da = mysqli_fetch_array($datoscli, MYSQLI_ASSOC)) {
            $idcli = ($da["ccodcli"]);
            $nlibreta = ($da["nlibreta"]);
            $estado = ($da["estado"]);
            ($estado != "A") ? $bandera = "Cuenta de ahorros Inactiva" : $bandera = "";
        }
        if ($bandera == "") {
            $data = mysqli_query($conexion, "SELECT `short_name`,`no_tributaria` num_nit FROM `tb_cliente` WHERE estado=1 AND `idcod_cliente` = '$idcli'");
            $dat = mysqli_fetch_array($data, MYSQLI_ASSOC);
            $nombre = ($dat["short_name"]);
            $nit = ($dat["num_nit"]);
        }
    ?>
        <!--Aho_0_iMprsnLbrt Impresión de Libretas-->
        <div class="text" style="text-align:center">IMPRESION DE LIBRETA</div>
        <input type="text" id="file" value="aperturas" style="display: none;">
        <input type="text" id="condi" value="iMprsnLbrt" style="display: none;">
        <div class="card">
            <div class="card-header">Impresion de Libreta</div>
            <div class="card-body">
                <!--Aho_0_iMprsnLbrt Libreta-->
                <div class="row mb-3">
                    <div class="col-md-5">
                        <div>
                            <span class="input-group-addon col">Cuenta de Ahorros</span>
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
                    <div class="col-md-3">
                        <div>
                            <span class="input-group-addon col">Libreta</span>
                            <input type="text" class="form-control " id="libreta" required readonly value="<?php if ($bandera == "") echo $nlibreta; ?>">
                        </div>
                    </div>
                </div>
                <!--Aho_0_iMprsnLbrt Libreta-->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div>
                            <span class="input-group-addon col">NIT</span>
                            <input type="text" class="form-control " id="nit" required readonly value="<?php if ($bandera == "") echo $nit; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div>
                            <span class="input-group-addon col">Nombre</span>
                            <input type="text" class="form-control " id="name" readonly value="<?php if ($bandera == "") echo ($nombre); ?>">
                        </div>
                    </div>
                </div>
                <!--Aho_0_iMprsnLbrt Borones, Imprimir, Cancelar, Salir-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center" id="">
                        <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[], [], [], ['<?= $id; ?>']], 'pdf', '1',0,1)">
                            <i class="fa-solid fa-print"></i> Imprimir
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="window.print();">
                            <i class="fa-solid fa-ban"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-outline-warning ">
                            <i class="fa-solid fa-right-from-bracket"></i> Salir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php
        break;
        //Cambio Libre
    case 'CambioLibre':
        $id = $_POST["xtra"];
        $ccodusu = $_SESSION['id'];
        $datos = [
            "id_tipo" => "",
        ];
        $nlibreta = "";
        $datoscli = mysqli_query($conexion, "SELECT * FROM `ahomcta` WHERE `ccodaho`=$id");
        $bandera = "Cuenta de ahorro no existe";
        while ($da = mysqli_fetch_array($datoscli, MYSQLI_ASSOC)) {
            $idcli = utf8_encode($da["ccodcli"]);
            $nit = utf8_encode($da["num_nit"]);
            $nlibreta = utf8_encode($da["nlibreta"]);
            $estado = utf8_encode($da["estado"]);
            ($estado != "A") ? $bandera = "Cuenta de ahorros Inactiva" : $bandera = "";
            $bandera = "";
        }
        if ($bandera == "") {
            $data = mysqli_query($conexion, "SELECT `short_name` FROM `tb_cliente` WHERE estado=1 AND `idcod_cliente`='$idcli'");
            //$data = mysqli_query($conexion, "SELECT `short_name` FROM `tb_cliente` WHERE `idcod_cliente`='$idcli' OR `no_tributaria` = '$nit'");
            $dat = mysqli_fetch_array($data, MYSQLI_ASSOC);
            $nombre = utf8_encode($dat["short_name"]);

            //------traer el saldo de la cuenta
            $monto = 0;
            $saldo = 0;
            $transac = mysqli_query($conexion, "SELECT `monto`,`ctipope` FROM `ahommov` WHERE `ccodaho`=$id AND cestado!=2");
            while ($row = mysqli_fetch_array($transac, MYSQLI_ASSOC)) {
                $tiptr = utf8_encode($row["ctipope"]);
                $monto = utf8_encode($row["monto"]);
                if ($tiptr == "R") {
                    $saldo = $saldo - $monto;
                }
                if ($tiptr == "D") {
                    $saldo = $saldo + $monto;
                }
            }
            //****fin saldo */
        }
        mysqli_close($conexion);
    ?>
        <!--Aho_1_CambioLibre Cambio de Libreria-->
        <div class="text" style="text-align:center">CAMBIO LIBRE</div>
        <input type="text" id="file" value="aperturas" style="display: none;">
        <input type="text" id="condi" value="CambioLibre" style="display: none;">
        <div class="card">
            <div class="card-header">Cambio Libre</div>
            <div class="card-body">
                <!--Aho_0_iMprsnLbrt Libreta-->
                <div class="row mb-3">
                    <div class="col-md-5">
                        <div>
                            <span class="input-group-addon col">Cuenta de Ahorros</span>
                            <?php if ($bandera == "") echo '<input type="text" disabled class="form-control " id="ccodaho" required value="' . $id . '">';
                            else echo '<input type="text" class="form-control" id="ccodaho" required placeholder="000-000-00-000000">';
                            ?>
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
                    <div class="col-md-3">
                        <div>
                            <span class="input-group-addon col">NIT</span>
                            <input type="text" class="form-control " id="nit" required readonly value="<?php if ($bandera == "") echo $nit; ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div>
                            <span class="input-group-addon col">Nombre</span>
                            <input type="text" class="form-control " id="name" readonly value="<?php if ($bandera == "") echo $nombre; ?>">
                        </div>
                    </div>
                </div>
                <!--Aho_0_iMprsnLbrt Libreta-->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div>
                            <span class="input-group-addon col">Libreta Actual</span>
                            <input type="text" class="form-control " id="libreta" readonly value="<?php if ($bandera == "") echo $nlibreta; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div>
                            <span class="input-group-addon col">Saldo Disponible</span>
                            <input type="text" class="form-control " id="salDisp" required readonly value="<?php if ($bandera == "") echo 'Q ' . number_format($saldo, 2, '.', ','); ?>">
                        </div>
                    </div>
                </div>

                <!--Aho_1_CambioLibre LibretaActual-Nueva Libreta-->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div>
                            <span class="input-group-addon col">Nueva Libreta</span>
                            <input type="number" class="form-control " id="newLibret" required placeholder="0" min="1">
                        </div>
                    </div>
                </div>
                <div style="display: none;">
                    <select class="form-control col-md-12" aria-label="Default select example" id="nothing">
                        <option value="0" selected>Abrir</option>
                    </select>

                    <input class="form-check-input" type="radio" name="nada" id=" " value="nada" checked>
                    <label class="form-check-label col">Todo</label>
                </div>


                <!--Aho_1_CambioLibre Botones, Guardar Cancelar-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center" id="modal_footer">
                        <button type="button" id="btnSave" class="btn btn-outline-success" onclick="obtiene(['ccodaho','newLibret'],['nothing'], ['nada'], 'modlib', '0', ['<?php echo $id; ?>',<?php echo $nlibreta; ?>,'<?php echo $ccodusu; ?>'])">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar
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
        //

} //FINAL DEL SWITCH
?>