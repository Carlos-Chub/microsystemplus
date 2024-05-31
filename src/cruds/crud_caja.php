<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
// include '../funcphp/fun_ppg.php';
include '../funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');

include '../../includes/Config/database.php';
$database = new Database($db_host, $db_name, $db_user, $db_password, $db_name_general);

$hoy = date("Y-m-d");
$hoy2 = date("Y-m-d H:i:s");
$idusuario = $_SESSION['id'];
$idagencia = $_SESSION['id_agencia'];

$condi = $_POST["condi"];
switch ($condi) {
    case 'paggrupal':
        $inputs = $_POST["inputs"];
        $montos = $inputs[0];
        $detalle = $inputs[1];
        $archivo = $_POST["archivo"];

        //DATOS DE ENCABEZADO
        $numdocumento = $detalle[0];
        $fechapago = $detalle[1];
        $formapago = $detalle[2];
        $bancoid = $detalle[3];
        $cuentaid = $detalle[4];
        $fechabanco = $detalle[5];
        $boletabanco = $detalle[6];

        //COMPROBAR CIERRE DE CAJA
        $cierre_caja = comprobar_cierre_caja($_SESSION['id'], $conexion);
        if ($cierre_caja[0] < 6) {
            echo json_encode([$cierre_caja[1], '0']);
            return;
        }

        //VALIDA SI SE INGRESARON DATOS DE RECIBO
        $validacion = validarcampo([$numdocumento, $fechapago], "");
        if ($validacion != "1") {
            echo json_encode(["Ingrese detalles del documento de pago", '0']);
            return;
        }

        $id_ctb_tipopoliza = 1; //TIPO DE POLIZA: CREDITOS (DEFAULT)
        //INICIA EL TRY
        $showmensaje = false;
        try {
            $database->openConnection();
            //VALIDACION DE NUMERO DE DOCUMENTO
            $result = $database->selectColumns('CREDKAR', ['CNUMING'], 'CNUMING=?', [$numdocumento]);
            if (!empty($result)) {
                $showmensaje = true;
                throw new Exception("Numero de documento de pago ya existente, Favor verificar");
            }

            //CONSULTAR LA CUENTA CONTABLE DE LA CAJA AGENCIA 
            $result = $database->selectColumns('tb_agencia', ['id_nomenclatura_caja'], 'id_agencia=?', [$idagencia]);
            $id_nomenclatura_caja = $result[0]['id_nomenclatura_caja'];

            if ($formapago === '0') {
                if ($cuentaid == "F000") {
                    $showmensaje = true;
                    throw new Exception("Seleccione una cuenta de banco");
                }
                if ($boletabanco == "") {
                    $showmensaje = true;
                    throw new Exception("Ingrese un numero de boleta de banco");
                }
                $id_ctb_tipopoliza = 11; // CUANDO ES POR BOLETA DE BANCO, EL TIPO DE POLIZA SE CAMBIA A NOTA DE CREDITO
                $result = $database->selectColumns('ctb_bancos', ['id', 'numcuenta', 'id_nomenclatura'], 'id=?', [$cuentaid]);
                $id_nomenclatura_caja = $result[0]['id_nomenclatura'];
            }

            //VALIDAR EL MONTO TOTAL INGRESADO
            if (array_sum(array_column($montos, 6)) <= 0) {
                $showmensaje = true;
                throw new Exception("Monto total a pagar invalido, favor verificar");
            }

            //generico([datainputs, datadetal], 0, 0, 'paggrupal', [0], [user, idgrup, idfondo, ciclo, id_agencia], 'crud_caja');
            // filas = getinputsval(['ccodcta' + (rows), 'namecli' + (rows), 'capital' + (rows), 'interes' + (rows), 'monmora' + (rows), 'otrospg' + (rows), 'totalpg' + (rows),'concepto' + (rows)]);
            //                     datos[rows] = filas;
            //                             detalles[i] = [monto, idgasto, idcontable, modulo, codaho];
            //                     datos[rows]['detallesotros'] = detalles;
            //VERIFICACION DE MONTOS POR CADA CREDITO
            $i = 0;
            $j = 0;
            $data[] = [];
            while ($i < count($montos)) {
                $filas = $montos[$i];
                $filas2 = $montos[$i];
                // $showmensaje = true;
                // throw new Exception("Monto negativo detectado");

                //UNSET ccodcta, namecli,   totalpg,   concepto
                unset($filas[0], $filas[1], $filas[6], $filas[7]);
                if (count(array_filter($filas, function ($var) {
                    return ($var < 0);
                })) > 0) {
                    $showmensaje = true;
                    throw new Exception("Monto negativo detectado");
                }
                if (array_sum($filas) > 0) {
                    //comprobar vacios
                    $keys = array_keys(array_filter($filas, function ($var) {
                        return ($var == "");
                    }));

                    $fi = 0;
                    while ($fi < count($keys)) {
                        $f = $keys[$fi];
                        $filas2[$f] = 0;
                        $fi++;
                    }
                    //fin comprueba vacios
                    // array_push($filas2, array_sum($filas));
                    //VALIDAR DETALLES OTROS DE CADA CREDITO detallesotros
                    $detalleotros = $filas2[8];
                    foreach ($detalleotros as $rowval) {
                        //[monto, idgasto, idcontable,modulo,codaho]
                        $monf = $rowval[0];
                        if (is_numeric($monf) && $monf < 0) {
                            $showmensaje = true;
                            throw new Exception("No puede ingresar valores negativos");
                        }

                        if ($rowval[3] > 0) {
                            $table = ($rowval[3] == 1) ? "ahomcta" : "aprcta";
                            $column = ($rowval[3] == 1) ? "ccodaho" : "ccodaport";
                            $texttitle = ($rowval[3] == 1) ? " ahorros" : "aportaciones";
                            $result = $database->selectColumns($table, ['nlibreta'], $column . '=?', [$rowval[4]]);
                            if (empty($result)) {
                                $showmensaje = true;
                                throw new Exception("La cuenta de ' . $texttitle . ': ' . $rowval[4] . ' no existe, por lo tanto no se puede completar la operacion por el monto especificado: ' . $monf . ', se recomienda configurar el vinculo con una cuenta existente para poder ingresar montos");
                            }
                            $nlibreta = $result[0]['nlibreta'];
                        }
                    }

                    $data[$j] = $filas2;
                    $j++;
                }
                //VALIDAR PAGO DE CAPITAL
                $result = $database->getAllResults("SELECT IFNULL((ROUND((IFNULL(cm.NCapDes,0)),2)-(SELECT ROUND( IFNULL(SUM(c.KP),0),2) FROM CREDKAR c 
                    WHERE c.CTIPPAG = 'P' AND  c.CCODCTA = cm.CCODCTA AND c.CESTADO!='X')),0)  AS saldopendiente FROM cremcre_meta cm WHERE cm.CCODCTA =?", [$filas2[0]]);
                $capital_pendiente = (empty($result)) ? 0 : $result[0]['saldopendiente'];

                if ($filas2[2] > $capital_pendiente) {
                    $i = 2000;
                    $showmensaje = true;
                    throw new Exception("No puede completar todos los pagos, por el credito ' . $filas2[0] . ', porque el saldo capital por pagar es de ' . $capital_pendiente . ' y usted quiere hacer un pago de ' . $filas2[2] . ' lo cual supera lo que resta por pagar, todo extra que se quiera pagar agreguelo en otros.");
                }
                $i++;
            }

            //CONSULTAR LA NOMENCLATURA PARA CAPITAL, INTERES Y MORA
            $result = $database->getAllResults("SELECT cp.id_cuenta_capital, cp.id_cuenta_interes, cp.id_cuenta_mora, cp.id_cuenta_otros,cp.id_fondo 
                FROM cre_productos cp INNER JOIN cremcre_meta cm ON cp.id=cm.CCODPRD WHERE cm.CCODCTA=?", [$data[0][0]]);
            if (empty($result)) {
                $showmensaje = true;
                throw new Exception("No se encontraron los datos del producto de crédito");
            }
            $id_nomenclatura_capital = $result[0]['id_cuenta_capital'];
            $id_nomenclatura_interes = $result[0]['id_cuenta_interes'];
            $id_nomenclatura_mora = $result[0]['id_cuenta_mora'];
            $id_nomenclatura_otros = $result[0]['id_cuenta_otros'];
            $id_fondo = $result[0]['id_fondo'];

            //TRAER CNROCUO SIGUIENTE PARA EL GRUPO
            $result = $database->getAllResults("SELECT IFNULL(MAX(CNROCUO),0) nocuo FROM CREDKAR WHERE CTIPPAG='P' AND CESTADO!='X' AND CCODCTA IN (SELECT CCODCTA FROM cremcre_meta WHERE CCodGrupo =? AND NCiclo=?);", [$archivo[1], $archivo[3]]);
            $nrocuo = (empty($result)) ? 1 : $result[0]['nocuo'] + 1;

            $database->beginTransaction();

            $i = 0;
            while ($i < count($data)) {
                //cada fila: 0ccodcta 1namecli 2capital 3interes 4monmora 5otrospg 6totalpg 7concepto 8detallesotros 9arraysumfilas?
                $datos = array(
                    'CCODCTA' =>  $data[$i][0],
                    'DFECPRO' => $fechapago,
                    'DFECSIS' => $hoy2,
                    'CNROCUO' => $nrocuo, //CREAR LA FUNCION QUE ORDENE LOS PAGOS O REVISAR LA EXISTENTE
                    'NMONTO' => $data[$i][6],
                    'CNUMING' => $numdocumento,
                    'CCONCEP' => $data[$i][7],
                    'KP' => $data[$i][2],
                    'INTERES' => $data[$i][3],
                    'MORA' => $data[$i][4],
                    'AHOPRG' => 0,
                    'OTR' => $data[$i][5],
                    'CCODINS' => "1",
                    'CCODOFI' => "1",
                    'CCODUSU' => $idusuario,
                    'CTIPPAG' => "P",
                    'CMONEDA' => "Q",
                    'CBANCO' => $cuentaid,
                    'FormPago' => $formapago,
                    'CCODBANCO' => $bancoid,
                    'DFECBANCO' => ($formapago === '0') ?  $fechabanco : "0000-00-00",
                    'boletabanco' => $boletabanco,
                    'CESTADO' => "1",
                    'DFECMOD' =>  $hoy2,
                    'CTERMID' => "0",
                    'MANCOMUNAD' => "0"
                );

                $id_credkar = $database->insert('CREDKAR', $datos);
                $numpartida = getnumcompdo($idusuario, $database);
                $datos = array(
                    'numcom' => $numpartida,
                    'id_ctb_tipopoliza' => $id_ctb_tipopoliza,
                    'id_tb_moneda' => 1,
                    'numdoc' => $numdocumento,
                    'glosa' =>  $data[$i][7],
                    'fecdoc' => ($formapago === '0') ?  $fechabanco : $fechapago,
                    'feccnt' => $fechapago,
                    'cod_aux' => $data[$i][0],
                    'id_tb_usu' => $idusuario,
                    'fecmod' => $hoy2,
                    'estado' => 1,
                    'editable' => 0
                );
                $id_ctb_diario = $database->insert('ctb_diario', $datos);
                //MOVIMIENTOS LADO DEL DEBE (MONTO TOTAL=> CAJA Ó CUENTA BANCOS)
                $datos = array(
                    'id_ctb_diario' => $id_ctb_diario,
                    'id_fuente_fondo' => $id_fondo,
                    'id_ctb_nomenclatura' => $id_nomenclatura_caja,
                    'debe' =>  $data[$i][6],
                    'haber' => 0
                );
                $database->insert('ctb_mov', $datos);
                //MOVIMIENTOS LADO DEL HABER (DETALLES => CAPITAL)
                $datos = array(
                    'id_ctb_diario' => $id_ctb_diario,
                    'id_fuente_fondo' => $id_fondo,
                    'id_ctb_nomenclatura' => $id_nomenclatura_capital,
                    'debe' =>  0,
                    'haber' => $data[$i][2]
                );
                $database->insert('ctb_mov', $datos);
                //MOVIMIENTOS LADO DEL HABER (DETALLES => INTERES)
                $datos = array(
                    'id_ctb_diario' => $id_ctb_diario,
                    'id_fuente_fondo' => $id_fondo,
                    'id_ctb_nomenclatura' => $id_nomenclatura_interes,
                    'debe' =>  0,
                    'haber' => $data[$i][3]
                );
                $database->insert('ctb_mov', $datos);
                //MOVIMIENTOS LADO DEL HABER (DETALLES => MORA)
                $datos = array(
                    'id_ctb_diario' => $id_ctb_diario,
                    'id_fuente_fondo' => $id_fondo,
                    'id_ctb_nomenclatura' => $id_nomenclatura_mora,
                    'debe' =>  0,
                    'haber' => $data[$i][4]
                );
                $database->insert('ctb_mov', $datos);

                /*  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ 
                +++++++++++++++++ INSERCION DE LOS GASTOS SI SE INGRESARON ++++++++++++++++++
                +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++  */
                $detalleotros = $data[$i][8];
                foreach ($detalleotros as $rowval) {
                    //[monto, idgasto, idcontable,modulo,codaho]
                    $monf = $rowval[0];
                    $idgasto = $rowval[1];
                    $modulo = $rowval[3];
                    if ($monf > 0) {
                        if ($idgasto == 0) {
                            //INSERCION DE OTROS, EN LA CONTA, NO ES UN GASTO ESPECIFICO
                            $datos = array(
                                'id_ctb_diario' => $id_ctb_diario,
                                'id_fuente_fondo' => $id_fondo,
                                'id_ctb_nomenclatura' => $id_nomenclatura_otros,
                                'debe' =>  0,
                                'haber' => $monf
                            );
                            $database->insert('ctb_mov', $datos);
                        } else {
                            //INSERCION DE GASTOS ESPECIFICOS
                            $datos = array(
                                'id_ctb_diario' => $id_ctb_diario,
                                'id_fuente_fondo' => $id_fondo,
                                'id_ctb_nomenclatura' => $rowval[2],
                                'debe' =>  0,
                                'haber' => $monf
                            );
                            $database->insert('ctb_mov', $datos);

                            //INSERCION DE GASTOS EN CREDKARDETALLE
                            $datos = array(
                                'id_credkar' => $id_credkar,
                                'id_concepto' => $idgasto,
                                'monto' => $monf
                            );
                            $database->insert('credkar_detalle', $datos);

                            //SI ES UN AHORRO VINCULADO
                            if ($modulo == '1') {
                                $datos = array(
                                    "ccodaho" => $rowval[4],
                                    "dfecope" => $fechapago,
                                    "ctipope" => "D",
                                    "cnumdoc" => $numdocumento,
                                    "ctipdoc" => "E",
                                    "crazon" => "DEPOSITO VINCULADO",
                                    "nlibreta" => $nlibreta,
                                    "nrochq" => '0',
                                    "tipchq" => "0",
                                    "dfeccomp" => "0000-00-00",
                                    "monto" => $monf,
                                    "lineaprint" => "N",
                                    "numlinea" => 1,
                                    "correlativo" => 1,
                                    "dfecmod" => $hoy2,
                                    "codusu" => $idusuario,
                                    "cestado" => 1,
                                    "auxi" => $data[$i][0],
                                    "created_at" => $hoy2,
                                    "created_by" => $idusuario,
                                );
                                $database->insert('ahommov', $datos);

                                // ORDENAMIENTO DE TRANSACCIONES
                                $database->executeQuery('CALL ahom_ordena_noLibreta(?,?);', [$nlibreta, $rowval[4]]);
                                $database->executeQuery('CALL ahom_ordena_Transacciones(?);', [$rowval[4]]);
                            }

                            //SI EXISTE UNA APORTACION VINCULADA
                            if ($modulo == '2') {
                                $datos = array(
                                    "ccodaport" => $rowval[4],
                                    "dfecope" => $fechapago,
                                    "ctipope" => "D",
                                    "cnumdoc" => $numdocumento,
                                    "ctipdoc" => "E",
                                    "crazon" => "DEPOSITO VINCULADO",
                                    "nlibreta" => $nlibreta,
                                    "nrochq" => '0',
                                    "tipchq" => "0",
                                    "dfeccomp" => "0000-00-00",
                                    "monto" => $monf,
                                    "lineaprint" => "N",
                                    "numlinea" => 1,
                                    "correlativo" => 1,
                                    "dfecmod" => $hoy2,
                                    "codusu" => $idusuario,
                                    "cestado" => 1,
                                    "auxi" => $data[$i][0],
                                    "created_at" => $hoy2,
                                    "created_by" => $idusuario,
                                );
                                $database->insert('aprmov', $datos);

                                // ORDENAMIENTO DE TRANSACCIONES
                                $database->executeQuery('CALL apr_ordena_noLibreta(?,?);', [$nlibreta, $rowval[4]]);
                                $database->executeQuery('CALL apr_ordena_Transacciones(?);', [$rowval[4]]);
                            }
                        }
                    }
                }

                //ACTUALIZACION DEL PLAN DE PAGO
                $database->executeQuery('CALL update_ppg_account(?);', [$data[$i][0]]);
                $database->executeQuery('SELECT calculo_mora(?);', [$data[$i][0]]);
                $i++;
            }

            $database->commit();
            // $database->rollback();
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

        echo json_encode([$mensaje, $status, $numdocumento, $archivo[3]]);
        break;
        //FIN DEL TRY
        break;
    case 'list_pagos_individuales':
        $consulta = mysqli_query($conexion, "SELECT 
        cm.CCODCTA AS ccodcta, 
        cm.CodCli AS codcli, 
        cl.short_name AS nombre, 
        cm.NCiclo AS ciclo, 
        cm.MonSug AS monsug, 
        DAY(cm.DfecPago) AS diapago
    FROM 
        cremcre_meta cm 
    INNER JOIN 
        tb_cliente cl ON cm.CodCli = cl.idcod_cliente 
    WHERE 
        cm.Cestado = 'F' AND 
        cm.TipoEnti = 'INDI';");
        //se cargan los datos de las beneficiarios a un array
        $array_datos = array();
        $total = 0;
        $i = 0;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $array_datos[] = array(
                "0" => $i + 1,
                "1" => $fila["ccodcta"],
                "2" => $fila["codcli"],
                "3" => $fila["nombre"],
                "4" => $fila["ciclo"],
                "5" => $fila["diapago"],
                "6" => $fila["monsug"],
                "7" => '<button type="button" class="btn btn-success"  data-bs-dismiss="modal" onclick="printdiv2(`#cuadro`,`' . $fila["ccodcta"] . '`)">Aceptar</button> '
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
        // -- NEGROY AGREGO, las boletas --
    case 'create_pago_individual':
        //[`nomcli`, `id_cod_cliente`, `codagencia`, `codproducto`, `codcredito`, `fechadesembolso`, `norecibo`, `fecpag`, `capital0`, `interes0`, `monmora0`, `otrospg0`, `totalgen`, `fecpagBANC`,`noboletabanco`,`concepto`]
        if (!isset($_SESSION['id_agencia'])) {
            echo json_encode(['Sesion expirada, vuelve a iniciar sesion e intente nuevamente', '0']);
            return;
        }
        $inputs = $_POST["inputs"];
        $archivo = $_POST["archivo"];
        $selects = $_POST["selects"];

        $detalleotros = (isset($archivo[6]) && $archivo[6] != null) ? $archivo[6] : null;
        //FECHA FORMATO 
        $timestamp = strtotime($inputs[13]);
        $dfecbanco = date("Y-m-d", $timestamp);
        //COMPROBAR CIERRE DE CAJA
        $cierre_caja = comprobar_cierre_caja($_SESSION['id'], $conexion);
        if ($cierre_caja[0] < 6) {
            echo json_encode([$cierre_caja[1], '0']);
            return;
        }
        $concepto = $inputs[15];
        //VALIDACIONES
        $validar = validar_campos([
            [$inputs[0], "", 'Debe seleccionar un crédito a pagar'],
            [$inputs[1], "", 'Debe seleccionar un crédito a pagar'],
            [$inputs[2], "", 'Debe seleccionar un crédito a pagar'],
            [$inputs[3], "", 'Debe seleccionar un crédito a pagar'],
            [$inputs[4], "", 'Debe seleccionar un crédito a pagar'],
            [$inputs[5], "", 'Debe seleccionar un crédito a pagar'],
            [$inputs[6], "", 'Debe digitar un número de recibo'],
            [$inputs[7], "", 'Debe digitar una fecha de pago'],
            [$concepto, "", 'Debe digitar un concepto']
        ]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }
        //VALIDAR LA FECHA DE PAGO QUE SEA IGUAL A HOY
        if ($inputs[7] > date('Y-m-d')) {
            echo json_encode(['La fecha de pago no puede ser mayor que la fecha de hoy', '0']);
            return;
        }
        //***************************************** */
        $validar = validar_campos([
            [$inputs[8], "", 'Debe digitar un monto de capital'],
            [$inputs[9], "", 'Debe digitar un monto de interés'],
            [$inputs[10], "", 'Debe digitar un monto de mora'],
            // [$inputs[11], "", 'Debe digitar un monto de ahorro programado'],
            [$inputs[11], "", 'Debe digitar un monto de otros pagos'],
            [$inputs[12], "", 'Debe exisitir un monto de total general']
        ]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }
        //validar que los montos no sean menor a cero
        if ($inputs[8] < 0) {
            echo json_encode(['No puede digitar un capital menor a 0', '0']);
            return;
        }
        if ($inputs[9] < 0) {
            echo json_encode(['No puede digitar un interes menor a 0', '0']);
            return;
        }
        if ($inputs[10] < 0) {
            echo json_encode(['No puede digitar una mora menor a 0', '0']);
            return;
        }
        //if($inputs[11]<0){echo json_encode(['No puede digitar un ahorro menor a 0','0']);return;}
        if ($inputs[11] < 0) {
            echo json_encode(['No puede digitar en otros pagos un monto menor a 0', '0']);
            return;
        }
        if ($inputs[12] < 0) {
            echo json_encode(['No puede tener un total general menor a 0', '0']);
            return;
        }

        /*  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ 
            +++++++++++++++++ VALIDACION DE LOS GASTOS INGRESADOS +++++++++++++++++++
            +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++  */
        if ($detalleotros != null) {
            foreach ($detalleotros as $rowval) {
                //[monto, idgasto, idcontable,modulo,codaho]
                $monf = $rowval[0];
                if (is_numeric($monf) && $monf < 0) {
                    echo json_encode(['No puede ingresar valores negativos', '0']);
                    return;
                }

                if ($rowval[3] > 0) {
                    $table = ($rowval[3] == 1) ? "ahomcta" : "aprcta";
                    $column = ($rowval[3] == 1) ? "ccodaho" : "ccodaport";
                    $texttitle = ($rowval[3] == 1) ? " ahorros" : "aportaciones";
                    $consulta = $conexion->prepare("SELECT * FROM " . $table . " WHERE " . $column . " = ?");
                    $consulta->bind_param("s", $rowval[4]);
                    if ($consulta->execute() === false) {
                        $error = $consulta->error;
                        echo json_encode(['Error en la consulta: ' . $error, '0']);
                        return;
                    }
                    $resultado = $consulta->get_result();
                    if ($resultado->num_rows == 0) {
                        echo json_encode(['La cuenta de ' . $texttitle . ': ' . $rowval[4] . ' no existe, por lo tanto no se puede completar la operacion por el monto especificado: ' . $monf . ', se recomienda configurar el vinculo con una cuenta existente para poder ingresar montos', '0']);
                        return;
                    }
                    while ($fila = $resultado->fetch_assoc()) {
                        $nlibreta = $fila['nlibreta'];
                    }
                    $consulta->close();
                }
            }
        }

        // echo json_encode(['QUI TAMOS', '0']);
        // return;
        //VALIDAR DE QUE EL CAPITAL NO A PAGAR NO SE PASE DE LO QUE CORRESPONDE
        $querysaldos = "SELECT  
                        IFNULL((ROUND((IFNULL(cm.NCapDes,0)),2)-(SELECT ROUND(IFNULL(SUM(c.KP),0),2) FROM CREDKAR c WHERE c.CTIPPAG = 'P' AND  c.CCODCTA = cm.CCODCTA AND c.CESTADO!='X')),0)  AS saldopendiente,
                        IFNULL(ROUND((SELECT ROUND(IFNULL(SUM(nintere),0),2) FROM Cre_ppg WHERE ccodcta = cm.CCODCTA)-
                        (SELECT ROUND(IFNULL(SUM(c.INTERES),0),2) FROM CREDKAR c WHERE c.CTIPPAG = 'P' AND  c.CCODCTA = cm.CCODCTA AND c.CESTADO!='X'),2),0)  AS intpendiente 
                        FROM cremcre_meta cm WHERE cm.CCODCTA = '$inputs[4]'";
        $consulta = mysqli_query($conexion, $querysaldos);
        $aux = mysqli_error($conexion);
        if ($aux) {
            echo json_encode(['Error al consultar el saldo por pagar', '0']);
            return;
        }
        if (!$consulta) {
            echo json_encode(['Fallo al consultar el saldo a pagar', '0']);
            return;
        }
        $capital_pendiente = 0;
        $interes_pendiente = 0;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $capital_pendiente = ($fila['saldopendiente'] > 0) ? $fila['saldopendiente'] : 0;
            $interes_pendiente = ($fila['intpendiente'] > 0) ? $fila['intpendiente'] : 0;
        }
        if ($inputs[8] > $capital_pendiente) {
            echo json_encode(['No puede completar el pago, porque el saldo capital por pagar es de ' . $capital_pendiente . ' y usted quiere hacer un pago de ' . $inputs[8] . ' lo cual supera lo que resta por pagar, todo extra que se quiera pagar agreguelo en otros.', '0']);
            return;
        }
        // if ($inputs[9] > $interes_pendiente) {
        //     echo json_encode(['No puede completar el pago, porque el saldo interés por pagar es de ' . $interes_pendiente . ' y usted quiere hacer un pago de ' . $inputs[9] . ' lo cual supera lo que resta por pagar, todo extra que se quiera pagar agreguelo en otros.', '0']);
        //     return;
        // }

        //CONSULTAR LA NOMENCLATURA PARA EL PAGO TOTAL
        $consulta = mysqli_query($conexion, "SELECT * FROM tb_agencia ag WHERE ag.id_agencia='$archivo[1]'");
        $aux = mysqli_error($conexion);
        if ($aux) {
            echo json_encode(['Error en encontrar la cuenta para el pago total', '0']);
            return;
        }
        if (!$consulta) {
            echo json_encode(['Fallo al encontrar la cuenta para el pago total', '0']);
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
            return;
        }

        //CONSULTAR LA NOMENCLATURA PARA CAPITAL, INTERES Y MORA
        $consulta = mysqli_query($conexion, "SELECT cp.id_cuenta_capital, cp.id_cuenta_interes, cp.id_cuenta_mora, cp.id_cuenta_otros FROM cre_productos cp INNER JOIN cremcre_meta cm ON cp.id=cm.CCODPRD WHERE cm.CCODCTA='$inputs[4]'");
        $aux = mysqli_error($conexion);
        if ($aux) {
            echo json_encode(['Error en encontrar las cuentas de capital, interés y mora', '0']);
            return;
        }
        if (!$consulta) {
            echo json_encode(['Fallo al encontrar las cuentas de capital, interés y mora', '0']);
            return;
        }
        $banderacaja = true;
        $id_nomenclatura_capital = 1;
        $id_nomenclatura_interes = 2;
        $id_nomenclatura_mora = 3;
        $id_nomenclatura_otros = 3;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $banderacaja = false;
            $id_nomenclatura_capital = $fila['id_cuenta_capital'];
            $id_nomenclatura_interes = $fila['id_cuenta_interes'];
            $id_nomenclatura_mora = $fila['id_cuenta_mora'];
            $id_nomenclatura_otros = $fila['id_cuenta_otros'];
        }
        if ($banderacaja) {
            echo json_encode(['No se encontraron la cuentas contables para el capital, interés y mora', '0']);
            return;
        }

        // Verificar el valor de $selects[2] $id_ctb_tipopoliza
        // EFECTIVO
        $id_ctb_tipopoliza = 1;
        $boletabanco = isset($inputs[14]) ? $inputs[14] : "";

        if ($selects[2] === '0') {
            if ($selects[1] == "F000") {
                echo json_encode(['Seleccione una cuenta de banco', '0']);
                return;
            }
            if ($boletabanco == "") {
                echo json_encode(['Ingrese un numero de boleta de banco', '0']);
                return;
            }
            $id_ctb_tipopoliza = 11; // BOLETA PAGO BANCO
            // $id_nomenclatura_caja 
            $ctb = mysqli_query($conexion, "SELECT id, numcuenta, id_nomenclatura FROM `ctb_bancos` WHERE id = '$selects[1]';");
            while ($fila = mysqli_fetch_array($ctb, MYSQLI_ASSOC)) {
                $id_nomenclatura_caja = $fila['id_nomenclatura'];
            }
        }
        // echo json_encode([$concepto, '0']);
        // return;
        //INICIO DE LAS TRANSACCIONES
        $conexion->autocommit(false);
        try {
            //INSERCION EN LA CREDKAR //NEGROY  CBANCO,DFECBANCO,CCODBANCO=CUENTA_BANCO, FormPago =$selects[2]
            $cnrocuo = getnumcnrocuo($inputs[4], $conexion);
            $desboleta = ($selects[2] === '0') ? (" - BOLETA DE BANCO NO. " . $boletabanco) : "";
            $numdocdiario = ($selects[2] === '0') ? $boletabanco : strtoupper($inputs[6]);
            // $concepto = "PAGO DE CRÉDITO A NOMBRE DE " . strtoupper($inputs[0]) . " CON NÚMERO DE RECIBO " . strtoupper($inputs[6]) . $desboleta;
            $res = $conexion->query("INSERT INTO `CREDKAR`(`CCODCTA`, `DFECPRO`, `DFECSIS`, `CNROCUO`, `NMONTO`, `CNUMING`, `CCONCEP`, `KP`, `INTERES`,`MORA`,`OTR`,`CCODOFI`,`CCODUSU`,`CTIPPAG`,`CMONEDA`,`DFECMOD`,`CESTADO`,`CBANCO`,`CCODBANCO`,`DFECBANCO`,`FormPago`,`boletabanco` ) 
            VALUES ('$inputs[4]','$inputs[7]','$hoy2',$cnrocuo,$inputs[12],'$inputs[6]','$concepto',$inputs[8],$inputs[9],$inputs[10],$inputs[11],'$archivo[1]','$archivo[0]','P','Q','$hoy','1','$selects[0]','$selects[1]','$dfecbanco','$selects[2]','$boletabanco' )");
            $aux = mysqli_error($conexion);

            if ($aux) {
                echo json_encode([$aux . '1', '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['Fallo la insercion de pago', '0']);
                $conexion->rollback();
                return;
            }
            $id_credkar = get_id_insertado($conexion); //obtener el id insertado en la credkar    
            //ACTUALIZACION EN LA CREPPG
            $res = $conexion->query("CALL update_ppg_account('" . $inputs[4] . "')");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode([$aux . '2', '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['Fallo la actualizacion del plan de pago', '0']);
                $conexion->rollback();
                return;
            }

            //ACTUALIZACION DE LA MORA
            $res = $conexion->query("SELECT calculo_mora('" . $inputs[4] . "')");
            if (!$res) {
                echo json_encode(['Error en el segundo procedimiento de actualizacion ' . $i, '0']);
                $conexion->rollback();
                return;
            }

            //INICIO DE TRANSACCIONES EN CONTA
            $numpartida = getnumcom($archivo[0], $conexion);
            //----------INSERCION EN EL LIBRO DE DIARIO
            $res = $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$numpartida','$id_ctb_tipopoliza',1,'$numdocdiario','$concepto','$inputs[7]','$inputs[7]','" . $inputs[4] . "',$archivo[0],'$hoy2',1)");
            if (!$res) {
                echo json_encode(['Error en la Creacion de partida de diario', '0']);
                $conexion->rollback();
                return;
            }
            $id_ctb_diario = get_id_insertado($conexion); //obtener el id insertado en diario  
            //---------INSERCION TOTAL
            $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario," . $archivo[5] . " ,$id_nomenclatura_caja, " . $inputs[12] . ",0)");
            if (!$res) {
                echo json_encode(['Error en la Creacion de movimiento de la partida de diario' . $i, '0']);
                $conexion->rollback();
                return;
            }
            //---------KP
            if ($inputs[8] > 0) {
                // $nomenclatura = 1264;
                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario," .  $archivo[5] . ",$id_nomenclatura_capital, 0," . $inputs[8] . ")");
                if (!$res) {
                    echo json_encode(['Error en la Creacion de movimiento de capital en la partida de diario' . $i, '0']);
                    $conexion->rollback();
                    return;
                }
            }
            //---------INTERES
            if ($inputs[9] > 0) {
                // $nomenclatura = 1291;
                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario," .  $archivo[5] . ",$id_nomenclatura_interes, 0," . $inputs[9] . ")");
                if (!$res) {
                    echo json_encode(['Error en la Creacion de movimientos de interes en la partida de diario' . $i, '0']);
                    $conexion->rollback();
                    return;
                }
            }
            //---------MORA
            if ($inputs[10] > 0) {
                // $nomenclatura = 1244;
                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario," .  $archivo[5] . ",$id_nomenclatura_mora, 0," . $inputs[10] . ")");
                if (!$res) {
                    echo json_encode(['Error en la Creacion de movimiento de mora en la partida de diario' . $i, '0']);
                    $conexion->rollback();
                    return;
                }
            }

            //---------OTROS
            // if ($inputs[11] > 0) {
            //     // $nomenclatura = 335;
            //     $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario," .  $archivo[5] . ",$id_nomenclatura_otros, 0," . $inputs[11] . ")");
            //     if (!$res) {
            //         echo json_encode(['Error en la Creacion de movimiento de otros gastos en la partida de diario' . $i, '0']);
            //         $conexion->rollback();
            //         return;
            //     }
            // }

            /*  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ 
            +++++++++++++++++ INSERCION DE LOS GASTOS SI SE INGRESARON ++++++++++++++++++
            +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++  */
            if ($detalleotros != null) {
                foreach ($detalleotros as $rowval) {
                    //[monto, idgasto, idcontable,modulo,codaho]
                    $monf = $rowval[0];
                    $idgasto = $rowval[1];
                    $modulo = $rowval[3];
                    // echo json_encode([$rowval,'0']);
                    // $conexion->rollback();
                    // return;
                    if ($idgasto == 0) {
                        $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) VALUES ($id_ctb_diario," .  $archivo[5] . ",$id_nomenclatura_otros, 0," . $monf . ")");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode(['Error conta gasto:' . $aux, '0']);
                            $conexion->rollback();
                            return;
                        }
                        if (!$res) {
                            echo json_encode(['Error en la Creacion de movimiento de otros gastos en la partida de diario' . $idgasto, '0']);
                            $conexion->rollback();
                            return;
                        }
                    } else {
                        $res = $conexion->query("INSERT INTO `credkar_detalle`(`id_credkar`,`id_concepto`,`monto`) VALUES ($id_credkar,$idgasto,$monf)");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode(['Error gasto:' . $aux, '0']);
                            $conexion->rollback();
                            return;
                        }
                        if (!$res) {
                            echo json_encode(['Error en la Creacion de movimientos de gastos en el kardex', '0']);
                            $conexion->rollback();
                            return;
                        }

                        $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) 
                            VALUES ($id_ctb_diario," .  $archivo[5] . ", $rowval[2], 0," . $monf . ")");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            echo json_encode(['Error conta gasto:' . $aux, '0']);
                            $conexion->rollback();
                            return;
                        }
                        if (!$res) {
                            echo json_encode(['Error en la Creacion de movimiento de otros gastos en la partida de diario' . $idgasto, '0']);
                            $conexion->rollback();
                            return;
                        }

                        if ($modulo == '1') {
                            $res = $conexion->query("INSERT INTO `ahommov`(`ccodaho`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,`lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`cestado`,`auxi`,`created_at`,`created_by`) 
                            VALUES ('$rowval[4]', '$inputs[7]', 'D', '$inputs[6]', 'E', 'DEPOSITO VINCULADO', $nlibreta, '0','0', '0', $monf, 'N', 1, 1, '$hoy', '$idusuario',1, '$inputs[4]', '$hoy2','$idusuario')");
                            $aux = mysqli_error($conexion);
                            if ($aux) {
                                echo json_encode(['Error aho gasto:' . $aux, '0']);
                                $conexion->rollback();
                                return;
                            }
                            if (!$res) {
                                echo json_encode(['Error en la Creacion de movimiento de aho' . $idgasto, '0']);
                                $conexion->rollback();
                                return;
                            }

                            // ORDENAMIENTO DE TRANSACCIONES
                            mysqli_query($conexion, "CALL ahom_ordena_noLibreta('$nlibreta', '$rowval[4]')");
                            mysqli_query($conexion, "CALL ahom_ordena_Transacciones('$rowval[4]')");
                        }
                        if ($modulo == '2') {
                            $res = $conexion->query("INSERT INTO `aprmov`(`ccodaport`,`dfecope`,`ctipope`,`cnumdoc`,`ctipdoc`,`crazon`,`nlibreta`,`nrochq`,`tipchq`,`numpartida`,`monto`,`lineaprint`,`numlinea`,`correlativo`,`dfecmod`,`codusu`,`cestado`,`auxi`,`created_at`,`created_by`) 
                            VALUES ('$rowval[4]', '$inputs[7]', 'D', '$inputs[6]', 'E', 'DEPOSITO VINCULADO', $nlibreta, '0','0', '0', $monf, 'N', 1, 1, '$hoy', '$idusuario',1, '$inputs[4]', '$hoy2','$idusuario')");
                            $aux = mysqli_error($conexion);
                            if ($aux) {
                                echo json_encode(['Error apr gasto:' . $aux, '0']);
                                $conexion->rollback();
                                return;
                            }
                            if (!$res) {
                                echo json_encode(['Error en la Creacion de movimiento de apr' . $idgasto, '0']);
                                $conexion->rollback();
                                return;
                            }

                            // ORDENAMIENTO DE TRANSACCIONES
                            mysqli_query($conexion, "CALL apr_ordena_noLibreta('$nlibreta', '$rowval[4]')");
                            mysqli_query($conexion, "CALL apr_ordena_Transacciones('$rowval[4]')");
                        }
                    }
                }
            }
            // echo json_encode(['aki to bien papu','0']);
            // $conexion->rollback();
            // return;

            //FIN TRANSACCIONES EN CONTA Y BANCOS
            $conexion->commit();
            echo json_encode(['Pago registrado correctamente con recibo No.' . $inputs[6], '1', $inputs[6], $cnrocuo]);
        } catch (Exception $e) {
            $conexion->rollback();
            $message = $e->getMessage();
            echo json_encode(['Error al ingresar: ', '0']);
        }

        mysqli_close($conexion);
        break;
    case 'validar_mora_individual':
        $datosmora = $_POST['datosmora'];
        $bandera = false;
        //SEGUNDA CONSULTA PARA LOS PLANES DE PAGO
        $i = 0;
        $consulta = mysqli_query($conexion, "SELECT cpg.Id_ppg AS id, cpg.dfecven, IF((timestampdiff(DAY,cpg.dfecven,'$hoy'))<0, 0,(timestampdiff(DAY,cpg.dfecven,'$hoy'))) AS diasatraso, cpg.cestado, cpg.cnrocuo AS numcuota, cpg.ncappag AS capital, cpg.nintpag AS interes, cpg.nmorpag AS mora, cpg.AhoPrgPag AS ahorropro, cpg.OtrosPagosPag AS otrospagos
			FROM Cre_ppg cpg
			WHERE cpg.cestado='X' AND cpg.ccodcta='" . $datosmora[0][0] . "'
			ORDER BY cpg.ccodcta, cpg.dfecven, cpg.cnrocuo");
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $datoscreppg[$i] = $fila;
            $i++;
            $bandera = true;
        }
        //ORDENAR ARRAYS PARA LA IMPRESION DE DATOS
        $cuotasvencidas = array_filter($datoscreppg, function ($sk) {
            return $sk['diasatraso'] > 0;
        });
        //FILTRAR UN SOLO REGISTRO NO PAGADO
        $cuotasnopagadas = array_filter($datoscreppg, function ($sk) {
            return $sk['diasatraso'] == 0;
        });

        //SECCION DE REESTRUCTURACION 
        unset($datoscreppg);
        $datoscreppg[] = [];
        $sumamora = 0;
        $j = 0;
        //OBTIENE CUOTA VENCIDAS SI HUBIERAN
        if (count($cuotasvencidas) != 0) {
            for ($i = $j; $i < count($cuotasvencidas); $i++) {
                $datoscreppg[$i] = $cuotasvencidas[$i];
                $j++;
            }
        }
        //TRAE LOS PAGOS A LA FECHA SI HUBIERAN Y SINO TRAE LA SIGUIENTE EN CASO DE QUE NO HAYAN CUOTAS VENCIDAS
        if (count($cuotasnopagadas) != 0) {
            for ($i = $j; $i < count($cuotasnopagadas); $i++) {
                if ($cuotasnopagadas[$i]['dfecven'] <= $hoy2) {
                    $datoscreppg[$j] = $cuotasnopagadas[$j];
                    $i = 2000;
                    $j++;
                } else {
                    if (count($cuotasvencidas) == 0) {
                        $datoscreppg[$j] = $cuotasnopagadas[$j];
                        $i = 2000;
                        $j++;
                    }
                }
            }
        }
        $sumamora = 'x';
        if (count($datoscreppg) != 0) {
            $sumamora = array_sum(array_column($datoscreppg, "mora"));
            $sumamora = round($sumamora, 2);
        }
        if ($bandera) {
            if ($sumamora == $datosmora[0][1]) {
                echo json_encode(['La suma anterior y actual son iguales', '1', 0, $sumamora, $datosmora]);
            } else {
                echo json_encode(['La suma anterior y actual no son iguales', '1', 1, $sumamora, $datosmora]);
            }
        } else {
            $sumamora = 'x';
            echo json_encode(['No se encontraron datos', '0', 'x', $sumamora]);
            return;
        }
        mysqli_close($conexion);
        break;
    case 'validar_mora_grupal':
        $datosmora = $_POST['datosmora'];
        $bandera = false;

        //CREDITOS DEL GRUPO
        $datos[] = [];
        $datacre = mysqli_query($conexion, 'SELECT gru.NombreGrupo,gru.direc,gru.codigo_grupo, cli.short_name, cre.CCODCTA,cre.NCiclo,cre.MonSug,cre.DFecDsbls,cre.NCapDes,
        IFNULL((SELECT  SUM(KP) FROM CREDKAR WHERE ctippag="P" AND CESTADO!="X" AND ccodcta=cre.CCODCTA GROUP BY ccodcta),0) cappag,prod.id_fondo From cremcre_meta cre
        INNER JOIN tb_cliente cli ON cli.idcod_cliente=cre.CodCli
        INNER JOIN tb_grupo gru ON gru.id_grupos=cre.CCodGrupo
        INNER JOIN cre_productos prod ON prod.id=cre.CCODPRD
        WHERE cre.TipoEnti="GRUP" AND cre.NCiclo=' . $_POST['ciclo'] . ' AND cre.CESTADO="F" AND cre.CCodGrupo="' . $_POST['idgrupo'] . '"');

        $i = 0;
        while ($da = mysqli_fetch_array($datacre, MYSQLI_ASSOC)) {
            $datos[$i] = $da;
            $i++;
            $bandera = true;
        }
        $bandera = false;

        //CUOTAS PENDIENTES DEL GRUPO
        $cuotas[] = [];
        $datacuo = mysqli_query($conexion, 'SELECT timestampdiff(DAY,ppg.dfecven,"' . $hoy . '") atraso,ppg.* FROM Cre_ppg ppg WHERE ccodcta IN (SELECT cre.CCODCTA From cremcre_meta cre WHERE cre.CESTADO="F" AND cre.CCodGrupo="' . $_POST['idgrupo'] . '") 
                            AND ppg.CESTADO="X" ORDER BY ppg.ccodcta,ppg.dfecven,ppg.cnrocuo');
        $i = 0;
        while ($da = mysqli_fetch_array($datacuo, MYSQLI_ASSOC)) {
            $cuotas[$i] = $da;
            $i++;
            $bandera = true;
        }

        //UNION DE TODOS LOS DATOS
        if ($bandera) {
            $datacom[] = [];
            $j = 0;
            while ($j < count($datos)) {
                $ccodcta = $datos[$j]["CCODCTA"];
                $datos[$j]["cuotaspen"] = [];
                $datacom[$j] = $datos[$j];

                //FILTRAR LAS CUOTAS DE LA CUENTA ACTUAL
                $keys = filtro($cuotas, "ccodcta", $ccodcta, $ccodcta);
                $fila = 0;
                $count = 0;
                while ($fila < count($keys)) {
                    $i = $keys[$fila];
                    $fecven = $cuotas[$i]["dfecven"];
                    if ($fecven <= $hoy) {
                        $cuotas[$i]["estado"] = ($fecven < $hoy) ? 2 : 1;
                        $count++;
                    } else {
                        $cuotas[$i]["estado"] = 0;
                    }
                    $datacom[$j]["cuotaspen"][$fila] = $cuotas[$i];
                    $fila++;
                }
                //COMPROBAR SI SOLO TIENE CUOTAS VENCIDAS O IMPRIMIR LA CUOTA SIGUIENTE A PAGAR
                if (count(filtro($datacom[$j]["cuotaspen"], 'estado', 1, 2)) == 0) {
                    //echo 'No hay cuotas vencidas o por vencer'; SE IMPRIMIRA SIGUIENTE NO PAGADA
                    $keyses = filtro($datacom[$j]["cuotaspen"], 'estado', 0, 0);
                    $fa = 0;
                    while ($fa < count($keyses) && $fa < 1) {
                        $il = $keyses[$fa];
                        $datacom[$j]["cuotaspen"][$il]["estado"] = 3;
                        $fa++;
                    }
                }
                //ELIMINACION DEL ARRAY LAS CUOTAS QUE NO SERAN IMPRESAS
                $keynot = filtro($datacom[$j]["cuotaspen"], 'estado', 0, 0);
                $faf = 0;
                while ($faf < count($keynot)) {
                    $il = $keynot[$faf];
                    unset($datacom[$j]["cuotaspen"][$il]);
                    $faf++;
                }
                // $datacom[$j]["sumaho"] = array_sum(array_column($datacom[$j]["cuotaspen"], "AhoPrgPag"));
                $datacom[$j]["summora"] = array_sum(array_column($datacom[$j]["cuotaspen"], "nmorpag"));
                $j++;
            }
            $sumatotalmoraant = array_sum(array_column($datacom, "summora"));
            $sumatotalmoraant = round($sumatotalmoraant, 2);
            $sumatotalmoranew = array_sum(array_column($datosmora, 4));
            $sumatotalmoranew = round($sumatotalmoranew, 2);
        }
        if ($bandera) {
            if ($sumatotalmoraant == $sumatotalmoranew) {
                echo json_encode(['La suma anterior y actual son iguales', '1', 0, $sumatotalmoraant, $sumatotalmoranew]);
            } else {
                echo json_encode(['La suma anterior y actual no son iguales', '1', 1, $sumatotalmoraant, $sumatotalmoranew]);
            }
        } else {
            $sumatotalmoraant = 'x';
            echo json_encode(['No se encontraron datos', '0', 'x', $sumatotalmoraant]);
            return;
        }
        mysqli_close($conexion);
        break;
    case 'consultar_plan_pago':
        $ccodcta = $_POST['codigocredito'];
        $consulta = mysqli_query($conexion, "SELECT cpg.Id_ppg AS id, cpg.dfecven, IF((timestampdiff(DAY,cpg.dfecven,'$hoy'))<0, 0,(timestampdiff(DAY,cpg.dfecven,'$hoy'))) AS diasatraso, cpg.cestado, cpg.cnrocuo AS numcuota, cpg.ncappag AS capital, cpg.nintpag AS interes, cpg.nmorpag AS mora, cpg.AhoPrgPag AS ahorropro, cpg.OtrosPagosPag AS otrospagos
        FROM Cre_ppg cpg
        WHERE cpg.ccodcta='$ccodcta'
        ORDER BY cpg.ccodcta, cpg.dfecven, cpg.cnrocuo");
        //se cargan los datos de las beneficiarios a un array
        $array_datos = array();
        $i = 0;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $estado = '';
            if ($fila['cestado'] == 'P') {
                $estado = '<span class="badge text-bg-success">Pagado</span>';
            } elseif ($fila['cestado'] == 'X' && $fila['diasatraso'] > 0) {
                $estado = '<span class="badge text-bg-danger">Vencido</span>';
            } else {
                $estado = '<span class="badge text-bg-primary">Por pagar</span>';
            }
            $array_datos[$i] = array(
                "0" => $fila["numcuota"],
                "1" => $fila["dfecven"],
                "2" => $estado,
                "3" => ($fila['cestado'] == 'P') ? (0) : ($fila["diasatraso"]),
                "4" => $fila["capital"],
                "5" => $fila["interes"],
                "6" => $fila["mora"],
                "7" => $fila["ahorropro"],
                "8" => $fila["otrospagos"]
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
    case 'list_reimp_creditos_indi':
        $consulta = mysqli_query($conexion, "SELECT ck.CCODCTA AS ccodcta, ck.CNUMING AS recibo, cm.NCiclo AS ciclo, ck.DFECPRO AS fecha, ck.NMONTO AS monto, ck.CNROCUO AS numcuota
        FROM CREDKAR ck
        INNER JOIN cremcre_meta cm ON ck.CCODCTA=cm.CCODCTA
        WHERE ck.CTIPPAG='P' AND ck.CESTADO!='X' AND cm.TipoEnti='INDI'");
        //se cargan los datos de las beneficiarios a un array
        $array_datos = array();
        $i = 0;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {

            $data = implode("||", $fila);

            $array_datos[] = array(
                "0" => $fila["ccodcta"],
                "1" => $fila["recibo"],
                "2" => $fila["ciclo"],
                "3" => $fila["fecha"],
                "4" => $fila["monto"],

                "5" => '<button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="reportes([[], [], [], [`' . $_SESSION['nombre'] . ' ' . $_SESSION['apellido'] . '`,`' . $fila["ccodcta"] . '`,`' . $fila["numcuota"] . '`,`' . $fila["recibo"] . '`]], `pdf`, `comp_individual`, 0)"><i class="fa-solid fa-print me-2"> </i>Reimpimir</button> 

                <button type="button" class="btn btn-outline-secondary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#staticBackdrop" onclick="capData(' . $data . ',["1","2"])"><i class="fa-sharp fa-solid fa-pen-to-square"></i></button>

                <button type="button" class="btn btn-outline-danger btn-sm mt-2"><i class="fa-solid fa-trash-can"></i></button>'

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

    case 'recibosgrupal':
        $consulta = mysqli_query($conexion, "SELECT kar.CODKAR, kar.CNUMING, kar.DFECPRO,SUM(kar.NMONTO) NMONTO,grup.NombreGrupo,cre.CCodGrupo,cre.NCiclo FROM CREDKAR kar
        INNER JOIN cremcre_meta cre on cre.ccodcta=kar.CCODCTA
        INNER JOIN tb_grupo grup on grup.id_grupos=cre.CCodGrupo
		WHERE kar.CTIPPAG='P' AND kar.CESTADO!='X' AND cre.TipoEnti='GRUP' GROUP BY cre.CCodGrupo,cre.NCiclo, kar.CNUMING
        ORDER BY kar.DFECPRO,kar.CNUMING,kar.CCODCTA");
        //se cargan los datos de las beneficiarios a un array
        $array_datos = array();
        $i = 0;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $array_datos[$i] = array(
                "0" => $i + 1,
                "1" => $fila["NombreGrupo"],
                "2" => $fila["NCiclo"],
                "3" => $fila["CNUMING"],
                "4" => date("d-m-Y", strtotime($fila["DFECPRO"])),
                "5" => $fila["NMONTO"],
                "6" => '<button type="button" class="btn btn-outline-primary" onclick="reportes([[], [], [], [' . $fila["CCodGrupo"] . ',`' . $fila["CNUMING"] . '`,' . $fila["NCiclo"] . ']], `pdf`, `comp_grupal`, 0);"><i class="fa-solid fa-print"></i> Reimpresion</button>',
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

        //Actualizar Recibo de credito individual
    case 'actReciCreIndi':
        $inputs = $_POST["inputs"];
        $codusu = $_POST["archivo"];

        $conexion->autocommit(false);
        //Obtener informaicon de la credkar
        $consulta = mysqli_query($conexion, "SELECT CCODCTA, DFECPRO, CNUMING, CAST(DFECSIS AS DATE) FROM CREDKAR WHERE CODKAR = $inputs[0]");
        $dato = $consulta->fetch_row();

        //COMPROBAR CIERRE DE CAJA
        $fechainicio = date('Y-m-d', strtotime(date('Y-m-d') . ' - 7 days'));
        $fechafin = date('Y-m-d');
        $cierre_caja = comprobar_cierre_caja($_SESSION['id'], $conexion, 1, $fechainicio, $fechafin, $dato[3]);
        if ($cierre_caja[0] < 6) {
            echo json_encode([$cierre_caja[1], '0']);
            return;
        }

        //Validar si existe los datos en la ctb_Diario
        $validarDatos = $conexion->query("SELECT EXISTS(SELECT * FROM ctb_diario WHERE cod_aux = '$dato[0]' AND fecdoc = '$dato[1]' AND numdoc = '$dato[2]') AS Resultado");

        // Si la consulta no fue exitosa 
        $resultado = $validarDatos->fetch_assoc()['Resultado'];
        if ($resultado == 0) {
            // echo json_encode(['Los datos no existen en el IDARIO, no se puede actualizar', '0']);
            // return;
        } //Fin validad repetidos

        try {
            $res = $conexion->query("UPDATE CREDKAR SET CNUMING = '$inputs[1]', DFECPRO = '$inputs[2]', CCONCEP = '$inputs[3]', updated_by = $codusu[0], updated_at = '$hoy2'  WHERE CODKAR = $inputs[0]");
            $aux = mysqli_error($conexion);

            $res1 = $conexion->query("UPDATE ctb_diario SET numdoc = '$inputs[1]', fecdoc = '$inputs[2]', feccnt = '$inputs[2]', updated_by = $codusu[0], updated_at = '$hoy2'  WHERE cod_aux = '$dato[0]' AND fecdoc = '$dato[1]' AND numdoc = '$dato[2]'");
            $aux1 = mysqli_error($conexion);

            if ($aux && $aux1) {
                echo json_encode(['Error', '0']);
                $conexion->rollback();
                return;
            }
            if (!$res && !$res1) {
                echo json_encode(['Error al ingresar ', '0']);
                $conexion->rollback();
                return;
            }
            $conexion->commit();
            echo json_encode(['Los datos se actualizaron con éxito. ', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, en la actualización: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;

        //INI

    case 'eliReIndi':
        $idDato = $_POST["ideliminar"];
        $codusu = $_POST["archivo"];

        $conexion->autocommit(false);
        //Obtener informaicon de la credkar
        $consulta = mysqli_query($conexion, "SELECT CCODCTA, DFECPRO, CNUMING, CAST(DFECSIS AS DATE) AS DFECSIS FROM CREDKAR WHERE CODKAR = $idDato");
        $dato = $consulta->fetch_row();

        //COMPROBAR CIERRE DE CAJA
        $fechainicio = date('Y-m-d', strtotime(date('Y-m-d') . ' - 7 days'));
        $fechafin = date('Y-m-d');
        $cierre_caja = comprobar_cierre_caja($_SESSION['id'], $conexion, 1, $fechainicio, $fechafin, $dato[3]);
        if ($cierre_caja[0] < 6) {
            echo json_encode([$cierre_caja[1], '0']);
            return;
        }

        //Validar si existe los datos en la ctb_Diario
        $validarDatos = $conexion->query("SELECT EXISTS(SELECT * FROM ctb_diario WHERE cod_aux = '$dato[0]' AND fecdoc = '$dato[1]' AND numdoc = '$dato[2]') AS Resultado");
        // Si la consulta no fue exitosa 
        $resultado = $validarDatos->fetch_assoc()['Resultado'];

        try {
            $res = $conexion->query("UPDATE CREDKAR SET CESTADO = 'X', deleted_by = $codusu, deleted_at = '$hoy2'  WHERE CODKAR = $idDato");
            $aux = mysqli_error($conexion);

            $res1 = $conexion->query("UPDATE ctb_diario SET estado = '0', deleted_by = $codusu, deleted_at = '$hoy2'  WHERE cod_aux = '$dato[0]' AND fecdoc = '$dato[1]' AND numdoc = '$dato[2]'");
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

            //ACTUALIZACION DEL PLAN DE PAGO
            $res = $conexion->query("CALL update_ppg_account('" . $dato[0] . "')");
            if (!$res) {
                echo json_encode(['Error al actualizar el plan de pago ' . $i, '0']);
                $conexion->rollback();
                return;
            }

            $conexion->commit();
            //$conexion->rollback();//cambiar por comit
            echo json_encode(['Los datos fueron actualizados con exito ', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, al hacer el registro: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        return;

        break;
        //Recibo de gruop
    case 'reciboDeGrupos':
        $data = $_POST['extra'];
        $data1 = explode("||", $data);
        ob_start();
        $consulta = mysqli_query($conexion, "SELECT cli.short_name AS nomCli, kar.CCONCEP
        FROM CREDKAR AS kar
        INNER JOIN cremcre_meta AS creMet ON kar.CCODCTA = creMet.CCODCTA 
        INNER JOIN tb_cliente AS cli ON cli.idcod_cliente = creMet.CodCli
        INNER JOIN tb_grupo AS gru ON gru.id_grupos = creMet.CCodGrupo
        WHERE kar.CESTADO != 'X' AND  kar.CNUMING = '$data1[0]' AND creMet.CCodGrupo = $data1[1] AND creMet.NCiclo = $data1[2]");

        $totalData = mysqli_affected_rows($conexion); //Cantidad de información que se esta retornando
        $con = 0;
        $flag = 0;

        $izq = $totalData / 2;
        if (($totalData % 2) != 0) {
            $izq = (intval($izq)) + 1;
        }

        while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $con = $con + 1;
            if ($flag == 0) {
                $flag++;  ?>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="accordion" id="accordionUno">
                        <?php }

                    if ($con <= $izq) {
                        ?>
                            <!-- INI -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="<?= 'heading' . $con ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="<?= '#collapse' . $con ?>" aria-expanded="false" aria-controls="<?= 'collapse' . $con ?>">
                                        <b> - </b><label><?php echo $row['nomCli'] ?></label><br>
                                    </button>
                                </h2>

                                <div id="<?= 'collapse' . $con ?>" class="accordion-collapse collapse" aria-labelledby="<?= 'heading' . $con ?>" data-bs-parent="#accordionUno">
                                    <div class="accordion-body">

                                        <div class="mb-3">
                                            <label for="exampleFormControlTextarea1" class="form-label"><b>Concepto</b></label>
                                            <textarea class="form-control" name="datoCon" rows="3" id="<?= $con . 'concep' ?>"> <?php echo $row['CCONCEP'] ?> </textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!-- FIN  -->

                        <?php }
                    if ($con == $izq) {
                        ?>
                        </div><!-- cerrar el acordion 1 -->
                    </div><!-- cerrar la calumna -->

                    <div class="col-lg-6">
                        <div class="accordion" id="accordionUno">
                        <?php
                    }
                    if ($con > $izq) {
                        ?>
                            <!-- INI -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="<?= 'heading' . $con ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="<?= '#collapse' . $con ?>" aria-expanded="false" aria-controls="<?= 'collapse' . $con ?>">
                                        <b> - </b><label><?php echo $row['nomCli'] ?></label><br>
                                    </button>
                                </h2>

                                <div id="<?= 'collapse' . $con ?>" class="accordion-collapse collapse" aria-labelledby="<?= 'heading' . $con ?>" data-bs-parent="#accordionUno">
                                    <div class="accordion-body">

                                        <div class="mb-3">
                                            <label for="exampleFormControlTextarea1" class="form-label"><b>Concepto</b></label>
                                            <textarea class="form-control" name="datoCon" rows="3" id="<?= $con . 'concep' ?>"> <?php echo $row['CCONCEP'] ?> </textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!-- FIN  -->
                            <?php
                            if ($con == $totalData) {
                            ?>
                        </div><!-- cerrar el acordion 1 -->
                    </div><!-- cerrar la calumna -->
        <?php
                            }
                        }
                    }
        ?>
                </div><!-- Cerrar la fila -->

                <input type="text" id="total" value="<?= $totalData ?>" disabled hidden>



        <?php

        $output = ob_get_clean();
        echo $output;

        break;

        //Actuliza  recibos grupales
    case 'actReciCreGru':
        $inputs = $_POST["inputs"];
        $codusu = $_POST["archivo"];
        $concep = $codusu[1];
        $conexion->autocommit(false);
        //Obtener las ides 
        $consultado = mysqli_query($conexion, "SELECT kar.CODKAR 
        FROM CREDKAR AS kar
        INNER JOIN cremcre_meta AS creMet ON kar.CCODCTA = creMet.CCODCTA 
        INNER JOIN tb_cliente AS cli ON cli.idcod_cliente = creMet.CodCli
        INNER JOIN tb_grupo AS gru ON gru.id_grupos = creMet.CCodGrupo
        WHERE kar.CESTADO != 'X' AND  kar.CNUMING = '$inputs[4]' AND creMet.CCodGrupo = $inputs[0]  AND creMet.NCiclo = $inputs[3]");

        $totalF = mysqli_affected_rows($conexion); //Total de filas afectadas
        $conR = 0; //Contador de resultados

        while ($dato1 = mysqli_fetch_row($consultado)) {
            //Obtener la informacion de la CREDKAR 
            $consultado1 = mysqli_query($conexion, "SELECT CCODCTA, DFECPRO, CNUMING, CAST(DFECSIS AS DATE) AS DFECSIS FROM CREDKAR WHERE CODKAR = $dato1[0]");
            $dato = $consultado1->fetch_row();

            //COMPROBAR CIERRE DE CAJA
            $fechainicio = date('Y-m-d', strtotime(date('Y-m-d') . ' - 7 days'));
            $fechafin = date('Y-m-d');
            $cierre_caja = comprobar_cierre_caja($_SESSION['id'], $conexion, 1, $fechainicio, $fechafin, $dato[3]);
            if ($cierre_caja[0] < 6) {
                echo json_encode([$cierre_caja[1], '0']);
                return;
            }

            //Validar si existe los datos en la ctb_Diario
            $validarDatos = $conexion->query("SELECT EXISTS(SELECT * FROM ctb_diario WHERE cod_aux = '$dato[0]' AND fecdoc = '$dato[1]' AND numdoc = '$dato[2]') AS Resultado");
            //Caputrar el resultado 
            $resultado = $validarDatos->fetch_assoc()['Resultado'];

            //Si el resultado es 1 sumarlo en conR
            if ($resultado == 1) {
                $conR = $conR + 1;
            } else {
                echo json_encode(['Uno de los datos no existen en el diario, no se puede actualizar la información', '0']);
                return;
            }
        }

        try {
            $aux1 = 0;
            $aux2 = 0;
            $con = 0;

            //Obtener las ides que se tienen que actualizar 
            $consultado = mysqli_query($conexion, "SELECT kar.CODKAR 
            FROM CREDKAR AS kar
            INNER JOIN cremcre_meta AS creMet ON kar.CCODCTA = creMet.CCODCTA 
            INNER JOIN tb_cliente AS cli ON cli.idcod_cliente = creMet.CodCli
            INNER JOIN tb_grupo AS gru ON gru.id_grupos = creMet.CCodGrupo
            WHERE kar.CESTADO != 'X' AND  kar.CNUMING = '$inputs[4]' AND creMet.CCodGrupo = $inputs[0]  AND creMet.NCiclo = $inputs[3]");

            if ($totalF == $conR) {
                while ($dato1 = mysqli_fetch_row($consultado)) {

                    // echo json_encode(['Re. '.$inputs[1].' Fe. '.$inputs[2], '0']);
                    // $conexion->rollback();
                    // return;

                    $consultado1 = mysqli_query($conexion, "SELECT CCODCTA, DFECPRO, CNUMING FROM CREDKAR WHERE CODKAR = $dato1[0]");
                    $dato = $consultado1->fetch_row();

                    //Actualiza la CREDKAR
                    $res = $conexion->query("UPDATE CREDKAR SET CNUMING = '$inputs[1]', DFECPRO = '$inputs[2]', CCONCEP = '$concep[$con]', updated_by = $codusu[0], updated_at = '$hoy2'  WHERE CODKAR = $dato1[0]");

                    $error1 = mysqli_error($conexion);

                    //Actualiza el diario 
                    $res1 = $conexion->query("UPDATE ctb_diario SET numdoc = '$inputs[1]', fecdoc = '$inputs[2]', feccnt = '$inputs[2]', updated_by = $codusu[0], updated_at = '$hoy2'  WHERE cod_aux = '$dato[0]' AND fecdoc = '$dato[1]' AND numdoc = '$dato[2]'");
                    $error2 = mysqli_error($conexion);

                    if ($error1 && $error2) {
                        echo json_encode(['Error', '0']);
                        $conexion->rollback();
                        return;
                    }
                    if (!$res && !$res1) {
                        echo json_encode(['Error al ingresar ', '0']);
                        $conexion->rollback();
                        return;
                    }
                    if ($res) $aux1++;
                    if ($res1) $aux2++;

                    $con++;
                }
            }
            if ($aux1 == $aux2) {
                $conexion->commit();
                echo json_encode(['Los datos se actualizaron con éxito. ', '1']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, en la actualización: ' . $e->getMessage(), '0']);
        }

        mysqli_close($conexion);

        break;

        //Eliminar recibo de grupos
    case 'eliReGru':
        $data = $_POST["ideliminar"];
        $identificado = explode("|*-*|", $data);
        $codusu = $_POST["archivo"];

        $conexion->autocommit(false);
        //Obtener las ides 
        $consultado = mysqli_query($conexion, "SELECT kar.CODKAR 
            FROM CREDKAR AS kar
            INNER JOIN cremcre_meta AS creMet ON kar.CCODCTA = creMet.CCODCTA 
            INNER JOIN tb_cliente AS cli ON cli.idcod_cliente = creMet.CodCli
            INNER JOIN tb_grupo AS gru ON gru.id_grupos = creMet.CCodGrupo
            WHERE kar.CESTADO != 'X' AND  kar.CNUMING = '$identificado[0]' AND creMet.CCodGrupo = $identificado[1]  AND creMet.NCiclo = $identificado[2]");

        $totalF = mysqli_affected_rows($conexion); //Total de filas afectadas
        $conR = 0; //Contador de resultados

        while ($dato1 = mysqli_fetch_row($consultado)) {
            //Obtener la informacion de la CREDKAR 
            $consultado1 = mysqli_query($conexion, "SELECT CCODCTA, DFECPRO, CNUMING, CAST(DFECSIS AS DATE) AS DFECSIS FROM CREDKAR WHERE CODKAR = $dato1[0]");
            $dato = $consultado1->fetch_row();

            //COMPROBAR CIERRE DE CAJA
            $fechainicio = date('Y-m-d', strtotime(date('Y-m-d') . ' - 7 days'));
            $fechafin = date('Y-m-d');
            $cierre_caja = comprobar_cierre_caja($_SESSION['id'], $conexion, 1, $fechainicio, $fechafin, $dato[3]);
            if ($cierre_caja[0] < 6) {
                echo json_encode([$cierre_caja[1], '0']);
                return;
            }

            //Validar si existe los datos en la ctb_Diario
            $validarDatos = $conexion->query("SELECT EXISTS(SELECT * FROM ctb_diario WHERE cod_aux = '$dato[0]' AND fecdoc = '$dato[1]' AND numdoc = '$dato[2]') AS Resultado");
            //Caputrar el resultado 
            $resultado = $validarDatos->fetch_assoc()['Resultado'];

            //Si el resultado es 1 sumarlo en conR
            if ($resultado == 1) {
                $conR = $conR + 1;
            } else {
                echo json_encode(['Uno de los datos no existen en el diario, no se puede actualizar la información', '0']);
                return;
            }
        }

        try {
            $aux1 = 0;
            $aux2 = 0;
            $con = 0;
            //Obtener las ides que se tienen que actualizar 
            $consultado = mysqli_query($conexion, "SELECT kar.CODKAR 
            FROM CREDKAR AS kar
            INNER JOIN cremcre_meta AS creMet ON kar.CCODCTA = creMet.CCODCTA 
            INNER JOIN tb_cliente AS cli ON cli.idcod_cliente = creMet.CodCli
            INNER JOIN tb_grupo AS gru ON gru.id_grupos = creMet.CCodGrupo
            WHERE kar.CESTADO != 'X' AND  kar.CNUMING = '$identificado[0]' AND creMet.CCodGrupo = $identificado[1]  AND creMet.NCiclo = $identificado[2]");

            if (!$consultado) {
                echo json_encode(['Error', '0']);
                $conexion->rollback();
                return;
            }

            if ($totalF == $conR) {
                while ($dato1 = mysqli_fetch_row($consultado)) {

                    $consultado1 = mysqli_query($conexion, "SELECT CCODCTA, DFECPRO, CNUMING FROM CREDKAR WHERE CODKAR = $dato1[0]");
                    $dato = $consultado1->fetch_row();

                    //Actualiza la CREDKAR
                    $res = $conexion->query("UPDATE CREDKAR SET CESTADO = 'X' , updated_by = $codusu[0], updated_at = '$hoy2'  WHERE CODKAR = $dato1[0]");
                    $error1 = mysqli_error($conexion);

                    //Actualiza el diario 
                    $res1 = $conexion->query("UPDATE ctb_diario SET estado = '0' , updated_by = $codusu[0], updated_at = '$hoy2'  WHERE cod_aux = '$dato[0]' AND fecdoc = '$dato[1]' AND numdoc = '$dato[2]'");
                    $error2 = mysqli_error($conexion);

                    if ($error1 || $error2) {
                        echo json_encode(['Error', '0']);
                        $conexion->rollback();
                        return;
                    }

                    if (!$res || !$res1) {
                        echo json_encode(['Error al ingresar ', '0']);
                        $conexion->rollback();
                        return;
                    }

                    //ACTUALIZACION DEL PLAN DE PAGO
                    $resAux = $conexion->query("CALL update_ppg_account('" . $dato[0] . "')");
                    if (!$resAux) {
                        echo json_encode(['Error al actualizar el plan de pago ' . $i, '0']);
                        $conexion->rollback();
                        return;
                    }

                    if ($res) $aux1++;
                    if ($res1) $aux2++;
                    $con++;
                }
            }

            if ($aux1 == $aux2) {
                $conexion->commit();
                echo json_encode(['Los datos se actualizaron con éxito. ', '1']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, en la actualización: ' . $e->getMessage(), '0']);
        }

        mysqli_close($conexion);
        //FIN
        break;
    case 'listado_aperturas':
        try {
            $stmt = $conexion->prepare("SELECT tcac.id, tcac.id_usuario AS iduser, tcac.saldo_inicial AS saldoinicial, tcac.saldo_final AS saldofinal, tcac.estado AS estado, tcac.fecha_apertura AS fecha_apertura, tcac.fecha_cierre AS fecha_cierre
            FROM tb_caja_apertura_cierre tcac
            WHERE (tcac.id_usuario = ? AND MONTH(tcac.fecha_apertura) = ? AND YEAR(tcac.fecha_apertura) = ?) OR (tcac.estado='1' AND tcac.id_usuario = ?) ORDER BY tcac.fecha_apertura DESC");
            if (!$stmt) {
                $error = $conexion->error;
                $results = [
                    "errorn" => 'Error preparando consulta: ' . $error,
                    "sEcho" => 1,
                    "iTotalRecords" => 0,
                    "iTotalDisplayRecords" => 0,
                    "aaData" => []
                ];
                echo json_encode($results);
                return;
            }
            $user = $_SESSION['id'];
            $mes = date('m');
            $anio = date('Y');
            $stmt->bind_param("ssss", $user, $mes, $anio, $user);
            if (!$stmt->execute()) {
                $results = [
                    "errorn" => "Fallo al ejecutar la consulta",
                    "sEcho" => 1,
                    "iTotalRecords" => 0,
                    "iTotalDisplayRecords" => 0,
                    "aaData" => []
                ];
                echo json_encode($results);
                return;
            }
            $result = $stmt->get_result();
            $array_datos = array();
            $i = 0;
            while ($fila = $result->fetch_assoc()) {
                // $fechacierre = (!validateDate($fila["fecha_cierre"], 'Y-m-d')) ? '0000-00-00' : date('d-m-Y', strtotime($fila["fecha_cierre"]));
                $fechacierre = ($fila["fecha_cierre"] == null) ? '0000-00-00' : date('d-m-Y', strtotime($fila["fecha_cierre"]));
                $array_datos[] = array(
                    "0" => $i + 1,
                    "1" => date('d-m-Y', strtotime($fila["fecha_apertura"])),
                    "2" => number_format($fila["saldoinicial"], 2, '.', ','),
                    "3" => ($fila["saldofinal"] == '0') ? 'Pendiente' : $fechacierre,
                    "4" => ($fila["saldofinal"] == '0') ? 'Pendiente' : $fila["saldofinal"],
                    "5" => ($fila["estado"] == '2') ? '<span class="badge text-bg-success">Cerrado</span>' : (($fila["fecha_apertura"] < date('Y-m-d')) ? '<span class="badge text-bg-danger">Pendiente de cierre con atraso</span>' : '<span class="badge text-bg-warning">Pendiente de cierre</span>'),
                    "6" => ($fila["estado"] == '2') ? '<button type="button" class="btn btn-success btn-sm" onclick="reportes([[],[],[],[`' . $fila["iduser"] . '`,`' . $fila["id"] . '`]], `pdf`, `arqueo_caja`,0)"><i class="fa-solid fa-file-pdf"></i></button>' : '<span class="badge text-bg-secondary">No aplica</span>',
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
        } catch (Exception $e) {
            $results = array(
                "sEcho" => 1,
                "iTotalRecords" => 0,
                "iTotalDisplayRecords" => 0,
                "aaData" => []
            );
            echo json_encode($results);
        } finally {
            if ($stmt !== false) {
                $stmt->close();
            }
            $conexion->close();
        }
        break;
    case 'create_caja_apertura':
        $inputs = $_POST["inputs"];
        $archivo = $_POST["archivo"];

        $validar = validar_campos_plus([
            [$inputs[0], "", 'No se ha detectado ningun identificador de usuario', 1],
            [$inputs[1], "", 'Debe existir una fecha de apertura', 1],
            [$inputs[1], date('Y-m-d'), 'La fecha de apertura deber ser igual a la fecha de hoy', 2],
            [$inputs[1], date('Y-m-d'), 'La fecha de apertura deber ser igual a la fecha de hoy', 3],
            [$inputs[2], "", 'Debe digitar un un saldo inicial', 1],
            [$inputs[2], 1, 'Debe digitar un saldo inicial mayor a 0', 2],
        ]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }

        //Validar si ya existe un registro igual que el nombre
        $stmt = $conexion->prepare("SELECT * FROM tb_caja_apertura_cierre tcac WHERE (tcac.fecha_apertura = ? AND tcac.id_usuario=? AND tcac.estado='1') OR (tcac.fecha_apertura < ? AND tcac.id_usuario=? AND tcac.estado='1')");
        if (!$stmt) {
            $error = $conexion->error;
            echo json_encode(['Error preparando consulta 1: ' . $error, '0']);
            return;
        }
        $aux = date('Y-m-d');
        $stmt->bind_param("ssss", $aux, $inputs[0], $aux, $inputs[0]);
        if (!$stmt->execute()) {
            $errorMsg = $stmt->error;
            echo json_encode(["Fallo al ejecutar la consulta 1: $errorMsg", '0']);
            return;
        }
        $resultado = $stmt->get_result();
        $numFilas = $resultado->num_rows;
        if ($numFilas > 0) {
            echo json_encode(["Ya existe un registro de apertura de caja con la misma fecha o bien tiene un cierre pendiente", '0']);
            return;
        }

        //PREPARACION DE ARRAY
        $data = array(
            'id_usuario' => $inputs[0],
            'saldo_inicial' => $inputs[2],
            'saldo_final' => '0',
            'fecha_apertura' => date('Y-m-d'),
            'estado' => '1',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
            'created_by' => $_SESSION['id'],
            'updated_by' => $_SESSION['id'],
        );

        $conexion->autocommit(FALSE);
        try {
            // //INSERCION DE CLIENTE NATURAL
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $stmt = $conexion->prepare("INSERT INTO tb_caja_apertura_cierre ($columns) VALUES ($placeholders)");
            if (!$stmt) {
                $error = $conexion->error;
                echo json_encode(['Error preparando consulta: ' . $error, '0']);
                return;
            }
            // Obtener los valores del array de datos
            $values = array_values($data);
            // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
            $types = str_repeat('s', count($values));
            // Vincular los parámetros
            $stmt->bind_param($types, ...$values);
            if (!$stmt->execute()) {
                $errorMsg = $stmt->error;
                $conexion->rollback();
                echo json_encode(["Error al ejecutar consulta 2: $errorMsg", '0']);
                return;
            }
            //Realizar el commit especifico
            $conexion->commit();
            echo json_encode(["Apertura de caja ingresado correctamente", '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(["Error: " . $e->getMessage(), '0']);
        } finally {
            if ($stmt !== false) {
                $stmt->close();
            }
            $conexion->close();
        }
        break;
    case 'create_caja_cierre':
        $inputs = $_POST["inputs"];
        $archivo = $_POST["archivo"];

        $validar = validar_campos_plus([
            [$inputs[0], "", 'No se ha detectado ningun identificador de usuario', 1],
            [$inputs[0], $_SESSION['id'], 'El identificador del usuario quien aperturo la caja no coincide con el que quiere cerrar', 8],
            [$archivo[1], "", 'No se encontro el identificador de la apertura de caja', 1],
            [$archivo[1], '0', 'El identificador de apertura de caja debe ser válido', 1],
        ]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }

        $datos[] = [];
        $i = 0;
        $conexion->autocommit(FALSE);
        try {
            //Validar si de casualidad ya se hizo el cierre otro usuario
            $stmt = $conexion->prepare("SELECT tcac.estado FROM tb_caja_apertura_cierre tcac WHERE tcac.id = ? AND tcac.estado='2'");
            if (!$stmt) {
                throw new Exception("Error en la consulta 1: " . $conexion->error);
            }
            $stmt->bind_param("s", $archivo[1]); //El arroba omite el warning de php
            if (!$stmt->execute()) {
                throw new Exception("Error en la ejecucion de la consulta 1: " . $stmt->error);
            }
            $result = $stmt->get_result();
            $numFilas2 = $result->num_rows;
            if ($numFilas2 > 0) {
                echo json_encode(["El cierre ya fue realizado, no se puede reescribir", '0']);
                return;
            }
            //consultando los montos para hacer los calculos
            $stmt2 = $conexion->prepare("SELECT tcac.*, tu.nombre AS nombres, tu.apellido AS apellidos, tu.usu AS usuario, 
                (SELECT IFNULL(SUM(a.monto) ,0) FROM ahommov a WHERE a.ctipope = 'D' AND a.dfecope = tcac.fecha_apertura AND a.codusu = tcac.id_usuario AND a.cestado!=2) AS ingresos_ahorros,
                (SELECT IFNULL(SUM(b.monto) ,0) FROM ahommov b WHERE b.ctipope = 'R' AND b.dfecope = tcac.fecha_apertura AND b.codusu = tcac.id_usuario AND b.cestado!=2) AS egresos_ahorros,
                (SELECT IFNULL(SUM(c.monto) ,0) FROM aprmov c WHERE c.ctipope = 'D' AND c.dfecope = tcac.fecha_apertura AND c.codusu = tcac.id_usuario AND c.cestado!=2) AS ingresos_aportaciones,
                (SELECT IFNULL(SUM(d.monto) ,0) FROM aprmov d WHERE d.ctipope = 'R' AND d.dfecope = tcac.fecha_apertura AND d.codusu = tcac.id_usuario AND d.cestado!=2) AS egresos_aportaciones,
                (SELECT IFNULL(SUM(ck.KP) ,0) FROM CREDKAR ck WHERE ck.CTIPPAG = 'D' AND  ck.DFECPRO = tcac.fecha_apertura AND ck.DFECPRO != 'X' AND ck.CCODUSU = tcac.id_usuario) AS desembolsos_creditos,
                (SELECT IFNULL(SUM(ck2.NMONTO) ,0)  FROM CREDKAR ck2 WHERE ck2.CTIPPAG = 'P' AND  ck2.DFECPRO = tcac.fecha_apertura AND ck2.DFECPRO != 'X' AND ck2.CCODUSU = tcac.id_usuario) AS pagos_creditos,
                (SELECT IFNULL(SUM(opm.monto) ,0)  FROM otr_pago_mov opm INNER JOIN otr_pago op ON opm.id_otr_pago = op.id INNER JOIN otr_tipo_ingreso oti ON opm.id_otr_tipo_ingreso = oti.id WHERE op.estado = '1' AND opm.estado = '1' AND oti.estado = '1' AND oti.tipo = '1' AND op.fecha = tcac.fecha_apertura AND op.created_by = tcac.id_usuario) AS otros_ingresos,
                (SELECT IFNULL(SUM(opm.monto) ,0)  FROM otr_pago_mov opm INNER JOIN otr_pago op ON opm.id_otr_pago = op.id INNER JOIN otr_tipo_ingreso oti ON opm.id_otr_tipo_ingreso = oti.id WHERE op.estado = '1' AND opm.estado = '1' AND oti.estado = '1' AND oti.tipo = '2' AND op.fecha = tcac.fecha_apertura AND op.created_by = tcac.id_usuario) AS otros_egresos
                FROM tb_caja_apertura_cierre tcac INNER JOIN tb_usuario tu ON tcac.id_usuario = tu.id_usu 
                WHERE tcac.id = ? AND tcac.estado='1'");
            if (!$stmt2) {
                throw new Exception("Error en la consulta 2: " . $conexion->error);
            }
            $stmt2->bind_param("s", $archivo[1]); //El arroba omite el warning de php
            if (!$stmt2->execute()) {
                throw new Exception("Error en la ejecucion de la consulta 2: " . $stmt2->error);
            }
            $result = $stmt2->get_result();
            while ($fila = $result->fetch_assoc()) {
                $datos[$i] = $fila;
                $i++;
            }
            $datos[0]['sumaingresos'] = ($datos[0]['saldo_inicial'] + $datos[0]['ingresos_ahorros'] + $datos[0]['ingresos_aportaciones'] + $datos[0]['pagos_creditos'] + $datos[0]['otros_ingresos']);
            $datos[0]['sumaegresos'] = ($datos[0]['egresos_ahorros'] + $datos[0]['egresos_aportaciones'] + $datos[0]['desembolsos_creditos'] + $datos[0]['otros_egresos']);
            $datos[0]['saldofinal'] = ($datos[0]['sumaingresos'] - $datos[0]['sumaegresos']);
            $datos[0]['saldofinal'] = round($datos[0]['saldofinal'], 2);

            //preparando la insercion
            $data = array(
                'id_usuario' => $inputs[0],
                'saldo_final' => $datos[0]['saldofinal'],
                'fecha_cierre' => date('Y-m-d'),
                'estado' => '2',
                'updated_at' => date("Y-m-d H:i:s"),
                'updated_by' => $_SESSION['id'],
            );
            $id = $archivo[1];
            //metodos de actualizacion
            // Columnas a actualizar
            $setCols = [];
            foreach ($data as $key => $value) {
                $setCols[] = "$key = ?";
            }
            $setStr = implode(', ', $setCols);
            $stmt3 = $conexion->prepare("UPDATE tb_caja_apertura_cierre SET $setStr WHERE id = ?");
            if (!$stmt3) {
                throw new Exception("Error en la consulta 3: " . $conexion->error);
            }
            // Obtener los valores del array de datos
            $values = array_values($data);
            // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
            $values[] = $id; // Agregar ID al final
            $types = str_repeat('s', count($values));
            // Vincular los parámetros
            $stmt3->bind_param($types, ...$values);
            if (!$stmt3->execute()) {
                throw new Exception("Error en la ejecucion de la consulta 3: " . $stmt3->error);
            }
            //Realizar el commit especifico
            $conexion->commit();
            echo json_encode(["Cierre de caja completado satisfactoriamente", '1']);
        } catch (Exception $e) {
            //Captura el error
            $mensaje_error = $e->getMessage();
            $conexion->rollback();
            echo json_encode([$mensaje_error, '0']);
        } finally {
            if ($stmt !== false) {
                $stmt->close();
            }
            $conexion->close();
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
}


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

function validar_expresion_regular($cadena, $expresion_regular)
{
    if (preg_match($expresion_regular, $cadena)) {
        return false;
    } else {
        return true;
    }
}

//FILTRO DE DATOS BY BENEQ
function filtro($array, $columna, $p1, $p2)
{
    return (array_keys(array_filter(array_column($array, $columna), function ($var) use ($p1, $p2) {
        return ($var >= $p1 && $var <= $p2);
    })));
}
