var tablaRecibos;
//Funcion para eliminar Recibo de creditos individuales
function eliminar(ideliminar, condi, archivo) {
	//console.log(ideliminar+" - "+condi+" - "+archivo)
	//return
	dire = "../../src/cruds/crud_caja.php";
	//alert(ideliminar + ' ' + condi + ' ' + archivo);

	Swal.fire({
		title: "¿ESTA SEGURO DE ELIMINAR?",
		showDenyButton: true,
		confirmButtonText: "Eliminar",
		denyButtonText: `Cancelar`,
	}).then((result) => {
		if (result.isConfirmed) {
			$.ajax({
				url: dire,
				method: "POST",
				data: { condi, ideliminar, archivo },
				beforeSend: function () {
					loaderefect(1);
				},
				success: function (data) {
					const data2 = JSON.parse(data);

					if (data2[1] == "1") {
						// if (condi === 'eliReGru') {
						//     recargarTabla();
						//     Swal.fire("Correcto", "Eliminado", "success");
						//     return;
						// }
						Swal.fire("Correcto", "Eliminado", "success");
						printdiv2("#cuadro", 0);
					} else Swal.fire("X(", data2[0], "error");
				},
				complete: function () {
					loaderefect(0);
				},
			});
		}
	});
}

//Funcion para capturar datos
function capData(dataPhp, dataJava = 0, pos = []) {
	let data = dataPhp.split("||");

	// console.log('Data de php--> ' + data);

	if (pos.length == 0) dataPos = dataJava.length;
	else dataPos = pos.length;

	for (let i = 0; i < dataPos; i++) {
		if ($(dataJava[i]).is('input')) {
			$(dataJava[i]).val(data[pos[i]]);
		}
		if ($(dataJava[i]).is('label')) {
			$(dataJava[i]).text(data[pos[i]]);
		}
		if ($(dataJava[i]).is('textarea')) {
			$(dataJava[i]).val(data[pos[i]]);
		}
	}
}

function capDataMul(nameEle, totalEle) {
	total = $(totalEle).val();
	var array = [];
	// console.log("Totla de elementos " + total);
	for (var con = 1; con <= total; con++) {
		var dato = ($("#" + con + nameEle).val()).trim();
		//console.log("Info... "+ dato);
		array.push(dato);
	}
	return array;
}

function dataName(nameEle, tipo) {
	var elementos = document.querySelectorAll('' + tipo + '[name="' + nameEle + '[]"]');
	var valores = [];
	elementos.forEach(function (elemento) {
		if (tipo === 'input') valores.push(elemento.value);
		if (tipo === 'td') valores.push(elemento.textContent);
		if (tipo === 'textarea') valores.push(elemento.value);

		//   if (tipo === 'textarea'){
		//     alert("Ingreso a la opcion "); 
		//     valores.push(elemento.textContent); 
		//   }
	});
	return valores;
}

// Buscar a los clientes 
function inyecCod(idElem, condi, extra = "0", url = "../../src/cruds/crud_caja.php") {
	//console.log(typeof(extra)+" Informacion del array : "+extra);return; 

	$.ajax({
		url: url,
		type: "POST",
		data: { condi, extra },
		beforeSend: function () {
			loaderefect(1);
		},
		success: function (data) {
			$(idElem).html(data);
		},
		complete: function () {
			loaderefect(0);
		}
	});
}

//Cerrar modal
function cerrarModal(modalCloss) {
	$(modalCloss).modal("hide"); // CERRAR MODAL
}

//#region printdivs
function printdiv(condi, idiv, dir, xtra) {
	dire = "./caja/" + dir + ".php";
	$.ajax({
		url: dire, method: "POST", data: { condi, xtra },
		beforeSend: function () {
			loaderefect(1);
		},
		success: function (data) {
			$(idiv).html(data);
		},
		complete: function () {
			loaderefect(0);
		}
	})
}
//para recargar en el mismo archivo, solo mandar id del cuadro y el extra
function printdiv2(idiv, xtra) {
	condi = $("#condi").val();
	dir = $("#file").val();
	dire = "caja/" + dir + ".php";
	$.ajax({
		url: dire, method: "POST", data: { condi, xtra },
		beforeSend: function () {
			loaderefect(1);
		},
		success: function (data) {
			$(idiv).html(data);
		},
		complete: function () {
			loaderefect(0);
		}
	})
}

function abrir_modal(id_modal, id_hidden, dato) {
	$(id_modal).modal('show');
	$(id_hidden).val(dato);
}

function seleccionar_cuenta_ctb2(id_hidden, valores) {
	printdiv5(id_hidden, valores);
}

//cerrarModal
function cerrar_modal(id_modal, estado, id_hidden) {
	$(id_modal).modal(estado);
	$(id_hidden).val("");
}

//#endregion
//#region Obtiene
function obtiene(inputs, selects, radios, condi, id, archivo) {
	//loaderefect(1);
	//alert("Datos -->"+archivo[0]); return; 

	var inputs2 = []; var selects2 = []; var radios2 = [];
	inputs2 = getinputsval(inputs)
	selects2 = getselectsval(selects)
	radios2 = getradiosval(radios)

	//console.log[inputs2]; return; 
	generico(inputs2, selects2, radios2, condi, id, archivo);
}
//--
function generico(inputs, selects, radios, condi, id, archivo) {
	//console.log("Datos "+inputs+" Cacep "+archivo[1]); return; 
	$.ajax({
		url: "../../src/cruds/crud_caja.php",
		method: "POST",
		data: { inputs, selects, radios, condi, id, archivo },
		beforeSend: function () {
			loaderefect(1);
		},
		success: function (data) {
			console.log(data);
			const data2 = JSON.parse(data);
			console.log(data2);
			//return;
			if (data2[1] == "1") {
				// if (condi === 'actReciCreGru') {
				//     recargarTabla();
				//     Swal.fire({ icon: 'success', title: 'Muy Bien!', text: data2[0] })
				//     return;
				// }
				Swal.fire({ icon: 'success', title: 'Muy Bien!', text: data2[0] })
				printdiv2("#cuadro", id);
				if (condi == "create_pago_individual") reportes([[], [], [], [archivo[2], archivo[3], data2[3], data2[2]]], 'pdf', '14', 0, 1);
				if (condi == "paggrupal") {
					reportes([[], [], [], [archivo[1], data2[2], data2[3]]], 'pdf', '15', 0, 1);//COMPROBANTE NORMAL 
					reportes([[], [], [], [archivo[1], data2[2], data2[3]]], 'pdf', 'recibo_pago_grup_resumen', 0);//COMPROBANTE RESUMEN 
				}
				if (condi == "create_caja_cierre") reportes([[], [], [], [archivo[0], archivo[1]]], `pdf`, 'arqueo_caja', 0);
			}
			else {
				Swal.fire({ icon: 'error', title: '¡ERROR!', text: data2[0] })
			}
		},
		complete: function () {
			loaderefect(0);
		}
	})
}
function consultar_reporte(file, bandera) {
	return new Promise(function (resolve, reject) {
		if (bandera == 0) {
			resolve('Aprobado');
		}
		$.ajax({
			url: "../../src/cruds/crud_caja.php",
			method: "POST",
			data: { 'condi': 'consultar_reporte', 'id_descripcion': file },
			beforeSend: function () {
				loaderefect(1);
			},
			success: function (data) {
				const data2 = JSON.parse(data);
				if (data2[1] == "1") {
					resolve(data2[2]);
				} else {
					reject(data2[0]);
				}
			},
			complete: function () {
				loaderefect(0);
			},
		});
	});
}

function reportes(datos, tipo, file, download, bandera = 0) {
	var datosval = [];
	datosval[0] = getinputsval(datos[0]);
	datosval[1] = getselectsval(datos[1]);
	datosval[2] = getradiosval(datos[2]);
	datosval[3] = datos[3];
	//CONSULTA PARA TRAER QUE REPORTE SE QUIERE
	consultar_reporte(file, bandera).then(function (action) {
		//PARTE ENCARGADA DE GENERAR EL REPORTE
		if (bandera == 1) {
			file = action;
		} else {
			file = file;
		}
		var url = "caja/reportes/" + file + ".php";
		$.ajax({
			url: url, async: true, type: "POST", dataType: "html", data: { datosval, tipo },
			beforeSend: function () {
				loaderefect(1);
			},
			success: function (data) {
				var opResult = JSON.parse(data);
				console.log(opResult)
				if (opResult.status == 1) {
					switch (download) {
						case 0:
							const ventana = window.open();
							ventana.document.write("<object data='" + opResult.data + "' type='application/" + opResult.tipo + "' width='100%' height='100%'></object>")
							break;
						case 1:
							var $a = $("<a href='" + opResult.data + "' download='" + opResult.namefile + "." + tipo + "'>");
							$("body").append($a);
							$a[0].click();
							$a.remove();
							break;
					}
					if (file != 'arqueo_caja') {
						Swal.fire({ icon: 'success', title: 'Muy Bien!', text: opResult.mensaje })
					}
				}
				else {
					Swal.fire({ icon: 'error', title: '¡ERROR!', text: opResult.mensaje })
				}
			},
			complete: function () {
				loaderefect(0);
			}
		})

	}).catch(function (error) {
		Swal.fire("Uff", error, "error");
	});
}
//#endregion

function opencollapse(i) {
	event.stopPropagation();
	if (i >= 0) {
		if ($('#collaps' + i).hasClass('collapse')) {
			$('.accordion-collapse').addClass('collapse');
			$('#collaps' + i).removeClass("collapse");
		} else {
			$('.accordion-collapse').addClass('collapse');
		}
	}
	if (i.toString().substring(0, 1) == "s") {
		if ($('#' + i).is(':checked')) {
			changedisabled('#bt' + i.substring(1) + ' .habi', 1);
		} else {
			changedisabled('#bt' + i.substring(1) + ' .habi', 0);
		}
	}
}

function changedisabled(padre, status) {
	if (status == 0) $(padre).attr('disabled', 'disabled');
	else $(padre).removeAttr('disabled');
}

let numOr0 = n => isNaN(parseFloat(n)) ? 0 : parseFloat(n);

function summon(id) {
	let rows = id.substring(7, 9);
	let filas = getinputsval(['capital' + (rows), 'interes' + (rows), 'monmora' + (rows), 'otrospg' + (rows)]);
	let sumdata = filas.reduce((a, b) => numOr0(a) + numOr0(b));
	$('#totalpg' + rows).val(sumdata.toFixed(2));
	var i = 0; let filtot = [];
	while (i != (-1)) {
		filtot[i] = getinputsval(['totalpg' + i]);
		i = (!!document.getElementById('totalpg' + (i + 1))) ? i + 1 : (-1);
	}
	let total = filtot.reduce((a, b) => numOr0(a) + numOr0(b));
	$('#totalgen').val((parseFloat(total)).toFixed(2));
}

//#region PRINTDIV5 
function printdiv5(id_hidden, valores) {
	//ver si sacar el dato de un idhidden o directamente un toString
	var cadena = id_hidden.substr(0, 1);
	if (cadena == "#") {
		//todo el input
		var todo = ($(id_hidden).val()).split("/");
	}
	else {
		//todo la cadena
		var todo = id_hidden.split("/");
	}

	//se extraen los nombres de los inputs
	var nomInputs = (todo[0].toString()).split(",");
	//se extraen los rangos
	var rangos = (todo[1].toString()).split(",");
	//se extrae el separador
	var separador = todo[2].toString();

	//todo lo relacionado a la habilitacion o deshabilitacion
	var habilitar = [];
	var deshabilitar = [];
	if (todo[3].toString() != "#") {
		habilitar = (todo[3].toString()).split(",")
	}
	if (todo[4].toString() != "#") {
		deshabilitar = (todo[4].toString()).split(",")
	}
	habilitar_deshabilitar(habilitar, deshabilitar);
	//----fin de la habilitacion y deshabilitacion

	//todo lo relacionado con show y hide de elementos
	var mostrar = [];
	var ocultar = [];
	if (todo[5].toString() != "#") {
		mostrar = (todo[5].toString()).split(",")
	}
	if (todo[6].toString() != "#") {
		ocultar = (todo[6].toString()).split(",")
	}
	mostrar_nomostrar(mostrar, ocultar);
	//fin de los elementos hidden o visible

	// tratar de validar o unir campos para mandarlos a un solo input
	var contador = 0;
	for (var index = 0; index < nomInputs.length; index++) {
		if (rangos[index] !== 'A') {
			var aux = rangos[index].toString();
			var arrayaux = aux.split("-");
			var concatenacion = "";
			for (var index2 = arrayaux[0]; index2 <= arrayaux[1]; index2++) {
				if (index2 === arrayaux[0]) {
					concatenacion = concatenacion + valores[index2 - 1];
				} else {
					concatenacion = concatenacion + " " + separador + " ";
					concatenacion = concatenacion + valores[index2 - 1];
				}
				contador++;
			}
			if (nomInputs[index] != "") {
				$("#" + nomInputs[index]).val(concatenacion);
			}
		} else {
			if (nomInputs[index] != "") {
				$("#" + nomInputs[index]).val(valores[contador]);
			}
			contador++;
		}
	}
}

function habilitar_deshabilitar(hab, des) {
	var i = 0;
	while (i < hab.length) {
		document.getElementById(hab[i]).disabled = false;
		i++;
	}
	var i = 0;
	while (i < des.length) {
		document.getElementById(des[i]).disabled = true;
		i++;
	}
}

function mostrar_nomostrar(mostrar, ocultar) {
	var i = 0;
	while (i < mostrar.length) {
		document.getElementById(mostrar[i]).style.display = "block";
		i++;
	}
	var i = 0;
	while (i < ocultar.length) {
		document.getElementById(ocultar[i]).style.display = "none";
		i++;
	}
}

// function savepag(cant, user, idgrup, idfondo, ciclo, id_agencia) {
// 	var datainputs = [];
// 	var rows = 0;
// 	var filas = [];
// 	while (rows <= cant) {
// 		filas = getinputsval(['ccodcta' + (rows), 'namecli' + (rows), 'capital' + (rows), 'interes' + (rows), 'monmora' + (rows), 'otrospg' + (rows), 'totalpg' + (rows)]);
// 		datainputs[rows] = filas;
// 		rows++;
// 	}
// 	datadetal = getinputsval(['numdoc', 'fecha'])
// 	generico([datainputs, datadetal], 0, 0, 'paggrupal', [0], [user, idgrup, idfondo, ciclo, id_agencia], 'crud_caja');
// }

// function guardar_pagos_individuales(cant, idusuario, idagencia, nomcompleto, codcredito, numerocuota, idfondo) {
// 	var datos = [];
// 	var rows = 0;
// 	while (rows <= cant) {
// 		filas = getinputsval(['codcredito', 'monmora' + (rows), 'nomcli', 'capital' + (rows), 'interes' + (rows), 'otrospg' + (rows), 'totalpg' + (rows)]);
// 		datos[rows] = filas;
// 		rows++;
// 	}
// 	//OBTIENE DATOS DE OTROS (DETALLE)
// 	var detalles = [];
// 	var i = 0;
// 	$('#tbgastoscuota tr').each(function (index, fila) {
// 		var monto = $(fila).find('td:eq(3) input[type="number"]');
// 		monto = (isNaN(monto.val())) ? 0 : Number(monto.val());
// 		var idgasto = $(fila).find('td:eq(3) input[name="idgasto"]');
// 		idgasto = Number(idgasto.val());
// 		var idcontable = $(fila).find('td:eq(3) input[name="idcontable"]');
// 		idcontable = Number(idcontable.val());
// 		var modulo = $(fila).find('td:eq(1)').data('id');
// 		var codaho = $(fila).find('td:eq(2)').data('cuenta');

// 		if (monto > 0) {
// 			detalles[i] = [monto, idgasto, idcontable, modulo, codaho];
// 			i++;
// 		}
// 	});
// 	detalles = detalles.length > 0 ? detalles : null;

// 	// console.log(detalles);
// 	// return;
// 	//NUEVA FORMA DE EJECUTAR CONSULTAS AJAX
// 	var verificando_clave = new Promise(function (resolve, reject) {
// 		// resolve('obtiene'); //--REQ--fape--1--Opcion para no validar usuario por cambio de mora
// 		// return; //--REQ--fape--1--Opcion para no validar usuario por cambio de mora
// 		$.ajax({
// 			url: "../../src/cruds/crud_caja.php",
// 			method: "POST",
// 			data: { 'condi': 'validar_mora_individual', 'datosmora': datos },
// 			beforeSend: function () {
// 				loaderefect(1);
// 			},
// 			success: function (data) {
// 				const data2 = JSON.parse(data);
// 				if (data2[1] == "1") {
// 					if (data2[2] == 1) {
// 						resolve('confirmar_clave');
// 					} else {
// 						resolve('obtiene');
// 					}
// 				} else {
// 					reject(data2[0]);
// 				}
// 			},
// 			complete: function () {
// 				loaderefect(0);
// 			},
// 		});
// 	});

// 	verificando_clave.then(function (action) {
// 		if (action === 'confirmar_clave') {
// 			// console.log('ejecutando la funcion confirmar');
// 			clave_confirmar_mora_individual(idusuario, idagencia, nomcompleto, codcredito, numerocuota, idfondo);
// 		} else if (action === 'obtiene') {
// 			// console.log('ejecutando la funcion obtiene');inputs, selects, radios, condi, id, archivo)
// 			obtiene([`nomcli`, `id_cod_cliente`, `codagencia`, `codproducto`, `codcredito`, `fechadesembolso`, `norecibo`, `fecpag`, `capital0`, `interes0`, `monmora0`, `otrospg0`, `totalgen`, `fecpagBANC`, `noboletabanco`, `concepto`], [`bancoid`, `cuentaid`, `metodoPago`], [], `create_pago_individual`, `0`, [idusuario, idagencia, nomcompleto, codcredito, numerocuota, idfondo, detalles]);

// 		}
// 	}).catch(function (error) {
// 		Swal.fire("Uff", error, "error");
// 	});

// }

// function clave_confirmar_mora_individual(idusuario, idagencia, nomcompleto, codcredito, numerocuota, idfondo) {
// 	Swal.fire({
// 		title: 'Autorización para modificación de mora',
// 		html:
// 			'<input id="user" class="swal2-input" type="text" placeholder="Usuario" autocapitalize="off">' +
// 			'<input id="pass" class="swal2-input" type="password" placeholder="contraseña" autocapitalize="off">',
// 		showCancelButton: true,
// 		confirmButtonText: 'Validar autorización',
// 		showLoaderOnConfirm: true,
// 		preConfirm: () => {
// 			const username = document.getElementById('user').value;
// 			const password = document.getElementById('pass').value;
// 			//AJAX PARA CONSULTAR EL USUARIO
// 			return $.ajax({
// 				url: "../../src/cruds/crud_usuario.php",
// 				method: "POST",
// 				data: { 'condi': 'validar_usuario_por_mora', 'username': username, 'pass': password },
// 				dataType: 'json',
// 				success: function (data) {
// 					// console.log(data);
// 					if (data[1] != "1") {
// 						Swal.showValidationMessage(data[0]);
// 					}
// 				}
// 			}).catch(xhr => {
// 				Swal.showValidationMessage(`${xhr.responseJSON[0]}`);
// 			});
// 		},
// 		allowOutsideClick: (outsideClickEvent) => {
// 			const isLoading = Swal.isLoading();
// 			const isClickInsideDialog = outsideClickEvent?.target?.closest('.swal2-container') !== null;
// 			return !isLoading && !isClickInsideDialog;
// 		}
// 	}).then((result) => {
// 		if (result.isConfirmed) {
// 			var datos = [];
// 			var rows = 0;
// 			var cant=0;
// 			while (rows <= cant) {
// 				filas = getinputsval(['codcredito', 'monmora' + (rows), 'nomcli', 'capital' + (rows), 'interes' + (rows), 'otrospg' + (rows), 'totalpg' + (rows)]);
// 				datos[rows] = filas;
// 				rows++;
// 			}
// 			//OBTIENE DATOS DE OTROS (DETALLE)
// 			var detalles = [];
// 			var i = 0;
// 			$('#tbgastoscuota tr').each(function (index, fila) {
// 				var monto = $(fila).find('td:eq(3) input[type="number"]');
// 				monto = (isNaN(monto.val())) ? 0 : Number(monto.val());
// 				var idgasto = $(fila).find('td:eq(3) input[name="idgasto"]');
// 				idgasto = Number(idgasto.val());
// 				var idcontable = $(fila).find('td:eq(3) input[name="idcontable"]');
// 				idcontable = Number(idcontable.val());
// 				var modulo = $(fila).find('td:eq(1)').data('id');
// 				var codaho = $(fila).find('td:eq(2)').data('cuenta');

// 				if (monto > 0) {
// 					detalles[i] = [monto, idgasto, idcontable, modulo, codaho];
// 					i++;
// 				}
// 			});
// 			detalles = detalles.length > 0 ? detalles : null;
// 			//aqui debera ejecutarse el obtiene
// 			// obtiene([`nomcli`, `id_cod_cliente`, `codagencia`, `codproducto`, `codcredito`, `fechadesembolso`, `norecibo`, `fecpag`, `capital0`, `interes0`, `monmora0`, `otrospg0`, `totalgen`], [], [], `create_pago_individual`, `0`, [idusuario, idagencia, nomcompleto, codcredito, numerocuota, idfondo]);
// 			obtiene([`nomcli`, `id_cod_cliente`, `codagencia`, `codproducto`, `codcredito`, `fechadesembolso`, `norecibo`, `fecpag`, `capital0`, `interes0`, `monmora0`, `otrospg0`, `totalgen`, `fecpagBANC`, `noboletabanco`, `concepto`], [`bancoid`, `cuentaid`, `metodoPago`], [], `create_pago_individual`, `0`, [idusuario, idagencia, nomcompleto, codcredito, numerocuota, idfondo, detalles]);
// 		}
// 	});
// }

//VALIDACION DE FORMA PARA CREDITOS GRUPALES
// function guardar_pagos_grupales(cant, idusuario, idgrup, ciclo, idfondo, idagencia) {
// 	var datos = [];
// 	var rows = 0;
// 	var filas = [];
// 	while (rows <= (cant)) {
// 		filas = getinputsval(['ccodcta' + (rows), 'namecli' + (rows), 'capital' + (rows), 'interes' + (rows), 'monmora' + (rows), 'otrospg' + (rows), 'totalpg' + (rows)]);
// 		datos[rows] = filas;
// 		rows++;
// 	}

// 	//NUEVA FORMA DE EJECUTAR CONSULTAS AJAX
// 	var verificando_clave = new Promise(function (resolve, reject) {
// 		// resolve('obtiene'); //--REQ--fape--1--Opcion para no validar usuario por cambio de mora
// 		// return; //--REQ--fape--1--Opcion para no validar usuario por cambio de mora
// 		$.ajax({
// 			url: "../../src/cruds/crud_caja.php",
// 			method: "POST",
// 			data: { 'condi': 'validar_mora_grupal', 'datosmora': datos, 'idgrupo': idgrup, 'ciclo': ciclo },
// 			beforeSend: function () {
// 				loaderefect(1);
// 			},
// 			success: function (data) {
// 				const data2 = JSON.parse(data);
// 				if (data2[1] == "1") {
// 					if (data2[2] == 1) {
// 						resolve('confirmar_clave');
// 					} else {
// 						resolve('obtiene');
// 					}
// 				} else {
// 					reject(data2[0]);
// 				}
// 			},
// 			complete: function () {
// 				loaderefect(0);
// 			},
// 		});
// 	});

// 	verificando_clave.then(function (action) {
// 		if (action === 'confirmar_clave') {
// 			clave_confirmar_mora_grupal(cant, idusuario, idgrup, idfondo, ciclo, idagencia);
// 		} else if (action === 'obtiene') {
// 			savepag(cant, idusuario, idgrup, idfondo, ciclo, idagencia);
// 		}
// 	}).catch(function (error) {
// 		Swal.fire("Uff", error, "error");
// 	});

// }

// function clave_confirmar_mora_grupal(cant, idusuario, idgrup, idfondo, ciclo, idagencia) {
// 	Swal.fire({
// 		title: 'Autorización para modificación de mora',
// 		html:
// 			'<input id="user" class="swal2-input" type="text" placeholder="Usuario" autocapitalize="off">' +
// 			'<input id="pass" class="swal2-input" type="password" placeholder="contraseña" autocapitalize="off">',
// 		showCancelButton: true,
// 		confirmButtonText: 'Validar autorización',
// 		showLoaderOnConfirm: true,
// 		preConfirm: () => {
// 			const username = document.getElementById('user').value;
// 			const password = document.getElementById('pass').value;
// 			//AJAX PARA CONSULTAR EL USUARIO
// 			return $.ajax({
// 				url: "../../src/cruds/crud_usuario.php",
// 				method: "POST",
// 				data: { 'condi': 'validar_usuario_por_mora', 'username': username, 'pass': password },
// 				dataType: 'json',
// 				success: function (data) {
// 					// console.log(data);
// 					if (data[1] != "1") {
// 						Swal.showValidationMessage(data[0]);
// 					}
// 				}
// 			}).catch(xhr => {
// 				Swal.showValidationMessage(`${xhr.responseJSON[0]}`);
// 			});
// 		},
// 		allowOutsideClick: (outsideClickEvent) => {
// 			const isLoading = Swal.isLoading();
// 			const isClickInsideDialog = outsideClickEvent?.target?.closest('.swal2-container') !== null;
// 			return !isLoading && !isClickInsideDialog;
// 		}
// 	}).then((result) => {
// 		if (result.isConfirmed) {
// 			//aqui debera ejecutarse el obtiene
// 			savepag(cant, idusuario, idgrup, idfondo, ciclo, idagencia);
// 		}
// 	});
// }

var tablaRecibos;
function tablaRecibos(codusu) {
	tablaRecibos = $("#tabla_recibos").DataTable({
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
			render: function (data, type, row) {
				imp = '';

				const separador = "||";
				var dataRow = row.join(separador);

				//console.log(dataRow);
				imp =
					`<button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="reportes([[], [], [], ['${row[5]}', '${row[1]}', '${row[6]}']], 'pdf', 'comp_grupal', 0)"><i class="fa-solid fa-print me-2"></i>Reimprimir</button>`;

				imp1 =
					`<button type="button" class="btn btn-outline-secondary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalCreReGrup" onclick="capData('${dataRow}',['#idGru','#ciclo','#fecha', '#nomGrupo', '#codGrup', '#recibo', '#antRe'],[5,6,2,4,7,1,1]);inyecCod('#integrantes','reciboDeGrupos','${row[1]}||${row[5]}||${row[6]}')"><i class="fa-sharp fa-solid fa-pen-to-square"></i></button>`;

				imp2 =
					`<button type="button" class="btn btn-outline-danger btn-sm mt-2" onclick="eliminar('${row[1]}|*-*|${row[5]}|*-*|${row[6]}','eliReGru', ${codusu});"><i class="fa-solid fa-trash-can"></i></button>`;

				return imp + imp1 + imp2;
			}
		},

		],
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
}

function recargarTabla() {
	tablaRecibos.ajax.reload();
}

//FUNCIONES PARA APERTURA Y CIERRE DE CAJA
function save_apertura_cierre(idusuario, banderaoperacion, condi = 'create_caja_apertura', idreg = '0') {
	const formatter = new Intl.NumberFormat('es-GT', {
		minimumFractionDigits: 2,
		maximumFractionDigits: 2
	});

	var saldoinicial = $('#saldoinicial').val() || 0;
	var saldofinal = $('#saldofinal').val() || 0;

	Swal.fire({
		title: 'Confirmación de ' + (banderaoperacion ? 'apertura' : 'cierre') + ' de caja',
		html: `
						<div style="text-align: center;">
								<label><b style="margin-right: 5px;">Saldo inicial:</b>${formatter.format(saldoinicial)}</label>
						</div>`+
			(banderaoperacion ? `` : `
								<div style="text-align: center;">
										<label><b style="margin-right: 5px;">Saldo final:</b>${formatter.format(saldofinal)}</label>
								</div>`) +
			`
						<div style="text-align: center; margin-top: 5px;">
								<label style="color: #0D6EFD; font-size: 0.8rem;">-Revise bien y confirme, ya que no se podra revertir la acción-</label>
						</div>`+
			`<input id="passconf" class="swal2-input" type="password" placeholder="contraseña" autocapitalize="off">`,
		showCancelButton: true,
		confirmButtonText: 'Confirmar ' + (banderaoperacion ? 'apertura' : 'cierre'),
		showLoaderOnConfirm: true,
		preConfirm: () => {
			const password = document.getElementById('passconf').value;
			//AJAX PARA CONSULTAR EL USUARIO
			return $.ajax({
				url: "../../src/cruds/crud_usuario.php",
				method: "POST",
				data: { 'condi': 'confirmar_apertura_cierre_caja', 'idusuario': idusuario, 'pass': password },
				dataType: 'json',
				success: function (data) {
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
			obtiene([`iduser`, `fec_apertura`, `saldoinicial`], [], [], condi, `0`, [idusuario, idreg]);
		}
	});
}

// NEGROY MOSTRAR OCULTAR BTN BOLETAS INFO PRUEBAS GAYs
function showBTN() {
	// Obtener el checkbox de para ocultar
	//var checkshow = document.getElementById('ShowCheck');
	var metodoPagoSelect = document.getElementById('metodoPago');
	// Elementos que se mostrarán u ocultarán con CLASES no ID
	var elementos = document.querySelectorAll('.mostrar');

	elementos.forEach(function (elemento) {
		if (metodoPagoSelect.value === '0') { // checkshow.checked 
			// Mostrar el elemento si el checkbox está seleccionado
			elemento.classList.remove('d-none');
			//cambio.classList.add('d-none');
		} else {
			// Ocultar el elemento si el checkbox no está seleccionado
			elemento.classList.add('d-none');
			//cambio.classList.remove('d-none');
		}
	})
}

/* COPIA DE SCRPT_CRE_INDI  //  negroy podria ser una funcion general  */
function buscar_cuentas() {
	idbanco = document.getElementById("bancoid").value;
	//consultar a la base de datos
	$.ajax({
		url: "../../src/cruds/crud_credito_indi.php",
		method: "POST",
		data: { condi: "buscar_cuentas", id: idbanco },
		beforeSend: function () {
			loaderefect(1);
		},
		success: function (data) {
			const data2 = JSON.parse(data);
			// console.log(data2);
			if (data2[1] == "1") {
				$("#cuentaid").empty();
				for (var i = 0; i < data2[2].length; i++) {
					$("#cuentaid").append(
						"<option value='" +
						data2[2][i]["id"] +
						"'>" +
						data2[2][i]["numcuenta"] +
						"</option>"
					);
				}
			} else {
				$("#cuentaid").empty();
				$("#cuentaid").append("<option value='F000'>Seleccione una cuenta de banco</option>");
				Swal.fire({ icon: "error", title: "¡ERROR!", text: data2[0] });
			}
		},
		complete: function () {
			loaderefect(0);
		},
	});
}


/*
document.addEventListener('DOMContentLoaded', function () {
  // Obtener el checkbox de para ocultar
  var checkshow = document.getElementById('ShowCheck');
  // Elementos que se mostrarán u ocultarán con CLASES no ID
  var elementos = document.querySelectorAll('.mostrar');
  // Agregar un evento al checkbox
  checkshow.addEventListener('change', function () {
	// Llamar a la función para mostrar u ocultar elementos
	mostrarOcultarElementos();
  });
	  // Función para mostrar u ocultar elementos
		function mostrarOcultarElementos() {
			// Recorrer la lista de elementos
			elementos.forEach(function (elemento) {
				if (checkshow.checked) {
					// Mostrar el elemento si el checkbox está seleccionado
					elemento.classList.remove('d-none');
				} else {
					// Ocultar el elemento si el checkbox no está seleccionado
					elemento.classList.add('d-none');
				}
			});
		}
	});*/
