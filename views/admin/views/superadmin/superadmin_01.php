<?php
session_start();
include '../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];

switch ($condi) {
    case 'modulos_usuarios': {
            $codusu = $_SESSION['id'];
            $id = $_POST["xtra"];
?>
            <!-- Crud para agregar, editar y eliminar usuarios -->
            <input type="text" id="file" value="superadmin_01" style="display: none;">
            <input type="text" id="condi" value="modulos_usuarios" style="display: none;">

            <div class="text" style="text-align:center">ADMINISTRACION DE MÓDULOS</div>
            <div class="card">
                <div class="card-header">Administración de Módulos</div>
                <div class="card-body">
                    <div class="text-center mb-2">
                        <h3>Datos del módulo</h3>
                    </div>
                    <!-- Seccion de inputs para edicion -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <!-- descripcion e icono -->
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3 mt-2">
                                    <input type="text" class="form-control" id="descripcion" placeholder="Descripción">
                                    <input type="text" name="" id="id" hidden>
                                    <label for="descripcion">Descripción</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3 mt-2">
                                    <input type="text" class="form-control" id="icon" placeholder="Icono">
                                    <label for="icon">Icono</label>
                                </div>
                            </div>
                        </div>
                        <!-- ruta y rama -->
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="ruta" placeholder="Ruta">
                                    <label for="ruta">Ruta</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="rama" aria-label="Rama">
                                        <option selected value="0">Seleccionar una rama</option>
                                        <option value=A>A</option>
                                        <option value=B>B</option>
                                        <option value=C>C</option>
                                        <option value=D>D</option>
                                        <option value=E>E</option>
                                        <option value=F>F</option>
                                        <option value=G>G</option>
                                        <option value=H>H</option>
                                        <option value=I>I</option>
                                        <option value=J>J</option>
                                        <option value=K>K</option>
                                        <option value=L>L</option>
                                        <option value=M>M</option>
                                        <option value=N>N</option>
                                        <option value=Ñ>Ñ</option>
                                        <option value=O>O</option>
                                        <option value=P>P</option>
                                        <option value=Q>Q</option>
                                        <option value=R>R</option>
                                        <option value=S>S</option>
                                        <option value=T>T</option>
                                        <option value=U>U</option>
                                        <option value=V>V</option>
                                        <option value=W>W</option>
                                        <option value=X>X</option>
                                        <option value=Y>Y</option>
                                        <option value=Z>Z</option>
                                    </select>
                                    <label for="rama">Rama</label>
                                </div>
                            </div>
                        </div>
                        <!-- ruta y rama -->
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="orden" placeholder="Orden">
                                    <label for="orden">Orden</label>
                                </div>
                            </div>
                        </div>
                        <div class="row justify-items-md-center">
                            <div class="col align-items-center mb-3" id="modal_footer">
                                <button type="button" class="btn btn-outline-success" id="btGuardar" onclick="obtiene([`descripcion`,`icon`,`ruta`,`orden`],[`rama`],[],`create_modulo`,`0`,['<?= $codusu; ?>'])">
                                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="btEditar" onclick="obtiene([`descripcion`,`icon`,`ruta`,`id`,`orden`],[`rama`],[],`update_modulo`,`0`,['<?= $codusu; ?>'])">
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
                    </div>

                    <!-- tabla de usuarios -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row mt-2 pb-2">
                            <div class="col">
                                <div class="table-responsive">
                                    <table id="table-modulos" class="table table-hover table-border">
                                        <thead class="text-light table-head-usu mt-2">
                                            <tr>
                                                <th>Acciones</th>
                                                <th>#</th>
                                                <th>Descripción</th>
                                                <th>Icono</th>
                                                <th>Ruta</th>
                                                <th>Rama</th>
                                                <th>Orden</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tb_cuerpo_modulos" style="font-size: 0.9rem !important;">
                                            <?php
                                            $consulta = mysqli_query($general, "SELECT * FROM tb_modulos WHERE estado=1 ORDER BY id ASC");
                                            while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                                $id = $row["id"];
                                                $descripcion = $row["descripcion"];
                                                $icon = $row["icon"];
                                                $ruta = $row["ruta"];
                                                $rama = $row["rama"];
                                                $orden = $row["orden"];
                                            ?>
                                                <!-- seccion de datos -->
                                                <tr>
                                                    <td>
                                                        <button type="button" class="btn btn-success btn-sm" onclick="printdiv5('id,descripcion,icon,ruta,rama,orden/A,A,A,A,A,A/'+'/#/#/#/#',['<?= $id ?>','<?= $descripcion ?>','<?= $icon ?>','<?= $ruta ?>','<?= $rama ?>','<?= $orden ?>']);  HabDes_boton(1);"><i class="fa-solid fa-eye"></i></button>
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="eliminar('<?= $id ?>', 'crud_superadmin', '0', 'delete_modulo')"><i class="fa-solid fa-trash"></i></button>
                                                    </td>
                                                    <th scope="row"><?= $id ?></th>
                                                    <td><?= $descripcion ?></td>
                                                    <td><?= $icon ?></td>
                                                    <td><?= $ruta ?></td>
                                                    <td><?= $rama ?></td>
                                                    <td><?= $orden ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        //Datatable para parametrizacion
                        $(document).ready(function() {
                            convertir_tabla_a_datatable("table-modulos");
                            HabDes_boton(0);
                        });
                    </script>
                </div>
            </div>
        <?php
        }
        break;
    case 'menus_usuarios': {
            $codusu = $_SESSION['id'];
            $id = $_POST["xtra"];
        ?>
            <!-- Crud para agregar, editar y eliminar usuarios -->
            <input type="text" id="file" value="superadmin_01" style="display: none;">
            <input type="text" id="condi" value="menus_usuarios" style="display: none;">

            <div class="text" style="text-align:center">ADMINISTRACION DE MENÚS</div>
            <div class="card">
                <div class="card-header">Administración de Menús</div>
                <div class="card-body">
                    <div class="text-center mb-2">
                        <h3>Datos del menú</h3>
                    </div>
                    <!-- Seccion de inputs para edicion -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col-12 col-sm-12">
                                <div class="input-group mb-3 mt-2">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="modulo" placeholder="Módulo" disabled>
                                        <input type="text" name="" id="id_modulo" hidden>
                                        <label for="modulo">Módulo</label>
                                    </div>
                                    <span type="button" class="input-group-text" id="basic-addon2" title="Buscar módulo" onclick="abrir_modal('#modal_modulos', '#id_modal_hidden', 'id_modulo,modulo/A,2-3/-/#/#/#/#')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                                </div>
                            </div>
                        </div>
                        <!-- descripcion e icono -->
                        <div class="row">
                            <div class="col-12 col-sm-12">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="descripcion" placeholder="Descripción">
                                    <input type="text" name="" id="id" hidden>
                                    <label for="descripcion">Descripción</label>
                                </div>
                            </div>
                        </div>
                        <!-- ruta y rama -->
                        <div class="row">
                            <div class="col-12 col-sm-12">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="orden" placeholder="Orden">
                                    <label for="orden">Orden</label>
                                </div>
                            </div>
                        </div>
                        <div class="row justify-items-md-center">
                            <div class="col align-items-center mb-3" id="modal_footer">
                                <button type="button" class="btn btn-outline-success" id="btGuardar" onclick="obtiene([`descripcion`,`id_modulo`,`modulo`,`orden`],[],[],`create_menu`,`0`,['<?= $codusu; ?>'])">
                                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="btEditar" onclick="obtiene([`descripcion`,`id`,`id_modulo`,`modulo`,`orden`],[],[],`update_menu`,`0`,['<?= $codusu; ?>'])">
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
                    </div>

                    <!-- tabla de usuarios -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row mt-2 pb-2">
                            <div class="col">
                                <div class="table-responsive">
                                    <table id="table-menus" class="table table-hover table-border">
                                        <thead class="text-light table-head-usu mt-2">
                                            <tr>
                                                <th>Acciones</th>
                                                <th>#</th>
                                                <th>Descripción</th>
                                                <th>Módulo</th>
                                                <th>Rama</th>
                                                <th>Orden</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tb_cuerpo_menus" style="font-size: 0.9rem !important;">
                                            <?php
                                            $consulta = mysqli_query($general, "SELECT ms.id AS id, ms.descripcion AS descripcion, ts.descripcion AS nommod, ts.rama AS rama, ts.id AS id_modulo, ms.orden AS orden
                                            FROM tb_menus ms INNER JOIN tb_modulos ts ON ms.id_modulo=ts.id  WHERE ms.estado=1 AND ts.estado=1 ORDER BY ts.id, ms.orden ASC");
                                            while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                                $id = $row["id"];
                                                $descripcion = $row["descripcion"];
                                                $nommod = $row["nommod"];
                                                $rama = $row["rama"];
                                                $id_modulo = $row["id_modulo"];
                                                $orden = $row["orden"];
                                            ?>

                                                <!-- seccion de datos -->
                                                <tr>
                                                    <td>
                                                        <button type="button" class="btn btn-success btn-sm" onclick="printdiv5('id,descripcion,modulo,id_modulo,orden/A,A,3-4,A,A/-/#/#/#/#',['<?= $id ?>','<?= $descripcion ?>','<?= $nommod ?>','<?= $rama ?>','<?= $id_modulo ?>','<?= $orden ?>']);  HabDes_boton(1);"><i class="fa-solid fa-eye"></i></button>
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="eliminar('<?= $id ?>', 'crud_superadmin', '0', 'delete_menu')"><i class="fa-solid fa-trash"></i></button>
                                                    </td>
                                                    <th scope="row"><?= $id ?></th>
                                                    <td><?= $descripcion ?></td>
                                                    <td><?= $nommod ?></td>
                                                    <td><?= $rama ?></td>
                                                    <td><?= $orden ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        //Datatable para parametrizacion
                        $(document).ready(function() {
                            convertir_tabla_a_datatable("table-menus");
                            HabDes_boton(0);
                        });
                    </script>
                </div>
            </div>
            <?php include "../../../../src/cris_modales/mdls_modulo.php"; ?>
        <?php
        }
        break;
    case 'submenus_usuarios': {
            $codusu = $_SESSION['id'];
            $id = $_POST["xtra"];
        ?>
            <!-- Crud para agregar, editar y eliminar usuarios -->
            <input type="text" id="file" value="superadmin_01" style="display: none;">
            <input type="text" id="condi" value="submenus_usuarios" style="display: none;">

            <div class="text" style="text-align:center">ADMINISTRACION DE SUBMENÚS</div>
            <div class="card">
                <div class="card-header">Administración de Submenús</div>
                <div class="card-body">
                    <div class="text-center mb-2">
                        <h3>Datos del submenú</h3>
                    </div>
                    <!-- Seccion de inputs para edicion -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <!-- modulo y submenu -->
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="input-group mb-3 mt-2">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="menu" placeholder="Menú" disabled>
                                        <input type="text" name="" id="id_menu" hidden>
                                        <input type="text" name="" id="id" hidden>
                                        <label for="menu">Menú</label>
                                    </div>
                                    <span type="button" class="input-group-text" id="basic-addon2" title="Buscar menú" onclick="abrir_modal('#modal_menus', '#id_modal_hidden', 'id_menu,menu/A,2-4/-/#/#/#/#')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3 mt-2">
                                    <input type="text" class="form-control" id="tcondi" placeholder="Condición">
                                    <label for="tcondi">Condición</label>
                                </div>
                            </div>
                        </div>
                        <!-- condi y file -->
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="archivo" placeholder="Archivo">
                                    <label for="archivo">Archivo</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="caption" placeholder="Texto de opción">
                                    <label for="caption">Texto de opción</label>
                                </div>
                            </div>
                        </div>
                        <!-- ruta y rama -->
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="desarrollo" aria-label="Porcentaje de desarrollo">
                                        <option selected value="0">Selecciona porcentaje de desarrollo</option>
                                        <option value=1>0%</option>
                                        <option value=2>20%</option>
                                        <option value=3>40%</option>
                                        <option value=4>60%</option>
                                        <option value=5>80%</option>
                                        <option value=6>100%</option>
                                    </select>
                                    <label for="desarrollo">Porcentaje de desarrollo</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="orden" placeholder="Orden">
                                    <label for="orden">Orden</label>
                                </div>
                            </div>
                        </div>
                        <div class="row justify-items-md-center">
                            <div class="col align-items-center mb-3" id="modal_footer">
                                <button type="button" class="btn btn-outline-success" id="btGuardar" onclick="obtiene([`id_menu`,`menu`,`tcondi`,`archivo`,`caption`,`orden`],[`desarrollo`],[],`create_submenu`,`0`,['<?= $codusu; ?>'])">
                                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="btEditar" onclick="obtiene([`id_menu`,`menu`,`tcondi`,`archivo`,`caption`,`id`,`orden`],[`desarrollo`],[],`update_submenu`,`0`,['<?= $codusu; ?>'])">
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
                    </div>
                    <!-- tabla de usuarios -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row mt-2 pb-2">
                            <div class="col">
                                <div class="table-responsive">
                                    <table id="table-submenus" class="table table-hover table-border">
                                        <thead class="text-light table-head-usu mt-2">
                                            <tr>
                                                <th>Acciones</th>
                                                <th>#</th>
                                                <th>Módulo</th>
                                                <th>Menú</th>
                                                <th>Condición</th>
                                                <th>Archivo</th>
                                                <th>Texto</th>
                                                <th>Orden</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tb_cuerpo_submenus" style="font-size: 0.9rem !important;">
                                            <?php
                                            $consulta = mysqli_query($general, "SELECT ts.id, td.id AS id_modulo, td.descripcion AS modulo, td.rama, tm.id AS id_menu, tm.descripcion AS menu, ts.condi AS condicion, ts.`file` AS archivo, ts.caption AS texto, ts.desarrollo, ts.orden AS orden FROM tb_submenus ts
                                            INNER JOIN tb_menus tm ON ts.id_menu=tm.id
                                            INNER JOIN tb_modulos td ON tm.id_modulo=td.id
                                            WHERE ts.estado='1' AND tm.estado='1' AND td.estado='1'
                                            ORDER BY ts.id ASC");
                                            while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                                $id = $row["id"];
                                                $modulo = $row["modulo"];
                                                $id_modulo = $row["id_modulo"];
                                                $menu = $row["menu"];
                                                $rama = $row["rama"];
                                                $id_menu = $row["id_menu"];
                                                $condicion = $row["condicion"];
                                                $archivo = $row["archivo"];
                                                $texto = $row["texto"];
                                                $desarrollo = $row["desarrollo"];
                                                $orden = $row["orden"];
                                                $textodesarrollo = "";
                                                $colorestado = "";
                                                if ($desarrollo == '1') {
                                                    $textodesarrollo = "0%";
                                                    $colorestado = "btn btn-danger";
                                                } elseif ($desarrollo == '2') {
                                                    $textodesarrollo = "20%";
                                                    $colorestado = "btn btn-danger";
                                                } elseif ($desarrollo == '3') {
                                                    $textodesarrollo = "40%";
                                                    $colorestado = "btn btn-danger";
                                                } elseif ($desarrollo == '4') {
                                                    $textodesarrollo = "60%";
                                                    $colorestado = "btn btn-warning";
                                                } elseif ($desarrollo == '5') {
                                                    $textodesarrollo = "80%";
                                                    $colorestado = "btn btn-warning";
                                                } elseif ($desarrollo == '6') {
                                                    $textodesarrollo = "100%";
                                                    $colorestado = "btn btn-success";
                                                }
                                            ?>
                                                <!-- seccion de datos -->
                                                <tr>
                                                    <td>
                                                        <button type="button" class="btn btn-success btn-sm" onclick="printdiv5('id,id_menu,menu,tcondi,archivo,caption,desarrollo,orden/A,A,3-5,A,A,A,A,A/-/#/#/#/#',['<?= $id ?>','<?= $id_menu ?>','<?= $menu ?>','<?= $modulo ?>','<?= $rama ?>','<?= $condicion ?>','<?= $archivo ?>','<?= $texto ?>','<?= $desarrollo ?>','<?= $orden ?>']);  HabDes_boton(1);"><i class="fa-solid fa-eye"></i></button>
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="eliminar('<?= $id ?>', 'crud_superadmin', '0', 'delete_submenu')"><i class="fa-solid fa-trash"></i></button>
                                                    </td>
                                                    <th scope="row"><?= $id ?></th>
                                                    <td><?= $modulo ?></td>
                                                    <td><?= $menu ?></td>
                                                    <td><?= $condicion ?></td>
                                                    <td><?= $archivo ?></td>
                                                    <td><?= $texto ?></td>
                                                    <td><?= $orden ?></td>
                                                    <td> <span class="<?= $colorestado; ?>" style="cursor: default"> <?= $textodesarrollo ?></span></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        //Datatable para parametrizacion
                        $(document).ready(function() {
                            convertir_tabla_a_datatable("table-submenus");
                            HabDes_boton(0);
                        });
                    </script>
                </div>
            </div>
            <!-- Aca van los modales necesarios -->
            <?php // include "../../../../src/cris_modales/mdls_modulo.php"; 
            ?>
            <?php include "../../../../src/cris_modales/mdls_menu.php"; ?>

<?php
        }
        break;

       

}
?>