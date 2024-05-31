<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../src/funcphp/func_gen.php';
include 'funciones/funciones.php';
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];

switch ($condi) {
    case 'cuentasBancos':
        $xtra = $_POST["xtra"];
        $codusu = $_SESSION['id'];
?>
        <div class="text" style="text-align:center">CUENTAS DE BANCOS</div>
        <input type="text" id="condi" value="cuentasBancos" hidden>
        <input type="text" id="file" value="BAN-05" hidden>
        <div class="card">
            <div class="card-header">Cuentas de bancos</div>
            <div class="card-body">
                <!-- cuadro -->
                <div class="contenedort container">
                    <!-- bancos y numero de cuenta -->
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="input-group mb-3 mt-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="banco" placeholder="Seleccione un banco" readonly>
                                    <label for="banco">Banco</label>
                                    <input type="text" id="id_banco" hidden>
                                    <input type="text" id="id" hidden>
                                </div>
                                <span class="input-group-text" style="cursor:pointer" onclick="abrir_modal('#modal_nombancos', '#id_modal_hidden', 'id_banco,banco/A,A/'+'/#/#/#/#')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="input-group mt-3 mb-3">
                                <div class="form-floating">
                                    <input readonly type="text" class="form-control" id="cuentac" placeholder="Seleccione una cuenta contable">
                                    <label for="cuentac">Cuenta contable</label>
                                    <input type="text" id="id_cuenta" hidden>
                                </div>
                                <span class="input-group-text" style="cursor:pointer" onclick="abrir_modal('#modal_nomenclatura', '#id_modal_hidden', 'id_cuenta,cuentac/A,2-3/-/#/#/#/#')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                            </div>

                        </div>
                    </div>
                    <!-- nomenclatura y correlativo -->
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="input-group mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="cuenta" placeholder="Ingrese una cuenta">
                                    <label for="cuenta">No. de cuenta</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="input-group mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="saldo" placeholder="Ingrese una cuenta">
                                    <label for="saldo">Saldo</label>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="col-12 col-sm-6">
                            <div class="input-group mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="floatingInputGroup1" placeholder="Correlativo">
                                    <label for="floatingInputGroup1">Correlativo</label>
                                </div>
                            </div>
                        </div> -->
                    </div>
                    <!-- botones -->
                    <div class="row d-flex justify-content-center">
                        <div class="col-12 col-md-4 text-center mb-3">
                            <button type="button" class="btn btn-success me-2" id="btGuardar" onclick="obtiene([`id_banco`,`banco`,`id_cuenta`,`cuentac`,`cuenta`,`saldo`],[],[],`create_cuentasbancos`,`0`,['<?= $codusu; ?>'])">
                                <i class="fa-solid fa-floppy-disk"></i> Guardar
                            </button>
                            <button type="button" class="btn btn-primary me-2" id="btEditar" onclick="obtiene([`id_banco`,`banco`,`id_cuenta`,`cuentac`,`cuenta`,`id`,`saldo`],[],[],`update_cuentasbancos`,`0`,['<?= $codusu; ?>'])">
                                <i class="fa-solid fa-floppy-disk"></i> Actualizar
                            </button>
                        </div>
                        <div class="col-12 col-md-4 text-center mb-3">
                            <button type="button" class="btn btn-danger me-2" onclick="printdiv2('#cuadro','0')">
                                <i class="fa-solid fa-ban"></i> Cancelar
                            </button>
                        </div>
                        <div class="col-12 col-md-4 text-center mb-3">
                            <button type="button" class="btn btn-warning me-2" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>
                        </div>
                    </div>
                </div>
                <!-- tabla para los  -->
                <div class="contenedort container">
                    <div class="row mt-4 mb-4">
                        <div class="col">
                            <div class="table-responsive">
                                <table class="table" id="tb_cuentasbancos">
                                    <thead>
                                        <tr style="font-size: 0.8rem;">
                                            <th>#</th>
                                            <th>Banco</th>
                                            <th>Cod. cuenta contable</th>
                                            <th>Nombre cuenta contable</th>
                                            <th>Cuenta destino</th>
                                            <th>saldo</th>
                                            <th>Editar/Eliminar</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-group-divider" style="font-size: 0.8rem;">
                                        <?php
                                        $consulta = mysqli_query($conexion, "SELECT cb.id, cb.id_banco, tbn.nombre, cb.id_nomenclatura, ctn.ccodcta, ctn.cdescrip, cb.numcuenta, cb.saldo_ini FROM ctb_bancos cb
                                        INNER JOIN ctb_nomenclatura ctn ON cb.id_nomenclatura=ctn.id
                                        INNER JOIN tb_bancos tbn ON cb.id_banco=tbn.id
                                        WHERE cb.estado='1'");
                                        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) { ?>
                                            <tr>
                                                <th scope="row"><?= $fila['id'] ?></th>
                                                <td><?= $fila['nombre'] ?></td>
                                                <td><?= $fila['ccodcta'] ?></td>
                                                <td><?= $fila['cdescrip'] ?></td>
                                                <td><?= $fila['numcuenta'] ?></td>
                                                <td><?= $fila['saldo_ini'] ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-success btn-sm" onclick="printdiv5('id,id_banco,banco,id_cuenta,cuentac,cuenta,saldo/A,A,A,A,5-6,A,A/-/#/#/#/#/#/#', ['<?= $fila['id'] ?>','<?= $fila['id_banco'] ?>','<?= $fila['nombre'] ?>','<?= $fila['id_nomenclatura'] ?>','<?= $fila['ccodcta'] ?>','<?= $fila['cdescrip'] ?>','<?= $fila['numcuenta'] ?>','<?= $fila['saldo_ini'] ?>']); HabDes_boton(1);"><i class="fa-solid fa-eye"></i></button>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminar('<?= $fila['id'] ?>', 'crud_bancos', '0', 'delete_cuentasbancos')"><i class="fa-solid fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                //Datatable para parametrizacion
                $(document).ready(function() {
                    convertir_tabla_a_datatable('tb_cuentasbancos');
                    HabDes_boton(0);
                });
            </script>
        </div>
    <?php
        break;
    case 'conciliacion':
    ?>
        <input type="text" id="file" value="BAN-05" style="display: none;">
        <input type="text" id="condi" value="conciliacion" style="display: none;">
        <div class="text" style="text-align:center">CONCILIACION BANCARIA POR CUENTAS</div>
        <div class="card">
            <!-- <div class="card-header">FILTROS</div> -->
            <div class="card-body">
                <div class="row container contenedort">
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header"> Cuentas</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-floating mb-3">
                                            <div class="input-group" style="width:min(70%,32rem);">
                                                <input style="display:none;" type="text" class="form-control" id="idcuenta" value="0">
                                                <input type="text" readonly class="form-control" id="cuenta">
                                                <button id="btncuenid" class="btn btn-outline-success" type="button" onclick="abrir_modal(`#modal_cuentas_bancos`, `#id_modal_bancos`, 'idcuenta,cuenta/A,A/'+'/#/#/#/#')" title="Buscar Cuenta contable"><i class="fa fa-magnifying-glass"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por fechas</div>
                            <div class="card-body">
                                <div class="row" id="filfechas">
                                    <div class=" col-sm-6">
                                        <label for="finicio">Desde</label>
                                        <input type="date" class="form-control" id="finicio" min="1950-01-01" value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                    <div class=" col-sm-6">
                                        <label for="ffin">Hasta</label>
                                        <input type="date" class="form-control" id="ffin" min="1950-01-01" value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-primary" title="Libro Bancos en pdf" onclick="procesar()">
                            <i class="fa-solid fa-rotate"></i></i> Procesar
                        </button>
                    </div>
                </div>
                <br>
                <div id="div_movimientos">

                </div>

            </div>
        </div>
        <script>
            function procesar() {
                datosval = getinputsval(['idcuenta', 'finicio', 'ffin']);
                printdiv('movimientos', '#div_movimientos', 'BAN-05', datosval)
            }
        </script>
    <?php
        include '../../src/cris_modales/mdls_cuentas_bancos.php';
        break;

    case 'movimientos':
        $datos = $_POST['xtra'];
        $idcuenta = $datos[0];
        $fecha_inicio = $datos[1];
        $fecha_fin = $datos[2];

        $error = "";
        //NO SE HA SELECCIONADO NINGUNA CUENTA
        if ($idcuenta == 0) {
            $error = 'Seleccione una cuenta bancaria';
            erroralert($error, 'danger');
            return;
        }
        //LAS FECHAS NO SON VALIDAS
        if ($fecha_inicio > $fecha_fin) {
            $error = 'Rango de fechas inválidos';
            erroralert($error, 'danger');
            return;
        }
        $fechaini = strtotime($fecha_inicio);
        $fechafin = strtotime($fecha_fin);
        $anioini = date("Y", $fechaini);
        $aniofin = date("Y", $fechafin);

        if ($anioini != $aniofin) {
            $error = 'Las fechas tienen que ser del mismo año';
            erroralert($error, 'danger');
            return;
        }
        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++ CONSULTA DE TODOS LOS MOVIMIENTOS DE LA CUENTA EN LA FECHA INDICADA +++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $strquery = "SELECT cmov.* from ctb_diario_mov cmov INNER JOIN ctb_bancos ban ON ban.id_nomenclatura=cmov.id_ctb_nomenclatura 
        WHERE ban.estado=1 AND cmov.estado=1 AND id_ctb_nomenclatura=" . $idcuenta . " 
        AND feccnt BETWEEN '" . $fecha_inicio . "' AND '" . $fecha_fin . "' ORDER BY id_ctb_nomenclatura,feccnt";
        $querypol = mysqli_query($conexion, $strquery);
        $ctbmovdata[] = [];
        $j = 0;
        while ($fil = mysqli_fetch_array($querypol)) {
            $ctbmovdata[$j] = $fil;
            $j++;
        }
        //COMPROBAR SI HAY REGISTROS
        if ($j == 0) {
            $error = "No hay movimientos de la cuenta en la fecha indicada";
            erroralert($error, 'danger');
            return;
        }

        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            +++++++ CONSULTA PARTIDA DE APERTURA INGRESADA EN ENERO DEL AÑO DEL REPORTE+++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $fechaini = strtotime($fecha_inicio);
        $anioini = date("Y", $fechaini);
        $inianio = $anioini . '-01-01';
        $finanio = $anioini . '-01-30';

        $qparapr = "SELECT ccodcta,id_ctb_nomenclatura,cdescrip,SUM(debe)-SUM(haber) saldo,SUM(debe) debe,SUM(haber) haber 
        from ctb_diario_mov 
        WHERE estado=1 AND id_tipopol = 9 AND feccnt BETWEEN '" . $fecha_inicio . "' AND '" . $fecha_fin . "' GROUP BY ccodcta ORDER BY ccodcta";
        $querys = mysqli_query($conexion, $qparapr);
        $apertura[] = [];
        $j = 0;
        while ($fil = mysqli_fetch_array($querys)) {
            $apertura[$j] = $fil;
            $j++;
        }
        $haypartidaapr = ($j == 0) ? false : true;
        $haypartidaapr =  false;

        //COMPROBAR SI HAY PARTIDA DE APERTURA
        /*  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            +++++++ CONSULTA DE REGISTROS ANTES DE LA FECHA QUE SE INGRESO SIN LA PARTIDA DE APERTURA +++++++
            +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */

        $querysali = "SELECT ccodcta,cmov.id,cdescrip,ban.saldo_ini saldo from ctb_nomenclatura cmov 
                            INNER JOIN ctb_bancos ban ON ban.id_nomenclatura=cmov.id 
                            WHERE ban.estado=1 AND cmov.estado=1 ORDER BY ccodcta";
        $querys = mysqli_query($conexion, $querysali);
        $salinidata[] = [];
        $j = 0;
        while ($fil = mysqli_fetch_array($querys)) {
            $salinidata[$j] = $fil;
            $j++;
        }
        $hayanteriores = ($j == 0) ? false : true;
        //VERIFICAR SI TIENE PARTIDA DE APERTURA
        if ($haypartidaapr) {
            $isal = array_search($idcuenta, array_column($apertura, 'id_ctb_nomenclatura'));
            $saldoapr = ($isal !== false) ? ($apertura[$isal]["debe"] - $apertura[$isal]["haber"]) : 0;
        } else {
            $saldoapr = 0;
        }
        //VERIFICAR SI TIENE SALDO ANTERIOR
        if ($hayanteriores) {
            $isal = array_search($idcuenta, array_column($salinidata, 'id'));
            $saldoanterior = ($isal !== false) ? ($salinidata[$isal]["saldo"]) : 0;
        } else {
            $saldoanterior = 0;
        }
        $saldo = $saldoapr + $saldoanterior;
    ?>
        <input type="text" id="condi" value="movimientos" hidden>
        <input type="text" id="file" value="BAN-05" hidden>
        <div class="card row container contenedort">
            <div class="card-header">
                <div class="row">
                    <h4>Movimientos de la cuenta de Bancos</h4>
                    <div class="col-8">
                        <h4><span class="badge text-bg-success"><?php echo $ctbmovdata[0]['cdescrip'] ?></span></h4>
                    </div>
                    <div class="col-4">
                        <label for="salini"><span class="badge text-bg-success">SALDO INICIAL</span></label>
                        <input style="text-align: right;" type="number" step="0.01" class="form-control" id="salini" value="<?php echo $saldo; ?>">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="tbmovimientos" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th title="En tránsito">ET</th>
                            <th style="width: 9%;">Fecha</th>
                            <!-- <th>Partida</th> -->
                            <th>Descripcion</th>
                            <th>Debe</th>
                            <th>Haber</th>
                            <th>Doc.</th>
                            <th>Destino</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="row justify-items-md-center">
                <div class="col align-items-center">
                    <!--  <button type="button" class="btn btn-outline-danger" title="Libro Bancos en pdf" onclick="reportes([[`finicio`,`ffin`,`idcuenta`,`cuenta`],[`codofi`,`fondoid`],[`rcuentas`,`rfondos`,`ragencia`],[]],`pdf`,`libro_bancos`,0)">
                        <i class="fa-solid fa-file-pdf"></i> Pdf
                    </button> -->
                    <button type="button" class="btn btn-outline-success" title="Excel" onclick="reportes([['salini'],[],[],[<?php echo $idcuenta ?>, '<?php echo $fecha_inicio ?>', '<?php echo $fecha_fin ?>', recolectar_checks2(tabla1)]], `xlsx`, `conciliacion`, 1);">
                        <i class="fa-solid fa-file-excel"></i>Excel
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
        <style>
            #tbmovimientos td {
                font-size: 0.75rem;
            }
        </style>
        <script>
            function recolectar_checks2(tabla) {
                checkboxActivados = [];
                tabla.rows().every(function(rowIdx, tableLoop, rowLoop) {
                    var checkbox = $(this.node()).find('input[type="checkbox"]');
                    if (checkbox.is(':checked')) {
                        checkboxActivados.push(checkbox.val());
                    }
                });
                return (checkboxActivados);
            }

            function loadconfig(datas, nomtabla) {
                var tabla = $('#' + nomtabla).on('search.dt').DataTable({
                    "aProcessing": true,
                    "aServerSide": true,
                    "ordering": false,
                    "lengthMenu": [
                        [10, 15, -1],
                        ['10 filas', '15 filas', 'Mostrar todos']
                    ],
                    "ajax": {
                        url: "../src/cruds/crud_bancos.php",
                        type: "POST",
                        beforeSend: function() {
                            loaderefect(1);
                        },
                        data: {
                            'condi': "movimientos_banco",
                            datas
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

            tabla1 = loadconfig([<?php echo $idcuenta ?>, '<?php echo $fecha_inicio ?>', '<?php echo $fecha_fin ?>'], "tbmovimientos");
        </script>
<?php
        break;
}
?>