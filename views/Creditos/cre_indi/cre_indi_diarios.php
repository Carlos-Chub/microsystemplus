<?php
session_start();
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
    case 'analisis': {
        echo 'PROBANDO SONNIDO NO MS';
            include_once "../../../src/funcphp/func_gen.php";
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
                $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cm.CodCli AS codcli,cl.date_birth, cl.short_name AS nomcli, cl.Direccion AS direccion, (SELECT IFNULL(MAX(cm2.NCiclo),0)+1 AS ciclo FROM cremcre_meta cm2 WHERE cm2.CodCli=cm.CodCli AND cm2.TipoEnti='INDI' AND (cm2.Cestado='F' OR cm2.Cestado='G')) AS ciclo,  cm.Cestado AS estado,
                cp.id AS idprod, cp.cod_producto AS codprod, cp.nombre AS nomprod, cm.NIntApro AS interesprod, cp.descripcion AS descprod, cp.monto_maximo AS montoprod, ff.descripcion AS nomfondo, 
                cm.MontoSol AS montosol, cm.MonSug AS montosug, cm.CtipCre AS tipocred, cm.NtipPerC AS tipoper, cm.DfecPago AS primerpago, cm.noPeriodo AS cuotas, cm.DFecDsbls AS fecdesembolso, cm.Dictamen AS dictamen,
                us.id_usu AS idanalista, CONCAT(us.nombre, ' ', us.apellido) AS nombreanalista, cl.url_img AS urlfoto
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
            <input type="text" id="file" value="cre_indi_diarios" style="display: none;">
            <input type="text" id="condi" value="analisis" style="display: none;">
            <div class="text" style="text-align:center">ANÁLISIS DE CRÉDITO DIARIO INDIVIDUAL</div>
            <div class="card">
                <div class="card-header">Análisis de crédito Diario individual</div>
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
                            <div class="col-12">
                                <div class="form-floating mb-3">
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
                                    <input type="number" class="form-control" id="montosol" placeholder="Monto solicitado" readonly <?php if ($bandera) {
                                                                                                                                        echo 'value="' . $datos[0]['montosol'] . '"';
                                                                                                                                    } ?>>
                                    <label for="montosol">Monto solicitado</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="form-floating mb-3 mt-2">
                                    <input min="0" type="number" class="form-control" id="montosug" placeholder="Monto por aprobar" <?php if ($bandera && $datos[0]['cuotas'] != '') {
                                                                                                                                        echo 'value="' . $datos[0]['montosug'] . '"';
                                                                                                                                    } ?>>
                                    <label for="montosug">Monto a aprobar</label>
                                </div>
                            </div>
                        </div>
                        <!-- tipo de credito, tipo periodo, fecha primer cuota -->
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-3">
                                    <select disabled class="form-select" id="tipocred">
                                        <option value="0" disabled>Seleccione un tipo de crédito</option>
                                        <?php
                                        $consulta = mysqli_query($general, "SELECT abre, Credito FROM `tb_credito`");
                                        $selectedop = " ";
                                        while ($dtas = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                            $id_abre = $dtas["abre"];
                                            $nomtip = $dtas["Credito"];
                                            $selectedop = ($id_abre == 'Flat') ? ' selected' : ' ';
                                            echo '<option ' . $selectedop . ' value="' . $id_abre . '">' . $nomtip . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="tipocred">Tipo de crédito</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-3">
                                    <select disabled class="form-select" id="peri">
                                        <option value="1D" selected disabled>Diario</option>
                                    </select>
                                    <label for="peri">Tipo de periodo</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control" id="primerpago" placeholder="Fecha primer pago" <?php if ($bandera && $datos[0]['cuotas'] != '') {
                                                                                                                                echo 'value="' . $datos[0]['primerpago'] . '"';
                                                                                                                            } else {
                                                                                                                                echo 'value="' . date('Y-m-d') . '"';
                                                                                                                            } ?>>
                                    <label for="primerpago">Fecha primer pago</label>
                                </div>
                            </div>
                        </div>
                        <!-- cuota, desembolso y dictamen -->
                        <div class="row">
                            <div class="col-12 col-sm-4">
                                <div class="form-floating mb-3 mt-2">
                                    <input type="number" min="1" class="form-control" id="cuota" placeholder="No de cuotas" <?php if ($bandera && $datos[0]['cuotas'] != '') {
                                                                                                                                echo 'value="' . $datos[0]['cuotas'] . '"';
                                                                                                                            } ?>>
                                    <label for="cuota">No. de cuotas</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="form-floating mb-3 mt-2">
                                    <input type="date" class="form-control" id="fecdesembolso" placeholder="Fecha de desembolso" <?php if ($bandera && $datos[0]['cuotas'] != '') {
                                                                                                                                        echo 'value="' . $datos[0]['fecdesembolso'] . '"';
                                                                                                                                    } else {
                                                                                                                                        echo 'value="' . date('Y-m-d') . '"';
                                                                                                                                    } ?>>
                                    <label for="fecdesembolso">Fecha de desembolso</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="form-floating mb-3 mt-2">
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
                //SELECCIONAR LOS CHECKBOXS DESPUES DE CARGAR EL DOM
                $(document).ready(function() {
                    creperi('tpscre2', '#alrtpnl', 'cre_indi_01', 'Flat');
                    <?php if ($bandera_garantias2) { ?>
                        marcar_garantias_recuperadas(<?php echo json_encode($datosgarantiasrecuperados); ?>);
                    <?php } ?>
                });
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
                                    <input type="text" class="form-control" id="tasaprod" placeholder="% Interes" readonly <?php if ($bandera) {
                                                                                                                                        echo 'value="' . $datos[0]['interesprod'] . '"';
                                                                                                                                    } ?>>
                                    <label for="tasaprod">% Interes </label>
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
            }
            // echo '<pre>';
            // print_r($datos);
            // echo '</pre>';
        ?>

            <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
            <input type="text" id="file" value="cre_indi_01" style="display: none;">
            <input type="text" id="condi" value="desembolso_individual" style="display: none;">
            <div class="text" style="text-align:center">DESEMBOLSO DE CRÉDITO INDIVIDUAL</div>
            <div class="card">
                <div class="card-header">Desembolso de crédito individual</div>
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
                                                                                                                                            echo 'value="' . $datos[0]['ccodcta'] . '"';
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
                    </div>

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
                            <button id="bt_desembolsar" class="btn btn-outline-success" onclick="savedesem('<?= $codusu; ?>','<?= $id_agencia; ?>')"><i class="fa-solid fa-money-bill"></i> Desembolsar</button>
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