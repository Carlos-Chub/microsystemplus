<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$idusuario = 1;
$condi = $_POST["condi"];
switch ($condi) {
        /*--------------------------------------------------------------------------------- */
    case 'catalogo_cuentas_contables':
        $xtra = $_POST["xtra"];
        $codusu = $_SESSION['id'];
?>
        <input type="text" id="condi" value="catalogo_cuentas_contables" hidden>
        <input type="text" id="file" value="ctb008" hidden>
        <div class="card">
            <div class="card-header">Catálogo de cuentas</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-6 border-end border-success">
                        <div class="row justify-content-center">
                            <div class="col-auto me-2 border-bottom border-success">
                                <h4 class="text-center">Listado de cuentas</h4>
                            </div>
                        </div>
                        <!-- <div class="row">
                            <div class="col-12 col-md-6 d-flex justify-content-center mt-2">
                                <button class="col btn btn-danger btn-sm"><i class="fa-solid fa-file-pdf me-2"></i>PDF</button>
                            </div>
                            <div class="col-12 col-md-6 d-flex justify-content-center mt-2">
                                <button class="col btn btn-success btn-sm"><i class="fa-solid fa-file-pdf me-2"></i>EXCEL</button>
                            </div>
                        </div> -->
                        <div class="row mt-3">
                            <div class="col">
                                <div class="table-responsive">
                                    <table class="table" id="tb_nomenclatura">
                                        <thead>
                                            <tr style="font-size: 0.8rem;">
                                                <th>#</th>
                                                <th>Cuenta</th>
                                                <th>Descripción</th>
                                                <th>R/D</th>
                                                <th>Editar/Eliminar</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-group-divider" style="font-size: 0.8rem;">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="row justify-content-center">
                            <div class="col-auto border-bottom border-success">
                                <h4 class="text-center">Creación/Edición de cuentas contables</h4>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col">
                                <div class="form-floating">
                                    <select class="form-select" id="tipo">
                                        <option value="" selected>Seleccione una opción</option>
                                        <option value="R">R - Resumen</option>
                                        <option value="D">D - Detalle</option>
                                    </select>
                                    <label for="tipo">Resumen/Detalle</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="input-group mb-3 mt-3">
                                    <span class="input-group-text" id="basic-addon1">Código de cuenta</span>
                                    <input type="text" class="form-control" id="cod_cuenta" placeholder="Ingrese código" aria-label="Username" aria-describedby="basic-addon1">
                                    <input type="text" class="form-control" id="id_hidden" hidden>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Descripción</span>
                                    <textarea class="form-control" id="descripcion" aria-label="With textarea"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-2" id="btGuardar">
                                <button type="button" class="col-12 button-85" onclick="obtiene([`cod_cuenta`,`descripcion`,`tipo`],[],[],`create_cuentas_contables`,`0`,['<?php echo $codusu; ?>'])">
                                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                                </button>
                            </div>
                            <div class="col-12 mb-2" id="btEditar">
                                <button type="button" class="col-12 button-85" onclick="obtiene([`id_hidden`,`cod_cuenta`,`descripcion`,`tipo`],[],[],`update_cuentas_contables`,`0`,['<?php echo $codusu; ?>'])">
                                    <i class="fa-solid fa-floppy-disk"></i> Actualizar
                                </button>
                            </div>
                            <div class="col-12  mb-2">
                                <button type="button" class="col-12 btn btn-danger" onclick="printdiv2('#cuadro','0')">
                                    <i class="fa-solid fa-ban"></i> Cancelar
                                </button>
                            </div>
                            <div class="col-12 ">
                                <button type="button" class="col-12 btn btn-warning" onclick="salir()">
                                    <i class="fa-solid fa-circle-xmark"></i> Salir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            //Datatable para parametrizacion
            $(document).ready(function() {
                cargar_datos_cuenta(<?php echo $codusu; ?>)
                HabDes_boton(0);
            });
        </script>
    <?php
        break;
        /*--------------------------------------------------------------------------------- */
    case 'fuenteFondos':

        $xtra = $_POST["xtra"];
        $codusu = $_SESSION['id'];
    ?>
        <input type="text" id="condi" value="fuenteFondos" hidden>
        <input type="text" id="file" value="ctb008" hidden>
        <div class="card">
            <div class="card-header">Fuente de Fondos</div>
            <div class="card-body">
                <!-- cuadro -->
                <div class="contenedort container">
                    <div class="row">
                        <div class="col">
                            <div class="input-group mb-3 mt-3">
                                <span class="input-group-text" id="basic-addon1">Descripción</span>
                                <input type="text" class="form-control" id="descripcion" placeholder="Descripción" aria-label="Username" aria-describedby="basic-addon1">
                                <input type="text" placeholder="Descripción" id="id_fuente" hidden>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mt-2 mb-3 d-flex justify-content-center">
                            <button type="button" class="button-85 me-4" id="btGuardar" onclick="obtiene([`descripcion`],[],[],`create_fuentefondos`,`0`,['<?= $codusu; ?>'])">
                                <i class="fa-solid fa-floppy-disk"></i> Guardar
                            </button>
                            <button type="button" class="button-85 me-4" id="btEditar" onclick="obtiene([`id_fuente`,`descripcion`],[],[],`update_fuentefondos`,`0`,['<?= $codusu; ?>'])">
                                <i class="fa-solid fa-floppy-disk"></i> Actualizar
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3 d-flex justify-content-center">
                            <button type="button" class="btn btn-danger me-2" onclick="printdiv2('#cuadro','0')">
                                <i class="fa-solid fa-ban"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-warning me-2" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>
                        </div>
                    </div>
                </div>
                <!-- tabla para los  -->
                <div class="row mt-2 mb-4">
                    <div class="col">
                        <div class="table-responsive">
                            <table class="table" id="tb_fuentefondos">
                                <thead>
                                    <tr style="font-size: 0.8rem;">
                                        <th>#</th>
                                        <th>Descripción</th>
                                        <th>Editar/Eliminar</th>
                                    </tr>
                                </thead>
                                <tbody class="table-group-divider" style="font-size: 0.8rem;">
                                    <?php
                                    $consulta = mysqli_query($conexion, "SELECT ff.id, ff.descripcion FROM ctb_fuente_fondos ff
                                    WHERE ff.estado='1'");
                                    while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) { ?>
                                        <tr>
                                            <th scope="row"><?= $fila['id'] ?></th>
                                            <td><?= $fila['descripcion'] ?></td>
                                            <td>
                                                <button type="button" class="btn btn-success btn-sm" onclick="printdiv5('id_fuente,descripcion/A,A/'+'/'+'#/#', ['<?= $fila['id'] ?>','<?= $fila['descripcion'] ?>']); HabDes_boton(1);"><i class="fa-solid fa-eye"></i></button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminar('<?= $fila['id'] ?>', 'crud_ctb', '0', 'delete_fuentefondos')"><i class="fa-solid fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <script>
                //Datatable para parametrizacion
                $(document).ready(function() {
                    convertir_tabla_a_datatable('tb_fuentefondos');
                    HabDes_boton(0);
                });
            </script>
        </div>
    <?php
        break;
    case 'cierres':
        $xtra = $_POST["xtra"];
        $codusu = $_SESSION['id'];
    ?>
        <input type="text" id="condi" value="cierres" hidden>
        <input type="text" id="file" value="ctb008" hidden>
        <div class="card">
            <div class="card-header">CIERRES MENSUALES</div>
            <div class="card-body">
                <div class="contenedort container">
                    <div class="row">
                        <div class="col">
                            <div class="text-center mb-2"><b>Mes contable actual</b></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3 d-flex justify-content-center">
                            <button type="button" class="btn btn-danger me-2" onclick="printdiv2('#cuadro','0')">
                                <i class="fa-solid fa-ban"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-warning me-2" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>
                        </div>
                    </div>
                </div>
                <div class="container contenedort" style="width: 100% !important;">
                    <div class="row">
                        <div class="col">
                            <div class="text-center mb-2"><b>Meses contables</b></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="table-responsive">
                                <table class="table mb-0" id="ctb_meses">
                                    <thead>
                                        <tr>
                                            <th scope="col">Mes</th>
                                            <th scope="col">Año</th>
                                            <th scope="col">Estado</th>
                                            <th scope="col">Opciones</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $('#ctb_meses').on('search.dt').DataTable({
                    "aProcessing": true,
                    "aServerSide": true,
                    "ordering": false,
                    "lengthMenu": [
                        [10, 15, -1],
                        ['10 filas', '15 filas', 'Mostrar todos']
                    ],
                    "ajax": {
                        url: '../src/cruds/crud_ctb.php',
                        type: "POST",
                        beforeSend: function() {
                            loaderefect(1);
                        },
                        data: {
                            'condi': "mesesctb"
                        },
                        dataType: "json",
                        complete: function(data) {
                            loaderefect(0);
                            // console.log(data)
                        }
                    },
                    "bDestroy": true,
                    "iDisplayLength": 10,
                    "order": [
                        [1, "desc"]
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
        </script>
    <?php
        break;
    case 'apertura_meses_rango':
        $xtra = $_POST["xtra"];
        $codusu = $_SESSION['id'];
    ?>
        <input type="text" id="condi" value="apertura_meses_rango" hidden>
        <input type="text" id="file" value="ctb008" hidden>
        <div class="card">
            <div class="card-header">APERTURAS MENSUALES POR RANGO DE FECHAS</div>
            <div class="card-body">
                <!-- cuadro -->
                <div class="contenedort container">
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="dateini" value="<?php echo date("Y-m-d"); ?>">
                                <label class="text-primary" for="dateini">Fecha inicio</label>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="datefin" value="<?php echo date("Y-m-d"); ?>">
                                <label class="text-primary" for="datefin">Fecha fin</label>
                            </div>
                        </div>
                        <div class="col mt-2 mb-3 d-flex justify-content-center">
                            <button type="button" class="button-85 me-4" id="btGuardar" onclick="obtiene([`dateini`,`datefin`],[],[],`apertura_mes_fecha`,`0`,1)">
                                <i class="fa-solid fa-floppy-disk"></i> apertura
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3 d-flex justify-content-center">
                            <button type="button" class="btn btn-danger me-2" onclick="printdiv2('#cuadro','0')">
                                <i class="fa-solid fa-ban"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-warning me-2" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    <?php
        break;
    case 'paramflujoefectivo':

        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                +++ CONSULTA DE TODAS LAS CUENTAS DE ACTIVO, PASIVO, PATRIMONIO, INGRESOS Y EGRESOS ++++++++++++++++
                ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $nomenclatura[] = [];
        $strque = "SELECT * from ctb_nomenclatura WHERE estado=1 AND tipo='D' AND substr(ccodcta,1,1)<=5 ORDER BY ccodcta";
        $querycuen = mysqli_query($conexion, $strque);
        $j = 0;
        while ($fil = mysqli_fetch_array($querycuen)) {
            $nomenclatura[$j] = $fil;
            $j++;
        }

    ?>
        <input type="text" id="condi" value="paramflujoefectivo" hidden>
        <input type="text" id="file" value="ctb008" hidden>
        <div class="card">
            <div class="card-header">
                <h4>Seleccionar cuentas que afectan el estado de Flujo de Efectivo</h4>
            </div>
            <div class="card-body">
                <div class="accordion accordion-flush" id="accordionFlushExample">
                    <div class="accordion-item row container contenedort">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                1- Gastos que no requirieron efectivo
                            </button>
                        </h2>
                        <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <table id="tbcuentas1" class="table table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Estado</th>
                                            <th>Cuenta</th>
                                            <th>Nombre Cuenta</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item row container contenedort">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
                                2- Efectivos Generados por actividades de operacion
                            </button>
                        </h2>
                        <div id="flush-collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <table id="tbcuentas2" class="table table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Estado</th>
                                            <th>Cuenta</th>
                                            <th>Nombre Cuenta</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item row container contenedort">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
                                3- Flujo de efectivos por actividades de inversion
                            </button>
                        </h2>
                        <div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <table id="tbcuentas3" class="table table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Estado</th>
                                            <th>Cuenta</th>
                                            <th>Nombre Cuenta</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item row container contenedort">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFour" aria-expanded="false" aria-controls="flush-collapseFour">
                                4- Flujo de efectivos por actividades de financiamiento
                            </button>
                        </h2>
                        <div id="flush-collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <table id="tbcuentas4" class="table table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Estado</th>
                                            <th>Cuenta</th>
                                            <th>Nombre Cuenta</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-items-md-center">
                <div class="col align-items-center">
                    <button type="button" class="btn btn-outline-success" onclick="savedataflujo()">
                        <i class="fa fa-floppy-disk"></i> Guardar Cambios
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
            function recolectar_checks2(tabla) {
                checkboxActivados = [];
                // Recorre todas las páginas de la tabla
                tabla.rows().every(function(rowIdx, tableLoop, rowLoop) {
                    // Obtén el estado del checkbox en la fila actual
                    var checkbox = $(this.node()).find('input[type="checkbox"]');
                    if (checkbox.is(':checked')) {
                        checkboxActivados.push(checkbox.val());
                    }
                });
                return (checkboxActivados);
            }

            function savedataflujo() {
                datos = [];
                datos[0] = recolectar_checks2(tabla1);
                datos[1] = recolectar_checks2(tabla2);
                datos[2] = recolectar_checks2(tabla3);
                datos[3] = recolectar_checks2(tabla4);
                obtiene([], [], [], `update_data_flujo`, `0`, datos);
            }

            function loadconfig(numero, nomtabla) {
                var tabla = $('#' + nomtabla).on('search.dt').DataTable({
                    "aProcessing": true,
                    "aServerSide": true,
                    "ordering": false,
                    "lengthMenu": [
                        [10, 15, -1],
                        ['10 filas', '15 filas', 'Mostrar todos']
                    ],
                    "ajax": {
                        url: "../src/cruds/crud_ctb.php",
                        type: "POST",
                        beforeSend: function() {
                            loaderefect(1);
                        },
                        data: {
                            'condi': "cuentasfe",
                            numero
                        },
                        dataType: "json",
                        complete: function(data) {
                            loaderefect(0);
                        }
                    },
                    "bDestroy": true,
                    "iDisplayLength": 10,
                    "order": [
                        [1, "desc"]
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
                return tabla;
            }

            tabla1 = loadconfig(1, "tbcuentas1");
            tabla2 = loadconfig(2, "tbcuentas2");
            tabla3 = loadconfig(3, "tbcuentas3");
            tabla4 = loadconfig(4, "tbcuentas4");
        </script>
    <?php
        break;
    case 'clases_cuentas':

        $xtra = $_POST["xtra"];
        $codusu = $_SESSION['id'];
    ?>

        <div class="card">
            <div class="card-header">Ingresar Nuevas cuentas contables a la Nomenclatura</div>
            <div class="card-body">
                <div class="contenedor container">
                    <div class="row">
                        <div class="col">

                            <div class="input-group mb-3 mt-3">
                                <span class="input-group-text" id="basic-add">Cuenta de Aplicacion</span>
                                <select class="form-select" id="ccodcta" aria-label="Cuenta de Aplicación" aria-describedby="basic-addon1">
                                    <?php
                                    $consulta_cuentas = mysqli_query($conexion, "SELECT ccodcta, cdescrip, tipo, estado FROM ctb_nomenclatura WHERE estado = '1'");
                                    while ($fila_cuenta = mysqli_fetch_array($consulta_cuentas, MYSQLI_ASSOC)) {
                                        $opcion = $fila_cuenta['ccodcta'] . ' - ' . $fila_cuenta['cdescrip'];
                                        echo '<option value="' . $fila_cuenta['ccodcta'] . '">' . $opcion . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="input-group mb-3 mt-3">
                                <label class="input-group-text" for="clase_add">Clase</label>
                                <select class="form-select" id="id" aria-label="Cuenta de Aplicación" aria-describedby="clase_add">
                                    <?php
                                    $consulta_cuentas = mysqli_query($conexion, "SELECT * FROM clhpzzvb_bd_general_coopera.ctb_cuentas_app;");
                                    while ($fila_cuenta2 = mysqli_fetch_array($consulta_cuentas, MYSQLI_ASSOC)) {
                                        echo '<option value="' . $fila_cuenta2['id'] . '">' . $fila_cuenta2['descripcion'] . '</option>';
                                    }
                                    ?>
                                </select>
                                <input type="text" placeholder="cdescrip" id="id_fuente_cdescrip" hidden>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3 d-flex justify-content-center">
                        <?php
                        $verifi_duplicate = mysqli_query($conexion, "SELECT id_tipo,clase FROM ctb_parametros_cuentas;")

                        ?>
                            <button type="button" class="button-85 me-4" onclick="verifi()">
                                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Guardar
                            </button>
                            <button type="button" class="btn btn-warning me-2" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <h1></h1>
        </div>
        <input type="text" id="condi" value="fuenteFondos" hidden>
        <input type="text" id="file" value="ctb008" hidden>
        <div class="card">
            <div class="card-header">Actualizar Clases de Cuentas</div>
            <div class="card-body">
                <!-- cuadro -->
                <div class="contenedort container">
                    <div class="row">
                        <div class="col">

                            <div class="input-group mb-3 mt-3" disabled>
                                <span class="input-group-text" id="basic-addon1">Cuenta de Aplicacion</span>
                                <input type="text" class="form-control" id="cdescrip" placeholder="Cuenta de Aplicacion" aria-label="Username" aria-describedby="basic-addon1" readonly>
                                <input type="text" placeholder="cdescrip" id="id_fuente_cdescrip" hidden readonly>
                            </div>

                            <div class="input-group mb-3 mt-3">
                                <label class="input-group-text" for="claseSelect">Clase</label>
                                <select class="form-select" id="claseSelect" aria-label="Cuenta de Aplicación" aria-describedby="basic-addon1">
                                    <?php
                                    $consulta_cuentas = mysqli_query($conexion, "SELECT * FROM clhpzzvb_bd_general_coopera.ctb_cuentas_app;");
                                    while ($fila_cuenta = mysqli_fetch_array($consulta_cuentas, MYSQLI_ASSOC)) {
                                        echo '<option value="' . $fila_cuenta['id'] . '">' . $fila_cuenta['descripcion'] . '</option>';
                                    }
                                    ?>
                                </select>
                                <input type="text" placeholder="cdescrip" id="id_fuente_cdescrip" hidden>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                    </div>
                    <div class="row">
                        <div class="col mb-3 d-flex justify-content-center">
                            <button type="button" class="button-85 me-4" id="btEditar" onclick="obtiene([`id_fuente`,`descripcion`],[],[],`update_fuentefondos`,`0`,['<?= $codusu; ?>'])">
                                <i class="fa-solid fa-floppy-disk"></i> Actualizar
                            </button>
                            <button type="button" class="btn btn-warning me-2" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>
                        </div>
                    </div>
                </div>
                <!-- tabla para los  -->
                <div class="row mt-2 mb-4">
                    <div class="col">
                        <div class="table-responsive">
                            <table class="table" id="tb_clase">
                                <thead>
                                    <tr style="font-size: 0.8rem;">
                                        <th>#</th>
                                        <th>Cuenta de Aplicacion</th>
                                        <!-- <th>No.</th> -->
                                        <th>Clase</th>
                                        <th>Editar/Eliminar</th>
                                    </tr>
                                </thead>
                                <tbody class="table-group-divider" style="font-size: 0.8rem;">
                                    <?php
                                    $consulta = mysqli_query($conexion, "SELECT * FROM ctb_parametros_cuentas
                                    INNER JOIN clhpzzvb_bd_general_coopera.ctb_cuentas_app ON ctb_parametros_cuentas.id_tipo = clhpzzvb_bd_general_coopera.ctb_cuentas_app.id
                                    INNER JOIN ctb_nomenclatura ON ctb_parametros_cuentas.clase = ctb_nomenclatura.ccodcta;");
                                    while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) { ?>
                                        <tr>
                                            <td><?= $fila['ccodcta'] ?></td>
                                            <td><?= $fila['cdescrip'] ?></td>
                                            <!-- <th scope="row"><?= $fila['id'] ?></th> -->
                                            <td><?= $fila['descripcion'] ?></td>
                                            <td>
                                                <button type="button" class="btn btn-success btn-sm" onclick="printdiv5('id,descripcion,cdescrip/A,A,A/'+'/'+'#/#/#', ['<?= $fila['id'] ?>','<?= $fila['descripcion'] ?>','<?= $fila['cdescrip'] ?>']); HabDes_boton(1);"><i class="fa-solid fa-eye"></i></button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminar('<?= $fila['id'] ?>', 'crud_ctb', '0', 'delete_fuentefondos')"><i class="fa-solid fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <script>
                //Datatable para parametrizacion
                $(document).ready(function() {
                    convertir_tabla_a_datatable('tb_clase');
                    HabDes_boton(0);
                });
            </script>
        </div>
<?php



        break;
}
?>