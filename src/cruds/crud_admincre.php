<?php
include '../funcphp/func_gen.php';
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
date_default_timezone_set('America/Guatemala');
$hoy = date("Y-m-d");
$hoy2 = date("Y-m-d H:i:s");

$condi = $_POST["condi"];

switch ($condi) {
    case 'create_gastos':
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $radios = $_POST["radios"];
        $archivo = $_POST["archivo"];

        $gastos = $inputs[0];
        $idNomenclatura = $inputs[1];
        $conexion->autocommit(false);

        // validar Repetidos
        $validarRep = $conexion->query("SELECT EXISTS(SELECT * FROM cre_tipogastos WHERE nombre_gasto='$gastos' AND id_nomenclatura=$idNomenclatura) AS Resultado");
        // Si la consulta fue exitosa
        $resultado = $validarRep->fetch_assoc()['Resultado'];
        if ($resultado == 1) {
            echo json_encode(['Los datos ingresados ya existen el sistema ', '0']);
            return;
        } //Fin validad repetidos

        try {

            $res = $conexion->query("INSERT INTO `cre_tipogastos` (`id_nomenclatura`, `nombre_gasto`, `estado`, `created_by`, `created_at`, `afecta_modulo`) 
            VALUES ($idNomenclatura, '$gastos', 1, '$archivo[0]', '$hoy2', $radios[0])");
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
            echo json_encode(['El registro del gasto se realizo con exito. ', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, al hacer el registro: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);

        break;

    case 'ActualizarTipoGasto':
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $radios = $_POST["radios"];
        $archivo = $_POST["archivo"];

        $conexion->autocommit(false);
        // Validar si existen cambios 
        $validarRep = $conexion->query("SELECT EXISTS(SELECT * FROM cre_tipogastos WHERE id=$inputs[0] AND nombre_gasto='$inputs[2]' AND id_nomenclatura=$inputs[1]) AS Resultado");
        // Si la consulta fue exitosa
        $resultado = $validarRep->fetch_assoc()['Resultado'];
        if ($resultado == 1) {
            echo json_encode(['Los datos que ingreso no fueron modificados por que no existe cambios.', '0']);
            return;
        } //Fin validad repetidos

        // Validar si los nuevos datos se repiten 
        $validarRep = $conexion->query("SELECT EXISTS(SELECT * FROM cre_tipogastos WHERE nombre_gasto='$inputs[2]' AND id_nomenclatura=$inputs[1]) AS Resultado");
        // Si la consulta fue exitosa
        $resultado = $validarRep->fetch_assoc()['Resultado'];
        if ($resultado == 1) {
            echo json_encode(['Los datos que está ingresando ya existe en el sistema, favor de cambiar el nombre del gasto. ', '0']);
            return;
        } //Fin validad repetidos

        try {
            $idRegis = $inputs[0];
            $idNomenclatura = $inputs[1];
            $gasto = $inputs[2];

            $res = $conexion->query("UPDATE `cre_tipogastos` SET `id_nomenclatura`=$idNomenclatura, nombre_gasto = '$gasto', updated_by = $archivo[0], updated_at ='$hoy2', afecta_modulo = $radios[0]  WHERE id = $idRegis; 
            ");

            $aux = mysqli_error($conexion);

            if ($aux) {
                echo json_encode(['Error slc', '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['Error al ingresar ', '0']);
                $conexion->rollback();
                return;
            }
            $conexion->commit();
            echo json_encode(['Los datos fuero actualizados con exito ', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, al hacer el registro: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;

    case 'EliminarGastos':
        $id = $_POST["ideliminar"];
        $archivo = $_POST["archivo"];
        // echo json_encode([$id, $archivo]); 

        // return;
        $conexion->autocommit(false);

        try {
            //$id = $archivo;

            $res = $conexion->query("UPDATE `cre_tipogastos` SET estado = 0, deleted_by = $archivo, deleted_at='$hoy2' WHERE id =" . $id);

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

        // ***********************************************************************************************************
    case 'guardarProducto':

        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $radios = $_POST["radios"];
        $archivo = $_POST["archivo"];

        $conexion->autocommit(false);

        $validarRep = $conexion->query("SELECT EXISTS(SELECT * FROM cre_productos WHERE nombre='$inputs[1]') AS Resultado");
        // Si la consulta fue exitosa
        $resultado = $validarRep->fetch_assoc()['Resultado'];
        if ($resultado == 1) {
            echo json_encode(['Favor de cambiar el nombre del producto por que ya existe en el sistema. ', '0']);
            return;
        } //Fin validad repetidos

        try {
            $est = 0;
            $caracteres = '0123456789';
            $codigo;

            while ($est != 1) {
                $codigo = '';
                $max = strlen($caracteres) - 1;
                for ($i = 0; $i < 2; $i++) {
                    $codigo .= $caracteres[mt_rand(0, $max)];
                }

                $cod = intval($codigo);

                $validarRep = $conexion->query("SELECT EXISTS(SELECT * FROM cre_productos WHERE cod_producto = $codigo ) AS Resultado");
                // Si la consulta fue exitosa
                $resultado = $validarRep->fetch_assoc()['Resultado'];
                if ($resultado == 0) {
                    $est = 1;
                } //Fin validad repetidos
            }
            //si la mora es de tipo monto fijo, se guarda en tipcalculo por cada cuantos dias de atraso se va a cobrar el monto fijado
            $tipcalculo = ($radios[0] == 1) ? $radios[1] : (($inputs[6] != '') ? $inputs[6] : 0);
            //***************************************************** */
            $res = $conexion->query("INSERT INTO `cre_productos` (`id_fondo`, `cod_producto`, `nombre`, `descripcion` , `monto_maximo`, `tasa_interes`, `porcentaje_mora`, `dias_de_gracias`, `tipo_mora`, `tipo_calculo`, `estado`,`created_by`, `created_at`,`id_cuenta_capital`,`id_cuenta_interes`, `id_cuenta_mora`,`id_cuenta_otros`) 
            VALUES ($selects[0],'$codigo','$inputs[0]','$inputs[1]',$inputs[2],$inputs[3],$inputs[4],$inputs[5],$radios[0],$tipcalculo,1,$archivo[0],'$hoy2',1,1,1,1)");

            $aux = mysqli_error($conexion);

            if ($aux) {
                echo json_encode([$aux, '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['Error al ingresar ', '0']);
                $conexion->rollback();
                return;
            }
            $conexion->commit();
            echo json_encode(['Los datos se registraron con éxito. ', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, al hacer el registro: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);

        break;
        // ****************** ACTUALIZACIÓN DE PRODUCTOS
    case 'actualizarProducto':
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $radios = $_POST["radios"];
        $archivo = $_POST["archivo"];

        $conexion->autocommit(false);

        $validarRep = $conexion->query("SELECT EXISTS(SELECT * FROM cre_productos WHERE nombre='$inputs[0]' AND id != '$inputs[6]') AS Resultado");
        // Si la consulta fue exitosa
        $resultado = $validarRep->fetch_assoc()['Resultado'];

        if ($resultado == 1) {
            echo json_encode(['Favor de cambiar el nombre del producto por que ya existe en el sistema. ', '0']);
            return;
        } //Fin validad repetidos

        try {
            //si la mora es de tipo monto fijo, se guarda en tipcalculo por cada cuantos dias de atraso se va a cobrar el monto fijado
            $tipcalculo = ($radios[0] == 1) ? $radios[1] : (($inputs[7] != '') ? $inputs[7] : 0);
            $res = $conexion->query("UPDATE cre_productos SET id_fondo = '" . $selects[0] . "', nombre = '" . $inputs[0] . "', descripcion = '" . $inputs[1] . "', monto_maximo = " . $inputs[2] . ", tasa_interes = " . $inputs[3] . ", porcentaje_mora = " . $inputs[4] . ", dias_de_gracias = " . $inputs[5] . ", tipo_mora = " . $radios[0] . ", tipo_calculo = " . $tipcalculo . ", updated_by = " . $archivo[0] . ", updated_at = '" . $hoy2 . "' WHERE id = " . $inputs[6]);

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
            echo json_encode(['Los datos se actualizaron con éxito. ', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, en la actualización: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);

        break;

    case 'eliminaProducto':
        $id = $_POST["ideliminar"];
        $archivo = $_POST["archivo"];

        // echo json_encode([$archivo, $id]); 
        // return;
        $resultado = $conexion->query("SELECT Cestado 
        FROM cremcre_meta AS creMet
        INNER JOIN cre_productos AS crePro ON creMet.CCODPRD = crePro.id
        WHERE creMet.Cestado = ('A' OR  'D' OR 'E' OR 'F' OR 'G') AND crePro.id =" . $id);

        $dato = mysqli_affected_rows($conexion);

        if ($dato > 0) {
            echo json_encode(["El producto ya cuenta con un crédito, no se puede eliminar ", '0']);
            return;
        }

        $conexion->autocommit(false);
        try {
            //$res = $conexion->query("UPDATE `cre_productos2` SET estado= 0, deleted_by = $archivo , deleted_at = '$hoy2' WHERE id =". $di);
            $res = $conexion->query("UPDATE `cre_productos` SET estado = 0, deleted_by = $archivo, deleted_at='$hoy2' WHERE id =" . $id);

            $aux = mysqli_error($conexion);

            // echo json_encode([$aux, $id]); 
            // return;

            if ($aux) {
                echo json_encode(['Error', '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['Error al eliminar ', '0']);
                $conexion->rollback();
                return;
            }
            $conexion->commit();
            echo json_encode(['Los datos se eliminaron con éxito. ', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, al hacer el registro: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;

        //********************************************************************************************* */
    case 'guadarGastosProductos':

        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $radios = $_POST["radios"];
        $archivo = $_POST["archivo"];

        $calculox = ($radios[1] == 3) ? 1 : $radios[2];

        $conexion->autocommit(false);
        // validar Repetidos
        $validarRep = $conexion->query("SELECT EXISTS(SELECT * FROM cre_productos_gastos WHERE id_producto=$inputs[0] AND id_tipo_deGasto = $selects[0] AND estado=1) AS Resultado");
        $resultado = $validarRep->fetch_assoc()['Resultado'];
        if ($resultado == 1) {
            echo json_encode(['Al producto ya se le asignó el tipo de gasto seleccionado, favor de cambiarlo por otro tipo de gasto.', '0']);
            return;
        } //Fin validad repetidos

        try {
            $res = $conexion->query("INSERT INTO `cre_productos_gastos` (`id_producto`, `id_tipo_deGasto`, `tipo_deCobro`, `tipo_deMonto`, `monto`, `estado`, `created_by`, `created_at`, `calculox`) 
            VALUE ($inputs[0],$selects[0],$radios[0],$radios[1],$inputs[1],1,$archivo[0],'$hoy2',$calculox);");
            //VALUE ($selects[0],$inputs[0],$radios[0],$radios[1],$inputs[1],1,$archivo[0],'$hoy2');");
            $aux = mysqli_error($conexion);

            //echo $aux;

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
            echo json_encode(['Los datos se registraron con éxito. ', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, al hacer el registro: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);

        break;

    case 'actGasPro':
        //************************************ */
        $inputs = $_POST["inputs"];
        $selects = $_POST["selects"];
        $radios = $_POST["radios"];
        $archivo = $_POST["archivo"];

        $conexion->autocommit(false);

        $validarRep = $conexion->query("SELECT EXISTS(SELECT * FROM cre_productos_gastos WHERE id_producto=$inputs[1] AND id_tipo_deGasto = $selects[0] AND id != $inputs[0]) AS Resultado");
        $resultado = $validarRep->fetch_assoc()['Resultado'];
        if ($resultado == 1) {
            echo json_encode(['Al producto ya se le asignó el tipo de gasto seleccionado, favor de cambiarlo por otro tipo de gasto.', '0']);
            return;
        } //Fin validad repetidos
        $calculox = ($radios[1] == 3) ? 1 : $radios[2];
        try {
            $res = $conexion->query("UPDATE cre_productos_gastos SET id_producto = " . $inputs[1] . ", id_tipo_deGasto = " . $selects[0] . ", tipo_deCobro = " . $radios[0] . ", tipo_deMonto = " . $radios[1] . ", monto = " . $inputs[2] . ", calculox = " . $calculox . ", updated_by = " . $archivo[0] . ", updated_at = '" . $hoy2 . "' WHERE id =" . $inputs[0]);
            //$res = $conexion->query("UPDATE cre_productos_gastos SET id_producto = ".$selects[0].", id_tipo_deGasto = ".$inputs[1].", tipo_deCobro = ".$radios[0].", tipo_deMonto = ".$radios[1].", monto = ".$inputs[2].", updated_by = ".$archivo[0].", updated_at = '".$hoy2."' WHERE id =" . $inputs[0]);

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
            echo json_encode(['Los datos se actualizaron con éxito. ', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, al hacer el registro: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        //************************************ */
        break;

    case 'elimnarGasPro':
        $id = $_POST["ideliminar"];
        $archivo = $_POST["archivo"];

        $conexion->autocommit(false);

        try {
            //$res = $conexion->query("UPDATE `cre_productos2` SET estado= 0, deleted_by = $archivo , deleted_at = '$hoy2' WHERE id =". $di);
            $res = $conexion->query("UPDATE cre_productos_gastos SET estado = 0, deleted_by = " . $archivo . ", deleted_at = '" . $hoy2 . "' WHERE id =" . $id);

            $aux = mysqli_error($conexion);

            // echo json_encode([$aux, $id]); 
            // return;

            if ($aux) {
                echo json_encode(['Error', '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['Error al eliminar ', '0']);
                $conexion->rollback();
                return;
            }
            $conexion->commit();
            echo json_encode(['Los datos se eliminaron con éxito. ', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, al hacer el registro: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'create_parametrizacion_creditos':
        $inputs = $_POST["inputs"];
        $archivo = $_POST["archivo"];

        //VALIDACIONES DE LOS CAMPOS
        $validar = validar_campos([
            [$inputs[0], "", 'Debe seleccionar un producto a actualizar'],
            [$inputs[1], "", 'Debe seleccionar un producto a actualizar'],
            [$inputs[3], "", 'Debe seleccionar un cuenta contable para capital'],
            [$inputs[4], "", 'Debe seleccionar un cuenta contable para capital'],
            [$inputs[5], "", 'Debe seleccionar un cuenta contable para capital'],
            [$inputs[6], "", 'Debe seleccionar una cuenta contable para interés'],
            [$inputs[7], "", 'Debe seleccionar una cuenta contable para interés'],
            [$inputs[8], "", 'Debe seleccionar una cuenta contable para interés'],
            [$inputs[9], "", 'Debe seleccionar una cuenta contable para mora'],
            [$inputs[10], "", 'Debe seleccionar una cuenta contable para mora'],
            [$inputs[11], "", 'Debe seleccionar una cuenta contable para mora'],
            [$inputs[12], "", 'Debe seleccionar una cuenta contable para otros'],
            [$inputs[13], "", 'Debe seleccionar una cuenta contable para otros'],
            [$inputs[14], "", 'Debe seleccionar una cuenta contable para otros'],
        ]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }

        //validar capital
        if ($inputs[3] < 1) {
            echo json_encode(["Debe digitar un cuenta contable válida para capital", '0']);
            return;
        }
        //validar interes
        if ($inputs[6] < 1) {
            echo json_encode(["Debe digitar un cuenta contable válida para interés", '0']);
            return;
        }
        //validar mora
        if ($inputs[9] < 1) {
            echo json_encode(["Debe digitar un cuenta contable válida para mora", '0']);
            return;
        }
        //validar mora
        if ($inputs[12] < 1) {
            echo json_encode(["Debe digitar un cuenta contable válida para otros", '0']);
            return;
        }

        $conexion->autocommit(false);
        try {
            $res = $conexion->query("UPDATE `cre_productos` SET  id_cuenta_capital='$inputs[3]', id_cuenta_interes='$inputs[6]', id_cuenta_mora='$inputs[9]', id_cuenta_otros='$inputs[12]', updated_by='$archivo[0]', updated_at='$hoy2' WHERE `id`='$inputs[0]'");
            $aux = mysqli_error($conexion);
            if ($aux) {
                $conexion->rollback();
                echo json_encode(['Fallo al actualizar la parametrización', '0']);
                return;
            }
            if ($res) {
                $conexion->commit();
                echo json_encode(['Parametrización actualizado satisfactoriamente', '1']);
            } else {
                $conexion->rollback();
                echo json_encode(['Parametrización no actualizado satisfactoriamente', '0']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al hacer la actualizacion: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'update_dias_laborales':
        $archivo = $_POST["archivo"];
        $conexion->autocommit(false);
        try {

            $estado = ($archivo[1] == 1) ? 1 : 0;
            //consultar si este dia tiene es como ajuste de otro
            if ($estado == 0) {
                $res = $conexion->query("SELECT EXISTS(SELECT * FROM tb_dias_laborales tdl WHERE tdl.laboral = 0 AND id_dia_ajuste ='$archivo[0]') AS resultado");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    $conexion->rollback();
                    echo json_encode(['Fallo al consultar disponibilidad dia', '0']);
                    return;
                }
                if (!$res) {
                    $conexion->commit();
                    echo json_encode(['Error al consultar disponbilidad', '1']);
                }
                $resultado = $res->fetch_assoc()['resultado'];
                if ($resultado == 1) {
                    echo json_encode(['El dia que quiere marcar como no laborable no se puede completar, porque esta asignado como dia de ajuste, primero quitelo como dia de ajuste y podra realizar la operación', '0']);
                    return;
                }
                //Consultar que dia por default se puede asignar
                $banderaant = false;
                $banderades = false;
                $k = 1;
                $idaux = $archivo[0];
                $diasajuste = array();
                $idant = $archivo[0];
                $iddes = $archivo[0];
                while ($k < 4) {
                    // validar rangos
                    $idant = $idant - 1;
                    $iddes = $iddes + 1;

                    if ($idant == 0) {
                        $idant = 7;
                    }

                    if ($iddes == 8) {
                        $iddes = 1;
                    }
                    if ($banderaant == false) {

                        $res = $conexion->query("SELECT tdl.id AS id, tdl.dia AS dia FROM tb_dias_laborales tdl WHERE (tdl.id = $idant) AND tdl.laboral = 1");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            $conexion->rollback();
                            echo json_encode(['Fallo al consultar dia de ajuste', '0']);
                            return;
                        }
                        if (!$res) {
                            $conexion->commit();
                            echo json_encode(['Error al consultar dia de ajuste', '1']);
                        }
                        //pasar los datos al array
                        while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
                            $diasajuste[] = $row;
                            $banderaant = true;
                        }
                    }
                    if ($banderades == false) {
                        $res = $conexion->query("SELECT tdl.id AS id, tdl.dia AS dia FROM tb_dias_laborales tdl WHERE (tdl.id = $iddes) AND tdl.laboral = 1");
                        $aux = mysqli_error($conexion);
                        if ($aux) {
                            $conexion->rollback();
                            echo json_encode(['Fallo al consultar dia de ajuste', '0']);
                            return;
                        }
                        if (!$res) {
                            $conexion->commit();
                            echo json_encode(['Error al consultar dia de ajuste', '1']);
                        }
                        //pasar los datos al array
                        while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
                            $diasajuste[] = $row;
                            $banderades = true;
                        }
                    }
                    $k = ($banderaant && $banderades) ? 4 : $k;
                    $k++;
                }
                if ($banderaant == false && $banderades == false) {
                    $conexion->rollback();
                    echo json_encode(['No se encontro un dia de ajuste disponible, por lo que no puede completar esta acción', '0']);
                    return;
                }
                //asignar el dia por default
                $res = $conexion->query("UPDATE tb_dias_laborales SET id_dia_ajuste = " . $diasajuste[0]['id'] . " WHERE `id`='$archivo[0]'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    $conexion->rollback();
                    echo json_encode(['Fallo al asignar dia de ajuste', '0']);
                    return;
                }
                if (!$res) {
                    $conexion->rollback();
                    echo json_encode(['Error al asignar dia de ajuste', '0']);
                    return;
                }
            }
            // CAMBIA EL ESTADO DEL DIA DE AJUSTE
            $res = $conexion->query("UPDATE tb_dias_laborales SET laboral = $estado WHERE `id`='$archivo[0]'");
            $aux = mysqli_error($conexion);
            if ($aux) {
                $conexion->rollback();
                echo json_encode(['Fallo al actualizar la parametrización', '0']);
                return;
            }
            if ($res) {
                $conexion->commit();
                echo json_encode(['Dia actualizado satisfactoriamente', '1']);
            } else {
                $conexion->rollback();
                echo json_encode(['Dia no actualizado satisfactoriamente', '0']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al hacer la actualizacion: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'update_dia_ajuste':
        $archivo = $_POST["archivo"];
        $conexion->autocommit(false);
        try {
            //Consultar que dia por default se puede asignar
            $banderaant = false;
            $banderades = false;
            $k = 1;
            $idaux = $archivo[1];
            $diasajuste = array();
            $idant = $archivo[1];
            $iddes = $archivo[1];
            while ($k < 4) {
                // validar rangos
                $idant = $idant - 1;
                $iddes = $iddes + 1;

                if ($idant == 0) {
                    $idant = 7;
                }

                if ($iddes == 8) {
                    $iddes = 1;
                }
                if ($banderaant == false) {

                    $res = $conexion->query("SELECT tdl.id AS id, tdl.dia AS dia FROM tb_dias_laborales tdl WHERE (tdl.id = $idant) AND tdl.laboral = 1");
                    $aux = mysqli_error($conexion);
                    if ($aux) {
                        $conexion->rollback();
                        echo json_encode(['Fallo al consultar dia de ajuste', '0']);
                        return;
                    }
                    if (!$res) {
                        $conexion->commit();
                        echo json_encode(['Error al consultar dia de ajuste', '1']);
                    }
                    //pasar los datos al array
                    while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
                        $diasajuste[] = $row;
                        $banderaant = true;
                    }
                }
                if ($banderades == false) {
                    $res = $conexion->query("SELECT tdl.id AS id, tdl.dia AS dia FROM tb_dias_laborales tdl WHERE (tdl.id = $iddes) AND tdl.laboral = 1");
                    $aux = mysqli_error($conexion);
                    if ($aux) {
                        $conexion->rollback();
                        echo json_encode(['Fallo al consultar dia de ajuste', '0']);
                        return;
                    }
                    if (!$res) {
                        $conexion->commit();
                        echo json_encode(['Error al consultar dia de ajuste', '1']);
                    }
                    //pasar los datos al array
                    while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
                        $diasajuste[] = $row;
                        $banderades = true;
                    }
                }
                $k = ($banderaant && $banderades) ? 4 : $k;
                $k++;
            }
            if ($banderaant == false && $banderades == false) {
                $conexion->rollback();
                echo json_encode(['No se encontro un dia de ajuste disponible, por lo que no puede completar esta acción', '0']);
                return;
            }

            $bandera=false;
            foreach ($diasajuste as $key => $value) {
                if ($value["id"] == $archivo[0]) {
                    $bandera=true;
                }
            }
            if ($bandera) {
                // asignar el dia por default
                $res = $conexion->query("UPDATE tb_dias_laborales SET id_dia_ajuste = " . $archivo[0] . " WHERE `id`='$archivo[1]'");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    $conexion->rollback();
                    echo json_encode(['Fallo al asignar dia de ajuste', '0']);
                    return;
                }
                if (!$res) {
                    $conexion->rollback();
                    echo json_encode(['Error al asignar dia de ajuste', '0']);
                    return;
                }
                $conexion->commit();
                echo json_encode(['Dia de ajuste actualizado satisfactoriamente', '1']);
            }else{
                $conexion->rollback();
                echo json_encode(['El dia que quiere asignar ya no es posible', '0']);
                return;
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al hacer la actualizacion: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
        //esto es para que sea automatico el numero de recibo
        case 'update_check_no_recibo':
            $config_name = $_POST['config_name'];
            $estado = $_POST['estado'];
    
            $sql = "UPDATE tb_configCre SET estado = ? WHERE config_name = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("is", $estado, $config_name);
    
            if ($stmt->execute()) {
                echo json_encode(['Successful', '1']);
            } else {
                echo "Error: " . $stmt->error;
            }
    
            $stmt->close();
            $conexion->close();
            break;
            //esto es para bloquear el campo de numero de recibo
            case 'update_auto_no_recib':
                $config_name = $_POST['config_name'];
                $estado = $_POST['estado'];
        
                $sql = "UPDATE tb_configCre SET estado = ? WHERE config_name = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("is", $estado, $config_name);
        
                if ($stmt->execute()) {
                    echo json_encode(['Successful', '1']);
                } else {
                    echo "Error: " . $stmt->error;
                }
        
                $stmt->close();
                $conexion->close();
                break;
        case 'update_check_fecha':
            $config_name = $_POST['config_name'];
            $estado = $_POST['estado'];
    
            $sql = "UPDATE tb_configCre SET estado = ? WHERE config_name = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("is", $estado, $config_name);
    
            if ($stmt->execute()) {
                echo json_encode(['Successful', '1']);
            } else {
                echo "Error: " . $stmt->error;
            }
    
            $stmt->close();
            $conexion->close();
            break;
            //esto es para bloquear el campo de numero de recibo
            case 'update_check_capital':
                $config_name = $_POST['config_name'];
                $estado = $_POST['estado'];
        
                $sql = "UPDATE tb_configCre SET estado = ? WHERE config_name = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("is", $estado, $config_name);
        
                if ($stmt->execute()) {
                    echo json_encode(['Successful', '1']);
                } else {
                    echo "Error: " . $stmt->error;
                }
        
                $stmt->close();
                $conexion->close();
                break;
            case 'update_check_interes':
                $config_name = $_POST['config_name'];
                $estado = $_POST['estado'];
        
                $sql = "UPDATE tb_configCre SET estado = ? WHERE config_name = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("is", $estado, $config_name);
        
                if ($stmt->execute()) {
                    echo json_encode(['Successful', '1']);
                } else {
                    echo "Error: " . $stmt->error;
                }
        
                $stmt->close();
                $conexion->close();
                break;
            case 'update_check_mora':
                $config_name = $_POST['config_name'];
                $estado = $_POST['estado'];
        
                $sql = "UPDATE tb_configCre SET estado = ? WHERE config_name = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("is", $estado, $config_name);
        
                if ($stmt->execute()) {
                    echo json_encode(['Successful', '1']);
                } else {
                    echo "Error: " . $stmt->error;
                }
        
                $stmt->close();
                $conexion->close();
                break;
            case 'update_check_otros':
                $config_name = $_POST['config_name'];
                $estado = $_POST['estado'];
        
                $sql = "UPDATE tb_configCre SET estado = ? WHERE config_name = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("is", $estado, $config_name);
        
                if ($stmt->execute()) {
                    echo json_encode(['Successful', '1']);
                } else {
                    echo "Error: " . $stmt->error;
                }
        
                $stmt->close();
                $conexion->close();
                break;
}

//FUNCION PARA REALIZAR VALIDACIONES
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
