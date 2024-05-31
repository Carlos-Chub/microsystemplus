<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];

switch ($condi) {
    case 'Parametrizacion_aprt':
        $codusu = $_SESSION['id'];
        $id = $_POST["xtra"];

        $bandera = false;
        $query = "SELECT * FROM ctb_nomenclatura WHERE estado=?";
        $response = executequery($query, [1], ['i'], $conexion);
        if (!$response[1]) {
            $bandera = ($response[0]);
        }
        $nomenclatura = $response[0];
?>
        <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
        <style>
            table {
                font-size: 13px;
            }
        </style>
        <div class="text" style="text-align:center">PARAMETRIZACION DE CUENTAS CONTABLES DE APORTACIONES</div>
        <div class="card">
            <input type="text" id="file" value="APRT_6" style="display: none;">
            <input type="text" id="condi" value="Parametrizacion_aprt" style="display: none;">
            <div class="card-header">Parametrizacion Ahorro</div>
            <div class="card-body">
                <div class="container contenedort">
                    <div class="row mt-2 pb-2">
                        <div class="col">
                            <div class="table-responsive">
                                <table id="table_parametrizacion" class="table table-hover table-border">
                                    <thead class="text-light table-head-aho">
                                        <tr>
                                            <th>No.</th>
                                            <th>Agencia</th>
                                            <th>Nombre</th>
                                            <th>Tasa</th>
                                            <th>Cuenta Contable</th>
                                            <th>Cuenta Cuota de Ingreso</th>
                                            <th>Editar Cuentas</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tb_cuerpo_parametrizacion">
                                        <?php
                                        $i = 1;
                                        $consulta = mysqli_query($conexion, "SELECT tip.*,ofi.nom_agencia FROM aprtip tip INNER JOIN tb_agencia ofi ON ofi.cod_agenc=tip.ccodage;");
                                        while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                            $id = $row["id_tipo"];
                                            $agencia = $row["nom_agencia"];
                                            $id_tipo_cuenta = $row["ccodtip"];
                                            $nombre = $row["nombre"];
                                            $tasa = $row["tasa"];

                                            //CUENTA CONTABLE TIPO DE APORTACION
                                            $id_cuenta = $row["id_cuenta_contable"];
                                            $cuenta = ' ';
                                            $namecuenta = 'No hay cuenta definida';
                                            $key = array_search($id_cuenta, array_column($nomenclatura, 'id'));
                                            if ($key !== false) {
                                                $cuenta = $nomenclatura[$key]['ccodcta'];
                                                $namecuenta = $nomenclatura[$key]['cdescrip'];
                                            } else {
                                                $id_cuenta = 0;
                                            }
                                            //CUENTA CONTABLE CUOTA DE INGRESO
                                            $id_cuentacuota = $row["cuenta_aprmov"];
                                            $cuentacuota = ' ';
                                            $namecuentacuota = 'No hay cuenta definida';
                                            $key = array_search($id_cuentacuota, array_column($nomenclatura, 'id'));
                                            if ($key !== false) {
                                                $cuentacuota = $nomenclatura[$key]['ccodcta'];
                                                $namecuentacuota = $nomenclatura[$key]['cdescrip'];
                                            } else {
                                                $id_cuentacuota = 0;
                                            }
                                            echo '<tr> <td>' . $i . '</td>';
                                            echo '<td>' . $agencia . '</td>';
                                            echo '<td>' . $nombre . '</td>';
                                            echo '<td>' . $tasa . '</td>';
                                            echo '<td>' .  $cuenta . ' - ' . $namecuenta  . '</td>';
                                            echo '<td>' .  $cuentacuota . ' - ' . $namecuentacuota . '</td>';
                                            echo '<td>
                                                <button type="button" class="btn btn-default" title="Editar" onclick="loaddata([' . $id . ',`' . $nombre . '`,' . $id_cuenta . ',`' . $cuenta . '`,`' . $namecuenta . '`,' . $id_cuentacuota . ',`' . $cuentacuota . '`,`' . $namecuentacuota . '`])"> <i class="fa-solid fa-pen"></i></button>
                                             </td></tr> ';
                                            $i++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    //Datatable para parametrizacion
                    $(document).ready(function() {
                        convertir_tabla_a_datatable("table_parametrizacion");
                    });
                </script>

                <div class="container contenedort">
                    <div class="row mb-3 mt-2">
                        <div class="col-sm-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="tipo" readonly>
                                <input type="text" class="form-control" id="idtipo" hidden>
                                <label for="tip_cuenta">Tipo de cuenta</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3 mt-2">
                        <div class="col-md-6">
                            <div class="row"></div>
                            <div class="input-group" id="div_cuenta1">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="text_cuenta1" placeholder="name@example.com" readonly>
                                    <input type="text" class="form-control" id="id_hidden1" hidden readonly>
                                    <label class="text-primary" for="text_cuenta1"><i class="fa-solid fa-file-invoice"></i>Cuenta Contable Tipo de aportacion</label>
                                </div>
                                <span type="button" class="input-group-text" id="basic-addon2" title="Buscar nomenclatura" onclick="abrir_modal('#modal_nomenclatura', '#id_modal_hidden', 'id_hidden1,text_cuenta1/A,2-3/-')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row"></div>
                            <div class="input-group" id="div_cuenta2">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="text_cuenta2" placeholder="name@example.com" readonly>
                                    <input type="text" class="form-control" id="id_hidden2" hidden readonly>
                                    <label class="text-primary" for="text_cuenta2"><i class="fa-solid fa-file-invoice"></i>Cuenta Contable Cuota de ingreso</label>
                                </div>
                                <span type="button" class="input-group-text" id="basic-addon2" title="Buscar nomenclatura" onclick="abrir_modal('#modal_nomenclatura', '#id_modal_hidden', 'id_hidden2,text_cuenta2/A,2-3/-')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row justify-items-md-center">
                    <div class="col align-items-center" id="modal_footer">
                        <button id="save" style="display: none;" type="button" class="btn btn-outline-primary" onclick="obtiene([`idtipo`,`id_hidden1`,`id_hidden2`],[],[],`update_aprt_cuentas_contables`,`0`,[])">
                            <i class="fa-solid fa-pen-to-square"></i>Actualizar
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
        <script>
            function loaddata(datos) {
                $('#idtipo').val(datos[0]);
                $('#tipo').val(datos[1]);
                $('#id_hidden1').val(datos[2]);
                $('#text_cuenta1').val(datos[3] + ' - ' + datos[4]);
                $('#id_hidden2').val(datos[5]);
                $('#text_cuenta2').val(datos[6] + ' - ' + datos[7]);
                $('#save').show();
            }
        </script>

        <?php
        break;
    case 'Parametrizacion_aprt_anterior': {
            $codusu = $_SESSION['id'];
            $id = $_POST["xtra"];
            if ($id == 0) {
                $id = ['0', '0', '0', "", "", "", "", "", ""];
            }
        ?>
            <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
            <div class="text" style="text-align:center">PARAMETRIZACIÓN DE APORTACIONES</div>
            <div class="card">
                <input type="text" id="file" value="APRT_6" style="display: none;">
                <input type="text" id="condi" value="Parametrizacion_aprt" style="display: none;">
                <div class="card-header">Parametrizacion Aportación</div>
                <div class="card-body">
                    <div class="container contenedort">
                        <div class="row mt-2 pb-2">
                            <div class="col">
                                <div class="table-responsive">
                                    <table id="table_parametrizacion" class="table table-hover table-border">
                                        <thead class="text-light table-head-aprt mt-2">
                                            <tr>
                                                <th>Tipo cuenta</th>
                                                <th>Documento</th>
                                                <th>Cuenta 1</th>
                                                <th>Cuenta 2</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tb_cuerpo_parametrizacion">
                                            <?php
                                            $consulta = mysqli_query($conexion, "SELECT ctb.id, ctb.id_tipo_cuenta, tip.nombre, ctb.id_tipo_doc, tpc.descripcion, ctb.id_cuenta1, ctb.id_cuenta2, nomen.ccodcta AS cuenta1, nomen1.ccodcta AS cuenta2, nomen.cdescrip AS nom1, nomen1.cdescrip AS nom2  FROM aprctb AS ctb 
                                        INNER JOIN aprtip AS tip ON ctb.id_tipo_cuenta = tip.id_tipo
                                        INNER JOIN aprtipdoc AS tpc ON ctb.id_tipo_doc = tpc.id
                                        INNER JOIN ctb_nomenclatura AS nomen ON ctb.id_cuenta1 = nomen.id
                                        INNER JOIN ctb_nomenclatura AS nomen1 ON ctb.id_cuenta2 = nomen1.id");
                                            while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                                $id_ctb = $row["id"];
                                                $id_tipo_cuenta = $row["id_tipo_cuenta"];
                                                $nombre = $row["nombre"];
                                                $id_tipo_doc = $row["id_tipo_doc"];
                                                $descripcion = $row["descripcion"];
                                                $id_cuenta1 = $row["id_cuenta1"];
                                                $cuenta1 = $row["cuenta1"];
                                                $nom1 = $row["nom1"];
                                                $id_cuenta2 = $row["id_cuenta2"];
                                                $cuenta2 = $row["cuenta2"];
                                                $nom2 = $row["nom2"];

                                                echo '<tr> <td>' . $nombre . '</td>';
                                                echo '<td>' . $descripcion . '</td>';
                                                echo '<td>' . $cuenta1 . '</td>';
                                                echo '<td>' . $cuenta2 . '</td>';
                                                echo '<td>
                                                <button type="button" class="btn btn-default" title="Editar" onclick="printdiv2(`#cuadro`,[' . $id_ctb . ',' . $id_tipo_cuenta . ',' . $id_tipo_doc . ',' . $id_cuenta1 . ',' . $cuenta1 . ',` - ' . $nom1 . '`,' . $id_cuenta2 . ',' . $cuenta2 . ',` - ' . $nom2 . '`])"> <i class="fa-solid fa-pen"></i></button>
                                                <button type="button" class="btn btn-default" title="Eliminar" onclick="eliminar(' . $id_ctb . ',`crud_aportaciones`,`0`,`delete_aprt_cuentas_contables`)"> <i class="fa-solid fa-trash-can"></i></button>
                                             </td></tr> ';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        //Datatable para parametrizacion
                        $(document).ready(function() {
                            convertir_tabla_a_datatable("table_parametrizacion");
                        });
                    </script>

                    <div class="container contenedort">
                        <div class="row mb-3 mt-2">
                            <div class="col-sm-6">
                                <div class="form-floating">
                                    <select class="form-select" id="tip_cuenta" aria-label="Tipos de cuenta">
                                        <option selected value="0">Seleccionar tipo de cuenta</option>
                                        <?php
                                        $tipdoc = mysqli_query($conexion, "SELECT * FROM `aprtip`");
                                        $selected = "";
                                        while ($tip = mysqli_fetch_array($tipdoc)) {
                                            ($tip['id_tipo'] == $id[1]) ? $selected = "selected" : $selected = "";
                                            echo '<option value="' . $tip['id_tipo'] . '"' . $selected . '>' . $tip['ccodtip'] . ' - ' . ($tip['nombre']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="tip_cuenta">Tipo de cuenta</label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-floating">
                                    <select class="form-select" id="tip_doc" aria-label="Tipos de cuenta">
                                        <option selected value="0">Seleccionar tipo de documento</option>
                                        <?php
                                        $tipdoc = mysqli_query($conexion, "SELECT * FROM `aprtipdoc`");
                                        $selected = "";
                                        while ($tip = mysqli_fetch_array($tipdoc)) {
                                            ($tip['id'] == $id[2]) ? $selected = "selected" : $selected = "";
                                            echo '<option value="' . $tip['id'] . '"' . $selected . '>' . ($tip['descripcion']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="tip_doc">Tipo de documento</label>
                                </div>

                            </div>
                        </div>
                        <!--Aho_0_BeneAho Nombre-->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group" id="div_cuenta1">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="text_cuenta1" readonly value="<?php echo $id[4] . $id[5]; ?>">
                                        <input type="text" class="form-control" id="id_hidden1" hidden value="<?php echo $id[3]; ?>" readonly>
                                        <label class="text-primary" for="text_cuenta1"><i class="fa-solid fa-file-invoice"></i>Cuenta 1</label>
                                    </div>
                                    <span type="button" class="input-group-text" id="basic-addon2" title="Buscar nomenclatura" onclick="abrir_modal('#modal_nomenclatura', '#id_modal_hidden', 'id_hidden1,text_cuenta1/A,2-3/-')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group" id="div_cuenta2">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="text_cuenta2" readonly value="<?php echo $id[7] . $id[8]; ?>">
                                        <input type="text" class="form-control" id="id_hidden2" hidden value="<?php echo $id[6]; ?>" readonly>
                                        <label class="text-primary" for="text_cuenta2"><i class="fa-solid fa-file-invoice"></i>Cuenta 2</label>
                                    </div>
                                    <span type="button" class="input-group-text" id="basic-addon2" title="Buscar nomenclatura" onclick="abrir_modal('#modal_nomenclatura', '#id_modal_hidden', 'id_hidden2,text_cuenta2/A,2-3/-')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <!-- en el metodo onclick se envian usuario y oficina para saber las cuentas de agencia a generar -->
                            <button type="button" class="<?php echo ($id[0] == 0) ? "btn btn-outline-success" : "btn btn-outline-primary" ?>" onclick="<?php echo ($id[0] == 0) ? ("obtiene([`id_hidden1`,`id_hidden2`],[`tip_cuenta`,`tip_doc`],[],  `create_aprt_cuentas_contables`,`0`,['$codusu'])")
                                                                                                                                                            : ("obtiene([`id_hidden1`,`id_hidden2`,`text_cuenta1`,`text_cuenta2`],[`tip_cuenta`,`tip_doc`],[],  `update_aprt_cuentas_contables`,`0`,['$codusu','$id[0]'])") ?>">
                                <i class="<?php echo ($id[0] == 0) ? "fa-solid fa-plus" : "fa-solid fa-pen-to-square" ?>"></i><?php echo ($id[0] == 0) ? "Agregar" : "Actualizar" ?>
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
            </div>

        <?php
        }
        break;
    case 'Parametrizacion_aprt_interes': {
            $codusu = $_SESSION['id'];
            $id = $_POST["xtra"];
            if ($id == 0) {
                $id = ['0', '0', '0', "", "", "", "", "", ""];
            }
        ?>
            <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
            <div class="text" style="text-align:center">PARAMETRIZACIÓN DE ACREDITACIÓN Y PROVISIÓN</div>
            <div class="card">
                <input type="text" id="file" value="APRT_6" style="display: none;">
                <input type="text" id="condi" value="Parametrizacion_aprt_interes" style="display: none;">
                <div class="card-header">Parametrizacion de Acreditación y Provisión</div>
                <div class="card-body">
                    <div class="container contenedort">
                        <div class="row mt-2 pb-2">
                            <div class="col">
                                <div class="table-responsive">
                                    <table id="table_parametrizacion" class="table table-hover table-border">
                                        <thead class="text-light table-head-aprt">
                                            <tr>
                                                <th>Tipo de cuenta</th>
                                                <th>Operación</th>
                                                <th>Cuenta 1</th>
                                                <th>Cuenta 2</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tb_cuerpo_parametrizacion">
                                            <?php
                                            $consulta = mysqli_query($conexion, "SELECT prt.id, prt.id_tipo_cuenta, tip.nombre, prt.id_descript_intere, inte.nombre AS nombre_inte, prt.id_cuenta1, prt.id_cuenta2, 
                                        nomen.ccodcta AS cuenta1, nomen1.ccodcta AS cuenta2, nomen.cdescrip AS nom1, nomen1.cdescrip AS nom2
                                        FROM aprparaintere AS prt 
                                        INNER JOIN aprtip AS tip ON prt.id_tipo_cuenta = tip.id_tipo
                                        INNER JOIN ctb_descript_intereses AS inte ON prt.id_descript_intere = inte.id
                                        INNER JOIN tb_usuario AS us ON prt.id_usuario = us.id_usu
                                        INNER JOIN ctb_nomenclatura AS nomen ON prt.id_cuenta1 = nomen.id
                                        INNER JOIN ctb_nomenclatura AS nomen1 ON prt.id_cuenta2 = nomen1.id");
                                            // WHERE us.agencia = '$agencia'
                                            while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                                $id_int = $row["id"];
                                                $id_tipo_cuenta = $row["id_tipo_cuenta"];
                                                $nombre = $row["nombre"];
                                                $id_descript_intere = $row["id_descript_intere"];
                                                $nombre_inte = $row["nombre_inte"];
                                                $id_cuenta1 = $row["id_cuenta1"];
                                                $cuenta1 = $row["cuenta1"];
                                                $nom1 = $row["nom1"];
                                                $id_cuenta2 = $row["id_cuenta2"];
                                                $cuenta2 = $row["cuenta2"];
                                                $nom2 = $row["nom2"];

                                                $params = json_encode([
                                                    $id_int,
                                                    $id_tipo_cuenta,
                                                    $id_descript_intere,
                                                    $id_cuenta1,
                                                    $cuenta1,
                                                    " - " . $nom1,
                                                    $id_cuenta2,
                                                    $cuenta2,
                                                    " - " . $nom2
                                                ]);

                                                echo '<tr> <td>' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</td>';
                                                echo '<td>' . htmlspecialchars($nombre_inte, ENT_QUOTES, 'UTF-8') . '</td>';
                                                echo '<td>' . htmlspecialchars($cuenta1, ENT_QUOTES, 'UTF-8') . '</td>';
                                                echo '<td>' . htmlspecialchars($cuenta2, ENT_QUOTES, 'UTF-8') . '</td>';
                                                echo '<td>
                                                <button type="button" class="btn btn-default" title="Editar" onclick=\'printdiv2("#cuadro", ' . $params . ')\'> <i class="fa-solid fa-pen"></i></button>
                                                <button type="button" class="btn btn-default" title="Eliminar" onclick="eliminar(' . $id_int . ',`crud_ahorro`,`0`,`delete_aho_cuentas_intereses`)"> <i class="fa-solid fa-trash-can"></i></button>
                                            </td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        //Datatable para parametrizacion
                        $(document).ready(function() {
                            convertir_tabla_a_datatable("table_parametrizacion");
                        });
                    </script>

                    <div class="container contenedort">
                        <div class="row mb-3 mt-2">
                            <div class="col-sm-6">
                                <div class="form-floating">
                                    <select class="form-select" id="tip_cuenta" aria-label="Tipos de cuenta">
                                        <option selected value="0">Seleccionar tipo de cuenta</option>
                                        <?php
                                        $tipdoc = mysqli_query($conexion, "SELECT * FROM `aprtip`");
                                        $selected = "";
                                        while ($tip = mysqli_fetch_array($tipdoc)) {
                                            ($tip['id_tipo'] == $id[1]) ? $selected = "selected" : $selected = "";
                                            echo '<option value="' . $tip['id_tipo'] . '"' . $selected . '>' . $tip['ccodtip'] . ' - ' . ($tip['nombre']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="tip_cuenta">Tipo de cuenta</label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-floating">
                                    <select class="form-select" id="tip_operacion" aria-label="Tipos de cuenta">
                                        <option selected value="0">Seleccionar tipo de operación</option>
                                        <?php
                                        $tip_op = mysqli_query($conexion, "SELECT * FROM `ctb_descript_intereses`");
                                        $selected = "";
                                        while ($tip = mysqli_fetch_array($tip_op)) {
                                            ($tip['id'] == $id[2]) ? $selected = "selected" : $selected = "";
                                            echo '<option value="' . $tip['id'] . '"' . $selected . '>' . ($tip['nombre']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="tip_operacion">Tipo de operación</label>
                                </div>

                            </div>
                        </div>
                        <!--Aho_0_BeneAho Nombre-->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group" id="div_cuenta1">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="text_cuenta1" readonly value="<?php echo $id[4] . $id[5]; ?>">
                                        <input type="text" class="form-control" id="id_hidden1" hidden value="<?php echo $id[3]; ?>" readonly>
                                        <label class="text-primary" for="text_cuenta1"><i class="fa-solid fa-file-invoice"></i>Cuenta 1 - Debe</label>
                                    </div>
                                    <span type="button" class="input-group-text" id="basic-addon2" title="Buscar nomenclatura" onclick="abrir_modal('#modal_nomenclatura', '#id_modal_hidden', 'id_hidden1,text_cuenta1/A,2-3/-')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group" id="div_cuenta2">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="text_cuenta2" readonly value="<?php echo $id[7] . $id[8]; ?>">
                                        <input type="text" class="form-control" id="id_hidden2" hidden value="<?php echo $id[6]; ?>" readonly>
                                        <label class="text-primary" for="text_cuenta2"><i class="fa-solid fa-file-invoice"></i>Cuenta 2 - Haber</label>
                                    </div>
                                    <span type="button" class="input-group-text" id="basic-addon2" title="Buscar nomenclatura" onclick="abrir_modal('#modal_nomenclatura', '#id_modal_hidden', 'id_hidden2,text_cuenta2/A,2-3/-')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <!-- en el metodo onclick se envian usuario y oficina para saber las cuentas de agencia a generar -->
                            <button type="button" class="<?php echo ($id[0] == 0) ? "btn btn-outline-success" : "btn btn-outline-primary" ?>" onclick="<?php echo ($id[0] == 0) ? ("obtiene([`id_hidden1`,`id_hidden2`],[`tip_cuenta`,`tip_operacion`],[],  `create_aprt_cuentas_intereses`,`0`,['$codusu'])") : ("obtiene([`id_hidden1`,`id_hidden2`,`text_cuenta1`,`text_cuenta2`],[`tip_cuenta`,`tip_operacion`],[],  `update_aprt_cuentas_intereses`,`0`,['$codusu','$id[0]'])") ?>">
                                <i class="<?php echo ($id[0] == 0) ? "fa-solid fa-plus" : "fa-solid fa-pen-to-square" ?>"></i><?php echo ($id[0] == 0) ? "Agregar" : "Actualizar" ?>
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
            </div>
        <?php
        }
        break;
    case 'calculo_interes_aprt': {
            $agencia = $_SESSION['agencia'];
            $codusu = $_SESSION['id'];
        ?>
            <input type="text" id="file" value="APRT_6" style="display: none;">
            <input type="text" id="condi" value="calculo_interes_aprt" style="display: none;">
            <div class="text" style="text-align:center">INTERESES MANUALES DE APRT</div>
            <div class="card">
                <div class="card-header">Intereses Manuales</div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col">
                            <div class="card">
                                <div class="card-header">Filtro por tipos de cuentas</div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="r_cuenta" id="all" value="all" checked onclick="activar_select_cuentas(this,true, 'tipcuenta')">
                                                <label for="Ting" class="form-check-label">Todo </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="r_cuenta" id="any" value="any" onclick="activar_select_cuentas(this,false, 'tipcuenta')">
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
                                                    $cuentas = mysqli_query($conexion, "SELECT * FROM `aprtip` WHERE ccodage='" . $agencia . "'");
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
                        <div class="col">
                            <div class="card">
                                <div class="card-header">Filtro por fechas</div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="r_fecha" id="ftodo" value="ftodo" checked onclick="activar_input_dates(this, true, 'fechaInicio', 'fechaFinal')">
                                                <label for="ftodo" class="form-check-label">Todo</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="r_fecha" id="frango" value="frango" onclick="activar_input_dates(this, false, 'fechaInicio', 'fechaFinal')">
                                                <label for="frango" class="form-check-label">Rango</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class=" col-sm-5">
                                            <label for="finicio">Desde</label>
                                            <input type="date" class="form-control" id="fechaInicio" min="1950-01-01" value="<?php echo date("Y-m-d"); ?>" disabled>
                                        </div>
                                        <div class=" col-sm-5">
                                            <label for="ffin">Hasta</label>
                                            <input type="date" class="form-control" id="fechaFinal" min="1950-01-01" value="<?php echo date("Y-m-d"); ?>" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Botones-->
                    <div class="row justify-items-md-center mb-3">
                        <div class="col align-items-center" id="modal_footer">
                            <button type="button" id="btnSave" class="btn btn-outline-success" onclick="obtiene([`fechaInicio`,`fechaFinal`],[`tipcuenta`],[`r_cuenta`,`r_fecha`],`procesar_interes_aprt`,`0`,['<?php echo $agencia; ?>','<?php echo $codusu; ?>'])">
                                <i class="fa-solid fa-file-export"></i> Procesar
                            </button>
                            <button type="button" class="btn btn-outline-warning" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>
                        </div>
                    </div>

                    <!-- tabla -->
                    <div class="container contenedort" style="padding: 10px 8px 10px 8px !important;">
                        <div class="table-responsive">
                            <table id="table_id2" class="table table-hover table-border">
                                <thead class="text-light table-head-aprt" style="font-size: 0.8rem;">
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
                                    $query = mysqli_query($conexion, "SELECT id, round(int_total,5) AS intotal,round(isr_total,5) AS isrtotal,fecmod,rango,tipo,partida,acreditado,codusu,fechacorte from `aprinteredetalle` order by fecmod DESC");
                                    while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
                                        $idcal = utf8_encode($row["id"]);
                                        $fecha = utf8_encode($row["fecmod"]);
                                        $intereses = number_format((float)utf8_encode($row["intotal"]), 2);
                                        $isrcal = number_format((float)utf8_encode($row["isrtotal"]), 2);
                                        $rango = utf8_encode($row["rango"]);
                                        $tipcuenta = utf8_encode($row["tipo"]);
                                        $partida = utf8_encode($row["partida"]);
                                        $acreditado = utf8_encode($row["acreditado"]);
                                        $usuario = utf8_encode($row["codusu"]);
                                        $fechacorte = utf8_encode($row["fechacorte"]);

                                        ($acreditado == 1) ? $acre = $check : $acre = '<button type="button" class="btn btn-outline-secondary" style="padding: 6px 9px !important;" title="Acreditacr" onclick="obtiene([`fechaInicio`],[`tipcuenta`],[`r_cuenta`],`acreditar_intereses`,`0`,[' . $idcal . ',`' . $fechacorte . '`,`' . $agencia . '`,' . $codusu . ',`' . $rango . '`])">
                                    <i class="fa-solid fa-money-bill-transfer"></i>
                                </button>';

                                        ($partida == 1) ? $part = $check : $part = '<button type="button" class="btn btn-outline-primary" title="Partida de provision" onclick="obtiene([`fechaInicio`],[`tipcuenta`],[`r_cuenta`],`partida_aprov_intereses`,`0`,[' . $idcal . ',`' . $fechacorte . '`,' . $codusu . ',`' . $rango . '`])">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                </button>';

                                        echo '<tr>
                                            <td>' . $idcal . ' </td>
                                            <td>' . $fecha . ' </td>
                                            <td>' . $rango . ' </td>
                                            <td>' . $tipcuenta . ' </td>
                                            <td>' . $intereses . ' </td>
                                            <td>' . $isrcal . '</td>
                                            <td align="center">' . $acre . '
                                            </td>
                                            <td align="center">' . $part . '
                                             </td>
                                            <td> <button type="button" class="btn btn-outline-success" title="Reporte Excel" onclick="reportes_aportaciones([`reportes_aportaciones`, `intereses_aprt`, `excel`, `xlsx`,`' . date("Y-m-d") . '`,' . $idcal . ',])">
                                                    <i class="fa-solid fa-file-excel"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" style="padding: 6px 10px !important;" title="Reporte pdf" onclick="reportes_aportaciones([`reportes_aportaciones`, `intereses_aprt`, `pdf`, `pdf`, `' . date("Y-m-d") . '`,' . $idcal . '])">
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
        }
        break;
    case 'List_mov_recibos_aprt':
        $codusu = $_SESSION['id'];
        $where = "";
        $mensaje_error = "";
        $bandera_error = false;
        //Validar si ya existe un registro igual que el nombre
        $nuew = "ccodusu='$codusu' AND (dfecsis BETWEEN '" . date('Y-m-d', strtotime(date('Y-m-d') . ' - 7 days')) . "' AND  '" . date('Y-m-d') . "')";
        try {
            $stmt = $conexion->prepare("SELECT IF(tu.puesto='ADM' OR tu.puesto='GER', '1=1', ?) AS valor FROM tb_usuario tu WHERE tu.id_usu = ?");
            if (!$stmt) {
                throw new Exception("Error en la consulta: " . $conexion->error);
            }
            $stmt->bind_param("ss", $nuew, $codusu);
            if (!$stmt->execute()) {
                throw new Exception("Error al consultar: " . $stmt->error);
            }
            $result = $stmt->get_result();
            $whereaux = $result->fetch_assoc();
            $where = $whereaux['valor'];
        } catch (Exception $e) {
            //Captura el error
            $mensaje_error = $e->getMessage();
            $bandera_error = true;
        }
        ?>
        <input type="text" id="file" value="APRT_6" style="display: none;">
        <input type="text" id="condi" value="List_mov_recibos_aprt" style="display: none;">
        <div class="text" style="text-align:center">RECIBOS DE APORTACIONES</div>
        <div class="card">
            <div class="card-header">Recibos de aportaciones</div>
            <div class="card-body">
                <?php if ($bandera_error) { ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>¡Error!</strong> <?= $mensaje_error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } ?>
                <!-- tabla -->
                <div class="container contenedort" style="padding: 10px 8px 10px 8px !important;">
                    <div class="table-responsive">
                        <table id="tabla_recibos_aportaciones" class="table table-hover table-border nowrap" style="width:100%">
                            <thead class="text-light table-head-aprt" style="font-size: 0.8rem;">
                                <tr>
                                    <th>ID</th>
                                    <th>No. Recibo</th>
                                    <th>No. Cuenta</th>
                                    <th>Razon</th>
                                    <th>Tipo documento</th>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <script>
                $(document).ready(function() {
                    $("#tabla_recibos_aportaciones").DataTable({
                        "processing": true,
                        "serverSide": true,
                        "sAjaxSource": "../src/server_side/lista_recibos_aportaciones.php",
                        columns: [{
                                data: [0]
                            },
                            {
                                data: [1]
                            },
                            {
                                data: [2]
                            },
                            {
                                data: [3]
                            },
                            {
                                data: [4]
                            },
                            {
                                data: [5]
                            },
                            {
                                data: [6]
                            },
                            {
                                data: [0],
                                render: function(data, type, row) {
                                    imp = '';
                                    imp1 = '';
                                    imp2 = '';
                                    if (row[8] == "1") {
                                        //imp1 = `<button type="button" class="btn btn-primary btn-sm me-1 ms-1" title="Reversion recibo" onclick="obtiene([],[],[],'reversion_recibo','0',['${row[0]}','<?= $codusu ?>'])"><i class="fa-solid fa-trash"></i></button>`;
                                        imp1 = `<button type="button" class="btn btn-primary btn-sm me-1 ms-1" title="Eliminacion recibo" onclick="eliminar('${row[0]}', 'crud_aportaciones', '0', 'eliminacion_recibo');"><i class="fa-solid fa-trash"></i></button>`;
                                        imp2 = `<button type="button" class="btn btn-warning btn-sm" title="Edicion" onclick="modal_edit_recibo('${row[0]}','${row[1]}', '${row[2]}','<?= $codusu ?>')"><i class="fa-solid fa-pen-to-square"></i></button>`;
                                    }
                                    imp = `<button type="button" class="btn btn-secondary btn-sm" title="Reimpresion" onclick="obtiene([],[],[],'reimpresion_recibo','0',['${row[0]}','<?= $codusu ?>'])"><i class="fa-solid fa-print"></i></button>`;
                                    return imp + imp1 + imp2;
                                }
                            },
                        ],
                        "fnServerParams": function(aoData) {
                            //PARAMETROS EXTRAS QUE SE LE PUEDEN ENVIAR AL SERVER ASIDE
                            aoData.push({
                                "name": "whereextra",
                                "value": "<?= $where; ?>"
                            });
                        },
                        "bDestroy": true,
                        "language": {
                            "lengthMenu": "Mostrar _MENU_ registros",
                            "zeroRecords": "No se encontraron registros",
                            "info": " ",
                            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                            "infoFiltered": "(filtrado de un total de: _MAX_ registros)",
                            "sSearch": "Buscar: ",
                            "oPaginate": {
                                "sFirst": "Primero",
                                "sLast": "Ultimo",
                                "sNext": "Siguiente",
                                "sPrevious": "Anterior"
                            },
                            "sProcessing": "Procesando..."
                        }

                    });
                });
            </script>
        </div>
<?php
        break;
}
?>

<!-- MODAL PARA EDICION DE RECIBO -->
<div class="modal fade" id="edicion_recibo" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Edición de recibo</h1>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-6">
                        <!-- titulo -->
                        <span class="input-group-addon col-8">No. Recibo anterior</span>
                        <div class="input-group">
                            <input type="text" class="form-control " id="id_recibo" readonly hidden>
                            <input type="text" class="form-control " id="id_codusu" readonly hidden>
                            <input type="text" class="form-control " id="numdoc_modal_recibo_ant" readonly>
                        </div>
                    </div>
                    <div class="col-6">
                        <!-- titulo -->
                        <span class="input-group-addon col-8">Cuenta de aportación</span>
                        <div class="input-group">
                            <input type="text" class="form-control " id="ccodaport_recibo" readonly>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <span class="input-group-addon col-8">Nuevo número de certificado</span>
                        <input type="text" aria-label="Certificado" id="numdoc_modal_recibo" class="form-control  col" placeholder="" required>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="cancelar_ben" onclick="obtiene(['id_recibo','numdoc_modal_recibo_ant','numdoc_modal_recibo','id_codusu'], [], [], 'edicion_recibo', '0', ['0'])">Guardar</button>
                <button type="button" class="btn btn-secondary" id="cancelar_ben" onclick="cancelar_edit_recibo()">Cancelar</button>
            </div>
        </div>
    </div>
</div>
<?php
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