<?php
session_start();
include '../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];

switch ($condi) {
    case 'gestion_usuarios': {
            $codusu = $_SESSION['id'];
            $id = $_POST["xtra"];
?>
            <!-- Crud para agregar, editar y eliminar usuarios -->
            <input type="text" id="file" value="usuario_01" style="display: none;">
            <input type="text" id="condi" value="gestion_usuarios" style="display: none;">
            <div class="text" style="text-align:center">GESTIÓN DE USUARIOS</div>
            <div class="card">
                <div class="card-header">Gestión de usuarios</div>
                <div class="card-body">
                    <div class="text-center mb-2">
                        <h3>Datos de usuario</h3>
                    </div>
                    <!-- Seccion de inputs para edicion -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <!-- agencia y nombres -->
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3 mt-2">
                                    <select class="form-select" id="agencia" aria-label="Tipos de agencia">
                                        <option selected value="0">Seleccionar una agencia</option>
                                        <?php
                                        $agencia = mysqli_query($conexion, "SELECT * FROM `tb_agencia`");
                                        while ($fila = mysqli_fetch_array($agencia)) {
                                            echo '<option value="' . $fila['id_agencia'] . '">' . $fila['cod_agenc'] . ' - ' . strtoupper($fila['nom_agencia']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="agencia">Agencia</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                              <div class="form-floating mb-3 mt-2">
                                <input type="text" class="form-control" id="nombres" placeholder="Nombres">
                                <input type="text" name="" id="id_usu" hidden>
                                <label for="nombres">Nombres</label>
                              </div>
                            </div>
                        </div>
                        <!-- apellidos y dpi -->
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="apellidos" placeholder="Apellidos">
                                    <label for="apellidos">Apellidos</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="dpi" placeholder="Documento de identificación (DPI)">
                                    <label for="dpi">Documento de Indentificacíon (DPI)</label>
                                </div>
                            </div>
                        </div>
                        <!-- cargo y correo electronico -->
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating">
                                    <select class="form-select" id="cargo" aria-label="Tipos de cargo">
                                        <option selected value="0">Seleccionar un cargo</option>
                                        <?php
                                        $cargos = mysqli_query($general, "SELECT * FROM `tb_usuarioscargoprofecional`");
                                        while ($fila = mysqli_fetch_array($cargos)) {
                                            echo '<option value="' . $fila['id_UsuariosCargoProfecional'] . '">' . $fila['UsuariosCargoProfecional'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="cargo">Cargo</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="email" placeholder="Correo electronico">
                                    <label for="email">Correo electronico</label>
                                </div>
                            </div>
                        </div>
                        <!-- usuario contraseña y confirmacion -->
                        <div class="row">
                            <div class="col-12 col-sm-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="usuario" placeholder="Usuario">
                                    <label for="usuario">Usuario</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="input-group mb-3">
                                    <div class="form-floating">
                                        <input type="password" class="form-control border-end-0" id="password" placeholder="Contraseña">
                                        <label for="password">Contraseña</label>
                                    </div>
                                    <span class="input-group-text bg-transparent border-start-0 text-primary"><i class="fa-regular fa-eye" id="togglePassword"></i></span>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="input-group mb-3">
                                    <div class="form-floating">
                                        <input type="password" class="form-control border-end-0" id="confpass" placeholder="Confirmar contraseña">
                                        <label for="confpass">Confirmar contraseña</label>
                                    </div>
                                    <span class="input-group-text bg-transparent border-start-0 text-primary"><i class="fa-regular fa-eye" id="togglePassword2"></i></span>
                                </div>
                            </div>
                        </div>
                        <!-- estado -->
                        <div class="row" id="select_estado" style="display: none;">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="estado" aria-label="Estado de usuario">
                                        <option selected value="1">Activo</option>
                                        <option value="2">Inactivo</option>
                                    </select>
                                    <label for="estado">Estado</label>
                                </div>
                            </div>
                        </div>
                        <div class="row justify-items-md-center">
                            <div class="col align-items-center mb-3" id="modal_footer">
                                <button type="button" class="btn btn-outline-success" id="btGuardar" onclick="obtiene([`nombres`,`apellidos`,`dpi`,`email`,`usuario`,`password`,`confpass`],[`agencia`,`cargo`],[],`create_user`,`0`,['<?= $codusu; ?>'])">
                                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="btEditar" onclick="obtiene([`nombres`,`apellidos`,`dpi`,`email`,`usuario`,`password`,`confpass`,`id_usu`],[`agencia`,`cargo`,`estado`],[],`update_user`,`0`,['<?= $codusu; ?>'])">
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
                                    <table id="table-usuarios" class="table table-hover table-border">
                                        <thead class="text-light table-head-usu mt-2">
                                            <tr>
                                                <th>Acciones</th>
                                                <th>#</th>
                                                <th>Nombres</th>
                                                <th>Apellidos</th>
                                                <th>Usuario</th>
                                                <th>Correo electronico</th>
                                                <th>DPI</th>
                                                <th>Cargo</th>
                                                <th>Agencia</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tb_cuerpo_usuarios" style="font-size: 0.9rem !important;">
                                            <?php
                                            $consulta = mysqli_query($conexion, "SELECT us.*, cg.UsuariosCargoProfecional AS cargo, ag.nom_agencia AS agencia FROM  tb_usuario us
                                            INNER JOIN clhpzzvb_bd_general_coopera.tb_usuarioscargoprofecional cg ON us.puesto=cg.id_UsuariosCargoProfecional
                                            INNER JOIN tb_agencia ag ON us.id_agencia=ag.id_agencia
                                            WHERE estado=1 OR estado=2 ORDER BY us.id_usu ASC");
                                            while ($row = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                                $id_usu = $row["id_usu"];
                                                $nombre = $row["nombre"];
                                                $apellido = $row["apellido"];
                                                $dpi = $row["dpi"];
                                                $usu = $row["usu"];
                                                $pass = $row["pass"];
                                                $estado = $row["estado"];
                                                $puesto = $row["puesto"];
                                                $id_agencia = $row["id_agencia"];
                                                $email = $row["Email"];
                                                $cargo = $row["cargo"];
                                                $agencia = $row["agencia"];
                                                ($estado == "1") ? $text_estado = "Activo" : $text_estado = "Inactivo";
                                                if ($_SESSION['id'] == 4) { ?>
                                                    <tr>
                                                        <td>
                                                            <button type="button" class="btn btn-success btn-sm" onclick="printdiv5('id_usu,agencia,nombres,apellidos,dpi,cargo,email,usuario,estado/A,A,A,A,A,A,A,A,A/'+'/#/#/select_estado/#',['<?= $id_usu ?>','<?= $id_agencia ?>','<?= $nombre ?>','<?= $apellido ?>','<?= $dpi ?>','<?= $puesto ?>','<?= $email ?>','<?= $usu ?>','<?= $estado ?>']); consultar_password('<?= $id_usu ?>'); HabDes_boton(1);"><i class="fa-solid fa-eye"></i></button>
                                                            <button type="button" class="btn btn-danger btn-sm" onclick="eliminar('<?= $id_usu ?>', 'crud_usuario', '0', 'delete_user')"><i class="fa-solid fa-trash"></i></button>
                                                        </td>
                                                        <th scope="row"><?= $id_usu ?></th>
                                                        <td><?= $nombre ?></td>
                                                        <td><?= $apellido ?></td>
                                                        <td><?= $usu ?></td>
                                                        <td><?= $email ?></td>
                                                        <td><?= $dpi ?></td>
                                                        <td><?= $cargo ?></td>
                                                        <td><?= strtoupper($agencia) ?></td>
                                                        <td><?= $text_estado ?></td>
                                                    </tr>
                                                    <?php } else {
                                                    if ($id_usu != 4) { ?>
                                                        <!-- seccion de datos -->
                                                        <tr>
                                                            <td>
                                                                <button type="button" class="btn btn-success btn-sm" onclick="printdiv5('id_usu,agencia,nombres,apellidos,dpi,cargo,email,usuario,estado/A,A,A,A,A,A,A,A,A/'+'/#/#/select_estado/#',['<?= $id_usu ?>','<?= $id_agencia ?>','<?= $nombre ?>','<?= $apellido ?>','<?= $dpi ?>','<?= $puesto ?>','<?= $email ?>','<?= $usu ?>','<?= $estado ?>']); consultar_password('<?= $id_usu ?>'); HabDes_boton(1);"><i class="fa-solid fa-eye"></i></button>
                                                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminar('<?= $id_usu ?>', 'crud_usuario', '0', 'delete_user')"><i class="fa-solid fa-trash"></i></button>
                                                            </td>
                                                            <th scope="row"><?= $id_usu ?></th>
                                                            <td><?= $nombre ?></td>
                                                            <td><?= $apellido ?></td>
                                                            <td><?= $usu ?></td>
                                                            <td><?= $email ?></td>
                                                            <td><?= $dpi ?></td>
                                                            <td><?= $cargo ?></td>
                                                            <td><?= strtoupper($agencia) ?></td>
                                                            <td><?= $text_estado ?></td>
                                                        </tr>
                                            <?php }
                                                }
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        //Datatable para parametrizacion
                        $(document).ready(function() {
                            convertir_tabla_a_datatable("table-usuarios");
                            HabDes_boton(0);

                            $("#togglePassword").click(function(e) {
                                e.preventDefault();
                                var type = $(this).parent().parent().find("#password").attr("type");
                                if (type == "password") {
                                    $(this).removeClass("fa-regular fa-eye");
                                    $(this).addClass("fa-regular fa-eye-slash");
                                    $(this).parent().parent().find("#password").attr("type", "text");
                                } else if (type == "text") {
                                    $(this).removeClass("fa-regular fa-eye-slash");
                                    $(this).addClass("fa-regular fa-eye");
                                    $(this).parent().parent().find("#password").attr("type", "password");
                                }
                            });

                            $("#togglePassword2").click(function(e) {
                                e.preventDefault();
                                var type = $(this).parent().parent().find("#confpass").attr("type");
                                if (type == "password") {
                                    $(this).removeClass("fa-regular fa-eye");
                                    $(this).addClass("fa-regular fa-eye-slash");
                                    $(this).parent().parent().find("#confpass").attr("type", "text");
                                } else if (type == "text") {
                                    $(this).removeClass("fa-regular fa-eye-slash");
                                    $(this).addClass("fa-regular fa-eye");
                                    $(this).parent().parent().find("#confpass").attr("type", "password");
                                }
                            });
                        });
                    </script>
                </div>
            </div>
<?php
        }
    break;
  // NEGROY CAMBIO DE CONTRASEÑA DE LOS USUARIOS ...
  case 'change_pass':
	$codusu = $_SESSION['id'];	$id = $_POST["xtra"];
	$sindesencriptar = "0"; $name = "0";

	$veri = mysqli_query($conexion, "SELECT pass, CONCAT(NOMBRE,' ',apellido)AS'name' FROM tb_usuario WHERE id_usu='".$codusu."'") or die(mysqli_error($conexion));
		/*$xxx = mysqli_fetch_array($veri); $name = $xxx[1]; echo''.$sindesencriptar.''.$name.'';*/
	while ($fila = mysqli_fetch_array($veri, MYSQLI_ASSOC)) {
      $sindesencriptar = $fila['pass'];
			$name = utf8_encode($fila['name']); 
		}
		
    //encriptar_password
    $pass = encriptar_desencriptar( $key1, $key2,'decrypt', $sindesencriptar);		
    ?>
			<input type="text" id="cont" class="d-none" value="<?=$codusu?>">
			<div class="text" style="text-align:center">CAMBIO DE CONTRASEÑA</div>
        <div class="card">
          <div class="card-header">CAMBIO DE CONTRASEÑA</div>
          <div class="card-body">
            <div class="text-center mb-2"> <h3>Datos de usuario</h3> </div>
					<!-- Seccion de inputs para cambio de contraseña -->
					<div class="container contenedort" style="max-width: 100% !important;">

						<div class="row">
							<div class="col-12 col-sm-6">
							<div class="form-floating mb-3">
								<div class="input-group mb-3">
  								<label class="input-group-text fw-bold" >USUSARIO: </label>
  								<input type="text" readonly class="form-control" value="<?=$name;	?>">
								</div>
							</div>	
							</div>
						</div>

						<!-- CONTRASEÑA ANTERIOR -->
						<div class="row">
							<div class="col-12 col-sm-6">
              <div class="form-floating mb-3">
								<div class="input-group mb-3">
									<label label class="col-6 input-group-text fw-bold">CONTRASEÑA ANTERIOR</label>
									<input type="text" readonly class="form-control" id="password" value="<?=$pass?>">
								</div>
							</div>
            	</div>
						</div>	<!--class="row"-->

						<!-- CONTRASEÑA NUEVA -->
						<div class="row">
							<div class="col-12 col-sm-6">
              <div class="form-floating mb-3">
								<div class="input-group mb-3">
									<label class="col-6 input-group-text fw-bold">CONTRASEÑA NUEVA</label>
									<input type="text" class="form-control" id="confpass">
								</div>
							</div>
            	</div>
						</div>	<!--class="row"-->

						<!-- CONTRASEÑA NUEVA CONFIRMACION -->
						<div class="row">
							<div class="col-12 col-sm-6">
              <div class="form-floating mb-3">
								<div class="input-group mb-3">
									<label class="col-6 input-group-text fw-bold">CONTRASEÑA CONFIRMACION</label>
									<input type="text" onchange="passrevisar([`password`,`confpass`,`inpass_new2`])" class="form-control" id="inpass_new2">
								</div>
							</div>
            	</div>
						</div>	<!--class="row"-->

					</div>
					<!-- BOTON PARA REVISAR CONTRASEnA -->
					<div class="container contenedort" style="max-width: 100% !important;">
						<div class="row">
							<div class="col-12 col-sm-6">
							<div class="form-floating mb-3">
							<div class="d-grid gap-2 d-md-flex justify-content-md-end">
								<button type="button" class="btn btn-outline-success btn-lg" id="miBoton" disabled  onclick="obtiene([`password`,`confpass`,`inpass_new2`,`cont`],[],[],`change_pass`,`0`,['<?=$codusu;?>'])">Actualizar Contraseña</button>
							</div>
							</div>	
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php
    break;
}


//funcion para encriptar y desencriptar usuarios PUEDE SER REUTILIZADA 
// TAMBIENSE USA EN CRUD_USUARIO.PHP
function encriptar_desencriptar($mykey1, $mykey2, $action = 'encrypt', $string = false)
{
    $action = trim($action);
    $output = false;

    $myKey = $mykey1;
    $myIV = $mykey2;
    $encrypt_method = 'AES-256-CBC';

    $secret_key = hash('sha256', $myKey);
    $secret_iv = substr(hash('sha256', $myIV), 0, 16);

    if ($action && ($action == 'encrypt' || $action == 'decrypt') && $string) {
        $string = trim(strval($string));

        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $secret_key, 0, $secret_iv);
        };

        if ($action == 'decrypt') {
            $output = openssl_decrypt($string, $encrypt_method, $secret_key, 0, $secret_iv);
        };
    };
    return $output;
};
?>