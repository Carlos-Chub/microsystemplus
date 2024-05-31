<?php
session_start();
include '../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];
$codusu = $_SESSION['id'];

switch ($condi) {
    case 'dias_laborales': {
        
            $codusu = $_SESSION['id'];
            $id_agencia = $_SESSION['id_agencia'];
            $codagencia = $_SESSION['agencia'];
            $xtra = $_POST["xtra"];
?>
            <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
            <input type="text" id="file" value="creditos_02" style="display: none;">
            <input type="text" id="condi" value="dias_laborales" style="display: none;">
            <div class="text" style="text-align:center">DIAS LABORALES</div>
            <div class="card">
                <div class="card-header">Días laborales</div>
                <div class="card-body" style="padding-bottom: 0px !important;">
                    <table id="table_id2" class="table table-hover table-border">
                        <thead class="text-light table-head-aprt" style="font-size: 0.8rem;">
                            <tr>
                                <th>ID</th>
                                <th>Día</th>
                                <th>Laboral</th>
                                <th>Acciones</th>
                                <th>Dia ajuste</th>
                            </tr>
                        </thead>
                        <?php

                        $query = "SELECT td.*, (SELECT tdl.dia FROM tb_dias_laborales tdl WHERE tdl.id=td.id_dia_ajuste) AS dia_ajuste FROM tb_dias_laborales td";
                        $result = $conexion->query($query);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td> <?= $row["id"]  ?></td>
                                    <td> <?= $row["dia"] ?></td>
                                    <td>
                                        <?php if ($row["laboral"] == 1) { ?>
                                            <span class="badge text-bg-success">Se labora</span>
                                        <?php } else { ?>
                                            <span class="badge text-bg-secondary">No se labora</span>
                                        <?php } ?>
                                    </td>
                                    <!-- switch -->
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" <?= ($row['laboral'] == 1) ? 'checked' : ' '; ?> id="<?= "S-" . $row["id"]; ?>" onchange="estado_switch('<?= 'S-' . $row['id'] ?>','<?= $row['id']; ?>')">
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($row["laboral"] == 0) {
                                            //BUSCAR OPCIONES DIA
                                            $banderaant = false;
                                            $banderades = false;
                                            $k = 1;
                                            $diasajuste = array();
                                            $idant = $row["id"];
                                            $iddes = $row["id"];
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
                                                        echo json_encode(['Fallo al consultar dia de ajuste', '0']);
                                                        return;
                                                    }
                                                    if (!$res) {
                                                        echo json_encode(['Error al consultar dia de ajuste', '1']);
                                                    }
                                                    //pasar los datos al array
                                                    while ($row2 = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
                                                        $diasajuste[] = $row2;
                                                        $banderaant = true;
                                                    }
                                                }
                                                if ($banderades == false) {
                                                    $res = $conexion->query("SELECT tdl.id AS id, tdl.dia AS dia FROM tb_dias_laborales tdl WHERE (tdl.id = $iddes) AND tdl.laboral = 1");
                                                    $aux = mysqli_error($conexion);
                                                    if ($aux) {
                                                        echo json_encode(['Fallo al consultar dia de ajuste', '0']);
                                                        return;
                                                    }
                                                    if (!$res) {
                                                        echo json_encode(['Error al consultar dia de ajuste', '1']);
                                                    }
                                                    //pasar los datos al array
                                                    while ($row1 = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
                                                        $diasajuste[] = $row1;
                                                        $banderades = true;
                                                    }
                                                }
                                                $k = ($banderaant && $banderades) ? 4 : $k;
                                                $k++;
                                            };
                                        ?>
                                            <div class="row">
                                                <div class="col">
                                                    <select class="form-select form-select-sm" aria-label=".form-select-sm example" onchange="dia_ajuste(this.value, '<?= $row['id']; ?>')">
                                                        <?php
                                                        //IMPRESION DE DIAS
                                                        $selected = "";
                                                        foreach ($diasajuste as $key => $value) {
                                                            ($value["id"] == $row['id_dia_ajuste']) ? $selected = "selected" : $selected = "";
                                                            $nombre = $value["dia"];
                                                            $id_dia = $value["id"];
                                                            echo '<option value="' . $id_dia . '" ' . $selected . '>' . $nombre . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else { ?>
                            <tr>
                                <td colspan='3'>No se encontraron resultados en la consulta.</td>
                            </tr>
                        <?php }
                        ?>
                    </table>
                </div>
            </div>
            <?php
            ?>
            <script>
                function estado_switch(elemento, id) {
                    var switchElement = document.getElementById(elemento);
                    var estado = switchElement.checked;
                    estado = estado ? 1 : 0;
                    obtiene([], [], [], `update_dias_laborales`, `0`, [id, estado]);
                }
                //Funcion para dia de ajuste con select
                function dia_ajuste(id, id_dia_general) {
                    obtiene([], [], [], `update_dia_ajuste`, `0`, [id, id_dia_general]);
                }
            </script>
<?php }
        break;
}
?>