<?php

use PhpOffice\PhpSpreadsheet\Worksheet\Row;

session_start();
include '../../includes/BD_con/db_con.php';
include_once "../../includes/Config/database.php";

mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');
// include '../../src/funcphp/func_gen.php';
include '../../src/funcphp/fun_ppg.php';
date_default_timezone_set('America/Guatemala');
$hoy2 = date("Y-m-d H:i:s");
$hoy = date("Y-m-d");
$idusuario = $_SESSION['id'];
$usu = $_SESSION['usu'];

$condi = $_POST["condi"];
switch ($condi) {
    case 'list_creditos_a_desembolsar': {
            $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cl.short_name, cm.CodCli AS codcli, cm.CODAgencia AS codagencia, pd.cod_producto AS codproducto, cm.MonSug AS monto, cm.Cestado AS estado   FROM cremcre_meta cm
            INNER JOIN cre_productos pd ON cm.CCODPRD=pd.id
            INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente WHERE cm.Cestado='E' AND cm.TipoEnti='INDI'");
            //se cargan los datos de las beneficiarios a un array
            $array_datos = array();
            $array_parenteco[] = [];
            $total = 0;
            $i = 0;
            while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                $array_datos[] = array(
                    "0" => $i + 1,
                    "1" => $fila["short_name"],
                    "2" => $fila["codproducto"],
                    "3" => $fila["ccodcta"],
                    "4" => $fila["monto"],
                    // "5" => '<button type="button" class="btn btn-success" onclick="seleccionar_credito_a_desembolsar(`#id_modal_hidden`,[`' . $fila["codcli"] . '`,`' . $fila["short_name"] . '`,`' . $fila["codagencia"] . '`,`' . $fila["codproducto"] . '`,`' . $fila["ccodcta"] . '`,`' . $fila["monto"] . '`]); consultar_gastos_monto(`' . $fila["ccodcta"] . '`); mostrar_tabla_gastos(`' . $fila["ccodcta"] . '`); cerrar_modal(`#modal_creditos_a_desembolsar`, `hide`, `#id_modal_hidden`); $(`#bt_desembolsar`).show(); concepto_default(`' . $fila["short_name"] . '`, `0`);">Aceptar</button>'
                    // "5" => '<button type="button" class="btn btn-success" onclick="seleccionar_credito_a_desembolsar(`#id_modal_hidden`,[`' . $fila["codcli"] . '`,`' . $fila["short_name"] . '`,`' . $fila["codagencia"] . '`,`' . $fila["codproducto"] . '`,`' . $fila["ccodcta"] . '`,`' . $fila["monto"] . '`]); consultar_gastos_monto(`' . $fila["ccodcta"] . '`); mostrar_tabla_gastos(`' . $fila["ccodcta"] . '`); cerrar_modal(`#modal_creditos_a_desembolsar`, `hide`, `#id_modal_hidden`); $(`#bt_desembolsar`).show(); concepto_default(`' . $fila["short_name"] . '`, `0`);">Aceptar</button>'
                    "5" => '<button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="printdiv2(`#cuadro`,`' . $fila["ccodcta"] . '`)" >Aceptar</button>'
                );
                $i++;

                // eliminar(ideliminar, dir, xtra, condi)
            }
            $results = array(
                "sEcho" => 1, //info para datatables
                "iTotalRecords" => count($array_datos), //enviamos el total de registros al datatable
                "iTotalDisplayRecords" => count($array_datos), //enviamos el total de registros a visualizar
                "aaData" => $array_datos
            );
            mysqli_close($conexion);
            echo json_encode($results);
        }
        break;

    case 'listado_consultar_estado_cuenta_for_delete': {
            $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cm.CodCli AS codcli, cl.short_name AS nombre, cm.NCiclo AS ciclo, cm.MonSug AS monsug, cm.TipoEnti AS tipocred, cm.Cestado
            FROM cremcre_meta cm
            INNER JOIN tb_cliente cl ON cm.CodCli = cl.idcod_cliente
            WHERE (cm.Cestado='F') AND cm.TipoEnti = 'INDI'
            ORDER BY cm.CCODCTA ASC;");
            //se cargan los datos de las beneficiarios a un array
            $array_datos = array();
            $total = 0;
            $i = 0;
            while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                $array_datos[] = array(
                    "0" => $i + 1,
                    "1" => $fila["ccodcta"],
                    "2" => $fila["nombre"],
                    "3" => $fila["ciclo"],
                    "4" => $fila["monsug"],
                    "5" => ($fila["tipocred"] == 'INDI') ? ('Individual') : ('Grupal'),
                    "6" => '<button type="button" class="btn btn-danger btn-sm mr-2" data-bs-dismiss="modal" data-ccodcta="' . $fila["ccodcta"] . '" onclick="enviarDelete(this)"><i class="fas fa-trash-alt"></i> Eliminar</button>',
                    "7" => '<button type="button" class="btn btn-warning btn-sm" data-bs-dismiss="modal" data-ccodcta="' . $fila["ccodcta"] . '" onclick="enviarAprobacion(this)"><i class="fas fa-arrow-left"></i> Estado a Aprobación</button>'
                );
                $i++;
            }
            $results = array(
                "sEcho" => 1,
                "iTotalRecords" => count($array_datos),
                "iTotalDisplayRecords" => count($array_datos),
                "aaData" => $array_datos
            );
            echo json_encode($results);
            mysqli_close($conexion);
        }
        break;

    case 'gastos_desembolsos':
        $idc = $_POST['id'];
        $datas = gastoscredito($idc, $conexion);
        $capital = 0;
        $suma_gasto = 0;
        if ($datas != null) {
            $suma_gasto = array_sum(array_column($datas, 'mongas'));
        }
        $nombrecliente = "";
        $consulta = mysqli_query($conexion, "SELECT cl.short_name, MonSug FROM cremcre_meta cm INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente WHERE cm.CCODCTA='" . $idc . "'");
        $aux = mysqli_error($conexion);
        if ($aux) {
            echo json_encode(['Error en la recuperacion de los gastos, intente nuevamente', '0']);
            return;
        }
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $nombrecliente = $fila['short_name'];
            $capital = $fila['MonSug'];
        }
        $suma_a_desembolsar = $capital - $suma_gasto;
        echo json_encode(['Satisfactorio', '1', $capital, $suma_gasto, $suma_a_desembolsar, $nombrecliente]);
        break;
    case 'lista_gastos':
        $idc = $_POST['id'];
        $filcuenta = $_POST['filcuenta'];
        $datas = gastoscredito($idc, $conexion);
        $array_datos = array();
        $total = 0;
        $i = 0;
        while ($datas != null && $i <  count($datas)) {
            $id = $datas[$i]['id'];
            $contable = $datas[$i]['id_nomenclatura'];
            $tipo = $datas[$i]['tipo_deMonto'];
            $nombregasto = $datas[$i]['nombre_gasto'];
            $mongas = $datas[$i]['mongas'];
            $afectaotros = $datas[$i]['afecta_modulo'];
            $fecdes = $datas[$i]['fecdes'];

            $dataselect = "";
            $visible = "none";
            if ($afectaotros == 3) {
                //BANDERA PARA ACTIVAR O DESACTIVAR EL CALCULO AUTOMATICO DEL INTERES HASTA LA FECHA DE HOY Y/O FECHA DE DESEMBOLSO
                $calculointeres = false;
                $cuentas = getcuentas($idc, $conexion);
                $j = 0;
                while ($cuentas != null && $j <  count($cuentas)) {
                    $account = $cuentas[$j]['CCODCTA'];
                    $pagadokp = $cuentas[$j]['pagadokp'];
                    $capdes = $cuentas[$j]['NCapDes'];
                    $intpen = $cuentas[$j]['intpen'];
                    $fecpago = $cuentas[$j]['fecpago'];
                    $fecult = ($cuentas[$j]['fecult'] == "-") ? $fecdes : $cuentas[$j]['fecult'];
                    $intapro = $cuentas[$j]['intapro'];
                    $saldo = round($capdes - $pagadokp, 2);
                    $diasdif = dias_dif($fecult, $hoy);
                    if ($calculointeres) {
                        $intpen = $saldo * $intapro / 100 / 360 * $diasdif;
                    }
                    $visible = "block";

                    $dataselect .= '<option data-saldo="' . $saldo . '" data-intpen="' . $intpen . '" value="' . $account . '">' . $account . ' | ' . $saldo . ' | ' . $intpen . '</option>';
                    $j++;
                }
            }
            $cuentasshow = '<select style="display:' . $visible . ';" id="ant_' . $i . '_' . substr($idc, 8) . '" class="form-select form-select-sm" aria-label=Cuentas anteriores" onchange="handleSelectChange(`mon_' . $i . '_' . substr($idc, 8) . '`,this);summongas(`' . substr($idc, 8) . '`,0)">';
            $cuentasshow .= '<option selected disabled value="0">Seleccione Cuenta | Saldokp | Int pendiente</option>';
            $cuentasshow .= $dataselect;
            $cuentasshow .= '</select>';

            $disabled = ($tipo < 3) ? ' ' : ' ';
            $array_datos[] = array(
                "0" => $i + 1,
                "1" => '<input type="number" id="idg_' . $i . '_' . substr($idc, 8) . '" min="0" value="' . $id . '" hidden>',
                "2" => $nombregasto,
                "3" => $cuentasshow,
                "4" => '<input type="number" class="form-control" onblur="summongas(`' . substr($idc, 8) . '`, ' . $filcuenta . ')"
                id="mon_' . $i . '_' . substr($idc, 8) . '" min="0" step="0.01" value="' . $mongas . '" ' . $disabled . '>',
                "5" => '<input type="number" id="con_' . $i . '_' . substr($idc, 8) . '" min="0" value="' . $contable . '" hidden>',
            );
            $i++;
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
    case 'buscar_cuentas':
        $id = $_POST['id'];
        $data[] = [];
        $bandera = true;
        $consulta = mysqli_query($conexion, "SELECT cbn.id, cbn.numcuenta FROM tb_bancos bn INNER JOIN ctb_bancos cbn ON bn.id=cbn.id_banco WHERE bn.estado='1' AND cbn.estado='1' AND bn.id='$id'");
        $aux = mysqli_error($conexion);
        if ($aux) {
            echo json_encode(['Error en la recuperacion de cuentas de bancos, intente nuevamente', '0']);
            return;
        }
        if ($consulta) {
            $i = 0;
            while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                $bandera = false;
                $data[$i] = $fila;
                $i++;
            }
            if ($bandera) {
                echo json_encode(['El banco seleccionado no tiene cuentas creadas, por lo que no se puede realizar un desembolso con cheque', '0']);
                return;
            }
            echo json_encode(['Satisfactorio', '1', $data]);
        } else {
            echo json_encode(['Error en la recuperacion de cuentas de bancos, intente nuevamente', '0']);
        }
        mysqli_close($conexion);
        break;
    case 'buscar_cuentas_ahorro_cli':
        $id = $_POST['id'];
        $data[] = [];
        $bandera = true;
        $consulta = mysqli_query($conexion, "SELECT cta.ccodaho, tp.nombre FROM ahomcta cta INNER JOIN ahomtip tp ON SUBSTR(cta.ccodaho, 7, 2)=tp.ccodtip WHERE cta.estado='A' AND cta.ccodcli='$id'");
        $aux = mysqli_error($conexion);
        if ($aux) {
            echo json_encode(['Error en la recuperacion de cuentas de ahorro del cliente, intente nuevamente', '0']);
            return;
        }
        if ($consulta) {
            $i = 0;
            while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                $bandera = false;
                $data[$i] = $fila;
                $i++;
            }

            if ($bandera) {
                echo json_encode(['El cliente no tiene ninguna cuenta de ahorro, debe crear al menos una cuenta para efectuar esta operación', '0']);
                return;
            }
            echo json_encode(['Satisfactorio', '1', $data]);
        } else {
            echo json_encode(['Error en la recuperacion de cuentas de ahorro del cliente, intente nuevamente', '0']);
        }
        mysqli_close($conexion);
        break;
    case 'create_desembolsoanterior': //desactualizado
        //validar todos los campos necesarios
        //COMPROBAR CIERRE DE CAJA
        $cierre_caja = comprobar_cierre_caja($_SESSION['id'], $conexion);
        if ($cierre_caja[0] < 6) {
            echo json_encode([$cierre_caja[1], '0']);
            return;
        }

        $validar = validar_campos([
            [$inputs[0], "", 'Debe seleccionar un cliente'],
            [$inputs[1], "", 'Debe seleccionar un cliente'],
            [$inputs[2], "", 'Debe tener un código de agencia, seleccione un cliente'],
            [$inputs[3], "", 'Debe tener un código de producto, seleccione un cliente'],
            [$inputs[4], "", 'Debe tener un código de crédito, seleccione un cliente'],
            [$inputs[5], "", 'Debe tener un capital, seleccione un cliente'],
            [$inputs[6], "", 'Debe tener un gasto, seleccione un cliente'],
            [$inputs[7], "", 'Debe tener un total a desembolsar, seleccione un cliente'],
            [$selects[0], "", 'Debe seleccionar un tipo de desembolso']
        ]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }
        $gastos = (array_key_exists(2, $archivo)) ? $archivo[2] : null;
        //COMPROBACION DE GASTOS
        if ($gastos != null) {
            if (count(array_filter(array_column($gastos, 1), function ($var) {
                return ($var < 0);
            })) > 0) {
                echo json_encode(["Monto negativo en el gasto detectado, favor verificar", '0']);
                return;
            }
        }
        //FIN COMPROBACION DE GASTOS
        //validar el tipo de desembolso
        if ($selects[0] == '1') {
            //validaciones de la transferencia
            $validar = validar_campos([
                [$inputs[12], "", 'Debe digitar un concepto']
            ]);
            if ($validar[2]) {
                echo json_encode([$validar[0], $validar[1]]);
                return;
            }
            //inicio de la transaccion
            $conexion->autocommit(false);
            try {
                $numpartida = getnumcom($archivo[0], $conexion); //Obtener numero de partida
                //CONSULTAR INFORMACION PARA FORMAR LA GLOSA
                $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cl.short_name, cf.descripcion, cm.DFecDsbls, cm.MonSug, cf.id AS id_fuente, pr.id_cuenta_capital FROM cremcre_meta cm
                INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
                INNER JOIN cre_productos pr ON cm.CCODPRD=pr.id
                INNER JOIN ctb_fuente_fondos cf ON pr.id_fondo=cf.id
                WHERE cm.CCODCTA='$inputs[4]'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error al momento de consultar al cliente', '0']);
                    $conexion->rollback();
                    return;
                }
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $cliente = strtoupper($fila['short_name']);
                    $fuente = strtoupper($fila['descripcion']);
                    $fechadesembolso = $fila['DFecDsbls'];
                    $monto_desembolsar = $fila['MonSug'];
                    $id_fuente = $fila['id_fuente'];
                    $id_cuenta_capital = $fila['id_cuenta_capital'];
                }
                //FIN DE CONSULTAR LA INFORMACION
                $glosa = "CRÉDITO INDIVIDUAL:" . $inputs[4] . " - FONDO:" . $fuente . " - BENEFICIARIO:" . $cliente;
                //INSERCCION EN EL LIBRO DE DIARIO
                $res = $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$numpartida',1,1,'$inputs[9]','$glosa','$fechadesembolso','$fechadesembolso','$inputs[4]',$archivo[0],'$hoy2',1)");
                $id_ctb_diario = get_id_insertado($conexion); //obtener el id insertado en diario
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en la inserción del libro de diario', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Insercion en libro de diario fallo', '0']);
                    $conexion->rollback();
                    return;
                }

                //INSERCCION EN MOVIMIENTOS CONTABLES------------------------------------------------------------------
                $capital = $monto_desembolsar;

                //INSERTAR EL CAPITAL
                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,$id_fuente,$id_cuenta_capital, '$capital',0)");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en la inserción del capital', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['No se logro insertar el capital', '0']);
                    $conexion->rollback();
                    return;
                }

                //INSERTAR GASTOS SI HUBIERAS
                $bandera = false;
                $aux_id_cogdas = 0;

                if ($gastos != null) {
                    for ($i = 0; $i < count($gastos); $i++) {
                        $nomenclatura =  $gastos[$i][2];
                        $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`)
                                VALUES ($id_ctb_diario,$id_fuente,'$nomenclatura', 0," . $gastos[$i][1] . ")"); //AQUI HAY UN ERROR AL INSERTAR LA CONSULTA
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode([$aux . ',id:' . $i, '0']);
                            $conexion->rollback();
                            return;
                        }
                        if (!$res) {
                            $bandera = true;
                        }
                    }
                    //validar si todas fueron insertadas
                    if ($bandera) {
                        echo json_encode(['Transaccion no completada con éxito', '0']);
                        $conexion->rollback();
                        return;
                    }
                }
                //INSERTAR MONTO TOTAL A DESEMBOLSAR
                // buscar la nomenclatura de la cuenta en la tabla de agencia
                $consulta = mysqli_query($conexion, "SELECT * FROM tb_agencia ag WHERE ag.id_agencia='$archivo[1]'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en encontrar la cuenta para el desembolso real', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$consulta) {
                    echo json_encode(['Fallo al encontrar la cuenta para el desembolso real', '0']);
                    $conexion->rollback();
                    return;
                }
                $banderacaja = true;
                $id_nomenclatura_caja = 0;
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $banderacaja = false;
                    $id_nomenclatura_caja = $fila['id_nomenclatura_caja'];
                }
                if ($banderacaja) {
                    echo json_encode(['No se encontro la cuenta contable para el desembolso real', '0']);
                    $conexion->rollback();
                    return;
                }

                $suma_gasto = $inputs[6];
                $suma_a_desembolsar = $capital - $suma_gasto;
                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,$id_fuente,$id_nomenclatura_caja, 0,'$suma_a_desembolsar')");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en la inserción del monto a desembolsar', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['No se logro insertar el monto a desembolsar', '0']);
                    $conexion->rollback();
                    return;
                }

                //INSERCION EN LA CREDKAR
                $cnrocuo = getnumcnrocuo($inputs[4], $conexion);
                $concepto = strtoupper($inputs[12]);
                $res = $conexion->query("INSERT INTO `CREDKAR`(`CCODCTA`, `DFECPRO`, `DFECSIS`, `CNROCUO`, `NMONTO`, `CNUMING`, `CCONCEP`, `KP`, `INTERES`, `MORA`, `AHOPRG`, `OTR`,`CCODOFI`, `CCODUSU`, `CTIPPAG`, `CMONEDA`,`DFECMOD`,`CESTADO`)
                    VALUES ('$inputs[4]','$fechadesembolso','$hoy2',$cnrocuo,$capital,'$inputs[9]','$concepto',$suma_a_desembolsar,0,0,0,$suma_gasto,'$archivo[1]','$archivo[0]','D','Q','$hoy','1')");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode([$aux . 'credkar', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Fallo al registrar el cheque', '0']);
                    $conexion->rollback();
                    return;
                }
                $id_credkar = get_id_insertado($conexion); //obtener el id insertado en la credkar
                //INSERCION EN LA CREDKAR_DETALLE
                $k = 0;
                while ($gastos != null && $k < count($gastos)) {
                    $gascal = $gastos[$k][1];
                    if ($gascal > 0) {
                        $idgasto = $gastos[$k][0];
                        $res = $conexion->query("INSERT INTO `credkar_detalle`(`id_credkar`,`id_concepto`,`monto`) VALUES ($id_credkar,$idgasto,$gascal)");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode(['Error gasto:' . $idgasto, '0']);
                            $conexion->rollback();
                            return;
                        }
                        if (!$res) {
                            echo json_encode(['Error en la Creacion de movimientos de gastos en el kardex', '0']);
                            $conexion->rollback();
                            return;
                        }
                    }
                    $k++;
                }

                //ACTUALIZACION EN LA CREMCRE
                $tipodesembolso = "";
                if ($selects[0] == '1') {
                    $tipodesembolso = 'E';
                } else if ($selects[0] == '2') {
                    $tipodesembolso = 'C';
                } else if ($selects[0] == '3') {
                    $tipodesembolso = 'T';
                }

                $res = $conexion->query("UPDATE `cremcre_meta` SET `Cestado`='F', `fecha_operacion`='$hoy', `NCapDes`=$capital, `TipDocDes`='$tipodesembolso' WHERE `CCODCTA`='$inputs[4]'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode([$aux . 'cremcremeta', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Fallo al actualizar el crédito', '0']);
                    $conexion->rollback();
                    return;
                }
                //INSERTAR CREPPG
                $res = creppg_INST($inputs[4], $conexion);
                if (!$res) {
                    echo json_encode(['Fallo al crear el plan de pago', '0']);
                    $conexion->rollback();
                    return;
                }

                //Cambio del estado de la cuenta de ahorro de plazo fijo por que ingreso como garantia
                $res = $conexion->query("UPDATE ahomcta cta SET cta.dep = 1 , cta.ret = 0 WHERE cta.ccodaho IN (SELECT cg.descripcionGarantia  FROM tb_garantias_creditos tgc
                INNER JOIN cli_garantia cg ON cg.idGarantia  = tgc.id_garantia
                WHERE tgc.id_cremcre_meta = '$inputs[4]')");

                $aux = mysqli_error($conexion);
                if (!$res || $aux) {
                    echo json_encode(['Erro 2000', '0']);
                    $conexion->rollback();
                    return;
                }

                //ENVIAR DATOS PARA GENERAR IMPRIMIR EL EFECTIVO
                $conexion->commit();
                echo json_encode(['Correcto,  Desembolso con efectivo generada, con No.: ' . $numpartida, '1', $inputs[4], 'efectivo', $id_ctb_diario]);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
            }
            mysqli_close($conexion);
        } else if ($selects[0] == '2') {
            //validaciones del cheque
            $validar = validar_campos([
                [$inputs[8], "", 'Debe digitar una cantidad del cheque'],
                // [$inputs[9], "", 'Debe digitar un número de cheque'],
                [$inputs[10], "", 'Debe digitar el campo paguese a la orden de'],
                [$inputs[11], "", 'Debe digitar el campo la suma de'],
                [$selects[1], "", 'Debe seleccionar un tipo de cheque'],
                [$selects[2], "", 'Debe seleccionar un banco'],
                [$selects[3], "", 'Debe seleccionar una cuenta de banco'],
                [$inputs[12], "", 'Debe digitar un concepto']
            ]);
            if ($validar[2]) {
                echo json_encode([$validar[0], $validar[1]]);
                return;
            }
            //inicio de la transaccion
            $conexion->autocommit(false);
            try {
                $numpartida = getnumcom($archivo[0], $conexion); //Obtener numero de partida
                //CONSULTAR INFORMACION PARA FORMAR LA GLOSA
                $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cl.short_name, cf.descripcion, cm.DFecDsbls, cm.MonSug, cf.id AS id_fuente, pr.id_cuenta_capital FROM cremcre_meta cm
                INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
                INNER JOIN cre_productos pr ON cm.CCODPRD=pr.id
                INNER JOIN ctb_fuente_fondos cf ON pr.id_fondo=cf.id
                WHERE cm.CCODCTA='$inputs[4]'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error al momento de consultar al cliente', '0']);
                    $conexion->rollback();
                    return;
                }
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $cliente = strtoupper($fila['short_name']);
                    $fuente = strtoupper($fila['descripcion']);
                    $fechadesembolso = $fila['DFecDsbls'];
                    $monto_desembolsar = $fila['MonSug'];
                    $id_fuente = $fila['id_fuente'];
                    $id_cuenta_capital = $fila['id_cuenta_capital'];
                }
                //FIN DE CONSULTAR LA INFORMACION
                $glosa = "CRÉDITO INDIVIDUAL:" . $inputs[4] . " - FONDO:" . $fuente . " - BENEFICIARIO:" . $cliente;
                //INSERCCION EN EL LIBRO DE DIARIO
                $res = $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$numpartida',1,1,'$inputs[9]','$glosa','$fechadesembolso','$fechadesembolso','$inputs[4]',$archivo[0],'$hoy2',1)");
                $id_ctb_diario = get_id_insertado($conexion); //obtener el id insertado en diario
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en la inserción del libro de diario', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Insercion en libro de diario fallo', '0']);
                    $conexion->rollback();
                    return;
                }

                //INSERCCION EN MOVIMIENTOS CONTABLES------------------------------------------------------------------
                $capital = $monto_desembolsar;

                //INSERTAR EL CAPITAL
                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,$id_fuente,$id_cuenta_capital, '$capital',0)");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en la inserción del capital', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['No se logro insertar el capital', '0']);
                    $conexion->rollback();
                    return;
                }

                //INSERTAR GASTOS SI HUBIERAS
                $bandera = false;
                $aux_id_cogdas = 0;

                if ($gastos != null) {
                    for ($i = 0; $i < count($gastos); $i++) {
                        $nomenclatura = $gastos[$i][2];
                        $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`)
                                VALUES ($id_ctb_diario,$id_fuente,'$nomenclatura', 0," . $gastos[$i][1] . ")"); //AQUI HAY UN ERROR AL INSERTAR LA CONSULTA
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode([$aux . ',id:' . $i, '0']);
                            $conexion->rollback();
                            return;
                        }
                        if (!$res) {
                            $bandera = true;
                        }
                    }
                    //validar si todas fueron insertadas
                    if ($bandera) {
                        echo json_encode(['Transaccion no completada con éxito', '0']);
                        $conexion->rollback();
                        return;
                    }
                }
                //INSERTAR MONTO TOTAL A DESEMBOLSAR
                //buscar la nomenclatura del banco
                $consulta = mysqli_query($conexion, "SELECT id,id_nomenclatura FROM ctb_bancos WHERE id='$selects[3]'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en encontrar el id de la cuenta bancaria', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$consulta) {
                    echo json_encode(['Fallo al encontrar el id de la cuenta bancaria', '0']);
                    $conexion->rollback();
                    return;
                }
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $id_nomenbanco = $fila['id_nomenclatura'];
                }
                $suma_gasto = $inputs[6];
                $suma_a_desembolsar = $capital - $suma_gasto;

                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,$id_fuente,$id_nomenbanco, 0,'$suma_a_desembolsar')");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(["Error en la inserción del monto a desembolsar", '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['No se logro insertar el monto a desembolsar', '0']);
                    $conexion->rollback();
                    return;
                }

                //INSERCCION EN CUENTAS DE CHEQUES
                $res = $conexion->query("INSERT INTO `ctb_chq`(`id_ctb_diario`,`id_cuenta_banco`,`numchq`,`nomchq`,`monchq`,`emitido`,`modocheque`)
                    VALUES ($id_ctb_diario,$selects[3],'$inputs[9]', '$cliente','$suma_a_desembolsar','0','$selects[1]')");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(["Error en la inserción del cheque", '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Fallo al registrar el cheque', '0']);
                    $conexion->rollback();
                    return;
                }
                //INSERCION EN LA CREDKAR
                $cnrocuo = getnumcnrocuo($inputs[4], $conexion);
                $concepto = strtoupper($inputs[12]);
                $res = $conexion->query("INSERT INTO `CREDKAR`(`CCODCTA`, `DFECPRO`, `DFECSIS`, `CNROCUO`, `NMONTO`, `CNUMING`, `CCONCEP`, `KP`, `INTERES`, `MORA`, `AHOPRG`, `OTR`,`CCODOFI`, `CCODUSU`, `CTIPPAG`, `CMONEDA`,`DFECMOD`)
                    VALUES ('$inputs[4]','$fechadesembolso','$hoy2',$cnrocuo,$capital,'$inputs[9]','$concepto',$suma_a_desembolsar,0,0,0,$suma_gasto,'$archivo[1]','$archivo[0]','D','Q','$hoy')");

                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode([$aux . "3", '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Fallo la insercion en la credkar', '0']);
                    $conexion->rollback();
                    return;
                }
                $id_credkar = get_id_insertado($conexion); //obtener el id insertado en la credkar
                //INSERCION EN LA CREDKAR_DETALLE
                $k = 0;
                while ($gastos != null && $k < count($gastos)) {
                    $gascal = $gastos[$k][1];
                    if ($gascal > 0) {
                        $idgasto = $gastos[$k][0];
                        $res = $conexion->query("INSERT INTO `credkar_detalle`(`id_credkar`,`id_concepto`,`monto`) VALUES ($id_credkar,$idgasto,$gascal)");
                        if (!$res) {
                            echo json_encode(['Error en la Creacion de movimientos de gastos en el kardex', '0']);
                            $conexion->rollback();
                            return;
                        }
                    }
                    $k++;
                }
                //ACTUALIZACION EN LA CREMCRE
                $tipodesembolso = "";
                if ($selects[0] == '1') {
                    $tipodesembolso = 'E';
                } else if ($selects[0] == '2') {
                    $tipodesembolso = 'C';
                } else if ($selects[0] == '3') {
                    $tipodesembolso = 'T';
                }
                $res = $conexion->query("UPDATE `cremcre_meta` SET `Cestado`='F', `fecha_operacion`='$hoy', `NCapDes`=$capital, `TipDocDes`='$tipodesembolso' WHERE `CCODCTA`='$inputs[4]'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode([$aux . "4", '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Fallo al actualizar el crédito', '0']);
                    $conexion->rollback();
                    return;
                }

                //INSERTAR CREPPG
                $res = creppg_INST($inputs[4], $conexion);
                if (!$res) {
                    echo json_encode(['Fallo al crear el plan de pago', '0']);
                    $conexion->rollback();
                    return;
                }

                //ENVIAR DATOS PARA GENERAR IMPRIMIR EL CHEQUE
                $conexion->commit();
                echo json_encode(['Correcto,  Desembolso con cheque generada, con No.: ' . $numpartida, '1', $inputs[4], 'cheque', $id_ctb_diario, $inputs[9]]);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
            }
            mysqli_close($conexion);
        } else if ($selects[0] == '3') {
            //validaciones de la transferencia
            $validar = validar_campos([
                [$selects[4], "", 'Seleccione una cuenta de ahorro, sino aparece ninguno, es posible que deba crear una cuenta de ahorro'],
                [$inputs[12], "", 'Debe digitar un concepto']
            ]);
            if ($validar[2]) {
                echo json_encode([$validar[0], $validar[1]]);
                return;
            }
            //INSERCIONES
            $conexion->autocommit(false);
            try {
                //CONSULTAR INFORMACION PARA FORMAR LA GLOSA
                $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cl.short_name, cf.descripcion, cm.DFecDsbls, cm.MonSug, cf.id AS id_fuente, cm.CodCli, cm.Dictamen, pr.id_cuenta_capital FROM cremcre_meta cm
                INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
                INNER JOIN cre_productos pr ON cm.CCODPRD=pr.id
                INNER JOIN ctb_fuente_fondos cf ON pr.id_fondo=cf.id
                WHERE cm.CCODCTA='$inputs[4]'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error al momento de consultar al cliente', '0']);
                    $conexion->rollback();
                    return;
                }
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $cliente = strtoupper($fila['short_name']);
                    $fuente = strtoupper($fila['descripcion']);
                    $fechadesembolso = $fila['DFecDsbls'];
                    $monto_desembolsar = $fila['MonSug'];
                    $id_fuente = $fila['id_fuente'];
                    $ccodcli = $fila['CodCli'];
                    $dictamen = $fila['Dictamen'];
                    $id_cuenta_capital = $fila['id_cuenta_capital'];
                }
                //FIN DE CONSULTAR LA INFORMACION
                $glosa = "CRÉDITO INDIVIDUAL:" . $inputs[4] . " - FONDO:" . $fuente . " - BENEFICIARIO:" . $cliente;
                //INSERCCION EN EL LIBRO DE DIARIO
                $numpartida = getnumcom($archivo[0], $conexion); //Obtener numero de partida
                $res = $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$numpartida',1,1,'$inputs[9]','$glosa','$fechadesembolso','$fechadesembolso','$inputs[4]',$archivo[0],'$hoy2',1)");
                $id_ctb_diario = get_id_insertado($conexion); //obtener el id insertado en diario
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en la inserción del libro de diario', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Insercion en libro de diario fallo', '0']);
                    $conexion->rollback();
                    return;
                }

                //INSERCCION EN MOVIMIENTOS CONTABLES-----------------------------------------------------------------------
                $capital = $monto_desembolsar;

                //INSERTAR EL CAPITAL
                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,$id_fuente,$id_cuenta_capital, '$capital',0)");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en la inserción del capital', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['No se logro insertar el capital', '0']);
                    $conexion->rollback();
                    return;
                }

                //INSERTAR GASTOS SI HUBIERA
                $bandera = false;
                $aux_id_cogdas = 0;

                if ($gastos != null) {
                    for ($i = 0; $i < count($gastos); $i++) {
                        $nomenclatura = $gastos[$i][2];
                        $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`)
                                                VALUES ($id_ctb_diario,$id_fuente,'$nomenclatura', 0," . $gastos[$i][1] . ")"); //AQUI HAY UN ERROR AL INSERTAR LA CONSULTA
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode([$aux . ',id:' . $i, '0']);
                            $conexion->rollback();
                            return;
                        }
                        if (!$res) {
                            $bandera = true;
                        }
                    }
                    //validar si todas fueron insertadas
                    if ($bandera) {
                        echo json_encode(['Transaccion no completada con éxito', '0']);
                        $conexion->rollback();
                        return;
                    }
                }
                $suma_gasto = $inputs[6];
                $suma_a_desembolsar = $capital - $suma_gasto;
                //INSERTAR MONTO TOTAL A DESEMBOLSAR
                // buscar la nomenclatura de la cuenta en la tabla de agencia
                $consulta = mysqli_query($conexion, "SELECT * FROM tb_agencia ag WHERE ag.id_agencia='$archivo[1]'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en encontrar la cuenta para el desembolso real', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$consulta) {
                    echo json_encode(['Fallo al encontrar la cuenta para el desembolso real', '0']);
                    $conexion->rollback();
                    return;
                }
                $banderacaja = true;
                $id_nomenclatura_caja = 0;
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $banderacaja = false;
                    $id_nomenclatura_caja = $fila['id_nomenclatura_caja'];
                }
                if ($banderacaja) {
                    echo json_encode(['No se encontro la cuenta contable para el desembolso real', '0']);
                    $conexion->rollback();
                    return;
                }

                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,$id_fuente,$id_nomenclatura_caja, 0,'$suma_a_desembolsar')");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en la inserción del monto a desembolsar', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['No se logro insertar el monto a desembolsar', '0']);
                    $conexion->rollback();
                    return;
                }

                //ACTUALIZACION EN LA CREMCRE
                $tipodesembolso = "";
                if ($selects[0] == '1') {
                    $tipodesembolso = 'E';
                } else if ($selects[0] == '2') {
                    $tipodesembolso = 'C';
                } else if ($selects[0] == '3') {
                    $tipodesembolso = 'T';
                }
                $res = $conexion->query("UPDATE `cremcre_meta` SET `Cestado`='F', `fecha_operacion`='$hoy', `NCapDes`=$capital, `TipDocDes`='$tipodesembolso' WHERE `CCODCTA`='$inputs[4]'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en la actualizacion de la linea de crédito', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Fallo al actualizar el crédito', '0']);
                    $conexion->rollback();
                    return;
                }

                //INSERCION EN LA CREDKAR
                $cnrocuo = getnumcnrocuo($inputs[4], $conexion);
                $concepto = strtoupper($inputs[12]);
                $res = $conexion->query("INSERT INTO `CREDKAR`(`CCODCTA`, `DFECPRO`, `DFECSIS`, `CNROCUO`, `NMONTO`, `CNUMING`, `CCONCEP`, `KP`, `INTERES`, `MORA`, `AHOPRG`, `OTR`,`CCODOFI`, `CCODUSU`, `CTIPPAG`, `CMONEDA`,`DFECMOD`) VALUES ('$inputs[4]','$fechadesembolso','$hoy2',$cnrocuo,$capital,'$inputs[9]','$concepto',$suma_a_desembolsar,0,0,0,$suma_gasto,'$archivo[1]','$archivo[0]','D','Q','$hoy')");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en la insercion en la credkar', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Fallo al registrar en la credkar', '0']);
                    $conexion->rollback();
                    return;
                }
                $id_credkar = get_id_insertado($conexion); //obtener el id insertado en la credkar
                //INSERCION EN LA CREDKAR_DETALLE
                $k = 0;
                while ($gastos != null && $k < count($gastos)) {
                    $gascal = $gastos[$k][1];
                    if ($gascal > 0) {
                        $idgasto = $gastos[$k][0];
                        $res = $conexion->query("INSERT INTO `credkar_detalle`(`id_credkar`,`id_concepto`,`monto`) VALUES ($id_credkar,$idgasto,$gascal)");
                        if (!$res) {
                            echo json_encode(['Error en la Creacion de movimientos de gastos en el kardex', '0']);
                            $conexion->rollback();
                            return;
                        }
                    }
                    $k++;
                }

                //INSERCCIONES PARA LA PARTE DE AHORROS
                $transacciontipo = "DEPOSITO";
                //obtener el datos para ingresar en el campo id_ctb_nomenclatura de la tabla ctb_mov
                list($id, $idcuenta1, $idcuenta2) = get_ctb_nomenclatura("ahomctb", "id_tipo_doc", (tipocuenta(substr($selects[4], 6, 2), "ahomtip", "id_tipo", $conexion)), (get_id_tipdoc('T', "ahotipdoc", $conexion)), $conexion);
                //validar si encontro un tipo de parametrizacion para el deposito
                if ($id == "X") {
                    echo json_encode(['NO PUEDE REALIZAR EL ' . $transacciontipo . ' DEBIDO A QUE NO HA PARAMETRIZADO UNA CUENTA CONTABLE PARA ELLO', '0']);
                    // echo json_encode(['ID: ' . $id . ' CUENTA1: '.$idcuenta1.' CUENTA2: '.$idcuenta2.' INPUT0: '.$inputs[0].' SELECTS: '.$selects[1], '0']);
                    return;
                }
                $camp_glosa = "";
                $camp_glosa .= glosa_obtenerMovimiento(0);
                $camp_glosa .= glosa_obtenerEspacio();
                $camp_glosa .= glosa_obtenerConector(0);
                $camp_glosa .= glosa_obtenerEspacio();
                $camp_glosa .= glosa_obtenerTipoModulo(0);
                $camp_glosa .= glosa_obtenerEspacio();
                $camp_glosa .= glosa_obtenerConector(0);
                $camp_glosa .= glosa_obtenerEspacio();
                $camp_glosa .= glosa_obtenerNomCliente($ccodcli, $conexion);
                $camp_glosa .= glosa_obtenerEspacio();
                //EL NUMERO DE RECIBO ES IGUAL AL DICTAMEN
                $camp_glosa .= glosa_obtenerRecibo($dictamen);
                //CONSULTAR NLIBRETA
                $datoscuenta = mysqli_query($conexion, "SELECT `nlibreta` FROM `ahomcta` WHERE `ccodaho`=$selects[4]");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en la recuperacion del numero de libreta', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$datoscuenta) {
                    echo json_encode(['Fallo al consultar lo de la libreta', '0']);
                    $conexion->rollback();
                    return;
                }

                while ($da = mysqli_fetch_array($datoscuenta, MYSQLI_ASSOC)) {
                    $nlibreta = utf8_encode($da["nlibreta"]);
                    $ultimonum = lastnumlin($selects[4], $nlibreta, "ahommov", "ccodaho", $conexion);
                    $ultimocorrel = lastcorrel($selects[4], $nlibreta, "ahommov", "ccodaho", $conexion);
                }

                //INSERTAR EN AHOMMOV
                $res = $conexion->query("INSERT INTO `ahommov`(`ccodaho`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`monto`,`lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`auxi`) VALUES ('$selects[4]','$fechadesembolso','D','$dictamen','T','DEPÓSITO POR DESEMBOLSO', $nlibreta,$suma_a_desembolsar,'N',$ultimonum+1,$ultimocorrel+1,'$hoy2','$archivo[0]','DESEMBOLSO CRÉDITO INDIVIDUAL')");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en la insercion de movimientos de ahorro', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Fallo ingresar movimiento en ahommov', '0']);
                    $conexion->rollback();
                    return;
                }
                //ACTUALIZACION EN AHOMCTA
                $res = $conexion->query("UPDATE `ahomcta` SET `fecha_ult` = '$hoy',`correlativo` = $ultimocorrel+1,`numlinea` = $ultimonum+1 WHERE `ccodaho` = '$selects[4]'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en la actualizacion de la tabla de cuentas de ahorro', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Fallo al actualizar ahomcta ', '0']);
                    $conexion->rollback();
                    return;
                }
                //INSERCION EN CTBDIARIO POR LA TRANSFERENCIA
                $numpartida2 = getnumcom($archivo[0], $conexion); //Obtener numero de partida
                $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$numpartida2',2,1,'$dictamen', '$camp_glosa','$fechadesembolso', '$fechadesembolso','$selects[4]','$archivo[0]','$hoy2',1)");
                $id_ctb_diario2 = get_id_insertado($conexion); //obtener el id insertado en diario
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en la insercion de la partida por transferencia', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Fallo al insetar diario de deposito', '0']);
                    $conexion->rollback();
                    return;
                }
                //INSERCION PARA MOVIMIENTO CONTABLES
                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario2,'$numpartida2',1,$idcuenta1, '$suma_a_desembolsar',0)");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en la insercion de movimientos contables por deposito', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Fallo al insertar movimiento contables', '0']);
                    $conexion->rollback();
                    return;
                }

                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario2,'$numpartida2',1,$idcuenta2, 0,'$suma_a_desembolsar')");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error en la insercion de movimiento contables por deposito', '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Fallo al insertar movimiento contables', '0']);
                    $conexion->rollback();
                    return;
                }

                //INSERTAR CREPPG
                $res = creppg_INST($inputs[4], $conexion);
                if (!$res) {
                    echo json_encode(['Fallo al crear el plan de pago', '0']);
                    $conexion->rollback();
                    return;
                }

                //ENVIAR DATOS PARA GENERAR IMPRIMIR EL EFECTIVO
                $conexion->commit();
                echo json_encode(['Correcto,  Desembolso con transferencia generada, con No.: ' . $numpartida . ' y ' . $numpartida2, '1', $inputs[4], 'transferencia', $id_ctb_diario]);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
            }
            mysqli_close($conexion);
        } else {
            echo json_encode(['Debe seleccionar un tipo de desembolso válido', '0']);
            return;
        }
        break;
        // *********************************************************************************
    case 'create_desembolso':
        //validar todos los campos necesarios
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $archivo = $_POST["archivo"];

        //COMPROBAR CIERRE DE CAJA
        $cierre_caja = comprobar_cierre_caja($_SESSION['id'], $conexion);
        if ($cierre_caja[0] < 6) {
            echo json_encode([$cierre_caja[1], '0']);
            return;
        }
        $validar = validar_campos([
            [$inputs[0], "", 'Debe seleccionar un cliente'],
            [$inputs[1], "", 'Debe seleccionar un cliente'],
            [$inputs[2], "", 'Debe tener un código de agencia, seleccione un cliente'],
            [$inputs[3], "", 'Debe tener un código de producto, seleccione un cliente'],
            [$inputs[4], "", 'Debe tener un código de crédito, seleccione un cliente'],
            [$inputs[5], "", 'Debe tener un capital, seleccione un cliente'],
            [$inputs[6], "", 'Debe tener un gasto, seleccione un cliente'],
            [$inputs[7], "", 'Debe tener un total a desembolsar, seleccione un cliente'],
            [$selects[0], "", 'Debe seleccionar un tipo de desembolso']
        ]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }

        $gastos = (array_key_exists(2, $archivo)) ? $archivo[2] : NULL;
        $gastos = (is_array($gastos)) ? $gastos : NULL;

        //COMPROBACION DE GASTOS
        if ($gastos !== NULL) {
            if (count(array_filter(array_column($gastos, 1), function ($var) {
                return ($var < 0);
            })) > 0) {
                echo json_encode(["Monto negativo en el gasto detectado, favor verificar", '0']);
                return;
            }
        }
        //FIN COMPROBACION DE GASTOS
        //validaciones de la transferencia
        $validar = validar_campos([[$inputs[12], "", 'Debe digitar un concepto']]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }
        if ($selects[0] == '2' || $selects[0] == '4') {
            //validaciones del cheque
            $validar = validar_campos([
                [$inputs[8], "", 'Debe digitar una cantidad del cheque'],
                // [$inputs[9], "", 'Debe digitar un número de cheque'],
                [$inputs[10], "", 'Debe digitar el campo paguese a la orden de'],
                [$inputs[11], "", 'Debe digitar el campo la suma de'],
                [$selects[1], "", 'Debe seleccionar un tipo de cheque'],
                [$selects[2], "", 'Debe seleccionar un banco'],
                [$selects[3], "", 'Debe seleccionar una cuenta de banco']
            ]);
            if ($validar[2]) {
                echo json_encode([$validar[0], $validar[1]]);
                return;
            }
        }
        if ($selects[0] == '3') {
            //validaciones de la transferencia
            $validar = validar_campos([
                [$selects[4], "", 'Seleccione una cuenta de ahorro, sino aparece ninguno, es posible que deba crear una cuenta de ahorro']
            ]);
            if ($validar[2]) {
                echo json_encode([$validar[0], $validar[1]]);
                return;
            }
        }
        /* +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        ++++++++++++++++++++++++++++ CONSULTAR INFORMACION PARA FORMAR LA GLOSA ++++++++++++++++++++++++++++++++
        ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cl.short_name, cf.descripcion, cm.DFecDsbls, cm.MonSug, cf.id AS id_fuente, cm.CodCli, cm.Dictamen, pr.id_cuenta_capital,pr.id_cuenta_interes FROM cremcre_meta cm
                INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
                INNER JOIN cre_productos pr ON cm.CCODPRD=pr.id
                INNER JOIN ctb_fuente_fondos cf ON pr.id_fondo=cf.id
                WHERE cm.CCODCTA='$inputs[4]'");
        $aux = mysqli_error($conexion);
        if ($aux) {
            echo json_encode(['Error al momento de consultar al cliente', '0']);
            $conexion->rollback();
            return;
        }
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $cliente = strtoupper($fila['short_name']);
            $fuente = strtoupper($fila['descripcion']);
            $fechadesembolso = $fila['DFecDsbls'];
            $monto_desembolsar = $fila['MonSug'];
            $id_fuente = $fila['id_fuente'];
            $ccodcli = $fila['CodCli'];
            $dictamen = $fila['Dictamen'];
            $id_cuenta_capital = $fila['id_cuenta_capital'];
            $id_cuenta_interes = $fila['id_cuenta_interes'];
        }
        /* +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        +++++++++++++++++++ buscar la nomenclatura de la cuenta en la tabla de agencia (DEFAULT)++++++++++++++++
        ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $consulta = mysqli_query($conexion, "SELECT * FROM tb_agencia ag WHERE ag.id_agencia='$archivo[1]'");
        $aux = mysqli_error($conexion);
        if ($aux) {
            echo json_encode(['Error en encontrar la cuenta para el desembolso real', '0']);
            $conexion->rollback();
            return;
        }
        if (!$consulta) {
            echo json_encode(['Fallo al encontrar la cuenta para el desembolso real', '0']);
            $conexion->rollback();
            return;
        }
        $banderacaja = true;
        $id_nomenclatura_caja = 1;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $banderacaja = false;
            $id_nomenclatura_caja = $fila['id_nomenclatura_caja'];
        }
        if ($banderacaja) {
            echo json_encode(['No se encontro la cuenta contable para el desembolso real', '0']);
            $conexion->rollback();
            return;
        }
        $cuentadebita = $id_nomenclatura_caja;
        //valida negroy
        if ($selects[0] == '4') {
            $caja_nomen = $id_nomenclatura_caja;
        }
        $tipodesembolso = "efectivo";

        /* +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
+++++++++++++++++++++++++++ SELECCION DE LA CUENTA DE BANCOS SI ES CON CHEQUES +++++++++++++++++++++++++
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        if ($selects[0] == "2" || $selects[0] == '4') {
            //buscar la nomenclatura del banco
            $consulta = mysqli_query($conexion, "SELECT id,id_nomenclatura FROM ctb_bancos WHERE id='$selects[3]'");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode(['Error en encontrar el id de la cuenta bancaria', '0']);
                $conexion->rollback();
                return;
            }
            if (!$consulta) {
                echo json_encode(['Fallo al encontrar el id de la cuenta bancaria', '0']);
                $conexion->rollback();
                return;
            }
            while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                $id_nomenbanco = $fila['id_nomenclatura'];
            }
            $cuentadebita = $id_nomenbanco;
            $tipodesembolso = "cheque";
        }
        // echo json_encode([$archivo[4], '0']);
        // return;
        //inicio de la transaccion
        $conexion->autocommit(false);
        try {
            $numpartida = getnumcom($idusuario, $conexion); //Obtener numero de partida
            /* +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
+++++++++++++++++++++++++++++++++++++++ CONTABILIDAD: DIARIO +++++++++++++++++++++++++++++++++++++++++++
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
            $glosa = "CRÉDITO INDIVIDUAL:" . $inputs[4] . " - FONDO:" . $fuente . " - BENEFICIARIO:" . $cliente;
            //LIBRO DE DIARIO
            $res = $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$numpartida',1,1,'$inputs[9]','$glosa','$fechadesembolso','$fechadesembolso','$inputs[4]',$archivo[0],'$hoy2',1)");
            $id_ctb_diario = get_id_insertado($conexion); //obtener el id insertado en diario
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode(['Error en la inserción del libro de diario', '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['Insercion en libro de diario fallo', '0']);
                $conexion->rollback();
                return;
            }
            $capital = $monto_desembolsar;
            /* +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++++++++++++++++ CONTABILIDAD: MOVIMIENTO -> CAPITAL +++++++++++++++++++++++++++++++++++++++++
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
            $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,$id_fuente,$id_cuenta_capital, '$capital',0)");
            $aux = mysqli_error($conexion);

            // AQUI AGREGAR LA VALIDACION PARA INSERTAR MULTIPLES DESEMBOLSOS DEBEN DE SER 2 UNO EN EFECTIVO Y OTRO EN CHEQUE

            if ($aux) {
                echo json_encode(['Error en la inserción del capital', '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['No se logro insertar el capital', '0']);
                $conexion->rollback();
                return;
            }

            /* +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            +++++++++++++++++++++++++++ CONTABILIDAD: MOVIMIENTO - GASTOS ++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
            $bandera = false;
            $aux_id_cogdas = 0;

            if ($gastos != null) {
                for ($i = 0; $i < count($gastos); $i++) {
                    if ($gastos[$i][1] > 0) {
                        $nomenclatura =  $gastos[$i][2];
                        $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`)
                                VALUES ($id_ctb_diario,$id_fuente,'$nomenclatura', 0," . $gastos[$i][1] . ")"); //AQUI HAY UN ERROR AL INSERTAR LA CONSULTA
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode([$aux . ',id:' . $i, '0']);
                            $conexion->rollback();
                            return;
                        }
                        if (!$res) {
                            $bandera = true;
                        }
                    }
                }
                //validar si todas fueron insertadas
                if ($bandera) {
                    echo json_encode(['Transaccion no completada con éxito', '0']);
                    $conexion->rollback();
                    return;
                }
            }
            /* +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            +++++++++++++++++++++++++++ CONTABILIDAD: MOVIMIENTO - MONTO REAL ++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */

            $suma_gasto = $inputs[6];
            $suma_a_desembolsar = $capital - $suma_gasto;
            // VALIDACION PARA LOS DESEMBOLSOS MIXTOS, si es mixto mete uno mas como efectivo
            if ($selects[0] == '4') {
                $suma_a_desembolsar = $inputs[14]; // CHEQUE monto en cheque
                $EFECTIVO = $inputs[13];
                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,$id_fuente,$caja_nomen, 0,'$EFECTIVO')");
            }
            $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario,$id_fuente,$cuentadebita, 0,'$suma_a_desembolsar')");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode(['Error en la inserción del monto a desembolsar', '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['No se logro insertar el monto a desembolsar', '0']);
                $conexion->rollback();
                return;
            }
            /* +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++ BANCOS: SI EL DESEMBOLSO ES CON CHEQUE ++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
            if ($selects[0] == "2"  || $selects[0] == '4') {
                //INSERCION EN CUENTAS DE CHEQUES
                $res = $conexion->query("INSERT INTO `ctb_chq`(`id_ctb_diario`,`id_cuenta_banco`,`numchq`,`nomchq`,`monchq`,`emitido`,`modocheque`)
                    VALUES ($id_ctb_diario,$selects[3],'$inputs[9]', '$cliente','$suma_a_desembolsar','0','$selects[1]')");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(["Error en la inserción del cheque", '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Fallo al registrar el cheque', '0']);
                    $conexion->rollback();
                    return;
                }
            }

            /* +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++ CREDKAR: REGISTRO DE DESEMBOLSO +++++++++++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
            $cnrocuo = getnumcnrocuo($inputs[4], $conexion);
            $concepto = strtoupper($inputs[12]);
            // VALIDACION NUEVA SI ES UN DESEMBOLSO MIXTO
            if ($selects[0] == '4') {
                $capital = $suma_a_desembolsar + $suma_gasto;
                $res = $conexion->query("INSERT INTO `CREDKAR`(`CCODCTA`, `DFECPRO`, `DFECSIS`, `CNROCUO`, `NMONTO`, `CNUMING`, `CCONCEP`, `KP`, `INTERES`, `MORA`, `AHOPRG`, `OTR`,`CCODOFI`, `CCODUSU`, `CTIPPAG`, `CMONEDA`,`DFECMOD`,`CESTADO`)
              VALUES ('$inputs[4]','$fechadesembolso','$hoy2',$cnrocuo,$EFECTIVO,'$inputs[9]','EN EFECTIVO $concepto',$EFECTIVO,0,0,0,0,'$archivo[1]','$archivo[0]','D','Q','$hoy','1')");
            }

            $idbanco = ($selects[0] == '2') ? $selects[0] : '0';
            $res = $conexion->query("INSERT INTO `CREDKAR`(`CCODCTA`, `DFECPRO`, `DFECSIS`, `CNROCUO`, `NMONTO`, `CNUMING`, `CCONCEP`, `KP`, `INTERES`, `MORA`, `AHOPRG`, `OTR`,`CCODOFI`, `CCODUSU`, `CTIPPAG`, `CMONEDA`,`DFECMOD`,`CESTADO`,`CBANCO`)
                    VALUES ('$inputs[4]','$fechadesembolso','$hoy2',$cnrocuo,$capital,'$inputs[9]','$concepto',$suma_a_desembolsar,0,0,0,$suma_gasto,'$archivo[1]','$archivo[0]','D','Q','$hoy','1','$idbanco')");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux . 'credkar', '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['Fallo al registrar el desembolso en la credkar', '0']);
                $conexion->rollback();
                return;
            }
            $id_credkar = get_id_insertado($conexion); //obtener el id insertado en la credkar
            /* +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            +++++++++++++++++++++++++++ CREDKAR_DETALLE: REGISTRO DE DESCUENTOS SI HUBIERAN ++++++++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
            $k = 0;
            while ($gastos != null && $k < count($gastos)) {
                $gascal = $gastos[$k][1];
                if ($gascal > 0) {
                    $idgasto = $gastos[$k][0];
                    $res = $conexion->query("INSERT INTO `credkar_detalle`(`id_credkar`,`id_concepto`,`monto`) VALUES ($id_credkar,$idgasto,$gascal)");
                    $aux = mysqli_error($conexion);
                    if ($aux) {
                        echo json_encode(['Error gasto:' . $idgasto, '0']);
                        $conexion->rollback();
                        return;
                    }
                    if (!$res) {
                        echo json_encode(['Error en la Creacion de movimientos de gastos en el kardex', '0']);
                        $conexion->rollback();
                        return;
                    }
                }
                $k++;
            }
            /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                +++++++++++++++++++++++ AHORROS: SI ES POR TRANSFERENCIA SE REALIZA LA TRANSACCION +++++++++++++++++++++
                ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
            if ($selects[0] == '3') {
                /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                ++++++++++++++++++++++++++++++ DATOS DE LA CUENTA DE AHORROS +++++++++++++++++++++++++++++++++++++++++++ */
                $a_cuenta = $selects[4];
                $query = "SELECT cta.ccodcli,cta.estado,cta.nlibreta,cli.no_tributaria num_nit,cli.short_name,cli.no_identifica dpi
                                FROM `ahomcta` cta INNER JOIN tb_cliente cli ON cli.idcod_cliente=cta.ccodcli
                                WHERE `ccodaho`=?";
                $response = executequery($query, [$a_cuenta], ['s'], $conexion);
                if (!$response[1]) {
                    echo json_encode([$response[0], '0']);
                    $conexion->rollback();
                    return;
                }
                $data = $response[0];
                $flag = ((count($data)) > 0) ? true : false;
                if (!$flag) {
                    echo json_encode(["Cuenta de ahorro no existe", '0']);
                    $conexion->rollback();
                    return;
                }
                $da = $data[0];
                $a_idcli = utf8_encode($da["ccodcli"]);
                $a_nit = ($da["num_nit"]);
                $a_dpi = ($da["dpi"]);
                $a_nlibreta = ($da["nlibreta"]);
                $a_estado = ($da["estado"]);
                $a_nombre = strtoupper($da["short_name"]);
                $a_ultimonum = lastnumlin($a_cuenta, $a_nlibreta, "ahommov", "ccodaho", $conexion);
                $a_ultimocorrel = lastcorrel($a_cuenta, $a_nlibreta, "ahommov", "ccodaho", $conexion);
                $a_numlib = numfront(substr($a_cuenta, 6, 2), "ahomtip") + numdorsal(substr($a_cuenta, 6, 2), "ahomtip");
                if ($a_ultimonum >= $a_numlib) {
                    echo json_encode(["El número de líneas en libreta ha llegado a su límite, se recomienda abrir otra libreta", '0']);
                    $conexion->rollback();
                    return;
                }
                if ($a_estado != "A") {
                    echo json_encode(["Cuenta de ahorros Inactiva", '0']);
                    $conexion->rollback();
                    return;
                }
                /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                ++++++++++++++++++++++++++++++ CUENTA CONTABLE DEL TIPO DE CUENTA ++++++++++++++++++++++++++++++++++++++++++ */
                $query = "SELECT id_cuenta_contable cuenta,nombre FROM ahomtip WHERE ccodtip=?";
                $response = executequery($query, [substr($a_cuenta, 6, 2)], ['s'], $conexion);
                if (!$response[1]) {
                    echo json_encode([$response[0], '0']);
                    $conexion->rollback();
                    return;
                }
                $data = $response[0];
                $flag = ((count($data)) > 0) ? true : false;
                if (!$flag) {
                    echo json_encode(['No se encontró el tipo de cuenta', '0']);
                    $conexion->rollback();
                    return;
                }
                $cuenta_tipo = $data[0]['cuenta']; //cuenta contable del tipo de ahorro
                $producto = $data[0]['nombre']; //cuenta contable del tipo de ahorro


                $a_camp_numcom = getnumcom($idusuario, $conexion);
                // Preparar la primera consulta para INSERT ahommov
                $res = $conexion->prepare("INSERT INTO `ahommov`(`ccodaho`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,`lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`cestado`,`auxi`,`created_at`,`created_by`)
                VALUES (?, ?, 'D', ?, 'T', 'DEPÓSITO POR DESEMBOLSO', ?, '0','0', '0', ?, 'N', ?, ?, ?, ?,1, 'DESEMBOLSO CRÉDITO INDIVIDUAL', ?,?)");

                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode([$aux, '0']);
                    $conexion->rollback();
                    return;
                }
                $a_ultimonum = ($a_ultimonum + 1);
                $a_ultimocorrel = ($a_ultimocorrel + 1);
                $res->bind_param('sssidiissss', $a_cuenta, $fechadesembolso, $dictamen, $a_nlibreta, $suma_a_desembolsar, $a_ultimonum, $a_ultimocorrel, $hoy2, $idusuario, $hoy2, $idusuario);
                $res->execute();

                // Preparar la segunda consulta para INSERT ctbdiario
                $a_camp_glosa = "DEPÓSITO DE AHORRO DE " . $a_nombre . " CON RECIBO NO. " . $dictamen;
                $res = $conexion->prepare("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`)
                VALUES (?,2,1,?, ?,?, ?,?,?,?,1)");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode([$aux, '0']);
                    $conexion->rollback();
                    return;
                }
                $res->bind_param('ssssssis', $a_camp_numcom, $dictamen, $a_camp_glosa, $fechadesembolso, $fechadesembolso, $a_cuenta, $idusuario, $hoy2);
                $res->execute();
                $a_id_ctb_diario = get_id_insertado($conexion);

                // Preparar la tercera consulta para INSERT ctbmov
                //REGISTRO DE LA CUENTA DEL TIPO DE AHORRO
                $res = $conexion->prepare("INSERT INTO `ctb_mov`(`id_ctb_diario`,`numcom`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES (?,' ',1,?,?,0)");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode([$aux, '0']);
                    $conexion->rollback();
                    return;
                }
                $res->bind_param('iid', $a_id_ctb_diario, $cuenta_tipo, $suma_a_desembolsar);
                $res->execute();
                //REGISTRO DE LA CUENTA DE CAJA
                $res = $conexion->prepare("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES (?,1,?,0,?)");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode([$aux, '0']);
                    $conexion->rollback();
                    return;
                }
                $res->bind_param('iid', $a_id_ctb_diario, $id_nomenclatura_caja, $suma_a_desembolsar);
                $res->execute();

                $tipodesembolso = "transferencia";
            }

            /*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
              ++++++++++++++++++++ CREMCRE: ACTUALIZACION DE ESTADO DE CREDITO Y TIPO DE DESEMBOLSO ++++++++++++++++++
              ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
            //$tipodes = ($selects[0] == '1') ? "E" : (($selects[0] == '2') ? 'C' : 'T');
            $tipodes = ($selects[0] == '1') ? "E" : (($selects[0] == '2') ? 'C' : (($selects[0] == '4') ? 'M' : 'T'));
            $cntaho = (isset($archivo[5])) ? $archivo[5] : 0;

            if ($selects[0] == '4') {
                $capital += $EFECTIVO;
            }
            $res = $conexion->query("UPDATE `cremcre_meta` SET `Cestado`='F', `fecha_operacion`='$hoy', `NCapDes`=$capital, `TipDocDes`='$tipodes', `id_pro_gas`=$archivo[3], `moduloafecta`=$archivo[4], `cntAho`= '$cntaho' WHERE `CCODCTA`='$inputs[4]'");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux . 'cremcremeta', '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['Fallo al actualizar el crédito', '0']);
                $conexion->rollback();
                return;
            }
            /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                +++++++++++++++++++++++++++ CRE_PPG: INSERCION DE CADA CUOTA A LA TABLA DE PAGOS +++++++++++++++++++++++
                ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
            $res = creppg_INST($inputs[4], $conexion);
            $test = $res[0];
            if (!$test) {
                echo json_encode([$res[1], '0']);
                $conexion->rollback();
                return;
            }
            /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                +++++++ Cambio del estado de la cuenta de ahorro de plazo fijo por que ingreso como garantia +++++++++++
                ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
            $res = $conexion->query("UPDATE ahomcta cta SET cta.dep = 1 , cta.ret = 0 WHERE cta.ccodaho IN (SELECT cg.descripcionGarantia  FROM tb_garantias_creditos tgc
                INNER JOIN cli_garantia cg ON cg.idGarantia  = tgc.id_garantia
                WHERE tgc.id_cremcre_meta = '$inputs[4]' AND cg.idTipoGa=3 AND cg.idTipoDoc=8 AND cg.estado=1)");

            $aux = mysqli_error($conexion);
            if (!$res || $aux) {
                echo json_encode(['Erro 2000' . $aux, '0']);
                $conexion->rollback();
                return;
            }
            /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                +++++++ SI HAY DESCUENTOS POR REFINANCIAMIENTO, AQUI SE CONTROLAN ($ARCHIVOS[3][3] TIENE LA CUENTA)+++++
                ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
            if ($gastos != null) {
                for ($i = 0; $i < count($gastos); $i++) {
                    $cuentaref = $gastos[$i][3];
                    $montoref = $gastos[$i][1];

                    if ($cuentaref != 0 && $montoref > 0) { //Si se seleccionó una cuenta y el monto a descontar es mayor a 0
                        $query = 'SELECT NCapDes,IFNULL((SELECT SUM(KP) FROM CREDKAR WHERE CCODCTA=cm.CCODCTA AND CTIPPAG="P" AND CESTADO!="X"),0) pagadokp,pr.id_fondo,pr.id_cuenta_capital,pr.id_cuenta_interes 
                        FROM cremcre_meta cm INNER JOIN cre_productos pr ON pr.id=cm.CCODPRD WHERE CCODCTA=?';
                        $response = executequery($query, [$cuentaref], ['s'], $conexion);
                        if (!$response[1]) {
                            echo json_encode([$response[0], '0']);
                            $conexion->rollback();
                            return;
                        }
                        $data = $response[0];
                        $flag = ((count($data)) > 0) ? true : false;
                        if (!$flag) {
                            echo json_encode(['No se encontró la cuenta a cancelar: ' . $cuentaref, '0']);
                            $conexion->rollback();
                            return;
                        }
                        $fondoref = $data[0]['id_fondo'];
                        $ccntkpref = $data[0]['id_cuenta_capital'];
                        $ccntintref = $data[0]['id_cuenta_interes'];
                        $mondesref = $data[0]['NCapDes'];
                        $pagadoref = $data[0]['pagadokp'];
                        $saldoref = round($mondesref - $pagadoref, 2);

                        if ($montoref < $saldoref) {
                            echo json_encode(['El monto ingresado (' . $montoref . ') no cubre el saldo pendiente(' . $saldoref . '), verificar', '0']);
                            $conexion->rollback();
                            return;
                        }
                        $datos = array(
                            'CCODCTA' => $cuentaref,
                            'DFECPRO' => $fechadesembolso,
                            'DFECSIS' => $fechadesembolso,
                            'CNROCUO' => "10",
                            'NMONTO' => $montoref,
                            'CNUMING' => 'CREF',
                            'CCONCEP' => "Cancelacion por refinanzamiento",
                            'KP' => $saldoref,
                            'INTERES' => ($montoref - $saldoref),
                            'MORA' => 0,
                            'AHOPRG' => 0,
                            'OTR' => 0,
                            'CCODINS' => "1",
                            'CCODOFI' => "1",
                            'CCODUSU' => $idusuario,
                            'CTIPPAG' => "P",
                            'CMONEDA' => "Q",
                            'CBANCO' => "",
                            'FormPago' => "1",
                            'CCODBANCO' => "C55",
                            'CESTADO' => "1",
                            'DFECMOD' =>  $fechadesembolso,
                            'CTERMID' => "0",
                            'MANCOMUNAD' => "0"
                        );
                        $campos = implode(', ', array_keys($datos));
                        $marcadores = '"' . implode('","', ($datos)) . '"';
                        $consulta = "INSERT INTO CREDKAR ($campos) VALUES ($marcadores)";

                        $res = $conexion->query($consulta);
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode([$aux . ' ', '0']);
                            $conexion->rollback();
                            return;
                        }

                        //AFECTACION CONTABLE
                        $numpartidapago = getnumcom($idusuario, $conexion); //Obtener numero de partida
                        $datos = array(
                            'numcom' => $numpartidapago,
                            'id_ctb_tipopoliza' => 1,
                            'id_tb_moneda' => 1,
                            'numdoc' => "CREF",
                            'glosa' => "Cancelacion por refinanzamiento",
                            'fecdoc' => $fechadesembolso,
                            'feccnt' => $fechadesembolso,
                            'cod_aux' => $cuentaref,
                            'id_tb_usu' => $idusuario,
                            'fecmod' => $hoy2,
                            'estado' => 1,
                            'editable' => 0
                        );
                        $campos = implode(', ', array_keys($datos));
                        $marcadores = '"' . implode('","', ($datos)) . '"';
                        $consulta = "INSERT INTO ctb_diario ($campos) VALUES ($marcadores)";

                        $res = $conexion->query($consulta);
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode([$aux . ' ', '0']);
                            $conexion->rollback();
                            return;
                        }
                        $id_ctb_diariopago = get_id_insertado($conexion); //obtener el id insertado en diario
                        //AFECTACION CONTABLE MOV 1 
                        $datos = array(
                            'id_ctb_diario' => $id_ctb_diariopago,
                            'id_fuente_fondo' => $fondoref,
                            'id_ctb_nomenclatura' => $ccntkpref,
                            'debe' => $montoref,
                            'haber' => 0
                        );
                        $campos = implode(', ', array_keys($datos));
                        $marcadores = '"' . implode('","', ($datos)) . '"';
                        $consulta = "INSERT INTO ctb_mov ($campos) VALUES ($marcadores)";

                        $res = $conexion->query($consulta);
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode([$aux . ' ', '0']);
                            $conexion->rollback();
                            return;
                        }
                        //AFECTACION CONTABLE MOV 2 
                        $datos = array(
                            'id_ctb_diario' => $id_ctb_diariopago,
                            'id_fuente_fondo' => $fondoref,
                            'id_ctb_nomenclatura' => $ccntkpref,
                            'debe' => 0,
                            'haber' => $saldoref
                        );
                        $campos = implode(', ', array_keys($datos));
                        $marcadores = '"' . implode('","', ($datos)) . '"';
                        $consulta = "INSERT INTO ctb_mov ($campos) VALUES ($marcadores)";

                        $res = $conexion->query($consulta);
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode([$aux . ' ', '0']);
                            $conexion->rollback();
                            return;
                        }
                        //AFECTACION CONTABLE MOV 3
                        $datos = array(
                            'id_ctb_diario' => $id_ctb_diariopago,
                            'id_fuente_fondo' => $fondoref,
                            'id_ctb_nomenclatura' => $ccntintref,
                            'debe' => 0,
                            'haber' => ($montoref - $saldoref)
                        );
                        $campos = implode(', ', array_keys($datos));
                        $marcadores = '"' . implode('","', ($datos)) . '"';
                        $consulta = "INSERT INTO ctb_mov ($campos) VALUES ($marcadores)";

                        $res = $conexion->query($consulta);
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode([$aux . ' ', '0']);
                            $conexion->rollback();
                            return;
                        }
                        //ACTUALIZACION DE CUOTAS DEL PLAN DE PAGO
                        $res = $conexion->prepare("CALL update_ppg_account(?)");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode([$aux, '0']);
                            $conexion->rollback();
                            return;
                        }
                        $res->bind_param('s', $cuentaref);
                        $res->execute();

                        //READECUACION DE LAS CUOTAS PENDIENTES EN EL PLAN DE PAGO
                        //UPDATE Cre_ppg SET ncapita=(ncapita-ncappag),nintere=(nintere-nintpag) WHERE ccodcta="0020010200000006" AND cestado='X';
                        $res = $conexion->query("UPDATE Cre_ppg SET ncapita=(ncapita-ncappag),nintere=(nintere-nintpag) WHERE ccodcta='" . $cuentaref . "' AND cestado='X';");

                        $aux = mysqli_error($conexion);
                        if (!$res || $aux) {
                            echo json_encode(['Erro 2000: ' . $aux, '0']);
                            $conexion->rollback();
                            return;
                        }

                        //ACTUALIZACION DE CUOTAS DEL PLAN DE PAGO PARA 
                        $res = $conexion->prepare("CALL update_ppg_account(?)");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode([$aux, '0']);
                            $conexion->rollback();
                            return;
                        }
                        $res->bind_param('s', $cuentaref);
                        $res->execute();
                    }
                }
                //validar si todas fueron insertadas
                if ($bandera) {
                    echo json_encode(['Transaccion no completada con éxito', '0']);
                    $conexion->rollback();
                    return;
                }
            }
            //ENVIAR DATOS PARA GENERAR IMPRIMIR EL EFECTIVO
            $conexion->commit();
            echo json_encode(['Correcto,  Desembolso con ' . $tipodesembolso . ' generado, con No.: ' . $numpartida, '1', $inputs[4], $tipodesembolso, $id_ctb_diario]);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'listado_consultar_estado_cuenta': {
            $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cm.CodCli AS codcli, cl.short_name AS nombre, cm.CCODPRD AS codprod, cm.MonSug AS monsug, cm.TipoEnti AS tipocred, cm.Cestado FROM cremcre_meta cm INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente WHERE (cm.Cestado='F' OR cm.Cestado='G') ORDER BY cm.CCODCTA ASC");
            //se cargan los datos de las beneficiarios a un array
            $array_datos = array();
            $total = 0;
            $i = 0;
            while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                $array_datos[] = array(
                    "0" => $i + 1,
                    "1" => $fila["ccodcta"],
                    "2" => $fila["nombre"],
                    "3" => $fila["codprod"],
                    "4" => $fila["monsug"],
                    "5" => ($fila["tipocred"] == 'INDI') ? ('Individual') : ('Grupal'),
                    "6" => ($fila["Cestado"] == 'F') ? ('Vigente') : ('Cancelado'),
                    "7" => '<button type="button" class="btn btn-success btn-sm"  data-bs-dismiss="modal" onclick="printdiv2(`#cuadro`,`' . $fila["ccodcta"] . '`)">Aceptar</button> '
                );
                $i++;
            }
            $results = array(
                "sEcho" => 1,
                "iTotalRecords" => count($array_datos),
                "iTotalDisplayRecords" => count($array_datos),
                "aaData" => $array_datos
            );
            echo json_encode($results);
            mysqli_close($conexion);
        }
        break;
    case 'restructuracionPpg':
        include_once "../../includes/Config/model/sqlBasica/sql.php";
        $sqlBasic = new ConsutlaSql();
        $conn = new Database($db_host, $db_name, $db_user, $db_password, $db_name_general);
        $conn->openConnection();
        $conn->beginTransaction();
        /******************************************************
         *DATOS PARA INICIAR, LA RESTRUCTURACION PPG***********
         ******************************************************/
        $inputs = $_POST['inputs'];
        $selects = $_POST['selects'];
        /******************************************************
         *VALIDAR SI EL CREDITO YA FUE RESTRUCTADO ************
         ******************************************************/
        $data = array(
            'ccodcta' => $inputs[0],
            'cnrocuo' => 0
        );
        $sqlVal = 'SELECT EXISTS (SELECT *FROM Cre_ppg cp WHERE ccodcta = :ccodcta AND cnrocuo = :cnrocuo) restructurado';
        $rst = $conn->selectEspecial($sqlVal, $data);
        if (!$rst) {
            $conn->rollback();
            echo json_encode(['Restructuracion erro 000', '0']);
            return;
        }
        if ($rst == 1) {
            echo json_encode(['El crédito no se puede restructurar por segunda vez... ', '2']);
            return;
        }

        /******************************************************
         *OBTENER UNA COPIA DEL LA Cre_ppg ANTERIOR************
         ******************************************************/
        $creppg_ant = $conn->selectDataID("Cre_ppg", "ccodcta", $inputs[0]);
        foreach ($creppg_ant as $row) {
            $datos = array(
                'ccodcta' => $row['ccodcta'],
                'dfecven' => $row['dfecven'],
                'dfecpag' => $row['dfecpag'],
                'cestado' => $row['cestado'],
                'ctipope' => $row['ctipope'],
                'cnrocuo' => $row['cnrocuo'],
                'SaldoCapital' => $row['SaldoCapital'],
                'nmorpag' => $row['nmorpag'],
                'ncappag' => $row['ncappag'],
                'nintpag' => $row['nintpag'],
                'AhoPrgPag' => $row['AhoPrgPag'],
                'OtrosPagosPag' => $row['OtrosPagosPag'],
                'ccodusu' => $row['ccodusu'],
                'dfecmod' => $row['dfecmod'],
                'cflag' => $row['cflag'],
                'codigo' => $row['codigo'],
                'creditosaf' => $row['creditosaf'],
                'saldo' => $row['saldo'],
                'nintmor' => $row['nintmor'],
                'ncapita' => $row['ncapita'],
                'nintere' => $row['nintere'],
                'NAhoProgra' => $row['NAhoProgra'],
                'OtrosPagos' => $row['OtrosPagos'],
                'delete_by' => $idusuario,
                'OtrosPagos' => $hoy2
            );
            /******************************************************
             *LA COPIA DE LA CRE_PPG INSERTARLA EN LA BITACORA*****
             ******************************************************/
            $sqlBitacoraCre_ppg = $sqlBasic->g_insert("bitacora_Cre_ppg", $datos);
            $rst = $conn->executeQuery($sqlBitacoraCre_ppg, $datos);

            if (!$rst) {
                $conn->rollback();
                echo json_encode(['Restructuracion erro 001', '0']);
                return;
            }
        }
        /******************************************************
         *OBTENER UNA COPIA DEL LA CREMCRE ANTERIOR************
         ******************************************************/
        $cremcre = $conn->selectDataID("cremcre_meta", "CCODCTA", $inputs[0]);
        foreach ($cremcre as $datos) {
            $datos['create_by'] = $idusuario;
            $datos['create_at'] = $hoy2;

            $sqlBitacoraCremcre = $sqlBasic->g_insert("bitacora_cremcre_meta", $datos);
            $rst = $conn->executeQuery($sqlBitacoraCremcre, $datos);

            if (!$rst) {
                $conn->rollback();
                echo json_encode(['Restructuracion erro cop000', '0']);
                return;
            }
        }
        // echo json_encode(['Copia generada de la cremcre', '0']);
        // return;
        /******************************************************
         *OBTENER SALDO E INT PAGADO ASI COMO LA MORA Y MORA PENDIENTE
         ******************************************************/
        $SUM_cap_int_mor_otr = $conn->selectAtributos("SELECT SUM(ncapita) capPag, SUM(nintere) intPag, IFNULL(SUM(nmorpag),0) moraPag, IFNULL(SUM(OtrosPagosPag),0) otrPag FROM", " Cre_ppg ", ['ccodcta', 'cestado'], [$inputs[0], 'P']);
        /******************************************************
         *PERDON DE MORA***************************************
         ******************************************************/
        $diaANT = date("Y-m-d", strtotime("-1 day"));
        $sqlMora = "SELECT ccodcta, cnrocuo, IFNULL(SUM(nmorpag),0) mora FROM Cre_ppg WHERE cestado = :cestado AND dfecven <= :dfecven AND ccodcta = :ccodcta AND nmorpag > :nmorpag";
        $data1 = array(
            'cestado' => "X",
            'dfecven' => $diaANT,
            'ccodcta' => $inputs[0],
            'nmorpag' => 0,
        );

        $perdonMora = $conn->selectNom($sqlMora, $data1);

        foreach ($perdonMora as $row) {
            if (isset($row['mora']) && $row['mora'] > 0) {
                $datos = array(
                    "tipo" => 2,
                    "ccodcta" => $row['ccodcta'],
                    "num_pago" => $row['cnrocuo'],
                    "efec_real" => $row['mora'],
                    "efec_perdonado" => $row['mora'],
                    "created_by" => $idusuario,
                    "created_at" => $hoy2
                );
                $sql_perMora = $sqlBasic->g_insert('tb_rpt_perdon', $datos);
                $rst = $conn->executeQuery($sql_perMora, $datos);
                if (!$rst) {
                    $conn->rollback();
                    echo json_encode(['Restructuracion erro 002', '0']);
                    return;
                }
            }
        }
        /******************************************************
         *ELIMINAR LA CRE_PPG ANTIGUA**************************
         ******************************************************/
        $dataDelete = array(
            'ccodcta' => $inputs[0]
        );
        if (isset($inputs[0])) {
            $conn->delete("Cre_ppg", "ccodcta = :ccodcta", $dataDelete);
        } else {
            $conn->rollback();
            echo json_encode(['Restructuracion erro 003', '0']);
            return;
        }
        /******************************************************
         *CREAR UNA CUOTA DE PAGO NUEVO DONDE SE INCLUE, EL CAP, INT Y MORA PAGADA
         ******************************************************/
        $datos = array(
            'ccodcta' => $inputs[0],
            'dfecven' => $inputs[7],
            'dfecpag' => $inputs[7],
            'cestado' => 'P',
            'ctipope' => 0,
            'cnrocuo' => 0,
            'SaldoCapital' => $inputs[3],
            'nmorpag' => $SUM_cap_int_mor_otr['moraPag'],
            'ncappag' => 0,
            'nintpag' => 0,
            'AhoPrgPag' => 0,
            'OtrosPagosPag' => 0,
            'ccodusu' => $idusuario,
            'dfecmod' => $inputs[7],
            'nintmor' => 0,
            'ncapita' => $SUM_cap_int_mor_otr['capPag'],
            'nintere' => $SUM_cap_int_mor_otr['intPag'],
            'NAhoProgra' => 0,
            'OtrosPagos' => $SUM_cap_int_mor_otr['otrPag'],
        );

        $PAGO0 = $sqlBasic->g_insert('Cre_ppg', $datos);
        $rst = $conn->executeQuery($PAGO0, $datos);
        if (!$rst) {
            $conn->rollback();
            echo json_encode(['Restructuracion erro 004', '0']);
            return;
        }

        /******************************************************
         *ACTUALIZAR LA CREMCRE META CON LOS NUEVOS DATOS******
         ******************************************************/

        $datos = array(
            'CCODPRD' => $inputs[6],
            'NIntApro' => $inputs[2],
            'DfecPago' => $inputs[4],
            'noPeriodo' => $inputs[5],

            'CtipCre' => $selects[0],
            'NtipPerC' => $selects[1]
        );
        $upCremcre = $sqlBasic->g_update("cremcre_meta", $datos, "CCODCTA");
        $datos['CCODCTA'] = $inputs[0];

        // echo json_encode(['Restructuracion erro 005 ' . $upCremcre, '0']);
        // $conn->rollback();
        // return;

        $rst = $conn->executeQuery($upCremcre, $datos);

        if (!$rst) {
            $conn->rollback();
            echo json_encode(['Restructuracion erro 005', '0']);
            return;
        }
        $conn->commit(); //NOTA. Se realizo el comit en esta parte ya que la coneccion que esta utilizando es PDO+POO+CONSULTA PREPARADA y la conexion que genera la ppg es basica
        /******************************************************
         *CREAR EL NUEVO PLAN DE PAGOS *************************
         ******************************************************/
        $rst = creppg_INST($inputs[0], $conexion, $inputs[3]);
        if (!$rst) {
            $conn->rollback();
            echo json_encode(['Restructuracion erro 004', '0']);
            return;
        }

        echo json_encode(['La restructuración de plan de pagos se generó y guardo con éxito. ¡Piensa en grande, piensa en microsystemplus!', '1']);
        return;

        break;
    case 'buscar_actividadeconomica':
        $id = $_POST['id'];
        $data[] = [];
        $bandera = true;
        $consulta = mysqli_query($general, "SELECT act.id_ActiEcono AS id, act.Titulo AS descripcion FROM `tb_ActiEcono` act where act.Id_SctrEcono='$id'");
        $aux = mysqli_error($general);
        if ($aux) {
            echo json_encode([$aux, '0']);
            return;
        }
        if ($consulta) {
            $i = 0;
            while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                $bandera = false;
                $data[$i] = $fila;
                $i++;
            }
            if ($bandera) {
                echo json_encode(['El sector no cuenta con actividades economicas', '0']);
                return;
            }
            echo json_encode(['Satisfactorio', '1', $data]);
        } else {
            echo json_encode(['Error en la recuperacion de cuentas de bancos, intente nuevamente', '0']);
        }
        mysqli_close($general);
        break;
    case 'lincred':
        $consulta = mysqli_query($conexion, "SELECT pro.id,pro.cod_producto,pro.nombre nompro,pro.descripcion descriprod,ff.descripcion fondesc,pro.tasa_interes, pro.monto_maximo
            FROM cre_productos pro
            INNER JOIN ctb_fuente_fondos ff ON ff.id=pro.id_fondo WHERE pro.estado=1
             ORDER BY pro.id ASC");
        $array_datos = array();
        $i = 0;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $array_datos[] = array(
                "0" => $fila["cod_producto"],
                "1" => $fila["descriprod"],
                "2" => $fila["nompro"],
                "3" => $fila["fondesc"],
                "4" => $fila["tasa_interes"],
                "5" => $fila["monto_maximo"],
                "6" => '<button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick= "seleccionar_cuenta_ctb2(`#id_modal_hidden`,[' . $fila["id"] . ',`' . $fila["cod_producto"] . '`,`' . $fila["nompro"] . '`,`' . $fila["descriprod"] . '`,' . $fila["tasa_interes"] . ',' . $fila["monto_maximo"] . ',`' . $fila["fondesc"] . '`]); cerrar_modal(`#modal_tiposcreditos`, `hide`, `#id_modal_hidden`);" >Aceptar</button>'
            );
            $i++;
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
    case 'create_solicitud':
        //validar todos los campos necesarios
        //[`codcli`,`nomcli`,`ciclo`,`codprod`,`tasaprod`,`maxprod`,`montosol`,`idprod`,`primerpago`,`cuota`,`crecimiento`,`recomendacion`],
        //[`analista`,`destino`,`sector`,`actividadeconomica`,`agenciaaplica`,`tipocred`,`peri`]
        if (!isset($_SESSION['id_agencia'])) {
            echo json_encode(['Sesion expirada, vuelve a iniciar sesion e intente nuevamente', '0']);
            return;
        }
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $archivo = $_POST["archivo"];

        $validar = validar_campos([
            [$inputs[0], "", 'Debe seleccionar un cliente'],
            [$inputs[1], "", 'Debe seleccionar un cliente'],
            [$inputs[2], "", 'Debe tener un ciclo'],
            [$inputs[3], "", 'Debe seleccionar un producto'],
            [$inputs[7], "", 'Debe seleccionar un productor'],
            [$inputs[4], "", 'Debe tener una tasa de interes'],
            [$inputs[5], "", 'Debe existir un monto máximo'],
            [$selects[0], "0", 'Debe seleccionar un analista'],
            [$inputs[6], "", 'Debe digitar un monto a solicitar'],
            [$selects[1], "0", 'Debe seleccionar un destino de crédito'],
            [$selects[2], "0", 'Debe seleccionar un sector económico'],
            [$selects[3], "0", 'Debe seleccionar una actividad económica'],
            [$inputs[9], "", 'Debe digitar la cuota'],
            [$selects[5], "0", 'Debe seleccionar una tipo de credito'],
            [$selects[6], "0", 'Debe seleccionar un tipo de periodo'],
        ]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }

        //validar que el monto maximo no sea mayor que el monto solicitado
        if ($inputs[6] < 0) {
            echo json_encode(["El monto solicitado no puede ser negativo", '0']);
            return;
        }
        if ($inputs[6] < 100) {
            echo json_encode(["El monto solicitado es demasiado pequeño, no es posible solicitar su crédito", '0']);
            return;
        }
        if ($inputs[6] > $inputs[5]) {
            echo json_encode(["No es posible solicitar el crédito, el monto solicitado no puede ser mayor que el monto máximo del producto", '0']);
            return;
        }
        //validar que se ha seleccionado al menos una garantia
        if (!isset($archivo[4])) {
            echo json_encode(["Debe seleccionar al menos una garantia para el crédito a solicitar", '0']);
            return;
        }

        $idagenciacredito = $selects[4];
        //GENERACION DEL CODIGO DE CREDITO
        // $codcredito = getcrecodcta($archivo[0], '01', $conexion);
        $codcredito = getcrecodcuenta($idagenciacredito, '01', $conexion);
        if ($codcredito[0] == 0) {
            echo json_encode(["Fallo!, No se pudo generar el código de crédito", '0']);
            return;
        }
        $codigoagencia = "";
        $consulta = mysqli_query($conexion, "SELECT ofi.cod_agenc FROM tb_agencia ofi WHERE ofi.id_agencia=$idagenciacredito");
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $codigoagencia = $fila["cod_agenc"];
        }
        if ($codigoagencia == "") {
            echo json_encode(["No se encontro la agencia especificada: " . $idagenciacredito, '0']);
            return;
        }

        //INSERTAR EN LA CREMCRE META
        $conexion->autocommit(false);
        try {
            //INSERCCION EN LA CREMCRE META
            $res = $conexion->query("INSERT INTO `cremcre_meta`(`CCODCTA`, `CodCli`, `Cestado`, `MontoSol`, `CODAgencia`, `CodAnal`,`CCODPRD`,`DfecSol`,`Cdescre`,`CSecEco`,`ActoEcono`,`TipoEnti`,`NCiclo`,`NIntApro`,`fecha_operacion`,`DfecPago`,`cuotassolicita`,`crecimiento`,`recomendacion`,`CtipCre`,`NtipPerC`)
            VALUES ('$codcredito[1]','$inputs[0]','A','$inputs[6]','$codigoagencia','$selects[0]','$inputs[7]','$hoy2','$selects[1]','$selects[2]','$selects[3]','INDI','$inputs[2]','$inputs[4]','$hoy','$inputs[8]',$inputs[9],'$inputs[10]','$inputs[11]','$selects[5]','$selects[6]')");
            $aux = mysqli_error($conexion);
            if ($aux) {
                $conexion->rollback();
                echo json_encode(['Error en la inserción de datos del credito', '0']);
                return;
            }
            if (!$res) {
                $conexion->commit();
                echo json_encode(['No se logro guardar los datos del crédito solicitado', '1']);
            }
            //INSERCCION DE GARANTIAS
            for ($i = 0; $i < count($archivo[4]); $i++) {
                $res = $conexion->query("INSERT INTO `tb_garantias_creditos`(`id_cremcre_meta`, `id_garantia`) VALUES ('$codcredito[1]'," . $archivo[4][$i] . ")");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    $conexion->rollback();
                    echo json_encode(['Error en la inserción de garantias del crédito', '0']);
                    return;
                }
                if (!$res) {
                    $conexion->rollback();
                    echo json_encode(['Solicitud no generada satisfactoriamente', '0']);
                }
            }
            $conexion->commit();
            echo json_encode(['Solicitud generada satisfactoriamente, código crédito: ' . $codcredito[1], '1', $codcredito[1]]);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'clientes_a_analizar':
        $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cl.short_name AS nombre, cl.Direccion AS direccion, (SELECT IFNULL(MAX(cm2.NCiclo),0)+1 AS ciclo FROM cremcre_meta cm2 WHERE cm2.CodCli=cm.CodCli AND cm2.TipoEnti='INDI' AND (cm2.Cestado='F' OR cm2.Cestado='G')) AS ciclo, cm.Cestado AS estado FROM cremcre_meta cm
        INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
        WHERE (cm.Cestado='A' OR cm.Cestado='D') AND cm.TipoEnti='INDI'");
        //se cargan los datos de las beneficiarios a un array
        $array_datos = array();
        $i = 0;
        $contador = 1;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $estado = ($fila['estado'] == 'A') ? 'Solicitado' : 'Analizado';
            $array_datos[] = array(
                "0" => $contador,
                "1" => $fila["ccodcta"],
                "2" => $fila["nombre"],
                "3" => $fila["direccion"],
                "4" => $fila["ciclo"],
                "5" => $estado,
                "6" => '<button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="printdiv2(`#cuadro`,`' . $fila["ccodcta"] . '`)" >Aceptar</button>'
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

    case 'cambiar_estado_analisis':
        $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cl.short_name AS nombre, cl.Direccion AS direccion, (SELECT IFNULL(MAX(cm2.NCiclo),0)+1 AS ciclo FROM cremcre_meta cm2 WHERE cm2.CodCli=cm.CodCli AND cm2.TipoEnti='INDI' AND (cm2.Cestado='F' OR cm2.Cestado='G')) AS ciclo, cm.Cestado AS estado FROM cremcre_meta cm
            INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
            WHERE (cm.Cestado='A' OR cm.Cestado='D') AND cm.TipoEnti='INDI'");
        //se cargan los datos de las beneficiarios a un array
        $array_datos = array();
        $i = 0;
        $contador = 1;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $estado = ($fila['estado'] == 'A') ? 'Solicitado' : 'Analizado';
            $array_datos[] = array(
                "0" => $contador,
                "1" => $fila["ccodcta"],
                "2" => $fila["nombre"],
                "3" => $fila["direccion"],
                "4" => $fila["ciclo"],
                "5" => $estado,
                "6" => '<button type="button" class="btn btn-primary" data-bs-dismiss="modal" data-ccodcta="' . $fila["ccodcta"] . '" onclick="enviarPOST(this)">Cambiar a SOLICITUD </button>'
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
    case 'rechazar_individual':
        $id = $_POST["ideliminar"];
        if ($id[0] == "" || $id[1] == "") {
            echo json_encode(['Tiene que seleccionar un crédito a cancelar', '0']);
            return;
        }
        if ($id[2] == "0") {
            echo json_encode(['Tiene que seleccionar un motivo de rechazo', '0']);
            return;
        }

        $conexion->autocommit(false);
        try {
            $res = $conexion->query("UPDATE `cremcre_meta` SET  Cestado='L', `fecha_operacion`='$hoy', id_rechazo_cred='" . $id[2] . "'  WHERE `cremcre_meta`.`CCODCTA`='" . $id[0] . "'");
            $aux = mysqli_error($conexion);
            if ($aux) {
                $conexion->rollback();
                echo json_encode(['Fallo al actualizar datos', '0']);
                return;
            }
            if ($res) {
                $conexion->commit();
                echo json_encode(['Credito rechazado satisfactoriamente', '1']);
            } else {
                $conexion->rollback();
                echo json_encode(['Crédito no rechazado satisfactoriamente', '0']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al hacer la eliminacion: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;

    case 'update_analisis':
        //validar todos los campos necesarios
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $archivo = $_POST["archivo"];
        // `ccodcta`,`codcli`,`nomcli`,`montosol`,`montosug`,`primerpago`,`cuota`,`fecdesembolso`,`dictamen`
        $validar = validar_campos([
            [$inputs[0], "", 'Debe seleccionar un cliente con crédito'],
            [$inputs[1], "", 'Debe seleccionar un cliente con crédito'],
            [$inputs[2], "", 'Debe seleccionar un cliente con crédito'],
            [$inputs[3], "", 'Debe digitar un interes'],
            [$selects[2], "0", 'Debe seleccionar un analista'],
            [$inputs[4], "", 'Debe tener un monto solicitado'],
            [$inputs[5], "", 'Debe digitar un monto a aprobar'],
            [$selects[0], "0", 'Debe seleccionar un tipo de crédito'],
            [$selects[1], "0", 'Debe seleccionar un tipo de periodo'],
            [$inputs[6], "", 'Debe digitar una fecha de primer pago'],
            [$inputs[7], "", 'Debe digitar un numero de cuotas o plazo'],
            [$inputs[8], "", 'Debe digitar una fecha de desembolso'],
            [$inputs[9], "", 'Debe digitar un número de dictamen'],
            [$inputs[10], "", 'Debe seleccionar un producto'],
            [$inputs[11], "", 'Debe seleccionar un producto'],
            [$inputs[12], "", 'Debe existir un monto maximo']
        ]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }

        //validar que el monto maximo no sea mayor que el monto solicitado
        if ($inputs[3] < 0) {
            echo json_encode(["No puede digitar un interes menor a 0", '0']);
            return;
        }
        if ($inputs[5] < 0) {
            echo json_encode(["No puede digitar un monto a aprobar menor a 0", '0']);
            return;
        }
        if ($inputs[5] < 100) {
            echo json_encode(["El monto a aprobar no puede ser muy pequeño, tiene que ser mayor a Q100.00", '0']);
            return;
        }
        // if ($inputs[5] > $inputs[12]) {
        //     echo json_encode(["El monto a aprobar no puede ser mayor que el monto maximo que permite el producto", '0']);
        //     return;
        // }
        /*         if ($inputs[6] < $hoy) {
            echo json_encode(["La fecha de primer pago no pueder ser menor al dia de hoy", '0']);
            return;
        } */
        if ($inputs[7] < 1) {
            echo json_encode(["El número de cuotas tiene que ser al menos 1", '0']);
            return;
        }
        /* if ($inputs[8] < $hoy) {
            echo json_encode(["La fecha de desembolso no pueder ser menor al dia de hoy", '0']);
            return;
        } */

        //ACTUALIZACION EN LA CREMCRE META
        $conexion->autocommit(false);
        try {
            $res = $conexion->query("UPDATE `cremcre_meta` SET  Cestado='A', CCODPRD='$inputs[10]', NIntApro='" . $inputs[3] . "', MonSug='$inputs[5]', CtipCre='$selects[0]', NtipPerC='$selects[1]', DfecPago='$inputs[6]',
            noPeriodo='" . $inputs[7] . "', DfecDsbls='$inputs[8]', Dictamen='$inputs[9]', NCiclo='$archivo[3]', CodAnal='$selects[2]',`fecha_operacion`='$hoy' WHERE `cremcre_meta`.`CCODCTA`='$inputs[0]'");
            $aux = mysqli_error($conexion);
            if ($aux) {
                $conexion->rollback();
                echo json_encode(['Fallo al actualizar datos', '0']);
                return;
            }
            if ($res) {
                $conexion->commit();
                echo json_encode(['Credito analizado satisfactoriamente', '1']);
            } else {
                $conexion->rollback();
                echo json_encode(['Crédito no analizado satisfactoriamente', '0']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al hacer la actualizacion: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'update_garantias':
        //validar todos los campos necesarios
        $inputs = $_POST["inputs"];
        $archivo = $_POST["archivo"];
        // `ccodcta`,`codcli`,`nomcli`,`montosol`,`montosug`,`primerpago`,`cuota`,`fecdesembolso`,`dictamen`
        $validar = validar_campos([
            [$inputs[0], "", 'Debe seleccionar un cliente con crédito'],
            [$inputs[1], "", 'Debe seleccionar un cliente con crédito'],
        ]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }
        //validar que se ha seleccionado al menos una garantia
        if (!isset($archivo[4])) {
            echo json_encode(["Debe seleccionar al menos una garantia para el crédito a aprobar", '0']);
            return;
        }

        //ACTUALIZACION EN LA CREMCRE META
        $conexion->autocommit(false);
        try {
            $res = $conexion->query("DELETE FROM `tb_garantias_creditos` WHERE id_cremcre_meta='$inputs[0]'");
            $aux = mysqli_error($conexion);
            if ($aux) {
                $conexion->rollback();
                echo json_encode(['Error en la actualizacion de garantias del crédito -E1', '0']);
                return;
            }
            if (!$res) {
                $conexion->rollback();
                echo json_encode(['Garantias no actualizadas correctamente-E1', '0']);
            }

            //INSERCCION DE GARANTIAS
            for ($i = 0; $i < count($archivo[4]); $i++) {
                $res = $conexion->query("INSERT INTO `tb_garantias_creditos`(`id_cremcre_meta`, `id_garantia`) VALUES ('$inputs[0]'," . $archivo[4][$i] . ")");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    $conexion->rollback();
                    echo json_encode(['Error en la actualizacion de garantias del crédito-E2', '0']);
                    return;
                }
                if (!$res) {
                    $conexion->rollback();
                    echo json_encode(['Garantias no actualizadas satisfactoriamente-E2', '0']);
                }
            }

            $conexion->commit();
            echo json_encode(['Garantias actualizadas satisfactoriamente', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al hacer la actualizacion: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'create_analisis':
        //validar todos los campos necesarios
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $archivo = $_POST["archivo"];
        // `ccodcta`,`codcli`,`nomcli`,`montosol`,`montosug`,`primerpago`,`cuota`,`fecdesembolso`,`dictamen`
        $validar = validar_campos([
            [$inputs[0], "", 'Debe seleccionar un cliente con crédito'],
            [$inputs[1], "", 'Debe seleccionar un cliente con crédito'],
            [$inputs[2], "", 'Debe seleccionar un cliente con crédito'],
            [$inputs[3], "", 'Debe digitar un interes'],
            [$selects[2], "0", 'Debe seleccionar un analista'],
            [$inputs[4], "", 'Debe tener un monto solicitado'],
            [$inputs[5], "", 'Debe digitar un monto a aprobar'],
            [$selects[0], "0", 'Debe seleccionar un tipo de crédito'],
            [$selects[1], "0", 'Debe seleccionar un tipo de periodo'],
            [$inputs[6], "", 'Debe digitar una fecha de primer pago'],
            [$inputs[7], "", 'Debe digitar un numero de cuotas o plazo'],
            [$inputs[8], "", 'Debe digitar una fecha de desembolso'],
            [$inputs[9], "", 'Debe digitar un número de dictamen'],
            [$inputs[10], "", 'Debe seleccionar un producto'],
            [$inputs[11], "", 'Debe seleccionar un producto'],
            [$inputs[12], "", 'Debe existir un monto maximo']
        ]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }
        //validar que el monto maximo no sea mayor que el monto solicitado
        if ($inputs[3] < 0) {
            echo json_encode(["No puede digitar un interes menor a 0", '0']);
            return;
        }
        if (!$archivo[4]) {
            echo json_encode(["No puede aprobar el crédito porque no tiene al menos una garantía", '0']);
            return;
        }
        if ($inputs[5] < 0) {
            echo json_encode(["No puede digitar un monto a aprobar menor a 0", '0']);
            return;
        }
        if ($inputs[5] < 100) {
            echo json_encode(["El monto a aprobar no puede ser muy pequeño, tiene que ser mayor a Q100.00", '0']);
            return;
        }
        if ($inputs[5] > $inputs[12]) {
            echo json_encode(["El monto a aprobar no puede ser mayor que el monto maximo que permite el producto", '0']);
            return;
        }
        /*         if ($inputs[6] < $hoy) {
            echo json_encode(["La fecha de primer pago no pueder ser menor al dia de hoy", '0']);
            return;
        } */
        if ($inputs[7] < 1) {
            echo json_encode(["El número de cuotas tiene que ser al menos 1", '0']);
            return;
        }
        /*         if ($inputs[8] < $hoy) {
            echo json_encode(["La fecha de desembolso no pueder ser menor al dia de hoy", '0']);
            return;
        } */
        //validar que se ha seleccionado al menos una garantia
        if (!isset($archivo[5])) {
            echo json_encode(["Debe seleccionar al menos una garantia para el crédito a aprobar", '0']);
            return;
        }
        //ACTUALIZACION EN LA CREMCRE META
        $conexion->autocommit(false);
        try {
            //ACTUALIZACION EN LA CREMCRE META
            $res = $conexion->query("UPDATE `cremcre_meta` SET  Cestado='D', CCODPRD='$inputs[10]', NIntApro='$inputs[3]', MonSug='$inputs[5]', CtipCre='$selects[0]', NtipPerC='$selects[1]', DfecPago='$inputs[6]',
            noPeriodo='$inputs[7]', DfecDsbls='$inputs[8]', Dictamen='$inputs[9]', NCiclo='$archivo[3]', DFecAnal='$hoy2', CodAnal='$selects[2]',`fecha_operacion`='$hoy' WHERE `cremcre_meta`.`CCODCTA`='$inputs[0]'");
            $aux = mysqli_error($conexion);
            if ($aux) {
                $conexion->rollback();
                echo json_encode(['Error al aprobar el credito', '0']);
                return;
            }
            if (!$res) {
                $conexion->rollback();
                echo json_encode(['Crédito no aprobado satisfactoriamente', '0']);
            }
            //ELIMINACION DE DATOS DE GARANTIAS
            $res = $conexion->query("DELETE FROM `tb_garantias_creditos` WHERE id_cremcre_meta='$inputs[0]'");
            $aux = mysqli_error($conexion);
            if ($aux) {
                $conexion->rollback();
                echo json_encode(['Error en la actualizacion de garantias del crédito -E1', '0']);
                return;
            }
            if (!$res) {
                $conexion->rollback();
                echo json_encode(['Garantias no actualizadas correctamente-E1', '0']);
            }

            //INSERCCION DE GARANTIAS
            for ($i = 0; $i < count($archivo[5]); $i++) {
                $res = $conexion->query("INSERT INTO `tb_garantias_creditos`(`id_cremcre_meta`, `id_garantia`) VALUES ('$inputs[0]'," . $archivo[5][$i] . ")");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    $conexion->rollback();
                    echo json_encode(['Error en la actualizacion de garantias del crédito-E2', '0']);
                    return;
                }
                if (!$res) {
                    $conexion->rollback();
                    echo json_encode(['Garantias no actualizadas satisfactoriamente-E2', '0']);
                }
            }

            $conexion->commit();
            echo json_encode(['Credito aprobado satisfactoriamente', '1', $inputs[0]]);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al hacer la actualizacion: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;

    case 'clientes_a_aprobar':
        $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cl.short_name AS nombre, cl.Direccion AS direccion, (SELECT IFNULL(MAX(cm2.NCiclo),0)+1 AS ciclo FROM cremcre_meta cm2 WHERE cm2.CodCli=cm.CodCli AND cm2.TipoEnti='INDI' AND (cm2.Cestado='F' OR cm2.Cestado='G')) AS ciclo, cm.Cestado AS estado FROM cremcre_meta cm
        INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
        WHERE cm.Cestado='D' AND cm.TipoEnti='INDI'");
        //se cargan los datos de las beneficiarios a un array
        $array_datos = array();
        $i = 0;
        $contador = 1;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $estado = ($fila['estado'] == 'D') ? 'Analizado' : ' ';
            $array_datos[] = array(
                "0" => $contador,
                "1" => $fila["ccodcta"],
                "2" => $fila["nombre"],
                "3" => $fila["direccion"],
                "4" => $fila["ciclo"],
                "5" => $estado,
                "6" => '<button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="printdiv2(`#cuadro`,`' . $fila["ccodcta"] . '`)" >Aceptar</button>'
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

    case 'cred_analisis_a_solicitud':
        $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cl.short_name AS nombre, cl.Direccion AS direccion, (SELECT IFNULL(MAX(cm2.NCiclo),0)+1 AS ciclo FROM cremcre_meta cm2 WHERE cm2.CodCli=cm.CodCli AND cm2.TipoEnti='INDI' AND (cm2.Cestado='F' OR cm2.Cestado='G')) AS ciclo, cm.Cestado AS estado FROM cremcre_meta cm
            INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
            WHERE cm.Cestado='D' AND cm.TipoEnti='INDI'");
        //se cargan los datos de las beneficiarios a un array
        $array_datos = array();
        $i = 0;
        $contador = 1;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $estado = ($fila['estado'] == 'D') ? 'Analizado' : ' ';
            $array_datos[] = array(
                "0" => $contador,
                "1" => $fila["ccodcta"],
                "2" => $fila["nombre"],
                "3" => $fila["direccion"],
                "4" => $fila["ciclo"],
                "5" => $estado,
                "6" => '<button type="button" class="btn btn-warning" data-bs-dismiss="modal"   data-ccodcta="' . $fila["ccodcta"] . '" onclick="enviarAprob(this)">Aprobacion a Solicitud</button>'
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

    case 'listadp_desembolso_a_solicitud':

        $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cl.short_name, cm.CodCli AS codcli, cm.CODAgencia AS codagencia, pd.cod_producto AS codproducto, cm.MonSug AS monto, cm.Cestado AS estado   FROM cremcre_meta cm
        INNER JOIN cre_productos pd ON cm.CCODPRD=pd.id
        INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente WHERE cm.Cestado='E' AND cm.TipoEnti='INDI'");
        //se cargan los datos de las beneficiarios a un array
        $array_datos = array();
        $array_parenteco[] = [];
        $total = 0;
        $i = 0;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $array_datos[] = array(
                "0" => $i + 1,
                "1" => $fila["short_name"],
                "2" => $fila["codproducto"],
                "3" => $fila["ccodcta"],
                "4" => $fila["monto"],
                "5" => '<button type="button" class="btn btn-danger" data-bs-dismiss="modal"   data-ccodcta="' . $fila["ccodcta"] . '" onclick="enviarDesem(this)">Desembolso a Solicitud</button>'
            );
            $i++;

            // eliminar(ideliminar, dir, xtra, condi)
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

    case 'create_aprobacion':
        //validar todos los campos necesarios
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $archivo = $_POST["archivo"];
        // `ccodcta`,`codcli`,`nomcli`,`montosol`,`montosug`,`primerpago`,`cuota`,`fecdesembolso`,`dictamen`
        $validar = validar_campos([
            [$inputs[0], "", 'Debe seleccionar un cliente con crédito'],
            [$inputs[1], "", 'Debe seleccionar un cliente con crédito'],
            [$inputs[2], "", 'Debe seleccionar un cliente con crédito'],
            [$inputs[3], "", 'Debe tener un código de producto'],
            [$inputs[4], "", 'Debe tener un código de producto'],
            [$selects[0], "", 'Debe seleccionar un tipo de contrato'],
            [$archivo[3], "", 'Debe tener un ciclo'],
        ]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }
        //ACTUALIZACION EN LA CREMCRE META
        $conexion->autocommit(false);
        try {
            $res = $conexion->query("UPDATE `cremcre_meta` SET  Cestado='E', CTipCon='$selects[0]', NCiclo='$archivo[3]', DFecApr='$hoy2',`fecha_operacion`='$hoy' WHERE `cremcre_meta`.`CCODCTA`='$inputs[0]'");
            $aux = mysqli_error($conexion);
            if ($aux) {
                $conexion->rollback();
                echo json_encode(['Fallo al confirmar aprobación de crédito', '0']);
                return;
            }
            if ($res) {
                $conexion->commit();
                echo json_encode(['Credito aprobado satisfactoriamente', '1', $inputs[0]]);
            } else {
                $conexion->rollback();
                echo json_encode(['Crédito no aprobado satisfactoriamente', '0']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al hacer la actualizacion: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'consultar_garantias': {
            $consulta = mysqli_query($conexion, "SELECT cm.CCODCTA AS ccodcta, cl.short_name, cm.CodCli AS codcli, cm.CODAgencia AS codagencia, pd.cod_producto AS codproducto, cm.MonSug AS monto, cm.Cestado AS estado   FROM cremcre_meta cm
            INNER JOIN cre_productos pd ON cm.CCODPRD=pd.id
            INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente WHERE cm.Cestado='E'");
            //se cargan los datos de las beneficiarios a un array
            $array_datos = array();
            $array_parenteco[] = [];
            $total = 0;
            $i = 0;
            while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                $array_datos[] = array(
                    "0" => $i + 1,
                    "1" => $fila["short_name"],
                    "2" => $fila["codproducto"],
                    "3" => $fila["ccodcta"],
                    "4" => $fila["monto"],
                    // "5" => '<button type="button" class="btn btn-success" onclick="seleccionar_credito_a_desembolsar(`#id_modal_hidden`,[`' . $fila["codcli"] . '`,`' . $fila["short_name"] . '`,`' . $fila["codagencia"] . '`,`' . $fila["codproducto"] . '`,`' . $fila["ccodcta"] . '`,`' . $fila["monto"] . '`]); consultar_gastos_monto(`' . $fila["ccodcta"] . '`); mostrar_tabla_gastos(`' . $fila["ccodcta"] . '`); buscar_cuentas(); cerrar_modal(`#modal_creditos_a_desembolsar`, `hide`, `#id_modal_hidden`);">Aceptar</button> '
                    "5" => '<button type="button" class="btn btn-success" onclick="seleccionar_credito_a_desembolsar(`#id_modal_hidden`,[`' . $fila["codcli"] . '`,`' . $fila["short_name"] . '`,`' . $fila["codagencia"] . '`,`' . $fila["codproducto"] . '`,`' . $fila["ccodcta"] . '`,`' . $fila["monto"] . '`]); consultar_gastos_monto(`' . $fila["ccodcta"] . '`); mostrar_tabla_gastos(`' . $fila["ccodcta"] . '`); cerrar_modal(`#modal_creditos_a_desembolsar`, `hide`, `#id_modal_hidden`); $(`#bt_desembolsar`).show(); concepto_default(`' . $fila["short_name"] . '`, `0`);">Aceptar</button>'

                );
                $i++;

                // eliminar(ideliminar, dir, xtra, condi)
            }
            $results = array(
                "sEcho" => 1, //info para datatables
                "iTotalRecords" => count($array_datos), //enviamos el total de registros al datatable
                "iTotalDisplayRecords" => count($array_datos), //enviamos el total de registros a visualizar
                "aaData" => $array_datos
            );
            mysqli_close($conexion);
            echo json_encode($results);
        }
        break;
        //Codigo para Garantias **********************************************************************************************************************************
    case 'insertarGarantia':
        if (!isset($_SESSION['id'])) {
            echo json_encode(['La sesion expiró, inicie sesión nuevamente.', '0']);
            return;
        }

        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $archivo = $_POST["archivo"];
        $res;
        $valor = 0;
        try {
            if ($selects[0] != 1 && $selects[1] != 8) {
                $res = $conexion->query("INSERT INTO cli_garantia (idCliente, idTipoGa,idTipoDoc, descripcionGarantia, direccion,
                depa, muni, valorComercial, montoAvaluo, montoGravamen, fechaCreacion, estado, created_by, created_at) VALUES ('$inputs[0]',
                '$selects[0]', '$selects[1]', '$inputs[1]', '$inputs[2]','$selects[2]' ,'$selects[3]', '$inputs[3]','$inputs[4]', '$inputs[5]', '$hoy', '1', '$archivo[0]', '$hoy2')");
            } else if ($selects[0] == 1 && $selects[1] == 1) {

                $valida = $conexion->query("SELECT EXISTS(SELECT cg.descripcionGarantia FROM cli_garantia cg WHERE cg.descripcionGarantia = '$inputs[1]') AS rstd");

                $aux1 = mysqli_error($conexion);
                if ($aux1 && !$valida) {
                    echo json_encode(['Error', '0']);
                    $conexion->rollback();
                    return;
                }

                $rst_rep = $valida->fetch_assoc();

                if ($rst_rep['rstd'] == 1) {
                    echo json_encode(['El fiador selecionado ya se encuentra registrado...', '0']);
                    $conexion->rollback();
                    return;
                }

                $datosFia = mysqli_query($conexion, "SELECT Direccion, depa_reside, muni_reside FROM tb_cliente WHERE estado = 1 AND idcod_cliente = '" . $inputs[1] . "'");
                $data = mysqli_fetch_array($datosFia);
                $dire = $data['Direccion'];
                $depa = $data['depa_reside'];
                $muni = $data['muni_reside'];

                $res = $conexion->query("INSERT INTO cli_garantia (idCliente, idTipoGa,idTipoDoc, descripcionGarantia, direccion,
                depa, muni, valorComercial, montoAvaluo, montoGravamen, fechaCreacion, estado, created_by, created_at) VALUES ('$inputs[0]',
                '$selects[0]', '$selects[1]', '$inputs[1]', '$dire', '$depa','$muni', '0', '0', '0', '$hoy', '1', '$archivo[0]', '$hoy2')");
                $valor = 1;
            } else if ($selects[0] == 3 && $selects[1] == 8) {

                $valida = $conexion->query("SELECT EXISTS(SELECT cg.descripcionGarantia FROM cli_garantia cg WHERE cg.descripcionGarantia = '$inputs[1]') AS rstd");

                $aux1 = mysqli_error($conexion);
                if ($aux1 && !$valida) {
                    echo json_encode(['Error', '0']);
                    $conexion->rollback();
                    return;
                }

                $rst_rep = $valida->fetch_assoc();

                if ($rst_rep['rstd'] == 1) {
                    echo json_encode(['La cuenta selecciona ya se encuentra registrada... ', '0']);
                    $conexion->rollback();
                    return;
                }

                $res = $conexion->query("INSERT INTO cli_garantia (idCliente, idTipoGa, idTipoDoc, descripcionGarantia,
                depa, muni, valorComercial, montoAvaluo, montoGravamen, fechaCreacion, estado, created_by, created_at) VALUES ('$inputs[0]',
                '$selects[0]', '$selects[1]', '$inputs[1]', '$selects[2]','$selects[3]', '0', '0', '$inputs[2]', '$hoy', '1', '$archivo[0]', '$hoy2')");
                $valor = 1;
            }

            //ini pack
            $aux = mysqli_error($conexion);
            // echo $aux;

            if ($aux) {
                echo json_encode(['Error', '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['Error al ingresar ', '0']);
                $conexion->rollback();
                return;
            }
            $conexion->commit();
            echo json_encode(['Datos registrados con exito', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, al hacer el registro: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;

        //Acatualizar Garantia
    case 'actualizaGarantia':

        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $archivo = $_POST["archivo"];

        try {
            if ($selects[0] == 1 && $selects[1] == 1) {
                $valida = $conexion->query("SELECT EXISTS(SELECT cg.descripcionGarantia FROM cli_garantia cg WHERE cg.descripcionGarantia = '$inputs[1]') AS rstd");

                $aux1 = mysqli_error($conexion);
                if ($aux1 && !$valida) {
                    echo json_encode(['Error', '0']);
                    $conexion->rollback();
                    return;
                }

                $rst_rep = $valida->fetch_assoc();

                if ($rst_rep['rstd'] == 1) {
                    echo json_encode(['El fiador selecionado ya se encuentra registrado...', '0']);
                    $conexion->rollback();
                    return;
                }
            }

            if ($selects[0] == 3 && $selects[1] == 8) {
                $valida = $conexion->query("SELECT EXISTS(SELECT cg.descripcionGarantia FROM cli_garantia cg WHERE cg.descripcionGarantia = '$inputs[1]') AS rstd");

                $aux1 = mysqli_error($conexion);
                if ($aux1 && !$valida) {
                    echo json_encode(['Error', '0']);
                    $conexion->rollback();
                    return;
                }

                $rst_rep = $valida->fetch_assoc();

                if ($rst_rep['rstd'] == 1) {
                    echo json_encode(['La cuenta selecciona ya se encuentra registrada...', '0']);
                    $conexion->rollback();
                    return;
                }
            }


            $res = $conexion->query("UPDATE cli_garantia SET idTipoGa = " . $selects[0] . ", idTipoDoc = " . $selects[1] . ", descripcionGarantia = '" . $inputs[1] . "', direccion = '" . $inputs[2] . "', depa = " . $selects[2] . ", muni = '" . $selects[3] . "', valorComercial = " . $inputs[3] . ", montoAvaluo = " . $inputs[4] . ", montoGravamen = " . $inputs[5] . ", updated_by = " . $archivo[0] . ", updated_at = '" . $hoy2 . "' WHERE idGarantia = " . $inputs[0]);

            //ini pack
            $aux = mysqli_error($conexion);
            echo $aux;

            if ($aux) {
                echo json_encode(['Error', '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['Error al ingresar ', '0']);
                $conexion->rollback();
                return;
            }
            $conexion->commit();
            echo json_encode(['Datos actualizados con exito', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, al hacer el registro: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;

        //Eliminar garantia
    case 'eliminaGarantia':
        $id = $_POST["ideliminar"];
        $archivo = $_POST["archivo"];
        // echo json_encode([$id, $archivo]);
        // return;

        mysqli_query($conexion, "SELECT dataCre.CCODCTA ,dataCre.Cestado
        FROM tb_garantias_creditos AS garaCre
        INNER JOIN cli_garantia AS gaCli ON gaCli.idGarantia = garaCre.id_garantia
        INNER JOIN cremcre_meta AS dataCre ON garaCre.id_cremcre_meta = dataCre.CCODCTA
        WHERE (dataCre.Cestado = 'A' OR dataCre.Cestado = 'D' OR dataCre.Cestado = 'E' OR dataCre.Cestado = 'F') AND gaCli.idGarantia =" . $id);
        $credi =  mysqli_affected_rows($conexion);

        if ($credi > 0) {
            echo json_encode(['La garantía ya cuenta con un credito, no se puede eliminar', '0']);
            return;
        }

        $conexion->autocommit(false);
        try {
            //$id = $archivo;
            //$res = $conexion->query("UPDATE `cre_tipogastos` SET estado = 0, deleted_by = $archivo, deleted_at='$hoy2' WHERE id =" . $id);
            $res = $conexion->query("UPDATE `cli_garantia` SET estado = 0, deleted_by = $archivo, deleted_at='$hoy2' WHERE idGarantia =" . $id);

            $aux = mysqli_error($conexion);

            if ($aux) {
                echo json_encode(['Error', '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['Error al ingresar ', '0']);
                $conexion->rollback();
                return;
            }
            $conexion->commit();
            echo json_encode(['El dato fue eliminado exitosamente. ', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, al hacer el registro: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;

        //Cargar imagenes
    case "ingresoimg":
        $ccodcli = $_POST["ccodcli"];
        //$rutaEnServidor='../../includes/imagescli';
        $salida = "../../../"; //SUDOMINIOS PROPIOS
        //$salida = "../../../"; // DOMINIO PRINCIPAL CON CARPETAS
        $queryins = mysqli_query($conexion, "SELECT * FROM clhpzzvb_bd_general_coopera.info_coperativa ins
              INNER JOIN tb_agencia ag ON ag.id_institucion=ins.id_cop where ag.id_agencia=" . $_SESSION['id_agencia']);
        $infoi[] = [];
        $j = 0;
        while ($fil = mysqli_fetch_array($queryins)) {
            $infoi[$j] = $fil;
            $j++;
        }
        if ($j == 0) {
            echo json_encode(['No se encuentra la ruta de la organizacion', '0']);
            return;
        }
        $folderprincipal = $infoi[0]['folder'];
        // $entrada = "imgcoope.sotecprotech.com/" . $folderprincipal . "/" . "garantias/" . $ccodcli;
        $entrada = "imgcoope.microsystemplus.com/" . $folderprincipal . "/" . "garantias/" . $ccodcli;
        $rutaEnServidor = $salida . $entrada;

        //comprobar si existe la ruta, si no, se crea
        if (!is_dir($rutaEnServidor)) {
            mkdir($rutaEnServidor, 0777, true);
        }
        //comprobar si se subio una imagen
        if (is_uploaded_file($_FILES['fileImage']['tmp_name'])) {
            $rutaTemporal = $_FILES['fileImage']['tmp_name'];
            //$nombreImagen=$_FILES['fileImage']['name'];
            //con esto la imagen siempre tendra un nombre distinto
            $nombreImagen = $ccodcli . '' . date('Ymdhis'); //asignar nuevo nombre
            $info = pathinfo($_FILES['fileImage']['name']); //extrae la extension
            $nomimagen = '/' . $nombreImagen . "." . $info['extension'];
            $rutaDestino = $rutaEnServidor . $nomimagen;

            $imgData = addslashes(file_get_contents($_FILES['fileImage']['tmp_name'])); //nop se usa, se va a usar por si se guardara la imagen en la bd
            if (($_FILES["fileImage"]["type"] == "image/pjpeg")
                || ($_FILES["fileImage"]["type"] == "image/jpeg")
                || ($_FILES["fileImage"]["type"] == "image/png")
                || ($_FILES["fileImage"]["type"] == "image/gif")
            ) {
                if (move_uploaded_file($rutaTemporal, $rutaDestino)) {
                    //$rutaimagen="../../includes/imagescli/".$_FILES['fileImage']['name'];
                    $consulta2 = mysqli_query($conexion, "UPDATE `cli_garantia` SET `archivo`='" . $entrada . $nomimagen . "' WHERE idGarantia = " . $ccodcli);
                    if ($consulta2) {
                        echo json_encode(['Foto Cargada correctamente', '1']);;
                    } else {
                        echo json_encode(['Fallo al guardar la imagen, error tipo 5', '0']); //error al guardar la ruta en la base de datos tipo 5
                    }
                } else {
                    echo json_encode(['Fallo al guardar la imagen, error tipo 10', '0']); //error al mover el archivo a la ruta tipo 10
                }
            } else {
                echo json_encode(['La extension de la imagen no es permitida', '0']); //error de tipo extension de imagen
            }
        }
        mysqli_close($conexion);
        break;

        //Tabla de garantias
    case 'tablaGarantias':
        $idCli = $_POST["id"];
        $consulta = mysqli_query($conexion, "SELECT  gaCli.idGarantia AS idGa, gaCli.idTipoGa as idGara, tipoGa.TiposGarantia AS garantia, gaCli.idTipoDoc idDoc, tipoDoc.NombreDoc AS doc, gaCli.archivo,
        gaCli.descripcionGarantia AS des, gaCli.direccion, gaCli.depa AS idDepa, (SELECT depa.nombre FROM clhpzzvb_bd_general_coopera.departamentos depa WHERE depa.codigo_departamento=gaCli.depa) AS depa,
		  gaCli.muni AS idMuni, (SELECT muni.nombre FROM clhpzzvb_bd_general_coopera.municipios muni WHERE muni.codigo_municipio=gaCli.muni) AS muni,
		  gaCli.valorComercial, gaCli.montoAvaluo, gaCli.montoGravamen, gaCli.fechaCreacion,
        IF(gaCli.idTipoGa = 1, (SELECT short_name FROM tb_cliente WHERE idcod_cliente = gaCli.descripcionGarantia), 0) AS fiador
        FROM cli_garantia AS gaCli
        INNER JOIN tb_cliente AS cli ON gaCli.idCliente = cli.idcod_cliente
        INNER JOIN clhpzzvb_bd_general_coopera.tb_tiposgarantia AS tipoGa ON gaCli.idTipoGa = tipoGa.id_TiposGarantia
        INNER JOIN clhpzzvb_bd_general_coopera.tb_tiposdocumentosR AS tipoDoc ON gaCli.idTipoDoc = tipoDoc.idDoc
        WHERE gaCli.estado = 1 AND gaCli.idCliente='" . $idCli . "'ORDER BY gaCli.idGarantia DESC");

        $array_datos = array();
        $array_parenteco[] = [];
        $total = 0;
        $i = 0;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $volComer = ($fila["idGara"] == 1 || $fila["idDoc"] == 8) ? '---' : $fila["valorComercial"];
            $monAva = ($fila["idGara"] == 1 || $fila["idDoc"] == 8) ? '---' : $fila["montoAvaluo"];

            $direc = ($fila["idGara"] == 1 || $fila["idDoc"] == 8) ? '---' : $fila["direccion"];

            $monGra = ($fila["idGara"] == 1) ? '---' : $fila["montoGravamen"];


            $id = $fila["idGa"];

            $dato = $fila["idGa"] . "||" . //0
                $dato = $fila["idGara"] . "||" . //1
                $dato = $fila["idDoc"] . "||" . //2
                $dato = $fila["des"] . "||" . //3
                $dato = $fila["idDepa"] . "||" . //4
                $dato = $fila["idMuni"] . "||" . //5
                $dato = $fila["direccion"] . "||" . //6
                $dato = $fila["valorComercial"] . "||" . //7
                $dato = $fila["montoAvaluo"] . "||" . //8
                $dato = $fila["montoGravamen"] . "||" . //9
                $dato = $fila["fiador"] . "||" . //10
                $dato = $fila["archivo"]; //11
            $descip = ($fila["idGara"] == 1) ? $fila["fiador"] : $fila["des"];



            $array_datos[] = array(
                "0" => $i + 1,
                "1" => $fila["garantia"],
                "2" => $fila["doc"],
                "3" => $descip,

                "4" => $fila["depa"],
                "5" => $fila["muni"],
                "6" => $direc,

                "7" => $volComer,
                "8" => $monAva,
                "9" => $monGra,

                "10" => "<button type='button' class='btn btn-primary' onclick='editargarantia(\"$dato\")'><i class='fa-solid fa-pen-to-square'></i></button> <button type='button' class='btn btn-danger mt-1' onclick='eliminar(\"$id\")'><i class='fa-solid fa-trash-can'></i></button>"

            );
            $i++;

            // eliminar(ideliminar, dir, xtra, condi)
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

    case 'actMasPlanPagos':
        $matrizJSON = $_POST['matriz'];
        $codCu = $_POST['extra'];
        $matriz = json_decode($matrizJSON, true);
        $res;
        $res1;
        $conexion->autocommit(false);

        try {
            $numF = count($matriz);
            $numC = count($matriz[0]);
            $idKill = array();
            $dataInset = array();
            // Bloque de código para crear un array de las ID que se tienen que eliminar
            if (isset($codCu)) {
                $res1 = $conexion->query("DELETE FROM Cre_ppg WHERE ccodcta = " . $codCu);
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error: ' . $aux, '0']);
                    $conexion->rollback();
                    return;
                }
            }

            //Blokc de codigo, para crear un array de los datos a insertar
            for ($fil = 0; $fil < $numF; $fil++) {
                $res = $conexion->query("INSERT INTO Cre_ppg (ccodcta, dfecven, cestado, cnrocuo, ncappag, nintpag, OtrosPagosPag, ncapita, nintere, SaldoCapital,dfecmod) VALUES ('$codCu', '{$matriz[$fil][1]}', 'X', {$matriz[$fil][2]}, {$matriz[$fil][3]}, {$matriz[$fil][4]}, {$matriz[$fil][5]}, {$matriz[$fil][3]}, {$matriz[$fil][4]}, {$matriz[$fil][6]},'$hoy')");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['Error: ' . $aux, '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    echo json_encode(['Error: ' . $aux, '0']);
                    $conexion->rollback();
                    return;
                }
            }
            $res4 = $conexion->query("CALL update_ppg_account('" . $codCu . "')");
            if (!$res4) {
                echo json_encode(['Error al actualizar el plan de pago ', '0']);
                $conexion->rollback();
                return;
            }

            if ($res && $res1) {
                $conexion->commit();
                echo json_encode(['Plan de pago actualizado correctamente', '1']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al actualizar el plan de pago 2', '0']);
        }
        break;

        //Funcion para eliminar la fila de plan de pagos
    case 'deleteFilaPlanDePagos':

        $id = $_POST['ideliminar'];

        $conexion->autocommit(false);

        $res = $conexion->query("DELETE FROM Cre_ppg WHERE Id_ppg = " . $id);

        $aux = mysqli_error($conexion);
        echo $aux;

        if ($aux) {
            echo json_encode(['Error', '0']);
            $conexion->rollback();
            return;
        }

        if (!$res) {
            echo 'Error al ingresar';
            $conexion->rollback();
            return;
        }

        if ($res) {
            $conexion->commit();
            echo json_encode(['Los datos, se eliminaron exitosamente. ', '1']);
            mysqli_close($conexion);
        }
        break;

    case 'PlanPagos':
        $codCu = $_POST['extra'];

        $slq = mysqli_query($conexion, "SELECT EXISTS(SELECT a.estado FROM tb_autorizacion a
        INNER JOIN clhpzzvb_bd_general_coopera.tb_rol r ON r.id = a.id_rol
        INNER JOIN clhpzzvb_bd_general_coopera.tb_restringido rs ON rs.id = a.id_restringido
        WHERE r.siglas = 'ADM' AND a.id_restringido = 1 AND a.estado = 1) AS rst");

        $rst = $slq->fetch_assoc()['rst'];

        ob_start();
        $consulta = mysqli_query($conexion, "SELECT credi.NCapDes, pagos.Id_ppg AS id, pagos.dfecven AS fecha, pagos.Cestado AS estado, pagos.cnrocuo AS cuota, pagos.ncapita, pagos.nintere, pagos.OtrosPagos, pagos.SaldoCapital
            FROM  Cre_ppg AS pagos
            INNER JOIN cremcre_meta AS credi ON pagos.ccodcta = credi.CCODCTA
            WHERE credi.Cestado = 'F'  AND credi.ccodcta = '$codCu'");
        $con = 0;
        $aux = 0;
        $ban = true;
        while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $con++;
?>
            <tr>
                <?php if ($ban) {
                    $aux = $row['NCapDes'];
                    $ban = false;
                } ?>
                <?php
                $aux = bcdiv(($aux - $row['ncapita']), '1', 2);
                // $auxEstado = ($esCou == "X") ? echo "<i class="fa-solid fa-money-bill" style="color: #c01111;"></i>": echo "<i class='fa-duotone fa-money-bill' style='--fa-primary-color: #0dab2c; --fa-secondary-color: #30d952;'></i>";

                $auxEstado = ($row['estado'] == "X") ? '<i class="fa-solid fa-money-bill" style="color: #c01111;"></i>' : '<i class="fa-solid fa-money-bill" style="color: #178109;"></i>';

                // $aux=$aux-$row['ncapita']
                ?>
                <td id="<?= $con . 'idCon' ?>"><?= $con ?></td> <!-- No -->
                <td id="idDes<?= $con; ?>" hidden> <?= $row['NCapDes'] ?></td> <!-- Capital Desembolsado -->
                <td id="<?= $con . 'idData' ?>" name="idPP[]" hidden><?= $row['id'] ?></td> <!-- ID -->
                <td><input id="<?= $con . 'fechaP' ?>" type="date" name="fecha[]" class="form-control" value="<?= $row['fecha'] ?>" onblur="validaF()"></td> <!-- Fecha -->
                <td><?= $auxEstado ?></td> <!-- Estado -->
                <td name="noCuo[]"><?= $row['cuota'] ?></td> <!-- No Cuota -->
                <td><input min="0" step="0.01" id="<?= $con . 'cap' ?>" name="capita[]" onkeyup="calPlanDePago()" type="number" class="form-control" value="<?= $row['ncapita'] ?>"></td> <!-- Capital -->
                <td><input min="0" step="0.01" id="<?= $con . 'inte' ?>" name="interes[]" onkeyup="calPlanDePago()" onchange="validaInteres(['<?= $usu ?>','<?= $codCu ?>', '<?= $row['cuota'] ?>', '<?= $con . 'inte' ?>','<?= $row['nintere'] ?>'])" type="number" class="form-control" value="<?= $row['nintere'] ?>" min="0" <?= ($rst == 1) ? "" : "disabled" ?>></td> <!-- Interes -->
                <td><input min="0" step="0.01" id="<?= $con . 'otros' ?>" name="otrosP[]" onkeyup="calPlanDePago()" type="number" class="form-control" value="<?= $row['OtrosPagos'] ?>" min="0"></td> <!-- Otros -->
                <td id="<?= $con . 'salCap' ?>" name="saldoCap[]"> <?= $aux ?> </td> <!-- Saldo Capital -->
                <td id="<?= $con . 'total' ?>"><?= ($row['ncapita'] + $row['nintere'] + $row['OtrosPagos']) ?></td> <!-- Total -->
            </tr>

        <?php }
        $output = ob_get_clean();
        echo $output;
        break;


    case 'couFech':
        $codCu = $_POST['extra'];

        //Obtener Codigo de cuenta de uno de los clientes
        $consulta = mysqli_query($conexion, "SELECT credi.CCODCTA AS codCu, gruCli.Codigo_grupo AS grup
              FROM tb_cliente_tb_grupo AS gruCli
              INNER JOIN tb_cliente AS cli ON gruCli.cliente_id = cli.idcod_cliente
              INNER JOIN cremcre_meta AS credi ON cli.idcod_cliente = credi.CodCli
              WHERE credi.cestado = 'F' AND gruCli.Codigo_grupo = $codCu[0] AND credi.NCiclo = $codCu[1] AND gruCli.estado = 1 AND credi.TipoEnti = 'GRUP' GROUP BY grup");

        $dato = mysqli_fetch_assoc($consulta);
        $codCu = $dato['codCu'];

        ob_start();
        $consulta = mysqli_query($conexion, "SELECT pagos.dfecven AS fecha, pagos.cnrocuo AS cuota
                                                        FROM  Cre_ppg AS pagos
                                                        INNER JOIN cremcre_meta AS credi ON pagos.ccodcta = credi.CCODCTA
                                                        WHERE credi.Cestado = 'F'  AND credi.ccodcta = '$codCu'");
        $con = 0;
        while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $con++;
        ?>

            <tr>
                <td id="<?= $con . 'conRow' ?>" hidden>kill</td> <!-- ID -->
                <td name="noCuo[]" id="<?= $row['cuota'] . 'idCon' ?>"> <?= $row['cuota'] ?> </td> <!-- No Cuota -->
                <td><input id="<?= $con . 'fechaP' ?>" type="date" name="fecha[]" class="form-control" value="<?= $row['fecha'] ?>" onchange="validaF()"></td> <!-- Fecha -->
            </tr>

            <?php
        }
        $output = ob_get_clean();
        echo $output;
        break;

        //Plan de pago de gurpos
    case 'planPagoGru':

        function planPago($conexion, $codCu)
        {
            $consulta1 = mysqli_query($conexion, "SELECT pagos.Id_ppg AS id, credi.NCapDes, pagos.Cestado AS estado, pagos.ncapita, pagos.nintere, pagos.OtrosPagos, pagos.SaldoCapital
            FROM  Cre_ppg AS pagos
            INNER JOIN cremcre_meta AS credi ON pagos.ccodcta = credi.CCODCTA
            WHERE credi.Cestado = 'F'  AND credi.ccodcta = '$codCu'");
            return $consulta1;
        }

        $codGru = $_POST['extra'];

        //Obtener Todos los numeros de cuenta
        $consulta = mysqli_query($conexion, "SELECT credi.CCODCTA AS codCu, cli.short_name AS nombre, credi.NCapDes AS capDes
                        FROM tb_cliente_tb_grupo AS gruCli
                        INNER JOIN tb_cliente AS cli ON gruCli.cliente_id = cli.idcod_cliente
                        INNER JOIN cremcre_meta AS credi ON cli.idcod_cliente = credi.CodCli
                        WHERE credi.cestado = 'F' AND gruCli.Codigo_grupo = $codGru[0] AND credi.NCiclo = $codGru[1] AND gruCli.estado = 1 AND credi.TipoEnti = 'GRUP';");

        $error = mysqli_error($conexion);
        $con = 0;

        ob_start();

        while ($rowData = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) { //Extraer cada nnumero de cuenta de forma individual

            $codCu = $rowData['codCu'];
            $consulta1 = planPago($conexion, $codCu);

            if ($con == 0) {
            ?>
                <div class="carousel-item active">

                    <!-- INI TABAL -->

                    <!-- INICIO DE LA TABLA -->
                    <div class="container table-responsive">
                        <table class="table" id="<?= $rowData['codCu'] ?>" name="tbCodCu[]">
                            <label><b>Cuenta:</b> </label> <label id="<?= 'codCu' . $rowData['codCu'] ?>"> <?= $rowData['codCu'] ?>
                            </label><br>
                            <label><b>Cliente:</b> <?= $rowData['nombre'] ?> <b>Capital desembolsado: Q </b></label> <label id="<?= 'capDes' . $rowData['codCu'] ?>"> <?= $rowData['capDes'] ?></label>
                            <thead class="table-dark">
                                <tr>
                                    <th class="col-1">Estado</th>
                                    <th class="col-2">Capital</th>
                                    <th class="col-2">Interes</th>
                                    <th class="col-2">Otros pagos</th>
                                    <th class="col-2">S. Capital</th>
                                    <th class="col-2">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- INI -->
                                <?php
                                $aux = 0;
                                $flag = true;
                                $con1 = 1;
                                while ($row = mysqli_fetch_array($consulta1, MYSQLI_ASSOC)) {
                                    if ($flag) {
                                        $aux = $rowData['capDes'];
                                        $flag = false;
                                    }
                                    $aux = bcdiv(($aux - $row['ncapita']), '1', 2);
                                    $auxEstado = ($row['estado'] == "X") ? '<i class="fa-solid fa-money-bill" style="color: #c01111;"></i>' : '<i class="fa-solid fa-money-bill" style="color: #178109;"></i>';

                                ?>
                                    <tr>
                                        <td id="<?= $con1 . 'idData' . $rowData['codCu'] ?>" name="<?= $rowData['codCu'] . 'idPP[]' ?>" hidden><?= $row['id'] ?></td> <!-- ID -->
                                        <td><?= $auxEstado ?></td> <!-- Estado -->
                                        <td><input min="0" step="0.01" id="<?= $con1 . 'cap' . $rowData['codCu'] ?>" name="<?= $rowData['codCu'] . 'capita[]' ?>" onkeyup="calPlanDePago('<?= $rowData['codCu'] ?>')" type="number" class="form-control" value="<?= $row['ncapita'] ?>"></td> <!-- Capital -->
                                        <td><input min="0" step="0.01" id="<?= $con1 . 'inte' . $rowData['codCu'] ?>" name="<?= $rowData['codCu'] . 'interes[]' ?>" onkeyup="calPlanDePago('<?= $rowData['codCu'] ?>')" type="number" class="form-control" value="<?= $row['nintere'] ?>" min="0"></td> <!-- Interes -->
                                        <td><input min="0" step="0.01" id="<?= $con1 . 'otros' . $rowData['codCu'] ?>" name="<?= $rowData['codCu'] . 'otrosP[]' ?>" onkeyup="calPlanDePago('<?= $rowData['codCu'] ?>')" type="number" class="form-control" value="<?= $row['OtrosPagos'] ?>" min="0"></td> <!-- Otros -->
                                        <td id="<?= $con1 . 'salCap' . $rowData['codCu'] ?>" name="<?= $rowData['codCu'] . 'saldoCap[]' ?>">
                                            <?= $aux ?> </td> <!-- Saldo Capital -->
                                        <td id="<?= $con1 . 'total' . $rowData['codCu'] ?>">
                                            <?= ($row['ncapita'] + $row['nintere'] + $row['OtrosPagos']) ?></td> <!-- Total -->
                                    </tr>
                                <?php
                                    $con1++;
                                }
                                ?>
                                <!-- FIN -->
                            </tbody>

                        </table>
                    </div>
                    <!-- FIN DE LA TABLA -->

                    <!-- FIN TABLA -->

                </div>
            <?php
            } else {
            ?>
                <div class="carousel-item">

                    <!-- INI TABAL -->

                    <!-- INICIO DE LA TABLA -->
                    <div class="container table-responsive">

                        <table class="table" id="<?= $rowData['codCu'] ?>" name="tbCodCu[]">
                            <label><b>Cuenta:</b> </label> <label id="<?= 'codCu' . $rowData['codCu'] ?>"> <?= $rowData['codCu'] ?>
                            </label><br>
                            <label><b>Cliente:</b> <?= $rowData['nombre'] ?> <b>Capital desembolsado: Q </b></label> <label id="<?= 'capDes' . $rowData['codCu'] ?>"> <?= $rowData['capDes'] ?></label>
                            <thead class="table-dark">
                                <tr>
                                    <th class="col-1">Estado</th>
                                    <th class="col-2">Capital</th>
                                    <th class="col-2">Interes</th>
                                    <th class="col-2">Otros pagos</th>
                                    <th class="col-2">S. Capital</th>
                                    <th class="col-2">Total</th>
                                </tr>
                            </thead>

                            <tbody>
                                <!-- INI -->
                                <?php
                                $aux = 0;
                                $flag = true;
                                $con1 = 1;
                                while ($row = mysqli_fetch_array($consulta1, MYSQLI_ASSOC)) {
                                    if ($flag) {
                                        $aux = $rowData['capDes'];
                                        $flag = false;
                                    }
                                    $aux = bcdiv(($aux - $row['ncapita']), '1', 2);
                                    $auxEstado = ($row['estado'] == "X") ? '<i class="fa-solid fa-money-bill" style="color: #c01111;"></i>' : '<i class="fa-solid fa-money-bill" style="color: #178109;"></i>';

                                ?>
                                    <tr>
                                        <td id="<?= $con1 . 'idData' . $rowData['codCu'] ?>" name="<?= $rowData['codCu'] . 'idPP[]' ?>" hidden><?= $row['id'] ?></td> <!-- ID -->
                                        <td><?= $auxEstado ?></td> <!-- Estado -->
                                        <td><input min="0" step="0.01" id="<?= $con1 . 'cap' . $rowData['codCu'] ?>" name="<?= $rowData['codCu'] . 'capita[]' ?>" onkeyup="calPlanDePago('<?= $rowData['codCu'] ?>')" type="number" class="form-control" value="<?= $row['ncapita'] ?>"></td> <!-- Capital -->
                                        <td><input min="0" step="0.01" id="<?= $con1 . 'inte' . $rowData['codCu'] ?>" name="<?= $rowData['codCu'] . 'interes[]' ?>" onkeyup="calPlanDePago('<?= $rowData['codCu'] ?>')" type="number" class="form-control" value="<?= $row['nintere'] ?>" min="0"></td> <!-- Interes -->
                                        <td><input min="0" step="0.01" id="<?= $con1 . 'otros' . $rowData['codCu'] ?>" name="<?= $rowData['codCu'] . 'otrosP[]' ?>" onkeyup="calPlanDePago('<?= $rowData['codCu'] ?>')" type="number" class="form-control" value="<?= $row['OtrosPagos'] ?>" min="0"></td> <!-- Otros -->
                                        <td id="<?= $con1 . 'salCap' . $rowData['codCu'] ?>" name="<?= $rowData['codCu'] . 'saldoCap[]' ?>">
                                            <?= $aux ?> </td> <!-- Saldo Capital -->
                                        <td id="<?= $con1 . 'total' . $rowData['codCu'] ?>">
                                            <?= ($row['ncapita'] + $row['nintere'] + $row['OtrosPagos']) ?></td> <!-- Total -->
                                    </tr>
                                <?php
                                    $con1++;
                                }
                                ?>
                                <!-- FIN -->
                            </tbody>

                        </table>
                    </div>
                    <!-- FIN DE LA TABLA -->

                    <!-- FIN TABLA -->

                </div>
<?php
            }
            $con++;
        }
        $output = ob_get_clean();
        echo $output;
        break;

        //Actualizar plan de pagos de los grupos
    case 'gruPlanPagosAct':
        $vecGeneral = $_POST['vecGeneral'];
        $codCu = $_POST['extra'];

        $totalEle = (count($vecGeneral) - 1); //Cantidad de Matrices que ingresaron

        $cuoFech = $vecGeneral[count($vecGeneral) - 1]; //Selecciona la matriz que contiene el plan de pagos
        $numF = count($cuoFech); //Valida cuanta filaz  tiene la matriz de plan de pagos

        $aux = 0;

        for ($conTE = 0; $conTE < $totalEle; $conTE++) {
            $aux = count($vecGeneral[$conTE]);
            if ($aux != $numF) {
                echo json_encode(['El plan de pago de una cuenta no cumple con el número de cuotras ' . $aux, '1']);
                return; //
            }
        }

        $res; // Encargada de capturar el resultado de la consulta
        $res1; // Encargada de camturar el resultado de la consutla

        try {
            for ($conTE = 0; $conTE < $totalEle; $conTE++) {
                $matriz = $vecGeneral[$conTE]; //obtener la matriz a trabajar
                if (isset($codCu[$conTE])) {
                    $res1 = $conexion->query("DELETE FROM Cre_ppg WHERE ccodcta = " . $codCu[$conTE]);
                    $aux = mysqli_error($conexion);
                    if ($aux) {
                        echo json_encode(['Error: ' . $aux, '0']);
                        $conexion->rollback();
                        return;
                    }
                }

                //Blokc de codigo, para crear un array de los datos a insertar
                for ($fil = 0; $fil < $numF; $fil++) {
                    $res = $conexion->query("INSERT INTO Cre_ppg (ccodcta, dfecven, cestado, cnrocuo, ncappag, nintpag, OtrosPagosPag, ncapita, nintere, SaldoCapital,dfecmod) VALUES ('{$codCu[$conTE]}', '{$cuoFech[$fil][1]}', 'X', {$cuoFech[$fil][0]}, {$matriz[$fil][1]}, {$matriz[$fil][2]}, {$matriz[$fil][3]}, {$matriz[$fil][1]}, {$matriz[$fil][2]}, {$matriz[$fil][4]},'$hoy')");
                    $aux = mysqli_error($conexion);
                    echo $aux;

                    if ($aux) {
                        echo 0;
                        $conexion->rollback();
                        return;
                    }

                    if (!$res) {
                        echo 0;
                        $conexion->rollback();
                        return;
                    }
                }
                $res4 = $conexion->query("CALL update_ppg_account('" . $codCu[$conTE] . "')");
                if (!$res4) {
                    echo json_encode(['Error al actualizar el plan de pago ', '0']);
                    $conexion->rollback();
                    return;
                }
            }

            if ($res && $res1) {
                //$conexion->commit();
                $conexion->rollback();
                echo json_encode(['Los datos se actualizaron con Exito', '1']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo 0;
        }
        break;

        //Funcion para eliminar la fila de plan de pagos de los grupos
    case 'deleteFilaPlanDePagosGrup':

        $id = $_POST['ideliminar'];
        $conTE = count($id);

        $res;
        $conexion->autocommit(false);
        for ($con = 0; $con < $conTE; $con++) {
            $res = $conexion->query("DELETE FROM Cre_ppg WHERE Id_ppg = " . $id[$con]);

            $aux = mysqli_error($conexion);
            echo $aux;

            if ($aux) {
                echo json_encode(['Error', '0']);
                $conexion->rollback();
                return;
            }

            if (!$res) {
                echo 'Error al ingresar';
                $conexion->rollback();
                return;
            }
        }

        if ($res) {
            $conexion->commit();
            echo json_encode(['Los datos, se eliminaron exitosamente. ', '1']);
            mysqli_close($conexion);
        }
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
    case 'consultar_history':
        $ccodcta = $_POST['cuenta'];
        $consulta = mysqli_query($conexion, "SELECT cnrocuo,dfecven,dfecpag,
            IF((timestampdiff(DAY,dfecven,'$hoy'))<0, 0,(timestampdiff(DAY,dfecven,'$hoy'))) AS diasatraso, cestado,ncapita,nintere,
            cflag FROM Cre_ppg WHERE ccodcta='$ccodcta'");
        $array_datos = array();
        $i = 0;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $estado = ($fila['cestado'] == 'P') ? 'Pagada' : (($fila['cestado'] == 'X' && $fila['diasatraso'] > 0) ? 'Vencida' : 'Por pagar');
            $color = ($fila['cestado'] == 'P') ? 'success' : (($fila['cestado'] == 'X' && $fila['diasatraso'] > 0) ? 'danger' : 'primary');
            $status = '<span class="badge text-bg-' . $color . '">' . $estado . '</span>';

            $pago = ($fila["cflag"] == 1) ? 'Puntual' : (($fila["cflag"] == 0) ? 'Impuntual' : 'Pendiente');
            $color = ($fila["cflag"] == 1) ? 'success' : (($fila["cflag"] == 0) ? 'danger' : 'primary');
            $calificacion = '<span class="badge text-bg-' . $color . '">' . $pago . '</span>';

            $array_datos[$i] = array(
                "0" => $fila["cnrocuo"],
                "1" => $fila["dfecven"],
                "2" => ($fila["dfecpag"] == "0000-00-00 00:00:00") ? '-' : date("d-m-Y", strtotime($fila["dfecpag"])),
                "3" => $status,
                "4" => $fila["ncapita"],
                "5" => $fila["nintere"],
                "6" => $calificacion,
            );
            $i++;
        }
        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($array_datos),
            "iTotalDisplayRecords" => count($array_datos),
            "aaData" => $array_datos
        );
        echo json_encode($results);
        mysqli_close($conexion);
        break;

    case 'act_interes':
        $data =  $_POST['datos'];
        // echo json_encode([$data[0], '1']);
        // return ;
        $sql  = "UPDATE Cre_ppg SET  nintpag = " . $data[0][5] . ", nintere = " . $data[0][5] . " WHERE ccodcta = '" . $data[0][1] . "' AND cnrocuo = " . $data[0][2] . "";
        $sql1 = "INSERT INTO tb_rpt_perdon (tipo, ccodcta, num_pago, efec_real, efec_perdonado, created_by, created_at) VALUES (1, '" . $data[0][1] . "', " . $data[0][2] . ", " . $data[0][4] . ", " . $data[0][5] . ", (SELECT id_usu FROM tb_usuario WHERE usu = '" . $data[0][0] . "'), '$hoy2');";

        $conexion->autocommit(false);
        $res = $conexion->query($sql);
        $res1 = $conexion->query($sql1);

        $aux = mysqli_error($conexion);
        echo $aux;

        if ($aux || !$res || !$res1) {
            echo json_encode(['Error', '0']);
            $conexion->rollback();
            return;
        }
        if ($res) {
            $conexion->commit();
            echo json_encode(['Interes modificado. ', '1']);
            mysqli_close($conexion);
        }
        break;
}


//************************************************************************************** AREA ROJA  XD */

function validar_campos($validaciones)
{
    for ($i = 0; $i < count($validaciones); $i++) {
        if ($validaciones[$i][0] == $validaciones[$i][1]) {
            return [$validaciones[$i][2], '0', true];
            $i = count($validaciones) + 1;
        }
    }
    return ["", '0', false];
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

function gastoscredito($idc, $conexion)
{
    $consulta = mysqli_query($conexion, "SELECT cg.*, cm.CCODPRD, cm.MonSug, cm.CodCli,tipg.nombre_gasto,cm.NtipPerC tiperiodo,cm.noPeriodo,cl.short_name,tipg.id_nomenclatura,tipg.afecta_modulo,DFecDsbls fecdes  FROM cremcre_meta cm
    INNER JOIN cre_productos_gastos cg ON cm.CCODPRD=cg.id_producto
    INNER JOIN cre_tipogastos tipg ON tipg.id=cg.id_tipo_deGasto
    INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
    WHERE cm.CCODCTA='$idc' AND tipo_deCobro=1 AND cg.estado=1");
    $datosgastos[] = [];
    $total = 0;
    $i = 0;
    while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
        $datosgastos[$i] = $fila;
        $id = $fila['id'];
        $tipo = $fila['tipo_deMonto'];
        $nombregasto = $fila['nombre_gasto'];
        $monapro = $fila['MonSug'];
        $cant = $fila['monto'];
        $calculax = $fila['calculox'];
        $cuotas = $fila['noPeriodo'];
        $tiperiodo = $fila['tiperiodo'];
        $plazo = ($tiperiodo == '1M') ? $cuotas : (($tiperiodo == '15D' || $tiperiodo == '14D') ? ($cuotas / 2) : (($tiperiodo == '7D') ? ($cuotas / 4) : (($tiperiodo == '1D') ? ($cuotas / 28) : $cuotas)));
        $mongas = 0;
        if ($tipo == 1) {
            $mongas = ($calculax == 1) ? ($cant) : (($calculax == 2) ? ($cant * $plazo) : (($calculax == 3) ? ($cant * $plazo * $monapro) : ($cant * $monapro)));
        }
        if ($tipo == 2) {
            $mongas = ($calculax == 1) ? ($cant / 100 * $monapro) : (($calculax == 2) ? ($cant / 100 * $plazo) : (($calculax == 3) ? ($cant / 100 * $plazo * $monapro) : ($cant / 100 * $monapro)));
        }
        $datosgastos[$i]['mongas'] = round($mongas, 2);
        $i++;
    }
    if ($i == 0) {
        return null;
    }
    return $datosgastos;
}
function getcuentas($idc, $conexion)
{
    $consulta = mysqli_query($conexion, 'SELECT CCODCTA,NCapDes,DfecPago fecpago,NIntApro intapro,IFNULL((SELECT SUM(KP) FROM CREDKAR WHERE CCODCTA=cm.CCODCTA AND CTIPPAG="P" AND CESTADO!="X"),0) pagadokp,
        IFNULL((SELECT SUM(nintpag) FROM Cre_ppg WHERE ccodcta=cm.CCODCTA),0) intpen,
        IFNULL((SELECT MAX(dfecven) from Cre_ppg where cestado="P" AND ccodcta=cm.CCODCTA),"-") fecult
        FROM cremcre_meta cm WHERE CodCli IN (SELECT Codcli FROM cremcre_meta WHERE CCODCTA="' . $idc . '") 
        AND CCODCTA!="' . $idc . '" AND Cestado="F" AND TipoEnti="INDI";');

    $datoscreditos[] = [];
    $total = 0;
    $i = 0;
    while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
        $datoscreditos[$i] = $fila;
        $i++;
    }
    if ($i == 0) {
        return null;
    }
    return $datoscreditos;
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
?>