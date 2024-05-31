<?php

use PhpOffice\PhpSpreadsheet\Cell\DataType;

session_start();
include '../../includes/BD_con/db_con.php';
include '../../includes/Config/database.php';
$database = new Database($db_host, $db_name, $db_user, $db_password, $db_name_general);
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];
mysqli_set_charset($conexion, 'utf8');
$hoy2 = date("Y-m-d H:i:s");
$hoy = date("Y-m-d");

$condi = $_POST["condi"];

switch ($condi) {
    case 'acceso':
        $direccion_ip = $_SERVER['REMOTE_ADDR'];
        $agente_usuario = $_SERVER['HTTP_USER_AGENT'];
        $hostname = gethostbyaddr($direccion_ip);
        if ($hostname === false || $hostname === $direccion_ip) {
            $hostname = "No se pudo obtener el nombre de host";
        }
        $user = $_POST["usuario"];
        $pass = $_POST["password"];
        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            +++++++++++++++++++++++  VERIFICACION DE LOS INTENTOS PERMITIDOS +++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        if (isset($_SESSION['intentos'])) {
            if ($_SESSION['intentos'] >= 5) {
                //BLOQUEAR EL ACCESO A LA INSTITUCION
                $datos = array(
                    "id_tb_usuario" => 0,
                    "fecha_inicio" => $hoy2,
                    "fecha_fin" => "0000-00-00",
                    "ip_direccion" => $direccion_ip,
                    "hostname" => $hostname,
                    "user_agent" => $agente_usuario,
                    "token" => "",
                    "status" => 0,
                    "info_adicional" => utf8_decode('El numero de intentos superó el limite establecido, USER: ' . $user . '  PASS: ' . $pass)
                );
                $database->openConnection();
                $database->insert('tb_registro_login', $datos);
                $database->closeConnection();

                echo json_encode([false, 'El numero de intentos superó el limite establecido']);
                unset($_SESSION['intentos']);
                return;
            }
        }

        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            +++++++++++++  VERIFICACION DE CREDENCIALES DE USUARIO INGRESADOS ++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */

        $passwordencritado = encriptar_desencriptar($key1, $key2, 'encrypt', $pass);
        $usuario_query = "SELECT * FROM `tb_usuario` tbu INNER JOIN `tb_agencia` tbg ON tbu.id_agencia=tbg.id_agencia WHERE `usu`=? AND `pass`=?";

        $usuario_stmt = $conexion->prepare($usuario_query);
        $usuario_stmt->bind_param("ss", $user, $passwordencritado);
        $usuario_stmt->execute();
        $usuario_result = $usuario_stmt->get_result();

        if ($usuario_result->num_rows < 1) {
            $_SESSION['intentos'] = isset($_SESSION['intentos']) ? $_SESSION['intentos'] + 1 : 1;
            mysqli_close($conexion);
            http_response_code(200);
            echo json_encode([false, 'Usuario o contraseña incorrecto']);
            return;
        }

        while ($fila = $usuario_result->fetch_assoc()) {
            // Obtener datos del usuario
            $id         = utf8_encode($fila['id_usu']);
            $nombre     = utf8_encode($fila['nombre']);
            $apellido   = utf8_encode($fila['apellido']);
            $dpi        = utf8_encode($fila['dpi']);
            $usu        = utf8_encode($fila['usu']);
            $estado     = utf8_encode($fila['estado']);
            $puesto     = utf8_encode($fila['puesto']);
            $id_agencia = utf8_encode($fila['id_agencia']);
            $agencia    = utf8_encode($fila['cod_agenc']);
            $nomagencia = utf8_encode($fila['nom_agencia']);
            $exp_date   = utf8_encode($fila['exp_date']);
        }
        $NIGGA = ValidaDatePass($exp_date, $id, $puesto, $conexion);

        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            ++++++++++++++++  VERIFICACION DE ESTADO DEL USUARIO AUTENTICADO +++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        if ($estado != 1) {
            mysqli_close($conexion);
            http_response_code(200);
            echo json_encode([false, 'Usuario inactivo']);
            return;
        }

        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            +++++++++++++  VERIFICACION DE ESTADO DEL SISTEMA DE LA INSTITUCION ++++++++++++++
            +++++++++++++++++++++++++ EXCEPTUANDO AL USUARIO ADMINISTRADOR +++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
      if ($id != 4) {
    




            // Verificar estado de pago
            $estado_query = "SELECT cop.estado_pag pago FROM tb_agencia age
                                INNER JOIN clhpzzvb_bd_general_coopera.info_coperativa cop ON cop.id_cop = age.id_institucion
                                GROUP BY age.id_institucion";

            $estado_stmt = $conexion->prepare($estado_query);
            $estado_stmt->execute();
            $estado_result = $estado_stmt->get_result();

            if (!$estado_result) {
                mysqli_close($conexion);
                echo json_encode([false, 'Se presentó un percance, intente dentro de 3 min, si el problema persiste comunicarse con soporte gracias...']);
                return;
            }

            $estado_row = $estado_result->fetch_assoc();
            if ($estado_row['pago'] == 0) {
                mysqli_close($conexion);
                echo json_encode([
                    false, 'El sistema se encuentra bloqueado por falta de pago, favor de cancelar su cuota… gracias att: La administración de SOTECPRO.',
                    'icon' => 'warning',
                    'title' => '¡Pago pendiente!'
                ]);
                return;
            }
        }

        /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            +++++++++++++++++++  LOGIN EXITOSO, SE REGISTRA EN LA TABLA LOG ++++++++++++++++++
            ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
        $datos = array(
            "id_tb_usuario" => $id,
            "fecha_inicio" => $hoy2,
            "fecha_fin" => "0000-00-00",
            "ip_direccion" => $direccion_ip,
            "hostname" => $hostname,
            "user_agent" => $agente_usuario,
            "token" => "",
            "status" => 1,
            "info_adicional" => 'Inicio de sesion exitoso!'
        );
        $database->openConnection();
        $database->insert('tb_registro_login', $datos);
        $database->closeConnection();
        // Establecer sesión y devolver respuesta
        $_SESSION['id'] = $id;
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellido'] = $apellido;
        $_SESSION['dpi'] = $dpi;
        $_SESSION['usu'] = $usu;
        $_SESSION['puesto'] = $puesto;
        $_SESSION['id_agencia'] = $id_agencia;
        $_SESSION['agencia'] = $agencia;
        $_SESSION['nomagencia'] = $nomagencia;
        $_SESSION['background'] = 1;

        // Bloquear sistema
        $verifi_query = "SELECT cope.nomb_cor AS nomAge, estado_pag AS estado, fecha_pago 
                         FROM tb_agencia AS agen	
                         INNER JOIN tb_usuario AS usu ON agen.id_agencia = usu.id_agencia
                         INNER JOIN clhpzzvb_bd_general_coopera.info_coperativa AS cope ON agen.id_institucion = cope.id_cop
                         WHERE usu.id_usu =" . $_SESSION["id"];
    
        $result = mysqli_query($conexion, $verifi_query);
        while ($row = mysqli_fetch_assoc($result)) {
            $estado = $row['estado'];
            $fecha_pago = $row['fecha_pago'];
    
            // Calcular la fecha de pago más 5 días
            $fecha_mas_dias = date('Y-m-d', strtotime($fecha_pago . ' + 5 days'));
    
            if ($fecha_mas_dias <= $hoy) {
                // UPDATE
                $update_query = "UPDATE tb_agencia AS agen
                                 INNER JOIN tb_usuario AS usu ON agen.id_agencia = usu.id_agencia
                                 INNER JOIN clhpzzvb_bd_general_coopera.info_coperativa AS cope ON agen.id_institucion = cope.id_cop
                                 SET estado_pag = '0'
                                 WHERE usu.id_usu = " . $_SESSION["id"];
                
                if (mysqli_query($conexion, $update_query)) {
                    echo json_encode([
                        false, 'El sistema se encuentra bloqueado por falta de pago, favor de cancelar su cuota… gracias att: La administración de SOTECPRO.',
                        'icon' => 'warning',
                        'title' => '¡Pago pendiente!'
                    ]);
                    return;
                    mysqli_close($conexion);
                } else {
                    echo json_encode([false, 'Error en la consulta de actualización']);
                }
            }  
        }

        if (isset($_SESSION["intentos"])) {
            unset($_SESSION["intentos"]);
        }

        http_response_code(200);
        echo json_encode([true, 'Acceso satisfactorio', $_SESSION]);
        mysqli_close($conexion);
        break;

    case 'salir':
        session_unset();
        //Destruìmos la sesión
        session_destroy();
        http_response_code(200);
        echo json_encode([false, 'Sesion eliminada', $_SESSION]);
        break;
    case 'validar_usuario_por_mora':
        if ($_POST['username'] == "") {
            http_response_code(400);
            echo json_encode(['Debe llenar el campo de usuario', '0']);
            return;
        }
        if ($_POST['pass'] == "") {
            http_response_code(400);
            echo json_encode(['Debe llenar el campo de contraseña', '0']);
            return;
        }

        //CONSULTA DE USUARIOS
        $aux_consulta = "SELECT * FROM `tb_usuario` tbu
        INNER JOIN `tb_agencia` tbg ON tbu.id_agencia=tbg.id_agencia 
        WHERE (`puesto`='ADM' OR `puesto`='AAD' OR `puesto`='COO' OR `puesto`='GER' OR `puesto`='ANA') AND `usu`='" . $_POST['username'] . "'";
        $consulta = mysqli_query($conexion, $aux_consulta);

        if ($consulta) {
            if (mysqli_num_rows($consulta) > 0) {
                //convertir a hash
                $passwordencritado = encriptar_desencriptar($key1, $key2, 'encrypt', $_POST['pass']);
                //consultar si existe el usuario
                $aux_consulta = "SELECT * FROM `tb_usuario` tbu
                INNER JOIN `tb_agencia` tbg ON tbu.id_agencia=tbg.id_agencia 
                WHERE (`puesto`='ADM' OR `puesto`='AAD' OR `puesto`='COO' OR `puesto`='GER' OR `puesto`='ANA') AND `usu`='" . $_POST['username'] . "' AND `pass`='" . $passwordencritado . "'";
                $consulta = mysqli_query($conexion, $aux_consulta);

                if ($consulta) {
                    if (mysqli_num_rows($consulta) > 0) {
                        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                            $id = utf8_encode($fila['id_usu']);
                            $estado = utf8_encode($fila['estado']);
                        }
                        if ($estado == 1) {
                            http_response_code(200);
                            echo json_encode(['Cambio de mora autorizado', '1', true, $id]);
                        } else {
                            http_response_code(200);
                            echo json_encode(['Usuario inactivo', '0', false]);
                        }
                    } else {
                        http_response_code(200);
                        echo json_encode(['Usuario o contraseña incorrecto', '0', false]);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['Error al consultar el usuario y la contraseña', '0', false]);
                }
            } else {
                http_response_code(200);
                echo json_encode(['Usuario no encontrado con los permisos necesarios', '0', false]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['Error en el sistema de la base de datos', '0', false]);
        }
        mysqli_close($conexion);
        break;

    case 'validar_usuario_por_interes':

        $data =  $_POST['datos'];
        $usu = $data[0][0];
        $pass = $data[1];

        if ($usu == "") {
            http_response_code(400);
            echo json_encode(['Debe llenar el campo de usuario', '0']);
            return;
        }
        if ($pass == "") {
            http_response_code(400);
            echo json_encode(['Debe llenar el campo de contraseña', '0']);
            return;
        }
        //CONSULTA DE USUARIOS
        $aux_consulta = "SELECT * FROM `tb_usuario` tbu
                INNER JOIN `tb_agencia` tbg ON tbu.id_agencia=tbg.id_agencia 
                WHERE (`puesto`='ADM' OR `puesto`='AAD' OR `puesto`='COO' OR `puesto`='GER' OR `puesto`='ANA') AND `usu`='" . $usu . "'";

        $consulta = mysqli_query($conexion, $aux_consulta);

        if ($consulta) {
            if (mysqli_num_rows($consulta) > 0) {
                //convertir a hash
                $passwordencritado = encriptar_desencriptar($key1, $key2, 'encrypt', $pass);
                //consultar si existe el usuario

                // echo $usu.' - slc - '.$passwordencritado ; 
                // return; 

                $aux_consulta = "SELECT * FROM `tb_usuario` tbu
                        INNER JOIN `tb_agencia` tbg ON tbu.id_agencia=tbg.id_agencia 
                        WHERE (`puesto`='ADM' OR `puesto`='AAD' OR `puesto`='COO' OR `puesto`='GER' OR `puesto`='ANA') AND `usu`='" . $usu . "' AND `pass`='" . $passwordencritado . "'";
                $consulta = mysqli_query($conexion, $aux_consulta);

                if ($consulta) {
                    if (mysqli_num_rows($consulta) > 0) {
                        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                            $id = utf8_encode($fila['id_usu']);
                            $estado = utf8_encode($fila['estado']);
                        }
                        if ($estado == 1) {
                            http_response_code(200);
                            echo json_encode(['Cambio de interes, autorizado', '1', true, $id]);
                        } else {
                            http_response_code(200);
                            echo json_encode(['Usuario inactivo', '0', false]);
                        }
                    } else {
                        http_response_code(200);
                        echo json_encode(['Contraseña incorrecto', '0', false]);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['Error al consultar el usuario y la contraseña', '0', false]);
                }
            } else {
                http_response_code(200);
                echo json_encode(['Usuario no encontrado con los permisos necesarios', '0', false]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['Error en el sistema de la base de datos', '0', false]);
        }
        mysqli_close($conexion);
        break;

    case 'create_user':
        //valores de los inputs
        $inputs = $_POST["inputs"];
        //Selects de datos 
        $selects = $_POST["selects"];
        $archivo = $_POST["archivo"];

        //validaciones
        if ($selects[0] == "0") {
            echo json_encode(['Debe selecionar una agencia', '0']);
            return;
        }
        if ($inputs[0] == "") {
            echo json_encode(['Debe llenar el campo nombres', '0']);
            return;
        }
        if ($inputs[1] == "") {
            echo json_encode(['Debe llenar el campo apellidos', '0']);
            return;
        }
        if ($inputs[2] == "") {
            echo json_encode(['Debe digitar un numero de identificacíon', '0']);
            return;
        }
        if (!is_numeric($inputs[2]) || strlen($inputs[2]) !=  13) {
            echo json_encode(['Debe digitar un numero de DPI válido', '0']);
            return;
        }
        if ($selects[1] == "0") {
            echo json_encode(['Debe selecionar un cargo', '0']);
            return;
        }
        if ($inputs[3] == "") {
            echo json_encode(['Debe digitar un correo electrónico', '0']);
            return;
        }
        //validar el correo con formato valido
        if (!filter_var($inputs[3], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['Debe digitar un correo válido', '0']);
            return;
        }
        if ($inputs[4] == "") {
            echo json_encode(['Debe digitar un nombre de usuario', '0']);
            return;
        }
        //validar la segurida de la contraseña
        $minusculas  =  preg_match('`[a-z]`',  $inputs[4]);
        $numeros =  preg_match('`[0-9]`',  $inputs[4]);
        if (!$minusculas   || !$numeros || strlen($inputs[4]) <  8) {
            echo json_encode(['El usuario debe tener un minimo de 8 caracteres y al menos un numero', '0']);
            return;
        }

        if ($inputs[5] == "") {
            echo json_encode(['Debe digitar una contraseña', '0']);
            return;
        }
        if ($inputs[6] == "") {
            echo json_encode(['Debe digitar la confirmacion de la contraseña', '0']);
            return;
        }
        //validar la confirmacion
        if ($inputs[5] != $inputs[6]) {
            echo json_encode(['La confirmacion de contraseña no es igual a la contraseña', '0']);
            return;
        }
        //validar estado inactivo
        // if ($selects[2] == "0") {
        //     echo json_encode(['Debe selecionar un cargo', '0']);
        //     return;
        // }

        //validar la segurida de la contraseña
        $mayusculas  =  preg_match('`[A-Z]`',  $inputs[5]);
        $minusculas  =  preg_match('`[a-z]`',  $inputs[5]);
        $numeros =  preg_match('`[0-9]`',  $inputs[5]);
        $specialChars  =  preg_match('`[^ \^\!\@\#\$\%\/\*\¡\¿\?]`',  $inputs[5]);

        if (!$mayusculas  || !$minusculas   || !$numeros || !$specialChars || strlen($inputs[5]) <  8) {
            echo json_encode(['La contraseña debe tener al menos 8 caracteres de longitud y debe incluir al menos una mayúscula, un número y un carácter especial', '0']);
            return;
        }

        //consultar sino existe un usuario con ese nombre
        $verificar = mysqli_query($conexion, "SELECT * FROM tb_usuario WHERE usu='" . $inputs[4] . "'");
        $bandera = false;
        while ($fila = mysqli_fetch_array($verificar, MYSQLI_ASSOC)) {
            $bandera = true;
        }
        if ($bandera) {
            echo json_encode(['No se puede registrar al usuario porque ya existe', '0']);
            return;
        }
        //encriptar_password
        $passwordencritado = encriptar_desencriptar($key1, $key2, 'encrypt', $inputs[5]);

        //realizar la insercion
        $conexion->autocommit(false);
        try {
            $res = $conexion->query("INSERT INTO `tb_usuario`(`nombre`, `apellido`, `dpi`, `usu`, `pass`, `estado`,`puesto`,`id_agencia`,`email`,`created_by`,`created_at`) 
            VALUES ('$inputs[0]','$inputs[1]','$inputs[2]','$inputs[4]','$passwordencritado',1,'$selects[1]','$selects[0]','$inputs[3]','$archivo[0]','$hoy2')");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode(['Error al momento de registrar los datos', '0']);
                // echo json_encode([$aux, '0']);
                return;
            }
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
    case 'update_user':
        //valores de los inputs
        $inputs = $_POST["inputs"];
        //Selects de datos 
        $selects = $_POST["selects"];
        $archivo = $_POST["archivo"];
        //validaciones
        if ($inputs[7] == "") {
            echo json_encode(['No ha seleccionado un registro para actualizar', '0']);
            return;
        }
        if ($selects[0] == "0") {
            echo json_encode(['Debe selecionar una agencia', '0']);
            return;
        }
        if ($inputs[0] == "") {
            echo json_encode(['Debe llenar el campo nombres', '0']);
            return;
        }
        if ($inputs[1] == "") {
            echo json_encode(['Debe llenar el campo apellidos', '0']);
            return;
        }
        if ($inputs[2] == "") {
            echo json_encode(['Debe digitar un numero de identificacíon', '0']);
            return;
        }
        if (!is_numeric($inputs[2]) || strlen($inputs[2]) !=  13) {
            echo json_encode(['Debe digitar un numero de DPI válido', '0']);
            return;
        }
        if ($selects[1] == "0") {
            echo json_encode(['Debe selecionar un cargo', '0']);
            return;
        }
        if ($inputs[3] == "") {
            echo json_encode(['Debe digitar un correo electrónico', '0']);
            return;
        }
        //validar el correo con formato valido
        if (!filter_var($inputs[3], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['Debe digitar un correo válido', '0']);
            return;
        }
        if ($inputs[4] == "") {
            echo json_encode(['Debe digitar un nombre de usuario', '0']);
            return;
        }
        //validar la segurida de la contraseña
        $minusculas  =  preg_match('`[a-z]`', $inputs[4]);
        $numeros =  preg_match('`[0-9]`', $inputs[4]);
        if (!$minusculas   || !$numeros || strlen($inputs[4]) <  8) {
            echo json_encode([$inputs[4], '0']);
            return;
        }
        if ($inputs[5] == "") {
            echo json_encode(['Debe digitar una contraseña', '0']);
            return;
        }
        if ($inputs[6] == "") {
            echo json_encode(['Debe digitar la confirmacion de la contraseña', '0']);
            return;
        }
        //validar la confirmacion
        if ($inputs[5] != $inputs[6]) {
            echo json_encode(['La confirmacion de contraseña no es igual a la contraseña', '0']);
            return;
        }
        //validar la segurida de la contraseña
        $mayusculas  =  preg_match('`[A-Z]`',  $inputs[5]);
        $minusculas  =  preg_match('`[a-z]`',  $inputs[5]);
        $numeros =  preg_match('`[0-9]`',  $inputs[5]);
        $specialChars  =  preg_match('`[^ \^\!\@\#\$\%\/\*\¡\¿\?]`',  $inputs[5]);

        if (!$mayusculas  || !$minusculas   || !$numeros || !$specialChars || strlen($inputs[5]) <  8) {
            echo json_encode(['La contraseña debe tener al menos 8 caracteres de longitud y debe incluir al menos una mayúscula, un número y un carácter especial', '0']);
            return;
        }
        // validar estado inactivo
        if ($selects[2] < 1 || $selects[2] > 2) {
            echo json_encode(['Debe seleccionar un estado', '0']);
            return;
        }
        //consultar sino existe un usuario con ese nombre
        $verificar = mysqli_query($conexion, "SELECT * FROM tb_usuario WHERE usu='" . $inputs[4] . "' AND id_usu!='" . $inputs[7] . "'");
        $bandera = false;
        while ($fila = mysqli_fetch_array($verificar, MYSQLI_ASSOC)) {
            $bandera = true;
        }
        if ($bandera) {
            echo json_encode(['No se puede actualizar el registro porque ya existe un usuario con los mismos datos', '0']);
            return;
        }
        //encriptar_password
        $passwordencritado = encriptar_desencriptar($key1, $key2, 'encrypt', $inputs[5]);

        //realizar la insercion
        $conexion->autocommit(false);
        try {
            $res = $conexion->query("UPDATE `tb_usuario` set `nombre`= '$inputs[0]', `apellido`= '$inputs[1]', `dpi`= '$inputs[2]', `usu`= '$inputs[4]', `pass`= '$passwordencritado', `estado`= '$selects[2]', `puesto`= '$selects[1]',`id_agencia`= '$selects[0]',`email`= '$inputs[3]',`updated_by`= '$archivo[0]',`updated_at`= '$hoy2' WHERE id_usu='$inputs[7]'");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode(['Error al momento de actualizar los datos', '0']);
                // echo json_encode([$aux, '0']);
                return;
            }
            if ($res) {
                $conexion->commit();
                if ($_SESSION['id'] == $inputs[7]) {
                    echo json_encode(['Registro actualizado satisfactoriamente', '1', '1']);
                } else {
                    echo json_encode(['Registro actualizado satisfactoriamente', '1', '0']);
                }
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
    case 'delete_user':
        $id = $_POST['ideliminar'];
        //realizar la insercion
        $conexion->autocommit(false);
        try {
            $res = $conexion->query("UPDATE `tb_usuario` set `estado`= '0',`deleted_by`= '" . $_SESSION['id'] . "',`deleted_at`= '$hoy2' WHERE id_usu='$id'");
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode(['Error al momento de eliminar el registro', '0']);
                // echo json_encode([$aux, '0']);
                return;
            }
            if ($res) {
                $conexion->commit();
                if ($_SESSION['id'] == $id) {
                    echo json_encode(['Registro eliminado satisfactoriamente', '1', '1']);
                } else {
                    echo json_encode(['Registro eliminado satisfactoriamente', '1', '0']);
                }
            } else {
                $conexion->rollback();
                echo json_encode(['Registro no eliminado satisfactoriamente', '0']);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
        }
        break;


    case 'get_password':
        $id = $_POST["id"];
        $verificar = mysqli_query($conexion, "SELECT pass FROM tb_usuario WHERE id_usu='" . $id . "'");
        $sindesencriptar = "";
        while ($fila = mysqli_fetch_array($verificar, MYSQLI_ASSOC)) {
            $sindesencriptar = $fila['pass'];
        }
        //encriptar_password
        $passwordesencritado = encriptar_desencriptar($key1, $key2, 'decrypt', $sindesencriptar);
        echo json_encode($passwordesencritado);
        break;
    case 'create_permisos':
        //obtener las variables necesarias
        $id = $_POST['id_actual'];
        $usuario = $_POST['usuario'];

        //validar los campos vacios
        if ($id == "" || $usuario == "") {
            echo json_encode(['Debe seleccionar un usuario', '0']);
            return;
        }
        //validar que quiera insertar permisos de un usuario que ya tiene permisos
        $verificar = mysqli_query($conexion, "SELECT us.id_usu AS id_usuario, CONCAT(us.nombre,' ', us.apellido) AS nombre, cg.UsuariosCargoProfecional AS cargo, ag.nom_agencia AS nombreagen, ag.cod_agenc AS codagen FROM tb_usuario us
        INNER JOIN tb_permisos2 pe ON us.id_usu=pe.id_usuario
        INNER JOIN clhpzzvb_bd_general_coopera.tb_submenus ts ON pe.id_submenu=ts.id
        INNER JOIN clhpzzvb_bd_general_coopera.tb_menus tm ON ts.id_menu =tm.id
        INNER JOIN clhpzzvb_bd_general_coopera.tb_modulos td ON tm.id_modulo =td.id
        INNER JOIN clhpzzvb_bd_general_coopera.tb_permisos_modulos tbps ON td.id=tbps.id_modulo
        INNER JOIN clhpzzvb_bd_general_coopera.tb_usuarioscargoprofecional cg ON us.puesto=cg.id_UsuariosCargoProfecional
        INNER JOIN tb_agencia ag ON us.id_agencia=ag.id_agencia
        WHERE ts.estado='1' AND tm.estado='1' AND td.estado='1' AND tbps.estado='1' AND us.estado!='0' AND us.id_usu='$id' AND 
            tbps.id_cooperativa=(SELECT ag1.id_institucion FROM tb_agencia ag1 LIMIT 1)
        GROUP BY us.id_usu 
        ORDER BY td.id, td.descripcion ASC");
        $bandera = false;
        while ($fila = mysqli_fetch_array($verificar, MYSQLI_ASSOC)) {
            $bandera = true;
        }
        if ($bandera) {
            echo json_encode(['No se pueden agregar los permisos al usuario seleccionado porque tal usuario ya tiene algunos permisos', '0']);
            return;
        }
        //validar que ha seleccionado permisos
        if (!isset($_POST['permisos'])) {
            echo json_encode(['No se puede realizar el registro porque no ha seleccionado al menos un permiso', '0']);
            return;
        }
        $permisos = $_POST['permisos'];

        //realizar las inserciones
        $bandera_insercion = false;
        $conexion->autocommit(false);
        for ($i = 0; $i < count($permisos); $i++) {
            //insercion en la base de datos
            $res = $conexion->query("INSERT INTO `tb_permisos2`(`id_usuario`, `id_submenu`) VALUES ('$id','$permisos[$i]')");
            $aux = mysqli_error($conexion);
            if ($aux) {
                $bandera_insercion = true;
            }
            if (!$res) {
                $bandera_insercion = true;
            }
        }

        if ($bandera_insercion) {
            $conexion->rollback();
            echo json_encode(['Registro de permisos no satisfactorios', '0']);
        } else {
            $conexion->commit();
            echo json_encode(['Registro de permisos satisfactorios', '1']);
        }
        mysqli_close($conexion);
        break;
    case 'update_permisos':
        //obtener las variables necesarias
        $id = $_POST['id_actual'];
        $id_past = $_POST['id_pasado'];
        $usuario = $_POST['usuario'];

        //validar los campos vacios
        if ($id == "" || $id_past == "" || $usuario == "") {
            echo json_encode(['Debe seleccionar un usuario', '0']);
            return;
        }
        if ($id != $id_past) {
            //validar que quiera insertar permisos de un usuario que ya tiene permisos
            $verificar = mysqli_query($conexion, "SELECT us.id_usu AS id_usuario, CONCAT(us.nombre,' ', us.apellido) AS nombre, cg.UsuariosCargoProfecional AS cargo, ag.nom_agencia AS nombreagen, ag.cod_agenc AS codagen FROM tb_usuario us
            INNER JOIN tb_permisos2 pe ON us.id_usu=pe.id_usuario
            INNER JOIN clhpzzvb_bd_general_coopera.tb_submenus ts ON pe.id_submenu=ts.id
            INNER JOIN clhpzzvb_bd_general_coopera.tb_menus tm ON ts.id_menu =tm.id
            INNER JOIN clhpzzvb_bd_general_coopera.tb_modulos td ON tm.id_modulo =td.id
            INNER JOIN clhpzzvb_bd_general_coopera.tb_permisos_modulos tbps ON td.id=tbps.id_modulo
            INNER JOIN clhpzzvb_bd_general_coopera.tb_usuarioscargoprofecional cg ON us.puesto=cg.id_UsuariosCargoProfecional
            INNER JOIN tb_agencia ag ON us.id_agencia=ag.id_agencia
            WHERE ts.estado='1' AND tm.estado='1' AND td.estado='1' AND tbps.estado='1' AND us.estado!='0' AND us.id_usu='$id' AND 
                tbps.id_cooperativa=(SELECT ag1.id_institucion FROM tb_agencia ag1 LIMIT 1)
            GROUP BY us.id_usu 
			ORDER BY td.id, td.descripcion ASC");
            $bandera = false;
            while ($fila = mysqli_fetch_array($verificar, MYSQLI_ASSOC)) {
                $bandera = true;
            }
            if ($bandera) {
                echo json_encode(['No se pueden editar los permisos al usuario seleccionado porque tal usuario ya tiene algunos permisos', '0']);
                return;
            }
        }
        //validar que ha seleccionado permisos
        if (!isset($_POST['permisos'])) {
            echo json_encode(['No se puede realizar el registro porque no ha seleccionado al menos un permiso', '0']);
            return;
        }
        $permisos = $_POST['permisos'];

        //eliminacion de permisos actuales para luego reescribir
        if ($id != $id_past) {
            //eliminar registro
            $conexion->autocommit(false);
            $res = $conexion->query("DELETE FROM `tb_permisos2` WHERE id_usuario='$id_past'");
            $aux = mysqli_error($conexion);
            if ($aux) {
                $conexion->rollback();
                echo json_encode(['Error en la eliminación de permisos anteriores, intente nuevamente', '0']);
                return;
            }
            if ($res) {
                $conexion->commit();
            } else {
                $conexion->rollback();
                echo json_encode(['Error en la eliminación de permisos anteriores, intente nuevamente', '0']);
                return;
            }
        }
        if ($id == $id_past) {
            //eliminar registro
            $conexion->autocommit(false);
            $res = $conexion->query("DELETE FROM `tb_permisos2` WHERE id_usuario='$id'");
            $aux = mysqli_error($conexion);
            if ($aux) {
                $conexion->rollback();
                echo json_encode(['Error en la insercion de permisos, intente nuevamente', '0']);
                return;
            }
            if ($res) {
                $conexion->commit();
            } else {
                $conexion->rollback();
                echo json_encode(['Error en la insercion de permisos, intente nuevamente', '0']);
                return;
            }
        }

        //realizar las inserciones
        $bandera_insercion = false;
        $conexion->autocommit(false);
        for ($i = 0; $i < count($permisos); $i++) {
            //insercion en la base de datos
            $res = $conexion->query("INSERT INTO `tb_permisos2`(`id_usuario`, `id_submenu`) VALUES ('$id','$permisos[$i]')");
            $aux = mysqli_error($conexion);
            if ($aux) {
                $bandera_insercion = true;
            }
            if (!$res) {
                $bandera_insercion = true;
            }
        }

        if ($bandera_insercion) {
            $conexion->rollback();
            echo json_encode(['Actualización de permisos no satisfactorios', '0']);
        } else {
            $conexion->commit();
            echo json_encode(['Actualización de permisos satisfactorios', '1']);
        }
        mysqli_close($conexion);
        break;
    case 'delete_permisos':
        $id = $_POST['ideliminar'];
        $conexion->autocommit(false);
        $res = $conexion->query("DELETE FROM `tb_permisos2` WHERE id_usuario='$id'");
        $aux = mysqli_error($conexion);
        if ($aux) {
            $conexion->rollback();
            echo json_encode(['Error en la eliminacion de los registros, intente nuevamente', '0']);
            return;
        }
        if ($res) {
            $conexion->commit();
            echo json_encode(['Registros eliminados correctamente', '1']);
        } else {
            $conexion->rollback();
            echo json_encode(['Error en la eliminacion de los registros, intente nuevamente', '0']);
        }
        mysqli_close($conexion);
        break;
    case 'obtener_permisos':
        $id = $_POST['id'];
        $datos = mysqli_query($conexion, "SELECT ts.id AS id_submenu FROM tb_usuario us
        INNER JOIN tb_permisos2 pe ON us.id_usu=pe.id_usuario
        INNER JOIN clhpzzvb_bd_general_coopera.tb_submenus ts ON pe.id_submenu=ts.id
        INNER JOIN clhpzzvb_bd_general_coopera.tb_menus tm ON ts.id_menu =tm.id
        INNER JOIN clhpzzvb_bd_general_coopera.tb_modulos td ON tm.id_modulo =td.id
        INNER JOIN clhpzzvb_bd_general_coopera.tb_permisos_modulos tbps ON td.id=tbps.id_modulo
        WHERE ts.estado='1' AND tm.estado='1' AND td.estado='1' AND tbps.estado='1' AND us.estado!='0' AND us.id_usu='$id' AND 
            tbps.id_cooperativa=(SELECT ag1.id_institucion FROM tb_agencia ag1 LIMIT 1)
        ORDER BY td.id, td.descripcion ASC");
        $data[] = [];
        $bandera = false;
        $i = 0;
        while ($fila = mysqli_fetch_array($datos, MYSQLI_ASSOC)) {
            $data[$i] = $fila;
            $bandera = true;
            $i++;
        }

        if ($bandera) {
            echo json_encode(['Se encontraron permisos', '1', $data]);
        } else {
            echo json_encode(['No se encontraron permisos disponibles', '0']);
        }
        mysqli_close($conexion);
        break;
    case 'redirect':
        $url = "";
        if ($type_host == '1') {
            $url = "/microsystemplus/views/";
        } else {
            $url = "/" . "views/";
        }
        if ($url == "") {
            echo json_encode(['No se encontro la ruta especificada', '0']);
        } else {
            echo json_encode([$url, '1']);
        }
        break;
    case 'confirmar_apertura_cierre_caja':
        if ($_POST['idusuario'] == "") {
            http_response_code(400);
            echo json_encode(['No se ha encontrado el identificador del usuario', '0']);
            return;
        }
        if ($_POST['pass'] == "") {
            http_response_code(400);
            echo json_encode(['Debe llenar el campo de contraseña', '0']);
            return;
        }

        //CONSULTA DE USUARIOS
        try {
            $stmt = $conexion->prepare("SELECT * FROM `tb_usuario` tbu WHERE tbu.id_usu=?");
            if (!$stmt) {
                $error = $conexion->error;
                http_response_code(400);
                echo json_encode(['Error preparando consulta 1: ' . $error, '0', false]);
                return;
            }
            $stmt->bind_param("s", $_POST['idusuario']);
            if (!$stmt->execute()) {
                $errorMsg = $stmt->error;
                http_response_code(400);
                echo json_encode(["Error en el sistema de la base de datos: $errorMsg", '0', false]);
                return;
            }
            $resultado = $stmt->get_result();
            $numFilas = $resultado->num_rows;
            if ($numFilas > 0) {
                //convertir a hash
                $passwordencritado = encriptar_desencriptar($key1, $key2, 'encrypt', $_POST['pass']);

                //comprobar si existe el usuario
                $stmt = $conexion->prepare("SELECT * FROM `tb_usuario` tbu WHERE tbu.id_usu=? AND tbu.pass=?");
                if (!$stmt) {
                    $error = $conexion->error;
                    http_response_code(400);
                    echo json_encode(['Error preparando consulta 2: ' . $error, '0', false]);
                    return;
                }
                $stmt->bind_param("ss", $_POST['idusuario'], $passwordencritado);
                if (!$stmt->execute()) {
                    $errorMsg = $stmt->error;
                    http_response_code(400);
                    echo json_encode(["Error al consultar la contraseña: $errorMsg", '0', false]);
                    return;
                }
                $resultado = $stmt->get_result();
                $numFilas = $resultado->num_rows;
                if ($numFilas > 0) {
                    while ($fila = $resultado->fetch_assoc()) {
                        $id = utf8_encode($fila['id_usu']);
                        $estado = utf8_encode($fila['estado']);
                    }
                    if ($estado == 1) {
                        http_response_code(200);
                        echo json_encode(['Confirmación válida', '1', true, $id]);
                    } else {
                        http_response_code(200);
                        echo json_encode(['Usuario inactivo', '0', false]);
                    }
                } else {
                    http_response_code(200);
                    echo json_encode(["Contraseña incorrecta", '0', false]);
                }
            } else {
                http_response_code(200);
                echo json_encode(['Usuario no existe en el sistema', '0', false]);
            }
        } catch (Exception $e) {
            $error = ($e->getMessage());
            http_response_code(400);
            echo json_encode(["Error interno: $error", '0', false]);
        }
        break;
    case 'modo':
        if (isset($_POST['color'])) {
            $_SESSION['color'] = ($_POST['color'] == 0) ? 1 : 0;
            $_SESSION['background'] = $_POST['color'];

            http_response_code(200);
            echo json_encode([true, 'listo', $_SESSION['background']]);
        } else {
            http_response_code(400);
            // echo json_encode($consulta);
            echo json_encode('Variable no declarada');
        }
        break;
        // FUNCION PARA CAMBIO DE CONTRASEÑA   
    case 'change_pass':
        //valores de los inputs
        $inputs = $_POST["inputs"];
        $archivo = $_POST["archivo"];
        $id = $inputs[3];
        // Sumar 3 meses a la fecha actual
        $fecha = date("Y-m-d H:i:s");
        $fechax3 = date("Y-m-d", strtotime($fecha . "+3 months"));
        //validar la segurida de la contraseña
        $mayusculas = preg_match('`[A-Z]`', $inputs[1]);
        $minusculas = preg_match('`[a-z]`', $inputs[1]);
        $numeros = preg_match('`[0-9]`', $inputs[1]);
        $specialChars = preg_match('`[^ \^\!\@\#\$\%\/\*\¡\¿\?]`', $inputs[1]);
        if (!$mayusculas || !$minusculas || !$numeros || !$specialChars || strlen($inputs[1]) <  8) {
            echo json_encode(['La contraseña debe tener al menos 8 caracteres de longitud y debe incluir al menos una mayúscula, un número y un carácter especial', '0']);
            return;
        }
        //encriptar_password
        $encrypt = encriptar_desencriptar($key1, $key2, 'encrypt', $inputs[1]);
        //CONSULTA (•_•) ( •_•)>⌐■-■ (⌐■_■)
        $query = "UPDATE tb_usuario SET exp_date='$fechax3',updated_by='$id',updated_at='$fecha',pass='$encrypt' WHERE id_usu=$id;";
        //echo json_encode([$query, 1]);
        //	realizar la insercion
        $conexion->autocommit(false);
        try {
            $res = $conexion->query($query);
            $aux = mysqli_error($conexion);
            if ($aux) {
                echo json_encode(['Error al momento de actualizar los datos', 0]);
                return;
            }
            if ($res) {
                $conexion->commit();
                echo json_encode(['Contraseña Actualizada', 1, 1]);
            } else {
                $conexion->rollback();
                echo json_encode(['Registro NO actualizado', 0]);
            }
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(['Error al ingresar:', 0]);
        }
        mysqli_close($conexion); //*/
        break;
    case 'parametrizaAgencia':
        $arch = $_POST['archivo'];
        // Crear la consulta SQL con marcadores de posición '?'
        $sql = "UPDATE tb_agencia set id_nomenclatura_caja = ? WHERE id_agencia = ? ";
        // Crear una sentencia preparada
        $stmt = $conexion->prepare($sql);

        if ($stmt) {
            // Vincular parámetros y valores a los marcadores de posición
            $stmt->bind_param("ii", $arch[0], $arch[1]);

            // Ejecutar la consulta preparada para insertar los datos
            if ($stmt->execute()) {
                echo json_encode(["Datos actualizados ", '1']);
                return;
            } else {
                echo json_encode(["Error al realizar la actualización ", '0']);
                return;
            }
            // Cerrar la sentencia preparada
            $stmt->close();
        } else {
            echo "Error en la consulta: ";
        }
        // Cerrar la conexión a la base de datos
        $conexion->close();
        break;
}


//funcion para encriptar y desencriptar usuarios
// TAMBIENSE USA EN USUARIO_01 (HACERLA REUTILIZABLE)
function encriptar_desencriptar($mykey1, $mykey2, $action = 'encrypt', $string = false)
{
    $action = trim($action);
    $output = false;
    $myKey = $mykey1;
    $myIV = $mykey2;
    $encrypt_method = 'AES-256-CBC';
    $secret_key = hash('sha256', $myKey);
    $secret_iv = substr(hash('sha256', $myIV), 0, 16);

    if ($action && ($action == 'encrypt' || $action == 'decrypt') && $string) {
        $string = trim(strval($string));

        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $secret_key, 0, $secret_iv);
        };

        if ($action == 'decrypt') {
            $output = openssl_decrypt($string, $encrypt_method, $secret_key, 0, $secret_iv);
        };
    };
    return $output;
};

//revisa si ya expiro su contraseña (NEGROY)
function ValidaDatePass($exp_date, $id, $puesto, $conexion)
{
    // Obtiene la fecha actual
    $current_date = date('Y-m-d');

    // Verifica si el cod_aux existe
    $check_query = "SELECT * FROM tb_alerta WHERE cod_aux = $id";
    $check_result = $conexion->query($check_query);

    if ($check_result->num_rows > 0) {
        // El cod_aux existe, realiza la actualización del estado
        if ($exp_date <= $current_date) {
            $update_query = "UPDATE tb_alerta SET estado = 1, updated_at='$current_date' WHERE cod_aux = $id";
        } else {
            $update_query = "UPDATE tb_alerta SET estado = 0, updated_at='$current_date' WHERE cod_aux = $id";;
        }
        $update_result = $conexion->query($update_query);
        return $update_result;
    } else {
        // El cod_aux no existe, crea una nueva alerta
        $insert_query = "INSERT INTO tb_alerta (puesto, tipo_alerta, mensaje, cod_aux, codDoc, proceso, estado, fecha, created_by, updated_by, created_at, updated_at) VALUES ('$puesto', 'PASS', 'CAMBIAR CONTRASEÑA', '$id', '123', 'X', 0, '$current_date', 4, 4, '$current_date', '$current_date')";
        $agregar = "UPDATE tb_usuario set exp_date = CURRENT_DATE WHERE id_usu = '$id'";
        // REALIZA LAS CONSULTAS
        $insert_result = $conexion->query($insert_query);
        $agregar_result = $conexion->query($agregar);
        return $agregar_result;
    }
    // SIN VALIDACIONES DE CONEXION POR QUE SOY UN HUEVON PISADO 	ᕕ(⌐■_■)ᕗ ♪♬
    /** FALTA UNA VALIDACION LA CUAL ES QUE SI NO HAY FECHGA DE EXPIRACION QUE SE AGREGE LA FECHA DE HPOY 
     * $agregar = "UPDATE tb_usuario set exp_date = CURRENT_DATE WHERE id_usu = '$id'";  */

    function val_estadoPag($conexion)
    {
        $estado = $conexion->query("SELECT cop.estado_pag pago FROM tb_agencia age
             INNER JOIN clhpzzvb_bd_general_coopera.info_coperativa cop ON cop.id_cop = age.id_institucion
             GROUP BY age.id_institucion");

        if (!$estado) {
            $rst = "Se presentó un percance, intente dentro de 3 min, si el problema persiste comunicarse con soporte gracias...";
            return $rst; // Retorna el mensaje de error
        }
        $rst = $estado->fetch_assoc()['pago'];
        return $rst; // Retorna el valor obtenido de la consulta
    }
}
