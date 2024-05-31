<?php
session_start();
///formularios para el modulo de creditos individuales,
// AQUI ESTAN LAS VENTANAS, solicitud, analisis, desembolso
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');
include '../../../src/funcphp/valida.php';
include '../../../src/funcphp/func_gen.php';
$mtmax = 0;
$condi = $_POST["condi"];
switch ($condi) {
    case 'prdscre':
        $xtra = $_POST["xtra"];
        $consulta = mysqli_query($general, "SELECT nombre, periodo FROM `tb_periodo` WHERE TipoCredito = '$xtra'");
        while ($crdperi = mysqli_fetch_array($consulta, MYSQLI_NUM)) {
            echo '<option value="' . $crdperi[1] . '"> ' . $crdperi[0] . ' </option>';
        }
        mysqli_close($conexion);
        break;
    case 'tpscre2':
        $xtra = $_POST["xtra"];
        switch ($xtra) {
                //SISTEMA NIVELADO
            case "Flat":
                echo '<h4 class="alert-heading">Nivelado</h4>
                <p> NIVELADO, FLAT, (Diario, semanal, quincenal), Interes y capital sera el mismos. </p> <hr>
                <p class="mb-0"> -Cuotas Constantes. <br>  -Los intereses son constantes, se calculan sobre la deuda. <br>  - Capital Fijo (La deuda dividida por el número de períodos).</p>
                <img src="../../includes/img/flat.png" class="w3-border w3-padding" alt="frances" width="400" height="200"> ';
                break;
                //SISTEMA FRANCES
            case "Franc":
                echo '<h4 class="alert-heading">Couta Fija</h4>
                <p>NIVELADO, SOBRE SALDO, (MENS-SEMESTRAL) ,tabla de amortizacion de créditos systema frances. </p> <hr>
                <p class="mb-0"> -Cuotas Constantes. <br>  -Los intereses son constantes, se calculan sobre Capital Restante . <br>  -La amortización del capital se hace en forma creciente, es lo contrario a la amortización de los intereses que se hace decreciente.  </p>
                <img src="../../includes/img/frances.png" class="w3-border w3-padding" alt="frances" width="400" height="200"> ';
                break;
                //SISTEMA ALEMAN
            case "Germa":
                echo '<h4 class="alert-heading">Sistema Aleman</h4>
                <p> PAGO FIJO A CAPITAL, (MENSUAL-SEMESTRAL), sistema aleman, interes variable, ,  interés a pagar se calculan sobre el saldo pendiente de pagar, </p><hr>
                <p class="mb-0">-Cuota Decrecientes. <br> -Interes variable. <br> - Capital Fijo (La deuda dividida por el número de períodos).</p>
                <img src="../../includes/img/aleman.png" class="w3-border w3-padding" alt="frances" width="400" height="200">';
                break;
                //SISTEMA AMERICANO
            case "Amer":
                echo '<h4 class="alert-heading">Capital Vencimiento</h4>
                <p> Capital Vencimiento, sistema americano, unica cuota, interes mensual, solo mensual.   </p> <hr>
                <p class="mb-0">- Couta interés constante. <br> - No existe capital amortizado.<br> - Última Couta conformada por capital prestado mas interés.</p>
                <img src="../../includes/img/american.png" class="w3-border w3-padding" alt="frances" width="400" height="200">';
                break;
        }
        break;
        /*------------------  CREDITOS INDIVIDUALES  *DESEMBOLSO* ------------------------------------------ */
    case 'solicitud_individual': {

            $codusu = $_SESSION['id'];
            $id_agencia = $_SESSION['id_agencia'];
            $codagencia = $_SESSION['agencia'];
            $xtra = $_POST["xtra"];

            //consultar
            $i = 0;
            $src = '../../includes/img/fotoClienteDefault.png';
            $bandera_creditos_proceso = false;
            $bandera_creditos_desembolsadods = false;
            $bandera = false;
            $bandera_garantias = false;
            $datos[] = [];
            $datosprocesos[] = [];
            $datosactivos[] = [];
            $datosgarantias[] = [];

            if ($xtra != 0) {
                $consulta = mysqli_query($conexion, "SELECT cl.idcod_cliente AS codcli, cl.short_name AS nomcli, cl.Direccion AS direccion,  (SELECT IFNULL(MAX(cm2.NCiclo),0)+1 AS ciclo FROM cremcre_meta cm2 WHERE cm2.CodCli='$xtra' AND cm2.TipoEnti='INDI' AND (cm2.Cestado='F' OR cm2.Cestado='G')) AS ciclo, cl.url_img AS urlfoto FROM tb_cliente cl
                WHERE cl.idcod_cliente='$xtra' AND cl.estado='1'");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $datos[$i] = $fila;
                    //CARGADO DE LA IMAGEN
                    $imgurl = __DIR__ . '/../../../../../' . $fila['urlfoto'];
                    if (!is_file($imgurl)) {
                        $src = '../../includes/img/fotoClienteDefault.png';
                    } else {
                        $imginfo   = getimagesize($imgurl);
                        $mimetype  = $imginfo['mime'];
                        $imageData = base64_encode(file_get_contents($imgurl));
                        $src = 'data:' . $mimetype . ';base64,' . $imageData;
                    }
                    $i++;
                    $bandera = true;
                }
                //consultar procesos de creditos en proceso de desembolso
                $i = 0;
                $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cm.Cestado AS estado FROM cremcre_meta cm WHERE cm.CodCli='$xtra' AND (cm.Cestado='A' OR cm.Cestado='D' OR cm.Cestado='E') AND cm.TipoEnti='INDI'");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $datosprocesos[$i] = $fila;
                    $i++;
                    $bandera_creditos_proceso = true;
                }
                //consultar procesos de creditos activos
                $i = 0;
                $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cm.Cestado AS estado, cm.MonSug AS monto FROM cremcre_meta cm WHERE cm.CodCli='$xtra' AND cm.Cestado='F' AND cm.TipoEnti='INDI'");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $datosactivos[$i] = $fila;
                    $i++;
                    $bandera_creditos_desembolsadods = true;
                }
                //CONSULTAR GARANTIAS
                $i = 0;
                $consulta = mysqli_query($conexion, "SELECT cl.idcod_cliente AS codcli, gr.idGarantia AS idgar, tipgar.id_TiposGarantia AS idtipgar, tipgar.TiposGarantia AS nomtipgar, tipc.idDoc AS idtipdoc, tipc.NombreDoc AS nomtipdoc,
                gr.descripcionGarantia AS descripcion, gr.direccion AS direccion, gr.montoGravamen AS montogravamen,
                IFNULL((SELECT cl2.short_name AS nomcli FROM tb_cliente cl2 WHERE cl2.idcod_cliente=gr.descripcionGarantia AND tipgar.id_TiposGarantia=1 AND tipc.idDoc=1),'x') AS nomcli,
                IFNULL((SELECT cl2.Direccion AS direccioncli FROM tb_cliente cl2 WHERE cl2.idcod_cliente=gr.descripcionGarantia AND tipgar.id_TiposGarantia=1 AND tipc.idDoc=1),'x') AS direccioncli
                FROM tb_cliente cl
                INNER JOIN cli_garantia gr ON cl.idcod_cliente=gr.idCliente
                INNER JOIN clhpzzvb_bd_general_coopera.tb_tiposgarantia tipgar ON gr.idTipoGa=tipgar.id_TiposGarantia
                INNER JOIN clhpzzvb_bd_general_coopera.tb_tiposdocumentosR tipc ON tipc.idDoc=gr.idTipoDoc
                WHERE cl.estado='1' AND gr.estado=1 AND cl.idcod_cliente='$xtra'");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $datosgarantias[$i] = $fila;
                    $i++;
                    $bandera_garantias = true;
                }
            }
            $selectagencia = 0; //0 NO SE MUESTRA EL SELECT DE AGENCIA, 1 PARA MOSTRAR EL SELECT DE AGENCIA
            $style = ($selectagencia == 1) ? "block" : "none";
            // echo '<pre>';
            // print_r($datosgarantias);
            // echo '</pre>';
            // echo $imgurl;
?>
            <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
            <input type="text" id="file" value="cre_indi_01" style="display: none;">
            <input type="text" id="condi" value="solicitud_individual" style="display: none;">
            <div class="text" style="text-align:center">SOLICITUD DE CRÉDITO INDIVIDUAL</div>
            <div class="card">
                <div class="card-header">Solicitud de crédito individual</div>
                <div class="card-body" style="padding-bottom: 0px !important;">

                    <!-- seleccion de cliente y su credito-->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Información de cliente</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 col-sm-6 col-md-2 mt-2">
                                <img width="120" height="130" id="vistaPrevia" src="<?php if ($bandera) {
                                                                                        echo $src;
                                                                                    } else {
                                                                                        echo $src;
                                                                                    } ?>">
                            </div>
                            <div class="col-12 col-sm-12 col-md-10">
                                <div class="row">
                                    <div class="col-12 col-sm-6">
                                        <div class="form-floating mb-2 mt-2">
                                            <input type="text" class="form-control" id="codcli" placeholder="Código de cliente" readonly <?php if ($bandera) {
                                                                                                                                                echo 'value="' . $datos[0]['codcli'] . '"';
                                                                                                                                            } ?>>
                                            <input type="text" name="" id="id_cod_cliente" hidden>
                                            <label for="cliente">Código de cliente</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <button type="button" class="btn btn-primary pt-3 pb-3 mb-2 mt-2 col-12 col-sm-12" onclick="abrir_modal('#modal_solicitud_01', '#id_modal_hidden', 'id')"><i class="fa-solid fa-magnifying-glass-plus me-2"></i>Buscar cliente</button>
                                    </div>
                                </div>
                                <!-- cargo, nombre agencia y codagencia  -->
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-5">
                                        <div class="form-floating mb-3 mt-2">
                                            <input type="text" class="form-control" id="nomcli" placeholder="Nombre cliente" readonly <?php if ($bandera) {
                                                                                                                                            echo 'value="' . $datos[0]['nomcli'] . '"';
                                                                                                                                        } ?>>
                                            <label for="nomcli">Nombre de cliente</label>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-md-5">
                                        <div class="form-floating mb-3 mt-2">
                                            <input type="text" class="form-control" id="direccion" placeholder="Dirección" readonly <?php if ($bandera) {
                                                                                                                                        echo 'value="' . $datos[0]['direccion'] . '"';
                                                                                                                                    } ?>>
                                            <label for="direccion">Dirección</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-2">
                                        <div class="form-floating mb-3 mt-2">
                                            <input type="text" class="form-control" id="ciclo" placeholder="Ciclo" readonly <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['ciclo'] . '"';
                                                                                                                            } ?>>
                                            <label for="ciclo">Ciclo</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- lineas de credito -->
                    <div class="container contenedort" style="max-width: 100% !important;" <?= ($bandera) ? ('') : ('hidden') ?>>
                        <div class="row">
                            <div class="col mt-2 mb-2">
                                <button type="button" class="btn btn-outline-primary" title="Buscar Grupo" onclick="abrir_modal('#modal_tiposcreditos', '#id_modal_hidden', 'idprod,codprod,nameprod,descprod,tasaprod,maxprod,ahorro/A,A,A,A,A,A,A/'+'/#/#/#/#')">
                                    <i class="fa-solid fa-magnifying-glass"> </i>Buscar Linea de Credito </button>
                            </div>
                        </div>

                        <div class="alert alert-primary" role="alert">
                            <div class="row crdbody">
                                <div class="col-sm-3">
                                    <div class="">
                                        <span class="fw-bold">Codigo Producto</span>
                                        <input type="number" class="form-control" id="idprod" readonly hidden>
                                        <input type="text" class="form-control" id="codprod" readonly>
                                    </div>
                                </div>
                                <div class="form-group col-sm-7">
                                    <span class="fw-bold">Nombre</span>
                                    <input type="text" class="form-control" id="nameprod" readonly>
                                </div>
                                <div class="form-group col-sm-2">
                                    <span class="fw-bold">%Interes Anual</span>
                                    <input type="number" step="0.01" class="form-control" id="tasaprod" readonly>
                                </div>
                            </div>
                            <div class="row crdbody">
                                <div class="form-group col-sm-6">
                                    <span class="fw-bold">Descripción</span>
                                    <input type="text" class="form-control" id="descprod" readonly>
                                </div>

                                <div class=" col-sm-3">
                                    <span class="fw-bold">Monto Maximo</span>
                                    <input type="number" step="0.01" class="form-control" id="maxprod" readonly>
                                </div>
                                <div class="col-sm-3">
                                    <span class="fw-bold">Fuente de fondos</span>
                                    <input type="text" step="0.01" class="form-control" id="ahorro" readonly>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- datos adicionales -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Datos complementarios</b></div>
                            </div>
                        </div>
                        <div class="row" style="display:<?php echo $style ?>">
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="agenciaaplica">
                                        <option value="0" selected disabled>Seleccione una agencia</option>
                                        <?php
                                        $consulta = mysqli_query($conexion, "SELECT nom_agencia, id_agencia FROM tb_agencia");
                                        while ($dtas = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                            $nomage = $dtas["nom_agencia"];
                                            $id_age = $dtas["id_agencia"];
                                            $selected = ($id_agencia == $id_age) ? " selected" : "";
                                            echo '<option' . $selected . ' value="' . $id_age . '">' . $nomage . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="agenciaaplica">Agencia</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="analista">
                                        <option value="0" selected>Seleccione un analista</option>
                                        <?php
                                        //$consulta = mysqli_query($conexion, "SELECT CONCAT(nombre, ' ', apellido) AS nameusu , id_usu FROM tb_usuario WHERE puesto='ANA' AND id_agencia IN( SELECT id_agencia FROM tb_usuario WHERE id_usu=$codusu)");
                                        $consulta = mysqli_query($conexion, "SELECT CONCAT(nombre, ' ', apellido) AS nameusu , id_usu FROM tb_usuario WHERE puesto='ANA'");
                                        while ($dtas = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                            $nombre = $dtas["nameusu"];
                                            $id_usu = $dtas["id_usu"];
                                            echo '<option value="' . $id_usu . '">' . $nombre . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="analista">Analista</label>
                                </div>
                            </div>
                        </div>
                        <!-- cargo, nombre agencia y codagencia  -->
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="montosol" placeholder="Monto solicitado" min="0" step="0.01">
                                    <label for="montosol">Monto solicitado</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="destino">
                                        <option value="0" selected>Seleccione un destino</option>
                                        <?php
                                        $quersec = mysqli_query($general, "SELECT id_DestinoCredito AS id, DestinoCredito AS destino FROM `tb_destinocredito`");
                                        while ($sect = mysqli_fetch_array($quersec, MYSQLI_ASSOC)) {
                                            $id = $sect["id"];
                                            $destino = $sect["destino"];
                                            echo '<option value="' . $id . '">' . $destino . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="destino">Destino de crédito</label>
                                </div>
                            </div>
                        </div>
                        <!-- sector y actividad -->
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="sector" onchange="buscar_actividadeconomica(this.value)">
                                        <option value="0" selected>Seleccione un sector económico</option>
                                        <?php
                                        $quersec = mysqli_query($general, "SELECT id_SectoresEconomicos, SectoresEconomicos FROM `tb_sectoreseconomicos`");
                                        while ($sect = mysqli_fetch_array($quersec, MYSQLI_ASSOC)) {
                                            $idSctr = $sect["id_SectoresEconomicos"];
                                            $SctrEcono = $sect["SectoresEconomicos"];
                                            echo '<option value="' . $idSctr . '">' . $SctrEcono . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="destino">Sector económico</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="actividadeconomica">
                                        <option value="0" selected>Seleccione una actividad económica</option>
                                    </select>
                                    <label for="destino">Actividad económica</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- datos -->

                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Datos del credito</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="tipocred" onchange="creperi('tpscre2','#alrtpnl','cre_indi_01',this.value)">
                                        <option value="0" selected disabled>Seleccione un tipo de crédito</option>
                                        <?php
                                        $consulta = mysqli_query($general, "SELECT abre, Credito FROM `tb_credito`");
                                        while ($dtas = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                            $id_abre = $dtas["abre"];
                                            $nomtip = $dtas["Credito"];
                                            echo '<option value="' . $id_abre . '">' . $nomtip . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="tipocred">Tipo de crédito</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="peri">
                                        <option value="0" selected disabled>Seleccione un tipo de periodo</option>

                                    </select>
                                    <label for="peri">Tipo de periodo</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control" id="primerpago" placeholder="Fecha primer pago" <?php echo 'value="' . date('Y-m-d') . '"'; ?>>
                                    <label for="primerpago">Fecha primer pago</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="number" min="1" class="form-control" id="cuota" placeholder="No de cuotas">
                                    <label for="cuota">No. de cuotas</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container contenedort">
                        <div class="accordion accordion-flush" id="accordionFlushExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                        <b>Datos adicionales</b>
                                    </button>
                                </h2>
                                <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <textarea type="text" class="form-control" id="crecimiento" placeholder="Crecimiento"></textarea>
                                                    <label for="primerpago">Crecimiento</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <textarea type="text" class="form-control" id="recomendacion" placeholder="Recomendacion"></textarea>
                                                    <label for="cuota">Recomendacion del oficial de crédito</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- SECCION DE GARANTIAS -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Garantías del cliente</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-2">
                                <div class="table-responsive">
                                    <table class="table mb-0" style="font-size: 0.8rem !important;">
                                        <thead>
                                            <tr>
                                                <th scope="col">Tipo Garantia</th>
                                                <th scope="col">Tipo Doc.</th>
                                                <th scope="col">Descripción</th>
                                                <th scope="col">Dirección</th>
                                                <th scope="col">Valor gravamen</th>
                                                <th scope="col">Marcar</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-group-divider">
                                            <?php if ($bandera_garantias && !$bandera_creditos_proceso) {
                                                for ($i = 0; $i < count($datosgarantias); $i++) { ?>
                                                    <tr>
                                                        <td scope="row"><?= ($datosgarantias[$i]["nomtipgar"]) ?></td>
                                                        <td><?= ($datosgarantias[$i]["nomtipdoc"]) ?></td>
                                                        <!-- VALIDAR SI ES UN GARANTIA NORMAL O ES UN FIADOR -->
                                                        <?php if ($datosgarantias[$i]["idtipgar"] == 1 && $datosgarantias[$i]["idtipdoc"] == 1) { ?>
                                                            <td><?= ($datosgarantias[$i]["nomcli"]) ?></td>
                                                            <td><?= ($datosgarantias[$i]["direccioncli"]) ?></td>
                                                        <?php } else { ?>
                                                            <td><?= ($datosgarantias[$i]["descripcion"]) ?></td>
                                                            <td><?= ($datosgarantias[$i]["direccion"]) ?></td>
                                                        <?php } ?>
                                                        <td><?= ($datosgarantias[$i]["montogravamen"]) ?></td>
                                                        <td>
                                                            <input class="form-check-input S" type="checkbox" value="<?= $datosgarantias[$i]['idgar']; ?>" id="<?= "S_" . $datosgarantias[$i]['idgar']; ?>">
                                                        </td>
                                                    </tr>
                                            <?php }
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ALERTA PARA MOSTRAR QUE NO SE PUEDE SOLICITAR UN NUEVO CREDITO -->
                    <?php
                    if ($bandera_creditos_proceso) { ?>
                        <div class="alert alert-danger" role="alert" style="margin-bottom: 0px !important;">
                            <div class="row">
                                <div class="col mb-3">
                                    <h4 class="alert-heading">IMPORTANTE!</h4>
                                    <p>El cliente seleccionado no se le permite solicitar un nuevo credito porque ya posee al menos un credito en proceso, debe de cancelarlos o proseguir con los mismos.</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col mb-2">
                                    <div class="text-center"><b>INFORMACIÓN DE CREDITOS EN PROCESO</b></div>
                                </div>
                            </div>
                            <?php for ($i = 0; $i < count($datosprocesos); $i++) {
                                $estado = "";
                                if ($datosprocesos[$i]['estado'] == 'A') {
                                    $estado = "Solicitado";
                                } elseif ($datosprocesos[$i]['estado'] == 'D') {
                                    $estado = "Analizado";
                                } else {
                                    $estado = "Aprobado";
                                }
                            ?>
                                <div class="row">
                                    <div class="col">
                                        <p class="me-2"><b class="me-2">No:</b><span class="me-3"><?= $i + 1; ?></span><b class="me-2">Código de crédito:</b><span class="me-3"><?= $datosprocesos[$i]['ccodcta']; ?></span><b class="me-2">Estado:</b><span><?= $estado; ?></span></p>
                                    </div>
                                </div>
                            <?php    } ?>
                        </div>
                    <?php  }
                    ?>

                    <!-- ALERTA DE QUE YA HAY UN CREDITO EN ESTADO F PARA SEGUIR CON EL PROCESO -->
                    <?php if ($bandera_creditos_desembolsadods) { ?>
                        <div class="alert alert-warning mt-2" role="alert" style="margin-bottom: 0px !important;">
                            <div class="row">
                                <div class="col mb-3">
                                    <h4 class="alert-heading">ADVERTENCIA!</h4>
                                    <p>Advertencia! El cliente seleccionado tiene un crédito en estado activo.</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col mb-2">
                                    <div class="text-center"><b>INFORMACIÓN DE CRÉDITOS ACTIVOS</b></div>
                                </div>
                            </div>
                            <?php for ($i = 0; $i < count($datosactivos); $i++) {
                            ?>
                                <div class="row">
                                    <div class="col">
                                        <p class="me-2"><b class="me-2">No:</b><span class="me-3"><?= $i + 1; ?></span><b class="me-2">Código de crédito:</b><span class="me-3"><?= $datosactivos[$i]['ccodcta'] ?></span><b class="me-2">Estado:</b><span class="me-3">Activo</span><b class="me-2">Monto Desembolsado:</b><span><?= $datosactivos[$i]['monto'] ?></span></p>
                                    </div>
                                </div>
                            <?php    } ?>
                        </div>
                    <?php  } ?>
                    <!-- ALERTA PARA AVISAR QUE NO HAY GARANTIAS -->
                    <?php if (!$bandera_garantias && $bandera) { ?>
                        <div class="alert alert-danger mt-2" role="alert" style="margin-bottom: 0px !important;">
                            <div class="row">
                                <div class="col mb-3">
                                    <h4 class="alert-heading">IMPORTANTE!</h4>
                                    <p>El cliente no tiene registrado al menos una garantia, por lo que no puede solicitar el crédito, Ingrese en la opción de garantías y agregue al menos uno para este cliente.</p>
                                </div>
                            </div>
                        </div>
                    <?php  } ?>
                    <!-- ALERTA PARA BUSCAR UN CLIENTE  -->
                    <?php if (!$bandera) { ?>
                        <div class="alert alert-success" role="alert" style="margin-bottom: 0px !important;">
                            <h4 class="alert-heading">IMPORTANTE!</h4>
                            <p>Debe seleccionar un cliente y luego seleccionar un tipo de crédito para hacer una solicitud</p>
                        </div>
                    <?php  } ?>
                </div>

                <div class="container" style="max-width: 100% !important;">
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center mb-3 ms-2" id="modal_footer">
                            <!-- boton para solicitar credito -->
                            <?php if (!$bandera_creditos_proceso && $bandera && $bandera_garantias) { ?>
                                <button class="btn btn-outline-success mt-2" onclick="obtiene([`codcli`,`nomcli`,`ciclo`,`codprod`,`tasaprod`,`maxprod`,`montosol`,`idprod`,`primerpago`,`cuota`,`crecimiento`,`recomendacion`],[`analista`,`destino`,`sector`,`actividadeconomica`,`agenciaaplica`,`tipocred`,`peri`],[],`create_solicitud`,`0`,['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>','<?= $datos[0]['ciclo']; ?>',recoletar_checks()])"><i class="fa-solid fa-floppy-disk me-2"></i>Solicitar crédito</button>
                            <?php } ?>
                            <button type="button" class="btn btn-outline-danger mt-2" onclick="printdiv2('#cuadro','0')">
                                <i class="fa-solid fa-ban"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-outline-warning mt-2" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            include_once "../../../src/cris_modales/mdls_solicitud_01.php";
            include_once "../../../src/cris_modales/mdls_credlin_indi.php";
            ?>
            <script>
                $(document).ready(function() {
                    buscar_actividadeconomica(0);
                })
            </script>
        <?php
        }
        break;
    case 'analisis_individual': {
            $codusu = $_SESSION['id'];
            $id_agencia = $_SESSION['id_agencia'];
            $codagencia = $_SESSION['agencia'];
            $xtra = $_POST["xtra"];

            //consultar
            $i = 0;
            $sumagarantias = 0;
            $src = '../../includes/img/fotoClienteDefault.png';
            $bandera_garantias = false;
            $bandera_garantias2 = false;
            $bandera = false;
            $datos[] = [];
            $datosgarantias[] = [];
            $datosgarantiasrecuperados[] = [];

            if ($xtra != 0) {
                $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta,cm.cuotassolicita, cm.CodCli AS codcli, cl.short_name AS nomcli, cl.Direccion AS direccion, (SELECT IFNULL(MAX(cm2.NCiclo),0)+1 AS ciclo FROM cremcre_meta cm2 WHERE cm2.CodCli=cm.CodCli AND cm2.TipoEnti='INDI' AND (cm2.Cestado='F' OR cm2.Cestado='G')) AS ciclo,  cm.Cestado AS estado,
                cp.id AS idprod, cp.cod_producto AS codprod, cp.nombre AS nomprod, cm.NIntApro AS interesprod, cp.descripcion AS descprod, cp.monto_maximo AS montoprod, ff.descripcion AS nomfondo,
                cm.MontoSol AS montosol, cm.MonSug AS montosug, cm.CtipCre AS tipocred, cm.NtipPerC AS tipoper, cm.DfecPago AS primerpago, cm.noPeriodo AS cuotas, cm.DFecDsbls AS fecdesembolso, cm.Dictamen AS dictamen,
                us.id_usu AS idanalista, CONCAT(us.nombre, ' ', us.apellido) AS nombreanalista, cl.url_img AS urlfoto,cm.crecimiento,cm.recomendacion
                FROM cremcre_meta cm
                INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
                INNER JOIN cre_productos cp ON cm.CCODPRD=cp.id
                INNER JOIN ctb_fuente_fondos ff ON cp.id_fondo=ff.id
                INNER JOIN tb_usuario us ON cm.CodAnal=us.id_usu
                WHERE (cm.Cestado='A' OR cm.Cestado='D') AND cm.TipoEnti='INDI' AND cm.CCODCTA='$xtra'");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $estado = ($fila['estado'] == 'A') ? 'Solicitado' : 'Analizado';
                    $datos[$i] = $fila;
                    $datos[$i]['estado2'] = $estado;
                    //CARGADO DE LA IMAGEN
                    $imgurl = __DIR__ . '/../../../../../' . $fila['urlfoto'];
                    if (!is_file($imgurl)) {
                        $src = '../../includes/img/fotoClienteDefault.png';
                    } else {
                        $imginfo   = getimagesize($imgurl);
                        $mimetype  = $imginfo['mime'];
                        $imageData = base64_encode(file_get_contents($imgurl));
                        $src = 'data:' . $mimetype . ';base64,' . $imageData;
                    }
                    $i++;
                    $bandera = true;
                }

                //CONSULTAR TODAS LAS GARANTIAS
                $i = 0;
                $consulta = mysqli_query($conexion, "SELECT cl.idcod_cliente AS codcli, gr.idGarantia AS idgar, tipgar.id_TiposGarantia AS idtipgar, tipgar.TiposGarantia AS nomtipgar, tipc.idDoc AS idtipdoc, tipc.NombreDoc AS nomtipdoc,
                gr.descripcionGarantia AS descripcion, gr.direccion AS direccion, gr.montoGravamen AS montogravamen,
                IFNULL((SELECT cl2.short_name AS nomcli FROM tb_cliente cl2 WHERE cl2.idcod_cliente=gr.descripcionGarantia AND tipgar.id_TiposGarantia=1 AND tipc.idDoc=1),'x') AS nomcli,
                IFNULL((SELECT cl2.Direccion AS direccioncli FROM tb_cliente cl2 WHERE cl2.idcod_cliente=gr.descripcionGarantia AND tipgar.id_TiposGarantia=1 AND tipc.idDoc=1),'x') AS direccioncli
                FROM tb_cliente cl
                INNER JOIN cli_garantia gr ON cl.idcod_cliente=gr.idCliente
                INNER JOIN clhpzzvb_bd_general_coopera.tb_tiposgarantia tipgar ON gr.idTipoGa=tipgar.id_TiposGarantia
                INNER JOIN clhpzzvb_bd_general_coopera.tb_tiposdocumentosR tipc ON tipc.idDoc=gr.idTipoDoc
                WHERE cl.estado='1' AND gr.estado=1 AND cl.idcod_cliente='" . $datos[0]['codcli'] . "'");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $datosgarantias[$i] = $fila;
                    $i++;
                    $bandera_garantias = true;
                }

                //CONSULTAR LOS REGISTROS SELECCIONADOS
                $i = 0;
                $consulta = mysqli_query($conexion, "SELECT gc.id_garantia AS id, clg.montoGravamen AS montogravamen FROM tb_garantias_creditos gc
                INNER JOIN cli_garantia clg ON gc.id_garantia=clg.idGarantia
                WHERE gc.id_cremcre_meta='$xtra'");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $sumagarantias = $sumagarantias + $fila["montogravamen"];
                    $datosgarantiasrecuperados[$i] = $fila;
                    $i++;
                    $bandera_garantias2 = true;
                }
            }


            // echo '<pre>';
            // print_r($datosgarantiasrecuperados);
            // echo '</pre>';
        ?>
            <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
            <input type="text" id="file" value="cre_indi_01" style="display: none;">
            <input type="text" id="condi" value="analisis_individual" style="display: none;">
            <div class="text" style="text-align:center">ANÁLISIS DE CRÉDITO INDIVIDUAL</div>
            <div class="card">
                <div class="card-header">Análisis de crédito individual</div>
                <div class="card-body" style="padding-bottom: 0px !important;">

                    <!-- seleccion de cliente y su credito-->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Información de cliente</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 col-sm-6 col-md-2 mt-2">
                                <img width="120" height="130" id="vistaPrevia" src="<?php if ($bandera) {
                                                                                        echo $src;
                                                                                    } else {
                                                                                        echo $src;
                                                                                    } ?>">
                            </div>
                            <div class="col-12 col-sm-12 col-md-10">
                                <div class="row">
                                    <div class="col-12 col-sm-6">
                                        <div class="form-floating mb-2 mt-2">
                                            <input type="text" class="form-control" id="ccodcta" placeholder="Código de credito" readonly <?php if ($bandera) {
                                                                                                                                                echo 'value="' . $datos[0]['ccodcta'] . '"';
                                                                                                                                            } ?>>
                                            <label for="cliente">Código de crédito</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <button type="button" class="btn btn-primary pt-3 pb-3 mb-2 mt-2 col-12 col-sm-12" onclick="abrir_modal('#modal_analisis_01', '#id_modal_hidden', 'id')"><i class="fa-solid fa-magnifying-glass-plus me-2"></i>Buscar cliente</button>
                                    </div>
                                </div>
                                <!-- cargo, nombre agencia y codagencia  -->
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-5">
                                        <div class="form-floating mb-2 mt-2">
                                            <input type="text" class="form-control" id="codcli" placeholder="Código de cliente" readonly <?php if ($bandera) {
                                                                                                                                                echo 'value="' . $datos[0]['codcli'] . '"';
                                                                                                                                            } ?>>
                                            <input type="text" name="" id="id_cod_cliente" hidden>
                                            <label for="cliente">Código de cliente</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-5">
                                        <div class="form-floating mb-2 mt-2">
                                            <input type="text" class="form-control" id="nomcli" placeholder="Nombre cliente" readonly <?php if ($bandera) {
                                                                                                                                            echo 'value="' . $datos[0]['nomcli'] . '"';
                                                                                                                                        } ?>>
                                            <label for="nomcli">Nombre de cliente</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-2">
                                        <div class="form-floating mb-2 mt-2">
                                            <input type="text" class="form-control" id="ciclo" placeholder="Ciclo" readonly <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['ciclo'] . '"';
                                                                                                                            } ?>>
                                            <label for="ciclo">Ciclo</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-8">
                                <div class="form-floating mb-3 mt-2">
                                    <input type="text" class="form-control" id="direccion" placeholder="Dirección" readonly <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['direccion'] . '"';
                                                                                                                            } ?>>
                                    <label for="direccion">Dirección</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="form-floating mb-3 mt-2">
                                    <input type="text" class="form-control" id="estado" placeholder="Estado" readonly <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['estado2'] . '"';
                                                                                                                        } ?>>
                                    <label for="estado">Estado</label>
                                </div>
                            </div>
                        </div>
                        <?php if ($bandera) {
                        ?>
                            <div class="row">
                                <div class="col-6">
                                    <a class="link-primary" style="cursor: pointer;" onclick="accesodirecto(1,`<?= $datos[0]['codcli'] ?>`)">Perfil económico</a>
                                </div>
                                <div class="col-6">
                                    <a class="link-success" style="cursor: pointer;" onclick="accesodirecto(2,`<?= $datos[0]['codcli'] ?>`)">Balance económico</a>
                                </div>
                            </div>
                        <?php
                        } ?>
                    </div>
                    <div class="container contenedort">
                        <div class="accordion accordion-flush" id="accordionFlushExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                        <b>Datos adicionales</b>
                                    </button>
                                </h2>
                                <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <textarea readonly type="text" class="form-control" id="crecimiento" placeholder="Crecimiento"><?php echo ($bandera) ? $datos[0]['crecimiento'] : ''; ?></textarea>
                                                    <label for="primerpago">Crecimiento</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <textarea readonly type="text" class="form-control" id="recomendacion" placeholder="Recomendacion"><?php echo ($bandera) ? $datos[0]['recomendacion'] : ''; ?></textarea>
                                                    <label for="cuota">Recomendacion del oficial de crédito</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- lineas de credito -->
                    <div class="container contenedort" style="max-width: 100% !important;" <?php //($bandera) ? ('') : ('hidden') 
                                                                                            ?>>
                        <div class="row">
                            <div class="col mt-2 mb-2">
                                <button <?= ($bandera) ? ('') : ('disabled') ?> type="button" class="btn btn-outline-primary" title="Buscar Grupo" onclick="abrir_modal('#modal_tiposcreditos', '#id_modal_hidden', 'idprod,codprod,nameprod,descprod,tasaprod,maxprod,ahorro/A,A,A,A,A,A,A/'+'/#/#/#/#')">
                                    <i class="fa-solid fa-magnifying-glass"> </i>Buscar Linea de Credito </button>
                            </div>
                        </div>

                        <div class="alert alert-primary" role="alert">
                            <div class="row crdbody">
                                <div class="col-sm-3">
                                    <div class="">
                                        <span class="fw-bold">Codigo Producto</span>
                                        <input type="number" class="form-control" id="idprod" readonly hidden <?php if ($bandera) {
                                                                                                                    echo 'value="' . $datos[0]['idprod'] . '"';
                                                                                                                } ?>>
                                        <input type="text" class="form-control" id="codprod" readonly <?php if ($bandera) {
                                                                                                            echo 'value="' . $datos[0]['codprod'] . '"';
                                                                                                        } ?>>
                                    </div>
                                </div>
                                <div class="form-group col-sm-7">
                                    <span class="fw-bold">Nombre</span>
                                    <input type="text" class="form-control" id="nameprod" readonly <?php if ($bandera) {
                                                                                                        echo 'value="' . $datos[0]['nomprod'] . '"';
                                                                                                    } ?>>
                                </div>
                                <div class="form-group col-sm-2">
                                    <span class="fw-bold">%Interes </span>
                                    <input type="number" step="0.01" class="form-control" id="tasaprod" <?php if (!$bandera) {
                                                                                                            echo 'disabled';
                                                                                                        }
                                                                                                        if ($bandera) {
                                                                                                            echo 'value="' . $datos[0]['interesprod'] . '"';
                                                                                                        } ?>>
                                </div>
                            </div>
                            <div class="row crdbody">
                                <div class="form-group col-sm-6">
                                    <span class="fw-bold">Descripción</span>
                                    <input type="text" class="form-control" id="descprod" readonly <?php if ($bandera) {
                                                                                                        echo 'value="' . $datos[0]['descprod'] . '"';
                                                                                                    } ?>>
                                </div>

                                <div class=" col-sm-3">
                                    <span class="fw-bold">Monto Maximo</span>
                                    <input type="number" step="0.01" class="form-control" id="maxprod" readonly <?php if ($bandera) {
                                                                                                                    echo 'value="' . $datos[0]['montoprod'] . '"';
                                                                                                                } ?>>
                                </div>
                                <div class="col-sm-3">
                                    <span class="fw-bold">Fuente de fondos</span>
                                    <input type="text" step="0.01" class="form-control" id="ahorro" readonly <?php if ($bandera) {
                                                                                                                    echo 'value="' . $datos[0]['nomfondo'] . '"';
                                                                                                                } ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- datos adicionales -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Datos complementarios</b></div>
                            </div>
                        </div>
                        <!-- Monto aprobado y solicitado -->
                        <div class="row">
                            <div class="col-4">
                                <div class="form-floating mb-3 mt-2">
                                    <select class="form-select" id="analista">
                                        <option value="0" selected>Seleccione un analista</option>
                                        <?php
                                        //$consulta = mysqli_query($conexion, "SELECT CONCAT(nombre, ' ', apellido) AS nameusu , id_usu FROM tb_usuario WHERE puesto='ANA' AND id_agencia IN( SELECT id_agencia FROM tb_usuario WHERE id_usu=$codusu)");
                                        $consulta = mysqli_query($conexion, "SELECT CONCAT(nombre, ' ', apellido) AS nameusu , id_usu FROM tb_usuario WHERE puesto='ANA'");
                                        $selected = "";
                                        while ($dtas = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                            if ($bandera) {
                                                ($dtas["id_usu"] == $datos[0]['idanalista']) ? $selected = "selected" : $selected = "";
                                            }
                                            $nombre = $dtas["nameusu"];
                                            $id_usu = $dtas["id_usu"];
                                            echo '<option value="' . $id_usu . '" ' . $selected . '>' . $nombre . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="analista">Analista</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="form-floating mb-3 mt-2">
                                    <input type="text" class="form-control" id="garantia" placeholder="Valor garantia" readonly disabled <?php if ($bandera_garantias) {
                                                                                                                                                echo 'value="' . $sumagarantias . '"';
                                                                                                                                            } ?>>
                                    <label for="montosol">Valor total de garantias</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="form-floating mb-3 mt-2">
                                    <input disabled readonly type="number" min="1" class="form-control" id="cuotasol" placeholder="No de cuotas" value="<?php echo ($bandera) ? $datos[0]['cuotassolicita'] : ''; ?>">
                                    <label for="cuotasol">Cuotas solicitadas</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-4">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="montosol" placeholder="Monto solicitado" readonly <?php if ($bandera) {
                                                                                                                                        echo 'value="' . $datos[0]['montosol'] . '"';
                                                                                                                                    } ?>>
                                    <label for="montosol">Monto solicitado</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="form-floating mb-3">
                                    <input min="0" type="number" class="form-control" id="montosug" placeholder="Monto por aprobar" <?php if ($bandera && $datos[0]['cuotas'] != '') {
                                                                                                                                        echo 'value="' . $datos[0]['montosug'] . '"';
                                                                                                                                    } ?>>
                                    <label for="montosug">Monto a aprobar</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control" id="primerpago" placeholder="Fecha primer pago" value="<?php echo ($bandera) ? $datos[0]['primerpago'] : date('Y-m-d'); ?>">
                                    <label for="primerpago">Fecha primer pago</label>
                                </div>
                            </div>
                        </div>
                        <!-- tipo de credito, tipo periodo, fecha primer cuota -->
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="tipocred" onchange="creperi('tpscre2','#alrtpnl','cre_indi_01',this.value)">
                                        <option value="0" selected disabled>Seleccione un tipo de crédito</option>
                                        <?php
                                        $consulta = mysqli_query($general, "SELECT abre, Credito FROM `tb_credito`");
                                        while ($dtas = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                            $id_abre = $dtas["abre"];
                                            $nomtip = $dtas["Credito"];
                                            echo '<option value="' . $id_abre . '">' . $nomtip . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="tipocred">Tipo de crédito</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="peri">
                                        <option value="0" selected disabled>Seleccione un tipo de periodo</option>

                                    </select>
                                    <label for="peri">Tipo de periodo</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="form-floating mb-3">
                                    <input type="number" min="1" class="form-control" id="cuota" placeholder="No de cuotas" value="<?php echo (!$bandera) ? '' : (($datos[0]['cuotas'] == '') ? $datos[0]['cuotassolicita'] : $datos[0]['cuotas']); ?>">
                                    <label for="cuota">No. de cuotas a aprobar</label>
                                </div>
                            </div>
                        </div>
                        <!-- cuota, desembolso y dictamen -->
                        <div class="row">
                            <div class="col-12 col-sm-4">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control" id="fecdesembolso" placeholder="Fecha de desembolso" <?php if ($bandera && $datos[0]['cuotas'] != '') {
                                                                                                                                        echo 'value="' . $datos[0]['fecdesembolso'] . '"';
                                                                                                                                    } else {
                                                                                                                                        echo 'value="' . date('Y-m-d') . '"';
                                                                                                                                    } ?>>
                                    <label for="fecdesembolso">Fecha de desembolso</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="dictamen" placeholder="No. Dictamen" <?php if ($bandera && $datos[0]['cuotas'] != '') {
                                                                                                                            echo 'value="' . $datos[0]['dictamen'] . '"';
                                                                                                                        } ?>>
                                    <label for="dictamen">No. Dictamen</label>
                                </div>
                            </div>
                        </div>
                        <!-- MENSAJE PARA MOSTRAR LOS TIPOS DE CREDITOS -->
                        <div class="row">
                            <div class="col">
                                <div class="input-group" id="tipsMEns">
                                    <div class="alert alert-success" role="alert" id="alrtpnl">
                                        <h4>Seleccione un tipo de crédito y un periodo de crédito</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- BOTON PARA GUARDAR CAMBIOS Y ASI GENERAR EL PLAN DE PAGO -->
                        <div class="row">
                            <div class="col">
                                <?php if ($bandera && $bandera_garantias) { ?>
                                    <button type="button" class="btn btn-outline-success" onclick="obtiene([`ccodcta`,`codcli`,`nomcli`,`tasaprod`,`montosol`,`montosug`,`primerpago`,`cuota`,`fecdesembolso`,`dictamen`,`idprod`,`codprod`,`maxprod`],[`tipocred`,`peri`,`analista`],[],`update_analisis`,'<?= $datos[0]['ccodcta']; ?>',['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>','<?= $datos[0]['ciclo']; ?>',recoletar_checks()])"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar cambios</button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <!-- SECCION DE GARANTIAS -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Garantías del cliente</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-2">
                                <div class="table-responsive">
                                    <table class="table mb-0" style="font-size: 0.8rem !important;">
                                        <thead>
                                            <tr>
                                                <th scope="col">Tipo Garantia</th>
                                                <th scope="col">Tipo Doc.</th>
                                                <th scope="col">Descripción</th>
                                                <th scope="col">Dirección</th>
                                                <th scope="col">Valor gravamen</th>
                                                <th scope="col">Marcar</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-group-divider">
                                            <?php if ($bandera_garantias) {
                                                for ($i = 0; $i < count($datosgarantias); $i++) { ?>
                                                    <tr>
                                                        <td scope="row"><?= ($datosgarantias[$i]["nomtipgar"]) ?></td>
                                                        <td><?= ($datosgarantias[$i]["nomtipdoc"]) ?></td>
                                                        <!-- VALIDAR SI ES UN GARANTIA NORMAL O ES UN FIADOR -->
                                                        <?php if ($datosgarantias[$i]["idtipgar"] == 1 && $datosgarantias[$i]["idtipdoc"] == 1) { ?>
                                                            <td><?= ($datosgarantias[$i]["nomcli"]) ?></td>
                                                            <td><?= ($datosgarantias[$i]["direccioncli"]) ?></td>
                                                        <?php } else { ?>
                                                            <td><?= ($datosgarantias[$i]["descripcion"]) ?></td>
                                                            <td><?= ($datosgarantias[$i]["direccion"]) ?></td>
                                                        <?php } ?>
                                                        <td><span id="<?= "MA_" . $datosgarantias[$i]['idgar']; ?>"><?= ($datosgarantias[$i]["montogravamen"]) ?></span></td>
                                                        <td>
                                                            <input class="form-check-input S" type="checkbox" value="<?= $datosgarantias[$i]['idgar']; ?>" id="<?= "S_" . $datosgarantias[$i]['idgar']; ?>" onclick="suma_garantias_de_chequeados('#garantia')">
                                                        </td>
                                                    </tr>
                                            <?php }
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- BOTON PARA GUARDAR CAMBIOS Y ASI GENERAR EL PLAN DE PAGO -->
                        <div class="row">
                            <div class="col mb-2">
                                <?php if ($bandera_garantias) { ?>
                                    <button type="button" class="btn btn-outline-success" onclick="obtiene([`ccodcta`,`codcli`],[],[],`update_garantias`,'<?= $datos[0]['ccodcta']; ?>',['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>','<?= $datos[0]['ciclo']; ?>',recoletar_checks()])"><i class="fa-solid fa-floppy-disk me-2"></i>Actualizar garantias</button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <!-- ALERTA PARA BUSCAR UN CLIENTE  -->
                    <?php if (!$bandera) { ?>
                        <div class="alert alert-warning" role="alert" style="margin-bottom: 0px !important;">
                            <h4 class="alert-heading">IMPORTANTE!</h4>
                            <p>Debe seleccionar un cliente a analizar</p>
                        </div>
                    <?php  } ?>
                    <!--  -->
                    <?php if ($bandera) {
                        if (!$bandera_garantias && !$bandera_garantias2) { ?>
                            <div class="alert alert-warning" role="alert" style="margin-bottom: 0px !important;">
                                <h4 class="alert-heading">IMPORTANTE!</h4>
                                <p>No se puede seguir con el analisis del cliente debido a que no se cargaron correctamente las garantias.</p>
                            </div>
                        <?php  } elseif ($bandera_garantias && !$bandera_garantias2) { ?>
                            <div class="alert alert-warning" role="alert" style="margin-bottom: 0px !important;">
                                <h4 class="alert-heading">IMPORTANTE!</h4>
                                <p>El cliente no tiene al menos una garantia seleccionada, por lo que debe seleccionar al menos uno y presionar el boton actualizar garantias para actualizar datos y seguir con el proceso.</p>
                            </div>
                            <?php } else {
                            if ($bandera_garantias && $bandera_garantias2 && $datos[0]['cuotas'] == '') { ?>
                                <div class="alert alert-warning" role="alert" style="margin-bottom: 0px !important;">
                                    <h4 class="alert-heading">IMPORTANTE!</h4>
                                    <p>Debe llenar los datos faltantes en la seccion de datos complementarios y luego presionar el boton guardar cambios para poder visualizar el plan de pago, aprobar el credito o bien rechazar el crédito.</p>
                                </div>
                    <?php }
                        }
                    } ?>
                </div>

                <div class="container" style="max-width: 100% !important;">
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center mb-3 ms-2" id="modal_footer">
                            <!-- boton para solicitar credito -->
                            <?php if ($bandera && $bandera_garantias2 && $bandera_garantias && $datos[0]['cuotas'] != '') { ?>
                                <button class="btn btn-outline-success mt-2" onclick="obtiene([`ccodcta`,`codcli`,`nomcli`,`tasaprod`,`montosol`,`montosug`,`primerpago`,`cuota`,`fecdesembolso`,`dictamen`,`idprod`,`codprod`,`maxprod`],[`tipocred`,`peri`,`analista`],[],`create_analisis`,`0`,['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>','<?= $datos[0]['ciclo']; ?>','<?= $bandera_garantias; ?>',recoletar_checks()])"><i class="fa-solid fa-floppy-disk me-2"></i>Aprobar crédito</button>
                                <button type="button" class="btn btn-outline-primary mt-2" onclick="reportes([[],[],[],['<?= $datos[0]['ccodcta']; ?>']], `pdf`, `planPago`,0)"> Generar plan </button>
                                <button type="button" class="btn btn-outline-danger mt-2" onclick="abrir_modal_cualquiera_con_valor('#modal_cancelar_credito', '#id_hidden', `<?= $datos[0]['ccodcta']; ?>,<?= $datos[0]['codcli']; ?>`,[`#credito`,`#nombre`])"><i class="fa-solid fa-sack-xmark me-2"></i>Rechazar Crédito</button>
                            <?php } ?>
                            <!-- Boton de rechazo -->
                            <?php if ($bandera) { ?>
                            <?php } ?>
                            <button type="button" class="btn btn-outline-danger mt-2" onclick="printdiv2('#cuadro','0')">
                                <i class="fa-solid fa-ban"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-outline-warning mt-2" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>
                            <!-- <button type="button" class="btn btn-outline-primary mt-2" onclick="reportes([[],[],[],['0030020100000009']], `pdf`, `dictamen`,0)">Generar dictamen</button> -->
                        </div>
                    </div>
                </div>
            </div>
            <?php
            include_once "../../../src/cris_modales/mdls_analisis_01.php";
            include_once "../../../src/cris_modales/mdls_credlin_indi.php";
            include_once "../../../src/cris_modales/mdls_cancelar_credito.php";
            ?>
            <script>
                <?php
                if ($bandera && $datos[0]["tipocred"] != '') {
                    echo "update('" . $datos[0]["tipocred"] . "','" . $datos[0]["tipoper"] . "');";
                }
                ?>

                function update(val1, val2) {
                    dire = "../../views/Creditos/cre_indi/cre_indi_01.php";
                    creperi('tpscre2', '#alrtpnl', 'cre_indi_01', val1, function() {
                        $("#tipocred option[value='" + val1 + "']").attr("selected", true);
                        $.ajax({
                            url: dire,
                            method: "POST",
                            data: {
                                condi: 'prdscre',
                                xtra: val1
                            },
                            beforeSend: function() {
                                loaderefect(1);
                            },
                            success: function(data) {
                                $('#peri').html(data);
                                $("#peri option[value='" + val2 + "']").attr("selected", true);
                                loaderefect(1);
                            },
                            complete: function(data) {
                                loaderefect(0);
                            }
                        });
                    });
                }

                //SELECCIONAR LOS CHECKBOXS DESPUES DE CARGAR EL DOM
                $(document).ready(function() {
                    <?php if ($bandera_garantias2) { ?>
                        marcar_garantias_recuperadas(<?php echo json_encode($datosgarantiasrecuperados); ?>);
                    <?php } ?>
                });

                function accesodirecto(opcion, codcliente) {
                    var nuevaVentana = window.open('../cliente.php', '_blank');
                    nuevaVentana.onload = function() {
                        switch (opcion) {
                            case 1:
                                nuevaVentana.printdiv('create_perfil_economico', '#cuadro', 'clientes_001', codcliente);
                                break;
                            case 2:
                                nuevaVentana.printdiv('balance_economico', '#cuadro', 'tem_clint', codcliente);
                                break;
                        }
                    };
                }
            </script>
        <?php
        }
        break;
    case 'aprobacion_individual': {
            $codusu = $_SESSION['id'];
            $id_agencia = $_SESSION['id_agencia'];
            $codagencia = $_SESSION['agencia'];
            $xtra = $_POST["xtra"];

            //consultar
            $i = 0;
            $bandera = false;
            $bandera_garantias = false;
            $datos[] = [];
            $datosgarantias[] = [];
            $src = '../../includes/img/fotoClienteDefault.png';

            if ($xtra != 0) {
                $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cm.CodCli AS codcli, cl.short_name AS nomcli, cl.Direccion AS direccion, (SELECT IFNULL(MAX(cm2.NCiclo),0)+1 AS ciclo FROM cremcre_meta cm2 WHERE cm2.CodCli=cm.CodCli AND cm2.TipoEnti='INDI' AND (cm2.Cestado='F' OR cm2.Cestado='G')) AS ciclo,  cm.Cestado AS estado,
                cp.id AS idprod, cp.cod_producto AS codprod, cp.nombre AS nomprod, cm.NIntApro AS interesprod, cp.descripcion AS descprod, cp.monto_maximo AS montoprod, ff.descripcion AS nomfondo,
                cm.MontoSol AS montosol, cm.MonSug AS montosug, cm.CtipCre AS tipocred, cm.NtipPerC AS tipoper, cm.DfecPago AS primerpago, cm.noPeriodo AS cuotas, cm.DFecDsbls AS fecdesembolso, cm.Dictamen AS dictamen,
                us.id_usu AS idanalista, CONCAT(us.nombre, ' ', us.apellido) AS nombreanalista, cl.url_img AS urlfoto, tipc.Credito AS nomtipocred, per.nombre AS nomperiodo
                FROM cremcre_meta cm
                INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
                INNER JOIN cre_productos cp ON cm.CCODPRD=cp.id
                INNER JOIN ctb_fuente_fondos ff ON cp.id_fondo=ff.id
                INNER JOIN tb_usuario us ON cm.CodAnal=us.id_usu
                INNER JOIN clhpzzvb_bd_general_coopera.tb_credito tipc ON cm.CtipCre=tipc.abre
                INNER JOIN clhpzzvb_bd_general_coopera.tb_periodo per ON cm.NtipPerC=per.periodo
                WHERE cm.Cestado='D' AND cm.TipoEnti='INDI' AND cm.CCODCTA='$xtra' LIMIT 1");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $estado = ($fila['estado'] == 'D') ? 'Analizado' : ' ';
                    $datos[$i] = $fila;
                    $datos[$i]['estado2'] = $estado;
                    //CARGADO DE LA IMAGEN
                    $imgurl = __DIR__ . '/../../../../../' . $fila['urlfoto'];
                    if (!is_file($imgurl)) {
                        $src = '../../includes/img/fotoClienteDefault.png';
                    } else {
                        $imginfo   = getimagesize($imgurl);
                        $mimetype  = $imginfo['mime'];
                        $imageData = base64_encode(file_get_contents($imgurl));
                        $src = 'data:' . $mimetype . ';base64,' . $imageData;
                    }
                    $i++;
                    $bandera = true;
                }

                //CONSULTAR TODAS LAS GARANTIAS
                $i = 0;
                $consulta = mysqli_query($conexion, "SELECT gr.idGarantia AS idgar, tipgar.id_TiposGarantia AS idtipgar, tipgar.TiposGarantia AS nomtipgar, tipc.idDoc AS idtipdoc, tipc.NombreDoc AS nomtipdoc,
                gr.descripcionGarantia AS descripcion, gr.direccion AS direccion, gr.montoGravamen AS montogravamen,
                IFNULL((SELECT cl2.short_name AS nomcli FROM tb_cliente cl2 WHERE cl2.idcod_cliente=gr.descripcionGarantia AND tipgar.id_TiposGarantia=1 AND tipc.idDoc=1),'x') AS nomcli,
                IFNULL((SELECT cl2.Direccion AS direccioncli FROM tb_cliente cl2 WHERE cl2.idcod_cliente=gr.descripcionGarantia AND tipgar.id_TiposGarantia=1 AND tipc.idDoc=1),'x') AS direccioncli
                FROM tb_cliente cl
                INNER JOIN cli_garantia gr ON cl.idcod_cliente=gr.idCliente
                INNER JOIN tb_garantias_creditos tbg ON gr.idGarantia=tbg.id_garantia
                INNER JOIN clhpzzvb_bd_general_coopera.tb_tiposgarantia tipgar ON gr.idTipoGa=tipgar.id_TiposGarantia
                INNER JOIN clhpzzvb_bd_general_coopera.tb_tiposdocumentosR tipc ON tipc.idDoc=gr.idTipoDoc
                WHERE cl.estado='1' AND gr.estado=1 AND tbg.id_cremcre_meta='$xtra'");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $datosgarantias[$i] = $fila;
                    $i++;
                    $bandera_garantias = true;
                }
            }
        ?>
            <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
            <input type="text" id="file" value="cre_indi_01" style="display: none;">
            <input type="text" id="condi" value="aprobacion_individual" style="display: none;">
            <div class="text" style="text-align:center">APROBACIÓN DE CRÉDITO INDIVIDUAL</div>
            <div class="card">
                <div class="card-header">Aprobación de crédito individual</div>
                <div class="card-body" style="padding-bottom: 0px !important;">

                    <!-- seleccion de cliente y su credito-->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Información de cliente</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 col-sm-6 col-md-2 mt-2">
                                <img width="120" height="130" id="vistaPrevia" src="<?php if ($bandera) {
                                                                                        echo $src;
                                                                                    } else {
                                                                                        echo $src;
                                                                                    } ?>">
                            </div>
                            <div class="col-12 col-sm-12 col-md-10">
                                <div class="row">
                                    <div class="col-12 col-sm-6">
                                        <div class="form-floating mb-2 mt-2">
                                            <input type="text" class="form-control" id="ccodcta" placeholder="Código de credito" readonly <?php if ($bandera) {
                                                                                                                                                echo 'value="' . $datos[0]['ccodcta'] . '"';
                                                                                                                                            } ?>>
                                            <label for="ccodcta">Código de crédito</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <button type="button" class="btn btn-primary pt-3 pb-3 mb-2 mt-2 col-12 col-sm-12" onclick="abrir_modal('#modal_aprobacion_01', '#id_modal_hidden', 'id')"><i class="fa-solid fa-magnifying-glass-plus me-2"></i>Buscar cliente</button>
                                    </div>
                                </div>
                                <!-- cargo, nombre agencia y codagencia  -->
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-3">
                                        <div class="form-floating mb-2 mt-2">
                                            <input type="text" class="form-control" id="codcli" placeholder="Código de cliente" readonly <?php if ($bandera) {
                                                                                                                                                echo 'value="' . $datos[0]['codcli'] . '"';
                                                                                                                                            } ?>>
                                            <input type="text" name="" id="id_cod_cliente" hidden>
                                            <label for="cliente">Código de cliente</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-7">
                                        <div class="form-floating mb-2 mt-2">
                                            <input type="text" class="form-control" id="nomcli" placeholder="Nombre cliente" readonly <?php if ($bandera) {
                                                                                                                                            echo 'value="' . $datos[0]['nomcli'] . '"';
                                                                                                                                        } ?>>
                                            <label for="nomcli">Nombre de cliente</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-2">
                                        <div class="form-floating mb-2 mt-2">
                                            <input type="text" class="form-control" id="ciclo" placeholder="Ciclo" readonly <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['ciclo'] . '"';
                                                                                                                            } ?>>
                                            <label for="ciclo">Ciclo</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-sm-8">
                                <div class="form-floating mb-3 mt-2">
                                    <input type="text" class="form-control" id="direccion" placeholder="Dirección" readonly <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['direccion'] . '"';
                                                                                                                            } ?>>
                                    <label for="direccion">Dirección</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="form-floating mb-3 mt-2">
                                    <input type="text" class="form-control" id="estado" placeholder="Estado" readonly <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['estado2'] . '"';
                                                                                                                        } ?>>
                                    <label for="estado">Estado</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- LINEA DE CREDITO MEJORADO -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Información del producto</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-4 col-md-3">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="codprod" placeholder="Código de producto" readonly <?php if ($bandera) {
                                                                                                                                        echo 'value="' . $datos[0]['codprod'] . '"';
                                                                                                                                    } ?>>
                                    <label for="codprod">Código de producto</label>
                                    <input type="text" class="form-control" id="idprod" hidden readonly <?php if ($bandera) {
                                                                                                            echo 'value="' . $datos[0]['idprod'] . '"';
                                                                                                        } ?>>
                                </div>
                            </div>
                            <div class="col-12 col-sm-8 col-md-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="nameprod" placeholder="Nombre" readonly <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['nomprod'] . '"';
                                                                                                                        } ?>>
                                    <label for="nameprod">Nombre</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-3">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="tasaprod" placeholder="% Interes anual" readonly <?php if ($bandera) {
                                                                                                                                        echo 'value="' . $datos[0]['interesprod'] . '"';
                                                                                                                                    } ?>>
                                    <label for="tasaprod">% Interes</label>
                                </div>
                            </div>
                        </div>
                        <!-- cargo, nombre agencia y codagencia  -->
                        <div class="row">
                            <div class="col-12 col-sm-8 col-md-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="descprod" placeholder="Descripción" readonly <?php if ($bandera) {
                                                                                                                                    echo 'value="' . $datos[0]['descprod'] . '"';
                                                                                                                                } ?>>
                                    <label for="descprod">Descripción</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4 col-md-3">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="maxprod" placeholder="Monto máximo" readonly <?php if ($bandera) {
                                                                                                                                    echo 'value="' . $datos[0]['montoprod'] . '"';
                                                                                                                                } ?>>
                                    <label for="maxprod">Monto máximo</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-3">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="fuenteprod" placeholder="Fuente de fondo" readonly <?php if ($bandera) {
                                                                                                                                        echo 'value="' . $datos[0]['nomfondo'] . '"';
                                                                                                                                    } ?>>
                                    <label for="fuenteprod">Fuente de fondo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- datos adicionales -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Datos complementarios</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-12">
                                <div class="form-floating mb-3 mt-2">
                                    <input type="text" class="form-control" id="analista" placeholder="Nombre del analista" readonly <?php if ($bandera) {
                                                                                                                                            echo 'value="' . $datos[0]['nombreanalista'] . '"';
                                                                                                                                        } ?>>
                                    <label for="analista">Nombre del analista</label>
                                </div>
                            </div>
                        </div>
                        <!-- Monto aprobado y solicitado -->
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="montosol" placeholder="Monto solicitado" readonly <?php if ($bandera) {
                                                                                                                                        echo 'value="' . $datos[0]['montosol'] . '"';
                                                                                                                                    } ?>>
                                    <label for="montosol">Monto solicitado</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-3">
                                    <input min="0" type="number" class="form-control" id="montosug" placeholder="Monto aprobado" readonly <?php if ($bandera) {
                                                                                                                                                echo 'value="' . $datos[0]['montosug'] . '"';
                                                                                                                                            } ?>>
                                    <label for="montosug">Monto aprobado</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="number" min="1" class="form-control" id="cuota" placeholder="No de cuotas" readonly <?php if ($bandera) {
                                                                                                                                            echo 'value="' . $datos[0]['cuotas'] . '"';
                                                                                                                                        } ?>>
                                    <label for="cuota">No. de cuotas</label>
                                </div>
                            </div>
                        </div>
                        <!-- tipo de credito, tipo periodo, fecha primer cuota -->
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="tipocred" placeholder="Tipo de crédito" readonly <?php if ($bandera) {
                                                                                                                                        echo 'value="' . $datos[0]['nomtipocred'] . '"';
                                                                                                                                    } ?>>
                                    <label for="tipocred">Tipo de crédito</label>
                                </div>

                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="peri" placeholder="Tipo de periodo" readonly <?php if ($bandera) {
                                                                                                                                    echo 'value="' . $datos[0]['nomperiodo'] . '"';
                                                                                                                                } ?>>
                                    <label for="peri">Tipo de periodo</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="dictamen" placeholder="No. Dictamen" readonly <?php if ($bandera) {
                                                                                                                                    echo 'value="' . $datos[0]['dictamen'] . '"';
                                                                                                                                } ?>>
                                    <label for="dictamen">No. Dictamen</label>
                                </div>
                            </div>

                        </div>
                        <!-- cuota, desembolso y dictamen -->
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control" id="fecdesembolso" placeholder="Fecha de desembolso" readonly <?php if ($bandera) {
                                                                                                                                                echo 'value="' . $datos[0]['fecdesembolso'] . '"';
                                                                                                                                            } else {
                                                                                                                                                echo 'value="' . date('Y-m-d') . '"';
                                                                                                                                            } ?>>
                                    <label for="fecdesembolso">Fecha de desembolso</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control" id="primerpago" placeholder="Fecha primer pago" readonly <?php if ($bandera) {
                                                                                                                                            echo 'value="' . $datos[0]['primerpago'] . '"';
                                                                                                                                        } else {
                                                                                                                                            echo 'value="' . date('Y-m-d') . '"';
                                                                                                                                        } ?>>
                                    <label for="primerpago">Fecha primer pago</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="tipcontrato">
                                        <option value="C" selected>Contrato individual</option>
                                    </select>
                                    <label for="tipcontrato">Tipo de crédito</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCION DE GARANTIAS -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Garantías del cliente</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-2">
                                <div class="table-responsive">
                                    <table class="table mb-0" style="font-size: 0.8rem !important;">
                                        <thead>
                                            <tr>
                                                <th scope="col">Tipo Garantia</th>
                                                <th scope="col">Tipo Doc.</th>
                                                <th scope="col">Descripción</th>
                                                <th scope="col">Dirección</th>
                                                <th scope="col">Valor gravamen</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-group-divider">
                                            <?php if ($bandera_garantias) {
                                                for ($i = 0; $i < count($datosgarantias); $i++) { ?>
                                                    <tr>
                                                        <td scope="row"><?= ($datosgarantias[$i]["nomtipgar"]) ?></td>
                                                        <td><?= ($datosgarantias[$i]["nomtipdoc"]) ?></td>
                                                        <!-- VALIDAR SI ES UN GARANTIA NORMAL O ES UN FIADOR -->
                                                        <?php if ($datosgarantias[$i]["idtipgar"] == 1 && $datosgarantias[$i]["idtipdoc"] == 1) { ?>
                                                            <td><?= ($datosgarantias[$i]["nomcli"]) ?></td>
                                                            <td><?= ($datosgarantias[$i]["direccioncli"]) ?></td>
                                                        <?php } else { ?>
                                                            <td><?= ($datosgarantias[$i]["descripcion"]) ?></td>
                                                            <td><?= ($datosgarantias[$i]["direccion"]) ?></td>
                                                        <?php } ?>
                                                        <td><?= ($datosgarantias[$i]["montogravamen"]) ?></td>
                                                    </tr>
                                            <?php }
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ALERTA PARA BUSCAR UN CLIENTE  -->
                    <?php if (!$bandera) { ?>
                        <div class="alert alert-warning" role="alert" style="margin-bottom: 0px !important;">
                            <h4 class="alert-heading">IMPORTANTE!</h4>
                            <p>Debe seleccionar un cliente para poder aprobar su crédito</p>
                        </div>
                    <?php  } ?>
                </div>

                <div class="container" style="max-width: 100% !important;">
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center mb-3 ms-2" id="modal_footer">
                            <!-- Boton de rechazo -->
                            <?php if ($bandera) { ?>
                                <button class="btn btn-outline-success mt-2" onclick="obtiene([`ccodcta`,`codcli`,`nomcli`,`idprod`,`codprod`],[`tipcontrato`],[],`create_aprobacion`,`0`,['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>','<?= $datos[0]['ciclo']; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Confirmar aprobar crédito</button>


                                <button type="button" class="btn btn-outline-danger mt-2" onclick="abrir_modal_cualquiera_con_valor('#modal_cancelar_credito', '#id_hidden', `<?= $datos[0]['ccodcta']; ?>,<?= $datos[0]['codcli']; ?>`,[`#credito`,`#nombre`])"><i class="fa-solid fa-sack-xmark me-2"></i>Rechazar Crédito</button>
                            <?php } ?>
                            <button type="button" class="btn btn-outline-danger mt-2" onclick="printdiv2('#cuadro','0')">
                                <i class="fa-solid fa-ban"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-outline-warning mt-2" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            include_once "../../../src/cris_modales/mdls_aprobacion_01.php";
            include_once "../../../src/cris_modales/mdls_cancelar_credito.php";
            ?>
        <?php
        }
        break;
    case 'desembolso_individual': {
            $codusu = $_SESSION['id'];
            $id_agencia = $_SESSION['id_agencia'];
            $xtra = $_POST["xtra"];

            //consultar
            $i = 0;
            $bandera = false;
            $datos[] = [];
            $src = '../../includes/img/fotoClienteDefault.png';

            //CONSULTA DE LOS DATOS
            if ($xtra != 0) {
                $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cl.short_name AS nomcli, cm.CodCli AS codcli, cm.CODAgencia AS codagencia, pd.cod_producto AS codproducto, cm.MonSug AS monto,
                (SELECT IFNULL(MAX(cm2.NCiclo),0)+1 AS ciclo FROM cremcre_meta cm2 WHERE cm2.CodCli=cm.CodCli AND cm2.TipoEnti='INDI' AND (cm2.Cestado='F' OR cm2.Cestado='G')) AS ciclo, cm.Cestado AS estado, cl.url_img AS urlfoto
                FROM cremcre_meta cm
                INNER JOIN cre_productos pd ON cm.CCODPRD=pd.id
                INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente WHERE cm.Cestado='E' AND cm.TipoEnti='INDI' AND cm.CCODCTA='$xtra' LIMIT 1");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $estado = ($fila['estado'] == 'E') ? 'Aprobado' : ' ';
                    $datos[$i] = $fila;
                    $datos[$i]['estado2'] = $estado;
                    //CARGADO DE LA IMAGEN
                    $imgurl = __DIR__ . '/../../../../../' . $fila['urlfoto'];
                    if (!is_file($imgurl)) {
                        $src = '../../includes/img/fotoClienteDefault.png';
                    } else {
                        $imginfo   = getimagesize($imgurl);
                        $mimetype  = $imginfo['mime'];
                        $imageData = base64_encode(file_get_contents($imgurl));
                        $src = 'data:' . $mimetype . ';base64,' . $imageData;
                    }
                    $i++;
                    $bandera = true;
                }

                $rst_tipCu = $conexion->query("SELECT cpg.id AS pro_gas, cp.cod_producto, cp.nombre, ct.nombre_gasto, ct.afecta_modulo  
                         FROM cremcre_meta cm 
                         INNER JOIN cre_productos cp ON cp.id = cm.CCODPRD 
                         INNER JOIN cre_productos_gastos cpg ON cpg.id_producto = cp.id 
                         INNER JOIN cre_tipogastos ct ON ct.id = cpg.id_tipo_deGasto 
                         WHERE cp.cod_producto = {$datos[0]['codproducto']} AND cm.CCODCTA = '{$datos[0]['ccodcta']}' AND ct.afecta_modulo > 0 AND cpg.tipo_deCobro=2");

                $aho_vin = mysqli_num_rows($rst_tipCu);
            }
            if (!isset($datos[0]['ccodcta'])) {
                $ccocta = '000';
            } else {
                $ccocta =  $datos[0]['ccodcta'];
            }
            if (!isset($datos[0]['codcli'])) {
                $codcli = '000';
            } else {
                $codcli =  $datos[0]['codcli'];
            }

            //CONSULTAR GARANTIAS NEGROY locuaz
            //BUSCAR DATOS DE GARANTIAS
            if ($ccocta != "000") {
                $strquery = "SELECT cl.idcod_cliente AS codcli, gr.idGarantia AS idgar, tipgar.id_TiposGarantia AS idtipgar, tipgar.TiposGarantia AS nomtipgar, tipc.idDoc AS idtipdoc, tipc.NombreDoc AS nomtipdoc,
      gr.descripcionGarantia AS descripcion, gr.direccion AS direccion, gr.montoGravamen AS montogravamen,
      IFNULL((SELECT cl2.short_name AS nomcli FROM tb_cliente cl2 WHERE cl2.idcod_cliente=gr.descripcionGarantia AND tipgar.id_TiposGarantia=1 AND tipc.idDoc=1),'x') AS nomcli,
      IFNULL((SELECT cl2.Direccion AS direccioncli FROM tb_cliente cl2 WHERE cl2.idcod_cliente=gr.descripcionGarantia AND tipgar.id_TiposGarantia=1 AND tipc.idDoc=1),'x') AS direccioncli,
      IFNULL((SELECT '1' AS marcado FROM tb_garantias_creditos tgc WHERE tgc.id_cremcre_meta='$ccocta' AND tgc.id_garantia=gr.idGarantia),0) AS marcado,
      IFNULL((SELECT SUM(cli.montoGravamen) AS totalgravamen FROM tb_garantias_creditos tgc INNER JOIN cli_garantia cli ON cli.idGarantia=tgc.id_garantia WHERE tgc.id_cremcre_meta='$ccocta' AND cli.estado=1),0) AS totalgravamen
      FROM tb_cliente cl
      INNER JOIN cli_garantia gr ON cl.idcod_cliente=gr.idCliente
      INNER JOIN clhpzzvb_bd_general_coopera.tb_tiposgarantia tipgar ON gr.idTipoGa=tipgar.id_TiposGarantia
      INNER JOIN clhpzzvb_bd_general_coopera.tb_tiposdocumentosR tipc ON tipc.idDoc=gr.idTipoDoc
      WHERE cl.estado='1' AND gr.estado=1 AND cl.idcod_cliente= '$codcli' ";
                $query = mysqli_query($conexion, $strquery);
                $datosgarantias[] = [];
                $ji = 0;
                $flag2 = false;
                while ($fila = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
                    $datosgarantias[$ji] = $fila;
                    $flag2 = true;
                    $ji++;
                }
            }
        ?>
            <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
            <input type="text" id="file" value="cre_indi_01" style="display: none;">
            <input type="text" id="condi" value="desembolso_individual" style="display: none;">
            <div class="text" style="text-align:center">DESEMBOLSO DE CRÉDITO INDIVIDUAL</div>
            <div class="card">
                <div class="card-header">Desembolso de crédito individual <?= $codcli ?> </div>
                <div class="card-body">

                    <!-- seleccion de cliente y su credito-->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Información de cliente y codigo de crédito</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 col-sm-6 col-md-2 mt-2">
                                <img width="120" height="130" id="vistaPrevia" src="<?php echo $src; ?>">
                            </div>
                            <div class="col-12 col-sm-12 col-md-10">
                                <!-- usuario y boton buscar -->
                                <div class="row">
                                    <div class="col-12 col-sm-6">
                                        <div class="form-floating mb-2 mt-2">
                                            <input type="text" class="form-control" id="nomcli" placeholder="Nombre de cliente" readonly <?php if ($bandera) {
                                                                                                                                                echo 'value="' . $datos[0]['nomcli'] . '"';
                                                                                                                                            } ?>>
                                            <input type="text" name="" id="id_cod_cliente" hidden <?php if ($bandera) {
                                                                                                        echo 'value="' . $datos[0]['codcli'] . '"';
                                                                                                    } ?>>
                                            <label for="cliente">Nombre cliente</label>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6">
                                        <button type="button" class="btn btn-primary pt-3 pb-3 mb-2 mt-2 col-12 col-sm-12" onclick="abrir_modal('#modal_creditos_a_desembolsar', '#id_modal_hidden', 'id_cod_cliente,nomcli,codagencia,codproducto,codcredito,ccapital/A,A,A,A,A,A/'+'/tipo_desembolso/#/#/mensaje')"><i class="fa-solid fa-magnifying-glass-plus me-2"></i>Buscar crédito a desembolsar</button>
                                    </div>
                                </div>
                                <!-- cargo, nombre agencia y codagencia  -->
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-4">
                                        <div class="form-floating mb-3 mt-2">
                                            <input type="text" class="form-control" id="codproducto" placeholder="Código de producto" readonly <?php if ($bandera) {
                                                                                                                                                    echo 'value="' . $datos[0]['codproducto'] . '"';
                                                                                                                                                } ?>>
                                            <label for="cargo">Codigo de producto</label>
                                        </div>
                                    </div>

                                    <!-- estado y ciclo -->
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <div class="form-floating mb-3 mt-2">
                                            <input type="text" class="form-control" id="estado" placeholder="Estado" readonly <?php if ($bandera) {
                                                                                                                                    echo 'value="' . $datos[0]['estado2'] . '"';
                                                                                                                                } ?>>
                                            <label for="estado">Estado</label>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-md-4">
                                        <div class="form-floating mb-3 mt-2">
                                            <input type="text" class="form-control" id="ciclo" placeholder="Ciclo" readonly <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['ciclo'] . '"';
                                                                                                                            } ?>>
                                            <label for="ciclo">Ciclo</label>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- cnumdoc, capital, gastos, total a desembolsar -->
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="codagencia" placeholder="Código de agencia" readonly <?php if ($bandera) {
                                                                                                                                            echo 'value="' . $datos[0]['codagencia'] . '"';
                                                                                                                                        } ?>>
                                    <label for="codagencia">Agencia</label>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="codcredito" placeholder="Codigo de crédito" readonly <?php if ($bandera) {
                                                                                                                                            echo 'value="' . $ccocta . '"';
                                                                                                                                        } ?>>
                                    <label for="nomagencia">Código de crédito</label>
                                </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-5">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="ccapital" placeholder="Capital" readonly <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['monto'] . '"';
                                                                                                                            } ?>>
                                    <label for="ccapital">Capital</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="gastos" placeholder="Gastos" readonly>
                                    <label for="gastos">Gastos</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="desembolsar" placeholder="Total a desembolsar" readonly>
                                    <label for="desembolsar">Total a desembolsar</label>
                                </div>
                            </div>
                        </div>
                        <!-- DIV PARA VISUALIZAR LAS GARANTIAS NEGROY -->

                        <h2 class="accordion-header">
                            <div class="row">
                                <div class="col-12">
                                    <button id="bt1" class="accordion-button collapsed loco" data-bs-toggle="collapse" data-bs-target="#data1" aria-expanded="false" aria-controls="data1">
                                        <div class="row center">
                                            <i class="fa-solid fa-arrow-turn-down">
                                                <a>Visualzar Garantias</a> </i>
                                            <br>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </h2>

                        <div id="data1" class="accordion-collapse collapse" data-bs-parent="#cuotas">
                            <div class="accordion-body">
                                <div class="row mb-3" style="font-size: 0.90rem;">
                                    <!-- SECCION DE GARANTIAS -->
                                    <div class="container contenedort" style="max-width: 100% !important;">
                                        <div class="row">
                                            <div class="col">
                                                <div class="text-center mb-2"> <b> Garantías del cliente </b> </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col mb-2">
                                                <div class="table-responsive">
                                                    <table class="table mb-0" style="font-size: 0.8rem !important;">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">Tipo Garantia</th>
                                                                <th scope="col">Tipo Doc.</th>
                                                                <th scope="col">Descripción</th>
                                                                <th scope="col">Dirección</th>
                                                                <th scope="col">Valor gravamen</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="table-group-divider">
                                                            <!-- GARANTIAS NEGROY  -->
                                                            <?php
                                                            if ($ccocta != "000") {
                                                                for ($i = 0; $i < count($datosgarantias); $i++) {
                                                                    if ($datosgarantias[$i]['marcado'] == 1) {
                                                                        // FORMATEADOR EXPRESS ʕっ•ᴥ•ʔっ
                                                                        $numero_formateado = number_format($datosgarantias[$i]['montogravamen'], 2, '.', ',');
                                                                        echo "<tr> <td scope='row'>" . $datosgarantias[$i]['nomtipgar'] . "</td>
							<td>" . $datosgarantias[$i]['nomtipdoc'] . "</td> ";
                                                                        if ($datosgarantias[$i]["idtipgar"] == 1 && $datosgarantias[$i]["idtipdoc"] == 1) {
                                                                            echo "<td>" . $datosgarantias[$i]['nomcli'] . "</td>
								<td>" . $datosgarantias[$i]['direccioncli'] . "</td>";
                                                                        } else {
                                                                            echo "<td>" . $datosgarantias[$i]['descripcion'] . "</td>
								<td>" . $datosgarantias[$i]['direccion'] . "</td>";
                                                                        }
                                                                        echo "<td>Q " . $numero_formateado . "</td>";
                                                                    }
                                                                }
                                                            } else {
                                                                echo "<tr> <td>-</td> <td>-</td> <td>-</td> 
									<td>-</td> <td>-</td> ";
                                                            } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- DIV PARA VISUALIZAR LAS GARANTIAS  -->
                    </div>

                    <!-- INI ********************************************************************************************************************* slc-->
                    <div class="container contenedort mt-2" style="max-width: 100% !important;" id="aho_vin">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-5">
                                        <h5>Seleccionar un tipo de ahorro vinculado o lo puede omitir</h5>
                                    </div>
                                    <div class="col">
                                        <button type="button" class="btn btn-outline-danger" id="ar_ahoVin" onclick="omitir_aho_vin()">Omitir</button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <table class="table table-success table-striped">
                                        <thead class="table-success">
                                            <th scope="row">id</th>
                                            <th scope="row">Código de producto</th>
                                            <th scope="row">Nombre de producto</th>
                                            <th scope="row">Nombre de gasto</th>
                                            <th scope="row">Cuenta afectada</th>
                                            <th scope="row">Check</th>

                                        </thead>
                                        <tbody>
                                            <?php
                                            if (isset($rst_tipCu) && $rst_tipCu->num_rows > 0) {
                                                while ($row = $rst_tipCu->fetch_assoc()) {
                                            ?>

                                                    <tr style="cursor: pointer;" id="<?= $row['pro_gas'] ?>">
                                                        <td><?= $row['pro_gas'] ?></td>
                                                        <td><?= $row['cod_producto'] ?></td>
                                                        <td><?= $row['nombre'] ?></td>
                                                        <td><?= $row['nombre_gasto'] ?></td>
                                                        <td><?= ($row['afecta_modulo'] == 1) ? "Cuenta de Ahorro" : "Cuenta de Aportación" ?></td>
                                                        <td>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="data_tipcu" value="<?= $row['pro_gas'] ?>" id="<?= $row['pro_gas'] ?>" onclick="bus_ahoVin('<?= (isset($datos[0]['codcli'])) ? $datos[0]['codcli'] : '' ?>')">
                                                                <label class="form-check-label" for="<?= $row['pro_gas'] ?>">
                                                                </label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                            <?php
                                                }
                                            }

                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div id="tip_cu"></div>
                        </div>
                    </div>

                    <!-- FIN *** -->

                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col-12 mt-2 mb-1">
                                <div class="table-responsive">
                                    <table id="tabla_gastos_desembolso" class="table" style="max-width: 100% !important;">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th></th>
                                                <th scope="col">Descripción de gasto</th>
                                                <th scope="col">Cuenta anterior</th>
                                                <th scope="col">Monto</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- select para la parte del tipo de desembolso -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col-sm-12 mb-3 mt-2">
                                <div class="form-floating">
                                    <select class="form-select" id="tipo_desembolso" aria-label="Tipo de desembolso" onchange="ocultar_div_desembolso(this.value)" <?= ($bandera) ? ' ' : 'disabled' ?>>
                                        <option selected value="1">Efectivo</option>
                                        <option value="2">Cheque</option>
                                        <option value="3">Transferencia</option>
                                    </select>
                                    <label for="tip_doc">Tipo de desembolso</label>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="container contenedort" id="region_cheque" style="display: none; max-width: 100% !important;">
                        <div class="row">
                            <div class="col-sm-4 mt-2">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="cantidad" step="0.01" placeholder="Cantidad" disabled>
                                    <label for="cantidad">Cantidad</label>
                                </div>
                            </div>
                            <div class="col-sm-4 mt-2">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="negociable">
                                        <option value="0">No Negociable</option>
                                        <option value="1">Negociable</option>
                                    </select>
                                    <label for="negociable">Tipo cheque</label>
                                </div>
                            </div>
                            <div class="col-sm-4 mb-3 mt-2">
                                <div class="form-floating">
                                    <input type="number" class="form-control" id="numcheque" placeholder="Numero de cheque">
                                    <label for="numcheque">No. de Cheque</label>
                                </div>
                            </div>
                        </div>
                        <!-- input de paguese a la orden de -->
                        <div class="row">
                            <div class="col-sm-12 mb-3">
                                <div class="form-floating">
                                    <input disabled type="text" class="form-control" id="paguese" placeholder="Paguese a la orden de">
                                    <label for="paguese">Paguese a la orden de</label>
                                </div>
                            </div>
                        </div>
                        <!-- input para numeros en letras -->
                        <div class="row">
                            <div class="col-sm-12 mb-3">
                                <div class="form-floating">
                                    <input disabled type="text" class="form-control" id="numletras" placeholder="La suma de (Q)">
                                    <label for="numletras">La suma de (Q)</label>
                                </div>
                            </div>
                        </div>
                        <!-- fila de seleccion de bancos -->
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="bancoid" onchange="buscar_cuentas()">
                                        <option value="" disabled selected>Seleccione un banco</option>
                                        <?php
                                        $bancos = mysqli_query($conexion, "SELECT * FROM tb_bancos WHERE estado='1'");
                                        while ($banco = mysqli_fetch_array($bancos)) {
                                            echo '<option  value="' . $banco['id'] . '">' . $banco['id'] . " - " . $banco['nombre'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="bancoid">Banco</label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-floating mb-3">
                                    <!-- id de cuenta para edicion -->
                                    <!-- select normal -->
                                    <select class="form-select" id="cuentaid">
                                        <option value="">Seleccione una cuenta</option>
                                    </select>
                                    <label for="cuentaid">No. de Cuenta</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- REGION DE TRANSFERENCIA -->
                    <div class="container contenedort" id="region_transferencia" style="display: none; max-width: 100% !important;">
                        <div class="row">
                            <div class="col-sm-12  mt-2">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="cuentaaho">
                                        <option value="">Seleccione una cuenta de ahorro</option>
                                    </select>
                                    <label for="cuentaaho">Cuenta de ahorro</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container contenedort" style="max-width: 100% !important;">
                        <!-- input de glosa -->
                        <div class="row">
                            <div class="col-sm-12 mb-1 mt-2">
                                <div class="form-floating">
                                    <textarea class="form-control" id="glosa" style="height: 100px" rows="1" placeholder="Concepto" <?= ($bandera) ? ' ' : 'disabled' ?>>  </textarea>
                                    <label for="glosa">Concepto</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!$bandera) { ?>
                        <div class="alert alert-success" role="alert" style="margin-bottom: 0px !important;" id="mensaje">
                            <h4 class="alert-heading">IMPORTANTE!</h4>
                            <p>Debe seleccionar un cliente para realizar un desembolso</p>
                        </div>
                    <?php } ?>

                </div>
                <div class="container" style="max-width: 100% !important;">
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center mb-3 ms-2" id="modal_footer">
                            <!-- en el metodo onclick se envian usuario y oficina para saber las cuentas de agencia a generar -->
                            <button id="bt_desembolsar" class="btn btn-outline-success" onclick="if(val_aho_vin()==false)return; savedesem('<?= $codusu; ?>','<?= $id_agencia; ?>')"><i class="fa-solid fa-money-bill"></i> Desembolsar</button>
                            <button type="button" class="btn btn-outline-danger" onclick="printdiv2('#cuadro','0')">
                                <i class="fa-solid fa-ban"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-outline-warning" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>
                            <!-- <button type="button" onclick="cheque_desembolso([[], [], [], ['21488']], 'pdf', 'cheque', 0)">prueba cheque</button> -->
                        </div>
                    </div>
                </div>
            </div>
            <script>
                $(document).ready(function() {
                    (<?= isset($aho_vin) ? $aho_vin : 0 ?> > 0) ? ac_even('aho_vin', 'vista', 1): ac_even('aho_vin', 'vista', 0);
                    idPro_gas = <?= isset($aho_vin) ? $aho_vin : 0 ?>;
                    afec = 0;
                    ahorro = 0;
                });
            </script>
            <?php
            include_once "../../../src/cris_modales/mdls_desembolso_indi.php";
            ?>
            <script>
                $(document).ready(function() {
                    // $('#bt_desembolsar').hide();
                });
                <?php
                if ($bandera) {
                    echo 'mostrar_tabla_gastos(`' . $datos[0]['ccodcta'] . '`);';
                    echo 'consultar_gastos_monto(`' . $datos[0]['ccodcta'] . '`);';
                    echo 'concepto_default(`' . $datos[0]['nomcli'] . '`, `0`);'; ?>
                    $(`#bt_desembolsar`).show();
                <?php } else { ?>
                    $('#bt_desembolsar').hide();
                <?php } ?>

                function setmonto(id, saldokp = 0, intpen = 0) {
                    saldokp = parseFloat(saldokp);
                    intpen = parseFloat(intpen);
                    $("#" + id).val(saldokp + intpen);
                }

                function handleSelectChange(id, select) {
                    var selectedOption = select.options[select.selectedIndex];
                    var account = selectedOption.value;
                    var saldo = parseFloat(selectedOption.dataset.saldo);
                    var intpen = parseFloat(selectedOption.dataset.intpen);
                    setmonto(id, saldo, intpen);
                }
            </script>
        <?php
        }
        break;

        /*--------------------------------------------------------------------------------- */
    case 'statusaccount':
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
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';
        ?>
        <input type="text" readonly hidden value='statusaccount' id='condi'>
        <input type="text" hidden value="cre_indi_01" id="file">
        <div class="card crdbody contenedort">
            <div class="card-header" style="text-align:left">
                <h4>Estado de cuenta Individual</h4>
            </div>
            <div class="card-body">
                <div class="row contenedort">
                    <h5>Detalle del cliente</h5>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <div>
                                <span class="input-group-addon col-8">Nombre Cliente</span>
                                <input type="text" class="form-control " id="name" value="<?php if ($bandera == "") echo $datos[0]["nombrecli"]; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <br>
                            <button type="button" class="btn btn-primary col-sm-12" onclick="abrir_modal('#modal_estado_cuenta', '#id_modal_hidden', 'name/A/'+'/#/#/#/#')">
                                <i class="fa-solid fa-magnifying-glass-plus me-2"></i>Buscar crédito
                            </button>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <span class="input-group-addon col-8">Codigo de Cuenta</span>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(11rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["ccodcta"] . '</span>'; ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <label for="codgrup" class="input-group-addon">Codigo de Cliente</label>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(11rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["codcli"] . '</span>'; ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($bandera != "" && $extra != "0") {
                        echo '<div class="alert alert-danger" role="alert">' . $bandera . '</div>';
                    }
                    ?>
                </div>
            </div>
            <div class="row justify-items-md-center">
                <div class="col align-items-center" id="modal_footer">
                    <?php
                    if ($bandera == "") {
                        echo '<button type="button" class="btn btn-outline-danger" onclick="reportes([[],[],[],[`' . $datos[0]["ccodcta"] . '`,' . $id_agencia . ']], `pdf`, `estado_de_cuenta`, 0)">
            <i class="fa-regular fa-file-pdf"></i> Estado de Cuenta
          </button>';
                    }
                    ?>
                    <button type="button" class="btn btn-outline-danger" onclick="printdiv('PagGrupAutom', '#cuadro', 'caja_cre', 0)">
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
        include_once "../../../src/cris_modales/mdls_estadocuenta.php";
        break;
}

?>