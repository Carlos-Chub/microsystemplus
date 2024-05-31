<?php
session_start();
include '../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];
$codusu = $_SESSION['id'];

switch ($condi) {
    case 'gastos': {
            $id = $_POST["xtra"];
            // print_r($id);
?>
            <!-- Crud para agregar, editar y eliminar tipo de gastos  -->
            <input type="text" id="file" value="creditos_01" style="display: none;">
            <input type="text" id="condi" value="gastos" style="display: none;">

            <div class="text" style="text-align:center">Tipos de Gastos</div>

            <div class="card">
                <div class="card-header">GASTOS</div>

                <div class="card-body">

                    <div class="mb-3">
                        <div class="row g-3">

                            <div class="col">
                                <label for="Nombre del Gasto" class="form-label ">Nombre del gasto</label>
                                <input type="text" class="form-control input-validation" id="gasto" placeholder="Nombre del gasto" required>
                                <input type="hidden" id="idRegistro">
                            </div>

                            <div class="col">
                                <label for="Nomenclatura" class="form-label">Nomenclatura</label>
                                <div class="input-group mb-3">

                                    <!-- <button class="btn btn-warning" type="button" id="buscarNomenclatura" onclick="abrir_modal('#modaljalagastos', '#id_modal_hidden', 'nomenclatura/A/'+'/#/#/#/#')">Buscar</button> -->
                                    <button class="btn btn-warning" type="button" id="buscarNomenclatura" onclick="abrir_modal('#modaljalagastos', '#id_modal_hidden', 'idCon,nomenclatura/A,2-3/-/#/#/#/#')">Buscar</button>
                                    <input type="text" disabled class="form-control input-validation" id="nomenclatura" placeholder="Nomenclatura" aria-label="Example text with button addon" aria-describedby="button-addon1">
                                    <input type="hidden" id="idCon">

                                    <!-- Inicia modal -->
                                    <div class="modal fade" id="modaljalagastos" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog  modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Lista de nomenclatura</h1>
                                                    <button type="hidden" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                    <input type="hidden" id="id_modal_hidden" value="" readonly>
                                                </div>
                                                <div class="modal-body">
                                                    <!-- INICIO Tabla de nomenclatura -->
                                                    <div class="container mt-3">
                                                        <h2>Registro de gastos </h2>
                                                        <table class="table" id="tbNomenclatura">
                                                            <thead class="table-dark">
                                                                <tr>
                                                                    <th>ID</th>
                                                                    <th>Nomenclatura</th>
                                                                    <th>Descripción</th>
                                                                    <th>Opciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>

                                                                <!--Inicio de la tb Modal-->
                                                                <?php
                                                                $consulta = mysqli_query($conexion, "SELECT id, ccodcta AS nomenclatura, cdescrip AS descripcion FROM ctb_nomenclatura;");

                                                                while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                                                    $id2 = $row["id"];
                                                                    $nomenclatura = $row["nomenclatura"];
                                                                    $descripcion = $row["descripcion"];
                                                                ?>
                                                                    <!-- seccion de datos -->
                                                                    <tr>
                                                                        <td><?= $id2 ?></td>
                                                                        <td><?= $nomenclatura ?></td>
                                                                        <td><?= $descripcion ?></td>
                                                                        <td>
                                                                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="seleccionar_cuenta_ctb2(`#id_modal_hidden`,['<?php echo $id2; ?>','<?php echo $nomenclatura; ?>','  <?php echo $descripcion; ?> ']); cerrar_modal(`#modaljalagastos`, `hide`, `#id_modal_hidden`);">Seleccionar</button>
                                                                        </td>
                                                                    </tr>
                                                                <?php } ?>
                                                                <!--Fin de la tb Modal-->

                                                            </tbody>
                                                        </table>
                                                        <script>
                                                            $(document).ready(function() {
                                                                $('#tbNomenclatura').on('search.dt')
                                                                    .DataTable({
                                                                        "lengthMenu": [
                                                                            [5, 10, 15, -1],
                                                                            ['5 filas', '10 filas', '15 filas', 'Mostrar todos']
                                                                        ],
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
                                                                            "sProcessing": "Procesando...",

                                                                        },
                                                                    });
                                                            })
                                                        </script>
                                                        <!-- FIN Tabla de nomenclatura -->
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" onclick="cerrar_modal('#modaljalagastos', 'hide', '#id_modal_hidden')">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Fin modal -->
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 col-ms-6">
                                    <div class="form-check form-switch" onclick="selecOp()">
                                        <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">Afecta a una cuenta</label>
                                    </div>
                                </div>

                                <div class="col-md-3 col-ms-6" id="tipCuenta">
                                    <div class="form-check" hidden>
                                        <input class="form-check-input" type="radio" name="opTipC" id="opTipC0" value="0" checked>
                                        <label class="form-check-label" for="opTipC0">
                                            default
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="opTipC" id="opTipC1" value="1">
                                        <label class="form-check-label" for="opTipC1">
                                            Ahorro
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="opTipC" id="opTipC2" value="2">
                                        <label class="form-check-label" for="opTipC2">
                                            Aportaciones
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="opTipC" id="opTipC3" value="3">
                                        <label class="form-check-label" for="opTipC3">
                                            Créditos
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="conBoton">
                                <button type="button" id="btnGua" class="btn btn-success" onclick="if(!validaG([`gasto`,`nomenclatura`],[],[])){return;}; if(!validaTipC()){return;} ;obtiene([`gasto`,`idCon`],[],['opTipC'],`create_gastos`,`0`,['<?php echo $codusu; ?>'])">Guardar</button>
                                <button type="button" id="btnAct" class="btn btn-warning" onclick="if(!validaG([`gasto`,`nomenclatura`],[],[])){return;}; if(!validaTipC()){return;} ;obtiene([`idRegistro`,`idCon`,`gasto`],[],['opTipC'],`ActualizarTipoGasto`,`0`,['<?php echo $codusu; ?>'])">Actualizar</button>
                                <button type="button" id="btnCan" class="btn btn-danger" onclick="limpiarInputs()">Cancelar</button>
                            </div>

                            <div class="container mt-3">
                                <h2>Registro de gastos </h2>
                                <table class="table" id="tbGastos">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Gasto</th>
                                            <th>Nomenclatura</th>
                                            <th>Descripción</th>
                                            <th>Cuenta afectada</th>
                                            <th>Opciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!--Inicio impritme tabla--->
                                        <?php
                                        $consulta = mysqli_query($conexion, "SELECT cp.id, cp.nombre_gasto AS gasto, ct.ccodcta AS nomenclatura, ct.cdescrip AS descripcion, cp.id_nomenclatura AS idNomenclatura, cp.afecta_modulo AS cuenta
                                        FROM cre_tipogastos cp
                                        INNER JOIN ctb_nomenclatura ct ON cp.id_nomenclatura = ct.id WHERE cp.estado = 1 ORDER BY cp.id DESC;");

                                        while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                            $id = $row["id"];
                                            $Gasto = $row["gasto"];
                                            $nomenclatura = $row["nomenclatura"];
                                            $descripcion = $row["descripcion"];
                                            $idNomenclatura = $row["idNomenclatura"];
                                            $cuenta = $row["cuenta"];

                                            $dato = $id . "||" . //0
                                                $Gasto . "||" . //1
                                                $nomenclatura . "||" . //2
                                                $descripcion . "||" . //3
                                                $idNomenclatura . "||" . //4
                                                $cuenta; //5
                                        ?>
                                            <!-- seccion de datos -->
                                            <tr>
                                                <td><?= $id ?></td>
                                                <td><?= $Gasto ?></td>
                                                <td><?= $nomenclatura ?></td>
                                                <td><?= $descripcion ?></td>
                                                <td><?= (($cuenta == 1) ? "Ahorro" : (($cuenta == 2) ? "Aportacion" : (($cuenta == 3) ? "Créditos" : "-"))) ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-primary" onclick="edita('<?php echo $dato ?>')">Editar</button>
                                                    <button type="button" class="btn btn-danger" onclick="eliminar('<?php echo $id ?>',`crud_admincre`,'0',`EliminarGastos`,'<?php echo $codusu ?>')">Eliminar</button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        <!--Fin de la tabla-->
                                    </tbody>
                                </table>
                            </div>
                            <script>
                                $(document).ready(function() {
                                    //RADIO BOOTONS
                                    $("#tipCuenta").hide();

                                    //DATA TABLE
                                    $('#tbGastos').DataTable({
                                        "order": [
                                            [0, 'desc'],
                                            [1, 'desc']
                                        ],
                                        "lengthMenu": [
                                            [5, 10, 15, -1],
                                            ['5 filas', '10 filas', '15 filas', 'Mostrar todos']
                                        ],
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

                                    $('#btnAct').hide();
                                    $('#btnCan').hide();
                                });
                            </script>

                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
        break;

    case 'cre_productos':
        ?>
        <!-- Los input de abajo se encarga de reimpirmir los datos  -->
        <input type="text" id="file" value="creditos_01" style="display: none;">
        <input type="text" id="condi" value="cre_productos" style="display: none;">

        <!-- INICIO DEL SISTEM  **************************************************************************************************************************************************-->
        <div class="card">
            <div class="card-header">
                Control de productos
            </div>
            <div class="card-body flex-grap">
                <!-- INI BODY -->
                <!-- ID ESPECIALES -->

                <input type="text" id="idPro" hidden>

                <!-- FIN DE ID -->
                <!-- INICIO DE ROW1 -->
                <div class="row flex-grap">
                    <div class="col-sm-12">
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control input-validation" id="nomPro" placeholder=".">
                            <label for="floatingInput">Nombre del producto</label>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <select id="selector" class="form-select" aria-label="Disabled select example">
                            <!-- <option selected class="text-primary" disabled>Seleccione un fondo </option> -->

                            <?php
                            $consulta = mysqli_query($conexion, "SELECT id, descripcion FROM ctb_fuente_fondos WHERE estado=1");
                            $con = 0;
                            while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                $id2 = $row["id"];
                                $descripcion = $row["descripcion"];
                                $dato = $id2 . "||" .
                                    $descripcion;
                            ?>
                                <option <?php if ($con == 0) {
                                            $con++;
                                            echo 'selected';
                                        } ?> value="<?= $id2 ?>"><?= $descripcion ?></option>

                            <?php } ?>
                        </select>

                    </div>
                </div>
                <!-- FIN DE ROW1 -->
                <!-- INI ROW2 -->
                <div class="row mt-3">
                    <div class="col-sm-12">
                        <div class="form-floating">
                            <textarea class="form-control input-validation" placeholder="Leave a comment here" id="desPro" style="height: 100px"></textarea>
                            <label for="floatingTextarea2">Descripción del producto. </label>
                        </div>
                    </div>
                </div>
                <!-- FIN ROW2 -->
                <!-- INI ROW3 -->
                <div class="row flex-grap mt-3">
                    <div class="col-sm-3">


                        <label class="form-check-label" for="flexRadioDefault1">
                            Monto maximo.
                        </label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="txtMon">Q</span>
                            <input type="number" id="montoMax" class="form-control" min="1" step="0.01" onkeyup="validaNegativo('#montoMax',1,10000000)">
                        </div>


                    </div>
                    <div class="col-sm-3 mt-1">
                        <div class="form-floating mb-4">
                            <input value="1" type="number" step="0.01" min="1" max="99" class="form-control input-validation" id="tasaInt" placeholder="." onkeyup="validaNegativo('#tasaInt',1,99)">
                            <!-- Tasa de interes anual 1-99 -->
                            <label for="floatingInput">Tasa de interes</label>

                        </div>
                    </div>
                    <div class="col-sm-3 mt-1">
                        <div class="form-floating mb-4">
                            <input value="0" type="number" min="0" max="99" class="form-control input-validation" id="porMo" placeholder="." onkeyup="validaNegativo('#porMo',0,99)">
                            <label for="porMo" id="porcentajeMora">Porcentaje de mora (1-99)</label>
                        </div>
                    </div>
                    <div class="col-sm-3 mt-1">
                        <div class="form-floating mb-4">
                            <input value="0" type="number" min="0" class="form-control input-validation" id="diaGra" placeholder="." onkeyup="validaNegativo('#diaGra',0,1000)">
                            <label for="floatingInput">Días de gracia</label>
                        </div>
                    </div>
                </div>
                <!-- FIN ROW3 -->
                <!-- INI ROW4 -->
                <div class="row flex-grap ">

                    <div class="col-sm-3">
                        <label for="text" class="form-label">Seleccione el tipo de mora:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="opMo" value="1" onclick="selRadios1();" checked>
                            <script>
                                function selRadios1() {
                                    $('#radTipoMora').show()
                                    $('#divfactordia').hide()
                                    document.getElementById("porcentajeMora").innerHTML = "Mora por porcentaje: ";
                                    $("input[name='opCal']")
                                        .filter("[value='" + 1 + "']")
                                        .prop("checked", true);
                                }
                            </script>
                            <label class="form-check-label" for="flexRadioDefault1">
                                Por Porcentaje
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="opMo" value="2" onclick="selRadios0();">
                            <script>
                                function selRadios0() {
                                    $('#divfactordia').show()
                                    $('#radTipoMora').hide()
                                    document.getElementById("porcentajeMora").innerHTML = "Mora por monto fijo: ";
                                    $("input[name='opCal']")
                                        .filter("[value='" + 0 + "']")
                                        .prop("checked", true);
                                }
                            </script>
                            <label class="form-check-label" for="flexRadioDefault2">
                                Monto Fijo
                            </label>
                        </div>
                    </div>

                    <div class="col-sm-3" id="radTipoMora">
                        <label for="text" class="form-label">Tipo de calculo:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="opCal" value="1" checked>
                            <label class="form-check-label" for="flexRadioDefault1">
                                Mora por cuota
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="opCal" value="2">
                            <label class="form-check-label" for="flexRadioDefault2">
                                Mora por capital
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="opCal" value="3">
                            <label class="form-check-label" for="flexRadioDefault2">
                                Mora por saldo capital
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="opCal" value="4">
                            <label class="form-check-label" for="flexRadioDefault2">
                                Mora por Monto Desembolsado
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="opCal" value="0" disabled hidden>
                        </div>
                    </div>
                    <div class="col-sm-3 mt-1" style="display: none;" id="divfactordia">
                        <div class="form-floating mb-4">
                            <input type="number" min="0" class="form-control input-validation" id="factordia" placeholder="0" onkeyup="validaNegativo('#factordia',1,100)" value="1">
                            <label for="floatingInput">Cada cuantos dias de atraso</label>
                        </div>
                    </div>
                </div>

                <!-- FIN ROW4-->
                <!-- INI ROW5 BOTONES -->
                <div class="row flex-grap mt-3">
                    <div class="col-sm-6">
                        <button type="button" id="btnGua" class="btn btn-success mt-1" onclick="if(validaNegativoant()==0)return;if(!validaG([`nomPro`,`desPro`,`montoMax`,`tasaInt`,`porMo`,`diaGra`],[],[])){return;};if(!validaMinMaxPor('tasaInt',1,99,'Tasa de interes anaual',1)){return;};if(!validaMinMaxPor('porMo',0,99,'Porcentaje de mora',1)){return;}; if(!validaMinMaxPor('diaGra',0,0,'Día de gracias por mes',2)){return;}; obtiene([`nomPro`,`desPro`,`montoMax`,`tasaInt`,`porMo`,`diaGra`,`factordia`],['selector'],['opMo','opCal'],'guardarProducto','0',['<?php echo $codusu; ?>'])">Guardar</button>
                        <button style="display: none;" type="button" id="btnAct" class="btn btn-warning mt-1" onclick="if(validaNegativoant()==0)return;if(!validaG([`nomPro`,`desPro`,`montoMax`,`tasaInt`,`porMo`,`diaGra`],[],[])){return;};obtiene([`nomPro`,`desPro`,`montoMax`,`tasaInt`,`porMo`,`diaGra`,`idPro`,`factordia`],['selector'],['opMo','opCal'],'actualizarProducto','0',['<?php echo $codusu; ?>'])">Actualizar</button>
                        <button type="button" id="btnCan" class="btn btn-danger mt-1" onclick="limpiarInputs()">Cancelar</button>
                    </div>
                </div>
                <!-- FIN ROW5 TABLA DE REGISTO-->
                <div class="row flex-grap mt-2">
                    <div class="table-responsive">
                        <table class="table" id="tbProductos">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Fondos</th>
                                    <th scope="col">Código</th>
                                    <th scope="col">Producto</th>
                                    <th scope="col">Descripción</th>
                                    <th scope="col">Monto</th>
                                    <th scope="col">Tasa de Interes</th>
                                    <th scope="col">Porcentaje de Mora</th>
                                    <th scope="col">Día de gracia</th>
                                    <th scope="col">Tipo de mora</th>
                                    <th scope="col">Tipo de Calculo</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $consulta = mysqli_query($conexion, "SELECT  pro.id AS idPro, fon.id AS idFon, fon.descripcion AS fondos, pro.cod_producto, pro.nombre, pro.descripcion, pro.monto_maximo, pro.tasa_interes, pro.porcentaje_mora, pro.dias_de_gracias, pro.tipo_mora, pro.tipo_calculo
                                        FROM cre_productos pro
                                        INNER JOIN ctb_fuente_fondos fon ON fon.id = pro.id_fondo  WHERE pro.estado = 1 ORDER BY pro.id DESC;");

                                while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                    $id2 = $row["idPro"]; //0
                                    $fondos = $row["fondos"]; //1
                                    $codPro = $row["cod_producto"]; //2
                                    $nombre = $row["nombre"]; //3
                                    $descripcion = $row["descripcion"]; //4
                                    $monto = $row["monto_maximo"]; //5

                                    $tasaInt = $row["tasa_interes"]; //6
                                    $mora = $row["porcentaje_mora"]; //7
                                    $dias_de_gracias = $row["dias_de_gracias"]; //8
                                    $tipo_mora = $row["tipo_mora"]; //9
                                    $idFon = $row["idFon"];
                                    $tipoCal = $row["tipo_calculo"];

                                    $dato = $id2 . "||" . //0
                                        $fondos . "||" . //1
                                        $codPro . "||" . //2
                                        $nombre . "||" . //3
                                        $descripcion . "||" . //4
                                        $monto . "||" . //5
                                        $tasaInt . "||" . //6
                                        $mora . "||" . //7
                                        $dias_de_gracias . "||" . //8
                                        $tipo_mora . "||" . //9
                                        $idFon . "||" . //10
                                        $tipoCal; //11

                                ?>
                                    <!-- seccion de datos -->
                                    <tr>
                                        <td><?= $id2 ?></td>
                                        <td><?= $fondos ?></td>
                                        <td><?= $codPro ?></td>
                                        <td><?= $nombre ?></td>
                                        <td><?= $descripcion ?></td>
                                        <td><?= "Q " . $monto ?></td>
                                        <td><?= $tasaInt . "%" ?></td>
                                        <td><?= $mora . "%" ?></td>
                                        <td><?= $dias_de_gracias ?></td>
                                        <td><?= ($tipo_mora == 1) ? 'Por Porcentaje' : 'Monto Fijo' ?></td>
                                        <td><?= ($tipoCal == 1) ? 'Mora por cuota' : (($tipoCal == 2) ? 'Mora por capital' : (($tipoCal == 3) ? 'Saldo capital' : (($tipoCal == 4) ? 'Monto Desembolsado' : 'Monto Fijo'))) ?></td>


                                        <td>

                                            <button type="button" class="btn btn-primary mt-1" onclick="editProCre('<?php echo $dato ?>')"><i class="fa-regular fa-pen-to-square" style="color: #ffffff;"></i></button>
                                            <button type="button" class="btn btn-danger mt-1" onclick="eliminar('<?php echo $id2 ?>',`crud_admincre`,'0',`eliminaProducto`,'<?php echo $codusu ?>')"><i class="fa-sharp fa-solid fa-trash" style="color: #ffffff;"></i></button>

                                        </td>

                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <script>
                    function validaNegativoant() {
                        var montoMax = $("#montoMax").val();
                        var tasaInt = $("#tasaInt").val();
                        var porMo = $("#porMo").val();
                        var diaGra = $("#diaGra").val();
                        if (montoMax < 0 || tasaInt < 0 || porMo < 0 || diaGra < 0) {
                            Swal.fire({
                                icon: 'error',
                                title: '¡ERROR!',
                                text: 'No se permiten números negativos '
                            })
                            return 0;
                        }
                    }

                    function validaNegativo(elemento, minimo, maximo) {
                        var cantidad = $(elemento).val();
                        if (cantidad < minimo || cantidad > maximo) {
                            $(elemento).val(minimo);
                            Swal.fire({
                                icon: 'error',
                                title: '¡ERROR!',
                                text: 'Valor inválido'
                            })
                            return 0;
                        }
                    }


                    $(document).ready(function() {
                        $('#tbProductos').DataTable({
                            //Codigo para reportes
                            "lengthMenu": [
                                [5, 10, 15, -1],
                                ['5 filas', '10 filas', '15 filas', 'Mostrar todos']
                            ],
                            dom: 'Bfrtilp',
                            buttons: [{
                                    extend: 'excelHtml5',
                                    title: 'Productos de creditos',
                                    text: "Excel <i class='fa-solid fa-file-csv' style='color: #ffffff;'></i>",
                                    titleAttr: 'Exportar a Excel',
                                    // className: 'btn btn-success !important',
                                    exportOptions: {
                                        columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                                    },
                                },
                                {
                                    extend: 'pdfHtml5',
                                    title: 'Productos de creditos',
                                    text: "PDF <i class='fa-solid fa-file-pdf' style='color: #ffffff;'></i>",
                                    titleAttr: 'Exportar a Excel',
                                    // className: 'btn btn-danger',
                                    exportOptions: {
                                        columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                                    },
                                    customize: function(doc) {
                                        // Cambiar orientación a horizontal
                                        doc.pageOrientation = 'landscape';
                                    }
                                },
                                {
                                    extend: 'print',
                                    title: 'Productos de creditos',
                                    text: "Imprimir <i class='fa-solid fa-print' style='color: #ffffff;'></i>",
                                    titleAttr: 'Exportar a Excel',
                                    // className: 'btn btn-primary',
                                    exportOptions: {
                                        columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                                    },
                                },
                            ],
                            //Codigo para ordenar
                            "order": [
                                [0, 'desc'],
                                [1, 'desc']
                            ],
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

                    $('#btnPDF').click(function() {
                        gPdf('tbProductos');
                    })
                </script>
                <!-- FIN BODY -->
            </div>
        </div>
        <!-- FIN DEL SISTEMA -->
    <?php
        break;
    case 'productoGastos':
    ?>
        <!-- Sistema para Gastos de producto CRUD ************************************************************************************** -->
        <!-- Los input de abajo se encarga de reimpirmir los datos  -->
        <input type="text" id="file" value="creditos_01" style="display: none;">
        <input type="text" id="condi" value="productoGastos" style="display: none;">

        <!-- ID ESPECIALES -->
        <input type="text" id="idGastoPro" placeholder="idGastoPro" disabled hidden>
        <input type="text" id="idTipoG" placeholder="idTipoG" disabled hidden>

        <div class="card container">
            <div class="card-header">
                <h5>Gastos de productos</h5>
            </div>
            <div class="card-body flex-grap">
                <!-- INI BODY -->
                <div class="row flex-grap">

                    <div class="col-lg-6 col-md-12 col-sm-12 mt-2">
                        <label class="form-check-label" for="flexRadioDefault1">
                            Nombre de producto.
                        </label>
                        <div class="input-group mb-3">
                            <!-- <button class="btn btn-warning" type="button" id="button-addon1"><i class="fa-sharp fa-solid fa-magnifying-glass" style="color: #ffffff;"></i></button> -->
                            <button class="btn btn-warning" type="button" id="button-addon1" onclick="abrir_modal('#modaljalagastos', '#id_modal_hidden', 'idTipoG,tipoGasto/A,A/#/#/#/#/#')"><i class="fa-sharp fa-solid fa-magnifying-glass" style="color: #ffffff;"></i></button>
                            <input type="text" id="tipoGasto" class="form-control" aria-describedby="button-addon1" placeholder="Buscar producto" disabled>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12 col-sm-12 mt-2">
                        <label class="form-check-label" for="flexRadioDefault1">
                            Tipo de gasto.
                        </label>
                        <select id="selecPro" class="form-select" aria-label="Default select example">
                            <?php
                            $consulta = mysqli_query($conexion, "SELECT id, nombre_gasto AS tipoGasto FROM cre_tipogastos WHERE estado =1;");
                            $con = 0;
                            while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                $id2 = $row["id"];
                                $tipoGasto = $row["tipoGasto"];
                            ?>
                                <option <?php if ($con == 0) {
                                            $con++;
                                            echo 'selected';
                                        } ?> value="<?= $id2 ?>"><?= $tipoGasto ?></option>

                            <?php } ?>
                        </select>
                    </div>

                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-12 col-sm-12 mt-2">
                        <label for="Nombre del Gasto" class="form-label ">Tipo de cobro. </label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="opCobro" id="T1" value="1" checked onclick="tipocobro(1)">
                            <label class="form-check-label" for="T1">
                                Desembolso
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="opCobro" id="T2" value="2" onclick="tipocobro(2)">
                            <label class="form-check-label" for="T2">
                                Cuota
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-12 col-sm-12 mt-2">
                        <label for="Nombre del Gasto" class="form-label ">Tipo de monto. </label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="opMonto" id="opmonto1" value="1" checked onclick="tipoMontoF()">
                            <label class="form-check-label" for="opmonto1">
                                Fijo
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="opMonto" id="opmonto2" value="2" onclick="tipoMontoP()">
                            <label class="form-check-label" for="opmonto2">
                                Porcentaje
                            </label>
                        </div>
                        <div class="form-check" id="divmonto3">
                            <input class="form-check-input" type="radio" name="opMonto" id="opmonto3" value="3" onclick="tipoMontoV()">
                            <label class="form-check-label" for="opmonto3">
                                Variable
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-12 col-sm-12 mt-2" id="grpradio3">
                        <label for="Nombre del Gasto" class="form-label ">Sobre que se calcula</label>
                        <div class="form-check" id="divopcalculo1">
                            <input class="form-check-input" type="radio" name="opcalculo" id="radio1" value="1" checked>
                            <label class="form-check-label" for="radio1" id="lb1">Fijo</label>
                        </div>
                        <div class="form-check" id="divopcalculo2">
                            <input class="form-check-input" type="radio" name="opcalculo" id="radio2" value="2">
                            <label class="form-check-label" for="radio2" id="lb2">Plazo</label>
                        </div>
                        <div class="form-check" id="divopcalculo3">
                            <input class="form-check-input" type="radio" name="opcalculo" id="radio3" value="3">
                            <label class="form-check-label" for="radio3" id="lb3">Plazo x Monto</label>
                        </div>
                        <div class="form-check" id="divopcalculo4">
                            <input class="form-check-input" type="radio" name="opcalculo" id="radio4" value="4">
                            <label class="form-check-label" for="radio4" id="lb4">Monto</label>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-12 col-sm-12 mt-2">
                        <!--  <label class="form-check-label" for="datoMonto">Monto.</label> -->
                        <div class="mb-3">
                            <span class="input-group-addon" id="txtMon">Monto Fijo</span>
                            <input type="number" id="datoMonto" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                    <script>
                        function showhide(ids, estados) {
                            var estado = ['none', 'block'];
                            for (let i = 0; i < ids.length; i++) {
                                document.getElementById(ids[i]).style.display = estado[estados[i]];
                            }
                        }

                        function tipocobro(opcion) {
                            $('#opmonto1').prop('checked', true);
                            tipoMontoF();
                            if (opcion == 1) {
                                $('#divmonto3').show();
                            } else {
                                $('#divmonto3').hide();

                            }
                        }

                        function tipoMontoF() {
                            var cobro = getradiosval(['opCobro'])[0];
                            if (cobro == 1) showhide(['divopcalculo1', 'divopcalculo2', 'divopcalculo3', 'divopcalculo4'], [1, 1, 1, 1]);
                            if (cobro == 2) showhide(['divopcalculo1', 'divopcalculo2', 'divopcalculo3', 'divopcalculo4'], [1, 1, 0, 0]);
                            cobro = (cobro == 1) ? cobro : 3;
                            changetitles(cobro);

                            var spanMont = document.querySelector(".input-group-addon#txtMon");
                            spanMont.textContent = "Monto Fijo";
                            var input = document.getElementById("datoMonto");
                            input.removeAttribute("max");
                        }

                        function tipoMontoP() {
                            var cobro = getradiosval(['opCobro'])[0];
                            if (cobro == 1) showhide(['divopcalculo1', 'divopcalculo2', 'divopcalculo3', 'divopcalculo4'], [0, 1, 1, 1]);
                            if (cobro == 2) showhide(['divopcalculo1', 'divopcalculo2', 'divopcalculo3', 'divopcalculo4'], [1, 1, 1, 0]);
                            changetitles(cobro);

                            var spanMont = document.querySelector(".input-group-addon#txtMon");
                            spanMont.textContent = "1 al 100 %";
                            document.getElementById("datoMonto").value = "";
                            var input = document.getElementById("datoMonto");
                            input.max = 100;
                        }

                        function changetitles(caso) {
                            //POR DESEMBOLSO
                            if (caso == 1) {
                                document.getElementById('lb1').textContent = "Fijo";
                                document.getElementById('lb2').textContent = "Plazo";
                                document.getElementById('lb3').textContent = "Plazo x Monto";
                            }

                            //POR CUOTA
                            if (caso == 2) {
                                document.getElementById('lb1').textContent = "% del capital de la cuota";
                                document.getElementById('lb2').textContent = "% del interes de la cuota";
                                document.getElementById('lb3').textContent = "% del total de la cuota";
                            }

                            if (caso == 3) {
                                document.getElementById('lb1').textContent = "Fijo en cada cuota";
                                document.getElementById('lb2').textContent = "Monto fijo / No. cuota";
                                document.getElementById('lb3').textContent = "Plazo x Monto";
                            }
                        }

                        function tipoMontoV() {
                            showhide(['divopcalculo1', 'divopcalculo2', 'divopcalculo3', 'divopcalculo4'], [0, 0, 0, 0]);
                            var spanMont = document.querySelector(".input-group-addon#txtMon");
                            spanMont.textContent = "Monto Variable";
                            document.getElementById("datoMonto").value = "0";
                            var input = document.getElementById("datoMonto");
                            input.removeAttribute("max");
                        }
                    </script>
                </div>
                <!-- <div class="row m-3">
                            <div class="alert alert-success" role="alert">
                                <h4 class="alert-heading">Well done!</h4>
                                <p>Aww onger so that you can see how spacing within an alert works with this kind of content.</p>
                                <hr>
                                <p class="mb-0">Whenever you need to, be sure to use margin utilities to keep things nice and tidy.</p>
                            </div>
                        </div> -->
                <div class="row flex-grap">
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-2">
                        <!-- <button type="button" id="btnGua" class="btn btn-success mt-1" onclick="validaMon('datoMonto','opMonto')">Prueba</button> -->
                        <button type="button" id="btnGua" class="btn btn-success mt-1" onclick="if(!validaG(['tipoGasto','datoMonto'],['selecPro'],[])){return;};if(!validaMon('datoMonto','opMonto')){return;};obtiene(['idTipoG','datoMonto'],['selecPro'],['opCobro','opMonto','opcalculo'],'guadarGastosProductos','0',['<?php echo $codusu; ?>'])">Guardar</button>
                        <button type="button" id="btnAct" class="btn btn-warning mt-1" onclick="if(!validaG(['tipoGasto','datoMonto'],['selecPro'],[])){return;};if(!validaMon('datoMonto','opMonto')){return;}obtiene(['idGastoPro','idTipoG','datoMonto'],['selecPro'],['opCobro','opMonto','opcalculo'],'actGasPro','0',['<?php echo $codusu; ?>'])">Actualizar</button>
                        <button type="button" id="btnCan" class="btn btn-danger mt-1" onclick="limpiarInputs()">Cancelar</button>
                    </div>
                </div>
                <div class="row  flex-grap">
                    <!-- INICIO DE LA TABLA ********** -->
                    <div class="container mt-3">
                        <h6>Registro de gastos </h6>
                        <table class="table" id="tbGastodeProducto">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Producto</th>
                                    <th>Tipo de gasto</th>
                                    <th>Tipo de cobro</th>
                                    <th>Tipo de monto</th>
                                    <th>monto</th>
                                    <th>Opciones</th>
                                </tr>
                            </thead>
                            <tbody>

                                <!--Inicio de la TABLA **********-->
                                <?php
                                $consulta = mysqli_query($conexion, "SELECT ProG.id, cPro.nombre AS nombreProducto, cTg.nombre_gasto AS tipoGasto, ProG.tipo_deCobro, ProG.tipo_deMonto, ProG.monto, cPro.id AS idproductos , cTg.id AS idtipogasto, ProG.calculox
                                        FROM cre_productos_gastos AS ProG
                                        INNER JOIN cre_productos AS cPro ON ProG.id_producto = cPro.id
                                        INNER JOIN cre_tipogastos AS cTg ON ProG.id_tipo_deGasto =  cTg.id 
                                        WHERE ProG.estado = 1 ORDER BY ProG.id DESC; ");

                                while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                    $id2 = $row["id"];
                                    $nombrePro = $row["nombreProducto"];
                                    $tipoGasto = $row["tipoGasto"];
                                    $tipoCobro = $row["tipo_deCobro"];
                                    $tipoMonto = $row["tipo_deMonto"];
                                    $monto = $row["monto"];
                                    $idPro = $row["idproductos"];
                                    $idTipG = $row["idtipogasto"];
                                    $calculox = $row["calculox"];

                                    $dato = $id2 . "||" . //0
                                        $nombrePro . "||" . //1
                                        $tipoGasto . "||" . //2
                                        $tipoCobro . "||" . //3
                                        $tipoMonto . "||" . //4
                                        $monto . "||" . //5
                                        $idPro . "||" . //6
                                        $idTipG . "||" . //7
                                        $calculox; //8

                                ?>
                                    <!-- seccion de datos -->
                                    <tr>
                                        <td><?= $id2 ?></td>
                                        <td><?= $nombrePro ?></td>
                                        <td><?= $tipoGasto ?></td>
                                        <td><?= ($tipoCobro == 1) ? "Desembolso" : (($tipoCobro == 2) ? "Cuota" : "Null") ?></td>
                                        <td><?= ($tipoMonto == 1) ? "Fijo" : (($tipoMonto == 2) ? "Porcentaje" : "Variable") ?></td>
                                        <td><?= ($tipoMonto == 1) ? $monto : (($tipoMonto == 2) ? $monto . "%" : "--"); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-primary mt-1" onclick="editGastoPro('<?php echo $dato ?>')"><i class="fa-regular fa-pen-to-square" style="color: #ffffff;"></i></button>
                                            <button type="button" class="btn btn-danger mt-1" onclick="eliminar('<?php echo $id2 ?>',`crud_admincre`,'0',`elimnarGasPro`,'<?php echo $codusu ?>')"><i class="fa-sharp fa-solid fa-trash" style="color: #ffffff;"></i></button>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <!--Fin de la TABLA **********-->
                            </tbody>
                        </table>
                        <!-- FIN DE LA TABLA ********** -->
                        <!-- FIN BODY -->
                    </div>
                    <script>
                        $(document).ready(function() {
                            inicializarDataTable('tbGastodeProducto');
                        });
                    </script>
                    <div class="row">
                        <!-- INICIO DEL MODAL ******************** -->

                        <!-- Inicia modal -->
                        <div class="modal fade" id="modaljalagastos" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <div class="modal-dialog  modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Lista de productos</h1>
                                        <button type="hidden" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        <input type="hidden" id="id_modal_hidden" value="" readonly>
                                    </div>
                                    <div class="modal-body">
                                        <!-- INICIO Tabla de nomenclatura -->
                                        <div class="container mt-3">
                                            <h5>Datos </h5>
                                            <table class="table" id="tbTipoGasto1">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Fondo</th>
                                                        <th>Producto</th>
                                                        <th>Descripción</th>
                                                        <th>Tasa de interes</th>
                                                        <th>Opciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <!--Inicio de la tb Modal-->
                                                    <?php
                                                    $consulta = mysqli_query($conexion, "SELECT Pro.id, Fon.descripcion AS fondo, Pro.nombre, Pro.descripcion, Pro.tasa_interes 
                                                            FROM cre_productos AS Pro
                                                            INNER JOIN ctb_fuente_fondos AS Fon ON Pro.id_fondo = Fon.id
                                                            WHERE Pro.estado = 1 ORDER BY Pro.id DESC; ");

                                                    while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                                        $id2 = $row["id"];
                                                        $nombre = $row["nombre"];
                                                    ?>
                                                        <!-- seccion de datos -->
                                                        <tr>
                                                            <td><?= $id2 ?></td>
                                                            <td><?= $row["fondo"] ?></td>
                                                            <td><?= $nombre ?></td>
                                                            <td><?= $row["descripcion"] ?></td>
                                                            <td><?= $row["tasa_interes"] . "%" ?></td>
                                                            <td>
                                                                <button type="" class="btn btn-primary" data-bs-dismiss="modal" onclick="seleccionar_cuenta_ctb3(`#id_modal_hidden`,['<?php echo $id2; ?>','<?php echo $nombre; ?>']); cerrar_modal(`#modaljalagastos`, `hide`, `#id_modal_hidden`);">Seleccionar</button>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                    <!--Fin de la tb Modal-->

                                                </tbody>
                                            </table>
                                            <script>
                                                $(document).ready(function() {
                                                    $('#tbTipoGasto1').on('search.dt')
                                                        .DataTable({
                                                            "lengthMenu": [
                                                                [5, 10, 15, -1],
                                                                ['5 filas', '10 filas', '15 filas', 'Mostrar todos']
                                                            ],
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
                                                                "sProcessing": "Procesando...",

                                                            },
                                                        });

                                                    $('#btnAct').hide();
                                                    $('#btnCan').hide();
                                                    //aqui
                                                    var inputs = document.querySelectorAll('.input-validation');
                                                    for (var i = 0; i < inputs.length; i++) {
                                                        inputs[i].addEventListener('input', function() {
                                                            if (this.value === '') {
                                                                this.classList.add('is-invalid');
                                                            } else {
                                                                this.classList.remove('is-invalid');
                                                            }
                                                        });
                                                    }
                                                });

                                                document.addEventListener('DOMContentLoaded', function() {
                                                    var selects = document.querySelectorAll('.select-validation');
                                                    for (var i = 0; i < selects.length; i++) {
                                                        selects[i].addEventListener('change', function() {
                                                            if (this.value === '') {
                                                                this.classList.add('is-invalid');
                                                            } else {
                                                                this.classList.remove('is-invalid');
                                                            }
                                                        });
                                                    }
                                                });

                                                document.addEventListener('DOMContentLoaded', function() {
                                                    var radios = document.querySelectorAll('.radio-validation');
                                                    for (var i = 0; i < radios.length; i++) {
                                                        var radioGroup = document.getElementsByName(radios[i].name);

                                                        for (var j = 0; j < radioGroup.length; j++) {
                                                            radioGroup[j].addEventListener('change', function() {
                                                                var checkedRadios = document.querySelectorAll('input[name="' + this.name + '"]:checked');
                                                                var isRequired = checkedRadios.length > 1;

                                                                for (var k = 0; k < radioGroup.length; k++) {
                                                                    radioGroup[k].required = isRequired;
                                                                }

                                                                if (!isRequired) {
                                                                    for (var k = 0; k < radioGroup.length; k++) {
                                                                        radioGroup[k].classList.remove('is-invalid');
                                                                    }
                                                                }
                                                            });
                                                        }
                                                    }
                                                });
                                            </script>
                                            <!-- FIN Tabla de nomenclatura -->
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" onclick="cerrar_modal('#modaljalagastos', 'hide', '#id_modal_hidden')">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Fin modal -->
                        </div>
                    </div>
                    <!-- FIN DEL MODAL ******************** -->
                </div>
            </div>
            <!-- FIN DEL SISTEMA -->
        <?php
        break;
    case 'parametrizacion_creditos':
        ?>
            <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
            <input type="text" id="file" value="creditos_01" style="display: none;">
            <input type="text" id="condi" value="parametrizacion_creditos" style="display: none;">
            <div class="text" style="text-align:center">PARAMETRIZACIÓN DE CUENTAS DE CRÉDITOS</div>
            <div class="card">
                <div class="card-header">Aprobación de crédito individual</div>
                <div class="card-body" style="padding-bottom: 0px !important;">
                    <!-- SECCION DE PRODUCTOS -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Productos parametrizados</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-2">
                                <div class="table-responsive">
                                    <table id="tabla_prod" class="table mb-0 nowrap" style="font-size: 0.9rem !important; width:100%">
                                        <thead>
                                            <tr>
                                                <th scope="col"></th>
                                                <th scope="col">Editar</th>
                                                <th scope="col">Producto</th>
                                                <th scope="col">Capital</th>
                                                <th scope="col">Interés</th>
                                                <th scope="col">Mora</th>
                                                <th scope="col">Otros</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CONTENEDOR QUE SE VA A REIMPRIMIR -->
                    <div id="contenedor_section">

                    </div>
                </div>
            </div>
            <script>
                var table;
                $(document).ready(function() {
                    printdiv3('section_parametrizacion_creditos', '#contenedor_section', '0');
                    table = $('#tabla_prod').DataTable({
                        processing: true,
                        serverSide: true,
                        sAjaxSource: '../../src/server_side/parametrizacion_creditos.php',
                        "lengthMenu": [
                            [10, 15],
                            ['10 filas', '15 filas']
                        ],
                        columns: [{
                                class: 'dt-control',
                                orderable: false,
                                data: null,
                                defaultContent: '',
                            },
                            {
                                data: [0],
                                render: function(data, type, row) {
                                    imp = '';
                                    //imp = `<button>${data+`A`+row[0]}</button>`;
                                    imp = `<button type="button" class="btn btn-primary btn-sm" style="font-size: 0.7rem !important;" onclick="printdiv3('section_parametrizacion_creditos', '#contenedor_section','${data}')"><i class="fa-solid fa-pen-to-square"></i></button>`;
                                    return imp;
                                }
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
                            }
                        ],
                        order: [
                            [1, 'asc']
                        ],
                        createdRow: function(row, data, dataIndex) {
                            //console.log(data);
                            //var rowData = table.row(dataIndex).data(); // Obtener los datos de la fila actual, aunque no se muestren
                            //var columnData = rowData[0]; // Acceder a los datos de la columna específica (columna 0 en este caso)
                            $(row).attr('id', 'row_' + data[0]); // Se el asigna un id dinamico a la row
                        },
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

                    var detailRows = [];
                    $('#tabla_prod tbody').on('click', 'tr td.dt-control', function() {
                        var tr = $(this).closest('tr');
                        var row = table.row(tr);
                        var idx = detailRows.indexOf(tr.attr('id'));

                        if (row.child.isShown()) {
                            tr.removeClass('details');
                            row.child.hide();
                            // Remove from the 'open' array
                            detailRows.splice(idx, 1);
                        } else {
                            tr.addClass('details');
                            row.child(format(row.data())).show();
                            // Add to the 'open' array
                            if (idx === -1) {
                                detailRows.push(tr.attr('id'));
                            }
                        }
                    });

                    function format(d) {
                        return (
                            '<b>Código de producto:</b> ' + d[11] + '<br>' +
                            '<b>Nombre:</b> ' + d[1] + '<br>' +
                            '<b>Descripción:</b> ' + d[6] + '<br>' +
                            '<b>Interés:</b> ' + d[7] + '% <br>' +
                            '<b>Monto máximo:</b> ' + d[8] + '<br>' +
                            '<b>Capital:</b> ' + d[9] + ', ' + d[2] + '<br>' +
                            '<b>Interés:</b> ' + d[10] + ', ' + d[3] + '<br>' +
                            '<b>Capital:</b> ' + d[11] + ', ' + d[4] + '<br>' +
                            '<b>Otros:</b> ' + d[12] + ', ' + d[5] + '<br>'
                        );
                    }

                    //Seleccion automatica
                    table.on('draw', function() {
                        detailRows.forEach(function(id, i) {
                            $('#' + id + ' td.dt-control').trigger('click');
                        });
                    });
                })
            </script>
            <?php
            include_once "../../../../src/cris_modales/mdls_nomenclatura.php";
            ?>
        <?php
        break;
    case 'section_parametrizacion_creditos':
        $codusu = $_SESSION['id'];
        $id_agencia = $_SESSION['id_agencia'];
        $codagencia = $_SESSION['agencia'];
        $xtra = $_POST["xtra"];

        //consultar
        $i = 0;
        $bandera = false;
        $datos[] = [];

        if ($xtra != 0) {
            //CONSULTAR TODAS LAS GARANTIAS
            $i = 0;
            $consulta = mysqli_query($conexion, "SELECT * FROM vs_parametros_creditos vpc WHERE vpc.id='$xtra'");
            while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                $datos[$i] = $fila;
                $i++;
                $bandera = true;
            }
        }
        ?>
            <div class="container contenedort" style="max-width: 100% !important;">
                <div class="row">
                    <div class="col">
                        <div class="text-center mb-2"><b>Información de fondo</b></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-sm-5">
                        <div class="form-floating mb-2 mt-2">
                            <input type="text" class="form-control" id="nameprod" placeholder="Nombre de producto" readonly <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['nombre'] . '"';
                                                                                                                            } ?>>
                            <label for="nameprod">Nombre de producto</label>
                        </div>
                        <input type="text" class="form-control" id="idprod" readonly hidden <?php if ($bandera) {
                                                                                                echo 'value="' . $datos[0]['id'] . '"';
                                                                                            } ?>>
                    </div>
                    <div class="col-12 col-sm-7">
                        <div class="form-floating mb-2 mt-2">
                            <input type="text" class="form-control" id="descprod" placeholder="Descripción" readonly <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['descripcion'] . '"';
                                                                                                                        } ?>>
                            <label for="descprod">Descripción</label>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-12 col-sm-6 col-md-6">
                        <div class="form-floating mb-2 mt-2">
                            <input type="text" class="form-control" id="codprod" placeholder="Código de producto" readonly <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['codprod'] . '"';
                                                                                                                            } ?>>
                            <label for="codprod">Código de producto</label>
                            <input type="text" class="form-control" id="idprod" hidden readonly <?php if ($bandera) {
                                                                                                    echo 'value="' . $datos[0]['id'] . '"';
                                                                                                } ?>>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-6">
                        <div class="form-floating mb-2 mt-2">
                            <input type="text" class="form-control" id="fuenteprod" placeholder="Fuente de fondo" readonly <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['nomfondo'] . '"';
                                                                                                                            } ?>>
                            <label for="fuenteprod">Fuente de fondo</label>
                        </div>
                    </div>
                </div>
                <!-- cargo, nombre agencia y codagencia  -->
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-4">
                        <div class="form-floating mb-2 mt-2">
                            <input type="text" class="form-control" id="tasaprod" placeholder="% Interes" readonly <?php if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['interes'] . '"';
                                                                                                                    } ?>>
                            <label for="tasaprod">% Interes</label>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="form-floating mb-2 mt-2">
                            <input type="text" class="form-control" id="maxprod" placeholder="Monto máximo" readonly <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['monto'] . '"';
                                                                                                                        } ?>>
                            <label for="maxprod">Monto máximo</label>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="form-floating mb-2 mt-2">
                            <input type="text" class="form-control" id="diasprod" placeholder="Dias de gracia" readonly <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['dias'] . '"';
                                                                                                                        } ?>>
                            <label for="diasprod">Dias de gracia</label>
                        </div>
                    </div>
                </div>
            </div>
            <!-- datos adicionales -->
            <div class="container contenedort" style="max-width: 100% !important;">
                <div class="row">
                    <div class="col">
                        <div class="text-center mb-2"><b>Cuentas contables del tipo de producto</b></div>
                    </div>
                </div>
                <!-- CAPITAL -->
                <div class="row">
                    <div class="col-12 col-sm-3 d-flex align-items-center justify-content-center mb-2">
                        <div class="badge bg-success text-wrap ps-4 pe-4 pt-2 pb-2" style="font-size: 1rem !important;">Capital:</div>
                    </div>
                    <div class="col-12 col-sm-3 mb-2">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="capital" placeholder="Cuenta contable" readonly <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['c1'] . '"';
                                                                                                                        } ?>>
                            <label for="capital">Cuenta contable</label>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 mb-2">
                        <div class="input-group">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="nomcapital" placeholder="Capital" readonly <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['cnom1'] . '"';
                                                                                                                        } ?>>
                                <input type="text" class="form-control" id="idcapital" placeholder="Capital" readonly hidden <?php if ($bandera) {
                                                                                                                                    echo 'value="' . $datos[0]['c1id'] . '"';
                                                                                                                                } ?>>
                                <label for="fcapital">Nombre cuenta contable para capital</label>
                            </div>
                            <span type="button" class="input-group-text bg-primary text-white" id="btcapital" onclick="abrir_modal('#modal_nomenclatura', '#id_modal_hidden', 'idcapital,capital,nomcapital/A,A,A/-/#/#/#/#')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                        </div>
                    </div>
                </div>

                <!-- INTERES -->
                <div class="row">
                    <div class="col-12 col-sm-3 d-flex align-items-center justify-content-center mb-2">
                        <div class="badge bg-success text-wrap ps-4 pe-4 pt-2 pb-2" style="font-size: 1rem !important;">Interés:</div>
                    </div>
                    <div class="col-12 col-sm-3 mb-2">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="interes" placeholder="Cuenta contable" readonly <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['c2'] . '"';
                                                                                                                        } ?>>
                            <label for="interes">Cuenta contable</label>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 mb-2">
                        <div class="input-group">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="nominteres" placeholder="Interes" readonly <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['cnom2'] . '"';
                                                                                                                        } ?>>
                                <input type="text" class="form-control" id="idinteres" placeholder="Interes" readonly hidden <?php if ($bandera) {
                                                                                                                                    echo 'value="' . $datos[0]['c2id'] . '"';
                                                                                                                                } ?>>
                                <label for="finteres">Nombre cuenta contable para interés</label>
                            </div>
                            <span type="button" class="input-group-text bg-primary text-white" id="btinteres" onclick="abrir_modal('#modal_nomenclatura', '#id_modal_hidden', 'idinteres,interes,nominteres/A,A,A/-/#/#/#/#')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                        </div>

                    </div>
                </div>

                <!-- MORA -->
                <div class="row">
                    <div class="col-12 col-sm-3 d-flex align-items-center justify-content-center mb-2">
                        <div class="badge bg-success text-wrap ps-4 pe-4 pt-2 pb-2" style="font-size: 1rem !important;">Mora:</div>
                    </div>
                    <div class="col-12 col-sm-3 mb-2">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="mora" placeholder="Cuenta contable" readonly <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['c3'] . '"';
                                                                                                                        } ?>>
                            <label for="mora">Cuenta contable</label>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 mb-2">
                        <div class="input-group">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="nommora" placeholder="Mora" readonly <?php if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['cnom3'] . '"';
                                                                                                                    } ?>>
                                <input type="text" class="form-control" id="idmora" placeholder="Mora" readonly hidden <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['c3id'] . '"';
                                                                                                                        } ?>>
                                <label for="fmora">Nombre cuenta contable para mora</label>
                            </div>
                            <span type="button" class="input-group-text bg-primary text-white" id="btmora" onclick="abrir_modal('#modal_nomenclatura', '#id_modal_hidden', 'idmora,mora,nommora/A,A,A/-/#/#/#/#')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                        </div>
                    </div>
                </div>

                <!-- OTROS -->
                <div class="row">
                    <div class="col-12 col-sm-3 d-flex align-items-center justify-content-center mb-2">
                        <div class="badge bg-success text-wrap ps-4 pe-4 pt-2 pb-2" style="font-size: 1rem !important;">Otros:</div>
                    </div>
                    <div class="col-12 col-sm-3 mb-2">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="otros" placeholder="Cuenta contable" readonly <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['c4'] . '"';
                                                                                                                        } ?>>
                            <label for="otros">Cuenta contable</label>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 mb-2">
                        <div class="input-group">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="nomotros" placeholder="Mora" readonly <?php if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['cnom4'] . '"';
                                                                                                                    } ?>>
                                <input type="text" class="form-control" id="idotros" placeholder="Mora" readonly hidden <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['c4id'] . '"';
                                                                                                                        } ?>>
                                <label for="nomotros">Nombre cuenta contable para mora</label>
                            </div>
                            <span type="button" class="input-group-text bg-primary text-white" id="btmora" onclick="abrir_modal('#modal_nomenclatura', '#id_modal_hidden', 'idotros,otros,nomotros/A,A,A/-/#/#/#/#')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-items-md-center">
                <div class="col align-items-center mb-3" id="modal_footer">
                    <?php if ($bandera) { ?>
                        <button class="btn btn-outline-primary mt-2" onclick="obtiene([`idprod`,`nameprod`,`descprod`,`idcapital`,`capital`,`nomcapital`,`idinteres`,`interes`,`nominteres`,`idmora`,`mora`,`nommora`,`idotros`,`otros`,`nomotros`],[],[],`create_parametrizacion_creditos`,`0`,['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>','<?= $datos[0]['id']; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Actualizar</button>
                    <?php } ?>
                    <button type="button" class="btn btn-outline-danger mt-2" onclick="printdiv2('#cuadro','0')">
                        <i class="fa-solid fa-ban"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-outline-warning mt-2" onclick="salir()">
                        <i class="fa-solid fa-circle-xmark"></i> Salir
                    </button>
                </div>
            </div>
    <?php
        break;
}
    ?>