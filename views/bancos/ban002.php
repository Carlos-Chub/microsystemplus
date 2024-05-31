<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../src/funcphp/func_gen.php';
date_default_timezone_set('America/Guatemala');
$idusuario = $_SESSION['id'];
$id_agencia = $_SESSION['id_agencia'];
$codagencia = $_SESSION['agencia'];
$condi = $_POST["condi"];
switch ($condi) {
    case 'libro_bancos':
?>
        <input type="text" id="file" value="ban002" style="display: none;">
        <input type="text" id="condi" value="libro_bancos" style="display: none;">
        <div class="text" style="text-align:center">GENERACION DE LIBRO BANCOS</div>
        <div class="card">
            <div class="card-header">FILTROS</div>
            <div class="card-body">
                <div class="row container contenedort">
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por Oficinas</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="ragencia" id="allofi" value="allofi" checked onclick="changedisabled(`#codofi`,0)">
                                            <label for="allofi" class="form-check-label">Consolidado </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="ragencia" id="anyofi" value="anyofi" onclick="changedisabled(`#codofi`,1)">
                                            <label for="anyofi" class="form-check-label"> Por Agencia</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <span class="input-group-addon col-2">Agencia</span>
                                        <select class="form-select" id="codofi" style="max-width: 70%;" disabled>
                                            <?php
                                            $ofis = mysqli_query($conexion, "SELECT ofi.id_agencia,ofi.cod_agenc,ofi.nom_agencia FROM tb_usuario AS usu INNER JOIN tb_agencia AS ofi 
                                                                ON ofi.id_agencia = usu.id_agencia GROUP BY ofi.id_agencia");
                                            while ($ofi = mysqli_fetch_array($ofis)) {
                                                echo '<option value="' . $ofi['id_agencia'] . '">' . $ofi['cod_agenc'] . " - " . $ofi['nom_agencia'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por Fuente de fondos</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rfondos" id="allf" value="allf" checked onclick="changedisabled(`#fondoid`,0)">
                                            <label for="allf" class="form-check-label">Todo </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rfondos" id="anyf" value="anyf" onclick="changedisabled(`#fondoid`,1)">
                                            <label for="anyf" class="form-check-label"> Por Fuente de fondos</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="fondoid" disabled>
                                                <?php
                                                $fons = mysqli_query($conexion, "SELECT * FROM `ctb_fuente_fondos` where estado=1");
                                                while ($fon = mysqli_fetch_array($fons)) {
                                                    echo '<option value="' . $fon['id'] . '">' . $fon['descripcion'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <label class="text-primary" for="fondoid">Fondos</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row container contenedort">
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por Cuentas</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rcuentas" id="allcuen" value="allcuen" checked onclick="changedisabled(`#btncuenid`,0)">
                                            <label for="allcuen" class="form-check-label">Todo </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="rcuentas" id="anycuen" value="anycuen" onclick="changedisabled(`#btncuenid`,1)">
                                            <label for="anycuen" class="form-check-label"> Una cuenta</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-floating mb-3">
                                            <div class="input-group" style="width:min(70%,32rem);">
                                                <input style="display:none;" type="text" class="form-control" id="idcuenta" value="0">
                                                <input type="text" disabled readonly class="form-control" id="cuenta">
                                                <button disabled id="btncuenid" class="btn btn-outline-success" type="button" onclick="abrir_modal(`#modal_cuentas_bancos`, `#id_modal_bancos`, 'idcuenta,cuenta/A,A/'+'/#/#/#/#')" title="Buscar Cuenta contable"><i class="fa fa-magnifying-glass"></i></button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card text-bg-light" style="height: 100%;">
                            <div class="card-header">Filtro por fechas</div>
                            <div class="card-body">
                                <div class="row" id="filfechas">
                                    <div class=" col-sm-6">
                                        <label for="finicio">Desde</label>
                                        <input type="date" class="form-control" id="finicio" min="1950-01-01" value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                    <div class=" col-sm-6">
                                        <label for="ffin">Hasta</label>
                                        <input type="date" class="form-control" id="ffin" min="1950-01-01" value="<?php echo date("Y-m-d"); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row justify-items-md-center">
                    <div class="col align-items-center">
                        <button type="button" class="btn btn-outline-danger" title="Libro Bancos en pdf" onclick="reportes([[`finicio`,`ffin`,`idcuenta`,`cuenta`],[`codofi`,`fondoid`],[`rcuentas`,`rfondos`,`ragencia`],[]],`pdf`,`libro_bancos`,0)">
                            <i class="fa-solid fa-file-pdf"></i> Pdf
                        </button>
                        <button type="button" class="btn btn-outline-success" title="Libro Bancos en Excel" onclick="reportes([[`finicio`,`ffin`,`idcuenta`,`cuenta`],[`codofi`,`fondoid`],[`rcuentas`,`rfondos`,`ragencia`],[]],`xlsx`,`libro_bancos`,1)">
                            <i class="fa-solid fa-file-excel"></i>Excel
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
        include '../../src/cris_modales/mdls_cuentas_bancos.php';
        break;
    case 'create_and_edit_bancos': {
            $xtra = $_POST["xtra"];
        ?>
            <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
            <input type="text" id="file" value="ban002" style="display: none;">
            <input type="text" id="condi" value="create_and_edit_bancos" style="display: none;">
            <div class="text" style="text-align:center">CREACIÓN Y EDICIÓN DE BANCOS</div>
            <div class="card">
                <div class="card-header">Creación y edición de bancos</div>
                <div class="card-body" style="padding-bottom: 0px !important;">
                    <!-- INFORMACION DE BANCO -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Información de banco</b></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="idbanco" placeholder="Nombre del banco" readonly hidden>
                                    <input type="text" class="form-control" id="nombanco" placeholder="Nombre del banco">
                                    <label for="nombanco">Nombre del banco</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="abreviatura" placeholder="Abreviatura">
                                    <label for="abreviatura">Abreviatura</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABLA PARA LOS DISTINTOS TIPOS DE INGRESOS -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Listado de bancos</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="table-responsive">
                                    <table class="table nowrap table-hover table-border" id="tb_bancos_new" style="width: 100% !important;">
                                        <thead class="text-light table-head-aprt">
                                            <tr style="font-size: 0.9rem;">
                                                <th>#</th>
                                                <th>Nombre banco</th>
                                                <th>Abreviatura</th>
                                                <th>Accciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-group-divider" style="font-size: 0.9rem !important;">
                                            <?php
                                            $consulta = mysqli_query($conexion, "SELECT * FROM tb_bancos tb WHERE tb.estado = '1'");
                                            while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) { ?>
                                                <tr>
                                                    <td><?= ($fila["id"]) ?></td>
                                                    <td><?= ($fila["nombre"]) ?></td>
                                                    <td><?= ($fila["abreviatura"]) ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-success btn-sm" onclick="printdiv5('idbanco,nombanco,abreviatura/A,A,A/-/#/#/#/#', ['<?= $fila['id'] ?>','<?= $fila['nombre'] ?>','<?= $fila['abreviatura'] ?>']); HabDes_boton(1);"><i class="fa-solid fa-eye"></i></button>
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="eliminar('<?= $fila['id'] ?>', 'crud_bancos', '0', 'delete_banco')"><i class="fa-solid fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- NAVBAR PARA LOS DISTINTOS TIPOS DE INGRESOS -->
                </div>
                <div class="container" style="max-width: 100% !important;">
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center mb-3 ms-2" id="modal_footer">
                            <!-- boton para solicitar credito -->
                            <button id="btGuardar" class="btn btn-outline-success mt-2" onclick="obtiene([`nombanco`,`abreviatura`],[],[],`create_banco`,`0`,['<?= $idusuario; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar</button>
                            <button id="btEditar" class="btn btn-outline-primary mt-2" onclick="obtiene([`nombanco`,`abreviatura`,`idbanco`],[],[],`update_banco`,`0`,['<?= $idusuario; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Actualizar</button>
                            <button type="button" class="btn btn-outline-danger mt-2" onclick="printdiv2('#cuadro','0')"><i class="fa-solid fa-ban"></i> Cancelar</button>
                            <button type="button" class="btn btn-outline-warning mt-2" onclick="salir()"><i class="fa-solid fa-circle-xmark"></i> Salir</button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                //SELECCIONAR LOS CHECKBOXS DESPUES DE CARGAR EL DOM
                $(document).ready(function() {
                    convertir_tabla_a_datatable('tb_bancos_new');
                    HabDes_boton(0);
                });
            </script>
<?php
        }
        break;
}
?>