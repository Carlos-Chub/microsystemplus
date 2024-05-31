<?php
session_start();
// ESTE CRUD ES PARA TODO EL MENU DE CLIENTES 
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');
date_default_timezone_set('America/Guatemala');
include '../../src/funcphp/func_gen.php';
include '../../src/funcphp/func_json_cli.php';
$hoy = date("Y-m-d");
$hoy2 = date("Y-m-d H:i:s");
$condi = $_POST["condi"];
$idusuario = $_SESSION['id'];
switch ($condi) {
  case 'cargar_imagen': {
      //OBTENER CODIGO DE CLIENTE
      $ccodcli = $_POST['codcli'];
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
      $entrada = "imgcoope.microsystemplus.com/" . $folderprincipal . "/" . $ccodcli;
      $rutaEnServidor = $salida . $entrada;
      $extensiones = ["jpg", "jpeg", "pjpeg", "png", "gif"];
      foreach ($extensiones as $key => $value) {
        if (file_exists($rutaEnServidor . "/" . $ccodcli . "." . $value)) {
          unlink($rutaEnServidor . "/" . $ccodcli . "." . $value);
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
        // $nombreImagen = $ccodcli . '' . date('Ymdhis'); //asignar nuevo nombre
        $nombreImagen = $ccodcli;
        $info = pathinfo($_FILES['fileimg']['name']); //extrae la extension     
        $nomimagen = '/' . $nombreImagen . "." . $info['extension'];
        $rutaDestino = $rutaEnServidor . $nomimagen;

        if (($_FILES["fileimg"]["type"] == "image/pjpeg") || ($_FILES["fileimg"]["type"] == "image/jpeg") || ($_FILES["fileimg"]["type"] == "image/jpg") || ($_FILES["fileimg"]["type"] == "image/png") || ($_FILES["fileimg"]["type"] == "image/gif")) {
          if (move_uploaded_file($rutaTemporal, $rutaDestino)) {
            $conexion->autocommit(false);
            try {
              $consulta2 = mysqli_query($conexion, "UPDATE `tb_cliente` SET `url_img`='" . $entrada . $nomimagen . "' WHERE idcod_cliente = '" . $ccodcli . "'");
              $aux = mysqli_error($conexion);
              if ($aux) {
                echo json_encode(['Error en la inserción de la ruta de la foto', '0']);
                $conexion->rollback();
                return;
              }
              if (!$consulta2) {
                echo json_encode(['Inserción de la ruta de la foto falló', '0']);
                $conexion->rollback();
                return;
              }
              $conexion->commit();
              echo json_encode(['Foto de cliente cargado correctamente', '1']);;
            } catch (Exception $e) {
              $conexion->rollback();
              echo json_encode(['Error al ingresar: ' . $e->getMessage(), '0']);
            }
          } else {
            echo json_encode(['Fallo al guardar la imagen, error al mover la imagen a la ruta en el servidor', '0']);
          }
        } else {
          echo json_encode(['La extension de la imagen no es permitida, ingrese una imagen jpeg, jpg, png o gif', '0']);
        }
      }
      mysqli_close($conexion);
    }
    break;
  case 'consultaCre':
    $cnt = $_POST["cnt"];
    mysqli_query($conexion, "SELECT * FROM cremcre_meta cm INNER JOIN tb_grupo gp ON cm.CCodGrupo=gp.id_grupos INNER JOIN tb_cliente_tb_grupo cgp ON gp.id_grupos=cgp.Codigo_grupo WHERE (cm.Cestado='F' OR cm.Cestado='A' OR cm.Cestado='D' OR cm.Cestado='E') AND cm.CCodGrupo=" . $cnt);
    $info =  mysqli_affected_rows($conexion);
    mysqli_query($conexion, "SELECT estadoGrupo FROM tb_grupo WHERE estado = 1 AND estadoGrupo = 'A' AND id_grupos =" . $cnt);
    $info1 =  mysqli_affected_rows($conexion);
    echo $info . '  ' . $info1;
    if ($info >= 1 && $info = 0) {
      echo 1;
    } else {
      echo 0;
    }
    break;
    /*------------------ INSERTAR DATOS DE GRUPOS  --------------------------------------------------------------- */
  case 'grptable':
    $cnt = $_POST["cnt"];
    $i = 1;
    mysqli_query($conexion, "SELECT * FROM cremcre_meta cm INNER JOIN tb_grupo gp ON cm.CCodGrupo=gp.id_grupos INNER JOIN tb_cliente_tb_grupo cgp ON gp.id_grupos=cgp.Codigo_grupo WHERE (cm.Cestado='F' OR cm.Cestado='A' OR cm.Cestado='D' OR cm.Cestado='E') AND cm.CCodGrupo=" . $cnt);
    $info =  mysqli_affected_rows($conexion);
    $control = 0;
    if ($info >= 1) {
      $control = 1;
    }
    mysqli_query($conexion, "SELECT estadoGrupo FROM tb_grupo WHERE estado = 1 AND estadoGrupo = 'A' AND id_grupos =" . $cnt);
    $estadoGupo =  mysqli_affected_rows($conexion);
    $consulta = mysqli_query($conexion, "SELECT idcod_cliente, short_name, no_identifica, date_birth, id_grupo  FROM tb_cliente INNER JOIN tb_cliente_tb_grupo ON tb_cliente.idcod_cliente = tb_cliente_tb_grupo.cliente_id WHERE Codigo_grupo = $cnt AND tb_cliente_tb_grupo.estado = 1");
    while ($grpclt = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
      $id = $grpclt["idcod_cliente"];
      $short_name = ($grpclt["short_name"]);
      $no_identifica = ($grpclt["no_identifica"]);
      $date_birth = ($grpclt["date_birth"]);
      $idgrptabl = $grpclt["id_grupo"];

      $info = ($estadoGupo == 1) ? '<button type="button" class="btn btn-default" title="Eliminar" onclick="dltgrpcli(&apos;' . $id . '&apos;, &apos;' . $idgrptabl . '&apos;)"> <i class="fa-solid fa-trash-can"></i></button>' : (($estadoGupo == 0 && $control == 1) ? '<button type="button" class="btn btn-default" title="No se puede eliminar" onclick="msjCreditosPen()"><i class="fa-solid fa-lock"></i></button>' : '<button type="button" class="btn btn-default" title="No se puede eliminar" onclick="msjCreditosPen()"><i class="fa-solid fa-lock"></i></button>');
      echo '
    <tr>
        <td>' . $i . '</td>
        <td>' . $id . '</td>
        <td>' . $short_name . '</td>
        <td>' . $no_identifica . '</td>
        <td>' . $date_birth . '</td>
        <td>' . $info . '</td>
      </tr>';
      $i++;
    }
    mysqli_close($conexion);
    break;

    /*--------------------------------------------------------------------------------- */
  case 'instupftgrp':
    $cnt = $_POST["cnt"]; // opcion 0 insert, diferente de 0 para actualizar 
    $grpinptval2 = $_POST["grpinptval2"];
    $depa = $_POST["depa"];
    $muni = $_POST["muni"];
    $usuario = $_POST["usuario"];
    if ($cnt == "0") {
      //$id = date("dmyhis");// Lo usan para, generar codigo 
      $est = 0;
      $caracteres = '0123456789';
      $codigo;
      while ($est != 1) {
        $codigo = '';
        $max = strlen($caracteres) - 1;
        for ($i = 0; $i < 5; $i++) {
          $codigo .= $caracteres[mt_rand(0, $max)];
        }
        $cod = intval($codigo);

        $validarRep = $conexion->query("SELECT EXISTS(SELECT * FROM tb_grupo WHERE codigo_grupo = $codigo ) AS Resultado");
        // Si la consulta fue exitosa
        $resultado = $validarRep->fetch_assoc()['Resultado'];
        if ($resultado == 0) {
          $est = 1;
        } //Fin validad repetidos
      }
      // inserta el grupo generar el cod del grupo 
      // $consulta = mysqli_query($conexion, "INSERT INTO `tb_grupo`(`id_grupos`, `codigo_grupo`, `NombreGrupo`, `fecha_sys`, `Depa`, `Muni`, `canton`, `direc`) VALUES ('','$id','".$grpinptval2[0]."','".$grpinptval2[5]."','$depa','$muni','".$grpinptval2[1]."','".$grpinptval2[2]."')");

      // $validarRep = $conexion->query("SELECT EXISTS(SELECT * FROM tb_grupo WHERE NombreGrupo = '$grpinptval2[0]' ) AS Resultado");
      // // Si la consulta fue exitosa
      // $resultado = $validarRep->fetch_assoc()['Resultado'];
      // if ($resultado == 1) {
      //   echo 'El nombre del grupo que ingreso ya existe en el sistema.';
      //   return;
      // } //Fin validad repetidos
      $consulta = mysqli_query($conexion, "INSERT INTO `tb_grupo`(`codigo_grupo`, `NombreGrupo`, `fecha_sys`, `Depa`, `Muni`, `canton`, `direc`, `estado`, `created_by`, `created_at`,`estadoGrupo`, `close_by`, `close_at`) VALUES ($codigo,'$grpinptval2[0]','$hoy','$depa','$muni','$grpinptval2[1]','$grpinptval2[2]',1,$usuario,'$hoy2', 'A', $usuario, '$hoy2')");
      //  echo json_encode([$codigo, " - ".$consulta]); 
      if (!$consulta) {
        //  echo "Error al INGRESAR".mysqli_error();
        //echo "Error al INGRESAR";
      } else {
        echo "GRUPO INGRESADO";
      }
    } else {
      //echo $usuario." - ".$hoy2." - ".$cnt; return; 
      // REALIZA UN UDATE
      // $consulta2 = mysqli_query($conexion, "UPDATE `tb_grupo` SET `NombreGrupo`='" . $grpinptval2[0] . "',`fecha_sys`='" . $hoy2 . "',`Depa`='$depa',`Muni`='$muni',`canton`='" . $grpinptval2[1] . "',`direc`='" . $grpinptval2[2] . "','update_at ='" . $usuario .", updated_at = '".$hoy2."' WHERE id_grupos = $cnt ");
      // $validarRep = $conexion->query("SELECT EXISTS(SELECT * FROM tb_grupo WHERE NombreGrupo = '$grpinptval2[0]' AND id_grupos != $cnt) AS Resultado");
      // // Si la consulta fue exitosa
      // $resultado = $validarRep->fetch_assoc()['Resultado'];
      // if ($resultado == 1) {
      //   echo 'El nombre del grupo que ingreso ya existe en el sistema.';
      //   return;
      // } //Fin validad repetidos
      $consulta2 = mysqli_query($conexion, "UPDATE `tb_grupo` SET `NombreGrupo`='$grpinptval2[0]', `fecha_sys`='$hoy2', `Depa`='$depa', `Muni`='$muni', `canton`='$grpinptval2[1]', `direc`='$grpinptval2[2]', `updated_by`='$usuario', `updated_at`='$hoy2' WHERE `id_grupos` = $cnt");

      if (!$consulta2) {
        // echo "Error al Actualizar".mysqli_error();
        echo "Error al Actualizar";
      } else {
        echo "GRUPO ACTUALIZADO";
      }
    }
    break;

    /*--------------------------------------------------------------------------------- */
  case 'instclntingrp':
    $idcln = $_POST["cln"]; //id del cliente
    $idgrp = $_POST["cnt"]; //id del grupo   
    $usuario = $_POST["usuario"];
    //Validar si el codigo de usuario ya existe en en el sistema
    $validarRep = $conexion->query("SELECT EXISTS(SELECT * FROM tb_cliente_tb_grupo WHERE cliente_id = $idcln) AS Resultado");
    //Recoger el resultado 
    $resultado = $validarRep->fetch_assoc()['Resultado'];
    //Si el usuario ya existe en el sistema por lo tanto, se tiene validar su estado
    if ($resultado == 1) {
      $validarRep = $conexion->query("SELECT EXISTS(SELECT estado FROM tb_cliente_tb_grupo WHERE cliente_id = '$idcln' AND estado = 1) AS Resultado;");
      $resultado = $validarRep->fetch_assoc()['Resultado'];
      if ($resultado == 1) {
        echo "El cliente ya pertenece a un grupo";
        return;
      }
    }
    // $insertamela = mysqli_query($conexion, "INSERT INTO `tb_cliente_tb_grupo` (`Codigo_grupo`, `cliente_id`,'estado','created_by','created_at') VALUES ('$idgrp', '$idcln',1,'$usuario','$hoy2');");
    //Validar si el grupo esta abierto
    $validarRep = $conexion->query("SELECT EXISTS(SELECT * FROM tb_grupo WHERE estadoGrupo = 'A' AND id_grupos = $idgrp) AS Resultado");
    //Recoger el resultado 
    $resultado = $validarRep->fetch_assoc()['Resultado'];
    // $resultado = 1; //--REQ--fape--1--sin restricciones para agregar integrantes a grupo
    if ($resultado == 0) {
      echo "En el grupo ya no se pueden ingresar clientes, por que se encuentra cerrado";
      return;
    }
    $insertamela = mysqli_query($conexion, "INSERT INTO `tb_cliente_tb_grupo` (`Codigo_grupo`, `cliente_id`, estado, created_by, created_at) VALUES ('$idgrp', '$idcln', 1, '$usuario', '$hoy2')");

    if (!$insertamela) {
      // echo "Error al Actualizar".mysqli_error();
      echo "Error al Ingresar el Cliente";
    } else {
      echo "Cliente Agregado";
    }
    mysqli_close($conexion);
    break;
    /* ----------------------------------------------------------------------------------*/
  case 'cerrarGrupo':
    $idgrp = $_POST["cnt"]; //id del grupo   
    $usuario = $_POST["usuario"];

    mysqli_query($conexion, "SELECT *FROM tb_cliente_tb_grupo WHERE Codigo_grupo = $idgrp AND estado = 1");
    $info =  mysqli_affected_rows($conexion);
    //echo $info.' ---  '.$idgrp; return ; 
    if ($info == 0) {
      echo "Para cerrar el grupo, tiene que ingresar clientes";
      return;
    }

    $consulta = mysqli_query($conexion, "UPDATE tb_grupo SET estadoGrupo = 'C', open_by =  $usuario, open_at = '$hoy2' WHERE id_grupos =" . $idgrp);
    if (!$consulta) {
      // echo "Error al Actualizar".mysqli_error();
      echo "Error al Eliminar";
    } else {
      echo "Grupo cerrado";
    }

    mysqli_close($conexion);

    break;
    /* ----------------------------------------------------------------------------------*/
  case 'abrirGrupo':
    $idgrp = $_POST["cnt"]; //id del grupo   
    $usuario = $_POST["usuario"];

    mysqli_query($conexion, "SELECT * FROM cremcre_meta cm INNER JOIN tb_grupo gp ON cm.CCodGrupo=gp.id_grupos INNER JOIN tb_cliente_tb_grupo cgp ON gp.id_grupos=cgp.Codigo_grupo WHERE (cm.Cestado='F' OR cm.Cestado='A' OR cm.Cestado='D' OR cm.Cestado='E') AND cm.CCodGrupo=" . $idgrp);

    $info =  mysqli_affected_rows($conexion);
    $control = 0;
    // $info=0; //--REQ--fape--1--sin restricciones para agregar integrantes a grupo
    if ($info >= 1) {
      echo "El grupo está en un ciclo de crédito, razón por la cual no se puede abrir. ";
      return;
    }

    $consulta = mysqli_query($conexion, "UPDATE tb_grupo SET estadoGrupo = 'A', close_by =  $usuario, close_at = '$hoy2' WHERE id_grupos =" . $idgrp);
    if (!$consulta) {
      // echo "Error al Actualizar".mysqli_error();
      echo "Error al Eliminar";
    } else {
      echo "El grupo esta abierto";
    }

    mysqli_close($conexion);

    break;

    /*--------------------------------------------------------------------------------- */
  case 'dltclntgrp': //ELIMINAR CLIENTE DEL GURPO...
    $idclnt = $_POST["id"];
    $idgrptabl = $_POST["nme"];
    $usuario = $_POST["usuario"];

    mysqli_query($conexion, "SELECT * FROM cremcre_meta cm
    INNER JOIN tb_grupo gp ON cm.CCodGrupo=gp.id_grupos
    INNER JOIN tb_cliente_tb_grupo cgp ON gp.id_grupos=cgp.Codigo_grupo
    WHERE (cm.Cestado='F' OR cm.Cestado='A' OR cm.Cestado='D' OR cm.Cestado='E') AND cm.CodCli=" . $idclnt);

    $info =  mysqli_affected_rows($conexion);

    if ($info >= 1) {
      echo "El cliente no se puede eliminar, tiene créditos pendientes.";
      return;
    }
    // $consulta = mysqli_query($conexion, "DELETE FROM `tb_cliente_tb_grupo` WHERE `tb_cliente_tb_grupo`.`id_grupo` = $idgrptabl");
    $consulta = mysqli_query($conexion, "UPDATE tb_cliente_tb_grupo set estado = 0, deleted_by = $usuario, deleted_at = '$hoy2' WHERE id_grupo=" . $idgrptabl);
    if (!$consulta) {
      // echo "Error al Actualizar".mysqli_error();
      echo "Error al Eliminar";
    } else {
      echo "Cliente ELIMINADO DEL GRUPO";
    }

    mysqli_close($conexion);
    break;

    /*--------------------------------------------------------------------------------- */
  case 'dltgrp':
    $idgrp = $_POST["cnt"];
    $usuario = $_POST['usuario'];
    //echo "GRUPO ELIMINADO";
    // $consulta = mysqli_query($conexion, "DELETE FROM `tb_grupo` WHERE `tb_grupo`.`id_grupos` = $idgrp");
    mysqli_query($conexion, "SELECT * FROM cremcre_meta cm INNER JOIN tb_grupo gp ON cm.CCodGrupo=gp.id_grupos INNER JOIN tb_cliente_tb_grupo cgp ON gp.id_grupos=cgp.Codigo_grupo WHERE (cm.Cestado='F' OR cm.Cestado='A' OR cm.Cestado='D' OR cm.Cestado='E') AND cm.CCodGrupo=" . $idgrp);
    $info =  mysqli_affected_rows($conexion);

    if ($info >= 1) {
      echo "No se puede eliminar, el grupo tiene creditos pendientes";
      return;
    }
    mysqli_query($conexion, "SELECT *FROM tb_cliente_tb_grupo WHERE Codigo_grupo = " . $idgrp . " AND estado = 1; ");
    $info =  mysqli_affected_rows($conexion);

    if ($info >= 1) {
      echo "Para eliminar el grupo primero tiene que eliminar a los clientes...";
      return;
    }

    $consulta = mysqli_query($conexion, "UPDATE tb_grupo SET estado = 0, deleted_by = $usuario, deleted_at = '$hoy2' WHERE id_grupos=" . $idgrp);

    if (!$consulta) {
      echo "Error al Eliminar";
    } else {
      echo "GRUPO ELIMINADO";
    }
    mysqli_close($conexion);
    break;
  case 'create_balance_economico':
    //validar todos los campos necesarios
    $inputs = $_POST["inputs"];
    $selects = $_POST["selects"];
    $archivo = $_POST["archivo"];

    $validar = validar_campos_plus([
      [$inputs[0], "", 'Debe seleccionar un cliente', 1],
      [$inputs[1], "", 'Debe seleccionar un cliente', 1],
      [$inputs[16], "", 'Debe digitar una fecha de evaluación', 1],
      [$inputs[17], "", 'Debe digitar una fecha de balance', 1],
      [$inputs[2], "", 'Digite un número para el campo ventas', 1],
      [$inputs[2], 0, 'Digite un número no negativo para el campo ventas', 2],
      [$inputs[3], "", 'Digite un número para el campo recuperación de cuentas por cobrar', 1],
      [$inputs[3], 0, 'Digite un número no negativo para el campo recuperación de cuentas por cobrar', 2],
      [$inputs[4], "", 'Digite un número para el campo compra de mercadería', 1],
      [$inputs[4], 0, 'Digite un número no negativo para el campo compra de mercadería', 2],
      [$inputs[5], "", 'Digite un número para el campo gastos del negocio', 1],
      [$inputs[5], 0, 'Digite un número no negativo para el campo gastos del negocio', 2],
      [$inputs[6], "", 'Digite un número para el campo pagos de créditos', 1],
      [$inputs[6], 0, 'Digite un número no negativo para el campo pagos de créditos', 2],
      [$inputs[7], "", 'Digite un número para el campo disponible', 1],
      [$inputs[7], 0, 'Digite un número no negativo para el campo disponible', 2],
      [$inputs[8], "", 'Digite un número para el campo cuentas por cobrar', 1],
      [$inputs[8], 0, 'Digite un número no negativo para el campo cuentas por cobrar', 2],
      [$inputs[9], "", 'Digite un número para el campo inventario', 1],
      [$inputs[9], 0, 'Digite un número no negativo para el campo inventario', 2],
      [$inputs[10], "", 'Digite un número para el campo activo fijo', 1],
      [$inputs[10], 0, 'Digite un número no negativo para el campo activo fijo', 2],
      [$inputs[11], "", 'Digite un número para el campo proveedores', 1],
      [$inputs[11], 0, 'Digite un número no negativo para el campo proveedores', 2],
      [$inputs[12], "", 'Digite un número para el campo otros préstamos', 1],
      [$inputs[12], 0, 'Digite un número no negativo para el campo otros préstamos', 2],
      [$inputs[13], "", 'Digite un número para el campo préstamos a instituciones', 1],
      [$inputs[13], 0, 'Digite un número no negativo para el campo préstamos a instituciones', 2],
      [$inputs[14], "", 'Digite un número para el campo patrimonio', 1],
      [$inputs[14], 0, 'Digite un número no negativo para el campo patrimonio', 2],
      // [$inputs[15], 0, 'El campo saldo debe ser igual a 0, cuadre los montos', 2],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }

    //sumar activo y pasivo
    if (($inputs[7] + $inputs[8] + $inputs[9] + $inputs[10]) != ($inputs[11] + $inputs[12] + $inputs[13] + $inputs[14])) {
      echo json_encode(['La suma de saldo debe ser igual a 0, cuadre los montos', '0']);
      return;
    }

    // //VAMOS A CONSULTAR SI NO HAY UNO EXISTENTE
    // $consulta = mysqli_query($conexion, "SELECT id_tb_cli_balance FROM tb_cliente cl INNER JOIN tb_cli_balance clb ON cl.id_tb_cli_balance=clb.id WHERE `idcod_cliente`='$inputs[0]'");
    // $aux = mysqli_error($conexion);
    // if ($aux) {
    //   echo json_encode(['Error en encontrar el id de balance económico', '0']);
    //   return;
    // }
    // if (!$consulta) {
    //   echo json_encode(['Fallo al encontrar el id de balance económico', '0']);
    //   return;
    // }
    // $banderabalance = true;
    // while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
    //   $banderabalance = false;
    // }
    // if (!$banderabalance) {
    //   echo json_encode(['Ya se tiene registrado un balance económico correspondiente al cliente', '0']);
    //   return;
    // }

    //VAMOS A REALIZAR LA INSERCION EN LA TABLA DE BALANCES
    $res = $conexion->query("INSERT INTO `tb_cli_balance`(`ccodcli`,`fechaeval`,`fechabalance`,`ventas`,`cuenta_por_cobrar`,`mercaderia`,`negocio`,`pago_creditos`,`disponible`,`cuenta_por_cobrar2`,`inventario`,`activo_fijo`,`proveedores`,`otros_prestamos`,`prest_instituciones`,`patrimonio`,`created_at`,`created_by`) VALUES ('$inputs[0]','$inputs[16]','$inputs[17]','$inputs[2]', '$inputs[3]','$inputs[4]','$inputs[5]','$inputs[6]','$inputs[7]','$inputs[8]','$inputs[9]','$inputs[10]','$inputs[11]','$inputs[12]','$inputs[13]','$inputs[14]','$hoy2','$archivo[0]')");
    $aux = mysqli_error($conexion);
    $balance_insert = get_id_insertado($conexion);
    if ($aux) {
      echo json_encode(['Error en la inserción del balance económico' . $aux, '0']);
      return;
    }
    if (!$res) {
      echo json_encode(['No se logro insertar el balance económico', '0']);
      return;
    }

    // //VAMOS A REALIZAR LA ACTUALIZACION
    // $res = $conexion->query("UPDATE `tb_cliente` SET `id_tb_cli_balance`='$balance_insert', `fecha_mod`='$hoy' WHERE `idcod_cliente`='$inputs[0]'");
    // $aux = mysqli_error($conexion);
    // if ($aux) {
    //   echo json_encode(['Error en la actualización del cliente', '0']);
    //   return;
    // }
    // if (!$res) {
    //   echo json_encode(['No se logro actualizar datos del cliente', '0']);
    //   return;
    // }
    echo json_encode(['Balance económico registrado satisfactoriamente', '1']);
    mysqli_close($conexion);
    break;
  case 'update_balance_economico':
    //validar todos los campos necesarios
    $inputs = $_POST["inputs"];
    $selects = $_POST["selects"];
    $archivo = $_POST["archivo"];

    $validar = validar_campos_plus([
      [$inputs[0], "", 'Debe seleccionar un cliente', 1],
      [$inputs[1], "", 'Debe seleccionar un cliente', 1],
      [$inputs[16], "", 'Debe digitar una fecha de evaluación', 1],
      [$inputs[17], "", 'Debe digitar una fecha de balance', 1],
      [$inputs[2], "", 'Digite un número para el campo ventas', 1],
      [$inputs[2], 0, 'Digite un número no negativo para el campo ventas', 2],
      [$inputs[3], "", 'Digite un número para el campo recuperación de cuentas por cobrar', 1],
      [$inputs[3], 0, 'Digite un número no negativo para el campo recuperación de cuentas por cobrar', 2],
      [$inputs[4], "", 'Digite un número para el campo compra de mercadería', 1],
      [$inputs[4], 0, 'Digite un número no negativo para el campo compra de mercadería', 2],
      [$inputs[5], "", 'Digite un número para el campo gastos del negocio', 1],
      [$inputs[5], 0, 'Digite un número no negativo para el campo gastos del negocio', 2],
      [$inputs[6], "", 'Digite un número para el campo pagos de créditos', 1],
      [$inputs[6], 0, 'Digite un número no negativo para el campo pagos de créditos', 2],
      [$inputs[7], "", 'Digite un número para el campo disponible', 1],
      [$inputs[7], 0, 'Digite un número no negativo para el campo disponible', 2],
      [$inputs[8], "", 'Digite un número para el campo cuentas por cobrar', 1],
      [$inputs[8], 0, 'Digite un número no negativo para el campo cuentas por cobrar', 2],
      [$inputs[9], "", 'Digite un número para el campo inventario', 1],
      [$inputs[9], 0, 'Digite un número no negativo para el campo inventario', 2],
      [$inputs[10], "", 'Digite un número para el campo activo fijo', 1],
      [$inputs[10], 0, 'Digite un número no negativo para el campo activo fijo', 2],
      [$inputs[11], "", 'Digite un número para el campo proveedores', 1],
      [$inputs[11], 0, 'Digite un número no negativo para el campo proveedores', 2],
      [$inputs[12], "", 'Digite un número para el campo otros préstamos', 1],
      [$inputs[12], 0, 'Digite un número no negativo para el campo otros préstamos', 2],
      [$inputs[13], "", 'Digite un número para el campo préstamos a instituciones', 1],
      [$inputs[13], 0, 'Digite un número no negativo para el campo préstamos a instituciones', 2],
      [$inputs[14], "", 'Digite un número para el campo patrimonio', 1],
      [$inputs[14], 0, 'Digite un número no negativo para el campo patrimonio', 2],
      // [$inputs[15], 0, 'El campo saldo debe ser igual a 0, cuadre los montos', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }

    //sumar activo y pasivo
    if (($inputs[7] + $inputs[8] + $inputs[9] + $inputs[10]) != ($inputs[11] + $inputs[12] + $inputs[13] + $inputs[14])) {
      echo json_encode(['La suma de saldo debe ser igual a 0, cuadre los montos2', '0']);
      return;
    }

    //VAMOS A CONSULTAR EL ID DEL BALANCE
    // $consulta = mysqli_query($conexion, "SELECT cl.id_tb_cli_balance FROM tb_cliente cl INNER JOIN tb_cli_balance clb ON cl.id_tb_cli_balance=clb.id WHERE `idcod_cliente`='$inputs[0]'");
    // $aux = mysqli_error($conexion);
    // if ($aux) {
    //   echo json_encode(['Error en encontrar el id de balance económico', '0']);
    //   return;
    // }
    // if (!$consulta) {
    //   echo json_encode(['Fallo al encontrar el id de balance económico', '0']);
    //   return;
    // }
    // $banderabalance = true;
    // $aux_balance = 0;
    // while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
    //   $banderabalance = false;
    //   $aux_balance = $fila['id_tb_cli_balance'];
    // }
    // if ($banderabalance) {
    //   echo json_encode(['No se encontro el id de balance económico', '0']);
    //   return;
    // }

    $aux_balance = $archivo[0];

    //VAMOS A REALIZAR LA INSERCION EN LA TABLA DE BALANCES
    $res = $conexion->query("UPDATE `tb_cli_balance`
      SET `fechaeval`='$inputs[16]',
      `fechabalance`='$inputs[17]',
      `ventas`='$inputs[2]',
      `cuenta_por_cobrar`='$inputs[3]',
      `mercaderia`='$inputs[4]',
      `negocio`='$inputs[5]',
      `pago_creditos`='$inputs[6]',
      `disponible`='$inputs[7]',
      `cuenta_por_cobrar2`='$inputs[8]',
      `inventario`='$inputs[9]',
      `activo_fijo`='$inputs[10]',
      `proveedores`='$inputs[11]',
      `otros_prestamos`='$inputs[12]',
      `prest_instituciones`='$inputs[13]',
      `patrimonio`='$inputs[14]',
      `updated_at`='$hoy2',
      `updated_by`='$idusuario' WHERE `id`='$aux_balance'");
    $aux = mysqli_error($conexion);
    $balance_insert = get_id_insertado($conexion);
    if ($aux) {
      echo json_encode(['Error en la inserción del balance económico' . $aux, '0']);
      return;
    }
    if (!$res) {
      echo json_encode(['No se logro insertar el balance económico', '0']);
      return;
    }

    // //VAMOS A REALIZAR LA ACTUALIZACION
    // $res = $conexion->query("UPDATE `tb_cliente` SET `fecha_mod`='$hoy' WHERE `idcod_cliente`='$inputs[0]'");
    // $aux = mysqli_error($conexion);
    // if ($aux) {
    //   echo json_encode(['Error en la actualización del cliente', '0']);
    //   return;
    // }
    // if (!$res) {
    //   echo json_encode(['No se logro actualizar datos del cliente', '0']);
    //   return;
    // }
    echo json_encode(['Balance económico registrado satisfactoriamente', '1']);
    mysqli_close($conexion);
    break;
  case 'delete_balance_economico':
    //validar todos los campos necesarios
    $inputs = $_POST["inputs"];
    $selects = $_POST["selects"];
    $archivo = $_POST["archivo"];

    $validar = validar_campos_plus([
      [$inputs[0], "", 'Debe seleccionar un cliente', 1],
      [$inputs[1], "", 'Debe seleccionar un cliente', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }

    // //VAMOS A CONSULTAR EL ID DEL BALANCE
    // $consulta = mysqli_query($conexion, "SELECT id_tb_cli_balance FROM tb_cliente WHERE `idcod_cliente`='$inputs[0]'");
    // $aux = mysqli_error($conexion);
    // if ($aux) {
    //   echo json_encode(['Error en encontrar el id de balance económico', '0']);
    //   return;
    // }
    // if (!$consulta) {
    //   echo json_encode(['Fallo al encontrar el id de balance económico', '0']);
    //   return;
    // }
    // $banderabalance = true;
    // $aux_balance = 0;
    // while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
    //   $banderabalance = false;
    //   $aux_balance = $fila['id_tb_cli_balance'];
    // }
    // if ($banderabalance) {
    //   echo json_encode(['No se encontro el id de balance económico', '0']);
    //   return;
    // }

    $aux_balance = $archivo[0];
    //VAMOS A REALIZAR LA INSERCION EN LA TABLA DE BALANCES
    $res = $conexion->query("DELETE FROM `tb_cli_balance` WHERE `id`='$aux_balance'");
    $aux = mysqli_error($conexion);
    $balance_insert = get_id_insertado($conexion);
    if ($aux) {
      echo json_encode(['Error en la inserción del balance económico' . $aux, '0']);
      return;
    }
    if (!$res) {
      echo json_encode(['No se logro insertar el balance económico', '0']);
      return;
    }

    // //VAMOS A REALIZAR LA ACTUALIZACION
    // $res = $conexion->query("UPDATE `tb_cliente` SET `id_tb_cli_balance`='', `fecha_mod`='$hoy' WHERE `idcod_cliente`='$inputs[0]'");
    // $aux = mysqli_error($conexion);
    // if ($aux) {
    //   echo json_encode(['Error en la actualización del cliente', '0']);
    //   return;
    // }
    // if (!$res) {
    //   echo json_encode(['No se logro actualizar datos del cliente', '0']);
    //   return;
    // }
    echo json_encode(['Balance económico eliminado satisfactoriamente', '1']);
    mysqli_close($conexion);
    break;
  case 'generar_json_cli':
    $idcliente = $_POST['idcliente'];
    //ARMANDO EL NIVEL BASE
    $data['titulares'] = [];
    $data['productos'] = [];
    $data['perfilEconomico'] = [];

    // TITULARES
    // BASE
    $dataaux = datos_titulares($conexion, $idcliente);
    if ($dataaux[0] == 0) {
      $datosCompletos = array('status' => 0, 'msj' => $dataaux[1], 'data' => [], 'file' => $idcliente . '_' . date('d-m-Y'));
      echo json_encode($datosCompletos, JSON_PRETTY_PRINT);
      return;
    }
    $data['titulares'] = $dataaux[2];

    // PRODUCTOS
    // BASE
    $dataaux = datos_productos($conexion, $idcliente);
    if ($dataaux[0] == 0) {
      $datosCompletos = array('status' => 0, 'msj' => $dataaux[1], 'data' => [], 'file' => $idcliente . '_' . date('d-m-Y'));
      echo json_encode($datosCompletos, JSON_PRETTY_PRINT);
      return;
    }
    if ($dataaux[3] != 0) {
      $data['productos'] = $dataaux[2];
    }

    // PERFIL ECONOMICO
    // BASE
    $dataaux = datos_perfil_economico($conexion, $idcliente);
    if ($dataaux[0] == 0) {
      $datosCompletos = array('status' => 0, 'msj' => $dataaux[1], 'data' => [], 'file' => $idcliente . '_' . date('d-m-Y'));
      echo json_encode($datosCompletos, JSON_PRETTY_PRINT);
      return;
    }
    if ($dataaux[3] != 0) {
      $data['perfilEconomico'] = $dataaux[2];
    }
    //CUANDO ESTA TODO CORRECTO
    $datosCompletos = array('status' => 1, 'msj' => 'JSON Generado correctamente', 'data' => $data, 'file' => 'JSON_' . $idcliente . '_' . date('d-m-Y'));
    echo json_encode($datosCompletos, JSON_PRETTY_PRINT);
    break;
  case 'create_cliente_natural':
    $inputs = $_POST["inputs"];
    $selects = $_POST["selects"];
    $radios = $_POST["radios"];
    $archivo = $_POST["archivo"];

    $agenciaID = $selects[20]; //agencidplus  NEGROY NATURAL

    // DPI 2 VALIDACION 
    $dpi = $inputs[12];
    $consulta = "SELECT idcod_cliente, no_identifica, agencia, short_name 
    FROM `tb_cliente` WHERE no_identifica= '$dpi';";
    $queryins = mysqli_query($conexion, $consulta);
    $numeroResultados = mysqli_num_rows($queryins);
    if ($numeroResultados >= 1) {
      echo json_encode(["DPI REPETIDO", "0", false]);
      return;
    }

    $validar = validar_campos_plus([
      [$inputs[0], "", 'Debe ingresar un primer nombre', 1],
      [$inputs[3], "", 'Debe ingresar un primer apellido', 1],
      [$selects[0], "0", 'Debe seleccionar un genero', 1],
      [$selects[1], "0", 'Debe seleccionar un estado civil', 1],
      [$inputs[6], "", 'Debe ingresar una profesión', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }
    //validar el correo electronico
    if ($inputs[7] != "") {
      $validar = validar_campos_plus([
        [$inputs[7], '/^(?:[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})?$/', 'Debe ingresar un correo electronico valido, sino tiene correo deje el campo vacio', 4],
      ]);
      if ($validar[2]) {
        echo json_encode([$validar[0], $validar[1]]);
        return;
      }
    }
    $validar = validar_campos_plus([
      [$inputs[9], "", 'Debe ingresar una fecha de nacimiento', 1],
      [$inputs[10], "1", 'Debe ingresar una fecha de nacimiento valida', 2],
      [$selects[2], "0", 'Debe seleccionar un origen valido', 1],
      [$selects[3], "0", 'Debe seleccionar un país', 1],
      [$selects[4], "0", 'Debe seleccionar un departamento', (($selects[3] != "GT") ? 5 : 1)],
      [$selects[5], "0", 'Debe seleccionar un municipio', (($selects[3] != "GT") ? 5 : 1)],
      [$selects[6], "0", 'Debe seleccionar un lugar de extension de documento', 1],
      [$selects[7], "0", 'Debe seleccionar un tipo de documento', 1],
      [$inputs[12], "", 'Debe ingresar un numero de documento de identificación', 1],
      [$inputs[12], (($selects[6] != "Guatemala") ? '/^[A-Z0-9]{6,15}$/' : '/^(?:[0-9]{4}[-\s]?[0-9]{5}[-\s]?[0-9]{4}|[0-9]{13})$/'), 'Debe ingresar un numero de documento de identificación valido', 4],
      [$selects[8], "0", 'Debe seleccionar un tipo de identificacion tributaria', (($selects[6] != "Guatemala") ? 5 : 1)],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }
    //validar tipo de idendentificacion
    if ($selects[8] == "NIT") {
      $validar = validar_campos_plus([
        [$inputs[13], "", 'Debe ingresar un numero de identificacion tributaria', (($selects[6] != "Guatemala") ? 5 : 1)],
        // [$inputs[13], "/^(?:\d{6,7}-?\d{1}|\d{8,9}-?\d{1}-?\d{2}|\d{12}-?\d{1}-?\d{2})$/", 'Debe ingresar un numero de documento de identificación tributaria valido', (($selects[6]!="GT") ? 5 : 4)],
        [$inputs[13], "/^(?:\d{6,7}-?\d{1}|\d{8,9}-?\d{1}-?\d{2}|\d{12}-?\d{1}-?\d{2})[A-Z]?$/", 'Debe ingresar un numero de documento de identificación tributaria valido', (($selects[6] != "GT") ? 5 : 4)],
      ]);
      if ($validar[2]) {
        echo json_encode([$validar[0], $validar[1]]);
        return;
      }
    }
    if ($selects[8] == "CUI") {
      $validar = validar_campos_plus([
        [$inputs[13], "", 'Debe ingresar un numero de CUI', (($selects[6] != "GT") ? 5 : 1)],
        [$inputs[13], "/^(?:[0-9]{4}[-\s]?[0-9]{5}[-\s]?[0-9]{4}|[0-9]{13})$/", 'Debe ingresar un numero de CUI valido', (($selects[6] != "GT") ? 5 : 4)],
      ]);
      if ($validar[2]) {
        echo json_encode([$validar[0], $validar[1]]);
        return;
      }
    }
    //validar lo demas
    $validar = validar_campos_plus([
      [$selects[9], "0", 'Debe seleccionar una nacionalidad', 1],
      [$selects[10], "0", 'Debe seleccionar una condición de vivienda', 1],
      [$inputs[15], "/^(1000|1[0-9]{3}|[2-9][0-9]{3})$/", 'Debe digitar una año de residencia', 4],
      [$selects[11], "0", 'Debe seleccionar un departamento de domicilio', (($selects[3] != "GT") ? 5 : 1)],
      [$selects[12], "0", 'Debe seleccionar un municipio de domicilio', (($selects[3] != "GT") ? 5 : 1)],
      [$inputs[25], '/^(?:\+?502\s?)?(?:\d{10}|\d{8}|\d{4}\s?\d{4}|\d{7}|\d{9})$/', 'Digite un numero de telefono 1', 4],
      [$inputs[26], "/^(?:\+?502\s?)?(?:\d{10}|\d{8}|\d{4}\s?\d{4}|\d{7}|\d{9})?$/", 'Digite un numero de telefono valido en telefono 2', 4],
      [$selects[13], "0", 'Debe seleccionar un tipo de actuación', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }
    //validar actua en nombre propio
    if ($selects[13] == "2") {
      $validar = validar_campos_plus([
        [$inputs[18], "", 'Debe digitar un nombre de representante', 1],
        [$selects[14], "", 'Debe seleccionar una calidad de actuación', 1],
      ]);
      if ($validar[2]) {
        echo json_encode([$validar[0], $validar[1]]);
        return;
      }
    }
    //validar lo demas
    $validar = validar_campos_plus([
      [$selects[16], "0", 'Debe seleccionar un tipo de etnia', 1],
      [$selects[17], "0", 'Debe seleccionar un tipo de religion', 1],
      [$selects[18], "0", 'Debe seleccionar un tipo de educación', 1],
      [$selects[19], "0", 'Debe seleccionar un tipo de relacion institucional', 1],
      [$inputs[19], "", 'Debe digitar ref. nombre 1', 1],
      [$inputs[20], "", 'Debe digitar ref. telefono 1', 1],
      [$inputs[20], '/^(?:\+?502\s?)?(?:\d{10}|\d{8}|\d{4}\s?\d{4}|\d{7}|\d{9})$/', 'Debe digitar ref. telefono 1', 4],
      [$inputs[22], "/^(?:\+?502\s?)?(?:\d{10}|\d{8}|\d{4}\s?\d{4}|\d{7}|\d{9})?$/", 'Debe digitar ref. telefono 2 válido', 4],
      [$inputs[24], "/^(?:\+?502\s?)?(?:\d{10}|\d{8}|\d{4}\s?\d{4}|\d{7}|\d{9})?$/", 'Debe digitar ref. telefono 3 válido', 4],
      [$radios[0], "/^(Si|No)$/i", 'Debe seleccionar si sabe leer o no', 4],
      [$radios[1], "/^(Si|No)$/i", 'Debe seleccionar si sabe escribir o no', 4],
      [$radios[2], "/^(Si|No)$/i", 'Debe seleccionar si sabe firmar o no', 4],
      [$radios[3], "/^(Si|No)$/i", 'Debe seleccionar si es cliente es PEP o no', 4],
      [$radios[4], "/^(Si|No)$/i", 'Debe seleccionar si es cliente es CPE o no', 4],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }

    //TRANSFORMAR EN MAYUCULA EL SHORTNAME
    $short_name = concatenar_nombre([$inputs[0], $inputs[1], $inputs[2]], [$inputs[3], $inputs[4], $inputs[5]], " ");
    $compl_name = concatenar_nombre([$inputs[3], $inputs[4], $inputs[5]], [$inputs[0], $inputs[1], $inputs[2]], ", ");
    $inputs[0] = mb_strtoupper($inputs[0], 'UTF-8');
    $inputs[1] = mb_strtoupper($inputs[1], 'UTF-8');
    $inputs[2] = mb_strtoupper($inputs[2], 'UTF-8');
    $inputs[3] = mb_strtoupper($inputs[3], 'UTF-8');
    $inputs[4] = mb_strtoupper($inputs[4], 'UTF-8');
    $inputs[5] = mb_strtoupper($inputs[5], 'UTF-8');

    (($selects[3] != "GT") ? $selects[4] = "" : $selects[4] = $selects[4]);
    (($selects[3] != "GT") ? $selects[5] = "" : $selects[5] = $selects[5]);
    (($selects[3] != "GT") ? $selects[11] = "" : $selects[11] = $selects[11]);
    (($selects[3] != "GT") ? $selects[12] = "" : $selects[12] = $selects[12]);
    (($selects[6] != "Guatemala") ? $selects[6] = "" : $selects[6] = $selects[6]);
    (($selects[6] != "Guatemala") ? $inputs[13] = "" : $inputs[13] = $inputs[13]);


    //GENERAR EL CODIGO DEL CLIENTE
    $gencodigo = cli_gencodcliente($agenciaID, $conexion); //$archivo[0]
    if ($gencodigo[0] == 0) {
      echo json_encode([$gencodigo[1], '0']);
      return;
    }
    $codgen = $gencodigo[1];
    //PREPARACION DE ARRAY
    $data = array(
      'idcod_cliente' => $codgen,
      'id_tipoCliente' => 'NATURAL',
      'agencia' => $agenciaID,
      'primer_name' => $inputs[0],
      'segundo_name' => $inputs[1],
      'tercer_name' => $inputs[2],
      'primer_last' => $inputs[3],
      'segundo_last' => $inputs[4],
      'casada_last' => $inputs[5],
      'short_name' => $short_name,
      'compl_name' => $compl_name,
      'url_img' => '',
      'date_birth' => $inputs[9],
      'genero' => $selects[0],
      'estado_civil' => $selects[1],
      'origen' => $selects[2],
      'pais_nacio' => $selects[3],
      'depa_nacio' => $selects[4],
      'muni_nacio' => $selects[5],
      'aldea' => $inputs[10],
      'type_doc' => $selects[7],
      'no_identifica' => $inputs[12],
      'pais_extiende' => $selects[6],
      'nacionalidad' => $selects[9],
      'depa_extiende' => $selects[4],
      'muni_extiende' => $selects[5],
      'otra_nacion' => $selects[15],
      'identi_tribu' => $selects[8],
      'no_tributaria' => ($inputs[13] == "") ? '-' : $inputs[13],
      'no_igss' => $inputs[14],
      'profesion' => $inputs[6],
      'Direccion' => $inputs[16],
      'depa_reside' => $selects[11],
      'muni_reside' => $selects[12],
      'aldea_reside' => $inputs[17],
      'tel_no1' => $inputs[25],
      'tel_no2' => $inputs[26],
      'area' => '',
      'ano_reside' => $inputs[15],
      'vivienda_Condi' => $selects[10],
      'email' => $inputs[7],
      'relac_propo' => $selects[19],
      'monto_ingre' => '0',
      'actu_Propio' => $selects[13],
      'representante_name' => ($selects[13] == "2") ? $inputs[18] : ' ',
      'repre_calidad' => ($selects[13] == "2") ? $selects[14] : ' ',
      'id_religion' => $selects[17],
      'leer' => $radios[0],
      'escribir' => $radios[1],
      'firma' => $radios[2],
      'cargo_grupo' => '',
      'educa' => $selects[18],
      'idioma' => $selects[16],
      'Rel_insti' => $selects[19],
      'datos_Adicionales' => '',
      'Conyuge' => $inputs[8],
      'telconyuge' => $inputs[27],
      'zona' => $inputs[28],
      'barrio' => $inputs[29],
      'hijos' => ($inputs[30] == "") ? 0 : $inputs[30],
      'dependencia' => ($inputs[31] == "") ? 0 : $inputs[31],
      'control_interno' => ($inputs[32] == "") ? " " : $inputs[32],
      'Nomb_Ref1' => $inputs[19],
      'Nomb_Ref2' => $inputs[21],
      'Nomb_Ref3' => $inputs[23],
      'Tel_Ref1' => $inputs[20],
      'Tel_Ref2' => $inputs[22],
      'Tel_Ref3' => $inputs[24],
      'PEP' => $radios[3],
      'CPE' => $radios[4],
      'estado' => '1',
      'fecha_alta' => $hoy2,
      'created_by' => $idusuario,
      'fecha_mod' => $hoy2,
    );

    $conexion->autocommit(FALSE);
    try {
      $columns = implode(', ', array_keys($data));
      $placeholders = implode(', ', array_fill(0, count($data), '?'));
      $stmt = $conexion->prepare("INSERT INTO tb_cliente ($columns) VALUES ($placeholders)");
      // Obtener los valores del array de datos
      $values = array_values($data);
      // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
      $types = str_repeat('s', count($values));
      // Vincular los parámetros
      $stmt->bind_param($types, ...$values);
      if ($stmt->execute()) {
        $conexion->commit();
        echo json_encode(["Cliente ingresado correctamente: " . $codgen, '1']);
      } else {
        $errorMsg = $stmt->error;
        $conexion->rollback();
        echo json_encode(["Error al ejecutar consulta: $errorMsg", '0']);
      }
    } catch (Exception $e) {
      $conexion->rollback();
      echo json_encode(["Error: " . $e->getMessage(), '0']);
    } finally {
      $stmt->close();
      $conexion->close();
    }
    break;
  case 'update_cliente_natural':
    $inputs = $_POST["inputs"];
    $selects = $_POST["selects"];
    $radios = $_POST["radios"];
    $archivo = $_POST["archivo"];
    $id_update = $_POST["id"];

    // $agenciaID = $selects[20]; //agencidplus actualiza NEGROY  (solo revisar el boton de GUARADAR )

    $validar = validar_campos_plus([
      [$archivo[3], "", 'No se ha seleccionado un cliente a editar', 1],
      [$id_update, "", 'No se ha seleccionado un cliente a editar', 1],
      [$inputs[0], "", 'Debe ingresar un primer nombre', 1],
      [$inputs[0], "", 'Debe ingresar un primer nombre', 1],
      [$inputs[3], "", 'Debe ingresar un primer apellido', 1],
      [$selects[0], "0", 'Debe seleccionar un genero', 1],
      [$selects[1], "0", 'Debe seleccionar un estado civil', 1],
      [$inputs[6], "", 'Debe ingresar una profesión', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }
    //validar el correo electronico
    if ($inputs[7] != "") {
      $validar = validar_campos_plus([
        [$inputs[7], '/^(?:[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})?$/', 'Debe ingresar un correo electronico valido, sino tiene correo deje el campo vacio', 4],
      ]);
      if ($validar[2]) {
        echo json_encode([$validar[0], $validar[1]]);
        return;
      }
    }
    $validar = validar_campos_plus([
      [$inputs[9], "", 'Debe ingresar una fecha de nacimiento', 1],
      [$inputs[10], "1", 'Debe ingresar una fecha de nacimiento valida', 2],
      [$selects[2], "0", 'Debe seleccionar un origen valido', 1],
      [$selects[3], "0", 'Debe seleccionar un país', 1],
      [$selects[4], "0", 'Debe seleccionar un departamento', (($selects[3] != "GT") ? 5 : 1)],
      [$selects[5], "0", 'Debe seleccionar un municipio', (($selects[3] != "GT") ? 5 : 1)],
      [$selects[6], "0", 'Debe seleccionar un lugar de extension de documento', 1],
      [$selects[7], "0", 'Debe seleccionar un tipo de documento', 1],
      [$inputs[12], "", 'Debe ingresar un numero de documento de identificación', 1],
      [$inputs[12], (($selects[6] != "Guatemala") ? '/^[A-Z0-9]{6,15}$/' : '/^(?:[0-9]{4}[-\s]?[0-9]{5}[-\s]?[0-9]{4}|[0-9]{13})$/'), 'Debe ingresar un numero de documento de identificación valido', 4],
      [$selects[8], "0", 'Debe seleccionar un tipo de identificacion tributaria', (($selects[6] != "Guatemala") ? 5 : 1)],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }
    //validar tipo de idendentificacion
    if ($selects[8] == "NIT") {
      $validar = validar_campos_plus([
        [$inputs[13], "", 'Debe ingresar un numero de identificacion tributaria', (($selects[6] != "Guatemala") ? 5 : 1)],
        // [$inputs[13], "/^(?:\d{6,7}-?\d{1}|\d{8,9}-?\d{1}-?\d{2}|\d{12}-?\d{1}-?\d{2})$/", 'Debe ingresar un numero de documento de identificación tributaria valido', (($selects[6]!="GT") ? 5 : 4)],
        [$inputs[13], "/^(?:\d{6,7}-?\d{1}|\d{8,9}-?\d{1}-?\d{2}|\d{12}-?\d{1}-?\d{2})[A-Z]?$/", 'Debe ingresar un numero de documento de identificación tributaria valido', (($selects[6] != "GT") ? 5 : 4)],
      ]);
      if ($validar[2]) {
        echo json_encode([$validar[0], $validar[1]]);
        return;
      }
    }
    if ($selects[8] == "CUI") {
      $validar = validar_campos_plus([
        [$inputs[13], "", 'Debe ingresar un numero de CUI', (($selects[6] != "GT") ? 5 : 1)],
        [$inputs[13], "/^(?:[0-9]{4}[-\s]?[0-9]{5}[-\s]?[0-9]{4}|[0-9]{13})$/", 'Debe ingresar un numero de CUI valido', (($selects[6] != "GT") ? 5 : 4)],
      ]);
      if ($validar[2]) {
        echo json_encode([$validar[0], $validar[1]]);
        return;
      }
    }
    //validar lo demas
    $validar = validar_campos_plus([
      [$selects[9], "0", 'Debe seleccionar una nacionalidad', 1],
      [$selects[10], "0", 'Debe seleccionar una condición de vivienda', 1],
      [$inputs[15], "/^(1000|1[0-9]{3}|[2-9][0-9]{3})$/", 'Debe digitar una año de residencia', 4],
      [$selects[11], "0", 'Debe seleccionar un departamento de domicilio', (($selects[3] != "GT") ? 5 : 1)],
      [$selects[12], "0", 'Debe seleccionar un municipio de domicilio', (($selects[3] != "GT") ? 5 : 1)],
      [$inputs[25], '/^(?:\+?502\s?)?(?:\d{10}|\d{8}|\d{4}\s?\d{4}|\d{7}|\d{9})$/', 'Digite un numero de telefono 1', 4],
      [$inputs[26], "/^(?:\+?502\s?)?(?:\d{10}|\d{8}|\d{4}\s?\d{4}|\d{7}|\d{9})?$/", 'Digite un numero de telefono valido en telefono 2', 4],
      [$selects[13], "0", 'Debe seleccionar un tipo de actuación', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }
    //validar actua en nombre propio
    if ($selects[13] == "2") {
      $validar = validar_campos_plus([
        [$inputs[18], "", 'Debe digitar un nombre de representante', 1],
        [$selects[14], "", 'Debe seleccionar una calidad de actuación', 1],
      ]);
      if ($validar[2]) {
        echo json_encode([$validar[0], $validar[1]]);
        return;
      }
    }
    //validar lo demas
    $validar = validar_campos_plus([
      [$selects[16], "0", 'Debe seleccionar un tipo de etnia', 1],
      [$selects[17], "0", 'Debe seleccionar un tipo de religion', 1],
      [$selects[18], "0", 'Debe seleccionar un tipo de educación', 1],
      [$selects[19], "0", 'Debe seleccionar un tipo de relacion institucional', 1],
      [$inputs[19], "", 'Debe digitar ref. nombre 1', 1],
      [$inputs[20], "", 'Debe digitar ref. telefono 1', 1],
      [$inputs[20], '/^(?:\+?502\s?)?(?:\d{10}|\d{8}|\d{4}\s?\d{4}|\d{7}|\d{9})$/', 'Debe digitar ref. telefono 1', 4],
      [$inputs[22], "/^(?:\+?502\s?)?(?:\d{10}|\d{8}|\d{4}\s?\d{4}|\d{7}|\d{9})?$/", 'Debe digitar ref. telefono 2 válido', 4],
      [$inputs[24], "/^(?:\+?502\s?)?(?:\d{10}|\d{8}|\d{4}\s?\d{4}|\d{7}|\d{9})?$/", 'Debe digitar ref. telefono 3 válido', 4],
      [$radios[0], "/^(Si|No)$/i", 'Debe seleccionar si sabe leer o no', 4],
      [$radios[1], "/^(Si|No)$/i", 'Debe seleccionar si sabe escribir o no', 4],
      [$radios[2], "/^(Si|No)$/i", 'Debe seleccionar si sabe firmar o no', 4],
      [$radios[3], "/^(Si|No)$/i", 'Debe seleccionar si es cliente es PEP o no', 4],
      [$radios[4], "/^(Si|No)$/i", 'Debe seleccionar si es cliente es CPE o no', 4],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }

    //TRANSFORMAR EN MAYUCULA EL SHORTNAME
    $short_name = concatenar_nombre([$inputs[0], $inputs[1], $inputs[2]], [$inputs[3], $inputs[4], $inputs[5]], " ");
    $compl_name = concatenar_nombre([$inputs[3], $inputs[4], $inputs[5]], [$inputs[0], $inputs[1], $inputs[2]], ", ");
    $inputs[0] = mb_strtoupper($inputs[0], 'UTF-8');
    $inputs[1] = mb_strtoupper($inputs[1], 'UTF-8');
    $inputs[2] = mb_strtoupper($inputs[2], 'UTF-8');
    $inputs[3] = mb_strtoupper($inputs[3], 'UTF-8');
    $inputs[4] = mb_strtoupper($inputs[4], 'UTF-8');
    $inputs[5] = mb_strtoupper($inputs[5], 'UTF-8');

    (($selects[3] != "GT") ? $selects[4] = "" : $selects[4] = $selects[4]);
    (($selects[3] != "GT") ? $selects[5] = "" : $selects[5] = $selects[5]);
    (($selects[3] != "GT") ? $selects[11] = "" : $selects[11] = $selects[11]);
    (($selects[3] != "GT") ? $selects[12] = "" : $selects[12] = $selects[12]);
    (($selects[6] != "Guatemala") ? $selects[6] = "" : $selects[6] = $selects[6]);
    (($selects[6] != "Guatemala") ? $inputs[13] = "" : $inputs[13] = $inputs[13]);

    //PREPARACION DE ARRAY $agenciaID => $archivo[2]
    $data = array(
      'agencia' => $archivo[2],
      'primer_name' => $inputs[0],
      'segundo_name' => $inputs[1],
      'tercer_name' => $inputs[2],
      'primer_last' => $inputs[3],
      'segundo_last' => $inputs[4],
      'casada_last' => $inputs[5],
      'short_name' => $short_name,
      'compl_name' => $compl_name,
      'date_birth' => $inputs[9],
      'genero' => $selects[0],
      'estado_civil' => $selects[1],
      'origen' => $selects[2],
      'pais_nacio' => $selects[3],
      'depa_nacio' => $selects[4],
      'muni_nacio' => $selects[5],
      'aldea' => $inputs[10],
      'type_doc' => $selects[7],
      'no_identifica' => $inputs[12],
      'pais_extiende' => $selects[6],
      'nacionalidad' => $selects[9],
      'depa_extiende' => $selects[4],
      'muni_extiende' => $selects[5],
      'otra_nacion' => $selects[15],
      'identi_tribu' => $selects[8],
      'no_tributaria' => ($inputs[13] == "") ? '-' : $inputs[13],
      'no_igss' => $inputs[14],
      'profesion' => $inputs[6],
      'Direccion' => $inputs[16],
      'depa_reside' => $selects[11],
      'muni_reside' => $selects[12],
      'aldea_reside' => $inputs[17],
      'tel_no1' => $inputs[25],
      'tel_no2' => $inputs[26],
      'area' => '',
      'ano_reside' => $inputs[15],
      'vivienda_Condi' => $selects[10],
      'email' => $inputs[7],
      'relac_propo' => $selects[19],
      'monto_ingre' => '0',
      'actu_Propio' => $selects[13],
      'representante_name' => ($selects[13] == "2") ? $inputs[18] : ' ',
      'repre_calidad' => ($selects[13] == "2") ? $selects[14] : ' ',
      'id_religion' => $selects[17],
      'leer' => $radios[0],
      'escribir' => $radios[1],
      'firma' => $radios[2],
      'cargo_grupo' => '',
      'educa' => $selects[18],
      'idioma' => $selects[16],
      'Rel_insti' => $selects[19],
      'datos_Adicionales' => '',
      'Conyuge' => $inputs[8],
      'telconyuge' => $inputs[27],
      'zona' => $inputs[28],
      'barrio' => $inputs[29],
      'hijos' => ($inputs[30] == "") ? 0 : $inputs[30],
      'dependencia' => ($inputs[31] == "") ? 0 : $inputs[31],
      'control_interno' => ($inputs[32] == "") ? " " : $inputs[32],
      'Nomb_Ref1' => $inputs[19],
      'Nomb_Ref2' => $inputs[21],
      'Nomb_Ref3' => $inputs[23],
      'Tel_Ref1' => $inputs[20],
      'Tel_Ref2' => $inputs[22],
      'Tel_Ref3' => $inputs[24],
      'PEP' => $radios[3],
      'CPE' => $radios[4],
      'updated_by' => $idusuario,
      'fecha_mod' => $hoy2,
    );

    $id = $archivo[3];
    $conexion->autocommit(FALSE);
    try {
      // Columnas a actualizar
      $setCols = [];
      foreach ($data as $key => $value) {
        $setCols[] = "$key = ?";
      }
      $setStr = implode(', ', $setCols);
      $stmt = $conexion->prepare("UPDATE tb_cliente SET $setStr WHERE idcod_cliente = ?");
      // Obtener los valores del array de datos
      $values = array_values($data);
      // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
      $values[] = $id; // Agregar ID al final
      $types = str_repeat('s', count($values));
      // Vincular los parámetros
      $stmt->bind_param($types, ...$values);
      if ($stmt->execute()) {
        $conexion->commit();
        echo json_encode(["Datos de cliente actualizado correctamente: " . $archivo[3], '1']);
      } else {
        $errorMsg = $stmt->error;
        $conexion->rollback();
        echo json_encode(["Error al ejecutar consulta: $errorMsg", '0']);
      }
    } catch (Exception $e) {
      $conexion->rollback();
      echo json_encode(["Error: " . $e->getMessage(), '0']);
    } finally {
      $stmt->close();
      $conexion->close();
    }
    break;
  case 'delete_cliente_natural': {
      $archivo = $_POST["ideliminar"];
      $validar = validar_campos_plus([
        [$archivo[0], "", 'No se ha detectado una agencia, recargue la pagina nuevamente', 1],
        [$archivo[3], "", 'Debe seleccionar un cliente a eliminar', 1],
      ]);
      if ($validar[2]) {
        echo json_encode([$validar[0], $validar[1]]);
        return;
      }
      //PREPARACION DE ARRAY
      $data = array(
        'estado' => '0',
        'fecha_mod' => date('Y-m-d')
      );

      $id = $archivo[3];
      $conexion->autocommit(FALSE);
      try {
        // Columnas a actualizar
        $setCols = [];
        foreach ($data as $key => $value) {
          $setCols[] = "$key = ?";
        }
        $setStr = implode(', ', $setCols);
        $stmt = $conexion->prepare("UPDATE tb_cliente SET $setStr WHERE idcod_cliente = ?");
        $values = array_values($data);
        $values[] = $id;
        $types = str_repeat('s', count($values));
        $stmt->bind_param($types, ...$values);
        if ($stmt->execute()) {
          $conexion->commit();
          echo json_encode(["Cliente eliminado correctamente: " . $archivo[3], '1']);
        } else {
          $errorMsg = $stmt->error;
          $conexion->rollback();
          echo json_encode(["Error al ejecutar consulta: $errorMsg", '0']);
        }
      } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(["Error: " . $e->getMessage(), '0']);
      } finally {
        $stmt->close();
        $conexion->close();
      }
    }
    break;
  case 'delete_image_cliente':
    $archivo = $_POST["ideliminar"];
    $codcliente = $archivo[1];
    $imgurl = $archivo[0];

    $consulta = mysqli_query($conexion, "SELECT url_img FROM tb_cliente tc WHERE tc.estado='1' AND tc.idcod_cliente='$codcliente'");
    $urlimg = 'xxxx';
    while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
      $urlimg = $fila['url_img'];
    }
    $salida = "../../../";
    $rutaenserver = $salida . $urlimg;
    if (file_exists($rutaenserver)) {
      unlink($rutaenserver);
      //inicio transaccion
      $conexion->autocommit(false);
      try {
        $conexion->query("UPDATE `tb_cliente` set `url_img`='ImageDeleted' WHERE idcod_cliente='$codcliente'");
        $aux = mysqli_error($conexion);
        if ($aux) {
          echo json_encode(['Error:' . $aux, '0']);
          $conexion->rollback();
          return;
        }
        $conexion->commit();
        echo json_encode(['Foto eliminada correctamente!', 1]);
      } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(['Error al eliminar la foto: ' . $e->getMessage(), '0']);
      }
      mysqli_close($conexion);
    } else {
      echo json_encode(['Archivo no encontrado', 1]);
    }
    break;
  case 'buscar_municipios':
    $id = $_POST['id'];
    $data[] = [];
    $bandera = true;
    $consulta = mysqli_query($general, "SELECT * FROM municipios cl WHERE cl.codigo_departamento1='$id'");
    $aux = mysqli_error($general);
    if ($aux) {
      echo json_encode(['Error en la recuperación de municipios del departamento, intente nuevamente', '0']);
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
        echo json_encode(['El departamento seleccionado no tiene municipios registrados', '0']);
        return;
      }
      echo json_encode(['Satisfactorio', '1', $data]);
    } else {
      echo json_encode(['Error en la recuperacion de municipios, intente nuevamente', '0']);
    }
    mysqli_close($general);
    break;
  case 'create_ingreso_propio':
    $inputs = $_POST["inputs"];
    $selects = $_POST["selects"];
    $radios = $_POST["radios"];
    $archivo = $_POST["archivo"];

    $validar = validar_campos_plus([
      [$archivo[3], "", 'Debe seleccionar un cliente', 1],
      [$inputs[0], "", 'Debe ingresar un nombre de negocio', 1],
      [$inputs[1], "", 'Debe ingresar una actividad economica', 1],
      [$inputs[2], "", 'Debe ingresar una actividad economica', 1],
      [$inputs[3], "", 'Debe ingresar una fecha de inicio o inscripción', 1],
      [$radios[0], "", 'Debe seleccionar un tipo de si tiene o no patente', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }
    //validar tipo de idendentificacion
    if ($radios[0] == "si") {
      $validar = validar_campos_plus([
        [$inputs[4], "", 'Debe ingresar un numero registro', 1],
        [$inputs[5], "", 'Debe ingresar un numero de folio', 1],
        [$inputs[6], "", 'Debe ingresar un numero de libro', 1],
      ]);
      if ($validar[2]) {
        echo json_encode([$validar[0], $validar[1]]);
        return;
      }
    } else {
      $inputs[4] = "";
      $inputs[5] = "";
      $inputs[6] = "";
    }

    $validar = validar_campos_plus([
      [$inputs[7], "", 'Debe ingresar un número de telefono', 1],
      [$selects[0], "", 'Debe ingresar una condicion local', 1],
      [$inputs[8], "", 'Debe ingresar un ingreso mensual estimado', 1],
      [$selects[1], "0", 'Debe seleccionar un departamento', 1],
      [$selects[2], "0", 'Debe ingresar un municipio', 1],
      [$inputs[9], "", 'Debe ingresar una dirección', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }

    //PREPARACION DE ARRAY
    $data = array(
      'id_cliente' => $archivo[3],
      'nombre_empresa' => $inputs[0],
      'patente' => $radios[0],
      'no_registro' => $inputs[4],
      'folio' => $inputs[5],
      'libro' => $inputs[6],
      'fecha_patente' => date('Y-m-d'),
      'depa_negocio' => $selects[1],
      'muni_negocio' => $selects[2],
      'detalle_ingreso' => '',
      'direc_negocio' => $inputs[9],
      'referencia' => $inputs[11],
      'telefono_negocio' => $inputs[7],
      'puesto_ocupa' => '',
      'empleados' => $inputs[10],
      'fecha_sys' => date('Y-m-d'),
      'fecha_labor' => $inputs[3],
      'sueldo_base' => $inputs[8],
      'NIT_empresa' => '',
      'condi_negocio' => $selects[0],
      'actividad_economica' => $inputs[1],
      'sector_Econo' => '',
      'fuente_ingreso' => 'Propio',
      'Tipo_ingreso' => '1',
      'created_at' => date('Y-m-d')
    );

    $conexion->autocommit(FALSE);
    try {
      $columns = implode(', ', array_keys($data));
      $placeholders = implode(', ', array_fill(0, count($data), '?'));
      $stmt = $conexion->prepare("INSERT INTO tb_ingresos ($columns) VALUES ($placeholders)");
      // Obtener los valores del array de datos
      $values = array_values($data);
      // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
      $types = str_repeat('s', count($values));
      // Vincular los parámetros
      $stmt->bind_param($types, ...$values);
      if ($stmt->execute()) {
        $conexion->commit();
        echo json_encode(["Ingreso propio ingresado correctamente del cliente: " . $archivo[3], '1']);
      } else {
        $errorMsg = $stmt->error;
        $conexion->rollback();
        echo json_encode(["Error al ejecutar consulta: $errorMsg", '0']);
      }
    } catch (Exception $e) {
      $conexion->rollback();
      echo json_encode(["Error: " . $e->getMessage(), '0']);
    } finally {
      $stmt->close();
      $conexion->close();
    }
    break;
  case 'update_ingreso_propio':
    $inputs = $_POST["inputs"];
    $selects = $_POST["selects"];
    $radios = $_POST["radios"];
    $archivo = $_POST["archivo"];

    $validar = validar_campos_plus([
      [$archivo[3], "", 'Debe seleccionar un cliente', 1],
      [$archivo[4], "", 'Debe seleccionar un registro a editar', 1],
      [$inputs[0], "", 'Debe ingresar un nombre de negocio', 1],
      [$inputs[1], "", 'Debe ingresar una actividad economica', 1],
      [$inputs[2], "", 'Debe ingresar una actividad economica', 1],
      [$inputs[3], "", 'Debe ingresar una fecha de inicio o inscripción', 1],
      [$radios[0], "", 'Debe seleccionar un tipo de si tiene o no patente', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }
    //validar tipo de idendentificacion
    if ($radios[0] == "si") {
      $validar = validar_campos_plus([
        [$inputs[4], "", 'Debe ingresar un numero registro', 1],
        [$inputs[5], "", 'Debe ingresar un numero de folio', 1],
        [$inputs[6], "", 'Debe ingresar un numero de libro', 1],
      ]);
      if ($validar[2]) {
        echo json_encode([$validar[0], $validar[1]]);
        return;
      }
    } else {
      $inputs[4] = "";
      $inputs[5] = "";
      $inputs[6] = "";
    }

    $validar = validar_campos_plus([
      [$inputs[7], "", 'Debe ingresar un número de telefono', 1],
      [$selects[0], "", 'Debe ingresar una condicion local', 1],
      [$inputs[8], "", 'Debe ingresar un ingreso mensual estimado', 1],
      [$selects[1], "0", 'Debe seleccionar un departamento', 1],
      [$selects[2], "0", 'Debe ingresar un municipio', 1],
      [$inputs[9], "", 'Debe ingresar una dirección', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }

    //PREPARACION DE ARRAY
    $data = array(
      'id_cliente' => $archivo[3],
      'nombre_empresa' => $inputs[0],
      'patente' => $radios[0],
      'no_registro' => $inputs[4],
      'folio' => $inputs[5],
      'libro' => $inputs[6],
      'fecha_patente' => $inputs[3],
      'depa_negocio' => $selects[1],
      'muni_negocio' => $selects[2],
      'detalle_ingreso' => '',
      'direc_negocio' => $inputs[9],
      'referencia' => $inputs[11],
      'telefono_negocio' => $inputs[7],
      'puesto_ocupa' => '',
      'empleados' => ($inputs[10] == "") ? 0 : $inputs[10],
      'fecha_sys' => date('Y-m-d'),
      'fecha_labor' => $inputs[3],
      'sueldo_base' => $inputs[8],
      'NIT_empresa' => '',
      'condi_negocio' => $selects[0],
      'actividad_economica' => $inputs[1],
      'sector_Econo' => '',
      'fuente_ingreso' => 'Propio',
      'Tipo_ingreso' => '1'
    );

    $id = $archivo[4];
    $conexion->autocommit(FALSE);
    try {
      // Columnas a actualizar
      $setCols = [];
      foreach ($data as $key => $value) {
        $setCols[] = "$key = ?";
      }
      $setStr = implode(', ', $setCols);
      $stmt = $conexion->prepare("UPDATE tb_ingresos SET $setStr WHERE id_ingre_dependi = ?");
      // Obtener los valores del array de datos
      $values = array_values($data);
      // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
      $values[] = $id; // Agregar ID al final
      $types = str_repeat('s', count($values));
      // Vincular los parámetros
      $stmt->bind_param($types, ...$values);
      if ($stmt->execute()) {
        $conexion->commit();
        echo json_encode(["Ingreso propio actualizado correctamente del cliente: " . $archivo[3], '1']);
      } else {
        $errorMsg = $stmt->error;
        $conexion->rollback();
        echo json_encode(["Error al ejecutar consulta: $errorMsg", '0']);
      }
    } catch (Exception $e) {
      $conexion->rollback();
      echo json_encode(["Error: " . $e->getMessage(), '0']);
    } finally {
      $stmt->close();
      $conexion->close();
    }
    break;
  case 'create_ingreso_dependiente':
    $inputs = $_POST["inputs"];
    $selects = $_POST["selects"];
    $radios = $_POST["radios"];
    $archivo = $_POST["archivo"];

    $validar = validar_campos_plus([
      [$archivo[3], "", 'Debe seleccionar un cliente', 1],
      [$selects[0], "", 'Debe seleccionar un sector', 1],
      [$inputs[0], "", 'Debe ingresar un nombre de negocio', 1],
      [$inputs[1], "", 'Debe ingresar una actividad economica', 1],
      [$inputs[2], "", 'Debe ingresar una actividad economica', 1],
      [$inputs[3], "", 'Debe ingresar una puesto', 1],
      [$inputs[5], "", 'Debe ingresar una direccion', 1],
      [$selects[1], "0", 'Debe seleccionar un departamento', 1],
      [$selects[2], "0", 'Debe ingresar un municipio', 1],
      [$inputs[4], "", 'Debe ingresar un ingreso mensual estimado', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }

    //PREPARACION DE ARRAY
    $data = array(
      'id_cliente' => $archivo[3],
      'nombre_empresa' => $inputs[0],
      'patente' => '',
      'no_registro' => '',
      'folio' => '',
      'libro' => '',
      'fecha_patente' => '0000-00-00',
      'depa_negocio' => $selects[1],
      'muni_negocio' => $selects[2],
      'detalle_ingreso' => '',
      'direc_negocio' => $inputs[5],
      'telefono_negocio' => '0',
      'puesto_ocupa' => $inputs[3],
      'fecha_sys' => date('Y-m-d'),
      'fecha_labor' => $inputs[6],
      'sueldo_base' => $inputs[4],
      'NIT_empresa' => '0',
      'condi_negocio' => '',
      'actividad_economica' => $inputs[1],
      'sector_Econo' => '',
      'fuente_ingreso' => 'Dependencia',
      'Tipo_ingreso' => '2',
      'created_at' => date('Y-m-d')
    );

    $conexion->autocommit(FALSE);
    try {
      $columns = implode(', ', array_keys($data));
      $placeholders = implode(', ', array_fill(0, count($data), '?'));
      $stmt = $conexion->prepare("INSERT INTO tb_ingresos ($columns) VALUES ($placeholders)");
      // Obtener los valores del array de datos
      $values = array_values($data);
      // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
      $types = str_repeat('s', count($values));
      // Vincular los parámetros
      $stmt->bind_param($types, ...$values);
      if ($stmt->execute()) {
        $conexion->commit();
        echo json_encode(["Ingreso en dependencia ingresado correctamente del cliente: " . $archivo[3], '1']);
      } else {
        $errorMsg = $stmt->error;
        $conexion->rollback();
        echo json_encode(["Error al ejecutar consulta: $errorMsg", '0']);
      }
    } catch (Exception $e) {
      $conexion->rollback();
      echo json_encode(["Error: " . $e->getMessage(), '0']);
    } finally {
      $stmt->close();
      $conexion->close();
    }
    break;
  case 'update_ingreso_dependiente':
    $inputs = $_POST["inputs"];
    $selects = $_POST["selects"];
    $archivo = $_POST["archivo"];

    $validar = validar_campos_plus([
      [$archivo[3], "", 'Debe seleccionar un cliente', 1],
      [$archivo[4], "", 'Debe seleccionar un registro a editar', 1],
      [$selects[0], "", 'Debe seleccionar un sector', 1],
      [$inputs[0], "", 'Debe ingresar un nombre de negocio', 1],
      [$inputs[1], "", 'Debe ingresar una actividad economica', 1],
      [$inputs[2], "", 'Debe ingresar una actividad economica', 1],
      [$inputs[3], "", 'Debe ingresar una puesto', 1],
      [$inputs[5], "", 'Debe ingresar una direccion', 1],
      [$selects[1], "0", 'Debe seleccionar un departamento', 1],
      [$selects[2], "0", 'Debe ingresar un municipio', 1],
      [$inputs[4], "", 'Debe ingresar un ingreso mensual estimado', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }

    //PREPARACION DE ARRAY
    //PREPARACION DE ARRAY
    $data = array(
      'id_cliente' => $archivo[3],
      'nombre_empresa' => $inputs[0],
      'patente' => '',
      'no_registro' => '',
      'folio' => '',
      'libro' => '',
      'fecha_patente' => '0000-00-00',
      'depa_negocio' => $selects[1],
      'muni_negocio' => $selects[2],
      'detalle_ingreso' => '',
      'direc_negocio' => $inputs[5],
      'telefono_negocio' => '0',
      'puesto_ocupa' => $inputs[3],
      'fecha_sys' => date('Y-m-d'),
      'fecha_labor' => $inputs[6],
      'sueldo_base' => $inputs[4],
      'NIT_empresa' => '',
      'condi_negocio' => '',
      'actividad_economica' => $inputs[1],
      'sector_Econo' => '',
      'fuente_ingreso' => 'Dependencia',
      'Tipo_ingreso' => '2'
    );

    $id = $archivo[4];
    $conexion->autocommit(FALSE);
    try {
      // Columnas a actualizar
      $setCols = [];
      foreach ($data as $key => $value) {
        $setCols[] = "$key = ?";
      }
      $setStr = implode(', ', $setCols);
      $stmt = $conexion->prepare("UPDATE tb_ingresos SET $setStr WHERE id_ingre_dependi = ?");
      // Obtener los valores del array de datos
      $values = array_values($data);
      // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
      $values[] = $id; // Agregar ID al final
      $types = str_repeat('s', count($values));
      // Vincular los parámetros
      $stmt->bind_param($types, ...$values);
      if ($stmt->execute()) {
        $conexion->commit();
        echo json_encode(["Ingreso en dependencia actualizado correctamente del cliente: " . $archivo[3], '1']);
      } else {
        $errorMsg = $stmt->error;
        $conexion->rollback();
        echo json_encode(["Error al ejecutar consulta: $errorMsg", '0']);
      }
    } catch (Exception $e) {
      $conexion->rollback();
      echo json_encode(["Error: " . $e->getMessage(), '0']);
    } finally {
      $stmt->close();
      $conexion->close();
    }
    break;
  case 'create_otros_ingresos':
    $inputs = $_POST["inputs"];
    $selects = $_POST["selects"];
    $radios = $_POST["radios"];
    $archivo = $_POST["archivo"];

    $validar = validar_campos_plus([
      [$archivo[3], "", 'Debe seleccionar un cliente', 1],
      [$selects[0], "", 'Debe seleccionar un tipo de ingreso', 1],
      [$inputs[1], "", 'Debe ingresar un monto aproximado mensual', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }

    //PREPARACION DE ARRAY
    $data = array(
      'id_cliente' => $archivo[3],
      'nombre_empresa' => $selects[0],
      'patente' => '',
      'no_registro' => '',
      'folio' => '',
      'libro' => '',
      'fecha_patente' => '0000-00-00',
      'depa_negocio' => '',
      'muni_negocio' => '',
      'detalle_ingreso' => $inputs[0],
      'direc_negocio' => '',
      'telefono_negocio' => '00',
      'puesto_ocupa' => '',
      'fecha_sys' => date('Y-m-d'),
      'fecha_labor' => '0000-00-00',
      'sueldo_base' => $inputs[1],
      'NIT_empresa' => '',
      'condi_negocio' => '',
      'actividad_economica' => '',
      'sector_Econo' => '',
      'fuente_ingreso' => 'Otros',
      'Tipo_ingreso' => '3',
      'created_at' => date('Y-m-d')
    );

    $conexion->autocommit(FALSE);
    try {
      $columns = implode(', ', array_keys($data));
      $placeholders = implode(', ', array_fill(0, count($data), '?'));
      $stmt = $conexion->prepare("INSERT INTO tb_ingresos ($columns) VALUES ($placeholders)");
      // Obtener los valores del array de datos
      $values = array_values($data);
      // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
      $types = str_repeat('s', count($values));
      // Vincular los parámetros
      $stmt->bind_param($types, ...$values);
      if ($stmt->execute()) {
        $conexion->commit();
        echo json_encode(["Otro ingreso registrado correctamente del cliente: " . $archivo[3], '1']);
      } else {
        $errorMsg = $stmt->error;
        $conexion->rollback();
        echo json_encode(["Error al ejecutar consulta: $errorMsg", '0']);
      }
    } catch (Exception $e) {
      $conexion->rollback();
      echo json_encode(["Error: " . $e->getMessage(), '0']);
    } finally {
      $stmt->close();
      $conexion->close();
    }
    break;
  case 'update_otros_ingresos':
    $inputs = $_POST["inputs"];
    $selects = $_POST["selects"];
    $archivo = $_POST["archivo"];

    $validar = validar_campos_plus([
      [$archivo[3], "", 'Debe seleccionar un cliente', 1],
      [$archivo[4], "", 'Debe seleccionar un registro a editar', 1],
      [$selects[0], "", 'Debe seleccionar un tipo de ingreso', 1],
      [$inputs[1], "", 'Debe ingresar un monto aproximado mensual', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }
    //PREPARACION DE ARRAY
    $data = array(
      'id_cliente' => $archivo[3],
      'nombre_empresa' => $selects[0],
      'patente' => '',
      'no_registro' => '',
      'folio' => '',
      'libro' => '',
      'fecha_patente' => '0000-00-00',
      'depa_negocio' => '',
      'muni_negocio' => '',
      'detalle_ingreso' => $inputs[0],
      'direc_negocio' => '',
      'telefono_negocio' => '000000000',
      'puesto_ocupa' => '',
      'fecha_sys' => date('Y-m-d'),
      'fecha_labor' => '0000-00-00',
      'sueldo_base' => $inputs[1],
      'NIT_empresa' => '',
      'condi_negocio' => '',
      'actividad_economica' => '',
      'sector_Econo' => '',
      'fuente_ingreso' => 'Otros',
      'Tipo_ingreso' => '3',
    );

    $id = $archivo[4];
    $conexion->autocommit(FALSE);
    try {
      // Columnas a actualizar
      $setCols = [];
      foreach ($data as $key => $value) {
        $setCols[] = "$key = ?";
      }
      $setStr = implode(', ', $setCols);
      $stmt = $conexion->prepare("UPDATE tb_ingresos SET $setStr WHERE id_ingre_dependi = ?");
      // Obtener los valores del array de datos
      $values = array_values($data);
      // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
      $values[] = $id; // Agregar ID al final
      $types = str_repeat('s', count($values));
      // Vincular los parámetros
      $stmt->bind_param($types, ...$values);
      if ($stmt->execute()) {
        $conexion->commit();
        echo json_encode(["Otro ingreso actualizado correctamente del cliente: " . $archivo[3], '1']);
      } else {
        $errorMsg = $stmt->error;
        $conexion->rollback();
        echo json_encode(["Error al ejecutar consulta: $errorMsg", '0']);
      }
    } catch (Exception $e) {
      $conexion->rollback();
      echo json_encode(["Error: " . $e->getMessage(), '0']);
    } finally {
      $stmt->close();
      $conexion->close();
    }
    break;
  case 'delete_perfil_economico':
    $archivo = $_POST["archivo"];

    $validar = validar_campos_plus([
      [$archivo[0], "", 'Debe seleccionar un registro a eliminar', 1],
      [$archivo[1], "", 'Debe seleccionar un cliente', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }

    $id = $archivo[0];
    $conexion->autocommit(FALSE);
    try {
      $stmt = $conexion->prepare("DELETE FROM tb_ingresos WHERE id_ingre_dependi = ?");
      $stmt->bind_param('i', $id);
      if ($stmt->execute()) {
        $conexion->commit();
        echo json_encode(["Registro de perfil economico eliminado satisfactoriamente: " . $archivo[1], '1']);
      } else {
        $errorMsg = $stmt->error;
        $conexion->rollback();
        echo json_encode(["Error al ejecutar consulta: $errorMsg", '0']);
      }
    } catch (Exception $e) {
      $conexion->rollback();
      echo json_encode(["Error: " . $e->getMessage(), '0']);
    } finally {
      $stmt->close();
      $conexion->close();
    }
    break;
  case 'create_cliente_juridico':
    $inputs = $_POST["inputs"];
    $selects = $_POST["selects"];
    $archivo = $_POST["archivo"];

    $validar = validar_campos_plus([
      [$inputs[0], "", 'Debe ingresar una razón social', 1],
      [$inputs[1], "", 'Debe ingresar un nombre comercial', 1],
      [$inputs[2], "", 'Debe ingresar un numero de registro de sociedad', 1],
      [$inputs[3], "", 'Debe ingresar nombre representante legal', 1],
      [$inputs[4], "", 'Debe digitar una fecha de fundación', 1],
      [$selects[0], "0", 'Debe seleccionar un departamento', 1],
      [$selects[1], "0", 'Debe seleccionar un municipio', 1],
      [$inputs[6], "", 'Debe ingresar el domicilio fiscal', 1],
      [$inputs[7], "", 'Debe ingresar el nombre de presidente(a)', 1],
      [$inputs[8], "", 'Debe ingresar el nombre de vicepresidente(a)', 1],
      [$inputs[9], "", 'Debe ingresar el nombre de secretario(a)', 1],
      [$inputs[10], "", 'Debe ingresar el nombre de tesorero(a)', 1],
      [$inputs[11], "", 'Debe ingresar el nombre de vocal 1', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }
    // Si la referencia y vocal 2 esta vacio, colocar guiones
    $inputs[5] = ($inputs[5] == "") ? '-' :  $inputs[5];
    $inputs[12] = ($inputs[12] == "") ? '-' :  $inputs[12];

    //GENERAR EL CODIGO DEL CLIENTE
    $gencodigo = getcodcli($archivo[0], $conexion);
    if ($gencodigo[0] == 0) {
      echo json_encode([$gencodigo[1], '0']);
      return;
    }
    $codgen = $gencodigo[1];
    //PREPARACION DE ARRAY
    $data = array(
      'idcod_cliente' => $codgen,
      'id_tipoCliente' => 'JURIDICO',
      'agencia' => $archivo[2],
      'primer_name' => '-',
      'segundo_name' => '-',
      'tercer_name' => '-',
      'primer_last' => '-',
      'segundo_last' => '-',
      'casada_last' => '-',
      'short_name' => $inputs[1],
      'compl_name' => $inputs[0],
      'url_img' => '',
      'date_birth' => $inputs[4],
      'no_identifica' => $inputs[2],
      'identi_tribu' => 'CUI',
      'no_tributaria' => $inputs[2],
      'Direccion' => $inputs[6],
      'depa_reside' => $selects[0],
      'muni_reside' => $selects[1],
      'aldea_reside' => $inputs[5],
      'representante_name' => $inputs[3],
      'estado' => '1',
      'fecha_alta' => date('Y-m-d'),
      'fecha_mod' => date('Y-m-d'),
    );

    $conexion->autocommit(FALSE);
    try {
      // //INSERCION DE CLIENTE NATURAL
      $columns = implode(', ', array_keys($data));
      $placeholders = implode(', ', array_fill(0, count($data), '?'));
      $stmt = $conexion->prepare("INSERT INTO tb_cliente ($columns) VALUES ($placeholders)");
      // Obtener los valores del array de datos
      $values = array_values($data);
      // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
      $types = str_repeat('s', count($values));
      // Vincular los parámetros
      $id_cliente_natural =  "";
      $stmt->bind_param($types, ...$values);
      if (!$stmt->execute()) {
        $errorMsg = $stmt->error;
        $conexion->rollback();
        echo json_encode(["Error al ejecutar consulta: $errorMsg", '0']);
        return;
      }
      // INSERCION DE SOCIOS DE CLIENTES JURIDICOS
      $data2 = array(
        array(
          'name_socio' => $inputs[7],
          'puesto_socio' => 'Presidente',
          'id_clnt_ntral' => $codgen
        ),
        array(
          'name_socio' => $inputs[8],
          'puesto_socio' => 'Vicepresidente',
          'id_clnt_ntral' => $codgen
        ),
        array(
          'name_socio' => $inputs[9],
          'puesto_socio' => 'Secretario',
          'id_clnt_ntral' => $codgen
        ),
        array(
          'name_socio' => $inputs[10],
          'puesto_socio' => 'Tesorero',
          'id_clnt_ntral' => $codgen
        ),
        array(
          'name_socio' => $inputs[11],
          'puesto_socio' => 'Vocal 1',
          'id_clnt_ntral' => $codgen
        ),
        array(
          'name_socio' => $inputs[12],
          'puesto_socio' => 'Vocal 2',
          'id_clnt_ntral' => $codgen
        )
      );

      foreach ($data2 as $key => $value) {
        $columns = implode(', ', array_keys($value));
        $placeholders = implode(', ', array_fill(0, count($value), '?'));
        $stmt2 = $conexion->prepare("INSERT INTO tb_socios_juri ($columns) VALUES ($placeholders)");
        // Obtener los valores del array de datos
        $values = array_values($value);
        // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
        $types = str_repeat('s', count($values));
        // Vincular los parámetros
        $stmt2->bind_param($types, ...$values);
        if (!$stmt2->execute()) {
          $errorMsg = $stmt2->error;
          $conexion->rollback();
          echo json_encode(["Error al ejecutar consulta 2: $errorMsg", '0']);
          return;
        }
      }
      //Realizar el commit especifico
      $conexion->commit();
      echo json_encode(["Cliente juridico ingresado correctamente: " . $codgen, '1']);
    } catch (Exception $e) {
      $conexion->rollback();
      echo json_encode(["Error: " . $e->getMessage(), '0']);
    } finally {
      $stmt->close();
      $stmt2->close();
      $conexion->close();
    }
    break;
  case 'update_cliente_juridico':
    $inputs = $_POST["inputs"];
    $selects = $_POST["selects"];
    $archivo = $_POST["archivo"];
    // COLOCAR EL VALOR DE LA AGENCIA

    $validar = validar_campos_plus([
      [$archivo[3], "", 'Debe seleccionar un cliente juridico a actualizar', 1],
      [$inputs[0], "", 'Debe ingresar una razón social', 1],
      [$inputs[1], "", 'Debe ingresar un nombre comercial', 1],
      [$inputs[2], "", 'Debe ingresar un numero de registro de sociedad', 1],
      [$inputs[3], "", 'Debe ingresar nombre representante legal', 1],
      [$inputs[4], "", 'Debe digitar una fecha de fundación', 1],
      [$selects[0], "0", 'Debe seleccionar un departamento', 1],
      [$selects[1], "0", 'Debe seleccionar un municipio', 1],
      [$inputs[6], "", 'Debe ingresar el domicilio fiscal', 1],
      [$inputs[7], "", 'Debe ingresar el nombre de presidente(a)', 1],
      [$inputs[8], "", 'Debe ingresar el nombre de vicepresidente(a)', 1],
      [$inputs[9], "", 'Debe ingresar el nombre de secretario(a)', 1],
      [$inputs[10], "", 'Debe ingresar el nombre de tesorero(a)', 1],
      [$inputs[11], "", 'Debe ingresar el nombre de vocal 1', 1],
    ]);
    if ($validar[2]) {
      echo json_encode([$validar[0], $validar[1]]);
      return;
    }
    // Si la referencia y vocal 2 esta vacio, colocar guiones
    $inputs[5] = ($inputs[5] == "") ? '-' :  $inputs[5];
    $inputs[12] = ($inputs[12] == "") ? '-' :  $inputs[12];

    // PREPARACION DE ARRAY
    $data = array(
      'id_tipoCliente' => 'JURIDICO',
      'agencia' => $archivo[2],
      'primer_name' => '-',
      'segundo_name' => '-',
      'tercer_name' => '-',
      'primer_last' => '-',
      'segundo_last' => '-',
      'casada_last' => '-',
      'short_name' => $inputs[1],
      'compl_name' => $inputs[0],
      'url_img' => '',
      'date_birth' => $inputs[4],
      'no_identifica' => $inputs[2],
      'identi_tribu' => 'CUI',
      'no_tributaria' => $inputs[2],
      'Direccion' => $inputs[6],
      'depa_reside' => $selects[0],
      'muni_reside' => $selects[1],
      'aldea_reside' => $inputs[5],
      'representante_name' => $inputs[3],
      'estado' => '1',
      'fecha_mod' => date('Y-m-d'),
    );

    $id = $archivo[3];
    $conexion->autocommit(FALSE);
    try {
      // Columnas a actualizar
      $setCols = [];
      foreach ($data as $key => $value) {
        $setCols[] = "$key = ?";
      }
      $setStr = implode(', ', $setCols);
      $stmt = $conexion->prepare("UPDATE tb_cliente SET $setStr WHERE idcod_cliente = ?");
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

      //ELIMINAR LOS REGISTROS ANTERIORES
      $stmt = $conexion->prepare("DELETE FROM tb_socios_juri WHERE id_clnt_ntral = ?");
      $stmt->bind_param('s', $id);
      if (!$stmt->execute()) {
        $errorMsg = $stmt->error;
        $conexion->rollback();
        echo json_encode(["Error al ejecutar consulta 2: $errorMsg", '0']);
        return;
      }

      // INSERCION DE SOCIOS DE CLIENTES JURIDICOS
      $data2 = array(
        array(
          'name_socio' => $inputs[7],
          'puesto_socio' => 'Presidente',
          'id_clnt_ntral' => $id
        ),
        array(
          'name_socio' => $inputs[8],
          'puesto_socio' => 'Vicepresidente',
          'id_clnt_ntral' => $id
        ),
        array(
          'name_socio' => $inputs[9],
          'puesto_socio' => 'Secretario',
          'id_clnt_ntral' => $id
        ),
        array(
          'name_socio' => $inputs[10],
          'puesto_socio' => 'Tesorero',
          'id_clnt_ntral' => $id
        ),
        array(
          'name_socio' => $inputs[11],
          'puesto_socio' => 'Vocal 1',
          'id_clnt_ntral' => $id
        ),
        array(
          'name_socio' => $inputs[12],
          'puesto_socio' => 'Vocal 2',
          'id_clnt_ntral' => $id
        )
      );

      foreach ($data2 as $key => $value) {
        $columns = implode(', ', array_keys($value));
        $placeholders = implode(', ', array_fill(0, count($value), '?'));
        $stmt2 = $conexion->prepare("INSERT INTO tb_socios_juri ($columns) VALUES ($placeholders)");
        // Obtener los valores del array de datos
        $values = array_values($value);
        // Obtener los tipos de datos para los valores (pueden ser todos 's' para cadena)
        $types = str_repeat('s', count($values));
        // Vincular los parámetros
        $stmt2->bind_param($types, ...$values);
        if (!$stmt2->execute()) {
          $errorMsg = $stmt2->error;
          $conexion->rollback();
          echo json_encode(["Error al ejecutar consulta 3: $errorMsg", '0']);
          return;
        }
      }
      //Realizar el commit especifico
      $conexion->commit();
      echo json_encode(["Cliente jurídico actualizado correctamente: " . $archivo[3], '1']);
    } catch (Exception $e) {
      $conexion->rollback();
      echo json_encode(["Error: " . $e->getMessage(), '0']);
    } finally {
      $stmt->close();
      $stmt2->close();
      $conexion->close();
    }
    break;
    // ------------> NEGROY dpivalidate validaciones ≽^•⩊•^≼
  case 'dpivalidate':
    $dpi = $_POST["dpi"];
    $cli = $_POST["cli"];
    if ($cli == 1) { // validamos solo DPI CLIENTE NUEVO 
      $consulta = "SELECT idcod_cliente, no_identifica, agencia, short_name 
        FROM `tb_cliente` WHERE no_identifica= '$dpi';";
    } else { // VALIDO con el codigo del cliente, si ya exite algun cliente != CLIENTE_Id
      $consulta = "SELECT idcod_cliente, no_identifica, agencia, short_name 
        FROM `tb_cliente` WHERE no_identifica = $dpi AND idcod_cliente != '$cli';";
    }
    $queryins = mysqli_query($conexion, $consulta);
    $numeroResultados = mysqli_num_rows($queryins);
    echo $numeroResultados;
    return;
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

function validar_expresion_regular($cadena, $expresion_regular)
{
  if (preg_match($expresion_regular, $cadena)) {
    return false;
  } else {
    return true;
  }
}

//FUNCION PARA CONCATENAR NOMBRES
function concatenar_nombre($array1, $array2, $separador)
{
  $concatenado = '';
  foreach ($array1 as $valor) {
    if (!empty($valor)) {
      // Convertir a mayúsculas maneando tildes
      $valor = mb_strtoupper($valor, 'UTF-8');
      $concatenado .= $valor . ' ';
    }
  }
  $concatenado2 = '';
  foreach ($array2 as $valor) {
    if (!empty($valor)) {
      // Convertir a mayúsculas maneando tildes
      $valor = mb_strtoupper($valor, 'UTF-8');
      $concatenado2 .= $valor . ' ';
    }
  }
  return trim($concatenado) . $separador . trim($concatenado2);
}
