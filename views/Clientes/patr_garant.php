<?php
//session_start();
include '../../includes/BD_con/db_con.php';
// BUSQUEDA GENERAL DE CLIENTE PARA TODOS 

function bscqClnt($idPerfil)
{
  include '../../includes/BD_con/db_con.php';
  //MISMA FUNCION EN TEM_CLI- PerfEconomico
  if ($idPerfil == "0") {
    $nombreCompleto = "Buscar";
    $idPerfil1 = "";
    return array($idPerfil1, $nombreCompleto);
  } else {
    $traerDatosCliente = mysqli_query($conexion, "SELECT compl_name FROM `tb_cliente` where idcod_cliente = " . $idPerfil);
    $datosCliente = mysqli_fetch_array($traerDatosCliente);
    $nombreCompleto = utf8_encode($datosCliente["compl_name"]);
    return array($idPerfil, $nombreCompleto);
  }
}

$condi = $_POST["condi"];

switch ($condi) {
  case 'productos':
    $idPerfil = $_POST["xtra"];
    $vlaos = bscqClnt($idPerfil);
  ?>
    <!-- ----------- inicio container 1---------------- -->
    <div class="text" style="text-align: center">PRODUCTOS</div>
    <input type="text" id="condi" value="productos" hidden>
    <input type="text" id="file" value="patr_garant" hidden>
    <div class="container">
      <div class="card crdbody">
        <div class="card-header panelcolor">HISTORIAL</div>
        <div class="card-body">

          <div class="row col-md-2 crdbody">
            <button type="button" class="btn btn-outline-primary" title="Buscar Grupo" data-bs-toggle="modal" data-bs-target="#buscar_cli_gen">
              <i class="fa-solid fa-magnifying-glass"></i> Buscar Cliente </button>
          </div>

          <div class="row crdbody">
            <div class="col-md-3">
              <div class="input-group ">
                <span class="input-group-text">Codigo</span>
                <input type="text" class="form-control" id="ccodcli" placeholder="Codigo del Cliente" value="<?php echo $vlaos[0]; ?>" readonly>
              </div>
            </div>

            <div class="col-md-6">
              <div class="input-group ">
                <span class="input-group-text">Nombre</span>
                <input type="text" class="form-control" id="nom" placeholder='Nombre Cliente' value="<?php echo $vlaos[1]; ?>" readonly>
              </div>
            </div>
          </div>
          <!-- Inicio de tabla responsive-->
          <table id="tlbProductos" class="table table-responsive">
            <thead>
              <tr>
                <th>No.</th>
                <th>Tipo de producto</th>
                <th>Nombre de producto</th>
                <th>Cod. Cuenta</th>
                <th>Monto Inicial</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($idPerfil == 0) {
                echo "";
              } else {
                //consulta a cuentas de ahorro
                $consulta1 = mysqli_query($conexion, "SELECT 'Ahorro' AS tipo, aht.nombre AS descripcion, aho.ccodaho AS cuenta, calcularsaldocuentaahom(aho.ccodaho) AS saldo FROM ahomcta aho
                INNER JOIN tb_cliente cl ON aho.ccodcli = cl.idcod_cliente
                INNER JOIN ahomtip aht ON aht.ccodtip = SUBSTR(aho.ccodaho, 7, 2)
                WHERE aho.estado='A' AND cl.idcod_cliente = '" . $idPerfil . "'");
                //consulta a cuentas de aportaciones
                $consulta2 = mysqli_query($conexion, "SELECT 'Aportación' AS tipo, apt.nombre AS descripcion, apr.ccodaport AS cuenta, calcularsaldocuentaprt(apr.ccodaport) AS saldo FROM aprcta apr
                INNER JOIN tb_cliente cl ON apr.ccodcli = cl.idcod_cliente
                INNER JOIN aprtip apt ON apt.ccodtip = apr.ccodtip
                WHERE apr.estado='A' AND cl.idcod_cliente = '" . $idPerfil . "'");
                //consultas a cuentas de aportaciones
                $consulta3 = mysqli_query($conexion, "SELECT 'Crédito' AS tipo, pr.descripcion AS descripcion, cm.CCODCTA AS cuenta, cm.MonSug AS saldo FROM cremcre_meta cm
                INNER JOIN cre_productos pr ON cm.CCODPRD= pr.id
                WHERE cm.Cestado='F' AND cm.CodCli = '" . $idPerfil . "'");
                //unificar el resultado de las 3 consultas
                $datos[] = [];
                $i = 0;
                while ($fila = mysqli_fetch_array($consulta1, MYSQLI_ASSOC)) {
                  $datos[$i] = $fila;
                  $datos[$i]['numero'] = $i + 1;
                  $i++;
                }
                while ($fila = mysqli_fetch_array($consulta2, MYSQLI_ASSOC)) {
                  $datos[$i] = $fila;
                  $datos[$i]['numero'] = $i + 1;
                  $i++;
                }
                while ($fila = mysqli_fetch_array($consulta3, MYSQLI_ASSOC)) {
                  $datos[$i] = $fila;
                  $datos[$i]['numero'] = $i + 1;
                  $i++;
                }

                $j = 0;
                foreach ($datos as $dato) { ?>
                  <tr>
                    <th scope="row"><?= $dato['numero']; ?></th>
                    <td><?= $dato['tipo']; ?></td>
                    <td><?= utf8_encode($dato['descripcion']); ?></td>
                    <td><?= $dato['cuenta']; ?></td>
                    <td><?= $dato['saldo']; ?></td>
                  </tr>
            </tbody>
        <?php }
              } ?>
          </table>
        </div>
      </div>
      <?php include_once "../../src/cris_modales/mdls_cli.php"; ?>
<?php
    break;
}
?>