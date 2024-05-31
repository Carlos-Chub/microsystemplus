//#region OBTIENE
//OBTENER DATOS DE INPUTS
function getinputsval(datos) {
  const inputs2 = [''];
  var i = 0;
  while (i < datos.length) {
    inputs2[i] = document.getElementById(datos[i]).value;
    i++;
  }
  return inputs2;
}
//OBTENER DATOS DE SELECTS
function getselectsval(datos) {
  const selects2 = [''];
  i = 0;
  while (i < datos.length) {
    var e = document.getElementById(datos[i]);
    selects2[i] = e.options[e.selectedIndex].value;
    i++;
  }
  return selects2;
}
//OBTENER DATOS DE RADIOS
function getradiosval(datos) {
  const radios2 = [''];
  i = 0;
  while (i < datos.length) {
    radios2[i] = document.querySelector('input[name="' + datos[i] + '"]:checked').value;
    i++;
  }
  return radios2;
}
//FUNCION PARA OBTENER DATOS DE FORMULARIO
function obtiene(inputs, selects, radios, condi, id, archivo) {
  loaderefect(1);

  var inputs2 = []; var selects2 = []; var radios2 = [];
  inputs2 = getinputsval(inputs);
  selects2 = getselectsval(selects);
  radios2 = getradiosval(radios);
  generico(inputs2, selects2, radios2, inputs, selects, radios, condi, id, archivo);
}

//FUNCION QUE HACE CONSULTA A CRUD
function generico(inputs, selects, radios, inputsn, selectsn, radiosn, condi, id, archivo) {
  // console.log(selects)
  // return;
  $.ajax({
    url: "../src/cruds/crud_aportaciones.php",
    method: "POST",
    data: { inputs, selects, radios, inputsn, selectsn, radiosn, condi, id, archivo }, //es el name basicamente, todo esto.
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      const data2 = JSON.parse(data);
      //  console.log(data2);
      // return;
      if (data2[1] == "1") {
        Swal.fire({
          icon: 'success',
          title: 'Muy Bien!',
          text: data2[0]
        })
        if ((condi == "cdaportmov")) {
          // console.log(data2);
          creaComprobante(data2);
          if (selects[0] == "1") {
            Swal.fire({
              title: 'Imprimir libreta?',
              showDenyButton: true,
              confirmButtonText: 'Imprimir',
              denyButtonText: `Cancelar`,
              allowOutsideClick: false
            }).then((result) => {
              if (result.isConfirmed) {
                creaLib(data2[2]);
              } else if (result.isDenied) {
                Swal.fire('Uff', 'Cancelado', 'success')
              }
            })
          }
        }
        //condicion para el reporte de certificados
        if ((condi == "create_certificado_aprt") || (condi == "update_certificado_aprt")) {
          printdiv('Certificados_aprt', '#cuadro', 'APRT_2', '0');
        }
        //condicion para el modal de beneficiario
        else if ((condi == "create_apr_ben") || (condi == 'update_apr_ben')) {
          refrescar_ben('lista_beneficiarios', id);
        }
        //condicion para los movimientos de recibo
        else if ((condi == 'reimpresion_recibo')) {
          // console.log(data2);
          creaComprobante(data2);
          cancelar_edit_recibo();
          printdiv2("#cuadro", id);
        }
        else {
          printdiv2("#cuadro", id);
        }
      }
      //SECCION DE REPORTES EN EXCEL Y PDF CON PHP---------------
      else if (data2[0] == "reportes_aportaciones") {
        reportes_aportaciones(data2);
      }
      //FINALIZACION DE SECCION DE REPORTES-----------------------
      else {
        Swal.fire({
          icon: 'error',
          title: '¡ERROR!',
          text: data2[0]
        })
        loaderefect(0);
      }
    },
    complete: function () {
      loaderefect(0);
    }
  })
}
//#endregion

//#region PRINTDIV Y DEMAS
//OBTIENE LA CONDICION
function condimodal() {
  var condi = document.getElementById("condi").value;
  return condi;
}
//OBTIENE NOMBRE DEL ARCHIVO
function filenow() {
  var file = document.getElementById("file").value;
  return file;
}

//FUNCION PRINCIPAL PARA MOSTRAR VENTANAS
function printdiv(condi, idiv, dir, xtra) {
  loaderefect(1);
  dire = "./APRT/" + dir + ".php";
  $.ajax({
    url: dire,
    method: "POST",
    data: {
      condi,
      xtra
    },
    success: function (data) {
      loaderefect(0);
      $(idiv).html(data);
    }
  })
}

//RECARGAR VENTANA PRINCIPAL
function printdiv2(idiv, xtra) {
  loaderefect(1);
  condi = condimodal();
  dir = filenow();
  dire = "APRT/" + dir + ".php";
  $.ajax({
    url: dire,
    method: "POST",
    data: { condi, xtra },
    success: function (data) {
      $(idiv).html(data);
      loaderefect(0);
    }
  })
}
//#endregion

//#region ELIMINAR REGISTRO
//FUNCION PARA ELIMINAR UN REGISTRO
function eliminar(ideliminar, dir, xtra, condi) {
  dire = "../src/cruds/" + dir + ".php";
  Swal.fire({
    title: '¿ESTA SEGURO DE ELIMINAR?',
    showDenyButton: true,
    confirmButtonText: 'Eliminar',
    denyButtonText: `Cancelar`,
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: dire,
        method: "POST",
        data: { condi, ideliminar },
        beforeSend: function () {
          loaderefect(1);
        },
        success: function (data) {
          const data2 = JSON.parse(data);
          if (data2[1] == "1") {
            Swal.fire('Correcto', data2[0], 'success');
            if (condi == 'delete_apr_ben') {
              refrescar_ben('lista_beneficiarios', xtra);
            } else {
              printdiv2("#cuadro", xtra);
            }
          }
          else Swal.fire('Uff', data2[0], 'error')
        },
        complete: function () {
          loaderefect(0);
        }
      })
    } else if (result.isDenied) {
      Swal.fire('Uff', 'Cancelado', 'success')
    }
  })
}
//#endregion

//#region FUNCIONES BASICAS
//FUNCION PARA FECHA HOY FORMATEADO
function formato_fecha() {
  let yourDate = new Date()
  yourDate.toISOString().split('T')[0]
  const offset = yourDate.getTimezoneOffset()
  yourDate = new Date(yourDate.getTime() - (offset * 60 * 1000))
  return yourDate.toISOString().split('T')[0]
}

//DARLE FORMATO DE MONEDA A NUMERO
const currency = function (number) {
  return new Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ', minimumFractionDigits: 2 }).format(number);
};

//FUNCION QUE FORMATEA UNA FECHA COMO ENTRADA
function conviertefecha(fecharecibidatexto) {
  fec = fecharecibidatexto;
  anio = fec.substring(0, 4);
  mes = fec.substring(5, 7);
  dia = fec.substring(8, 10);

  ensamble = mes + "-" + dia + "-" + anio;
  fecha = new Date(ensamble).toLocaleDateString('es-GT');
  return fecha;
}

//FUNCION PARA CONVERTIR MES A LETRAS
function convertir_mes(numeroMes) {
  var meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
  var mes = "";
  if (!isNaN(numeroMes) && numeroMes >= 1 && numeroMes <= 12) {
    mes = meses[numeroMes - 1];
  }
  return mes;
}
//#endregion

//#region GENERADOR DE CORRELATIVO
//GENERADOR DE CORRELATIVO DE CUENTA
function correltipcuenta(tipo, ins, ofi) {
  dire = "../src/cruds/crud_aportaciones.php";
  condi = "correl";
  var ant = document.getElementsByName('targets');
  // console.log(ant)
  i = 0;
  while (i < (ant.length)) {
    ant[i].className = 'tarjeta';
    i++;
  }
  var intro = document.getElementById('' + tipo);
  intro.className = 'tarjeta tarjeta-activa';
  $.ajax({
    url: dire,
    method: "POST",
    data: { condi, tipo, ins, ofi },
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      const data2 = JSON.parse(data);
      document.getElementById("correla").value = data2[0];
      document.getElementById("tasa").value = data2[1];
      //ocultar si la tasa es 0
      if (data2[1] == 0) {
        $("#div_tasa").hide();
      }
      else {
        $("#div_tasa").show();
      }
      document.getElementById("tipCuenta").value = tipo;
      document.getElementById("ccodofi").value = data2[2];
    },
    complete: function () {
      loaderefect(0);
    }
  })
}
//#endregion

//#region VALIDAR CUENTA APORTACION
//FUNCION PARA VALIDAR UNA CUENTA DE APORTACION
function aplicarcod(codigo) {
  var cod = document.getElementById(codigo).value;
  if (cod == "") { cod = "01"; }
  printdiv2("#cuadro", cod);
}
//#endregion

//#region HABILITAR Y DESHABILITAR INPUTS
//FUNCION PARA HABILITAR O DESHABILITAR INPUTS
function habdeshab(hab, des) {
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

//COMPLEMENTO DE HABILITAR Y DESHABILITAR
function tipdoc(ids) {
  banco = document.getElementById('region_cheque');
  switch (ids) {
    case "E":
      banco.style = "display:none";
      break;
    case "D":
      banco.style = "display:block";
      break;
    case "C":
      banco.style = "display:block";
      break;
    case "T":
      habdeshab(['ccodahodestino'], ['nrochq', 'tipchq', 'feccom', 'numpartida'])
      break;
  }
}
function buscar_cuentas() {
  idbanco = document.getElementById('bancoid').value;
  $.ajax({
    url: "../src/cruds/crud_bancos.php",
    method: "POST",
    data: { 'condi': 'buscar_cuentas', 'id': idbanco },
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      const data2 = JSON.parse(data);
      $("#cuentaid").empty();
      $("#cuentaid").append("<option value='0' selected disabled>Seleccione una cuenta</option>");
      if (data2[1] == "1") {
        for (var i = 0; i < data2[2].length; i++) {
          $("#cuentaid").append("<option value='" + data2[2][i]["id"] + "'>" + data2[2][i]["numcuenta"] + "</option>");
        }
      } else {
        Swal.fire({ icon: "error", title: "¡ERROR!", text: data2[0] });
      }
    },
    complete: function () {
      loaderefect(0);
    }
  })
}
//FUNCION PARA TRAER EL NUMERO DE CHEQUE EN AUTOMATICO
function cheque_automatico(id_cuenta_banco, id_reg_cheque) {
  $.ajax({
    url: "../src/cruds/crud_bancos.php",
    method: "POST",
    data: { 'condi': 'cheque_automatico', 'id_cuenta_banco': id_cuenta_banco, 'id_reg_cheque': id_reg_cheque },
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      const data2 = JSON.parse(data);
      $("#numcheque").val(data2[2]);
    },
    complete: function () {
      loaderefect(0);
    }
  })
}
//#endregion


//#region GUARDADO DE RETIRO Y DEPOSITO
//FUNCION QUE SIRVE PARA GUARDAR RETIROS Y DEPOSITOS
function saveaprmov(flag, usu, ofi, tipotransaction) {
  if (flag == "") {
    //restringir la fecha actual
    var fecha_input = document.getElementById("dfecope").value;
    if (fecha_input < formato_fecha()) {
      Swal.fire({
        icon: 'error',
        title: '¡ERROR!',
        text: 'Debe ingresar una fecha de transaccion no menor a hoy'
      })
      return;
    }
    //se obtienen los valores de los campos
    obtiene(['ccodaport', 'dfecope', 'cnumdoc', 'monto', 'numpartida', 'feccom', 'nrochq', 'cuotaIngreso'], ['salida', 'tipdoc', 'tipchq'], [], 'cdaportmov', '0', [usu, ofi, tipotransaction]);
  }
  else {
    Swal.fire({
      icon: 'error',
      title: '¡ERROR!',
      text: 'Debe seleccionar una cuenta'
    })
  }
}
//#endregion

//#region RECIBO DE RETIRO Y DEPOSITO
//RECIBO DE RETIRO Y DEPOSITO
function creaComprobante(datos, file = 11, bandera = 1) {
  consultar_reporte(file, bandera).then(function (action) {
    //PARTE ENCARGADA DE RETORNAR EL NOMBRE DE LA FUNCION A EJECUTAR
    if (bandera == 1) {
      file = action;
    } else {
      file = file;
    }
    //EJECUTA FUNCION BASADO EN LA VARIABLE FILE
    window[file](datos);
  }).catch(function (error) {
    Swal.fire("Uff", error, "error");
  });
}

//FUNCIONES PARA EDICION DE RECIBO
//FUNCION QUE ABRE MODAL PARA LA EDICION DE RECIBO
function modal_edit_recibo(id_recibo, numdoc_ant, ccodaport, codusu) {
  $('#edicion_recibo').modal('show');
  document.getElementById("id_recibo").value = id_recibo;
  document.getElementById("id_codusu").value = codusu;
  document.getElementById("numdoc_modal_recibo_ant").value = numdoc_ant;
  document.getElementById("ccodaport_recibo").value = ccodaport;
}

//FUNCION PARA CANCELAR LA EDICION
function cancelar_edit_recibo() {
  document.getElementById("id_recibo").value = "";
  document.getElementById("id_codusu").value = "";
  document.getElementById("numdoc_modal_recibo_ant").value = "";
  document.getElementById("ccodaport_recibo").value = "";
  document.getElementById("numdoc_modal_recibo").value = "";
  $('#edicion_recibo').modal('hide');
}
//#endregion

//#region FUNCIONES DE IMPRESION DE LIBRETA
//FUNCION PARA EL CAMBIO DE LIBRETA
function creaLib(cod, file = 9, bandera = 1) {
  //CONSULTA PARA TRAER QUE REPORTE SE QUIERE
  var data2;
  consultar_reporte(file, bandera).then(function (action) {
    //PARTE ENCARGADA DE GENERAR EL REPORTE
    if (bandera == 1) {
      file = action;
    } else {
      file = file;
    }
    return consultar_movimientos_libreta(file, cod);
  }).then(function (action2) {
    // DATOS DE MOVIMIENTOS DE AHORRO
    data2 = (action2);
    // IDENTIFICADOR DE ARCHIVO DE OPERACIONES DE LIBRETA
    file = 10;
    return consultar_reporte(file, bandera);
  }).then(function (action3) {
    // INTERCAMBIO DE VARIABLES
    if (bandera == 1) {
      file = action3;
    } else {
      file = file;
    }
    //SE PREPARAN LOS DATOS
    var inif = parseInt(data2[0][2]);
    var nfront = parseInt(data2[0][0]);
    var inid = parseInt(data2[0][3]);
    var ndors = parseInt(data2[0][1]);

    numi = parseInt(data2[1][0]['numlinea']);
    numf = parseInt(data2[1][data2[1].length - 1]['numlinea']);
    saldo = parseFloat(data2[0][4]);

    resta = 0;
    ini = 0;
    posfin = 0;
    if (numi <= nfront) {
      resta = 0;
      ini = inif;
      posfin = nfront;
    }
    if (numi > nfront) {
      resta = nfront;
      ini = inid;
      posfin = nfront + ndors;
    }
    //EJECUTA FUNCION BASADO EN LA VARIABLE FILE
    window[file](data2, resta, ini, 0, posfin, saldo, numi, file);
  }).catch(function (error) {
    Swal.fire("Uff", error, "error");
  });
}

function consultar_movimientos_libreta(file, cod) {
  return new Promise(function (resolve, reject) {
    dire = "../views/APRT/reportes/" + file + ".php";
    condi = "lib";
    $.ajax({
      url: dire,
      method: "POST",
      data: { condi, cod },
      beforeSend: function () {
        loaderefect(1);
      },
      success: function (data) {
        const data2 = JSON.parse(data);
        if (data2[2] == "1") {
          resolve(data2);
        } else {
          reject("NO HAY DATOS PARA IMPRIMIR")
        }
      },
      complete: function () {
        loaderefect(0);
      },
    });
  });
}

//RELLENAR ESPACIOS EN LA IMPRESION DE LIBRETA
function pad(input) {
  var cadenaNumerica = '*************';
  var resultado = cadenaNumerica + input;
  return resultado = resultado.substring(resultado.length - cadenaNumerica.length);
}

//RELLENO 2 EN LA IMPRESION DE LIBRETAS
function relleno2(input, caracter = " ", cant = 12) {
  cadenaNumerica = ""
  var i = 0;
  while (i < cant) {
    cadenaNumerica = cadenaNumerica + caracter;
    i++;
  }
  var resultado = cadenaNumerica + input;
  return resultado = resultado.substring(resultado.length - cadenaNumerica.length);
}
//#endregion
//#region REPORTE ESTADO DE CUENTA
//FUNCION PARA EL REPORTE DE ESTADO DE CUENTA
function statusaccount(id) {
  var win = window.open('APRT/reportes/estado_cuenta_aprt.php?id=' + id, '_blank');
}
//#endregion

//#region FUNCIONES PARA BENEFICIARIOS
//EDITAR BENEFICIARIO
function editben(idaprben, bennom, bendpi, bendire, benparent, benfec, benporcent, bentel) {
  $('#databen').modal('show');
  document.getElementById("createben").style = "display:none;";
  document.getElementById("updateben").style = "display:yes;";
  document.getElementById("idben").value = idaprben;
  document.getElementById("benname").value = bennom;
  document.getElementById("bendpi").value = bendpi;
  document.getElementById("bendire").value = bendire;
  document.getElementById("bentel").value = bentel;
  document.getElementById("bennac").value = benfec;
  document.getElementById("benporcent").value = benporcent;
  document.getElementById("benporcentant").value = benporcent;
  document.getElementById("benparent").value = benparent;
}

//FUNCION QUE SIRVE PARA ABRIR EL MODAL DEL BENEFICIARIO
function crear_editar_beneficiario(ccodaport, nombre) {
  if (ccodaport === "0" || nombre === "") {
    Swal.fire({
      icon: 'error',
      title: '¡ERROR!',
      text: 'Debe seleccionar una cuenta de aportación'
    });
    return;
  }

  refrescar_ben('lista_beneficiarios', ccodaport);
  $('#databen').modal('show');
  document.getElementById("ccodaport_modal").value = ccodaport;
  document.getElementById("name_modal").value = nombre;
}

//FUNCION PARA EL BOTON CANCELAR DE AGREGAR Y EDITAR BENEFICIARIO
function cancelar_crear_editar_beneficiario(condi, id) {
  loaderefect(1);
  dire = "../src/cruds/crud_aportaciones.php";
  $.ajax({
    url: dire,
    type: "POST",
    data: { 'condi': condi, 'l_codaport': id },
    dataType: "JSON",
    success: function (data) {
      loaderefect(0);
      //cuando se intenta ingresar el primer beneficiario pero no se hace al final
      if ((data[1] != 100) && ((data[0][0].length) == 0)) {
        printdiv2('#cuadro', id)
        $('#databen').modal('hide');
      }
      //cuando el porcentaje es 100, despues de existir un registro
      else if (data[1] == 100) {
        printdiv2('#cuadro', id)
        $('#databen').modal('hide');
        //cuando el porcentaje no es 100
      } else {
        Swal.fire({
          icon: 'error',
          title: '¡ERROR!',
          text: 'Tiene que ajustar a los beneficiarios para que en total sumen 100%, de lo contrario no puede salir de la ventana'
        });
        limpiar_modal_ben();
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      alert('Error...');
    }
  });
}

//FUNCION PARA RECARGAR LA TABLA DE BENEFICIARIO DEL MODAL
function refrescar_ben(condi, id) {
  loaderefect(1);
  dire = "../src/cruds/crud_aportaciones.php";
  $.ajax({
    url: dire,
    type: "POST",
    data: { 'condi': condi, 'l_codaport': id },
    dataType: "JSON",
    success: function (data) {
      if (data[1] == 0) {
        loaderefect(0);
        return;
      }
      var html = '';
      var i;
      for (i = 0; i < data[0].length; i++) {
        html += '<tr>' +
          '<td>' + data[0][i]['dpi'] + '</td>' +
          '<td>' + data[0][i]['nombre'] + '</td>' +
          '<td>' + data[0][i]['fecnac'] + '</td>' +
          '<td>' + data[0][i]['pariente'] + '</td>' +
          '<td>' + data[0][i]['porcentaje'] + '</td>' +
          '<td> <button type="button" class="btn btn-warning me-1" title="Editar Beneficiario" onclick="editben(' + data[0][i]['id_ben'] + ',`' + data[0][i]['nombre'] + '`,`' + data[0][i]['dpi'] + '`,`' + data[0][i]['direccion'] + '`,' + data[0][i]['codparent'] + ',`' + data[0][i]['fecnac'] + '`,' + data[0][i]['porcentaje'] + ',`' + data[0][i]['telefono'] + '`)"' + '>' +
          '<i class="fa-solid fa-pen"></i>' +
          '</button>' +
          '<button type="button" class="btn btn-danger" title="Eliminar Beneficiario" onclick="eliminar(' + data[0][i]['id_ben'] + ',`crud_aportaciones`,`' + id + '`,`delete_apr_ben`)"' + '>' +
          '<i class="fa-solid fa-trash-can"></i>' +
          '</button>' +
          '</td>' +
          '</tr>';
      }
      $('#tabla_ben').html(html);
      document.querySelector('#total').innerText = 'Total: ' + data[1] + '%';
      limpiar_modal_ben();
      loaderefect(0);
    },
    error: function (jqXHR, textStatus, errorThrown) {
      alert('Error...');
      loaderefect(0);
    }
  });
}

//LIMPIAR EL MODAL DEL BENEFICIARIO
function limpiar_modal_ben() {
  document.getElementById("createben").style = "display:yes;";
  document.getElementById("updateben").style = "display:none;";
  document.getElementById("idben").value = "";
  document.getElementById("benname").value = "";
  document.getElementById("bendpi").value = "";
  document.getElementById("bendire").value = "";
  document.getElementById("bentel").value = "";
  var date1 = document.getElementById('bennac');
  date1.value = formato_fecha();
  document.getElementById("benporcent").value = "";
  document.getElementById("benporcentant").value = "";
  document.getElementById("benparent").value = "";
}
//#endregion

//#region FUNCIONES UTILIZADAS PARA LOS REPORTES PDF Y EXCEL
//FUNCION GENERAL PARA LOS REPORTES
function reportes_aportaciones(data1) {
  //  console.log(data1);
  // return
  loaderefect(1);
  $.ajax({
    url: 'APRT/reportes/' + data1[1] + '_' + data1[2] + '.php',
    async: true,
    type: "POST",
    dataType: "html",
    contentType: "application/x-www-form-urlencoded",
    data: { data: data1 },
    success: function (data) {
      loaderefect(0);
      var opResult = JSON.parse(data);
      var $a = $("<a>");
      $a.attr("href", opResult.data);
      $("body").append($a);
      $a.attr("download", data1[1] + "_" + data1[4] + "." + data1[3]);
      $a[0].click();
      $a.remove();
    }
  })
}

function consultar_reporte(file, bandera) {
  return new Promise(function (resolve, reject) {
    if (bandera == 0) {
      resolve('Aprobado');
    }
    $.ajax({
      url: "../src/cruds/crud_aportaciones.php",
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

function reportes(datos, tipo, file, download = 1, bandera = 0) {
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
    //PARTE ENCARGADA DE GENERAR EL REPORTE
    var url = "APRT/reportes/" + file + ".php";
    $.ajax({
      url: url,
      async: true,
      type: "POST",
      dataType: "html",
      contentType: "application/x-www-form-urlencoded",
      data: { datosval, tipo },
      beforeSend: function () {
        loaderefect(1);
      },
      success: function (data) {
        console.log(data)
        var opResult = JSON.parse(data);
        // console.log(opResult)
        // if (opResult.status == 0) {
        //     Swal.fire({ icon: 'error', title: '¡ERROR sin datos', text: opResult.mensaje });
        //     return;
        // }
        if (opResult.status == 1) {
          switch (download) {
            case 0:
              const ventana = window.open();
              ventana.document.write("<object data='" + opResult.data + "' type='application/" + opResult.tipo + "' width='100%' height='100%'></object>");
              break;
            case 1:
              var $a = $("<a href='" + opResult.data + "' download='" + opResult.namefile + "." + tipo + "'>");
              $("body").append($a);
              $a[0].click();
              $a.remove();
              break;
          }
          Swal.fire({ icon: 'success', title: 'Muy Bien!', text: opResult.mensaje });
        } else {
          Swal.fire({ icon: 'error', title: '¡ERROR!', text: opResult.mensaje });
        }
      },
      complete: function () {
        loaderefect(0);
      }
    });
  }).catch(function (error) {
    Swal.fire("Uff" + error, error, "error");
  });

}

//FUNCION PARA ACTIVAR Y DESACTIVAR UN SELECT CUANDO SE PRESIONA UN RADIO BUTTON
function activar_select_cuentas(radio, estado, select) {
  if (radio.checked) {
    if (estado) {
      document.getElementById(select).disabled = estado;
      $("#" + select).val(0);
    }
    else {
      //cuando se seleccionan una cuenta se habilita el select
      document.getElementById(select).disabled = estado;
    }
  }
}

//FUNCION PARA ACTIVAR Y DESACTIVAR INPUTS CUANDO SE PRESION UN RADIO BUTTON
function activar_input_dates(radio, estado, dateInicial, dateFinal) {
  if (radio.checked) {
    if (estado) {
      document.getElementById(dateInicial).disabled = estado;
      document.getElementById(dateFinal).disabled = estado;
      var date1 = document.getElementById(dateInicial);
      date1.value = formato_fecha();
      var date2 = document.getElementById(dateFinal);
      date2.value = formato_fecha();

    }
    else {
      //cuando se seleccionan una cuenta se habilita el select
      document.getElementById(dateInicial).disabled = estado;
      document.getElementById(dateFinal).disabled = estado;
    }
  }
}
//#endregion

//#region FUNCION CON LO DE CERTIFICADOS
//FUNCION PARA IMPRIMIR CERTIFICADO
function imprimir_certificado_aprt(idcrt, crud, condi, estado, newcod, codusu, file = 12, bandera = 1) {
  consultar_reporte(file, bandera).then(function (action) {
    //PARTE ENCARGADA DE RETORNAR EL NOMBRE DE LA FUNCION A EJECUTAR
    if (bandera == 1) {
      file = action;
    } else {
      file = file;
    }
    $.ajax({
      url: "../src/cruds/" + crud + ".php",
      method: "POST",
      data: { condi, idcrt, estado, newcod, codusu },
      beforeSend: function () {
        loaderefect(1);
      },
      success: function (data) {
        const data2 = JSON.parse(data);
        printdiv2("#cuadro", '0');
        //EJECUTA FUNCION BASADO EN LA VARIABLE FILE
        window[file](data2);
      },
      beforeSend: function () {
        // loaderefect(0);
      },
    })
  }).catch(function (error) {
    Swal.fire("Uff", error, "error");
  });
}

//FUNCION QUE ABRE MODAL PARA LA REIMPRESION DE CERTIFICADO
function modal_cambio_certif(id, ccodaport, codusu) {
  $('#cambio_certif').modal('show');
  document.getElementById("id_modal_crt").value = id;
  document.getElementById("ccodaport_modal_crt").value = ccodaport;
  document.getElementById("id_codusu_crt").value = codusu;
}

//FUNCION PARA CANCELAR LA REIMPRESION DE CERTIFICADO
function cancelar_cambio_certif() {
  var idcrt = document.getElementById("id_modal_crt").value = "";
  var codusu = document.getElementById("id_codusu_crt").value = "";
  var newcod = document.getElementById("certif_modal").value = "";
  $('#cambio_certif').modal('hide');
}

//FUNCION QUE CREA PERMITE LA REIMPRESION CON NUEVO CODIGO DE CERTIFICADO
function create_cambio_certif() {
  //obtener idcrt y codusu
  var idcrt = document.getElementById("id_modal_crt").value;
  var codusu = document.getElementById("id_codusu_crt").value;
  var newcod = document.getElementById("certif_modal").value;
  if (newcod === "" || newcod === null) {
    Swal.fire({
      icon: 'error',
      title: '¡ERROR!',
      text: 'Debe agregar un código de certificado'
    })
    return;
  }
  imprimir_certificado_aprt(idcrt, 'crud_aportaciones', 'pdf_certificado_aprt', 'R', newcod, codusu);
  var idcrt = document.getElementById("id_modal_crt").value = "";
  var codusu = document.getElementById("id_codusu_crt").value = "";
  var newcod = document.getElementById("certif_modal").value = "";
  $('#cambio_certif').modal('hide');

}
//#endregion

//#region FUNCIONES PARA LA PARAMETRIZACION DE CUENTAS

//PRINT DIV MEJORADO
function printdiv3(condi, idiv, xtra) {
  loaderefect(1);
  dir = filenow();
  dire = "APRT/" + dir + ".php";
  $.ajax({
    url: dire,
    method: "POST",
    data: { condi, xtra },
    success: function (data) {
      loaderefect(0);
      $(idiv).html(data);
    }
  })
}

//PRINT SUPER MEJORADO - FUNCION PARA IMPRIMIR DATOS EN INPUTS, PERMITE CONCATENAR DATOS Y LUEGO IMPRIMIR
function printdiv4(id_hidden, valores) {
  //todo el input
  var todo = ($(id_hidden).val()).split("/");
  //se extraen los nombres de los inputs
  var nomInputs = (todo[0].toString()).split(",");
  //se extraen los rangos
  var rangos = (todo[1].toString()).split(",");
  //se extrae el separador
  var separador = todo[2].toString();

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
      $("#" + nomInputs[index]).val(concatenacion);
    } else {
      $("#" + nomInputs[index]).val(valores[contador]);
      contador++;
    }
  }
}

//FUNCION PARA OBTENER DATOS QUE SE ENVIAN DESDE EL MODAL
function seleccionar_cuenta_ctb(id_hidden, datos) {
  //IMPRIME LOS DATOS EN EL INPUT CORRESPONDIENTE
  printdiv4(id_hidden, datos);
  cerrar_modal('#modal_nomenclatura', '#id_modal_hidden');
}

//FUNCION PARA ABRIR CUALQUIER MODAL Y PASARLE UN DATO
function abrir_modal(id_modal, id_hidden, dato) {
  $(id_modal).modal("show");
  $(id_hidden).val(dato);
}

//FUNCION PARA CERRAR CUALQUIER MODAL Y LIMPIAR EL DATO PASADO
function cerrar_modal(id_modal, id_hidden) {
  $(id_modal).modal("hide");
  //limpiar el campo pasado
  $(id_hidden).val("");
}

function pruebas() {
  // console.log($("#id_hidden2").val());
  // console.log($("#text_cuenta2").val());
}
//#endregion

//#region FUNCION QUE EVALUA CUANDO CARGA TODOS LOS RECURSOS Y DESAPARECE EL LOADER
$(document).ready(function () {
  loaderefect(0);
});
//#endregion

//#region FUNCION PARA CARGAR UNA TABLA TIPO DataTable
function convertir_tabla_a_datatable(id_tabla) {
  $('#' + id_tabla).on('search.dt')
    .DataTable({
      "lengthMenu": [
        [5, 10, 15, -1],
        ['5 filas', '10 filas', '15 filas', 'Mostrar todos']
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
        "sProcessing": "Procesando...",
      },
    });
}
//#endregion
