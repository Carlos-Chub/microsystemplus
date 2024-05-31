<?php
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '3600');
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../funcphp/func_gen.php';

require '../../vendor/autoload.php';
$idusuario = $_SESSION['id'];
$idagencia = $_SESSION['id_agencia'];

use Luecano\NumeroALetras\NumeroALetras;

date_default_timezone_set('America/Guatemala');

$condi = $_POST["condi"];

switch ($condi) {
        //CRUD - TIPOS DE APORTACIONES
        //-----CREATE
    case 'create_aport_tip': {
            //valores de los inputs
            $inputs = $_POST["inputs"];
            $selects = $_POST["selects"]; //selects datos
            //nombre de etiquetas
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
            $tasa = floatval($inputs[3]);
            $valido = validarcampo($inputs, "");
            if ($valido != "1") {
                echo json_encode([$valido, '0']);
                return;
            }

            $valido1 = validar_limites(0, 500, $tasa);
            if ($valido1 != "1") {
                echo json_encode([$valido1, '0']);
                return;
            }

            $query = mysqli_query($conexion, "INSERT INTO `aprtip`($consulta,`correlativo`,`numfront`,`front_ini`,`numdors`,`dors_ini`) VALUES ('$inputs[0]','$inputs[1]','$inputs[2]',$tasa,'$selects[0]',0,30,20,30,20)");
            if ($query) {
                echo json_encode(['Registro Ingresado ', '1']);
            } else {
                echo json_encode(['Error al ingresar ', '0']);
            }

            mysqli_close($conexion);
        }
        break;
        //-----ACTUALIZAR
    case 'update_aport_tip': {
            $inputs = $_POST["inputs"];
            $inputsn = $_POST["inputsn"];
            $idtip = $_POST["archivo"];  // 
            $consulta = "";
            //PARA LOS INPUTS
            $i = 0;
            foreach ($inputs as $input) {
                $consulta = $consulta . "`" . $inputsn[$i] . "` = '" . utf8_encode($input) . "'";
                if ($i != count($inputs) - 1) {
                    $consulta = $consulta . ",";
                }
                $i = $i + 1;
            }

            $valido = validarcampo($inputs, "");
            if ($valido != "1") {
                echo json_encode([$valido, '0']);
                return;
            }

            $valido1 = validar_limites(1, 500, $inputs[3]);
            if ($valido1 != "1") {
                echo json_encode([$valido1, '0']);
                return;
            }

            $query = mysqli_query($conexion, "UPDATE `aprtip` set $consulta WHERE id_tipo=" . $idtip);
            if ($query) {
                echo json_encode(['Registro actualizado correctamente ', '1']);
            } else {
                echo json_encode(['Error al actualizar ', '0']);
            }
            mysqli_close($conexion);
        }
        break;
        //-----ELIMINAR

    case 'delete_aport_tip': {
            $idtip = $_POST["ideliminar"];

            $eliminar = "DELETE FROM aprtip WHERE id_tipo =" . $idtip;
            if (mysqli_query($conexion, $eliminar)) {
                echo json_encode(['Eliminacion correcta ', '1']);
            } else {
                echo json_encode(['Error al eliminar ', '0']);
            }
            mysqli_close($conexion);
        }
        break;

        //CRUD - APERTURA DE CUENTAS
        //-----CREATE

    case 'create_apr_cuenta': {
            //SUBSTR EN PHP INICIA EN 0, SUBSTR EN SQL INICIA EN 1

            $hoy = date("Y-m-d");
            $inputs = $_POST["inputs"];
            $selects = $_POST["selects"]; //selects datos
            $inputsn = $_POST["inputsn"];  // 
            $selectsn = $_POST["selectsn"];     // selects nombres

            $validacion = validarcampo([$inputs[4], $inputs[5]], "");
            //validar los productos
            if ($selects[0] == "0") {
                echo json_encode(['No ha seleccionado un tipo de producto o bien la agencia del usuario no tiene asignado ningun prouducto', '0']);
                return;
            }

            if ($validacion == "1") {
                $tipo = $selects[0];
                // list($correlactual, $generar) = correlativo_general("aprcta", "ccodaport", "aprtip", "ccodage", $tipo, $conexion);
                $codcredito = getccodaport($idagencia, $tipo, $conexion);
                if ($codcredito[0] == 0) {
                    echo json_encode(["Fallo!, No se pudo generar el código de cuenta", '0']);
                    return;
                }
                $generar = $codcredito[1];

                $tasa = floatval($inputs[2]);

                //inicio transaccion
                $conexion->autocommit(false);
                try {
                    $conexion->query("INSERT INTO `aprcta`(`ccodaport`,`ccodcli`,`ccodtip`,`num_nit`,`nlibreta`,`estado`,`fecha_apertura`,`fecha_mod`,`codigo_usu`,`tasa`) VALUES ('$generar','$inputs[3]','$selects[0]','$inputs[4]','$inputs[5]','A','$hoy','$hoy','$inputs[6]',$tasa)");
                    // $conexion->query("UPDATE `aprtip` set `correlativo`= $correlactual WHERE ccodtip=" . $selects[0]);
                    $conexion->query("INSERT INTO `aprlib`(`nlibreta`,`ccodaport`,`estado`,`date_ini`,`ccodusu`) VALUES ('$inputs[5]','$generar','A','$hoy','$inputs[6]')");
                    $conexion->commit();
                    echo json_encode(['Correcto,  Codigo Generado: ' . $generar, '1']);
                } catch (Exception $e) {
                    $conexion->rollback();
                    echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
                }
            } else {
                echo json_encode([$validacion, '0']);
            }

            mysqli_close($conexion);
        }
        break;
        //---CONSULTAR EL ULTIMO CORRELATIVO PARA ASIGNARLE AL NUEVO
    case 'correl': {
            $tipo = $_POST["tipo"];
            $ins = $_POST["ins"];
            $ofi = $_POST["ofi"];
            //correlativo actual y total mediante function
            // list($correlactual, $generar) = correlativo_general("aprcta", "ccodaport", "aprtip", "ccodage", $tipo, $conexion);
            $codcredito = getccodaport($idagencia, $tipo, $conexion);
            if ($codcredito[0] == 0) {
                echo json_encode(["Fallo!, No se pudo generar el código de cuenta", '0']);
                return;
            }
            $generar = $codcredito[1];

            $tasa = 0;
            $agencia = 0;
            $consultatas = mysqli_query($conexion, "SELECT `tasa`, `ccodage` FROM `aprtip` WHERE `ccodtip`=$tipo");
            while ($row = mysqli_fetch_array($consultatas, MYSQLI_ASSOC)) {
                $tasa = utf8_encode($row['tasa']);
                $agencia = utf8_encode($row['ccodage']);
            }
            //---
            echo json_encode([$generar, $tasa, $agencia]);
            mysqli_close($conexion);
        }
        break;

        //CRUD - DEPOSITO Y RETIRO
        //-----CREATE DEPOSITO O RETIRO
    case 'cdaportmov':
        //anteriord(['ccodaport', 'dfecope', 'cnumdoc', 'monto', 'numpartida', 'feccom', 'nrochq', 'cuotaIngreso'], ['salida', 'tipdoc', 'tipchq'], [], 'cdaportmov', '0', [usu, ofi, tipotransaction]);
        //actuald(['ccodaho', 'dfecope', 'cnumdoc', 'monto', 'cuotaIngreso','cnumdocboleta'], ['salida', 'tipdoc', 'bancoid', 'cuentaid'], [], 'cdaportmov', '0', [ echo $id;', action]);
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $archivo = $_POST["archivo"];
        $hoy = date("Y-m-d H:i:s");
        $hoy2 = date("Y-m-d");


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
            +++++++++++++++++++++++++++++++++ SALDO DE LA CUENTA DE APORTACIONES +++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $montoaux = 0;
        $saldo = 0;
        $query = "SELECT `monto`,`ctipope`,`dfecope` FROM `aprmov` WHERE `ccodaport`=? AND cestado!=2";
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
        // SELECT id_cuenta_contable FROM aprtip WHERE ccodtip="02";
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

        $query = "SELECT id_cuenta_contable cuenta,cuenta_aprmov cuentaingreso FROM aprtip WHERE ccodtip=?";
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
        $cuenta_cuotaingreso = $data[0]['cuentaingreso']; //cuenta contable del tipo de ahorro

        $cuentacontable = $cuentacaja;
        $tipopoliza = 3; //por defecto es de tipo APORTACIONES
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
                $nocheque = $inputs[5];
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
                $nocheque = $inputs[5];
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
            FROM `aprcta` cta INNER JOIN tb_cliente cli ON cli.idcod_cliente=cta.ccodcli 
            WHERE `ccodaport`=?";

        $response = executequery($query, [$cuenta], ['s'], $conexion);
        if (!$response[1]) {
            echo json_encode([$response[0], '0']);
            return;
        }
        $data = $response[0];
        $flag = ((count($data)) > 0) ? true : false;
        if (!$flag) {
            echo json_encode(["Cuenta de aportaciones no existe", '0']);
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
        $ultimonum = lastnumlin($cuenta, $nlibreta, "aprmov", "ccodaport", $conexion);
        $ultimocorrel = lastcorrel($cuenta, $nlibreta, "aprmov", "ccodaport", $conexion);
        $numlib = numfront(substr($cuenta, 6, 2), "aprtip") + numdorsal(substr($cuenta, 6, 2), "aprtip");
        if ($ultimonum >= $numlib) {
            echo json_encode(["El número de líneas en libreta ha llegado a su límite, se recomienda abrir otra libreta", '0']);
            return;
        }
        if ($estado != "A") {
            echo json_encode(["Cuenta de aportaciones Inactiva", '0']);
            return;
        }
        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            +++++++++++++++++++++++++++++++++++++++++++ ALERTA IVE +++++++++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        //INICIO ALERTA IVE
        //ALERTE IVE ---> No superar los $ 10,000 - hacer la conversion a quetazqles
        $consulta = mysqli_query($conexion, "SELECT ccodcli FROM aprcta WHERE ccodaport = '$cuenta'"); //Seleccionar el codigo del cliente

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
                echo json_encode(['001 ALERTA IVE... en los últimos 30 días la cuenta del cliente ha superado los $10000, para continuar con la transacción el “contador o administrador” tiene que aprobar la alerta. Favor de apuntar el No. Documento: ' . $numdoc . '', '0']);
                return;
            }
        }
        //FIN ALERTA IVE
        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++ INSERCIONES EN LA BASE DE DATOS +++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $conexion->autocommit(false);
        try {
            $camp_numcom = getnumcom($idusuario, $conexion);

            $cuotaingreso = (isset($inputs[4]) && $inputs[4] != null && $inputs[4] != 0 && $tipotransaccion == "D") ? $inputs[4] : 0;
            // Preparar la primera consulta para INSERT ahommov
            $res = $conexion->prepare("INSERT INTO `aprmov`(`ccodaport`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,`cuota_ingreso`,`lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`cestado`,`auxi`,`created_at`,`created_by`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?,'0', '0', ?,?, 'N', ?, ?, ?, ?,1, ?, ?,?)");

            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux, '0']);
                $conexion->rollback();
                return;
            }
            $ultimonum = ($ultimonum + 1);
            $ultimocorrel = ($ultimocorrel + 1);
            $res->bind_param('ssssssisddiisssss', $cuenta, $fechaoperacion, $tipotransaccion, $numdoc, $tipo_documento, $razon, $nlibreta, $nocheque, $monto, $cuotaingreso, $ultimonum, $ultimocorrel, $hoy, $idusuario, $auxiliar, $hoy, $idusuario);
            $res->execute();

            // Preparar la segunda consulta para INSERT ctbdiario
            $camp_glosa = $razon . " DE APORTACIONES DE " . $nombre . " CON RECIBO NO. " . $numdoc . $desnumdocconta;
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
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux, '0']);
                $conexion->rollback();
                return;
            }
            $res->bind_param('iidd', $id_ctb_diario, $cuenta_tipo, $mondebe, $monhaber);
            $res->execute();
            // *********************************************** 
            $auxMonto = $monto;
            //SI HAY UN DEPOSITO DE CUOTA DE INGRESO INPUTS[4]
            $cuotaingreso = 0;
            if (isset($inputs[4]) && $inputs[4] != null && $inputs[4] != 0 && $tipotransaccion == "D") {
                $cuotaingreso = $inputs[4];
                $auxMonto = $cuotaingreso + $monto;
                $ccodtip = substr($cuenta, 6, 2);
                $resultado =  mysqli_query($conexion, "SELECT a.cuenta_aprmov AS cuenta FROM aprtip a WHERE ccodtip = '$ccodtip'");
                if ($resultado) {
                    $idNomenclatura = mysqli_fetch_assoc($resultado)['cuenta'];
                }
                $res = $conexion->prepare("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) 
                VALUES (?,' ',1,?,0,?)");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode([$aux, '0']);
                    $conexion->rollback();
                    return;
                }
                $res->bind_param('iid', $id_ctb_diario, $cuenta_cuotaingreso, $cuotaingreso);
                $res->execute();
            }
            //REGISTRO DE LA CUENTA DE CAJA O BANCOs
            $mondebe = ($tipotransaccion == "R") ? 0 : $auxMonto;
            $monhaber = ($tipotransaccion == "R") ? $auxMonto : 0;
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
            $res = $conexion->prepare("CALL apr_ordena_noLibreta(?, ?)");
            // $response = executequery($query, [$nlibreta, $cuenta], ['i', 's'], $conexion);
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux, '0']);
                $conexion->rollback();
                return;
            }
            $res->bind_param('is', $nlibreta, $cuenta);
            $res->execute();

            $res = $conexion->prepare("CALL apr_ordena_Transacciones(?)");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux, '0']);
                $conexion->rollback();
                return;
            }
            $res->bind_param('s', $cuenta);
            $res->execute();
            //-----FIN

            //calcular total 
            $total_ap2rcuo = ($cuotaingreso + $monto);

            //formatt
            $format_monto = new NumeroALetras();
            $decimal = explode(".", $total_ap2rcuo);
            $res = (isset($decimal[1]) == false) ? 0 : $decimal[1];
            $letras_total = ($format_monto->toMoney($decimal[0], 2, 'QUETZALES', '')) . " " . $res . "/100";

            //NUMERO EN LETRAS
            $format_monto = new NumeroALetras();
            $decimal = explode(".", $monto);
            $res = (isset($decimal[1]) == false) ? 0 : $decimal[1];
            $letras_monto = ($format_monto->toMoney($decimal[0], 2, 'QUETZALES', '')) . " " . $res . "/100";

            $particionfecha = explode("-", $fechaoperacion);

            $transaccion = $selects[4];

            if ($conexion->commit()) {
                $auxdes = ($tipotransaccion == "D") ? "Depósito a cuenta " . $cuenta : "Retiro a cuenta " . $cuenta;
                echo json_encode(['Datos ingresados correctamente', '1', $cuenta, number_format($monto, 2, '.', ','), date("d-m-Y", strtotime($fechaoperacion)), $numdoc, $auxdes, $nombre, utf8_decode($_SESSION['nombre']), utf8_decode($_SESSION['apellido']), $hoy, $letras_monto, $particionfecha[0], $particionfecha[1], $particionfecha[2], $dpi, $cuotaingreso, $transaccion, $total_ap2rcuo, $letras_total, $_SESSION['id'], $controlinterno]);
            } else {
                echo json_encode(['Error al ingresar: ', '0']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        //fin transaccion
        break;
    case 'cdaportmov2': {
            $inputs = $_POST["inputs"];
            $selects = $_POST["selects"];
            $inputsn = $_POST["inputsn"];
            $selectsn = $_POST["selectsn"];
            $archivos = $_POST["archivo"];
            $hoy = date("Y-m-d H:i:s");
            $hoy2 = date("Y-m-d");

            //DECLARACION DE VARIABLES
            $ccodcli = "";
            $nit = "";
            $flag = 0; //BANDERA PARA CONTROLAR SI TODOS LOS DATOS ESTAN BIEN PARA MANDAR A GUARDAR LOS DATOS
            $numchq = "";
            $tipchq = "";
            $numpartida = "";
            $transacciontipo = "";
            $camp_glosa = "";

            //COMPROBACION DEL ESTADO DEL MES CONTABLE
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


            ($archivos[2] == "R") ? $transacciontipo = "RETIRO" : $transacciontipo = "DEPOSITO";
            //obtener el datos para ingresar en el campo id_ctb_nomenclatura de la tabla ctb_mov
            list($id, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("aprctb", "id_tipo_doc", (tipocuenta(substr($inputs[0], 6, 2), "aprtip", "id_tipo", $conexion)), (get_id_tipdoc($selects[1], "aprtipdoc", $conexion)), $conexion);
            //validar si encontro un tipo de parametrizacion para el deposito
            if ($id == "X") {
                echo json_encode(['NO PUEDE REALIZAR EL ' . $transacciontipo . ' DEBIDO A QUE NO HA PARAMETRIZADO UNA CUENTA CONTABLE PARA ELLO', '0']);
                return;
            }

            if (validarcampo([$inputs[0]], "") == "1") {

                $datoscuenta = mysqli_query($conexion, "SELECT `nlibreta`,`ccodcli`,`num_nit` FROM `aprcta` WHERE `ccodaport`=$inputs[0]");
                $mensaje = "Cuenta de ahorro no existe";
                while ($da = mysqli_fetch_array($datoscuenta, MYSQLI_ASSOC)) {
                    $nlibreta = $da["nlibreta"];
                    $ccodcli = $da["ccodcli"];
                    $nit = $da["num_nit"];
                    $mensaje = "";
                    $ultimonum = lastnumlin($inputs[0], $nlibreta, "aprmov", "ccodaport", $conexion);
                    $ultimocorrel = lastcorrel($inputs[0], $nlibreta, "aprmov", "ccodaport", $conexion);
                }

                if ($mensaje == "") {
                    $shortname = "";
                    $compl_name = "";
                    $datacli =  mysqli_query($conexion, "SELECT `short_name`,`compl_name`, `no_identifica` FROM `tb_cliente` WHERE `idcod_cliente`='$ccodcli'");
                    while ($fil = mysqli_fetch_array($datacli, MYSQLI_ASSOC)) {
                        $shortname = (mb_strtoupper($fil["short_name"], 'utf-8'));
                        $compl_name = (mb_strtoupper($fil["compl_name"], 'utf-8'));
                        $dpi = $fil["no_identifica"];
                    }

                    $valido = validarcampo([$inputs[0], $inputs[2], $inputs[3]], ""); //validacion de los campos montos y numero de recibo si se ingresaron
                    if ($valido == "1") {
                        if ($inputs[3] > 0) {
                            //------traer el saldo de la cuenta
                            $monto = 0;
                            $saldo = 0;
                            $data = mysqli_query($conexion, "SELECT `monto`,`ctipope` FROM `aprmov` WHERE `ccodaport`='$inputs[0]' AND cestado!=2");
                            while ($row = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                                $tiptr = $row["ctipope"];
                                $monto = $row["monto"];
                                if ($tiptr == "R") {
                                    $saldo = $saldo - $monto;
                                }
                                if ($tiptr == "D") {
                                    $saldo = $saldo + $monto;
                                }
                            }
                            $saldo = round($saldo, 2);
                            //****fin saldo */
                            $inactivar = "";
                            if ($archivos[2] == "R") {
                                if ($inputs[3] > $saldo) {
                                    $flag = 0;
                                    echo json_encode(['El saldo disponible en la cuenta es menor al monto solicitado', '0']);
                                } else {
                                    $flag = 1;
                                    if ($inputs[3] == $saldo) {
                                        $inactivar = ", `estado` = 'B',`fecha_cancel` = '" . $hoy . "'"; //SE AGREGA A LA ACTUALIZACION DEL APRCTA LA INACTIVACION DE LA CUENTA: ESTADO B
                                    }
                                }
                            } else {
                                $flag = 1;
                            }

                            //verificar tipo de documento con que se realiza la transaccion
                            switch ($selects[1]) {
                                case "E":

                                    break;
                                case "P":
                                    $numpartida = $inputs[4];
                                    break;
                                case "C":
                                    $numchq = $inputs[7];
                                    $tipchq = $selects[2];
                                    break;
                            }

                            if ($flag == 1) {
                                //si todo esta bien, se guardan los registros
                                //llamar al metodo numcom
                                $camp_numcom = getnumcom($archivos[0], $conexion);
                                //CREAR LA GLOSA

                                if ($transacciontipo == "DEPOSITO") {
                                    $camp_glosa .= glosa_obtenerMovimiento(0);
                                }
                                if ($transacciontipo == "RETIRO") {
                                    $camp_glosa .= glosa_obtenerMovimiento(1);
                                }
                                $camp_glosa .= glosa_obtenerEspacio();
                                $camp_glosa .= glosa_obtenerConector(0);
                                $camp_glosa .= glosa_obtenerEspacio();
                                $camp_glosa .= glosa_obtenerTipoModulo(1); //deposito o ahorro de aportacion
                                $camp_glosa .= glosa_obtenerEspacio();
                                $camp_glosa .= glosa_obtenerConector(0);
                                $camp_glosa .= glosa_obtenerEspacio();
                                $camp_glosa .= glosa_obtenerNomCliente($ccodcli, $conexion); //cliente
                                $camp_glosa .= glosa_obtenerEspacio();
                                $camp_glosa .= glosa_obtenerRecibo($inputs[2]);

                                $conexion->autocommit(false);
                                try {
                                    $conexion->query("INSERT INTO `aprmov`(`ccodaport`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,`lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`created_by`,`created_at`,`cestado`) VALUES ('$inputs[0]','$inputs[1]','$archivos[2]','$inputs[2]','$selects[1]','$transacciontipo', $nlibreta,'$numchq','$tipchq','$numpartida',$inputs[3],'N',$ultimonum+1,$ultimocorrel+1,'$hoy','$archivos[0]','$archivos[0]','$hoy','1')");
                                    $aux = mysqli_error($conexion);
                                    if ($aux) {
                                        echo json_encode(['Error al insertar los movimientos:' . $aux, '0']);
                                        $conexion->rollback();
                                        return;
                                    }
                                    $conexion->query("UPDATE `aprcta` SET `fecha_ult` = '$hoy2',`correlativo` = $ultimocorrel+1,`numlinea` = $ultimonum+1 " . $inactivar . " WHERE `ccodaport` = '$inputs[0]'");
                                    $aux = mysqli_error($conexion);
                                    if ($aux) {
                                        echo json_encode(['Error al momento actualizar la cuenta:' . $aux, '0']);
                                        $conexion->rollback();
                                        return;
                                    }
                                    //SE HACE UN REGISTRO EN CONTABILIDAD, EN LA TABLA "ctb_diario"
                                    $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$camp_numcom',3,1,'$inputs[2]', '$camp_glosa','$inputs[1]', '$inputs[1]','$inputs[0]','$archivos[0]','$hoy',1)");
                                    $aux = mysqli_error($conexion);
                                    if ($aux) {
                                        echo json_encode(['Error al insertar en diario:' . $aux, '0']);
                                        $conexion->rollback();
                                        return;
                                    }
                                    //-----FIN

                                    //SE HACE 2 REGISTROS EN CONTABILIDAD EN LA TABLA "ctb_mov"
                                    $id_ctb_diario = get_id_insertado($conexion);
                                    $auxMonto = ((isset($inputs[7]) && $inputs[7] != null) ? ($inputs[3] + $inputs[7]) : $inputs[3]);

                                    if ($transacciontipo == "DEPOSITO") {
                                        $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta1, '$auxMonto',0)");
                                        $aux = mysqli_error($conexion);
                                        if ($aux) {
                                            echo json_encode(['Error al insertar en contabilidad 1:' . $aux, '0']);
                                            $conexion->rollback();
                                            return;
                                        }
                                        $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta2, 0,'$inputs[3]')");
                                        if ($aux) {
                                            echo json_encode(['Error al insertar en contabilidad 2:' . $aux, '0']);
                                            $conexion->rollback();
                                            return;
                                        }
                                        // *********************************************** 

                                        if (isset($inputs[7]) && $inputs[7] != null) {
                                            $ccodtip = substr($inputs[0], 6, 2);
                                            $resultado =  mysqli_query($conexion, "SELECT a.cuenta_aprmov AS cuenta FROM aprtip a WHERE ccodtip = '$ccodtip'");
                                            if ($resultado) {
                                                $idNomenclatura = mysqli_fetch_assoc($resultado)['cuenta'];
                                            }

                                            $resultado1 =  mysqli_query($conexion, "INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idNomenclatura, 0,'$inputs[7]')");
                                            if (!$resultado1) {
                                                echo json_encode(['Error al insertar en contabilidad 2.2:' . $aux, '0']);
                                                $conexion->rollback();
                                                return;
                                            }
                                        }
                                    }
                                    if ($transacciontipo == "RETIRO") {
                                        $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta2, '$inputs[3]',0)");
                                        if ($aux) {
                                            echo json_encode(['Error al insertar en contabilidad 1:' . $aux, '0']);
                                            $conexion->rollback();
                                            return;
                                        }
                                        $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta1, 0,'$inputs[3]')");
                                        if ($aux) {
                                            echo json_encode(['Error al insertar en contabilidad 1:' . $aux, '0']);
                                            $conexion->rollback();
                                            return;
                                        }
                                    }
                                    // ORDENAMIENTO DE TRANSACCIONES
                                    mysqli_query($conexion, "CALL ahom_ordena_noLibreta('$nlibreta', '$inputs[0]')");
                                    mysqli_query($conexion, "CALL ahom_ordena_Transacciones('$inputs[0]')");
                                    //-----FIN

                                    if ($conexion->commit()) {
                                        //NUMERO EN LETRAS
                                        $format_monto = new NumeroALetras();
                                        $decimal = explode(".", $inputs[3]);
                                        $res = (isset($decimal[1]) == false) ? 0 : $decimal[1];
                                        $letras_monto = ($format_monto->toMoney($decimal[0], 2, 'QUETZALES', '')) . " " . $res . "/100";
                                        $particionfecha = explode("-", $inputs[1]);

                                        ($archivos[2] == "D") ? $archivos[2] = "Depósito a cuenta " . $inputs[0] : $archivos[2] = "Retiro a cuenta " . $inputs[0];
                                        echo json_encode(['Datos ingresados correctamente', '1', $inputs[0], number_format($auxMonto, 2, '.', ','), date("d-m-Y", strtotime($inputs[1])), $inputs[2], $archivos[2], $shortname, utf8_decode($_SESSION['nombre']), utf8_decode($_SESSION['apellido']), $hoy, $letras_monto, $particionfecha[0], $particionfecha[1], $particionfecha[2], $dpi]);
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
                            echo json_encode(["Ingrese un monto correcto", '0']);
                        }
                    } else {
                        echo json_encode([$valido, '0']);
                    }
                } else {
                    echo json_encode([$mensaje, '0']);
                }
            } else {
                echo json_encode(["Ingrese una cuenta", '0']);
            }
            mysqli_close($conexion);
        }
        break;

        //CRUD - CAMBIO DE LIBRETA
        //-----CREATE NUEVA LIBRETA
    case 'cambiar_libreta': {
            $inputs = $_POST["inputs"];
            $inputsn = $_POST["inputsn"];  // 
            $archivos = $_POST["archivo"];
            $hoy = date("Y-m-d H:i:s");
            $hoy2 = date("Y-m-d");
            $validar = validarcampo($inputs, "");
            if ($validar == "1") {
                if ($inputs[1] < 1) {
                    echo json_encode(['Ingrese un número de libreta válido', '0']);
                } else {
                    //------traer el saldo de la cuenta
                    $monto = 0;
                    $saldo = 0;
                    $transac = mysqli_query($conexion, "SELECT `monto`,`ctipope` FROM `aprmov` WHERE `ccodaport`='$archivos[0]' AND cestado!=2");
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
                        $ultimonum = lastnumlin($inputs[0], $archivos[1], "aprmov", "ccodaport", $conexion);
                        $ultimocorrel = lastcorrel($inputs[0], $archivos[1], "aprmov", "ccodaport", $conexion);
                        //desactivar en aprlib los datos de la antigua libreta
                        $conexion->query("UPDATE `aprlib` SET `estado` = 'B',`date_fin` = '$hoy' WHERE `ccodaport` = '$inputs[0]' AND `nlibreta`=  $archivos[1]");
                        //creacion de nueva libreta en aprlib
                        $conexion->query("INSERT INTO `aprlib`(`nlibreta`,`ccodaport`,`estado`,`date_ini`,`ccodusu`,`crazon`) VALUES ('$inputs[1]','$inputs[0]','A','$hoy2','$archivos[2]','maxlin')");
                        //insertar en aprmov para traer el saldo anterior de la libreta pasada
                        //registro por cambio de libreta
                        $conexion->query("INSERT INTO `aprmov`(`ccodaport`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,`lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`) VALUES ('$inputs[0]','$hoy2','R','LIB0001','E','CAMBIO LIBRETA', $archivos[1],'','','',$saldo,'N',$ultimonum+1,$ultimocorrel+1,'$hoy','$archivos[2]')");
                        //registro por saldo inicial en la nueva libreta
                        $conexion->query("INSERT INTO `aprmov`(`ccodaport`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,`lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`) VALUES ('$inputs[0]','$hoy2','D','LIB0001','E','SALDO INI', $inputs[1],'','','',$saldo,'N',1,$ultimocorrel+2,'$hoy','$archivos[2]')");
                        //actualizar en aprcta
                        $conexion->query("UPDATE `aprcta` SET `nlibreta` = '$inputs[1]',`numlinea` = 1,`correlativo` = $ultimocorrel+2 WHERE `ccodaport` = '$inputs[0]'");

                        if ($conexion->commit()) {
                            echo json_encode(['Cambio de libreta satisfactorio', '1']);
                        } else {
                            echo json_encode(['Error al intentar cambiar la libreta: ', '0']);
                        }
                    } catch (Exception $e) {
                        $conexion->rollback();
                        echo json_encode(['Error al intentar cambiar la libreta: ' . $e->getMessage(), '0']);
                    }
                    //fin transaccion
                }
            } else {
                echo json_encode([$validar, '0']);
            }
            mysqli_close($conexion);
        }
        break;
        //CRUD - BENEFICIARIOS
        //-----CREATE
    case 'create_apr_ben': {
            $hoy = date("Y-m-d");
            $inputs = $_POST["inputs"];
            $selects = $_POST["selects"];
            $inputsn = $_POST["inputsn"];
            $selectsn = $_POST["selectsn"];
            $archivos = $_POST["archivo"];
            $consulta2 = mysqli_query($conexion, "SELECT * FROM `aprben` WHERE `codaport`='$archivos[0]'");
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
                                        $conexion->query("INSERT INTO `aprben`(`codaport`,`nombre`,`dpi`,`direccion`,`codparent`,`fecnac`,`porcentaje`,`telefono`) VALUES ('$archivos[0]','$inputs[0]','$inputs[1]','$inputs[2]','$selects[0]','$inputs[4]','$inputs[5]','$inputs[3]')");
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
                echo json_encode(['Seleccione primeramente una cuenta de aportación', '0']);
            }
            mysqli_close($conexion);
        }
        break;
        //-----ACTUALIZAR
    case 'update_apr_ben': {
            $hoy = date("Y-m-d");


            $inputs = $_POST["inputs"];
            $selects = $_POST["selects"]; //selects datos// 
            $archivos = $_POST["archivo"];

            $consulta2 = mysqli_query($conexion, "SELECT * FROM `aprben` WHERE `codaport`='$archivos[0]'");
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
                            $conexion->query("UPDATE `aprben` SET `nombre` = '$inputs[0]',`dpi` = '$inputs[1]',`direccion` = '$inputs[2]',`codparent` = $selects[0],`fecnac` = '$inputs[4]',`porcentaje` = $inputs[5],`telefono` = '$inputs[3]' WHERE `id_ben` = $inputs[7]");
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
        //-----ELIMINAR
    case 'delete_apr_ben': {
            $idaprben = $_POST["ideliminar"];
            $eliminar = "DELETE FROM aprben WHERE id_ben =" . $idaprben;
            if (mysqli_query($conexion, $eliminar)) {
                echo json_encode(['Eliminacion correcta ', '1']);
            } else {
                echo json_encode(['Error al eliminar ', '0']);
            }
            mysqli_close($conexion);
        }
        break;
        //-----LISTADO DE BENEFICIARIOS DE 1 CLIENTE
    case 'lista_beneficiarios': {
            $id = $_POST["l_codaport"];
            $consulta2 = mysqli_query($conexion, "SELECT * FROM `aprben` WHERE `codaport`='$id'");
            //se cargan los datos de las beneficiarios a un array
            $array_beneficiarios[] = [];
            $array_parenteco[] = [];
            $total = 0;
            $i = 0;
            while ($fila = mysqli_fetch_array($consulta2, MYSQLI_ASSOC)) {
                $array_beneficiarios[$i] = $fila;
                $array_beneficiarios[$i]['pariente'] = parenteco(utf8_encode($fila["codparent"]));
                $benporcent = utf8_encode($fila["porcentaje"]);
                $total = $total + $benporcent;
                $i++;
            }
            echo json_encode([$array_beneficiarios, $total]);
        }
        break;

        //REPORTES
        //-----REPORTE DE ESTADOS DE CUENTA DE CLIENTE
    case 'reporte_estado_cuenta_aprt': {
            $inputs = $_POST["inputs"];
            $archivos = $_POST["archivo"];
            $radioss = $_POST["radios"];
            $radiosn = $_POST["radiosn"];
            $tipo_doc = $_POST["id"];

            //validar si ingreso un cuenta de aportacion
            if ($inputs[0] == "" && $inputs[1] == "") {
                echo json_encode(["Debe cargar una cuenta de aportación", '0']);
                return;
            }

            //validar si la cuenta de ahorro existe
            $datoscli = mysqli_query($conexion, "SELECT * FROM `aprcta` WHERE `ccodaport`=$inputs[0]");
            $bandera = true;
            while ($da = mysqli_fetch_array($datoscli, MYSQLI_ASSOC)) {
                $bandera = false;
            }
            if ($bandera) {
                echo json_encode(["Debe cargar una cuenta de aportación válida", '0']);
                return;
            }

            //validaciones de fechas
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
            echo json_encode(["reportes_aportaciones", "estado_cuenta_aprt", $tipo_doc, $formato, date("d-m-Y"), $inputs[0], $inputs[2], $inputs[3], $radioss[0], $archivos[0], $archivos[1]]);
        }
        break;
        //-----REPORTE DE CUENTAS ACTIVAS E INACTIVAS
    case 'reporte_cuentas_act_inact_aprt': {
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
            echo json_encode(["reportes_aportaciones", "listado_cuentas_aprt", $tipo_doc, $formato, date("d-m-Y"), $radioss[0], $radioss[1], $selects[0], $archivos[0], $archivos[1]]);
        }
        break;
        //-----REPORTE DEL LISTADO DEL DIA
    case 'reporte_listado_dia': {
            $inputs = $_POST["inputs"];
            $archivos = $_POST["archivo"];
            $radioss = $_POST["radios"];
            $selects = $_POST["selects"];
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
            echo json_encode(["reportes_aportaciones", "listado_dia_aprt", $tipo_doc, $formato, date("d-m-Y"), $inputs[0], $inputs[1], $radioss[0], $radioss[1], $radioss[2], $selects[0], $archivos[0], $archivos[1]]);
        }
        break;
        //-----REPORTE DE SALDOS DE TIPOS DE CUENTAS
    case 'reporte_saldos_de_cuenta': {

            $inputs = $_POST["inputs"];
            $archivos = $_POST["archivo"];
            $radioss = $_POST["radios"];
            $selects = $_POST["selects"];
            $tipo_doc = $_POST["id"];
            $hoy = date("Y-m-d");

            //validar si selecciono una cuenta
            if ($selects[0] == "0") {
                echo json_encode(["Debe seleccionar un tipo de cuenta", '0']);
                return;
            }
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
            echo json_encode(["reportes_aportaciones", "saldos_por_cuenta", $tipo_doc, $formato, date("d-m-Y"), $inputs[0], $radioss[0], $selects[0], $archivos[0], $archivos[1]]);
        }
        break;
    case 'cuadre_de_diario': {
            $inputs = $_POST["inputs"];
            $archivos = $_POST["archivo"];
            $radioss = $_POST["radios"];
            $selects = $_POST["selects"];
            $tipo_doc = $_POST["id"];

            //si hay un error en el ingreso de datos
            if ($radioss[0] == "2") {
                if ($selects[0] == "0") {
                    echo json_encode(["Debe seleccionar un tipo de cuenta", '0']);
                    return;
                }
            }

            if ($radioss[0] == "1") {
                if ($selects[0] != "0") {
                    echo json_encode(["Error en su solicitud", '0']);
                    return;
                }
            }

            $fecha_actual = strtotime(date("Y-m-d"));
            $fecha_1 = strtotime($inputs[0]);
            $fecha_2 = strtotime($inputs[1]);

            if ($radioss[1] == "2") {
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

            if ($radioss[1] == "1") {
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
            echo json_encode(["reportes_aportaciones", "cuadre_diario_aprt", $tipo_doc, $formato, date("d-m-Y"), $inputs[0], $inputs[1], $radioss[0], $radioss[1], $selects[0], $archivos[0], $archivos[1]]);
        }
        break;

        //CRUD - CERTIFICADOS DE APORTACION
        //-----CREATE
    case 'create_certificado_aprt': {
            // `certif_n`,`ccodaport`,`codcli`,`nit`,`monapr_n`,`fecaper`,`norecibo`
            $hoy = date("Y-m-d H:i:s");
            $hoy2 = date("Y-m-d");
            $inputs = $_POST["inputs"];
            $archivo = $_POST["archivo"];

            //valida si ingreso un numero de certificado
            $validacion2 = validarcampo([$inputs[2]], "");
            if ($validacion2 != "1") {
                echo json_encode(["Debe seleccionar una cuenta", '0']);
                return;
            }

            //validacion de los inputs si estan vacios
            $validacion = validarcampo($inputs, "");
            if ($validacion != "1") {
                echo json_encode([$validacion, '0']);
                return;
            }

            $validar_monto = validar_limites(1, 1000000, $inputs[4]);

            //VALIDACION DE MONTO
            if ($validar_monto != "1") {
                echo json_encode(["Campo monto: " . $validar_monto, '0']);
                return;
            }

            //validar lo de si tiene un beneficiario
            if ($archivo[3] == null) {
                echo json_encode(["No puede generar el certificado debido a que no tiene al menos un beneficiario", '0']);
                return;
            }

            //validar lo de si tiene el porcentaje de beneficiario al 100%
            if ($archivo[4] != "100") {
                echo json_encode(["No puede generar el certificado debido a que el porcentaje de beneficiario es diferente del 100%", '0']);
                return;
            }

            //inicio transaccion
            $conexion->autocommit(false);
            try {
                $conexion->query("INSERT INTO `aprcrt`(`ccodcrt`,`ccodcli`,`ccodaport`,`montoapr`,`norecibo`,`fec_crt`,`codusu`) 
                                 VALUES ('$inputs[0]','$inputs[2]','$inputs[1]',$inputs[4],$inputs[6],'$hoy','$archivo[2]')");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error al insertar el certificado: ' . $aux, '0']);
                    $conexion->rollback();
                    return;
                }
                $conexion->commit();
                echo json_encode(['Registro ingresado correctamente', '1']);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
            }
            mysqli_close($conexion);
        }
        break;
        //-----ACTUALIZAR CERTIFICADO
    case 'update_certificado_aprt': {
            $hoy = date("Y-m-d H:i:s");
            $inputs = $_POST["inputs"];
            $archivo = $_POST["archivo"];

            //validacion de los inputs si estan vacios
            $validacion = validarcampo($inputs, "");
            if ($validacion != "1") {
                echo json_encode([$validacion, '0']);
                return;
            }

            $validar_monto = validar_limites(1, 1000000, $inputs[0]);

            //validar si el porcentaje de beneficiarios es del 100%
            if ($archivo[2] != "100") {
                echo json_encode(["No puede actualizar el certificado debido a que el porcentaje de beneficiario es diferente del 100%", '0']);
                return;
            }

            //inicio transaccion
            $conexion->autocommit(false);
            try {
                $conexion->query("UPDATE `aprcrt` SET `montoapr`=$inputs[0], `fec_mod`='$hoy',`codusu`='$archivo[1]' WHERE aprcrt.id_crt = '$archivo[0]'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error al actualizar certificado: ' . $aux, '0']);
                    $conexion->rollback();
                    return;
                }
                $conexion->commit();
                echo json_encode(['Registro actualizado correctamente', '1']);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
            }
            mysqli_close($conexion);
        }
        break;
        //-----CREAR PDF DE CERTIFICADO
    case 'pdf_certificado_aprt': {
            $idcrt = $_POST["idcrt"];
            $estado = $_POST["estado"];
            $newcod = $_POST["newcod"];
            $hoy = date("Y-m-d H:i:s");
            $hoy2 = date("d-m-Y");
            $codusu = $_POST["codusu"];



            // devolver la consulta con todos los datos requeridos
            // obtener el nombre del cliente
            $consulta = mysqli_query($conexion, "SELECT cl.short_name, crt.ccodcrt, crt.montoapr, crt.estado ,cl.no_identifica,crt.norecibo,crt.ccodaport,cl.control_interno
            FROM aprcrt AS crt 
            INNER JOIN tb_cliente AS cl 
            ON crt.ccodcli = cl.idcod_cliente
            WHERE crt.id_crt='$idcrt'");

            $consulta2 = mysqli_query($conexion, "SELECT ben.nombre 
            FROM aprcrt AS crt 
            INNER JOIN aprben AS ben 
            ON crt.ccodaport = ben.codaport
            WHERE crt.id_crt='$idcrt'");

            //se cargan los datos de las beneficiarios a un array
            $array_beneficiarios[] = [];
            while ($fila = mysqli_fetch_array($consulta2, MYSQLI_ASSOC)) {
                $array_beneficiarios[] = $fila;
            }

            //se carga el dato del cliente a una variable normal
            while ($valor = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                $cliente = utf8_encode($valor['short_name']);
                $codcertificado = utf8_encode($valor['ccodcrt']);
                $ccodaport = ($valor['ccodaport']);
                $norecibo = ($valor['norecibo']);
                $controlinterno = ($valor['control_interno']);
                $monto_cert = utf8_encode($valor['montoapr']);
                $estado_aux = utf8_encode($valor['estado']);
                $dpi_cli  = utf8_encode($valor['no_identifica']);
            }
            // $estado = "";
            //convertir monto a letras
            $format_monto = new NumeroALetras();
            $texto_monto = $format_monto->toMoney($monto_cert, 2, 'QUETZALES', 'CENTAVOS');
            //se valida la reimpresion y se crea una bitacora
            if ($estado_aux == null || $estado_aux == "") {
                //actualizar la tabla de crt
                $conexion->query("UPDATE `aprcrt` SET `estado` = 'I', `fec_mod` = '$hoy', `codusu` = '$codusu' WHERE aprcrt.id_crt = '$idcrt'");
            } else if (($estado_aux == "I" || $estado_aux = "R") && ($estado == "I")) {
                $conexion->query("UPDATE `aprcrt` SET `fec_mod` = '$hoy', `codusu` = '$codusu' WHERE aprcrt.id_crt = '$idcrt'");
                $estado = "R";
            } else if (($estado_aux = "I" || $estado_aux = "R") && ($estado == "R")) {
                //actualizar la tabla de crt
                $conexion->query("UPDATE `aprcrt` SET `estado` = 'R', `ccodcrt`='$newcod', `fec_mod` = '$hoy', `codusu` = '$codusu' WHERE `id_crt` = '$idcrt'");
                $codcertificado = $newcod;
            }


            //enviar datos de respuesta
            echo json_encode([$array_beneficiarios, $cliente, $hoy2, $codcertificado, $monto_cert, $texto_monto, $estado, $dpi_cli, $norecibo, $ccodaport, $controlinterno]);
            mysqli_close($conexion);
        }
        break;

        //CRUD - INTERESES APORTACIONES
        //-----CALCULAR O PROCESAR INTERESES
    case 'procesar_interes_aprt': {
            $inputs = $_POST["inputs"];
            $selects = $_POST["selects"];
            $radios = $_POST["radios"];
            $archivo = $_POST["archivo"];
            $codusu = $archivo[1];
            $hoy = date("Y-m-d");
            $hoy2 = date("Y-m-d H:i:s");
            //validar el filtro por tipo de cuenta
            if ($selects[0] == "0" && $radios[0] == "any") {
                echo json_encode(['Seleccionar tipo de cuenta', '0']);
                return;
            }
            //validar que la fecha inicial no sea mayor que la final, pero la fecha final puede ser mayor que la de hoy
            if ($inputs[1] <= $inputs[0] && $radios[1] == "frango") {
                echo json_encode(['La fecha inicial no puede ser mayor o igual que la final', '0']);
                return;
            }
            //se obtienen los valores desde los campos de fecha
            $fini = $inputs[0];
            $ffin = $inputs[1];

            //ARMANDO LA CONSULTA
            $condicion = "";
            $bandera = 0;
            if ($radios[0] == "any") {
                $condicion = $condicion . " SUBSTR(`ccodaport`,7,2) =" . $selects[0];
                $tipocuenta = $selects[0];
                $bandera = 1;
            } else {
                $tipocuenta = "Todos";
            }

            $and = "";
            if ($bandera == 1) {
                $and = " AND ";
            }
            $wherefecha = "";

            if ($radios[1] == "frango") {
                $rango = "" . date("d-m-Y", strtotime($fini)) . "_" . date("d-m-Y", strtotime($ffin));
                $wherefecha = " AND dfecope<='" . $ffin . "'";
            } else {
                $rango = "Todo";
                $ffin = $hoy;
                $date = new DateTime('2000-01-01');
                $fini = $date->format('Y-m-d');
            }

            $where = " WHERE SUBSTR(`ccodaport`,4,3)=" . $_SESSION['agencia'];
            if ($bandera > 0) {
                $where = " WHERE SUBSTR(`ccodaport`,4,3)=" . $_SESSION['agencia'] . " AND ";
            }
            //-------------------------INICIO DE PROCESO
            //query pa los movimientos
            $array[] = [];
            $i = 0;
            $consulta = "SELECT * FROM data_calc_apr " . $where . $condicion . $wherefecha." ORDER BY ccodaport,dfecope";
            // echo json_encode(['Error: ' . $consulta, '0']);
            // return;
            $cuenta = "X";
            $aprmov = mysqli_query($conexion, $consulta);
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode(['Error: ' . $aux, '0']);
                return;
            }
            $bandera = "Sin registros";
            while ($row = mysqli_fetch_array($aprmov, MYSQLI_ASSOC)) {
                $ccodaport = utf8_encode($row["ccodaport"]);
                $array[$i] = $row;
                if ($ccodaport != $cuenta && $cuenta != "X") {
                    $array[$i - 1]["ult"] = 1; //se agrega en ult del array 1 si es el fin de transacciones de una cuenta
                }
                $array[$i]["ult"] = 0;
                $cuenta = $ccodaport;
                $i++;
                $bandera = "";
            }
            //si no hay movimiento o registro en data_calc_apr
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
            //[`fechaInicio`,`fechaFinal`],[`tipcuenta`],[`r_cuenta`,`r_fecha`],`procesar_interes_aprt`,`0`
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
            //Manda un mensaje sino hay movimiento que puedan ser guardados
            if ($bandera != "") {
                echo json_encode([$bandera, '0']);
                return;
            }
            //----------
            $query = mysqli_query($conexion, "INSERT INTO `aprinteredetalle`(tipo,rango,partida,acreditado,int_total,isr_total,fecmod,codusu,fechacorte) VALUES ('" . $tipocuenta . "','" . $rango . "',0,0," . $totalint . "," . $totalisr . ",'" . $hoy2 . "','" . $codusu . "','" . $ffin . "')");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$tipocuenta . 'Error al insertar en detalle de intereses 1:' . $aux, '0']);
                $conexion->rollback();
                return;
            }
            if ($query) {
                $queryult = mysqli_query($conexion, "SELECT MAX(id) AS id FROM aprinteredetalle");
                $identificador = 0;
                while ($row = mysqli_fetch_array($queryult, MYSQLI_ASSOC)) {
                    $identificador = utf8_encode($row["id"]);
                }
                $conexion->autocommit(false);
                try {
                    foreach ($paqueton as $pac) {
                        $conexion->query("INSERT INTO `aprintere`(`ccodaport`,`codcli`,`nomcli`,`tipope`,`fecope`,`numdoc`,`tipdoc`,`monto`,`saldo`,`saldoant`,`dias`,`tasa`,`intcal`,`isrcal`,`idcalc`) 
                                VALUES ('" . $pac['ccodaport'] . "','" . $pac['ccodcli'] . "','" . $pac['short_name'] . "','" . $pac['ctipope'] . "','" . $pac['dfecope'] . "','" . $pac['cnumdoc'] . "','" . $pac['ctipdoc'] . "'," . $pac['monto'] . ",
                                " . $pac['saldo'] . "," . $pac['saldoant'] . "," . $pac['dias'] . "," . $pac['tasa'] . "," . $pac['interescal'] . "," . $pac['isr'] . "," . $identificador . ")");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode(['Error al insertar en detalle de interes 2:' . $aux, '0']);
                            $conexion->rollback();
                            return;
                        }
                    }
                    $conexion->commit();
                    echo json_encode(['Correcto, proceso finalizado correctamente', '1']);
                } catch (Exception $e) {
                    $conexion->rollback();
                    echo json_encode(['Error al Registrar: ' . $e->getMessage(), '0']);
                }
            } else {
                echo json_encode(['Error al ingresar ', '0']);
            }

            //fin guardado
            mysqli_close($conexion);
        }
        break;
        //-----ACREDITAR INTERESES
    case 'acreditar_intereses': {
            $archivo = $_POST["archivo"];
            $hoy = date("Y-m-d H:i:s");
            $id = $archivo[0];
            $fechacorte = $archivo[1];
            $codofi = $archivo[2];
            $usu = $archivo[3];
            $rango = $archivo[4];

            $campo_glosa1 = "";
            $campo_glosa2 = "";

            //------validar si existen todas las parametrizaciones correctas para realizar la acreditacion
            $consulta4 = "SELECT cta.ccodtip AS grupo, tip.nombre 
            FROM aprintere AS apint
            INNER JOIN aprcta AS cta ON cta.ccodaport=apint.ccodaport 
            INNER JOIN aprtip AS tip ON cta.ccodtip=tip.ccodtip 
            WHERE apint.idcalc=" . $id . "
            GROUP BY cta.ccodtip";
            $data4 = mysqli_query($conexion, $consulta4);

            while ($row = mysqli_fetch_array($data4, MYSQLI_ASSOC)) {
                $val_tipcuenta = $row["grupo"];
                $val_nombre = $row["nombre"];
                //obtener el datos para ingresar en el campo id_ctb_nomenclatura de la tabla ctb_mov
                list($id1, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("aprparaintere", "id_descript_intere", (tipocuenta($val_tipcuenta, "aprtip", "id_tipo", $conexion)), (1), $conexion);
                //validar si encontro un tipo de parametrizacion para el interes
                if ($id1 == "X") {
                    echo json_encode(['NO PUEDE REALIZAR LA ACREDITACIÓN DEBIDO A QUE NO HA PARAMETRIZADO UNA CUENTA CONTABLE PARA EL TIPO DE CUENTA ' . $val_nombre . ' EN RELACIÓN AL INTERES', '0']);
                    return;
                }
                list($id1, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("aprparaintere", "id_descript_intere", (tipocuenta($val_tipcuenta, "aprtip", "id_tipo", $conexion)), (2), $conexion);
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
                $data3 = mysqli_query($conexion, "SELECT `acreditado` FROM `aprinteredetalle` WHERE id='$id'");
                while ($row = mysqli_fetch_array($data3, MYSQLI_ASSOC)) {
                    $acreditado = $row["acreditado"];
                }
                if ($acreditado == "1") {
                    echo json_encode(['Este campo ya ha sido acreditado', '1']);
                    return;
                }
                //COMPROBAR SI EL MES CONTABLE ESTA ABIERTO
                $cierre = comprobar_cierre($idusuario, $fechacorte, $conexion);
                if ($cierre[0] == 0) {
                    echo json_encode([$cierre[1], '0']);
                    return;
                }


                $conexion->query("UPDATE `aprinteredetalle` SET acreditado=1 WHERE id=" . $id);

                //CONSULTA PARA ACREDITACION POR CLIENTE
                $consulta = "SELECT apint.ccodaport,SUM(apint.intcal) AS totalint, SUM(apint.isrcal) AS totalisr, cta.nlibreta, cta.numlinea, cta.correlativo 
                FROM aprintere AS apint
                INNER JOIN aprcta AS cta ON cta.ccodaport=apint.ccodaport 
                WHERE apint.idcalc=" . $id . " 
                GROUP BY apint.ccodaport";
                $data = mysqli_query($conexion, $consulta);
                while ($row = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                    $ccodaport = utf8_encode($row["ccodaport"]);
                    $interes = utf8_encode($row["totalint"]);
                    $isr = utf8_encode($row["totalisr"]);
                    $libreta = utf8_encode($row["nlibreta"]);
                    $num = lastnumlin($ccodaport, $libreta, "aprmov", "ccodaport", $conexion);
                    $correl = lastcorrel($ccodaport, $libreta, "aprmov", "ccodaport", $conexion);

                    if ($interes > 0) {
                        //INSERTAR EN AHOMMOV
                        $conexion->query("INSERT INTO `aprmov`(`ccodaport`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,
                    `lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`auxi`)
                    VALUES ('$ccodaport','$fechacorte','D','INT3112','IN','INTERES', $libreta,'','','',$interes,'N',$num+1,$correl+1,'$hoy','$usu','INTERE" . $id . "')");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode(['Error al insertar en aprmov 1:' . $aux, '0']);
                            $conexion->rollback();
                            return;
                        }

                        $conexion->query("INSERT INTO `aprmov`(`ccodaport`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,
                    `lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`auxi`)
                    VALUES ('$ccodaport','$fechacorte','R','ISR3112','IP','INTERES', $libreta,'','','',$isr,'N',$num+2,$correl+2,'$hoy','$usu','INTERE" . $id . "')");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode(['Error al insertar en aprmov 2:' . $aux, '0']);
                            $conexion->rollback();
                            return;
                        }
                    }
                }
                //consulta para insertar datos en ctb_diario
                $consulta2 = "SELECT cta.ccodtip AS grupo,SUM(apint.intcal) AS totalint, SUM(apint.isrcal) AS totalisr, cta.nlibreta, cta.numlinea, cta.correlativo, tip.nombre 
                FROM aprintere AS apint
                INNER JOIN aprcta AS cta ON cta.ccodaport=apint.ccodaport 
                INNER JOIN aprtip AS tip ON cta.ccodtip=tip.ccodtip 
                WHERE apint.idcalc=" . $id . " 
                GROUP BY cta.ccodtip";

                $data2 = mysqli_query($conexion, $consulta2);
                //insercion en la tabla de dario
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
                        $aux = "APRT-" . $grupo;
                        $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$camp_numcom',3,1,'INT', '$campo_glosa1','$fechacorte', '$fechacorte','$aux','$usu','$hoy',1)");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode(['Error al insertar en diario:' . $aux, '0']);
                            $conexion->rollback();
                            return;
                        }
                        //INSERCION EN CTB_MOV PARA EL INTERES ACREDITADO
                        $id_ctb_diario = get_id_insertado($conexion); //obtener el ultimo id insertado
                        list($id1, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("aprparaintere", "id_descript_intere", (tipocuenta($grupo, "aprtip", "id_tipo", $conexion)), (1), $conexion);
                        $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta1, '$interes',0)");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode(['Error al insertar en movimientos contables 1:' . $aux, '0']);
                            $conexion->rollback();
                            return;
                        }
                        $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta2, 0,'$interes')");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode(['Error al insertar en movimientos contables 2:' . $aux, '0']);
                            $conexion->rollback();
                            return;
                        }

                        //INSERCIONES EN CTB_DIARIO - ISR ACREDITADO
                        //llamar al metodo numcom
                        $camp_numcom = getnumcom($usu, $conexion);
                        //insertar glosa de retencion de isr
                        $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$camp_numcom',3,1,'ISR', '$campo_glosa2','$fechacorte', '$fechacorte','$aux','$usu','$hoy',1)");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode(['Error al insertar en diario 2:' . $aux, '0']);
                            $conexion->rollback();
                            return;
                        }
                        //INSERCION EN CTB_MOV PARA EL ISR ACREDITADO
                        $id_ctb_diario = get_id_insertado($conexion); //obtener el ultimo id insertado
                        list($id1, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("aprparaintere", "id_descript_intere", (tipocuenta($grupo, "aprtip", "id_tipo", $conexion)), (2), $conexion);
                        $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta1, '$isr',0)");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode(['Error al insertar en movimientos contables 3:' . $aux, '0']);
                            $conexion->rollback();
                            return;
                        }
                        $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta2, 0,'$isr')");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode(['Error al insertar en movimientos contables 4:' . $aux, '0']);
                            $conexion->rollback();
                            return;
                        }
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
        }
        break;
        //-----APROVISIONAR INTERESES
    case 'partida_aprov_intereses': {
            $archivo = $_POST["archivo"];
            $hoy = date("Y-m-d H:i:s");
            $id = $archivo[0];
            $fechacorte = $archivo[1];
            $usu = $archivo[2];
            $rango = $archivo[3];

            $campo_glosa = "";

            //------validar si existen todas las parametrizaciones correctas para realizar la acreditacion
            $consulta4 = "SELECT cta.ccodtip AS grupo, tip.nombre 
            FROM aprintere AS apint
            INNER JOIN aprcta AS cta ON cta.ccodaport=apint.ccodaport 
            INNER JOIN aprtip AS tip ON cta.ccodtip=tip.ccodtip 
            WHERE apint.idcalc=" . $id . "
            GROUP BY cta.ccodtip";
            $data4 = mysqli_query($conexion, $consulta4);

            while ($row = mysqli_fetch_array($data4, MYSQLI_ASSOC)) {
                $val_tipcuenta = $row["grupo"];
                $val_nombre = $row["nombre"];
                //obtener el datos para ingresar en el campo id_ctb_nomenclatura de la tabla ctb_mov
                list($id1, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("aprparaintere", "id_descript_intere", (tipocuenta($val_tipcuenta, "aprtip", "id_tipo", $conexion)), (3), $conexion);
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
                //validacion de provision
                $data3 = mysqli_query($conexion, "SELECT `partida` FROM `aprinteredetalle` WHERE id='$id'");
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

                $conexion->query("UPDATE `aprinteredetalle` SET partida=1 WHERE id=" . $id);

                $consulta = "SELECT cta.ccodtip AS grupo,SUM(apint.intcal) AS totalint, SUM(apint.isrcal) AS totalisr, cta.nlibreta, cta.numlinea, cta.correlativo, tip.nombre 
                FROM aprintere AS apint
                INNER JOIN aprcta AS cta ON cta.ccodaport=apint.ccodaport 
                INNER JOIN aprtip AS tip ON cta.ccodtip=tip.ccodtip 
                WHERE apint.idcalc=" . $id . " 
                GROUP BY cta.ccodtip";
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
                        //INSERCIONES EN CTB_DIARIO - INTERES PROVISION
                        //llamar al metodo numcom
                        $camp_numcom = getnumcom($usu, $conexion);
                        //insertar glosa de provision
                        $aux = "APRT-" . $grupo;
                        $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$camp_numcom',3,1,'PROV', '$campo_glosa','$fechacorte', '$fechacorte','$aux','$usu','$hoy',1)");

                        //INSERCION EN CTB_MOV PARA EL INTERES ACREDITADO
                        $id_ctb_diario = get_id_insertado($conexion); //obtener el ultimo id insertado
                        list($id1, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("aprparaintere", "id_descript_intere", (tipocuenta($grupo, "aprtip", "id_tipo", $conexion)), (3), $conexion);
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
        }

        //CRUD - PARAMETRIZACION DE APORTACIONES
        //----CREATE
    case "create_aprt_cuentas_contables": {
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
            list($id1, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("aprctb", "id_tipo_doc", $selects[0], $selects[1], $conexion);
            //validar si encontro un tipo de parametrizacion para el interes
            if ($id1 != "X") {
                echo json_encode(['No puede agregar esta parametrizacion porque ya existe', '0']);
                return;
            }

            //se hara la insercion
            $conexion->autocommit(false);
            try {
                $conexion->query("INSERT INTO aprctb (id_tipo_cuenta,id_tipo_doc,id_cuenta1,id_cuenta2,dfecmod,codusu)
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
        //-----UPDATE
    case "update_aprt_cuentas_contables": {
            $hoy = date("Y-m-d H:i:s");
            $inputs = $_POST["inputs"];
            //validar input cuenta 1
            if ($inputs[1] == "0") {
                echo json_encode(['Debe seleccionar una cuenta contable', '0']);
                return;
            }
            //validar input cuenta 2
            if ($inputs[2] == "0") {
                echo json_encode(['Debe seleccionar una cuenta contable para la cuota de ingreso', '0']);
                return;
            }
            //se hara la actualizacion
            $conexion->autocommit(false);
            try {
                $conexion->query("UPDATE aprtip
                    SET id_cuenta_contable = $inputs[1],cuenta_aprmov = $inputs[2] WHERE id_tipo=$inputs[0]");
                $conexion->commit();
                echo json_encode(['Datos actualizados correctamente', '1']);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
            }
            mysqli_close($conexion);
        }
        break;
    case "update_aprt_cuentas_contablesanterior": {
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
            $id1 = get_ctb_nomenclatura2("aprctb", "id_tipo_doc", $selects[0], $selects[1], $archivos[1], $conexion);
            //validar si encontro un tipo de parametrizacion para el interes
            if ($id1 != "X") {
                echo json_encode(['No puede realizar esta actualizacion de parametrizacion porque ya existe', '0']);
                return;
            }

            //se hara la actualizacion
            $conexion->autocommit(false);
            try {
                $conexion->query("UPDATE aprctb
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
        //-----DELETE
    case "delete_aprt_cuentas_contables": {
            $id = $_POST["ideliminar"];
            $eliminar = "DELETE FROM aprctb WHERE id =" . $id;
            if (mysqli_query($conexion, $eliminar)) {
                echo json_encode(['Eliminacion correcta ', '1']);
            } else {
                echo json_encode(['Error al eliminar ', '0']);
            }
            mysqli_close($conexion);
        }
        break;
        //CRUD - PARAMETRIZACION DE INTERESES
        //----CREATE
    case "create_aprt_cuentas_intereses": {
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
            list($id1, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("aprparaintere", "id_descript_intere", $selects[0], $selects[1], $conexion);
            //validar si encontro un tipo de parametrizacion para el interes
            if ($id1 != "X") {
                echo json_encode(['No puede agregar esta parametrizacion porque ya existe', '0']);
                return;
            }

            //se hara la insercion
            $conexion->autocommit(false);
            try {
                $conexion->query("INSERT INTO aprparaintere (id_tipo_cuenta,id_descript_intere,id_cuenta1,id_cuenta2,dfecmod,id_usuario)
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
    case "update_aprt_cuentas_intereses": {
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
            $id1 = get_ctb_nomenclatura2("aprparaintere", "id_descript_intere", $selects[0], $selects[1], $archivos[1], $conexion);
            //validar si encontro un tipo de parametrizacion para el interes
            if ($id1 != "X") {
                echo json_encode(['No puede realizar esta actualizacion de parametrizacion porque ya existe', '0']);
                return;
            }

            //se hara la actualizacion
            $conexion->autocommit(false);
            try {
                $conexion->query("UPDATE aprparaintere
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
    case "delete_aprt_cuentas_intereses": {
            $id = $_POST["ideliminar"];
            $eliminar = "DELETE FROM aprparaintere WHERE id =" . $id;
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

            $ccodaport = "";
            $ctipope_aux = "";
            $cnumdoc_aux = "";
            $tipotransaccion = "";
            $camp_glosa = "";
            $fechaaux4 = "";
            $usuario4 = "";

            //consultar datos del aprmov con id recibido
            $data_aprmov = mysqli_query($conexion, "SELECT `ccodaport`,`ctipope`,`cnumdoc`,`ctipdoc`,`monto`, CAST(`created_at` AS DATE) AS created_at, created_by,`dfecope` FROM `aprmov` WHERE `id_mov`='$archivos[0]' AND cestado!=2");
            while ($da = mysqli_fetch_array($data_aprmov, MYSQLI_ASSOC)) {
                $ccodaport = $da["ccodaport"];
                $ctipope = $da["ctipope"];
                $cnumdoc = $da["cnumdoc"];
                $ctipdoc = $da["ctipdoc"];
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
            $data_aprcta = mysqli_query($conexion, "SELECT `nlibreta`,`ccodcli`,`num_nit`,`ccodtip` FROM `aprcta` WHERE `ccodaport`=$ccodaport");
            while ($da = mysqli_fetch_array($data_aprcta, MYSQLI_ASSOC)) {
                $nlibreta = $da["nlibreta"];
                $ccodcli = $da["ccodcli"];
                $ccodtip = $da["ccodtip"];
                $ultimonum = lastnumlin($ccodaport, $nlibreta, "aprmov", "ccodaport", $conexion);
                $ultimocorrel = lastcorrel($ccodaport, $nlibreta, "aprmov", "ccodaport", $conexion);
            }
            //consultar datos de tabla cliete para el nombre
            // $shortname = "";
            $data_cliente =  mysqli_query($conexion, "SELECT `short_name`,`compl_name`, `no_identifica` FROM `tb_cliente` WHERE `idcod_cliente`='$ccodcli'");
            while ($da = mysqli_fetch_array($data_cliente, MYSQLI_ASSOC)) {
                $shortname = strtoupper($da["short_name"]);
                $dpi = $da["no_identifica"];
            }
            //consultar ids nomenclaturas
            list($id, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("aprctb", "id_tipo_doc", (tipocuenta($ccodtip, "aprtip", "id_tipo", $conexion)), (get_id_tipdoc($ctipdoc, "aprtipdoc", $conexion)), $conexion);

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
                $camp_glosa .= glosa_obtenerTipoModulo(1); //deposito o ahorro de aportacion
                $camp_glosa .= glosa_obtenerEspacio();
                $camp_glosa .= glosa_obtenerConector(0);
                $camp_glosa .= glosa_obtenerEspacio();
                $camp_glosa .= glosa_obtenerNomCliente($ccodcli, $conexion); //cliente
                $camp_glosa .= glosa_obtenerEspacio();
                $camp_glosa .= glosa_obtenerRecibo($cnumdoc_aux);

                //INSERCIONES POR APRMOV
                //actualizar registro anterior, para cambiarle su estado
                $conexion->query("UPDATE `aprmov` SET `dfecmod` = '$hoy',`codusu` = '$archivos[1]',`cestado` = '0' WHERE `id_mov` = '$archivos[0]'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error al actualizar aprmov:' . $aux, '0']);
                    $conexion->rollback();
                    return;
                }
                //insercion en aprmov
                $conexion->query("INSERT INTO `aprmov`(`ccodaport`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`monto`,`lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`created_by`,`created_at`,`cestado`) VALUES ('$ccodaport','$hoy2','$ctipope_aux','$cnumdoc_aux','$ctipdoc','$tipotransaccion', $nlibreta,$monto,'N',$ultimonum+1,$ultimocorrel+1,'$hoy','$archivos[1]','$archivos[1]','$hoy','0')");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error al insertar aprmov:' . $aux, '0']);
                    $conexion->rollback();
                    return;
                }
                //insercion en aprcta
                $conexion->query("UPDATE `aprcta` SET `fecha_ult` = '$hoy2',`correlativo` = $ultimocorrel+1,`numlinea` = $ultimonum+1 WHERE `ccodaport` = '$ccodaport'");
                if ($aux) {
                    echo json_encode(['Error al actualizar aprcta:' . $aux, '0']);
                    $conexion->rollback();
                    return;
                }
                //INSERCIONES EN CTB_DIARIO
                $camp_numcom = getnumcom($archivos[1], $conexion);
                //SE HACE UN REGISTRO EN CONTABILIDAD, EN LA TABLA "ctb_diario"
                $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$camp_numcom',3,1,'$cnumdoc_aux', '$camp_glosa','$hoy2', '$hoy2','$ccodaport','$archivos[1]','$hoy',1)");
                if ($aux) {
                    echo json_encode(['Error al insertar ctbdiaro:' . $aux, '0']);
                    $conexion->rollback();
                    return;
                }
                //-----FIN

                //SE HACE 2 REGISTROS EN CONTABILIDAD EN LA TABLA "ctb_mov"
                $id_ctb_diario = get_id_insertado($conexion);

                if ($ctipope == "D") {
                    $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta2, '$monto',0)");
                    if ($aux) {
                        echo json_encode(['Error al insertar ctb_mov 1:' . $aux, '0']);
                        $conexion->rollback();
                        return;
                    }
                    $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta1, 0,'$monto')");
                    if ($aux) {
                        echo json_encode(['Error al insertar ctb_mov 2:' . $aux, '0']);
                        $conexion->rollback();
                        return;
                    }
                }
                if ($ctipope == "R") {
                    $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta1, '$monto',0)");
                    if ($aux) {
                        echo json_encode(['Error al insertar ctb_mov 3:' . $aux, '0']);
                        $conexion->rollback();
                        return;
                    }
                    $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,'$camp_numcom',1,$idcuenta2, 0,'$monto')");
                    if ($aux) {
                        echo json_encode(['Error al insertar ctb_mov 4:' . $aux, '0']);
                        $conexion->rollback();
                        return;
                    }
                }
                // -----FIN
                if ($conexion->commit()) {
                    //NUMERO EN LETRAS
                    $format_monto = new NumeroALetras();
                    $decimal = explode(".", $monto);
                    $res = (isset($decimal[1]) == false) ? 0 : $decimal[1];
                    $letras_monto = ($format_monto->toMoney($decimal[0], 2, 'QUETZALES', '')) . " " . $res . "/100";
                    $particionfecha = explode("-", $dfecope);

                    ($ctipope == "D") ? $archivos[1] = "Reversión de depósito a cuenta " . $ccodaport : $archivos[1] = "Reversión de retiro a cuenta " . $ccodaport;
                    echo json_encode(['Datos reversados correctamente', '1', $ccodaport, number_format($monto, 2, '.', ','), date("d-m-Y", strtotime($hoy)), $cnumdoc_aux, $archivos[1], $shortname, utf8_decode($_SESSION['nombre']), utf8_decode($_SESSION['apellido']), $hoy, $letras_monto, $particionfecha[0], $particionfecha[1], $particionfecha[2], $dpi, 0]);
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
        $data_aprmov = mysqli_query($conexion, "SELECT `ccodaport`,`ctipope`,`cnumdoc`,`monto`, CAST(`created_at` AS DATE) AS created_at, created_by,`dfecope` FROM `aprmov` WHERE `id_mov`='$inputs[0]' AND cestado!=2");
        while ($da = mysqli_fetch_array($data_aprmov, MYSQLI_ASSOC)) {
            $ccodaport = $da["ccodaport"];
            $ctipope = $da["ctipope"];
            $cnumdoc = $da["cnumdoc"];
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
        $data_aprcta = mysqli_query($conexion, "SELECT `ccodcli` FROM `aprcta` WHERE `ccodaport`=$ccodaport");
        while ($da = mysqli_fetch_array($data_aprcta, MYSQLI_ASSOC)) {
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
            $conexion->query("UPDATE `aprmov` SET `cnumdoc` = '$inputs[2]',`dfecmod` = '$hoy',`codusu` = '$inputs[3]' WHERE `id_mov` = '$inputs[0]'");

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

                ($ctipope == "D") ? $inputs[3] = "Depósito a cuenta " . $ccodaport : $inputs[3] = "Retiro a cuenta " . $ccodaport;
                echo json_encode(['Datos actualizados correctamente', '1', $ccodaport, number_format($monto, 2, '.', ','), date("d-m-Y", strtotime($hoy)), $inputs[2], $inputs[3], $shortname, utf8_decode($_SESSION['nombre']), utf8_decode($_SESSION['apellido']), $hoy, $letras_monto, $particionfecha[0], $particionfecha[1], $particionfecha[2], $dpi]);
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
        $data_aprmov = mysqli_query($conexion, "SELECT `ccodaport`,`ctipope`,`cnumdoc`,`monto`,`cuota_ingreso`,`dfecope` FROM `aprmov` WHERE `id_mov`='$archivos[0]' AND cestado!=2");
        while ($da = mysqli_fetch_array($data_aprmov, MYSQLI_ASSOC)) {
            $ccodaport = $da["ccodaport"];
            $ctipope = $da["ctipope"];
            $cnumdoc = $da["cnumdoc"];
            $monto = $da["monto"];
            $cuotaingreso = $da["cuota_ingreso"];
            $dfecope = $da["dfecope"];
        }

        //consultar datos de aprcta
        $data_aprcta = mysqli_query($conexion, "SELECT `ccodcli`,tip.nombre FROM `aprcta` cta INNER JOIN aprtip tip on tip.ccodtip=cta.ccodtip WHERE `ccodaport`=$ccodaport");
        while ($da = mysqli_fetch_array($data_aprcta, MYSQLI_ASSOC)) {
            $ccodcli = $da["ccodcli"];
            $producto = $da["nombre"];
        }
        //consultar datos de tabla cliete para el nombre
        $data_cliente =  mysqli_query($conexion, "SELECT `short_name`, `no_identifica`,control_interno FROM `tb_cliente` WHERE `idcod_cliente`='$ccodcli'");
        while ($da = mysqli_fetch_array($data_cliente, MYSQLI_ASSOC)) {
            $shortname = (mb_strtoupper($da["short_name"], 'utf-8'));
            $dpi = $da["no_identifica"];
            $controlinterno = $da["control_interno"];
        }

        if (substr($cnumdoc, 0, 4) == "REV-") {
            ($ctipope == "R") ? $archivos[1] = "Reversión de depósito a cuenta " . $ccodaport : $archivos[1] = "Reversión de retiro a cuenta " . $ccodaport;
        } else {
            ($ctipope == "D") ? $archivos[1] = "Depósito a cuenta " . $ccodaport : $archivos[1] = "Retiro a cuenta " . $ccodaport;
        }

        //NUMERO EN LETRAS
        $format_monto = new NumeroALetras();
        $decimal = explode(".", ($monto + $cuotaingreso));
        $res = (isset($decimal[1]) == false) ? 0 : $decimal[1];
        $letras_monto = ($format_monto->toMoney($decimal[0], 2, 'QUETZALES', '')) . " " . $res . "/100";
        $particionfecha = explode("-", $dfecope);

        echo json_encode(['Datos reimpresos correctamente', '1', $ccodaport, number_format($monto, 2, '.', ','), date("d-m-Y", strtotime($hoy)), $cnumdoc, $archivos[1], $shortname, utf8_decode($_SESSION['nombre']), utf8_decode($_SESSION['apellido']), $hoy, $letras_monto, $particionfecha[0], $particionfecha[1], $particionfecha[2], $dpi, $cuotaingreso, $producto, ($monto + $cuotaingreso), $letras_monto, $_SESSION['id'], $controlinterno]);
        // echo json_encode(['Datos ingresados correctamente', '1', $cuenta, number_format($monto, 2, '.', ','), date("d-m-Y", strtotime($fechaoperacion)), $numdoc, $auxdes, $nombre, utf8_decode($_SESSION['nombre']), utf8_decode($_SESSION['apellido']), $hoy, $letras_monto, $particionfecha[0], $particionfecha[1], $particionfecha[2], $dpi, $cuotaingreso, $transaccion, $total_ap2rcuo, $letras_total, $_SESSION['id']]);
        mysqli_close($conexion);
        break;
    case 'eliminacion_recibo':
        $idDato = $_POST["ideliminar"];
        $hoy2 = date("Y-m-d H:i:s");
        $conexion->autocommit(false);
        //Obtener informaicon de la ahommov
        $consulta = mysqli_query($conexion, "SELECT ccodaport, dfecope, cnumdoc, CAST(created_at AS DATE) AS fecsis FROM aprmov WHERE id_mov = $idDato AND cestado!=2");
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
            $res = $conexion->query("UPDATE aprmov SET cestado = '2', codusu = $idusuario, dfecmod = '$hoy2'  WHERE id_mov = $idDato");
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
            FROM aprmov AS mov
            INNER JOIN aprcta AS ac ON mov.ccodaport = ac.ccodaport
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

            $dato1 = mysqli_query($conexion, "SELECT IFNULL(dfecmod, '0') AS fecha FROM aprmov WHERE cestado!=2 AND cnumdoc = '" . $codDoc['codDoc'] . "';");

            if (mysqli_affected_rows($conexion) != 0) {
                $fechaHora = mysqli_fetch_assoc($dato1);
                $dato1 = mysqli_query($conexion, "SELECT (IFNULL(SUM(monto),0))  AS mov 
                FROM aprmov AS mov
                INNER JOIN aprcta AS ac ON mov.ccodaport = ac.ccodaport
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
function gendatarecibo($id, $conexion)
{
    // $hoy = date("Y-m-d H:i:s");
    // $hoy2 = date("Y-m-d");

    // //consultar datos del aprmov con id recibido
    // $data_aprmov = mysqli_query($conexion, "SELECT `ccodaport`,`ctipope`,`cnumdoc`,`monto`,`dfecope` FROM `aprmov` WHERE `id_mov`=$id AND cestado!=2");
    // while ($da = mysqli_fetch_array($data_aprmov, MYSQLI_ASSOC)) {
    //     $ccodaport = $da["ccodaport"];
    //     $ctipope = $da["ctipope"];
    //     $cnumdoc = $da["cnumdoc"];
    //     $monto = $da["monto"];
    //     $dfecope = $da["dfecope"];
    // }

    // //consultar datos de aprcta
    // $data_aprcta = mysqli_query($conexion, "SELECT `ccodcli` FROM `aprcta` WHERE `ccodaport`=$ccodaport");
    // while ($da = mysqli_fetch_array($data_aprcta, MYSQLI_ASSOC)) {
    //     $ccodcli = $da["ccodcli"];
    // }
    // //consultar datos de tabla cliete para el nombre
    // $data_cliente =  mysqli_query($conexion, "SELECT `short_name`, `no_identifica` FROM `tb_cliente` WHERE `idcod_cliente`='$ccodcli'");
    // while ($da = mysqli_fetch_array($data_cliente, MYSQLI_ASSOC)) {
    //     $shortname = (mb_strtoupper($da["short_name"], 'utf-8'));
    //     $dpi = $da["no_identifica"];
    // }

    // if (substr($cnumdoc, 0, 4) == "REV-") {
    //     ($ctipope == "R") ? $archivos[1] = "Reversión de depósito a cuenta " . $ccodaport : $archivos[1] = "Reversión de retiro a cuenta " . $ccodaport;
    // } else {
    //     ($ctipope == "D") ? $archivos[1] = "Depósito a cuenta " . $ccodaport : $archivos[1] = "Retiro a cuenta " . $ccodaport;
    // }

    // //NUMERO EN LETRAS
    // $format_monto = new NumeroALetras();
    // $decimal = explode(".", $monto);
    // $res = (isset($decimal[1]) == false) ? 0 : $decimal[1];
    // $letras_monto = ($format_monto->toMoney($decimal[0], 2, 'QUETZALES', '')) . " " . $res . "/100";
    // $particionfecha = explode("-", $dfecope);
    // mysqli_close($conexion);


    // echo json_encode(['Datos reimpresos correctamente', '1', $ccodaport, number_format($monto, 2, '.', ','), date("d-m-Y", strtotime($hoy)), $cnumdoc, $archivos[1], $shortname, utf8_decode($_SESSION['nombre']), utf8_decode($_SESSION['apellido']), $hoy, $letras_monto, $particionfecha[0], $particionfecha[1], $particionfecha[2], $dpi, 0]);
}
