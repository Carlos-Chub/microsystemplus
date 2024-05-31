<?php

use PhpOffice\PhpSpreadsheet\Calculation\Engineering\BesselK;

if (!isset($_SESSION)) {
    session_start();
}
date_default_timezone_set('America/Guatemala');
$hoy = date("Y-m-d");
$hoy2 = date("Y-m-d H:i:s");
include_once "../../../src/cris_modales/mdls_cre_indi02.php";
include '../../../includes/BD_con/db_con.php';

include '../../../src/funcphp/valida.php';
include '../../../src/funcphp/func_gen.php';

mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');

$condi = $_POST["condi"];

switch ($condi) {
    case 'create_garantias':
        //$idPerfil = $_POST["xtra"]; // Captura la clavel del registro
        //$op = $_POST["option"]; //Opcion que se dese ejecutar 
        $idPerfil = $_POST["xtra"] ?? null; // Captura la clave del registro
        $op = isset($_POST["option"]) ? $_POST["option"] : 0;

        $codusu = $_SESSION['id']; //Usuario que ingreso al sistema

        //Se inicializan toda las variables a utilizar 
        $idGa = "";
        $idCli = "";
        $nameCli = "";
        $idGara = "";
        $idDoc = "";
        $des = "";
        $direc = "";
        $idDep = "";
        $idMun = "";
        $valComer = "";
        $monAva = "";
        $monGra = "";
        $fiador = "";
        $src = "";
        $tpCuenta = "";

        switch ($op) {
            case 1: //opcion de editar
                $datosGarantia = mysqli_query($conexion, "SELECT gaCli.idGarantia, gaCli.idCliente, cli.short_name AS Cliente, gaCli.idTipoGa AS idGa, gaCli.idTipoDoc AS idDoc , gaCli.descripcionGarantia, gaCli.direccion, gaCli.depa AS idDepa, gaCli.muni AS idMuni, gaCli.valorComercial, gaCli.montoAvaluo, gaCli.montoGravamen, gaCli.archivo,
                IF(gaCli.idTipoGa = 1, (SELECT short_name FROM tb_cliente WHERE idcod_cliente = gaCli.descripcionGarantia), 0) AS fiador,
                IF(gaCli.idTipoDoc = 8, (SELECT nombre FROM ahomtip AS tip WHERE tip.ccodtip = SUBSTRING(gaCli.descripcionGarantia,7,2)), 0) AS tp_cuenta
                FROM cli_garantia AS gaCli 
                INNER JOIN tb_cliente AS cli ON gaCli.idCliente = cli.idcod_cliente 
                INNER JOIN clhpzzvb_bd_general_coopera.tb_tiposgarantia AS tipoGa ON gaCli.idTipoGa = tipoGa.id_TiposGarantia
                INNER JOIN clhpzzvb_bd_general_coopera.tb_tiposdocumentosR AS tipoDoc ON gaCli.idTipoDoc = tipoDoc.idDoc
                WHERE gaCli.estado = 1 AND gaCli.idGarantia = " . $idPerfil);

                $data = mysqli_fetch_array($datosGarantia);
                //$imgurl = __DIR__ . "../../../../../" . utf8_encode($datosCli["url_img"]); FORMA INCORRECTA DE PONER LA RUTA

                $idGa = $data['idGarantia'];
                $idCli = $data['idCliente'];
                $nameCli = $data['Cliente'];
                $idGara = $data['idGa'];
                $idDoc = $data['idDoc'];

                $des = $data['descripcionGarantia'];
                $direc = $data['direccion'];
                $idDep = $data['idDepa'];
                $idMun = $data['idMuni'];
                $valComer = $data['valorComercial'];
                $monAva = $data['montoAvaluo'];
                $monGra = $data['montoGravamen'];
                //$archi = $data['archivo'];
                $fiador = $data['fiador'];
                $tpCuenta = $data['tp_cuenta'];

                $archiURL = __DIR__ . '/../../../../' . $data['archivo']; //FORMA CORRECTA DE PONER LA RUTA CON UNA DIAGONAL ANTES

                if (!is_file($archiURL)) {
                    $src = '../../includes/img/fotoClienteDefault.png';
                } else {
                    $imginfo   = getimagesize($archiURL);
                    $mimetype  = $imginfo['mime'];
                    $imageData = base64_encode(file_get_contents($archiURL));
                    $src = 'data:' . $mimetype . ';base64,' . $imageData;
                }
                break;

            case 2:
                $datosGarantia = mysqli_query($conexion, "SELECT idcod_cliente, short_name FROM tb_cliente WHERE idcod_cliente = " . $idPerfil);
                $data = mysqli_fetch_array($datosGarantia);
                //****************************************/
                $idCli = $data['idcod_cliente'];
                $nameCli = $data['short_name'];
                $idDep = 1;
                break;
        }
?>
<!-- *** los dos inputs + la funcion printdiv2 se utilizan para remprimir la pantalla pero todo se pierde *** -->
<!-- Input que se utiliza para controlar el case del switch -->
<input type="text" id="condi" value="create_garantias" hidden>
<!-- Input que se utiliza para controlar el archvivo donde se esta programando -->
<input type="text" id="file" value="cre_indi_02" hidden>
<!-- Input para llevar el control de las opciones -->
<input type="text" id="idControl" value="<?php echo $op; ?>" hidden>

<!-- LLAMAR AL MODAL PARA SELECIONAR EL CLIENTE -->
<div id="modalCliente"></div> <!-- llamar al modal cliente -->

<div class="text" style="text-align:center">GARANTÍAS</div>
<div class="card">
    <div class="card-header">GARANTÍAS</div>
    <div class="card-body">
        <form id="miForm" action="">
            <!-- Input para controlar la id de la garantia -->
            <input type="text" id="idGarantia" placeholder="idGarantia" disabled value="<?php echo $idGa; ?>" hidden>
            <!-- Input para controlar la id del usuario  -->
            <input type="text" id="idUser" placeholder="idGarantia" disabled value="<?php echo $codusu ?>" hidden>

            <div class="row">
                <div class="col-lg-6 col-md-12 mt-1">
                    <label for="exampleInputEmail1" class="form-label">Seleccionar un cliente</label>
                    <div class="input-group mb-3">
                        <button id="btnBus" class="btn btn-warning" type="button" data-bs-toggle="modal"
                            data-bs-target="#modalCliente">Buscar</button>
                        <input id="cliente" type="text" class="form-control" placeholder="Cliente"
                            aria-label="Example text with button addon" aria-describedby="button-addon1"
                            readonly="readonly" value="<?php echo $nameCli ?>">
                    </div>
                </div>

                <div class="col-lg-6 col-md-12 mt-1">
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">Código cliente</label>
                        <input id="codCliente" placeholder="Código cliente" type="text" class="form-control"
                            aria-describedby="emailHelp" readonly="readonly" value="<?php echo $idCli ?>">
                    </div>
                </div>
            </div>
            <!-- Botono para agregar y para ver garantias -->
            <div class="row">
                <div class="col">
                    <button type="button" id="btnNew" class="btn btn-success"> <i class="fa-solid fa-folder-open"></i>
                        Nuevo registro</button>
                </div>
            </div>
        </form>

        <form id="miForm2" action="">
            <div class="container" id="contenedor0" style="display: none;">
                <!-- FIN DE LA FILA -->
                <div class="row">
                    <div class="col-lg-6 col-md-12 col-sm-12 mt-1">
                        <label for="exampleInputEmail1" class="form-label">Tipo de garantía</label>
                        <select id="selecTipoGa" class="form-select" aria-label="Default select example"
                            onchange="opr_tipoGarantia()">
                            <!-- Info de los selects -->
                            <option value="0">Seleccionar una garantía</option>
                            <?php
                                    $tipoGa = mysqli_query($general, "SELECT id_TiposGarantia AS id, TiposGarantia AS garantia FROM clhpzzvb_bd_general_coopera.tb_tiposgarantia");
                                    while ($garantia = mysqli_fetch_array($tipoGa, MYSQLI_ASSOC)) {
                                        $id = $garantia["id"];
                                        $garantia = utf8_encode($garantia["garantia"]);
                                        ($id == $idGara) ? $selected = " selected" : $selected = "";
                                        echo '<option value="' . $id . '" ' . $selected . '>' . $garantia . '</option>';
                                    }
                                    ?>

                            <!-- Fin de los selects -->
                        </select>
                    </div>
                    <div class="col-lg-6 col-md-12 col-sm-12 mt-1">
                        <label for="exampleInputEmail1" class="form-label">Tipo de Documento</label>
                        <select id="selecTipoDoc" class="form-select" aria-label="Default select example"
                            onchange="opr_tipoDoc()">
                            <!-- Info de los selects -->
                            <option value="0">Seleccionar un documento</option>
                            <?php
                                    $tipoGa = mysqli_query($general, "SELECT idDoc AS id, NombreDoc AS doc FROM clhpzzvb_bd_general_coopera.tb_tiposdocumentosR");
                                    while ($garantia = mysqli_fetch_array($tipoGa, MYSQLI_ASSOC)) {
                                        $id = $garantia["id"];
                                        $doc = utf8_encode($garantia["doc"]);

                                        ($id == $idDoc) ? $selected = " selected" : $selected = "";
                                        echo '<option value="' . $id . '" ' . $selected . '>' . $doc . '</option>';
                                    }
                                    ?>
                            <!-- Fin de los selects -->
                        </select>
                    </div>
                    <!-- <div class="col-lg-4 col-md-12 col-sm-12 mt-1">
                        <label for="exampleInputEmail1" class="form-label">Documento digital</label>
                        <div class="input-group mb-3">
                            <label class="input-group-text" for="inputGroupFile01"></label>
                            <input type="file" class="form-control" id="arvhivo">
                        </div>

                    </div> -->
                </div>

                <!-- INI FILA -->
                <div class="col-lg-12 col-md-12 mt-2" id="busFiador">
                    <label for="exampleInputEmail1" class="form-label">Seleccionar un fiador</label>
                    <div class="input-group mb-3">
                        <button id="btnFiador" class="btn btn-success" type="button" data-bs-toggle="modal"
                            data-bs-target="#modalFiador">Buscar</button>
                        <input id="fiador1" type="text" class="form-control" placeholder="Fiador"
                            aria-label="Example text with button addon" aria-describedby="button-addon1"
                            readonly="readonly" value="<?php echo $fiador ?>">
                    </div>
                </div>

                <!-- INI FILA *****************************************************************************************************************************-->
                <div class="col-lg-12 col-md-12 mt-2" id="bus_aho_plz">
                    <label for="exampleInputEmail1" class="form-label">Cuenta de ahorro de plazo fijo</label>
                    <div class="input-group mb-3">
                        <button id="btn_aho_plz" class="btn btn-success" type="button"
                            onclick="abrir_modal_cualquiera('#aho_plz_fijo')">Buscar</button>
                        <input id="input_aho_plz" type="text" class="form-control" placeholder="Cuenta de ahorro"
                            aria-label="Example text with button addon" aria-describedby="button-addon1"
                            readonly="readonly" value="<?php echo $tpCuenta ?>">
                    </div>
                </div>
                <!-- INI FILA MODAL para las cuentas de ahorro a plazo fijo -->
                <div class="container" id='modal_aho_plz'>

                </div>

                <!-- FIN DE LA FILA -->
                <div class="row">
                    <div class="col-lg-12 mt-1">
                        <div class="form-floating">
                            <textarea id="descrip" class="form-control"
                                placeholder="Leave a comment here"><?php echo $des ?></textarea>
                            <label id="idDescip" for="floatingTextarea">Descripción de la garantia</label>
                        </div>
                    </div>
                </div>
                <!-- FIN DE LA FILA -->

                <!-- Contenedor para las garantias diferentes a fiador ******************************************************************-->

                <div class="" id="conteInt">
                    <div class="card crdbody mt-2" id="archivo">
                        <div class="card-header panelcolor">Cargar archivo </div>
                        <div class="card-body">
                            <div class="row crdbody">
                                <div class="form-group col-md-4">
                                    <div class="input-group">
                                        <input type="file" class="form-control" name="foto" id="foto"
                                            onchange="readImage(this)">
                                        <!-- VALIDAR LA EXTENCION, MOSTRAR UN MENSAJE DE CARGADA  -->
                                    </div>
                                    <img width="130" height="135" id="vistaPrevia" src="<?php echo $src; ?>" /><br />
                                </div>
                                <div class="progress" id="progressdiv">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                        role="progressbar" aria-label="Animated striped example" aria-valuenow="75"
                                        aria-valuemin="0" aria-valuemax="100" style="width: 0%" id="barprogress"></div>
                                </div>
                                <div class="progress" id="cancelprogress" style="display: none;">
                                    <div class="progress-bar progress-bar-striped bg-danger" role="progressbar"
                                        aria-label="Danger striped example" style="width: 100%" aria-valuenow="100"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="form-group col-md-2">
                                    <button class="btn btn-outline-success" onclick="saveimage()" type="button">Guardar
                                        foto</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- INI nueva fila para imagenes y fotos -->


                    <!-- INI FILA -->
                    <div class="row  mt-2">
                        <div class="col-lg-4 col-md-12 mt-1">
                            <label for="exampleInputEmail1" class="form-label">Departamento</label>
                            <select id="selectDepa" class="form-select" onchange="municipio('#selectMuni', this.value)">
                                <!-- INI INFO DEL SELECT -->
                                <?php
                                        // mysqli_set_charset($general, "utf8mb4");
                                        $departa = mysqli_query($general, "SELECT * FROM `departamentos`");
                                        while ($municipalidad = mysqli_fetch_array($departa, MYSQLI_ASSOC)) {
                                            $nombre = ($municipalidad["nombre"]);
                                            $codigo_departa = $municipalidad["codigo_departamento"];

                                            ($codigo_departa == $idDep) ? $selected = "selected" : $selected = "";
                                            echo '<option value="' . $codigo_departa . '" ' . $selected . '>' . $nombre . '</option>';
                                            //echo '<option value="' . $codigo_departa . '">' . $nombre . '</option>';
                                        }
                                        ?>
                            </select>
                        </div>

                        <div class="col-lg-4 col-md-12 mt-1">
                            <label for="exampleInputEmail1" class="form-label">Municipio</label>
                            <select id="selectMuni" class="form-select" aria-label="Default select example">
                                <!-- INFO DEL SELECT -->
                                <?php
                                        //mysqli_set_charset($general, 'utf8');
                                        if ($idDep != "") {
                                            $departa = mysqli_query($general, "SELECT * FROM `municipios` WHERE codigo_departamento1 = $idDep");
                                            while ($municipalidad = mysqli_fetch_array($departa, MYSQLI_ASSOC)) {
                                                $nombre = ($municipalidad["nombre"]);
                                                $codigo_municipio = $municipalidad["codigo_municipio"];
                                                ($codigo_municipio == $idMun) ? $selected = " selected" : $selected = "";
                                                echo '<option value="' . $codigo_municipio . '" ' . $selected . '>' . $nombre . '</option>';
                                            }
                                        }
                                        ?>
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-12 mt-1" id="colDireccion">
                            <div class="mb-3">
                                <label for="exampleInputEmail1" class="form-label">Dirección</label>
                                <input id="direccion" type="text" class="form-control" aria-describedby="emailHelp"
                                    value="<?php echo ($direc != null) ? $direc : '' ?>">
                            </div>
                        </div>
                    </div>
                    <!-- FIN DE LA FILA -->
                    <div class="row">
                        <div class="col-lg-4 col-md-12 mt-1" id="colComer">
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Valor comercial</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">Q</span>
                                    <input id="valorComer" type="number" class="form-control"
                                        aria-describedby="basic-addon1" min="1" step="0.01"
                                        value="<?php echo $valComer ?>" onkeyup="validaNegativo(['#valorComer'])">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-12 mt-1" id="colAvaluo">
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Monto Avalúo</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">Q</span>
                                    <input id="montoAvaluo" type="number" class="form-control"
                                        aria-describedby="basic-addon1" min="1" step="0.01"
                                        value="<?php echo $monAva ?>" onkeyup="validaNegativo(['#montoAvaluo'])">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-12 mt-1" id="colGra">
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Monto Gravamen</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">Q</span>
                                    <input id="monntoGra" type="number" class="form-control"
                                        aria-describedby="basic-addon1" min="1" step="0.01"
                                        value="<?php echo $monGra ?>" onkeyup="validaNegativo(['#monntoGra'])">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FIN DE LA FILA -->
                <!-- BOTONES guardar, actualizar, limpiar -->
                <div class="row">
                    <div class="col">
                        <button type="button" id="btnGua" class="btn btn-success">Guardar <i
                                class="fa-solid fa-plus"></i></button>
                        <button type="button" id="btnAct" class="btn btn-primary">Actualizar <i
                                class="fa-solid fa-pen-to-square"></i></button>
                        <button type="button" id="btnLim" class="btn btn-danger">limpiar <i
                                class="fa-solid fa-mug-hot"></i></button>

                        <script>
                        function validaNegativo(nameEle) {
                            totalEle = nameEle.length

                            for (var con = 0; con < totalEle; con++) {
                                var dato = $(nameEle[con]).val();
                                if (dato < 0) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: '¡ERROR!',
                                        text: 'No se permiten números negativos '
                                    })
                                    return 0;
                                }
                            }

                        }
                        //CONTROL PARA EL BOTON DE NEW 
                        $('#btnNew').click(function() {
                            var codCli = $('#codCliente').val();
                            setVar();
                            if (codCli == '') {
                                Swal.fire({
                                    icon: 'error',
                                    title: '¡ERROR!',
                                    text: 'Primero tiene que seleccionar un cliente'
                                });
                                return;
                            }
                            $("#contenedor0").show();
                            $("#archivo").hide();
                            $("#btnNew").hide();
                            $("#btnAct").hide();
                            $("#btnGua").show();
                        });

                        //CONTROL PARA EL BOTON GUARDAR 
                        $('#btnGua').click(function() {

                            if (validaNegativo(['#valorComer', '#montoAvaluo', '#monntoGra']) == 0) return;
                            var codCli = $('#codCliente').val();
                            if (validaCap() == false) return;
                            if (validarPrecios() == false) return;

                            let tipoGa = document.getElementById("selecTipoGa").value;
                            let tipoDoc = document.getElementById("selecTipoDoc").value;
                            if (tipoGa == 1 && tipoDoc == 1) {
                                obtiene(['codCliente', 'descrip', 'idGarantia'], ['selecTipoGa',
                                    'selecTipoDoc'], [], 'insertarGarantia', ['0'], ['<?= $codusu ?>'])
                            } else if (tipoGa == 3 && tipoDoc == 8) {
                                obtiene(['codCliente', 'descrip', 'monntoGra'], ['selecTipoGa', 'selecTipoDoc',
                                    'selectDepa', 'selectMuni'
                                ], [], 'insertarGarantia', ['0'], ['<?= $codusu ?>'])

                            } else {
                                obtiene(['codCliente', 'descrip', 'direccion', 'valorComer', 'montoAvaluo',
                                        'monntoGra', 'idGarantia'
                                    ], ['selecTipoGa', 'selecTipoDoc', 'selectDepa', 'selectMuni'], [],
                                    'insertarGarantia', ['0'], ['<?= $codusu ?>'])
                            }

                            document.getElementById("miForm2").reset();
                            tbGarantias(codCli);
                            $("#contenedor0").hide();
                            $("#btnNew").show();
                            $("#monntoGra").val('');
                        });

                        //CONTROL PARA BOTON ACTUALIZAR 
                        $('#btnAct').click(function() {
                            if (validaNegativo(['#valorComer', '#montoAvaluo', '#monntoGra']) == 0) return;

                            var codCli = $('#codCliente').val();
                            if (validaCap() == false) return;
                            if (validarPrecios() == false) return;
                            //Se envia la informacion que se tiene que actualizar
                            obtiene(['idGarantia', 'descrip', 'direccion', 'valorComer', 'montoAvaluo',
                                    'monntoGra', 'codCliente'
                                ], ['selecTipoGa', 'selecTipoDoc', 'selectDepa', 'selectMuni'], [],
                                'actualizaGarantia', ['0'], ['<?php echo $codusu ?>'])
                            $("#contenedor0").hide();
                            $("#btnNew").show();
                            $("#btnAct").hide();
                            $("#archivo").hide();
                            $("#btnBus").show();
                            //***
                            $('#selectDepa').prop('disabled', false);
                            $('#selectMuni').prop('disabled', false);
                            $('#direccion').prop('disabled', false);
                            $('#valorComer').prop('disabled', false);
                            $('#montoAvaluo').prop('disabled', false);
                            $('#monntoGra').prop('disabled', false);
                            // //Escoger muni idHTML, idDepa, idMuni
                            $("#direccion").val('');
                            $("#valorComer").val('');
                            $("#montoAvaluo").val('');
                            $("#monntoGra").val('');
                        });

                        //CONTROL PARA EL BOTON ELIMINAR
                        $('#btnLim').click(function() {
                            municipio("#selectMuni", 1);
                            printdiv2("#cuadro", 0)
                            opr_tipoGarantia();
                            $("#contenedor0").hide();
                            $("#btnNew").show();
                            $("#tablaGa").hide();
                        });

                        $('#btnBus').click(function() {
                            $("#contenedor0").hide();
                            $("#btnNew").show();
                            setVar();
                        });

                        $('#btnEdi').click(function() {
                            alert('Opciones de booton editar')
                            var codCli = $('#codCliente').val();
                            tbGarantias(codCli);
                        });

                        function setVar() {
                            //Reinicia variables 
                            $("#idGarantia").val(0);
                            $("#selecTipoGa").val(0);
                            $("#selecTipoDoc").val(0);
                            opr_tipoGarantia();
                            $("#descrip").val('');
                            $("#selectDepa").val(1);
                            municipio('#selectMuni', 1);
                        }

                        $('#btnFiador').click(function() {
                            // console.log("Inicio su funcion")
                            var idCli = $('#codCliente').val();
                            cargarDatos(idCli);
                        })
                        </script>
                    </div>
                </div>
            </div>
        </form>

        <!-- INI TABLA DE GARANTIAS -->
        <div class="container mt-3" id="tablaGa" style="max-width: 100% !important;">
            <h2>Garantias </h2>
            <div class="row">
                <div class="table-responsive">
                    <table class="table nowrap" style="max-width:100%; font-size: 0.8rem !important;" id="tbGarantias">
                        <thead class="table-dark">
                            <tr>
                                <th>No.</th>
                                <th>Tipo de garantia</th>
                                <th>Tipo de Documento</th>
                                <th>Descripción</th>

                                <th>Departamento</th>
                                <th>Municipio</th>
                                <th>Dirección</th>

                                <th>Valor comercial</th>
                                <th>Monto Avalúo</th>
                                <th>Monto Gravamen</th>
                                <th>Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- FIN TABLA DE GARANTIAS -->
    </div>
</div>
</div>
</div>

<!-- AREA DE SCRIPTS -->
<script>
$(document).ready(function() {
    var dato = $('#idControl').val();
    switch (dato) {
        case '0':
            $("#busFiador").hide();
            $("#contenedor0").hide();
            $("#btnAct").hide();
            $("#tablaGa").hide();
            $("#archivo").hide();
            municipio('#selectMuni', 1);
            break;
        case '1': //opcion para eliminar
            $("#btnGua").hide();
            $("#btnNew").hide();
            var tipoGarantia = document.getElementById("selecTipoGa").value;
            var tipoDoc = document.getElementById("selecTipoDoc").value;

            if (tipoGarantia != 1 && tipoDoc != 1 && tipoDoc != 8) {
                $("#contenedor0").show();
                $("#busFiador").hide();
                $("#bus_aho_plz").hide();
                $('#selectDepa').prop('disabled', false);
                $('#selectMuni').prop('disabled', false);
                $('#direccion').prop('disabled', false);
                $('#valorComer').prop('disabled', false);
                $('#montoAvaluo').prop('disabled', false);
                $('#monntoGra').prop('disabled', false);
            }
            if (tipoGarantia == 1 && tipoDoc == 1) {
                $("#contenedor0").show();
                $("#busFiador").show();
                $("#bus_aho_plz").hide();
                $('#selectDepa').prop('disabled', true);
                $('#selectMuni').prop('disabled', true);
                $('#direccion').prop('disabled', true);
                $('#valorComer').prop('disabled', true);
                $('#montoAvaluo').prop('disabled', true);
                $('#monntoGra').prop('disabled', true);
            }
            if (tipoGarantia == 3 && tipoDoc == 8) {
                $("#colDireccion").hide();
                $("#colComer").hide();
                $("#colAvaluo").hide();
                $("#busFiador").hide();
                $('#monntoGra').prop('disabled', true);
                $("#idDescip").text(
                    "Codígo de cuenta de ahorro de plazo fijo"
                );
                $("#descrip").attr("readonly", true);

                $("#contenedor0").show();

                var codCli = $("#codCliente").val();
                inyecCod('#modal_aho_plz', 'modal_aho_plz', codCli, 'cre_indi/inyecCod/inyecCod.php');
                $("#bus_aho_plz").show();
            }

            break;
        case '2':
            $("#btnNew").show();
            $("#contenedor0").hide();
            var tbGa = $("#codCliente").val();
            tbGarantias(tbGa);
            break;
        default:
            $('#selectDepa').prop('disabled', false);
            $('#selectMuni').prop('disabled', false);
            $('#direccion').prop('disabled', false);
            $('#valorComer').prop('disabled', false);
            $('#montoAvaluo').prop('disabled', false);
            $('#monntoGra').prop('disabled', false);
            break;
    }
});

$('#btnFiador').click(function() {
    var idCli = $('#codCliente').val();
    cargarDatos(idCli);
});

function validaCap() {
    var dato = $('#codCliente').val();
    var des = $('#descrip').val();

    var direc = $('#direccion').val();

    var mo1 = $('#valorComer').val();
    var mo2 = $('#montoAvaluo').val();
    var mo3 = $('#monntoGra').val();

    var tipo = $('#input_aho_plz').val();

    var tipoGarantia = document.getElementById("selecTipoGa").value;
    var tipoDoc = document.getElementById("selecTipoDoc").value;

    var ban = true;

    if (dato == '') {
        ban = false
        Swal.fire({
            icon: 'error',
            title: '¡ERROR!',
            text: 'Seleccione un cliente'
        })
        loaderefect(0)
        return ban;
    }

    if (tipoGarantia == 0) {
        ban = false
        Swal.fire({
            icon: 'error',
            title: '¡ERROR!',
            text: 'Seleccionar un tipo de garantía'
        })
        loaderefect(0)
        return ban;
    }
    if (tipoDoc == 0) {
        ban = false
        Swal.fire({
            icon: 'error',
            title: '¡ERROR!',
            text: 'Seleccionar un tipo de documento'
        })
        loaderefect(0)
        return ban;
    }

    if (tipoGarantia == 3 && tipoDoc == 8 && des == '' && tipo == '') {
        ban = false
        Swal.fire({
            icon: 'error',
            title: '¡ERROR!',
            text: 'Selecione una cuenta'
        })
        loaderefect(0)
        return ban;
    }

    if ((mo1 == '' || mo2 == '' || mo3 == '' || des == '' || direc == '') && tipoGarantia != 1 && tipoDoc != 8) {
        ban = false
        Swal.fire({
            icon: 'error',
            title: '¡ERROR!',
            text: 'Llenar todos los campos'
        })
        loaderefect(0)
        return ban;
    }


    if ((mo1 == 0 || mo2 == 0 || mo3 == 0 || des == 0 || direc == '') && tipoGarantia != 1 && tipoDoc != 8) {
        ban = false
        Swal.fire({
            icon: 'error',
            title: '¡ERROR!',
            text: 'El valor comercial, Monto de Evaluó y Monto gravamen, no pueden estar con valor 0'
        })
        loaderefect(0)
        return ban;
    }
}

function validarPrecios() {
    var mo1 = parseFloat($('#valorComer').val());
    var mo2 = parseFloat($('#montoAvaluo').val());
    var mo3 = parseFloat($('#monntoGra').val());
    var tipoDoc = document.getElementById("selecTipoDoc").value;

    var ban = true;
    if (mo2 > mo1 && tipoDoc != 8) {
        ban = false
        Swal.fire({
            icon: 'error',
            title: '¡ERROR!',
            text: 'El monto de evaluó no puede ser mayor que el monto comercial'
        })
        loaderefect(0)
        return ban;
    }

    if (mo2 < mo3 && tipoDoc != 8) {
        ban = false
        Swal.fire({
            icon: 'error',
            title: '¡ERROR!',
            text: 'El monto de gravamen no puede ser mayor que el monto de evaluó'
        })
        loaderefect(0)
        return ban;
    }
    return ban;
}
</script>

<?php
        break;

    case 'plan_de_pagos':
        $rst = 0;
        $codusu = $_SESSION['id'];
        $slq = mysqli_query($conexion, "SELECT EXISTS(SELECT a.estado FROM tb_autorizacion a
        INNER JOIN clhpzzvb_bd_general_coopera.tb_rol r ON r.id = a.id_rol
        INNER JOIN clhpzzvb_bd_general_coopera.tb_restringido rs ON rs.id = a.id_restringido 
        WHERE r.siglas = 'ADM' AND a.id_restringido = 1 AND a.estado = 1) AS rst");

        $rst = $slq->fetch_assoc()['rst'];
    ?>
<div class="card">
    <h5 class="card-header">Editar Plan de pago</h5>
    <div class="card-body">
        <!-- Formulario para el nombre del cliente y codigo de cuenta -->
        <form id="form" action="">
            <input type="text" value="<?= $rst ?>" id="control" hidden>
            <!-- INICIO DE LA FILA -->
            <div class="row">
                <div class="col-lg-6 col-md-12 mt-2">
                    <label for="text" class="form-label">Cliente</label>
                    <div class="input-group mb-3">
                        <button class="btn btn-warning" type="button" data-bs-toggle="modal"
                            data-bs-target="#cuentaYcli">Buscar <i class="fa-solid fa-magnifying-glass"></i></button>
                        <input id="usuCli" type="text" class="form-control" placeholder="Cliente"
                            aria-label="Example text with button addon" aria-describedby="button-addon1" readonly>
                    </div>
                </div>

                <div class="col-lg-6 col-md-12 mt-2">
                    <label for="text" class="form-label">Código de cuenta</label>
                    <input id="codCu" type="text" class="form-control" placeholder="Código de cuenta"
                        aria-label="Example text with button addon" aria-describedby="button-addon1" readonly>
                </div>
            </div>
            <!-- FIN DE LA FILA -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="row mb-2">
                        <div class="col-12">
                            <button id="btnAct" type="button" class="btn btn-primary mt-1"
                                onclick="capDataTb()">Actualizar <i class="fa-solid fa-pen-to-square"></i></button>
                            <button id="newRow" type="button" class="btn btn-success mt-1">Agregar fila <i
                                    class="fa-solid fa-diagram-next"></i></button>
                            <button id="killRow" type="button" class="btn btn-danger mt-1">Eliminar fila <i
                                    class="fa-solid fa-diagram-next fa-rotate-180"></i></button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button id="gPDF" type="button" class="btn btn-outline-danger"
                                onclick="if(validaCliCod()==0)return; reportes([['usuCli','codCu'],[],[],['<?php echo $codusu ?>']],'pdf','editPlanPagos',0)">Plan
                                de pagos <i class="fa-solid fa-file-pdf"></i></button>
                            <button id="ppgresumen" type="button" class="btn btn-outline-danger"
                                onclick="if(validaCliCod()==0)return; reportes([['usuCli','codCu'],[],[],['<?php echo $codusu ?>']],'pdf','planpagoresumen',0)">Plan
                                de pagos Resumen <i class="fa-solid fa-file-pdf"></i></button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h4>
                        Monto. Q <label for="text" id="desembolso1"> - - -</label>
                    </h4>
                </div>
            </div>
        </form>

        <!-- INICIA LA TABLA -->
        <div class="row mt-2">
            <!--  -->
            <h2>Editar plan de pagos </h2>
            <table class="table" id="tbPlanPagos">
                <thead class="table-dark">
                    <tr>
                        <th>No.</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Pago</th>
                        <th>Capital</th>
                        <th>Interes</th>
                        <th>Otros pagos</th>
                        <th>Saldo Capital</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody id="dataPlanPago">
                    <!-- INI de la información -->
            </table>
            <script>
            function conFila(nametb) {
                var tabla = document.getElementById(nametb);
                var filas = tabla.getElementsByTagName('tr');
                var noFila = filas.length;
                return noFila;
            }

            function validaCliCod() {
                let susCli = $('#usuCli').val();
                let codCu = $('#codCu').val();
                if (susCli == '' || codCu == '') {
                    Swal.fire({
                        icon: 'error',
                        title: '¡ERROR!',
                        text: 'Tiene que seleccionar un cliente, gracias :)'
                    });
                    return 0
                }
            }

            function hoy() {
                //Fecha 
                var hoy = new Date();
                var anio = hoy.getFullYear();
                var mes = hoy.getMonth() + 1; // Los meses comienzan desde 0, por lo que sumamos 1
                var dia = hoy.getDate();
                var fechaFormateada = anio + '-' + (mes < 10 ? '0' + mes : mes) + '-' + (dia < 10 ? '0' + dia : dia);
                return fechaFormateada;
            }

            function validaF() {
                noFila = conFila('tbPlanPagos');
                //Obneter fecha actual del dia
                var hoyF = new Date(hoy());
                //Se el asigna el valor al objeto fecha
                var fAnt = new Date(hoyF);
                var fAct = new Date($('#1fechaP').val());

                for (let i = 1; i <= (noFila - 1); i++) {
                    if (i >= 2) {
                        fAct = new Date($('#' + i + 'fechaP').val());
                        //console.log(fAnt + ' > ' + fAct);
                    }

                    if ((fAnt >= fAct) && (i < noFila) && i > 1) {
                        $('#' + i + 'fechaP').addClass('is-invalid');

                        if (i == 1) {
                            Swal.fire({
                                icon: 'error',
                                title: '¡ERROR!',
                                text: 'La fecha tiene que ser mayor a la fecha actual'
                            });
                            return 0;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: '¡ERROR!',
                            text: 'La fecha tiene que ser mayor a la anterior'
                        });
                        return 0;
                    }

                    $('#' + i + 'fechaP').removeClass('is-invalid');

                    fAnt = fAct
                }
            }

            function validarTabla() {

                noFila = conFila('tbPlanPagos');

                for (let i = 1; i <= (noFila - 1); i++) {

                    if (validaF() == 0) return 0;

                    cap = parseFloat($('#' + i + 'cap').val());
                    inte = parseFloat($('#' + i + 'inte').val());
                    otros = parseFloat($('#' + i + 'otros').val());
                    salCap = parseFloat($('#' + i + 'saldoCap').text());

                    if ($('#' + i + 'cap').val() == '' || $('#' + i + 'inte').val() == '' || $('#' + i + 'otros')
                    .val() == '') {
                        Swal.fire({
                            icon: 'error',
                            title: '¡ERROR!',
                            text: 'No se permiten campos vacíos '
                        })
                        return 0;
                    }

                    if (cap < 0 && inte < 0 && otros < 0) {
                        Swal.fire({
                            icon: 'error',
                            title: '¡ERROR!',
                            text: 'No se permiten números negativos '
                        })
                        return 0;
                    }

                    if (cap == 0 && i == (noFila - 1)) {
                        Swal.fire({
                            icon: 'error',
                            title: '¡ERROR!',
                            text: 'El capital de la ultima fila no puede quedar en 0 '
                        })
                        return 0;
                    }

                }
            }

            function calPlanDePago() {
                //$('#1salCap').val('Hola'); // Para enviar datos a los inputs de la tabla
                //Contador de fila
                noFila = conFila('tbPlanPagos');
                //var desembolso = parseInt($('#idDes1').text());
                var estado = false;
                if (!estado) {
                    var desembolso = parseFloat($('#idDes1').text());
                    //console.log(typeof(desembolso) + ' - ' + desembolso);
                    estado = true;
                }

                for (let i = 1; i <= (noFila - 1); i++) {
                    //console.log(typeof(cap)+' - '+cap);
                    cap = parseFloat($('#' + i + 'cap').val());
                    //console.log(cap);
                    inte = parseFloat($('#' + i + 'inte').val());
                    //console.log(inte);
                    otros = parseFloat($('#' + i + 'otros').val());
                    //console.log(otros);

                    if (cap < 0 || inte < 0 || otros < 0) {
                        Swal.fire({
                            icon: 'error',
                            title: '¡ERROR!',
                            text: 'No se permite números negativos'
                        })
                        return
                    }

                    desembolso = (desembolso - $('#' + i + 'cap').val()).toFixed(2);
                    $('#' + i + 'salCap').text(desembolso);

                    total = (parseFloat(cap + inte + otros)).toFixed(2);
                    $('#' + i + 'total').text(total);
                }
            }

            function gMatriz(vacMaster) {
                // Obtener la cantidad de filas
                var filas = 0;
                for (var i = 0; i < vacMaster.length; i++) {
                    var longitudVector = vacMaster[i].length;
                    filas = Math.max(filas, longitudVector);
                }
                // Crear la matriz
                var matriz = [];
                // Generar la matriz automáticamente
                for (var i = 0; i < filas; i++) {
                    var fila = [];
                    for (var j = 0; j < vacMaster.length; j++) {
                        fila.push(vacMaster[j][i] || null);
                    }
                    matriz.push(fila);
                }
                //console.log(matriz);
                return matriz;
            }

            $('#btnAct').click(function() {
                if (validaCliCod() == 0) return;
                if (validarTabla() == 0) return;

                noFila = (conFila('tbPlanPagos')) - 1;
                dato = $("#" + noFila + "salCap").text();
                //console.log('Saldo Cap ' + dato);
                if (dato != 0 || dato < 0) {
                    Swal.fire({
                        icon: 'error',
                        title: '¡ERROR!',
                        text: 'El saldo capital tiene que terminar en 0'
                    })
                    return;
                }

                vecMaster = [];
                vecMaster.push(capDataTb('idPP', 'td'));
                vecMaster.push(capDataTb('fecha', 'input'));
                vecMaster.push(capDataTb('noCuo', 'td'));
                vecMaster.push(capDataTb('capita', 'input'));
                vecMaster.push(capDataTb('interes', 'input'));
                vecMaster.push(capDataTb('otrosP', 'input'));
                vecMaster.push(capDataTb('saldoCap', 'td'));

                let matriz = gMatriz(vecMaster);
                let codCu = $('#codCu').val();
                actMasiva(matriz, 'actMasPlanPagos', codCu);

            });


            // Codigo para agregar una fila en la tabla
            $('#newRow').click(function() {
                if (validaCliCod() == 0) return;
                // Sample Data
                var tabla = document.getElementById('tbPlanPagos');
                var filas = tabla.getElementsByTagName('tr');
                var noFila = filas.length;

                var tr = $("<tr>")
                tr.append(`<td id="${noFila+'idData'}" name="idPP[]" hidden>0</td>`) // Identificador new
                tr.append(`<td id="${noFila+'idCon'}">${noFila}</td>`) // No de fila
                tr.append(
                    `<td><input id="${noFila+'fechaP'}" type="date" name="fecha[]" class="form-control" onblur="validaF()"></td>`
                    ) //fecha
                tr.append('<td><i class="fa-solid fa-money-bill" style="color: #c01111;"></i></td>') //Estado 
                tr.append(`<td name="noCuo[]">${noFila}</td>`) // No de Pago 
                tr.append(
                    `<td><input min="0" step="0.01" id="${noFila+'cap'}" name="capita[]" onkeyup="calPlanDePago()" type="number" class="form-control"  value="0" min="0"></td>`
                    ) //Capital
                tr.append(
                    `<td><input min="0" step="0.01" id="${noFila+'inte'}" name="interes[]" onkeyup="calPlanDePago()" type="number" class="form-control"  value="0" min="0" ` +
                    ((($('#control').val()) == 1) ? "" : "disabled") + `> </td>`) //Interes
                tr.append(
                    `<td><input min="0" step="0.01" id="${noFila+'otros'}" name="otrosP[]" onkeyup="calPlanDePago()" type="number" class="form-control"  value="0" min="0"></td>`
                    ) //Otros pagos
                tr.append(`<td id="${noFila+'salCap'}" name="saldoCap[]"></td>`) // Saldo Capital 
                tr.append(`<td id="${noFila+'total'}"></td>`) // Total
                $('#tbPlanPagos tbody').append(tr)

                $('#' + noFila + 'fechaP').val(hoy());
                calPlanDePago();

                // Remove Selected Table Row(s)
                $('#killRow').click(function() {
                    if (validaCliCod() == 0) return;
                    var tabla = document.getElementById('tbPlanPagos');
                    var filas = tabla.getElementsByTagName('tr');
                    var noFila = filas.length - 1;

                    fila = parseInt($('#' + noFila + 'idCon').text());
                    filaData = parseInt($('#' + noFila + 'idData').text());

                    if (fila == 1) {
                        Swal.fire({
                            icon: "error",
                            title: "¡ERROR!",
                            text: "Ya no se puede eliminar más filas"
                        });
                        return;
                    }

                    if (filaData != 0) {
                        Swal.fire({
                            icon: "error",
                            title: "¡ERROR!",
                            text: "Los datos de la fila serán eliminados en la base de datos"
                        });
                        eliminarFila(filaData, 'deleteFilaPlanDePagos')

                    } else {
                        tabla.deleteRow(noFila);
                        calPlanDePago();
                    }

                })
            })
            </script>
        </div>



    </div>

</div>
</div>

<?php
        break;

    case 'cambiar_estado_cred':
    ?>
<div class="card">
    <h5 class="card-header">Cambiar estado de Credito de Analisis a Solicitud </h5>
    <div class="card-body">
        <!-- Formulario para el nombre del cliente y codigo de cuenta -->
        <form id="form" action="">
            <!-- INICIO DE LA FILA -->
            <div class="row">
            </div>
            <!-- FIN DE LA FILA -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <button type="button"
                                        class="btn btn-outline-primary pt-3 pb-3 mb-2 mt-2 col-12 col-sm-12"
                                        onclick="abrir_modal('#modal_analisis_01', '#id_modal_hidden', 'id')"><i
                                            class="fa-solid fa-magnifying-glass-plus me-2"></i>Buscar Credito </button>
                                </div>
                            </div>
                            <?php
                                    include_once "../../../src/cris_modales/mdls_cambiar_estado_analisis_01.php";
                                    ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">

                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">

        <h5 class="card-header">Cambiar estado de Credito de Aprobacion a Solicitud </h5>
        <div class="card-body">
            <!-- Formulario para el nombre del cliente y codigo de cuenta -->
            <form id="form" action="">
                <!-- INICIO DE LA FILA -->
                <div class="row">
                </div>
                <!-- FIN DE LA FILA -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="row mb-2">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-12 col-sm-6">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <button type="button"
                                            class="btn btn-outline-warning pt-3 pb-3 mb-2 mt-2 col-12 col-sm-12"
                                            onclick="abrir_modal('#modal_aprobacion_01', '#id_modal_hidden', 'id')"><i
                                                class="fa-solid fa-magnifying-glass-plus me-2"></i>Buscar
                                            Credito</button>
                                    </div>
                                </div>
                                <?php
                                        include_once "../../../src/cris_modales/mdls_cambiar_estado_aprobacion_02.php";
                                        ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="card">
    <div class="card-body">

        <h5 class="card-header">Cambiar estado de Credito de Desembolso a Solicitud </h5>
        <div class="card-body">
            <!-- Formulario para el nombre del cliente y codigo de cuenta -->
            <form id="form" action="">
                <!-- INICIO DE LA FILA -->
                <div class="row">
                </div>
                <!-- FIN DE LA FILA -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="row mb-2">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-12 col-sm-6">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <button type="button"
                                            class="btn btn-outline-danger pt-3 pb-3 mb-2 mt-2 col-12 col-sm-12"
                                            onclick="abrir_modal('#modal_creditos_a_desembolsar', '#id_modal_hidden', 'id')"><i
                                                class="fa-solid fa-magnifying-glass-plus me-2"></i>Buscar
                                            Credito</button>
                                    </div>
                                </div>
                                <?php
                                        include_once "../../../src/cris_modales/mdls_cambiar_estado_desembolso_02.php";
                                        ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
        break;

    case 'delete_desembolso':

        $extra = $_POST["xtra"];
        $codusu = $_SESSION['id'];
        $id_agencia = $_SESSION['id_agencia'];
        $datos[] = [];
        $bandera = "CODIGO DE CUENTA INEXISTENTE";
        if ($extra != 0) {
            $consulta = mysqli_query($conexion, "SELECT cl.short_name AS nombrecli, cl.idcod_cliente AS codcli, cm.CCODCTA AS ccodcta, cm.MonSug AS monsug, cm.NIntApro AS interes, cm.DFecDsbls AS fecdesembolso,
                ((cm.MonSug)-(SELECT IFNULL(SUM(ck.KP),0) FROM CREDKAR ck WHERE ck.CESTADO!='X' AND ck.CTIPPAG='P' AND ck.CCODCTA='$extra')) AS saldocap
                FROM cremcre_meta cm
                INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
                WHERE cm.CCODCTA='$extra'");
            $i = 0;
            while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                $datos[$i] = $fila;
                $i++;
                $bandera = "";
            }
        }
    ?>
<input type="text" readonly hidden value='statusaccount' id='condi'>
<input type="text" hidden value="cre_indi_01" id="file">
<div class="card crdbody contenedort">
    <div class="card-header" style="text-align:left">
        <h4>Eliminacion de Creditos Desembolsados</h4>
    </div>
    <div class="card-body">
        <div class="row contenedort">
            <h5>Buscar cliente a Eliminar/Cambiar de estado</h5>
            <div class="row mb-3">
                <div class="col-sm-5">
                    <br>
                    <button type="button" class="btn btn-primary col-sm-12"
                        onclick="abrir_modal_for_delete('#modal_estado_cuenta_for_delete', '#id_modal_hidden', 'name/A/'+'/#/#/#/#')">
                        <i class="fa-solid fa-magnifying-glass-plus me-2"></i>Buscar crédito
                    </button>
                </div>
            </div>
            <div class="row mb-3">


            </div>
            <?php if ($bandera != "" && $extra != "0") {
                        echo '<div class="alert alert-danger" role="alert">' . $bandera . '</div>';
                    }
                    ?>
        </div>
    </div>
    <!-- <div class="row contenedort justify-content-center">
                <h5>Buscar cliente </h5>
                <div class="col-sm-5">
                    <br>
                    <button type="button" class="btn btn-primary col-sm-12" onclick="abrir_modal_for_delete('#modal_estado_cuenta_for_delete', '#id_modal_hidden', 'name/A/'+'/#/#/#/#')">
                        <i class="fa-solid fa-magnifying-glass-plus me-2"></i>Buscar crédito
                    </button>
                </div>
            </div> -->
</div>

<div class="row justify-items-md-center">
    <div class="col align-items-center" id="modal_footer">

        <?php
                if ($bandera == "") {
                    echo '<button type="button" class="btn btn-outline-danger" data-ccodcta="' . $fila["ccodcta"] . '" onclick="enviarAprob(this))">
                        <i class="fas fa-trash-alt"></i> Eliminar
                     </button>';
                }
                ?>

        <button type="button" class="btn btn-outline-danger"
            onclick="printdiv('PagGrupAutom', '#cuadro', 'caja_cre', 0)">
            <i class="fa-solid fa-ban"></i> Cancelar
        </button>
        <button type="button" class="btn btn-outline-warning" onclick="salir()">
            <i class="fa-solid fa-circle-xmark"></i> Salir
        </button>
        <!-- <button onclick="reportes([['numdoc', 'nciclo'], [], [], [5]], 'pdf', 'comp_grupal', 0)">asdfas</button> -->
    </div>
</div>
</div>
<?php
        include_once "../../../src/cris_modales/mdls_estadocuenta_for_delete.php";
        break;
        ?>


<?php
        break;
} ?>