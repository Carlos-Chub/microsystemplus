<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
//include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];

switch ($condi) {
        //Curd otros ingresos 01
    case 'create_tipo_ingresos':
        $codusu = $_SESSION["id"];
?>
        <!-- Crud para agregar, editar y eliminar tipo de gastos  -->
        <input type="text" id="file" value="creditos_01" style="display: none;">
        <input type="text" id="condi" value="gastos" style="display: none;">

        <div class="text" style="text-align:center">Otros gastos</div>

        <div class="card">
            <div class="card-header">Información</div>
            <div class="card-body">
                <form id="miForm">

                    <div class="col">
                        <!-- ID de nomenclaturas -->
                        <input id="idReg" placeholder="idRegistro" disabled hidden><!-- ID de registro  -->
                        <input id="idNom" placeholder="idNomenclatura" disabled hidden> <!-- ID de nomenclatura -->
                    </div>

                    <div class="row g-3">

                        <div class="col-lg-6 col-md-12">
                            <label for="Nombre del Gasto" class="form-label ">Nombre</label>
                            <input type="text" class="form-control input-validation" id="gasto" placeholder="Nombre del gasto" required>

                        </div>

                        <div class="col-lg-6 col-md-12">
                            <label for="Nomenclatura" class="form-label">Nomenclatura</label>
                            <div class="input-group mb-3">
                                <button class="btn btn-warning" type="button" id="buscarNomenclatura" data-bs-toggle="modal" data-bs-target="#otrosGastos">Buscar</button>
                                <input type="text" disabled class="form-control input-validation" id="nomenclatura" placeholder="Nomenclatura" aria-label="Example text with button addon" aria-describedby="button-addon1">
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-lg-6 col-md-12">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" aria-label="Default select example" id="idSelect">
                                <option value="1">Ingreso</option>
                                <option value="2">Egreso</option>
                            </select>

                        </div>
                    </div>
                </form>

                <div class="row mt-2">
                    <div class="col">
                        <div class="conBoton">
                            <button type="button" id="btnGua" class="btn btn-success">Guardar</button>
                            <button type="button" id="btnAct" class="btn btn-warning">Actualizar</button>
                            <button type="button" id="btnCan" class="btn btn-danger">Cancelar</button>
                            <script>
                                $('#btnGua').click(function() {
                                    if ((vaData(['#gasto', '#idNom'])) == false) return;
                                    obtiene(['gasto', 'idNom'], ['idSelect'], [], 'ins_otrGasto', '0', '<?php echo $codusu; ?>')
                                })
                                $('#btnAct').click(function() {
                                    if ((vaData(['#gasto', '#idNom'])) == false) return;
                                    obtiene(['idReg', 'idNom', 'gasto'], ['idSelect'], [], 'act_otrGasto', '0', '<?php echo $codusu; ?>')
                                })
                                $('#btnCan').click(function() {
                                    $("#miForm")[0].reset();
                                    verEle(['#btnAct', '#btnCan'])
                                    verEle(['#btnGua'], 1)
                                })
                            </script>
                        </div>
                    </div>
                </div>

                <div class="container mt-3">
                    <h2>Registro de gastos </h2>
                    <!-- En el siguiente div se imprime la tabla XD con inyecCod cod Calicho XV -->
                    <div id="tbOtrosG"></div>
                </div>
            </div>
            <button type="button" class="btn btn-outline-primary mt-2" onclick="reportes([[],[],[],['38']], `pdf`, `recibo`,0)">Imprimir</button>

        </div>
        <script>
            $(document).ready(function() {
                inyecCod('#tbOtrosG', 'rep_otroGas');
                verEle(['#btnAct', '#btnCan']);
            });
        </script>
    <?php
        include_once '../../src/cris_modales/mdls_otrosGastos.php'; //LLamar al modal
        break;

    case 'fac_otrIngresos':
        $codusu = $_SESSION["id"];
        //Flag para cativar el correlativo automaticamente (1 activo, 0 desactivado)
        $flag_correlativo = 1;
        if ($flag_correlativo == 1) {
            //Obtener la ID de la agencia
            $sql = "SELECT id_agencia FROM tb_usuario WHERE id_usu  = ?";
            $stmt1 = $conexion->prepare($sql);

            $stmt1->bind_param("i", $codusu);
            $dato = $stmt1->execute();

            // Obtener el resultado de la consulta
            $stmt1->bind_result($id_agencia);
            $stmt1->fetch();
            $stmt1->close();

            //Consulta para obtener el numero correlativo
            //$dato = 2 ;
            $sql = "SELECT MAX(CAST(recibo AS SIGNED)) FROM otr_pago op 
            INNER JOIN tb_agencia ta  ON ta.id_agencia = op.agencia 
            INNER JOIN otr_pago_mov opm ON op.id = opm.id_otr_pago 
            INNER JOIN otr_tipo_ingreso oti ON opm.id_otr_tipo_ingreso = oti.id 
            WHERE op.estado = 1 AND oti.tipo = 1 AND op.agencia = ?";
            $stmt1 = $conexion->prepare($sql);

            $stmt1->bind_param("i", $dato);
            $dato = $stmt1->execute();

            $stmt1->bind_result($correlativo);
            $stmt1->fetch();
            $stmt1->close();
        }

    ?>

        <div class="card">
            <h5 class="card-header">Recibo de ingresos</h5>
            <div class="card-body">
                <form id="miForm">
                    <!-- INI CONTENIDO -->
                    <div class="row">
                        <div class="col-lg-7">

                            <div class="card">
                                <h5 class="card-header">Datos</h5>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">Fecha</label>
                                                <input type="date" class="form-control" id="fecha" aria-describedby="emailHelp" value="<?= date('Y-m-d'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-lg-6 col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">Recibo</label>
                                                <input type="text" class="form-control" id="recibo" aria-describedby="emailHelp" value="<?php echo ($flag_correlativo == 1) ? ((int)$correlativo + 1) : '' ?>">
                                                <!-- <input type="text" class="form-control" id="recibo" aria-describedby="emailHelp"> -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12">
                                            <label class="form-label">Cliente</label>
                                            <div class="input-group mb-3">
                                                <button class="btn btn-warning" type="button" id="button-addon1" data-bs-toggle="modal" data-bs-target="#otr_cli">Buscar</button>
                                                <input type="text" id="cliente" class="form-control" placeholder="Cliente" aria-label="Example text with button addon" aria-describedby="button-addon1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-floating">
                                                <textarea class="form-control" placeholder="Leave a comment here" id="descrip"></textarea>
                                                <label for="floatingTextarea">Descripción</label>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="alert alert-success mt-1" role="alert">

                                        <!-- INI -->
                                        <form id="myForm2">
                                            <div class="row">
                                                <div class="col-lg-9 col-md-12">
                                                    <label class="form-label">Tipo de gasto</label>
                                                    <div class="input-group mb-3">
                                                        <button class="btn btn-warning" type="button" id="button-addon1" data-bs-toggle="modal" data-bs-target="#otr_Ingresos">Buscar</button>
                                                        <input type="text" id="otr_gasto" readonly class="form-control" placeholder="información" aria-label="Example text with button addon" aria-describedby="button-addon1">
                                                    </div>
                                                </div>

                                                <div class="col-lg-3 col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Monto</label>
                                                        <input type="number" class="form-control" id="monto" aria-describedby="emailHelp" placeholder="0" min="0" step="0.01">
                                                    </div>
                                                </div>

                                                <div class="col">
                                                    <input type="text" placeholder="idTipoG" id="idTG" disabled hidden>
                                                </div>

                                            </div>

                                            <div class="row">
                                                <div class="col mt-2">
                                                    <button type="button" id="btnAgr" class="btn btn-primary">Agregar</button>
                                                </div>
                                            </div>
                                        </form>
                                        <!-- FIN -->
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="col-lg-5">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <!-- INI TABLA -->
                                            <div class="container mt-3">
                                                <table class="table table-hover tbRecibo-Reset" id="tbRecibo">
                                                    <thead>
                                                        <tr>
                                                            <th>*</th>
                                                            <th>Tipo de Ingreso</th>
                                                            <th>Monto</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- INICIO DE LA INFO -->
                                                    </tbody>
                                                </table>
                                                <script>
                                                    dataTB('#tbRecibo')
                                                </script>
                                            </div>
                                            <!-- FIN TABLA -->
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col d-flex justify-content-end">
                                            <h5><b id="total1"></b></h5>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- area de los botonoes -->
                <div class="row">
                    <div class="col">
                        <button type="button" id="btnGua" class="btn btn-outline-success"><b>Guardar</b></button>
                        <script>
                            $('#btnGua').click(function() {
                                // console.log('Ini');
                                var tabla = document.getElementById('tbRecibo');
                                var filas = tabla.getElementsByTagName('tr');
                                var noFila = filas.length;

                                if ((vaData(['#fecha', '#recibo', '#cliente', '#descrip'])) == false) return;
                                if (noFila == 1) {
                                    Swal.fire({
                                        icon: "info",
                                        title: "Alerta",
                                        text: "Tiene que agregar gastos para realizar el pago"
                                    });
                                    return;
                                }
                                vecMaster = [];
                                vecMaster.push(capDataTb('idG', 'td'));
                                vecMaster.push(capDataTb('monto', 'td'));
                                var matriz = gMatriz(vecMaster);
                                // console.log(matriz);
                                // return;
                                obtiene(['fecha', 'recibo', 'cliente', 'descrip'], [], [], 'cre_otrRecibo', '0', ['<?= $codusu ?>', matriz, 1])
                            })
                        </script>
                    </div>
                </div>
                <!-- FIN CONTENIDO -->
            </div>
        </div>

        <div id="modalTinpoIngreso"></div>

        <script>
            $('#btnAgr').click(function() {
                //BOTON PARA AGREGAR UN DETALLE
                var idG = $('#idTG').val();
                var otr_gasto = $('#otr_gasto').val();
                var monto = $('#monto').val();

                if (otr_gasto === '' || monto === '') {
                    Swal.fire({
                        icon: "info",
                        title: "Alerta",
                        text: "Todos los campos son obligatorios, favor de ingresar la información."
                    });
                    return;
                }

                //Para agregar una fila
                var tabla = document.getElementById('tbRecibo');
                var filas = tabla.getElementsByTagName('tr');
                var noFila = filas.length;

                var tr = $(`<tr id="${'no'+noFila}" >`)
                tr.append(`<td onclick="eliF('${"#no"+noFila}')"><button type="button" class="btn btn-sm btn-danger"><i class="fa-solid fa-minus"></i></button> </td>`) //Accion
                tr.append(`<td name="idG[]" hidden>${idG}</td>`) //idOculto
                tr.append(`<td name="otr_gasto[]">${otr_gasto}</td>`) //Tipo de gasto 
                tr.append(`<td name="monto[]">${monto}</td>`) //Monto
                $('#tbRecibo tbody').append(tr)
                sum();
                //limpiar los campos
                $('#idTG').val("");
                $('#otr_gasto').val("");
                $('#monto').val("");
            })

            function eliF(fila) {
                // var fila = $(fila).text();
                // $('#tbRecibo tr').eq(fila).remove();
                $('#tbRecibo ' + fila).remove();
                sum();
            }

            function sum() {
                // Inicializar una variable para almacenar la suma total
                var sumaTotal = 0;
                // Recorrer los elementos y sumar sus valores
                $('td[name="monto[]"]').each(function() {
                    sumaTotal += parseFloat($(this).text());
                });
                $('#total1').text("Total: " + sumaTotal);
            }
            $(document).ready(function() {
                inyecCod('#modalTinpoIngreso', 'tipo_ingreso', 1);
            })
        </script>
    <?php
        include_once '../../src/cris_modales/mdls_otr_recibo.php'; //LLamar al modal
        break;

    case 'otr_tipoEgreso':
        $codusu = $_SESSION["id"];
        //Flag para cativar el correlativo automaticamente (1 activo, 0 desactivado)
        $flag_correlativo = 1;
        if ($flag_correlativo == 1) {
            //Obtener la ID de la agencia
            $sql = "SELECT id_agencia FROM tb_usuario WHERE id_usu  = ?";
            $stmt1 = $conexion->prepare($sql);

            $stmt1->bind_param("i", $codusu);
            $dato = $stmt1->execute();

            // Obtener el resultado de la consulta
            $stmt1->bind_result($id_agencia);
            $stmt1->fetch();
            $stmt1->close();

            //Consulta para obtener el numero correlativo
            //$dato = 2 ;
            $sql = "SELECT MAX(CAST(recibo AS SIGNED)) FROM otr_pago op 
            INNER JOIN tb_agencia ta  ON ta.id_agencia = op.agencia 
            INNER JOIN otr_pago_mov opm ON op.id = opm.id_otr_pago 
            INNER JOIN otr_tipo_ingreso oti ON opm.id_otr_tipo_ingreso = oti.id 
            WHERE op.estado = 1 AND oti.tipo = 2 AND op.agencia = ?";
            $stmt1 = $conexion->prepare($sql);

            $stmt1->bind_param("i", $dato);
            $dato = $stmt1->execute();

            $stmt1->bind_result($correlativo);
            $stmt1->fetch();
            $stmt1->close();
        }
    ?>
        <div class="card">
            <h5 class="card-header">Recibo de egresos</h5>
            <div class="card-body">
                <form id="miForm">
                    <!-- INI CONTENIDO -->
                    <div class="row">
                        <div class="col-lg-7">

                            <div class="card">
                                <h5 class="card-header">Datos</h5>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">Fecha</label>
                                                <input type="date" class="form-control" id="fecha" aria-describedby="emailHelp" value="<?= date('Y-m-d'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-lg-6 col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">Recibo</label>
                                                <input type="text" class="form-control" id="recibo" aria-describedby="emailHelp" value="<?php echo ($flag_correlativo == 1) ? ((int)$correlativo + 1) : '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12">
                                            <label class="form-label">Cliente</label>
                                            <div class="input-group mb-3">
                                                <button class="btn btn-warning" type="button" id="button-addon1" data-bs-toggle="modal" data-bs-target="#otr_cli">Buscar</button>
                                                <input type="text" id="cliente" class="form-control" placeholder="Cliente" aria-label="Example text with button addon" aria-describedby="button-addon1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-floating">
                                                <textarea class="form-control" placeholder="Leave a comment here" id="descrip"></textarea>
                                                <label for="floatingTextarea">Descripción</label>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="alert alert-success mt-1" role="alert">

                                        <!-- INI -->
                                        <form id="myForm2">
                                            <div class="row">
                                                <div class="col-lg-9 col-md-12">
                                                    <label class="form-label">Tipo de gasto</label>
                                                    <div class="input-group mb-3">
                                                        <button class="btn btn-warning" type="button" id="button-addon1" data-bs-toggle="modal" data-bs-target="#otr_Ingresos">Buscar</button>
                                                        <input type="text" id="otr_gasto" readonly class="form-control" placeholder="información" aria-label="Example text with button addon" aria-describedby="button-addon1">
                                                    </div>
                                                </div>

                                                <div class="col-lg-3 col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Monto</label>
                                                        <input type="number" class="form-control" id="monto" aria-describedby="emailHelp" placeholder="0" min="0" step="0.01">
                                                    </div>
                                                </div>

                                                <div class="col">
                                                    <input type="text" placeholder="idTipoG" id="idTG" disabled hidden>
                                                </div>

                                            </div>

                                            <div class="row">
                                                <div class="col mt-2">
                                                    <button type="button" id="btnAgr" class="btn btn-primary">Agregar</button>
                                                </div>
                                            </div>
                                        </form>
                                        <!-- FIN -->
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="col-lg-5">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <!-- INI TABLA -->
                                            <div class="container mt-3">
                                                <table class="table table-hover tbRecibo-Reset" id="tbRecibo">
                                                    <thead>
                                                        <tr>
                                                            <th>*</th>
                                                            <th>Tipo de Ingreso</th>
                                                            <th>Monto</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- INICIO DE LA INFO -->
                                                    </tbody>
                                                </table>
                                                <script>
                                                    dataTB('#tbRecibo')
                                                </script>
                                            </div>
                                            <!-- FIN TABLA -->
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col d-flex justify-content-end">
                                            <h5><b id="total1"></b></h5>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- area de los botonoes -->
                <div class="row">
                    <div class="col">
                        <button type="button" id="btnGua" class="btn btn-outline-success"><b>Guardar</b></button>
                        <script>
                            $('#btnGua').click(function() {
                                //console.log('Ini');
                                var tabla = document.getElementById('tbRecibo');
                                var filas = tabla.getElementsByTagName('tr');
                                var noFila = filas.length;

                                if ((vaData(['#fecha', '#recibo', '#cliente', '#descrip'])) == false) return;
                                if (noFila == 1) {
                                    Swal.fire({
                                        icon: "info",
                                        title: "Alerta",
                                        text: "Tiene que agregar gastos para realizar el pago"
                                    });
                                    return;
                                }
                                vecMaster = [];
                                vecMaster.push(capDataTb('idG', 'td'));
                                vecMaster.push(capDataTb('monto', 'td'));
                                var matriz = gMatriz(vecMaster);
                                // console.log(matriz);
                                // return;
                                obtiene(['fecha', 'recibo', 'cliente', 'descrip'], [], [], 'cre_otrRecibo', '0', ['<?= $codusu ?>', matriz, 2])
                            })
                        </script>
                    </div>
                </div>
                <!-- FIN CONTENIDO -->
            </div>
        </div>

        <div id="modalTinpoIngreso"></div>

        <script>
            $('#btnAgr').click(function() {
                //Capturar dato
                var idG = $('#idTG').val();
                var otr_gasto = $('#otr_gasto').val();
                var monto = $('#monto').val();

                //console.log("Id del tipo de gasto "+id);

                if (otr_gasto === '' || monto === '') {
                    Swal.fire({
                        icon: "info",
                        title: "Alerta",
                        text: "Todos los campos son obligatorios, favor de ingresar la información."
                    });
                    return;
                }

                //Para agregar una fila
                var tabla = document.getElementById('tbRecibo');
                var filas = tabla.getElementsByTagName('tr');
                var noFila = filas.length;

                var tr = $(`<tr id="${'no'+noFila}" >`)
                tr.append(`<td onclick="eliF('${"#no"+noFila}')"><button type="button" class="btn btn-sm btn-danger"><i class="fa-solid fa-minus"></i></button> </td>`) //Accion
                tr.append(`<td name="idG[]" hidden>${idG}</td>`) //idOculto
                tr.append(`<td name="otr_gasto[]">${otr_gasto}</td>`) //Tipo de gasto 
                tr.append(`<td name="monto[]">${monto}</td>`) //Monto
                $('#tbRecibo tbody').append(tr)
                //$("#myForm2")[0].reset();
                sum();
                //limpiar los campos
                $('#idTG').val("");
                $('#otr_gasto').val("");
                $('#monto').val("");
            })

            function eliF(fila) {
                // var fila = $(fila).text();
                // $('#tbRecibo tr').eq(fila).remove();
                $('#tbRecibo ' + fila).remove();
                sum();
            }

            function sum() {
                // Inicializar una variable para almacenar la suma total
                var sumaTotal = 0;
                // Recorrer los elementos y sumar sus valores
                $('td[name="monto[]"]').each(function() {
                    sumaTotal += parseFloat($(this).text());
                });
                $('#total1').text("Total: " + sumaTotal);
            }
            $(document).ready(function() {
                inyecCod('#modalTinpoIngreso', 'tipo_ingreso', 2);
            })
        </script>
    <?php
        include_once '../../src/cris_modales/mdls_otr_recibo.php'; //LLamar al modal
        break;
    case 'recibos_otros_ingresos':
        $xtra = $_POST["xtra"];
        $usuario = $_SESSION["id"];
        $where = "";
        $mensaje_error = "";
        $bandera_error = false;
        //Validar si ya existe un registro igual que el nombre
        $nuew = "ccodusu='$usuario' AND (dfecsis BETWEEN '" . date('Y-m-d', strtotime(date('Y-m-d') . ' - 7 days')) . "' AND  '" . date('Y-m-d') . "')";
        try {
            $stmt = $conexion->prepare("SELECT IF(tu.puesto='ADM' OR tu.puesto='GER', '1=1', ?) AS valor FROM tb_usuario tu WHERE tu.id_usu = ?");
            if (!$stmt) {
                throw new Exception("Error en la consulta: " . $conexion->error);
            }
            $stmt->bind_param("ss", $nuew, $usuario);
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
        <input type="text" id="condi" value="recibos_otros_ingresos" hidden>
        <input type="text" id="file" value="otros_ingresos_01" hidden>

        <div class="text" style="text-align:center">ADMINISTRACIÓN RECIBO OTROS INGRESOS</div>
        <div class="card">
            <div class="card-header">Administración de recibos de otros ingresos</div>
            <div class="card-body">
                <?php if ($bandera_error) { ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>¡Error!</strong> <?= $mensaje_error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } ?>
                <!-- tabla de recibos individuales -->
                <div class="row mt-2 pb-2">
                    <div class="table-responsive">
                        <table id="otr_Recibos" class="table table-hover table-border nowrap" style="width:100%">
                            <thead class="text-light table-head-aprt mt-2">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Recibo</th>
                                    <th>Concepto</th>
                                    <th>Opción</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 0.9rem !important;">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $("#otr_Recibos").DataTable({
                    "processing": true,
                    "serverSide": true,
                    "sAjaxSource": "../src/server_side/otr_recibo.php",
                    columns: [{
                            data: [1]
                        },
                        {
                            data: [2]
                        },
                        {
                            data: [4]
                        },
                        {
                            data: [0],
                            render: function(data, type, row) {
                                // console.log(row);
                                btn4 = "";
                                data1 = row.join('||');
                                if (row[6] == "1") {
                                    btn1 = `<button type="button" class="btn btn-primary btn-sm" onclick="reportes([[],[],[],['${row[0]}']], 'pdf', '21',0,1)"><i class="fa-solid fa-print"></i></button>`;
                                    btn2 = `<button type="button" class="btn btn-success btn-sm mx-1" onclick="printdiv('edit_recibo_otros_ingresos', '#cuadro', 'Otros_ingresos_01', '${row[0]}')"><i class="fa-solid fa-pen-to-square"></i></button>`;
                                    btn3 = `<button type="button" class="btn btn-danger btn-sm" onclick="eliminar('${row[0]}', 'eli_otrRecibo', ['<?= $usuario; ?>'])"><i class="fa-solid fa-trash"></i></button>`;
                                    if (row[8] != null) {
                                        btn4 = `<button type="button" class="btn btn-warning btn-sm ms-1" onclick="download_image_or_pdf([[],[],[],['${row[0]}']], 'download_file', 1)"><i class="fa-solid fa-download"></i></button>`;
                                    }
                                }
                                return btn1 + btn2 + btn3 + btn4;
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
    case 'edit_recibo_otros_ingresos':
        $xtra = $_POST["xtra"];
        $usuario = $_SESSION["id"];
        $mensaje_error = "";
        $bandera_error = false;
        $bandera = false;
        $datos[] = [];
        try {
            $stmt = $conexion->prepare("SELECT paMov.id AS iddetalle, paMov.id_otr_tipo_ingreso AS idtipo,(SELECT nombre_gasto FROM otr_tipo_ingreso WHERE id = paMov.id_otr_tipo_ingreso) AS nomdetalle,paMov.monto AS montodetalle, op.fecha AS fecharecibo, op.recibo AS recibo, op.cliente AS nomcliente, op.descripcion AS descripcion, op.file AS archivo
            FROM otr_pago_mov paMov
            INNER JOIN otr_tipo_ingreso ingre ON ingre.id = paMov.id_otr_tipo_ingreso
            INNER JOIN otr_pago op ON paMov.id_otr_pago = op.id 
            WHERE id_otr_pago=?");
            if (!$stmt) {
                throw new ErrorException("Error en la consulta: " . $conexion->error);
            }
            $stmt->bind_param("s", $xtra);
            if (!$stmt->execute()) {
                throw new ErrorException("Error al consultar: " . $stmt->error);
            }
            $result = $stmt->get_result();
            $numFilas = $result->num_rows;
            if ($numFilas < 1) {
                throw new ErrorException("No se encontraron registros");
            }
            $i = 0;
            while ($fila = $result->fetch_assoc()) {
                $datos[$i] = $fila;
                $ext = pathinfo($fila['archivo'], PATHINFO_EXTENSION);
                if ($ext == 'pdf') {
                    $src = '../includes/img/icon-pdf.png';
                    // es pdf
                    $html = '<img class="img-thumbnail" id="vistaPrevia" style="max-width:120px; max-height:130px;" src="' . $src . '">';
                } else {
                    // es imagen
                    $imgurl = __DIR__ . '/../../../' . $fila['archivo'];
                    if (!is_file($imgurl)) {
                        $src = '../includes/img/file_not_found.png';
                        $html = '<img class="img-thumbnail" id="vistaPrevia" style="max-width:120px; max-height:130px;" src="' . $src . '">';
                    } else {
                        $imginfo   = getimagesize($imgurl);
                        $mimetype  = $imginfo['mime'];
                        $imageData = base64_encode(file_get_contents($imgurl));
                        $html = '<img class="img-thumbnail" id="vistaPrevia" style="max-width:120px; max-height:130px;" src="data:' . $mimetype . ';base64,' . $imageData . '">';
                    }
                }

                $i++;
            }
            $bandera = true;
        } catch (\ErrorException $e) {
            //Captura el error
            $mensaje_error = $e->getMessage();
            $bandera_error = true;
        }
        // echo '<pre>';
        // print_r($datos);
        // echo '</pre>';
        // echo $html;
    ?>
        <input type="text" id="file" value="otros_ingresos_01" style="display: none;">
        <input type="text" id="condi" value="edit_recibo_otros_ingresos" style="display: none;">
        <div class="text" style="text-align:center">ACTUALIZACIÓN RECIBO DE OTRO INGRESO</div>
        <div class="card">
            <div class="card-header">Actualización de recibo</div>
            <div class="card-body" style="padding-bottom: 0px !important;">
                <?php if ($bandera_error) { ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>¡Error!</strong> <?= $mensaje_error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } ?>
                <!-- seleccion de cliente y su credito-->
                <div class="container contenedort" style="max-width: 100% !important;">
                    <div class="row">
                        <div class="col">
                            <div class="text-center mb-2"><b>Información de encabezado</b></div>
                        </div>
                    </div>
                    <?php if ($bandera) { ?>
                        <div class="row">
                            <div class="col">
                                <div class="text-center"><span class="text-secondary">Sube un archivo de imagen o PDF</span></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="text-center"><span class="text-primary">Codigo recibo: <b><?= $xtra; ?></b></span></div>
                            </div>
                            <input type="text" class="form-control" id="idenca" hidden placeholder="Fecha" <?php if ($bandera) {
                                                                                                                echo 'value="' . $xtra . '"';
                                                                                                            } ?>>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-6 col-sm-6 col-md-2 mt-2 d-flex align-items-center">
                                <div class="mx-auto">
                                    <?php echo $html; ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-2 mt-2">
                                <div class="input-group">
                                    <input type="file" class="form-control" id="fileuploadcli" aria-describedby="inputGroupFileAddon04" aria-label="Upload" onchange="LeerImagen(this)">
                                    <button class="btn btn-outline-primary" type="button" id="inputGroupFileAddon04" onclick="CargarImagen('fileuploadcli','<?= $xtra; ?>')"><i class="fa-solid fa-sd-card me-2"></i>Guardar</button>
                                </div>
                            </div>
                        </div>
                    <?php }; ?>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-floating mb-2 mt-2">
                                <input type="date" class="form-control" id="fecrecibo" placeholder="Fecha" <?php if ($bandera) {
                                                                                                                echo 'value="' . $datos[0]['fecharecibo'] . '"';
                                                                                                            } ?>>
                                <label for="fecrecibo">Fecha</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-floating mb-2 mt-2">
                                <input type="text" class="form-control" id="numrecibo" placeholder="Recibo" <?php if ($bandera) {
                                                                                                                echo 'value="' . $datos[0]['recibo'] . '"';
                                                                                                            } ?>>
                                <label for="numrecibo">Recibo</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-floating mb-2 mt-2">
                                <input type="text" class="form-control" id="nomcliente" placeholder="Nombre cliente" <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['nomcliente'] . '"';
                                                                                                                        } ?>>
                                <label for="nomcliente">Nombre cliente</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-floating mb-2 mt-2">
                                <textarea class="form-control" placeholder="Leave a comment here" id="descrip"><?php if ($bandera) {
                                                                                                                    echo $datos[0]['descripcion'];
                                                                                                                } ?></textarea>
                                <label for="floatingTextarea">Descripción</label>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- NACIMIENTO -->
                <div class="container contenedort" style="max-width: 100% !important;">
                    <div class="row">
                        <div class="col">
                            <div class="text-center mb-2"><b>Detalle recibo</b></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="table-responsive">
                            <table id="detalle_recibo" class="table table-hover table-border nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tipo de Ingreso</th>
                                        <th>Monto</th>
                                    </tr>
                                </thead>
                                <tbody style="font-size: 0.9rem !important;">
                                    <?php if ($bandera) {
                                        for ($i = 0; $i < count($datos); $i++) { ?>
                                            <tr>
                                                <td scope="row"><?= ($datos[$i]["iddetalle"]) ?></td>
                                                <td><?= ($datos[$i]["nomdetalle"]) ?></td>
                                                <td><?= ($datos[$i]["montodetalle"]) ?></td>
                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container" style="max-width: 100% !important;">
                <div class="row justify-items-md-center">
                    <div class="col align-items-center mb-3 ms-2" id="modal_footer">
                        <?php if ($bandera) { ?>
                            <button class="btn btn-outline-primary mt-2" onclick="obtiene(['idenca', 'fecrecibo', 'numrecibo', 'nomcliente', 'descrip'], [], [], 'act_otrRecibo', '0', ['<?= $usuario; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Actualizar</button>
                            <button type="button" class="btn btn-outline-danger mt-2" onclick="printdiv('recibos_otros_ingresos', '#cuadro', 'otros_ingresos_01', '0')">
                                <i class="fa-solid fa-ban"></i> Cancelar
                            </button>
                        <?php } ?>
                        <!-- boton para solicitar credito -->
                        <button type="button" class="btn btn-outline-warning mt-2" onclick="salir()">
                            <i class="fa-solid fa-circle-xmark"></i> Salir
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <script>
            //SELECCIONAR LOS CHECKBOXS DESPUES DE CARGAR EL DOM
            $(document).ready(function() {

            });
        </script>

<?php
        break;
}
?>