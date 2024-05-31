<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$idusuario = $_SESSION['id'];
$condi = $_POST["condi"];
switch ($condi) {
    case 'cheques':

        // echo ('<pre>');
        // print_r($ctbmovdata);
        // echo ('</pre>');
?>
        <input type="text" id="condi" value="cheques" hidden>
        <input type="text" id="file" value="ban001" hidden>
        <div class="text" style="text-align:center">EMISIÓN DE CHEQUES</div>
        <div class="card">
            <div class="card-header bg-primary bg-gradient">Emisión de Cheques</div>
            <div class="card-body">
                <div class="row">
                    <!-- SECCION DE LA TABLA DE POLIZAS -->
                    <div class="col-4">
                        <div id="list-example" class="h-100 flex-column align-items-stretch pe-4 border-end">
                            <div class="table-responsive">
                                <table class="table nowrap" id="tb_cheques" style="width: 100% !important;">
                                    <thead>
                                        <tr style="font-size: 0.7rem;">
                                            <th>Poliza</th>
                                            <th>Fecha</th>
                                            <th>Mon. Cheque</th>
                                            <th>Est</th>
                                            <th>Acc.</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-group-divider" style="font-size: 0.6rem !important;">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- FIN DE LA TABLA DE POLIZAS -->
                    <!-- CONTENEDOR QUE SE VA A REIMPRIMIR -->
                    <div id="contenedor_section" class="col-8" style="padding-left: 0px !important; padding-right: 7px !important;">

                    </div>

                    <script>
                        var table_cheques_aux;
                        $(document).ready(function() {
                            printdiv3('section_cheques', '#contenedor_section', '0');
                            table_cheques_aux = $('#tb_cheques').on('search.dt').DataTable({
                                "processing": true,
                                "serverSide": true,
                                "sAjaxSource": "../src/server_side/lista_cheques.php",
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
                                        data: [5],
                                        render: function(data, type, row) {
                                            imp = '';
                                            if (data == 1) {
                                                imp = `<span class="badge bg-success">Sí</span>`;
                                            } else if (data == 2) {
                                                imp = `<span class="badge bg-secondary">Nulo</span>`;
                                            } else {
                                                if (row[6] == '' || row[6] == null) {
                                                    imp = `<span class="badge bg-danger">No</span>`;
                                                } else {
                                                    imp = `<span class="badge bg-warning text-dark">No</span>`;
                                                }
                                            }
                                            return imp;
                                        }
                                    },
                                    {
                                        data: [0],
                                        render: function(data, type, row) {
                                            return `<button type="button" class="btn btn-outline-success btn-sm" onclick="printdiv3('section_cheques', '#contenedor_section','${data}')" ><i class="fa-sharp fa-solid fa-eye"></i></i></button>`;
                                        }
                                    },
                                ],
                                "fnServerParams": function(aoData) {
                                    //PARAMETROS EXTRAS QUE SE LE PUEDEN ENVIAR AL SERVER ASIDE
                                    aoData.push({
                                        "name": "whereextra",
                                        "value": "id_agencia=" + '<?= $_SESSION['id_agencia'] ?>'
                                    });
                                },
                                // "bDestroy": true,
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
                        })
                    </script>

                <?php
                break;

            case 'section_cheques':
                $xtra = $_POST["xtra"];
                $ctbmovdata[] = [];
                $querypol = mysqli_query($conexion, "SELECT cd.id, cc.emitido, cd.numcom, cd.fecdoc, cd.feccnt, ta.id_agencia, ta.cod_agenc, cm.id_fuente_fondo, cc.monchq, cd.numdoc, cc.nomchq, tb.id AS id_banco, cc.id_cuenta_banco, cc.numchq, cd.glosa, cm.id_ctb_nomenclatura, cn.ccodcta, cm.debe, cm.haber, cc.id AS id_reg_cheque, cc.modocheque AS modocheque FROM ctb_diario cd
                INNER JOIN ctb_mov cm ON cd.id=cm.id_ctb_diario
                INNER JOIN ctb_nomenclatura cn ON cm.id_ctb_nomenclatura=cn.id
                INNER JOIN ctb_chq cc ON cd.id=cc.id_ctb_diario
                INNER JOIN ctb_bancos cb ON cc.id_cuenta_banco=cb.id
                INNER JOIN tb_bancos tb ON cb.id_banco=tb.id
                INNER JOIN tb_usuario tu ON cd.id_tb_usu=tu.id_usu
                INNER JOIN tb_agencia ta ON tu.id_agencia=ta.id_agencia
                WHERE cd.estado='1' AND cd.id='$xtra'");
                $j = 0;
                while ($fil = mysqli_fetch_array($querypol)) {
                    $ctbmovdata[$j] = $fil;
                    $j++;
                }
                $disabled = ($xtra == 0) ? '' : ' disabled ';

                //FONDOS
                $fondoselect[] = [];
                $j = 0;
                $querypol = mysqli_query($conexion, "SELECT * FROM `ctb_fuente_fondos` where estado=1");
                while ($fil = mysqli_fetch_array($querypol)) {
                    $fondoselect[$j] = $fil;
                    $j++;
                }
                ?>
                    <!-- INICIO DE SECCION DE CRUD -->
                    <!-- <div class="col-8" style="padding-left: 0px !important; padding-right: 7px !important;"> -->
                    <div class="scrollspy-example-2" tabindex="0">
                        <div class="container contenedort">
                            <div class="row">
                                <div class="col mb-2">
                                    <?php if ($xtra != 0) {
                                        echo '
                                            <div class="row">
                                                <label class="text-success" for="datedoc">Poliza No. ' . $ctbmovdata[0]['numcom'] . ' </label>
                                            </div>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row">
                                <!-- input de fecha de documento -->
                                <div class="col-sm-4" style="padding-right: 0px !important;">
                                    <div class="form-floating mb-3">
                                        <input <?php echo $disabled; ?> type="date" class="form-control" id="datedoc" value="<?php echo ($xtra == 0) ? date("Y-m-d") : $ctbmovdata[0]["fecdoc"]; ?>">
                                        <label class="text-primary" for="datedoc">Fecha Documento</label>
                                    </div>
                                </div>
                                <!-- input de fecha  contable -->
                                <div class="col-sm-4" style="padding-right: 0px !important;">
                                    <div class="form-floating mb-3">
                                        <input <?php echo $disabled; ?> type="date" class="form-control" id="datecont" value="<?php echo ($xtra == 0) ? date("Y-m-d") : $ctbmovdata[0]['feccnt']; ?>">
                                        <label class="text-primary" for="datecont">Fecha Contable</label>
                                    </div>
                                </div>
                                <!-- input de agencia -->
                                <div class="col-sm-4">
                                    <div class="form-floating mb-3">
                                        <input disabled type="text" class="form-control" id="codofi2" placeholder="Agencia" value="<?= $_SESSION['agencia'] . ' - ' . $_SESSION['nomagencia'] ?>">
                                        <input disabled hidden type="text" class="form-control" id="codofi" placeholder="Agencia" value="<?= $_SESSION['id_agencia'] ?>">
                                        <label class="text-primary" for="codofi">Agencia</label>
                                    </div>
                                </div>
                                <!-- select de fondos -->
                            </div>
                            <!-- row de cantiad, negociable y numdoc -->
                            <div class="row">
                                <div class="col-sm-4" style="padding-right: 0px !important;">
                                    <div class="form-floating mb-3">
                                        <input <?php echo $disabled; ?> type="number" class="form-control" id="cantidad" value="<?php echo ($xtra == 0) ? '0' : $ctbmovdata[0]['monchq']; ?>" onchange="cantidad_a_letras()" step="0.01">
                                        <label class="text-primary" for="cantidad">Cantidad</label>
                                    </div>
                                </div>
                                <div class="col-sm-4" style="padding-right: 0px !important;">
                                    <div class="form-floating mb-3">
                                        <select <?php echo $disabled; ?> class="form-select" id="negociable">
                                            <?php if ($xtra != 0) { ?>
                                                <option <?php if ($ctbmovdata[0]['modocheque'] == 0) echo 'selected'; ?> value="0">No Negociable</option>
                                                <option <?php if ($ctbmovdata[0]['modocheque'] == 1) echo 'selected'; ?> value="1">Negociable</option>
                                            <?php } else { ?>
                                                <option selected value="0">No Negociable</option>
                                                <option value="1">Negociable</option>
                                            <?php } ?>
                                        </select>
                                        <label class="text-primary" for="negociable">Tipo cheque</label>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-floating mb-3">
                                        <input <?php echo $disabled; ?> type="text" class="form-control" id="numdoc" value="<?php echo ($xtra == 0) ? 'X' : $ctbmovdata[0]['numdoc']; ?>">
                                        <label class="text-primary" for="numdoc">No. de Documento</label>
                                    </div>
                                </div>
                            </div>
                            <!-- input de paguese a la orden de -->
                            <div class="row">
                                <div class="col-sm-12 mb-3">
                                    <div class="form-floating">
                                        <input <?php echo $disabled; ?> type="text" class="form-control" id="paguese" value="<?php echo ($xtra == 0) ? '' : $ctbmovdata[0]['nomchq']; ?>">
                                        <label for="paguese">Paguese a la orden de</label>
                                    </div>
                                </div>
                            </div>
                            <!-- input para numeros en letras -->
                            <div class="row">
                                <div class="col-sm-12 mb-3">
                                    <div class="form-floating">
                                        <input disabled type="text" class="form-control" id="numletras">
                                        <label for="numletras">La suma de (Q)</label>
                                    </div>
                                </div>
                            </div>
                            <!-- fila de seleccion de bancos -->
                            <div class="row">
                                <div class="col-sm-4" style="padding-right: 0px !important;">
                                    <div class="form-floating mb-3">
                                        <select <?php echo $disabled; ?> class="form-select" id="bancoid" onchange="buscar_cuentas()">
                                            <?php
                                            $bancos = mysqli_query($conexion, "SELECT * FROM tb_bancos WHERE estado='1'");
                                            while ($banco = mysqli_fetch_array($bancos)) {
                                                $selec = ($banco['id'] == $ctbmovdata[0]['id_banco']) ? 'selected' : '';
                                                echo '<option ' . $selec . ' value="' . $banco['id'] . '">' . $banco['id'] . " - " . $banco['nombre'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                        <label class="text-primary" for="bancoid">Banco</label>
                                    </div>
                                </div>
                                <div class="col-sm-4" style="padding-right: 0px !important;">
                                    <div class="form-floating mb-3">
                                        <!-- id de cuenta para edicion -->
                                        <input disabled hidden type="text" class="form-control" id="id_cuenta_b" value="<?php echo ($xtra == 0) ? '' : $ctbmovdata[0]['id_cuenta_banco']; ?>">

                                        <!-- select normal -->
                                        <select <?php echo $disabled; ?> class="form-select" id="cuentaid" onchange="cheque_automatico(this.value,0)">
                                            <option value="">Seleccione una cuenta</option>
                                        </select>
                                        <label class="text-primary" for="cuentaid">No. de Cuenta</label>
                                    </div>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <div class="form-floating">
                                        <input <?php echo $disabled; ?> type="number" class="form-control" id="numcheque" value="<?php echo ($xtra == 0) ? '0' : $ctbmovdata[0]['numchq']; ?>">
                                        <label class="text-primary" for="numcheque">No. de Cheque</label>
                                    </div>
                                </div>
                            </div>
                            <!-- input de glosa -->
                            <div class="row">
                                <div class="col-sm-12 mb-2">
                                    <div class="form-floating">
                                        <textarea <?php echo $disabled; ?> class="form-control" id="glosa" style="height: 100px" rows="1"><?php echo ($xtra == 0) ? '' : ($ctbmovdata[0]["glosa"]); ?></textarea>
                                        <label for="glosa">Concepto</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="container contenedort" id="ladopoliza">

                            <!-- seccion de cuentas contables o el detalle del libro de diario -->
                            <div class="row">
                                <table id="Cuentas" class="display mb-2" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th style="width:1%;">No.</th>
                                            <th style="width:0.01%;"></th>
                                            <th>Cuenta</th>
                                            <th style="width:25%;">Debe</th>
                                            <th style="width:25%;">Haber</th>
                                            <th style="width:20%;">Fondos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($xtra != 0) {
                                            $it = 0;
                                            $debe = 0;
                                            $haber = 0;
                                            while ($it < count($ctbmovdata)) {
                                                $incuentas = '<div class="input-group"><input style="display:none;" type="text" class="form-control" id="idcuenta' . ($it + 1) . '" value="' . $ctbmovdata[$it]["id_ctb_nomenclatura"] . '"><input type="text" disabled style="font-size: 0.9rem;" readonly class="form-control" id="cuenta'  . ($it + 1) .  '" value="' . $ctbmovdata[$it]["ccodcta"] . '"><button disabled class="btn btn-outline-success" type="button" onclick="abrir_modal(`#modal_nomenclatura`, `#id_modal_hidden`, `idcuenta'  . ($it + 1) . ',cuenta'  . ($it + 1) .  '/A,A//#/#/#/#`)" title="Buscar Cuenta contable"><i class="fa fa-magnifying-glass"></i></button></div>';
                                                $ind = '<div class="input-group"><span class="input-group-text">Q</span><input disabled style="text-align: right; font-size: 0.9rem;" type="number" step="0.01" class="form-control" id="debe' . ($it + 1) . '" onblur="validadh(this.id,this.value)" value="' . $ctbmovdata[$it]["debe"] . '"></div>';
                                                $inh = '<div class="input-group"><span class="input-group-text">Q</span><input disabled style="text-align: right; font-size: 0.9rem;" type="number" step="0.01" class="form-control" id="habe' . ($it + 1) . '" onblur="validadh(this.id,this.value)" value="' . $ctbmovdata[$it]["haber"] . '"></div>';
                                                $debe = $debe + $ctbmovdata[$it]["debe"];
                                                $haber = $haber + $ctbmovdata[$it]["haber"];
                                                $selectfondo = '<select class="form-select" disabled id="fondoid' . ($it + 1) . '">';
                                                $k = 0;
                                                while ($k < count($fondoselect)) {
                                                    $selec = ($fondoselect[$k]['id'] == $ctbmovdata[$it]['id_fuente_fondo']) ? 'selected' : '';
                                                    $selectfondo .= '<option ' . $selec . ' value="' . $fondoselect[$k]['id'] . '">' . $fondoselect[$k]['descripcion'] . '</option>';
                                                    $k++;
                                                }
                                                $selectfondo .= '</select>';
                                                echo '<tr style="font-size: 0.9rem;">';
                                                echo '<td class="ps-0">' . ($it + 1) . '</td>';
                                                echo '<td class="ps-0">' . ($it + 1) . '</td>';
                                                echo '<td class="ps-0">' . $incuentas . '</td>';
                                                echo '<td class="ps-0">' . $ind . '</td>';
                                                echo '<td class="ps-0">' . $inh . '</td>';
                                                echo '<td class="ps-0">' . $selectfondo . '</td>';
                                                echo '</tr>';
                                                $it++;
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <div class="col-10">
                                    <div class="row">
                                        <div class="col-sm-4 mb-2">
                                            <button <?php echo $disabled; ?> id="addRow" class="btn btn-outline-primary" title="Añadir nueva fila" onclick="newrow3()"><i class="fa-solid fa-plus"></i></button>
                                            <button <?php echo $disabled; ?> id="deletefila" class="btn btn-outline-danger" title="Eliminar fila" onclick="deletefila()"><i class="fa-solid fa-trash"></i></button>
                                        </div>
                                        <div class="col-sm-4 mb-2 ps-0">
                                            <div class="input-group" style="width: 88%;float: right;">
                                                <span class="input-group-text">Q</span>
                                                <input id="totdebe" style="text-align: right;" type="number" step="0.01" class="form-control" readonly value="<?php echo ($xtra == 0) ? '' : $debe; ?>">
                                            </div>
                                        </div>
                                        <div class="col-sm-4 mb-2 ps-0">
                                            <div class="input-group" style="width: 87%;float: left;">
                                                <span class="input-group-text">Q</span>
                                                <input id="tothaber" style="text-align: right;" type="number" step="0.01" class="form-control" readonly value="<?php echo ($xtra == 0) ? '' : $haber; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        if ($xtra != 0) {
                            if ($ctbmovdata[0]['numchq'] == "") { ?>
                                <div class=" contenedort container">
                                    <div class="row">
                                        <div class="col mt-1 mb-1">
                                            <div class="alert alert-success" role="alert" style="margin-bottom: 0px !important;" id="mensaje">
                                                <h4 class="alert-heading">IMPORTANTE!</h4>
                                                <p>Debe presionar el boton de modificar, luego digitar un número de cheque y documento, seguidamente presionar el boton actualizar, esperar a que se graben los cambios, luego le aparecera el boton de imprimir y asi terminar con el proceso</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php }
                        }; ?>
                        <div class="row justify-items-md-center" id="ladopoliza">
                            <div class="col align-items-center" id="btns_footer">
                                <br>
                                <?php
                                if ($xtra == 0) {
                                    echo '<button type="button" class="btn btn-outline-success" onclick="savecom(' . $idusuario . ',`create_cheques`,0)">
                                            <i class="fa fa-floppy-disk"></i> Guardar
                                        </button>';
                                } else {
                                    if ($ctbmovdata[0]['emitido'] != 2) {
                                        echo '<button id="modpol" type="button" title="Modificar datos de la Poliza" class="btn btn-outline-primary" onclick="changedisabled(`#ladopoliza *`,1);habilitar_deshabilitar([`datedoc`,`datecont`,`cantidad`,`numdoc`,`paguese`,`bancoid`,`cuentaid`,`numcheque`,`glosa`,`negociable`], []);changedisabled(`#btns_footer .btn-outline-primary`,0);changedisabled(`#deletepol`,0); cheque_automatico(' . $ctbmovdata[0]['id_cuenta_banco'] . ',' . $ctbmovdata[0]['id_reg_cheque'] . ')">
                                                <i class="fa fa-pen"></i> Modificar
                                            </button>';
                                        echo '<button disabled type="button" title="Guardar cambios modificados de la poliza" class="btn btn-outline-success" onclick="savecom(' . $idusuario . ',`update_cheques`,' . $xtra . ')">
                                                <i class="fa fa-floppy-disk"></i> Actualizar
                                            </button>';
                                    }
                                    if ($ctbmovdata[0]['emitido'] == 0 && $ctbmovdata[0]['numchq'] != "") {
                                        echo '<button id="printpol" type="button" title="Imprimir datos de la Poliza" class="btn btn-outline-primary" onclick="reportes([[],[],[],[' . $xtra . ']], `pdf`, 13,0,1)">
                                                <i class="fa fa-print"></i>Imprimir
                                            </button>';
                                    }
                                    if ($ctbmovdata[0]['emitido'] < 2) {
                                        echo '<button type="button" title="Anular un cheque" class="btn btn-outline-secondary" onclick="savecom(' . $idusuario . ',`anular_cheques`,' . $xtra . ')">
                                                    <i class="fa fa-floppy-disk"></i> Anular
                                                </button>';
                                    }
                                    echo '<button id="deletepol" type="button" title="Eliminar Poliza seleccionada" class="btn btn-outline-danger" onclick="eliminar(' . $xtra . ', `crud_bancos`, 0, `delete_cheques`)">
                                            <i class="fa fa-trash"></i>Eliminar
                                        </button>';
                                }
                                ?>
                                <button type="button" class="btn btn-outline-danger" onclick="printdiv3('section_cheques', '#contenedor_section','0'); reinicio(0)">
                                    <i class="fa-solid fa-ban"></i> Cancelar
                                </button>
                                <button type="button" class="btn btn-outline-warning" onclick="salir()">
                                    <i class="fa-solid fa-circle-xmark"></i> Salir
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- </div> -->
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                var t = $('#Cuentas').dataTable({
                    "searching": false,
                    "paging": false,
                    "ordering": false,
                    "info": false,
                    "language": {
                        "zeroRecords": " ",
                    },
                }).DataTable();
                $('#Cuentas tbody').on('click', 'tr', function() {
                    if ($(this).hasClass('selected')) {
                        $(this).removeClass('selected');
                    } else {
                        $('.selected').removeClass('selected');
                        $(this).addClass('selected');
                    }
                });
                reinicio(0);
                var column = t.column(1);
                column.visible(false);
                <?php
                if ($xtra != 0) {
                    echo 'reinicio(' . count($ctbmovdata) . ');';
                }
                ?>
                //ejecutar busqueda de cuentas
                buscar_cuentas();
                //convertir a letras
                cantidad_a_letras();
            });

            function newrow3() {
                newrow2(<?php echo json_encode($fondoselect); ?>);
            }
        </script>
    <?php
                break;
            case 'deposito_bancos':
    ?>
        <input type="text" id="condi" value="deposito_bancos" hidden>
        <input type="text" id="file" value="ban001" hidden>
        <div class="text" style="text-align:center">DEPOSITO A BANCOS</div>
        <div class="card">
            <div class="card-header bg-primary bg-gradient">Depositos a Bancos</div>
            <div class="card-body">
                <div class="row">
                    <!-- SECCION DE LA TABLA DE POLIZAS -->
                    <div class="col-4">
                        <div id="list-example" class="h-100 flex-column align-items-stretch pe-4 border-end">
                            <div class="table-responsive">
                                <table class="table nowrap" id="tb_depositos" style="width: 100% !important;">
                                    <thead>
                                        <tr style="font-size: 0.7rem;">
                                            <th>Poliza</th>
                                            <th>Fecha</th>
                                            <th>Monto</th>
                                            <th>Acc.</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-group-divider" style="font-size: 0.6rem !important;">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- FIN DE LA TABLA DE POLIZAS -->
                    <!-- CONTENEDOR QUE SE VA A REIMPRIMIR -->
                    <div id="contenedor_section" class="col-8" style="padding-left: 0px !important; padding-right: 7px !important;">

                    </div>

                    <script>
                        var table_cheques_aux2;
                        $(document).ready(function() {
                            printdiv3('section_partidas_deposito', '#contenedor_section', '0');
                            table_cheques_aux2 = $('#tb_depositos').on('search.dt').DataTable({
                                "processing": true,
                                "serverSide": true,
                                "sAjaxSource": "../src/server_side/list_depositos_bancos.php",
                                columns: [{
                                        data: [1]
                                    },
                                    {
                                        data: [2]
                                    },
                                    {
                                        data: [3]
                                    },
                                    {
                                        data: [0],
                                        render: function(data, type, row) {
                                            return `<button type="button" class="btn btn-outline-success btn-sm" onclick="printdiv3('section_partidas_deposito', '#contenedor_section','${data}')" ><i class="fa-sharp fa-solid fa-eye"></i></i></button>`;
                                        }
                                    },
                                ],
                                "fnServerParams": function(aoData) {
                                    //PARAMETROS EXTRAS QUE SE LE PUEDEN ENVIAR AL SERVER ASIDE
                                    aoData.push({
                                        "name": "whereextra",
                                        "value": "id_agencia=" + '<?= $_SESSION['id_agencia'] ?>'
                                    });
                                },
                                // "bDestroy": true,
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
                        })
                    </script>

                <?php
                break;

            case 'section_partidas_deposito':
                $xtra = $_POST["xtra"];
                $ctbmovdata[] = [];
                $querypol = mysqli_query($conexion, "SELECT cd.id,cd.id_ctb_tipopoliza, cd.numcom, cd.fecdoc, cd.feccnt, ta.id_agencia, ta.cod_agenc, cm.id_fuente_fondo,cd.numdoc, cd.glosa, cm.id_ctb_nomenclatura, cn.ccodcta, cm.debe, cm.haber FROM ctb_diario cd
                                            INNER JOIN ctb_mov cm ON cd.id=cm.id_ctb_diario
                                            INNER JOIN ctb_nomenclatura cn ON cm.id_ctb_nomenclatura=cn.id
                                            INNER JOIN tb_usuario tu ON cd.id_tb_usu=tu.id_usu
                                            INNER JOIN tb_agencia ta ON tu.id_agencia=ta.id_agencia
                                            WHERE cd.estado='1' AND cd.id='$xtra' ORDER BY cm.haber,cm.id_ctb_nomenclatura");
                $j = 0;
                while ($fil = mysqli_fetch_array($querypol)) {
                    $ctbmovdata[$j] = $fil;
                    $j++;
                }
                $disabled = ($xtra == 0) ? '' : ' disabled ';
                $datafondos[] = [];
                $fons = mysqli_query($conexion, "SELECT * FROM `ctb_fuente_fondos` where estado=1");
                $j = 0;
                while ($fon = mysqli_fetch_array($fons)) {
                    $datafondos[$j] = $fon;
                    $j++;
                }
                if ($j == 0) {
                    echo '<div class="alert alert-danger" role="alert">No hay fuentes de fondos disponibles</div>';
                    return;
                }
                ?>
                    <!-- INICIO DE SECCION DE CRUD -->
                    <!-- <div class="col-8" style="padding-left: 0px !important; padding-right: 7px !important;"> -->
                    <div class="scrollspy-example-2" tabindex="0">
                        <div class="container contenedort">
                            <div class="row">
                                <div class="col mb-2">
                                    <?php if ($xtra != 0) {
                                        echo '<div class="row">
                                                    <label class="text-success">Poliza No. ' . $ctbmovdata[0]['numcom'] . ' </label>
                                              </div>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row">
                                <!-- input de fecha de documento -->
                                <div class="col-sm-3" style="padding-right: 0px !important;">
                                    <div class="form-floating mb-3">
                                        <input <?php echo $disabled; ?> type="date" class="form-control" id="datedoc" value="<?php echo ($xtra == 0) ? date("Y-m-d") : $ctbmovdata[0]["fecdoc"]; ?>">
                                        <label class="text-primary" for="datedoc">Fecha Documento</label>
                                    </div>
                                </div>
                                <!-- input de fecha  contable -->
                                <div class="col-sm-3" style="padding-right: 0px !important;">
                                    <div class="form-floating mb-3">
                                        <input <?php echo $disabled; ?> type="date" class="form-control" id="datecont" value="<?php echo ($xtra == 0) ? date("Y-m-d") : $ctbmovdata[0]['feccnt']; ?>">
                                        <label class="text-primary" for="datecont">Fecha Contable</label>
                                    </div>
                                </div>
                                <!-- input de agencia -->
                                <div class="col-sm-3" style="padding-right: 0px !important;">
                                    <div class="form-floating mb-3">
                                        <input disabled type="text" class="form-control" id="codofi2" placeholder="Agencia" value="<?= $_SESSION['agencia'] . ' - ' . $_SESSION['nomagencia'] ?>">
                                        <input disabled hidden type="text" class="form-control" id="codofi" placeholder="Agencia" value="<?= $_SESSION['id_agencia'] ?>">
                                        <label class="text-primary" for="codofi">Agencia</label>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-floating mb-2">
                                        <select <?php echo $disabled; ?> class="form-select" id="idtipo_poliza">
                                            <?php
                                            $tipol = mysqli_query($general, "SELECT * FROM `ctb_tipo_poliza` where id=10 OR id=11 OR id=12");
                                            while ($tipo = mysqli_fetch_array($tipol)) {
                                                $selec = ($tipo['id'] == $ctbmovdata[0]['id_ctb_tipopoliza']) ? ' selected' : '';
                                                echo '<option ' . $selec . ' value="' . $tipo['id'] . '">' . utf8_encode($tipo['descripcion']) . '</option>';
                                            }
                                            ?>
                                        </select>
                                        <label class="text-primary" for="idtipo_poliza">Tipo de Poliza</label>
                                    </div>
                                </div>

                            </div>
                            <!-- input de glosa -->
                            <div class="row">
                                <div class="col-sm-9 mb-2">
                                    <div class="form-floating">
                                        <textarea <?php echo $disabled; ?> class="form-control" id="glosa" style="height: 100px" rows="1"><?php echo ($xtra == 0) ? '' : ($ctbmovdata[0]["glosa"]); ?></textarea>
                                        <label for="glosa">Concepto</label>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-floating mb-3">
                                        <input <?php echo $disabled; ?> type="text" class="form-control" id="numdoc" value="<?php echo ($xtra == 0) ? 'X' : $ctbmovdata[0]['numdoc']; ?>">
                                        <label class="text-primary" for="numdoc">No. Documento</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="container contenedort" id="ladopoliza">

                            <!-- seccion de cuentas contables o el detalle del libro de diario -->
                            <div class="row">
                                <table id="Cuentas" class="display mb-2" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th style="width:1%;">No</th>
                                            <th style="width:0.01%;"></th>
                                            <th>Cuenta</th>
                                            <th style="width:25%;">Debe</th>
                                            <th style="width:25%;">Haber</th>
                                            <th style="width:20%;">Fondo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                                        if ($xtra != 0) {
                                            $it = 0;
                                            $debe = 0;
                                            $haber = 0;
                                            while ($it < count($ctbmovdata)) {
                                                $incuentas = '<div class="input-group"><input style="display:none;" type="text" class="form-control" id="idcuenta' . ($it + 1) . '" value="' . $ctbmovdata[$it]["id_ctb_nomenclatura"] . '"><input type="text" disabled style="font-size: 0.9rem;" readonly class="form-control" id="cuenta'  . ($it + 1) .  '" value="' . $ctbmovdata[$it]["ccodcta"] . '"><button disabled class="btn btn-outline-success" type="button" onclick="abrir_modal(`#modal_nomenclatura`, `#id_modal_hidden`, `idcuenta'  . ($it + 1) . ',cuenta'  . ($it + 1) .  '/A,A//#/#/#/#`)" title="Buscar Cuenta contable"><i class="fa fa-magnifying-glass"></i></button></div>';
                                                $ind = '<div class="input-group"><span class="input-group-text">Q</span><input disabled style="text-align: right; font-size: 0.9rem;" type="number" step="0.01" class="form-control" id="debe' . ($it + 1) . '" onblur="validadh(this.id,this.value)" value="' . $ctbmovdata[$it]["debe"] . '"></div>';
                                                $inh = '<div class="input-group"><span class="input-group-text">Q</span><input disabled style="text-align: right; font-size: 0.9rem;" type="number" step="0.01" class="form-control" id="habe' . ($it + 1) . '" onblur="validadh(this.id,this.value)" value="' . $ctbmovdata[$it]["haber"] . '"></div>';
                                                $debe = $debe + $ctbmovdata[$it]["debe"];
                                                $haber = $haber + $ctbmovdata[$it]["haber"];
                                                $fondoselect = '<select class="form-select" disabled id="fondoid' . ($it + 1) . '">';
                                                $k = 0;
                                                while ($k < count($datafondos)) {
                                                    $selec = ($datafondos[$k]['id'] == $ctbmovdata[$it]['id_fuente_fondo']) ? 'selected' : '';
                                                    $fondoselect .= '<option ' . $selec . ' value="' . $datafondos[$k]['id'] . '">' . $datafondos[$k]['descripcion'] . '</option>';
                                                    $k++;
                                                }
                                                $fondoselect .= '</select>';
                                                echo '<tr style="font-size: 0.9rem;">';
                                                echo '<td class="ps-0">' . ($it + 1) . '</td>';
                                                echo '<td class="ps-0">' . ($it + 1) . '</td>';
                                                echo '<td class="ps-0">' . $incuentas . '</td>';
                                                echo '<td class="ps-0">' . $ind . '</td>';
                                                echo '<td class="ps-0">' . $inh . '</td>';
                                                echo '<td class="ps-0">' . $fondoselect . '</td>';
                                                echo '</tr>';
                                                $it++;
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <div class="col-10">
                                    <div class="row">
                                        <div class="col-sm-4 mb-2">
                                            <button <?php echo $disabled; ?> id="addRow" class="btn btn-outline-primary" title="Añadir nueva fila" onclick="nuevafila()"><i class="fa-solid fa-plus"></i></button>
                                            <button <?php echo $disabled; ?> id="deletefila" class="btn btn-outline-danger" title="Eliminar fila" onclick="deletefila()"><i class="fa-solid fa-trash"></i></button>
                                        </div>
                                        <div class="col-sm-4 mb-2 ps-0">
                                            <div class="input-group" style="width: 88%;float: right;">
                                                <span class="input-group-text">Q</span>
                                                <input id="totdebe" style="text-align: right;" type="number" step="0.01" class="form-control" readonly value="<?php echo ($xtra == 0) ? '' : $debe; ?>">
                                            </div>
                                        </div>
                                        <div class="col-sm-4 mb-2 ps-0">
                                            <div class="input-group" style="width: 87%;float: left;">
                                                <span class="input-group-text">Q</span>
                                                <input id="tothaber" style="text-align: right;" type="number" step="0.01" class="form-control" readonly value="<?php echo ($xtra == 0) ? '' : $haber; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="row justify-items-md-center" id="ladopoliza">
                            <div class="col align-items-center" id="btns_footer">
                                <br>
                                <?php
                                if ($xtra == 0) {
                                    echo '<button type="button" class="btn btn-outline-success" onclick="savecomdeposito(`create_depositos_bancos`,0)">
                                                <i class="fa fa-floppy-disk"></i> Guardar
                                            </button>';
                                } else {
                                    echo '<button id="modpol" type="button" title="Modificar datos de la Poliza" class="btn btn-outline-primary" onclick="changedisabled(`#ladopoliza *`,1);habilitar_deshabilitar([`datedoc`,`datecont`,`numdoc`,`glosa`,`idtipo_poliza`], []);changedisabled(`#btns_footer .btn-outline-primary`,0);changedisabled(`#deletepol`,0);">
                                                <i class="fa fa-pen"></i> Modificar
                                            </button>';
                                    echo '<button disabled type="button" title="Guardar cambios modificados de la poliza" class="btn btn-outline-success" onclick="savecomdeposito(`update_depositos_bancos`,' . $xtra . ')">
                                                <i class="fa fa-floppy-disk"></i> Actualizar
                                            </button>';
                                    echo '<button id="printpol" type="button" title="Imprimir datos de la Poliza" class="btn btn-outline-primary" onclick="reportes([[],[],[],[' . $xtra . ']], `pdf`, `../../conta/reportes/partida_diario`,0)">
                                                <i class="fa fa-print"></i> Imprimir
                                            </button>';
                                    echo '<button id="deletepol" type="button" title="Eliminar Poliza seleccionada" class="btn btn-outline-danger" onclick="eliminar(' . $xtra . ', `crud_bancos`, 0, `delete_depositos_bancos`)">
                                                <i class="fa fa-trash"></i>Eliminar
                                            </button>';
                                }
                                ?>
                                <button type="button" class="btn btn-outline-danger" onclick="printdiv3('section_partidas_deposito', '#contenedor_section','0'); reinicio(0)">
                                    <i class="fa-solid fa-ban"></i> Cancelar
                                </button>
                                <button type="button" class="btn btn-outline-warning" onclick="salir()">
                                    <i class="fa-solid fa-circle-xmark"></i> Salir
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- </div> -->
                    <!-- FIN DE SECCION DE CRUD -->
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                var t = $('#Cuentas').dataTable({
                    "searching": false,
                    "paging": false,
                    "ordering": false,
                    "info": false,
                    "language": {
                        "zeroRecords": " ",
                    },
                }).DataTable();
                $('#Cuentas tbody').on('click', 'tr', function() {
                    if ($(this).hasClass('selected')) {
                        $(this).removeClass('selected');
                    } else {
                        $('.selected').removeClass('selected');
                        $(this).addClass('selected');
                    }
                });
                reinicio(0);
                var column = t.column(1);
                column.visible(false);
                <?php
                if ($xtra != 0) {
                    echo 'reinicio(' . count($ctbmovdata) . ');';
                }
                ?>
            });
            var dataf = <?php echo json_encode($datafondos); ?>;

            function nuevafila() {
                newrow2(dataf);
                $("#Cuentas tr td").addClass("ps-0");
            }

            function savecomdeposito(condio, idr) {
                loaderefect(1)
                if (validanewrow() == 0) {
                    Swal.fire({
                        icon: 'error',
                        title: '¡ERROR!',
                        text: 'Hay registros sin completarse, verique que se hayan ingresado montos y se hayan seleccionado cuentas.'
                    })
                    loaderefect(0);
                    return;
                }
                var datainputsd = [''];
                var datainputsh = [''];
                var datacuentas = [''];
                var datafondos = [''];
                var datainputs = [];
                var dataselects = [];
                var rows = 1;
                var fila = 0;
                var pibo = 0;
                while (rows <= countid) {
                    var mm = datoseliminados.includes(rows);
                    if (mm == false) {
                        pibo = getinputsval(['debe' + (rows), 'habe' + (rows), 'idcuenta' + (rows), 'fondoid' + (rows)]);
                        datainputsd[fila] = (pibo[0] == "") ? 0 : pibo[0];
                        datainputsh[fila] = (pibo[1] == "") ? 0 : pibo[1];
                        datacuentas[fila] = pibo[2];
                        datafondos[fila] = pibo[3];
                        fila++;
                    }
                    rows++;
                }
                datainputs = getinputsval(['datedoc', 'datecont', 'codofi', 'codofi2', 'numdoc', 'glosa', 'totdebe', 'tothaber', 'idtipo_poliza'])
                generico([datainputs, datainputsd, datainputsh, datacuentas, datafondos], [], [], condio, idr, [idr]);
            }
        </script>
<?php
                break;
        }
