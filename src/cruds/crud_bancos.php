<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$hoy2 = date("Y-m-d H:i:s");
$hoy = date("Y-m-d");
$idusuario = $_SESSION['id'];
$condi = $_POST["condi"];
switch ($condi) {
    case 'create_cuentasbancos': //CREAR REGISTRO DE POLIZA DE DIARIO EN CTB_DIARIO Y CTB_MOV
        $inputs = $_POST["inputs"];
        $archivo = $_POST["archivo"];

        //validar si todo esta lleno
        if ($inputs[0] == "" || $inputs[1] == "") {
            echo json_encode(['Debe seleccionar un banco', '0']);
            return;
        }
        if ($inputs[2] == "" || $inputs[3] == "") {
            echo json_encode(['Debe seleccionar una cuenta contable', '0']);
            return;
        }
        if ($inputs[4] == "") {
            echo json_encode(['Debe digitar una cuenta', '0']);
            return;
        }
        if ($inputs[5] == "" ) {
            echo json_encode(['Debe ingresar un saldo', '0']);
            return;
        }
        // if ($inputs[5] != "") { 
        //     echo json_encode([' saldo: ' . $inputs[5], '1']); 
        //     return; 
        // }
        //realizar la validacion que no haiga otro registro igual
        $bandera = false;
        $consulta = mysqli_query($conexion, "SELECT * FROM ctb_bancos WHERE estado=1 AND id_banco=$inputs[0] AND id_nomenclatura=$inputs[2]  AND saldo_ini=$inputs[5] AND numcuenta='$inputs[4]'");
        $aux = mysqli_error($conexion);
        if ($aux) {
            echo json_encode(['Error en la verificación de si hay un registro existente, registro no completado', '0']);
            return;
        }
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $bandera = true;
        }
        if ($bandera) {
            echo json_encode(['No se puede actualizar el registro, porque ya hay un registro igual', '0']);
            return;
        }

        //INSERCION EN LA BASE DE DATOS
        $conexion->autocommit(false);
        try {
            $res = $conexion->query("INSERT INTO `ctb_bancos`(`id_banco`, `numcuenta`, `id_nomenclatura`, `correlativo`, `dfecmod`, `estado`,`codusu`,`saldo_ini`) VALUES ('$inputs[0]','$inputs[4]','$inputs[2]',0,'$hoy2',1,'$archivo[0]','$inputs[5]')");
            if ($res) {
                $conexion->commit();
                echo json_encode(['Registro satisfactorio', '1']);
            } else {
                $conexion->rollback();
                echo json_encode(['Registro no ingresado satisfactoriamente', '0']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'update_cuentasbancos':
        $inputs = $_POST["inputs"];
        $archivo = $_POST["archivo"];

        //validar si todo esta lleno
        if ($inputs[5] == "") {
            echo json_encode(['Debe seleccionar un registro ', '0']);
            return;
        }
        if ($inputs[0] == "" || $inputs[1] == "") {
            echo json_encode(['Debe seleccionar un banco', '0']);
            return;
        }
        if ($inputs[2] == "" || $inputs[3] == "") {
            echo json_encode(['Debe seleccionar una cuenta contable', '0']);
            return;
        }
        if ($inputs[4] == "") {
            echo json_encode(['Debe digitar una cuenta', '0']);
            return;
        }
        if ($inputs[6] == "") {
            echo json_encode(['Debe digitar un saldo', '0']);
            return;   
        }
        //    if ($inputs[5] != "") { 
        //     echo json_encode([' saldo: ' . $inputs[6], '1']); 
        //     return; 
        // }

        //realizar la validacion que no haiga otro registro igual
        $bandera = false;
        $consulta = mysqli_query($conexion, "SELECT * FROM ctb_bancos WHERE estado=1 AND id_banco=$inputs[0] AND id_nomenclatura=$inputs[2] AND numcuenta='$inputs[4]' AND id!=$inputs[5]");
        $aux = mysqli_error($conexion);
        if ($aux) {
            echo json_encode(['Hubo un error en la verificación de un registro igual', '0']);
            return;
        }
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $bandera = true;
        }
        if ($bandera) {
            echo json_encode(['No se puede actualizar debido a que ya hay un registro igual', '0']);
            return;
        }

        //ACTUALIZACION EN LA BASE DE DATOS
        $conexion->autocommit(false);
        try {
            $res = $conexion->query("UPDATE `ctb_bancos` set `id_banco`= '$inputs[0]', `numcuenta`= '$inputs[4]', `id_nomenclatura`= '$inputs[2]',`saldo_ini`= '$inputs[6]', `dfecmod`= '$hoy2', `codusu`= '$archivo[0]' WHERE id=$inputs[5]");
            if ($res) {
                $conexion->commit();
                echo json_encode(['Registro actualizado satisfactoriamente', '1']);
            } else {
                $conexion->rollback();
                echo json_encode(['Registro no actualizado satisfactoriamente', '0']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'delete_cuentasbancos':
        $ideliminar = $_POST['ideliminar'];
        $conexion->autocommit(false);
        try {
            $resultado = $conexion->query("UPDATE `ctb_bancos` SET `deleted_at`='$hoy2', `estado`=0 WHERE id =" . $ideliminar);
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode(['Error: ' . $aux, '0']);
                return;
            }
            $conexion->commit();
            echo json_encode(['Cuenta de bancos eliminado satisfactoriamente', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error, en la eliminacion: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
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
                echo json_encode(['El banco no tiene cuentas creadas, por lo que no se puede completar la transacción', '0']);
                return;
            }
            echo json_encode(['Satisfactorio', '1', $data]);
        } else {
            echo json_encode(['Error en la recuperacion de cuentas de bancos, intente nuevamente', '0']);
        }
        break;
    case 'create_cheques':
        $inputs = $_POST["inputs"];
        $datospartida = $inputs[0];
        $datosdebe = $inputs[1];
        $datoshaber = $inputs[2];
        $datoscuentas = $inputs[3];
        $datosfondos = $inputs[4];
        $selects = $_POST["selects"];
        $archivo = $_POST["archivo"];

        //validar cada uno de los Campos
        //validar fechas
        if ($datospartida[0] > date("Y-m-d")) {
            echo json_encode(['La fecha de documento no puede ser mayor que la fecha de hoy', '0']);
            return;
        }
        if ($datospartida[1] > date("Y-m-d")) {
            echo json_encode(['La fecha contable no puede ser mayor que la fecha de hoy', '0']);
            return;
        }
        //validar agencia
        if ($datospartida[2] == "") {
            echo json_encode(['Para completar el registro es necesario una agencia', '0']);
            return;
        }
        //validar fondos propios
        // if ($selects[0] == "") {
        //     echo json_encode(['Debe seleccionar una fuente de fondo', '0']);
        //     return;
        // }
        //validar cantidad
        if ($datospartida[4] < 1) {
            echo json_encode(['Debe ingresar una cantidad mayor a 0.00 quetzales', '0']);
            return;
        }
        //validar tipo de cheque
        if ($selects[0] == "") {
            echo json_encode(['Debe seleccionar un tipo de cheque', '0']);
            return;
        }
        //validar numero de documento
        if ($datospartida[5] == "" || $datospartida[5] == "X") {
            echo json_encode(['Debe ingresar un numero de documento', '0']);
            return;
        }
        //validar paguese
        if ($datospartida[6] == "") {
            echo json_encode(['Debe digitar un nombre para el campo paguese a la orden de', '0']);
            return;
        }
        //validar numero en letras
        if ($datospartida[7] == "") {
            echo json_encode(['Se necesita descripcion de cantidad en quetzales', '0']);
            return;
        }
        //validar banco
        if ($selects[1] == "") {
            echo json_encode(['Debe seleccionar un banco', '0']);
            return;
        }
        //validar cuenta
        if ($selects[2] == "") {
            echo json_encode(['Debe seleccionar una cuenta de banco', '0']);
            return;
        }
        //validar numero de cheque
        if ($datospartida[8] == "") {
            echo json_encode(['Debe digitar un numero de cheque', '0']);
            return;
        }
        //validar concepto
        if ($datospartida[9] == "") {
            echo json_encode(['Debe digitar un concepto', '0']);
            return;
        }
        //validar tipo de cheque
        if ($datospartida[10] != $datospartida[11]) {
            echo json_encode(['Sumatoria de debe no es igual a la del haber', '0']);
            return;
        }
        if ($datospartida[10] == 0 || $datospartida[11] == 0) {
            echo json_encode(['Sumatoria es igual a 0, Ingrese montos', '0']);
            return;
        }
        if ($datospartida[10] < 0 || $datospartida[11] < 0) {
            echo json_encode(['Las sumatorias del debe y del haber no deben ser negativas', '0']);
            return;
        }
        //hasta aca todo funciona

        //inicio transaccion
        $conexion->autocommit(false);
        try {
            $numpartida = getnumcom($archivo[0], $conexion); //Obtener numero de partida
            //INSERCCION EN EL LIBRO DE DIARIO
            $glosa = strtoupper($datospartida[9]);
            $res = $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$numpartida',7,1,'$datospartida[5]','$glosa','$datospartida[0]','$datospartida[1]','CHEQUES',$archivo[0],'$hoy2',1)");
            $id_ctb_diario = get_id_insertado($conexion); //obtener el id insertado en diario        

            if (!$res) {
                echo json_encode(['Transaccion no completada con éxito', '0']);
                $conexion->rollback();
                return;
            }
            //INSERCCION EN MOVIMIENTOS CONTABLES
            $i = 0;
            $bandera_insercion = false;
            while ($i < count($datoscuentas)) {
                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) 
                VALUES ($id_ctb_diario,'$datosfondos[$i]','$datoscuentas[$i]', $datosdebe[$i],$datoshaber[$i])");
                if (!$res) {
                    $bandera_insercion = true;
                }
                $i++;
            }
            //validar si todas fueron insertadas
            if ($bandera_insercion) {
                echo json_encode(['Transaccion no completada con éxito', '0']);
                $conexion->rollback();
                return;
            }
            //INSERCCION EN CUENTAS DE CHEQUES
            $paguese = strtoupper($datospartida[6]);
            $res = $conexion->query("INSERT INTO `ctb_chq`(`id_ctb_diario`,`id_cuenta_banco`,`numchq`,`nomchq`,`monchq`,`emitido`,`modocheque`) 
            VALUES ($id_ctb_diario,$selects[2],'$datospartida[8]', '$paguese','$datospartida[4]','0','$selects[0]')");
            if (!$res) {
                echo json_encode(['Transaccion no completada con éxito', '0']);
                $conexion->rollback();
                return;
            }

            $conexion->commit();
            echo json_encode(['Correcto,  Emision de cheque generada con No.: ' . $numpartida, '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'update_cheques':
        $inputs = $_POST["inputs"];
        $datospartida = $inputs[0];
        $datosdebe = $inputs[1];
        $datoshaber = $inputs[2];
        $datoscuentas = $inputs[3];
        $datosfondos = $inputs[4];
        $selects = $_POST["selects"];
        $archivo = $_POST["archivo"];

        //validar cada uno de los Campos
        if ($archivo[1] == "") {
            echo json_encode(['No ha seleccionado un identificador de registro a editar', '0']);
            return;
        }
        //validar fechas
        // if ($datospartida[0] > date("Y-m-d")) {
        //     echo json_encode(['La fecha de documento no puede ser mayor que la fecha de hoy', '0']);
        //     return;
        // }
        // if ($datospartida[1] > date("Y-m-d")) {
        //     echo json_encode(['La fecha contable no puede ser mayor que la fecha de hoy', '0']);
        //     return;
        // }
        //validar agencia
        if ($datospartida[2] == "") {
            echo json_encode(['Para completar el registro es necesario una agencia', '0']);
            return;
        }
        //validar fondos propios
        // if ($selects[0] == "") {
        //     echo json_encode(['Debe seleccionar una fuente de fondo', '0']);
        //     return;
        // }
        //validar cantidad
        if ($datospartida[4] < 1) {
            echo json_encode(['Debe ingresar una cantidad mayor a 0.00 quetzales', '0']);
            return;
        }
        //validar tipo de cheque
        if ($selects[0] == "") {
            echo json_encode(['Debe seleccionar un tipo de cheque', '0']);
            return;
        }
        //validar numero de documento
        if ($datospartida[5] == "" || $datospartida[5] == "X") {
            echo json_encode(['Debe ingresar un numero de documento', '0']);
            return;
        }
        //validar paguese
        if ($datospartida[6] == "") {
            echo json_encode(['Debe digitar un nombre para el campo paguese a la orden de', '0']);
            return;
        }
        //validar numero en letras
        if ($datospartida[7] == "") {
            echo json_encode(['Se necesita descripcion de cantidad en quetzales', '0']);
            return;
        }
        //validar banco
        if ($selects[1] == "") {
            echo json_encode(['Debe seleccionar un banco', '0']);
            return;
        }
        //validar cuenta
        if ($selects[2] == "") {
            echo json_encode(['Debe seleccionar una cuenta de banco', '0']);
            return;
        }
        //validar numero de cheque
        if ($datospartida[8] == "") {
            echo json_encode(['Debe digitar un numero de cheque', '0']);
            return;
        }
        //validar concepto
        if ($datospartida[9] == "") {
            echo json_encode(['Debe digitar un concepto', '0']);
            return;
        }
        //validar tipo de cheque
        if ($datospartida[10] != $datospartida[11]) {
            echo json_encode(['Sumatoria de debe no es igual a la del haber', '0']);
            return;
        }
        if ($datospartida[10] == 0 || $datospartida[11] == 0) {
            echo json_encode(['Sumatoria es igual a 0, Ingrese montos', '0']);
            return;
        }
        if ($datospartida[10] < 0 || $datospartida[11] < 0) {
            echo json_encode(['Las sumatorias del debe y del haber no deben ser negativas', '0']);
            return;
        }
        //inicio transaccion
        $conexion->autocommit(false);
        try {
            //INSERCCION EN EL LIBRO DE DIARIO
            $glosa = strtoupper($datospartida[9]);
            // $res = $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$numpartida',7,1,'$datospartida[5]','$glosa','$datospartida[0]','$datospartida[1]','CHEQUES',$archivo[0],'$hoy2',1)");
            $res = $conexion->query("UPDATE `ctb_diario` SET `glosa`='$datospartida[9]',`fecdoc`='$datospartida[0]',`feccnt`='$datospartida[1]',`numdoc`='$datospartida[5]',`id_tb_usu`='$archivo[0]',`fecmod`='$hoy2' WHERE id =" . $archivo[1]);

            if (!$res) {
                echo json_encode(['Transaccion no completada con éxito', '0']);
                $conexion->rollback();
                return;
            }
            //ELIMINAR LOS DATOS ANTERIORE PARA LUEGO REESCRIBIRLOS
            $res = $conexion->query("DELETE FROM ctb_mov WHERE id_ctb_diario =" . $archivo[1]);
            if (!$res) {
                echo json_encode(['Transaccion no completada con éxito', '0']);
                $conexion->rollback();
                return;
            }
            //INSERCCION EN MOVIMIENTOS CONTABLES
            $i = 0;
            $bandera_insercion = false;
            while ($i < count($datoscuentas)) {
                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) 
                VALUES ($archivo[1],'$datosfondos[$i]','$datoscuentas[$i]', $datosdebe[$i],$datoshaber[$i])");
                if (!$res) {
                    $bandera_insercion = true;
                }
                $i++;
            }
            //validar si todas fueron insertadas
            if ($bandera_insercion) {
                echo json_encode(['Transaccion no completada con éxito', '0']);
                $conexion->rollback();
                return;
            }
            //INSERCCION EN CUENTAS DE CHEQUES
            $paguese = strtoupper($datospartida[6]);
            // $res = $conexion->query("INSERT INTO `ctb_chq`(`id_ctb_diario`,`id_cuenta_banco`,`numchq`,`nomchq`,`monchq`,`emitido`) 
            // VALUES ($id_ctb_diario,$selects[3],'$datospartida[8]', '$paguese','$datospartida[4]','0')");
            $res = $conexion->query("UPDATE `ctb_chq` SET `id_cuenta_banco`='$selects[2]',`numchq`='$datospartida[8]',`nomchq`='$paguese',`monchq`='$datospartida[4]', `modocheque`='$selects[0]' WHERE id_ctb_diario =" . $archivo[1]);
            if (!$res) {
                echo json_encode(['Transaccion no completada con éxito', '0']);
                $conexion->rollback();
                return;
            }

            $conexion->commit();
            echo json_encode(['Correcto,  Emision de cheque actualizada con No.: ' . $archivo[1], '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'delete_cheques':
        $id = $_POST["ideliminar"];
        $conexion->autocommit(false);
        try {
            $res = $conexion->query("UPDATE `ctb_diario` SET `fecmod`='$hoy2',`estado`=0 WHERE id =" . $id);
            if (!$res) {
                echo json_encode(['Transaccion no completada con éxito', '0']);
                $conexion->rollback();
                return;
            }
            $conexion->commit();
            echo json_encode(['Correcto,  Partida de diario Eliminada: ', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al hacer la eliminacion: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'listar_cheques':
        $id_agencia = $_POST['id_agencia'];
        $consulta = mysqli_query($conexion, "SELECT dia.id,dia.numcom,dia.feccnt,SUM(mov.debe) debe,SUM(mov.haber) haber, ch.monchq AS moncheque, ch.emitido AS estado, ch.numchq FROM ctb_mov AS mov 
        INNER JOIN ctb_diario dia ON mov.id_ctb_diario = dia.id
        INNER JOIN ctb_chq ch ON dia.id=ch.id_ctb_diario
        INNER JOIN tb_usuario tu ON dia.id_tb_usu=tu.id_usu
        INNER JOIN tb_agencia ta ON tu.id_agencia=ta.id_agencia
        WHERE dia.estado=1 AND ta.id_agencia='$id_agencia'
        GROUP BY mov.id_ctb_diario");
        //se cargan los datos de las beneficiarios a un array
        $array_datos = array();
        $i = 0;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $imp = '';
            if ($fila["estado"] == 1) {
                $imp = '<span class="badge bg-success">Sí</span>';
            } else {
                if ($fila["numchq"] == '' || $fila["numchq"] == null) {
                    $imp = '<span class="badge bg-danger">No</span>';
                } else {
                    $imp = '<span class="badge bg-warning text-dark">No</span>';
                }
            }
            $array_datos[] = array(
                "0" => $fila["numcom"],
                "1" => $fila["feccnt"],
                "2" => $fila["debe"],
                "3" => $fila["moncheque"],
                "4" => $imp,
                "5" => '<td> <button class="btn btn-outline-success btn-sm" title="Ver Cheque" onclick="printdiv2(`#cuadro`, ' . $fila["id"] . ')"><i class="fa-sharp fa-solid fa-eye"></i></i></button></td>'
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
    case 'cheque_automatico':
        $id_cuenta_banco = $_POST['id_cuenta_banco'];
        $id_reg_cheque = $_POST['id_reg_cheque'];
        $numcheque = 'NA';
        //verificar si ya tiene numero de Cheque
        if ($id_reg_cheque != 0) {
            $datos = mysqli_query($conexion, "SELECT ch.numchq AS numerocheque FROM ctb_chq ch WHERE ch.id='" . $id_reg_cheque . "'");
            while ($row = mysqli_fetch_array($datos)) {
                $numcheque = $row["numerocheque"];
            }
        }
        //crear el siguiente numero
        if ($numcheque == '' || $numcheque == null || $numcheque == 'NA') {
            $datos = mysqli_query($conexion, "SELECT CAST((MAX(ch.numchq)+1) AS CHAR) AS numerocheque FROM ctb_chq ch WHERE ch.id_cuenta_banco='" . $id_cuenta_banco . "'");
            while ($row = mysqli_fetch_array($datos)) {
                $numcheque = $row["numerocheque"];
            }
        }
        echo json_encode(['Numero de cheque automatico', '1', $numcheque]);
        break;
    case 'create_depositos_bancos':
        $inputs = $_POST["inputs"];
        $datospartida = $inputs[0];
        $datosdebe = $inputs[1];
        $datoshaber = $inputs[2];
        $datoscuentas = $inputs[3];
        $datosfondos = $inputs[4];
        $archivo = $_POST["archivo"];
        $idusuario = $_SESSION['id'];

        //validar cada uno de los Campos
        //datainputs = getinputsval(['datedoc', 'datecont', 'codofi', 'codofi2', 'numdoc', 'glosa', 'totdebe', 'tothaber','idtipo_poliza'])
        /* generico([datainputs, datainputsd, datainputsh, datacuentas,datafondos], [], [], condio, idr, [idr]); */
        //validar fechas

        if ($datospartida[0] > date("Y-m-d")) {
            echo json_encode(['La fecha de documento no puede ser mayor que la fecha de hoy', '0']);
            return;
        }
        if ($datospartida[1] > date("Y-m-d")) {
            echo json_encode(['La fecha contable no puede ser mayor que la fecha de hoy', '0']);
            return;
        }
        //validar agencia
        if ($datospartida[2] == "") {
            echo json_encode(['Para completar el registro es necesario una agencia', '0']);
            return;
        }
        //validar numero de documento
        if ($datospartida[4] == "") {
            echo json_encode(['Debe ingresar un numero de documento', '0']);
            return;
        }

        //validar concepto
        if ($datospartida[5] == "") {
            echo json_encode(['Debe digitar un concepto', '0']);
            return;
        }
        //validar montos debe y haber
        if ($datospartida[6] != $datospartida[7]) {
            echo json_encode(['Sumatoria de debe no es igual a la del haber', '0']);
            return;
        }
        if ($datospartida[6] == 0 || $datospartida[7] == 0) {
            echo json_encode(['Sumatoria es igual a 0, Ingrese montos', '0']);
            return;
        }
        if ($datospartida[6] < 0 || $datospartida[7] < 0) {
            echo json_encode(['Las sumatorias del debe y del haber no deben ser negativas', '0']);
            return;
        }
        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            +++++++ COMPROBACION DE QUE EXISTA AL MENOS UNA CUENTA DE BANCOS +++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $cuentas_bancos[] = [];
        $consulta2 = mysqli_query($conexion, "SELECT ctb.id id_cuenta, ctb.ccodcta, ctb.cdescrip,ban.numcuenta FROM ctb_nomenclatura ctb 
                    INNER JOIN ctb_bancos ban ON ban.id_nomenclatura=ctb.id WHERE ban.estado=1 AND ctb.estado=1");
        $i = 0;
        while ($filas = mysqli_fetch_array($consulta2, MYSQLI_ASSOC)) {
            $cuentas_bancos[$i] = $filas;
            $i++;
        }
        $contador = 0;
        if ($i != 0) {
            $k = 0;
            while ($k < count($cuentas_bancos)) {
                if (in_array($cuentas_bancos[$k]['id_cuenta'], $datoscuentas)) {
                    $contador++;
                }
                $k++;
            }
        }
        if ($contador == 0) {
            echo json_encode(['Falta al menos una cuenta bancaria', '0']);
            return;
        }

        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            +++++++++++++++++++++++++++++++ INICIO DE TRANSACCIONES EN LA BD +++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $conexion->autocommit(false);
        try {
            $numpartida = getnumcom($idusuario, $conexion); //Obtener numero de partida
            //INSERCION EN EL LIBRO DE DIARIO
            $glosa = strtoupper($datospartida[5]);
            $res = $conexion->query("INSERT INTO `ctb_diario`(`numcom`,`id_ctb_tipopoliza`,`id_tb_moneda`,`numdoc`,`glosa`,`fecdoc`,`feccnt`,`cod_aux`,`id_tb_usu`,`fecmod`,`estado`) VALUES ('$numpartida',$datospartida[8],1,'$datospartida[4]','$glosa','$datospartida[0]','$datospartida[1]','DEPOSITOS_BANCOS',$idusuario,'$hoy2',1)");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode(['ERROR 1: ' . $aux, '0']);
                $conexion->rollback();
                return;
            }
            $id_ctb_diario = get_id_insertado($conexion); //obtener el id insertado en diario        
            if (!$res) {
                echo json_encode(['Transaccion no completada con éxito', '0']);
                $conexion->rollback();
                return;
            }
            //INSERCION EN MOVIMIENTOS CONTABLES
            $i = 0;
            $bandera_insercion = false;
            while ($i < count($datoscuentas)) {
                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) 
                 VALUES ($id_ctb_diario,$datosfondos[$i],'$datoscuentas[$i]', $datosdebe[$i],$datoshaber[$i])");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['ERROR 2: ' . $aux, '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    $bandera_insercion = true;
                }
                $i++;
            }
            //validar si todas fueron insertadas
            if ($bandera_insercion) {
                echo json_encode(['Transaccion no completada con éxito', '0']);
                $conexion->rollback();
                return;
            }

            $conexion->commit();
            echo json_encode(['Correcto,  Deposito registrado con Partida No.: ' . $numpartida, '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'update_depositos_bancos':
        $inputs = $_POST["inputs"];
        $datospartida = $inputs[0];
        $datosdebe = $inputs[1];
        $datoshaber = $inputs[2];
        $datoscuentas = $inputs[3];
        $datosfondos = $inputs[4];
        $archivo = $_POST["archivo"];
        $idusuario = $_SESSION['id'];

        //validar cada uno de los Campos
        //datainputs = getinputsval(['datedoc', 'datecont', 'codofi', 'codofi2', 'numdoc', 'glosa', 'totdebe', 'tothaber','idtipo_poliza'])
        /* generico([datainputs, datainputsd, datainputsh, datacuentas,datafondos], [], [], condio, idr, [idr]); */
        //validar fechas

        if ($datospartida[0] > date("Y-m-d")) {
            echo json_encode(['La fecha de documento no puede ser mayor que la fecha de hoy', '0']);
            return;
        }
        if ($datospartida[1] > date("Y-m-d")) {
            echo json_encode(['La fecha contable no puede ser mayor que la fecha de hoy', '0']);
            return;
        }
        //validar agencia
        if ($datospartida[2] == "") {
            echo json_encode(['Para completar el registro es necesario una agencia', '0']);
            return;
        }
        //validar numero de documento
        if ($datospartida[4] == "") {
            echo json_encode(['Debe ingresar un numero de documento', '0']);
            return;
        }

        //validar concepto
        if ($datospartida[5] == "") {
            echo json_encode(['Debe digitar un concepto', '0']);
            return;
        }
        //validar montos debe y haber
        if ($datospartida[6] != $datospartida[7]) {
            echo json_encode(['Sumatoria de debe no es igual a la del haber', '0']);
            return;
        }
        if ($datospartida[6] == 0 || $datospartida[7] == 0) {
            echo json_encode(['Sumatoria es igual a 0, Ingrese montos', '0']);
            return;
        }
        if ($datospartida[6] < 0 || $datospartida[7] < 0) {
            echo json_encode(['Las sumatorias del debe y del haber no deben ser negativas', '0']);
            return;
        }
        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            +++++++ COMPROBACION DE QUE EXISTA AL MENOS UNA CUENTA DE BANCOS +++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $cuentas_bancos[] = [];
        $consulta2 = mysqli_query($conexion, "SELECT ctb.id id_cuenta, ctb.ccodcta, ctb.cdescrip,ban.numcuenta FROM ctb_nomenclatura ctb 
                        INNER JOIN ctb_bancos ban ON ban.id_nomenclatura=ctb.id WHERE ban.estado=1 AND ctb.estado=1");
        $i = 0;
        while ($filas = mysqli_fetch_array($consulta2, MYSQLI_ASSOC)) {
            $cuentas_bancos[$i] = $filas;
            $i++;
        }
        $contador = 0;
        if ($i != 0) {
            $k = 0;
            while ($k < count($cuentas_bancos)) {
                if (in_array($cuentas_bancos[$k]['id_cuenta'], $datoscuentas)) {
                    $contador++;
                }
                $k++;
            }
        }
        if ($contador == 0) {
            echo json_encode(['Falta al menos una cuenta bancaria', '0']);
            return;
        }
        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            +++++++++++++++++++++++++++++++ INICIO DE TRANSACCIONES EN LA BD +++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $conexion->autocommit(false);
        try {
            //ACTUALIZACION DEL DIARIO
            $glosa = strtoupper($datospartida[5]);
            $res = $conexion->query("UPDATE `ctb_diario` SET `glosa`='$glosa',`fecdoc`='$datospartida[0]',`feccnt`='$datospartida[1]',`numdoc`='$datospartida[4]',`id_tb_usu`='$idusuario',`fecmod`='$hoy2',`id_ctb_tipopoliza`='$datospartida[8]' WHERE id =" . $archivo[0]);
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode(['ERROR 1: ' . $aux, '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['Transaccion no completada con éxito', '0']);
                $conexion->rollback();
                return;
            }

            //ELIMINAR LOS DATOS ANTERIORE PARA LUEGO REESCRIBIRLOS
            $res = $conexion->query("DELETE FROM ctb_mov WHERE id_ctb_diario =" . $archivo[0]);
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode(['ERROR 2: ' . $aux, '0']);
                $conexion->rollback();
                return;
            }
            if (!$res) {
                echo json_encode(['Transaccion no completada con éxito', '0']);
                $conexion->rollback();
                return;
            }
            //INSERCION EN MOVIMIENTOS CONTABLES
            $i = 0;
            $bandera_insercion = false;
            while ($i < count($datoscuentas)) {
                $res = $conexion->query("INSERT INTO `ctb_mov`(`id_ctb_diario`,`id_fuente_fondo`,`id_ctb_nomenclatura`,`debe`,`haber`) 
                    VALUES ($archivo[0],$datosfondos[$i],'$datoscuentas[$i]', $datosdebe[$i],$datoshaber[$i])");
                $aux = mysqli_error($conexion);
                if ($aux) {
                    echo json_encode(['ERROR 3: ' . $aux, '0']);
                    $conexion->rollback();
                    return;
                }
                if (!$res) {
                    $bandera_insercion = true;
                }
                $i++;
            }
            //validar si todas fueron insertadas
            if ($bandera_insercion) {
                echo json_encode(['Transaccion no completada con éxito', '0']);
                $conexion->rollback();
                return;
            }
            //-----------
            $conexion->commit();
            echo json_encode(['Correcto, información de Depósito actualizada', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'delete_depositos_bancos':
        $id = $_POST["ideliminar"];
        //COMPROBAR SI EL MES CONTABLE ESTA ABIERTO 
        $consulta = mysqli_query($conexion, "SELECT feccnt FROM ctb_diario WHERE id =" . $id);
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $fechapoliza = $fila["feccnt"];
        }

        $cierre = comprobar_cierre($idusuario, $fechapoliza, $conexion);
        if ($cierre[0] == 0) {
            echo json_encode([$cierre[1], '0']);
            return;
        }
        $conexion->autocommit(false);
        try {
            $conexion->query("UPDATE `ctb_diario` SET `deleted_at`='$hoy2',`deleted_by`=$idusuario,`estado`=0 WHERE id =" . $id);
            $conexion->commit();
            echo json_encode(['Correcto,  Depósito a bancos Eliminado: ', '1']);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al hacer la eliminacion: ' . $e->getMessage(), '0']);
        }
        mysqli_close($conexion);
        break;
    case 'movimientos_banco':
        $param = $_POST['datas'];
        $idcuenta = $param[0];
        $fecha_inicio = $param[1];
        $fecha_fin = $param[2];

        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++ CONSULTA DE TODOS LOS MOVIMIENTOS DE LA CUENTA EN LA FECHA INDICADA +++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $strquery = "SELECT cmov.* from ctb_diario_mov cmov INNER JOIN ctb_bancos ban ON ban.id_nomenclatura=cmov.id_ctb_nomenclatura 
                        WHERE ban.estado=1 AND id_tipopol != 9 AND cmov.estado=1 AND id_ctb_nomenclatura=" . $idcuenta . " 
                        AND feccnt BETWEEN '" . $fecha_inicio . "' AND '" . $fecha_fin . "' ORDER BY id_ctb_nomenclatura,feccnt";

        $consulta = mysqli_query($conexion, $strquery);
        $array_datos = array();
        $i = 0;
        $contador = 1;
        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
            $nomcheque = $fila["nombrecheque"];
            $tipopol = $fila["id_tipopol"];
            $disabled = ($nomcheque != '-' || $tipopol == 7) ? '' : ' disabled';
            $switch = '<div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" value="' .  $fila["id_ctb_diario"] . '" ' . $disabled . '></div>';
            $array_datos[] = array(
                "0" => $switch,
                "1" => date("d-m-Y", strtotime($fila["feccnt"])),
                // "2" => $fila["numcom"],
                "2" => $fila["glosa"],
                "3" => $fila["debe"],
                "4" => $fila["haber"],
                "5" => $fila["numdoc"],
                "6" => $nomcheque
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
    case 'create_banco':
        $inputs = $_POST["inputs"];
        $archivo = $_POST["archivo"];

        $validar = validar_campos_plus([
            [$inputs[0], "", 'Debe ingresar un nombre de banco', 1],
            [$inputs[1], "", 'Debe ingresar la abreviatura del banco', 1],
        ]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }

        //Validar si ya existe un registro igual que el nombre
        $stmt = $conexion->prepare("SELECT LOWER(tb.nombre) AS resultado FROM tb_bancos tb WHERE tb.nombre = ?");
        if (!$stmt) {
            $error = $conexion->error;
            echo json_encode(['Error preparando consulta 1: ' . $error, '0']);
            return;
        }
        $aux = (mb_strtolower($inputs[0], 'utf-8'));
        $stmt->bind_param("s", $aux);
        if (!$stmt->execute()) {
            $errorMsg = $stmt->error;
            echo json_encode(["Fallo al ejecutar la consulta 1: $errorMsg", '0']);
            return;
        }
        $resultado = $stmt->get_result();
        $numFilas = $resultado->num_rows;
        if ($numFilas > 0) {
            echo json_encode(["No se puede registrar debibo a que ya existe un banco con este nombre", '0']);
            return;
        }

        //PREPARACION DE ARRAY
        $data = array(
            'nombre' => $inputs[0],
            'abreviatura' => $inputs[1],
            'estado' => '1',
        );

        $conexion->autocommit(FALSE);
        try {
            // //INSERCION DE CLIENTE NATURAL
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $stmt = $conexion->prepare("INSERT INTO tb_bancos ($columns) VALUES ($placeholders)");
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
            echo json_encode(["Banco ingresado correctamente: ", '1']);
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
    case 'update_banco':
        $inputs = $_POST["inputs"];
        $archivo = $_POST["archivo"];

        $validar = validar_campos_plus([
            [$inputs[2], "", 'Debe seleccionar un banco a actualizar', 1],
            [$inputs[0], "", 'Debe ingresar un nombre de banco', 1],
            [$inputs[1], "", 'Debe ingresar la abreviatura del banco', 1],
        ]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }

        //Validar si ya existe un registro igual que el nombre
        $stmt = $conexion->prepare("SELECT LOWER(tb.nombre) AS resultado FROM tb_bancos tb WHERE tb.nombre = ?");
        if (!$stmt) {
            $error = $conexion->error;
            echo json_encode(['Error preparando consulta 1: ' . $error, '0']);
            return;
        }
        $aux = (mb_strtolower($inputs[0], 'utf-8'));
        $stmt->bind_param("s", $aux);
        if (!$stmt->execute()) {
            echo json_encode(["Fallo al ejecutar la consulta 1", '0']);
            return;
        }
        $resultado = $stmt->get_result();
        $numFilas = $resultado->num_rows;
        if ($numFilas > 0) {
            echo json_encode(["No se puede actualizar debibo a que ya existe un registro de un banco con el mismo nombre", '0']);
            return;
        }

        //PREPARACION DE ARRAY
        $data = array(
            'nombre' => $inputs[0],
            'abreviatura' => $inputs[1],
            'estado' => '1',
        );

        $id = $inputs[2];
        $conexion->autocommit(FALSE);
        try {
            // Columnas a actualizar
            $setCols = [];
            foreach ($data as $key => $value) {
                $setCols[] = "$key = ?";
            }
            $setStr = implode(', ', $setCols);
            $stmt = $conexion->prepare("UPDATE tb_bancos SET $setStr WHERE id = ?");
            // Obtener los valores del array de datos
            $values = array_values($data);
            // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
            $values[] = $id; // Agregar ID al final
            $types = str_repeat('s', count($values));
            // Vincular los parámetros
            $stmt->bind_param($types, ...$values);
            if (!$stmt->execute()) {
                $errorMsg = $stmt->error;
                $conexion->rollback();
                echo json_encode(["Error al ejecutar consulta: $errorMsg", '0']);
                return;
            }

            //Realizar el commit especifico
            $conexion->commit();
            echo json_encode(["Banco actualizado correctamente", '1']);
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
    case 'delete_banco': {
            $archivo = $_POST["ideliminar"];
            $validar = validar_campos_plus([
                [$archivo, "", 'Debe seleccionar un registro a eliminar', 1],
            ]);
            if ($validar[2]) {
                echo json_encode([$validar[0], $validar[1]]);
                return;
            }

            //validar si se puede eliminar o no
            $stmt = $conexion->prepare("SELECT * FROM ctb_bancos WHERE id_banco = ?");
            if (!$stmt) {
                $error = $conexion->error;
                echo json_encode(['Error preparando consulta: ' . $error, '0']);
                return;
            }
            $id = $archivo;
            $stmt->bind_param('i', $id);
            if (!$stmt->execute()) {
                echo json_encode(["Fallo al ejecutar la consulta", '0']);
                return;
            }
            $resultado = $stmt->get_result();
            $numFilas = $resultado->num_rows;

            if ($numFilas > 0) {
                echo json_encode(["No se puede eliminar porque tiene al menos un numero de cuenta registrado", '0']);
                return;
            }

            //PREPARACION DE ARRAY
            $data = array(
                'estado' => '0',
            );

            $id = $archivo;
            $conexion->autocommit(FALSE);
            try {
                // Columnas a actualizar
                $setCols = [];
                foreach ($data as $key => $value) {
                    $setCols[] = "$key = ?";
                }
                $setStr = implode(', ', $setCols);
                $stmt = $conexion->prepare("UPDATE tb_bancos SET $setStr WHERE id = ?");
                // Obtener los valores del array de datos
                $values = array_values($data);
                // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
                $values[] = $id; // Agregar ID al final
                $types = str_repeat('s', count($values));
                // Vincular los parámetros
                $stmt->bind_param($types, ...$values);
                if ($stmt->execute()) {
                    $conexion->commit();
                    echo json_encode(["Banco eliminado correctamente", '1']);
                } else {
                    $errorMsg = $stmt->error;
                    $conexion->rollback();
                    echo json_encode(["Error al ejecutar consulta: $errorMsg", '0']);
                }
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(["Error: " . $e->getMessage(), '0']);
            } finally {
                if ($stmt !== false) {
                    $stmt->close();
                }
                $conexion->close();
            }
        }
        break;
    case 'anular_cheques':
        $archivo = $_POST["archivo"];
        $validar = validar_campos_plus([
            [$archivo[0], "", 'No se ha detectado el usuario, refresque la página o bien cierre e inicie sesión nuevamente', 1],
            [$archivo[1], "", 'No se ha detectado un identificador de cheque', 1],
        ]);
        if ($validar[2]) {
            echo json_encode([$validar[0], $validar[1]]);
            return;
        }

        $mensaje_error = "";
        try {
            $stmt = false;
            $stmt12 = false;
            $stmt13 = false;
            $stmt14 = false;
            $stmt = $conexion->prepare("SELECT cd.glosa AS glosa FROM ctb_diario cd WHERE cd.id = ?");
            if (!$stmt) {
                throw new ErrorException("Error en la consulta 1: " . $conexion->error);
            }
            $aux = $archivo[1];
            $stmt->bind_param("i", $aux); //El arroba omite el warning de php

            if (!$stmt->execute()) {
                throw new ErrorException("Error al consultar 1: " . $stmt->error);
            }
            $result = $stmt->get_result();
            $numFilas = $result->num_rows;
            if ($numFilas < 1) {
                throw new ErrorException("No se encontraron datos 1");
            }
            $resultado = $result->fetch_assoc();
            $datos = $resultado['glosa'];
            $datos = $datos . " (CHEQUE ANULADO)";

            //INICIO DE TRANSACCIONES
            $conexion->autocommit(FALSE);
            //Actualizar ctb_mov
            $stmt2 = $conexion->prepare("UPDATE ctb_mov SET debe=0, haber=0 WHERE id_ctb_diario=?");
            if (!$stmt2) {
                throw new ErrorException("Error en la consulta 2: " . $conexion->error);
            }
            $aux = $archivo[1];
            $stmt2->bind_param("i", $aux); //El arroba omite el warning de php
            if (!$stmt2->execute()) {
                throw new ErrorException("Error al consultar 2: " . $stmt2->error);
            }

            $stmt3 = $conexion->prepare("UPDATE ctb_chq SET monchq='0', emitido=2 WHERE id_ctb_diario=?");
            if (!$stmt3) {
                throw new ErrorException("Error en la consulta 3: " . $conexion->error);
            }
            $aux = $archivo[1];
            $stmt3->bind_param("i", $aux); //El arroba omite el warning de php
            if (!$stmt3->execute()) {
                throw new ErrorException("Error al consultar 3: " . $stmt3->error);
            }

            $stmt4 = $conexion->prepare("UPDATE ctb_diario SET glosa=? WHERE id = ?");
            if (!$stmt4) {
                throw new ErrorException("Error en la consulta 4: " . $conexion->error);
            }
            $aux = $archivo[1];
            $stmt4->bind_param("si", $datos, $aux); //El arroba omite el warning de php
            if (!$stmt4->execute()) {
                throw new ErrorException("Error al consultar 4: " . $stmt4->error);
            }
            $conexion->commit();
            echo json_encode(["Cheque anulado correctamente", '1']);
        } catch (\ErrorException $e) {
            //Captura el error
            $mensaje_error = $e->getMessage();
            $conexion->rollback();
            echo json_encode([$mensaje_error, '0']);
        } finally {
            if ($stmt !== false) {
                $stmt->close();
            }
            if ($stmt2 !== false) {
                $stmt2->close();
            }
            if ($stmt3 !== false) {
                $stmt3->close();
            }
            if ($stmt4 !== false) {
                $stmt4->close();
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
        }
    }
    return ["", '0', false];
}
