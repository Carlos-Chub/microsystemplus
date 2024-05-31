<?php

use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\NumberFormat;

include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../funcphp/fun_ppg.php';
/* include '../funcphp/func_gen.php'; */
session_start();
date_default_timezone_set('America/Guatemala');
$hoy = date("Y-m-d");
$hoy2 = date("Y-m-d H:i:s");

$condi = $_POST["condi"];
switch ($condi) {
    case 'soligrupal':
        $inputs = $_POST["inputs"];
        $montos = $inputs[0];
        $detalle = $inputs[1];
        $archivo = $_POST["archivo"];

        //VALIDAR EL ANALISTA
        if ($detalle[1] == "0") {
            echo json_encode(["Seleccionar Analista", '0']);
            return;
        }
        //VERIFICAR NEGATIVOS
        if (count(array_filter(array_column($montos, 1), function ($var) {
            return ($var < 0);
        })) > 0) {
            echo json_encode(["Monto negativo detectado, favor verificar", '0']);
            return;
        }
        //SI SE INGRESARON MONTOS A SOLICITAR
        if (array_sum(array_column($montos, 1)) <= 0) {
            echo json_encode(["Monto total Solicitado invalido, favor verificar", '0']);
            return;
        }

        $i = 0;
        $j = 0;
        $data[] = [];
        while ($i < count($montos)) {
            $filas = $montos[$i];
            //SE VERIFICAN LOS CAMPOS QUE SON MAYORES A 0 PARA TOMARLOS EN CUENTA
            if ($filas[1] != "" || $filas[1] > 0) {
                //VERIFICAR SI SE SELECCIONARON SECTORES Y ACTIVIDADES ECONOMICAS PARA CADA CREDITO
                $validacion = validarcampo([$filas[3], $filas[4]], "0");
                if ($validacion != "1") {
                    echo json_encode(["Seleccionar Sector Y Actividad economica!", '0']);
                    return;
                }
                $data[$j] = $filas;
                $j++;
            }
            $i++;
        }
        /* filas = getinputsval(['ccodcli' + (rows), 'monsol' + (rows),  'descre' + (rows), 'sectorecono' + (rows), 'actecono' + (rows)]);
        datadetal = getinputsval(['nciclo', 'fecsol', 'codanal']); */
        //INSERCION EN LA BD

        $conexion->autocommit(false);
        try {
            $i = 0;
            while ($i < count($data)) {
                $date = new DateTime();
                $date = $date->format('YmdHisv');

                $gencodigo = getcrecodcta($archivo[0], "02", $conexion);
                if ($gencodigo[0] == 0) {
                    echo json_encode([$gencodigo[1] . ": " . $i, '0']);
                    return;
                }
                $codgen = $gencodigo[1];
                $res = $conexion->query("INSERT INTO `cremcre_meta`(`CCODCTA`,`CodCli`,`CODAgencia`,`CodAnal`,`Cestado`,`DfecSol`,`ActoEcono`,`Cdescre`,`CSecEco`,`CCodGrupo`,`MontoSol`,`TipoEnti`,`NCiclo`,`fecha_operacion`) 
                VALUES('$codgen','" . $data[$i][0] . "','$archivo[2]','$detalle[1]','A','$hoy2','" . $data[$i][4] . "','" . $data[$i][2] . "','" . $data[$i][3] . "','$archivo[1]'," . $data[$i][1] . ",'GRUP',$detalle[0],'$hoy')");

                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode([$aux . ": " . $i, '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Fallo al crear la solicitud', '0']);
                    $conexion->rollback();
                    return;
                }

                $i++;
            }

            $conexion->query("UPDATE `tb_grupo` SET estadoGrupo='C',close_by='$detalle[1]',close_at='$hoy2' WHERE id_grupos=$archivo[1]");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux . ": " . $i, '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['Fallo al Cerrar el grupo', '0']);
                $conexion->rollback();
                return;
            }
            if ($conexion->commit()) {
                echo json_encode(['Datos ingresados correctamente', '1']);
            } else {
                echo json_encode(['Error al ingresar: ', '0']);
                $conexion->rollback();
                return;
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'analgrupal':
        $inputs = $_POST["inputs"];
        $montos = $inputs[0];
        $detalle = $inputs[1];
        $archivo = $_POST["archivo"];

        //VALIDAR PRODUCTO
        if ($detalle[0] == "") {
            echo json_encode(["Seleccione un Producto ó Linea de Crédito", '0']);
            return;
        }
        //TIPO DE CREDITO
        if ($detalle[2] == "0") {
            echo json_encode(["Seleccione un tipo de Crédito", '0']);
            return;
        }
        //TIPO DE PERIODO
        if ($detalle[3] == "0") {
            echo json_encode(["Seleccione un tipo de Periodo", '0']);
            return;
        }
        //NUMERO DE CUOTAS
        if ($detalle[5] == "" || $detalle[5] < 1) {
            echo json_encode(["Numero de cuotas Inválido", '0']);
            return;
        }
        //FECHA DE DESEMBOLSO Y PRIMERA CUOTA
        if ($detalle[4] == "" || $detalle[6] == "") {
            echo json_encode(["Fecha de primera cuota o Desembolso invalida", '0']);
            return;
        }
        if ($detalle[4] < $detalle[6]) {
            echo json_encode(["La fecha de la primera cuota no debe ser menor a la fecha de desembolso", '0']);
            return;
        }
        //VERIFICAR MONTOS VACIOS
        if (count(array_filter(array_column($montos, 1), function ($var) {
            return ($var == "");
        })) > 0) {
            echo json_encode(["Monto invalido detectado, favor verificar", '0']);
            return;
        }
        //VERIFICAR NEGATIVOS
        if (count(array_filter(array_column($montos, 1), function ($var) {
            return ($var <= 0);
        })) > 0) {
            echo json_encode(["Monto negativo ó igual a 0 detectado, favor verificar", '0']);
            return;
        }
        //VERIFICAR MONTOS QUE NO SEAN MAYORES AL LIMITE
        $monmax = $detalle[1];
        if (count(array_filter(array_column($montos, 1), function ($var) use ($monmax) {
            return ($var > $monmax);
        })) > 0) {
            echo json_encode(["Monto invalido, Maximo permitido en la linea de credito: " . $monmax . ", favor verificar", '0']);
            return;
        }

        $conexion->autocommit(false);
        try {
            $i = 0;
            while ($i < count($montos)) {
                /* $ahorro =  ($montos[$i][1] * ($detalle[9] / 100)); */
                $conexion->query("UPDATE `cremcre_meta` SET  Cestado='D',MonSug='" . $montos[$i][1] . "',Dictamen='" . $detalle[7] . "',noPeriodo='" . $detalle[5] . "',DfecDsbls='" . $detalle[6] . "',DfecPago='" . $detalle[4] . "',`CtipCre`='" . $detalle[2] . "', NtipPerC='" . $detalle[3] . "',NIntApro='" . $detalle[8] . "',CCODPRD='" . $detalle[0] . "',`DFecAnal`='" . $hoy2 . "',`fecha_operacion`='" . $hoy . "' WHERE `CCODCTA`='" . $montos[$i][0] . "'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode([$aux . ": " . $i, '0']);
                    return;
                }
                $i++;
            }
            if ($conexion->commit()) {
                echo json_encode(['Datos ingresados correctamente', '1']);
            } else {
                echo json_encode(['Error al ingresar: ', '0']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'aprobgrupal':
        $inputs = $_POST["inputs"];
        $conexion->autocommit(false);
        try {
            $i = 0;
            while ($i < count($inputs)) {
                $conexion->query("UPDATE `cremcre_meta` SET  Cestado='E',`DFecApr`='" . $hoy2 . "',`fecha_operacion`='" . $hoy . "' WHERE `CCODCTA`='" . $inputs[$i][0] . "'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode([$aux . ": " . $i, '0']);
                    return;
                }
                $i++;
            }
            if ($conexion->commit()) {
                echo json_encode(['Créditos Aprobados correctamente', '1']);
            } else {
                echo json_encode(['Error al ingresar: ', '0']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'desemgrupal':
        $inputs = $_POST["inputs"];
        $montos = $inputs[0];
        $detalle = $inputs[1];
        $archivo = $_POST["archivo"];
        $datoscre = $archivo[5];

        //COMPROBAR CIERRE DE CAJA
        $cierre_caja = comprobar_cierre_caja($_SESSION['id'], $conexion);
        if ($cierre_caja[0] < 6) {
            echo json_encode([$cierre_caja[1], '0']);
            return;
        }

        $tipdoc = ["E", "C", "T"];

        //COMPRUEBA SI SE SELECCIONO BANCO
        if ($detalle[0] == 2 && $detalle[1] == "0") {
            echo json_encode(["Seleccione un Banco", '0']);
            return;
        }
        //COMPRUEBA SI SE SELECCIONO BANCO
        if ($detalle[0] == 2 && $detalle[2] == "0") {
            echo json_encode(["Seleccione una cuenta", '0']);
            return;
        }
        // $gastos =(array_key_exists(5,$montos))? $montos[5]:null;
        // unset($montos[5]);
        $i = 0;
        $data[] = [];
        while ($i < count($montos)) {
            $filas = $montos[$i];
            $gastos = (array_key_exists(5, $filas)) ? $filas[5] : null;
            $validacion = validarcampo([$filas[1]], "");
            if ($validacion != "1") {
                echo json_encode(["No se ingreso descripción al desembolso del credito: " . $filas[0], '0']);
                return;
            }
            //VALIDACION SI EL DESEMBOLSO ES POR CHEQUE
            if ($detalle[0] == 2) {
                // $validacion = validarcampo([$filas[2]], "");
                // if ($validacion != "1") {
                //     echo json_encode(["No se ingreso numero de cheque al desembolso del credito: " . $filas[0], '0']);
                //     return;
                // }
            }
            //COMPROBACION DE GASTOS
            $k = 0;
            while ($gastos != null && $k < count($gastos)) {
                if (count(array_filter(array_column($gastos, 1), function ($var) {
                    return ($var < 0);
                })) > 0) {
                    echo json_encode(["Monto negativo en el gasto detectado, favor verificar", '0']);
                    return;
                }
                $k++;
            }
            //FIN COMPROBACION DE GASTOS
            $data[$i] = $filas;
            $i++;
        }

        $idnomentrega = 8; //DE MOMENTO SE PONE ESTATICO SI NO FUERA DESEMBOLSO POR CHEQUE
        //ID NOMENCLATURA DONDE IRA EL MONTO A ENTREGAR POR DESEMBOLSO EN EFECTIVO
        if ($detalle[0] == 1) {
            $consulta = mysqli_query($conexion, "SELECT * FROM tb_agencia ag WHERE ag.id_agencia='$archivo[4]'");
            if (!$consulta) {
                echo json_encode(['Fallo al encontrar la cuenta para el desembolso real en efectivo', '0']);
                return;
            }
            while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                $idnomentrega = $fila['id_nomenclatura_caja'];
            }
        }

        //COMPROBACION DE NUMEROS DE CHEQUES
        $porcheque = 0;
        if ($detalle[0] == 2) {
            // $unicos = array_unique(array_column($data, 2));
            // if (count(array_column($data, 2)) > count($unicos)) {
            //     echo json_encode(["Se repite el numero de cheque ingresado", "0"]);
            //     return;
            // }

            //IDNOMENCLATURA DE LA CUENTA DE BANCO
            $consulta = mysqli_query($conexion, "SELECT id,id_nomenclatura FROM ctb_bancos WHERE id=$detalle[2]");
            if (!$consulta) {
                echo json_encode(['Fallo al encontrar el id de la cuenta bancaria', '0']);
                return;
            }
            while ($filae = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                $idnomentrega = $filae['id_nomenclatura'];
            }
            $porcheque = 1;
        }

        // CONSULTAR EL LA CUENTA CAPITAL PARA EL PRODUCTO
        $id_nomeclatura_capital = 1;
        $consulta = mysqli_query($conexion, "SELECT cp.id_cuenta_capital FROM cre_productos cp
        INNER JOIN cremcre_meta cm ON cp.id=cm.CCODPRD 
        WHERE cm.CCODCTA='" . $data[0][0] . "'");
        if (!$consulta) {
            echo json_encode(['Fallo al encontrar el id de la cuenta bancaria', '0']);
            return;
        }
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $id_nomeclatura_capital = $fila['id_cuenta_capital'];
        }

        //GASTOS DE LA LINEA DE CREDITO DEL GRUPO
        $datosgrupo[] = [];
        //$haygastos = 0;
        $grupoinfo = mysqli_query($conexion, 'SELECT * from tb_grupo WHERE id_grupos=' . $archivo[0] . '');
        $i = 0;
        while ($das = mysqli_fetch_array($grupoinfo, MYSQLI_ASSOC)) {
            $datosgrupo[$i] = $das;
            // $haygastos = 1;
            $i++;
        }

        /*         echo json_encode([$cregastos, "0"]);
        return;   */
        $idsdiario = [];

        /* echo json_encode(["hasta aqui bien", '0']);
        return; */
        //-----------------
        $conexion->autocommit(false);
        try {
            $i = 0;
            while ($i < count($data)) {
                //INSERTAR CREPPG
                $res = creppg_INST($data[$i][0], $conexion);
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode([$aux . ": " . $i, '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res[0]) {
                    echo json_encode(['Fallo al crear el plan de pago', '0']);
                    $conexion->rollback();
                    return;
                }
                //INSERCION EN LA CREDKAR
                $cnrocuo = getnumcnrocuo($data[$i][0], $conexion);
                $concepto = strtoupper($data[$i][1]);
                $numdoc = ($detalle[0] == 2) ? $data[$i][2] : " ";
                $res = $conexion->query("INSERT INTO `CREDKAR`(`CCODCTA`, `DFECPRO`, `DFECSIS`, `CNROCUO`, `NMONTO`, `CNUMING`, `CCONCEP`, `KP`, `OTR`,`CCODOFI`, `CCODUSU`, `CTIPPAG`, `CMONEDA`,`DFECMOD`) 
                VALUES ('" . $data[$i][0] . "','$archivo[2]','$hoy2',$cnrocuo," . $data[$i][3] . ",'$numdoc','$concepto'," . ($data[$i][3] - $data[$i][4]) . "," . $data[$i][4] . ",'" . $archivo[4] . "','" . $archivo[3] . "','D','Q','$hoy')");

                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode([$aux . ": " . $i, '0']);
                    return;
                }
                if (!$res) {
                    echo json_encode(['Error en la insercion en el Kardex:' . $i, '0']);
                    $conexion->rollback();
                    return;
                }
                $id_credkar = get_id_insertado($conexion);
                //ACTUALIZACION EN LA CREMCRE
                $res = $conexion->query("UPDATE `cremcre_meta` SET  Cestado='F',`NCapDes`=MonSug,`TipDocDes`='" . $tipdoc[$detalle[0] - 1] . "',`fecha_operacion`='" . $hoy . "' WHERE `CCODCTA`='" . $data[$i][0] . "'");
                if (!$res) {
                    echo json_encode(['Error en la actualizacion del estado del Credito' . $i, '0']);
                    $conexion->rollback();
                    return;
                }
                //INICIO DE TRANSACCIONES EN CONTA Y BANCOS
                $glosa = "CRÉDITO GRUPAL:" . $data[$i][0] . " - GRUPO:" . $datosgrupo[0]['NombreGrupo'] . " - FONDO:" . $datoscre[$i]['descfondo'] . " - BENEFICIARIO:" . strtoupper($datoscre[$i]['short_name']);
                $numpartida = getnumcom($archivo[3], $conexion);

                //----------INSERCION EN EL LIBRO DE DIARIO
                $res = $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$numpartida',1,1,'$numdoc','$glosa','$archivo[2]','$archivo[2]','" . $data[$i][0] . "',$archivo[3],'$hoy2',1)");
                if (!$res) {
                    echo json_encode(['Error en la Creacion de partida de diario' . $i, '0']);
                    $conexion->rollback();
                    return;
                }
                $id_ctb_diario = get_id_insertado($conexion); //obtener el id insertado en diario  

                //---------INSERCION TOTAL
                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario," . $datoscre[$i]['id_fondo'] . " ,$id_nomeclatura_capital, " . $data[$i][3] . ",0)");
                if (!$res) {
                    echo json_encode(['Error en la Creacion de movimiento de la partida de diario' . $i, '0']);
                    $conexion->rollback();
                    return;
                }
                //---------INSERCION DE GASTOS
                // $k = 0;
                // while ($haygastos == 1 && $k < count($cregastos)) {
                //     $gascal = ($cregastos[$k]['tipo_deMonto'] == 1) ? $cregastos[$k]['monto'] : ($data[$i][3] * $cregastos[$k]['monto'] / 100);
                //     $nomenclatura = 100;
                //     $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario," . $datoscre[$i]['id_fondo'] . ",$nomenclatura, 0," . $gascal . ")");
                //     if (!$res) {
                //         echo json_encode(['Error en la Creacion de movimientos de gastos en la partida de diario' . $i, '0']);
                //         $conexion->rollback();
                //         return;
                //     }
                //     $k++;
                // }

                $gastos = (array_key_exists(5, $data[$i])) ? $data[$i][5] : null;
                $k = 0;
                while ($gastos != null && $k < count($gastos)) {
                    $gascal = $gastos[$k][1];
                    if ($gascal > 0) {
                        $nomenclatura = $gastos[$k][2];
                        $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario," . $datoscre[$i]['id_fondo'] . ",$nomenclatura, 0," . $gascal . ")");
                        if (!$res) {
                            echo json_encode(['Error en la Creacion de movimientos de gastos en la partida de diario' . $i, '0']);
                            $conexion->rollback();
                            return;
                        }
                        //INSERTAR EN LA TABLA CREDKAR_DETALLE
                        $idgasto = $gastos[$k][0];
                        $res = $conexion->query("INSERT INTO `credkar_detalle`(`id_credkar`,`id_concepto`,`monto`) VALUES ($id_credkar,$idgasto,$gascal)");
                        if (!$res) {
                            echo json_encode(['Error en la Creacion de movimientos de gastos en el kardex' . $i, '0']);
                            $conexion->rollback();
                            return;
                        }
                    }
                    $k++;
                }
                //INSERCION DE MONTO A ENTREGAR
                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario," . $datoscre[$i]['id_fondo'] . ",$idnomentrega, 0," . ($data[$i][3] - $data[$i][4]) . ")");
                if (!$res) {
                    echo json_encode(['Error en la Creacion del movimiento a entregar en la partida de diario' . $i, '0']);
                    $conexion->rollback();
                    return;
                }
                //---------INSERCION EN CUENTAS DE CHEQUES SI EL DESEMBOLSO ES POR CHEQUE
                if ($detalle[0] == 2) {
                    $res = $conexion->query("INSERT INTO `ctb_chq`(`id_ctb_diario`,`id_cuenta_banco`,`numchq`,`nomchq`,`monchq`,`emitido`) 
                    VALUES ($id_ctb_diario,$detalle[2],'" . $data[$i][2] . "', '" . strtoupper($datoscre[$i]['short_name']) . "'," . ($data[$i][3] - $data[$i][4]) . ",'0')");

                    if (!$res) {
                        echo json_encode(['Error en la Creacion del Cheque' . $i, '0']);
                        $conexion->rollback();
                        return;
                    }
                }
                $idsdiario[$i] = $id_ctb_diario;

                //FIN TRANSACCIONES EN CONTA Y BANCOS
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode([$aux . ": " . $i, '0']);
                    $conexion->rollback();
                    return;
                }
                $i++;
            }
            if ($conexion->commit()) {
                echo json_encode(['Créditos Desembolsados correctamente', '1', $idsdiario, $porcheque]);
            } else {
                echo json_encode(['Error al ingresar: ', '0']);
                $conexion->rollback();
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'cuotasgrupo':
        $param = $_POST['datas'];
        $codgrupo = $param[0];
        $ciclo = $param[1];

        $strquery = 'SELECT crem.DFecDsbls apertura,SUM(crem.NCapDes) monto_desembolsado,
                IFNULL((SELECT SUM(KP) FROM CREDKAR cred INNER JOIN cremcre_meta cre ON cre.CCODCTA = cred.ccodcta WHERE cre.CCodGrupo=crem.CCodGrupo AND cre.NCiclo=crem.NCiclo AND cre.Cestado="F" AND cred.CTIPPAG="P" AND cred.CESTADO!="X"),0) pagado,
                ppg.cnrocuo nocuota,ppg.dfecven fecha_cuota,SUM(ppg.ncapita) montocapital,SUM(ppg.nintere) montointeres,
                SUM(ppg.OtrosPagos) monto_otros,SUM(ppg.SaldoCapital) saldo_kp
                FROM Cre_ppg ppg 
                INNER JOIN cremcre_meta crem ON crem.CCODCTA = ppg.ccodcta 
                INNER JOIN tb_cliente cli ON cli.idcod_cliente=crem.CodCli
                WHERE crem.CCodGrupo="' . $codgrupo . '" AND crem.NCiclo="' . $ciclo . '" AND crem.Cestado="F" GROUP BY ppg.dfecven;';

        $consulta = mysqli_query($conexion, $strquery);
        $array_datos = array();
        $i = 0;
        $contador = 1;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $fecha = $fila["fecha_cuota"];
            $boton = '<button type="button" class="btn btn-outline-success" title="Planilla en Excel" onclick="reportes([[],[],[],[' . $codgrupo . ',' . $ciclo . ',`' . $fecha . '`]],`xlsx`,`planilla_cuota`,1)">
                        <i class="fa-solid fa-file-excel"></i></button>';
            $array_datos[] = array(
                "0" => $fila["nocuota"],
                "1" => date("d-m-Y", strtotime($fecha)),
                "2" => number_format($fila["montocapital"], 2),
                "3" => number_format($fila["montointeres"], 2),
                "4" => number_format($fila["monto_otros"], 2),
                "5" => number_format($fila["montocapital"] + $fila["montointeres"] + $fila["monto_otros"], 2),
                "6" => $fila["saldo_kp"],
                "7" => $boton

            );
            $i++;
            $contador++;
        }
        $results = array(
            "sEcho" => 1, //info para datatables
            "iTotalRecords" => count($array_datos), //enviamos el total de registros al datatable
            "iTotalDisplayRecords" => count($array_datos), //enviamos el total de registros a visualizar
            "aaData" => $array_datos
        );
        mysqli_close($conexion);
        echo json_encode($results);
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
        }
    }
    return ["", '0', false];
}
