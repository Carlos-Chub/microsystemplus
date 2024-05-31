<?php
session_start();
include '../includes/Config/config.php';
if (!isset($_SESSION['usu'])) {
  header('location: ' . BASE_URL);
} else {
  include '../includes/BD_con/db_con.php';
  mysqli_set_charset($conexion, 'utf8');
  date_default_timezone_set('America/Guatemala');
?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otros ingresos</title>
    <link rel="shortcut icon" type="image/x-icon" href="../includes/img/favmicro.ico">
    <link rel="stylesheet" href="../includes/css/style.css">

    <?php
    require_once '../includes/incl.php';
    ?>
  </head>

  <body class="<?= ($_SESSION['background'] == '1') ? 'dark' : ''; ?>">
    <?php require '../src/menu/menu_bar.php';
    require 'infoEnti/infoEnti.php';

    $infoEnti = infoEntidad($_SESSION['id'], $conexion);
    ?>

    <section class="home">
      <div class="container" style="max-width: none !important;">
        <div class="row">
          <div class="col d-flex justify-content-start">
            <div class="text">MODULO OTROS GASTOS</div>
          </div>
          <div class="col d-flex justify-content-end">
            <div class="text"><?= $infoEnti['nomAge'] ?></div>
          </div>
        </div>

        <div class="btn-group" id="nav_group" role="group">
          <!-- IMPRESION DE OPCIONES -->
          <?php
          $consulta = "SELECT tbp.id_usuario, tbs.id AS menu, tbs.descripcion, tbm.id AS opcion, tbm.condi, tbm.`file`, tbm.caption FROM tb_usuario tbu
        INNER JOIN tb_permisos2 tbp ON tbu.id_usu=tbp.id_usuario
        INNER JOIN clhpzzvb_bd_general_coopera.tb_submenus tbm ON tbp.id_submenu=tbm.id
        INNER JOIN clhpzzvb_bd_general_coopera.tb_menus tbs ON tbm.id_menu =tbs.id
        INNER JOIN clhpzzvb_bd_general_coopera.tb_modulos tbo ON tbs.id_modulo =tbo.id
        INNER JOIN clhpzzvb_bd_general_coopera.tb_permisos_modulos tbps ON tbo.id=tbps.id_modulo
        WHERE tbu.id_usu=" . $_SESSION['id'] . " AND tbo.estado='1' AND tbs.estado='1' AND tbm.estado='1' AND tbps.estado='1' AND
          tbps.id_cooperativa=(SELECT ag1.id_institucion FROM tb_agencia ag1 LIMIT 1) 
        AND tbo.rama='G' AND  tbo.id=18  ORDER BY tbo.orden, tbs.orden, tbm.orden ASC";

          $valores[] = [];
          $j = 0;
          $resultado = mysqli_query($conexion, $consulta);
          while ($fila = mysqli_fetch_array($resultado, MYSQLI_ASSOC)) {
            $valores[$j] = $fila;
            $j++;
          }

          $bandera = true;
          $bandera2 = false;
          $bandera3 = false; //esto
          for ($i = 0; $i < $j; $i++) {
            if ($i == 0) {
              //MENU 1
              echo '<div class="btn-group me-1" role="group">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">' . $valores[$i]['descripcion'] . '
                            <span class="caret"></span></button>
                            <ul class="dropdown-menu">';
              //PRIMER LI
              echo '<li><a class="dropdown-item" style="cursor: pointer;" onclick="printdiv(`' . $valores[$i]['condi'] . '`, `#cuadro`, `' . $valores[$i]['file'] . '`, `0`)">' . $valores[$i]['caption'] . '</a></li>';

              $aux2 = $valores[$i]['menu'];
              $bandera = false;
              $bandera3 = true; //esto
            }

            $aux = $valores[$i]['menu'];
            if ($aux != $aux2) {
              $aux2 = $valores[$i]['menu'];
              $bandera2 = true;
              $bandera3 = false; //esto
              //CIERRE DE MENU
              echo '</ul>
                            </div>';
              //APERTURA DE SIGUIENTE MENU
              echo '<div class="btn-group me-1" role="group">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">' . $valores[$i]['descripcion'] . '
                            <span class="caret"></span></button>
                            <ul class="dropdown-menu">';
              echo '<li><a class="dropdown-item" style="cursor: pointer;" onclick="printdiv(`' . $valores[$i]['condi'] . '`, `#cuadro`, `' . $valores[$i]['file'] . '`, `0`)">' . $valores[$i]['caption'] . '</a></li>';
            } elseif ($aux == $aux2 && $bandera) {
              //SIGUIENTES LI
              if (($i + 1) != count($valores)) {
                echo '<li><a class="dropdown-item" style="cursor: pointer;" onclick="printdiv(`' . $valores[$i]['condi'] . '`, `#cuadro`, `' . $valores[$i]['file'] . '`, `0`)">' . $valores[$i]['caption'] . '</a></li>';
                $bandera2 = false;
                $bandera3 = false; //esto
              }
            }

            if (($i + 1) == count($valores)) {
              if ($bandera2) {
                echo '</ul>
                  </div>';
              } else if ($bandera3) {
                echo '</ul>
                  </div>';
              } else {
                echo '<li><a class="dropdown-item" style="cursor: pointer;" onclick="printdiv(`' . $valores[$i]['condi'] . '`, `#cuadro`, `' . $valores[$i]['file'] . '`, `0`)">' . $valores[$i]['caption'] . '</a></li>';
                echo '</ul>
                                </div>';
              }
            }
            $bandera = true;
            $bandera2 = false;
            $bandera3 = false;
          }
          ?>
          <!-- CIERRE DE IMPRESION DE OPCIONES -->
        </div>
        <button type="button" class="btn btn-warning" onclick="window.location.reload();">RELOAD <i class="fa-solid fa-arrow-rotate-right"></i> </button>

        <br>

        <div id="cuadro">
          <div class="d-flex flex-column h-100">
            <div class="flex-grow-1">
              <div class="row align-items-center" style="max-width: none !important; height: calc(75vh) !important;">
                <div class="row d-flex justify-content-center">
                  <div class="col-auto">
                    <img src="<?= '..' . $infoEnti['imagenEnti'] ?>" alt="" srcset="" width="500">
                    <p class="displayed text-success text-center" style='font-family: "Garamond", serif;
                      font-weight: bold;
                      font-size: x-large;'> Sistema orientado para microfinanzas </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <div class="loader-container loading--show">
      <div class="loader"></div>
      <div class="loaderimg"></div>
      <div class="loader2"></div>
    </div>


    <script src="../includes/js/script.js"></script>
    <script src="../includes/js/scrpt_otrosingresos.js"></script>

  </body>

  </html>
<?php
}
?>