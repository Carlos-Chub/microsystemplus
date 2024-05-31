<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];
$hoy = date("Y-m-d");
$hoy2 = date("Y-m-d H:i:s");

switch ($condi) {
    case 'report_ingresos': {
            $id = $_POST["xtra"];
            $codusu = $_SESSION['id'];
            $agencia = $_SESSION['agencia'];
?>
            <!-- APR_05_LstdCntsActvsDspnbls -->
            <div class="text" style="text-align:center">REPORTE DE OTROS INGRESOS</div>
            <input type="text" value="report_ingresos" id="condi" style="display: none;">
            <input type="text" value="otros_ingresos_02" id="file" style="display: none;">

            <div class="card">
                <div class="card-header">REPORTE DE OTROS INGRESOS</div>
                <div class="card-body">
                    <!-- segunda linea -->
                    <div class="row d-flex align-items-stretch mb-3">
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro de tipo de movimiento</b></div>
                                <div class="card-body">
                                    <div class="row mt-3">
                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_movimiento" id="r_movimiento" value="1" checked>
                                                <label class="form-check-label" for="r_movimiento">Ingresos</label>
                                            </div>
                                        </div>
                                        <div class="col d-flex justify-content-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="filter_movimiento" id="r_movimiento" value="2">
                                                <label class="form-check-label" for="r_movimiento">Egresos</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- card para seleccionar una cuenta -->
                        <div class="col-6">
                            <div class="card" style="height: 100% !important;">
                                <div class="card-header"><b>Filtro por fecha</b></div>
                                <div class="list-group list-group-flush card-body ps-3">
                                    <div class="row mb-1">
                                        <div class="col-6">
                                            <span class="input-group-addon">Desde:</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="input-group-addon">Hasta:</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="input-group">
                                                <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?php echo date("Y-m-d"); ?>" id="fechaInicio">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group">
                                                <input type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1" value="<?php echo date("Y-m-d"); ?>" id="fechaFinal">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Card para las transacciones -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><b>Filtro de agencia</b></div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- radio button para los tipos de transacciones -->
                                        <div class="col d-flex justify-content-center">
                                            <select class="form-select" aria-label="Default select example" id="agencia">
                                                <option selected value="0">Todas las agencias</option>
                                                <?php
                                                $data = mysqli_query($conexion, "SELECT id_agencia, CONCAT(nom_agencia,' - ',cod_agenc) AS nombre FROM tb_agencia");
                                                while ($dato = mysqli_fetch_array($data, MYSQLI_ASSOC)) { ?>
                                                    <option value="<?= $dato["id_agencia"]; ?>"><?= $dato["nombre"] ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="row justify-items-md-center">
                        <div class="col align-items-center" id="modal_footer">
                            <button type="button" id="btnSave" class="btn btn-outline-primary" onclick="reportes([[`fechaInicio`,`fechaFinal`],[`agencia`],[`filter_movimiento`],['<?= $_SESSION['id_agencia'];?>','<?= $_SESSION['nombre'] . ' ' . $_SESSION['apellido']; ?>','<?= $_SESSION['id'];?>']],`pdf`,`reporte_ingresos`,0)">
                                <i class="fa-solid fa-file-pdf"></i> Reporte en PDF
                            </button>

                            <button type="button" class="btn btn-outline-danger" onclick="printdiv2('#cuadro','0')">
                                <i class="fa-solid fa-ban"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-outline-warning" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
<?php
        }
        break;
}

?>