<?php
if (!isset($_SESSION)) {
	session_start();
}
date_default_timezone_set('America/Guatemala');
$hoy = date("Y-m-d");
$hoy2 = date("Y-m-d H:i:s");
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');


$condi = $_POST["condi"];
switch ($condi) {
	case 'apertura_caja': {
			$xtra = $_POST["xtra"];
?>
			<input type="text" id="condi" value="apertura_caja" hidden>
			<input type="text" id="file" value="caja001" hidden>

			<div class="text" style="text-align:center">APERTURA DE CAJA</div>
			<div class="card">
				<div class="card-header">Apertura de caja</div>
				<div class="card-body">
					<!-- CAMPOS PARA INSERCION-->
					<div class="container contenedort" style="max-width: 100% !important;">
						<div class="row">
							<div class="col">
								<div class="text-center mb-2"><b>Apertura de caja</b></div>
							</div>
						</div>
						<div class="row">
							<div class="col-12 col-sm-6">
								<div class="form-floating mb-2 mt-2">
									<input type="text" class="form-control" id="iduser" readonly hidden <?php echo 'value="' . $_SESSION['id'] . '"'; ?>>
									<input type="text" class="form-control" id="nomuser" placeholder="Nombre de usuario" readonly <?php echo 'value="' . $_SESSION['nombre'] . '"'; ?>>
									<label for="nomuser">Nombres</label>
								</div>
							</div>
							<div class="col-12 col-sm-6">
								<div class="form-floating mb-2 mt-2">
									<input type="text" class="form-control" id="nomape" placeholder="Apellido de usuario" readonly <?php echo 'value="' . $_SESSION['apellido'] . '"'; ?>>
									<label for="nomape">Apellidos</label>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12 col-sm-6">
								<div class="form-floating mb-2 mt-2">
									<input type="text" class="form-control" id="user" placeholder="Usuario" readonly <?php echo 'value="' . $_SESSION['usu'] . '"'; ?>>
									<label for="user">Usuario</label>
								</div>
							</div>
							<div class="col-12 col-sm-6">
								<div class="form-floating mb-2 mt-2">
									<input type="date" class="form-control" id="fec_apertura" placeholder="Fecha de apertura" readonly <?php echo 'value="' . date('Y-m-d') . '"'; ?>>
									<label for="fec_apertura">Fecha de apertura</label>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="input-group mb-2 mt-2">
								<span class="input-group-text bg-primary text-white" style="border: none !important;" id="basic-addon1"><i class="fa-solid fa-money-bill"></i></span>
								<div class="form-floating">
									<input type="number" class="form-control" id="saldoinicial" placeholder="Saldoinicial" step="0.01">
									<label for="saldoinicial">Digite su saldo inicial</label>
								</div>
							</div>
						</div>
						<div class="row justify-items-md-center">
							<div class="col align-items-center mt-2 mb-2" id="modal_footer">
								<button class="btn btn-outline-success" onclick="save_apertura_cierre('<?= $_SESSION['id']; ?>', true)"><i class="fa-solid fa-box-open me-2"></i></i>Aperturar</button>
								<button type="button" class="btn btn-outline-danger" onclick="printdiv2('#cuadro','0')"><i class="fa-solid fa-ban"></i> Cancelar</button>
								<button type="button" class="btn btn-outline-warning" onclick="salir()"><i class="fa-solid fa-circle-xmark"></i> Salir</button>
							</div>
						</div>
					</div>

					<!-- TABLA PARA LOS DISTINTOS TIPOS DE INGRESOS -->
					<div class="container contenedort" style="max-width: 100% !important;">
						<div class="row">
							<div class="col">
								<div class="text-center mb-2"><b>Historial de cierres y aperturas</b></div>
							</div>
						</div>
						<div class="row">
							<div class="col">
								<div class="table-responsive">
									<table class="table nowrap table-hover table-border" id="tb_aperturas_cierres" style="width: 100% !important;">
										<thead class="text-light table-head-aprt">
											<tr style="font-size: 0.9rem;">
												<th>#</th>
												<th>Fecha de apertura</th>
												<th>Saldo inicial</th>
												<th>Fecha de cierre</th>
												<th>Saldo final</th>
												<th>Estado</th>
												<th>R. arqueo</th>
											</tr>
										</thead>
										<tbody class="table-group-divider" style="font-size: 0.9rem !important;">

										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				<script>
					$(document).ready(function() {
						$('#tb_aperturas_cierres').on('search.dt').DataTable({
							"aProcessing": true,
							"aServerSide": true,
							"ordering": false,
							"lengthMenu": [
								[10],
								['10 filas']
							],
							"ajax": {
								url: '../../src/cruds/crud_caja.php',
								type: "POST",
								beforeSend: function() {
									loaderefect(1);
								},
								data: {
									'condi': "listado_aperturas"
								},
								dataType: "json",
								complete: function(response) {
									console.log(response);
									loaderefect(0);
									if (response.responseJSON.error != undefined) {
										Swal.fire('Error', response.responseJSON.error, 'error')
									}
								}
							},
							"bDestroy": true,
							"iDisplayLength": 10,
							"order": [
								[1, "desc"]
							],
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
			<?php
		}
		break;
	case 'cierre_caja':
		$mensaje_error = "";
		$bandera = false;
		$bandera_error = false;
		$datos[] = [];
		$i = 0;
		//Validar si ya existe un registro igual que el nombre
		try {
			$stmt = $conexion->prepare("SELECT tcac.*, tu.nombre AS nombres, tu.apellido AS apellidos, tu.usu AS usuario, 
			(SELECT IFNULL(SUM(a.monto) ,0) FROM ahommov a WHERE a.cestado!=2 AND a.ctipope = 'D' AND a.created_at = tcac.fecha_apertura AND a.created_by = tcac.id_usuario) AS ingresos_ahorros,
			(SELECT IFNULL(SUM(b.monto) ,0) FROM ahommov b WHERE b.cestado!=2 AND b.ctipope = 'R' AND b.created_at = tcac.fecha_apertura AND b.created_by = tcac.id_usuario) AS egresos_ahorros,
			(SELECT IFNULL(SUM(c.monto) ,0) FROM aprmov c WHERE c.cestado!=2 AND c.ctipope = 'D' AND c.created_at = tcac.fecha_apertura AND c.created_by = tcac.id_usuario) AS ingresos_aportaciones,
			(SELECT IFNULL(SUM(d.monto) ,0) FROM aprmov d WHERE d.cestado!=2 AND d.ctipope = 'R' AND d.created_at = tcac.fecha_apertura AND d.created_by = tcac.id_usuario) AS egresos_aportaciones,
			(SELECT IFNULL(SUM(ck.KP) ,0) FROM CREDKAR ck WHERE ck.CTIPPAG = 'D' AND  CAST(ck.DFECSIS AS DATE) = tcac.fecha_apertura AND ck.CESTADO != 'X' AND ck.CCODUSU = tcac.id_usuario) AS desembolsos_creditos,
			(SELECT IFNULL(SUM(ck2.NMONTO) ,0)  FROM CREDKAR ck2 WHERE ck2.CTIPPAG = 'P' AND  CAST(ck2.DFECSIS AS DATE) = tcac.fecha_apertura AND ck2.CESTADO != 'X' AND ck2.CCODUSU = tcac.id_usuario) AS pagos_creditos,
			(SELECT IFNULL(SUM(opm.monto) ,0)  FROM otr_pago_mov opm INNER JOIN otr_pago op ON opm.id_otr_pago = op.id INNER JOIN otr_tipo_ingreso oti ON opm.id_otr_tipo_ingreso = oti.id WHERE op.estado = '1' AND opm.estado = '1' AND oti.estado = '1' AND oti.tipo = '1' AND CAST(op.created_at AS DATE) = tcac.fecha_apertura AND op.created_by = tcac.id_usuario) AS otros_ingresos,
			(SELECT IFNULL(SUM(opm.monto) ,0)  FROM otr_pago_mov opm INNER JOIN otr_pago op ON opm.id_otr_pago = op.id INNER JOIN otr_tipo_ingreso oti ON opm.id_otr_tipo_ingreso = oti.id WHERE op.estado = '1' AND opm.estado = '1' AND oti.estado = '1' AND oti.tipo = '2' AND CAST(op.created_at AS DATE) = tcac.fecha_apertura AND op.created_by = tcac.id_usuario) AS otros_egresos
			FROM tb_caja_apertura_cierre tcac INNER JOIN tb_usuario tu ON tcac.id_usuario = tu.id_usu 
			WHERE (tcac.id_usuario =? AND tcac.fecha_apertura=? AND tcac.estado='1') OR (tcac.id_usuario=? AND tcac.fecha_apertura < ? AND tcac.estado='1') ORDER BY tcac.fecha_apertura ASC LIMIT 1");
			if (!$stmt) {
				throw new Exception("Error en la consulta: " . $conexion->error);
			}
			$aux = date('Y-m-d');
			$aux2 = $_SESSION['id'];
			$stmt->bind_param("ssss", $aux2, $aux, $aux2, $aux); //El arroba omite el warning de php
			// @$stmt->bind_param("ssss", $aux2, $aux, $aux2); //Sin arroba no omite el warning

			if (!$stmt->execute()) {
				throw new Exception("Error al consultar: " . $stmt->error);
			}
			$result = $stmt->get_result();
			while ($fila = $result->fetch_assoc()) {
				$datos[$i] = $fila;
				$datos[$i]['mensajeestado'] = ($fila['fecha_apertura'] < date('Y-m-d')) ? '<span class="badge text-bg-danger">Cierre pendiente con atraso</span>'  : '<span class="badge text-bg-success">Cierre pendiente</span>';
				$i++;

				$bandera = true;
			}
			if ($bandera) {
				$datos[0]['sumaingresos'] = ($datos[0]['saldo_inicial'] + $datos[0]['ingresos_ahorros'] + $datos[0]['ingresos_aportaciones'] + $datos[0]['pagos_creditos'] + $datos[0]['otros_ingresos']);
				$datos[0]['sumaegresos'] = ($datos[0]['egresos_ahorros'] + $datos[0]['egresos_aportaciones'] + $datos[0]['desembolsos_creditos'] + $datos[0]['otros_egresos']);
				$datos[0]['saldofinal'] = ($datos[0]['sumaingresos'] - abs($datos[0]['sumaegresos']));
				$datos[0]['sumasiguales'] = ($datos[0]['sumaegresos'] + $datos[0]['saldofinal']);
			}
		} catch (Exception $e) {
			//Captura el error
			$mensaje_error = $e->getMessage();
			$bandera_error = true;
		}
			?>
			<input type="text" id="condi" value="cierre_caja" hidden>
			<input type="text" id="file" value="caja001" hidden>

			<div class="text" style="text-align:center">CIERRE DE CAJA</div>
			<div class="card">
				<div class="card-header">Cierre de caja</div>
				<div class="card-body">
					<?php if ($bandera_error) { ?>
						<div class="alert alert-warning alert-dismissible fade show" role="alert">
							<strong>¡Error!</strong> <?= $mensaje_error; ?>
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					<?php } ?>
					<?php if (!$bandera) { ?>
						<div class="alert alert-success alert-dismissible fade show" role="alert">
							<strong>¡Bienvenido!</strong> <?= 'No se tienen ningun cierre por realizar, todo esta bien'; ?>
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					<?php } ?>

					<!-- CAMPOS PARA INSERCION-->
					<div class="container contenedort" style="max-width: 100% !important;">
						<div class="row">
							<div class="col">
								<div class="text-center mb-2"><b>Cierre de caja</b></div>
							</div>
						</div>
						<?php if ($bandera) { ?>
							<div class="row">
								<div class="col mt-3 mb-3">
									<div class="text-center">Estado: <?php if ($bandera) {
																			echo $datos[0]['mensajeestado'];
																		} ?>
									</div>
								</div>
							</div>
						<?php } ?>
						<div class="row">
							<div class="col-12 col-sm-6">
								<div class="form-floating mb-2 mt-2">
									<input type="text" id="saldoinicial" readonly hidden <?php if ($bandera) {
																								echo 'value="' . $datos[0]['saldo_inicial'] . '"';
																							} ?>>
									<input type="text" id="saldofinal" readonly hidden <?php if ($bandera) {
																							echo 'value="' . $datos[0]['saldofinal'] . '"';
																						} ?>>
									<input type="text" id="iduser" readonly hidden <?php if ($bandera) {
																						echo 'value="' . $datos[0]['id_usuario'] . '"';
																					} ?>>
									<input type="text" class="form-control" id="nomuser" placeholder="Nombre de usuario" readonly <?php if ($bandera) {
																																		echo 'value="' . $datos[0]['nombres'] . '"';
																																	} ?>>
									<label for="nomuser">Nombres</label>
								</div>
							</div>
							<div class="col-12 col-sm-6">
								<div class="form-floating mb-2 mt-2">
									<input type="text" class="form-control" id="nomape" placeholder="Apellido de usuario" readonly <?php if ($bandera) {
																																		echo 'value="' . $datos[0]['apellidos'] . '"';
																																	} ?>>
									<label for="nomape">Apellidos</label>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12 col-sm-12 col-md-4">
								<div class="form-floating mb-2 mt-2">
									<input type="text" class="form-control" id="user" placeholder="Usuario" readonly <?php if ($bandera) {
																															echo 'value="' . $datos[0]['usuario'] . '"';
																														} ?>>
									<label for="user">Usuario</label>
								</div>
							</div>
							<div class="col-12 col-sm-6 col-md-4">
								<div class="form-floating mb-2 mt-2">
									<input type="date" class="form-control" id="fec_apertura" placeholder="Fecha de apertura" readonly <?php if ($bandera) {
																																			echo 'value="' . $datos[0]['fecha_apertura'] . '"';
																																		} ?>>
									<label for="fec_apertura">Fecha de apertura</label>
								</div>
							</div>
							<div class="col-12 col-sm-6 col-md-4">
								<div class="form-floating mb-2 mt-2">
									<input type="date" class="form-control" id="fec_cierre" placeholder="Fecha de cierre" readonly <?= ($bandera) && print('value="' . date('Y-m-d') . '"'); ?>>
									<label for="fec_cierre">Fecha de cierre</label>
								</div>
							</div>
						</div>
					</div>
					<div class="container contenedort" style="max-width: 100% !important;">
						<div class="row">
							<div class="col">
								<div class="text-center mb-2"><b>Resumen de movimientos</b></div>
							</div>
						</div>
						<div class="row">
							<div class="col">
								<div class="table-responsive">
									<table class="table nowrap table-borderless table-hover" id="tb_aperturas_cierres2" style="width: 100% !important;">
										<thead class="table-light">
											<tr style="font-size: 0.9rem; border-bottom: 3px solid #000 !important; ">
												<th scope="col" style="width: 5%">#</th>
												<th scope="col" class="text-center">Descripción</th>
												<th scope="col" class="text-center">Ingresos</th>
												<th scope="col" class="text-center">Egresos</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<th scope="row">1</th>
												<td class="table-info"><span>Saldo inicial</span></td>
												<td class="text-end"><?= ($bandera) ? ('Q ' . number_format($datos[0]['saldo_inicial'], 2, '.', ',')) : ''; ?></td>
												<td class="text-end"></td>
											</tr>
											<tr>
												<th scope="row">2</th>
												<td class="table-secondary"><span>Ingresos ahorro</span></td>
												<td class="text-end"><?= ($bandera) ? ('Q ' . number_format($datos[0]['ingresos_ahorros'], 2, '.', ',')) : ''; ?></td>
												<td class="text-end"></td>
											</tr>
											<tr>
												<th scope="row">4</th>
												<td class="table-secondary"><span>Ingresos aportaciones</span></td>
												<td class="text-end"><?= ($bandera) ? ('Q ' . number_format($datos[0]['ingresos_aportaciones'], 2, '.', ',')) : ''; ?></td>
												<td class="text-end"></td>
											</tr>
											<tr>
												<th scope="row">7</th>
												<td class="table-secondary"><span>Cobros de créditos</span></td>
												<td class="text-end"><?= ($bandera) ? ('Q ' . number_format($datos[0]['pagos_creditos'], 2, '.', ',')) : ''; ?></td>
												<td class="text-end"></td>
											</tr>
											<tr>
												<th scope="row">8</th>
												<td class="table-secondary"><span>Otros ingresos (Entradas)</span></td>
												<td class="text-end"><?= ($bandera) ? ('Q ' . number_format($datos[0]['otros_ingresos'], 2, '.', ',')) : ''; ?></td>
												<td class="text-end"></td>
											</tr>
											<tr>
												<th scope="row">3</th>
												<td class="table-secondary"><span>Egresos ahorro</span></td>
												<td class="text-end"></td>
												<td class="text-end"><?= ($bandera) ? ('Q ' . number_format($datos[0]['egresos_ahorros'], 2, '.', ',')) : ''; ?></td>
											</tr>
											<tr>
												<th scope="row">5</th>
												<td class="table-secondary"><span>Egresos aportaciones</span></td>
												<td class="text-end"></td>
												<td class="text-end"><?= ($bandera) ? ('Q ' . number_format($datos[0]['egresos_aportaciones'], 2, '.', ',')) : ''; ?></td>
											</tr>
											<tr>
												<th scope="row">6</th>
												<td class="table-secondary"><span>Desembolsos de créditos en efectivo</span></td>
												<td class="text-end"></td>
												<td class="text-end"><?= ($bandera) ? ('Q ' . number_format($datos[0]['desembolsos_creditos'], 2, '.', ',')) : ''; ?></td>
											</tr>
											<tr>
												<th scope="row">8</th>
												<td class="table-secondary"><span>Otros ingresos (Salidas)</span></td>
												<td class="text-end"></td>
												<td class="text-end"><?= ($bandera) ? ('Q ' . number_format($datos[0]['otros_egresos'], 2, '.', ',')) : ''; ?></td>
											</tr>
											<tr style="border-top: 3px solid #000 !important;">
												<th scope="row"></th>
												<td class="table-info"><span>Subtotales</span></td>
												<td class="text-end"><?= ($bandera) ? ('Q ' . number_format($datos[0]['sumaingresos'], 2, '.', ',')) : ''; ?></td>
												<td class="text-end"><?= ($bandera) ? ('Q ' . number_format($datos[0]['sumaegresos'], 2, '.', ',')) : ''; ?></td>
											</tr>
											<tr>
												<th scope="row"></th>
												<td class="table-success"><span>Saldo final</span></td>
												<td class="text-end"><?= ($bandera) ? ('Q 0.00') : ''; ?></td>
												<td class="text-end"><b><?= ($bandera) ? ('Q ' . number_format($datos[0]['saldofinal'], 2, '.', ',')) : ''; ?></b></td>
											</tr>
											<tr style="border-top: 3px solid #000 !important;" class="table-warning">
												<td colspan="2" class="text-center"><span><b>Sumas iguales</b></span></td>
												<td class="text-end"><b><?= ($bandera) ? ('Q ' . number_format($datos[0]['sumasiguales'], 2, '.', ',')) : ''; ?></b></td>
												<td class="text-end"><b><?= ($bandera) ? ('Q ' . number_format($datos[0]['sumasiguales'], 2, '.', ',')) : ''; ?></b></td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
					<div class="row justify-items-md-center">
						<div class="col align-items-center mt-2 mb-2" id="modal_footer">
							<?php if ($bandera) { ?>
								<button class="btn btn-outline-primary" onclick="save_apertura_cierre('<?= $_SESSION['id']; ?>', false, 'create_caja_cierre',<?= $datos[0]['id']; ?>)"><i class="fa-solid fa-box me-2"></i>Cerrar caja</button>
								<button type="button" class="btn btn-outline-danger" onclick="printdiv2('#cuadro','0')"><i class="fa-solid fa-ban"></i> Cancelar</button>
							<?php } ?>
							<!-- <button type="button" class="btn btn-outline-primary btn-sm" onclick="reportes([[],[],[],['4','3']], `pdf`, `arqueo_caja`,0)"> Arqueo</button> -->
							<button type="button" class="btn btn-outline-warning" onclick="salir()"><i class="fa-solid fa-circle-xmark"></i> Salir</button>
						</div>
					</div>
				</div>
			</div>
			<script>
			</script>
		<?php
		break;
	case 'pagos_individuales':
		$xtra = $_POST["xtra"];
		$codusu = $_SESSION["id"];
		$id_agencia = $_SESSION['id_agencia'];

		$numrecibo = 0;
		$i = 0;
		$consulta = mysqli_query($conexion, "SELECT MAX(CAST(CNUMING AS SIGNED)) numactual,usu.id_agencia FROM CREDKAR cred 
		INNER JOIN tb_usuario usu ON usu.id_usu=cred.CCODUSU 
		WHERE usu.id_agencia=" . $id_agencia . " AND cred.CTIPPAG='P' AND cred.CESTADO!='X' GROUP BY usu.id_agencia;");
		while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
			$numrecibo = $fila['numactual'];
			$i++;
		}
		$numrecibo++;

		$src = '../../includes/img/fotoClienteDefault.png';
		$datos[] = [];
		$datoscreppg[] = [];
		$cuotasnopagadas[] = [];
		$cuotasvencidas[] = [];
		$bandera = false;
		$i = 0;
		if ($xtra != 0) {
			$bandera = true;
			//SE REALIZA LA CONSULTA
			$consulta = mysqli_query($conexion, "SELECT cl.short_name AS nombrecli, cl.idcod_cliente AS codcli, ag.cod_agenc AS codagencia, cm.CCODPRD AS codprod, cm.CCODCTA AS ccodcta, cm.MonSug AS monsug, cm.NIntApro AS interes, cm.DFecDsbls AS fecdesembolso, cm.noPeriodo AS cuotas, ce.Credito AS tipocred, per.nombre AS nomper,
			((cm.MonSug)-(SELECT IFNULL(SUM(ck.KP),0) FROM CREDKAR ck WHERE ck.CESTADO!='X' AND ck.CTIPPAG='P' AND ck.CCODCTA=cm.CCODCTA)) AS saldocap,
			((SELECT IFNULL(SUM(nintere),0) FROM Cre_ppg WHERE ccodcta=cm.CCODCTA)-(SELECT IFNULL(SUM(ck.INTERES),0) FROM CREDKAR ck WHERE ck.CESTADO!='X' AND ck.CTIPPAG='P' AND ck.CCODCTA=cm.CCODCTA)) AS saldoint,
			prod.id_fondo, cl.url_img AS urlfoto
			FROM cremcre_meta cm
			INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
			INNER JOIN tb_agencia ag ON cm.CODAgencia=ag.cod_agenc
			INNER JOIN cre_productos prod ON prod.id=cm.CCODPRD
			INNER JOIN clhpzzvb_bd_general_coopera.tb_credito ce ON cm.CtipCre=ce.abre
			INNER JOIN clhpzzvb_bd_general_coopera.tb_periodo per ON cm.NtipPerC=per.periodo
			WHERE cm.Cestado='F' AND cm.TipoEnti='INDI' AND cm.CCODCTA='$xtra'
			GROUP BY cm.CCODCTA");
			while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
				$datos[$i] = $fila;
				//CARGADO DE LA IMAGEN
				$imgurl = __DIR__ . '/../../../../../' . $fila['urlfoto'];
				if (!is_file($imgurl)) {
					$src = '../../includes/img/fotoClienteDefault.png';
				} else {
					$imginfo   = getimagesize($imgurl);
					$mimetype  = $imginfo['mime'];
					$imageData = base64_encode(file_get_contents($imgurl));
					$src = 'data:' . $mimetype . ';base64,' . $imageData;
				}
				$i++;
				$bandera = true;
				$saldocapital=($fila['saldocap'])>0?number_format($fila['saldocap'],2):0;
				$saldointeres=($fila['saldoint'])>0?number_format($fila['saldoint'],2):0;
			}
			$bandera = false;
			//SEGUNDA CONSULTA PARA LOS PLANES DE PAGO
			$i = 0;
			$consulta = mysqli_query($conexion, "SELECT cpg.Id_ppg AS id, cpg.dfecven, IF((timestampdiff(DAY,cpg.dfecven,'$hoy'))<0, 0,(timestampdiff(DAY,cpg.dfecven,'$hoy'))) AS diasatraso, cpg.cestado, cpg.cnrocuo AS numcuota, cpg.ncappag AS capital, cpg.nintpag AS interes, cpg.nmorpag AS mora, cpg.AhoPrgPag AS ahorropro, cpg.OtrosPagosPag AS otrospagos
			FROM Cre_ppg cpg
			WHERE cpg.cestado='X' AND cpg.ccodcta='$xtra'
			ORDER BY cpg.ccodcta, cpg.dfecven, cpg.cnrocuo");
			while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
				$datoscreppg[$i] = $fila;
				$i++;
				$bandera = true;
			}
			//ORDENAR ARRAYS PARA LA IMPRESION DE DATOS
			$cuotasvencidas = array_filter($datoscreppg, function ($sk) {
				return $sk['diasatraso'] > 0;
			});
			//FILTRAR UN SOLO REGISTRO NO PAGADO
			$cuotasnopagadas = array_filter($datoscreppg, function ($sk) {
				return $sk['diasatraso'] == 0;
			});

			//SECCION DE REESTRUCTURACION 
			unset($datoscreppg);
			$datoscreppg[] = [];
			$sumacap = 0;
			$sumainteres = 0;
			$sumamora = 0;
			$sumaotrospagos = 0;
			$j = 0;
			//OBTIENE CUOTA VENCIDAS SI HUBIERAN
			if (count($cuotasvencidas) != 0) {
				for ($i = $j; $i < count($cuotasvencidas); $i++) {
					$datoscreppg[$i] = $cuotasvencidas[$i];
					$j++;
				}
			}
			//TRAE LOS PAGOS A LA FECHA SI HUBIERAN Y SINO TRAE LA SIGUIENTE EN CASO DE QUE NO HAYAN CUOTAS VENCIDAS
			if (count($cuotasnopagadas) != 0) {
				for ($i = $j; $i < count($cuotasnopagadas); $i++) {
					if ($cuotasnopagadas[$i]['dfecven'] <= $hoy2) {
						$datoscreppg[$j] = $cuotasnopagadas[$j];
						$i = 2000;
						$j++;
					} else {
						if (count($cuotasvencidas) == 0) {
							$datoscreppg[$j] = $cuotasnopagadas[$j];
							$i = 2000;
							$j++;
						}
					}
				}
			}
			if (count($datoscreppg) != 0) {
				$sumacap = array_sum(array_column($datoscreppg, "capital"));
				$sumainteres = array_sum(array_column($datoscreppg, "interes"));
				$sumamora = array_sum(array_column($datoscreppg, "mora"));
				// $sumaahorro = array_sum(array_column($datoscreppg, "ahorropro"));
				$sumaotrospagos = array_sum(array_column($datoscreppg, "otrospagos"));
				$sumafilas = $sumacap + $sumainteres + $sumamora + $sumaotrospagos;
			}
		}
		$concepto = ($bandera) ? "PAGO DE CRÉDITO A NOMBRE DE " . strtoupper($datos[0]['nombrecli']) . " CON NÚMERO DE RECIBO " . $numrecibo : "";
		?>
			<input type="text" id="condi" value="pagos_individuales" hidden>
			<input type="text" id="file" value="caja001" hidden>

			<div class="text" style="text-align:center">PAGO DE CRÉDITO INDIVIDUAL</div>
			<div class="card">
				<div class="card-header">Pago de crédito individual</div>
				<div class="card-body">

					<!-- seleccion de cliente y su credito-->
					<div class="container contenedort">
						<div class="row">
							<div class="col">
								<div class="text-center mb-2"><b>Información de cliente y crédito</b></div>
							</div>
						</div>
						<!-- usuario y boton buscar -->
						<div class="row">
							<div class="col-6 col-sm-6 col-md-2 mt-2">
								<img width="120" height="130" id="vistaPrevia" src="<?php if ($bandera) {
																						echo $src;
																					} else {
																						echo $src;
																					} ?>">
							</div>
							<div class="col-12 col-sm-12 col-md-10">
								<div class="row">
									<div class="col-12 col-sm-6">
										<div class="form-floating mb-2 mt-2">
											<input type="text" class="form-control" id="nomcli" placeholder="Nombre de cliente" readonly <?php if ($bandera) {
																																				echo 'value="' . $datos[0]['nombrecli'] . '"';
																																			} ?>>
											<label for="cliente">Nombre cliente</label>
										</div>
									</div>
									<div class="col-12 col-sm-6">
										<button type="button" class="btn btn-primary pt-3 pb-3 mb-2 mt-2 col-12 col-sm-12" data-bs-toggle="modal" data-bs-target="#modal_pagos_cre_individuales"><i class="fa-solid fa-magnifying-glass-plus me-2"></i>Buscar crédito a pagar</button>
									</div>
								</div>
								<!-- cargo, nombre agencia y codagencia  -->
								<div class="row">
									<div class="col-12 col-sm-6 col-md-4">
										<div class="form-floating mb-3 mt-2">
											<input type="text" class="form-control" id="id_cod_cliente" placeholder="Código de cliente" readonly <?php if ($bandera) {
																																						echo 'value="' . $datos[0]['codcli'] . '"';
																																					} ?>>
											<label for="id_cod_cliente">Código cliente</label>
										</div>
									</div>
									<div class="col-12 col-sm-6 col-md-4">
										<div class="form-floating mb-3 mt-2">
											<input type="text" class="form-control" id="codagencia" placeholder="Código de agencia" readonly <?php if ($bandera) {
																																					echo 'value="' . $datos[0]['codagencia'] . '"';
																																				} ?>>
											<label for="codagencia">Código de agencia</label>
										</div>
									</div>

									<div class="col-12 col-sm-12 col-md-4">
										<div class="form-floating mb-3 mt-2">
											<input type="text" class="form-control" id="codproducto" placeholder="Código de producto" readonly <?php if ($bandera) {
																																					echo 'value="' . $datos[0]['codprod'] . '"';
																																				} ?>>
											<label for="cargo">Codigo de producto</label>
										</div>
									</div>

								</div>
							</div>
						</div>

						<!-- cnumdoc, capital, gastos, total a desembolsar -->
						<div class="row">
							<div class="col-12 col-sm-6 col-md-4">
								<div class="form-floating mb-3 mt-2">
									<input type="text" class="form-control" id="codcredito" placeholder="Codigo de crédito" readonly <?php if ($bandera) {
																																			echo 'value="' . $datos[0]['ccodcta'] . '"';
																																		} ?>>
									<label for="nomagencia">Código de crédito</label>
								</div>
							</div>
							<div class="col-12 col-sm-6 col-md-4">
								<div class="form-floating mb-3 mt-2">
									<input type="text" class="form-control" id="ccapital" placeholder="Capital" readonly <?php if ($bandera) {
																																echo 'value="' . $datos[0]['monsug'] . '"';
																															} ?>>
									<label for="ccapital">Capital</label>
								</div>
							</div>
							<div class="col-12 col-sm-12 col-md-4">
								<div class="form-floating mb-3 mt-2">
									<input type="text" class="form-control" id="saldocap" placeholder="Saldo Capital" readonly <?php if ($bandera) {
																																	echo 'value="' . $saldocapital . '"';
																																} ?>>
									<label for="saldocap">Saldo capital</label>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12 col-sm-12 col-md-4">
								<div class="form-floating mb-3 mt-2">
									<input type="text" class="form-control" id="interes" placeholder="Interes" readonly <?php if ($bandera) {
																															echo 'value="' . $datos[0]['interes'] . '"';
																														} ?>>
									<label for="gastos">Interes</label>
								</div>
							</div>
							<div class="col-12 col-sm-6 col-md-4">
								<div class="form-floating mb-3 mt-2">
									<input type="text" class="form-control" id="fechadesembolso" placeholder="Fecha desembolso" readonly <?php if ($bandera) {
																																				echo 'value="' . $datos[0]['fecdesembolso'] . '"';
																																			} ?>>
									<label for="desembolsar">Fecha desembolso</label>
								</div>
							</div>
							<div class="col-12 col-sm-6 col-md-4">
								<div class="form-floating mb-3 mt-2">
									<input type="text" class="form-control" id="saldointeres" readonly <?php if ($bandera) {
																																		echo 'value="' . $saldointeres. '"';
																																	} ?>>
									<label for="saldo interes">Saldo interés</label>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12 col-sm-6 col-md-4">
								<div class="form-floating mb-3 mt-2">
									<input type="text" class="form-control" id="tipocredito" placeholder="Tipo de crédito" readonly <?php if ($bandera) {
																																		echo 'value="' . $datos[0]['tipocred'] . '"';
																																	} ?>>
									<label for="desembolsar">Tipo de crédito</label>
								</div>
							</div>
							<div class="col-12 col-sm-6 col-md-4">
								<div class="form-floating mb-3 mt-2">
									<input type="text" class="form-control" id="tipoperiodo" placeholder="Tipo de periodo" readonly <?php if ($bandera) {
																																		echo 'value="' . $datos[0]['nomper'] . '"';
																																	} ?>>
									<label for="desembolsar">Tipo de periodo</label>
								</div>
							</div>
							<div class="col-12 col-sm-6 col-md-4">
								<div class="form-floating mb-3 mt-2">
									<input type="text" class="form-control" id="cantcuotas" placeholder="Cantidad cuotas" readonly <?php if ($bandera) {
																																		echo 'value="' . $datos[0]['cuotas'] . '"';
																																	} ?>>
									<label for="desembolsar">Cantidad cuotas</label>
								</div>
							</div>
						</div>
					</div>
					<?php if ($bandera) { ?>
						<div class="container contenedort">
							<div class="row">
								<div class="col">
									<div class="text-center mb-2"><b>Detalle de boleta de pago</b></div>
								</div>
							</div>
							<div class="row">
								<div class="col-12 col-sm-4">
									<div class="form-floating mb-2 mt-2">
										<input type="text" class="form-control" id="norecibo" placeholder="Número de recibo" value="<?= $numrecibo ?>">
										<label for="norecibo">No. Recibo o Boleta</label>
									</div>
								</div>
								<div class="col-12 col-sm-4">
									<div class="form-floating mb-2 mt-2">
										<select id="metodoPago" name="metodoPago" aria-label="Default select example" onchange="showBTN()" class="form-select">
											<option value="1">Pago en Efectivo</option>
											<option value="0">Boleta de Banco</option>
										</select>
										<label for="metodoPago">Método de Pago:</label>
									</div>
								</div>
								<div class="col-12 col-sm-4">
									<div class="form-floating mb-2 mt-2">
										<input type="date" class="form-control" id="fecpag" placeholder="Fecha de pago" value="<?= date('Y-m-d') ?>">
										<label for="fecpag">Fecha de pago:</label>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-12"><span class="badge text-bg-primary">Verifique el concepto antes de guardar</span></div>
								<div class="col-sm-12 mb-2">
									<div class="form-floating">
										<textarea class="form-control" id="concepto" style="height: 100px" rows="1"><?= $concepto ?></textarea>
										<label for="concepto">Concepto</label>
									</div>
								</div>
							</div>
						</div>


						<!-- NEGROY AGREGAR BOLETAS DE PAGO ******************************************** -->
						<div class="container contenedort">
							<div class="row col">
								<div class="text-center mb-2"><b>Forma de Pago </b></div>
							</div>
							<!-- fila de seleccion de bancos -->
							<div class="row d-none mostrar">
								<div class="col-sm-6">
									<div class="form-floating mb-3">
										<select class="form-select" id="bancoid" onchange="buscar_cuentas()">
											<option value="F000" disabled selected>Seleccione un banco</option>
											<?php $bancos = mysqli_query($conexion, "SELECT * FROM tb_bancos WHERE estado='1'");
											while ($banco = mysqli_fetch_array($bancos)) {
												echo '<option value="' . $banco['id'] . '">' . $banco['id'] . "-" . $banco['nombre'] . '</option>';
											}
											?>
										</select>
										<label for="bancoid">Banco</label>
									</div>
								</div>
								<!-- id de cuenta para edicion --> <!-- select normal -->
								<div class="col-sm-6">
									<div class="form-floating mb-3">
										<select class="form-select" id="cuentaid">
											<option selected disabled value="F000">Seleccione una cuenta</option>
										</select>
										<label for="cuentaid">No. de Cuenta</label>
									</div>
								</div>
							</div>
							<!-- FECHA DE LA BOLETA  -->
							<div class="row d-none mostrar">
								<div class="col-sm-6">
									<div class="form-floating mb-3">
										<input type="date" class="form-control" id="fecpagBANC" value="<?= date('Y-m-d') ?>">
										<label for="fecpag">Fecha Boleta:</label>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-floating mb-3">
										<input type="text" class="form-control" id="noboletabanco" placeholder="Número de boleta de banco">
										<label for="noboletabanco">No. Boleta de Banco</label>
									</div>
								</div>
								<div class="row d-none">
									<input type="date" class="form-control" id="efectivo" value="<?= date('Y-m-d') ?>">
								</div>
							</div>
						</div>
						<!-- NEGROY AGREGAR BOLETAS DE PAGO *FIN*  ******************************************** -->
						<div class="container contenedort">
							<div class="row">
								<div class="col">
									<div class="text-center mb-2"><b>Pagos pendientes</b></div>
								</div>
							</div>
						<?php } ?>
						<!-- ES UN ROW POR CADA CUOTA -->
						<div class="accordion" id="cuotas">
							<?php
							if (!$bandera) { ?>
								<div class="alert alert-warning alert-dismissible fade show" role="alert">
									<div class="text-center">
										<strong class="me-2">¡Bienvenido!</strong>Debe seleccionar un crédito a pagar.
									</div>
									<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
								</div>

							<?php } else {
								$variables = [["warning", "X vencer"], ["danger", "Vencida"], ["success", "Vigente"]];
							?>

								<div class="row">
									<div class="col mb-2">
										<div class="accordion-item">
											<!-- ENCABEZADO -->
											<h2 class="accordion-header">
												<button id="bt0" onclick="opencollapse(0)" style="--bs-bg-opacity: .2;" class="accordion-button collapsed bg-<?php if ($datoscreppg[0]['diasatraso'] > 0) {
																																									echo 'danger';
																																								} else {
																																									echo 'success';
																																								} ?>" data-bs-target="#collaps0" aria-expanded="false" aria-controls="collaps0">
													<div class="row" style="font-size: 0.80rem;">
														<div class="col-sm-2">
															<span class="input-group-addon">Capital</span>
															<input id="capital0" disabled onclick="opencollapse(-1)" onchange="summon(this.id)" type="number" step="0.01" class="form-control habi form-control-sm" value="<?= $sumacap; ?>">
														</div>
														<div class="col-sm-2">
															<span class="input-group-addon">Interes</span>
															<input id="interes0" disabled onclick="opencollapse(-1)" onchange="summon(this.id)" type="number" step="0.01" class="form-control habi form-control-sm" value="<?= $sumainteres; ?>">
														</div>
														<div class="col-sm-2">
															<span class="input-group-addon">Mora</span>
															<input id="monmora0" disabled onclick="opencollapse(-1)" onchange="summon(this.id)" type="number" step="0.01" class="form-control habi form-control-sm" value="<?= $sumamora; ?>">
														</div>
														<div class="col-sm-2">
															<span class="input-group-addon">Otros</span>
															<div class="input-group">
																<input style="height: 10px !important;" id="otrospg0" disabled readonly onclick="opencollapse(-1);" onchange="summon(this.id)" type="number" step="0.01" class="form-control habi form-control-sm" value="<?= $sumaotrospagos; ?>">
																<span id="lotrospg0" title="Modificar detalle otros" class="input-group-addon btn btn-link" data-bs-toggle="modal" data-bs-target="#modalgastos" onclick="opencollapse(-1);"><i class="fa-solid fa-pen-to-square"></i></span>
															</div>
														</div>
														<div class="col-sm-2">
															<label class="form-label">Total</label>
															<input id="totalpg0" readonly onclick="opencollapse(-1)" onchange="summon(this.id)" type="number" step="0.01" class="form-control habi form-control-sm" value="<?= $sumafilas; ?>">
														</div>
														<div class="col-sm-1">
															<div class="form-check form-switch">
																<br>
																<input class="form-check-input" onclick="opencollapse('s0')" type="checkbox" role="switch" id="s0" title="Modificar pago">
															</div>
														</div>
													</div>
												</button>
											</h2>


											<!-- SECCION DE DETALLE DE UNA CUOTA -->
											<div id="collaps0" class="accordion-collapse collapse" data-bs-parent="#cuotas">
												<div class="accordion-body">
													<ul class="list-group">
														<?php
														for ($i = 0; $i < count($datoscreppg); $i++) {
														?>
															<li class="list-group-item">
																<div class="row" style="font-size: 0.80rem;">
																	<div class="col-sm-2">
																		<div class="row">
																			<span class="input-group-addon">No. Cuota</span>
																			<span class="input-group-addon"><?= $datoscreppg[$i]['numcuota']; ?></span>
																		</div>
																	</div>
																	<div class="col-sm-2">
																		<div class="row">
																			<span class="input-group-addon">Vencimiento:</span>
																			<span class="input-group-addon"><?= date("d-m-Y", strtotime($datoscreppg[$i]['dfecven'])); ?></span>
																		</div>
																	</div>
																	<div class="col-sm-2">
																		<div class="row">
																			<span class="input-group-addon">Capital</span>
																			<span class="input-group-addon"><?= $datoscreppg[$i]['capital']; ?></span>
																		</div>
																	</div>
																	<div class="col-sm-2">
																		<div class="row">
																			<span class="input-group-addon">Interes</span>
																			<span class="input-group-addon"><?= $datoscreppg[$i]['interes']; ?></span>
																		</div>
																	</div>
																	<div class="col-sm-2">
																		<div class="row">
																			<span class="input-group-addon">Dias Atraso</span>
																			<span class="input-group-addon"><?= $datoscreppg[$i]['diasatraso']; ?></span>
																		</div>
																	</div>
																	<div class="col-sm-2">
																		<div class="row">
																			<span class="input-group-addon">Estado</span>
																			<span class="input-group-addon badge text-bg-<?php if ($datoscreppg[$i]['diasatraso'] > 0) {
																																echo 'danger';
																															} else {
																																echo 'success';
																															} ?>"><?php if ($datoscreppg[$i]['diasatraso'] > 0) {
																																		echo 'Vencida';
																																	} else {
																																		echo 'Vigente';
																																	} ?></span>
																		</div>
																	</div>
																</div>
															</li>
														<?php } ?>
													</ul>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- FILA DEL TOTAL GENERAL -->
								<div class="row d-flex justify-content-end">
									<div class="col-4 mb-2">
										<span class="input-group-addon">Total General</span>
										<input id="totalgen" readonly type="number" step="0.01" class="form-control form-control-sm" value="<?= $sumafilas; ?>">
									</div>
								</div>
							<?php } ?>
						</div>
						</div>
						<!-- <div class="container"> -->
						<div class="row justify-items-md-center">
							<div class="col align-items-center mt-2" id="modal_footer">
								<?php if ($bandera) { ?>
									<button class="btn btn-outline-success" onclick="guardar_pagos_individuales(0, '<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $_SESSION['nombre'] . ' ' . $_SESSION['apellido']; ?>','<?= $datos[0]['ccodcta']; ?>','<?= $datoscreppg[0]['numcuota']; ?>','<?= $datos[0]['id_fondo']; ?>',<?= $sumacap; ?>,<?= $sumainteres; ?>,<?= $sumamora; ?>)"><i class="fa-solid fa-money-bill me-2"></i>Pagar</button>
									<button class="btn btn-outline-primary " onclick="mostrar_planpago('<?= $datos[0]['ccodcta']; ?>'); printdiv5('nomcli2,codcredito2/A,A/'+'/#/#/#/#',['<?= $datos[0]['nombrecli']; ?>','<?= $datos[0]['ccodcta']; ?>']);" data-bs-toggle="modal" data-bs-target="#modal_plan_pago"><i class="fa-solid fa-rectangle-list me-2"></i>Consultar plan de pago</button>
								<?php } ?>
								<button type="button" class="btn btn-outline-danger" onclick="printdiv2('#cuadro','0')">
									<i class="fa-solid fa-ban"></i> Cancelar
								</button>
								<button type="button" class="btn btn-outline-warning" onclick="salir()">
									<i class="fa-solid fa-circle-xmark"></i> Salir
								</button>
							</div>
						</div>
						<!-- </div> -->
				</div>
				<?php include_once "../../../src/cris_modales/mdls_pagos_individuales.php"; ?>
				<?php include_once "../../../src/cris_modales/mdls_planpago.php"; ?>

				<!-- Modal -->
				<div class="modal fade " id="modalgastos" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<h1 class="modal-title fs-5" id="exampleModalLabel">Detalle de otros cobros</h1>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								<input style="display: none;" id="flagid" readonly type="number" value="0">
							</div>
							<div class="modal-body">
								<table class="table" id="tbgastoscuota" class="display" style="width:100%">
									<thead>
										<tr>
											<th>Descripcion</th>
											<th>Afecta Otros modulos</th>
											<th>Adicional</th>
											<th>Monto</th>
										</tr>
									</thead>
									<tbody id="categoria_tb">
										<tr>
											<td>OTROS</td>
											<td data-id="0">-</td>
											<td data-cuenta="0">-</td>
											<td>
												<div class="row d-flex justify-content-end">
													<input style="display:none;" type="text" name="idgasto" value="0">
													<input style="display:none;" type="text" name="idcontable" value="0">
													<input onkeyup="sumotros()" style="text-align: right;" id="DS" type="number" step="0.01" class="form-control form-control-sm inputNoNegativo" value="<?= ($bandera) ? $sumaotrospagos : 0; ?>">
												</div>
											</td>
										</tr>
										<?php
										$query = "SELECT gas.id,gas.id_nomenclatura,gas.nombre_gasto,gas.afecta_modulo,cre.cntAho ccodaho,cre.NCapDes mondes, cre.noPeriodo cuotas,cre.moduloafecta,pro.* FROM cre_productos_gastos pro 
													INNER JOIN cre_tipogastos gas ON gas.id=pro.id_tipo_deGasto
													INNER JOIN cremcre_meta cre ON cre.CCODPRD=pro.id_producto
													WHERE cre.CCODCTA='" . $datos[0]['ccodcta'] . "' AND pro.tipo_deCobro=2";
										$consulta = mysqli_query($conexion, $query);
										$array_datos = array();

										$i = 0;
										while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
											$tipo = $fila['tipo_deMonto'];
											$cant = $fila['monto'];
											$calculax = $fila['calculox'];
											$mondes = $fila['mondes'];
											$cuotas = $fila['cuotas'];
											if ($tipo == 1) { //MONTO FIJO
												$mongas = ($calculax == 1) ? $cant : (($calculax == 2) ? ($cant / $cuotas) : $cant);
											}
											if ($tipo == 2) { //PORCENTAJE
												//$mongas = ($calculax == 1) ? ($cant / 100 * $amortiza1[$i]) : (($calculax == 2) ? ($cant / 100 * $row) : (($calculax == 3) ? ($cant / 100 * ($amortiza1[$i] + $row)) : 0));
											}
											$afecta = $fila["afecta_modulo"];
											$cremmodulo = $fila["moduloafecta"];
											$modulo = ($afecta == 1) ? 'AHORROS' : (($afecta == 2) ? 'APORTACIONES' : 'NO');
											$cuenta = ($afecta == 1 || $afecta == 2) ? (($cremmodulo == $afecta && strlen(trim($fila["ccodaho"])) >= 12) ? $fila["ccodaho"] : 'No hay cuenta vinculada') : 'NO';
											$disabled = ($afecta == 1 || $afecta == 2) ? (($cremmodulo == $afecta && strlen(trim($fila["ccodaho"])) >= 12) ? '' : 'disabled readonly') : '';

											// $cuenta = ($cremmodulo == $afecta) ? $fila["ccodaho"] : 'NO';
											echo '<tr>
													<td>' . $fila["nombre_gasto"] . '</td>
													<td data-id="' . $afecta . '">' . $modulo . '</td>
													<td data-cuenta="' . $cuenta . '">' . $cuenta . '</td>
													<td><div class="row d-flex justify-content-end">
															<input style="display:none;" type="text" name="idgasto" value="' . $fila['id'] . '">
															<input style="display:none;" type="text" name="idcontable" value="' . $fila['id_nomenclatura'] . '">
															<input ' . $disabled . ' onkeyup="sumotros()" style="text-align: right;" id="totalgen" type="number" step="0.01" class="form-control form-control-sm inputNoNegativo" value="0">
														</div></td>
												</tr>';

											$i++;
										}
										$cantfila = $i;
										?>
									</tbody>
								</table>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
							</div>
						</div>
					</div>
				</div>
				<script>
					//-------------------inicio chaka
					function procesarPagos(reestructura, cant, idusuario, idagencia, nomcompleto, codcredito, numerocuota, idfondo, verificamora) {
						var datos = [];
						var rows = 0;
						while (rows <= cant) {
							filas = getinputsval(['codcredito', 'monmora' + (rows), 'nomcli', 'capital' + (rows), 'interes' + (rows), 'otrospg' + (rows), 'totalpg' + (rows)]);
							datos[rows] = filas;
							rows++;
						}

						var detalles = [];
						var i = 0;
						$('#tbgastoscuota tr').each(function(index, fila) {
							var monto = $(fila).find('td:eq(3) input[type="number"]');
							monto = (isNaN(monto.val())) ? 0 : Number(monto.val());
							var idgasto = $(fila).find('td:eq(3) input[name="idgasto"]');
							idgasto = Number(idgasto.val());
							var idcontable = $(fila).find('td:eq(3) input[name="idcontable"]');
							idcontable = Number(idcontable.val());
							var modulo = $(fila).find('td:eq(1)').data('id');
							var codaho = $(fila).find('td:eq(2)').data('cuenta');

							if (monto > 0) {
								detalles[i] = [monto, idgasto, idcontable, modulo, codaho];
								i++;
							}
						});
						detalles = detalles.length > 0 ? detalles : null;

						if (verificamora == 1) {
							//SE TIENE QUE AUTORIZAR CAMBIO DE MORA
							clave_confirmar_mora_individual(idusuario, idagencia, nomcompleto, codcredito, numerocuota, idfondo, reestructura);
						} else {
							// PASA DIRECTAMENTE A GUARDAR
							obtiene([`nomcli`, `id_cod_cliente`, `codagencia`, `codproducto`, `codcredito`, `fechadesembolso`, `norecibo`, `fecpag`, `capital0`, `interes0`, `monmora0`, `otrospg0`, `totalgen`, `fecpagBANC`, `noboletabanco`, `concepto`], [`bancoid`, `cuentaid`, `metodoPago`], [], `create_pago_individual`, `0`, [idusuario, idagencia, nomcompleto, codcredito, numerocuota, idfondo, detalles, reestructura]);
						}
					}
					function guardar_pagos_individuales(cant, idusuario, idagencia, nomcompleto, codcredito, numerocuota, idfondo, kp, int, mor) {
						var capital = document.getElementById("capital0").value;
						capital = parseFloat(capital);
						var interes = document.getElementById("interes0").value;
						interes = parseFloat(interes);
						var mora = document.getElementById("monmora0").value;
						mora = parseFloat(mora);

						var verificamora = (mor.toFixed(2) != mora.toFixed(2)) ? 1 : 0;

						var reestructura = 0;
						// if (kp.toFixed(2) != capital.toFixed(2) || int.toFixed(2) != interes.toFixed(2)) {
						// 	Swal.fire({
						// 		title: "Se modificó la cuota, Confirme si se procede a una reestructuración del plan de pagos después de guardar el pago?",
						// 		text: " ",
						// 		icon: "question",
						// 		showCancelButton: true,
						// 		confirmButtonText: "Sí, Reestructurar",
						// 		confirmButtonColor: '#4CAF50', // Color verde
						// 		cancelButtonText: "No reestructurar, solo pagar"
						// 	}).then((result) => {
						// 		if (result.isConfirmed) {
						// 			reestructura = 1;
						// 		}
						// 		procesarPagos(reestructura, cant, idusuario, idagencia, nomcompleto, codcredito, numerocuota, idfondo, verificamora);
						// 	});
						// } else {
							procesarPagos(reestructura, cant, idusuario, idagencia, nomcompleto, codcredito, numerocuota, idfondo, verificamora);
						// }
					}

					function clave_confirmar_mora_individual(idusuario, idagencia, nomcompleto, codcredito, numerocuota, idfondo, reestructura) {
						Swal.fire({
							title: 'Autorización para modificación de mora',
							html: '<input id="user" class="swal2-input" type="text" placeholder="Usuario" autocapitalize="off">' +
								'<input id="pass" class="swal2-input" type="password" placeholder="contraseña" autocapitalize="off">',
							showCancelButton: true,
							confirmButtonText: 'Validar autorización',
							showLoaderOnConfirm: true,
							preConfirm: () => {
								const username = document.getElementById('user').value;
								const password = document.getElementById('pass').value;
								//AJAX PARA CONSULTAR EL USUARIO
								return $.ajax({
									url: "../../src/cruds/crud_usuario.php",
									method: "POST",
									data: {
										'condi': 'validar_usuario_por_mora',
										'username': username,
										'pass': password
									},
									dataType: 'json',
									success: function(data) {
										// console.log(data);
										if (data[1] != "1") {
											Swal.showValidationMessage(data[0]);
										}
									}
								}).catch(xhr => {
									Swal.showValidationMessage(`${xhr.responseJSON[0]}`);
								});
							},
							allowOutsideClick: (outsideClickEvent) => {
								const isLoading = Swal.isLoading();
								const isClickInsideDialog = outsideClickEvent?.target?.closest('.swal2-container') !== null;
								return !isLoading && !isClickInsideDialog;
							}
						}).then((result) => {
							if (result.isConfirmed) {
								procesarPagos(reestructura, 0, idusuario, idagencia, nomcompleto, codcredito, numerocuota, idfondo, 0);
							}
						});
					}
					//------------------ fin chaka



					function sumotros() {
						var valor = 0
						$('#tbgastoscuota tr').each(function(index, fila) {
							var inputDS = $(fila).find('td:eq(3) input[type="number"]');
							var valorDS = Number(inputDS.val());
							valor += (isNaN(valorDS)) ? 0 : valorDS;
						});
						$("#otrospg0").val(valor);
						summon("otrospg0")
					}
					var inputs = document.querySelectorAll('.inputNoNegativo');
					inputs.forEach(function(input) {
						input.addEventListener('input', function() {
							var expresion = input.value;
							var esValida = /^-?\d+(\.\d+)?([-+*/]\d+(\.\d+)?)*$/.test(expresion);
							if (!esValida) {
								alert("Por favor, ingresa una expresión matemática válida.");
								input.value = 0;
								sumotros();
							}
							if (parseFloat(input.value) < 0) {
								input.value = 0;
								sumotros();
							}
						});
					});
				</script>
			<?php
			break;
		case 'reimpresion_recibo_indi':
			$xtra = $_POST["xtra"];
			$usuario = $_SESSION["id"];
			$id_agencia = $_SESSION['id_agencia'];
			$nombreS = $_SESSION['nombre'];
			$apellidoS = $_SESSION['apellido'];
			$where = "";
			$mensaje_error = "";
			$bandera_error = false;
			//Validar si ya existe un registro igual que el nombre
			$nuew = "ccodusu='$usuario' AND (dfecsis BETWEEN '" . date('Y-m-d', strtotime(date('Y-m-d') . ' - 7 days')) . "' AND  '" . date('Y-m-d') . "')";
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
				<input type="text" id="condi" value="reimpresion_recibo_indi" hidden>
				<input type="text" id="file" value="caja001" hidden>

				<div class="text" style="text-align:center">REIMPRESION DE RECIBO DE CRÉDITOS INDIVIDUALES</div>
				<div class="card">
					<div class="card-header">Reimpresión de recibo de créditos individuales</div>
					<div class="card-body">
						<?php if ($bandera_error) { ?>
							<div class="alert alert-warning alert-dismissible fade show" role="alert">
								<strong>¡Error!</strong> <?= $mensaje_error; ?>
								<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
							</div>
						<?php } ?>
						<!-- tabla de recibos individuales -->
						<div class="row mt-2 pb-2">
							<div class="table-responsive">
								<table id="table-recibos-individuales" class="table table-hover table-border nowrap" style="width:100%">
									<thead class="text-light table-head-aprt mt-2">
										<tr>
											<th>Crédito</th>
											<th>Recibo</th>
											<th>Ciclo</th>
											<th>Fecha Doc.</th>
											<th>Monto</th>
											<th col-lg-1 col-md-1>Acción</th>
										</tr>
									</thead>
									<tbody style="font-size: 0.9rem !important;">

									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<?php include_once "../../../src/cris_modales/mdls_editReciboCreIndi.php"; ?>

				<script>
					$(document).ready(function() {
						$("#table-recibos-individuales").DataTable({
							"processing": true,
							"serverSide": true,
							"sAjaxSource": "../../src/server_side/recibo_credito_individual.php",
							columns: [{
									data: [1]
								},
								{
									data: [2]
								},
								{
									data: [3]
								},
								{
									data: [4]
								},
								{
									data: [5]
								},
								{
									data: [0],
									render: function(data, type, row) {
										imp = '';
										imp1 = '';
										imp2 = '';
										const separador = "||";
										var dataRow = row.join(separador);
										if (row[9] == "1") {
											imp1 = `<button type="button" class="btn btn-outline-secondary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#staticBackdrop" onclick="capData('${dataRow}',['#idR','#recibo','#fecha','#concepto'],[0,2,4,7])"><i class="fa-sharp fa-solid fa-pen-to-square"></i></button>`;
											imp2 = `<button type="button" class="btn btn-outline-danger btn-sm mt-2" onclick="eliminar('${row[0]}','eliReIndi','<?php echo $usuario ?>')"><i class="fa-solid fa-trash-can"></i></button>`;
										}
										imp = `<button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="reportes([[], [], [], ['<?php echo $_SESSION['nombre'] . ' ' . $_SESSION['apellido'] ?>', '${row[1]}', '${row[6]}', '${row[2]}']], 'pdf', '14', 0,1)"><i class="fa-solid fa-print me-2"></i>Reimprimir</button>`;
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
		<?php
			break;
	} ?>