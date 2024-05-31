<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];

switch ($condi) {
    case 'PrmtrzcAhrrs':
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
        <div class="text" style="text-align:center">PARAMETRIZACION DE AHORRO</div>
        <div class="card">
            <input type="text" id="file" value="aho_07" style="display: none;">
            <input type="text" id="condi" value="PrmtrzcAhrrs" style="display: none;">
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
                                            <!-- <th>Acciones</th> -->
                                        </tr>
                                    </thead>
                                    <tbody id="tb_cuerpo_parametrizacion">
                                        <?php
                                        $i = 1;
                                        $consulta = mysqli_query($conexion, "SELECT tip.*,ofi.nom_agencia FROM ahomtip tip INNER JOIN tb_agencia ofi ON ofi.cod_agenc=tip.ccodofi;");
                                        while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                            $id = $row["id_tipo"];
                                            $agencia = $row["nom_agencia"];
                                            $id_tipo_cuenta = $row["ccodtip"];
                                            $nombre = $row["nombre"];
                                            $tasa = $row["tasa"];
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
                                            echo '<tr> <td>' . $i . '</td>';
                                            echo '<td>' . $agencia . '</td>';
                                            echo '<td>' . $nombre . '</td>';
                                            echo '<td>' . $tasa . '</td>';
                                            // echo '<td>' . $cuenta . ' - ' . $namecuenta . '</td>';
                                            echo '<td>' . $cuenta . ' - ' . $namecuenta . '
                                                <button type="button" class="btn btn-default" title="Editar" onclick="loaddata([' . $id . ',`' . $nombre . '`,' . $id_cuenta . ',`' . $cuenta . '`,`' . $namecuenta . '`])"> <i class="fa-solid fa-pen"></i></button>
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
                        <div class="col-md-6">
                            <div class="row"></div>
                            <div class="input-group" id="div_cuenta1">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="text_cuenta1" placeholder="name@example.com" readonly>
                                    <input type="text" class="form-control" id="id_hidden1" hidden readonly>
                                    <label class="text-primary" for="text_cuenta1"><i class="fa-solid fa-file-invoice"></i>Cuenta Contable</label>
                                </div>
                                <span type="button" class="input-group-text" id="basic-addon2" title="Buscar nomenclatura" onclick="abrir_modal('#modal_nomenclatura', 'show', '#id_modal_hidden', '1')">
                                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row justify-items-md-center">
                    <div class="col align-items-center" id="modal_footer">
                        <button id="save" style="display: none;" type="button" class="btn btn-outline-primary" onclick="obtiene([`id_hidden1`,`idtipo`],[],[],`update_aho_cuentas_contables`,`0`,[])">
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
                $('#save').show();
            }
        </script>

    <?php
        break;
    case 'PrmtrzcAhrrsant': //antes de la remodelacion
        $codusu = $_SESSION['id'];
        $id = $_POST["xtra"];
        $bandera = false;
        if ($id == 0) {
            $id = ['0', '0', '0', "", "", "", "", "", ""];
        }
    ?>
        <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
        <div class="text" style="text-align:center">PARAMETRIZACION DE AHORRO</div>
        <div class="card">
            <input type="text" id="file" value="aho_07" style="display: none;">
            <input type="text" id="condi" value="PrmtrzcAhrrs" style="display: none;">
            <div class="card-header">Parametrizacion Ahorro</div>
            <div class="card-body">
                <div class="container contenedort">
                    <div class="row mt-2 pb-2">
                        <div class="col">
                            <div class="table-responsive">
                                <table id="table_parametrizacion" class="table table-hover table-border">
                                    <thead class="text-light table-head-aho">
                                        <tr>
                                            <th>TIPO CUENTA</th>
                                            <th>DOCUMENTO</th>
                                            <th>CUENTA1</th>
                                            <th>CUENTA2</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tb_cuerpo_parametrizacion">
                                        <?php
                                        $consulta = mysqli_query($conexion, "SELECT ctb.id, ctb.id_tipo_cuenta, tip.nombre, ctb.id_tipo_doc, tpc.descripcion, ctb.id_cuenta1, ctb.id_cuenta2, nomen.ccodcta AS cuenta1, nomen1.ccodcta AS cuenta2, nomen.cdescrip AS nom1, nomen1.cdescrip AS nom2  FROM ahomctb AS ctb 
                                        INNER JOIN ahomtip AS tip ON ctb.id_tipo_cuenta = tip.id_tipo
                                        INNER JOIN ahotipdoc AS tpc ON ctb.id_tipo_doc = tpc.id
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
                                                <button type="button" class="btn btn-default" title="Eliminar" onclick="eliminar(' . $id_ctb . ',`crud_ahorro`,`0`,`delete_aho_cuentas_contables`)"> <i class="fa-solid fa-trash-can"></i></button>
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
                                    $tipdoc = mysqli_query($conexion, "SELECT * FROM `ahomtip`");
                                    $selected = "";
                                    // echo '<option selected value="0">Seleccione un tipo de cuenta</option>';
                                    while ($tip = mysqli_fetch_array($tipdoc)) {
                                        ($tip['id_tipo'] == $id[1]) ? $selected = "selected" : $selected = "";
                                        echo '<option value="' . $tip['id_tipo'] . '"' . $selected . '>' . $tip['ccodtip'] . ' - ' . $tip['nombre'] . '</option>';
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
                                    $tipdoc = mysqli_query($conexion, "SELECT * FROM `ahotipdoc`");
                                    $selected = "";
                                    while ($tip = mysqli_fetch_array($tipdoc)) {
                                        ($tip['id'] == $id[2]) ? $selected = "selected" : $selected = "";
                                        echo '<option value="' . $tip['id'] . '"' . $selected . '>' . $tip['descripcion'] . '</option>';
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
                            <div class="row"></div>
                            <div class="input-group" id="div_cuenta1">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="text_cuenta1" placeholder="name@example.com" readonly value="<?php echo $id[4] . $id[5]; ?>">
                                    <input type="text" class="form-control" id="id_hidden1" hidden value="<?php echo $id[3]; ?>" readonly>
                                    <label class="text-primary" for="text_cuenta1"><i class="fa-solid fa-file-invoice"></i>Cuenta 1 - Debe</label>
                                </div>
                                <span type="button" class="input-group-text" id="basic-addon2" title="Buscar nomenclatura" onclick="abrir_modal('#modal_nomenclatura', 'show', '#id_modal_hidden', '1')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group" id="div_cuenta2">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="text_cuenta2" placeholder="name@example.com" readonly value="<?php echo $id[7] . $id[8]; ?>">
                                    <input type="text" class="form-control" id="id_hidden2" hidden value="<?php echo $id[6]; ?>" readonly>
                                    <label class="text-primary" for="text_cuenta2"><i class="fa-solid fa-file-invoice"></i>Cuenta 2 - Haber</label>
                                </div>
                                <span type="button" class="input-group-text" id="basic-addon2" title="Buscar nomenclatura" onclick="abrir_modal('#modal_nomenclatura', 'show', '#id_modal_hidden', '2')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>

                            </div>
                        </div>

                    </div>
                </div>
                <div class="row justify-items-md-center">
                    <div class="col align-items-center" id="modal_footer">
                        <!-- en el metodo onclick se envian usuario y oficina para saber las cuentas de agencia a generar -->
                        <button type="button" class="<?php echo ($id[0] == 0) ? "btn btn-outline-success" : "btn btn-outline-primary" ?>" onclick="<?php echo ($id[0] == 0) ? ("obtiene([`id_hidden1`,`id_hidden2`],[`tip_cuenta`,`tip_doc`],[],  `create_aho_cuentas_contables`,`0`,['$codusu'])")
                                                                                                                                                        : ("obtiene([`id_hidden1`,`id_hidden2`,`text_cuenta1`,`text_cuenta2`],[`tip_cuenta`,`tip_doc`],[],  `update_aho_cuentas_contables`,`0`,['$codusu','$id[0]'])") ?>">
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
        break;
    case 'Parametrizacion_aho_interes':
        $codusu = $_SESSION['id'];
        $id = $_POST["xtra"];
        if ($id == 0) {
            $id = ['0', '0', '0', "", "", "", "", "", ""];
        }
    ?>
        <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
        <div class="text" style="text-align:center">PARAMETRIZACIÓN DE ACREDITACIÓN Y PROVISIÓN</div>
        <div class="card">
            <input type="text" id="file" value="aho_07" style="display: none;">
            <input type="text" id="condi" value="Parametrizacion_aho_interes" style="display: none;">
            <div class="card-header">Parametrizacion de Acreditación y Provisión</div>
            <div class="card-body">
                <div class="container contenedort">
                    <div class="row mt-2 pb-2">
                        <div class="col">
                            <div class="table-responsive">


                                <table id="table_parametrizacion" class="table table-hover table-border">
                                    <thead class="text-light table-head-aho">
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
                                    FROM ahomparaintere AS prt 
                                    INNER JOIN ahomtip AS tip ON prt.id_tipo_cuenta = tip.id_tipo
                                    INNER JOIN ctb_descript_intereses AS inte ON prt.id_descript_intere = inte.id
                                    INNER JOIN tb_usuario AS us ON prt.id_usuario = us.id_usu
                                    INNER JOIN ctb_nomenclatura AS nomen ON prt.id_cuenta1 = nomen.id
                                    INNER JOIN ctb_nomenclatura AS nomen1 ON prt.id_cuenta2 = nomen1.id");
                                        // WHERE us.id_agencia = '3';
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
                                            $nom2 = ($row["nom2"]);

                                            // echo '<tr> <td>' . $nombre . '</td>';
                                            // echo '<td>' . $nombre_inte . '</td>';
                                            // echo '<td>' . $cuenta1 . '</td>';
                                            // echo '<td>' . $cuenta2 . '</td>';
                                            // echo '<td>
                                            //     <button type="button" class="btn btn-default" title="Editar" onclick="printdiv2(`#cuadro`,[' . $id_int . ',' . $id_tipo_cuenta . ',' . $id_descript_intere . ',' . $id_cuenta1 . ',' . $cuenta1 . ',` - ' . $nom1 . '`,' . $id_cuenta2 . ',' . $cuenta2 . ',` - ' . $nom2 . '`])"> <i class="fa-solid fa-pen"></i></button>
                                            //     <button type="button" class="btn btn-default" title="Eliminar" onclick="eliminar(' . $id_int . ',`crud_ahorro`,`0`,`delete_aho_cuentas_intereses`)"> <i class="fa-solid fa-trash-can"></i></button>
                                            //  </td></tr> ';
                                            // Crear un array con los valores a pasar a la función JavaScript
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
                                    $tipdoc = mysqli_query($conexion, "SELECT * FROM `ahomtip`");
                                    $selected = "";
                                    while ($tip = mysqli_fetch_array($tipdoc)) {
                                        ($tip['id_tipo'] == $id[1]) ? $selected = "selected" : $selected = "";
                                        echo '<option value="' . $tip['id_tipo'] . '"' . $selected . '>' . $tip['ccodtip'] . ' - ' . $tip['nombre'] . '</option>';
                                    }
                                    ?>
                                </select>
                                <label for="tip_cuenta">Tipo de cuenta</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-floating">
                                <select class="form-select" id="tip_doc" aria-label="Tipos de cuenta">
                                    <option selected value="0">Seleccionar tipo de operación</option>
                                    <?php
                                    $tip_op = mysqli_query($conexion, "SELECT * FROM `ctb_descript_intereses`");
                                    $selected = "";
                                    while ($tip = mysqli_fetch_array($tip_op)) {
                                        ($tip['id'] == $id[2]) ? $selected = "selected" : $selected = "";
                                        echo '<option value="' . $tip['id'] . '"' . $selected . '>' . $tip['nombre'] . '</option>';
                                    }
                                    ?>
                                </select>
                                <label for="tip_doc">Tipo de operación</label>
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
                                <span type="button" class="input-group-text" id="basic-addon2" title="Buscar nomenclatura" onclick="abrir_modal('#modal_nomenclatura', 'show', '#id_modal_hidden', '1')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group" id="div_cuenta2">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="text_cuenta2" readonly value="<?php echo $id[7] . $id[8]; ?>">
                                    <input type="text" class="form-control" id="id_hidden2" hidden value="<?php echo $id[6]; ?>" readonly>
                                    <label class="text-primary" for="text_cuenta2"><i class="fa-solid fa-file-invoice"></i>Cuenta 2 - Haber</label>
                                </div>
                                <span type="button" class="input-group-text" id="basic-addon2" title="Buscar nomenclatura" onclick="abrir_modal('#modal_nomenclatura', 'show', '#id_modal_hidden', '2')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="row justify-items-md-center">
                    <div class="col align-items-center" id="modal_footer">
                        <!-- en el metodo onclick se envian usuario y oficina para saber las cuentas de agencia a generar -->
                        <button type="button" class="<?php echo ($id[0] == 0) ? "btn btn-outline-success" : "btn btn-outline-primary" ?>" onclick="<?php echo ($id[0] == 0) ? ("obtiene([`id_hidden1`,`id_hidden2`],[`tip_cuenta`,`tip_doc`],[],  `create_aho_cuentas_intereses`,`0`,['$codusu'])") : ("obtiene([`id_hidden1`,`id_hidden2`,`text_cuenta1`,`text_cuenta2`],[`tip_cuenta`,`tip_doc`],[],  `update_aho_cuentas_intereses`,`0`,['$codusu','$id[0]'])") ?>">
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
        break;
    case 'cuenta__1': {
            $id = $_POST["xtra"];
        ?>
            <div class="form-floating">
                <input type="text" class="form-control" id="text_cuenta1" placeholder="name@example.com" readonly value="<?php echo $id[1] . " - " . $id[2]; ?>">
                <input type="text" class="form-control" id="id_hidden1" value="<?php echo $id[0]; ?>" hidden readonly>
                <label class="text-primary" for="text_cuenta1"><i class="fa-solid fa-file-invoice"></i>Cuenta 1 - Debe</label>
            </div>
            <span type="button" class="input-group-text" id="basic-addon2" title="Buscar nomenclatura" onclick="abrir_modal('#modal_nomenclatura', 'show', '#id_modal_hidden', '1')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
        <?php
        }
        break;
    case 'cuenta__2': {
            $id = $_POST["xtra"];
        ?>
            <div class="form-floating">
                <input type="text" class="form-control" id="text_cuenta2" placeholder="name@example.com" readonly value="<?php echo $id[1] . " - " . $id[2]; ?>">
                <input type="text" class="form-control" id="id_hidden2" value="<?php echo $id[0]; ?>" hidden readonly>
                <label class="text-primary" for="text_cuenta2"><i class="fa-solid fa-file-invoice"></i>Cuenta 2 - Haber</label>
            </div>
            <span type="button" class="input-group-text" id="basic-addon2" title="Buscar nomenclatura" onclick="abrir_modal('#modal_nomenclatura', 'show', '#id_modal_hidden', '2')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
        <?php
        }
        break;
    case 'mancomuna':
        $id = $_POST["xtra"];
        $ccodusu = $_SESSION['id'];
        $datos = [
            "id_tipo" => "",
        ];
        $idcli2 = NULL;
        $lista[] = [];
        $datoscli = mysqli_query($conexion, "SELECT * FROM `ahomcta` WHERE `ccodaho`=$id");
        $bandera = "Cuenta de ahorro no existe";
        while ($da = mysqli_fetch_array($datoscli, MYSQLI_ASSOC)) {

            $idcli = utf8_encode($da["ccodcli"]);
            $idcli2 = utf8_encode($da["ccodcli2"]);
            $lista[0][0] = $idcli;
            $nit = utf8_encode($da["num_nit"]);
            $nlibreta = utf8_encode($da["nlibreta"]);
            $estado = utf8_encode($da["estado"]);
            ($estado != "A") ? $bandera = "Cuenta de ahorros Inactiva" : $bandera = "";
        }
        if ($bandera == "") {
            $bandera = "Cliente vinculado a la cuenta no existe";
            $data = mysqli_query($conexion, "SELECT `short_name` FROM `tb_cliente` WHERE `idcod_cliente` = '$idcli'");
            while ($dat = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                $nombre = utf8_encode($dat["short_name"]);
                $lista[0][1] = $nombre;
                $bandera = "";
            }
        }

        ?>
        <div class="text" style="text-align:center">CONFIGURACION DE CUENTAS MANCOMUNADAS</div>
        <input type="text" id="file" value="aperturas" style="display: none;">
        <input type="text" id="condi" value="mancomuna" style="display: none;">
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
                <div class="container contenedort">
                    <h3>TITULARES DE LA CUENTA</h3>
                    <table id="tiposahorros" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Codigo de cliente</th>
                                <th>Nombre</th>
                                <th>Opciones</th>
                            </tr>
                        </thead>
                        <tbody id="categoria_tb">
                            <?php
                            if ($id != "0") {
                                if ($idcli2 != NULL) {
                                    $datacli2 = mysqli_query($conexion, "SELECT `short_name` FROM `tb_cliente` WHERE `idcod_cliente` = '$idcli2'");
                                    while ($dat = mysqli_fetch_array($datacli2, MYSQLI_ASSOC)) {
                                        $nombre2 = utf8_encode($dat["short_name"]);
                                    }
                                    $lista[1][0] = $idcli2;
                                    $lista[1][1] = $nombre2;
                                }
                                $i = 0;
                                while ($i < count($lista)) {

                                    echo '<tr> <td>' . $lista[$i][0] . '</td>';
                                    echo '<td>' . $lista[$i][1] . '</td>';
                                    if ($i == 1) {
                                        echo '<td>
                                                <button type="button" class="btn btn-default" title="Eliminar" onclick="eliminartitular(5,`crud_ahorro`,`0`,`dahomtip`)"> <i class="fa-solid fa-trash-can"></i></button></td>';
                                    } else {
                                        echo '<td></td>';
                                    }
                                    echo '</tr> ';
                                    $i++;
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php
                    if ($id != "0") {
                        if ($i >= 2) {
                            echo 'No se pueden agregar mas titulares a la cuenta';
                        } else {
                            echo '<button type="button" class="btn btn-default" title="Agregar nuevo titular" data-bs-toggle="modal" data-bs-target="#buscar_cli_gen"> <i class="fa-solid fa-plus"></i>Agregar Titular</button>';
                            echo '<div id="datacli"></div>';
                        }
                    }
                    ?>
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">

                            <button type="button" class="btn btn-outline-success" onclick="obtiene([`id_hidden1`,`id_hidden2`],[`tip_cuenta`,`tip_doc`],[],  `create_aho_cuentas_contables`,`0`,[`'<?php echo $codusu; ?>'`])">
                                <i class="fa-solid fa-ban"></i> Guardar
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
        break;
    case 'List_mov_recibos_aho':
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
        <input type="text" id="file" value="aho_07" style="display: none;">
        <input type="text" id="condi" value="List_mov_recibos_aho" style="display: none;">
        <div class="text" style="text-align:center">RECIBOS DE AHORROS</div>
        <div class="card">
            <div class="card-header">Recibos de ahorros</div>
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
                        <table id="tabla_recibos_ahorros" class="table table-hover table-border" style="width: 100%;">
                            <thead class="text-light table-head-aho" style="font-size: 0.8rem;">
                                <tr>
                                    <th>ID</th>
                                    <th>No. Recibo</th>
                                    <th>No. Cuenta</th>
                                    <th>Razon</th>
                                    <th>Tipo documento</th>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th style="width: 15%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $("#tabla_recibos_ahorros").DataTable({
                    "processing": true,
                    "serverSide": true,
                    "sAjaxSource": "../src/server_side/lista_recibos_ahorros.php",
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
                                imp3 = '';
                                if (row[8] == "1") {
                                    //imp1 = `<button type="button" class="btn btn-primary btn-sm me-1 ms-1" title="Reversion recibo" onclick="obtiene([],[],[],'reversion_recibo','0',['${row[0]}','<?= $codusu ?>'])"><i class="fa-solid fa-arrow-rotate-left"></i></button>`;
                                    imp3 = `<button type="button" class="btn btn-primary btn-sm me-1 ms-1" title="Eliminacion recibo" onclick="eliminar('${row[0]}', 'crud_ahorro', '0', 'eliminacion_recibo');"><i class="fa-solid fa-trash"></i></button>`;
                                    imp2 = `<button type="button" class="btn btn-warning btn-sm" title="Edicion" onclick="modal_edit_recibo('${row[0]}','${row[1]}', '${row[2]}','<?= $codusu ?>')"><i class="fa-solid fa-pen-to-square"></i></button>`;
                                }
                                imp = `<button type="button" class="btn btn-secondary btn-sm" title="Reimpresion" onclick="obtiene([],[],[],'reimpresion_recibo','0',['${row[0]}','<?= $codusu ?>'])"><i class="fa-solid fa-print"></i></button>`;
                                return imp + imp2 + imp3;
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
<?php
        break;
} //FINAL DEL SWITCH
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
                            <input type="text" class="form-control " id="ccodaho_recibo" readonly>
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