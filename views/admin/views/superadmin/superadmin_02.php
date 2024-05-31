<?php
session_start();
include '../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];

switch ($condi) {
    case 'adm_per': {
            $codusu = $_SESSION['id'];
            $id = $_POST["xtra"];
?>
<!-- Crud para agregar, editar y eliminar usuarios -->
<input type="text" id="file" value="usuario_02" style="display: none;">
<input type="text" id="condi" value="permisos_usuarios" style="display: none;">

<div class="text" style="text-align:center">ASIGNACIÓN DE PERMISOS</div>

<div class="card mb-2">
    <div class="container contenedort" style="max-width: 100% !important;">
        <div class="row">
            <div class="col">
                <div class="text-center mb-2"><b>Agregar nuevo Permiso</b></div>
            </div>
        </div>
        <!-- cargo, nombre agencia y codagencia  -->
        <div class="row">
            <?php
            $consult = "SELECT modulo_area,estado FROM clhpzzvb_bd_general_coopera.tb_restringido WHERE estado='1'";
            $result = $conexion->query($consult);
            ?>
            <div id="Select_modules" class="col-15 col-sm-8 col-md-6" style="display: none;">
                <div class="form-floating mb-3 mt-2">
                    <select class="form-select" id="update_estado">
                        <option value="">Seleccione un modulo</option>
                        <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<option value="' . $row["modulo_area"] . '">' . $row["modulo_area"] . '</option>';
                    }
                } else {
                    echo '<option value="" disabled> Error  </option>';
                }
                ?>
                    </select>
                    <label for="update_estado">Estado</label>
                </div>
            </div>

            <style>
            .custom-btn {
                width: 380px;
            }
            </style>

            <div id="inputNombre" class="col-12 col-sm-6 col-md-6">
                <div class="form-floating mb-3 mt-2">
                    <input type="text" class="form-control" id="Nombre" placeholder="modulo_area">
                    <label for="Nombre">Nombre</label>
                </div>
            </div>
            <div class="col-15 col-sm-8 col-md-6">
                <div class="form-floating mb-3 mt-2">
                    <select class="form-select" id="estado">
                        <option value="">Selccione una opcion</option>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                    <label for="estado">Estado</label>
                </div>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm custom-btn" id="btBuscar"
                onclick="replaceInputs()">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>
            <button type="button" class="btn btn-outline-success btn-sm custom-btn" id="btGuardar"
                onclick="new_permise('create_permiso')">
                <i class="fa-solid fa-floppy-disk"></i> Guardar
            </button>
            <button type="button" class="btn btn-outline-success btn-sm custom-btn" id="btActualizar"
                style="display: none;" onclick="update_permise('update_permiso')">
                <i class="fa-solid fa-pen"></i> Actualizar
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm custom-btn" onclick="cancelReplace()">
                <i class="fa-solid fa-ban"></i> Cancelar
            </button>
        </div>
    </div>


    <div class="card-body">
        <!-- Seccion de informacion de usuario -->
        <div class="container contenedort" style="max-width: 100% !important;">
            <h2>Asignar Permisos</h2>

            <!-- usuario y boton buscar -->
            <div class="row">
                <div class="col-12 col-sm-6">
                    <div class="form-floating mb-2 mt-2">
                        <input type="text" class="form-control" id="usuario" placeholder="Nombre de usuario" disabled>
                        <input type="text" id="id_usuario" hidden>
                        <input type="text" id="id_cargo" hidden>
                        <input type="text" id="id_usuario_past" hidden>
                        <label for="cliente">Nombre de usuario</label>
                    </div>
                </div>

                <div class="col-12 col-sm-6">
                    <button type="button" class="btn btn-primary pt-3 pb-3 mb-2 mt-2 col-12 col-sm-12"
                        onclick="abrir_modal('#modal_users', '#id_modal_hidden', 'id_usuario,usuario,cargo,nomagencia,codagencia,id_cargo/A,A,A,A,A,A/'+'/#/#/#/#/#/#')"><i
                            class="fa-solid fa-magnifying-glass-plus me-2"></i>Buscar usuario</button>
                </div>
            </div>
            <!-- cargo, nombre agencia y codagencia  -->
            <div class="row">

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="form-floating mb-3 mt-2">
                        <input type="text" class="form-control" id="cargo" placeholder="Cargo" disabled>
                        <label for="cargo">Cargo</label>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="form-floating mb-3 mt-2">
                        <input type="text" class="form-control" id="nomagencia" placeholder="Nombre de agencia"
                            disabled>
                        <label for="nomagencia">Nombre de agencia</label>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="form-floating mb-3 mt-2">
                        <input type="text" class="form-control" id="codagencia" placeholder="Código de agencia"
                            disabled>
                        <label for="codagencia">Código de agencia</label>
                    </div>
                </div>
                <!-- asignar permisos  -->
                <?php
             $consult = "SELECT id,modulo_area,estado FROM clhpzzvb_bd_general_coopera.tb_restringido WHERE estado='1'";
             $result = $conexion->query($consult);
            ?>
                <div class="col-12 col-sm-6 col-md-66">
                    <div class="form-floating mb-3 mt-2">
                        <select class="form-select" id="update_estado2">
                            <option value="">Seleccione un modulo</option>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . $row["id"] . '">' . $row["modulo_area"] . '</option>';
                                }
                            } else {
                                echo '<option value="" disabled> Error  </option>';
                            }
                            ?>
                        </select>
                        <label for="update_estado2">Estado</label>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-66">
                    <div class="form-floating mb-3 mt-2">
                        <select class="form-select" id="value_estado">
                            <option value="">Selccione una opcion</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                        <label for="value_estado">Estado</label>
                    </div>
                </div>


            </div>

            <div class="col align-items-center mt-2" id="modal_footer">
                <button type="button" class="btn btn-outline-success" id="btGuardar"
                    onclick="create_permisos('create_permisos')">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
                <button type="button" class="btn btn-outline-primary" id="btEditar"
                    onclick="guardar_editar_permisos('update_permisos')">
                    <i class="fa-solid fa-floppy-disk"></i> Actualizar
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="printdiv2('#cuadro','0')">
                    <i class="fa-solid fa-ban"></i> Cancelar
                </button>
                <button type="button" class="btn btn-outline-warning" onclick="salir()">
                    <i class="fa-solid fa-circle-xmark"></i> Salir
                </button>
            </div>

        </div>


        <script>
        //Datatable para parametrizacion
        $(document).ready(function() {
            convertir_tabla_a_datatable("table-submenus");
            HabDes_boton(0);
        });
        </script>
        <div class="container contenedort" style="max-width: 100% !important;">
            <div class="row">
                <div class="col">
                    <div class="text-center mb-2"><b>Usuarios con permisos</b></div>
                </div>
            </div>
            <!-- here -->
            <div class="container contenedort" style="max-width: 100% !important;">
                <div class="row mt-2 pb-2">
                    <div class="col">
                        <div class="table-responsive">
                            <table id="table-utorizacion" class="table table-hover table-border">
                                <thead class="text-light table-head-usu mt-2">
                                    <tr>
                                        <th>Acciones</th>
                                        <th>#</th>
                                        <th>Usuario</th>
                                        <th>apellido</th>
                                        <th> rol</th>
                                        <th>Cant. Permisos</th>

                                    </tr>
                                </thead>
                                <tbody id="tb_cuerpo_submenus" style="font-size: 0.9rem !important;">
                                    <?php
                                    //consulta que filtra 1 usuario y muestras cuantos permisos tiene asignado en estado 1 / 0
                                            $consulta = mysqli_query($conexion, "SELECT 
                                            ta.id_usuario AS id, 
                                            COUNT(ta.id_usuario) AS cantidad_permisos,
                                            MAX(ta.id) AS id_2,
                                            MAX(ta.id_restringido) AS id_restringido,
                                            MAX(ta.id_rol) AS id_rol,
                                            MAX(tu.id_usu) AS id_usu,
                                            MAX(tu.nombre) AS nombre,
                                            MAX(tu.apellido) AS apellido,
                                            MAX(tu.estado) AS estado,
                                            MAX(tu.puesto) AS puesto,
                                            MAX(tu.id_agencia) AS id_agencia,
                                            MAX(tr.modulo_area) AS modulo_area
                                        FROM 
                                            tb_autorizacion AS ta
                                        INNER JOIN 
                                            tb_usuario AS tu ON ta.id_usuario = tu.id_usu
                                        INNER JOIN 
                                            `clhpzzvb_bd_general_coopera`.tb_restringido AS tr ON ta.id_restringido = tr.id
                                        GROUP BY
                                            ta.id_usuario;");
                                            
                                            while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                                $id = $row["id"];
                                                $nombre = $row["nombre"];
                                                $apellido = $row["apellido"];
                                                $rol = $row["puesto"];
                                                $restringido = $row["cantidad_permisos"];
                                                
                                                if ($_SESSION['id'] == 4) { ?>
                                    <!-- seccion de datos -->
                                    <tr>
                                        <td>
                                            <button type="button" class="btn btn-outline-success btn-sm*2"
                                                onclick="viewAcordeon(<?= $id ?>, '<?= $nombre ?>' , '<?= $apellido ?>', '<?= $rol ?>', <?= $restringido ?>);">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </td>
                                        <th scope="row"><?= $id ?></th>
                                        <td><?= $nombre ?></td>
                                        <td><?= $apellido ?></td>
                                        <td><?= $rol ?></td>
                                        <td><?= $restringido ?></td>
                                    </tr>

                                    <?php } 
                                            } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- VIEW PERMISOS -->
            <div class="accordion" id="accordionExample">
                <div class="accordion-item d-none" id="acordeon"
                    style="background-color: #82E0AA  ; padding: 20px; border-radius: 10px; margin-bottom: 10px;">
                    <h2 class="accordion-header" id="heading">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse" aria-expanded="false" aria-controls="collapse"
                            style="font-size: 1.2rem; color: #333;">
                            <p> Permisos</p>
                        </button>
                    </h2>
                    <div class="accordion-collapse collapse" aria-labelledby="heading"
                        data-bs-parent="#accordionExample" id="collapse">
                        <div class="accordion-body" style="font-size: 1rem; color: #333;">
                            <table class="table">
                                <thead class="thead-dark">
                                    <tr>
                                        <th scope="col">Id</th>
                                        <th scope="col">Usuario</th>
                                        <th scope="col">Apellido</th>
                                        <th scope="col">Rol</th>
                                        <th scope="col">Permisos Totales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span id="id"></span></td>
                                        <td><span id="nombre"></span></td>
                                        <td><span id="apellido"></span></td>
                                        <td><span id="rol"></span></td>
                                        <td><span id="restringido"></span></td>
                                    </tr>
                                </tbody>
                            </table>
                            <h3 class="col align-items-center mt-2 d-flex justify-content-center"> 
                            </h3>
                            <!-- tabla de Permisos , se muestra en el crud -->
                            <div id="table-placeholder"></div>
                        </div>
                        <div class="col align-items-center mt-2 d-flex justify-content-center  " id="modal_footer">
                            <button type="button" class="btn btn-outline-primary" id="btsearch_id"
                                onclick="search_id('search_id')">
                                <i class="fa-solid fa-magnifying-glass"></i> Buscar Permisos
                            </button>
                            <button type="button" class="btn btn-outline-warning" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>

                        </div>
                    </div>
                </div>
            </div>

            <script>
            //Datatable para parametrizacion
            $(document).ready(function() {
                convertir_tabla_a_datatable("table-utorizacion");
                HabDes_boton(0);
            });
            </script>
        </div>
    </div>
</div>
<!-- Aca van los modales necesarios -->
<?php include "../../../../src/cris_modales/mdls_users.php"; ?>
<?php
        }
        break;
}