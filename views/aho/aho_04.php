<?php
session_start();
include '../../includes/BD_con/db_con.php';
include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];

switch ($condi) {
        //-Adicion de certificados
    case 'AddCertif':
        $id  = $_POST["xtra"];
        $codusu = $_SESSION['id'];
        $datoscli = mysqli_query($conexion, " SELECT cta.ccodcli,cta.estado,cta.nlibreta,cli.no_tributaria num_nit,cli.short_name,tip.tipcuen,tip.diascalculo 
                        FROM `ahomcta` cta INNER JOIN tb_cliente cli ON cli.idcod_cliente=cta.ccodcli
                        INNER JOIN ahomtip tip ON tip.ccodtip=SUBSTR(cta.ccodaho,7,2)
                        WHERE `ccodaho`='$id'   ");
        $bandera = "Cuenta de ahorro no existe";
        while ($da = mysqli_fetch_array($datoscli, MYSQLI_ASSOC)) {
            $idcli = ($da["ccodcli"]);
            $dayscalc = ($da["diascalculo"]);
            $nit = ($da["num_nit"]);
            $nlibreta = ($da["nlibreta"]);
            $estado = ($da["estado"]);
            $tipcuen = ($da["tipcuen"]);
            $nombre = utf8_encode($da["short_name"]);
            $bandera =  "";
            if ($estado != "A") {
                $bandera =  "Cuenta de ahorros Inactiva";
            }
            if ($tipcuen != "pf") {
                $bandera =  "Cuenta de ahorros no es de tipo Plazo fijo";
            }

            // $bandera = "";
        }
        $hoy = date("Y-m-d");
        $fec1anio = strtotime('+365 day', strtotime($hoy));
        $fec1anio = date('Y-m-j', $fec1anio);
?>
        <!--Aho_0_ApertCuenAhor Inicio de Ahorro Sección 0 Apertura de Cuenta-->
        <div class="text" style="text-align:center">ADICION DE CERTIFICADOS DE PLAZO FIJO</div>
        <input type="text" id="condi" value="AddCertif" hidden>
        <input type="text" id="file" value="aho_04" hidden>
        <input type="text" id="dayscalc" value="<?php echo ($bandera == "") ? $dayscalc : 365; ?>" hidden>
        <div class="card">
            <div class="card-header">Adicion de certificados</div>
            <div class="card-body">
                <div class="accordion" id="accordionExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" aria-expanded="true" aria-controls="collapseOne">
                                IDENTIFICACION DEL CLIENTE
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="row mb-1">
                                    <div class="col-sm-5">
                                        <div>
                                            <span class="input-group-addon col-8">Cuenta de ahorro</span>
                                            <input type="text" aria-label="Cuenta" id="codaho" class="form-control  col" placeholder="" value="<?php if ($bandera == "") echo $id; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <br>
                                        <button title="Buscar cuenta" class="btn btn-outline-secondary" type="button" id="button-addon1" data-bs-toggle="modal" data-bs-target="#findahomcta">
                                            <i class="fa fa-magnifying-glass"></i>
                                        </button>
                                    </div>
                                </div>
                                <!--Aho_0_ApertCuenAhor Búsqueda NIT-->
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <div>
                                            <span class="input-group-addon col-8">Cliente</span>
                                            <input type="text" class="form-control " id="nomcli" placeholder="" value="<?php if ($bandera == "") echo $nombre; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div>
                                            <span class="input-group-addon col-8">Codigo de cliente</span>
                                            <input type="text" class="form-control " id="codcli" placeholder="" value="<?php if ($bandera == "") echo $idcli; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div>
                                            <span class="input-group-addon col-8">NIT</span>
                                            <input type="text" class="form-control " id="nit" placeholder="" value="<?php if ($bandera == "") echo $nit; ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-2">
                                        <span class="input-group-addon col-8">Certificado</span>
                                        <input type="text" aria-label="Certificado" id="certif" class="form-control  col" placeholder="" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if ($bandera != "" && $id != "0") {
                            echo '<div class="alert alert-danger" role="alert">' . $bandera . '</div>';
                        }
                        ?>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button" type="button" aria-expanded="false" aria-controls="collapseTwo">
                                DATOS DE LA CUENTA
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Monto</span>
                                        <input type="float" class="form-control" id="monapr" placeholder="0.00" required="required">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Plazo</span>
                                        <input type="number" class="form-control" id="plazo" placeholder="365" required="required" onblur="calcfecven(2)">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Dias de gracia</span>
                                        <input type="float" class="form-control" id="gracia" placeholder="0" required="required" value="0">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Interes %</span>
                                        <input type="float" class="form-control" id="tasint" placeholder="0.00" required="required" onblur="calcfecven(1)">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Fecha Apertura</span>
                                        <input type="date" class="form-control" id="fecaper" required="required" value="<?php echo $hoy; ?>" onblur="calcfecven(3)">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Fecha Vencimiento</span>
                                        <input type="date" class="form-control" id="fecven" required="required" value="<?php echo $hoy; ?>" onblur="calcfecven(3)">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Int. Calc.</span>
                                        <input type="float" class="form-control" id="moncal" readonly>
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">IPF</span>
                                        <input type="float" class="form-control" id="intcal" readonly>
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Total a pagar</span>
                                        <input type="float" class="form-control" id="totcal" readonly>
                                    </div>
                                </div>
                                <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" id="toastalert">
                                    <div class="toast-header">
                                        <strong class="me-auto">Advertencia</strong>
                                        <small class="text-muted">Tomar en cuenta</small>
                                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                                    </div>
                                    <div class="toast-body" id="body_text">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button" type="button">
                                DATOS ADICIONALES
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse show" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Calc. Interes</span>
                                        <select class="form-select" id="calintere" placeholder="" aria-label="Default select example">
                                            <option value="M" selected>Mensual</option>
                                            <option value="V">Vencimiento</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <span class="input-group-addon col-8">Pago de intereses</span>
                                        <select class="form-select" id="pagintere" placeholder="" aria-label="Default select example" onchange="pagintere(this.value)">
                                            <option value="1" selected>Cuenta de ahorro</option>
                                            <!-- <option value="2">Cheque personal</option>
                                            <option value="3">Cuenta corriente</option> -->
                                        </select>
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">No Recibo</span>
                                        <input type="text" class="form-control" id="norecibo">
                                    </div>
                                </div>
                                <div class="row mb-3">

                                    <div class="col-sm-5" style="display: none;">
                                        <span class="input-group-addon col-8">Banco comercial</span>
                                        <select class="form-select" id="bancom" placeholder="" aria-label="Default select example" disabled>
                                            <option value="0" selected disabled> Seleccionar Institucion bancaria </option>
                                            <?php
                                            $credits = mysqli_query($conexion, "SELECT `CCODCTA` FROM `cremcre_meta` where CodCli='$idcli'");
                                            while ($fil = mysqli_fetch_array($credits)) {
                                                echo '<option value="' . $fil['CCODCTA'] . '">' . $fil['CCODCTA'] . '</option>';
                                            }
                                            ?>

                                        </select>
                                    </div>


                                    </select>
                                </div>
                                <div class="col-sm-5" style="display: none;">
                                    <span class="input-group-addon col-8">Cuenta corriente</span>
                                    <input type="text" class="form-control" id="cuentacor" disabled>
                                </div>


                            </div>
                            <div class="row mb-3" style="display: none;">
                                <div class="col-sm-3">
                                    <span class="input-group-addon col-8">Pignorado</span>
                                    <select class="form-select" id="pignora" placeholder="" aria-label="Default select example" onchange="pignora(this.value)">
                                        <option value="S">Si</option>
                                        <option value="N" selected>No</option>
                                    </select>
                                </div>
                                <div class="col-sm-5">
                                    <span class="input-group-addon col-8">Prestamo</span>
                                    <select class="form-select" id="codpres" placeholder="" aria-label="Default select example" disabled>
                                        <option value="0" selected>Seleccionar cuenta</option>
                                        <?php
                                        $credits = mysqli_query($conexion, "SELECT `CCODCTA` FROM `cremcre_meta` where CodCli='$idcli'");
                                        while ($fil = mysqli_fetch_array($credits)) {
                                            echo '<option value="' . $fil['CCODCTA'] . '">' . $fil['CCODCTA'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <br>
                            <div class="row justify-items-md-center">
                                <div class="col align-items-center" id="modal_footer">
                                    <button type="button" class="btn btn-outline-success" onclick="obtiene([`certif`,`codaho`,`codcli`,`nit`,`monapr`,`plazo`,`gracia`,`tasint`,`fecaper`,`fecven`,`cuentacor`,`norecibo`],[`calintere`,`calintere`,`pagintere`,`bancom`,`pignora`,`codpres`],[`nada`],`cahomcrt`,`0`,['<?php echo $id; ?>','<?php echo $bandera; ?>','<?php echo $codusu; ?>']);">
                                        <!-- <button type="button" class="btn btn-outline-success" onclick="obtiene([`certif`,`codaho`,`codcli`,`nit`,`monapr`,`plazo`,`gracia`,`tasint`,`fecaper`,`fecven`,`cuentacor`],[`calintere`,`calintere`,`pagintere`,`bancom`,`pignora`,`codpres`],[`nada`],`cahomcrt`,`0`,[' echo $id; ?>',' echo $bandera; ?>',' echo $codusu; ?>']); printdiv('certificados', '#cuadro', 'aho_04', '0');"> -->
                                        <i class="fa fa-floppy-disk"></i> Guardar
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="printdiv('certificados', '#cuadro', 'aho_04', '0')">
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
            <input type="radio" class="form-control " id=" " name="nada" checked style="display: none;">
        </div>
        </div>
    <?php
        break;
        //Modificacion de certificados 
    case 'certificados':
        $id  = $_POST["xtra"];
    ?>
        <input type="text" id="condi" value="certificados" hidden>
        <input type="text" id="file" value="aho_04" hidden>
        <div class="text" style="text-align:center">CERTIFICADOS DE PLAZO FIJO</div>
        <div class="card">
            <div class="card-body">
                <div class="container contenedort">
                    <div class="table-responsive">
                        <table id="tb_certificados" class="table table-hover table-border" style="width:100%">
                            <thead class="text-light table-head-aho">
                                <tr>
                                    <th>Crt.</th>
                                    <th>Cod. cliente</th>
                                    <th>Cuenta</th>
                                    <th>Monto</th>
                                    <th>Apertura</th>
                                    <th>Vence</th>
                                    <th>Liquidar</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="categoria_tb">
                                <?php
                                $check = '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-circle-check" width="40" height="40" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00b341" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <circle cx="12" cy="12" r="9" />
                                <path d="M9 12l2 2l4 -4" />
                              </svg>';
                                $query = mysqli_query($conexion, "SELECT * FROM `ahomcrt` order by id_crt");
                                while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
                                    $idcrt = utf8_encode($row["id_crt"]);
                                    $codcrt = utf8_encode($row["ccodcrt"]);
                                    $codcli = utf8_encode($row["ccodcli"]);
                                    $codaho = utf8_encode($row["codaho"]);
                                    $monto = utf8_encode($row["montoapr"]);
                                    $fecap = utf8_encode($row["fec_apertura"]);
                                    $fecven = utf8_encode($row["fec_ven"]);
                                    $liquid = utf8_encode($row["liquidado"]);
                                    $acre = ($liquid == "S") ? $check : '<button type="button" class="btn btn-success btn-sm" title="Acreditar y liquidar" onclick="printdiv(`liquidcrt`, `#cuadro`, `aho_04`,`' . $idcrt . '`)">
                                            <i class="fa-solid fa-sack-dollar"></i>
                                         </button>';
                                    $printliquida = ($liquid == "S") ? '<button type="button" class="btn btn-success btn-sm" title="Imprimir Comprobante liquidacion" onclick="obtiene([],[],[],`printliquidcrt`,`0`,[`' . $idcrt . '`])">
                                            <i class="fa-solid fa-print"></i>
                                         </button>' : '';

                                    $editar = ($liquid == "S") ? ' ' : '<button type="button" class="btn btn-warning btn-sm" title="Modificar Certificado" onclick="printdiv(`modcrt`,`#cuadro`,`aho_04`,' . $idcrt . ')">
                                                                            <i class="fa-solid fa-pen"></i>
                                                                        </button>';
                                    $addben = ($liquid == "S") ? ' ' : '<button type="button" class="btn btn-primary btn-sm" title="Añadir beneficiarios" onclick="printdiv(`benecrt`, `#cuadro`, `aho_04`,`' . $idcrt . '`)">
                                                                            <i class="fa-solid fa-people-line"></i>
                                                                        </button>';
                                    echo '<tr>
                                            <td>' . $codcrt . ' </td>
                                            <td>' . $codcli . ' </td>
                                            <td>' . $codaho . ' </td>
                                            <td>' . $monto . '</td>
                                            <td>' . $fecap . '</td>
                                            <td>' . $fecven . '</td>
                                            <td>' . $acre . $printliquida . '</td>
                                            <td>' . $editar . ' ' . $addben . '
                                                <button type="button" class="btn btn-danger btn-sm" title="Imprimir certificado" onclick="printcrt(' . $idcrt . ',`crud_ahorro`,`printcrt`)">
                                                    <i class="fa-solid fa-print"></i>
                                                </button>
                                            </td>
                                        </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row justify-items-md-center">
                    <div class="col align-items-center" id="modal_footer">
                        <button type="button" id="btnnew" class="btn btn-outline-success" onclick="printdiv('AddCertif', '#cuadro', 'aho_04', '0')">
                            <i class="fa fa-file"></i> Adicion de certificado
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
            //Datatable para parametrizacion
            $(document).ready(function() {
                convertir_tabla_a_datatable("tb_certificados");
            });
        </script>

    <?php
        break;

        //Reimpresion de certuficadio

    case 'modcrt':
        $id  = $_POST["xtra"];
        $codusu = $_SESSION['id'];
        $datos = [
            "id_tipo" => "",
        ];
        $datoscrt = mysqli_query($conexion, "SELECT crt.*,tip.diascalculo FROM `ahomcrt` crt INNER JOIN ahomtip tip on tip.ccodtip=substr(crt.codaho,7,2) WHERE `id_crt`=$id");
        //SELECT crt.*,tip.diascalculo FROM `ahomcrt` crt INNER JOIN ahomtip tip on tip.ccodtip=substr(crt.codaho,7,2) WHERE `id_crt`=$idcrt
        $bandera = "Codigo de certificado no existe";
        while ($row = mysqli_fetch_array($datoscrt, MYSQLI_ASSOC)) {
            $codcrt = utf8_encode($row["ccodcrt"]);
            $dayscalc = utf8_encode($row["diascalculo"]);
            $idcli = utf8_encode($row["ccodcli"]);
            $nit = utf8_encode($row["num_nit"]);
            $codaho = utf8_encode($row["codaho"]);
            $montoapr = utf8_encode($row["montoapr"]);
            $plazo = utf8_encode($row["plazo"]);
            $interes = utf8_encode($row["interes"]);
            $fecapr = utf8_encode($row["fec_apertura"]);
            $fec_ven = utf8_encode($row["fec_ven"]);
            $dia_gra = utf8_encode($row["dia_gra"]);
            $calint = utf8_encode($row["calint"]);
            $pagint = utf8_encode($row["pagint"]);
            $codban = utf8_encode($row["codban"]);
            $cuentaho = utf8_encode($row["cuentaho"]);
            $pignora = utf8_encode($row["pignorado"]);
            $codpres = utf8_encode($row["codcta"]);
            $norecibo = ($row["recibo"]);
            $bandera = "";
        }

        if ($bandera == "") {
            $data = mysqli_query($conexion, "SELECT `short_name` FROM `tb_cliente` WHERE `idcod_cliente`='$idcli'");
            $nombre = "";
            $bandera = "No existe el cliente relacionado a la cuenta de ahorro";
            while ($dat = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                $nombre = utf8_encode($dat["short_name"]);
                $bandera = "";
            }
        }
        $hoy = date("Y-m-d");
        $fec1anio = strtotime('+365 day', strtotime($hoy));
        $fec1anio = date('Y-m-j', $fec1anio);

        $intcal = $montoapr * ($interes / 100 / $dayscalc);
        $intcal = $intcal * $plazo;
        $ipf = $intcal * 0.10;
        $total = $intcal - $ipf;
    ?>
        <div class="text" style="text-align:center">MODIFICACION DE CERTIFICADOS DE PLAZO FIJO</div>
        <input type="text" id="condi" value="addcrt" hidden>
        <input type="text" id="file" value="aho_04" hidden>
        <input type="text" id="dayscalc" value="<?php echo ($bandera == "") ? $dayscalc : 365; ?>" hidden>
        <div class="card">
            <div class="card-header">Modificacion de certificados</div>
            <div class="card-body">
                <div class="accordion" id="accordionExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" aria-expanded="true" aria-controls="collapseOne">
                                IDENTIFICACION DEL CLIENTE
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-sm-2">
                                        <span class="input-group-addon col-8">Certificado</span>
                                        <input type="text" aria-label="Certificado" id="certif" class="form-control  col" placeholder="" disabled value="<?php echo $codcrt; ?>">
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-sm-5">
                                        <div>
                                            <span class="input-group-addon col-8">Cuenta de ahorro</span>
                                            <input type="text" aria-label="Cuenta" id="codaho" class="form-control  col" placeholder="" value="<?php echo $codaho; ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <!--Aho_0_ApertCuenAhor Búsqueda NIT-->
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <div>
                                            <span class="input-group-addon col-8">Cliente</span>
                                            <input type="text" class="form-control " id="nomcli" placeholder="" value="<?php echo $nombre; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div>
                                            <span class="input-group-addon col-8">Codigo de cliente</span>
                                            <input type="text" class="form-control " id="codcli" placeholder="" value="<?php echo $idcli; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div>
                                            <span class="input-group-addon col-8">NIT</span>
                                            <input type="text" class="form-control " id="nit" placeholder="" value="<?php echo $nit; ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button" type="button" aria-expanded="false" aria-controls="collapseTwo">
                                DATOS DE LA CUENTA
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Monto</span>
                                        <input type="float" class="form-control" id="monaprup" placeholder="0.00" required="required" value="<?php echo $montoapr; ?>">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Plazo</span>
                                        <input type="number" class="form-control" id="plazo" placeholder="365" required="required" value="<?php echo $plazo; ?>" onchange="calcfecven(2)">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Dias de gracia</span>
                                        <input type="float" class="form-control" id="gracia" placeholder="0" required="required" value="<?php echo $dia_gra; ?>">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Interes %</span>
                                        <input type="float" class="form-control" id="tasint" placeholder="0.00" required="required" onchange="calcfecven(1)" value="<?php echo number_format((float)$interes, 2); ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Fecha Apertura</span>
                                        <input type="date" class="form-control" id="fecaper" required="required" value="<?php echo $fecapr; ?>" onblur="calcfecven(3)">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Fecha Vencimiento</span>
                                        <input type="date" class="form-control" id="fecven" required="required" value="<?php echo $fec_ven; ?>" onblur="calcfecven(3)">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Int. Calc.</span>
                                        <input type="float" class="form-control" id="moncal" readonly value="<?php echo number_format((float)$intcal, 2); ?>">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">IPF</span>
                                        <input type="float" class="form-control" id="intcal" readonly value="<?php echo number_format((float)$ipf, 2); ?>">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Total a pagar</span>
                                        <input type="float" class="form-control" id="totcal" readonly value="<?php echo number_format((float)$total, 2); ?>">
                                    </div>
                                </div>
                                <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" id="toastalert">
                                    <div class="toast-header">
                                        <strong class="me-auto">Advertencia</strong>
                                        <small class="text-muted">Tomar en cuenta</small>
                                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                                    </div>
                                    <div class="toast-body" id="body_text">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button" type="button">
                                DATOS ADICIONALES
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse show" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Calc. Interes</span>
                                        <select class="form-select" id="calintere" placeholder="" aria-label="Default select example">
                                            <option value="M" <?php if ($calint == 'M') echo 'selected'; ?>>Mensual</option>
                                            <option value="V" <?php if ($calint == 'V') echo 'selected'; ?>>Vencimiento</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <span class="input-group-addon col-8">Pago de intereses</span>
                                        <select class="form-select" id="pagintere" placeholder="" aria-label="Default select example" onchange="pagintere(this.value)">
                                            <option value="1" <?php if ($pagint == '1') echo 'selected'; ?>>Cuenta de ahorro</option>
                                            <option value="2" <?php if ($pagint == '2') echo 'selected'; ?>>Cheque personal</option>
                                            <option value="3" <?php if ($pagint == '3') echo 'selected'; ?>>Cuenta corriente</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">No recibo</span>
                                        <input type="text" class="form-control" id="norecibo" value="<?php echo $norecibo; ?>">
                                    </div>
                                    
                                </div>
                                <div class="row mb-3" style="display: none;">
                                    <div class="col-sm-5">
                                        <span class="input-group-addon col-8">Banco comercial</span>
                                        <select class="form-select" id="bancom" placeholder="" aria-label="Default select example" disabled>
                                            <option value="0" <?php if ($codban == '0') echo 'selected'; ?> disabled>Seleccionar Institucion bancaria</option>
                                            <?php
                                            $selected = "";
                                            $bancs = mysqli_query($conexion, "SELECT `id`,`nombre` FROM `tb_bancos` where estado = 1");
                                            while ($filas = mysqli_fetch_array($bancs)) {
                                                ($filas['id'] == $codban) ? $selected = "selected" : $selected = "";
                                                echo '<option ' . $selected . ' value="' . $filas['id'] . '">' . $filas['nombre'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-5">
                                        <span class="input-group-addon col-8">Cuenta corriente</span>
                                        <input type="text" class="form-control" id="cuentacor" disabled value="<?php echo $cuentaho; ?>">
                                    </div>
                                </div>
                                <div class="row mb-3" style="display: none;">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Pignorado</span>
                                        <select class="form-select" id="pignora" placeholder="" aria-label="Default select example" onchange="pignora(this.value)">
                                            <option value="S" <?php if ($pignora == 'S') echo 'selected'; ?>>Si</option>
                                            <option value="N" <?php if ($pignora == 'N') echo 'selected'; ?>>No</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-5">
                                        <span class="input-group-addon col-8">Prestamo</span>
                                        <select class="form-select" id="codpres" placeholder="" aria-label="Default select example" disabled>
                                            <option value="0" <?php if ($codpres == '0') echo 'selected'; ?>>Seleccionar cuenta</option>
                                            <?php
                                            $select = "";
                                            $credits = mysqli_query($conexion, "SELECT `ccodcta` FROM `cremcre_meta` where CodCli='$idcli'");
                                            while ($fil = mysqli_fetch_array($credits)) {
                                                ($fil['ccodcta'] == $codpres) ? $select = "selected" : $select = "";
                                                echo '<option ' . $select . ' value="' . $fil['ccodcta'] . '">' . $fil['ccodcta'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <br>
                                <div class="row justify-items-md-center">
                                    <div class="col align-items-center" id="modal_footer">
                                        <button type="button" class="btn btn-outline-success" onclick="obtiene([`certif`,`codaho`,`codcli`,`nit`,`monaprup`,`plazo`,`gracia`,`tasint`,`fecaper`,`fecven`,`cuentacor`,`norecibo`],[`calintere`,`calintere`,`pagintere`,`bancom`,`pignora`,`codpres`],[`nada`],`uahomcrt`,`0`,['<?php echo $id; ?>','<?php echo $bandera; ?>','<?php echo $codusu; ?>']);printdiv('certificados', '#cuadro', 'aho_04', '0')">
                                            <i class="fa fa-floppy-disk"></i> Actualizar
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="printdiv('certificados', '#cuadro', 'aho_04', '0')">
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
                <input type="radio" class="form-control " id=" " name="nada" checked style="display: none;">
            </div>
        </div>

    <?php
        break;

        //beneficiarios de deposito a plazo
    case 'benecrt':
        $id = $_POST["xtra"];
        $datos = [
            "id_tipo" => "",
        ];
        $datoscrt = mysqli_query($conexion, "SELECT * FROM `ahomcrt` WHERE `id_crt`=$id");
        $bandera = "Codigo de certificado no existe";
        while ($row = mysqli_fetch_array($datoscrt, MYSQLI_ASSOC)) {
            $codcrt = utf8_encode($row["ccodcrt"]);
            $idcli = utf8_encode($row["ccodcli"]);
            $nit = utf8_encode($row["num_nit"]);
            $codaho = utf8_encode($row["codaho"]);
            $bandera = "";
        }
        if ($bandera == "") {
            //$data = mysqli_query($conexion, "SELECT `short_name` FROM `tb_cliente` WHERE `idcod_cliente`='$idcli' OR `no_tributaria` = '$nit'");
            $data = mysqli_query($conexion, "SELECT `short_name` FROM `tb_cliente` WHERE `idcod_cliente`='$idcli'");
            $nombre = "";
            $bandera = "No existe el cliente relacionado a la cuenta de ahorro";
            while ($dat = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                $nombre = utf8_encode($dat["short_name"]);
                $bandera = "";
            }
        }
    ?>
        <!--Aho_0_BeneAho Inicio de Ahorro Sección 0 Beneficiario de Ahorro-->
        <div class="text" style="text-align:center">BENEFICIARIOS</div>
        <div class="card">
            <input type="text" id="file" value="aho_04" style="display: none;">
            <input type="text" id="condi" value="benecrt" style="display: none;">
            <div class="card-header">Beneficiarios</div>
            <div class="card-body">
                <!--Aho_0_BeneAho Cta.Ahorros-->
                <div class="row contenedort">
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <span class="input-group-addon col-8">Numero de certificado</span>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control " id="ccodcrt" required placeholder="000-000-00-000000" value="<?php if ($bandera == "") echo $codcrt; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <span class="input-group-addon col-8">Cuenta de Ahorros</span>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control " id="ccodaho" required placeholder="000-000-00-000000" value="<?php if ($bandera == "") echo $codaho; ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <span class="input-group-addon col-8">Nombre</span>
                            <input type="text" class="form-control " id="name" value="<?php if ($bandera == "") echo $nombre; ?>" readonly>
                        </div>
                    </div>
                    <?php if ($bandera != "" && $id != "0") {
                        echo '<div class="alert alert-danger" role="alert">' . $bandera . '</div>';
                    }
                    ?>
                </div>
                <!--Aho_0_BeneAho Tabla de Datos-->
                <div class="row contenedort">
                    <div class="row">
                        <table id="table_id2" class="table">
                            <thead>
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
                                    $queryben = mysqli_query($conexion, "SELECT * FROM `ahomben` WHERE `ccodcrt`='$codcrt'");
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
                    <div class="row">
                        <!--TOTAL-->
                        <div class="col-md-3">
                            <label for="">Total: <?php echo $total; ?> %</label>
                        </div>
                    </div>
                </div>
                <!--Aho_0_BeneAho Botones Guardar, Editar, Eliminar, Guardar-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center" id="modal_footer">
                        <button type="button" id="btnnew" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#databen">
                            <i class="fa fa-file"></i> Nuevo
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="printdiv('certificados', '#cuadro', 'aho_04', '0')">
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
            <div class="modal-dialog  modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Datos de Beneficiario</h1>
                    </div>
                    <div class="modal-body">
                        <div class="row contenedort">
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
                    <input type="radio" name="nada" id="0" checked style="display: none;">
                    <div class="modal-footer">
                        <button id="createben" type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="obtiene(['benname', 'bendpi', 'bendire', 'bentel', 'bennac', 'benporcent','ccodcrt'], ['benparent'], ['nada'], 'cahomben', '<?php echo $id; ?>', ['<?php echo $codaho; ?>',<?php echo $total; ?>,'<?php echo $bandera; ?>']); printdiv2('#cuadro','<?php echo $id; ?>');">
                            <i class="fa fa-floppy-disk"></i> Guardar
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="printdiv2('#cuadro','<?php echo $id; ?>')">Cancelar</button>
                        <button id="updateben" style="display:none;" type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="obtiene(['benname', 'bendpi', 'bendire', 'bentel', 'bennac', 'benporcent','benporcentant','idben'], ['benparent'], ['nada'], 'uahomben', '<?php echo $id; ?>', ['<?php echo $id; ?>',<?php echo $total; ?>])">
                            <i class="fa fa-floppy-disk"></i> Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php
        break;
    case 'liquidcrt':
        $id  = $_POST["xtra"];
        $codusu = $_SESSION['id'];
        $codofi = $_SESSION['agencia'];
        $datos = [
            "id_tipo" => "",
        ];
        $datoscrt = mysqli_query($conexion, "SELECT crt.*,tip.diascalculo FROM `ahomcrt` crt INNER JOIN ahomtip tip on tip.ccodtip=substr(crt.codaho,7,2) WHERE `id_crt`=$id");
        //SELECT crt.*,tip.diascalculo FROM `ahomcrt` crt INNER JOIN ahomtip tip on tip.ccodtip=substr(crt.codaho,7,2) WHERE `id_crt`=
        $bandera = "Codigo de certificado no existe";
        while ($row = mysqli_fetch_array($datoscrt, MYSQLI_ASSOC)) {
            $codcrt = utf8_encode($row["ccodcrt"]);
            $dayscalc = utf8_encode($row["diascalculo"]);
            $idcli = utf8_encode($row["ccodcli"]);
            $nit = utf8_encode($row["num_nit"]);
            $codaho = utf8_encode($row["codaho"]);
            $montoapr = utf8_encode($row["montoapr"]);
            $plazo = utf8_encode($row["plazo"]);
            $interes = utf8_encode($row["interes"]);
            $fecapr = utf8_encode($row["fec_apertura"]);
            $fec_ven = utf8_encode($row["fec_ven"]);
            $dia_gra = utf8_encode($row["dia_gra"]);
            $calint = utf8_encode($row["calint"]);
            $pagint = utf8_encode($row["pagint"]);
            $codban = utf8_encode($row["codban"]);
            $cuentaho = utf8_encode($row["cuentaho"]);
            $pignora = utf8_encode($row["pignorado"]);
            $codpres = utf8_encode($row["codcta"]);
            $bandera = "";
        }

        if ($bandera == "") {
            $data = mysqli_query($conexion, "SELECT `short_name` FROM `tb_cliente` WHERE `idcod_cliente`='$idcli'");
            $nombre = "";
            $bandera = "No existe el cliente relacionado a la cuenta de ahorro";
            while ($dat = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                $nombre = utf8_encode($dat["short_name"]);
                $bandera = "";
            }
        }
        $hoy = date("Y-m-d");
        $fec1anio = strtotime('+365 day', strtotime($hoy));
        $fec1anio = date('Y-m-j', $fec1anio);

        $fecfin = ($hoy <= $fec_ven) ? $hoy : $fec_ven;
        $diasdif = dias_dif($fecapr, $fecfin);

        $intcal = $montoapr * ($interes / 100 / $dayscalc);
        $intcal = $intcal * $diasdif;
        $ipf = $intcal * 0.10;
        $total = $intcal - $ipf;

        $totaltodo = ($montoapr + $intcal) - ($ipf);
        // if ($calint == "V" && $hoy <= $fec_ven) {
        //     $diasdif = dias_dif($fecapr, $hoy);
        //     $interescalcven = $montoapr * ($interes / 100 / 365);
        //     $interescalcven = $interescalcven * $diasdif;
        //     $ipfcalcven = $interescalcven * 0.10;
        //     $totalcalcven = $interescalcven - $ipfcalcven;

        //     $totaltodo = ($montoapr + $interescalcven) - ($ipfcalcven);
        // }
    ?>
        <div class="text" style="text-align:center">ACREDITACION Y LIQUIDACION DE PLAZO FIJO</div>
        <input type="text" id="condi" value="certificados" hidden>
        <input type="text" id="file" value="aho_04" hidden>
        <input type="text" id="dayscalc" value="<?php echo ($bandera == "") ? $dayscalc : 365; ?>" hidden>
        <div class="card">
            <div class="card-header">Acreditacion y liquidacion de certificados</div>
            <div class="card-body">
                <div class="accordion" id="accordionExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" aria-expanded="true" aria-controls="collapseOne">
                                IDENTIFICACION DEL CLIENTE
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-sm-2">
                                        <span class="input-group-addon col-8">Certificado</span>
                                        <input type="text" aria-label="Certificado" id="certif" class="form-control  col" placeholder="" disabled value="<?php echo $codcrt; ?>">
                                    </div>
                                    <div class="col-sm-2"> </div>
                                    <div class="col-sm-4">
                                        <span class="input-group-addon col-8">ACCION</span>
                                        <select class="form-select" id="accion">
                                            <option value=" 1" selected>ACREDITAR Y LIQUIDAR</option>
                                            <option value="2">SOLO ACREDITAR</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-sm-5">
                                        <div>
                                            <span class="input-group-addon col-8">Cuenta de ahorro</span>
                                            <input type="text" aria-label="Cuenta" id="codaho" class="form-control  col" placeholder="" value="<?php echo $codaho; ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <!--Aho_0_ApertCuenAhor Búsqueda NIT-->
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <div>
                                            <span class="input-group-addon col-8">Cliente</span>
                                            <input type="text" class="form-control " id="nomcli" placeholder="" value="<?php echo $nombre; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div>
                                            <span class="input-group-addon col-8">Codigo de cliente</span>
                                            <input type="text" class="form-control " id="codcli" placeholder="" value="<?php echo $idcli; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div>
                                            <span class="input-group-addon col-8">NIT</span>
                                            <input type="text" class="form-control " id="nit" placeholder="" value="<?php echo $nit; ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button" type="button" aria-expanded="false" aria-controls="collapseTwo">
                                DATOS DE LA CUENTA
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Monto</span>
                                        <input type="float" class="form-control" id="monapr" placeholder="0.00" disabled value="<?php echo $montoapr; ?>">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Plazo Establecido</span>
                                        <input type="number" class="form-control" id="plazoest" placeholder="365" disabled value="<?php echo $plazo; ?>">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Dias de gracia</span>
                                        <input type="float" class="form-control" id="gracia" placeholder="0" disabled value="<?php echo $dia_gra; ?>">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Interes %</span>
                                        <input type="float" class="form-control" id="tasint" placeholder="0.00" disabled value="<?php echo number_format((float) $interes, 2); ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Fecha Apertura</span>
                                        <input type="date" class="form-control" id="fecaper" required="required" disabled value="<?php echo $fecapr; ?>">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Fecha Vencimiento</span>
                                        <input type="date" class="form-control" id="fecv" required="required" disabled value="<?php echo $fec_ven; ?>">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Fecha de Acreditacion</span>
                                        <input type="date" class="form-control" id="fecacredita" required="required" value="<?php echo ($hoy <= $fec_ven) ? $hoy : $fec_ven; ?>">
                                    </div>
                                    <div class="col-sm-3">
                                        <br>
                                        <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="recalculoxfecha(<?php echo $dayscalc; ?>)">
                                            <i class="fa-solid fa-calculator"></i> Recalcular interes
                                        </button>
                                    </div>
                                </div>
                                <?php
                                if ($calint == "V" && $hoy <= $fec_ven) {
                                ?>
                                    <div class="alert alert-danger" role="alert">
                                        Se esta acreditando antes de la fecha de vencimiento, ingrese el porcentaje de penalizacion ó cambie la fecha de acreditacion y presione el boton recalcular
                                    </div>
                                <?php
                                } else {
                                ?>
                                    <!-- <div class="alert alert-success" role="alert">
                                        Se esta acreditando despues de la fecha de vencimiento, proceda sin problemas
                                    </div> -->
                                <?php
                                }
                                ?>
                                <div class="container">
                                    <h6>ACREDITAR</h6>
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">Plazo al dia de hoy</span>
                                            <input type="number" class="form-control" id="plazo" disabled value="<?php echo $diasdif; ?>">
                                        </div>
                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">Penalizacion %</span>
                                            <input type="number" step="0.01" class="form-control" id="porc_pena" required="required" value="0" onkeyup="recalculo()">
                                        </div>
                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">Monto Penalizacion</span>
                                            <input type="number" step="0.01" class="form-control" id="penaliza" value="0" onkeyup="interesc()">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">Int. Calc.</span>
                                            <input type="number" step="0.01" class="form-control" id="moncal1" value="<?php echo round((float)$intcal, 2); ?>" onkeyup="equi()">
                                        </div>
                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">Int. acreditar</span>
                                            <input type="number" step="0.01" class="form-control" readonly id="moncal" value="<?php echo  round((float)$intcal, 2); ?>" onkeyup="ipfc()">
                                        </div>
                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">IPF</span>
                                            <input type="number" step="0.01" class="form-control" id="intcal" readonly value="<?php echo round((float)$ipf, 2); ?>">
                                        </div>
                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">Interes a pagar</span>
                                            <input type="number" step="0.01" class="form-control" id="totcal" readonly value="<?php echo round((float)$total, 2); ?>">
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button" type="button">
                                DATOS ADICIONALES
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse show" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">No Recibo</span>
                                        <input type="text" class="form-control" id="norecibo">
                                    </div>
                                </div>

                                <br>
                                <div class="row justify-items-md-center">
                                    <div class="col align-items-center" id="modal_footer">
                                        <button type="button" class="btn btn-outline-success" onclick="obtiene([`moncal`,`intcal`,`penaliza`,`norecibo`,`fecacredita`],[`accion`],[],`liquidcrt`,`0`,['<?php echo $codcrt; ?>','<?php echo $codaho; ?>','<?php echo $idcli; ?>','<?php echo $montoapr; ?>','<?php echo $codusu; ?>','<?php echo $codofi; ?>'])">
                                            <i class="fa fa-floppy-disk"></i> Liquidar
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="printdiv('certificados', '#cuadro', 'aho_04', '0')">
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
                <input type="radio" class="form-control " id=" " name="nada" checked style="display: none;">
            </div>
        </div>
        <script>
            function interesc() {
                var monpenaliza = $('#penaliza').val();
                monpenaliza = (isNaN(monpenaliza)) ? 0 : monpenaliza;
                var interes = $('#moncal1').val();
                interes = (!isNaN(interes)) ? interes - monpenaliza : 0;
                $("#moncal").val(parseFloat(interes.toFixed(2)));
                ipfc();
            }

            function equi() {
                var monto1 = $('#moncal1').val();
                $("#moncal").val(monto1);
                ipfc();
            }

            function ipfc() {
                var mon = $('#moncal').val();
                mon = (!isNaN(mon)) ? mon : 0;
                ipf = (mon * 0.1);
                totcal = mon - ipf;
                $("#intcal").val(parseFloat(ipf.toFixed(2)));
                $("#totcal").val(parseFloat(totcal.toFixed(2)));
            }

            function recalculo() {
                var mon = $('#moncal1').val();
                mon = (!isNaN(mon)) ? mon : 0;
                var porcpena = $('#porc_pena').val();
                porcpena = (!isNaN(porcpena)) ? parseFloat(porcpena) : 0;
                var penaliza = mon * (porcpena / 100);
                document.getElementById("penaliza").value = parseFloat(penaliza.toFixed(2));
                interesc();
            }

            function recalculoxfecha(dayscalc = 360) {
                var fecha1 = new Date($('#fecaper').val());
                var fecha2 = new Date($("#fecacredita").val())

                var diferenciaEnMilisegundos = fecha2 - fecha1;
                var diferenciaEnDias = diferenciaEnMilisegundos / (1000 * 60 * 60 * 24);
                diferenciaEnDias = (diferenciaEnDias > 180 && diferenciaEnDias < 190) ? 180 : diferenciaEnDias;
                var monapr = parseFloat($('#monapr').val()).toFixed(2);
                var tasa = parseFloat($('#tasint').val()).toFixed(2) / 100;
                var interes = monapr * (tasa / dayscalc) * diferenciaEnDias;

                document.getElementById("moncal").value = parseFloat(interes.toFixed(2));
                document.getElementById("moncal1").value = parseFloat(interes.toFixed(2));

                interesc();
            }
        </script>
    <?php
        break;
    case 'liquidcrtant':
        $id  = $_POST["xtra"];
        $codusu = $_SESSION['id'];
        $codofi = $_SESSION['agencia'];
        $datos = [
            "id_tipo" => "",
        ];
        $datoscrt = mysqli_query($conexion, "SELECT * FROM `ahomcrt` WHERE `id_crt`=$id");
        $bandera = "Codigo de certificado no existe";
        while ($row = mysqli_fetch_array($datoscrt, MYSQLI_ASSOC)) {
            $codcrt = utf8_encode($row["ccodcrt"]);
            $idcli = utf8_encode($row["ccodcli"]);
            $nit = utf8_encode($row["num_nit"]);
            $codaho = utf8_encode($row["codaho"]);
            $montoapr = utf8_encode($row["montoapr"]);
            $plazo = utf8_encode($row["plazo"]);
            $interes = utf8_encode($row["interes"]);
            $fecapr = utf8_encode($row["fec_apertura"]);
            $fec_ven = utf8_encode($row["fec_ven"]);
            $dia_gra = utf8_encode($row["dia_gra"]);
            $calint = utf8_encode($row["calint"]);
            $pagint = utf8_encode($row["pagint"]);
            $codban = utf8_encode($row["codban"]);
            $cuentaho = utf8_encode($row["cuentaho"]);
            $pignora = utf8_encode($row["pignorado"]);
            $codpres = utf8_encode($row["codcta"]);
            $bandera = "";
        }

        if ($bandera == "") {
            $data = mysqli_query($conexion, "SELECT `short_name` FROM `tb_cliente` WHERE `idcod_cliente`='$idcli'");
            $nombre = "";
            $bandera = "No existe el cliente relacionado a la cuenta de ahorro";
            while ($dat = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                $nombre = utf8_encode($dat["short_name"]);
                $bandera = "";
            }
        }
        $hoy = date("Y-m-d");
        $fec1anio = strtotime('+365 day', strtotime($hoy));
        $fec1anio = date('Y-m-j', $fec1anio);

        $intcal = $montoapr * ($interes / 100 / 360);
        $intcal = $intcal * $plazo;
        $ipf = $intcal * 0.10;
        $total = $intcal - $ipf;

        $totaltodo = ($montoapr + $intcal) - ($ipf);
        //------------datos si se liquida antes de la fecha de vencimiento
        if ($hoy <= $fec_ven) {
            $diasdif = dias_dif($fecapr, $hoy);

            $interescalcven = $montoapr * ($interes / 100 / 360);
            $interescalcven = $interescalcven * $diasdif;
            $ipfcalcven = $interescalcven * 0.10;
            $totalcalcven = $interescalcven - $ipfcalcven;

            $totaltodo = ($montoapr + $interescalcven) - ($ipfcalcven);
        }
    ?>
        <div class="text" style="text-align:center">LIQUIDACION DE PLAZO FIJO</div>
        <input type="text" id="condi" value="certificados" hidden>
        <input type="text" id="file" value="aho_04" hidden>
        <div class="card">
            <div class="card-header">Liquidacion de certificados</div>
            <div class="card-body">
                <div class="accordion" id="accordionExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" aria-expanded="true" aria-controls="collapseOne">
                                IDENTIFICACION DEL CLIENTE
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-sm-2">
                                        <span class="input-group-addon col-8">Certificado</span>
                                        <input type="text" aria-label="Certificado" id="certif" class="form-control  col" placeholder="" disabled value="<?php echo $codcrt; ?>">
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-sm-5">
                                        <div>
                                            <span class="input-group-addon col-8">Cuenta de ahorro</span>
                                            <input type="text" aria-label="Cuenta" id="codaho" class="form-control  col" placeholder="" value="<?php echo $codaho; ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <!--Aho_0_ApertCuenAhor Búsqueda NIT-->
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <div>
                                            <span class="input-group-addon col-8">Cliente</span>
                                            <input type="text" class="form-control " id="nomcli" placeholder="" value="<?php echo $nombre; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div>
                                            <span class="input-group-addon col-8">Codigo de cliente</span>
                                            <input type="text" class="form-control " id="codcli" placeholder="" value="<?php echo $idcli; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div>
                                            <span class="input-group-addon col-8">NIT</span>
                                            <input type="text" class="form-control " id="nit" placeholder="" value="<?php echo $nit; ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button" type="button" aria-expanded="false" aria-controls="collapseTwo">
                                DATOS DE LA CUENTA
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Monto</span>
                                        <input type="float" class="form-control" id="monapr" placeholder="0.00" disabled value="<?php echo $montoapr; ?>">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Plazo Establecido</span>
                                        <input type="number" class="form-control" id="plazoest" placeholder="365" disabled value="<?php echo $plazo; ?>">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Dias de gracia</span>
                                        <input type="float" class="form-control" id="gracia" placeholder="0" disabled value="<?php echo $dia_gra; ?>">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Interes %</span>
                                        <input type="float" class="form-control" id="tasint" placeholder="0.00" disabled value="<?php echo number_format((float) $interes, 2); ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Fecha Apertura</span>
                                        <input type="date" class="form-control" id="fecaper" required="required" disabled value="<?php echo $fecapr; ?>">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Fecha Vencimiento</span>
                                        <input type="date" class="form-control" id="fecv" required="required" disabled value="<?php echo $fec_ven; ?>">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Int. Calc.</span>
                                        <input type="float" class="form-control" id="moncale" readonly value="<?php echo number_format((float)$intcal, 2); ?>">
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">IPF</span>
                                        <input type="float" class="form-control" id="intcale" readonly value="<?php echo number_format((float)$ipf, 2); ?>">
                                    </div>

                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Total interes a pagar</span>
                                        <input type="float" class="form-control" id="totcale" readonly value="<?php echo number_format((float)$total, 2); ?>">
                                    </div>
                                </div>
                                <?php
                                if ($hoy <= $fec_ven) {
                                ?>
                                    <div class="alert alert-danger" role="alert">
                                        Se esta liquidando antes de la fecha de vencimiento, debe ingresar el porcentaje de penalizacion
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">Plazo al dia de hoy</span>
                                            <input type="number" class="form-control" id="plazo" disabled value="<?php echo $diasdif; ?>">
                                        </div>
                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">Penalizacion %</span>
                                            <input type="float" class="form-control" id="porc_pena" required="required" value="">
                                        </div>
                                        <div class="col-sm-3">
                                            <br>
                                            <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="penalizacion(<?php echo $interescalcven; ?>)">
                                                <i class="fa-solid fa-calculator"></i> Recalcular
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">Int. Calc.</span>
                                            <input type="float" class="form-control" id="moncal" readonly value="<?php echo $interescalcven; ?>">
                                        </div>
                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">IPF</span>
                                            <input type="float" class="form-control" id="intcal" readonly value="<?php echo number_format((float)$ipfcalcven, 2); ?>">
                                        </div>
                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">Interes a pagar</span>
                                            <input type="float" class="form-control" id="totcal" readonly value="<?php echo $totalcalcven; ?>">
                                        </div>
                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">Monto Penalizacion</span>
                                            <input type="float" class="form-control" id="penaliza" value="0">
                                        </div>
                                    </div>
                                <?php
                                } else {
                                ?>
                                    <div class="alert alert-success" role="alert">
                                        Se esta liquidando despues de la fecha de vencimiento
                                    </div>
                                    <div class="row mb-3" style="display: none;">
                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">Int. Calc.</span>
                                            <input type="float" class="form-control" id="moncal" readonly value="<?php echo $intcal; ?>">
                                        </div>
                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">IPF</span>
                                            <input type="float" class="form-control" id="intcal" readonly value="<?php echo $ipf; ?>">
                                        </div>

                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">Total interes a pagar</span>
                                            <input type="float" class="form-control" id="totcal" readonly value="<?php echo $total; ?>">
                                        </div>
                                        <div class="col-sm-3">
                                            <span class="input-group-addon col-8">Monto Penalizacion</span>
                                            <input type="float" class="form-control" id="penaliza" value="0">
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">Total a pagar</span>
                                        <input type="float" class="form-control" id="totaltodo" readonly value="<?php echo number_format((float)$totaltodo, 2); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button" type="button">
                                DATOS ADICIONALES
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse show" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <span class="input-group-addon col-8">No Recibo</span>
                                        <input type="text" class="form-control" id="norecibo">
                                    </div>
                                    <div class="col-sm-3" style="display: none;">
                                        <span class="input-group-addon col-8">nada</span>
                                        <select class="form-select" id="pagintere">
                                            <option value="S" selected>Si</option>
                                        </select>
                                    </div>
                                </div>

                                <br>
                                <div class="row justify-items-md-center">
                                    <div class="col align-items-center" id="modal_footer">
                                        <button type="button" class="btn btn-outline-success" onclick="obtiene([`moncal`,`intcal`,`penaliza`,`norecibo`],[`pagintere`],[`nada`],`liquidcrt`,`0`,['<?php echo $codcrt; ?>','<?php echo $codaho; ?>','<?php echo $idcli; ?>','<?php echo $montoapr; ?>','<?php echo $codusu; ?>','<?php echo $codofi; ?>'])">
                                            <i class="fa fa-floppy-disk"></i> Liquidar
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="printdiv('certificados', '#cuadro', 'aho_04', '0')">
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
                <input type="radio" class="form-control " id=" " name="nada" checked style="display: none;">
            </div>
        </div>
    <?php
        break;
    case 'reportcrt':
    ?>
        <style>
            form {
                margin: auto;
                padding: 1.1em 0;
                position: relative
            }

            form .labeld:before,
            form span:before {
                border-radius: 50%;
                content: ""
            }

            form .labeld {
                cursor: pointer;
                display: flex;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                align-items: center;
            }

            form .labeld:before {
                background-image: linear-gradient(45deg, #93a5cf 0%, #e4efe9 100%);
                display: inline-block;
                margin-right: 0.375em;
                width: 1.1em;
                height: 1.1em
            }

            form .labeld:not(:last-of-type) {
                margin-bottom: 1.2em
            }

            form span {
                position: absolute;
                top: 1.35em;
                left: 0.20em;
                width: 0.8em;
                height: 0.8em;
                transition: transform 0.25s linear;
                z-index: 1
            }

            form span,
            form span:before {
                display: block
            }

            form span:before {
                background-image: linear-gradient(-225deg, #22E1FF 0%, #1D8FE1 48%, #625EB1 100%);
                border-radius: 50%;
                box-shadow: 0 0.1em 0.1em 0 rgba(0, 0, 0, 0.5), 0 0 0.1em 0.1em rgba(0, 0, 0, 0.25) inset;
                width: 100%;
                height: 100%
            }

            .tiprad {
                position: fixed;
                top: -1.5em;
                left: -1.5em
            }

            .tiprad:nth-of-type(1):checked~span {
                transform: translateY(0.1em)
            }

            .tiprad:nth-of-type(1):checked~span:before {
                animation: wobble1 0.5s linear forwards
            }

            .tiprad:nth-of-type(2):checked~span {
                transform: translateY(2.8em)
            }

            .tiprad:nth-of-type(2):checked~span:before {
                animation: wobble2 0.5s linear forwards
            }

            .tiprad:nth-of-type(3):checked~span {
                transform: translateY(5.5em)
            }

            .tiprad:nth-of-type(3):checked~span:before {
                animation: wobble3 0.5s linear forwards
            }
        </style>
        <input type="text" id="file" value="aho_04" style="display: none;">
        <input type="text" id="condi" value="reportcrt" style="display: none;">
        <div class="text" style="text-align:center">REPORTE DE CERTIFICADOS</div>
        <div class="card">
            <div class="card-header"></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="card text-bg-light mb-3">
                            <div class="card-header">Filtro por Estado</div>
                            <div class="card-body">
                                <form class="formstyle">
                                    <input class="tiprad" type="radio" name="r1" id="all" value="all" checked>
                                    <input class="tiprad" type="radio" name="r1" id="vig" value="vig">
                                    <input class="tiprad" type="radio" name="r1" id="can" value="can">
                                    <span></span>
                                    <label for="all" class="labeld">Todo </label>
                                    <label for="vig" class="labeld"> Vigentes</label>
                                    <label for="can" class="labeld"> Cancelados</label>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card text-bg-light mb-3">
                            <div class="card-header">Filtro por fecha de vencimiento</div>
                            <div class="card-body">
                                <form>
                                    <input class="tiprad" type="radio" name="r2" id="ftodo" value="ftodo" checked onclick="habdeshab([],['finicio','ffin'])">
                                    <input class="tiprad" type="radio" name="r2" id="frango" value="frango" onclick="habdeshab(['finicio','ffin'],[])">
                                    <span></span>
                                    <label for="ftodo" class="labeld">Todo</label>
                                    <label for="frango" class="labeld">Rango</label>

                                    <div class="row mt-3">
                                        <div class="col-sm-5">
                                            <label for="finicio">Desde</label>
                                            <input type="date" class="form-control" id="finicio" min="1950-01-01" value="<?php echo date("Y-m-d"); ?>" disabled>
                                        </div>
                                        <div class=" col-sm-5">
                                            <label for="ffin">Hasta</label>
                                            <input type="date" class="form-control" id="ffin" min="1950-01-01" value="<?php echo date("Y-m-d"); ?>" disabled>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Botones-->
                <div class="row justify-items-md-center">
                    <div class="col align-items-center" id="modal_footer">
                        <button type="button" id="btnSave" class="btn btn-outline-danger" onclick="reportes([[`finicio`,`ffin`],[],[`r1`,`r2`],['']], 'pdf', 'certificados_plazo_fijo',0)">
                            <i class="fa-solid fa-file-pdf"></i> Generar Pdf
                        </button>
                        <button type="button" id="btnSave" class="btn btn-outline-success" onclick="reportes([[`finicio`,`ffin`],[],[`r1`,`r2`],['']], 'xlsx', 'certificados_plazo_fijo',1)">
                            <i class="fa-solid fa-file-excel"></i> Generar Excel
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
} //FINAL DEL SWITCH
?>