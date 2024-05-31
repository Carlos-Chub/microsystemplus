<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');
date_default_timezone_set('America/Guatemala');
$hoy2 = date("Y-m-d H:i:s");
$hoy = date("Y-m-d");

$condi = $_POST["condi"];

switch ($condi) {
    case 'create_modulo':
        //valores de los inputs
        $inputs = $_POST["inputs"];
        //Selects de datos 
        $selects = $_POST["selects"];
        //validaciones de los input's
        if ($inputs[0] == "") {
            echo json_encode(['Debe llenar el campo descripción', '0']);
            return;
        }

        if ($inputs[1] == "") {
            echo json_encode(['Debe llenar el campo icono', '0']);
            return;
        }

        if ($inputs[2] == "") {
            echo json_encode(['Debe llenar el campo Ruta', '0']);
            return;
        }

        if ($selects[0] == "0") {
            echo json_encode(['Debe selecionar una rama', '0']);
            return;
        }
        if ($inputs[3] == "") {
            echo json_encode(['Debe llenar el campo orden', '0']);
            return;
        }
        if ($inputs[3] < 1) {
            echo json_encode(['El campo orden debe ser un número mayor a 0', '0']);
            return;
        }
        //insercion en la base de datos
        //realizar la insercion
        $res = $general->query("INSERT INTO `tb_modulos`(`descripcion`, `icon`, `ruta`, `rama`, `orden`, `estado`) 
            VALUES ('$inputs[0]','$inputs[1]','$inputs[2]','$selects[0]', '$inputs[3]', 1)");
        $aux = mysqli_error($general);
        if ($aux) {
            echo json_encode(['Error al momento de registrar los datos', '0']);
            // echo json_encode([$aux, '0']);
            return;
        }
        if ($res) {
            echo json_encode(['Registro satisfactorio', '1']);
        } else {
            echo json_encode(['Registro no ingresado satisfactoriamente', '0']);
        }
        mysqli_close($general);
        break;
    case 'update_modulo':
        //valores de los inputs
        $inputs = $_POST["inputs"];
        //Selects de datos 
        $selects = $_POST["selects"];
        //validaciones de los input's
        if ($inputs[3] == "") {
            echo json_encode(['No ha seleccionado un registro a editar', '0']);
            return;
        }
        if ($inputs[0] == "") {
            echo json_encode(['Debe llenar el campo descripción', '0']);
            return;
        }

        if ($inputs[1] == "") {
            echo json_encode(['Debe llenar el campo icono', '0']);
            return;
        }

        if ($inputs[2] == "") {
            echo json_encode(['Debe llenar el campo Ruta', '0']);
            return;
        }

        if ($selects[0] == "0") {
            echo json_encode(['Debe selecionar una rama', '0']);
            return;
        }
        if ($inputs[4] == "") {
            echo json_encode(['Debe llenar el campo orden', '0']);
            return;
        }
        if ($inputs[4] < 1) {
            echo json_encode(['El campo orden debe ser un número mayor a 0', '0']);
            return;
        }
        //realizar la insercion
        $res = $general->query("UPDATE `tb_modulos` set `descripcion`= '$inputs[0]', `icon`= '$inputs[1]', `ruta`= '$inputs[2]', `orden`= $inputs[4], `rama`= '$selects[0]' WHERE id='$inputs[3]'");
        $aux = mysqli_error($general);
        if ($aux) {
            echo json_encode(['Error al momento de actualizar los datos', '0']);
            // echo json_encode([$aux, '0']);
            return;
        }
        if ($res) {
            echo json_encode(['Registro actualizado satisfactoriamente', '1']);
        } else {
            echo json_encode(['Registro no actualizado satisfactoriamente', '0']);
        }
        mysqli_close($general);
        break;
    case 'delete_modulo':
        $id = $_POST['ideliminar'];
        $res = $general->query("UPDATE `tb_modulos` set `estado`= 0 WHERE id='$id'");
        $aux = mysqli_error($general);
        if ($aux) {
            echo json_encode(['Error al momento de eliminar el registro', '0']);
            // echo json_encode([$aux, '0']);
            return;
        }
        if ($res) {
            echo json_encode(['Registro eliminado satisfactoriamente', '1']);
        } else {
            echo json_encode(['Registro no eliminado satisfactoriamente', '0']);
        }
        mysqli_close($general);
        break;
    case 'create_menu':
        //valores de los inputs
        $inputs = $_POST["inputs"];
        //Selects de datos 
        $selects = $_POST["selects"];
        //validaciones de los input's
        if ($inputs[1] == "") {
            echo json_encode(['Debe seleccionar un módulo', '0']);
            return;
        }
        if ($inputs[2] == "") {
            echo json_encode(['Debe seleccionar un módulo', '0']);
            return;
        }
        if ($inputs[0] == "") {
            echo json_encode(['Debe llenar el campo descripción', '0']);
            return;
        }
        if ($inputs[3] == "") {
            echo json_encode(['Debe llenar el campo orden', '0']);
            return;
        }
        if ($inputs[3] < 1) {
            echo json_encode(['El campo orden debe ser un número mayor a 0', '0']);
            return;
        }
        //insercion en la base de datos
        $res = $general->query("INSERT INTO `tb_menus`(`id_modulo`,`descripcion`, `orden`, `estado`) 
            VALUES ('$inputs[1]','$inputs[0]',$inputs[3],1)");
        $aux = mysqli_error($general);
        if ($aux) {
            echo json_encode(['Error al momento de registrar los datos', '0']);
            // echo json_encode([$aux, '0']);
            return;
        }
        if ($res) {
            echo json_encode(['Registro satisfactorio', '1']);
        } else {
            echo json_encode(['Registro no ingresado satisfactoriamente', '0']);
        }
        mysqli_close($general);
        break;
    case 'update_menu':
        //valores de los inputs
        $inputs = $_POST["inputs"];
        //Selects de datos 
        $selects = $_POST["selects"];
        //validaciones de los input's
        if ($inputs[2] == "") {
            echo json_encode(['No ha seleccionado un registro a editar', '0']);
            return;
        }
        if ($inputs[3] == "") {
            echo json_encode(['Debe seleccionar un módulo', '0']);
            return;
        }
        if ($inputs[1] == "") {
            echo json_encode(['No ha seleccionado un registro a editar', '0']);
            return;
        }
        if ($inputs[0] == "") {
            echo json_encode(['Debe llenar el campo descripción', '0']);
            return;
        }
        if ($inputs[4] == "") {
            echo json_encode(['Debe llenar el campo orden', '0']);
            return;
        }
        if ($inputs[4] < 1) {
            echo json_encode(['El campo orden debe ser un número mayor a 0', '0']);
            return;
        }
        //realizar la insercion
        $res = $general->query("UPDATE `tb_menus` set `id_modulo`= $inputs[2], `descripcion`= '$inputs[0]', `orden`= '$inputs[4]' WHERE id='$inputs[1]'");
        $aux = mysqli_error($general);
        if ($aux) {
            echo json_encode(['Error al momento de actualizar los datos', '0']);
            // echo json_encode([$aux, '0']);
            return;
        }
        if ($res) {
            echo json_encode(['Registro actualizado satisfactoriamente', '1']);
        } else {
            echo json_encode(['Registro no actualizado satisfactoriamente', '0']);
        }
        mysqli_close($general);
        break;
    case 'delete_menu':
        $id = $_POST['ideliminar'];
        $res = $general->query("UPDATE `tb_menus` set `estado`= '0' WHERE id='$id'");
        $aux = mysqli_error($general);
        if ($aux) {
            echo json_encode(['Error al momento de eliminar el registro', '0']);
            // echo json_encode([$aux, '0']);
            return;
        }
        if ($res) {
            echo json_encode(['Registro eliminado satisfactoriamente', '1']);
        } else {
            echo json_encode(['Registro no eliminado satisfactoriamente', '0']);
        }
        mysqli_close($general);
        break;
    case 'create_submenu':
        //valores de los inputs
        $inputs = $_POST["inputs"];
        //Selects de datos 
        $selects = $_POST["selects"];
        //validaciones de los input's
        if ($inputs[0] == "" || $inputs[1] == "") {
            echo json_encode(['Debe seleccionar un menu', '0']);
            return;
        }
        if ($inputs[2] == "") {
            echo json_encode(['Debe llenar el campo condición (condi)', '0']);
            return;
        }
        if ($inputs[3] == "") {
            echo json_encode(['Debe llenar el campo archivo', '0']);
            return;
        }
        if ($inputs[4] == "") {
            echo json_encode(['Debe llenar el campo texto', '0']);
            return;
        }
        if ($selects[0] == "0") {
            echo json_encode(['Debe seleccionar el porcentaje de avance de la opción', '0']);
            return;
        }
        if ($inputs[5] == "") {
            echo json_encode(['Debe llenar el campo orden', '0']);
            return;
        }
        if ($inputs[5] < 1) {
            echo json_encode(['El campo orden debe ser un número mayor a 0', '0']);
            return;
        }
        //realizar la insercion
        $res = $general->query("INSERT INTO `tb_submenus`(`id_menu`, `condi`, `file`, `caption`, `desarrollo`, `orden`, `estado`) 
            VALUES ('$inputs[0]','$inputs[2]','$inputs[3]','$inputs[4]','$selects[0]','$inputs[5]',1)");
        $aux = mysqli_error($general);
        if ($aux) {
            echo json_encode(['Error al momento de registrar los datos', '0']);
            // echo json_encode([$aux, '0']);
            return;
        }
        if ($res) {
            echo json_encode(['Registro satisfactorio', '1']);
        } else {
            echo json_encode(['Registro no ingresado satisfactoriamente', '0']);
        }
        mysqli_close($general);
        break;
    case 'update_submenu':
        //valores de los inputs
        $inputs = $_POST["inputs"];
        //Selects de datos 
        $selects = $_POST["selects"];
        //validaciones de los input's
        if ($inputs[5] == "") {
            echo json_encode(['No ha seleccionado un registro a editar', '0']);
            return;
        }
        if ($inputs[0] == "" || $inputs[1] == "") {
            echo json_encode(['Debe seleccionar un menu', '0']);
            return;
        }
        if ($inputs[2] == "") {
            echo json_encode(['Debe llenar el campo condición (condi)', '0']);
            return;
        }
        if ($inputs[3] == "") {
            echo json_encode(['Debe llenar el campo archivo', '0']);
            return;
        }
        if ($inputs[4] == "") {
            echo json_encode(['Debe llenar el campo texto', '0']);
            return;
        }
        if ($selects[0] == "0") {
            echo json_encode(['Debe seleccionar el porcentaje de avance de la opción', '0']);
            return;
        }
        if ($inputs[6] == "") {
            echo json_encode(['Debe llenar el campo orden', '0']);
            return;
        }
        if ($inputs[6] < 1) {
            echo json_encode(['El campo orden debe ser un número mayor a 0', '0']);
            return;
        }
        //realizar la insercion
        $res = $general->query("UPDATE `tb_submenus` set `id_menu`= '$inputs[0]', `condi`= '$inputs[2]',`file`= '$inputs[3]', `caption`= '$inputs[4]',`desarrollo`= '$selects[0]', `orden`= '$inputs[6]' WHERE id='$inputs[5]'");
        $aux = mysqli_error($general);
        if ($aux) {
            echo json_encode(['Error al momento de actualizar los datos', '0']);
            // echo json_encode([$inputs, '0']);
            return;
        }
        if ($res) {
            echo json_encode(['Registro actualizado satisfactoriamente', '1']);
        } else {
            echo json_encode(['Registro no actualizado satisfactoriamente', '0']);
        }
        mysqli_close($general);
        break;
    case 'delete_submenu':
        $id = $_POST['ideliminar'];
        $res = $general->query("UPDATE `tb_submenus` set `estado`= '0' WHERE id='$id'");
        $aux = mysqli_error($general);
        if ($aux) {
            echo json_encode(['Error al momento de eliminar el registro', '0']);
            // echo json_encode([$aux, '0']);
            return;
        }
        if ($res) {
            echo json_encode(['Registro eliminado satisfactoriamente', '1']);
        } else {
            echo json_encode(['Registro no eliminado ', '0']);
        }
        mysqli_close($general);
        break;

        case 'create_permiso':
            $name = $_POST['nombre'];
            $estado = $_POST['estado'];
            
            if (isset($name, $estado)) {
                $query_check = " SELECT COUNT(*) as count FROM clhpzzvb_bd_general_coopera.tb_restringido WHERE `modulo_area` = ?";
                $stmt_check = $general->prepare($query_check);
                $stmt_check->bind_param("s", $name);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                $row_check = $result_check->fetch_assoc();
            
                if ($row_check['count'] > 0) {
                    echo json_encode(['El modulo ya existe', '0']);
                } else {
                    $query_insert = "INSERT INTO clhpzzvb_bd_general_coopera.tb_restringido (`modulo_area`, `estado`) VALUES (?, ?)";
                    $stmt_insert = $general->prepare($query_insert);
                    $stmt_insert->bind_param("ss", $name, $estado);
                    $success = $stmt_insert->execute();
            
                    if (!$success) {
                        $error = $stmt_insert->error;
                        echo json_encode([$error, '0']);
                    } else {
                        echo json_encode(['Registrado', '1']);
                    }
                }
            } else {
                echo json_encode(['Faltan datos', '0']);
            }
            break;
            case 'update_permiso':
                $update_estado = $_POST['update_estado'];
                $estado = $_POST['estado'];

                if (isset($update_estado, $estado)) {
                    $query_update = "UPDATE clhpzzvb_bd_general_coopera.tb_restringido SET estado = ? WHERE modulo_area = ?";
                    $stmt_update = $general->prepare($query_update);
                    $stmt_update->bind_param("ss", $estado, $update_estado);
                    $success = $stmt_update->execute();
            
                    if (!$success) {
                        $error = $stmt_update->error;
                        echo json_encode([$error, '0']);
                    } else {
                        echo json_encode(['Actualizado', '1']);
                    }
                } else {
                    echo json_encode(['Faltan datos', '0']);
                }
                break;
        case 'create_permisos':

            $id = $_POST['id'];
            $id_cargo = $_POST['id_cargo'];
            $update_estado = $_POST['update_estado'];
            $estado = $_POST['estado'];

            if (isset($id, $update_estado, $estado, $id_cargo)) {
                //verifi que no se repita
                $query_check = "SELECT COUNT(*) AS count FROM tb_autorizacion WHERE id_usuario = ? AND id_rol = ? AND id_restringido = ? AND estado = ?";
                $stmt_check = mysqli_prepare($conexion, $query_check);
                mysqli_stmt_bind_param($stmt_check, "iiii", $id, $id_cargo, $update_estado, $estado);
                mysqli_stmt_execute($stmt_check);
                mysqli_stmt_bind_result($stmt_check, $count);
                mysqli_stmt_fetch($stmt_check);
                mysqli_stmt_close($stmt_check);

                if ($count > 0) {
                    echo json_encode(['El registro ya existe', '0']);
                } else {
                    // Insertar
                    $query_insert = "INSERT INTO tb_autorizacion (id_usuario, id_rol, id_restringido, estado) VALUES (?, ?, ?, ?)";
                    $stmt_insert = mysqli_prepare($conexion, $query_insert);
                    mysqli_stmt_bind_param($stmt_insert, "iiii", $id, $id_cargo, $update_estado, $estado);
                    $success = mysqli_stmt_execute($stmt_insert);
                    mysqli_stmt_close($stmt_insert);

                    if ($success) {
                        echo json_encode(['Datos ingresados ✔ ', '1']);
                    } else {
                        echo json_encode(['Error al insertar en la base de datos', '0']);
                    }
                }
            } else {
                echo json_encode(['ERROR', '0']);
            }
        break;
        case 'search_id':
            if (!$conexion) {
                die("Error de conexión: " . mysqli_connect_error());
            }
            $id = isset($_POST['id']) ? $_POST['id'] : '';
        
            if ($id != '') {
                $query_insert = "SELECT 
                                    ta.id_usuario AS id_autorizacion, 
                                    ta.estado, 
                                    tr.modulo_area
                                FROM tb_autorizacion AS ta
                                INNER JOIN `clhpzzvb_bd_general_coopera`.tb_restringido AS tr ON ta.id_restringido = tr.id
                                WHERE  ta.id_usuario = $id ";

                $result = mysqli_query($conexion, $query_insert);
            
                if ($result) {
                    $data = array();
                    while ($row = mysqli_fetch_assoc($result)) {
                        $data[] = $row;
                    }
                
                    // Procesar los datos del checkbox
                    if(isset($_POST['checked_values'])) {
                        foreach ($_POST['checked_values'] as $item) {
                            $modulo_checkbox = $item[0];
                            $id_autorizacion = $item[1];
                        }
                    }
                    $table_html = '<table class="table"><h3>Permisos Asignados</h3>';
                    $table_html .= '<thead class="thead-dark">';
                    $table_html .= '<tr>';
                    $table_html .= '<th>#</th>'; 
                    $table_html .= '<th>Estado</th>';
                    $table_html .= '<th>Módulo Área</th>';
                    $table_html .= '</tr>';
                    $table_html .= '</thead>';
                    $table_html .= '<tbody>';
                    $count = 1; 

                    foreach ($data as $row) {
                        $estado_checked = ($row['estado'] == 1) ? 'checked' : '';
                        $estado_switch = ($row['estado'] == 1) ? 'checked' : ''; // Estado 
                        $table_html .= '<tr>';
                        $table_html .= '<td>' . $count++ . '</td>'; 
                        $table_html .= '<td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" ' . $estado_switch . ' id="switch' . $row['id_autorizacion'] . '" onclick="updateEstado(\'update_estado\',' . $row['id_autorizacion'] . ', this.checked, \'' . $row['modulo_area'] . '\')">
                                                <label class="form-check-label" for="switch' . $row['id_autorizacion'] . '"></label>
                                            </div>
                                        </td>';
                        $table_html .= '<td>' . $row['modulo_area'] . '</td>';
                        $table_html .= '</tr>';
                    }
                    $table_html .= '</tbody>';
                    $table_html .= '</table>';

                    echo $table_html;
                    ?>                               
                    </div>
                    <?php
                } else {
                    echo "Error al ejecutar la consulta: " . mysqli_error($conexion);
                }
            } else {
                echo "No se recibió el valor de 'id'";
            }
        break;
        case 'update_estado':
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                if (isset($_POST['id_autorizacion'], $_POST['modulo_area'], $_POST['estado_convertido'])) {
                    $id = $_POST['id_autorizacion'];
                    $modulo = $_POST['modulo_area'];
                    $estado = $_POST['estado_convertido'];
        
 
                    $query_update = "UPDATE tb_autorizacion AS ta
                                     INNER JOIN `clhpzzvb_bd_general_coopera`.tb_restringido AS tr ON ta.id_restringido = tr.id
                                     SET ta.estado = '$estado'
                                     WHERE ta.id_usuario = '$id' AND tr.modulo_area = '$modulo'";
        
                    // execute consulta
                    $result = mysqli_query($conexion, $query_update);
        
                    if ($result) {
                        echo json_encode(['Simon ', '1']); 
                    } else {
                        echo json_encode(["Error: No se pudo actualizar", "0"]);
                    }
                } else {
                    echo json_encode(["Error: Datos incorrectos", "0"]);
                }
            } else {
                echo json_encode(["Error: Solicitud incorrecta", "0"]);
            }
        break;                
}