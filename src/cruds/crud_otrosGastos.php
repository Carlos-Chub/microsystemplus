<?php
session_start();
include_once '../funcphp/func_gen.php';
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
//mysqli_set_charset($general, 'utf8');
date_default_timezone_set('America/Guatemala');
$hoy = date("Y-m-d");
$hoy2 = date("Y-m-d H:i:s");

use Luecano\NumeroALetras\NumeroALetras;

use function PHPSTORM_META\type;

function valida($valida, $op, $conexion)
{
    switch ($op) {
        case 1:
            if (!$valida) {
                echo json_encode(["Error al ingresar los datos", '0']);
                return;
            }
            break;
        case 2:
            if (!$valida->execute()) {
                $conexion->rollback();
                echo json_encode(["Error al ingresar los datos", '0']);
                return;
            }
            break;
    }
}

$condi = $_POST["condi"];

switch ($condi) {
    case 'ins_otrGasto': //Insertar otros gastos
        $input = $_POST['inputs'];
        $select = $_POST['selects'];
        $codUsu = $_POST['archivo'];

        // Crear la consulta SQL con marcadores de posición '?'
        $sql = "INSERT INTO otr_tipo_ingreso (id_nomenclatura, nombre_gasto, tipo, created_by, created_at) VALUES (?,?,?,?,?)";
        // Crear una sentencia preparada
        $stmt = $conexion->prepare($sql);

        if ($stmt) {
            // Vincular parámetros y valores a los marcadores de posición
            $stmt->bind_param("isiis", $input[1], $input[0], $select[0], $codUsu, $hoy2); // "siss" indica los tipos de datos: string, integer, string, string

            // Ejecutar la consulta preparada para insertar los datos
            if ($stmt->execute()) {
                echo json_encode(["Registro exitoso ", '1']);
                return;
            } else {
                echo json_encode(["Error al realizar el registro ", '0']);
                return;
            }
            // Cerrar la sentencia preparada
            $stmt->close();
        } else {
            echo "Error en la consulta: ";
        }
        // Cerrar la conexión a la base de datos
        $conexion->close();
        // echo json_encode(["Ingreso correctamente ".$codUsu, '0']);
        // return;        
        break;

    case 'act_otrGasto':
        $input = $_POST['inputs'];
        $select = $_POST['selects'];
        $codUsu = $_POST['archivo'];
        // Crear la consulta SQL con marcadores de posición '?'
        $sql = "UPDATE otr_tipo_ingreso SET id_nomenclatura = ?, nombre_gasto = ?, tipo = ?, updated_by = ?, updated_at = ? WHERE id = ?;";
        // Crear una sentencia preparada
        $stmt = $conexion->prepare($sql);

        if ($stmt) {
            // Vincular parámetros y valores a los marcadores de posición
            $stmt->bind_param("isiisi", $input[1], $input[2], $select[0], $codUsu, $hoy2, $input[0]);

            // Ejecutar la consulta preparada para insertar los datos
            if ($stmt->execute()) {
                echo json_encode(["Registro exitoso ", '1']);
                return;
            } else {
                echo json_encode(["Error al realizar el registro ", '0']);
                return;
            }
            // Cerrar la sentencia preparada
            $stmt->close();
        } else {
            echo "Error en la consulta: ";
        }
        // Cerrar la conexión a la base de datos
        $conexion->close();
        // echo json_encode(["Ingreso correctamente ".$codUsu, '0']);
        // return;  
        break;
    case 'eli_otrGasto':
        $id = $_POST['ideliminar'];
        $codUsu = $_POST['archivo'];
        $estado = 0;

        // Crear la consulta SQL con marcadores de posición '?'
        $sql = "UPDATE otr_tipo_ingreso SET estado = ?, deleted_by = ?, deleted_at = ? WHERE id = ?";
        // Crear una sentencia preparada
        $stmt = $conexion->prepare($sql);

        if ($stmt) {
            // Vincular parámetros y valores a los marcadores de posición
            $stmt->bind_param("iisi", $estado, $codUsu, $hoy2, $id);

            // Ejecutar la consulta preparada para insertar los datos
            if ($stmt->execute()) {
                echo json_encode(["Registro exitoso ", '1']);
                return;
            } else {
                echo json_encode(["Error al realizar el registro ", '0']);
                return;
            }
            // Cerrar la sentencia preparada
            $stmt->close();
        } else {
            echo "Error en la consulta: ";
        }
        // Cerrar la conexión a la base de datos
        $conexion->close();
        // echo json_encode(["Ingreso correctamente ".$codUsu, '0']);
        // return;
        break;
    case 'cre_otrRecibo':
        $arch = $_POST['archivo'];
        $input = $_POST['inputs'];
        $matriz = $arch[1];
        $numF = count($matriz); // Numero de filas 
        $estado = 1;
        $mensaje_error = "";

        //COMPROBAR CIERRE DE CAJA
        $cierre_caja = comprobar_cierre_caja($_SESSION['id'], $conexion);
        if ($cierre_caja[0] < 6) {
            echo json_encode([$cierre_caja[1], '0']);
            return;
        }

        $conexion->autocommit(FALSE);
        try {
            //Consulta para obtener la id de la agencia
            $sql = "SELECT id_agencia FROM tb_usuario WHERE id_usu  = ?";
            $stmt1 = $conexion->prepare($sql);
            if (!$stmt1) {
                echo json_encode(["Error al ingresar los datos", '0']);
                return;
            }

            $stmt1->bind_param("i", $arch[0]);
            $dato = $stmt1->execute();

            if (!$dato) {
                $conexion->rollback();
                echo json_encode(["Error al ingresar los datos", '0']);
                return;
            }

            // Obtener el resultado de la consulta
            $stmt1->bind_result($id_agencia);
            $stmt1->fetch();
            $stmt1->close();

            //Primera consulta para insertar otr_pago 
            $sql = "INSERT INTO otr_pago (fecha, recibo, cliente, descripcion, estado, created_by, created_at, agencia) 
            VALUE (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                echo json_encode(["Error al ingresar los datos", '0']);
                return;
            }

            $stmt->bind_param("ssssiisi", $input[0], $input[1], $input[2], $input[3], $estado, $arch[0], $hoy2, $id_agencia);
            if (!$stmt->execute()) {
                $conexion->rollback();
                echo json_encode(["Error al ingresar los datos", '0']);
                return;
            }
            //Obtener la ID  de la consulta anterior 
            $idR = intval(get_id_insertado($conexion));
            //Inserta los datos en mov
            for ($fila = 0; $fila < $numF; $fila++) {
                $sql = "INSERT INTO otr_pago_mov (id_otr_tipo_ingreso, id_otr_pago, monto, created_by, created_at) 
            VALUE (?, ?, ?, ?, ?)";
                $stmt = $conexion->prepare($sql);
                if (!$stmt) {
                    echo json_encode(["Error al ingresar los datos", '0']);
                    return;
                }
                $stmt->bind_param("iidis", $matriz[$fila][0], $idR, $matriz[$fila][1], $arch[0], $hoy2);
                if (!$stmt->execute()) {
                    $conexion->rollback();
                    echo json_encode(["Error al ingresar los datos", '0']);
                    return;
                }
            }
            //INSERCIONES A CONTA
            $numpartida = getnumcom($arch[0], $conexion);
            //Determinar si es ingreso o egreso
            $ccodaux = ($arch[2] == 1) ? 'I-' : 'E-';
            $ccodaux = "OTR-" . $idR;
            $glosa = mb_strtoupper($input[3], 'utf-8');
            $data = array(
                'numcom' => $numpartida,
                'id_ctb_tipopoliza' => '8',
                'id_tb_moneda' => 1,
                'numdoc' => $input[1],
                'glosa' => $glosa,
                'fecdoc' => $input[0],
                'feccnt' => $input[0],
                'cod_aux' => $ccodaux,
                'id_tb_usu' => $arch[0],
                'fecmod' => $hoy2,
                'estado' => 1
            );

            //INGRESO AL LIBRO DE DIARIO
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $stmt3 = $conexion->prepare("INSERT INTO ctb_diario ($columns) VALUES ($placeholders)");
            if (!$stmt3) {
                throw new ErrorException("Error en la consulta 1: " . $conexion->error);
            }
            // Obtener los valores del array de datos
            $values = array_values($data);
            // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
            $types = str_repeat('s', count($values));
            // Vincular los parámetros
            $stmt3->bind_param($types, ...$values);
            if (!$stmt3->execute()) {
                throw new ErrorException("Error al ejecutar la consultar 1: " . $stmt3->error);
            }
            $id_ctb_diario = get_id_insertado($conexion);

            //CONSULTAR LA CUENTA DE CAJA
            $stmt4 = $conexion->prepare("SELECT ta.id_nomenclatura_caja AS cuentacaja FROM tb_agencia ta WHERE ta.id_agencia = ?");
            if (!$stmt4) {
                throw new ErrorException("Error en la consulta 2: " . $conexion->error);
            }
            $aux = $_SESSION['id_agencia'];
            $stmt4->bind_param("s", $aux);
            if (!$stmt4->execute()) {
                throw new ErrorException("Error al consultar 2: " . $stmt4->error);
            }
            $result = $stmt4->get_result();
            $numFilas = $result->num_rows;
            if ($numFilas < 1) {
                throw new ErrorException("No se encontro la cuenta contable de caja en la agencia ");
            }
            $resultado = $result->fetch_assoc();
            $cuentacaja = $resultado['cuentacaja'];

            //CREAR CADA UNO DE LOS MOVIMIENTOS
            foreach ($matriz as $key => $value) {
                //CONSULTAR CUENTA CONTABLE DE OTRO INGRESO
                $stmt5 = $conexion->prepare("SELECT oti.id_nomenclatura AS cuentaotro FROM otr_tipo_ingreso oti WHERE oti.id = ?");
                if (!$stmt5) {
                    throw new ErrorException("Error en la consulta 3: " . $conexion->error);
                }
                $aux = $value[0];
                $stmt5->bind_param("s", $aux);
                if (!$stmt5->execute()) {
                    throw new ErrorException("Error al consultar 3: " . $stmt5->error);
                }
                $result = $stmt5->get_result();
                $numFilas = $result->num_rows;
                if ($numFilas < 1) {
                    throw new ErrorException("No se encontro la cuenta contable para el otro ingreso");
                }
                $resultado = $result->fetch_assoc();
                $cuentaotro = $resultado['cuentaotro'];

                //PRIMERA INSERCION
                $data = array(
                    'id_ctb_diario' => $id_ctb_diario,
                    'numcom' => $numpartida,
                    'id_fuente_fondo' => 1,
                    'id_ctb_nomenclatura' => $cuentacaja,
                    'debe' => ($arch[2] == 1) ? $value[1] : 0,
                    'haber' => ($arch[2] == 1) ? 0 : $value[1],
                );

                //INGRESO AL LIBRO DE DIARIO
                $columns = implode(', ', array_keys($data));
                $placeholders = implode(', ', array_fill(0, count($data), '?'));
                $stmt6 = $conexion->prepare("INSERT INTO ctb_mov ($columns) VALUES ($placeholders)");
                if (!$stmt6) {
                    throw new ErrorException("Error en la consulta 4: " . $conexion->error);
                }
                // Obtener los valores del array de datos
                $values = array_values($data);
                // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
                $types = str_repeat('s', count($values));
                // Vincular los parámetros
                $stmt6->bind_param($types, ...$values);
                if (!$stmt6->execute()) {
                    throw new ErrorException("Error al ejecutar la consultar 4: " . $stmt6->error);
                }
                //SEGUNDA INSERCION
                $data = array(
                    'id_ctb_diario' => $id_ctb_diario,
                    'numcom' => $numpartida,
                    'id_fuente_fondo' => 1,
                    'id_ctb_nomenclatura' => $cuentaotro,
                    'debe' => ($arch[2] == 1) ? 0 : $value[1],
                    'haber' => ($arch[2] == 1) ? $value[1] : 0,
                );

                //INGRESO AL LIBRO DE DIARIO
                $columns = implode(', ', array_keys($data));
                $placeholders = implode(', ', array_fill(0, count($data), '?'));
                $stmt7 = $conexion->prepare("INSERT INTO ctb_mov ($columns) VALUES ($placeholders)");
                if (!$stmt7) {
                    throw new ErrorException("Error en la consulta 5: " . $conexion->error);
                }
                // Obtener los valores del array de datos
                $values = array_values($data);
                // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
                $types = str_repeat('s', count($values));
                // Vincular los parámetros
                $stmt7->bind_param($types, ...$values);
                if (!$stmt7->execute()) {
                    throw new ErrorException("Error al ejecutar la consultar 5: " . $stmt7->error);
                }
            }
            //Realizar el commit especifico
            $conexion->commit();
            echo json_encode(["Registro insertado correctamente", '1', $idR]);
        } catch (\ErrorException $e) {
            //Captura el error
            $mensaje_error = $e->getMessage();
            $conexion->rollback();
            echo json_encode([$mensaje_error, '0']);
        }
        $stmt->close();
        $conexion->close();
        break;
    case 'act_otrRecibo':
        $arch = $_POST['archivo'];
        $input = $_POST['inputs'];
        $mensaje_error = "";

        //comprobar
        $stmt = $conexion->prepare("SELECT CAST(created_at AS DATE) AS created_at, created_by FROM otr_pago WHERE id = ?");
        if (!$stmt) {
            echo json_encode(["Error en la consulta: " . $conexion->error, '0']);
            return;
        }
        $stmt->bind_param("s", $input[0]);
        if (!$stmt->execute()) {
            echo json_encode(["Error al ejecutar la consulta: " . $stmt->error, '0']);
            return;
        }

        $result = $stmt->get_result();
        $aux = $result->fetch_assoc();
        $fechaaux4 = $aux['created_at'];
        $usuario4 = $aux['created_by'];

        //COMPROBAR CIERRE DE CAJA
        $fechainicio = date('Y-m-d', strtotime(date('Y-m-d') . ' - 7 days'));
        $fechafin = date('Y-m-d');
        $cierre_caja = comprobar_cierre_caja($_SESSION['id'], $conexion, 1, $fechainicio, $fechafin, $fechaaux4);
        if ($cierre_caja[0] < 6) {
            echo json_encode([$cierre_caja[1], '0']);
            return;
        }

        if ($cierre_caja[0] == 8) {
            if ($usuario4 != $arch[0]) {
                echo json_encode(['El usuario creador del registro no coincide con el que quiere editar, no es posible completar la acción', '0']);
                return;
            }
        }

        $conexion->autocommit(FALSE);
        try {
            //Primera consulta para otr pago 
            $sql = "UPDATE otr_pago SET fecha = ?, recibo = ?, cliente = ?, descripcion = ?, updated_by = ?, updated_at = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                echo json_encode(["Error al ingresar los datos", '0']);
                return;
            }
            $stmt->bind_param("ssssisi", $input[1], $input[2], $input[3], $input[4], $arch[0], $hoy2, $input[0]);
            if (!$stmt->execute()) {
                $conexion->rollback();
                echo json_encode(["Error al ingresar los datos", '0']);
                return;
            }

            //Editar en movimiento contables
            $glosa = mb_strtoupper($input[4], 'utf-8');
            $data = array(
                'numdoc' => $input[2],
                'glosa' => $glosa,
                'fecdoc' => $input[1],
                'feccnt' => $input[1],
                'updated_by' => $arch[0],
                'updated_at' => $hoy2,

            );
            $id = "OTR-" . $input[0];
            //metodos de actualizacion
            // Columnas a actualizar
            $setCols = [];
            foreach ($data as $key => $value) {
                $setCols[] = "$key = ?";
            }
            $setStr = implode(', ', $setCols);
            $stmt3 = $conexion->prepare("UPDATE ctb_diario SET $setStr WHERE cod_aux = ?");
            if (!$stmt3) {
                throw new ErrorException("Error en la consulta 2: " . $conexion->error);
            }
            // Obtener los valores del array de datos
            $values = array_values($data);
            // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
            $values[] = $id; // Agregar ID al final
            $types = str_repeat('s', count($values));
            // Vincular los parámetros
            $stmt3->bind_param($types, ...$values);
            if (!$stmt3->execute()) {
                throw new ErrorException("Error en la ejecucion de la consulta 2: " . $stmt3->error);
            }

            $conexion->commit();
            echo json_encode(["Los datos se actualizaron con éxito", '1', $input[0]]);
        } catch (\ErrorException $e) {
            //Captura el error
            $mensaje_error = $e->getMessage();
            $conexion->rollback();
            echo json_encode([$mensaje_error, '0']);
        }
        $stmt->close();
        $conexion->close();
        break;
    case 'eli_otrRecibo':
        $ideliminar = $_POST['ideliminar'];
        $archivo = $_POST['archivo'];

        $conexion->autocommit(FALSE);
        try {
            //MOVIMIENTO EN CONTA
            $data = array(
                'estado' => 0,
                'deleted_by' => $archivo[0],
                'deleted_at' => $hoy2
            );
            $id = "OTR-" . $ideliminar;
            // Columnas a actualizar
            $setCols = [];
            foreach ($data as $key => $value) {
                $setCols[] = "$key = ?";
            }
            $setStr = implode(', ', $setCols);
            $stmt = $conexion->prepare("UPDATE ctb_diario SET $setStr WHERE cod_aux = ?");
            if (!$stmt) {
                throw new ErrorException("Error en la consulta 1: " . $conexion->error);
            }
            // Obtener los valores del array de datos
            $values = array_values($data);
            // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
            $values[] = $id; // Agregar ID al final
            $types = str_repeat('s', count($values));
            // Vincular los parámetros
            $stmt->bind_param($types, ...$values);
            if (!$stmt->execute()) {
                throw new ErrorException("Error en la ejecucion de la consulta 1: " . $stmt->error);
            }

            // REGISTRO DE PAGO EN RECIBOS
            $data = array(
                'estado' => 0,
                'deleted_by' => $archivo[0],
                'deleted_at' => $hoy2
            );
            $id = $ideliminar;
            // Columnas a actualizar
            $setCols = [];
            foreach ($data as $key => $value) {
                $setCols[] = "$key = ?";
            }
            $setStr = implode(', ', $setCols);
            $stmt = $conexion->prepare("UPDATE otr_pago SET $setStr WHERE id = ?");
            if (!$stmt) {
                throw new ErrorException("Error en la consulta 2: " . $conexion->error);
            }
            // Obtener los valores del array de datos
            $values = array_values($data);
            // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
            $values[] = $id; // Agregar ID al final
            $types = str_repeat('s', count($values));
            // Vincular los parámetros
            $stmt->bind_param($types, ...$values);
            if (!$stmt->execute()) {
                throw new ErrorException("Error en la ejecucion de la consulta 2: " . $stmt->error);
            }
            $conexion->commit();
            echo json_encode(["Registro eliminando correctamente", '1']);
        } catch (\ErrorException $e) {
            //Captura el error
            $mensaje_error = $e->getMessage();
            $conexion->rollback();
            echo json_encode([$mensaje_error, '0']);
        }
        $conexion->close();
        break;
    case 'eli_otrGasto1':
        $id = $_POST['ideliminar'];

        //comprobar
        $stmt = $conexion->prepare("SELECT CAST(ot.created_at AS DATE) AS created_at, ot.created_by FROM otr_pago ot INNER JOIN otr_pago_mov opm ON opm.id_otr_pago = ot.id WHERE opm.id = ?");
        if (!$stmt) {
            echo json_encode(["Error en la consulta: " . $conexion->error, '0']);
            return;
        }
        $stmt->bind_param("s", $id);
        if (!$stmt->execute()) {
            echo json_encode(["Error al ejecutar la consulta: " . $stmt->error, '0']);
            return;
        }

        $result = $stmt->get_result();
        $aux = $result->fetch_assoc();
        $fechaaux4 = $aux['created_at'];
        $usuario4 = $aux['created_by'];

        //COMPROBAR CIERRE DE CAJA
        $fechainicio = date('Y-m-d', strtotime(date('Y-m-d') . ' - 7 days'));
        $fechafin = date('Y-m-d');
        $cierre_caja = comprobar_cierre_caja($_SESSION['id'], $conexion, 1, $fechainicio, $fechafin, $fechaaux4);
        if ($cierre_caja[0] < 6) {
            echo json_encode([$cierre_caja[1], '0']);
            return;
        }

        $conexion->autocommit(false);
        //Primera consulta para otr pago 
        $sql = "DELETE FROM otr_pago_mov WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            echo json_encode(["Error al ingresar los datos", '0']);
            return;
        }
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            $conexion->rollback();
            echo json_encode(["Error al ingresar los datos", '0']);
            return;
        }
        //Obtener la ID  de la consulta anterior 
        $conexion->commit();
        echo json_encode(["Gasto eliminado ", '1']);
        $stmt->close();
        $conexion->close();
        break;
    case 'cargar_imagen_ingreso': {
            //OBTENER CODIGO DE CLIENTE
            $ccodimage = $_POST['codimage'];
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
            // $entrada = "imgcoope.sotecprotech.com/" . $folderprincipal . "/" . $ccodcli;
            $entrada = "imgcoope.microsystemplus.com/otrosingresos/" . $folderprincipal . "/" . $ccodimage;
            $rutaEnServidor = $salida . $entrada;
            $extensiones = ["jpg", "jpeg", "pjpeg", "png", "gif", "pdf"];
            foreach ($extensiones as $key => $value) {
                if (file_exists($rutaEnServidor . "/" . $ccodimage . "." . $value)) {
                    unlink($rutaEnServidor . "/" . $ccodimage . "." . $value);
                }
            }
            //comprobar si existe la ruta, si no, se crea
            if (!is_dir($rutaEnServidor)) {
                mkdir($rutaEnServidor, 0777, true);
            }

            //comprobar si se subio una imagen
            if (is_uploaded_file($_FILES['fileimg']['tmp_name'])) {
                $rutaTemporal = $_FILES['fileimg']['tmp_name'];
                //con esto la imagen siempre tendra un nombre distinto
                $nombreImagen = $ccodimage;
                $info = pathinfo($_FILES['fileimg']['name']); //extrae la extension     
                $nomimagen = '/' . $nombreImagen . "." . $info['extension'];
                $rutaDestino = $rutaEnServidor . $nomimagen;

                if (($_FILES["fileimg"]["type"] == "image/pjpeg") || ($_FILES["fileimg"]["type"] == "image/jpeg") || ($_FILES["fileimg"]["type"] == "image/jpg") || ($_FILES["fileimg"]["type"] == "image/png") || ($_FILES["fileimg"]["type"] == "image/gif") || ($_FILES["fileimg"]["type"] == "application/pdf")) {
                    if (move_uploaded_file($rutaTemporal, $rutaDestino)) {
                        $conexion->autocommit(false);
                        try {
                            $consulta2 = mysqli_query($conexion, "UPDATE `otr_pago` SET `file`='" . $entrada . $nomimagen . "' WHERE id = '" . $ccodimage . "'");
                            $aux = mysqli_error($conexion);
                            if ($aux) {
                                echo json_encode(['Error en la inserción de la ruta del archivo fallo', '0']);
                                $conexion->rollback();
                                return;
                            }
                            if (!$consulta2) {
                                echo json_encode(['Inserción de la ruta del archivo falló', '0']);
                                $conexion->rollback();
                                return;
                            }
                            $conexion->commit();
                            echo json_encode(['Archivo cargado correctamente', '1']);;
                        } catch (Exception $e) {
                            $conexion->rollback();
                            echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
                        }
                    } else {
                        echo json_encode(['Fallo al guardar el archivo, error al mover el archivo a la ruta en el servidor', '0']);
                    }
                } else {
                    echo json_encode(['La extension del archivo no es permitida, ingrese una imagen jpeg, jpg, png, gif o un archivo pdf', '0']);
                }
            }
            mysqli_close($conexion);
        }
        break;
    case 'download_file':
        //se recibe los datos
        $datos = $_POST["datosval"];
        //Informacion de datosval 
        $archivo = $datos[3];

        //consultar la ruta del archivo
        try {
            //Validar si de casualidad ya se hizo el cierre otro usuario
            $stmt = $conexion->prepare("SELECT op.file AS file FROM otr_pago op WHERE op.id = ?");
            if (!$stmt) {
                throw new ErrorException("Error en la consulta de la ruta del archivo: " . $conexion->error);
            }
            $stmt->bind_param("s", $archivo[0]); //El arroba omite el warning de php
            if (!$stmt->execute()) {
                throw new ErrorException("Error en la ejecucion de la consulta de la ruta del archivo: " . $stmt->error);
            }
            $result = $stmt->get_result();
            $numFilas = $result->num_rows;
            if ($numFilas < 1) {
                throw new ErrorException("No se encontro ningun registro");
            }
            $dato = $result->fetch_assoc();
            if ($dato['file'] == "" || $dato['file'] == null) {
                throw new ErrorException("Al registro no se le ha cargado ningun archivo");
            }
            //Envio de la imagen
            $file_path =  __DIR__ . '/../../../' . $dato['file'];
            $path_parts = pathinfo($file_path);
            $extension = $path_parts['extension'];
            $image = ["jpg", "jpeg", "pjpeg", "png", "gif"];
            $archivos = ["pdf"];

            $key = in_array($extension, $image);
            $compdata = ($key) ? "image" : "";
            if (!$key) {
                $key = in_array($extension, $archivos);
                $compdata = ($key) ? "application" : "";
            }

            ob_start();
            readfile($file_path);
            $getData = ob_get_contents();
            ob_end_clean();

            $opResult = array(
                'status' => 1,
                'mensaje' => 'Recurso descargado correctamente',
                'namefile' => $archivo[0],
                'tipo' => $extension,
                'data' => "data:$compdata/$extension;base64," . base64_encode($getData)
            );
            echo json_encode($opResult);
        } catch (\ErrorException $e) {
            //Captura el error
            $mensaje_error = $e->getMessage();
            $opResult = array(
                'status' => 0,
                'mensaje' => $mensaje_error,
                'namefile' => "download",
                'tipo' => 'pdf',
            );
            echo json_encode($opResult);
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
