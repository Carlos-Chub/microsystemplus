<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$idusuario = $_SESSION['id'];
$condi = $_POST["condi"];
switch ($condi) {
    case 'partidas':

?>
        <input type="text" id="condi" value="partidas" hidden>
        <input type="text" id="file" value="ctb001" hidden>
        <div class="text" style="text-align:center">PARTIDAS DE DIARIO</div>
        <div class="card">

            <div class="card-header bg-primary bg-gradient">Partidas de Diario</div>

            <div class="card-body">

                <div class="row">

                    <div class="col-5">
                        <div id="list-example" class="h-100 flex-column align-items-stretch pe-1 border-end">
                            <table class="table nowrap" id="tb_partidas" style="width: 100% !important;">
                                <thead>
                                    <tr style="font-size: 0.80rem;">
                                        <th>Poliza</th>
                                        <th>Fecha</th>
                                        <th>Monto</th>
                                        <th>Acc.</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <!-- CONTENEDOR QUE SE VA A REIMPRIMIR -->
                    <div id="contenedor_section" class="col-7" style="padding-left: 0px !important; padding-right: 7px !important;">

                    </div>

                    <script>
                        var table_partidas_aux;
                        $(document).ready(function() {
                            printdiv3('section_partidas_conta', '#contenedor_section', '0');
                            table_partidas_aux = $('#tb_partidas').on('search.dt').DataTable({
                                "processing": true,
                                "serverSide": true,
                                "sAjaxSource": "../src/server_side/lista_partidas_conta.php",
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
                                            return `<button type="button" class="btn btn-outline-success btn-sm" onclick="printdiv3('section_partidas_conta', '#contenedor_section','${data}')" ><i class="fa-sharp fa-solid fa-eye"></i></i></button>`;
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
                        });
                    </script>
                </div>
            <?php
            break;

        case 'section_partidas_conta':
            $xtra = $_POST["xtra"];
            $ctbmovdata[] = [];
            $querypol = mysqli_query($conexion, "SELECT dia.id, dia.id_ctb_tipopoliza, mov.id_fuente_fondo, dia.numcom, mov.id_ctb_nomenclatura, cue.ccodcta, cue.cdescrip, mov.debe, mov.haber, dia.glosa, dia.feccnt, dia.fecdoc, dia.id_tb_usu, dia.numdoc,dia.editable 
        FROM ctb_mov AS mov 
        INNER JOIN ctb_diario AS dia ON mov.id_ctb_diario = dia.id 
        INNER JOIN ctb_nomenclatura AS cue ON cue.id = mov.id_ctb_nomenclatura WHERE dia.id=$xtra and dia.estado=1");
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
                <div id="ladopoliza">
                    <div class="scrollspy-example-2" tabindex="0">
                        <div class="contenedort container ">
                            <?php if ($xtra != 0) {
                                echo '
                  <div class="row">
                    <label class="text-success">Poliza No. ' . $ctbmovdata[0]['numcom'] . ' </label>
                  </div>';
                            }
                            ?>

                            <div class="row g-2">
                                <div class="col-sm-4 mt-3 mb-3">
                                    <div class="form-floating">
                                        <input <?php echo $disabled; ?> type="date" class="form-control" id="datedoc" value="<?php echo ($xtra == 0) ? date("Y-m-d") : $ctbmovdata[0]["fecdoc"]; ?>">
                                        <label class="text-primary" for="datedoc">Fecha Documento</label>
                                    </div>
                                </div>
                                <div class="col-sm-4 mt-3 mb-3">
                                    <div class="form-floating">
                                        <input <?php echo $disabled; ?> type="date" class="form-control" id="datecont" value="<?php echo ($xtra == 0) ? date("Y-m-d") : $ctbmovdata[0]['feccnt']; ?>">
                                        <label class="text-primary" for="datecont">Fecha Contable</label>
                                    </div>
                                </div>
                                <div class="col-sm-4 mt-3 mb-3">
                                    <div class="form-floating">
                                        <select <?php echo $disabled; ?> class="form-select" id="codofi">
                                            <?php
                                            $userq = ($xtra == 0) ? $idusuario : $ctbmovdata[0]['id_tb_usu'];
                                            $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                  ON ofi.id_agencia = usu.id_agencia WHERE usu.id_usu=" . $userq . "");
                                            while ($ofi = mysqli_fetch_array($ofis)) {
                                                echo '<option value="' . $ofi['id_agencia'] . '" selected>' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                        <label class="text-primary" for="codofi">Agencia</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-sm-5">
                                    <div class="form-floating mb-3">
                                        <input <?php echo $disabled; ?> type="text" class="form-control" id="numdoc" value="<?php echo ($xtra == 0) ? ' ' : $ctbmovdata[0]['numdoc']; ?>">
                                        <label class="text-primary" for="numdoc">Documento</label>
                                    </div>
                                </div>
                                <div class="col-sm-7">
                                    <div class="form-floating mb-2">
                                        <select <?php echo $disabled; ?> class="form-select" id="idtipo_poliza">
                                            <?php
                                            $filtert = ($xtra == 0) ? ' where id=6 OR id=9 OR id=13' : '';
                                            $tipol = mysqli_query($general, "SELECT * FROM `ctb_tipo_poliza`" . $filtert);
                                            while ($tipo = mysqli_fetch_array($tipol)) {
                                                $selec = ($tipo['id'] == $ctbmovdata[0]['id_ctb_tipopoliza']) ? 'selected' : '';
                                                echo '<option ' . $selec . ' value="' . $tipo['id'] . '">' . $tipo['descripcion'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                        <label class="text-primary" for="fondoid">Tipo de Poliza</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-sm-12 mb-2">
                                    <div class="form-floating">
                                        <textarea <?php echo $disabled; ?> class="form-control" id="glosa" style="height: 100px" rows="1"><?php echo ($xtra == 0) ? '' : ($ctbmovdata[0]["glosa"]); ?></textarea>
                                        <label for="glosa">Glosa</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="container contenedort">
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
                                            $incuentas = '<div class="input-group" title="' . $ctbmovdata[$it]["cdescrip"] . '"><input style="display:none;" type="text" class="form-control" id="idcuenta' . ($it + 1) . '" value="' . $ctbmovdata[$it]["id_ctb_nomenclatura"] . '"><input type="text" disabled style="font-size: 0.9rem;" readonly class="form-control" id="cuenta'  . ($it + 1) .  '" value="' . $ctbmovdata[$it]["ccodcta"] . '"><button disabled class="btn btn-outline-success" type="button" onclick="abrir_modal(`#modal_nomenclatura`, `show`, `#id_modal_hidden`, `idcuenta'  . ($it + 1) . ',cuenta'  . ($it + 1) .  '`)" title="Buscar Cuenta contable"><i class="fa fa-magnifying-glass"></i></button></div>';
                                            $ind = '<div class="input-group"><span class="input-group-text">Q</span><input disabled style="text-align: right; font-size: 0.9rem;" type="number" step="0.01" class="form-control" id="debe' . ($it + 1) . '" onblur="validadh(this.id,this.value)" value="' . $ctbmovdata[$it]["debe"] . '"></div>';
                                            $inh = '<div class="input-group"><span class="input-group-text">Q</span><input disabled style="text-align: right; font-size: 0.9rem;" type="number" step="0.01" class="form-control" id="habe' . ($it + 1) . '" onblur="validadh(this.id,this.value)" value="' . $ctbmovdata[$it]["haber"] . '"></div>';
                                            $debe = $debe + $ctbmovdata[$it]["debe"];
                                            $haber = $haber + $ctbmovdata[$it]["haber"];
                                            echo '<tr style="font-size: 0.9rem;">';
                                            $selectfondo = '<select class="form-select" disabled id="fondoid' . ($it + 1) . '">';
                                            $k = 0;
                                            while ($k < count($fondoselect)) {
                                                $selec = ($fondoselect[$k]['id'] == $ctbmovdata[$it]['id_fuente_fondo']) ? 'selected' : '';
                                                $selectfondo .= '<option ' . $selec . ' value="' . $fondoselect[$k]['id'] . '">' . $fondoselect[$k]['descripcion'] . '</option>';
                                                $k++;
                                            }
                                            $selectfondo .= '</select>';
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
                                        <button <?php echo $disabled; ?> id="addRow" class="btn btn-outline-primary" title="Añadir nueva fila" onclick="newrow3()"><i class="fa-solid fa-plus fa-fade fa-sm"></i></button>
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
                        <div class="row justify-items-md-center">
                            <div class="col align-items-center" id="btns_footer">
                                <br>
                                <?php
                                if ($xtra == 0) {
                                    echo '<button type="button" class="btn btn-outline-success" onclick="savecom(' . $idusuario . ',`cpoliza`,0)">
                    <i class="fa fa-floppy-disk"></i> Guardar
                  </button>';
                                } else {
                                    //SOLO PERMITE LA MODIFICACION DE PARTIDAS DE DIARIO Y DE CIERRRE-APERTURA  ***VERSION ANTERIOR
                                    // if ($ctbmovdata[0]["id_ctb_tipopoliza"] == 6 || $ctbmovdata[0]["id_ctb_tipopoliza"] == 9 || $ctbmovdata[0]["id_ctb_tipopoliza"] == 13) {
                                    //   echo '<button id="modpol" type="button" title="Modificar datos de la Poliza" class="btn btn-outline-primary" onclick="changedisabled(`#ladopoliza *`,1); changedisabled(`#btns_footer .btn-outline-primary`,0); changedisabled(`#deletepol`,0); changedisabled(`#idtipo_poliza`,0);">
                                    // <i class="fa fa-pen"></i> Modificar</button>';
                                    // }
                                    //SOLO PERMITE LA MODIFICACION DE PARTIDAS EDITABLES  ***VERSION ACTUAL
                                    if ($ctbmovdata[0]["editable"] == 1) {
                                        echo '<button id="modpol" type="button" title="Modificar datos de la Poliza" class="btn btn-outline-primary" onclick="changedisabled(`#ladopoliza *`,1); changedisabled(`#btns_footer .btn-outline-primary`,0); changedisabled(`#deletepol`,0); changedisabled(`#idtipo_poliza`,0);">
                    <i class="fa fa-pen"></i> Modificar</button>';
                                    }
                                    echo '<button disabled type="button" title="Guardar cambios modificados de la poliza" class="btn btn-outline-success" onclick="savecom(' . $idusuario . ',`upoliza`,' . $xtra . ')">
                    <i class="fa fa-floppy-disk"></i> Actualizar</button>';
                                    echo '<button id="printpol" type="button" title="Imprimir datos de la Poliza" class="btn btn-outline-primary" onclick="reportes([[],[],[],[' . $xtra . ']], `pdf`, `partida_diario`,0)">
                    <i class="fa fa-print"></i> Imprimir</button>';
                                    //SOLO PERMITE LA ELIMINACION DE PARTIDAS DE DIARIO Y DE CIERRRE-APERTURA  ***VERSION ANTERIOR
                                    // if ($ctbmovdata[0]["id_ctb_tipopoliza"] == 6 || $ctbmovdata[0]["id_ctb_tipopoliza"] == 9 || $ctbmovdata[0]["id_ctb_tipopoliza"] == 13) {
                                    //   echo '<button id="deletepol" type="button" title="Eliminar Poliza seleccionada" class="btn btn-outline-danger" onclick="eliminar(' . $xtra . ', `crud_ctb`, 0, `dpoliza`)">
                                    // <i class="fa fa-trash"></i>Eliminar</button>';
                                    // }
                                    //SOLO PERMITE LA ELIMINACION DE PARTIDAS EDITABLES  ***VERSION ACTUAL
                                    if ($ctbmovdata[0]["editable"] == 1) {
                                        echo '<button id="deletepol" type="button" title="Eliminar Poliza seleccionada" class="btn btn-outline-danger" onclick="eliminar(' . $xtra . ', `crud_ctb`, 0, `dpoliza`)">
                    <i class="fa fa-trash"></i>Eliminar</button>';
                                    }
                                }
                                ?>
                                <button type="button" class="btn btn-outline-danger" onclick="printdiv2('#cuadro','0');reinicio(0)">
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

            function newrow3() {
                newrow2(<?php echo json_encode($fondoselect); ?>);
            }
        </script>
<?php
            break;
    }
?>