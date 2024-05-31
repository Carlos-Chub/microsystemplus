<?php
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '3600');
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../includes/Config/database.php';
$database = new Database($db_host, $db_name, $db_user, $db_password, $db_name_general);

include '../funcphp/func_gen.php';
require '../../vendor/autoload.php';
include_once '../envia_correo.php';
$idusuario = $_SESSION['id'];

date_default_timezone_set('America/Guatemala');
$hoy2 = date("Y-m-d H:i:s");
$hoy = date("Y-m-d");
$idagencia = $_SESSION['id_agencia'];
// $archivoLog = __DIR__ . '/../../logs/errores.log';

use Luecano\NumeroALetras\NumeroALetras;


$condi = $_POST["condi"];

switch ($condi) {
    case 'cahomtip':
        if (!isset($_SESSION['id_agencia'])) {
            echo json_encode(['Sesion expirada, vuelve a iniciar sesion e intente nuevamente', '0']);
            return;
        }
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"]; //selects datos
        $inputsn = $_POST["inputsn"];  // 
        $selectsn = $_POST["selectsn"];     // selects nombres
        $consulta = "";
        //PARA LOS INPUTS
        if ($inputsn[0] != "nada") {
            $i = 0;
            foreach ($inputs as $input) {
                $consulta = $consulta . "`" . $inputsn[$i] . "`";
                if ($i != count($inputs) - 1) {
                    $consulta = $consulta . ",";
                }
                $i = $i + 1;
            }
        }
        //PARA LOS SELECTS
        $i = 0;
        foreach ($selects as $select) {
            $consulta = $consulta . ",";
            $consulta = $consulta . "`" . $selectsn[$i] . "`";
            $i = $i + 1;
        }
        $tasa = floatval($inputs[2]);
        $valido = validarcampo($inputs, "");
        if ($valido == "1") {
            $query = mysqli_query($conexion, "INSERT INTO `ahomtip`($consulta,`correlativo`) VALUES ('$inputs[0]','$inputs[1]',$tasa,'$inputs[3]',$inputs[4],'$selects[0]','$selects[1]',0)");
            if ($query) {
                echo json_encode(['Registro Ingresado ', '1']);
            } else {
                echo json_encode(['Error al ingresar ', '0']);
            }
        } else {
            echo json_encode([$valido, '0']);
        }

        mysqli_close($conexion);
        break;

        /*--------------------------------------------------------------------------------- */
    case 'uahomtip':
        if (!isset($_SESSION['id_agencia'])) {
            echo json_encode(['Sesion expirada, vuelve a iniciar sesion e intente nuevamente', '0']);
            return;
        }
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $inputsn = $_POST["inputsn"];
        $idtip = $_POST["archivo"];  // 
        $consulta = "";
        //PARA LOS INPUTS
        $i = 0;
        foreach ($inputs as $input) {
            $consulta = $consulta . "`" . $inputsn[$i] . "` = '" . $input . "'";
            if ($i != count($inputs) - 1) {
                $consulta = $consulta . ",";
            }
            $i = $i + 1;
        }

        $valido = validarcampo($inputs, "");
        if ($valido == "1") {
            $query = mysqli_query($conexion, "UPDATE `ahomtip` set $consulta,tipcuen='$selects[1]' WHERE id_tipo=" . $idtip);
            if ($query) {
                echo json_encode(['Registro actualizado correctamente ', '1']);
            } else {
                echo json_encode(['Error al actualizar ', '0']);
            }
        } else {
            echo json_encode([$$valido, '0']);
        }
        mysqli_close($conexion);
        break;

        //****** */
    case 'dahomtip':
        if (!isset($_SESSION['id_agencia'])) {
            echo json_encode(['Sesion expirada, vuelve a iniciar sesion e intente nuevamente', '0']);
            return;
        }
        $idahomtip = $_POST["ideliminar"];

        $eliminar = "DELETE FROM ahomtip WHERE id_tipo =" . $idahomtip;
        if (mysqli_query($conexion, $eliminar)) {
            echo json_encode(['Eliminacion correcta ', '1']);
        } else {
            echo json_encode(['Error al eliminar ', '0']);
        }
        mysqli_close($conexion);

        break;

    case 'cahomcta':
        if (!isset($_SESSION['id_agencia'])) {
            echo json_encode(['Sesion expirada, vuelve a iniciar sesion e intente nuevamente', '0']);
            return;
        }
        $hoy = date("Y-m-d");
        // [`ins`,`ccodofi`,`tasa`,`ccodcli`,`nit`,`libreta`],[`tipCuenta`,`presta`]  
        //SELECT MAX(SUBSTR(`ccodaho`,9,6)) campo FROM `ahomcta` WHERE SUBSTR(`ccodaho`,7,2)='20'
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"]; //selects datos
        $inputsn = $_POST["inputsn"];  // 
        $selectsn = $_POST["selectsn"];     // selects nombres

        if ($selects[0] == "0") {
            echo json_encode(['No ha seleccionado un tipo de producto o bien la agencia del usuario no tiene asignado ningun prouducto', '0']);
            return;
        }

        // $consulta = mysqli_query($conexion, "SELECT MAX(SUBSTR(`ccodaho`,9,6)) campo FROM `ahomcta` WHERE SUBSTR(`ccodaho`,7,2)=$selects[0]");
        // $ultimocorrel = "0";
        // while ($ultimo = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
        //     $ultimocorrel = ($ultimo['campo']);
        // }
        // $correlactual = ((int)$ultimocorrel) + 1;
        //genera codigo
        // $generar = $inputs[0] . $inputs[1] . $selects[0] . (sprintf('%06d', $correlactual));
        $codcredito = getccodaho($idagencia, $selects[0], $conexion);
        if ($codcredito[0] == 0) {
            echo json_encode(["Fallo!, No se pudo generar el código de cuenta", '0']);
            return;
        }
        $generar=$codcredito[1];
        // echo $generar;

        $tasa = floatval($inputs[2]);

        $validacion = validarcampo([$inputs[4], $inputs[5]], "");
        if ($validacion == "1") {
            //inicio transaccion
            $conexion->autocommit(false);
            try {
                $conexion->query("INSERT INTO `ahomcta`(`ccodaho`,`ccodcli`,`num_nit`,`nlibreta`,`estado`,`fecha_apertura`,`fecha_mod`,`codigo_usu`,`tasa`) VALUES ('$generar','$inputs[3]','$inputs[4]','$inputs[5]','A','$hoy','$hoy','$inputs[6]',$tasa)");
                // $conexion->query("UPDATE `ahomtip` set `correlativo`= $correlactual WHERE ccodtip=" . $selects[0]);
                $conexion->query("INSERT INTO `ahomlib`(`nlibreta`,`ccodaho`,`estado`,`date_ini`,`ccodusu`) VALUES ('$inputs[5]','$generar','A','$hoy','$inputs[6]')");
                $conexion->commit();
                echo json_encode(['Correcto,  Codigo Generado: ' . $generar, '1']);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
            }
            //FIN TRANSACCION
        } else {
            echo json_encode([$validacion, '0']);
        }

        mysqli_close($conexion);
        break;

        //consultar el correlativo que toca
    case 'correl':
        $tipo = $_POST["tipo"];
        $ins = $_POST["ins"];
        $ofi = $_POST["ofi"];
        // $consulta = mysqli_query($conexion, "SELECT MAX(SUBSTR(`ccodaho`,9,6)) campo FROM `ahomcta` WHERE SUBSTR(`ccodaho`,7,2)=$tipo");
        // $ultimocorrel = "0";
        // while ($ultimo = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
        //     $ultimocorrel = utf8_encode($ultimo['campo']);
        // }
        // $correlactual = ((int)$ultimocorrel) + 1;
        //genera codigo
        $codcredito = getccodaho($idagencia, $tipo, $conexion);
        if ($codcredito[0] == 0) {
            echo json_encode(["Fallo!, No se pudo generar el código de cuenta", '0']);
            return;
        }
        // $generar = $ins . $ofi . $tipo . (sprintf('%06d', $correlactual));
        $generar=$codcredito[1];
        //echo $generar;

        //----
        $tasa = 0;
        $consultatas = mysqli_query($conexion, "SELECT `tasa`,`ccodofi` FROM `ahomtip` WHERE `ccodtip`=$tipo");
        while ($row = mysqli_fetch_array($consultatas, MYSQLI_ASSOC)) {
            $tasa = ($row['tasa']);
            $oficina = ($row['ccodofi']);
        }
        //---
        echo json_encode([$generar, $tasa, $oficina]);
        mysqli_close($conexion);
        break;

        //insertar deposito a cuenta de ahorro
    case 'cdahommov':
        // depositos (['ccodaho', 'dfecope', 'cnumdoc', 'monto','cnumdocboleta'], ['salida', 'tipdoc', 'bancoid', 'cuentaid'], [], 
        // 'cdahommov', '0', [<?php echo $id; , action]);

        //retiros(['ccodaho', 'dfecope', 'cnumdoc', 'monto','numcheque'], ['salida', 'tipdoc', 'bancoid', 'cuentaid','negociable'],
        // [], 'cdahommov', '0', [' echo $id; ', 'R']);
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $archivo = $_POST["archivo"];
        $hoy = date("Y-m-d H:i:s");
        $hoy2 = date("Y-m-d");

        if ($archivo[1] == 'R') {
            //Validar si la cuenta esta como garantia
            $valida = $conexion->query("SELECT cta.ret AS std  FROM tb_garantias_creditos tgc 
               INNER JOIN cli_garantia cg ON cg.idGarantia = tgc.id_garantia 
               INNER JOIN ahomcta cta ON cta.ccodaho = cg.descripcionGarantia
               INNER JOIN cremcre_meta cm ON cm.CCODCTA = tgc.id_cremcre_meta
               WHERE cta.ccodaho = '$inputs[0]'");

            $aux = mysqli_error($conexion);
            if (!$valida || $aux) {
                $conexion->rollback();
                echo json_encode(['Erro 2000', '0']);
                return;
            }
            $std = $valida->fetch_assoc();

            $data = (isset($std['std'])) ? $std['std'] : "NoAPL";

            if ($data = '0') {

                echo json_encode(['En la cuenta no se puede realizar retiros, ya que se encuentra vinculada a un crédito.', '0']);
                return;
            }
        }

        //INI proceso de deposito 
        if (!isset($_SESSION['id_agencia'])) {
            echo json_encode(['Sesion expirada, vuelve a iniciar sesion e intente nuevamente', '0']);
            return;
        }
        //COMPROBAR SI EL MES CONTABLE ESTA ABIERTO
        $cierre = comprobar_cierre($idusuario, $inputs[1], $conexion);
        if ($cierre[0] == 0) {
            echo json_encode([$cierre[1], '0']);
            return;
        }
        //COMPROBAR CIERRE DE CAJA
        $cierre_caja = comprobar_cierre_caja($idusuario, $conexion);
        if ($cierre_caja[0] < 6) {
            echo json_encode([$cierre_caja[1], '0']);
            return;
        }
        //VALIDACION DE MONTO
        $monto = $inputs[3];
        if (!(is_numeric($monto))) {
            echo json_encode(['Monto inválido, ingrese un monto correcto', '0']);
            return;
        }
        if ($monto <= 0) {
            echo json_encode(['Monto negativo ó igual a 0, ingrese un monto correcto', '0']);
            return;
        }
        //VALIDACION DE FECHA
        $fechaoperacion = $inputs[1];
        if (!validateDate($fechaoperacion, 'Y-m-d')) {
            echo json_encode(['Fecha inválida, ingrese una fecha correcta', '0']);
            return;
        }
        // if ($fechaoperacion < $hoy2) {
        //     echo json_encode(['Esta ingresando una fecha menor a la de hoy', '0']);
        //     return;
        // }

        //VALIDACION DE NUMERO DE DOCUMENTOS
        $numdoc = $inputs[2];
        if ($numdoc == "") {
            echo json_encode(['Numero de Documento inválido', '0']);
            return;
        }

        $cuenta = $archivo[0];
        $tipotransaccion = $archivo[1];
        $razon = ($tipotransaccion == "R") ? "RETIRO" : "DEPOSITO";
        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            +++++++++++++++++++++++++++++++++ SALDO DE LA CUENTA DE AHORRO +++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $montoaux = 0;
        $saldo = 0;
        $query = "SELECT `monto`,`ctipope`,`dfecope` FROM `ahommov` WHERE `ccodaho`=? AND cestado!=2";
        $response = executequery($query, [$cuenta], ['s'], $conexion);
        if (!$response[1]) {
            echo json_encode([$response[0], '0']);
            return;
        }
        $data = $response[0];
        //$flag = ((count($data)) > 0) ? true : false;
        foreach ($data as $row) {
            $tiptr = ($row["ctipope"]);
            $montoaux = ($row["monto"]);
            if ($tiptr == "R") {
                $saldo = $saldo - $montoaux;
            }
            if ($tiptr == "D") {
                $saldo = $saldo + $montoaux;
            }
        }
        $saldo = round($saldo, 2);
        if ($tipotransaccion == "R") {
            if ($monto > $saldo) {
                echo json_encode(['El saldo disponible en la cuenta es menor al monto solicitado', '0']);
                return;
            }
            if ($monto == $saldo) {
                $inactivar = ", `estado` = '0',`fecha_cancel` = '" . $hoy . "'"; //SE AGREGA A LA ACTUALIZACION DEL AHOMCTA LA INACTIVACION DE LA CUENTA: ESTADO B
            }
        }
        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++ CUENTAS CONTABLES +++++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        // SELECT id_nomenclatura_caja FROM tb_agencia WHERE id_agencia=1;
        // SELECT id_cuenta_contable FROM ahomtip WHERE ccodtip="02";
        // SELECT id_nomenclatura FROM ctb_bancos WHERE id=14;
        $tipo_documento = $selects[1];
        $query = "SELECT id_nomenclatura_caja cuenta FROM tb_agencia WHERE id_agencia=?";
        $response = executequery($query, [$idagencia], ['i'], $conexion);
        if (!$response[1]) {
            echo json_encode([$response[0], '0']);
            return;
        }
        $data = $response[0];
        $cuentacaja = $data[0]['cuenta']; //cuenta contable de caja de la agencia

        $query = "SELECT id_cuenta_contable cuenta,nombre FROM ahomtip WHERE ccodtip=?";
        $response = executequery($query, [substr($cuenta, 6, 2)], ['s'], $conexion);
        if (!$response[1]) {
            echo json_encode([$response[0], '0']);
            return;
        }
        $data = $response[0];
        $flag = ((count($data)) > 0) ? true : false;
        if (!$flag) {
            echo json_encode(['No se encontró el tipo de cuenta', '0']);
            return;
        }
        $cuenta_tipo = $data[0]['cuenta']; //cuenta contable del tipo de ahorro
        $producto = $data[0]['nombre']; //cuenta contable del tipo de ahorro

        $cuentacontable = $cuentacaja;
        $tipopoliza = 2; //por defecto es de tipo ahorros
        $nocheque = '0';
        $auxiliar = "";
        $nodocconta = $numdoc;
        $desnumdocconta = "";
        if ($tipo_documento == "D" || $tipo_documento == "C") { //SI LA TRANSACCION ES DE TIPO DEPOSITO CON BOLETA DE BANCOS O CHEQUES
            if ($selects[2] == 0) {
                echo json_encode(['Seleccione un banco', '0']);
                return;
            }
            if ($selects[3] == 0) {
                echo json_encode(['Seleccione una cuenta de banco', '0']);
                return;
            }
            $tipopoliza = 11; //NOTA DE CREDITO ES 11, CHEQUE ES 7
            if ($tipo_documento == "C") {
                //VALIDACION DE NUMERO DE CHEQUE
                $nocheque = $inputs[4];
                if (!(is_numeric($nocheque))) {
                    echo json_encode(['Número de cheque inválido, ingrese un número correcto', '0']);
                    return;
                }
                if ($monto < 0) {
                    echo json_encode(['Número de cheque negativo, ingrese un número correcto', '0']);
                    return;
                }
                $tipopoliza = 7; //NOTA DE CREDITO ES 11, CHEQUE ES 7
                $negociable = $selects[4];
                $desnumdocconta = ", CON CHEQUE NO. " . $nocheque;
            }
            if ($tipo_documento == "D") {
                //VALIDACION DE NUMERO DE DOC BOLETA
                $nocheque = $inputs[4];
                if ($nocheque == "") {
                    echo json_encode(['Número de Boleta de banco inválido', '0']);
                    return;
                }
                $desnumdocconta = ", CON BOLETA DE BANCO NO. " . $nocheque;
            }
            $nodocconta = $nocheque;
            $auxiliar = $selects[3];
            $query = "SELECT id_nomenclatura cuenta FROM ctb_bancos WHERE id=?";
            $response = executequery($query, [$selects[3]], ['i'], $conexion);
            if (!$response[1]) {
                echo json_encode([$response[0], '0']);
                return;
            }
            $data = $response[0];
            $cuenta_banco = $data[0]['cuenta']; //cuenta contable de la cuenta de banco si es por bancos
            $cuentacontable = $cuenta_banco;
        }
        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++ DATOS DE LA CUENTA DE AHORROS +++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $query = "SELECT cta.ccodcli,cta.estado,cta.nlibreta,cli.no_tributaria num_nit,cli.short_name,cli.no_identifica dpi,cli.control_interno 
            FROM `ahomcta` cta INNER JOIN tb_cliente cli ON cli.idcod_cliente=cta.ccodcli 
            WHERE `ccodaho`=? AND cta.estado='A'";

        $response = executequery($query, [$cuenta], ['s'], $conexion);
        if (!$response[1]) {
            echo json_encode([$response[0], '0']);
            return;
        }
        $data = $response[0];
        $flag = ((count($data)) > 0) ? true : false;
        if (!$flag) {
            echo json_encode(["Cuenta de ahorro no existe ó no está activa", '0']);
            return;
        }
        $da = $data[0];
        $idcli = utf8_encode($da["ccodcli"]);
        $nit = ($da["num_nit"]);
        $dpi = ($da["dpi"]);
        $controlinterno = ($da["control_interno"]);
        $nlibreta = ($da["nlibreta"]);
        $estado = ($da["estado"]);
        $nombre = ($da["short_name"]);
        $ultimonum = lastnumlin($cuenta, $nlibreta, "ahommov", "ccodaho", $conexion);
        $ultimocorrel = lastcorrel($cuenta, $nlibreta, "ahommov", "ccodaho", $conexion);
        $numlib = numfront(substr($cuenta, 6, 2), "ahomtip") + numdorsal(substr($cuenta, 6, 2), "ahomtip");
        if ($ultimonum >= $numlib) {
            echo json_encode(["El número de líneas en libreta ha llegado a su límite, se recomienda abrir otra libreta", '0']);
            return;
        }
        if ($estado != "A") {
            echo json_encode(["Cuenta de ahorros Inactiva", '0']);
            return;
        }
        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            +++++++++++++++++++++++++++++++++++++++++++ ALERTA IVE +++++++++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        //INICIO ALERTA IVE
        //ALERTE IVE ---> No superar los $ 10,000 - hacer la conversion a quetazqles
        $consulta = mysqli_query($conexion, "SELECT ccodcli FROM ahomcta WHERE ccodaho = '$cuenta'"); //Seleccionar el codigo del cliente

        $error = mysqli_error($conexion);
        if ($error) {
            echo json_encode(['Error … !!!, ' . $error, '0']);
            return;
        };

        $codCli = mysqli_fetch_assoc($consulta);
        $validaAlerta = alerta($conexion, 3, $cuenta); //Validar si en los utimos 30 días, la cuenta del cliente ha llenado el formulario del IVE 
        $alertaAux = alerta($conexion, 4, $cuenta, '', '', '', '', $numdoc); // Valida si el codigo de documento y num de cuenta ya fue registrada la tb_Alerta
        $dolar = bcdiv(((movimiento($conexion, 2)) * 10000), '1', 2); //Se obtine el valor neto de los $10000 en Quetzales

        //En los ultimos 30 días ya lleno el formulario ive 
        if ($validaAlerta == 1 && $alertaAux == 0) {
            $mov = ((movimiento($conexion, 5, $codCli, 'D', '', $cuenta)) - (movimiento($conexion, 5, $codCli, 'R', '', $cuenta))); //Validador auxiliar
            $alert = alerta($conexion, 2, $cuenta, $hoy2); //Valida el proceso de alerta

            //Alerta de IVE ***
            if ($alert == 'EP1') {
                echo json_encode(['004 Para continuar con la transacción, el usuario tiene que pasar a secretaria para llenar el formulario IVE. ', '0']);
                return;
            }

            //Alerta de IVE ***
            if ($alert == 'VC' && ($mov + $monto) > $dolar) {
                alerta($conexion, 1, $cuenta, $hoy2, $idusuario, $hoy, 'EP1', $fechaoperacion);
                alerta($conexion, 5, $cuenta, '', '', '', '', $fechaoperacion, $nombre);
                echo json_encode(['003 ALERTA IVE... en los últimos 30 días la cuenta del cliente ha superado los $10000, para continuar con la transacción el “contador o administrador” tiene que aprobar la alerta. Favor de apuntar el No. Documento: ' . $numdoc . '', '0']);
                return;
            }

            if ($alert == 'A1' && ($mov + $monto) > $dolar) {
                // echo json_encode(['0021 mov '.($mov+$inputs[3])." alert ".$alert, '0']);
                // return;
                alerta($conexion, 1, $cuenta, $hoy2, $idusuario, $hoy, 'EP1', $numdoc);
                alerta($conexion, 5, $cuenta, '', '', '', '', $numdoc, $nombre);
                echo json_encode(['005 ALERTA IVE... en los últimos 30 días la cuenta del cliente ha superado los $10000, para continuar con la transacción el “contador o administrador” tiene que aprobar la alerta. Favor de apuntar el No. Documento: ' . $numdoc . '', '0']);
                return;
            }
        }

        //No se ha llenado el formulario de ive durante los ultimos 30 días 
        if ($validaAlerta == 0 && $alertaAux == 0) {
            $mov = movimiento($conexion, 3, $codCli); //Deposito - Retiros 
            $alert = alerta($conexion, 2, $cuenta, $hoy2); //Valida el proceso de alerta

            //    echo json_encode(['001mov '.($mov+ $inputs[3])." alert ".$alert, '0']); 
            //    return; 

            //Alerta de IVE ***
            if ($alert == 'EP') {
                echo json_encode(['002 Para continuar con la transacción, el usuario tiene que pasar a secretaria para llenar el formulario IVE. ', '0']);
                return;
            }

            //Alerta de IVE ***
            if ($alert == "VC" && ($mov + $monto) > $dolar) {
                alerta($conexion, 1, $cuenta, $hoy2, $idusuario, $hoy, 'EP', $numdoc);
                alerta($conexion, 5, $cuenta, '', '', '', '', $numdoc, $nombre);
                echo json_encode(['alert ' . $alert . ' mov ' . $mov . ' monto ' . $monto . ' dolar ' . $dolar . '001 ALERTA IVE... en los últimos 30 días la cuenta del cliente ha superado los $10000, para continuar con la transacción el “contador o administrador” tiene que aprobar la alerta. Favor de apuntar el No. Documento: ' . $numdoc . '', '0']);
                return;
            }
        }

        // echo json_encode(['alert '.$alert.' mov '.$mov.' monto '.$monto.' dolar '.$dolar.'  fidisafll', '0']);
        //         return;
        //FIN ALERTA IVE 
        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++ INSERCIONES EN LA BASE DE DATOS +++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $conexion->autocommit(false);
        try {
            $camp_numcom = getnumcom($idusuario, $conexion);
            // Preparar la primera consulta para INSERT ahommov
            // $query = "INSERT INTO `ahommov`(`ccodaho`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,`lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`cestado`,`auxi`,`created_at`,`created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";
            $res = $conexion->prepare("INSERT INTO `ahommov`(`ccodaho`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,`lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`cestado`,`auxi`,`created_at`,`created_by`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?,'0', '0', ?, 'N', ?, ?, ?, ?,1, ?, ?,?)");

            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux, '0']);
                $conexion->rollback();
                return;
            }
            $ultimonum = ($ultimonum + 1);
            $ultimocorrel = ($ultimocorrel + 1);
            $res->bind_param('ssssssisdiisssss', $cuenta, $fechaoperacion, $tipotransaccion, $numdoc, $tipo_documento, $razon, $nlibreta, $nocheque, $monto, $ultimonum, $ultimocorrel, $hoy, $idusuario, $auxiliar, $hoy, $idusuario);
            $res->execute();

            // Preparar la segunda consulta para INSERT ctbdiario
            $camp_glosa = $razon . " DE AHORRO DE " . $nombre . " CON RECIBO NO. " . $numdoc . $desnumdocconta;
            $res = $conexion->prepare("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) 
            VALUES (?,?,1,?, ?,?, ?,?,?,?,1)");
            // $response = executequery($query, [$camp_numcom, $tipopoliza, $numdoc, $camp_glosa, $fechaoperacion, $fechaoperacion, $cuenta, $idusuario, $hoy, 1], ['s', 'i', 's', 's', 's', 's', 's', 'i', 's', 'i'], $conexion);
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux, '0']);
                $conexion->rollback();
                return;
            }
            $res->bind_param('sisssssis', $camp_numcom, $tipopoliza, $nodocconta, $camp_glosa, $fechaoperacion, $fechaoperacion, $cuenta, $idusuario, $hoy);
            $res->execute();
            $id_ctb_diario = get_id_insertado($conexion);

            // Preparar la tercera consulta para INSERT ctbmov
            //REGISTRO DE LA CUENTA DEL TIPO DE AHORRO
            $mondebe = ($tipotransaccion == "R") ? $monto : 0;
            $monhaber = ($tipotransaccion == "R") ? 0 : $monto;
            $res = $conexion->prepare("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES (?,' ',1,?,?,?)");
            // $response = executequery($query, [$id_ctb_diario, $cuenta_tipo, $mondebe, $monhaber], ['iidd'], $conexion);
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux, '0']);
                $conexion->rollback();
                return;
            }
            $res->bind_param('iidd', $id_ctb_diario, $cuenta_tipo, $mondebe, $monhaber);
            $res->execute();
            //REGISTRO DE LA CUENTA DE CAJA O BANCOs
            $mondebe = ($tipotransaccion == "R") ? 0 : $monto;
            $monhaber = ($tipotransaccion == "R") ? $monto : 0;
            // $response = executequery($query, [$id_ctb_diario, $cuentacontable, $mondebe, $monhaber], ['i', 'i', 'd', 'd'], $conexion);
            $res = $conexion->prepare("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES (?,' ',1,?,?,?)");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux, '0']);
                $conexion->rollback();
                return;
            }
            $res->bind_param('iidd', $id_ctb_diario, $cuentacontable, $mondebe, $monhaber);
            $res->execute();

            if ($tipo_documento == "C") {
                //INSERCION EN CUENTAS DE CHEQUES
                $res = $conexion->prepare("INSERT INTO `ctb_chq`(`id_ctb_diario`,`id_cuenta_banco`,`numchq`,`nomchq`,`monchq`,`emitido`,`modocheque`) 
                                    VALUES (?,?,?, ?,?,'0',?)");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode([$aux, '0']);
                    $conexion->rollback();
                    return;
                }
                $res->bind_param('iissdi', $id_ctb_diario, $selects[3], $nocheque, $nombre, $monto, $negociable);
                $res->execute();
            }

            //ORDENAMIENTO DE TRANSACCIONES
            $res = $conexion->prepare("CALL ahom_ordena_noLibreta(?, ?)");
            // $response = executequery($query, [$nlibreta, $cuenta], ['i', 's'], $conexion);
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux, '0']);
                $conexion->rollback();
                return;
            }
            $res->bind_param('is', $nlibreta, $cuenta);
            $res->execute();

            $res = $conexion->prepare("CALL ahom_ordena_Transacciones(?)");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux, '0']);
                $conexion->rollback();
                return;
            }
            $res->bind_param('s', $cuenta);
            $res->execute();
            //-----FIN
            //NUMERO EN LETRAS
            $format_monto = new NumeroALetras();
            $decimal = explode(".", $monto);
            $res = (isset($decimal[1]) == false) ? 0 : $decimal[1];
            $letras_monto = ($format_monto->toMoney($decimal[0], 2, 'QUETZALES', '')) . " " . $res . "/100";
            $particionfecha = explode("-", $fechaoperacion);

            // $conexion->rollback(); //solo para pruebas00100221000011
            // echo json_encode([' se hizo rollback', '0']);
            // return;
            if ($conexion->commit()) {
                $auxdes = ($tipotransaccion == "D") ? "Depósito a cuenta " . $cuenta : "Retiro a cuenta " . $cuenta;
                echo json_encode(['Datos ingresados', '1', $cuenta, number_format($monto, 2, '.', ','), date("d-m-Y", strtotime($fechaoperacion)), $numdoc, $auxdes, $nombre, utf8_decode($_SESSION['nombre']), utf8_decode($_SESSION['apellido']), $hoy, $letras_monto, $particionfecha[0], $particionfecha[1], $particionfecha[2], $dpi, $producto, $_SESSION['id'], $controlinterno]);
            } else {
                echo json_encode(['Error al ingresar: ', '0']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        //fin transaccion
        break;
    case 'acreditaindi':
        $inputs = $_POST["inputs"];
        $archivo = $_POST["archivo"];

        $hoy = date("Y-m-d H:i:s");
        $hoy2 = date("Y-m-d");
        $ccodaho = $archivo[0];
        $fecope = $inputs[0];
        $montoint = $inputs[1];
        $montoipf = $inputs[2];


        if (!validateDate($fecope, 'Y-m-d')) {
            echo json_encode(['Fecha inválida, ingrese una fecha correcta', '0']);
            return;
        }
        if (!is_numeric($montoint)) {
            echo json_encode(['Monto Inválido (Interés)', '0']);
            return;
        }
        if ($montoint <= 0) {
            echo json_encode(['Monto negativo ó igual a 0 (Interés)', '0']);
            return;
        }
        if (is_numeric($montoipf)) {
            if ($montoipf < 0) {
                echo json_encode(['Monto negativo (Impuesto)', '0']);
                return;
            }
        } else {
            $montoipf = 0;
        }
        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++  DATOS DE LA CUENTA DE AHORROS ++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $showmensaje = false;
        try {
            $database->openConnection();
            $database->beginTransaction();
            $result = $database->selectColumns('ahommov', ['nlibreta'], 'ccodaho=?', [$ccodaho]);
            foreach ($result as $dat) {
                $nlibreta = $dat['nlibreta'];
            }
            $datosint = array(
                "ccodaho" => $ccodaho,
                "dfecope" => $fecope,
                "ctipope" => "D",
                "cnumdoc" => "INT",
                "ctipdoc" => "E",
                "crazon" => "INTERES",
                "nlibreta" => $nlibreta,
                "nrochq" => 0,
                "tipchq" => "",
                "dfeccomp" => "0000-00-00",
                "monto" => $montoint,
                "lineaprint" => "N",
                "numlinea" => 1,
                "correlativo" => 1,
                "dfecmod" => $hoy,
                "codusu" => $idusuario,
                "cestado" => 1,
                "auxi" => "ACREDITACION INDIVIDUAL",
                "created_at" => $hoy,
                "created_by" => $idusuario,
            );
            $datosipf = array(
                "ccodaho" => $ccodaho,
                "dfecope" => $fecope,
                "ctipope" => "R",
                "cnumdoc" => "IPF",
                "ctipdoc" => "E",
                "crazon" => "INTERES",
                "nlibreta" => $nlibreta,
                "nrochq" => 0,
                "tipchq" => "",
                "dfeccomp" => "0000-00-00",
                "monto" => $montoipf,
                "lineaprint" => "N",
                "numlinea" => 1,
                "correlativo" => 1,
                "dfecmod" => $hoy,
                "codusu" => $idusuario,
                "cestado" => 1,
                "auxi" => "ACREDITACION INDIVIDUAL",
                "created_at" => $hoy,
                "created_by" => $idusuario,
            );

            $database->insert('ahommov', $datosint);
            if ($montoipf > 0) {
                $database->insert('ahommov', $datosipf);
            }

            $database->executeQuery('CALL ahom_ordena_noLibreta(?, ?);', [$nlibreta, $ccodaho]);
            $database->executeQuery('CALL ahom_ordena_Transacciones(?);', [$ccodaho]);

            //MOVIMIENTOS EN LA CONTA
            $result = $database->getAllResults("SELECT ap.* FROM ahomparaintere ap INNER JOIN ahomtip tip ON tip.id_tipo=ap.id_tipo_cuenta 
                    WHERE ccodtip=SUBSTR(?,7,2) AND id_descript_intere IN (1,2)", [$ccodaho]);

            if (empty($result)) {
                $showmensaje = true;
                throw new Exception("No se encontraron cuentas contables parametrizadas.");
            }
            $keyint = array_search(1, array_column($result, 'id_descript_intere'));
            $keyisr = array_search(2, array_column($result, 'id_descript_intere'));

            if ($keyint === false || $keyisr === false) {
                $showmensaje = true;
                throw new Exception("No se encontraron cuentas contables parametrizadas ()." . $keyisr);
            }

            $cuentaint1 = $result[$keyint]['id_cuenta1'];
            $cuentaint2 = $result[$keyint]['id_cuenta2'];
            $cuentaisr1 = $result[$keyisr]['id_cuenta1'];
            $cuentaisr2 = $result[$keyisr]['id_cuenta2'];

            //AFECTACION CONTABLE
            $numpartida = getnumcom($idusuario, $conexion); //Obtener numero de partida
            $datos = array(
                'numcom' => $numpartida,
                'id_ctb_tipopoliza' => 2,
                'id_tb_moneda' => 1,
                'numdoc' => "INT",
                'glosa' => "ACREDITACION DE INTERESES A CUENTA DE AHORROS: " . $ccodaho,
                'fecdoc' => $fecope,
                'feccnt' => $fecope,
                'cod_aux' => $ccodaho,
                'id_tb_usu' => $idusuario,
                'fecmod' => $hoy,
                'estado' => 1,
                'editable' => 0
            );
            $id_ctb_diario = $database->insert('ctb_diario', $datos);

            //AFECTACION CONTABLE MOV 1 
            $datos = array(
                'id_ctb_diario' => $id_ctb_diario,
                'id_fuente_fondo' => 1,
                'id_ctb_nomenclatura' => $cuentaint1,
                'debe' => $montoint,
                'haber' => 0
            );
            $database->insert('ctb_mov', $datos);

            $datos = array(
                'id_ctb_diario' => $id_ctb_diario,
                'id_fuente_fondo' => 1,
                'id_ctb_nomenclatura' => $cuentaint2,
                'debe' => 0,
                'haber' => $montoint
            );
            $database->insert('ctb_mov', $datos);

            if ($montoipf > 0) {
                $datos = array(
                    'id_ctb_diario' => $id_ctb_diario,
                    'id_fuente_fondo' => 1,
                    'id_ctb_nomenclatura' => $cuentaisr1,
                    'debe' => $montoipf,
                    'haber' => 0
                );
                $database->insert('ctb_mov', $datos);

                $datos = array(
                    'id_ctb_diario' => $id_ctb_diario,
                    'id_fuente_fondo' => 1,
                    'id_ctb_nomenclatura' => $cuentaisr2,
                    'debe' => 0,
                    'haber' => $montoipf
                );
                $database->insert('ctb_mov', $datos);
            }
            $database->commit();
            $mensaje = "Registro grabado correctamente";
            $status = 1;
        } catch (Exception $e) {
            $database->rollback();
            if (!$showmensaje) {
                $codigoError = logerrores($e->getMessage(), __FILE__, __LINE__, $e->getFile(), $e->getLine());
            }
            $mensaje = ($showmensaje) ? "Error: " . $e->getMessage() : "Error: Intente nuevamente, o reporte este codigo de error($codigoError)";
            $status = 0;
        } finally {
            $database->closeConnection();
        }
        echo json_encode([$mensaje, $status]);
        break;
    case 'modlib':
        $inputs = $_POST["inputs"];
        $inputsn = $_POST["inputsn"];  // 
        $archivos = $_POST["archivo"];
        $hoy = date("Y-m-d H:i:s");
        $hoy2 = date("Y-m-d");
        $validar = validarcampo($inputs, "");
        if ($validar == "1") {
            if ($inputs[1] < 1) {
                echo json_encode(['Ingrese un numero valido', '0']);
            } else {
                //------traer el saldo de la cuenta
                $monto = 0;
                $saldo = 0;
                $transac = mysqli_query($conexion, "SELECT `monto`,`ctipope` FROM `ahommov` WHERE `ccodaho`='$archivos[0]' AND cestado!=2");
                while ($row = mysqli_fetch_array($transac, MYSQLI_ASSOC)) {
                    $tiptr = utf8_encode($row["ctipope"]);
                    $monto = utf8_encode($row["monto"]);
                    if ($tiptr == "R") {
                        $saldo = $saldo - $monto;
                    }
                    if ($tiptr == "D") {
                        $saldo = $saldo + $monto;
                    }
                }
                //****fin saldo */
                //transaccion
                $conexion->autocommit(false);
                try {
                    $ultimonum = lastnumlin($inputs[0], $archivos[1], "ahommov", "ccodaho", $conexion);
                    $ultimocorrel = lastcorrel($inputs[0], $archivos[1], "ahommov", "ccodaho", $conexion);
                    //desactivar en ahomlib los datos de la nueva libreta
                    $conexion->query("UPDATE `ahomlib` SET `estado` = 'B',`date_fin` = '$hoy' WHERE `ccodaho` = '$inputs[0]' AND `nlibreta`=  $archivos[1]");
                    //insertar en ahomlib   ['ccodaho','newLibret'],['nothing'], ['nada'], 'modlib', '0', ['<?php echo $id; ',<?php echo $nlibreta; ,'<?php echo $ccodusu; ']
                    $conexion->query("INSERT INTO `ahomlib`(`nlibreta`,`ccodaho`,`estado`,`date_ini`,`ccodusu`,`crazon`) VALUES ('$inputs[1]','$inputs[0]','A','$hoy2','$archivos[2]','maxlin')");
                    //insertar en ahommov
                    $conexion->query("INSERT INTO `ahommov`(`ccodaho`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,`lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`) VALUES ('$inputs[0]','$hoy2','R','LIB0001','E','CAMBIO LIBRETA', $archivos[1],'','','',$saldo,'N',$ultimonum+1,$ultimocorrel+1,'$hoy','$archivos[2]')");
                    $conexion->query("INSERT INTO `ahommov`(`ccodaho`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,`lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`) VALUES ('$inputs[0]','$hoy2','D','LIB0001','E','SALDO INI', $inputs[1],'','','',$saldo,'N',1,$ultimocorrel+2,'$hoy','$archivos[2]')");
                    //actualizar en ahomcta
                    $conexion->query("UPDATE `ahomcta` SET `nlibreta` = '$inputs[1]',`numlinea` = 1,`correlativo` = $ultimocorrel+2 WHERE `ccodaho` = '$inputs[0]'");

                    if ($conexion->commit()) {
                        echo json_encode(['Datos ingresados correctamente', '1']);
                    } else {
                        echo json_encode(['Error al ingresar: ', '0']);
                    }
                } catch (Exception $e) {
                    $conexion->rollback();
                    echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
                }
                //fin transaccion
            }
        } else {
            echo json_encode([$validar, '0']);
        }
        mysqli_close($conexion);
        break;
        /*--------------------------------------------------------------------------------- */
    case 'cahomben':
        //['benname', 'bendpi', 'bendire', 'bentel', 'bennac', 'benporcent'], ['benparent']
        $hoy = date("Y-m-d");
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"]; //selects datos
        $inputsn = $_POST["inputsn"];  // 
        $selectsn = $_POST["selectsn"];     // selects nombres
        $archivos = $_POST["archivo"];
        if ($archivos[2] == "") {
            $validacion = validarcampo($inputs, "");
            if ($validacion == "1") {
                $total = $archivos[1] + $inputs[5];
                if ($total > 100 || $inputs[5] <= 0) {
                    echo json_encode(['Verifique el porcentaje ingresado, que con la sumatoria no supere los 100 ó que no sea menor o igual a 0', '0']);
                } else {
                    $validparent = validarcampo($selects, "0");
                    if ($validparent == "1") {
                        if (preg_match('/^\d{13}$/', $inputs[1]) == false) {
                            echo json_encode(['Ingrese un número de DPI válido, debe tener 13 caracteres numericos', '0']);
                            return;
                        }
                        if (preg_match('/^(?:\+?502\s?)?(?:\d{10}|\d{8}|\d{4}\s?\d{4}|\d{7}|\d{9})$/', $inputs[3]) == false) {
                            echo json_encode(['Debe digitar un número de teléfono válido', '0']);
                            return;
                        }
                        $conexion->autocommit(false);
                        try {
                            $conexion->query("INSERT INTO `ahomben`(`codaho`,`nombre`,`dpi`,`direccion`,`codparent`,`fecnac`,`porcentaje`,`telefono`,`ccodcrt`) VALUES ('$archivos[0]','$inputs[0]','$inputs[1]','$inputs[2]','$selects[0]','$inputs[4]','$inputs[5]','$inputs[3]','$inputs[6]')");
                            $conexion->commit();
                            echo json_encode(['Correcto,  Beneficiario guardado ', '1']);
                        } catch (Exception $e) {
                            $conexion->rollback();
                            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
                        }
                    } else {
                        echo json_encode(['Seleccione parentesco', '0']);
                    }
                }
            } else {
                echo json_encode([$validacion, '0']);
            }
        } else {
            echo json_encode(['Seleccione Cliente primero', '0']);
        }
        mysqli_close($conexion);
        break;
    case 'dahomben':
        $idahomben = $_POST["ideliminar"];

        $eliminar = "DELETE FROM ahomben WHERE id_ben =" . $idahomben;
        if (mysqli_query($conexion, $eliminar)) {
            echo json_encode(['Eliminacion correcta ', '1']);
        } else {
            echo json_encode(['Error al eliminar ', '0']);
        }
        mysqli_close($conexion);
        break;
    case 'uahomben':
        //--------------------------------------------------------['benname', 'bendpi', 'bendire', 'bentel', 'bennac', 'benporcent','benporcentant','idben'], ['benparent'] 
        $hoy = date("Y-m-d");
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"]; //selects datos// 
        $archivos = $_POST["archivo"];
        $validacion = validarcampo($inputs, "");
        if ($validacion == "1") {
            $total = $archivos[1] - $inputs[6] + $inputs[5];
            if ($total > 100 || $inputs[5] <= 0) {
                echo json_encode(['Verifique el porcentaje ingresado, que con la sumatoria no supere los 100 ó que no sea menor o igual a 0', '0']);
            } else {
                $validparent = validarcampo($selects, "0");
                if ($validparent == "1") {
                    $conexion->autocommit(false);
                    try {
                        $conexion->query("UPDATE `ahomben` SET `nombre` = '$inputs[0]',`dpi` = '$inputs[1]',`direccion` = '$inputs[2]',`codparent` = $selects[0],`fecnac` = '$inputs[4]',`porcentaje` = $inputs[5],`telefono` = '$inputs[3]' WHERE `id_ben` = $inputs[7]");
                        $conexion->commit();
                        echo json_encode(['Correcto,  Beneficiario actualizado', '1']);
                    } catch (Exception $e) {
                        $conexion->rollback();
                        echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
                    }
                } else {
                    echo json_encode(['Seleccione parentesco', '0']);
                }
            }
        } else {
            echo json_encode([$validacion, '0']);
        }
        mysqli_close($conexion);
        break;
    case 'calculoprg':
        //monto,fini,ffin,freq 
        $monto = $_POST["monto"];
        $fecin = $_POST["fini"];
        $fecfi = $_POST["ffin"];
        $freq = $_POST["freq"];
        if ($monto == "") {
            echo json_encode(['Monto invalido', 0, 0, 0, 0]);
        } else {
            if ($fecfi > $fecin) {
                $dateDifference = abs(strtotime($fecfi) - strtotime($fecin)); //DIFERENCIA EN SEGUNDOS
                $dias_diferencia = $dateDifference / (60 * 60 * 24); //DIFERENCIA EN DIAS
                $dias_diferencia = abs($dias_diferencia); //valor absoluto y quitar posible negativo
                $dias_diferencia = floor($dias_diferencia); //quito los decimales a los días de diferencia
                /*
                            $years  = floor($dateDifference / (365 * 60 * 60 * 24));//cuantos años de diferencia entre las dos fechas
                            $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));//cuantos meses de diferencia incluyendo el año en el clculo
                            $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));//cuantos dias de diferencia incluyendo año y mes
                    */
                $diasfrec = 0;
                switch ($freq) {
                    case 'day7':
                        $diasfrec = 7;
                        break;
                    case 'day15':
                        $diasfrec = 14;
                        break;
                    case 'day30':
                        $diasfrec = 30;
                        break;
                    case 'none':
                        $diasfrec = 0;
                        break;
                }
                //$fecha = date('Y-m-j');
                //$nuevafecha = strtotime('+' . $diasfrec . ' day', strtotime($fecin));
                //$nuevafecha = date('Y-m-j', $nuevafecha);
                //echo $nuevafecha;
                //----
                $registros = array();

                $i = 0;
                $j = 0;
                $nuevafecha = $fecin;
                $cuota = round(($monto / floor(($dias_diferencia + $diasfrec - 1) / $diasfrec)), 2);
                $sald = 0;
                while ($i < $dias_diferencia) {
                    $sald = round($sald + $cuota, 2);
                    $registros[$j] = array($j + 1, $nuevafecha, $cuota, $sald);

                    $nuevafecha = strtotime('+' . $diasfrec . ' day', strtotime($nuevafecha));
                    $nuevafecha = date('Y-m-j', $nuevafecha);

                    if ($sald >= $monto) {
                        break;
                    }
                    $i = $i + $diasfrec;
                    $j++;
                }
                echo json_encode([$registros, $fecin, $fecfi, $monto, 1]);
            } else {
                echo json_encode(['Fechas de inicio y finalizacion invalidas', 0, 0, 0, 0]);
            }
        }
        //------------------------


        break;
    case 'cahoprog':
        if (!isset($_SESSION['id_agencia'])) {
            echo json_encode(['Sesion expirada, vuelve a iniciar sesion e intente nuevamente', '0']);
            return;
        }
        $hoy = date("Y-m-d");
        $hoy2 = date("Y-m-d H:i:s");
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"]; //selects datos
        $radios = $_POST["radios"]; //selects datos
        $archivos = $_POST["archivo"]; //selects datos
        //-----------------
        // $consulta = mysqli_query($conexion, "SELECT MAX(SUBSTR(`ccodaho`,9,6)) campo FROM `ahomcta` WHERE SUBSTR(`ccodaho`,7,2)=$selects[0]");
        // $ultimocorrel = "0";
        // while ($ultimo = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
        //     $ultimocorrel = ($ultimo['campo']);
        // }
        // $correlactual = ((int)$ultimocorrel) + 1;
        // //genera codigo
        // $generar = $archivos[1] . $archivos[2] . $selects[0] . (sprintf('%06d', $correlactual));

        $codcredito = getccodaho($idagencia, $selects[0], $conexion);
        if ($codcredito[0] == 0) {
            echo json_encode(["Fallo!, No se pudo generar el código de cuenta", '0']);
            return;
        }
        $generar=$codcredito[1];

        $tasa = floatval($inputs[4]);
        //-------------para la frecuencia
        $diasfrec = 0;
        switch ($radios[0]) {
            case 'day7':
                $diasfrec = 7;
                break;
            case 'day15':
                $diasfrec = 14;
                break;
            case 'day30':
                $diasfrec = 30;
                break;
            case 'none':
                $diasfrec = 0;
                break;
        }
        //---------------

        //-----------
        if ($inputs[0] == "") $nomaho = "AHORRO PROGRAMADO";
        else $nomaho = $inputs[0];
        //-------
        $monto = $inputs[1];
        if ($archivos[3] != "0") {
            $validacion = validarcampo([$inputs[1]], "");
            if ($validacion == "1") {
                //------------------------
                $fecin = $inputs[2];
                $fecfi = $inputs[3];
                if ($fecfi > $fecin) {
                    $dateDifference = abs(strtotime($fecfi) - strtotime($fecin));
                    $dias_diferencia = $dateDifference / (60 * 60 * 24);
                    $dias_diferencia = abs($dias_diferencia); //valor absoluto y quitar posible negativo
                    $dias_diferencia = floor($dias_diferencia); //quito los decimales a los días de diferencia

                    //----
                    $registros = array();

                    $i = 0;
                    $j = 0;
                    $nuevafecha = $fecin;
                    $cuota = round(($monto / floor(($dias_diferencia + $diasfrec - 1) / $diasfrec)), 2);
                    $sald = 0;

                    //-----------
                    while ($i < $dias_diferencia) {
                        $sald = round($sald + $cuota, 2);
                        $registros[$j] = array($j + 1, $nuevafecha, $cuota, $sald);

                        $nuevafecha = strtotime('+' . $diasfrec . ' day', strtotime($nuevafecha));
                        $nuevafecha = date('Y-m-j', $nuevafecha);

                        if ($sald >= $monto) {
                            break;
                        }
                        $i = $i + $diasfrec;
                        $j++;
                    }
                    //----------------

                    $conexion->autocommit(false);
                    try {
                        $conexion->query("INSERT INTO `ahomcta`(`ccodaho`,`ccodcli`,`num_nit`,`estado`,`fecha_apertura`,`fecha_mod`,`codigo_usu`,`tasa`,`cnomaho`,`accountprg`,`monobj`,`fecini`,`fecfin`,`frec`) 
                                                            VALUES ('$generar','$archivos[3]','','A','$hoy','$hoy','$archivos[4]',$tasa,'$nomaho','$selects[1]',$inputs[1],'$inputs[2]','$inputs[3]',$diasfrec)");
                        // $conexion->query("UPDATE `ahomtip` set `correlativo`= $correlactual WHERE ccodtip=" . $selects[0]);
                        //$conexion->query("INSERT INTO `ahomlib`(`nlibreta`,`ccodaho`,`estado`,`date_ini`,`ccodusu`) VALUES ('$inputs[5]','$generar','A','$hoy','$inputs[6]')");
                        $conexion->query("INSERT INTO `ahomppg`(`ccodaho`,`fecven`,`estado`,`tipope`,`nrocuo`,`monto`,`interes`,`usuario`,`fecmod`) VALUES ('$generar','$hoy','P','M',0,$inputs[1],0,$archivos[4],'$hoy2')");
                        foreach ($registros as $registro) {
                            $conexion->query("INSERT INTO `ahomppg`(`ccodaho`,`fecven`,`estado`,`tipope`,`nrocuo`,`monto`,`interes`,`usuario`,`fecmod`) VALUES ('$generar','$registro[1]','X','C',$registro[0],$registro[2],0,$archivos[4],'$hoy2')");
                        }

                        $conexion->commit();
                        echo json_encode(['Correcto,  Codigo Generado: ' . $generar, '1']);
                    } catch (Exception $e) {
                        $conexion->rollback();
                        echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
                    }
                } else {
                    echo json_encode(["Fechas de inicio y finalizacion invalidas", '0']);
                }
            } else {
                echo json_encode([$validacion, '0']);
            }
        } else {
            echo json_encode(["Seleccione un cliente primero", '0']);
        }


        mysqli_close($conexion);

        //--------------------------
        break;

    case 'cahomcrt':
        $hoy = date("Y-m-d H:i:s");
        $hoy2 = date("Y-m-d");
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $archivo = $_POST["archivo"];

        //valida si el cliente esta seleccionado
        $validacion = validarcampo([$archivo[1]], "");
        if ($validacion == "1") {
            echo json_encode(["Seleccione una cuenta primero", '0']);
            return;
        }
        //validacion del ingreso de una cuenta corriente
        if ($inputs[10] == "") {
            if ($selects[2] == "3") {
                echo json_encode(["Ingrese una cuenta corriente", '0']);
                return;
            } else {
                $inputs[10] = "0";
            }
        } else {
            if ($selects[2] != "3") {
                $inputs[10] = "0";
            }
        }

        //validacion del ingreso de una cuenta prestamo
        if ($selects[5] == "0") {
            if ($selects[4] == "S") {
                echo json_encode(["Seleccione una cuenta de prestamo", '0']);
                return;
            } else {
                $selects[5] = "0";
            }
        } else {
            if ($selects[4] != "S") {
                $selects[5] = "0";
            }
        }

        //validacion de los inputs si estan vacios
        $validacion = validarcampo($inputs, "");
        if ($validacion != "1") {
            echo json_encode([$validacion, '0']);
            return;
        }

        //validacion de limites
        if (validar_limites(1, 1000000, $inputs[4]) != "1" || validar_limites(1, 1000000, $inputs[5]) != "1" || validar_limites(1, 100, $inputs[7]) != "1") {
            echo json_encode(["Se ingresaron datos invalidos, revisar", '0']);
            return;
        }
        $norecibo = $inputs[11];
        //inicio transaccion
        $conexion->autocommit(false);
        try {
            $conexion->query("INSERT INTO `ahomcrt`(`ccodcrt`,`ccodcli`,`num_nit`,`codaho`,`montoapr`,`plazo`,`interes`,`recibo`,`fec_apertura`,`fec_ven`,`dia_gra`,`calint`,`pagint`,`codban`,`cuentaho`,`pignorado`,`codcta`,`liquidado`,`fec_mod`,`codusu`) 
                                 VALUES ('$inputs[0]','$inputs[2]','$inputs[3]','$inputs[1]',$inputs[4],$inputs[5],$inputs[7],'$norecibo','$inputs[8]','$inputs[9]',$inputs[6],'$selects[1]','$selects[2]','$selects[3]','$inputs[10]','$selects[4]','$selects[5]','N','$hoy','$archivo[2]')");

            $conexion->commit();
            echo json_encode(['Correcto registro ingresado', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'uahomcrt':
        //([`certif`,`codaho`,`codcli`,`nit`,`monapr`,`plazo`,`gracia`,`tasint`,`fecaper`,`fecven`,`cuentacor`],[`capitaliza`,`calintere`,`pagintere`,`bancom`,`pignora`,`codpres`],[`nada`],`cahomcrt`,`0`,'<?php echo $idtip; ')
        $hoy = date("Y-m-d H:i:s");
        $hoy2 = date("Y-m-d");
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $archivo = $_POST["archivo"];

        //valida si el cliente esta seleccionado
        /* $validacion = validarcampo([$archivo[1]], "");
                if ($validacion == "1") {
                    echo json_encode(["Seleccione una cuenta primero", '0']);
                    return;
                } */
        //validacion del ingreso de una cuenta corriente
        if ($inputs[10] == "") {
            if ($selects[2] == "3") {
                echo json_encode(["Ingrese una cuenta corriente", '0']);
                return;
            } else {
                $inputs[10] = "0";
            }
        } else {
            if ($selects[2] != "3") {
                $inputs[10] = "0";
            }
        }

        //validacion del ingreso de una cuenta prestamo
        if ($selects[5] == "0") {
            if ($selects[4] == "S") {
                echo json_encode(["Seleccione una cuenta de prestamo", '0']);
                return;
            } else {
                $selects[5] = "0";
            }
        } else {
            if ($selects[4] != "S") {
                $selects[5] = "0";
            }
        }

        //validacion de los inputs si estan vacios
        $validacion = validarcampo($inputs, "");
        if ($validacion != "1") {
            echo json_encode([$validacion, '0']);
            return;
        }

        //validacion de limites
        if (validar_limites(1, 1000000, $inputs[4]) != "1" || validar_limites(1, 1000000, $inputs[5]) != "1" || validar_limites(1, 100, $inputs[7]) != "1") {
            echo json_encode(["Se ingresaron datos invalidos, revisar", '0']);
            return;
        }
        $norecibo = $inputs[11];
        //inicio transaccion
        $conexion->autocommit(false);
        try {
            $conexion->query("UPDATE `ahomcrt` SET `montoapr`=$inputs[4],`plazo`=$inputs[5],`interes`=$inputs[7],`fec_apertura`='$inputs[8]',`fec_ven`='$inputs[9]',`dia_gra`=$inputs[6],
                `calint`='$selects[1]',`pagint`='$selects[2]',`codban`='$selects[3]',`cuentaho`='$inputs[10]',`pignorado`='$selects[4]',`codcta`='$selects[5]',
                `fec_mod`='$hoy',`codusu`='$archivo[2]',`recibo`='$norecibo' WHERE id_crt=$archivo[0]");

            $conexion->commit();
            echo json_encode(['Correcto registro actualizado', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;

    case 'calfec':
        $fecapr = $_POST["fecapr"];
        $fecven = $_POST["fecven"];
        $plazo = $_POST["plazo"];
        $monto = $_POST["mon"];
        $intere = $_POST["int"];
        $cond = $_POST["cond"];
        $daysc = $_POST["days"];
        switch ($cond) {
            case 1:
                if (validarcampo([$monto, $intere], "") != "1") {
                    echo json_encode(['0', "Ingresar Monto y tasa de Interes"]);
                    return;
                }

                if (validar_limites(1, 10000000, $monto) != "1") {
                    echo json_encode(['0', "Ingresar Monto correcto"]);
                    return;
                }
                if (validar_limites(1, 100, $intere) != "1") {
                    echo json_encode(['0', "Ingresar tasa correcta"]);
                    return;
                }
                $plazo = ($plazo > 180 && $plazo < 190) ? 180 : $plazo;

                $interes = $monto * ($intere / 100 / $daysc);
                $interes = round($interes * $plazo, 2);
                $ipf = $interes * 0.10;
                $total = $interes - $ipf;
                echo json_encode(['1', $interes, $ipf, $total]);
                break;
            case 2:
                //------------------fecha plazo
                if (validarcampo([$plazo], "") != "1") {
                    $plazo = 0;
                }
                if (validar_limites(1, 10000, $plazo) != 1) {
                    echo json_encode(['0', "Ingresar un plazo correcto"]);
                    return;
                }

                $nuevafecha = strtotime('+' . $plazo . ' day', strtotime($fecapr));
                $nuevafecha = date('Y-m-j', $nuevafecha);
                $date = new DateTime($nuevafecha);
                $result = $date->format('Y-m-d');
                //-----------------------------------

                echo json_encode(['1', $result]);
                break;
            case 3:
                $diasdif = dias_dif($fecapr, $fecven);
                echo json_encode(['1', $diasdif]);
                break;
        }
        break;
    case 'printcrt':
        $idcrt = $_POST["idcrt"];

        $hoy = date("Y-m-d");

        $datoscrt = mysqli_query($conexion, "SELECT crt.*,tip.diascalculo,tip.ccodofi FROM `ahomcrt` crt INNER JOIN ahomtip tip on tip.ccodtip=substr(crt.codaho,7,2) WHERE `id_crt`=$idcrt");
        $bandera = "Codigo de certificado no existe";
        while ($row = mysqli_fetch_array($datoscrt, MYSQLI_ASSOC)) {
            $codcrt = ($row["ccodcrt"]);
            $idcli = ($row["ccodcli"]);
            $dayscalculo = ($row["diascalculo"]);
            $nit = ($row["num_nit"]);
            $codaho = ($row["codaho"]);
            $montoapr = ($row["montoapr"]);
            $plazo = ($row["plazo"]);
            $interes = ($row["interes"]);
            $fecapr = ($row["fec_apertura"]);
            $fec_ven = ($row["fec_ven"]);
            $ccodofi = ($row["ccodofi"]);
            $norecibo = ($row["recibo"]);
            $bandera = "";
        }

        if ($bandera != "") {
            echo json_encode(['0', $bandera]);
            return;
        }

        $data = mysqli_query($conexion, "SELECT `short_name`,`no_identifica`,`Direccion`,`tel_no1`,`control_interno` FROM `tb_cliente` WHERE `estado`=1 AND `idcod_cliente`=$idcli");
        $bandera = "No existe el cliente relacionado a la cuenta de ahorro";
        while ($dat = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
            $nombre = utf8_encode($dat["short_name"]);
            $dpi = ($dat["no_identifica"]);
            $dire = utf8_encode($dat["Direccion"]);
            $tel = ($dat["tel_no1"]);
            $controlinterno = ($dat["control_interno"]);

            $bandera = "";
        }

        if ($bandera != "") {
            echo json_encode(['0', $bandera]);
            return;
        }

        $intcal = $montoapr * ($interes / 100 / $dayscalculo);
        $intcal = $intcal * $plazo;
        $ipf = $intcal * 0.10;
        $total = $intcal - $ipf;

        //------------------  trae beneficiarios
        $confirma = 0;
        $array[] = [];
        $databen =  mysqli_query($conexion, "SELECT * FROM `ahomben` WHERE `ccodcrt`=$codcrt AND `codaho`=$codaho");
        while ($fila = mysqli_fetch_array($databen, MYSQLI_ASSOC)) {
            $array[] = $fila;
            $confirma = 1;
        }
        $format_monto = new NumeroALetras();
        $montoletra = $format_monto->toMoney($montoapr, 2, 'QUETZALES', 'CENTAVOS');

        //convertir los codigos de parentesco a la descripcion
        $i = 1;
        while ($i < count($array)) {
            $array[$i]['codparent'] = parenteco($array[$i]['codparent']);
            $i++;
        }
        //---------------------------

        echo json_encode([[$codcrt, $nombre, $codaho, $dire, $dpi, $tel, $montoletra, $montoapr, $plazo, $fecapr, $fec_ven, $interes, $intcal, $ipf, $total, $hoy, $controlinterno, $ccodofi, $norecibo], $array, $confirma]);
        mysqli_close($conexion);
        break;

    case 'process':
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $radios = $_POST["radios"];
        $archivo = $_POST["archivo"];
        $codusu = "9999";
        $hoy = date("Y-m-d");
        $hoy2 = date("Y-m-d H:i:s");


        if ($selects[0] == "0" && $radios[0] == "any") {
            echo json_encode(['Seleccionar tipo de cuenta', '0']);
            return;
        }
        if ($inputs[1] < $inputs[0] && $radios[1] == "frango") {
            echo json_encode(['Rango de fechas invalidas', '0']);
            return;
        }

        $ffin = $inputs[1];
        $fini = $inputs[0];

        //ARMANDO LA CONSULTA
        $condicion = "";
        $bandera = 0;
        if ($radios[0] == "any") {
            $condicion = $condicion . " SUBSTR(`ccodaho`,7,2) =" . $selects[0];
            $tipocuenta = $selects[0];
            $bandera = 1;
        } else {
            $tipocuenta = "Todos";
        }

        $and = "";
        if ($bandera == 1) {
            $and = " AND ";
        }

        $wherefecha="";
        if ($radios[1] == "frango") {
            $rango = "" . date("d-m-Y", strtotime($fini)) . "_" . date("d-m-Y", strtotime($ffin));
            $wherefecha = " AND dfecope<='" . $ffin . "'";
        } else {
            $rango = "Todo";
            $ffin = $hoy;
            $date = new DateTime('2000-01-01');
            $fini = $date->format('Y-m-d');
        }

        /*         if ($radios[1] == "frango") {
                $condicion = $condicion . $and . " dfecope>='" . $fini . "' AND dfecope<='" . $ffin . "'";
                $bandera = 2;
            } */
        $where = " WHERE SUBSTR(`ccodaho`,4,3)=" . $_SESSION['agencia'];
        if ($bandera > 0) {
            $where = " WHERE SUBSTR(`ccodaho`,4,3)=" . $_SESSION['agencia'] . " AND ";
        }
        //-------------------------INICIO DE PROCESO
        //query pa los movimientos
        $array[] = [];
        $i = 0;
        $consulta = "SELECT * FROM datascalc " . $where . $wherefecha." ORDER BY ccodaho,dfecope";
        // echo json_encode([$consulta, '0']);
        // return;
        $cuenta = "X";
        $ahommov = mysqli_query($conexion, $consulta);
        $bandera = "Sin Registros";
        while ($row = mysqli_fetch_array($ahommov, MYSQLI_ASSOC)) {
            $ccodaho = utf8_encode($row["ccodaho"]);
            $array[$i] = $row;
            if ($ccodaho != $cuenta && $cuenta != "X") {
                $array[$i - 1]["ult"] = 1; //se agrega en ult del array 1 si es el fin de transacciones de una cuenta
            }
            $array[$i]["ult"] = 0;
            $cuenta = $ccodaho;
            $i++;
            $bandera = "";
        }
        //--------
        if ($bandera != "") {
            echo json_encode([$bandera, '0']);
            return;
        }
        //----------
        $array[count($array) - 1]["ult"] = 1; //se agrega en ult del array 1 si es el fin de transacciones de una cuenta


        $rowant = [];
        $registros[] = [];
        $i = 0;
        $j = 0;
        $saldoant = 0;
        $diasdif = 0;
        if ($radios[1] == "frango" && $array[0]["fechamin"] < $fini) { //verifica si se selecciono rango de fechas
            $fec_ult = $fini; //si es asi se asigna como fecha de ultima transaccion a la fecha ingresada por el usuario
            $array[0]["rango"] = 0;
        } else {
            $fec_ult = $array[0]["fechamin"]; //si no es asi se asigna como fecha de ultima transaccion a la fecha de la primera transaccion de la cuenta
        }

        while ($i < count($array)) {
            $saldoant = 0;
            $tipope = $array[$i]["ctipope"];
            $fecope = $array[$i]["dfecope"];
            $fechamin = $array[$i]["fechamin"];
            $monto = $array[$i]["monto"];
            $tasa = $array[$i]["tasa"];
            $saldo = $array[$i]["saldo"];
            //----------------------
            $diasdif = dias_dif($fec_ult, $fecope);
            $fechaant = $fecope;
            if ($tipope == "R") {
                $saldoant = $saldo + $monto;
            } else {
                $saldoant = $saldo - $monto;
            }
            $interes = $saldoant * ($tasa / 100) / 365 * $diasdif;

            $array[$i]["saldoant"] = $saldoant;
            $array[$i]["dias"] = $diasdif;
            $array[$i]["interescal"] = $interes;
            $array[$i]["isr"] = $interes * 0.10;

            $registros[$j] = $array[$i];
            $j++;
            //--------------            //verificar si la cuenta sigue siendo en el recorrido
            if ($array[$i]["ult"] == 0) {
                if ($array[$i]["dfecope"] < $fini) {
                    $fec_ult = $fini;
                } else {
                    $fec_ult = $fecope; //si es asi, la fecha ultima es la fecha de la transaccion anterior
                }
            } else {
                $registros[$j] = $array[$i];
                $registros[$j]["saldoant"] = $registros[$j - 1]["saldo"];
                $registros[$j]["monto"] = 0;
                $registros[$j]["cnumdoc"] = "corte";
                $registros[$j]["ctipope"] = "D";
                $registros[$j]["montooo"] = 0;
                $registros[$j]["dfecope"] = $ffin;
                $registros[$j]["ult"] = 2; //corte a la fecha

                $saldoant = $registros[$j]["saldoant"];
                $tasa = $registros[$j]["tasa"];

                if ($registros[$j - 1]["dfecope"] < $fini) {
                    $diasdif = dias_dif($fini, $ffin);
                } else {
                    $diasdif = dias_dif($registros[$j - 1]["dfecope"], $ffin);
                }

                $interes = $saldoant * ($tasa / 100) / 365 * $diasdif;
                $registros[$j]["dias"] = $diasdif;
                $registros[$j]["interescal"] = $interes;
                $registros[$j]["isr"] = $interes * 0.10;

                $j++;

                if ($i != count($array) - 1) {
                    $fechaminsig = $array[$i + 1]["fechamin"];
                    if (($radios[1] == "frango") && ($fechaminsig < $fini)) { //verifica si se selecciono rango de fechas
                        $fec_ult = $fini; //si es asi se asigna como fecha de ultima transaccion a la fecha ingresada por el usuario    
                        $array[$i + 1]["rango"] = 0;
                    } else {
                        $fec_ult = $fechaminsig; //si no es asi se asigna como fecha de ultima transaccion a la fecha de la primera transaccion de la cuenta
                    }
                }
                //------ 
            }
            $i++;
        }
        //-------------------FIN DE PROCESO
        //inicio de guardado
        $bandera = "No hay movimientos";
        $paqueton[] = [];
        $i = 0;
        $j = 0;
        $totalint = 0;
        $totalisr = 0;
        while ($i < count($registros)) {
            $fechaope = $registros[$i]["dfecope"];
            $fecmin = $registros[$i]["fechamin"];
            $saldoant = $registros[$i]["saldoant"];
            $minimo = $registros[$i]["mincalc"];
            if ($fechaope >= $fini && $fechaope <= $ffin && $fecmin <= $ffin) {
                $paqueton[$j] = $registros[$i];
                if ($saldoant < $minimo) {
                    //$paqueton[$j]["dias"] = 0;
                    $paqueton[$j]["interescal"] = 0;
                    $paqueton[$j]["isr"] = 0;
                }
                $totalint = $totalint + $paqueton[$j]["interescal"];
                $totalisr = $totalisr + $paqueton[$j]["isr"];
                $j++;
                $bandera = "";
            }
            $i++;
        }
        //--------

        if ($bandera != "") {
            echo json_encode([$bandera, '0']);
            return;
        }
        //----------

        // $identificador=substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 20);
        $query = mysqli_query($conexion, "INSERT INTO `ahointeredetalle`(tipo,rango,partida,acreditado,int_total,isr_total,fecmod,codusu,fechacorte) VALUES ('" . $tipocuenta . "','" . $rango . "',0,0," . $totalint . "," . $totalisr . ",'" . $hoy2 . "','" . $codusu . "','" . $ffin . "')");
        if ($query) {
            $queryult = mysqli_query($conexion, "SELECT MAX(id) AS id FROM ahointeredetalle");
            $identificador = 0;
            while ($row = mysqli_fetch_array($queryult, MYSQLI_ASSOC)) {
                $identificador = utf8_encode($row["id"]);
            }
            $conexion->autocommit(false);
            try {
                foreach ($paqueton as $pac) {
                    $conexion->query("INSERT INTO `ahointere`(`codaho`,`codcli`,`nomcli`,`tipope`,`fecope`,`numdoc`,`tipdoc`,`monto`,`saldo`,`saldoant`,`dias`,`tasa`,`intcal`,`isrcal`,`idcalc`) 
                                    VALUES ('" . $pac['ccodaho'] . "','" . $pac['ccodcli'] . "','" . $pac['short_name'] . "','" . $pac['ctipope'] . "','" . $pac['dfecope'] . "','" . $pac['cnumdoc'] . "','" . $pac['ctipdoc'] . "'," . $pac['monto'] . ",
                                    " . $pac['saldo'] . "," . $pac['saldoant'] . "," . $pac['dias'] . "," . $pac['tasa'] . "," . $pac['interescal'] . "," . $pac['isr'] . "," . $identificador . ")");
                }
                $conexion->commit();
                echo json_encode(['Correcto, proceso finalizado y guardado', '1']);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['Error al Registrar: ' . $e->getMessage(), '0']);
            }
        } else {
            echo json_encode(['Error al ingresar ', '0']);
        }

        mysqli_close($conexion);
        // echo json_encode([[$registros, $paqueton], '0']);

        break;
    case 'acredita':
        $archivo = $_POST["archivo"];
        $hoy = date("Y-m-d H:i:s");
        $id = $archivo[0];
        $usu = $archivo[1];
        $fechacorte = $archivo[2];
        $codofi = $archivo[3];
        $rango = $archivo[4];

        $campo_glosa1 = "";
        $campo_glosa2 = "";

        //------validar si existen todas las parametrizaciones correctas para realizar la acreditacion
        $consulta4 = "SELECT SUBSTR(ai.codaho,7,2) AS grupo, tp.nombre 
                    FROM ahointere ai
                    INNER JOIN ahomcta ac ON ac.ccodaho=ai.codaho 
                    INNER JOIN ahomtip tp ON SUBSTR(ac.ccodaho,7,2)=tp.ccodtip
                    WHERE ai.idcalc=" . $id . " 
                    GROUP BY SUBSTR(ai.codaho,7,2)";
        $data4 = mysqli_query($conexion, $consulta4);

        while ($row = mysqli_fetch_array($data4, MYSQLI_ASSOC)) {
            $val_tipcuenta = $row["grupo"];
            $val_nombre = $row["nombre"];
            //obtener el datos para ingresar en el campo id_ctb_nomenclatura de la tabla ctb_mov
            list($id1, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("ahomparaintere", "id_descript_intere", (tipocuenta($val_tipcuenta, "ahomtip", "id_tipo", $conexion)), (1), $conexion);
            //validar si encontro un tipo de parametrizacion para el interes
            if ($id1 == "X") {
                echo json_encode(['NO PUEDE REALIZAR LA ACREDITACIÓN DEBIDO A QUE NO HA PARAMETRIZADO UNA CUENTA CONTABLE PARA EL TIPO DE CUENTA ' . $val_nombre . ' EN RELACIÓN AL INTERES', '0']);
                return;
            }
            list($id1, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("ahomparaintere", "id_descript_intere", (tipocuenta($val_tipcuenta, "ahomtip", "id_tipo", $conexion)), (2), $conexion);
            //validar si encontro un tipo de parametrizacion para el isr
            if ($id1 == "X") {
                echo json_encode(['NO PUEDE REALIZAR LA ACREDITACIÓN DEBIDO A QUE NO HA PARAMETRIZADO UNA CUENTA CONTABLE PARA EL TIPO DE CUENTA ' . $val_nombre . ' EN RELACIÓN AL ISR', '0']);
                return;
            }
        }
        //------FIN

        //transaccion
        $conexion->autocommit(false);
        try {
            //validacion de acreditacion
            $data3 = mysqli_query($conexion, "SELECT `acreditado` FROM `ahointeredetalle` WHERE id='$id'");
            while ($row = mysqli_fetch_array($data3, MYSQLI_ASSOC)) {
                $acreditado = $row["acreditado"];
            }
            if ($acreditado == "1") {
                echo json_encode(['Este calculo ya ha sido acreditado', '1']);
                return;
            }

            //COMPROBAR SI EL MES CONTABLE ESTA ABIERTO
            $cierre = comprobar_cierre($idusuario, $fechacorte, $conexion);
            if ($cierre[0] == 0) {
                echo json_encode([$cierre[1], '0']);
                return;
            }

            $conexion->query("UPDATE `ahointeredetalle` SET acreditado=1 where id=" . $id);

            $consulta = "SELECT ai.codaho,sum(ai.intcal) as totalint, sum(isrcal) as totalisr,ac.nlibreta,ac.numlinea,ac.correlativo FROM ahointere ai
                INNER JOIN ahomcta ac ON ac.ccodaho=ai.codaho where ai.idcalc=" . $id . " group by ai.codaho";
            $data = mysqli_query($conexion, $consulta);
            // acreditacion por cliente
            while ($row = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                $ccodaho = ($row["codaho"]);
                $interes = ($row["totalint"]);
                $isr = ($row["totalisr"]);
                $libreta = ($row["nlibreta"]);
                $num = lastnumlin($ccodaho, $libreta, "ahommov", "ccodaho", $conexion);
                $correl = lastcorrel($ccodaho, $libreta, "ahommov", "ccodaho", $conexion);



                if ($interes > 0) {
                    //INSERCIONES EN AHOMMOV
                    $conexion->query("INSERT INTO `ahommov`(`ccodaho`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,
                        `lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`auxi`)
                        VALUES ('$ccodaho','$fechacorte','D','INT3112','IN','INTERES', $libreta,'','','',$interes,'N',$num+1,$correl+1,'$hoy','$usu','INTERE" . $id . "')");

                    $conexion->query("INSERT INTO `ahommov`(`ccodaho`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,
                        `lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`auxi`)
                        VALUES ('$ccodaho','$fechacorte','R','ISR3112','IP','INTERES', $libreta,'','','',$isr,'N',$num+2,$correl+2,'$hoy','$usu','INTERE" . $id . "')");
                }
            }
            //CONSULTA
            //consulta para insertar datos en ctb_diario
            $consulta2 = "SELECT SUBSTR(ai.codaho,7,2) AS grupo,sum(ai.intcal) as totalint, sum(isrcal) as totalisr,ac.nlibreta,ac.numlinea,ac.correlativo, tp.nombre FROM ahointere ai
                INNER JOIN ahomcta ac ON ac.ccodaho=ai.codaho 
                INNER JOIN ahomtip tp ON SUBSTR(ac.ccodaho,7,2)=tp.ccodtip
                WHERE ai.idcalc=" . $id . " 
                GROUP BY SUBSTR(ai.codaho,7,2)";
            $data2 = mysqli_query($conexion, $consulta2);
            // insercion en la tabla de dario
            while ($row = mysqli_fetch_array($data2, MYSQLI_ASSOC)) {
                $nombre = utf8_encode($row["nombre"]);
                $interes = ($row["totalint"]);
                $isr = ($row["totalisr"]);
                $grupo = ($row["grupo"]);

                if ($interes > 0) {
                    //insertar en ctb_diario
                    //glosa de acreditacion y retencion
                    $campo_glosa1 .= "ACREDITACIÓN DE INTERESES A CUENTA DE ";
                    $campo_glosa1 .= strtoupper($nombre);

                    $campo_glosa2 .= "RETENCIÓN DE ISR A CUENTA DE ";
                    $campo_glosa2 .= strtoupper($nombre);
                    //validar si es con rango de fecha o no
                    if ($rango != "Todo") {
                        $campo_glosa1 .= " COMPRENDIDO DEL ";
                        $campo_glosa1 .= substr($rango, 0, 10);
                        $campo_glosa1 .= " AL ";
                        $campo_glosa1 .= substr($rango, 11, 20);

                        $campo_glosa2 .= " COMPRENDIDO DEL ";
                        $campo_glosa2 .= substr($rango, 0, 10);
                        $campo_glosa2 .= " AL ";
                        $campo_glosa2 .= substr($rango, 11, 20);
                    }
                    //INSERCIONES EN CTB_DIARIO - INTERES ACREDITADO
                    //llamar al metodo numcom
                    $camp_numcom = getnumcom($usu, $conexion);
                    //insertar glosa de acreditacion
                    $aux = "AHO-" . $grupo;
                    $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$camp_numcom',2,1,'INT', '$campo_glosa1','$fechacorte', '$fechacorte','$aux','$usu','$hoy',1)");

                    //INSERCION EN CTB_MOV PARA EL INTERES ACREDITADO
                    $id_ctb_diario = get_id_insertado($conexion); //obtener el ultimo id insertado
                    list($id1, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("ahomparaintere", "id_descript_intere", (tipocuenta($grupo, "ahomtip", "id_tipo", $conexion)), (1), $conexion);
                    $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta1, '$interes',0)");
                    $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta2, 0,'$interes')");

                    //INSERCIONES EN CTB_DIARIO - ISR ACREDITADO
                    //llamar al metodo numcom
                    $camp_numcom = getnumcom($usu, $conexion);
                    //insertar glosa de retencion de isr
                    $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$camp_numcom',2,1,'ISR', '$campo_glosa2','$fechacorte', '$fechacorte','$aux','$usu','$hoy',1)");

                    //INSERCION EN CTB_MOV PARA EL ISR ACREDITADO
                    $id_ctb_diario = get_id_insertado($conexion); //obtener el ultimo id insertado
                    list($id1, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("ahomparaintere", "id_descript_intere", (tipocuenta($grupo, "ahomtip", "id_tipo", $conexion)), (2), $conexion);
                    $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta1, '$isr',0)");
                    $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta2, 0,'$isr')");

                    $campo_glosa1 = "";
                    $campo_glosa2 = "";
                }
            }

            $conexion->commit();
            echo json_encode(['Datos ingresados correctamente', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        //fin transaccion
        mysqli_close($conexion);
        break;
    case 'partidaprov':
        $archivo = $_POST["archivo"];
        $hoy = date("Y-m-d H:i:s");
        $id = $archivo[0];
        $usu = $archivo[1];
        $fechacorte = $archivo[2];
        $rango = $archivo[3];

        $campo_glosa = "";

        //------validar si existen todas las parametrizaciones correctas para realizar la acreditacion
        $consulta4 = "SELECT SUBSTR(ai.codaho,7,2) AS grupo, tp.nombre 
                    FROM ahointere ai
                    INNER JOIN ahomcta ac ON ac.ccodaho=ai.codaho 
                    INNER JOIN ahomtip tp ON SUBSTR(ac.ccodaho,7,2)=tp.ccodtip
                    WHERE ai.idcalc=" . $id . " 
                    GROUP BY SUBSTR(ai.codaho,7,2)";
        $data4 = mysqli_query($conexion, $consulta4);

        while ($row = mysqli_fetch_array($data4, MYSQLI_ASSOC)) {
            $val_tipcuenta = $row["grupo"];
            $val_nombre = $row["nombre"];
            //obtener el datos para ingresar en el campo id_ctb_nomenclatura de la tabla ctb_mov
            list($id1, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("ahomparaintere", "id_descript_intere", (tipocuenta($val_tipcuenta, "ahomtip", "id_tipo", $conexion)), (3), $conexion);
            //validar si encontro un tipo de parametrizacion para el interes
            if ($id1 == "X") {
                echo json_encode(['NO PUEDE REALIZAR LA PROVISIÓN DEBIDO A QUE NO HA PARAMETRIZADO UNA CUENTA CONTABLE PARA EL TIPO DE CUENTA ' . $val_nombre . ' EN RELACIÓN AL INTERES', '0']);
                return;
            }
        }
        //------FIN
        //transaccion
        $conexion->autocommit(false);
        try {
            //validacion de acreditacion
            $data3 = mysqli_query($conexion, "SELECT `partida` FROM `ahointeredetalle` WHERE id='$id'");
            while ($row = mysqli_fetch_array($data3, MYSQLI_ASSOC)) {
                $partida = $row["partida"];
            }
            if ($partida == "1") {
                echo json_encode(['Este campo ya ha sido provisionado', '1']);
                return;
            }

            //COMPROBAR SI EL MES CONTABLE ESTA ABIERTO
            $cierre = comprobar_cierre($idusuario, $fechacorte, $conexion);
            if ($cierre[0] == 0) {
                echo json_encode([$cierre[1], '0']);
                return;
            }

            $conexion->query("UPDATE `ahointeredetalle` SET partida=1 where id=" . $id);

            $consulta = "SELECT SUBSTR(ai.codaho,7,2) AS grupo,sum(ai.intcal) as totalint, sum(isrcal) as totalisr,ac.nlibreta,ac.numlinea,ac.correlativo, tp.nombre FROM ahointere ai
                INNER JOIN ahomcta ac ON ac.ccodaho=ai.codaho 
                INNER JOIN ahomtip tp ON SUBSTR(ac.ccodaho,7,2)=tp.ccodtip
                WHERE ai.idcalc=" . $id . " 
                GROUP BY SUBSTR(ai.codaho,7,2)";
            $data = mysqli_query($conexion, $consulta);
            //insercion en la tabla de dario
            while ($row = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                $nombre = utf8_encode($row["nombre"]);
                $interes = ($row["totalint"]);
                $isr = ($row["totalisr"]);
                $grupo = ($row["grupo"]);

                if ($interes > 0) {
                    //insertar en ctb_diario
                    //glosa de provision
                    $campo_glosa .= "PROVISION DE INTERESES DE CUENTAS DE ";
                    $campo_glosa .= strtoupper($nombre);

                    //validar si es con rango de fecha o no
                    if ($rango != "Todo") {
                        $campo_glosa .= " COMPRENDIDO DEL ";
                        $campo_glosa .= substr($rango, 0, 10);
                        $campo_glosa .= " AL ";
                        $campo_glosa .= substr($rango, 11, 20);
                    }
                    //INSERCIONES EN CTB_DIARIO - INTERES ACREDITADO
                    //llamar al metodo numcom
                    $camp_numcom = getnumcom($usu, $conexion);
                    //insertar glosa de acreditacion
                    $aux = "AHO-" . $grupo;
                    $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$camp_numcom',2,1,'PROV', '$campo_glosa','$fechacorte', '$fechacorte','$aux','$usu','$hoy',1)");

                    //INSERCION EN CTB_MOV PARA EL INTERES PROVISIONADO
                    $id_ctb_diario = get_id_insertado($conexion); //obtener el ultimo id insertado
                    list($id1, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("ahomparaintere", "id_descript_intere", (tipocuenta($grupo, "ahomtip", "id_tipo", $conexion)), (3), $conexion);
                    $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta1, '$interes',0)");
                    $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta2, 0,'$interes')");

                    $campo_glosa = "";
                }
            }

            $conexion->commit();
            echo json_encode(['Datos ingresados correctamente', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        //fin transaccion
        mysqli_close($conexion);

        break;
    case 'liquidcrt':
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $archivo = $_POST["archivo"];
        $interescal = $inputs[0];
        $ipfcalc = $inputs[1];
        $penaliza = $inputs[2];
        $recibo = $inputs[3];
        $codcrt = $archivo[0];
        $codaho = $archivo[1];
        $codcli = $archivo[2];
        $montoapr = $archivo[3];
        $codusu = $archivo[4];
        $codofi = $archivo[5];

        if ($recibo == "") {
            echo json_encode(['Ingrese un numero de recibo', '0']);
            return;
        }
        $fechaliquidacion = $inputs[4];
        $accion =  $selects[0];
        // ([`moncal`,`intcal`,`penaliza`,`norecibo`,`fecacredita`],[`accion`],[],`liquidcrt`,`0`,[' $codcrt; ',' $codaho; ', $idcli; ',' $montoapr; ',' $codusu; ',' $codofi; ])

        $hoy = date("Y-m-d");
        $hoy2 = date("Y-m-d H:i:s");

        $consulta = "SELECT ca.nlibreta,cli.short_name FROM ahomcta ca INNER JOIN tb_cliente cli ON cli.idcod_cliente=ca.ccodcli where ca.ccodaho='" . $codaho . "'";
        $data = mysqli_query($conexion, $consulta);
        $libreta = 0;
        $nombrecli = "";
        $bandera = "No existe cuenta de ahorro";
        while ($row = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
            $libreta = ($row["nlibreta"]);
            $nombrecli = utf8_encode($row["short_name"]);
            $bandera = "";
        }

        $num = lastnumlin($codaho, $libreta, "ahommov", "ccodaho", $conexion);
        $correl = lastcorrel($codaho, $libreta, "ahommov", "ccodaho", $conexion);
        $format_monto = new NumeroALetras();
        $texto_monto = $format_monto->toMoney($montoapr + $interescal - $ipfcalc, 2, 'QUETZALES', 'CENTAVOS');

        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++ CUENTAS CONTABLES +++++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $val_tipcuenta = substr($codaho, 6, 2);
        list($id1_1, $idcuenta1_1, $idcuenta2_1) = get_ctb_nomenclatura("ahomparaintere", "id_descript_intere", (tipocuenta($val_tipcuenta, "ahomtip", "id_tipo", $conexion)), (1), $conexion);
        if ($id1_1 == "X") {
            echo json_encode(['NO PUEDE REALIZAR LA ACREDITACIÓN DEBIDO A QUE NO HA PARAMETRIZADO UNA CUENTA CONTABLE PARA EL TIPO DE CUENTA EN RELACIÓN AL INTERES', '0']);
            return;
        }
        list($id1_2, $idcuenta1_2, $idcuenta2_2) = get_ctb_nomenclatura("ahomparaintere", "id_descript_intere", (tipocuenta($val_tipcuenta, "ahomtip", "id_tipo", $conexion)), (2), $conexion);
        if ($id1_2 == "X") {
            echo json_encode(['NO PUEDE REALIZAR LA ACREDITACIÓN DEBIDO A QUE NO HA PARAMETRIZADO UNA CUENTA CONTABLE PARA EL TIPO DE CUENTA EN RELACIÓN AL ISR', '0']);
            return;
        }

        $tipopoliza = 2; //por defecto es de tipo ahorros
        //transaccion
        $conexion->autocommit(false);
        try {
            //INSERCION EN TABLAS DE CONTA PRIMERA PARTIDA
            $camp_numcom = getnumcom($idusuario, $conexion);
            //INSERT ctbdiario
            $camp_glosa = "LIQUIDACION DE AHORRO A PLAZO FIJO DE " . $nombrecli . " CON CERTIFICADO NO. " . $codcrt;
            $res = $conexion->prepare("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) 
            VALUES (?,?,1,?, ?,?, ?,?,?,?,1)");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux, '0']);
                $conexion->rollback();
                return;
            }
            $res->bind_param('sisssssis', $camp_numcom, $tipopoliza, $recibo, $camp_glosa, $fechaliquidacion, $fechaliquidacion, $codaho, $idusuario, $hoy);
            $res->execute();
            $id_ctb_diario = get_id_insertado($conexion);

            //1 REGISTRO CTBMOV
            $res = $conexion->prepare("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES (?,' ',1,?,?,0)");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux, '0']);
                $conexion->rollback();
                return;
            }
            $res->bind_param('iid', $id_ctb_diario, $idcuenta1_1, $interescal);
            $res->execute();

            //2 REGISTRO CTBMOV
            $res = $conexion->prepare("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES (?,' ',1,?,0,?)");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux, '0']);
                $conexion->rollback();
                return;
            }
            $res->bind_param('iid', $id_ctb_diario, $idcuenta2_1, $interescal);
            $res->execute();

            //INSERCION EN TABLAS DE CONTA :RETENCION ISR
            //$camp_numcom = getnumcom($idusuario, $conexion);
            //INSERT ctbdiario
            //$camp_glosa = "RETENCION DE ISR ";
            // $res = $conexion->prepare("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) 
            // VALUES (?,?,1,?, ?,?, ?,?,?,?,1)");
            // $aux = mysqli_error($conexion);
            // if ($aux) {
            //     echo json_encode([$aux, '0']);
            //     $conexion->rollback();
            //     return;
            // }
            // $res->bind_param('sisssssis', $camp_numcom, $tipopoliza, $recibo, $camp_glosa, $fechaliquidacion, $fechaliquidacion, $codaho, $idusuario, $hoy);
            // $res->execute();
            // $id_ctb_diario = get_id_insertado($conexion);

            //1 REGISTRO CTBMOV
            $res = $conexion->prepare("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES (?,' ',1,?,?,0)");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux, '0']);
                $conexion->rollback();
                return;
            }
            $res->bind_param('iid', $id_ctb_diario, $idcuenta1_2, $ipfcalc);
            $res->execute();

            //2 REGISTRO CTBMOV
            $res = $conexion->prepare("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES (?,' ',1,?,0,?)");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux, '0']);
                $conexion->rollback();
                return;
            }
            $res->bind_param('iid', $id_ctb_diario, $idcuenta2_2, $ipfcalc);
            $res->execute();


            //INSERCION EN TABLAS DE AHORROS
            $conexion->query("INSERT INTO `ahommov`(`ccodaho`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,
                        `lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`auxi`)
                        VALUES ('$codaho','$fechaliquidacion','D','$codcrt','IN','INTERES', $libreta,'','','',$interescal,'N',$num+1,$correl+1,'$hoy2','$codusu','INTCRT" . $codcrt . "')");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode(['ERROR: ' . $aux, '0']);
                $conexion->rollback();
                return;
            }
            $conexion->query("INSERT INTO `ahommov`(`ccodaho`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,
                        `lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`auxi`)
                        VALUES ('$codaho','$fechaliquidacion','R','$codcrt','IP','IPF', $libreta,'','','',$ipfcalc,'N',$num+2,$correl+2,'$hoy2','$codusu','INTCRT" . $codcrt . "')");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode(['ERROR2: ' . $aux, '0']);
                $conexion->rollback();
                return;
            }

            // $nums = intval($num) + 2;
            // $correls = intval($correl) + 2;
            // if ($penaliza > 0) {
            //     $nums = $nums + 1;
            //     $correls = $correls + 1;
            //     $conexion->query("INSERT INTO `ahommov`(`ccodaho`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,
            //             `lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`auxi`)
            //             VALUES ('$codaho','$hoy','R','$codcrt','E','COMISION', $libreta,'','','',$penaliza,'N',$nums,$correls,'$hoy2','$codusu','INTCRT" . $codcrt . "')");
            //     $aux = mysqli_error($conexion);
            //     if ($aux) {
            //         echo json_encode(['ERROR3: ' . $aux, '0']);
            //         $conexion->rollback();
            //         return;
            //     }
            // }
            if ($accion == 1) {
                $conc="UPDATE `ahomcrt` SET `liquidado`='S',`intcal`='" . round($interescal, 2)  . "',`recibo_liquid`='" . $recibo . "',`fec_liq`='$fechaliquidacion' WHERE `ccodcrt`='$codcrt'";
                $conexion->query($conc);
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['ERROR4: ' . $aux, '0']);
                    $conexion->rollback();
                    return;
                }
            }
            $conexion->commit();
            // $conexion->rollback();
            echo json_encode(['Datos ingresados correctamente', '1', $codcrt, $fechaliquidacion, $codaho, $nombrecli, $montoapr, $interescal, $ipfcalc, $texto_monto, $recibo]);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage() . $e->getLine(), '0']);
        }
        //fin transaccion
        mysqli_close($conexion);
        break;
    case 'liquidcrtonly':
        $archivo = $_POST["archivo"];
        $idcrt = $archivo[0];

        $hoy = date("Y-m-d");
        $hoy2 = date("Y-m-d H:i:s");

        $conexion->autocommit(false);
        try {
            $conexion->query("UPDATE `ahomcrt` SET liquidado='S',fec_liq='$hoy' where id_crt=" . $idcrt);
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode(['ERROR4: ' . $aux, '0']);
                $conexion->rollback();
                return;
            }
            $conexion->commit();
            echo json_encode(['Datos ingresados correctamente', '1', $codcrt, $fechaliquidacion, $codaho, $nombrecli, $montoapr, $interescal, $ipfcalc, $texto_monto, $recibo]);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        //fin transaccion
        mysqli_close($conexion);
        break;
    case 'printliquidcrt':
        $archivo = $_POST["archivo"];
        $idcrt = $archivo[0];
        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++  DATOS DE LA CUENTA DE AHORROS ++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        try {
            $database->openConnection();

            $result = $database->getAllResults("SELECT cli.short_name,cli.no_identifica dpi,crt.* FROM ahomcrt crt 
            INNER JOIN tb_cliente cli ON cli.idcod_cliente=crt.ccodcli WHERE id_crt=?", [$idcrt]);

            foreach ($result as $dat) {
                $codcrt = $dat['ccodcrt'];
                $fechaliquidacion = $dat['fec_liq'];
                $codaho = $dat['codaho'];
                $nombrecli = $dat['short_name'];
                $dpi = $dat['dpi'];
                $montoapr = $dat['montoapr'];
                $interescal = $dat['intcal'];
                $ipfcalc = $interescal * 0.1;
                $recibo = $dat['recibo_liquid'];
            }

            $mensaje = "Consulta procesada correctamente";
            $status = 1;
        } catch (Exception $e) {
            $codigoError = logerrores($e->getMessage(), __FILE__, __LINE__, $e->getFile(), $e->getLine());
            $mensaje = "Error: Intente nuevamente, o reporte este codigo de error($codigoError)";
            $status = 0;
        } finally {
            $database->closeConnection();
        }

        if ($status == 1) {
            $format_monto = new NumeroALetras();
            $liquido = round($montoapr + $interescal - $ipfcalc, 2);
            $texto_monto = $format_monto->toMoney($liquido, 2, 'QUETZALES', 'CENTAVOS');
            echo json_encode(['Datos ingresados correctamente', '1', $codcrt, $fechaliquidacion, $codaho, $nombrecli, $montoapr, $interescal, $ipfcalc, $texto_monto, $recibo, $dpi]);
        } else {
            echo json_encode([$mensaje, '0']);
        }
        break;
    case 'reporte_listado_dia':
        $inputs = $_POST["inputs"];
        $archivos = $_POST["archivo"];
        $radioss = $_POST["radios"];
        $radiosn = $_POST["radiosn"];
        $selects = $_POST["selects"];
        $selectsn = $_POST["selects"];
        $tipo_doc = $_POST["id"];

        //si hay un error en el ingreso de datos
        if ($radioss[1] == "2") {
            if ($selects[0] == "0") {
                echo json_encode(["Debe seleccionar un tipo de cuenta", '0']);
                return;
            }
        }

        if ($radioss[1] == "1") {
            if ($selects[0] != "0") {
                echo json_encode(["Error en su solicitud", '0']);
                return;
            }
        }

        $fecha_actual = strtotime(date("Y-m-d"));
        $fecha_1 = strtotime($inputs[0]);
        $fecha_2 = strtotime($inputs[1]);

        if ($radioss[2] == "2") {
            //validacion de fechas
            if ($fecha_2 > $fecha_actual) {
                echo json_encode(["La fecha de hasta no puede ser mayor a la fecha de hoy", '0']);
                return;
            }
            if ($fecha_1 > $fecha_2) {
                echo json_encode(["La fecha inicial no puede ser mayor a la fecha final", '0']);
                return;
            }
        }

        if ($radioss[2] == "1") {
            //validacion de fechas
            $fecha_actual = strtotime(date("Y-m-d"));
            if ($fecha_2 != $fecha_actual && $fecha_1 != $fecha_actual) {
                echo json_encode(["Error en su solicitud", '0']);
                return;
            }
        }

        $formato = "pdf";
        if ($tipo_doc == "excel") {
            $formato = "xlsx";
        }

        //unicamente para encontrar los valores
        echo json_encode(["reportes_ahorros", "listado_del_dia", $tipo_doc, $formato, date("d-m-Y"), $inputs[0], $inputs[1], $radioss[0], $radioss[1], $radioss[2], $selects[0], $archivos[0], $archivos[1]]);

        break;
    case 'reporte_estado_cuenta_aho':
        $inputs = $_POST["inputs"];
        $archivos = $_POST["archivo"];
        $radioss = $_POST["radios"];
        $radiosn = $_POST["radiosn"];
        $tipo_doc = $_POST["id"];

        //validar si ingreso un cuenta de aportacion
        if ($inputs[0] == "" && $inputs[1] == "") {
            echo json_encode(["Debe cargar una cuenta de ahorro", '0']);
            return;
        }

        //validar si la cuenta de ahorro existe
        $datoscli = mysqli_query($conexion, "SELECT * FROM `ahomcta` WHERE `ccodaho`=$inputs[0]");
        $bandera = true;
        while ($da = mysqli_fetch_array($datoscli, MYSQLI_ASSOC)) {
            $bandera = false;
        }
        if ($bandera) {
            echo json_encode(["Debe cargar una cuenta de ahorro válida", '0']);
            return;
        }

        //validaciones en cuanto a fechas
        $fecha_actual = strtotime(date("Y-m-d"));
        $fecha_1 = strtotime($inputs[2]);
        $fecha_2 = strtotime($inputs[3]);

        if ($radioss[0] == "2") {
            //validacion de fechas
            if ($fecha_2 > $fecha_actual) {
                echo json_encode(["La fecha de hasta no puede ser mayor a la fecha de hoy", '0']);
                return;
            }
            if ($fecha_1 > $fecha_2) {
                echo json_encode(["La fecha inicial no puede ser mayor a la fecha final", '0']);
                return;
            }
        }

        if ($radioss[0] == "1") {
            //validacion de fechas
            $fecha_actual = strtotime(date("Y-m-d"));
            if ($fecha_2 != $fecha_actual && $fecha_1 != $fecha_actual) {
                echo json_encode(["Error en su solicitud", '0']);
                return;
            }
        }
        $formato = "pdf";
        if ($tipo_doc == "excel") {
            $formato = "xlsx";
        }

        //unicamente para encontrar los valores
        echo json_encode(["reportes_ahorros", "estado_cuenta_aho", $tipo_doc, $formato, date("d-m-Y"), $inputs[0], $inputs[2], $inputs[3], $radioss[0], $archivos[0], $archivos[1]]);
        break;
    case 'saldo_de_cuentas':
        $inputs = $_POST["inputs"];
        $archivos = $_POST["archivo"];
        $radioss = $_POST["radios"];
        $selects = $_POST["selects"];
        $tipo_doc = $_POST["id"];

        $hoy = date("Y-m-d");

        //validar si selecciono una cuenta
        // if ($selects[0] == "0") {
        //     echo json_encode(["Debe seleccionar un tipo de cuenta", '0']);
        //     return;
        // }
        //validar si selecciono una fecha valida
        if ($inputs[0] > $hoy) {
            echo json_encode(["Debe ingresar una fecha no mayor al de hoy", '0']);
            return;
        }

        $formato = "pdf";
        if ($tipo_doc == "excel") {
            $formato = "xlsx";
        }

        //unicamente para encontrar los valores
        echo json_encode(["reportes_ahorros", "saldo_de_cuentas", $tipo_doc, $formato, date("d-m-Y"), $inputs[0], $radioss[0], $selects[0], $archivos[0], $archivos[1]]);

        break;
    case 'reporte_cuentas_act_inact_aho':
        $archivos = $_POST["archivo"];
        $radioss = $_POST["radios"];
        $radiosn = $_POST["radiosn"];
        $selects = $_POST["selects"];
        $selectsn = $_POST["selects"];
        $tipo_doc = $_POST["id"];

        //si hay un error en el ingreso de datos
        if ($radioss[1] == "2") {
            if ($selects[0] == "0") {
                echo json_encode(["Debe seleccionar un tipo de cuenta", '0']);
                return;
            }
        }

        if ($radioss[1] == "1") {
            if ($selects[0] != "0") {
                echo json_encode(["Error en su solicitud", '0']);
                return;
            }
        }

        $formato = "pdf";
        if ($tipo_doc == "excel") {
            $formato = "xlsx";
        }

        //unicamente para encontrar los valores
        echo json_encode(["reportes_ahorros", "listado_cuentas_aho", $tipo_doc, $formato, date("d-m-Y"), $radioss[0], $radioss[1], $selects[0], $archivos[0], $archivos[1]]);
        break;
    case 'obtener_total_ben': {
            $id = $_POST["l_codaho2"];
            $consulta2 = mysqli_query($conexion, "SELECT * FROM `ahomben` WHERE `codaho`='$id'");
            //se cargan los datos de las beneficiarios a un array
            $total = 0;
            $i = 0;
            while ($fila = mysqli_fetch_array($consulta2, MYSQLI_ASSOC)) {
                $benporcent = ($fila["porcentaje"]);
                $total = $total + $benporcent;
                $i++;
            }
            echo json_encode($total);
        }
        break;
    case 'lista_beneficiarios': {
            $id = $_POST['l_codaho'];
            $consulta2 = mysqli_query($conexion, "SELECT * FROM `ahomben` WHERE `codaho`='$id'");
            //se cargan los datos de las beneficiarios a un array
            $array_beneficiarios = array();
            $array_parenteco[] = [];
            $total = 0;
            $i = 0;
            while ($fila = mysqli_fetch_array($consulta2, MYSQLI_ASSOC)) {
                $array_beneficiarios[] = array(
                    "0" => utf8_encode($fila["dpi"]),
                    "1" => utf8_encode($fila["nombre"]),
                    "2" => utf8_encode($fila["fecnac"]),
                    "3" => parenteco(utf8_encode($fila["codparent"])),
                    "4" => utf8_encode($fila["porcentaje"]),
                    "5" => '<button type="button" class="btn btn-warning me-1" title="Editar Beneficiario" onclick="editben(' . utf8_encode($fila["id_ben"]) . ',`' . utf8_encode($fila["nombre"]) . '`,`' . utf8_encode($fila["dpi"]) . '`,`' . utf8_encode($fila["direccion"]) . '`,' . utf8_encode($fila["codparent"]) . ',`' . utf8_encode($fila["fecnac"]) . '`,' . utf8_encode($fila["porcentaje"]) . ',`' . utf8_encode($fila["telefono"]) . '`)"><i class="fa-solid fa-pen"></i></button>
                        <button type="button" class="btn btn-danger" title="Eliminar Beneficiario" onclick="eliminar(' . utf8_encode($fila["id_ben"]) . ',`crud_ahorro`,`' . $id . '`,`delete_apr_ben`)"><i class="fa-solid fa-trash-can"></i>
                        </button>'
                );
                $i++;
            }
            $results = array(
                "sEcho" => 1, //info para datatables
                "iTotalRecords" => count($array_beneficiarios), //enviamos el total de registros al datatable
                "iTotalDisplayRecords" => count($array_beneficiarios), //enviamos el total de registros a visualizar
                "aaData" => $array_beneficiarios
            );
            mysqli_close($conexion);
            echo json_encode($results);
        }
        break;
    case 'delete_apr_ben': {
            $idaprben = $_POST["ideliminar"];
            $eliminar = "DELETE FROM ahomben WHERE id_ben =" . $idaprben;
            if (mysqli_query($conexion, $eliminar)) {
                echo json_encode(['Eliminacion correcta ', '1']);
            } else {
                echo json_encode(['Error al eliminar ', '0']);
            }
            mysqli_close($conexion);
        }
        break;
    case 'create_aho_ben': {
            $hoy = date("Y-m-d");
            $inputs = $_POST["inputs"];
            $selects = $_POST["selects"];
            $archivos = $_POST["archivo"];
            $consulta2 = mysqli_query($conexion, "SELECT * FROM `ahomben` WHERE `codaho`='$archivos[0]'");
            //se cargan los datos de las beneficiarios a un array
            $total_aux = 0;
            while ($fila = mysqli_fetch_array($consulta2, MYSQLI_ASSOC)) {
                $benporcent = utf8_encode($fila["porcentaje"]);
                $total_aux = $total_aux + $benporcent;
            }

            if ($archivos[1] == "") {
                $validacion = validarcampo($inputs, "");
                if ($validacion == "1") {
                    //validando que el primer beneficiario tiene que tener el 100%
                    $total = $total_aux + $inputs[5];
                    if (($total_aux == 0) && ($total != 100)) {
                        echo json_encode(['Al ser el primer beneficiario tiene que digitar que sea el 100%', '0']);
                    } else {
                        if ($total > 100) {
                            echo json_encode(['El porcentaje ingresado del nuevo beneficiario sumados con los anteriores no puede ser mayor a 100', '0']);
                        } else {
                            if ($inputs[5] <= 0) {
                                echo json_encode(['Verifique que el porcentaje ingresado del nuevo beneficiario no puede ser menor o igual a 0', '0']);
                            } else {
                                $validparent = validarcampo($selects, "0");
                                if ($validparent == "1") {
                                    $conexion->autocommit(false);
                                    try {
                                        $conexion->query("INSERT INTO `ahomben`(`codaho`,`nombre`,`dpi`,`direccion`,`codparent`,`fecnac`,`porcentaje`,`telefono`) VALUES ('$archivos[0]','$inputs[0]','$inputs[1]','$inputs[2]','$selects[0]','$inputs[4]','$inputs[5]','$inputs[3]')");
                                        $conexion->commit();
                                        echo json_encode(['Correcto,  Beneficiario guardado ', '1']);
                                    } catch (Exception $e) {
                                        $conexion->rollback();
                                        echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
                                    }
                                } else {
                                    echo json_encode(['Seleccione parentesco', '0']);
                                }
                            }
                        }
                    }
                } else {
                    echo json_encode([$validacion, '0']);
                }
            } else {
                echo json_encode(['Seleccione primeramente una cuenta de ahorro', '0']);
            }
            mysqli_close($conexion);
        }
        break;
    case 'update_aho_ben': {
            $hoy = date("Y-m-d");
            $inputs = $_POST["inputs"];
            $selects = $_POST["selects"]; //selects datos// 
            $archivos = $_POST["archivo"];

            $consulta2 = mysqli_query($conexion, "SELECT * FROM `ahomben` WHERE `codaho`='$archivos[0]'");
            //se cargan los datos de las beneficiarios a un array
            $total_aux = 0;
            while ($fila = mysqli_fetch_array($consulta2, MYSQLI_ASSOC)) {
                $benporcent = utf8_encode($fila["porcentaje"]);
                $total_aux = $total_aux + $benporcent;
            }

            $validacion = validarcampo($inputs, "");
            if ($validacion == "1") {
                $total = $total_aux - $inputs[6] + $inputs[5];

                if ($total > 100) {
                    echo json_encode(['No se puede actualizar debido a que con el nuevo porcentaje supera el 100%, debe acomodar el o los porcentajes anteriores', '0']);
                } else if ($inputs[5] <= 0) {
                    echo json_encode(['El porcentaje nuevo no puede ser menor o igual a 0', '0']);
                } else {
                    $validparent = validarcampo($selects, "0");
                    if ($validparent == "1") {
                        $conexion->autocommit(false);
                        try {
                            $conexion->query("UPDATE `ahomben` SET `nombre` = '$inputs[0]',`dpi` = '$inputs[1]',`direccion` = '$inputs[2]',`codparent` = $selects[0],`fecnac` = '$inputs[4]',`porcentaje` = $inputs[5],`telefono` = '$inputs[3]' WHERE `id_ben` = $inputs[7]");
                            $conexion->commit();
                            echo json_encode(['Correcto,  Beneficiario actualizado', '1']);
                        } catch (Exception $e) {
                            $conexion->rollback();
                            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
                        }
                    } else {
                        echo json_encode(['Seleccione parentesco', '0']);
                    }
                }
            } else {
                echo json_encode([$validacion, '0']);
            }
            mysqli_close($conexion);
        }
        break;
    case "create_aho_cuentas_contables": {
            $hoy = date("Y-m-d H:i:s");
            $inputs = $_POST["inputs"];
            $selects = $_POST["selects"]; //selects datos// 
            $archivos = $_POST["archivo"];

            //validaciones
            //validacion de select tipo de cuenta
            if ($selects[0] == "0") {
                echo json_encode(['Debe seleccionar un tipo de cuenta', '0']);
                return;
            }
            if ($selects[1] == "0") {
                echo json_encode(['Debe seleccionar un tipo de documento', '0']);
                return;
            }
            //validacion de select de tipo de documento
            //validar input cuenta 1
            if ($inputs[0] == "") {
                echo json_encode(['Debe seleccionar una cuenta 1', '0']);
                return;
            }
            //validar input cuenta 2
            if ($inputs[1] == "") {
                echo json_encode(['Debe seleccionar una cuenta 2', '0']);
                return;
            }

            //Validar si ya existe una insercion con los mismos
            list($id1, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("ahomctb", "id_tipo_doc", $selects[0], $selects[1], $conexion);
            //validar si encontro un tipo de parametrizacion para el interes
            if ($id1 != "X") {
                echo json_encode(['No puede agregar esta parametrizacion porque ya existe', '0']);
                return;
            }

            //se hara la insercion
            $conexion->autocommit(false);
            try {
                $conexion->query("INSERT INTO ahomctb (id_tipo_cuenta,id_tipo_doc,id_cuenta1,id_cuenta2,dfecmod,codusu)
                        VALUES ($selects[0],$selects[1],$inputs[0],$inputs[1],'$hoy',$archivos[0])");

                $conexion->commit();
                echo json_encode(['Datos ingresados correctamente', '1']);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
            }
            mysqli_close($conexion);
        }
        break;
    case "update_aho_cuentas_contables": {

            $hoy = date("Y-m-d H:i:s");
            $inputs = $_POST["inputs"];
            //validar input cuenta 1
            if ($inputs[0] == "0") {
                echo json_encode(['Debe seleccionar una cuenta contable', '0']);
                return;
            }
            //se hara la actualizacion
            $conexion->autocommit(false);
            try {
                $conexion->query("UPDATE ahomtip
                    SET id_cuenta_contable = $inputs[0] WHERE id_tipo=$inputs[1]");

                $conexion->commit();
                echo json_encode(['Datos actualizados correctamente', '1']);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
            }
            mysqli_close($conexion);
        }
        break;
    case "update_aho_cuentas_contablesanterior": {
            $hoy = date("Y-m-d H:i:s");
            $inputs = $_POST["inputs"];
            $selects = $_POST["selects"]; //selects datos// 
            $archivos = $_POST["archivo"];

            // echo json_encode([$inputs[2]."-".$inputs[3], '0']);
            //validaciones
            //validacion de select tipo de cuenta
            if ($selects[0] == "0") {
                echo json_encode(['Debe seleccionar un tipo de cuenta', '0']);
                return;
            }
            if ($selects[1] == "0") {
                echo json_encode(['Debe seleccionar un tipo de documento', '0']);
                return;
            }
            //validacion de select de tipo de documento
            //validar input cuenta 1
            if ($inputs[0] != "" && $inputs[2] == "") {
                echo json_encode(['Debe seleccionar una cuenta 1', '0']);
                return;
            }
            //validar input cuenta 2
            if ($inputs[1] != "" && $inputs[3] == "") {
                echo json_encode(['Debe seleccionar una cuenta 2', '0']);
                return;
            }

            //Validar si ya existe una insercion con los mismos
            $id1 = get_ctb_nomenclatura2("ahomctb", "id_tipo_doc", $selects[0], $selects[1], $archivos[1], $conexion);
            //validar si encontro un tipo de parametrizacion para el interes
            if ($id1 != "X") {
                echo json_encode(['No puede realizar esta actualizacion de parametrizacion porque ya existe', '0']);
                return;
            }

            //se hara la actualizacion
            $conexion->autocommit(false);
            try {
                $conexion->query("UPDATE ahomctb
                    SET id_tipo_cuenta = $selects[0],id_tipo_doc=$selects[1],id_cuenta1=$inputs[0],id_cuenta2=$inputs[1],dfecmod='$hoy',codusu=$archivos[0] WHERE id=$archivos[1]");

                $conexion->commit();
                echo json_encode(['Datos actualizados correctamente', '1']);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
            }
            mysqli_close($conexion);
        }
        break;
    case "delete_aho_cuentas_contables": {
            $id = $_POST["ideliminar"];
            $eliminar = "DELETE FROM ahomctb WHERE id =" . $id;
            if (mysqli_query($conexion, $eliminar)) {
                echo json_encode(['Eliminacion correcta ', '1']);
            } else {
                echo json_encode(['Error al eliminar ', '0']);
            }
            mysqli_close($conexion);
        }
        break;
    case "create_aho_cuentas_intereses": {
            $hoy = date("Y-m-d H:i:s");
            $inputs = $_POST["inputs"];
            $selects = $_POST["selects"];
            $archivos = $_POST["archivo"];

            //validaciones
            //validacion de select tipo de cuenta
            if ($selects[0] == "0") {
                echo json_encode(['Debe seleccionar un tipo de cuenta', '0']);
                return;
            }
            if ($selects[1] == "0") {
                echo json_encode(['Debe seleccionar un tipo de operacion', '0']);
                return;
            }
            //validacion de select de tipo de documento
            //validar input cuenta 1
            if ($inputs[0] == "") {
                echo json_encode(['Debe seleccionar una cuenta para el debe', '0']);
                return;
            }
            //validar input cuenta 2
            if ($inputs[1] == "") {
                echo json_encode(['Debe seleccionar una cuenta para el haber', '0']);
                return;
            }

            //Validar si ya existe una insercion con los mismos
            list($id1, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("ahomparaintere", "id_descript_intere", $selects[0], $selects[1], $conexion);
            //validar si encontro un tipo de parametrizacion para el interes
            if ($id1 != "X") {
                echo json_encode(['No puede agregar esta parametrizacion porque ya existe', '0']);
                return;
            }

            //se hara la insercion
            $conexion->autocommit(false);
            try {
                $conexion->query("INSERT INTO ahomparaintere (id_tipo_cuenta,id_descript_intere,id_cuenta1,id_cuenta2,dfecmod,id_usuario)
                        VALUES ($selects[0],$selects[1],$inputs[0],$inputs[1],'$hoy',$archivos[0])");

                $conexion->commit();
                echo json_encode(['Datos ingresados correctamente', '1']);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
            }
            mysqli_close($conexion);
        }
        break;
    case "update_aho_cuentas_intereses": {
            $hoy = date("Y-m-d H:i:s");
            $inputs = $_POST["inputs"];
            $selects = $_POST["selects"];
            $archivos = $_POST["archivo"];

            //validaciones
            //validacion de select tipo de cuenta
            if ($selects[0] == "0") {
                echo json_encode(['Debe seleccionar un tipo de cuenta', '0']);
                return;
            }
            if ($selects[1] == "0") {
                echo json_encode(['Debe seleccionar un tipo de operación', '0']);
                return;
            }
            //validacion de select de tipo de documento
            //validar input cuenta 1
            if ($inputs[0] != "" && $inputs[2] == "") {
                echo json_encode(['Debe seleccionar una cuenta para el debe', '0']);
                return;
            }
            //validar input cuenta 2
            if ($inputs[1] != "" && $inputs[3] == "") {
                echo json_encode(['Debe seleccionar una cuenta para el haber', '0']);
                return;
            }

            //Validar si ya existe una insercion con los mismos
            $id1 = get_ctb_nomenclatura2("ahomparaintere", "id_descript_intere", $selects[0], $selects[1], $archivos[1], $conexion);
            //validar si encontro un tipo de parametrizacion para el interes
            if ($id1 != "X") {
                echo json_encode(['No puede realizar esta actualizacion de parametrizacion porque ya existe', '0']);
                return;
            }

            //se hara la actualizacion
            $conexion->autocommit(false);
            try {
                $conexion->query("UPDATE ahomparaintere
                    SET id_tipo_cuenta = $selects[0],id_descript_intere=$selects[1],id_cuenta1=$inputs[0],id_cuenta2=$inputs[1],dfecmod='$hoy',id_usuario=$archivos[0] WHERE id=$archivos[1]");

                $conexion->commit();
                echo json_encode(['Datos actualizados correctamente', '1']);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
            }
            mysqli_close($conexion);
        }
        break;
    case "delete_aho_cuentas_intereses": {
            $id = $_POST["ideliminar"];
            $eliminar = "DELETE FROM ahomparaintere WHERE id =" . $id;
            if (mysqli_query($conexion, $eliminar)) {
                echo json_encode(['Eliminacion correcta ', '1']);
            } else {
                echo json_encode(['Error al eliminar ', '0']);
            }
            mysqli_close($conexion);
        }
        break;
    case "reversion_recibo": {
            $archivos = $_POST["archivo"];
            $hoy = date("Y-m-d H:i:s");
            $hoy2 = date("Y-m-d");

            $ccodaho = "";
            $ctipope_aux = "";
            $cnumdoc_aux = "";
            $tipotransaccion = "";
            $camp_glosa = "";
            $fechaaux4 = "";
            $usuario4 = "";

            //consultar datos del aprmov con id recibido
            $data_ahommov = mysqli_query($conexion, "SELECT `ccodaho`,`ctipope`,`cnumdoc`,`ctipdoc`,`monto`, CAST(`created_at` AS DATE) AS created_at, created_by,`dfecope` FROM `ahommov` WHERE `id_mov`='$archivos[0]' AND cestado!=2");
            while ($da = mysqli_fetch_array($data_ahommov, MYSQLI_ASSOC)) {
                $ccodaho = $da["ccodaho"];
                $ctipope = $da["ctipope"];
                $cnumdoc = $da["cnumdoc"];
                $ctipdoc = $da["ctipdoc"];
                $ccodtip = substr($da["ccodaho"], 6, 2);
                $monto = $da["monto"];
                $fechaaux4 = $da["created_at"];
                $usuario4 = $da["created_by"];
                $dfecope = $da["dfecope"];
            }

            //COMPROBAR SI EL MES CONTABLE ESTA ABIERTO
            $cierre = comprobar_cierre($idusuario, $hoy2, $conexion);
            if ($cierre[0] == 0) {
                echo json_encode([$cierre[1], '0']);
                return;
            }

            //COMPROBAR CIERRE DE CAJA
            $fechainicio = date('Y-m-d', strtotime(date('Y-m-d') . ' - 7 days'));
            $fechafin = date('Y-m-d');
            $cierre_caja = comprobar_cierre_caja($_SESSION['id'], $conexion, 1, $fechainicio, $fechafin, $fechaaux4);
            if ($cierre_caja[0] < 6) {
                echo json_encode([$cierre_caja[1], '0']);
                return;
            }
            if ($cierre_caja[0] == 8) {
                if ($usuario4 != $archivos[1]) {
                    echo json_encode(['El usuario creador del registro no coincide con el que quiere reversar, no es posible completar la acción', '0']);
                    return;
                }
            }
            //FIN DE COMPROBACION DE CIERRE

            //consultar datos de aprcta
            $data_ahomcta = mysqli_query($conexion, "SELECT `nlibreta`,`ccodcli` FROM `ahomcta` WHERE `ccodaho`=$ccodaho");
            while ($da = mysqli_fetch_array($data_ahomcta, MYSQLI_ASSOC)) {
                $nlibreta = $da["nlibreta"];
                $ccodcli = $da["ccodcli"];

                $ultimonum = lastnumlin($ccodaho, $nlibreta, "ahomcta", "ccodaho", $conexion);
                $ultimocorrel = lastcorrel($ccodaho, $nlibreta, "ahomcta", "ccodaho", $conexion);
            }
            //consultar datos de tabla cliete para el nombre
            $data_cliente = mysqli_query($conexion, "SELECT `short_name`, `no_identifica`,tip.nombre FROM tb_cliente cli 
                INNER JOIN ahomcta aho ON cli.idcod_cliente=aho.ccodcli 
                INNER JOIN ahomtip tip ON tip.ccodtip=SUBSTR(aho.ccodaho,7,2)
                WHERE aho.ccodaho='$ccodaho'");
            while ($da = mysqli_fetch_array($data_cliente, MYSQLI_ASSOC)) {
                $producto = $da["nombre"];
                $shortname = (mb_strtoupper($da["short_name"], 'utf-8'));
                $dpi = $da["no_identifica"];
            }

            //consultar ids nomenclaturas
            list($id, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("ahomctb", "id_tipo_doc", (tipocuenta($ccodtip, "ahomtip", "id_tipo", $conexion)), (get_id_tipdoc($ctipdoc, "ahotipdoc", $conexion)), $conexion);

            // echo json_encode(['Datos ingresados correctamente', '1', $id,$idcuenta1, $idcuenta2]);
            // return;

            $conexion->autocommit(false);
            try {
                ($ctipope == "D") ? $ctipope_aux = "R" : $ctipope_aux = "D";
                $cnumdoc_aux = "REV-" . $cnumdoc;

                $camp_glosa .= "REVERSIÓN DE ";
                if ($ctipope == "D") {
                    $tipotransaccion = "REVERSIÓN DE DEPÓSITO";

                    $camp_glosa .= glosa_obtenerMovimiento(0);
                } else {
                    $tipotransaccion = "REVERSIÓN DE RETIRO";
                    $camp_glosa .= glosa_obtenerMovimiento(1);
                }
                $camp_glosa .= glosa_obtenerEspacio();
                $camp_glosa .= glosa_obtenerConector(0);
                $camp_glosa .= glosa_obtenerEspacio();
                $camp_glosa .= glosa_obtenerTipoModulo(0); //deposito o ahorro de aportacion
                $camp_glosa .= glosa_obtenerEspacio();
                $camp_glosa .= glosa_obtenerConector(0);
                $camp_glosa .= glosa_obtenerEspacio();
                $camp_glosa .= glosa_obtenerNomCliente($ccodcli, $conexion); //cliente
                $camp_glosa .= glosa_obtenerEspacio();
                $camp_glosa .= glosa_obtenerRecibo($cnumdoc_aux);

                //INSERCIONES POR APRMOV
                //actualizar registro anterior, para cambiarle su estado
                $conexion->query("UPDATE `ahommov` SET `dfecmod` = '$hoy',`codusu` = '$archivos[1]',`cestado` = '0' WHERE `id_mov` = '$archivos[0]'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error al actualizar aprmov:' . $aux, '0']);
                    $conexion->rollback();
                    return;
                }
                //insercion en aprmov
                $conexion->query("INSERT INTO `ahommov`(`ccodaho`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`monto`,`lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`created_by`,`created_at`,`cestado`) VALUES ('$ccodaho','$hoy2','$ctipope_aux','$cnumdoc_aux','$ctipdoc','$tipotransaccion', $nlibreta,$monto,'N',$ultimonum+1,$ultimocorrel+1,'$hoy','$archivos[1]','$archivos[1]','$hoy','0')");
                //insercion en aprcta
                $conexion->query("UPDATE `ahomcta` SET `fecha_ult` = '$hoy2',`correlativo` = $ultimocorrel+1,`numlinea` = $ultimonum+1 WHERE `ccodaho` = '$ccodaho'");

                //INSERCIONES EN CTB_DIARIO
                $camp_numcom = getnumcom($archivos[1], $conexion);
                //SE HACE UN REGISTRO EN CONTABILIDAD, EN LA TABLA "ctb_diario"
                $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$camp_numcom',2,1,'$cnumdoc_aux', '$camp_glosa','$hoy2', '$hoy2','$ccodaho','$archivos[1]','$hoy',1)");
                //-----FIN

                //SE HACE 2 REGISTROS EN CONTABILIDAD EN LA TABLA "ctb_mov"
                $id_ctb_diario = get_id_insertado($conexion);

                if ($ctipope == "D") {
                    $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta2, '$monto',0)");
                    $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta1, 0,'$monto')");
                }
                if ($ctipope == "R") {
                    $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta1, '$monto',0)");
                    $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta2, 0,'$monto')");
                }
                // -----FIN

                if ($conexion->commit()) {
                    //NUMERO EN LETRAS
                    $format_monto = new NumeroALetras();
                    $decimal = explode(".", $monto);
                    $res = (isset($decimal[1]) == false) ? 0 : $decimal[1];
                    $letras_monto = ($format_monto->toMoney($decimal[0], 2, 'QUETZALES', '')) . " " . $res . "/100";
                    $particionfecha = explode("-", $dfecope);

                    ($ctipope == "D") ? $archivos[1] = "Reversión de depósito a cuenta " . $ccodaho : $archivos[1] = "Reversión de retiro a cuenta " . $ccodaho;
                    echo json_encode(['Datos reversados correctamente', '1', $ccodaho, number_format($monto, 2, '.', ','), date("d-m-Y", strtotime($hoy)), $cnumdoc_aux, $archivos[1], $shortname, utf8_decode($_SESSION['nombre']), utf8_decode($_SESSION['apellido']), $hoy, $letras_monto, $particionfecha[0], $particionfecha[1], $particionfecha[2], $dpi, $producto]);
                } else {
                    echo json_encode(['Error al ingresar: ', '0']);
                }
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
            }

            mysqli_close($conexion);
        }
        break;
    case 'edicion_recibo':
        $inputs = $_POST["inputs"];
        $hoy = date("Y-m-d H:i:s");
        $hoy2 = date("Y-m-d");

        $fechaaux4 = "";
        $usuario4 = "";

        //validar si hay campos vacios
        $valido = validarcampo([$inputs[0], $inputs[2], $inputs[3]], "");
        if ($valido != "1") {
            echo json_encode(['Hay campos que no estan llenos, no se puede completar la operación', '0']);
        }

        //consultar datos del aprmov con id recibido
        $data_ahommov = mysqli_query($conexion, "SELECT `ccodaho`,`ctipope`,`cnumdoc`,`ctipdoc`,`monto`, CAST(`created_at` AS DATE) AS created_at, created_by,`dfecope` FROM `ahommov` WHERE `id_mov`='$inputs[0]' AND cestado!=2");
        while ($da = mysqli_fetch_array($data_ahommov, MYSQLI_ASSOC)) {
            $ccodaho = $da["ccodaho"];
            $ctipope = $da["ctipope"];
            $cnumdoc = $da["cnumdoc"];
            $ctipdoc = $da["ctipdoc"];
            $ccodtip = substr($da["ccodaho"], 6, 2);
            $monto = $da["monto"];
            $fechaaux4 = $da["created_at"];
            $usuario4 = $da["created_by"];
            $dfecope = $da["dfecope"];
        }

        //COMPROBACION DE ESTADO DEL MES CONTABLE
        $cierre = comprobar_cierre($idusuario, $hoy2, $conexion);
        if ($cierre[0] == 0) {
            echo json_encode([$cierre[1], '0']);
            return;
        }

        //COMPROBAR CIERRE DE CAJA
        $fechainicio = date('Y-m-d', strtotime(date('Y-m-d') . ' - 7 days'));
        $fechafin = date('Y-m-d');
        $cierre_caja = comprobar_cierre_caja($_SESSION['id'], $conexion, 1, $fechainicio, $fechafin, $fechaaux4);
        if ($cierre_caja[0] < 6) {
            echo json_encode([$cierre_caja[1], '0']);
            return;
        }

        if ($cierre_caja[0] == 8) {
            if ($usuario4 != $inputs[3]) {
                echo json_encode(['El usuario creador del registro no coincide con el que quiere editar, no es posible completar la acción', '0']);
                return;
            }
        }

        if (substr($cnumdoc, 0, 4) == "REV-") {
            echo json_encode(['No puede editar la reversión de un recibo', '0']);
            return;
        }

        //consultar datos de aprcta
        $data_ahomcta = mysqli_query($conexion, "SELECT `ccodcli` FROM `ahomcta` WHERE `ccodaho`=$ccodaho");
        while ($da = mysqli_fetch_array($data_ahomcta, MYSQLI_ASSOC)) {
            $ccodcli = $da["ccodcli"];
        }

        //consultar datos de tabla cliete para el nombre
        $data_cliente =  mysqli_query($conexion, "SELECT `short_name`, `no_identifica` FROM `tb_cliente` WHERE `idcod_cliente`='$ccodcli'");
        while ($da = mysqli_fetch_array($data_cliente, MYSQLI_ASSOC)) {
            $shortname = (mb_strtoupper($da["short_name"], 'utf-8'));
            $dpi = $da["no_identifica"];
        }

        //obtener el registro anterior
        $bandera = false;
        $data_reg_ant = mysqli_query($conexion, "SELECT `id` FROM `ctb_diario` WHERE `numdoc`='$inputs[1]'");
        while ($da = mysqli_fetch_array($data_reg_ant, MYSQLI_ASSOC)) {
            $id_diario = $da["id"];
            $bandera = true;
        }

        $conexion->autocommit(false);
        try {
            //ACTUALIZACIONES EN APRMOV
            $conexion->query("UPDATE `ahommov` SET `cnumdoc` = '$inputs[2]',`dfecmod` = '$hoy',`codusu` = '$inputs[3]' WHERE `id_mov` = '$inputs[0]'");
            if ($bandera) {
                //INSERCIONES EN CTB_DIARIO
                $conexion->query("UPDATE `ctb_diario` SET `numdoc` = '$inputs[2]',`fecmod` = '$hoy',`id_tb_usu` = '$inputs[3]' WHERE `id` = $id_diario");
            }
            if ($conexion->commit()) {

                //NUMERO EN LETRAS
                $format_monto = new NumeroALetras();
                $decimal = explode(".", $monto);
                $res = (isset($decimal[1]) == false) ? 0 : $decimal[1];
                $letras_monto = ($format_monto->toMoney($decimal[0], 2, 'QUETZALES', '')) . " " . $res . "/100";
                $particionfecha = explode("-", $dfecope);

                ($ctipope == "D") ? $inputs[3] = "Depósito a cuenta " . $ccodaho : $inputs[3] = "Retiro a cuenta " . $ccodaho;
                echo json_encode(['Datos actualizados correctamente', '1', $ccodaho, number_format($monto, 2, '.', ','), date("d-m-Y", strtotime($hoy)), $inputs[2], $inputs[3], $shortname, utf8_decode($_SESSION['nombre']), utf8_decode($_SESSION['apellido']), $hoy, $letras_monto, $particionfecha[0], $particionfecha[1], $particionfecha[2], $dpi, "producto", $_SESSION['id']]);
            } else {
                echo json_encode(['Error al ingresar: ', '0']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);

        break;
    case 'reimpresion_recibo':
        $archivos = $_POST["archivo"];
        $hoy = date("Y-m-d H:i:s");
        $hoy2 = date("Y-m-d");

        //consultar datos del aprmov con id recibido
        $data_ahommov = mysqli_query($conexion, "SELECT `ccodaho`,`ctipope`,`cnumdoc`,`monto`,`dfecope` FROM `ahommov` WHERE `id_mov`='$archivos[0]' AND cestado!=2");
        while ($da = mysqli_fetch_array($data_ahommov, MYSQLI_ASSOC)) {
            $ccodaho = $da["ccodaho"];
            $ctipope = $da["ctipope"];
            $cnumdoc = $da["cnumdoc"];
            $monto = $da["monto"];
            $dfecope = $da["dfecope"];
        }

        //consultar datos de aprcta
        $data_ahomcta = mysqli_query($conexion, "SELECT `short_name`, `no_identifica`,tip.nombre,cli.control_interno FROM tb_cliente cli 
                INNER JOIN ahomcta aho ON cli.idcod_cliente=aho.ccodcli 
                INNER JOIN ahomtip tip ON tip.ccodtip=SUBSTR(aho.ccodaho,7,2)
                WHERE aho.ccodaho='$ccodaho'");
        while ($da = mysqli_fetch_array($data_ahomcta, MYSQLI_ASSOC)) {
            $producto = $da["nombre"];
            $shortname = (mb_strtoupper($da["short_name"], 'utf-8'));
            $dpi = $da["no_identifica"];
            $controlinterno = $da["control_interno"];
        }

        if (substr($cnumdoc, 0, 4) == "REV-") {
            ($ctipope == "R") ? $archivos[1] = "Reversión de depósito a cuenta " . $ccodaho : $archivos[1] = "Reversión de retiro a cuenta " . $ccodaho;
        } else {
            ($ctipope == "D") ? $archivos[1] = "Depósito a cuenta " . $ccodaho : $archivos[1] = "Retiro a cuenta " . $ccodaho;
        }

        //NUMERO EN LETRAS
        $format_monto = new NumeroALetras();
        $decimal = explode(".", $monto);
        $res = (isset($decimal[1]) == false) ? 0 : $decimal[1];
        $letras_monto = ($format_monto->toMoney($decimal[0], 2, 'QUETZALES', '')) . " " . $res . "/100";
        $particionfecha = explode("-", $dfecope);

        echo json_encode(['Datos reimpresos correctamente', '1', $ccodaho, number_format($monto, 2, '.', ','), date("d-m-Y", strtotime($dfecope)), $cnumdoc, $archivos[1], $shortname, utf8_decode($_SESSION['nombre']), utf8_decode($_SESSION['apellido']), $hoy, $letras_monto, $particionfecha[0], $particionfecha[1], $particionfecha[2], $dpi, $producto, $_SESSION['id'], $controlinterno]);
        mysqli_close($conexion);
        break;
    case 'eliminacion_recibo':
        $idDato = $_POST["ideliminar"];

        $conexion->autocommit(false);
        //Obtener informaicon de la ahommov
        $consulta = mysqli_query($conexion, "SELECT ccodaho, dfecope, cnumdoc, CAST(created_at AS DATE) AS fecsis FROM ahommov WHERE id_mov = $idDato AND cestado!=2");
        $dato = $consulta->fetch_row();

        //COMPROBAR CIERRE DE CAJA
        $fechainicio = date('Y-m-d', strtotime(date('Y-m-d') . ' - 7 days'));
        $fechafin = date('Y-m-d');
        $cierre_caja = comprobar_cierre_caja($_SESSION['id'], $conexion, 1, $fechainicio, $fechafin, $dato[3]);
        if ($cierre_caja[0] < 6) {
            echo json_encode([$cierre_caja[1], '0']);
            return;
        }
        $fechapoliza = $dato[1];
        //COMPROBAR SI EL MES CONTABLE ESTA ABIERTO 
        $consulta = mysqli_query($conexion, "SELECT feccnt FROM ctb_diario WHERE cod_aux = '$dato[0]' AND fecdoc = '$dato[1]' AND numdoc = '$dato[2]'");
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $fechapoliza = $fila["feccnt"];
        }

        $cierre = comprobar_cierre($idusuario, $fechapoliza, $conexion);
        if ($cierre[0] == 0) {
            echo json_encode([$cierre[1], '0']);
            return;
        }
        try {
            $res = $conexion->query("UPDATE ahommov SET cestado = '2', codusu = $idusuario, dfecmod = '$hoy2'  WHERE id_mov = $idDato");
            $aux = mysqli_error($conexion);

            $res1 = $conexion->query("UPDATE ctb_diario SET estado = '0', deleted_by = $idusuario, deleted_at = '$hoy2'  WHERE cod_aux = '$dato[0]' AND fecdoc = '$dato[1]' AND numdoc = '$dato[2]'");
            $aux1 = mysqli_error($conexion);

            if ($aux && $aux1) {
                echo json_encode(['Error fff', '0']);
                $conexion->rollback();
                return;
            }
            if (!$res && !$res1) {
                echo json_encode(['Error al ingresar ', '0']);
                $conexion->rollback();
                return;
            }
            $conexion->commit();
            echo json_encode(['Los datos fueron actualizados con exito ', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, al hacer el registro: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'consultar_reporte':
        $id_descripcion = $_POST["id_descripcion"];
        $validar = validar_campos_plus([
            [$id_descripcion, "", 'No se ha detectado un identificador de reporte válido', 1],
            [$id_descripcion, "0", 'Ingrese un número de reporte mayor a 0', 1],
        ]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }
        try {
            //Validar si de casualidad ya se hizo el cierre otro usuario
            $stmt = $conexion->prepare("SELECT * FROM tb_documentos td WHERE td.id = ?");
            if (!$stmt) {
                throw new Exception("Error en la consulta 1: " . $conexion->error);
            }
            $stmt->bind_param("s", $id_descripcion); //El arroba omite el warning de php
            if (!$stmt->execute()) {
                throw new Exception("Error en la ejecucion de la consulta 1: " . $stmt->error);
            }
            $result = $stmt->get_result();
            $numFilas2 = $result->num_rows;
            if ($numFilas2 == 0) {
                throw new Exception("No se encontro el reporte en el listado de documentos disponible");
            }
            $fila = $result->fetch_assoc();
            echo json_encode(["Reporte encontrado", '1', $fila['nombre']]);
        } catch (Exception $e) {
            //Captura el error
            $mensaje_error = $e->getMessage();
            echo json_encode([$mensaje_error, '0']);
        } finally {
            if ($stmt !== false) {
                $stmt->close();
            }
            $conexion->close();
        }
        break;
}

//FUNCION para obtener los depositos de los utimos 30 dias y los R(reversion y retiros) asi como el valor del dolar.
function movimiento($conexion, $op = 0, $codCli = [], $tipoMov = '', $fechaH = '', $codCu = '')
{
    switch ($op) {
        case 1: //Depositos y Retiros
            $dato = mysqli_query($conexion, "SELECT (IFNULL(SUM(monto),0))  AS dato 
            FROM ahommov AS mov
            INNER JOIN ahomcta AS ac ON mov.ccodaho = ac.ccodaho
            WHERE ac.estado = 'A' AND mov.cestado!=2 AND ac.ccodcli = '" . $codCli['ccodcli'] . "' AND ctipope = '" . $tipoMov . "'
            AND dfecope BETWEEN " . $fechaH . " AND CURDATE()");
            $error = mysqli_error($conexion);
            if ($error) {
                echo json_encode(['Error … !!!,  comunicarse con soporte. ', '0']);
                return;
            };
            $movMot = mysqli_fetch_assoc($dato);
            return $movMot['dato'];
            break;
        case 2:
            $dato = mysqli_query($conexion, "SELECT equiDolar AS dato FROM clhpzzvb_bd_general_coopera.tb_monedas WHERE id = 1");
            $error = mysqli_error($conexion);
            if ($error) {
                echo json_encode(['Error … !!!,  comunicarse con soporte. ', '0']);
                return;
            };
            $movMot = mysqli_fetch_assoc($dato);
            return $movMot['dato'];
            break;
        case 3:
            return ((movimiento($conexion, 1, $codCli, 'D', '(DATE_SUB(CURDATE(), INTERVAL 30 DAY))')) - (movimiento($conexion, 1, $codCli, 'R', '(DATE_SUB(CURDATE(), INTERVAL 30 DAY))')));
            break;
        case 4:
            $fecha = '';
            $dato = mysqli_query($conexion, "SELECT MAX(fecha) as fecha FROM tb_alerta WHERE cod_aux = '$codCu' AND proceso = ('A' OR 'A1') AND fecha BETWEEN (DATE_SUB(CURDATE(), INTERVAL 30 DAY)) AND CURDATE()");

            $fila =  mysqli_affected_rows($conexion);
            if ($fila > 0) {
                $datoF = mysqli_fetch_assoc($dato);
                $fecha = "'" . $datoF['fecha'] . "'";
            }
            if ($fila == 0) $fecha = 'CURDATE()';
            return ((movimiento($conexion, 1, $codCli, 'D', $fecha)) - (movimiento($conexion, 1, $codCli, 'R', $fecha)));
            break;
        case 5:
            $dato = mysqli_query($conexion, "SELECT MAX(codDoc) AS codDoc FROM tb_alerta WHERE cod_aux = '" . $codCu . "'");
            $codDoc = mysqli_fetch_assoc($dato);

            $dato1 = mysqli_query($conexion, "SELECT IFNULL(dfecmod, '0') AS fecha FROM ahommov WHERE cestado!=2 AND cnumdoc = '" . $codDoc['codDoc'] . "';");

            if (mysqli_affected_rows($conexion) != 0) {
                $fechaHora = mysqli_fetch_assoc($dato1);
                $dato1 = mysqli_query($conexion, "SELECT (IFNULL(SUM(monto),0))  AS mov 
                FROM ahommov AS mov
                INNER JOIN ahomcta AS ac ON mov.ccodaho = ac.ccodaho
                WHERE ac.estado = 'A' AND mov.cestado!=2 AND ac.ccodcli = '" . $codCli['ccodcli'] . "' AND ctipope = '" . $tipoMov . "'
                AND mov.dfecmod > '" . $fechaHora['fecha'] . "';");
                $auxMov = mysqli_fetch_assoc($dato1);
                // echo json_encode(['Fecha '.$auxMov['mov'], '0']);
                // return; 
                return $auxMov['mov'];
            }
            return 0;
            break;
    }
}

//FUNCIN para el control de las alertas
function alerta($conexion, $op = 0, $codCu = '', $hoy2 = '', $codUsu = 0, $hoy = '', $proceso = '', $cnumdoc = '', $cliente = '')
{
    switch ($op) {
        case 1:
            $res = $conexion->query("INSERT INTO `tb_alerta` (`puesto`, `tipo_alerta`, `mensaje`, `cod_aux`, `proceso`,`estado`, `fecha`,`created_by`, `created_at`, `codDoc`) value('LOG', 'IVE', 'Llenar el formulario del IVE', '$codCu', '$proceso', 1, '$hoy2', $codUsu, '$hoy', '$cnumdoc')");
            if (mysqli_error($conexion) || !$res) {
                echo json_encode(['Error … !!!,  comunicarse con soporte. ', '0']);
                return;
            }
            break;
        case 2:
            $dato = '';
            $consulta = mysqli_query($conexion, "SELECT IFNULL(MAX(proceso),'0') AS pro  FROM  `tb_alerta` WHERE proceso = ('A' OR 'A1') AND `cod_aux` = '$codCu' AND `fecha` = '$hoy2'");
            $datoAlerta = mysqli_fetch_assoc($consulta);
            $dato = $datoAlerta['pro'];

            if ($dato == 'A' || $dato == '0') $dato = 'VC'; //Retorno un valor vacio
            return $dato;
            break;
        case 3:
            $consulta = mysqli_query($conexion, "SELECT EXISTS(
            SELECT id FROM tb_alerta WHERE cod_aux = '$codCu' AND proceso = 'A' AND fecha BETWEEN (DATE_SUB(CURDATE(), INTERVAL 30 DAY)) AND CURDATE()) AS dato");
            $rsultadoIVE = mysqli_fetch_assoc($consulta);
            return $rsultadoIVE['dato'];
            break;
        case 4:
            $consulta = mysqli_query($conexion, "SELECT EXISTS(SELECT codDoc FROM tb_alerta WHERE 
            cod_aux = '" . $codCu . "' AND codDoc = '" . $cnumdoc . "' AND estado = 0 AND proceso IN ('A' ,'A1')) AS dato ;");
            $datoAux = mysqli_fetch_assoc($consulta);
            return $datoAux['dato'];
            break;
        case 5:
            $consulta = mysqli_query($conexion, "SELECT CONCAT(nombre, apellido) AS cli, Email FROM tb_usuario WHERE estado = 1 AND puesto IN ('CNT', 'ADM')");
            if (mysqli_error($conexion)) {
                echo json_encode(['Error … !!!,  comunicarse con soporte. ', '0']);
                return;
            }
            $arch = [$cliente, $codCu, $cnumdoc];
            while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                enviarCorreo("" . $row['Email'] . "", "" . $row['cli'] . "", "Alerta IVE", "<h5>El sistema se encuentra a la espera de la aprobación de una alerta de IVE.</h5>", $arch);
            }
            break;
    }
}

function validar_campos_plus($validaciones)
{
    for ($i = 0; $i < count($validaciones); $i++) {
        if ($validaciones[$i][3] == 1) { //igual
            if ($validaciones[$i][0] == $validaciones[$i][1]) {
                return [$validaciones[$i][2], '0', true];
                $i = count($validaciones) + 1;
            }
        } elseif ($validaciones[$i][3] == 2) { //menor que
            if ($validaciones[$i][0] < $validaciones[$i][1]) {
                return [$validaciones[$i][2], '0', true];
                $i = count($validaciones) + 1;
            }
        } elseif ($validaciones[$i][3] == 3) { //mayor que
            if ($validaciones[$i][0] > $validaciones[$i][1]) {
                return [$validaciones[$i][2], '0', true];
                $i = count($validaciones) + 1;
            }
        } elseif ($validaciones[$i][3] == 4) { //Validarexpresionesregulares
            if (validar_expresion_regular($validaciones[$i][0], $validaciones[$i][1])) {
                return [$validaciones[$i][2], '0', true];
                $i = count($validaciones) + 1;
            }
        } elseif ($validaciones[$i][3] == 5) { //Escapar de la validacion
        } elseif ($validaciones[$i][3] == 6) { //menor o igual
            if ($validaciones[$i][0] <= $validaciones[$i][1]) {
                return [$validaciones[$i][2], '0', true];
                $i = count($validaciones) + 1;
            }
        } elseif ($validaciones[$i][3] == 7) { //menor o igual
            if ($validaciones[$i][0] >= $validaciones[$i][1]) {
                return [$validaciones[$i][2], '0', true];
                $i = count($validaciones) + 1;
            }
        } elseif ($validaciones[$i][3] == 8) { //diferente de
            if ($validaciones[$i][0] != $validaciones[$i][1]) {
                return [$validaciones[$i][2], '0', true];
                $i = count($validaciones) + 1;
            }
        }
    }
    return ["", '0', false];
}
function executequery($query, $params, $typparams, $conexion)
{
    $stmt = $conexion->prepare($query);
    $aux = mysqli_error($conexion);
    if ($aux) {
        return ['ERROR: ' . $aux, false];
    }
    $types = '';
    $bindParams = [];
    $bindParams[] = &$types;
    $i = 0;
    foreach ($params as &$param) {
        // $types .= 's';
        $types .= $typparams[$i];
        $bindParams[] = &$param;
        $i++;
    }
    call_user_func_array(array($stmt, 'bind_param'), $bindParams);
    if (!$stmt->execute()) {
        return ["Error en la ejecución de la consulta: " . $stmt->error, false];
    }
    $data = [];
    $resultado = $stmt->get_result();
    $i = 0;
    while ($fila = $resultado->fetch_assoc()) {
        $data[$i] = $fila;
        $i++;
    }
    $stmt->close();
    return [$data, true];
}
