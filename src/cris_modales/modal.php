
<?php
include '../../includes/BD_con/db_con.php';
include '../funcphp/func_gen.php';
$condi = $_POST["condi"];

switch ($condi) {
  case "grupo":
    $consulta2 = mysqli_query($conexion, "SELECT * FROM `tb_grupo` where estado=1");

    while ($registro = mysqli_fetch_array($consulta2, MYSQLI_ASSOC)) {
      $codigo_grupo = ($registro["codigo_grupo"]);
      $NombreGrupo = utf8_encode($registro["NombreGrupo"]);
      $fecha_sys = ($registro["fecha_sys"]);
      $direc = utf8_encode($registro["direc"]);
      $id_grupos = ($registro["id_grupos"]);
      $canton = utf8_encode($registro["canton"]);
      $Depa = ($registro["Depa"]);
      $Muni = ($registro["Muni"]);
      echo '
      <tr> 
      <td>' . $codigo_grupo . '</td>
      <td>' . $NombreGrupo . '</td>
      <td>' . $fecha_sys . '</td>
      <td>' . $direc . '</td>
      <td> <button type="button" class="btn btn-success" onclick="instgrp(&apos;' . $codigo_grupo . '&apos;, &apos;' . $NombreGrupo . '&apos;, &apos;' . $fecha_sys . '&apos;, &apos;' . $direc . '&apos;, &apos;' . $id_grupos . '&apos; ,  &apos;' . $Muni . '&apos;, &apos;' . $Depa . '&apos; , &apos;' . $canton . '&apos;)" >Aceptar</button> </td>
      </tr> ';
    }
    mysqli_close($conexion);
    break;
  case "cuentascli":
    //SUBSTR EN PHP INICIA EN 0, SUBSTR EN SQL INICIA EN 1
    $id = $_POST["id"];
    $consulta2 = mysqli_query($conexion, " SELECT aho.ccodaho,aho.ccodcli,aho.nlibreta,cli.no_tributaria num_nit,tip.nombre FROM ahomcta aho 
                        INNER JOIN tb_cliente cli ON cli.idcod_cliente=aho.ccodcli
                        INNER JOIN ahomtip tip ON tip.ccodtip=SUBSTR(aho.ccodaho,7,2) 
                        WHERE aho.estado='A' AND aho.ccodcli='$id'");
    $flag = 0;
    //$con=$conexion;
    while ($registro = mysqli_fetch_array($consulta2, MYSQLI_ASSOC)) {
      $flag = 1;
      // $id = utf8_encode($registro["id_cuenta"]);
      $codigo = '' . ($registro["ccodaho"]);
      $tipo = utf8_decode($registro["nombre"]);
      $ccodcli = ($registro["ccodcli"]);
      $nit = ($registro["num_nit"]);
      $libreta = ($registro["nlibreta"]);
      echo '
          <tr> 
                  <td scope="row">' . $tipo . '</td>
                  <td scope="row">' . $codigo . '</td>
                  <td scope="row"> </td>
                  <td scope="row">' . $libreta . '</td>
                  <td scope="row"> <button type="button" style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;" class="btn btn-primary btn-sm" onclick="printdiv2(`#cuadro`, `' . $codigo . '`)" data-bs-dismiss="modal">Aceptar</button> </td>
          </tr> ';
    }
    if ($flag == 0) {
      echo '
      <tr>
        <td colspan="5" class="alert alert-danger" role="alert">EL CLIENTE SELECCIONADO NO TIENE CUENTAS</td>
      </tr>';
    }
    mysqli_close($conexion);
    break;

  case "cuentas_aport_cli":
    //SUBSTR EN PHP INICIA EN 0, SUBSTR EN SQL INICIA EN 1
    $id = $_POST["id"];
    $consulta2 = mysqli_query($conexion, "SELECT aho.ccodaport,aho.ccodcli,aho.nlibreta,cli.no_tributaria num_nit,tip.nombre FROM aprcta aho 
                                                INNER JOIN tb_cliente cli ON cli.idcod_cliente=aho.ccodcli
                                                INNER JOIN aprtip tip ON tip.ccodtip=SUBSTR(aho.ccodaport,7,2) 
                                                WHERE aho.estado='A' AND aho.ccodcli='$id'");
    $flag = 0;
    while ($registro = mysqli_fetch_array($consulta2, MYSQLI_ASSOC)) {
      $flag = 1;
      // $id = utf8_encode($registro["id_cuenta"]);
      $codigo = '' . ($registro["ccodaport"]);
      $tipo = utf8_decode($registro["nombre"]);
      $ccodcli = ($registro["ccodcli"]);
      $nit = ($registro["num_nit"]);
      $libreta = ($registro["nlibreta"]);
      echo '
      <tr> 
              <td scope="row">' . $tipo . '</td>
              <td scope="row">' . $codigo . '</td>
              <td scope="row"> </td>
              <td scope="row">' . $libreta . '</td>
              <td scope="row"> <button style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;" type="button" class="btn btn-primary" onclick="printdiv2(`#cuadro`, `' . $codigo . '`)" data-bs-dismiss="modal">Aceptar</button> </td>
      </tr> ';
    }
    if ($flag == 0) {
      echo '
  <tr>
    <td colspan="5" class="alert alert-danger" role="alert">EL CLIENTE SELECCIONADO NO TIENE CUENTAS</td>
  </tr>';
    }
    mysqli_close($conexion);
    break;
}
