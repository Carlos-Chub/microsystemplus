<?php
session_start();
$usuario = $_SESSION["id"];
date_default_timezone_set('America/Guatemala');
$hoy = date("Y-m-d");
$hoy2 = date("Y-m-d H:i:s");
include '../../../includes/BD_con/db_con.php';
include '../cre_grupo/functions/group_functions.php';
include_once "../../../src/cris_modales/mdls_editReciboCreGrupo.php";

mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');

$condi = $_POST["condi"];
switch ($condi) {
    case 'recibosgrupales':
        $codusu = $_SESSION['id'];
        $where = "";
        $mensaje_error = "";
        $bandera_error = false;
        //Validar si ya existe un registro igual que el nombre
        $nuew = "ccodusu='$codusu' AND (dfecsis BETWEEN '" . date('Y-m-d', strtotime(date('Y-m-d') . ' - 7 days')) . "' AND  '" . date('Y-m-d') . "')";
        try {
            $stmt = $conexion->prepare("SELECT IF(tu.puesto='ADM' OR tu.puesto='GER', '1=1', ?) AS valor FROM tb_usuario tu WHERE tu.id_usu = ?");
            if (!$stmt) {
                throw new Exception("Error en la consulta: " . $conexion->error);
            }
            $stmt->bind_param("ss", $nuew, $usuario);
            if (!$stmt->execute()) {
                throw new Exception("Error al consultar: " . $stmt->error);
            }
            $result = $stmt->get_result();
            $whereaux = $result->fetch_assoc();
            $where = $whereaux['valor'];
            // if ($usuario=='27') { //--REQ--fape--3--Permisos fape para un usuario especial
            // 	$where='1=1';
            // }
        } catch (Exception $e) {
            //Captura el error
            $mensaje_error = $e->getMessage();
            $bandera_error = true;
        }
?>
        <input type="text" id="file" value="caja003" style="display: none;">
        <input type="text" id="condi" value="recibosgrupales" style="display: none;">
        <div class="text" style="text-align:center">RECIBOS DE CREDITOS</div>
        <div class="card">
            <div class="card-header">RECIBOS DE CREDITOS</div>
            <div class="card-body">
                <?php if ($bandera_error) { ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>¡Error!</strong> <?= $mensaje_error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } ?>
                <!-- tabla -->
                <div class="container contenedort" style="padding: 10px 8px 10px 8px !important;">
                    <div class="table-responsive">
                        <table id="tabla_recibos_grupales" class="table table-hover table-border nowrap" style="width:100%">
                            <thead class="text-light table-head-aho" style="font-size: 0.8rem;">
                                <tr>
                                    <!-- <th>No.</th> -->
                                    <th>Nombre Grupo</th>
                                    <th>Ciclo</th>
                                    <th>No. Recibo</th>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <script>
                $(document).ready(function() {
                    $("#tabla_recibos_grupales").DataTable({
                        "processing": true,
                        "serverSide": true,
                        "sAjaxSource": "../../src/server_side/recibo_credito_grupales.php",
                        columns: [{
                                data: [4]
                            },
                            {
                                data: [6]
                            },
                            {
                                data: [1]
                            },
                            {
                                data: [2]
                            },
                            {
                                data: [3]
                            },
                            {
                                data: [0], //Es la columna de la tabla
                                render: function(data, type, row) {
                                    imp = '';
                                    imp1 = '';
                                    imp2 = '';
                                    const separador = "||";
                                    var dataRow = row.join(separador);

                                    imp =
                                        `<button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="reportes([[], [], [], ['${row[5]}', '${row[1]}', '${row[6]}']], 'pdf', '15', 0,1)"><i class="fa-solid fa-print me-2"></i>Reimprimir</button>`;
                                    if (row[9] == "1") {
                                        imp1 =
                                            `<button type="button" class="btn btn-outline-secondary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalCreReGrup" onclick="capData('${dataRow}',['#idGru','#ciclo','#fecha', '#nomGrupo', '#codGrup', '#recibo', '#antRe'],[5,6,2,4,7,1,1]);inyecCod('#integrantes','reciboDeGrupos','${row[1]}||${row[5]}||${row[6]}')"><i class="fa-sharp fa-solid fa-pen-to-square"></i></button>`;
                                        imp2 =
                                            `<button type="button" class="btn btn-outline-danger btn-sm mt-2" onclick="eliminar('${row[1]}|*-*|${row[5]}|*-*|${row[6]}','eliReGru', '<?= $_SESSION['id']; ?>');"><i class="fa-solid fa-trash-can"></i></button>`;
                                    }
                                    return imp + imp1 + imp2;
                                }
                            },
                        ],
                        "fnServerParams": function(aoData) {
                            //PARAMETROS EXTRAS QUE SE LE PUEDEN ENVIAR AL SERVER ASIDE
                            aoData.push({
                                "name": "whereextra",
                                "value": "<?= $where; ?>"
                            });
                        },
                        "bDestroy": true,
                        "language": {
                            "lengthMenu": "Mostrar _MENU_ registros",
                            "zeroRecords": "No se encontraron registros",
                            "info": " ",
                            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                            "infoFiltered": "(filtrado de un total de: _MAX_ registros)",
                            "sSearch": "Buscar: ",
                            "oPaginate": {
                                "sFirst": "Primero",
                                "sLast": "Ultimo",
                                "sNext": "Siguiente",
                                "sPrevious": "Anterior"
                            },
                            "sProcessing": "Procesando..."
                        }

                    });
                });
            </script>
        </div>
<?php
        break;
} ?>